<?php
// ============================================
// DATABASE CONNECTION - ONLINE VERSION
// For production/demo with internet
// ============================================

$servername = "sql12.freesqldatabase.com";
$username   = "sql12814263";
$password   = "W2VRUwnFv4";
$dbname     = "sql12814263";
$port       = 3306;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Online database connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// For activity logs
$connLog = new mysqli($servername, $username, $password, $dbname, $port);
if (!$connLog->connect_error) {
    $connLog->set_charset("utf8");
}
