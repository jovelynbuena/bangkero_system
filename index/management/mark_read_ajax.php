<?php
mysqli_report(MYSQLI_REPORT_OFF);
session_start();
require_once('../../config/db_connect.php');

header('Content-Type: application/json');

if (empty($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$role = strtolower($_SESSION['role'] ?? 'guest');
if (!in_array($role, ['admin', 'officer'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$raw_id = $_GET['id'] ?? '';
$id = intval($raw_id);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$stmt = $conn->prepare("UPDATE contact_messages SET status='read' WHERE id=?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => $conn->error]);
    exit;
}
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
