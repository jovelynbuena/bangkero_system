<?php
session_start();
require_once('../../config/db_connect.php');

echo "<h2>Backup Debug Test</h2>";
echo "<style>body { font-family: Arial; padding: 20px; } pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }</style>";

// 1. Check session
echo "<h3>1. Session Check:</h3>";
if (isset($_SESSION['user_id'])) {
    echo "✅ User ID: " . $_SESSION['user_id'] . "<br>";
} else {
    echo "❌ No user_id in session!<br>";
    echo "<pre>Session contents: " . print_r($_SESSION, true) . "</pre>";
}

// 2. Check database connection
echo "<h3>2. Database Connection:</h3>";
if (isset($conn) && !$conn->connect_error) {
    echo "✅ Connected<br>";
    $dbResult = $conn->query("SELECT DATABASE()");
    $currentDb = $dbResult->fetch_row()[0];
    echo "Database: " . $currentDb . "<br>";
} else {
    echo "❌ Connection failed<br>";
    exit;
}

// 3. Check backup directory
echo "<h3>3. Backup Directory:</h3>";
$backupDir = __DIR__ . '/backups/';
echo "Path: " . $backupDir . "<br>";

if (!file_exists($backupDir)) {
    echo "⚠️ Directory doesn't exist. Trying to create...<br>";
    if (mkdir($backupDir, 0777, true)) {
        echo "✅ Created successfully<br>";
    } else {
        echo "❌ Failed to create directory<br>";
    }
} else {
    echo "✅ Directory exists<br>";
    echo "Permissions: " . substr(sprintf('%o', fileperms($backupDir)), -4) . "<br>";
    echo "Writable: " . (is_writable($backupDir) ? "✅ Yes" : "❌ No") . "<br>";
}

// 4. Test file write
echo "<h3>4. Test File Write:</h3>";
$testFile = $backupDir . 'test_' . time() . '.txt';
$writeResult = file_put_contents($testFile, "Test content");
if ($writeResult !== false) {
    echo "✅ Can write files (" . $writeResult . " bytes)<br>";
    echo "Test file: " . basename($testFile) . "<br>";
    @unlink($testFile); // Clean up
} else {
    echo "❌ Cannot write files<br>";
    echo "Error: " . error_get_last()['message'] . "<br>";
}

// 5. Test query execution
echo "<h3>5. Test Database Queries:</h3>";
$result = $conn->query("SHOW TABLES");
if ($result) {
    echo "✅ SHOW TABLES works (" . $result->num_rows . " tables)<br>";
    
    // Try to get first table structure
    if ($result->num_rows > 0) {
        $firstTable = $result->fetch_row()[0];
        echo "Testing with table: " . $firstTable . "<br>";
        
        $createResult = $conn->query("SHOW CREATE TABLE `{$firstTable}`");
        if ($createResult) {
            echo "✅ SHOW CREATE TABLE works<br>";
        } else {
            echo "❌ SHOW CREATE TABLE failed: " . $conn->error . "<br>";
        }
        
        $dataResult = $conn->query("SELECT * FROM `{$firstTable}` LIMIT 1");
        if ($dataResult) {
            echo "✅ SELECT works<br>";
        } else {
            echo "❌ SELECT failed: " . $conn->error . "<br>";
        }
    }
} else {
    echo "❌ SHOW TABLES failed: " . $conn->error . "<br>";
}

// 6. Memory and time limits
echo "<h3>6. PHP Configuration:</h3>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . " seconds<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";

echo "<hr>";
echo "<h3>Try Small Backup Test:</h3>";
echo '<form method="post">
    <button type="submit" name="test_backup" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
        Test Create Small Backup
    </button>
</form>';

if (isset($_POST['test_backup'])) {
    echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>Running test backup...</strong><br><br>";
    
    try {
        $backupFileName = 'test_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $backupFilePath = $backupDir . $backupFileName;
        
        $sqlContent = "-- Test Backup\n";
        $sqlContent .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Get only first 3 tables
        $result = $conn->query("SHOW TABLES");
        
        if (!$result) {
            throw new Exception("SHOW TABLES failed: " . $conn->error);
        }
        
        if ($result->num_rows == 0) {
            throw new Exception("No tables found in database");
        }
        
        $count = 0;
        while (($row = $result->fetch_row()) && $count < 3) {
            $table = $row[0];
            
            if (empty($table)) {
                continue;
            }
            
            $sqlContent .= "-- Table: {$table}\n";
            
            $createResult = $conn->query("SHOW CREATE TABLE `{$table}`");
            if ($createResult) {
                $createRow = $createResult->fetch_row();
                $sqlContent .= $createRow[1] . ";\n\n";
            } else {
                $sqlContent .= "-- Error getting CREATE TABLE for {$table}: " . $conn->error . "\n\n";
            }
            
            $count++;
        }
        
        $writeResult = file_put_contents($backupFilePath, $sqlContent);
        
        if ($writeResult === false) {
            throw new Exception("Failed to write file");
        }
        
        echo "✅ <strong>Success!</strong><br>";
        echo "File: " . $backupFileName . "<br>";
        echo "Size: " . filesize($backupFilePath) . " bytes<br>";
        echo "Location: " . $backupFilePath . "<br>";
        
    } catch (Exception $e) {
        echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br>";
    }
    
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='backup.php'>← Back to Backup Page</a></p>";
?>
