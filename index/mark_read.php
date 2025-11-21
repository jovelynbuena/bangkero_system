<?php
session_start();
if (!isset($_SESSION['read_announcements'])) {
    $_SESSION['read_announcements'] = [];
}
if (isset($_GET['all'])) {
    // Mark all as read (simulate by loading ids from last query)
    include('../config/db_connect.php');
    $announcement_sql = "SELECT id FROM announcements ORDER BY date_posted DESC LIMIT 5";
    $announcement_result = $conn->query($announcement_sql);
    if ($announcement_result) {
        while ($row = $announcement_result->fetch_assoc()) {
            if (!in_array($row['id'], $_SESSION['read_announcements'])) {
                $_SESSION['read_announcements'][] = $row['id'];
            }
        }
    }
} elseif (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if (!in_array($id, $_SESSION['read_announcements'])) {
        $_SESSION['read_announcements'][] = $id;
    }
}
?>