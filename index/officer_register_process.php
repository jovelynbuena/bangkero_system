<?php
session_start();
require '../config/db_connect.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'officer';
    $status = 'pending'; // pending by default until admin approves
    $created_at = date('Y-m-d H:i:s');

    // Check if username or email already exists
    $check = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Username or Email already exists!'); window.location='officer_register.php';</script>";
        exit;
    }

    // Insert new officer record
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role, status, created_at)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $email, $password, $role, $status, $created_at);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful! Wait for admin approval.'); window.location='../login.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "'); window.location='officer_register.php';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header('Location: officer_register.php');
    exit;
}
?>
