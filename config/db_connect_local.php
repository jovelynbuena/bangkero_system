<?php
// ============================================
// DATABASE CONNECTION - LOCAL VERSION
// For offline/defense day backup
// ============================================

$servername = "localhost";
$username   = "root";
$password   = "";  // XAMPP default (blank)
$dbname     = "bangkero_local";
$port       = 3306;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Local database connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// For activity logs
$connLog = new mysqli($servername, $username, $password, $dbname, $port);
if (!$connLog->connect_error) {
    $connLog->set_charset("utf8");
}
