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

// Fetch upcoming events
$eventSql = "SELECT * FROM events WHERE date >= CURDATE() ORDER BY date ASC";
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
$members = $membersRow ? $membersRow['total'] : 0;

$officersRow = $conn->query("SELECT COUNT(*) AS total FROM officers")->fetch_assoc();
$officers = $officersRow ? $officersRow['total'] : 0;

$announcementsRow = $conn->query("SELECT COUNT(*) AS total FROM announcements")->fetch_assoc();
$announcements = $announcementsRow ? $announcementsRow['total'] : 0;

$eventsRow = $conn->query("SELECT COUNT(*) AS total FROM events WHERE date >= CURDATE()")->fetch_assoc();
$events = $eventsRow ? $eventsRow['total'] : 0;
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
                        <div class="card-text fs-4 fw-semibold"><?php echo $events; ?></div>
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

</body>
</html>
