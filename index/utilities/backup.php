<?php 
session_start();

// Huwag mag-render ng full navbar/HTML kapag AJAX request (para malinis ang JSON response)
$isAjaxRequest = isset($_GET['ajax']) && $_GET['ajax'] === '1';

if (!$isAjaxRequest) {
    include('../navbar.php');
}

require_once('../../config/db_connect.php');

// CSRF token (shared with other admin pages)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_token'];

function backup_check_csrf() {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        return false;
    }
    return true;
}

// Read flash messages from restore_action.php (if any)
$success_message = $success_message ?? null;
$error_message = $error_message ?? null;
$warning_message = $warning_message ?? null;


if (isset($_SESSION['success']) && !$success_message) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error']) && !$error_message) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}
if (isset($_SESSION['warning']) && !$warning_message) {
    $warning_message = $_SESSION['warning'];
    unset($_SESSION['warning']);
}

// Create tables if they don't exist
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


// Get existing backups from file system
$backupDir = __DIR__ . '/backups/';
$backups = array();
$totalStorage = 0;
$lastBackupDate = null;

if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $filePath = $backupDir . $file;
            $filesize = filesize($filePath);
            $filedate = filemtime($filePath);
            
            $backups[] = array(
                'name' => $file,
                'size' => $filesize,
                'date' => $filedate
            );
            
            $totalStorage += $filesize;
            
            if ($lastBackupDate === null || $filedate > $lastBackupDate) {
                $lastBackupDate = $filedate;
            }
        }
    }
    usort($backups, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

// Enhanced file size formatter
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes) / log($k));
    
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

// Handle backup creation
if (isset($_POST['create_backup'])) {
    if (!backup_check_csrf()) {
        $error_message = 'Invalid session token. Please reload the page and try again.';
    } else {
    try {

        // Ensure backup directory exists
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        // Create backup filename
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
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }

        // Loop through tables
        foreach ($tables as $table) {
            $sqlContent .= "\n-- Table: {$table}\n";
            $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n\n";

            // Get CREATE TABLE
            $createTableResult = $conn->query("SHOW CREATE TABLE `{$table}`");
            $row = $createTableResult->fetch_row();
            $sqlContent .= $row[1] . ";\n\n";

            // Get table data
            $sqlContent .= "-- Data for table `{$table}`\n";
            $dataResult = $conn->query("SELECT * FROM `{$table}`");
            
            if ($dataResult->num_rows > 0) {
                while ($row = $dataResult->fetch_assoc()) {
                    $columns = array_keys($row);
                    $values = array_values($row);
                    
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
        file_put_contents($backupFilePath, $sqlContent);

        // Save to database
        $user_id = $_SESSION['user_id'];
        $filesize = filesize($backupFilePath);
        
        $backup_stmt = $conn->prepare("INSERT INTO backups (filename, filesize, created_by) VALUES (?, ?, ?)");
        $backup_stmt->bind_param("sii", $backupFileName, $filesize, $user_id);
        $backup_stmt->execute();

        // Log activity
        $action = 'Database Backup';
        $description = "Created backup: {$backupFileName}";
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $log_stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
        $log_stmt->execute();

        $success_message = "Backup created successfully!";
        $download_file = $backupFileName;

        // Reload backups list
        $backups = array();
        $totalStorage = 0;
        $lastBackupDate = null;
        $files = scandir($backupDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $filePath = $backupDir . $file;
                $filesize = filesize($filePath);
                $filedate = filemtime($filePath);
                
                $backups[] = array(
                    'name' => $file,
                    'size' => $filesize,
                    'date' => $filedate
                );
                
                $totalStorage += $filesize;
                
                if ($lastBackupDate === null || $filedate > $lastBackupDate) {
                    $lastBackupDate = $filedate;
                }
            }
        }
        usort($backups, function($a, $b) {
            return $b['date'] - $a['date'];
        });

    } catch (Exception $e) {
        $error_message = "Backup failed: " . $e->getMessage();
    }
  }
}

// Handle restore from existing backup file in /backups/ folder
if (isset($_POST['restore_file'])) {
    if (!backup_check_csrf()) {
        $error_message = 'Invalid session token. Please reload the page and try again.';
    } else {
        try {
            $filename = basename($_POST['restore_file']);
            $filepath = $backupDir . $filename;

            if (!file_exists($filepath) || pathinfo($filepath, PATHINFO_EXTENSION) !== 'sql') {
                throw new Exception('Selected backup file does not exist or is invalid.');
            }

            // Read SQL file
            $sql = file_get_contents($filepath);
            if ($sql === false || $sql === '') {
                throw new Exception('Backup SQL file is empty or unreadable.');
            }

            // Disable foreign key checks and autocommit
            if (!$conn->query('SET FOREIGN_KEY_CHECKS=0')) {
                throw new Exception('Failed to disable foreign key checks: ' . $conn->error);
            }
            if (!$conn->query('SET autocommit=0')) {
                throw new Exception('Failed to disable autocommit: ' . $conn->error);
            }

            // Execute multi-query with proper error checking
            if (!$conn->multi_query($sql)) {
                throw new Exception('Failed to execute SQL: ' . $conn->error);
            }

            // Process all results and check for errors
            $queryCount = 0;
            $errors = [];
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
                if ($conn->error) {
                    $errors[] = $conn->error;
                } else {
                    $queryCount++;
                }
                $hasMoreResults = $conn->more_results();
                if ($hasMoreResults && !$conn->next_result()) {
                    if ($conn->error) {
                        $errors[] = 'Failed to move to next query: ' . $conn->error;
                    }
                    break;
                }
            } while ($hasMoreResults);

            // Commit changes
            if (!$conn->query('COMMIT')) {
                throw new Exception('Failed to commit changes: ' . $conn->error);
            }

            // Re-enable foreign key checks and autocommit
            $conn->query('SET FOREIGN_KEY_CHECKS=1');
            $conn->query('SET autocommit=1');

            // If there were any errors during execution, surface them as warning/error
            if (count($errors) > 0) {
                $errorSummary = 'Restore completed with ' . count($errors) . ' error(s):\n' . implode("\n", array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $errorSummary .= "\n... and " . (count($errors) - 3) . ' more errors';
                }
                $warning_message = $errorSummary;
            }

            // Log activity
            $user_id = $_SESSION['user_id'];
            $action = 'Database Restore';
            $description = "Restored database from backup file: {$filename} ({$queryCount} queries executed)";
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $log_stmt = $conn->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)');
            $log_stmt->bind_param('isss', $user_id, $action, $description, $ip_address);
            $log_stmt->execute();

            $success_message = "Database restored successfully from {$filename} ({$queryCount} queries executed).";

        } catch (Exception $e) {
            $conn->query('ROLLBACK');
            $conn->query('SET FOREIGN_KEY_CHECKS=1');
            $conn->query('SET autocommit=1');
            $error_message = 'Restore failed: ' . $e->getMessage();
        }
    }
}






// Handle delete
if (isset($_POST['delete_file'])) {
    if (!backup_check_csrf()) {
        $error_message = 'Invalid session token. Please reload the page and try again.';
    } else {
    $filename = $_POST['delete_file'];
    $filepath = $backupDir . $filename;
    
    if (file_exists($filepath) && pathinfo($filepath, PATHINFO_EXTENSION) === 'sql') {

        unlink($filepath);
        
        // Log deletion
        $user_id = $_SESSION['user_id'];
        $action = 'Backup Deleted';
        $description = "Deleted backup: {$filename}";
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $log_stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
        $log_stmt->execute();
        
        $success_message = "Backup deleted successfully!";
        
        // Reload backups list
        $backups = array();
        $totalStorage = 0;
        $lastBackupDate = null;
        $files = scandir($backupDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $filePath = $backupDir . $file;
                $filesize = filesize($filePath);
                $filedate = filemtime($filePath);
                
                $backups[] = array(
                    'name' => $file,
                    'size' => $filesize,
                    'date' => $filedate
                );
                
                $totalStorage += $filesize;
                
                if ($lastBackupDate === null || $filedate > $lastBackupDate) {
                    $lastBackupDate = $filedate;
                }
            }
        }
        usort($backups, function($a, $b) {
            return $b['date'] - $a['date'];
        });
    }
  }
}

// ========= Filtering, sorting, pagination =========
$totalBackups = count($backups);

$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$minSize = $_GET['min_size'] ?? '';
$maxSize = $_GET['max_size'] ?? '';

$fromTs = $dateFrom ? strtotime($dateFrom . ' 00:00:00') : null;
$toTs = $dateTo ? strtotime($dateTo . ' 23:59:59') : null;
$minSizeBytes = is_numeric($minSize) ? (float)$minSize * 1024 * 1024 : null;
$maxSizeBytes = is_numeric($maxSize) ? (float)$maxSize * 1024 * 1024 : null;

$filteredBackups = array_filter($backups, function($b) use ($search, $fromTs, $toTs, $minSizeBytes, $maxSizeBytes) {
    if ($search !== '' && stripos($b['name'], $search) === false) {
        return false;
    }
    if ($fromTs && $b['date'] < $fromTs) return false;
    if ($toTs && $b['date'] > $toTs) return false;
    if ($minSizeBytes !== null && $b['size'] < $minSizeBytes) return false;
    if ($maxSizeBytes !== null && $b['size'] > $maxSizeBytes) return false;
    return true;
});

usort($filteredBackups, function($a, $b) use ($sort) {
    switch ($sort) {
        case 'oldest':
            return $a['date'] <=> $b['date'];
        case 'largest':
            return $b['size'] <=> $a['size'];
        case 'smallest':
            return $a['size'] <=> $b['size'];
        default:
            return $b['date'] <=> $a['date']; // newest
    }
});

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$totalFiltered = count($filteredBackups);
$totalPages = max(1, (int)ceil($totalFiltered / $perPage));
if ($page > $totalPages) { $page = $totalPages; }
$offset = ($page - 1) * $perPage;
$pagedBackups = array_slice($filteredBackups, $offset, $perPage);
$showingFrom = $totalFiltered ? $offset + 1 : 0;
$showingTo = $offset + count($pagedBackups);

// Storage limit + auto backup status from system_config
$maxStorageMb = 100;
$autoBackupStatus = 0;
$autoBackupNextRun = null;

// Try to read extended columns if they exist
$configCols = $conn->query("SHOW COLUMNS FROM system_config LIKE 'auto_backup_status'");
$hasAutoBackup = $configCols && $configCols->num_rows > 0;
$configColsLimit = $conn->query("SHOW COLUMNS FROM system_config LIKE 'backup_storage_limit_mb'");
$hasLimit = $configColsLimit && $configColsLimit->num_rows > 0;
$configColsNext = $conn->query("SHOW COLUMNS FROM system_config LIKE 'auto_backup_next_run'");
$hasNext = $configColsNext && $configColsNext->num_rows > 0;

if ($hasAutoBackup || $hasLimit || $hasNext) {
    $cfgRes = $conn->query("SELECT * FROM system_config WHERE id=1 LIMIT 1");
    if ($cfgRes && $cfgRes->num_rows > 0) {
        $cfg = $cfgRes->fetch_assoc();
        if ($hasAutoBackup) {
            $autoBackupStatus = (int)($cfg['auto_backup_status'] ?? 0);
        }
        if ($hasLimit && !empty($cfg['backup_storage_limit_mb'])) {
            $maxStorageMb = (int)$cfg['backup_storage_limit_mb'];
            if ($maxStorageMb <= 0) { $maxStorageMb = 100; }
        }
        if ($hasNext && !empty($cfg['auto_backup_next_run'])) {
            $autoBackupNextRun = $cfg['auto_backup_next_run'];
        }
    }
}

$maxStorageBytes = $maxStorageMb * 1024 * 1024;
$storagePercent = $maxStorageBytes > 0 ? min(100, round(($totalStorage / $maxStorageBytes) * 100, 1)) : 0;
$storageLimitReached = $maxStorageBytes > 0 && $totalStorage >= $maxStorageBytes;

if ($storageLimitReached) {
    $warning_message = ($warning_message ? $warning_message . "\n" : '') . 'Storage limit reached. Delete old backups before creating new ones.';
}

// AJAX: return only backup list + filters

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    ob_start();
    include __DIR__ . '/backup_list_partial.php';
    $html = ob_get_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'html' => $html,
        'page' => $page,
        'totalPages' => $totalPages,
        'total' => $totalFiltered,
        'showingFrom' => $showingFrom,
        'showingTo' => $showingTo,
    ]);
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Restore - <?= $assocName ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .main-content {
            margin-left: 270px;
            padding: 20px;
            min-height: 100vh;
        }

        /* Page Header - Compact */
        .page-header {
            background: white;
            padding: 16px 20px;
            border-radius: 10px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            margin-bottom: 20px;
            border-left: 4px solid;
            border-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%) 1;
        }

        .page-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0 0 4px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-header p {
            color: #6c757d;
            margin: 0;
            font-size: 13px;
        }

        .page-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        /* Alert Messages - Compact with auto-dismiss animation */
        .alert-custom {
            border-radius: 8px;
            border: none;
            padding: 14px 18px;
            margin-bottom: 16px;
            display: flex;
            align-items: start;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }

        .alert-custom.fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }

        .alert-success-custom {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }

        .alert-error-custom {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }

        .alert-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .alert-success-custom .alert-icon {
            background: #28a745;
            color: white;
        }

        .alert-error-custom .alert-icon {
            background: #dc3545;
            color: white;
        }

        .download-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin-top: 8px;
            transition: all 0.2s ease;
            font-size: 12px;
        }

        .download-badge:hover {
            background: #218838;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }

        /* Summary Dashboard - Minimalist */
        .summary-dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: white;
            border-radius: 10px;
            padding: 14px 18px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            transition: all 0.2s ease;
            border: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: rgba(102, 126, 234, 0.3);
        }

        .summary-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .summary-icon.purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .summary-icon.blue {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
        }

        .summary-icon.green {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .summary-content {
            flex-grow: 1;
        }

        .summary-value {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 2px;
            line-height: 1;
        }

        .summary-label {
            color: #6c757d;
            font-size: 12px;
            font-weight: 500;
            line-height: 1;
        }

        /* Action Cards - Minimalist */
        .action-card {
            background: white;
            border-radius: 10px;
            padding: 18px 20px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            transition: all 0.2s ease;
            border: 1px solid #e9ecef;
        }

        .action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: rgba(102, 126, 234, 0.3);
        }

        .action-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .action-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .backup-card-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .restore-card-icon {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .action-card h3 {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
            line-height: 1.2;
        }

        .action-card p {
            color: #6c757d;
            margin-bottom: 14px;
            line-height: 1.5;
            font-size: 13px;
        }

        /* Button Hierarchy - Compact */
        .btn-custom {
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-success-custom {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .btn-success-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            color: white;
        }

        /* File Input - Compact and Clear */
        .file-upload-wrapper {
            margin-bottom: 14px;
        }

        .file-upload-label {
            display: block;
            padding: 16px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .file-upload-label:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            border-color: #667eea;
        }

        .file-upload-label.has-file {
            background: linear-gradient(135deg, #e7f5ff 0%, #d0ebff 100%);
            border-color: #667eea;
            border-style: solid;
        }

        .file-upload-icon {
            font-size: 28px;
            color: #667eea;
            margin-bottom: 8px;
        }

        .file-upload-text {
            color: #495057;
            font-weight: 500;
            font-size: 13px;
        }

        .file-upload-wrapper input[type="file"] {
            display: none;
        }

        /* Backup History - Compact */
        .history-card {
            background: white;
            border-radius: 10px;
            padding: 18px 20px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            border: 1px solid #e9ecef;
        }

        .history-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e9ecef;
        }

        .history-header h3 {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
            flex-grow: 1;
        }

        .backup-count {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 12px;
        }

        /* Backup Items - Minimalist with Enhanced Hover */
        .backup-item {
            background: linear-gradient(135deg, #fafbfc 0%, #ffffff 100%);
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .backup-item:hover {
            background: white;
            border-color: #667eea;
            transform: translateX(6px) scale(1.005);
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.12);
        }

        .backup-icon-wrapper {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .backup-item:hover .backup-icon-wrapper {
            transform: rotate(360deg) scale(1.1);
        }

        .backup-details {
            flex-grow: 1;
            min-width: 0;
        }

        .backup-filename {
            font-weight: 600;
            color: #1a1a1a;
            font-size: 13px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .backup-meta {
            display: flex;
            gap: 14px;
            color: #6c757d;
            font-size: 11px;
            flex-wrap: wrap;
        }

        .backup-meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .backup-actions {
            display: flex;
            gap: 6px;
            flex-shrink: 0;
        }

        .btn-action-small {
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            border: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            white-space: nowrap;
        }

        /* Blue for Download */
        .btn-download-small {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
        }

        .btn-download-small:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
            text-decoration: none;
            color: white;
        }

        /* Red for Delete */
        .btn-delete-small {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .btn-delete-small:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
        }

        /* Enhanced Empty State - Compact */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .empty-state-icon {
            font-size: 64px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-15px);
            }
        }

        .empty-state-title {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .empty-state-text {
            color: #6c757d;
            font-size: 13px;
            line-height: 1.6;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .backup-item {
                flex-direction: column;
                align-items: start;
            }

            .backup-actions {
                width: 100%;
            }

            .btn-action-small {
                flex: 1;
            }

            .summary-dashboard {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .page-header h2 {
                font-size: 22px;
            }

            .action-card {
                padding: 20px;
            }

            .backup-meta {
                flex-direction: column;
                gap: 8px;
            }

            .summary-value {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex align-items-center">
                <div class="page-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <h2>Backup & Restore System</h2>
                    <p>Protect your data with automated backups and easy restoration</p>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert-custom alert-success-custom" id="successAlert">
                <div class="alert-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>Success!</strong> <?= htmlspecialchars($success_message) ?>
                    <?php if (isset($download_file)): ?>
                        <a href="download_backup.php?file=<?= urlencode($download_file) ?>" class="download-badge">
                            <i class="bi bi-download"></i>
                            Download: <?= htmlspecialchars($download_file) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($warning_message)): ?>
            <div class="alert-custom alert-error-custom" id="warningAlert" style="background: linear-gradient(135deg,#fff3cd 0%,#ffeeba 100%); color:#856404;">
                <div class="alert-icon" style="background:#ffc107;">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </div>
                <div>
                    <strong>Warning!</strong> <?= htmlspecialchars($warning_message) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert-custom alert-error-custom" id="errorAlert">
                <div class="alert-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <strong>Error!</strong> <?= htmlspecialchars($error_message) ?>
                </div>
            </div>
        <?php endif; ?>


        <!-- Summary Dashboard -->
        <div class="summary-dashboard">
            <div class="summary-card">
                <div class="summary-icon purple">
                    <i class="bi bi-database-fill"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-value"><?= count($backups) ?></div>
                    <div class="summary-label">Total Backups</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon blue">
                    <i class="bi bi-hdd-fill"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-value"><?= formatFileSize($totalStorage) ?> / <?= formatFileSize($maxStorageBytes) ?></div>
                    <div class="summary-label">Storage Used (<?= $storagePercent ?>%)</div>
                    <div class="progress mt-2" style="height: 8px;">
                        <?php
                        $barClass = 'bg-success';
                        if ($storagePercent > 90) {
                            $barClass = 'bg-danger';
                        } elseif ($storagePercent > 70) {
                            $barClass = 'bg-warning';
                        }
                        ?>
                        <div class="progress-bar <?= $barClass ?>" role="progressbar" style="width: <?= $storagePercent ?>%;" aria-valuenow="<?= $storagePercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>


            <div class="summary-card">
                <div class="summary-icon green">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-value"><?= $lastBackupDate ? date('M j', $lastBackupDate) : 'N/A' ?></div>
                    <div class="summary-label">Last Backup<?= $lastBackupDate ? ' - ' . date('g:i A', $lastBackupDate) : '' ?></div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon <?= $autoBackupStatus ? 'green' : 'purple' ?>">
                    <i class="<?= $autoBackupStatus ? 'bi bi-cloud-check' : 'bi bi-cloud-slash' ?>"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-value">
                        <?php if ($autoBackupStatus): ?>
                            <span class="badge bg-success" data-bs-toggle="tooltip" title="Runs automatically based on system schedule.">Auto Backup Enabled</span>
                        <?php else: ?>
                            <span class="badge bg-danger" data-bs-toggle="tooltip" title="Automatic backups are currently disabled.">Auto Backup Disabled</span>
                        <?php endif; ?>
                    </div>
                    <div class="summary-label">
                        <?php if ($autoBackupStatus && $autoBackupNextRun): ?>
                            Next run: <?= date('M j, g:i A', strtotime($autoBackupNextRun)) ?>
                        <?php elseif ($autoBackupStatus): ?>
                            Next run: Scheduled
                        <?php else: ?>
                            <a href="../settings/config.php" class="link-primary" style="text-decoration: none; font-weight: 500;">
                                Configure in System Settings
                                <i class="bi bi-box-arrow-up-right ms-1"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Action Cards -->
        <div class="row mb-3">
            <!-- Create Backup -->
            <div class="col-lg-6 mb-3">
                <div class="action-card">
                    <div class="action-card-header">
                        <div class="action-card-icon backup-card-icon">
                            <i class="bi bi-cloud-download"></i>
                        </div>
                        <h3>Create Database Backup</h3>
                    </div>
                    <p>Generate a complete snapshot of your database. The backup file includes all tables, data, and structures.</p>
                    
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <button type="submit" name="create_backup" class="btn-custom btn-primary-custom" <?= $storageLimitReached ? 'disabled' : '' ?> data-bs-toggle="tooltip" title="<?= $storageLimitReached ? 'Storage limit reached. Delete old backups to create new ones.' : 'Create a new full database backup.' ?>">
                            <i class="bi bi-plus-circle"></i>
                            Create Backup Now
                        </button>
                    </form>

                </div>
            </div>

            <!-- Restore Database -->
            <div class="col-lg-6 mb-3">
                <div class="action-card">
                    <div class="action-card-header">
                        <div class="action-card-icon restore-card-icon">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </div>
                        <h3>Restore Database</h3>
                    </div>
                    <p class="mb-2">To restore, choose one of the backups below and click <strong>Restore</strong> on that row.</p>
                    <?php if (!empty($backups)): ?>
                        <?php $latest = $backups[0]; ?>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2">
                            <div>
                                <div style="font-size:13px; color:#6c757d;">Latest backup:</div>
                                <div style="font-size:13px; font-weight:600;">
                                    <?= htmlspecialchars($latest['name']) ?>
                                </div>
                                <div style="font-size:12px; color:#6c757d;">
                                    <?= date('M j, Y - g:i A', $latest['date']) ?> · <?= formatFileSize($latest['size']) ?>
                                </div>
                            </div>
                            <button type="button" class="btn-custom btn-success-custom" onclick="confirmRestoreFromBackup('<?= htmlspecialchars($latest['name'], ENT_QUOTES) ?>')">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                Restore Latest Backup
                            </button>
                        </div>
                    <?php else: ?>
                        <p class="text-muted" style="font-size:13px;">No backups yet. Create one on the left before restoring.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Backup History -->
        <div class="history-card">
            <div class="history-header">
                <i class="bi bi-clock-history" style="font-size: 20px; color: #667eea;"></i>
                <h3>Backup History</h3>
                <span class="backup-count"><?= $totalFiltered ?> Backups</span>
            </div>
            <div id="backupFiltersAndList">
                <?php include __DIR__ . '/backup_list_partial.php'; ?>
            </div>
        </div>

    </div>
</div>

<!-- Hidden form for restore & delete actions -->
<form id="restoreForm" method="post" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <input type="hidden" name="restore_file" id="restoreFileName">
</form>

<form id="deleteForm" method="post" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <input type="hidden" name="delete_file" id="deleteFileName">
</form>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>

<script>
// Show restore success SweetAlert
<?php if (isset($restore_success) && $restore_success === true): ?>
window.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: 'Restore Successful!',
        html: 'Database has been restored successfully from:<br><br><strong><?= htmlspecialchars($restore_filename) ?></strong><br><br><small class="text-muted"><?= $restore_query_count ?> queries executed</small>',
        icon: 'success',
        confirmButtonColor: '#28a745',
        confirmButtonText: 'OK',
        customClass: {
            confirmButton: 'btn btn-success px-4 py-2'
        },
        buttonsStyling: false
    }).then(() => {
        // Reload the page to show updated backup list
        window.location.href = 'backup.php';
    });
});
<?php endif; ?>

// Show restore error SweetAlert
<?php if (isset($restore_error) && $restore_error === true): ?>
window.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: 'Restore Failed!',
        html: 'An error occurred while restoring the database:<br><br><div style="text-align: left; background: #f8f9fa; padding: 12px; border-radius: 6px; font-size: 12px; color: #dc3545; max-height: 300px; overflow-y: auto; white-space: pre-wrap; font-family: monospace;"><code><?= htmlspecialchars($restore_error_msg) ?></code></div>',
        icon: 'error',
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'OK',
        width: '650px',
        customClass: {
            confirmButton: 'btn btn-danger px-4 py-2'
        },
        buttonsStyling: false
    });
});
<?php endif; ?>

// Auto-dismiss success alert after 4 seconds
window.addEventListener('DOMContentLoaded', function() {
    const successAlert = document.getElementById('successAlert');
    if (successAlert) {
        setTimeout(function() {
            successAlert.classList.add('fade-out');
            setTimeout(function() {
                successAlert.remove();
            }, 500);
        }, 4000);
    }

    // Enable Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    attachBackupFilterListeners();
});

let backupSearchTimer = null;

function getBackupFilters() {
    const wrapper = document.getElementById('backupFiltersAndList');
    if (!wrapper) return {};
    return {
        search: wrapper.querySelector('#backupSearch')?.value || '',
        date_from: wrapper.querySelector('#backupDateFrom')?.value || '',
        date_to: wrapper.querySelector('#backupDateTo')?.value || '',
        min_size: wrapper.querySelector('#backupMinSize')?.value || '',
        max_size: wrapper.querySelector('#backupMaxSize')?.value || '',
        sort: wrapper.querySelector('#backupSort')?.value || 'newest',
    };
}

function attachBackupFilterListeners() {
    const wrapper = document.getElementById('backupFiltersAndList');
    if (!wrapper) return;

    const searchInput = wrapper.querySelector('#backupSearch');
    if (searchInput && !searchInput.dataset.bound) {
        searchInput.dataset.bound = '1';

        // Live search habang nagta-type
        searchInput.addEventListener('input', function() {
            if (backupSearchTimer) clearTimeout(backupSearchTimer);
            backupSearchTimer = setTimeout(function() {
                fetchBackupList(1);
            }, 400);
        });

        // Search kapag pinindot Enter sa keyboard
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (backupSearchTimer) clearTimeout(backupSearchTimer);
                fetchBackupList(1);
            }
        });
    }

    // Search kapag pinindot yung Search button
    const searchBtn = wrapper.querySelector('#backupSearchBtn');
    if (searchBtn && !searchBtn.dataset.bound) {
        searchBtn.dataset.bound = '1';
        searchBtn.addEventListener('click', function() {
            if (backupSearchTimer) clearTimeout(backupSearchTimer);
            fetchBackupList(1);
        });
    }

    const filterInputs = wrapper.querySelectorAll('.backup-filter-input');
    filterInputs.forEach(function(input) {
        if (!input.dataset.bound) {
            input.dataset.bound = '1';
            input.addEventListener('change', function() {
                fetchBackupList(1);
            });
        }
    });

    // Pagination buttons
    wrapper.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-page]');
        if (btn) {
            e.preventDefault();
            const page = parseInt(btn.getAttribute('data-page'), 10);
            if (!isNaN(page) && page > 0) {
                fetchBackupList(page);
            }
        }
    });
}

function fetchBackupList(page) {
    const filters = getBackupFilters();
    const params = new URLSearchParams({ ajax: '1', page: String(page) });
    Object.keys(filters).forEach(function(key) {
        if (filters[key] !== '') {
            params.append(key, filters[key]);
        }
    });

    fetch('backup.php?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            const wrapper = document.getElementById('backupFiltersAndList');
            if (wrapper && data.html) {
                wrapper.innerHTML = data.html;
                attachBackupFilterListeners();
            }
        })
        .catch(function(err) {
            console.error('Failed to fetch backups list', err);
        });
}

// Restore from backup history
function confirmRestoreFromBackup(filename) {

    Swal.fire({
        title: 'Restore Database?',
        html: `<strong style="color: #dc3545;">⚠️ WARNING</strong><br><br>This will completely replace your current database with the backup:<br><br><code>${filename}</code>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-check-circle"></i> Yes, Restore It',
        cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-success px-4 py-2',
            cancelButton: 'btn btn-secondary px-4 py-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            let timerInterval;
            Swal.fire({
                title: 'Restoring Database...',
                html: 'Please wait while we restore your database<br><br><b></b> seconds elapsed',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                    const b = Swal.getHtmlContainer().querySelector('b');
                    let seconds = 0;
                    timerInterval = setInterval(() => {
                        seconds++;
                        b.textContent = seconds;
                    }, 1000);
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            });

            document.getElementById('restoreFileName').value = filename;
            setTimeout(() => {
                document.getElementById('restoreForm').submit();
            }, 100);
        }
    });
}

// SweetAlert confirmation for delete
function confirmDelete(filename) {

    Swal.fire({
        title: 'Delete Backup?',
        html: `Are you sure you want to delete this backup?<br><br><strong>${filename}</strong><br><br>This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash"></i> Yes, Delete It',
        cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-danger px-4 py-2',
            cancelButton: 'btn btn-secondary px-4 py-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteFileName').value = filename;
            document.getElementById('deleteForm').submit();
        }
    });
}
</script>

</body>
</html>
