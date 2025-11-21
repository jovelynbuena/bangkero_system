<?php
session_start();
header('Content-Type: application/json');

if ($_SESSION['username'] == "") {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}
include('../../config/db_connect.php');

if (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Announcement not found or already deleted']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
}
exit;
?>