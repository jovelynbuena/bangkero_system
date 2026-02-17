<?php
// Simple auto-backup script for scheduler (Windows Task Scheduler / cron)
// Usage (Windows Task Scheduler action):
//   Program/script:  C:\xampp\php\php.exe
//   Arguments:       C:\xampp\htdocs\bangkero_system\index\utilities\auto_backup_cron.php

// Run from CLI only for safety
if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

require_once __DIR__ . '/../../config/db_connect.php';

// Optional: respect system_config.auto_backup_status if table/column exists
$autoBackupAllowed = true;
$hasSystemConfig = false;
$hasAutoBackupStatus = false;

// Check if system_config table exists
$tblRes = $conn->query("SHOW TABLES LIKE 'system_config'");
if ($tblRes && $tblRes->num_rows > 0) {
    $hasSystemConfig = true;
    $colRes = $conn->query("SHOW COLUMNS FROM system_config LIKE 'auto_backup_status'");
    if ($colRes && $colRes->num_rows > 0) {
        $hasAutoBackupStatus = true;
    }
}

if ($hasSystemConfig && $hasAutoBackupStatus) {
    $cfgRes = $conn->query("SELECT id, auto_backup_status FROM system_config WHERE id = 1 LIMIT 1");
    if ($cfgRes && $cfgRes->num_rows > 0) {
        $cfg = $cfgRes->fetch_assoc();
        if ((int)$cfg['auto_backup_status'] !== 1) {
            $autoBackupAllowed = false; // Disabled in settings
        }
    }
}

if (!$autoBackupAllowed) {
    echo "Auto backup is disabled in system_config (auto_backup_status != 1).\n";
    exit(0);
}

// START: same backup logic as in backup.php (simplified, no session / redirects)

// Ensure backup directory exists
$backupDir = __DIR__ . '/backups/';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Create backup filename
$backupFileName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
$backupFilePath = $backupDir . $backupFileName;

// Get database name
$dbResult = $conn->query("SELECT DATABASE()");
$currentDb = $dbResult->fetch_row()[0];

// Build SQL content
$sqlContent  = "-- Database Backup\n";
$sqlContent .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
$sqlContent .= "-- Database: {$currentDb}\n\n";
$sqlContent .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$sqlContent .= "SET time_zone = \"+00:00\";\n\n";

// Get all tables
$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

foreach ($tables as $table) {
    $sqlContent .= "\n-- Table: {$table}\n";
    $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n\n";

    // CREATE TABLE
    $createTableResult = $conn->query("SHOW CREATE TABLE `{$table}`");
    $row = $createTableResult->fetch_row();
    $sqlContent .= $row[1] . ";\n\n";

    // DATA
    $sqlContent .= "-- Data for table `{$table}`\n";
    $dataResult = $conn->query("SELECT * FROM `{$table}`");
    if ($dataResult->num_rows > 0) {
        while ($dataRow = $dataResult->fetch_assoc()) {
            $columns = array_keys($dataRow);
            $values  = array_values($dataRow);

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

// Write file
file_put_contents($backupFilePath, $sqlContent);

// Record into backups + activity_logs if tables exist (or create them if not)
$conn->query("CREATE TABLE IF NOT EXISTS `backups` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `filename` varchar(255) NOT NULL,
    `filesize` bigint(20) NOT NULL,
    `created_by` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$conn->query("CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `action` varchar(100) NOT NULL,
    `description` text,
    `ip_address` varchar(50),
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$userId   = 0; // system
$filesize = filesize($backupFilePath);

$backupStmt = $conn->prepare("INSERT INTO backups (filename, filesize, created_by) VALUES (?, ?, ?)");
$backupStmt->bind_param('sii', $backupFileName, $filesize, $userId);
$backupStmt->execute();

$action      = 'Auto Database Backup';
$description = "Auto backup created: {$backupFileName}";
$ipAddress   = 'SYSTEM';

$logStmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
$logStmt->bind_param('isss', $userId, $action, $description, $ipAddress);
$logStmt->execute();

// Optional: update next run time if column exists
if ($hasSystemConfig) {
    $colNext = $conn->query("SHOW COLUMNS FROM system_config LIKE 'auto_backup_next_run'");
    if ($colNext && $colNext->num_rows > 0) {
        $conn->query("UPDATE system_config SET auto_backup_next_run = DATE_ADD(NOW(), INTERVAL 1 DAY) WHERE id = 1");
    }
}

echo "Auto backup created: {$backupFileName}\n";
