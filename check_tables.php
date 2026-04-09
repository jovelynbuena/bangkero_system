<?php
/**
 * Check if all tables exist in database
 */
require_once __DIR__ . '/config/db_connect.php';

echo "<h2>Database Table Check</h2>";
echo "<p>Database: bangkero_local</p>";
echo "<hr>";

// Get all tables
$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

echo "<h3>Existing Tables:</h3>";
echo "<ul>";
foreach ($tables as $table) {
    echo "<li>✅ $table</li>";
}
echo "</ul>";

// Check specific tables
$required_tables = ['users', 'members', 'activity_logs', 'announcements', 'events', 'galleries'];
echo "<h3>Required Tables Check:</h3>";
echo "<ul>";
foreach ($required_tables as $table) {
    if (in_array($table, $tables)) {
        // Count rows
        $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
        $count = $count_result->fetch_assoc()['count'];
        echo "<li>✅ <b>$table</b> - $count rows</li>";
    } else {
        echo "<li>❌ <b>$table</b> - MISSING!</li>";
    }
}
echo "</ul>";

// Check users table structure specifically
if (in_array('users', $tables)) {
    echo "<h3>Users Table Structure:</h3>";
    $result = $conn->query("DESCRIBE users");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'><b>ERROR: users table does not exist!</b></p>";
}

echo "<hr>";
echo "<h3>Fix Options:</h3>";
echo "<p>If users table is missing, <a href='emergency_restore.php'>Click here for Emergency Restore</a></p>";

$conn->close();
?>
