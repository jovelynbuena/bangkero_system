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

        $success_message = "✅ Backup created successfully! File: {$backupFileName}";
        $download_file = $backupFileName;

    } catch (Exception $e) {
        $error_message = "❌ Backup failed: " . $e->getMessage();
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

        $success_message = "✅ Database restored successfully from: {$file['name']}";

    } catch (Exception $e) {
        $error_message = "❌ Restore failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Restore</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .message {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 20px;
        }

        .section p {
            color: #666;
            margin-bottom: 15px;
        }

        form {
            margin-top: 15px;
        }

        input[type="file"] {
            display: block;
            padding: 10px;
            margin-bottom: 15px;
            border: 2px dashed #ddd;
            border-radius: 5px;
            width: 100%;
            background: #f9f9f9;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
        }

        button:hover {
            background-color: #0056b3;
        }

        button.restore {
            background-color: #28a745;
        }

        button.restore:hover {
            background-color: #218838;
        }

        .backup-list {
            margin-top: 20px;
        }

        .backup-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .backup-info {
            flex: 1;
        }

        .backup-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .backup-meta {
            font-size: 13px;
            color: #666;
        }

        .backup-actions {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 14px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }

        .btn-download {
            background-color: #007bff;
            color: white;
        }

        .btn-download:hover {
            background-color: #0056b3;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .download-link {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .download-link:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="main-content">
    <div class="container">
        <h1>🗄️ Backup & Restore System</h1>

        <?php if (isset($success_message)): ?>
            <div class="message success">
                <?php echo $success_message; ?>
                <?php if (isset($download_file)): ?>
                    <a href="download_backup.php?file=<?php echo urlencode($download_file); ?>" class="download-link">
                        📥 Download Backup
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="message error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Create Backup Section -->
        <div class="section">
            <h2>📦 Create Database Backup</h2>
            <p>Generate a complete backup of your database. The backup file will be saved and available for download.</p>
            
            <form method="post">
                <button type="submit" name="create_backup">Create Backup Now</button>
            </form>
        </div>

        <!-- Restore Section -->
        <div class="section">
            <h2>♻️ Restore Database</h2>
            <p>Upload a backup SQL file to restore your database to a previous state.</p>
            
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="sql_file" accept=".sql" required>
                <button type="submit" name="restore" class="restore">Restore Database</button>
            </form>
        </div>

        <!-- Backup History -->
        <div class="section">
            <h2>📋 Backup History</h2>
            
            <?php if (count($backups) > 0): ?>
                <div class="backup-list">
                    <?php foreach ($backups as $backup): ?>
                        <div class="backup-item">
                            <div class="backup-info">
                                <div class="backup-name">📄 <?php echo htmlspecialchars($backup['name']); ?></div>
                                <div class="backup-meta">
                                    🗓️ <?php echo date('F j, Y - g:i A', $backup['date']); ?> | 
                                    💾 <?php echo formatFileSize($backup['size']); ?>
                                </div>
                            </div>
                            <div class="backup-actions">
                                <a href="download_backup.php?file=<?php echo urlencode($backup['name']); ?>" 
                                   class="btn-small btn-download">
                                    Download
                                </a>
                                <form method="post" style="display: inline; margin: 0;">
                                    <input type="hidden" name="delete_file" value="<?php echo htmlspecialchars($backup['name']); ?>">
                                    <button type="submit" class="btn-small btn-delete" 
                                            onclick="return confirm('Delete this backup?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>📭 No backup files found. Create your first backup to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>

<?php
// Handle delete after page render
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
        
        echo "<script>alert('Backup deleted successfully!'); window.location.href='backup_new.php';</script>";
    }
}
?>
