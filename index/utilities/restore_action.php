<?php
session_start();
require_once('../../config/db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if restore button was clicked
if (isset($_POST['restore'])) {
    try {
        // Check if file was uploaded
        if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("No file uploaded or upload error occurred.");
        }

        $file = $_FILES['sql_file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file extension
        if ($fileExt !== 'sql') {
            throw new Exception("Invalid file type. Please upload an SQL file.");
        }

        // Validate file size (max 50MB)
        if ($fileSize > 50 * 1024 * 1024) {
            throw new Exception("File too large. Maximum size is 50MB.");
        }

        // Read SQL file content
        $sqlContent = file_get_contents($fileTmpName);
        
        if ($sqlContent === false) {
            throw new Exception("Failed to read SQL file.");
        }

        // Use the existing connection from db_connect.php
        if (!isset($conn) || $conn->connect_error) {
            throw new Exception("Database connection not available");
        }

        // Disable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        // Split SQL content into individual queries
        $queries = array_filter(
            array_map('trim', 
                explode(';', $sqlContent)
            ),
            function($query) {
                return !empty($query) && 
                       !preg_match('/^--/', $query) && 
                       !preg_match('/^\/\*/', $query);
            }
        );

        // Execute each query
        $successCount = 0;
        $errorCount = 0;
        $errors = array();

        foreach ($queries as $query) {
            // Skip comments and empty queries
            if (empty($query) || preg_match('/^--/', $query)) {
                continue;
            }

            if ($conn->query($query) === TRUE) {
                $successCount++;
            } else {
                $errorCount++;
                $errors[] = $conn->error;
                // Continue with other queries even if one fails
            }
        }

        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");

        // Log the activity
        try {
            $user_id = $_SESSION['user_id'];
            $action = 'Database Restore';
            $description = "Restored database from file: {$fileName} ({$successCount} queries executed)";
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
            $log_stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
            $log_stmt->execute();
        } catch (Exception $e) {
            // Silently fail logging
        }

        // Close connection (no need to close, we're using the global connection)
        // $conn->close();

        if ($errorCount > 0) {
            $_SESSION['warning'] = "Restore completed with {$errorCount} errors. {$successCount} queries executed successfully.";
        } else {
            $_SESSION['success'] = "Database restored successfully! {$successCount} queries executed.";
        }

        header("Location: backup.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "Restore failed: " . $e->getMessage();
        header("Location: backup.php");
        exit();
    }
} else {
    header("Location: backup.php");
    exit();
}
