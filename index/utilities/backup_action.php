<?php
session_start();

// Start output buffering to prevent any accidental output before headers
ob_start();

require_once('../../config/db_connect.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display, save to session instead
ini_set('log_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if backup button was clicked
if (isset($_POST['backup'])) {
    // Convert PHP warnings/notices to exceptions so we can show them
    set_error_handler(function($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    try {
        if (!isset($conn) || $conn->connect_error) {
            throw new Exception("Database connection not available");
        }

        // Ensure backup directory exists
        $backupDir = __DIR__ . '/backups/';
        if (!file_exists($backupDir)) {
            if (!mkdir($backupDir, 0777, true)) {
                throw new Exception("Cannot create backups folder: " . $backupDir);
            }
        } else {
            @chmod($backupDir, 0777);
        }

        // Create backup filename with timestamp
        $backupFileName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $backupFilePath = $backupDir . $backupFileName;

        // Get database name
        $dbResult = $conn->query("SELECT DATABASE()");
        $currentDb = $dbResult->fetch_row()[0];
        
        // Start building SQL content
        $sqlContent = "-- Database Backup\n";
        $sqlContent .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
        $sqlContent .= "-- Database: {$currentDb}\n\n";
        $sqlContent .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sqlContent .= "SET time_zone = \"+00:00\";\n\n";

        // Get all tables
        $tables = array();
        $result = $conn->query("SHOW TABLES");
        if (!$result) {
            throw new Exception("SHOW TABLES failed: " . $conn->error);
        }
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }

        if (empty($tables)) {
            throw new Exception("No tables found in database");
        }

        // Loop through tables and generate backup
        foreach ($tables as $table) {
            // Drop table if exists
            $sqlContent .= "\n-- Table structure for table `{$table}`\n";
            $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n\n";

            // Get CREATE TABLE statement
            $createTableResult = $conn->query("SHOW CREATE TABLE `{$table}`");
            if (!$createTableResult) {
                throw new Exception("SHOW CREATE TABLE failed for {$table}: " . $conn->error);
            }
            $row = $createTableResult->fetch_row();
            $sqlContent .= $row[1] . ";\n\n";

            // Get table data
            $sqlContent .= "-- Dumping data for table `{$table}`\n";
            $dataResult = $conn->query("SELECT * FROM `{$table}`");
            if (!$dataResult) {
                throw new Exception("SELECT * failed for {$table}: " . $conn->error);
            }
            
            if ($dataResult->num_rows > 0) {
                while ($row = $dataResult->fetch_assoc()) {
                    $columns = array_keys($row);
                    $values = array_values($row);
                    
                    // Escape values
                    $escapedValues = array_map(function($value) use ($conn) {
                        if ($value === null) {
                            return 'NULL';
                        }
                        return "'" . $conn->real_escape_string($value) . "'";
                    }, $values);

                    $sqlContent .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n";
                }
                $sqlContent .= "\n";
            }
        }

        // Write to file
        $writeResult = file_put_contents($backupFilePath, $sqlContent);
        
        if ($writeResult === false) {
            throw new Exception("Failed to write backup file");
        }

        // Verify file was created with content
        if (!file_exists($backupFilePath) || filesize($backupFilePath) <= 0) {
            throw new Exception("Backup file is empty. Backup may have failed.");
        }

        // Create backups table if it doesn't exist
        try {
            $conn->query("CREATE TABLE IF NOT EXISTS `backups` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `filename` varchar(255) NOT NULL,
                `filesize` bigint(20) NOT NULL,
                `created_by` int(11) NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        } catch (Exception $e) {
            // Table might already exist
        }

        // Create activity_logs table if it doesn't exist
        try {
            $conn->query("CREATE TABLE IF NOT EXISTS `activity_logs` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `action` varchar(100) NOT NULL,
                `description` text,
                `ip_address` varchar(50),
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        } catch (Exception $e) {
            // Table might already exist
        }

        // Save backup record to database
        try {
            $user_id = $_SESSION['user_id'];
            $filesize = filesize($backupFilePath);
            
            $backup_stmt = $conn->prepare("INSERT INTO backups (filename, filesize, created_by) VALUES (?, ?, ?)");
            $backup_stmt->bind_param("sii", $backupFileName, $filesize, $user_id);
            $backup_stmt->execute();
        } catch (Exception $e) {
            // Continue even if logging fails
        }

        // Log the activity
        try {
            $user_id = $_SESSION['user_id'];
            $action = 'Database Backup';
            $description = "Created backup: {$backupFileName} (" . number_format(filesize($backupFilePath)) . " bytes)";
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
            $log_stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
            $log_stmt->execute();
        } catch (Exception $e) {
            // Silently fail logging
        }

        // Set success message and redirect with download trigger
        $_SESSION['success'] = "Backup created successfully! File: {$backupFileName}";
        $_SESSION['download_file'] = $backupFileName;
        
        // Clear output buffer and redirect
        ob_end_clean();
        header("Location: backup.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "Backup failed: " . $e->getMessage();
        $_SESSION['debug_error'] = $e->getMessage();
        error_log("Backup error: " . $e->getMessage());
        
        // Clear output buffer and redirect
        ob_end_clean();
        header("Location: backup.php");
        exit();
    }
} else {
    ob_end_clean();
    header("Location: backup.php");
    exit();
}
