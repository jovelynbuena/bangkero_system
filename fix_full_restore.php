<?php
/**
 * FULL RESTORE SCRIPT
 * Restores all data from the latest backup with FK checks disabled
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(300);

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "bangkero_local";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("<b style='color:red'>Connection failed: " . $conn->connect_error . "</b>");
}
$conn->set_charset("utf8mb4");

// Find latest backup
$backupDir = __DIR__ . '/index/utilities/backups/';
$latestFile = null;
$latestTime = 0;
foreach (glob($backupDir . 'backup_*.sql') as $file) {
    $t = filemtime($file);
    if ($t > $latestTime) {
        $latestTime = $t;
        $latestFile = $file;
    }
}

if (!$latestFile) {
    die("<b style='color:red'>No backup file found!</b>");
}

echo "<h2>Full Database Restore</h2>";
echo "<p>Using backup: <b>" . basename($latestFile) . "</b> (" . round(filesize($latestFile)/1024, 1) . " KB)</p>";

// If form not submitted, show confirm button
if (!isset($_POST['confirm'])) {
    echo "<form method='POST'>
        <p style='color:orange;font-weight:bold'>⚠️ This will DROP and recreate all tables then restore all data.</p>
        <input type='hidden' name='confirm' value='1'>
        <button type='submit' style='padding:10px 30px;background:#dc3545;color:white;border:none;border-radius:5px;font-size:16px;cursor:pointer'>
            ✅ YES - Restore Full Database
        </button>
    </form>";
    $conn->close();
    exit;
}

echo "<pre style='background:#f4f4f4;padding:15px;border-radius:5px'>";

// Read SQL file
$sql = file_get_contents($latestFile);
if (!$sql) {
    die("❌ Cannot read backup file.");
}

echo "✓ Backup file read (" . strlen($sql) . " bytes)\n";

// Disable FK checks
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("SET sql_mode = ''");
echo "✓ Foreign key checks disabled\n\n";

// Split SQL into individual statements
$statements = array_filter(
    array_map('trim', explode(";\n", $sql)),
    fn($s) => !empty($s)
);

$success = 0;
$errors  = [];

foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    if (empty($stmt) || str_starts_with($stmt, '--') || str_starts_with($stmt, '/*')) continue;
    
    if (!$conn->query($stmt)) {
        $errors[] = substr($stmt, 0, 80) . "... → " . $conn->error;
    } else {
        $success++;
    }
}

echo "✓ Executed: $success statements\n";

if (!empty($errors)) {
    echo "\n⚠️ Errors (" . count($errors) . "):\n";
    foreach (array_slice($errors, 0, 10) as $err) {
        echo "  - $err\n";
    }
    if (count($errors) > 10) echo "  ... and " . (count($errors) - 10) . " more\n";
}

// Re-enable FK checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "\n✓ Foreign key checks re-enabled\n";

// Verify key tables
echo "\n========== TABLE STATUS ==========\n";
$tables = ['users', 'members', 'events', 'officers', 'announcements', 'activity_logs', 'galleries', 'transparency_docs'];
foreach ($tables as $table) {
    $res = $conn->query("SELECT COUNT(*) as cnt FROM `$table`");
    if ($res) {
        $cnt = $res->fetch_assoc()['cnt'];
        echo "✅ $table: $cnt rows\n";
    } else {
        echo "❌ $table: NOT FOUND\n";
    }
}

echo "\n===================================\n";
echo "RESTORE COMPLETE!\n";
echo "</pre>";
echo "<br><a href='index/home/dashboard.php' style='padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;font-size:16px'>🏠 Go to Dashboard</a>";

$conn->close();
?>
