<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('location: login.php');
    exit;
}

include('../config/db_connect.php');

$role = $_SESSION['role'] ?? 'member';
$username = $_SESSION['username'] ?? '';
$fullname = $_SESSION['fullname'] ?? ucfirst($role);

$eventSql = "SELECT * FROM events WHERE is_archived = 0 AND date >= CURDATE() ORDER BY date ASC";
$eventResult = $conn->query($eventSql);
if (!$eventResult) die("Query failed: " . $conn->error);



$membersRow = $conn->query("SELECT COUNT(*) AS total FROM members")->fetch_assoc();
$members = $membersRow ? (int)$membersRow['total'] : 0;

$officersStats = $conn->query("SELECT COUNT(DISTINCT member_id) AS distinct_members, COUNT(*) AS assignments FROM officers")->fetch_assoc();
$officersActive = $officersStats ? (int)$officersStats['distinct_members'] : 0;
$officersAssignments = $officersStats ? (int)$officersStats['assignments'] : 0;

$officersArchivedRow = $conn->query("SELECT COUNT(DISTINCT member_id) AS distinct_members FROM officers_archive")->fetch_assoc();
$officersArchived = $officersArchivedRow ? (int)$officersArchivedRow['distinct_members'] : 0;

$officers = $officersActive;

$announcementsRow = $conn->query("SELECT COUNT(*) AS total FROM announcements")->fetch_assoc();
$announcements = $announcementsRow ? (int)$announcementsRow['total'] : 0;

$eventsTotalRow = $conn->query("SELECT COUNT(*) AS total FROM events")->fetch_assoc();
$eventsTotal = $eventsTotalRow ? (int)$eventsTotalRow['total'] : 0;

$eventsUpcomingRow = $conn->query("SELECT COUNT(*) AS total FROM events WHERE is_archived=0 AND date >= CURDATE()")->fetch_assoc();
$eventsUpcoming = $eventsUpcomingRow ? (int)$eventsUpcomingRow['total'] : 0;

$memberCols = [];
$colsRes = $conn->query("SHOW COLUMNS FROM members");
if ($colsRes) {
    while ($c = $colsRes->fetch_assoc()) $memberCongels[] = $c['Field'];
}

$dateCandidates = ['created_at', 'date_added', 'date_registered', 'created', 'date_joined', 'reg_date', 'registered_at', 'date', 'added_on'];
$dateCol = null;
foreach ($dateCandidates as $c) {
    if (in_array($c, $memberCols)) {
        $dateCol = $c;
        break;
    }
}

$statusCandidates = ['status', 'is_active', 'active', 'member_status', 'status_id'];
$statusCol = null;
foreach ($statusCandidates as $c) {
    if (in_array($c, $memberCols)) {
        $statusCol = $c;
        break;
    }
}

$typeCandidates = ['member_type', 'type', 'role', 'category', 'member_category'];
$typeCol = null;
foreach ($typeCandidates as $c) {
    if (in_array($c, $memberCols)) {
        $typeCol = $c;
        break;
    }
}

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
    $sql = "SELECT DATE_FORMAT($dateCol, '%Y-%m') AS ym, COUNT(*) AS cnt FROM members WHERE $dateCol >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) GROUP BY ym ORDER BY ym";
    $res = $conn->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $idx = array_search(date('M Y', strtotime($r['ym'] . '-01')), $memberMonthLabels);
            if ($idx !== false) $monthlyNewData[$idx] = (int)$r['cnt'];
        }
    }

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
                if (is_numeric($st)) {
                    $isActive = intval($st) === 1;
                } else {
                    $ls = strtolower(trim((string)$st));
                    $isActive = in_array($ls, ['active', 'a', 'yes', 'y', 'true', '1']);
                }
                if ($isActive) {
                    $activeTrendActive[$idx] = (int)$activeTrendActive[$idx] + (int)$val;
                } else {
                    $activeTrendInactive[$idx] = (int)$activeTrendInactive[$idx] + (int)$val;
                }
            }
        }
    }
}

if (!$dateCol && $statusCol) {
    $overall = $conn->query("SELECT $statusCol AS st, COUNT(*) FROM members GROUP BY st");
    if ($overall) {
        while ($r = $overall->fetch_assoc()) {
            $st = $r['st'];
            $cnt = (int)$r['cnt'];
            $isActive = false;
            if (is_numeric($st)) {
                $isActive = intval($st) === 1;
            } else {
                $ls = strtolower(trim((string)$st));
                $isActive = in_array($ls, ['active', 'a', 'yes', 'y', 'true', '1']);
            }
            if ($isActive) {
                $activeTrendActive = [$cnt, 0, 0, 0, 0, 0];
            } else {
                $activeTrendInactive = [$cnt, 0, 0, 0, 0, 0];
            }
        }
    }
}

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

$eventCols = [];
$ecRes = $conn->query("SHOW COLUMNS FROM events");
if ($ecRes) {
    while ($r = $ecRes->fetch_assoc()) $eventCols[] = $r['Field'];
}

$eventDateCandidates = ['date', 'event_date', 'start_date', 'event_start', 'created_at'];
$eventDateCol = null;
foreach ($eventDateCandidates as $c) {
    if (in_array($c, $eventCols)) {
        $eventDateCol = $c;
        break;
    }
}

$eventTypeCandidates = ['category', 'event_type', 'type', 'event_category'];
$eventTypeCol = null;
foreach ($eventTypeCandidates as $c) {
    if (in_array($c, $eventCols)) {
        $eventTypeCol = $c;
        break;
    }
}

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

$marchEventPercent = 82;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo ucfirst($role); ?> Dashboard | Bangkero & Fishermen Association</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/index.global.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/index.global.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.10/index.global.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: #f9f9f9;
    margin: 0;
}

.main-content {
    margin-left: 250px;
    padding: 32px;
    min-height: 100vh;
    box-sizing: border-box;
    overflow-x: hidden;
}

.dashboard-card {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    border: 1px solid #E0E0E0;
    background: #FFFFFF;
}

.dashboard-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.icon-box {
    border-radius: 8px;
    background: #5B6B7A;
    color: #fff;
    padding: 14px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 2rem;
}

.event-card {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #E0E0E0;
    background: #FFFFFF;
    color: #2C3E50;
    transition: box-shadow 0.2s, transform 0.2s;
}

.event-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
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

.btn-primary {
    background-color: #5B6B7A;
    border-color: #5B6B7A;
}

.btn-primary:hover {
    background-color: #3E4A54;
    border-color: #3E4A54;
}

#calendar-container {
    display: none;
}

.calendar-header {
    display: none;
}

.chart-fixed {
    height: 220px;
    max-height: 320px;
}

.chart-fixed-sm {
    height: 160px;
    max-height: 220px;
}

.chart-fixed .chart-canvas {
    height: 100% !important;
    width: 100% !important;
}

.small-chart {
    height: 100px;
    max-height: 130px;
    margin-bottom: 16px;
    padding: 8px 0;
}

.small-chart .chart-canvas {
    height: 100% !important;
    width: 100% !important;
}

    /* FullCalendar customization */
    .fc {
        font-family: 'Segoe UI', sans-serif;
    }

    .fc-theme-standard {
        border-color: #E0E0E0;
    }

    .fc .fc-button-primary {
        background-color: #5B6B7A !important;
        border-color: #5B6B7A !important;
        font-weight: 500;
    }

    .fc .fc-button-primary:hover {
        background-color: #3E4A54 !important;
        border-color: #3E4A54 !important;
    }

    .fc .fc-button-primary.fc-button-active {
        background-color: #3E4A54 !important;
        border-color: #3E4A54 !important;
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active:focus {
        box-shadow: 0 0 0 0.2rem rgba(91, 107, 122, 0.5);
    }

    .fc-toolbar {
        flex-wrap: wrap;
        gap: 12px;
        padding: 16px;
        background: #f9f9f9;
        border-bottom: 1px solid #E0E0E0;
        border-radius: 8px 8px 0 0;
        align-items: center;
    }

    .fc-toolbar-title {
        font-size: 1.8rem !important;
        font-weight: 700 !important;
        color: #2C3E50;
        margin: 0 !important;
    }

    .fc-button-group {
        gap: 6px;
    }

    .fc .fc-button-primary {
        background-color: #5B6B7A !important;
        border-color: #5B6B7A !important;
        font-weight: 500;
        padding: 6px 14px !important;
        font-size: 0.9rem !important;
    }

    .fc .fc-button-primary:hover {
        background-color: #3E4A54 !important;
        border-color: #3E4A54 !important;
    }

    .fc .fc-button-primary.fc-button-active {
        background-color: #3E4A54 !important;
        border-color: #3E4A54 !important;
    }

    .fc-col-header-cell {
        background: #f5f5f5;
        border-color: #E0E0E0;
        color: #2C3E50;
        font-weight: 700;
        padding: 14px 0 !important;
        font-size: 1rem;
        text-transform: capitalize;
    }

    .fc-daygrid-day {
        border-color: #E0E0E0;
    }

    .fc-daygrid-day-number {
        padding: 10px 8px;
        color: #2C3E50;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .fc-daygrid-day.fc-day-other {
        background-color: #fafafa;
    }

    .fc-daygrid-day.fc-day-today {
        background-color: #ECF0F1;
    }

    .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        background: #5B6B7A;
        color: white;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .fc-event {
        border: none !important;
        border-radius: 4px;
        padding: 4px 6px !important;
        font-size: 0.75rem;
        margin-bottom: 3px;
        cursor: pointer;
    }

    .fc-event-title {
        font-weight: 700;
        white-space: normal;
        overflow: visible;
        text-overflow: clip;
        display: block;
        line-height: 1.4;
        color: #ffffff;
        word-break: break-word;
    }

    .fc-daygrid-event {
        margin: 3px 2px;
    }

    .fc-daygrid-event-harness {
        margin: 0;
    }

    .fc-daygrid-day-frame {
        position: relative;
        min-height: 180px;
    }

    .fc-daygrid-day-events {
        margin: 6px 2px;
    }

    .fc-daygrid-day-content {
        position: relative;
    }

    .fc-more {
        background: #5B6B7A;
        color: white;
        border-radius: 3px;
        padding: 4px 8px;
        font-size: 0.7rem;
        font-weight: 700;
        cursor: pointer;
        margin: 3px 2px;
        display: inline-block;
    }

    .fc-more:hover {
        background: #3E4A54;
    }

    .fc-popover {
        border: 1px solid #E0E0E0;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        border-radius: 8px;
        background: white;
    }

    .fc-popover-header {
        background: #5B6B7A;
        color: white;
        border-radius: 8px 8px 0 0;
        padding: 12px 16px;
        font-weight: 700;
        border: none;
        font-size: 1rem;
    }

    .fc-popover-body {
        padding: 12px;
    }

    .fc-popover-close {
        color: white;
        font-weight: 700;
        opacity: 0.7;
    }

    .fc-popover-close:hover {
        opacity: 1;
    }
}

@media (max-width: 767px) {
    .chart-fixed {
        height: 180px;
    }

    .chart-fixed-sm {
        height: 140px;
    }

    .small-chart {
        height: 80px;
        margin-bottom: 8px;
    }
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
                            <div class="card-body p-2">
                                <h6 class="card-title mb-3" style="font-size: 0.95rem;">Event Analytics</h6>
                                <div class="small-chart">
                                    <label class="small d-block text-muted mb-1">Events / Month</label>
                                    <canvas id="eventsPerMonthChart" class="chart-canvas"></canvas>
                                </div>
                                <div class="small-chart">
                                    <label class="small d-block text-muted mb-1">Types of Events</label>
                                    <canvas id="eventTypesChart" class="chart-canvas"></canvas>
                                </div>
                                <div class="small-chart">
                                    <label class="small d-block text-muted mb-1">March Event (% Achievement)</label>
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

<!-- Event Calendar Section -->
<h3 class="fw-bold mb-3">Event Calendar</h3>

<div class="row mb-4">
    <div class="col-12">
        <div class="card event-card p-0" style="border-radius: 8px; overflow: hidden;">
            <div id="calendar"></div>
        </div>
    </div>
</div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');

    const events = [];
    <?php 
    if ($eventResult && $eventResult->num_rows > 0) {
        $eventResult->data_seek(0);
        while ($row = $eventResult->fetch_assoc()): 
    ?>
    events.push({
        title: '<?php echo addslashes(htmlspecialchars($row['event_name'])); ?>',
        start: '<?php echo $row['date']; ?>T<?php echo $row['time']; ?>',
        allDay: <?php echo ($row['is_all_day'] ?? 0) ? 'true' : 'false'; ?>,
        url: 'event.php?id=<?php echo $row['id']; ?>',
        description: '<?php echo addslashes(htmlspecialchars($row['description'] ?? '')); ?>',
        location: '<?php echo addslashes(htmlspecialchars($row['location'] ?? '')); ?>',
        color: '<?php
            $eventType = $row['event_type'] ?? 'default';
            switch ($eventType) {
                case 'Meeting': echo '#9AD0EC'; break;
                case 'Workshop': echo '#D5A6BD'; break;
                case 'Call': echo '#A9DFBF'; break;
                case 'Launch': echo '#F5B7B1'; break;
                case 'Campaign': echo '#F7DC6F'; break;
                default: echo '#95A5A6'; 
            }
        ?>'
    });
    <?php 
        endwhile; 
    }
    ?>

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today currentTimeIndicator',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay addEventButton'
        },
        customButtons: {
            addEventButton: {
                text: 'Add Event',
                click: function() {
                    window.location.href = 'event.php';
                }
            },
            currentTimeIndicator: {
                text: '24h',
                click: function() {
                    console.log('24h view');
                }
            }
        },
        events: events,
        dayMaxEvents: true,
        eventDisplay: 'auto',
        slotLabelInterval: '00:15:00',
        eventClick: function(info) {
            if (info.event.url) {
                window.location.href = info.event.url;
            }
        },
        eventDidMount: function(info) {
            let tooltipContent = info.event.title;
            if (info.event.start) {
                const startTime = new Date(info.event.start).toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit'});
                tooltipContent += '<br><small><strong>Time:</strong> ' + startTime + '</small>';
            }
            if (info.event.extendedProps && info.event.extendedProps.location) {
                tooltipContent += '<br><small><strong>Location:</strong> ' + info.event.extendedProps.location + '</small>';
            }
            const tooltip = new bootstrap.Tooltip(info.el, {
                title: tooltipContent,
                html: true,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
            info.el.style.cursor = 'pointer';
        },
        height: 'auto',
        contentHeight: 'auto',
        editable: false,
        selectable: false,
        slotMinTime: '00:00:00',
        slotMaxTime: '24:00:00'
    });
    calendar.render();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
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
                        backgroundColor: '#7F8C8D'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        const activeData = <?php echo json_encode($activeTrendActive); ?> || [];
        const inactiveData = <?php echo json_encode($activeTrendInactive); ?> || [];
        const activeCtx = document.getElementById('activeTrendChart');
        if (activeCtx) {
            new Chart(activeCtx, {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [
                        {
                            label: 'Active',
                            data: activeData,
                            borderColor: '#5B6B7A',
                            backgroundColor: 'rgba(91, 107, 122, 0.1)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Inactive',
                            data: inactiveData,
                            borderColor: '#95A5A6',
                            backgroundColor: 'rgba(149, 165, 166, 0.1)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        const eventMonthLabels = <?php echo json_encode($eventMonthLabels); ?> || [];
        const eventMonthData = <?php echo json_encode($eventMonthData); ?> || [];
        const eventsPerMonthCtx = document.getElementById('eventsPerMonthChart');
        if (eventsPerMonthCtx) {
            new Chart(eventsPerMonthCtx, {
                type: 'bar',
                data: {
                    labels: eventMonthLabels.map(l => l.replace(' ', '\n')),
                    datasets: [{
                        label: 'Events',
                        data: eventMonthData,
                        backgroundColor: '#7F8C8D'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: false,
                                font: {
                                    size: 9
                                },
                                padding: 4
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    size: 9
                                }
                            }
                        }
                    }
                }
            });
        }

        const evtTypesLabels = <?php echo json_encode($eventTypesLabels); ?> || [];
        const evtTypesData = <?php echo json_encode($eventTypesData); ?> || [];
        const evtTypesCtx = document.getElementById('eventTypesChart');
        if (evtTypesCtx) {
            new Chart(evtTypesCtx, {
                type: 'bar',
                data: {
                    labels: evtTypesLabels,
                    datasets: [{
                        label: 'Count',
                        data: evtTypesData,
                        backgroundColor: '#95A5A6'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                font: {
                                    size: 10
                                },
                                padding: 6
                            }
                        },
                        x: {
                            ticks: {
                                autoSkip: true,
                                font: {
                                    size: 8
                                }
                            }
                        }
                    }
                }
            });
        }

        const marchPercent = <?php echo json_encode($marchEventPercent); ?> || 0;
        const marchCtx = document.getElementById('marchEventChart');
        if (marchCtx) {
            new Chart(marchCtx, {
                type: 'bar',
                data: {
                    labels: ['March'],
                    datasets: [{
                        label: 'Achievement %',
                        data: [marchPercent],
                        backgroundColor: '#5B6B7A'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            max: 100,
                            ticks: {
                                callback: v => v + '%',
                                font: {
                                    size: 8
                                },
                                stepSize: 25
                            }
                        },
                        y: {
                            ticks: {
                                font: {
                                    size: 10
                                },
                                padding: 8
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    } catch (err) {
        console.error('Member charts error', err);
    }
});
</script>

</body>
</html>
