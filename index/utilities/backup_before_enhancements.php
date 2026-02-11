<?php 
session_start();
include('../navbar.php');
require_once('../../config/db_connect.php');

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

if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $filePath = $backupDir . $file;
            $backups[] = array(
                'name' => $file,
                'size' => filesize($filePath),
                'date' => filemtime($filePath)
            );
        }
    }
    usort($backups, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Handle backup creation
if (isset($_POST['create_backup'])) {
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
        $files = scandir($backupDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $filePath = $backupDir . $file;
                $backups[] = array(
                    'name' => $file,
                    'size' => filesize($filePath),
                    'date' => filemtime($filePath)
                );
            }
        }
        usort($backups, function($a, $b) {
            return $b['date'] - $a['date'];
        });

    } catch (Exception $e) {
        $error_message = "Backup failed: " . $e->getMessage();
    }
}

// Handle restore
if (isset($_POST['restore']) && isset($_FILES['sql_file'])) {
    try {
        $file = $_FILES['sql_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed");
        }

        // Read SQL file
        $sql = file_get_contents($file['tmp_name']);
        
        // Execute SQL
        $conn->multi_query($sql);
        
        // Wait for all queries to complete
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());

        // Log activity
        $user_id = $_SESSION['user_id'];
        $action = 'Database Restore';
        $description = "Restored database from: {$file['name']}";
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $log_stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
        $log_stmt->execute();

        $success_message = "Database restored successfully!";

    } catch (Exception $e) {
        $error_message = "Restore failed: " . $e->getMessage();
    }
}

// Handle delete
if (isset($_POST['delete_file'])) {
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
        $files = scandir($backupDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $filePath = $backupDir . $file;
                $backups[] = array(
                    'name' => $file,
                    'size' => filesize($filePath),
                    'date' => filemtime($filePath)
                );
            }
        }
        usort($backups, function($a, $b) {
            return $b['date'] - $a['date'];
        });
    }
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
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .main-content {
            margin-left: 270px;
            padding: 32px;
            min-height: 100vh;
        }

        /* Page Header */
        .page-header {
            background: white;
            padding: 28px 32px;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 32px;
            border-left: 4px solid;
            border-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%) 1;
        }

        .page-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header p {
            color: #6c757d;
            margin: 0;
            font-size: 15px;
        }

        .page-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        /* Alert Messages */
        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 20px 24px;
            margin-bottom: 24px;
            display: flex;
            align-items: start;
            gap: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
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
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
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
            gap: 8px;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            margin-top: 12px;
            transition: all 0.3s ease;
        }

        .download-badge:hover {
            background: #218838;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        /* Action Cards */
        .action-card {
            background: white;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            height: 100%;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            border-color: rgba(102, 126, 234, 0.3);
        }

        .action-card-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
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
            font-size: 20px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 12px;
        }

        .action-card p {
            color: #6c757d;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        /* Buttons */
        .btn-custom {
            padding: 12px 28px;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 15px;
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

        /* File Input */
        .file-upload-wrapper {
            position: relative;
            margin-bottom: 20px;
        }

        .file-upload-label {
            display: block;
            padding: 24px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
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
            font-size: 40px;
            color: #667eea;
            margin-bottom: 12px;
        }

        .file-upload-text {
            color: #495057;
            font-weight: 500;
        }

        input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        /* Backup History */
        .history-card {
            background: white;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .history-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }

        .history-header h3 {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
            flex-grow: 1;
        }

        .backup-count {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        /* Backup Items */
        .backup-item {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .backup-item:hover {
            background: white;
            border-color: #667eea;
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .backup-icon-wrapper {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            flex-shrink: 0;
        }

        .backup-details {
            flex-grow: 1;
        }

        .backup-filename {
            font-weight: 700;
            color: #1a1a1a;
            font-size: 15px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .backup-meta {
            display: flex;
            gap: 20px;
            color: #6c757d;
            font-size: 13px;
        }

        .backup-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .backup-actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }

        .btn-action-small {
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-download-small {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-download-small:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-delete-small {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .btn-delete-small:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state-icon {
            font-size: 72px;
            color: #dee2e6;
            margin-bottom: 20px;
        }

        .empty-state-text {
            color: #6c757d;
            font-size: 16px;
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
            <div class="alert-custom alert-success-custom">
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

        <?php if (isset($error_message)): ?>
            <div class="alert-custom alert-error-custom">
                <div class="alert-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <strong>Error!</strong> <?= htmlspecialchars($error_message) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Action Cards -->
        <div class="row mb-4">
            <!-- Create Backup -->
            <div class="col-lg-6 mb-4">
                <div class="action-card">
                    <div class="action-card-icon backup-card-icon">
                        <i class="bi bi-cloud-download"></i>
                    </div>
                    <h3>Create Database Backup</h3>
                    <p>Generate a complete snapshot of your database. The backup file includes all tables, data, and structures.</p>
                    
                    <form method="post">
                        <button type="submit" name="create_backup" class="btn-custom btn-primary-custom">
                            <i class="bi bi-plus-circle"></i>
                            Create Backup Now
                        </button>
                    </form>
                </div>
            </div>

            <!-- Restore Database -->
            <div class="col-lg-6 mb-4">
                <div class="action-card">
                    <div class="action-card-icon restore-card-icon">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </div>
                    <h3>Restore Database</h3>
                    <p>Upload a backup SQL file to restore your database to a previous state. This will replace current data.</p>
                    
                    <form method="post" enctype="multipart/form-data" onsubmit="return confirm('⚠️ WARNING: This will replace your current database!\n\nMake sure you have a recent backup before proceeding.\n\nContinue with restore?');">
                        <div class="file-upload-wrapper">
                            <label class="file-upload-label" id="fileLabel">
                                <div class="file-upload-icon">
                                    <i class="bi bi-cloud-upload"></i>
                                </div>
                                <div class="file-upload-text" id="fileName">
                                    <strong>Choose SQL backup file</strong><br>
                                    <small>or drag and drop here</small>
                                </div>
                            </label>
                            <input type="file" name="sql_file" accept=".sql" required onchange="updateFileName(this)">
                        </div>
                        <button type="submit" name="restore" class="btn-custom btn-success-custom">
                            <i class="bi bi-upload"></i>
                            Restore Database
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Backup History -->
        <div class="history-card">
            <div class="history-header">
                <i class="bi bi-clock-history" style="font-size: 28px; color: #667eea;"></i>
                <h3>Backup History</h3>
                <span class="backup-count"><?= count($backups) ?> Backups</span>
            </div>
            
            <?php if (count($backups) > 0): ?>
                <div class="backup-list">
                    <?php foreach ($backups as $backup): ?>
                        <div class="backup-item">
                            <div class="backup-icon-wrapper">
                                <i class="bi bi-database"></i>
                            </div>
                            <div class="backup-details">
                                <div class="backup-filename">
                                    <i class="bi bi-file-earmark-zip"></i>
                                    <?= htmlspecialchars($backup['name']) ?>
                                </div>
                                <div class="backup-meta">
                                    <div class="backup-meta-item">
                                        <i class="bi bi-calendar3"></i>
                                        <?= date('M j, Y - g:i A', $backup['date']) ?>
                                    </div>
                                    <div class="backup-meta-item">
                                        <i class="bi bi-hdd"></i>
                                        <?= formatFileSize($backup['size']) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="backup-actions">
                                <a href="download_backup.php?file=<?= urlencode($backup['name']) ?>" 
                                   class="btn-action-small btn-download-small">
                                    <i class="bi bi-download"></i>
                                    Download
                                </a>
                                <form method="post" style="display: inline; margin: 0;">
                                    <input type="hidden" name="delete_file" value="<?= htmlspecialchars($backup['name']) ?>">
                                    <button type="submit" class="btn-action-small btn-delete-small" 
                                            onclick="return confirm('Are you sure you want to delete this backup?\n\n<?= htmlspecialchars($backup['name']) ?>\n\nThis action cannot be undone.')">
                                        <i class="bi bi-trash"></i>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <p class="empty-state-text">
                        No backup files found.<br>
                        Create your first backup to get started!
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// File upload UI update
function updateFileName(input) {
    const fileLabel = document.getElementById('fileLabel');
    const fileName = document.getElementById('fileName');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        fileName.innerHTML = `<strong>${file.name}</strong><br><small>Size: ${(file.size / 1024 / 1024).toFixed(2)} MB</small>`;
        fileLabel.classList.add('has-file');
    } else {
        fileName.innerHTML = '<strong>Choose SQL backup file</strong><br><small>or drag and drop here</small>';
        fileLabel.classList.remove('has-file');
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
