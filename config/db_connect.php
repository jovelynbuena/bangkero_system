<?php
$servername = "localhost";
$username = "root";  // Default XAMPP username
$password = "";      // Default is empty
$database = "bangkero_association";

// Create connection with error handling
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set character encoding to UTF-8 for better compatibility
$conn->set_charset("utf8");

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
