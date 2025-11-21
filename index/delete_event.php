<?php
session_start();
if ($_SESSION['username'] == "") {
    header('location: login.php');
    exit;
}
include('../config/db_connect.php');

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: event.php");
    exit;
}

// Optionally, delete the poster file from uploads folder
$stmt = $conn->prepare("SELECT event_poster FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
if ($event && !empty($event['event_poster'])) {
    $poster_path = "../uploads/" . $event['event_poster'];
    if (file_exists($poster_path)) {
        unlink($poster_path);
    }
}

// Delete the event
$stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: event.php?deleted=1");
exit;