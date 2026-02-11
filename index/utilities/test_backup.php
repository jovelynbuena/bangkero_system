<?php
session_start();
require_once('../../config/db_connect.php');

echo "<h2>Database Connection Test</h2>";

// Test connection
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Connection failed: " . $conn->connect_error . "</p>";
    exit();
} else {
    echo "<p style='color: green;'>✅ Database connected successfully!</p>";
    echo "<p><strong>Database:</strong> " . ($conn->query("SELECT DATABASE()")->fetch_row()[0]) . "</p>";
}

// Test if we can create tables
try {
    // Create backups table
    $result = $conn->query("CREATE TABLE IF NOT EXISTS `backups` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `filename` varchar(255) NOT NULL,
        `filesize` bigint(20) NOT NULL,
        `created_by` int(11) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    
    if ($result) {
        echo "<p style='color: green;'>✅ 'backups' table checked/created successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create 'backups' table: " . $conn->error . "</p>";
    }
    
    // Create activity_logs table
    $result = $conn->query("CREATE TABLE IF NOT EXISTS `activity_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `action` varchar(100) NOT NULL,
        `description` text,
        `ip_address` varchar(50),
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    
    if ($result) {
        echo "<p style='color: green;'>✅ 'activity_logs' table checked/created successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create 'activity_logs' table: " . $conn->error . "</p>";
    }
    
    // Check if tables exist
    $tables = $conn->query("SHOW TABLES");
    echo "<h3>Existing Tables:</h3><ul>";
    while ($row = $tables->fetch_row()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='backup.php'>← Back to Backup Page</a></p>";
?>
