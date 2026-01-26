<?php
header('Content-Type: application/json');
include('../../config/db_connect.php');

// Get event ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid event ID']);
    exit;
}

$event_id = (int)$_GET['id'];

// Check for is_archived column
$hasIsArchived = false;
$colRes = $conn->query("SHOW COLUMNS FROM `events` LIKE 'is_archived'");
if ($colRes && $colRes->num_rows > 0) $hasIsArchived = true;

$archivedCondition = $hasIsArchived ? "events.is_archived = 0" : "1";

// Fetch event
$sql = "SELECT * FROM events WHERE id = $event_id AND {$archivedCondition} LIMIT 1";
$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Event not found']);
    exit;
}

$event = $result->fetch_assoc();

// Format dates
$event['date_formatted'] = date("M d, Y", strtotime($event['date']));
$event['date_long'] = date("l, F d, Y", strtotime($event['date']));

// Ensure category exists
if (!isset($event['category']) || empty($event['category'])) {
    $event['category'] = 'General';
}

// Format description (convert newlines to <br>)
if (!empty($event['description'])) {
    $event['description'] = nl2br(htmlspecialchars($event['description']));
}

echo json_encode([
    'success' => true,
    'event' => $event
]);
?>
