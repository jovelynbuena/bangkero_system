<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

// Read filters from query string (same as officerslist.php)
$search      = trim($_GET['search'] ?? '');
$role_filter = $_GET['role_filter'] ?? '';
$term_status = $_GET['term_status'] ?? 'all'; // current, previous, all
$sort        = $_GET['sort'] ?? 'pos_asc';

$where  = [];
$params = [];
$types  = '';

// Term status condition
if ($term_status === 'current') {
    $where[] = 'o.term_end >= CURDATE()';
} elseif ($term_status === 'previous') {
    $where[] = 'o.term_end < CURDATE()';
}

// Search filter (by member name, position name, or description)
if ($search !== '') {
    $where[] = '(m.name LIKE ? OR r.role_name LIKE ? OR o.description LIKE ?)';
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types   .= 'sss';
}

// Position filter
if ($role_filter !== '') {
    $where[]  = 'o.role_id = ?';
    $params[] = $role_filter;
    $types   .= 'i';
}

$where_sql = '';
if (!empty($where)) {
    $where_sql = ' WHERE ' . implode(' AND ', $where);
}

// Sort logic (mirror officerslist.php)
$order_sql = ' ORDER BY r.role_name ASC';
if ($sort === 'pos_desc') {
    $order_sql = ' ORDER BY r.role_name DESC';
} elseif ($sort === 'name_asc') {
    $order_sql = ' ORDER BY m.name ASC';
} elseif ($sort === 'name_desc') {
    $order_sql = ' ORDER BY m.name DESC';
} elseif ($sort === 'term_new') {
    $order_sql = ' ORDER BY o.term_start DESC';
} elseif ($sort === 'term_old') {
    $order_sql = ' ORDER BY o.term_start ASC';
}

$sql = "
    SELECT 
        o.id,
        m.name       AS member_name,
        r.role_name  AS position,
        o.term_start,
        o.term_end,
        o.description
    FROM officers o
    JOIN members m       ON o.member_id = m.id
    JOIN officer_roles r ON o.role_id   = r.id
    {$where_sql}
    {$order_sql}
";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=officers_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, ['#', 'Officer Name', 'Position', 'Term Start', 'Term End', 'Description']);

$count = 1;
if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $count++,
            $row['member_name'],
            $row['position'],
            $row['term_start'],
            $row['term_end'],
            $row['description'],
        ]);
    }
}

fclose($output);
exit;
