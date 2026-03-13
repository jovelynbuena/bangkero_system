<?php
/**
 * MIGRATION: Add force_password_change column to users table
 * Run this once to enable the admin password reset feature
 */

require_once __DIR__ . '/db_connect.php';

echo "Checking users table for force_password_change column...\n";

// Check if column exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'force_password_change'");

if ($result && $result->num_rows > 0) {
    echo "✓ Column 'force_password_change' already exists.\n";
} else {
    // Add the column
    $sql = "ALTER TABLE users ADD COLUMN force_password_change TINYINT(1) DEFAULT 0 AFTER password_hash";
    if ($conn->query($sql)) {
        echo "✓ Column 'force_password_change' added successfully.\n";
    } else {
        echo "✗ Error adding column: " . $conn->error . "\n";
    }
}

// Also add temp_password column for storing temporary passwords
$result2 = $conn->query("SHOW COLUMNS FROM users LIKE 'temp_password'");

if ($result2 && $result2->num_rows > 0) {
    echo "✓ Column 'temp_password' already exists.\n";
} else {
    $sql2 = "ALTER TABLE users ADD COLUMN temp_password VARCHAR(255) NULL AFTER force_password_change";
    if ($conn->query($sql2)) {
        echo "✓ Column 'temp_password' added successfully.\n";
    } else {
        echo "✗ Error adding column: " . $conn->error . "\n";
    }
}

echo "\nMigration complete!\n";
?>
