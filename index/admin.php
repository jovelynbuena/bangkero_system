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

$eventSql = "SELECT * FROM events WHERE is_archived = 0 ORDER BY date ASC";
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
    while ($c = $colsRes->fetch_assoc()) {
        $memberCols[] = $c['Field'];
    }
}


$dateCandidates = ['created_at', 'date_added', 'date_registered', 'created', 'date_joined', 'reg_date', 'registered_at', 'date', 'added_on'];
$dateCol = null;
foreach ($dateCandidates as $c) {
    if (in_array($c, $memberCols)) {
        $dateCol = $c;
        break;
    }
}

$statusCandidates = [
    'membership_status',
    'status',
    'is_active',
    'active',
    'member_status',
    'status_id'
];

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
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/list@6.1.10/index.global.min.css" rel="stylesheet">
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
        background: #FFFFFF !important;
    }

    .fc-theme-standard {
        border-color: #E0E0E0;
        background: #FFFFFF !important;
    }

    /* Main calendar container - Pure White */
    #calendar {
        background: #FFFFFF !important;
    }

    .fc-view-harness {
        background: #FFFFFF !important;
    }

    /* Hide default FullCalendar toolbar */
    .fc-toolbar {
        display: none !important;
    }

    /* Custom Calendar Header */
    .calendar-custom-header {
        border-bottom: 1px solid #E0E0E0;
    }

    .calendar-custom-header h3 {
        line-height: 1.2;
    }

    .calendar-nav-btn {
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }

    .calendar-nav-btn:hover {
        background-color: #f8f9fa;
        border-color: #adb5bd;
    }

    /* Ensure consistent button heights */
    .calendar-custom-header .btn,
    .calendar-custom-header .form-select {
        font-size: 0.875rem;
    }

    /* View buttons group */
    .calendar-custom-header .btn-group .btn {
        border-radius: 0;
    }

    .calendar-custom-header .btn-group .btn:first-child {
        border-top-left-radius: 4px;
        border-bottom-left-radius: 4px;
    }

    .calendar-custom-header .btn-group .btn:last-child {
        border-top-right-radius: 4px;
        border-bottom-right-radius: 4px;
    }

    /* Active state for toggle buttons */
    #toggle24h.active,
    #calendarFilter.active {
        background-color: #5B6B7A !important;
        border-color: #5B6B7A !important;
        color: white !important;
    }

    /* Filter dropdown styles */
    .filter-dropdown {
        font-size: 0.875rem;
        background: #FFFFFF;
        border: 1px solid rgba(17, 24, 39, 0.12);
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(17, 24, 39, 0.12);
        padding: 14px;
        width: 280px;
        z-index: 1000;
    }

    .filter-dropdown .form-check {
        margin-bottom: 8px;
    }

    .filter-dropdown .form-check-label {
        cursor: pointer;
        user-select: none;
    }

    .filter-dropdown .form-check-input {
        cursor: pointer;
    }

    .filter-dropdown .btn-link {
        color: #5B6B7A;
    }

    .filter-dropdown .btn-link:hover {
        color: #3E4A54;
    }

    .calendar-custom-header .btn:focus,
    .calendar-custom-header .form-select:focus {
        box-shadow: 0 0 0 0.2rem rgba(91, 107, 122, 0.18);
        border-color: rgba(91, 107, 122, 0.55);
    }

    .calendar-view-btn.active {
        background-color: #5B6B7A !important;
        border-color: #5B6B7A !important;
        color: white !important;
    }

    /* Calendar Container - Pure White */
    .fc-view-harness,
    .fc-scroller,
    .fc-scroller-liquid-absolute {
        background: #FFFFFF !important;
    }

    .fc-col-header-cell {
        background: #FFFFFF !important;
        border-color: #E0E0E0;
        color: #2C3E50;
        font-weight: 600;
        padding: 12px 0 !important;
        font-size: 0.9rem;
        text-transform: uppercase;
    }

    .fc-daygrid-day {
        border-color: #E0E0E0;
        min-height: 100px;
        background: #FFFFFF !important;
    }

    .fc-daygrid-day-number {
        padding: 8px;
        color: #2C3E50;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .fc-daygrid-day.fc-day-other {
        background-color: #FFFFFF !important;
    }

    .fc-daygrid-day.fc-day-today {
        background-color: #FFFFFF !important;
    }

    /* Calendar table background */
    .fc-daygrid-body,
    .fc-daygrid-body table,
    .fc-col-header,
    .fc-col-header table {
        background: #FFFFFF !important;
    }

    /* Remove any grey backgrounds */
    .fc-theme-standard td,
    .fc-theme-standard th {
        border-color: #E0E0E0;
        background: #FFFFFF !important;
    }

    /* Additional white backgrounds for all calendar elements */
    .fc-scrollgrid,
    .fc-scrollgrid-section,
    .fc-scrollgrid-section table,
    .fc-daygrid-body-wrapper,
    .fc-daygrid-body-wrapper table,
    .fc-daygrid-body-wrapper tbody,
    .fc-daygrid-body-wrapper tbody tr,
    .fc-daygrid-body-wrapper tbody td {
        background: #FFFFFF !important;
    }

    /* Ensure no grey hover effects */
    .fc-daygrid-day:hover {
        background: #FFFFFF !important;
    }

    /* Remove any background from calendar wrapper */
    .fc .fc-view-harness-active > .fc-view {
        background: #FFFFFF !important;
    }

    .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        background: #000000;
        color: white;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        margin: 4px;
        font-weight: 700;
    }

    /* Event Styles - Modern Design */
    .fc-daygrid-event {
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        margin: 2px 0;
        cursor: pointer;
    }

    .fc-daygrid-event .fc-event-title-container {
        padding: 0 !important;
    }

    .fc-daygrid-event .fc-event-title {
        display: block;
        background-color: var(--event-bg, #F6E58D);
        color: #2C3E50;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.3;
        text-align: left;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .fc-daygrid-event .fc-event-time {
        display: block;
        font-size: 0.7rem;
        font-weight: 500;
        color: #666;
        margin-top: 2px;
        padding-left: 8px;
    }

    /* Event Colors Based on Type/Category */
    .fc-event[data-event-type="training"] .fc-event-title,
    .fc-event[data-event-type="meeting"] .fc-event-title {
        background-color: #74B9FF !important;
        color: #2C3E50;
    }

    .fc-event[data-event-type="cleanup"] .fc-event-title,
    .fc-event[data-event-type="clean up"] .fc-event-title,
    .fc-event[data-event-type="call"] .fc-event-title {
        background-color: #55EFC4 !important;
        color: #2C3E50;
    }

    .fc-event[data-event-type="festival"] .fc-event-title,
    .fc-event[data-event-type="launch"] .fc-event-title {
        background-color: #FD79A8 !important;
        color: #2C3E50;
    }

    .fc-event[data-event-type="livelihood"] .fc-event-title,
    .fc-event[data-event-type="campaign"] .fc-event-title {
        background-color: #FDCB6E !important;
        color: #2C3E50;
    }

    .fc-event[data-event-type="general"] .fc-event-title,
    .fc-event:not([data-event-type]) .fc-event-title {
        background-color: #F6E58D !important;
        color: #2C3E50;
    }

    /* + More Link */
    .fc-more-link {
        background: #5B6B7A;
        color: #fff !important;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-top: 2px;
        display: inline-block;
    }

    .fc-more-link:hover {
        background: #3E4A54;
    }

    /* Popover */
    .fc-popover {
        border-radius: 8px;
        border: 1px solid #E0E0E0;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }

    .fc-popover-header {
        background: #5B6B7A;
        color: #fff;
        font-weight: 700;
        padding: 10px;
    }

    .fc-daygrid-event-dot {
        display: none !important;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .calendar-custom-header {
            padding: 12px !important;
        }
        
        .calendar-custom-header .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
        }
        
        .calendar-nav-controls {
            margin-top: 12px;
        }
    }

    @media (max-width: 767px) {
        .calendar-custom-header h3 {
            font-size: 1.2rem;
        }
        
        .calendar-view-btn,
        .calendar-nav-btn {
            padding: 2px 6px;
            font-size: 0.8rem;
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
<div class="row mb-4">
    <div class="col-12">
        <div class="card event-card p-0" style="border-radius: 8px; overflow: hidden; background: #FFFFFF !important;">
            <!-- Custom Calendar Header -->
            <div class="calendar-custom-header p-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2" style="background: #FFFFFF !important;">
                <h3 class="fw-bold mb-0" style="color: #2C3E50;">Event Calendar</h3>

                <div class="d-flex flex-wrap align-items-center gap-2 justify-content-end w-100">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary calendar-nav-btn" id="calendarPrev" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary calendar-nav-btn" id="calendarToday" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-calendar"></i>
                        </button>
                        <select class="form-select form-select-sm" id="calendarMonth" style="width: auto; min-width: 110px; height: 32px;">
                            <option value="0">January</option>
                            <option value="1">February</option>
                            <option value="2">March</option>
                            <option value="3">April</option>
                            <option value="4">May</option>
                            <option value="5">June</option>
                            <option value="6">July</option>
                            <option value="7">August</option>
                            <option value="8">September</option>
                            <option value="9">October</option>
                            <option value="10">November</option>
                            <option value="11">December</option>
                        </select>
                        <select class="form-select form-select-sm" id="calendarYear" style="width: auto; min-width: 85px; height: 32px;">
                            <?php 
                            $currentYear = date('Y');
                            for($y = $currentYear - 2; $y <= $currentYear + 5; $y++): 
                            ?>
                            <option value="<?php echo $y; ?>" <?php echo $y == $currentYear ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-secondary calendar-nav-btn" id="calendarNext" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggle24h" style="height: 32px;">24h</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="calendarFilter" style="height: 32px;">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="calendarThisMonth" style="height: 32px;">This Month</button>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-secondary calendar-view-btn" data-view="listWeek" style="height: 32px; width: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-list-ul"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary calendar-view-btn" data-view="timeGridWeek" style="height: 32px; width: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-calendar-week"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary calendar-view-btn active" data-view="dayGridMonth" style="height: 32px; width: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-calendar3"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary calendar-view-btn" data-view="dayGrid" style="height: 32px; width: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-grid-3x3"></i>
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-dark" id="addEventBtn" style="height: 32px; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-calendar-plus"></i> Add Event
                        </button>
                    </div>
                </div>
            </div>
            <div id="calendar"></div>
        </div>
    </div>
</div>
</div>

<!-- Quick Add Event Modal (Calendar) -->
<div class="modal fade" id="quickAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-3">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-dark" id="quickAddTitle">Add Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAddForm" method="POST" action="event.php" enctype="multipart/form-data">
                <input type="hidden" name="event_id" id="quick_event_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Event Name</label>
                            <input type="text" name="event_name" id="quick_event_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="event_category" id="quick_event_category" class="form-select" required>
                                <option value="">-- Select --</option>
                                <option>Training</option>
                                <option>Cleanup</option>
                                <option>Festival</option>
                                <option>Livelihood</option>
                                <option>General</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" name="event_date" id="quick_event_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Time</label>
                            <input type="time" name="event_time" id="quick_event_time" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Location</label>
                            <input type="text" name="event_location" id="quick_event_location" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="event_description" id="quick_event_description" rows="3" class="form-control" required></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Poster (image)</label>
                            <input type="file" name="event_poster" id="quick_event_poster" accept="image/*" class="form-control">
                            <img id="quick_poster_preview" class="mt-2 rounded" style="width:150px;display:none;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary w-100">Save Event</button>
                </div>
            </form>
        </div>
    </div>
 </div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/list@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    // Build events from PHP
    const allEvents = [];
    <?php if ($eventResult && $eventResult->num_rows > 0):
        $eventResult->data_seek(0);
        while ($row = $eventResult->fetch_assoc()):
    ?>
    allEvents.push({
        title: '<?php echo addslashes(htmlspecialchars($row['event_name'])); ?>',
        start: '<?php echo $row['date']; ?>T<?php echo $row['time'] ?: "00:00:00"; ?>',
        allDay: <?php echo ($row['is_all_day'] ?? 0) ? 'true' : 'false'; ?>,
        url: 'event.php?id=<?php echo $row['id']; ?>',
        extendedProps: {
            important: <?php echo ($row['is_important'] ?? 0) ? 'true' : 'false'; ?>,
            category: '<?php echo addslashes($row['category'] ?? $row['event_type'] ?? 'general'); ?>'
        }
    });
    <?php endwhile; endif; ?>

    // UI elements
    const prevBtn = document.getElementById('calendarPrev');
    const nextBtn = document.getElementById('calendarNext');
    const todayBtn = document.getElementById('calendarToday');
    const thisMonthBtn = document.getElementById('calendarThisMonth');
    const monthSelect = document.getElementById('calendarMonth');
    const yearSelect = document.getElementById('calendarYear');
    const toggle24hBtn = document.getElementById('toggle24h');
    const filterBtn = document.getElementById('calendarFilter');
    const viewBtns = Array.from(document.querySelectorAll('.calendar-view-btn'));
    const headerEl = document.querySelector('.calendar-custom-header');
    const quickAddModalEl = document.getElementById('quickAddModal');
    const quickAddForm = document.getElementById('quickAddForm');
    const quickFields = {
        id: document.getElementById('quick_event_id'),
        name: document.getElementById('quick_event_name'),
        category: document.getElementById('quick_event_category'),
        date: document.getElementById('quick_event_date'),
        time: document.getElementById('quick_event_time'),
        location: document.getElementById('quick_event_location'),
        description: document.getElementById('quick_event_description')
    };
    const quickModal = quickAddModalEl ? new bootstrap.Modal(quickAddModalEl) : null;

    // State
    let is24Hour = false;
    let activeCategories = null; // null = show all, Set = selected categories

    function getTimeFormats() {
        const hour12 = !is24Hour;
        return {
            slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12 },
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12 }
        };
    }

    function normalizeCategory(v) {
        return String(v || 'general').trim().toLowerCase();
    }

    function getFilteredEvents() {
        if (!activeCategories || activeCategories.size === 0) return allEvents;
        return allEvents.filter(e => activeCategories.has(normalizeCategory(e.extendedProps?.category)));
    }

    function applyFilters() {
        calendar.removeAllEvents();
        calendar.addEventSource(getFilteredEvents());
    }

    function syncMonthYearControls() {
        if (!monthSelect || !yearSelect) return;
        const d = calendar.getDate();
        monthSelect.value = String(d.getMonth());
        yearSelect.value = String(d.getFullYear());
    }

    function setActiveViewButton(viewName) {
        viewBtns.forEach(b => b.classList.toggle('active', b.getAttribute('data-view') === viewName));
    }

    // Calendar init
    const tf = getTimeFormats();
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: false,
        firstDay: 1,
        height: 'auto',
        dayMaxEventRows: 3,
        moreLinkClick: 'popover',
        events: getFilteredEvents(),
        slotLabelFormat: tf.slotLabelFormat,
        eventTimeFormat: tf.eventTimeFormat,
        eventDisplay: 'block',

        eventContent: function(arg) {
            const title = arg.event.title || '';
            const timeText = arg.timeText || '';
            const isAllDay = arg.event.allDay;
            const timeLine = isAllDay ? (is24Hour ? '00:00 - 23:59' : 'All Day') : (timeText ? timeText : '');
            const timeHtml = timeLine ? `<div class="fc-event-time">${timeLine}</div>` : '';
            return { html: `<div class="fc-event-title">${title}</div>${timeHtml}` };
        },

        eventDidMount: function(info) {
            const cat = normalizeCategory(info.event.extendedProps?.category);
            info.el.setAttribute('data-event-type', cat);
        },

        datesSet: function() {
            syncMonthYearControls();
        },

        eventClick: function(info) {
            if (info.event.url) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.url;
            }
        },

        dateClick: function(arg) {
            if (!quickModal) return;
            // reset form
            quickAddForm?.reset();
            if (quickFields.id) quickFields.id.value = '';
            if (quickFields.date) quickFields.date.value = arg.dateStr;
            if (quickFields.time) quickFields.time.value = '08:00';
            quickModal.show();
        },

        dayHeaderFormat: { weekday: 'short' },
        dayHeaderContent: function(arg) {
            return arg.text.toUpperCase();
        }
    });

    calendar.render();
    syncMonthYearControls();

    // Navigation
    prevBtn?.addEventListener('click', () => calendar.prev());
    nextBtn?.addEventListener('click', () => calendar.next());
    todayBtn?.addEventListener('click', () => calendar.today());
    thisMonthBtn?.addEventListener('click', () => calendar.today());

    // Month/Year jump
    monthSelect?.addEventListener('change', function() {
        const d = calendar.getDate();
        const newDate = new Date(d.getFullYear(), parseInt(this.value, 10), 1);
        calendar.gotoDate(newDate);
    });

    yearSelect?.addEventListener('change', function() {
        const d = calendar.getDate();
        const newDate = new Date(parseInt(this.value, 10), d.getMonth(), 1);
        calendar.gotoDate(newDate);
    });

    // Views
    const viewMap = {
        listWeek: 'listWeek',
        timeGridWeek: 'timeGridWeek',
        dayGridMonth: 'dayGridMonth',
        dayGrid: 'dayGridWeek'
    };

    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const key = this.getAttribute('data-view');
            const viewName = viewMap[key] || 'dayGridMonth';
            calendar.changeView(viewName);
            setActiveViewButton(key);
        });
    });

    // 24h toggle (affects timeGrid + event time labels)
    toggle24hBtn?.addEventListener('click', function() {
        is24Hour = !is24Hour;
        this.classList.toggle('active', is24Hour);
        this.textContent = is24Hour ? '12h' : '24h';

        const nextFormats = getTimeFormats();
        calendar.setOption('slotLabelFormat', nextFormats.slotLabelFormat);
        calendar.setOption('eventTimeFormat', nextFormats.eventTimeFormat);

        // force re-render of events timeText
        calendar.rerenderEvents();
    });

    // Filter popover (stable, no leaks)
    let popoverEl = null;
    let onDocClick = null;

    function closeFilter() {
        if (!popoverEl) return;
        popoverEl.remove();
        popoverEl = null;
        filterBtn?.classList.remove('active');
        if (onDocClick) {
            document.removeEventListener('click', onDocClick, true);
            onDocClick = null;
        }
    }

    function openFilter() {
        if (!filterBtn) return;
        if (popoverEl) return;

        if (headerEl) headerEl.style.position = 'relative';

        const categories = Array.from(
            new Set(allEvents.map(e => normalizeCategory(e.extendedProps?.category)))
        ).sort();

        popoverEl = document.createElement('div');
        popoverEl.className = 'filter-dropdown';
        popoverEl.setAttribute('role', 'dialog');
        popoverEl.style.position = 'absolute';
        popoverEl.style.top = '52px';
        popoverEl.style.right = '16px';
        popoverEl.style.left = 'auto';

        const selected = activeCategories ? new Set(activeCategories) : new Set();
        const showAllChecked = !activeCategories || activeCategories.size === 0;

        popoverEl.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-bold">Filter Events</div>
                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" id="closeFilterBtn">Close</button>
            </div>
            <div class="mb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="filterAll" ${showAllChecked ? 'checked' : ''}>
                    <label class="form-check-label" for="filterAll">Show All</label>
                </div>
            </div>
            <div class="mb-3" style="max-height: 220px; overflow:auto;">
                <div class="small text-muted mb-2">Categories</div>
                ${categories.map(cat => {
                    const id = `filter-${cat.replace(/[^a-z0-9]+/g,'-')}`;
                    const checked = selected.has(cat) ? 'checked' : '';
                    const label = cat.charAt(0).toUpperCase() + cat.slice(1);
                    return `
                        <div class="form-check">
                            <input class="form-check-input filter-category" type="checkbox" value="${cat}" id="${id}" ${checked}>
                            <label class="form-check-label" for="${id}">${label}</label>
                        </div>
                    `;
                }).join('')}
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary flex-grow-1" id="applyFilter">Apply</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearFilter">Clear</button>
            </div>
        `;

        (headerEl || document.body).appendChild(popoverEl);
        filterBtn.classList.add('active');

        popoverEl.querySelector('#closeFilterBtn')?.addEventListener('click', closeFilter);

        const filterAllCb = popoverEl.querySelector('#filterAll');
        filterAllCb?.addEventListener('change', function() {
            if (this.checked) {
                popoverEl.querySelectorAll('.filter-category').forEach(cb => { cb.checked = false; });
            }
        });

        popoverEl.querySelectorAll('.filter-category').forEach(cb => {
            cb.addEventListener('change', function() {
                if (this.checked && filterAllCb) filterAllCb.checked = false;
            });
        });

        popoverEl.querySelector('#applyFilter')?.addEventListener('click', function() {
            const showAll = !!filterAllCb?.checked;
            if (showAll) {
                activeCategories = null;
            } else {
                const picked = Array.from(popoverEl.querySelectorAll('.filter-category:checked')).map(x => x.value);
                activeCategories = new Set(picked.map(normalizeCategory));
                if (activeCategories.size === 0) activeCategories = null;
            }
            applyFilters();
            closeFilter();
        });

        popoverEl.querySelector('#clearFilter')?.addEventListener('click', function() {
            activeCategories = null;
            applyFilters();
            closeFilter();
        });

        // close when clicking outside
        onDocClick = function(e) {
            if (!popoverEl) return;
            if (popoverEl.contains(e.target) || filterBtn.contains(e.target)) return;
            closeFilter();
        };
        document.addEventListener('click', onDocClick, true);
    }

    filterBtn?.addEventListener('click', function() {
        if (popoverEl) closeFilter();
        else openFilter();
    });

    // Add Event Button - Open Modal
    const addEventBtn = document.getElementById('addEventBtn');
    addEventBtn?.addEventListener('click', function() {
        if (!quickModal) return;
        // Reset form
        quickAddForm?.reset();
        quickFields.id.value = '';
        quickFields.date.value = '';
        quickFields.time.value = '08:00';
        document.getElementById('quickAddTitle').textContent = 'Add Event';
        const previewImg = document.getElementById('quick_poster_preview');
        if (previewImg) previewImg.style.display = 'none';
        quickModal.show();
    });

    // Poster preview for quick add modal
    const quickPosterInput = document.getElementById('quick_event_poster');
    quickPosterInput?.addEventListener('change', function() {
        const file = this.files[0];
        const previewImg = document.getElementById('quick_poster_preview');
        if (file && previewImg) {
            previewImg.src = URL.createObjectURL(file);
            previewImg.style.display = 'block';
        } else if (previewImg) {
            previewImg.style.display = 'none';
        }
    });
});




</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

Chart.defaults.color = '#4A4A4A';
Chart.defaults.font.family = "'Segoe UI', sans-serif";
Chart.defaults.font.size = 11;

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
                                backgroundColor: 'rgba(91, 107, 122, 0.6)',
                                borderColor: '#5B6B7A',
                                borderWidth: 1,
                                borderRadius: 6,
                                hoverBackgroundColor: 'rgba(91, 107, 122, 0.85)'
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
                        backgroundColor: 'rgba(127, 140, 141, 0.7)',
                        borderRadius: 4

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
                                backgroundColor: [
                                    'rgba(91,107,122,0.85)',
                                    'rgba(91,107,122,0.6)',
                                    'rgba(91,107,122,0.4)'
                                ]
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
                       backgroundColor: 'rgba(108, 140, 122, 0.8)',
                        borderRadius: 10

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
