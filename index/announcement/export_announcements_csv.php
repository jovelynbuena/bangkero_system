<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

// Match filters from admin_announcement.php
$search         = trim($_GET['q'] ?? '');
$date_from      = trim($_GET['from'] ?? '');
$date_to        = trim($_GET['to'] ?? '');
$has_image      = $_GET['has_image'] ?? 'all';
$filter_category = trim($_GET['category'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where[] = "(title LIKE ? OR content LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types   .= 'ss';
}

if ($date_from !== '') {
    $where[]  = "date_posted >= ?";
    $params[] = $date_from . " 00:00:00";
    $types   .= 's';
}

if ($date_to !== '') {
    $where[]  = "date_posted <= ?";
    $params[] = $date_to . " 23:59:59";
    $types   .= 's';
}

if ($has_image === '1') {
    $where[] = "image IS NOT NULL AND image <> ''";
} elseif ($has_image === '0') {
    $where[] = "(image IS NULL OR image = '')";
}

if ($filter_category !== '') {
    $where[]  = "category = ?";
    $params[] = $filter_category;
    $types   .= 's';
}

$sql = "SELECT id, title, content, category, date_posted, expiry_date, posted_by FROM announcements";
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY date_posted DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $bind = [$types];
    foreach ($params as $k => $v) {
        $bind[] = &$params[$k];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Helper to compute status (same as page)
function getAnnouncementStatus($expiry_date) {
    if (empty($expiry_date)) return 'Ongoing';

    $today  = new DateTime();
    $expiry = new DateTime($expiry_date);
    $diff   = $today->diff($expiry);

    if ($expiry < $today) {
        return 'Expired';
    } elseif ($diff->days <= 3) {
        return 'Expiring Soon';
    } else {
        return 'Active';
    }
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=announcements_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, ['#', 'Title', 'Category', 'Status', 'Date Posted', 'Expiry Date', 'Posted By']);

$count = 1;
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status = getAnnouncementStatus($row['expiry_date'] ?? '');
        fputcsv($output, [
            $count++,
            $row['title'],
            $row['category'],
            $status,
            $row['date_posted'],
            $row['expiry_date'],
            $row['posted_by'] ?? 'Admin',
        ]);
    }
}

fclose($output);
exit;
