<?php
require 'config/db_connect.php';

// Delete rows with id = 0
$del = $conn->query("DELETE FROM announcements WHERE id = 0");
echo "Deleted rows with id=0: " . $conn->affected_rows . "<br>";

// Fix AUTO_INCREMENT
$r = $conn->query("SELECT MAX(id) as max_id FROM announcements");
$row = $r->fetch_assoc();
$next = intval($row['max_id']) + 1;
$conn->query("ALTER TABLE announcements AUTO_INCREMENT = $next");
echo "AUTO_INCREMENT reset to: $next<br>";

// Fix empty expiry_date strings to NULL
$fix = $conn->query("UPDATE announcements SET expiry_date = NULL WHERE expiry_date = '' OR expiry_date = '0000-00-00'");
echo "Fixed blank expiry_date to NULL: " . $conn->affected_rows . " rows<br>";

// Check recent announcements expiry_date values
echo "<br><strong>Recent announcements:</strong><br>";
$r3 = $conn->query("SELECT id, title, expiry_date FROM announcements ORDER BY id DESC LIMIT 5");
while ($row = $r3->fetch_assoc()) {
    echo "ID: {$row['id']} | expiry_date: [" . var_export($row['expiry_date'], true) . "] | Title: {$row['title']}<br>";
}

echo "<br><strong>Done! You can close this page.</strong>";
