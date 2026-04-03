<?php
session_start();

// Clear caches for development
clearstatcache();
if (function_exists('opcache_invalidate')) {
    opcache_invalidate(__FILE__, true);
}

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

// ===== TREND INDICATORS (Last Month Comparison) =====
$lastMonthStart = date('Y-m-01', strtotime('-1 month'));
$lastMonthEnd = date('Y-m-t', strtotime('-1 month'));
$thisMonthStart = date('Y-m-01');

// Check if members table has date column
$dateCol = null;
$columnsCheck = $conn->query("SHOW COLUMNS FROM members LIKE 'created_at'");
if ($columnsCheck && $columnsCheck->num_rows > 0) {
    $dateCol = 'created_at';
} else {
    $columnsCheck = $conn->query("SHOW COLUMNS FROM members LIKE 'date_registered'");
    if ($columnsCheck && $columnsCheck->num_rows > 0) {
        $dateCol = 'date_registered';
    }
}

// Members trend
$membersTrend = ['change' => 0, 'direction' => 'same', 'percent' => 0];
if ($dateCol) {
    $lastMonthMembers = $conn->query("SELECT COUNT(*) AS total FROM members WHERE $dateCol >= '$lastMonthStart' AND $dateCol <= '$lastMonthEnd'")->fetch_assoc();
    $thisMonthMembers = $conn->query("SELECT COUNT(*) AS total FROM members WHERE $dateCol >= '$thisMonthStart'")->fetch_assoc();
    $lastCount = $lastMonthMembers ? (int)$lastMonthMembers['total'] : 0;
    $thisCount = $thisMonthMembers ? (int)$thisMonthMembers['total'] : 0;
    $membersTrend['change'] = $thisCount - $lastCount;
    $membersTrend['direction'] = $membersTrend['change'] > 0 ? 'up' : ($membersTrend['change'] < 0 ? 'down' : 'same');
    $membersTrend['percent'] = $lastCount > 0 ? round(($membersTrend['change'] / $lastCount) * 100, 1) : 0;
}

// Officers trend (using created_at if exists in officers table)
$officersTrend = ['change' => 0, 'direction' => '', 'percent' => 0];
$officerCols = [];
$offColsRes = $conn->query("SHOW COLUMNS FROM officers");
if ($offColsRes) {
    while ($c = $offColsRes->fetch_assoc()) $officerCols[] = $c['Field'];
}
$officerDateCol = null;
foreach (['created_at', 'date_added', 'assigned_date'] as $c) {
    if (in_array($c, $officerCols)) {
        $officerDateCol = $c;
        break;
    }
}
if ($officerDateCol) {
    $lastMonthOfficers = $conn->query("SELECT COUNT(DISTINCT member_id) AS total FROM officers WHERE $officerDateCol >= '$lastMonthStart' AND $officerDateCol <= '$lastMonthEnd'")->fetch_assoc();
    $thisMonthOfficers = $conn->query("SELECT COUNT(DISTINCT member_id) AS total FROM officers WHERE $officerDateCol >= '$thisMonthStart'")->fetch_assoc();
    $lastCount = $lastMonthOfficers ? (int)$lastMonthOfficers['total'] : 0;
    $thisCount = $thisMonthOfficers ? (int)$thisMonthOfficers['total'] : 0;
    $officersTrend['change'] = $thisCount - $lastCount;
    $officersTrend['direction'] = $officersTrend['change'] > 0 ? 'up' : ($officersTrend['change'] < 0 ? 'down' : 'same');
    $officersTrend['percent'] = $lastCount > 0 ? round(($officersTrend['change'] / $lastCount) * 100, 1) : 0;
}

// Events trend
$eventsTrend = ['change' => 0, 'direction' => '', 'percent' => 0];
$lastMonthEvents = $conn->query("SELECT COUNT(*) AS total FROM events WHERE date >= '$lastMonthStart' AND date <= '$lastMonthEnd'")->fetch_assoc();
$thisMonthEvents = $conn->query("SELECT COUNT(*) AS total FROM events WHERE date >= '$thisMonthStart'")->fetch_assoc();
$lastCount = $lastMonthEvents ? (int)$lastMonthEvents['total'] : 0;
$thisCount = $thisMonthEvents ? (int)$thisMonthEvents['total'] : 0;
$eventsTrend['change'] = $thisCount - $lastCount;
$eventsTrend['direction'] = $eventsTrend['change'] > 0 ? 'up' : ($eventsTrend['change'] < 0 ? 'down' : 'same');
$eventsTrend['percent'] = $lastCount > 0 ? round(($eventsTrend['change'] / $lastCount) * 100, 1) : 0;

// Announcements trend
$announcementsTrend = ['change' => 0, 'direction' => '', 'percent' => 0];
$lastMonthAnnouncements = $conn->query("SELECT COUNT(*) AS total FROM announcements WHERE date_posted >= '$lastMonthStart' AND date_posted <= '$lastMonthEnd'")->fetch_assoc();
$thisMonthAnnouncements = $conn->query("SELECT COUNT(*) AS total FROM announcements WHERE date_posted >= '$thisMonthStart'")->fetch_assoc();
$lastCount = $lastMonthAnnouncements ? (int)$lastMonthAnnouncements['total'] : 0;
$thisCount = $thisMonthAnnouncements ? (int)$thisMonthAnnouncements['total'] : 0;
$announcementsTrend['change'] = $thisCount - $lastCount;
$announcementsTrend['direction'] = $announcementsTrend['change'] > 0 ? 'up' : ($announcementsTrend['change'] < 0 ? 'down' : 'same');
$announcementsTrend['percent'] = $lastCount > 0 ? round(($announcementsTrend['change'] / $lastCount) * 100, 1) : 0;

// Messages (unread contact messages)
$messagesRow = $conn->query("SELECT COUNT(*) AS total FROM contact_messages WHERE status = 'unread'")->fetch_assoc();
$unreadMessages = $messagesRow ? (int)$messagesRow['total'] : 0;
$totalMessagesRow = $conn->query("SELECT COUNT(*) AS total FROM contact_messages")->fetch_assoc();
$totalMessages = $totalMessagesRow ? (int)$totalMessagesRow['total'] : 0;

// Messages trend
$messagesTrend = ['change' => 0, 'direction' => '', 'percent' => 0];
$lastMonthMessages = $conn->query("SELECT COUNT(*) AS total FROM contact_messages WHERE created_at >= '$lastMonthStart' AND created_at <= '$lastMonthEnd'")->fetch_assoc();
$thisMonthMessages = $conn->query("SELECT COUNT(*) AS total FROM contact_messages WHERE created_at >= '$thisMonthStart'")->fetch_assoc();
$lastCount = $lastMonthMessages ? (int)$lastMonthMessages['total'] : 0;
$thisCount = $thisMonthMessages ? (int)$thisMonthMessages['total'] : 0;
$messagesTrend['change'] = $thisCount - $lastCount;
$messagesTrend['direction'] = $messagesTrend['change'] > 0 ? 'up' : ($messagesTrend['change'] < 0 ? 'down' : 'same');
$messagesTrend['percent'] = $lastCount > 0 ? round(($messagesTrend['change'] / $lastCount) * 100, 1) : 0;

// ===== UPCOMING EVENTS (TODAY & NEXT 24 HOURS) =====
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$upcomingAlertsQuery = "SELECT * FROM events WHERE is_archived=0 AND date BETWEEN '$today' AND '$tomorrow' ORDER BY date ASC, time ASC LIMIT 5";
$upcomingAlertsResult = $conn->query($upcomingAlertsQuery);
$upcomingAlerts = [];
if ($upcomingAlertsResult && $upcomingAlertsResult->num_rows > 0) {
    while ($alert = $upcomingAlertsResult->fetch_assoc()) {
        $upcomingAlerts[] = $alert;
    }
}

// ===== TIME-BASED GREETING (Philippine Time) =====
date_default_timezone_set('Asia/Manila');
$hour = date('G');
if ($hour >= 5 && $hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour >= 12 && $hour < 18) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}

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
    // Start from the first day of the CURRENT month, then subtract $i months
    // This perfectly prevents 28/29/30/31 day overflows
    $dateObj = new DateTime("first day of this month");
    $dateObj->modify("-$i month");
    
    $ym = $dateObj->format('Y-m');
    $memberMonthIndex[$ym] = 0;
    $memberMonthLabels[] = $dateObj->format('M Y');
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
                    if (!isset($activeTrendActive[$idx])) { $activeTrendActive[$idx] = 0; }
                    $activeTrendActive[$idx] = (int)$activeTrendActive[$idx] + (int)$val;
                } else {
                    if (!isset($activeTrendInactive[$idx])) { $activeTrendInactive[$idx] = 0; }
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
    $dateObj = new DateTime("first day of this month");
    $dateObj->modify("-$i month");
    
    $ym = $dateObj->format('Y-m');
    $eventMonthIndex[$ym] = 0;
    $eventMonthLabels[] = $dateObj->format('M Y');
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

// Calculate current month progress dynamically
$currentYear = date('Y');
$currentMonth = date('m');
$currentMonthName = date('F');

// Get total ACTIVE events for current month (excluding archived)
$totalEventsQuery = $conn->query("SELECT COUNT(*) as total FROM events WHERE YEAR(date) = $currentYear AND MONTH(date) = $currentMonth AND (is_archived = 0 OR is_archived IS NULL)");
$totalEvents = $totalEventsQuery ? $totalEventsQuery->fetch_assoc()['total'] : 0;

// Get completed ACTIVE events for current month (events with date < today, excluding archived)
$today = date('Y-m-d');
$completedEventsQuery = $conn->query("SELECT COUNT(*) as completed FROM events WHERE YEAR(date) = $currentYear AND MONTH(date) = $currentMonth AND date < '$today' AND (is_archived = 0 OR is_archived IS NULL)");
$completedEvents = $completedEventsQuery ? $completedEventsQuery->fetch_assoc()['completed'] : 0;

// Calculate percentage
if ($totalEvents > 0) {
    $marchEventPercent = round(($completedEvents / $totalEvents) * 100);
} else {
    $marchEventPercent = 0; // No events planned for this month
}
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
/* ===== GLOBAL SENIOR-FRIENDLY BASE ===== */
body {
    font-family: 'Inter', 'Segoe UI', sans-serif;
    background: #f0f2f5;
    margin: 0;
    font-size: 16px;
    line-height: 1.6;
}

h1, h2 { font-size: calc(1.375rem + 0.4vw); }
h3 { font-size: 1.6rem; }
h4 { font-size: 1.35rem; }
h5 { font-size: 1.18rem; }
h6 { font-size: 1.05rem; }

.main-content {
    margin-left: 260px;
    padding: 28px 32px 40px;
    min-height: 100vh;
    box-sizing: border-box;
    overflow-x: hidden;
    padding-top: 86px;
}

/* ===== GREETING BANNER ===== */
.greeting-banner {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    padding: 32px 36px;
    border-radius: 18px;
    box-shadow: 0 8px 28px rgba(99, 102, 241, 0.28);
    animation: fadeInDown 0.5s ease-out;
    margin-bottom: 2rem;
}
.greeting-banner h3 {
    font-weight: 800;
    margin: 0;
    font-size: 1.9rem;
    letter-spacing: -0.5px;
}
.greeting-banner p {
    margin: 6px 0 0;
    opacity: 0.92;
    font-size: 1.05rem;
}
.greeting-banner .btn-light {
    background: rgba(255,255,255,0.22);
    border: 1.5px solid rgba(255,255,255,0.45);
    color: white;
    font-weight: 600;
    font-size: 1rem;
    padding: 11px 22px;
    border-radius: 10px;
    min-height: 48px;
    transition: all 0.25s ease;
}
.greeting-banner .btn-light:hover {
    background: rgba(255,255,255,0.35);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.greeting-banner .btn-warning {
    background: linear-gradient(135deg, #ffd93d 0%, #ffb347 100%);
    border: none;
    color: #333;
    font-weight: 700;
    font-size: 1rem;
    padding: 11px 22px;
    min-height: 48px;
    border-radius: 10px;
    animation: pulse 2s infinite;
    box-shadow: 0 4px 12px rgba(255, 179, 71, 0.4);
}
.greeting-banner .btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(255, 179, 71, 0.5);
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes chartFadeIn {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}
.chart-card-animate {
    animation: chartFadeIn 0.6s ease-out both;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50%       { transform: scale(1.04); }
}

/* ===== DASHBOARD STAT CARDS ===== */
.stat-card-wrap {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    border-left: 4px solid #6366f1;
    box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer;
    height: 100%;
    display: block;
    text-decoration: none;
}
.stat-card-wrap:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    text-decoration: none;
}
.stat-card-wrap .sc-icon  { font-size: 1.5rem; margin-bottom: 6px; }
.stat-card-wrap .sc-num   { font-size: 1.4rem; font-weight: 700; color: #1e293b; line-height: 1.2; }
.stat-card-wrap .sc-label { font-size: 0.8rem; color: #64748b; margin-top: 2px; }
.stat-card-wrap .sc-trend { font-size: 0.75rem; font-weight: 600; margin-top: 4px; display: flex; align-items: center; gap: 3px; }
.sc-trend.trend-up   { color: #16a34a; }
.sc-trend.trend-down { color: #dc2626; }
.sc-trend.trend-same { color: #8b5cf6; }

/* per-card colors */
.stat-card-members  { background: #ffffff; border-left-color: #6366f1; }
.stat-card-members  .sc-icon { color: #6366f1; }
.stat-card-officers { background: #ffffff; border-left-color: #22c55e; }
.stat-card-officers .sc-icon { color: #22c55e; }
.stat-card-events   { background: #ffffff; border-left-color: #f97316; }
.stat-card-events   .sc-icon { color: #f97316; }
.stat-card-announce { background: #ffffff; border-left-color: #a855f7; }
.stat-card-announce .sc-icon { color: #a855f7; }
.stat-card-messages { background: #ffffff; border-left-color: #f59e0b; }
.stat-card-messages .sc-icon { color: #f59e0b; }

/* ===== ICON BOX (used elsewhere in page) ===== */
.icon-box {
    border-radius: 12px;
    color: #fff;
    width: 48px;
    height: 48px;
    min-width: 48px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}
.icon-box-members  { background: #6366f1; }
.icon-box-officers { background: #22c55e; }
.icon-box-events   { background: #f97316; }
.icon-box-announce { background: #a855f7; }
.icon-box-messages { background: #f59e0b; }

/* ===== TREND INDICATORS (legacy, used in other sections) ===== */
.trend-indicator {
    font-size: 0.82rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 4px;
}
.trend-indicator.trend-up   { color: #16a34a; }
.trend-indicator.trend-down { color: #dc2626; }
.trend-indicator.trend-same { color: #8b5cf6; }

/* ===== ANALYTICS / EVENT CARDS ===== */
.event-card {
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    border: 1px solid #e8ecf0;
    background: #FFFFFF;
    color: #2C3E50;
    transition: box-shadow 0.2s, transform 0.2s;
}
.event-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}
.event-card img {
    border-top-left-radius: 14px;
    border-top-right-radius: 14px;
    max-height: 180px;
    object-fit: cover;
}
.card-text {
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
    line-height: 1.4em;
    max-height: 4.2em;
    font-size: 1rem;
}

/* ===== BUTTONS ===== */
.btn {
    font-size: 1rem;
    min-height: 44px;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
}
.btn-sm {
    font-size: 0.9rem;
    min-height: 40px;
    padding: 8px 16px;
}
.btn-primary {
    background-color: #6366f1;
    border-color: #6366f1;
}
.btn-primary:hover {
    background-color: #4f46e5;
    border-color: #4f46e5;
}

/* ===== CHART CONTAINERS ===== */
#calendar-container { display: none; }
.calendar-header    { display: none; }

.chart-fixed {
    height: 320px;
    max-height: 400px;
}
.chart-fixed-sm {
    height: 220px;
    max-height: 280px;
}
.chart-fixed .chart-canvas,
.chart-fixed-sm .chart-canvas {
    height: 100% !important;
    width: 100% !important;
}
.small-chart {
    height: 120px;
    max-height: 160px;
    margin-bottom: 16px;
    padding: 8px 0;
}
.small-chart .chart-canvas {
    height: 100% !important;
    width: 100% !important;
}

    /* FullCalendar customization */
    .fc {
        font-family: 'Inter', 'Segoe UI', sans-serif;
        font-size: 1rem;
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
        font-weight: 700;
        padding: 14px 0 !important;
        font-size: 1rem;
        text-transform: uppercase;
    }

    .fc-daygrid-day {
        border-color: #E0E0E0;
        min-height: 110px;
        background: #FFFFFF !important;
    }

    .fc-daygrid-day-number {
        padding: 10px;
        color: #2C3E50;
        font-weight: 700;
        font-size: 1rem;
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

    /* Add Event Button */
    .btn-dark {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        border: none !important;
        color: white !important;
        font-weight: 600;
        box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-dark:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 24px rgba(102, 126, 234, 0.4);
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%) !important;
        color: white !important;
    }


</style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="main-content">

<!-- Greeting Banner -->
<div class="greeting-banner mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h3 class="mb-1"><?= $greeting ?>, <?= htmlspecialchars($fullname) ?>! 👋</h3>
            <p class="mb-0">You are logged in as <strong><?= ucfirst($role) ?></strong></p>
        </div>
        <div class="d-flex gap-2 align-items-center mt-2 mt-md-0">
            <button class="btn btn-light btn-sm" onclick="showQuickSearch()">
                <i class="bi bi-search"></i> Quick Search
            </button>
            <?php if (count($upcomingAlerts) > 0): ?>
            <button class="btn btn-warning btn-sm position-relative" onclick="showUpcomingAlerts()">
                <i class="bi bi-bell-fill"></i> Alerts
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?= count($upcomingAlerts) ?>
                </span>
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Dashboard Cards -->
<div class="dashboard-cards row g-3 mb-4">

    <!-- Members -->
    <div class="col-6 col-md">
        <a href="management/memberlist.php" class="stat-card-wrap stat-card-members">
            <div class="sc-icon"><i class="bi bi-people-fill"></i></div>
            <div class="sc-num"><?= $members ?></div>
            <div class="sc-label">Total Members</div>
            <?php if ($membersTrend['change'] != 0): ?>
            <div class="sc-trend trend-<?= $membersTrend['direction'] ?>">
                <i class="bi bi-arrow-<?= $membersTrend['direction'] === 'up' ? 'up' : 'down' ?>-short"></i>
                <?= abs($membersTrend['change']) ?> from last month
            </div>
            <?php endif; ?>
        </a>
    </div>

    <!-- Officers -->
    <div class="col-6 col-md">
        <a href="management/officerslist.php" class="stat-card-wrap stat-card-officers">
            <div class="sc-icon"><i class="bi bi-person-badge-fill"></i></div>
            <div class="sc-num"><?= $officers ?></div>
            <div class="sc-label">Total Officers</div>
            <?php if ($officersTrend['change'] != 0): ?>
            <div class="sc-trend trend-<?= $officersTrend['direction'] ?>">
                <i class="bi bi-arrow-<?= $officersTrend['direction'] === 'up' ? 'up' : 'down' ?>-short"></i>
                <?= abs($officersTrend['change']) ?> from last month
            </div>
            <?php endif; ?>
        </a>
    </div>

    <!-- Upcoming Events -->
    <div class="col-6 col-md">
        <a href="event.php" class="stat-card-wrap stat-card-events">
            <div class="sc-icon"><i class="bi bi-calendar-event-fill"></i></div>
            <div class="sc-num"><?= $eventsUpcoming ?></div>
            <div class="sc-label">Upcoming Events</div>
            <?php if ($eventsTrend['change'] != 0): ?>
            <div class="sc-trend trend-<?= $eventsTrend['direction'] ?>">
                <i class="bi bi-arrow-<?= $eventsTrend['direction'] === 'up' ? 'up' : 'down' ?>-short"></i>
                <?= abs($eventsTrend['change']) ?> from last month
            </div>
            <?php endif; ?>
        </a>
    </div>

    <!-- Announcements -->
    <div class="col-6 col-md">
        <a href="announcement/admin_announcement.php" class="stat-card-wrap stat-card-announce">
            <div class="sc-icon"><i class="bi bi-megaphone-fill"></i></div>
            <div class="sc-num"><?= $announcements ?></div>
            <div class="sc-label">Announcements</div>
            <?php if ($announcementsTrend['change'] != 0): ?>
            <div class="sc-trend trend-<?= $announcementsTrend['direction'] ?>">
                <i class="bi bi-arrow-<?= $announcementsTrend['direction'] === 'up' ? 'up' : 'down' ?>-short"></i>
                <?= abs($announcementsTrend['change']) ?> from last month
            </div>
            <?php endif; ?>
        </a>
    </div>

    <!-- Messages -->
    <div class="col-6 col-md">
        <a href="http://localhost/bangkero_system/index/management/contact_messages.php" class="stat-card-wrap stat-card-messages">
            <div class="sc-icon" style="position:relative;display:inline-block;">
                <i class="bi bi-envelope-fill"></i>
                <?php if ($unreadMessages > 0): ?>
                <span style="position:absolute;top:-4px;right:-8px;background:#ef4444;color:#fff;border-radius:50%;font-size:0.55rem;font-weight:700;width:15px;height:15px;display:inline-flex;align-items:center;justify-content:center;"><?= min($unreadMessages,99) ?></span>
                <?php endif; ?>
            </div>
            <div class="sc-num"><?= $unreadMessages ?><?= $unreadMessages > 0 ? '<span style="font-size:0.75rem;font-weight:500;color:#92400e;"> unread</span>' : '' ?></div>
            <div class="sc-label">New Messages</div>
            <?php if ($messagesTrend['change'] != 0): ?>
            <div class="sc-trend trend-<?= $messagesTrend['direction'] ?>">
                <i class="bi bi-arrow-<?= $messagesTrend['direction'] === 'up' ? 'up' : 'down' ?>-short"></i>
                <?= abs($messagesTrend['change']) ?> from last month
            </div>
            <?php else: ?>
            <div style="font-size:0.72rem;color:#94a3b8;margin-top:4px;"><?= $totalMessages ?> total received</div>
            <?php endif; ?>
        </a>
    </div>

</div>

<!-- Member Analytics Panel -->
<div class="row mb-4">
    <div class="col-12">
        <!-- Monthly New Members — modern smooth line chart card -->
        <div class="chart-card-animate" style="background:#ffffff; border-radius:18px; box-shadow:0 4px 24px rgba(99,102,241,0.08); padding:28px 28px 20px; border:1px solid #ede9fe;">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:1rem;color:#1e293b;">Monthly New Members</div>
                        <div style="font-size:0.78rem;color:#94a3b8;">Growth trend over the last 6 months</div>
                    </div>
                </div>
                <span style="font-size:0.72rem;font-weight:600;letter-spacing:0.5px;color:#6366f1;background:#eef2ff;padding:4px 12px;border-radius:20px;">LAST 6 MONTHS</span>
            </div>
            <div style="height:290px;position:relative;margin-top:12px;">
                <canvas id="monthlyNewMembersChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Event Analytics Panel -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card event-card p-3 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="icon-box me-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="bi bi-calendar-event-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">Event Analytics</h5>
                        <p class="mb-0 text-muted small">Event trends and statistics</p>
                    </div>
                </div>
                <div class="row gy-4">
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff8f0 0%, #ffffff 100%);">
                            <div class="card-body p-4">
                                <h6 class="card-title fw-bold text-dark mb-3">
                                    <i class="bi bi-bar-chart-fill me-2" style="color: #f59e0b;"></i>Events Per Month
                                </h6>
                                <div class="chart-fixed" style="height: 250px;">
                                    <canvas id="eventsPerMonthChart" class="chart-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%);">
                            <div class="card-body p-4">
                                <h6 class="card-title fw-bold text-dark mb-3">
                                    <i class="bi bi-pie-chart-fill me-2" style="color: #3b82f6;"></i>Event Categories
                                </h6>
                                <div class="chart-fixed" style="height: 250px;">
                                    <canvas id="eventTypesChart" class="chart-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);">
                            <div class="card-body p-4">
                                <h6 class="card-title fw-bold text-dark mb-3">
                                    <i class="bi bi-trophy-fill me-2" style="color: #10b981;"></i><?php echo $currentMonthName; ?> Progress
                                </h6>
                                <div class="chart-fixed" style="height: 250px;">
                                    <canvas id="marchEventChart" class="chart-canvas"></canvas>
                                </div>
                                <div class="text-center mt-2">
                                    <small class="text-muted">
                                        <?php echo $completedEvents; ?> completed / <?php echo $totalEvents; ?> total events
                                    </small>
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
                        <button type="button" class="btn btn-sm btn-outline-secondary calendar-nav-btn" id="calendarPrev" title="Previous Month" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary calendar-nav-btn" id="calendarToday" title="Go to Today" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
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
                        <button type="button" class="btn btn-sm btn-outline-secondary calendar-nav-btn" id="calendarNext" title="Next Month" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggle24h" title="Toggle 24-hour format" style="height: 32px;">24h</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="calendarFilter" title="Filter Events" style="height: 32px;">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <span class="badge bg-light text-dark border" id="currentMonthDisplay" style="height: 32px; display: flex; align-items: center; font-size: 0.875rem; font-weight: 500;">This Month</span>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-secondary calendar-view-btn" data-view="listWeek" title="List View" style="height: 32px; width: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-list-ul"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary calendar-view-btn" data-view="timeGridWeek" title="Week View (with time)" style="height: 32px; width: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-calendar-week"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary calendar-view-btn active" data-view="dayGridMonth" title="Month View" style="height: 32px; width: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-calendar3"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary calendar-view-btn" data-view="dayGrid" title="Year View" style="height: 32px; width: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-grid-3x3"></i>
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-dark" id="addEventBtn" title="Add New Event" style="height: 32px; display: flex; align-items: center; gap: 6px;">
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
    const currentMonthDisplay = document.getElementById('currentMonthDisplay');
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
        
        // Update the current month display text
        if (currentMonthDisplay) {
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                               'July', 'August', 'September', 'October', 'November', 'December'];
            currentMonthDisplay.textContent = monthNames[d.getMonth()] + ' ' + d.getFullYear();
        }
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
        listWeek: 'listMonth',   // List view shows events for the current month only
        timeGridWeek: 'timeGridWeek',
        dayGridMonth: 'dayGridMonth',
        dayGrid: 'multiMonthYear'   // Year view - shows all months
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
            // Gradient fill
            const mlCtx2d = monthlyCtx.getContext('2d');
            const gradientFill = mlCtx2d.createLinearGradient(0, 0, 0, 280);
            gradientFill.addColorStop(0,   'rgba(99, 102, 241, 0.22)');
            gradientFill.addColorStop(0.6, 'rgba(99, 102, 241, 0.05)');
            gradientFill.addColorStop(1,   'rgba(99, 102, 241, 0)');

            // Per-point styling: highlight the last data point
            const lastIdx = monthlyNew.length - 1;
            const pointBg    = monthlyNew.map((_, i) => i === lastIdx ? '#6366f1' : '#ffffff');
            const pointBorder = monthlyNew.map((_, i) => i === lastIdx ? '#6366f1' : '#6366f1');
            const pointRadius = monthlyNew.map((_, i) => i === lastIdx ? 7 : 5);
            const pointHover  = monthlyNew.map((_, i) => i === lastIdx ? 9 : 7);

            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'New Members',
                        data: monthlyNew,
                        borderColor: '#6366f1',
                        borderWidth: 2.5,
                        tension: 0.45,
                        fill: true,
                        backgroundColor: gradientFill,
                        pointBackgroundColor: pointBg,
                        pointBorderColor: pointBorder,
                        pointBorderWidth: 2.5,
                        pointRadius: pointRadius,
                        pointHoverRadius: pointHover,
                        pointHoverBackgroundColor: '#6366f1',
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 3,
                        cubicInterpolationMode: 'monotone'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1200,
                        easing: 'easeInOutQuart',
                        x: { from: 0 }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleColor: '#94a3b8',
                            bodyColor: '#ffffff',
                            bodyFont: { size: 14, weight: '700' },
                            titleFont: { size: 11 },
                            padding: 12,
                            cornerRadius: 10,
                            displayColors: false,
                            callbacks: {
                                title: ctx => ctx[0].label,
                                label: ctx => '  ' + ctx.parsed.y + ' new members'
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: { color: '#94a3b8', font: { size: 11, weight: '500' } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(148,163,184,0.12)', drawBorder: false },
                            border: { display: false, dash: [4, 4] },
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 11 },
                                stepSize: 1,
                                padding: 8
                            }
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
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.3,
                            fill: true,
                            borderWidth: 2
                        },
                        {
                            label: 'Inactive',
                            data: inactiveData,
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            tension: 0.3,
                            fill: true,
                            borderWidth: 2
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
                        backgroundColor: 'rgba(245, 158, 11, 0.7)',
                        borderColor: '#f59e0b',
                        borderWidth: 2,
                        borderRadius: 8,
                        hoverBackgroundColor: 'rgba(245, 158, 11, 0.9)'

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
                                    'rgba(59, 130, 246, 0.85)',
                                    'rgba(59, 130, 246, 0.65)',
                                    'rgba(59, 130, 246, 0.45)'
                                ],
                                borderColor: '#3b82f6',
                                borderWidth: 2,
                                borderRadius: 6
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
        const currentMonthName = <?php echo json_encode($currentMonthName); ?> || 'Current Month';
        const marchCtx = document.getElementById('marchEventChart');
        if (marchCtx) {
            new Chart(marchCtx, {
                type: 'bar',
                data: {
                    labels: [currentMonthName],
                    datasets: [{
                        label: 'Achievement %',
                        data: [marchPercent],
                       backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: '#10b981',
                        borderWidth: 2,
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

<!-- Quick Search Modal -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function showQuickSearch() {
    Swal.fire({
        title: '<i class="bi bi-search"></i> Quick Search',
        html: `
            <input type="text" id="quickSearchInput" class="swal2-input" placeholder="Search members, events, announcements...">
            <select id="quickSearchType" class="swal2-select">
                <option value="all">All</option>
                <option value="members">Members</option>
                <option value="events">Events</option>
                <option value="announcements">Announcements</option>
            </select>
        `,
        showCancelButton: true,
        confirmButtonText: 'Search',
        confirmButtonColor: '#5B6B7A',
        preConfirm: () => {
            const query = document.getElementById('quickSearchInput').value;
            const type = document.getElementById('quickSearchType').value;
            if (!query) {
                Swal.showValidationMessage('Please enter a search term');
                return false;
            }
            return { query, type };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { query, type } = result.value;
            // Redirect based on type
            if (type === 'members' || type === 'all') {
                window.location.href = `management/memberlist.php?search=${encodeURIComponent(query)}`;
            } else if (type === 'events') {
                window.location.href = `event.php?search=${encodeURIComponent(query)}`;
            } else if (type === 'announcements') {
                window.location.href = `announcement/admin_announcement.php?q=${encodeURIComponent(query)}`;
            }
        }
    });
}

function showUpcomingAlerts() {
    const alerts = <?php echo json_encode($upcomingAlerts); ?>;
    
    let html = '<div style="text-align: left; max-height: 400px; overflow-y: auto;">';
    alerts.forEach(alert => {
        const eventDate = new Date(alert.date);
        const isToday = eventDate.toDateString() === new Date().toDateString();
        const dateText = isToday ? 'Today' : 'Tomorrow';
        const timeText = alert.time || 'All Day';
        
        html += `
            <div style="border-left: 4px solid #ff9800; padding: 12px; margin-bottom: 12px; background: #fff3e0; border-radius: 4px;">
                <div style="font-weight: 600; color: #333; margin-bottom: 4px;">
                    <i class="bi bi-calendar-event"></i> ${alert.event_name}
                </div>
                <div style="font-size: 0.9rem; color: #666;">
                    <i class="bi bi-clock"></i> ${dateText} at ${timeText}<br>
                    <i class="bi bi-geo-alt"></i> ${alert.location || 'No location'}
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    Swal.fire({
        title: '<i class="bi bi-bell-fill"></i> Upcoming Events',
        html: html,
        icon: 'info',
        confirmButtonText: 'Got it!',
        confirmButtonColor: '#ff9800',
        width: '600px'
    });
}

// Show alerts on page load if there are any
<?php if (count($upcomingAlerts) > 0): ?>
setTimeout(() => {
    const alertToast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
    
    alertToast.fire({
        icon: 'warning',
        title: '<?= count($upcomingAlerts) ?> upcoming event(s) in next 24 hours!'
    });
}, 2000);
<?php endif; ?>

// Access message for Transparency permissions
<?php if (($_GET['error'] ?? '') === 'transparency_access'): ?>
Swal.fire({
    icon: 'error',
    title: 'Access denied',
    text: 'Wala kang permission sa Transparency page na iyon. (Treasurer: Donations, Secretary: Programs)',
    confirmButtonColor: '#667eea'
});
<?php endif; ?>
</script>

</body>
</html>
