<?php
session_start();
require_once('../../config/db_connect.php');

// Simple direct backup test
echo "<h2>Simple Backup Test</h2>";
echo "<style>body { font-family: Arial; padding: 20px; } .success { color: green; } .error { color: red; } pre { background: #f5f5f5; padding: 10px; }</style>";

try {
    // 1. Check connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database not connected");
    }
    echo "<p class='success'>✅ Database connected</p>";
    
    // 2. Get database name
    $dbResult = $conn->query("SELECT DATABASE()");
    if (!$dbResult) {
        throw new Exception("Cannot get database name: " . $conn->error);
    }
    $currentDb = $dbResult->fetch_row()[0];
    echo "<p class='success'>✅ Database: {$currentDb}</p>";
    
    // 3. Get tables
    $tablesResult = $conn->query("SHOW TABLES");
    if (!$tablesResult) {
        throw new Exception("SHOW TABLES failed: " . $conn->error);
    }
    
    $tables = [];
    while ($row = $tablesResult->fetch_row()) {
        $tables[] = $row[0];
    }
    
    echo "<p class='success'>✅ Found " . count($tables) . " tables</p>";
    echo "<pre>Tables: " . implode(", ", $tables) . "</pre>";
    
    // 4. Check backups directory
    $backupDir = __DIR__ . '/backups/';
    if (!is_dir($backupDir)) {
        if (!mkdir($backupDir, 0777, true)) {
            throw new Exception("Cannot create backups directory");
        }
    }
    
    if (!is_writable($backupDir)) {
        @chmod($backupDir, 0777);
        if (!is_writable($backupDir)) {
            throw new Exception("Backups directory is not writable");
        }
    }
    echo "<p class='success'>✅ Backups directory ready</p>";
    
    // 5. Create backup file
    $backupFileName = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backupFilePath = $backupDir . $backupFileName;
    
    echo "<h3>Creating backup...</h3>";
    
    $sqlContent = "-- Database Backup\n";
    $sqlContent .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $sqlContent .= "-- Database: {$currentDb}\n\n";
    $sqlContent .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $sqlContent .= "SET time_zone = \"+00:00\";\n\n";
    
    $processedTables = 0;
    $errorTables = [];
    
    foreach ($tables as $table) {
        try {
            echo "Processing table: <strong>{$table}</strong>... ";
            
            // Get CREATE TABLE
            $createResult = $conn->query("SHOW CREATE TABLE `{$table}`");
            if (!$createResult) {
                throw new Exception("SHOW CREATE TABLE failed: " . $conn->error);
            }
            
            $createRow = $createResult->fetch_row();
            $sqlContent .= "\n-- Table: {$table}\n";
            $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sqlContent .= $createRow[1] . ";\n\n";
            
            // Get data
            $dataResult = $conn->query("SELECT * FROM `{$table}`");
            if (!$dataResult) {
                throw new Exception("SELECT failed: " . $conn->error);
            }
            
            $rowCount = 0;
            if ($dataResult->num_rows > 0) {
                $sqlContent .= "-- Data for table {$table}\n";
                
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
                    $rowCount++;
                }
                $sqlContent .= "\n";
            }
            
            echo "<span class='success'>✅ ({$rowCount} rows)</span><br>";
            $processedTables++;
            
        } catch (Exception $e) {
            echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span><br>";
            $errorTables[] = $table . ": " . $e->getMessage();
        }
    }
    
    // Write to file
    echo "<h3>Writing to file...</h3>";
    $writeResult = file_put_contents($backupFilePath, $sqlContent);
    
    if ($writeResult === false) {
        throw new Exception("Failed to write backup file");
    }
    
    if (!file_exists($backupFilePath) || filesize($backupFilePath) == 0) {
        throw new Exception("Backup file is empty or doesn't exist");
    }
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724; margin-top: 0;'>✅ Backup Created Successfully!</h3>";
    echo "<p><strong>File:</strong> {$backupFileName}</p>";
    echo "<p><strong>Size:</strong> " . number_format(filesize($backupFilePath)) . " bytes</p>";
    echo "<p><strong>Tables processed:</strong> {$processedTables} / " . count($tables) . "</p>";
    echo "<p><strong>Location:</strong> {$backupFilePath}</p>";
    
    if (!empty($errorTables)) {
        echo "<p style='color: #856404;'><strong>⚠️ Errors:</strong></p>";
        echo "<ul style='color: #856404;'>";
        foreach ($errorTables as $error) {
            echo "<li>{$error}</li>";
        }
        echo "</ul>";
    }
    
    echo "<p><a href='download_backup.php?file=" . urlencode($backupFileName) . "' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;'>📥 Download Backup</a></p>";
    echo "</div>";
    
    // Now save to database
    if (isset($_SESSION['user_id'])) {
        echo "<h3>Saving to database...</h3>";
        try {
            $user_id = $_SESSION['user_id'];
            $filesize = filesize($backupFilePath);
            
            $backup_stmt = $conn->prepare("INSERT INTO backups (filename, filesize, created_by) VALUES (?, ?, ?)");
            if (!$backup_stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $backup_stmt->bind_param("sii", $backupFileName, $filesize, $user_id);
            
            if (!$backup_stmt->execute()) {
                throw new Exception("Execute failed: " . $backup_stmt->error);
            }
            
            echo "<p class='success'>✅ Saved to backups table</p>";
            
            // Log activity
            $action = 'Database Backup';
            $description = "Created backup: {$backupFileName} (" . number_format($filesize) . " bytes)";
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
            if ($log_stmt) {
                $log_stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
                $log_stmt->execute();
                echo "<p class='success'>✅ Logged to activity_logs</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>⚠️ Database logging failed: " . $e->getMessage() . "</p>";
            echo "<p>Backup file was created successfully, but couldn't save to database.</p>";
        }
    } else {
        echo "<p style='color: #856404;'>⚠️ Not logged in - backup not saved to database</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #721c24;'>❌ Backup Failed</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='backup.php'>← Back to Backup Page</a> | <a href='backup_debug2.php'>Debug Page</a></p>";
?>
