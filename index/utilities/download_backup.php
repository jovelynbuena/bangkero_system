<?php
session_start();

// Clear any previous output to prevent header issues
if (ob_get_level()) {
    ob_end_clean();
}

require_once('../../config/db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Unauthorized access');
}

// Check if filename is provided
if (!isset($_GET['file']) || empty($_GET['file'])) {
    http_response_code(400);
    exit('No file specified');
}

// Sanitize filename (prevent directory traversal)
$filename = basename($_GET['file']);
$backupDir = __DIR__ . '/backups/';
$filePath = $backupDir . $filename;

// Validate file existence and extension
if (!file_exists($filePath) || pathinfo($filename, PATHINFO_EXTENSION) !== 'sql') {
    http_response_code(404);
    exit('File not found');
}

// Optional: Log download activity
try {
    if (isset($conn)) {
        $user_id = $_SESSION['user_id'];
        $action = 'Download Backup';
        $description = "Downloaded backup file: {$filename}";
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        if ($log_stmt) {
            $log_stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
            $log_stmt->execute();
            $log_stmt->close();
        }
    }
} catch (Exception $e) {
    // Don't block download if logging fails
}

// Set proper headers for forced download
header('Content-Description: File Transfer');
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Clear buffer again before output
flush();

// Output file
readfile($filePath);
exit;
