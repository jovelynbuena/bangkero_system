<?php
/**
 * EMERGENCY RESTORE SCRIPT
 * Run this to restore your data from the latest backup
 */

// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bangkero_local";

echo "<h2>Emergency Restore</h2>";
echo "<pre>";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✓ Database connected\n";

// Find the latest backup file
$backupDir = __DIR__ . '/index/utilities/backups/';
$latestBackup = null;
$latestTime = 0;

foreach (glob($backupDir . '*.sql') as $file) {
    $fileTime = filemtime($file);
    if ($fileTime > $latestTime) {
        $latestTime = $fileTime;
        $latestBackup = $file;
    }
}

$backupFile = $latestBackup;

if (!$backupFile || !file_exists($backupFile)) {
    die("No backup file found in: " . $backupDir);
}

echo "✓ Found backup file: " . basename($backupFile) . "\n";
echo "✓ File size: " . round(filesize($backupFile)/1024/1024, 2) . " MB\n\n";

// Read SQL file
$sql = file_get_contents($backupFile);
if ($sql === false) {
    die("Failed to read backup file");
}

// Disable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
echo "✓ Foreign key checks disabled\n";

// Execute multi_query
if ($conn->multi_query($sql)) {
    echo "✓ Executing queries...\n";
    
    $count = 0;
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
        $count++;
        if ($conn->more_results()) {
            $conn->next_result();
        }
    } while ($conn->more_results());
    
    echo "✓ Executed approximately " . $count . " queries\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "✓ Foreign key checks re-enabled\n\n";

// Check if data was restored
$result = $conn->query("SELECT COUNT(*) as total FROM members");
if ($result) {
    $row = $result->fetch_assoc();
    echo "✓ Members restored: " . $row['total'] . " records\n";
}

$result = $conn->query("SELECT COUNT(*) as total FROM events");
if ($result) {
    $row = $result->fetch_assoc();
    echo "✓ Events restored: " . $row['total'] . " records\n";
}

$result = $conn->query("SELECT COUNT(*) as total FROM officers");
if ($result) {
    $row = $result->fetch_assoc();
    echo "✓ Officers restored: " . $row['total'] . " records\n";
}

echo "\n========================================\n";
echo "RESTORE COMPLETED!\n";
echo "========================================\n";
echo "\n<a href='index/home/dashboard.php' style='padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;'>Go to Dashboard</a>";

echo "</pre>";

$conn->close();
?>
