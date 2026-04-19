<?php
ob_start();
session_start();

// Detect AJAX requests (GET or POST)
$isAjaxRequest = (isset($_GET['ajax']) && $_GET['ajax'] === '1') || 
                 (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');

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

// Helper function to parse SQL file into individual queries - MUST BE DEFINED BEFORE USE
function parseSqlFile($sql) {
    $queries = [];
    $currentQuery = '';
    $length = strlen($sql);
    $inString = false;
    $stringChar = null;
    $escapeNext = false;
    $inComment = false;
    $commentType = null;
    
    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $nextChar = ($i + 1 < $length) ? $sql[$i + 1] : null;
        
        if ($escapeNext) {
            $currentQuery .= $char;
            $escapeNext = false;
            continue;
        }
        
        if ($char === '\\') {
            $currentQuery .= $char;
            $escapeNext = true;
            continue;
        }
        
        if (!$inString && !$inComment) {
            if ($char === '-' && $nextChar === '-') {
                $inComment = true;
                $commentType = 'line';
                continue;
            }
            if ($char === '/' && $nextChar === '*') {
                $inComment = true;
                $commentType = 'block';
                $i++;
                continue;
            }
        } elseif ($inComment) {
            if ($commentType === 'line' && ($char === "\n" || $char === "\r")) {
                $inComment = false;
                $commentType = null;
            } elseif ($commentType === 'block' && $char === '*' && $nextChar === '/') {
                $inComment = false;
                $commentType = null;
                $i++;
            }
            continue;
        }
        
        if (!$inString && ($char === "'" || $char === '"' || $char === '`')) {
            $inString = true;
            $stringChar = $char;
            $currentQuery .= $char;
        } elseif ($inString && $char === $stringChar) {
            $inString = false;
            $stringChar = null;
            $currentQuery .= $char;
        } else {
            $currentQuery .= $char;
        }
        
        if (!$inString && !$inComment && $char === ';') {
            $trimmed = trim($currentQuery);
            if (!empty($trimmed) && !preg_match('/^\s*(--|#|\/\*)/', $trimmed)) {
                $queries[] = $trimmed;
            }
            $currentQuery = '';
        }
    }
    
    $trimmed = trim($currentQuery);
    if (!empty($trimmed) && !preg_match('/^\s*(--|#|\/\*)/', $trimmed)) {
        $queries[] = $trimmed;
    }
    
    return $queries;
}

// Include navbar only for non-AJAX requests - moved to after <body>

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
$totalStorage = 0;   // SQL-only storage (used for storage limit check)
$lastBackupDate = null;

if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($ext === 'sql' || $ext === 'zip') {
            $filePath = $backupDir . $file;
            $filesize = filesize($filePath);
            $filedate = filemtime($filePath);

            $backups[] = array(
                'name' => $file,
                'size' => $filesize,
                'date' => $filedate,
                'type' => $ext,
            );

            // Only count .sql files toward the storage limit
            if ($ext === 'sql') {
                $totalStorage += $filesize;
            }

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

// Handle system backup (DB + files zip) — responds with JSON for iframe AJAX
if (isset($_POST['create_system_backup'])) {
    $isSystemBackupRequest = true;
    if (!backup_check_csrf()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid session token. Please reload the page and try again.']);
        exit;
    } else {
        try {
            if (!class_exists('ZipArchive')) {
                throw new Exception('ZipArchive is not available on this server. Please enable the zip extension in PHP.');
            }

            $systemBackupDir = __DIR__ . '/backups/';
            if (!file_exists($systemBackupDir)) {
                mkdir($systemBackupDir, 0777, true);
            }

            $zipFileName = 'system_backup_' . date('Y-m-d_H-i-s') . '.zip';
            $zipFilePath = $systemBackupDir . $zipFileName;

            // --- Step 1: Generate SQL dump in memory ---
            $dbResult = $conn->query("SELECT DATABASE()");
            $currentDb = $dbResult->fetch_row()[0];

            $sqlContent  = "-- System Backup - Database Dump\n";
            $sqlContent .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
            $sqlContent .= "-- Database: {$currentDb}\n\n";
            $sqlContent .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $sqlContent .= "SET time_zone = \"+00:00\";\n\n";

            $tables = [];
            $result = $conn->query("SHOW TABLES");
            while ($row = $result->fetch_row()) { $tables[] = $row[0]; }

            foreach ($tables as $table) {
                $sqlContent .= "\n-- Table: {$table}\n";
                $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n\n";
                $createRes = $conn->query("SHOW CREATE TABLE `{$table}`");
                $row = $createRes->fetch_row();
                $sqlContent .= $row[1] . ";\n\n";
                $sqlContent .= "-- Data for table `{$table}`\n";
                $dataRes = $conn->query("SELECT * FROM `{$table}`");
                if ($dataRes->num_rows > 0) {
                    while ($row = $dataRes->fetch_assoc()) {
                        $cols = array_keys($row);
                        $vals = array_map(function($v) use ($conn) {
                            return $v === null ? 'NULL' : "'" . $conn->real_escape_string($v) . "'";
                        }, array_values($row));
                        $sqlContent .= "INSERT INTO `{$table}` (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', $vals) . ");\n";
                    }
                    $sqlContent .= "\n";
                }
            }

            // --- Step 2: Create ZIP ---
            $zip = new ZipArchive();
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new Exception('Cannot create zip file. Check folder permissions.');
            }

            // Add SQL dump
            $zip->addFromString('database_dump.sql', $sqlContent);

            // Root of the project (bangkero_system)
            $projectRoot = realpath(__DIR__ . '/../../');

            // Folders/files to include
            $includeDirs = ['index', 'uploads', 'config', 'css', 'images'];
            $includeFiles = ['composer.json', 'composer.lock'];

            // Folders to exclude (relative to project root)
            $excludeDirs = [
                realpath($projectRoot . '/vendor'),
                realpath($projectRoot . '/index/utilities/backups'),
                realpath($projectRoot . '/node_modules'),
            ];

            // Helper: recursive add to zip, skipping excluded dirs
            function addDirToZip(ZipArchive $zip, $dirPath, $zipPrefix, array $excludeDirs) {
                $dirPath = rtrim($dirPath, '/\\');
                if (!is_dir($dirPath)) return;
                $realDir = realpath($dirPath);
                // Skip excluded
                foreach ($excludeDirs as $ex) {
                    if ($ex && strpos($realDir . DIRECTORY_SEPARATOR, $ex . DIRECTORY_SEPARATOR) === 0) return;
                    if ($ex && $realDir === $ex) return;
                }
                $items = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($items as $item) {
                    $realItem = realpath($item->getPathname());
                    // Skip excluded
                    $skip = false;
                    foreach ($excludeDirs as $ex) {
                        if ($ex && ($realItem === $ex || strpos($realItem . DIRECTORY_SEPARATOR, $ex . DIRECTORY_SEPARATOR) === 0)) {
                            $skip = true;
                            break;
                        }
                    }
                    if ($skip) continue;
                    $relativePath = $zipPrefix . '/' . substr($item->getPathname(), strlen($dirPath) + 1);
                    $relativePath = str_replace('\\', '/', $relativePath);
                    if ($item->isDir()) {
                        $zip->addEmptyDir($relativePath);
                    } else {
                        $zip->addFile($item->getPathname(), $relativePath);
                    }
                }
            }

            foreach ($includeDirs as $dir) {
                $fullPath = $projectRoot . '/' . $dir;
                if (is_dir($fullPath)) {
                    addDirToZip($zip, $fullPath, $dir, $excludeDirs);
                }
            }

            foreach ($includeFiles as $file) {
                $fullPath = $projectRoot . '/' . $file;
                if (file_exists($fullPath)) {
                    $zip->addFile($fullPath, $file);
                }
            }

            $zip->close();

            // Log activity
            $user_id = $_SESSION['user_id'];
            $fsize = filesize($zipFilePath);
            $action = 'System Backup';
            $description = "Created system backup: {$zipFileName}";
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
            $log_stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
            $log_stmt->execute();

            $success_message = "System backup created successfully! (" . round($fsize / 1024 / 1024, 2) . " MB)";
            $download_zip = $zipFileName;

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'file' => $zipFileName, 'size' => round($fsize / 1024 / 1024, 2)]);
            exit;

        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}

// Handle system restore from uploaded ZIP or from history — responds with JSON
if (isset($_POST['restore_system_zip'])) {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');
    if (!backup_check_csrf()) {
        echo json_encode(['success' => false, 'error' => 'Invalid session token. Please reload the page and try again.']);
        exit;
    }
    try {
        if (!class_exists('ZipArchive')) {
            throw new Exception('ZipArchive is not available on this server.');
        }

        // Determine source: from history (server-side file) or uploaded file
        $fromHistory = !empty($_POST['restore_from_history']) && !empty($_POST['zip_filename']);

        if ($fromHistory) {
            $origName = basename($_POST['zip_filename']);
            if (strtolower(pathinfo($origName, PATHINFO_EXTENSION)) !== 'zip') {
                throw new Exception('Only .zip system backup files are accepted.');
            }
            $tmpFile = __DIR__ . '/backups/' . $origName;
            if (!file_exists($tmpFile)) {
                throw new Exception('Backup file not found on server: ' . $origName);
            }
        } else {
            if (empty($_FILES['zip_file']) || $_FILES['zip_file']['error'] !== UPLOAD_ERR_OK) {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE   => 'File too large (exceeds server limit).',
                    UPLOAD_ERR_FORM_SIZE  => 'File too large (exceeds form limit).',
                    UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
                    UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                    UPLOAD_ERR_CANT_WRITE => 'Cannot write file to disk.',
                ];
                $errCode = $_FILES['zip_file']['error'] ?? UPLOAD_ERR_NO_FILE;
                throw new Exception($uploadErrors[$errCode] ?? 'Upload failed (error ' . $errCode . ').');
            }
            $tmpFile = $_FILES['zip_file']['tmp_name'];
            $origName = basename($_FILES['zip_file']['name']);
            if (strtolower(pathinfo($origName, PATHINFO_EXTENSION)) !== 'zip') {
                throw new Exception('Only .zip system backup files are accepted.');
            }
        }

        // Open ZIP
        $zip = new ZipArchive();
        if ($zip->open($tmpFile) !== true) {
            throw new Exception('Cannot open ZIP file. It may be corrupted.');
        }

        // Must contain database_dump.sql
        if ($zip->locateName('database_dump.sql') === false) {
            $zip->close();
            throw new Exception('Invalid system backup ZIP — database_dump.sql not found inside.');
        }

        $projectRoot = realpath(__DIR__ . '/../../');

        // --- Step 1: Extract files (skip database_dump.sql, extract everything else) ---
        $extractedFiles = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if ($entryName === 'database_dump.sql') continue;

            // Safety: prevent path traversal
            $safeName = ltrim(str_replace(['../', '..\\', '..'], '', $entryName), '/\\');
            if (empty($safeName)) continue;

            $destPath = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $safeName);

            // Create directory if needed
            $destDir = substr($entryName, -1) === '/' ? $destPath : dirname($destPath);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0777, true);
            }

            if (substr($entryName, -1) !== '/') {
                $content = $zip->getFromIndex($i);
                if ($content !== false) {
                    file_put_contents($destPath, $content);
                    $extractedFiles++;
                }
            }
        }

        // --- Step 2: Restore database from database_dump.sql ---
        $sqlContent = $zip->getFromName('database_dump.sql');  // must be BEFORE close()
        $zip->close();  // close AFTER reading

        if (empty(trim($sqlContent))) {
            throw new Exception('database_dump.sql inside the ZIP is empty.');
        }

        $queries = parseSqlFile($sqlContent);
        $queryCount = 0;
        $errors = [];

        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        foreach ($queries as $query) {
            if (!$conn->query($query)) {
                $errors[] = $conn->error;
            } else {
                $queryCount++;
            }
        }
        $conn->query("SET FOREIGN_KEY_CHECKS=1");

        // Log activity
        $user_id = $_SESSION['user_id'] ?? 0;
        $action = 'System Restore';
        $description = "Restored system from ZIP: {$origName} ({$extractedFiles} files, {$queryCount} SQL queries)";
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        if ($log_stmt) {
            $log_stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
            $log_stmt->execute();
        }

        $warningText = !empty($errors) ? implode('; ', array_slice($errors, 0, 5)) : null;

        echo json_encode([
            'success'        => true,
            'file'           => $origName,
            'extracted_files'=> $extractedFiles,
            'query_count'    => $queryCount,
            'warnings'       => $warningText,
        ]);
        exit;

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
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
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($ext === 'sql' || $ext === 'zip') {
                $filePath = $backupDir . $file;
                $filesize = filesize($filePath);
                $filedate = filemtime($filePath);
                $backups[] = array('name' => $file, 'size' => $filesize, 'date' => $filedate, 'type' => $ext);
                if ($ext === 'sql') $totalStorage += $filesize;
                if ($lastBackupDate === null || $filedate > $lastBackupDate) $lastBackupDate = $filedate;
            }
        }
        usort($backups, function($a, $b) { return $b['date'] - $a['date']; });

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
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        $restoreResults = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        try {
            $filename = basename($_POST['restore_file']);
            $filepath = $backupDir . $filename;

            if (!file_exists($filepath) || pathinfo($filepath, PATHINFO_EXTENSION) !== 'sql') {
                throw new Exception('Selected backup file does not exist or is invalid.');
            }

            $sql = file_get_contents($filepath);
            if ($sql === false || $sql === '') {
                throw new Exception('Backup SQL file is empty or unreadable.');
            }

            // Parse SQL into individual statements
            $queries = parseSqlFile($sql);
            
            // Execute each query individually with error tracking
            foreach ($queries as $index => $query) {
                $query = trim($query);
                if (empty($query)) continue;
                
                if (!$conn->query($query)) {
                    $restoreResults['failed']++;
                    $errorPreview = substr($query, 0, 80) . (strlen($query) > 80 ? '...' : '');
                    $restoreResults['errors'][] = "Query " . ($index + 1) . ": " . $conn->error . " | Preview: " . $errorPreview;
                } else {
                    $restoreResults['success']++;
                }
            }

            // Log activity
            $user_id = $_SESSION['user_id'];
            $action = 'Database Restore';
            $description = "Restored from {$filename}: {$restoreResults['success']} success, {$restoreResults['failed']} failed";
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $log_stmt = $conn->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)');
            $log_stmt->bind_param('isss', $user_id, $action, $description, $ip_address);
            $log_stmt->execute();

            // Build result message
            if ($restoreResults['failed'] === 0) {
                $success_message = "Database restored successfully! {$restoreResults['success']} queries executed.";
            } elseif ($restoreResults['success'] > 0) {
                $success_message = "Partial restore completed. {$restoreResults['success']} queries succeeded, {$restoreResults['failed']} failed.";
                $warning_message = "Some queries failed:\n" . implode("\n", array_slice($restoreResults['errors'], 0, 5));
                if (count($restoreResults['errors']) > 5) {
                    $warning_message .= "\n... and " . (count($restoreResults['errors']) - 5) . " more errors";
                }
            } else {
                throw new Exception("All queries failed. First error: " . ($restoreResults['errors'][0] ?? 'Unknown'));
            }

        } catch (Exception $e) {
            $error_message = 'Restore failed: ' . $e->getMessage();
        } finally {
            $conn->query('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}

// Handle restore from uploaded SQL file
if (isset($_POST['restore_sql_upload'])) {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');
    if (!backup_check_csrf()) {
        echo json_encode(['success' => false, 'error' => 'Invalid session token. Please reload the page and try again.']);
        exit;
    }
    $restoreResults = ['success' => 0, 'failed' => 0, 'errors' => []];
    try {
        if (empty($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE   => 'File too large (exceeds server limit).',
                UPLOAD_ERR_FORM_SIZE  => 'File too large (exceeds form limit).',
                UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Cannot write file to disk.',
            ];
            $errCode = $_FILES['sql_file']['error'] ?? UPLOAD_ERR_NO_FILE;
            throw new Exception($uploadErrors[$errCode] ?? 'Upload failed (error ' . $errCode . ').');
        }
        $origName = basename($_FILES['sql_file']['name']);
        if (strtolower(pathinfo($origName, PATHINFO_EXTENSION)) !== 'sql') {
            throw new Exception('Only .sql backup files are accepted.');
        }
        $sql = file_get_contents($_FILES['sql_file']['tmp_name']);
        if ($sql === false || trim($sql) === '') {
            throw new Exception('Uploaded SQL file is empty or unreadable.');
        }
        $queries = parseSqlFile($sql);
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        foreach ($queries as $index => $query) {
            $query = trim($query);
            if (empty($query)) continue;
            if (!$conn->query($query)) {
                $restoreResults['failed']++;
                $restoreResults['errors'][] = "Query " . ($index + 1) . ": " . $conn->error;
            } else {
                $restoreResults['success']++;
            }
        }
        $conn->query('SET FOREIGN_KEY_CHECKS=1');

        // Log activity
        $user_id = $_SESSION['user_id'] ?? 0;
        $action = 'Database Restore (Upload)';
        $description = "Restored uploaded SQL: {$origName} — {$restoreResults['success']} success, {$restoreResults['failed']} failed";
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $log_stmt = $conn->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)');
        if ($log_stmt) { $log_stmt->bind_param('isss', $user_id, $action, $description, $ip_address); $log_stmt->execute(); }

        $warningText = !empty($restoreResults['errors']) ? implode('; ', array_slice($restoreResults['errors'], 0, 5)) : null;
        echo json_encode([
            'success'       => true,
            'file'          => $origName,
            'query_count'   => $restoreResults['success'],
            'failed_count'  => $restoreResults['failed'],
            'warnings'      => $warningText,
        ]);
        exit;
    } catch (Exception $e) {
        $conn->query('SET FOREIGN_KEY_CHECKS=1');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
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
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($ext === 'sql' || $ext === 'zip') {
                $filePath = $backupDir . $file;
                $filesize = filesize($filePath);
                $filedate = filemtime($filePath);
                $backups[] = array('name' => $file, 'size' => $filesize, 'date' => $filedate, 'type' => $ext);
                if ($ext === 'sql') $totalStorage += $filesize;
                if ($lastBackupDate === null || $filedate > $lastBackupDate) $lastBackupDate = $filedate;
            }
        }
        usort($backups, function($a, $b) { return $b['date'] - $a['date']; });
    }
  }
}

// ========= Filtering, sorting, pagination =========
$totalBackups = count($backups);

// --- SQL Backup Tab ---
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

$sqlBackups = array_values(array_filter($backups, fn($b) => $b['type'] === 'sql'));

$filteredBackups = array_filter($sqlBackups, function($b) use ($search, $fromTs, $toTs, $minSizeBytes, $maxSizeBytes) {
    if ($search !== '' && stripos($b['name'], $search) === false) return false;
    if ($fromTs && $b['date'] < $fromTs) return false;
    if ($toTs && $b['date'] > $toTs) return false;
    if ($minSizeBytes !== null && $b['size'] < $minSizeBytes) return false;
    if ($maxSizeBytes !== null && $b['size'] > $maxSizeBytes) return false;
    return true;
});

usort($filteredBackups, function($a, $b) use ($sort) {
    switch ($sort) {
        case 'oldest':  return $a['date'] <=> $b['date'];
        case 'largest': return $b['size'] <=> $a['size'];
        case 'smallest':return $a['size'] <=> $b['size'];
        default:        return $b['date'] <=> $a['date'];
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

// --- System (ZIP) Backup Tab ---
$sysSearch   = trim($_GET['sys_search'] ?? '');
$sysSort     = $_GET['sys_sort'] ?? 'newest';
$sysDateFrom = $_GET['sys_date_from'] ?? '';
$sysDateTo   = $_GET['sys_date_to'] ?? '';
$sysMinSize  = $_GET['sys_min_size'] ?? '';
$sysMaxSize  = $_GET['sys_max_size'] ?? '';

$sysFromTs      = $sysDateFrom ? strtotime($sysDateFrom . ' 00:00:00') : null;
$sysToTs        = $sysDateTo   ? strtotime($sysDateTo . ' 23:59:59')   : null;
$sysMinSizeBytes= is_numeric($sysMinSize) ? (float)$sysMinSize * 1024 * 1024 : null;
$sysMaxSizeBytes= is_numeric($sysMaxSize) ? (float)$sysMaxSize * 1024 * 1024 : null;

$zipBackups = array_values(array_filter($backups, fn($b) => $b['type'] === 'zip'));

$sysFilteredBackups = array_filter($zipBackups, function($b) use ($sysSearch, $sysFromTs, $sysToTs, $sysMinSizeBytes, $sysMaxSizeBytes) {
    if ($sysSearch !== '' && stripos($b['name'], $sysSearch) === false) return false;
    if ($sysFromTs && $b['date'] < $sysFromTs) return false;
    if ($sysToTs && $b['date'] > $sysToTs) return false;
    if ($sysMinSizeBytes !== null && $b['size'] < $sysMinSizeBytes) return false;
    if ($sysMaxSizeBytes !== null && $b['size'] > $sysMaxSizeBytes) return false;
    return true;
});

usort($sysFilteredBackups, function($a, $b) use ($sysSort) {
    switch ($sysSort) {
        case 'oldest':  return $a['date'] <=> $b['date'];
        case 'largest': return $b['size'] <=> $a['size'];
        case 'smallest':return $a['size'] <=> $b['size'];
        default:        return $b['date'] <=> $a['date'];
    }
});

$sysPerPage = 10;
$sysPage = max(1, (int)($_GET['sys_page'] ?? 1));
$sysTotalFiltered = count($sysFilteredBackups);
$sysTotalPages = max(1, (int)ceil($sysTotalFiltered / $sysPerPage));
if ($sysPage > $sysTotalPages) { $sysPage = $sysTotalPages; }
$sysOffset = ($sysPage - 1) * $sysPerPage;
$sysPagedBackups = array_slice($sysFilteredBackups, $sysOffset, $sysPerPage);
$sysShowingFrom = $sysTotalFiltered ? $sysOffset + 1 : 0;
$sysShowingTo = $sysOffset + count($sysPagedBackups);

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

$maxStorageBytes = 0; // No storage limit
$storagePercent = 0;
$storageLimitReached = false;

// AJAX: return only backup list + filters
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    $ajaxTab = $_GET['tab'] ?? 'sql';
    if ($ajaxTab === 'system') {
        ob_start();
        include __DIR__ . '/system_backup_list_partial.php';
        $html = ob_get_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'html'        => $html,
            'page'        => $sysPage,
            'totalPages'  => $sysTotalPages,
            'total'       => $sysTotalFiltered,
            'showingFrom' => $sysShowingFrom,
            'showingTo'   => $sysShowingTo,
        ]);
    } else {
        ob_start();
        include __DIR__ . '/backup_list_partial.php';
        $html = ob_get_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'html'        => $html,
            'page'        => $page,
            'totalPages'  => $totalPages,
            'total'       => $totalFiltered,
            'showingFrom' => $showingFrom,
            'showingTo'   => $showingTo,
        ]);
    }
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
            padding-top: 66px;
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
            border-image: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%) 1;
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
            background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
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
            background: #2E86AB;
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
            background: #2E86AB;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin-top: 8px;
            transition: all 0.2s ease;
            font-size: 12px;
            border: none;
            cursor: pointer;
        }

        .download-badge:hover {
            background: #1B4F72;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(46, 134, 171, 0.4);
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
            border-color: rgba(46, 134, 171, 0.20);
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
            background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
            color: white;
        }

        .summary-icon.blue {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
        }

        .summary-icon.green {
            background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
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
            border-color: rgba(46, 134, 171, 0.20);
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
            background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
            color: white;
        }

        .restore-card-icon {
            background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
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
            background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 134, 171, 0.20);
            color: white;
        }

        .btn-success-custom {
            background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
            color: white;
        }

        .btn-success-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 134, 171, 0.4);
            color: white;
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
            background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
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
            border-color: #2E86AB;
            transform: translateX(6px) scale(1.005);
            box-shadow: 0 4px 16px rgba(46, 134, 171, 0.20);
        }

        .backup-icon-wrapper {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
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
            background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
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

<?php if (!$isAjaxRequest) { include('../navbar.php'); } ?>

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
        <?php if (!empty($success_message)): ?>
            <div class="alert-custom alert-success-custom" id="successAlert">
                <div class="alert-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>Success!</strong> <?= htmlspecialchars($success_message) ?>
                    <?php if (!empty($download_file)): ?>
                        <button type="button" class="download-badge"
                                onclick="triggerBackupDownload('<?= htmlspecialchars($download_file, ENT_QUOTES) ?>')">
                            <i class="bi bi-download"></i>
                            Download: <?= htmlspecialchars($download_file) ?>
                        </button>
                    <?php endif; ?>
                    <?php if (!empty($download_zip)): ?>
                        <button type="button" class="download-badge" style="background:#1B4F72;"
                                onclick="triggerBackupDownload('<?= htmlspecialchars($download_zip, ENT_QUOTES) ?>')">
                            <i class="bi bi-file-zip"></i>
                            Download System Backup: <?= htmlspecialchars($download_zip) ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($warning_message)): ?>
            <div class="alert-custom alert-error-custom" id="warningAlert" style="background: linear-gradient(135deg,#fff3cd 0%,#ffeeba 100%); color:#856404;">
                <div class="alert-icon" style="background:#ffc107;">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </div>
                <div>
                    <strong>Warning!</strong> <?= htmlspecialchars($warning_message) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
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
                <div class="summary-icon green">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-value"><?= $lastBackupDate ? date('M j', $lastBackupDate) : 'N/A' ?></div>
                    <div class="summary-label">Last Backup<?= $lastBackupDate ? ' - ' . date('g:i A', $lastBackupDate) : '' ?></div>
                </div>
            </div>

            <?php /* AUTO BACKUP STATUS CARD - temporarily hidden
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
            */ ?>

        </div>

        <!-- Action Cards -->
        <div class="row mb-3">
            <!-- Create Database Backup -->
            <div class="col-lg-3 mb-3">
                <div class="action-card h-100">
                    <div class="action-card-header">
                        <div class="action-card-icon backup-card-icon">
                            <i class="bi bi-cloud-download"></i>
                        </div>
                        <h3>Create Database Backup</h3>
                    </div>
                    <p>Generate a complete snapshot of your database. The backup file includes all tables, data, and structures.</p>

                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <button type="submit" name="create_backup" class="btn-custom btn-primary-custom w-100" <?= $storageLimitReached ? 'disabled' : '' ?> data-bs-toggle="tooltip" title="<?= $storageLimitReached ? 'Storage limit reached. Delete old backups to create new ones.' : 'Create a new full database backup.' ?>">
                            <i class="bi bi-plus-circle"></i>
                            Create Backup Now
                        </button>
                    </form>
                </div>
            </div>

            <!-- Restore Database Backup (SQL upload) -->
            <div class="col-lg-3 mb-3">
                <div class="action-card h-100">
                    <div class="action-card-header">
                        <div class="action-card-icon backup-card-icon">
                            <i class="bi bi-database-up"></i>
                        </div>
                        <h3>Restore Database Backup</h3>
                    </div>
                    <p style="font-size:13px; color:#6c757d;">
                        Upload a <strong>.sql</strong> backup file to restore the database.
                    </p>

                    <form method="post" enctype="multipart/form-data" id="sqlRestoreUploadForm">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="hidden" name="restore_sql_upload" value="1">

                        <div class="mb-3">
                            <label for="sqlRestoreFile" class="form-label" style="font-size:13px; font-weight:600;">
                                <i class="bi bi-folder2-open me-1"></i> Select SQL file
                            </label>
                            <input type="file" class="form-control form-control-sm" id="sqlRestoreFile"
                                   name="sql_file" accept=".sql" required>
                            <div class="form-text" style="font-size:11px;">Only <code>.sql</code> backup files are accepted.</div>
                        </div>

                        <button type="button" class="btn-custom btn-primary-custom w-100"
                                onclick="confirmSqlUploadRestore()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore Database
                        </button>
                    </form>

                    <div class="mt-2" style="font-size:11px; color:#dc3545;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>Warning:</strong> This will overwrite the current database.
                    </div>
                </div>
            </div>

            <!-- Create System Backup -->
            <div class="col-lg-3 mb-3">
                <div class="action-card h-100">
                    <div class="action-card-header">
                        <div class="action-card-icon backup-card-icon">
                            <i class="bi bi-file-zip"></i>
                        </div>
                        <h3>Create System Backup</h3>
                    </div>
                    <p>Generate a full ZIP backup including the database dump + all project files (uploads, config, css, etc.). Excludes vendor/ and backups/ folder.</p>

                    <button type="button" class="btn-custom btn-primary-custom w-100" onclick="confirmSystemBackup()" data-bs-toggle="tooltip" title="Create a full system backup (DB + files) as ZIP.">
                        <i class="bi bi-archive"></i>
                        Create System Backup
                    </button>
                    <!-- Hidden iframe for background form submission -->
                    <iframe id="systemBackupIframe" name="systemBackupIframe" style="display:none;"></iframe>
                    <form method="post" id="systemBackupForm" target="systemBackupIframe" data-ajax>
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="hidden" name="create_system_backup" value="1">
                    </form>

                    <div class="mt-2" style="font-size:11px; color:#6c757d;">
                        <i class="bi bi-info-circle"></i>
                        Includes: index/, uploads/, config/, css/, images/, composer.json + DB dump<br>
                        <i class="bi bi-x-circle text-danger"></i>
                        Excludes: vendor/, backups/, node_modules/
                    </div>
                </div>
            </div>

            <!-- Restore System Backup -->
            <div class="col-lg-3 mb-3">
                <div class="action-card h-100">
                    <div class="action-card-header">
                        <div class="action-card-icon restore-card-icon">
                            <i class="bi bi-file-zip"></i>
                        </div>
                        <h3>Restore System Backup</h3>
                    </div>

                    <p style="font-size:13px; color:#6c757d;">
                        Upload a <strong>.zip</strong> system backup to restore files and database.
                    </p>

                    <!-- Hidden iframe for background submission -->
                    <iframe id="sysRestoreIframe" name="sysRestoreIframe" style="display:none;"></iframe>
                    <form method="post" id="sysRestoreForm" enctype="multipart/form-data"
                          target="sysRestoreIframe" data-ajax>
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="hidden" name="restore_system_zip" value="1">

                        <div class="mb-3">
                            <label for="sysRestoreFile" class="form-label" style="font-size:13px; font-weight:600;">
                                <i class="bi bi-folder2-open me-1"></i> Select ZIP file
                            </label>
                            <input type="file" class="form-control form-control-sm" id="sysRestoreFile"
                                   name="zip_file" accept=".zip" required>
                            <div class="form-text" style="font-size:11px;">Only <code>system_backup_*.zip</code> files are accepted.</div>
                        </div>

                        <button type="button" class="btn-custom btn-primary-custom w-100"
                                onclick="confirmSystemRestore()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore System
                        </button>
                    </form>

                    <div class="mt-2" style="font-size:11px; color:#dc3545;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>Warning:</strong> This will overwrite existing files and database.
                    </div>
                </div>
            </div>

        </div>

        <!-- Backup History -->
        <div class="history-card">
            <div class="history-header">
                <i class="bi bi-clock-history" style="font-size: 20px; color: #2E86AB;"></i>
                <h3>Backup History</h3>
                <span class="backup-count"><?= count($backups) ?> Backups</span>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3" id="backupHistoryTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-sql-btn" data-bs-toggle="tab" data-bs-target="#tab-sql" type="button" role="tab">
                        <i class="bi bi-database me-1"></i> SQL Backup
                        <?php $sqlCount = count(array_filter($backups, fn($b) => $b['type'] === 'sql')); ?>
                        <span class="badge ms-1" style="background:#2E86AB; font-size:11px;"><?= $sqlCount ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-system-btn" data-bs-toggle="tab" data-bs-target="#tab-system" type="button" role="tab">
                        <i class="bi bi-file-zip me-1"></i> System Backup
                        <?php $zipCount = count(array_filter($backups, fn($b) => $b['type'] === 'zip')); ?>
                        <span class="badge ms-1" style="background:#1B4F72; font-size:11px;"><?= $zipCount ?></span>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="backupHistoryTabContent">
                <!-- SQL Backup Tab -->
                <div class="tab-pane fade show active" id="tab-sql" role="tabpanel">
                    <div id="backupFiltersAndList">
                        <?php include __DIR__ . '/backup_list_partial.php'; ?>
                    </div>
                </div>
                <!-- System Backup Tab -->
                <div class="tab-pane fade" id="tab-system" role="tabpanel">
                    <div id="systemBackupFiltersAndList">
                        <?php include __DIR__ . '/system_backup_list_partial.php'; ?>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- Hidden form for restore & delete actions -->
<!-- Hidden iframe for background file downloads (prevents page loading indicator) -->
<iframe id="downloadBackupIframe" style="display:none;" title="download"></iframe>

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

// Show upload restore success SweetAlert
<?php if (isset($upload_restore_success) && $upload_restore_success === true): ?>
window.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: 'Upload & Restore Successful!',
        html: 'Database has been restored successfully from your uploaded file:<br><br><strong><?= htmlspecialchars($upload_restore_filename) ?></strong><br><br><small class="text-muted"><?= $upload_restore_query_count ?> queries executed</small>',
        icon: 'success',
        confirmButtonColor: '#28a745',
        confirmButtonText: 'OK',
        customClass: {
            confirmButton: 'btn btn-success px-4 py-2'
        },
        buttonsStyling: false
    });
});
<?php endif; ?>

// Show warning SweetAlert for partial restores
<?php if (!empty($warning_message)): ?>
window.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: 'Restore Completed with Warnings',
        html: '<div style="text-align:left; max-height:300px; overflow-y:auto; font-size:13px;"><?= htmlspecialchars(str_replace("\n", "<br>", $warning_message)) ?></div>',
        icon: 'warning',
        confirmButtonColor: '#ffc107',
        confirmButtonText: 'OK',
        customClass: {
            confirmButton: 'btn btn-warning px-4 py-2'
        },
        buttonsStyling: false,
        width: '600px'
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
    attachSystemBackupFilterListeners();

    // Restore active tab from sessionStorage
    const savedTab = sessionStorage.getItem('backupActiveTab');
    if (savedTab) {
        const tabEl = document.querySelector('#backupHistoryTabs button[data-bs-target="' + savedTab + '"]');
        if (tabEl) {
            new bootstrap.Tab(tabEl).show();
        }
    }

    // Save active tab on switch
    document.querySelectorAll('#backupHistoryTabs button[data-bs-toggle="tab"]').forEach(function(btn) {
        btn.addEventListener('shown.bs.tab', function(e) {
            sessionStorage.setItem('backupActiveTab', e.target.getAttribute('data-bs-target'));
        });
    });
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

// Download backup via hidden iframe (no page loading indicator stuck)
function triggerBackupDownload(filename) {
    const iframe = document.getElementById('downloadBackupIframe');
    iframe.src = 'download_backup.php?file=' + encodeURIComponent(filename);
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

// Restore a system ZIP directly from backup history (no file upload needed)
function confirmRestoreZipFromHistory(filename) {
    Swal.fire({
        title: 'Restore System Backup?',
        html: `<div style="text-align:left;">
                 <p><strong style="color:#dc3545;">⚠️ WARNING: This cannot be undone!</strong></p>
                 <p>This will <strong>overwrite</strong> your current files and database with:</p>
                 <p style="background:#f8f9fa; padding:8px; border-radius:6px; font-size:13px;">
                   <i class="bi bi-file-zip"></i> <strong>${filename}</strong>
                 </p>
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-arrow-counterclockwise"></i> Yes, Restore!',
        cancelButtonText: 'Cancel',
        customClass: { confirmButton: 'btn btn-danger px-4 py-2', cancelButton: 'btn btn-secondary px-4 py-2' },
        buttonsStyling: false,
    }).then(result => {
        if (!result.isConfirmed) return;

        let sec = 0;
        Swal.fire({
            title: 'Restoring System...',
            html: 'Extracting files and restoring database.<br>Please do not close this page.<br><br><b>0</b> seconds elapsed',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
                const b = Swal.getHtmlContainer().querySelector('b');
                window._sysRestoreTimer = setInterval(() => { sec++; b.textContent = sec; }, 1000);
            },
        });

        const formData = new FormData();
        formData.append('restore_system_zip', '1');
        formData.append('restore_from_history', '1');
        formData.append('zip_filename', filename);
        formData.append('csrf_token', document.querySelector('#sysRestoreForm input[name="csrf_token"]').value);

        fetch(window.location.href, { method: 'POST', body: formData })
        .then(res => res.text())
        .then(raw => {
            clearInterval(window._sysRestoreTimer);
            let data;
            try { data = JSON.parse(raw.trim()); }
            catch(e) {
                Swal.fire({ title: 'Restore Failed!', html: '<pre style="font-size:11px;max-height:200px;overflow:auto;">' + raw.substring(0,1500) + '</pre>', icon: 'error', confirmButtonColor: '#dc3545', width:'650px' });
                return;
            }
            if (data.success) {
                Swal.fire({
                    title: 'System Restored!',
                    html: `System restored successfully!<br><br><strong>${data.file}</strong><br>
                           <small class="text-muted">${data.extracted_files} files · ${data.query_count} SQL queries</small>`,
                    icon: 'success',
                    confirmButtonColor: '#28a745',
                    customClass: { confirmButton: 'btn btn-success px-4 py-2' },
                    buttonsStyling: false,
                }).then(() => location.reload());
            } else {
                Swal.fire({ title: 'Restore Failed!', html: data.error || 'Unknown error.', icon: 'error', confirmButtonColor: '#dc3545' });
            }
        })
        .catch(err => {
            clearInterval(window._sysRestoreTimer);
            Swal.fire({ title: 'Restore Failed!', html: 'Network error: ' + err.message, icon: 'error', confirmButtonColor: '#dc3545' });
        });
    });
}

// SweetAlert confirmation for SQL upload restore
function confirmSqlUploadRestore() {
    const fileInput = document.getElementById('sqlRestoreFile');
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        Swal.fire({
            title: 'No File Selected',
            text: 'Please choose a .sql backup file first.',
            icon: 'warning',
            confirmButtonColor: '#28a745',
        });
        return;
    }

    const file = fileInput.files[0];
    const sizeMB = (file.size / 1024 / 1024).toFixed(2);

    Swal.fire({
        title: 'Restore Database?',
        html: `<div style="text-align:left;">
                 <p><strong style="color:#dc3545;">⚠️ WARNING: This cannot be undone!</strong></p>
                 <p>This will <strong>overwrite</strong> your current database with:</p>
                 <p style="background:#f8f9fa; padding:8px; border-radius:6px; font-size:13px;">
                   <i class="bi bi-file-earmark-code"></i> <strong>${file.name}</strong><br>
                   <small class="text-muted">${sizeMB} MB</small>
                 </p>
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-arrow-counterclockwise"></i> Yes, Restore!',
        cancelButtonText: 'Cancel',
        customClass: { confirmButton: 'btn btn-success px-4 py-2', cancelButton: 'btn btn-secondary px-4 py-2' },
        buttonsStyling: false,
    }).then(result => {
        if (!result.isConfirmed) return;

        let sec = 0;
        Swal.fire({
            title: 'Restoring Database...',
            html: 'Please wait while we restore your database.<br>Do not close this page.<br><br><b>0</b> seconds elapsed',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
                const b = Swal.getHtmlContainer().querySelector('b');
                window._sqlRestoreTimer = setInterval(() => { sec++; b.textContent = sec; }, 1000);
            },
        });

        const formData = new FormData();
        formData.append('restore_sql_upload', '1');
        formData.append('csrf_token', document.querySelector('#sqlRestoreUploadForm input[name="csrf_token"]').value);
        formData.append('sql_file', file);

        fetch(window.location.href, { method: 'POST', body: formData })
        .then(res => res.text())
        .then(raw => {
            clearInterval(window._sqlRestoreTimer);
            let data;
            try { data = JSON.parse(raw.trim()); }
            catch(e) {
                Swal.fire({ title: 'Restore Failed!', html: '<pre style="font-size:11px;max-height:200px;overflow:auto;">' + raw.substring(0,1500) + '</pre>', icon: 'error', confirmButtonColor: '#dc3545', width:'650px' });
                return;
            }
            if (data.success) {
                let warningHtml = data.warnings ? `<br><small class="text-warning"><i class="bi bi-exclamation-triangle"></i> Warnings: ${data.warnings}</small>` : '';
                Swal.fire({
                    title: 'Database Restored!',
                    html: `Database restored successfully!<br><br>
                           <strong>${data.file}</strong><br>
                           <small class="text-muted">${data.query_count} queries executed${data.failed_count > 0 ? ', ' + data.failed_count + ' failed' : ''}</small>
                           ${warningHtml}`,
                    icon: 'success',
                    confirmButtonColor: '#28a745',
                    customClass: { confirmButton: 'btn btn-success px-4 py-2' },
                    buttonsStyling: false,
                }).then(() => location.reload());
            } else {
                Swal.fire({ title: 'Restore Failed!', html: data.error || 'Unknown error.', icon: 'error', confirmButtonColor: '#dc3545' });
            }
        })
        .catch(err => {
            clearInterval(window._sqlRestoreTimer);
            Swal.fire({ title: 'Restore Failed!', html: 'Network error: ' + err.message, icon: 'error', confirmButtonColor: '#dc3545' });
        });
    });
}

// SweetAlert confirmation for system backup
function confirmSystemBackup() {
    Swal.fire({
        title: 'Create System Backup?',
        html: `This will create a <strong>ZIP file</strong> containing:<br><br>
               <ul style="text-align:left; font-size:13px;">
                 <li>Database dump (SQL)</li>
                 <li>index/, uploads/, config/, css/, images/</li>
                 <li>composer.json, composer.lock</li>
               </ul>
               <small class="text-muted">Excludes: vendor/, backups/, node_modules/</small><br><br>
               This may take a few seconds depending on file size.`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#1B4F72',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-archive"></i> Yes, Create It',
        cancelButtonText: '<i class="bi bi-x-circle"></i> Cancel',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-primary px-4 py-2',
            cancelButton: 'btn btn-secondary px-4 py-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            let sec = 0;
            Swal.fire({
                title: 'Creating System Backup...',
                html: 'Please wait, this may take a moment.<br><br><b>0</b> seconds elapsed',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                    const b = Swal.getHtmlContainer().querySelector('b');
                    window._sysBackupTimer = setInterval(() => { sec++; b.textContent = sec; }, 1000);
                },
                willClose: () => { clearInterval(window._sysBackupTimer); }
            });

            // Submit form via iframe (no page reload)
            const iframe = document.getElementById('systemBackupIframe');
            iframe.onload = function() {
                clearInterval(window._sysBackupTimer);
                try {
                    const raw = iframe.contentDocument?.body?.innerText || iframe.contentWindow?.document?.body?.innerText || '';
                    const data = JSON.parse(raw);
                    if (data.success) {
                        Swal.fire({
                            title: 'System Backup Created!',
                            html: `Backup created successfully!<br><br>
                                   <strong>${data.file}</strong><br>
                                   <small class="text-muted">${data.size} MB</small><br><br>
                                   <a href="download_backup.php?file=${encodeURIComponent(data.file)}" 
                                      class="btn btn-primary mt-2" style="background:#1B4F72; border:none;"
                                      download data-print>
                                      <i class="bi bi-file-zip"></i> Download ZIP
                                   </a>`,
                            icon: 'success',
                            confirmButtonColor: '#1B4F72',
                            confirmButtonText: 'OK',
                            customClass: { confirmButton: 'btn btn-primary px-4 py-2' },
                            buttonsStyling: false
                        });
                    } else {
                        Swal.fire({
                            title: 'Backup Failed!',
                            html: data.error || 'An unknown error occurred.',
                            icon: 'error',
                            confirmButtonColor: '#dc3545',
                            confirmButtonText: 'OK'
                        });
                    }
                } catch(e) {
                    Swal.fire({
                        title: 'Backup Failed!',
                        html: 'Could not parse server response. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'OK'
                    });
                }
            };
            document.getElementById('systemBackupForm').submit();
        }
    });
}

// SweetAlert confirmation for system restore (ZIP upload) — uses fetch() for reliable response
function confirmSystemRestore() {
    const fileInput = document.getElementById('sysRestoreFile');
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        Swal.fire({
            title: 'No File Selected',
            text: 'Please choose a system backup .zip file first.',
            icon: 'warning',
            confirmButtonColor: '#e67e22',
        });
        return;
    }

    const file = fileInput.files[0];
    const sizeMB = (file.size / 1024 / 1024).toFixed(2);

    Swal.fire({
        title: 'Restore System Backup?',
        html: `<div style="text-align:left;">
                 <p><strong style="color:#dc3545;">⚠️ WARNING: This cannot be undone!</strong></p>
                 <p>This will <strong>overwrite</strong> your current files and database with:</p>
                 <p style="background:#f8f9fa; padding:8px; border-radius:6px; font-size:13px;">
                   <i class="bi bi-file-zip"></i> <strong>${file.name}</strong><br>
                   <small class="text-muted">${sizeMB} MB</small>
                 </p>
                 <p style="font-size:13px;">Make sure this is a valid <code>system_backup_*.zip</code> created by this system.</p>
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-arrow-counterclockwise"></i> Yes, Restore!',
        cancelButtonText: 'Cancel',
        customClass: { confirmButton: 'btn btn-danger px-4 py-2', cancelButton: 'btn btn-secondary px-4 py-2' },
        buttonsStyling: false,
    }).then(result => {
        if (!result.isConfirmed) return;

        let sec = 0;
        Swal.fire({
            title: 'Restoring System...',
            html: 'Extracting files and restoring database.<br>Please do not close this page.<br><br><b>0</b> seconds elapsed',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
                const b = Swal.getHtmlContainer().querySelector('b');
                window._sysRestoreTimer = setInterval(() => { sec++; b.textContent = sec; }, 1000);
            },
        });

        // Build FormData and use fetch() — avoids iframe parsing issues
        const formData = new FormData();
        formData.append('restore_system_zip', '1');
        formData.append('csrf_token', document.querySelector('#sysRestoreForm input[name="csrf_token"]').value);
        formData.append('zip_file', file);

        fetch(window.location.href, {
            method: 'POST',
            body: formData,
        })
        .then(res => res.text())
        .then(raw => {
            clearInterval(window._sysRestoreTimer);
            let data;
            try {
                // Strip any leading/trailing whitespace or BOM before parsing
                data = JSON.parse(raw.trim());
            } catch (e) {
                Swal.fire({
                    title: 'Restore Failed!',
                    html: '<b>Raw server response:</b><br><pre style="text-align:left;font-size:11px;max-height:200px;overflow:auto;background:#f8f9fa;padding:8px;border-radius:4px;">' + raw.substring(0, 1500) + '</pre>',
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    width: '650px',
                });
                return;
            }

            if (data.success) {
                let warningHtml = '';
                if (data.warnings) {
                    warningHtml = `<br><small class="text-warning"><i class="bi bi-exclamation-triangle"></i> Some warnings: ${data.warnings}</small>`;
                }
                Swal.fire({
                    title: 'System Restored!',
                    html: `System has been restored successfully!<br><br>
                           <strong>${data.file}</strong><br>
                           <small class="text-muted">${data.extracted_files} files extracted · ${data.query_count} SQL queries executed</small>
                           ${warningHtml}`,
                    icon: 'success',
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'btn btn-success px-4 py-2' },
                    buttonsStyling: false,
                }).then(() => { location.reload(); });
            } else {
                Swal.fire({
                    title: 'Restore Failed!',
                    html: data.error || 'An unknown error occurred.',
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                });
            }
        })
        .catch(err => {
            clearInterval(window._sysRestoreTimer);
            Swal.fire({
                title: 'Restore Failed!',
                html: 'Network error: ' + err.message,
                icon: 'error',
                confirmButtonColor: '#dc3545',
            });
        });
    });
}

// SweetAlert confirmation for delete (ZIP system backups)
function confirmDeleteZip(filename) {
    Swal.fire({
        title: 'Delete System Backup?',
        html: `Are you sure you want to delete this system backup?<br><br><strong>${filename}</strong><br><br>This action cannot be undone.`,
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

// ===== System Backup Tab Filters & AJAX =====
let sysBackupSearchTimer = null;

function getSysBackupFilters() {
    const wrapper = document.getElementById('systemBackupFiltersAndList');
    if (!wrapper) return {};
    return {
        sys_search:    wrapper.querySelector('#sysBackupSearch')?.value   || '',
        sys_date_from: wrapper.querySelector('#sysBackupDateFrom')?.value || '',
        sys_date_to:   wrapper.querySelector('#sysBackupDateTo')?.value   || '',
        sys_min_size:  wrapper.querySelector('#sysBackupMinSize')?.value  || '',
        sys_max_size:  wrapper.querySelector('#sysBackupMaxSize')?.value  || '',
        sys_sort:      wrapper.querySelector('#sysBackupSort')?.value     || 'newest',
    };
}

function attachSystemBackupFilterListeners() {
    const wrapper = document.getElementById('systemBackupFiltersAndList');
    if (!wrapper) return;

    const searchInput = wrapper.querySelector('#sysBackupSearch');
    if (searchInput && !searchInput.dataset.bound) {
        searchInput.dataset.bound = '1';
        searchInput.addEventListener('input', function() {
            if (sysBackupSearchTimer) clearTimeout(sysBackupSearchTimer);
            sysBackupSearchTimer = setTimeout(function() { fetchSystemBackupList(1); }, 400);
        });
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (sysBackupSearchTimer) clearTimeout(sysBackupSearchTimer);
                fetchSystemBackupList(1);
            }
        });
    }

    const searchBtn = wrapper.querySelector('#sysBackupSearchBtn');
    if (searchBtn && !searchBtn.dataset.bound) {
        searchBtn.dataset.bound = '1';
        searchBtn.addEventListener('click', function() {
            if (sysBackupSearchTimer) clearTimeout(sysBackupSearchTimer);
            fetchSystemBackupList(1);
        });
    }

    const filterInputs = wrapper.querySelectorAll('.sys-backup-filter-input');
    filterInputs.forEach(function(input) {
        if (!input.dataset.bound) {
            input.dataset.bound = '1';
            input.addEventListener('change', function() { fetchSystemBackupList(1); });
        }
    });

    // Pagination buttons
    wrapper.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-sys-page]');
        if (btn) {
            e.preventDefault();
            const p = parseInt(btn.getAttribute('data-sys-page'), 10);
            if (!isNaN(p) && p > 0) { fetchSystemBackupList(p); }
        }
    });
}

function fetchSystemBackupList(page) {
    const filters = getSysBackupFilters();
    const params = new URLSearchParams({ ajax: '1', tab: 'system', sys_page: String(page) });
    Object.keys(filters).forEach(function(key) {
        if (filters[key] !== '') { params.append(key, filters[key]); }
    });

    fetch('backup.php?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            const wrapper = document.getElementById('systemBackupFiltersAndList');
            if (wrapper && data.html) {
                wrapper.innerHTML = data.html;
                attachSystemBackupFilterListeners();
            }
        })
        .catch(function(err) { console.error('Failed to fetch system backups list', err); });
}


// SweetAlert confirmation for delete (SQL backups)
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
