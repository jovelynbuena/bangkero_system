<?php
session_start();

echo "<!DOCTYPE html><html><head><title>Backup Debug</title><style>
body { font-family: monospace; padding: 20px; background: #f5f5f5; }
.success { color: green; }
.error { color: red; }
pre { background: white; padding: 15px; border-radius: 8px; }
</style></head><body>";

echo "<h2>🔍 Backup Process Debug</h2>";
echo "<pre>";

// Check if user is logged in
echo "1. SESSION CHECK:\n";
echo "   User logged in: " . (isset($_SESSION['user_id']) ? "✅ YES (ID: {$_SESSION['user_id']})" : "❌ NO") . "\n\n";

// Check if POST data received
echo "2. POST DATA:\n";
echo "   Backup button clicked: " . (isset($_POST['backup']) ? "✅ YES" : "❌ NO") . "\n";
echo "   POST data: " . print_r($_POST, true) . "\n\n";

// Check database connection
echo "3. DATABASE CONNECTION:\n";
require_once('../../config/db_connect.php');

if (isset($conn) && !$conn->connect_error) {
    echo "   Connection: ✅ CONNECTED\n";
    echo "   Server: {$conn->host_info}\n";
    
    // Test query
    $result = $conn->query("SELECT COUNT(*) as cnt FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "   Test query: ✅ SUCCESS ({$row['cnt']} users found)\n";
    }
    
    // Check tables
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    echo "   Tables: " . count($tables) . " tables found\n";
    echo "   Table names: " . implode(", ", $tables) . "\n\n";
    
} else {
    echo "   Connection: ❌ FAILED\n";
    echo "   Error: " . ($conn->connect_error ?? 'Unknown') . "\n\n";
}

// Check backups folder
echo "4. BACKUPS FOLDER:\n";
$backupDir = __DIR__ . '/backups/';
echo "   Path: {$backupDir}\n";
echo "   Exists: " . (file_exists($backupDir) ? "✅ YES" : "❌ NO") . "\n";
echo "   Writable: " . (is_writable($backupDir) ? "✅ YES" : "❌ NO") . "\n\n";

// List existing backups
echo "5. EXISTING BACKUPS:\n";
if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    $sqlFiles = array_filter($files, function($f) {
        return pathinfo($f, PATHINFO_EXTENSION) === 'sql';
    });
    
    if (empty($sqlFiles)) {
        echo "   ⚠️ No SQL files found\n";
    } else {
        foreach ($sqlFiles as $file) {
            $filePath = $backupDir . $file;
            $size = filesize($filePath);
            $date = date('Y-m-d H:i:s', filemtime($filePath));
            echo "   - {$file}\n";
            echo "     Size: " . number_format($size) . " bytes\n";
            echo "     Date: {$date}\n\n";
        }
    }
}

// Check session messages
echo "6. SESSION MESSAGES:\n";
echo "   Success: " . ($_SESSION['success'] ?? 'none') . "\n";
echo "   Error: " . ($_SESSION['error'] ?? 'none') . "\n";
echo "   Download file: " . ($_SESSION['download_file'] ?? 'none') . "\n\n";

echo "</pre>";

echo "<h3>Try Manual Backup:</h3>";
echo "<form method='post' action='backup_action.php'>";
echo "<button type='submit' name='backup' style='padding: 10px 20px; font-size: 16px; background: #0d6efd; color: white; border: none; border-radius: 8px; cursor: pointer;'>Create Backup Now</button>";
echo "</form>";

echo "<br><a href='backup.php'>← Back to Backup Page</a>";
echo "</body></html>";
?>
