<?php
session_start();
if ($_SESSION['username'] == "") {
    header('location: ../login.php');
    exit;
}
require_once('../../config/db_connect.php');

$search = '';
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT id, name, phone, address FROM members
            WHERE name LIKE '%$search%' OR phone LIKE '%$search%' OR address LIKE '%$search%'";
} else {
    $sql = "SELECT id, name, phone, address FROM members";
}
$result = $conn->query($sql);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=members_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['#', 'Name', 'Contact', 'Address']);

$count = 1;
if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $count++,
            $row['name'],
            $row['phone'],
            $row['address'],
        ]);
    }
}
fclose($output);
exit;
?>


