<?php
// ============================================
// CONNECTION MODE TESTER
// Test if ONLINE or OFFLINE mode is working
// ============================================

echo "<style>
    body { font-family: 'Inter', sans-serif; padding: 40px; background: #f5f5f5; }
    .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
    .success { color: #28a745; font-size: 18px; font-weight: 600; }
    .error { color: #dc3545; font-size: 18px; font-weight: 600; }
    .info { background: #e7f5ff; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #339af0; }
    table { width: 100%; margin-top: 20px; border-collapse: collapse; }
    td { padding: 10px; border-bottom: 1px solid #eee; }
    td:first-child { font-weight: 600; width: 150px; }
    .btn { display: inline-block; padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 6px; margin-top: 20px; }
    .btn:hover { background: #0b5ed7; }
</style>";

echo "<div class='card'>";
echo "<h2>🔍 Database Connection Test</h2>";

require_once(__DIR__ . '/db_connect.php');

if ($conn) {
    echo "<p class='success'>✅ Connection Successful!</p>";
    
    echo "<div class='info'>";
    echo "<strong>Connection Details:</strong>";
    echo "<table>";
    echo "<tr><td>Host:</td><td>" . $conn->host_info . "</td></tr>";
    echo "<tr><td>Server:</td><td>" . $conn->server_info . "</td></tr>";
    echo "<tr><td>Database:</td><td>" . $dbname . "</td></tr>";
    echo "<tr><td>Character Set:</td><td>" . $conn->character_set_name() . "</td></tr>";
    
    // Check if local or online
    if (strpos($conn->host_info, 'localhost') !== false || strpos($conn->host_info, '127.0.0.1') !== false) {
        echo "<tr><td>Mode:</td><td><strong style='color: #0d6efd;'>💻 OFFLINE (Localhost)</strong></td></tr>";
    } else {
        echo "<tr><td>Mode:</td><td><strong style='color: #28a745;'>🌐 ONLINE (Remote)</strong></td></tr>";
    }
    
    // Test query
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<tr><td>Users Count:</td><td>" . $row['count'] . " users</td></tr>";
    }
    
    // List tables
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        echo "<tr><td>Tables Found:</td><td>" . count($tables) . " tables</td></tr>";
        echo "<tr><td>Table Names:</td><td style='font-size: 12px;'>" . implode(", ", $tables) . "</td></tr>";
    }
    
    echo "</table>";
    echo "</div>";
    
    echo "<p style='margin-top: 20px;'><strong>Status:</strong> System is ready to use! ✨</p>";
    
} else {
    echo "<p class='error'>❌ Connection Failed!</p>";
    echo "<p>Error: " . $conn->connect_error . "</p>";
}

echo "<a href='../index/login.php' class='btn'>Go to Login Page</a> ";
echo "<a href='../index/utilities/backup.php' class='btn' style='background: #6c757d;'>Go to Backup</a>";
echo "</div>";

$conn->close();
?>
