<?php
session_start();

// Redirect if user not logged in
if (!isset($_SESSION['username'])) {
    header('location: login.php');
    exit;
}

include('../config/db_connect.php');

// User info
$role = $_SESSION['role'] ?? 'member';
$username = $_SESSION['username'] ?? '';
$fullname = $_SESSION['fullname'] ?? ucfirst($role);

// Fetch upcoming events (exclude archived)
$eventSql = "SELECT * FROM events WHERE is_archived = 0 AND date >= CURDATE() ORDER BY date ASC";
$eventResult = $conn->query($eventSql);
if (!$eventResult) die("Query failed: " . $conn->error);

// Prepare event links for calendar (first event of the day)
$eventDatesLinks = [];
if ($eventResult && $eventResult->num_rows > 0) {
    $eventResult->data_seek(0);
    while ($row = $eventResult->fetch_assoc()) {
        $date = $row['date'];
        if (!isset($eventDatesLinks[$date])) {
            $eventDatesLinks[$date] = $row['id'];
        }
    }
}

// Quick stats for all roles
$membersRow = $conn->query("SELECT COUNT(*) AS total FROM members")->fetch_assoc();
$members = $membersRow ? (int)$membersRow['total'] : 0;

/* --- REPLACED: count distinct members assigned as officers + assignments + archived distinct --- */
$officersStats = $conn->query("SELECT COUNT(DISTINCT member_id) AS distinct_members, COUNT(*) AS assignments FROM officers")->fetch_assoc();
$officersActive = $officersStats ? (int)$officersStats['distinct_members'] : 0;
$officersAssignments = $officersStats ? (int)$officersStats['assignments'] : 0;

$officersArchivedRow = $conn->query("SELECT COUNT(DISTINCT member_id) AS distinct_members FROM officers_archive")->fetch_assoc();
$officersArchived = $officersArchivedRow ? (int)$officersArchivedRow['distinct_members'] : 0;

/* legacy variable used elsewhere */
$officers = $officersActive;

$announcementsRow = $conn->query("SELECT COUNT(*) AS total FROM announcements")->fetch_assoc();
$announcements = $announcementsRow ? (int)$announcementsRow['total'] : 0;

// Events: compute total (includes archived) + upcoming for quick info
$eventsTotalRow = $conn->query("SELECT COUNT(*) AS total FROM events")->fetch_assoc();
$eventsTotal = $eventsTotalRow ? (int)$eventsTotalRow['total'] : 0;

$eventsUpcomingRow = $conn->query("SELECT COUNT(*) AS total FROM events WHERE is_archived=0 AND date >= CURDATE()")->fetch_assoc();
$eventsUpcoming = $eventsUpcomingRow ? (int)$eventsUpcomingRow['total'] : 0;

// --- Member analytics: detect columns and prepare datasets ---
$memberCols = [];
$colsRes = $conn->query("SHOW COLUMNS FROM members");
if ($colsRes) {
    while ($c = $colsRes->fetch_assoc()) $memberCols[] = $c['Field'];
}

$dateCandidates = ['created_at','date_added','date_registered','created','date_joined','reg_date','registered_at','date','added_on'];
$dateCol = null;
foreach ($dateCandidates as $c) { if (in_array($c, $memberCols)) { $dateCol = $c; break; } }

$statusCandidates = ['status','is_active','active','member_status','status_id'];
$statusCol = null;
foreach ($statusCandidates as $c) { if (in_array($c, $memberCols)) { $statusCol = $c; break; } }

$typeCandidates = ['member_type','type','role','category','member_category'];
$typeCol = null;
foreach ($typeCandidates as $c) { if (in_array($c, $memberCols)) { $typeCol = $c; break; } }

// last 6 months labels
$memberMonthLabels = [];
$memberMonthIndex = [];
for ($i = 5; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-$i month"));
    $memberMonthIndex[$ym] = 0;
    $memberMonthLabels[] = date('M Y', strtotime($ym . '-01'));
}

$monthlyNewData = array_values($memberMonthIndex);
$activeTrendActive = array_values($memberMonthIndex);
$activeTrendInactive = array_values($memberMonthIndex);

if ($dateCol) {
    // Monthly new members
    $sql = "SELECT DATE_FORMAT($dateCol, '%Y-%m') AS ym, COUNT(*) AS cnt FROM members WHERE $dateCol >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) GROUP BY ym ORDER BY ym";
    $res = $conn->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $idx = array_search(date('M Y', strtotime($r['ym'] . '-01')), $memberMonthLabels);
            if ($idx !== false) $monthlyNewData[$idx] = (int)$r['cnt'];
        }
    }

    // Active vs Inactive per month (if status column exists)
    if ($statusCol) {
        $sql2 = "SELECT DATE_FORMAT($dateCol, '%Y-%m') AS ym, $statusCol AS st, COUNT(*) AS cnt FROM members WHERE $dateCol >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) GROUP BY ym, st ORDER BY ym";
        $r2 = $conn->query($sql2);
        if ($r2) {
            while ($row = $r2->fetch_assoc()) {
                $label = date('M Y', strtotime($row['ym'] . '-01'));
                $idx = array_search($label, $memberMonthLabels);
                if ($idx === false) continue;
                $val = $row['cnt'];
                $st = $row['st'];
                $isActive = false;
                if (is_numeric($st)) { $isActive = intval($st) === 1; }
                else { $ls = strtolower(trim((string)$st)); $isActive = in_array($ls, ['active','a','yes','y','true','1']); }
                if ($isActive) $activeTrendActive[$idx] = (int)$activeTrendActive[$idx] + (int)$val;
                else $activeTrendInactive[$idx] = (int)$activeTrendInactive[$idx] + (int)$val;
            }
        }
    }
}

// If no date column, attempt an overall active/inactive split (no trend)
if (!$dateCol && $statusCol) {
    $overall = $conn->query("SELECT $statusCol AS st, COUNT(*) FROM members GROUP BY st");
    if ($overall) {
        while ($r = $overall->fetch_assoc()) {
            $st = $r['st']; $cnt = (int)$r['cnt'];
            $isActive = false;
            if (is_numeric($st)) { $isActive = intval($st) === 1; }
            else { $ls = strtolower(trim((string)$st)); $isActive = in_array($ls, ['active','a','yes','y','true','1']); }
            if ($isActive) $activeTrendActive = [$cnt,0,0,0,0,0]; else $activeTrendInactive = [$cnt,0,0,0,0,0];
        }
    }
}

// Member types distribution
$memberTypesLabels = [];
$memberTypesData = [];
if ($typeCol) {
    $tq = $conn->query("SELECT COALESCE(NULLIF($typeCol, ''), 'Unknown') AS t, COUNT(*) AS cnt FROM members GROUP BY t ORDER BY cnt DESC");
    if ($tq) {
        while ($tr = $tq->fetch_assoc()) {
            $memberTypesLabels[] = $tr['t'];
            $memberTypesData[] = (int)$tr['cnt'];
        }
    }
}

// --- Event analytics (replace member types card) ---
$eventCols = [];
$ecRes = $conn->query("SHOW COLUMNS FROM events");
if ($ecRes) { while ($r = $ecRes->fetch_assoc()) $eventCols[] = $r['Field']; }

$eventDateCandidates = ['date','event_date','start_date','event_start','created_at'];
$eventDateCol = null;
foreach ($eventDateCandidates as $c) { if (in_array($c, $eventCols)) { $eventDateCol = $c; break; } }

$eventTypeCandidates = ['category','event_type','type','event_category'];
$eventTypeCol = null;
foreach ($eventTypeCandidates as $c) { if (in_array($c, $eventCols)) { $eventTypeCol = $c; break; } }

// last 6 months for events
$eventMonthLabels = [];
$eventMonthIndex = [];
for ($i = 5; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-$i month"));
    $eventMonthIndex[$ym] = 0;
    $eventMonthLabels[] = date('M Y', strtotime($ym . '-01'));
}
$eventMonthData = array_values($eventMonthIndex);

if ($eventDateCol) {
    $esql = "SELECT DATE_FORMAT($eventDateCol, '%Y-%m') AS ym, COUNT(*) AS cnt FROM events WHERE $eventDateCol >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) GROUP BY ym ORDER BY ym";
    $eres = $conn->query($esql);
    if ($eres) {
        while ($er = $eres->fetch_assoc()) {
            $label = date('M Y', strtotime($er['ym'] . '-01'));
            $idx = array_search($label, $eventMonthLabels);
            if ($idx !== false) $eventMonthData[$idx] = (int)$er['cnt'];
        }
    }
}

$eventTypesLabels = [];
$eventTypesData = [];
if ($eventTypeCol) {
    $etq = $conn->query("SELECT COALESCE(NULLIF($eventTypeCol, ''), 'Unknown') AS t, COUNT(*) AS cnt FROM events GROUP BY t ORDER BY cnt DESC");
    if ($etq) {
        while ($er = $etq->fetch_assoc()) {
            $eventTypesLabels[] = $er['t'];
            $eventTypesData[] = (int)$er['cnt'];
        }
    }
}

// March event highlight value (explicitly requested)
$marchEventPercent = 82;

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo ucfirst($role); ?> Dashboard | Bangkero & Fishermen Association</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
body { font-family: 'Segoe UI', sans-serif; background: #f9f9f9; margin: 0; }
.main-content { margin-left: 250px; padding: 32px; min-height: 100vh; box-sizing: border-box; overflow-x: hidden; }

/* Dashboard Cards */
.dashboard-card { 
  border-radius: 18px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.08);
  transition: transform 0.13s, box-shadow 0.13s;
  border: none;
  background: linear-gradient(145deg, #fbe9e7, #80cbc4 70%);
}
.dashboard-card:hover {
  transform: translateY(-2px) scale(1.01);
  box-shadow: 0 6px 32px rgba(2,136,209,0.13);
}
.icon-box {
  border-radius: 10px;
  background: #ff7043;
  color: #fff;
  padding: 14px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 2rem;
}

/* Event Cards */
.event-card {
  border-radius: 18px;
  box-shadow: 0 2px 14px rgba(2,136,209,0.08);
  border: none;
  background: #fff;
  color: #4e342e;
  transition: box-shadow 0.13s, transform 0.13s;
}
.event-card:hover {
  transform: translateY(-2px) scale(1.01);
  box-shadow: 0 8px 36px rgba(2,136,209,0.13);
}
.event-card img { 
  border-top-left-radius: 18px; 
  border-top-right-radius: 18px; 
  max-height: 180px; 
  object-fit: cover; 
}
.card-text {
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
  line-height: 1.2em;
  max-height: 3.6em;
}

/* Buttons */
.btn-primary { background-color: #ff7043; border-color: #ff7043; }
.btn-primary:hover { background-color: #00897b; border-color: #00897b; }

/* Calendar */
#calendar-container {
  background: #ade2e9ff;
  border-radius: 16px;
  box-shadow: 0 2px 14px rgba(0,0,0,0.08);
  padding: 18px;
  max-width: 360px;
  margin: 0 auto;
}
.calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.calendar-header h5 { font-size: 1.2rem; font-weight: 700; color: #00897b; margin: 0; }
.calendar-header button { background-color: #ff7043; color: white; border: none; border-radius: 8px; font-size: 1.1rem; padding: 4px 10px; cursor: pointer; transition: background-color 0.2s ease; }
.calendar-header button:hover { background-color: #e64a19; }
#calendar { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; user-select: none; max-width: 100%; overflow-x: auto; font-size: 1.05rem; }
#calendar table { width: 100%; border-collapse: collapse; table-layout: fixed; }
#calendar th, #calendar td { text-align: center; padding: 12px 6px; word-wrap: break-word; }
#calendar th { color: #00897b; font-size: 1.1rem; font-weight: 700; }
#calendar td { border-radius: 8px; transition: background-color 0.3s ease; }
#calendar td.today { border: 2px solid #ff7043; font-weight: bold; background-color: #fff3e0; }
#calendar td.event { background-color: #ff7043; color: #fff; font-weight: 600; cursor: pointer; text-decoration: underline; }
#calendar td.event:hover { background-color: #e64a19; }

@media (max-width: 991px) {
  .d-flex.flex-column.flex-lg-row.gap-4 { flex-direction: column !important; }
  #calendar-container { width: 100%; padding: 12px; }
  #calendar { width: 100% !important; max-width: none !important; font-size: 1rem; }
  #calendar th, #calendar td { padding: 8px 4px; }
}

/* Chart fixed heights to prevent overly tall charts */
.chart-fixed { height: 220px; max-height: 320px; }
.chart-fixed-sm { height: 160px; max-height: 220px; }
.chart-fixed .chart-canvas { height: 100% !important; width: 100% !important; }

@media (max-width: 767px) {
    .chart-fixed { height: 180px; }
    .chart-fixed-sm { height: 140px; }
}

/* smaller stacked charts inside right column */
.small-chart { height: 110px; max-height: 140px; }
.small-chart .chart-canvas { height: 100% !important; width: 100% !important; }

@media (max-width: 767px) {
    .small-chart { height: 90px; }
}
</style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="main-content">

<!-- Dashboard Cards -->
<div class="dashboard-cards row g-4 mb-4">
    <div class="col-md-3">
        <a href="management/memberlist.php" class="text-decoration-none">
            <div class="card dashboard-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="icon-box bg-primary"><i class="bi bi-people"></i></span>
                    <div>
                        <div class="card-title">Total Members</div>
                        <div class="card-text fs-4 fw-semibold"><?php echo $members; ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3">
        <a href="management/officerslist.php" class="text-decoration-none">
            <div class="card dashboard-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="icon-box bg-success"><i class="bi bi-person-badge"></i></span>
                    <div>
                        <div class="card-title">Total Officers</div>
                        <div class="card-text fs-4 fw-semibold"><?php echo $officers; ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3">
        <a href="event.php" class="text-decoration-none">
            <div class="card dashboard-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="icon-box bg-warning"><i class="bi bi-calendar-event"></i></span>
                    <div>
                        <div class="card-title">Upcoming Events</div>
                        <div class="card-text fs-4 fw-semibold"><?php echo $eventsUpcoming; ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-3">
        <a href="announcement/admin_announcement.php" class="text-decoration-none">
            <div class="card dashboard-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="icon-box bg-danger"><i class="bi bi-megaphone"></i></span>
                    <div>
                        <div class="card-title">Announcements</div>
                        <div class="card-text fs-4 fw-semibold"><?php echo $announcements; ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Member Analytics Panel -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card event-card p-3">
            <div class="card-body">
                <h5 class="mb-3">Member Analytics (Last 6 months)</h5>
                <div class="row gy-3">
                    <div class="col-lg-6">
                        <div class="card p-2 h-100">
                            <div class="card-body">
                                <h6 class="card-title">Monthly New Members</h6>
                                <div class="chart-fixed">
                                    <canvas id="monthlyNewMembersChart" class="chart-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                  
                    <div class="col-lg-4 mt-3 d-lg-none"></div>
                    <div class="col-12 col-lg-4 mt-3">
                        <div class="card p-2 h-100 text-center">
                            <div class="card-body">
                                <h6 class="card-title">Event Analytics</h6>
                                <div class="small-chart mb-2">
                                    <label class="small d-block text-muted">Events / Month</label>
                                    <canvas id="eventsPerMonthChart" class="chart-canvas"></canvas>
                                </div>
                                <div class="small-chart mb-2">
                                    <label class="small d-block text-muted">Types of Events</label>
                                    <canvas id="eventTypesChart" class="chart-canvas"></canvas>
                                </div>
                                <div class="small-chart">
                                    <label class="small d-block text-muted">March Event (% Achievement)</label>
                                    <canvas id="marchEventChart" class="chart-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Events Section -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold mb-0">Upcoming Events</h3>
    <?php if ($role === 'admin'): ?>
        <a href="event.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Event
        </a>
    <?php endif; ?>
</div>

<div class="d-flex flex-column flex-lg-row gap-4">

    <!-- Event Cards List -->
    <div class="flex-grow-1">
        <?php if ($eventResult && $eventResult->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                <?php
                $eventResult->data_seek(0);
                while ($row = $eventResult->fetch_assoc()):
                    $poster = !empty($row['event_poster']) ? $row['event_poster'] : 'default.jpg';
                ?>
                <div class="col">
                    <div class="card event-card h-100">
                        <img src="../uploads/<?php echo htmlspecialchars($poster); ?>" class="card-img-top" alt="Event Poster">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['event_name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                            <div class="event-meta mb-2 text-muted">
                                <i class="bi bi-calendar"></i> <?php echo $row['date']; ?> |
                                <i class="bi bi-clock"></i> <?php echo $row['time']; ?> |
                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($row['location']); ?>
                            </div>
                            <a href="event.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-info btn-sm" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">No upcoming events scheduled.</div>
        <?php endif; ?>
    </div>

    <!-- Sidebar Calendar -->
    <div id="calendar-container">
        <div class="calendar-header">
            <button id="prev-month">&lt;</button>
            <h5 id="month-year"></h5>
            <button id="next-month">&gt;</button>
        </div>
        <div id="calendar"></div>
    </div>

</div>
</div>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventDatesLinks = <?php echo json_encode($eventDatesLinks); ?>;
    const calendarEl = document.getElementById('calendar');
    const monthYearEl = document.getElementById('month-year');
    const prevBtn = document.getElementById('prev-month');
    const nextBtn = document.getElementById('next-month');

    const dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    let currentDate = new Date();

    function formatDate(d) { return d.toISOString().split('T')[0]; }

    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const today = new Date();

        monthYearEl.textContent = `${monthNames[month]} ${year}`;

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const firstWeekDay = firstDay.getDay();
        const daysInMonth = lastDay.getDate();

        let html = `<table class="table table-borderless mb-0">
            <thead>
                <tr>${dayNames.map(day => `<th>${day}</th>`).join('')}</tr>
            </thead>
            <tbody><tr>`;

        for (let i = 0; i < firstWeekDay; i++) html += '<td></td>';

        for (let d = 1; d <= daysInMonth; d++) {
            const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            let classes = [];
            let title = '';
            let clickable = false;

            if (dateStr === formatDate(today)) classes.push('today');
            if (dateStr in eventDatesLinks) { classes.push('event'); title='Click to view event(s)'; clickable=true; }

            html += clickable ?
                `<td class="${classes.join(' ')}" title="${title}" style="cursor:pointer;" onclick="window.location.href='event.php?id=${eventDatesLinks[dateStr]}'">${d}</td>` :
                `<td class="${classes.join(' ')}" title="${title}">${d}</td>`;

            if ((firstWeekDay + d) % 7 === 0 && d !== daysInMonth) html += '</tr><tr>';
        }

        let trailingCells = 7 - ((firstWeekDay + daysInMonth) % 7);
        if (trailingCells < 7) for (let i=0;i<trailingCells;i++) html += '<td></td>';

        html += '</tr></tbody></table>';
        calendarEl.innerHTML = html;
    }

    prevBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    nextBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    renderCalendar();
});
</script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Monthly new members
        const monthLabels = <?php echo json_encode($memberMonthLabels); ?> || [];
        const monthlyNew = <?php echo json_encode($monthlyNewData); ?> || [];

        const monthlyCtx = document.getElementById('monthlyNewMembersChart');
        if (monthlyCtx) {
            new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'New Members',
                        data: monthlyNew,
                        backgroundColor: 'rgba(54,162,235,0.85)'
                    }]
                },
                options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}} }
            });
        }

        // Active vs Inactive trend
        const activeData = <?php echo json_encode($activeTrendActive); ?> || [];
        const inactiveData = <?php echo json_encode($activeTrendInactive); ?> || [];
        const activeCtx = document.getElementById('activeTrendChart');
        if (activeCtx) {
            new Chart(activeCtx, {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [
                        { label: 'Active', data: activeData, borderColor: 'rgba(40,167,69,0.9)', backgroundColor: 'rgba(40,167,69,0.15)', tension:0.3, fill:true },
                        { label: 'Inactive', data: inactiveData, borderColor: 'rgba(220,53,69,0.9)', backgroundColor: 'rgba(220,53,69,0.12)', tension:0.3, fill:true }
                    ]
                },
                options: { responsive:true, maintainAspectRatio:false }
            });
        }

        // Events per month (bar)
        const eventMonthLabels = <?php echo json_encode($eventMonthLabels); ?> || [];
        const eventMonthData = <?php echo json_encode($eventMonthData); ?> || [];
        const eventsPerMonthCtx = document.getElementById('eventsPerMonthChart');
        if (eventsPerMonthCtx) {
            new Chart(eventsPerMonthCtx, {
                type: 'bar',
                data: { labels: eventMonthLabels, datasets:[{ label:'Events', data: eventMonthData, backgroundColor: '#2cd1e7' }] },
                options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}} }
            });
        }

        // Types of events (bar) if available
        const evtTypesLabels = <?php echo json_encode($eventTypesLabels); ?> || [];
        const evtTypesData = <?php echo json_encode($eventTypesData); ?> || [];
        const evtTypesCtx = document.getElementById('eventTypesChart');
        if (evtTypesCtx) {
            new Chart(evtTypesCtx, {
                type: 'bar',
                data: { labels: evtTypesLabels, datasets:[{ label:'Count', data: evtTypesData, backgroundColor: '#FF7043' }] },
                options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{x:{ticks:{autoSkip:false}}} }
            });
        }

        // March Event percent bar (highlight)
        const marchPercent = <?php echo json_encode($marchEventPercent); ?> || 0;
        const marchCtx = document.getElementById('marchEventChart');
        if (marchCtx) {
            const ctx = marchCtx.getContext('2d');
            // create gradient using theme colors
            const grad = ctx.createLinearGradient(0,0, marchCtx.width, 0);
            grad.addColorStop(0, '#2cd1e7');
            grad.addColorStop(1, '#FF7043');
            new Chart(marchCtx, {
                type: 'bar',
                data: { labels: ['March'], datasets:[{ label: 'Achievement %', data: [marchPercent], backgroundColor: grad }] },
                options: { indexAxis: 'y', responsive:true, maintainAspectRatio:false, scales:{x:{max:100, ticks:{callback: v => v + '%'}}}, plugins:{legend:{display:false}} }
            });
        }

    } catch (err) { console.error('Member charts error', err); }
});
</script>

</body>
</html>
