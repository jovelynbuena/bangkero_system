<?php
session_start();
require_once('../../config/db_connect.php');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Complete Backup Test</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 30px; font-family: Arial, sans-serif; }
        .test-section { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #007bff; font-weight: bold; }
        pre { background: #e9ecef; padding: 15px; border-radius: 5px; }
        .btn-test { margin: 10px 5px; }
    </style>
</head>
<body>";

echo "<h1>🔍 Complete Backup System Test</h1>";
echo "<p class='text-muted'>Testing all components of the backup system</p>";
echo "<hr>";

// Test 1: Session Check
echo "<div class='test-section'>";
echo "<h3>1️⃣ Session Check</h3>";
if (isset($_SESSION['user_id'])) {
    echo "<p class='success'>✅ User logged in</p>";
    echo "<ul>";
    echo "<li><strong>User ID:</strong> " . $_SESSION['user_id'] . "</li>";
    echo "<li><strong>Username:</strong> " . ($_SESSION['username'] ?? 'Not set') . "</li>";
    echo "<li><strong>Role:</strong> " . ($_SESSION['role'] ?? 'Not set') . "</li>";
    echo "<li><strong>First Name:</strong> " . ($_SESSION['first_name'] ?? 'Not set') . "</li>";
    echo "</ul>";
} else {
    echo "<p class='error'>❌ No user logged in! Please login first.</p>";
    echo "<a href='../login.php' class='btn btn-primary'>Go to Login</a>";
    echo "</div></body></html>";
    exit;
}
echo "</div>";

// Test 2: Database Connection
echo "<div class='test-section'>";
echo "<h3>2️⃣ Database Connection</h3>";
if (isset($conn) && !$conn->connect_error) {
    echo "<p class='success'>✅ Connected to database</p>";
    $dbResult = $conn->query("SELECT DATABASE()");
    $currentDb = $dbResult->fetch_row()[0];
    echo "<ul>";
    echo "<li><strong>Database:</strong> " . $currentDb . "</li>";
    echo "<li><strong>Host:</strong> " . $conn->host_info . "</li>";
    echo "</ul>";
} else {
    echo "<p class='error'>❌ Database connection failed!</p>";
    echo "</div></body></html>";
    exit;
}
echo "</div>";

// Test 3: Tables Check
echo "<div class='test-section'>";
echo "<h3>3️⃣ Database Tables</h3>";
$tablesResult = $conn->query("SHOW TABLES");
if ($tablesResult) {
    $tableCount = $tablesResult->num_rows;
    echo "<p class='success'>✅ Found {$tableCount} tables</p>";
    
    $tables = [];
    while ($row = $tablesResult->fetch_row()) {
        $tables[] = $row[0];
    }
    
    echo "<details><summary>View all tables ({$tableCount})</summary>";
    echo "<pre>" . implode("\n", $tables) . "</pre>";
    echo "</details>";
} else {
    echo "<p class='error'>❌ Cannot fetch tables: " . $conn->error . "</p>";
}
echo "</div>";

// Test 4: Backup Directory
echo "<div class='test-section'>";
echo "<h3>4️⃣ Backup Directory</h3>";
$backupDir = __DIR__ . '/backups/';
echo "<p><strong>Path:</strong> " . $backupDir . "</p>";

if (!file_exists($backupDir)) {
    echo "<p class='info'>ℹ️ Directory doesn't exist. Creating...</p>";
    if (mkdir($backupDir, 0777, true)) {
        echo "<p class='success'>✅ Directory created</p>";
    } else {
        echo "<p class='error'>❌ Failed to create directory</p>";
    }
} else {
    echo "<p class='success'>✅ Directory exists</p>";
}

if (is_writable($backupDir)) {
    echo "<p class='success'>✅ Directory is writable</p>";
} else {
    echo "<p class='error'>❌ Directory is NOT writable</p>";
    @chmod($backupDir, 0777);
    if (is_writable($backupDir)) {
        echo "<p class='success'>✅ Fixed permissions!</p>";
    }
}

// Count existing backups
$existingBackups = glob($backupDir . '*.sql');
echo "<p><strong>Existing backups:</strong> " . count($existingBackups) . " files</p>";
echo "</div>";

// Test 5: Session Messages
echo "<div class='test-section'>";
echo "<h3>5️⃣ Session Messages</h3>";
if (isset($_SESSION['success'])) {
    echo "<div class='alert alert-success'><strong>Success:</strong> " . $_SESSION['success'] . "</div>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-danger'><strong>Error:</strong> " . $_SESSION['error'] . "</div>";
    if (isset($_SESSION['debug_error'])) {
        echo "<pre>Debug: " . $_SESSION['debug_error'] . "</pre>";
        unset($_SESSION['debug_error']);
    }
    unset($_SESSION['error']);
}
if (isset($_SESSION['warning'])) {
    echo "<div class='alert alert-warning'><strong>Warning:</strong> " . $_SESSION['warning'] . "</div>";
    unset($_SESSION['warning']);
}
if (!isset($_SESSION['success']) && !isset($_SESSION['error']) && !isset($_SESSION['warning'])) {
    echo "<p class='text-muted'>No messages in session</p>";
}
echo "</div>";

// Test 6: Action Buttons
echo "<div class='test-section'>";
echo "<h3>6️⃣ Test Actions</h3>";
echo "<div class='d-flex flex-wrap'>";

// Button 1: Test via backup_action.php (form submit)
echo "<form method='post' action='backup_action.php' class='btn-test'>";
echo "<button type='submit' name='backup' class='btn btn-primary btn-lg'>
    <i class='bi bi-database-down'></i> Test Backup via backup_action.php
</button>";
echo "</form>";

// Button 2: Test via simple test
echo "<a href='backup_simple_test.php' class='btn btn-success btn-lg btn-test'>
    <i class='bi bi-play-circle'></i> Test with Simple Test Page
</a>";

// Button 3: Go to main backup page
echo "<a href='backup.php' class='btn btn-info btn-lg btn-test'>
    <i class='bi bi-arrow-right-circle'></i> Go to Main Backup Page
</a>";

echo "</div>";
echo "</div>";

// Test 7: Quick Inline Backup Test
echo "<div class='test-section'>";
echo "<h3>7️⃣ Quick Inline Backup Test</h3>";

if (isset($_POST['quick_test'])) {
    echo "<div class='alert alert-info'><strong>Running quick backup test...</strong></div>";
    
    try {
        $backupFileName = 'test_quick_' . date('Y-m-d_H-i-s') . '.sql';
        $backupFilePath = $backupDir . $backupFileName;
        
        // Get first 2 tables only for speed
        $sqlContent = "-- Quick Test Backup\n";
        $sqlContent .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        $result = $conn->query("SHOW TABLES");
        $count = 0;
        $maxTables = 2; // Limit to 2 tables only
        
        while ($row = $result->fetch_row()) {
            if ($count >= $maxTables) {
                break; // Stop after 2 tables
            }
            
            $table = $row[0];
            $sqlContent .= "-- Table: {$table}\n";
            
            $createResult = $conn->query("SHOW CREATE TABLE `{$table}`");
            if ($createResult) {
                $createRow = $createResult->fetch_row();
                $sqlContent .= $createRow[1] . ";\n\n";
                $count++;
            }
        }
        
        // Write file
        $bytes = file_put_contents($backupFilePath, $sqlContent);
        
        if ($bytes === false) {
            throw new Exception("Failed to write file");
        }
        
        // Save to database
        $user_id = $_SESSION['user_id'];
        $filesize = filesize($backupFilePath);
        
        $stmt = $conn->prepare("INSERT INTO backups (filename, filesize, created_by) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sii", $backupFileName, $filesize, $user_id);
            if ($stmt->execute()) {
                echo "<div class='alert alert-success'>";
                echo "<strong>✅ SUCCESS!</strong><br>";
                echo "File: {$backupFileName}<br>";
                echo "Size: " . number_format($bytes) . " bytes<br>";
                echo "Tables: {$count}<br>";
                echo "Saved to database: YES<br>";
                echo "<a href='download_backup.php?file=" . urlencode($backupFileName) . "' class='btn btn-sm btn-primary mt-2'>Download</a>";
                echo "</div>";
            } else {
                echo "<div class='alert alert-warning'>";
                echo "<strong>⚠️ PARTIAL SUCCESS!</strong><br>";
                echo "File created but not saved to database<br>";
                echo "Error: " . $stmt->error;
                echo "</div>";
            }
        } else {
            echo "<div class='alert alert-warning'>";
            echo "<strong>⚠️ PARTIAL SUCCESS!</strong><br>";
            echo "File created but database insert failed<br>";
            echo "Error: " . $conn->error;
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>";
        echo "<strong>❌ FAILED!</strong><br>";
        echo "Error: " . $e->getMessage();
        echo "</div>";
    }
} else {
    echo "<form method='post'>";
    echo "<button type='submit' name='quick_test' class='btn btn-warning btn-lg'>
        <i class='bi bi-lightning'></i> Run Quick Test (2 tables only)
    </button>";
    echo "<p class='text-muted mt-2'>This will create a small test backup with just 2 tables</p>";
    echo "</form>";
}

echo "</div>";

echo "<hr>";
echo "<p class='text-center'><a href='backup.php' class='btn btn-primary'>← Back to Backup Page</a></p>";

echo "</body></html>";
?>
