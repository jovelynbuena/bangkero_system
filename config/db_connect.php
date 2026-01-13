<?php
$servername = "sql12.freesqldatabase.com";
$username   = "sql12814263";
$password   = "W2VRUwnFv4";
$dbname     = "sql12814263";
$port       = 3306;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// Debug (pwede mo alisin pag ok na)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
