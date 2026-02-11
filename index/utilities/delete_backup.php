<?php
session_start();
require_once('../../config/db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (isset($_POST['filename'])) {
    $filename = basename($_POST['filename']); // Security: prevent directory traversal
    $backupDir = __DIR__ . '/backups/';
    $filePath = $backupDir . $filename;

    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            // Log the activity
            try {
                if (isset($connLog)) {
                    $user_id = $_SESSION['user_id'];
                    $action = 'Delete Backup';
                    $description = "Deleted backup file: {$filename}";
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                    
                    $log_stmt = $connLog->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
                    $log_stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
                    $log_stmt->execute();
                }
            } catch (Exception $e) {
                // Silently fail logging
            }

            echo json_encode(['success' => true, 'message' => 'Backup deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete backup']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Backup file not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No filename provided']);
}
