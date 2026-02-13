<?php
require_once('config/db_connect.php');

$sql = "CREATE TABLE IF NOT EXISTS galleries_archive (
    archive_id INT AUTO_INCREMENT PRIMARY KEY,
    gallery_id INT,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) DEFAULT 'Uncategorized',
    images TEXT NOT NULL,
    original_created_at DATETIME,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    echo "galleries_archive table created successfully\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}
?>