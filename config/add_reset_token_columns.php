<?php
/**
 * MIGRATION: Add password reset token columns to users table
 * Run this once to ensure the table has the correct structure
 */

require_once __DIR__ . '/db_connect.php';

echo "Checking users table structure...\n";

// Check which columns exist
$result = $conn->query("SHOW COLUMNS FROM users");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

echo "Existing columns: " . implode(', ', $columns) . "\n\n";

// Add reset_token_hash column if not exists
if (!in_array('reset_token_hash', $columns)) {
    echo "Adding reset_token_hash column...\n";
    $conn->query("ALTER TABLE users ADD COLUMN reset_token_hash VARCHAR(64) NULL");
    echo "✓ reset_token_hash added\n";
} else {
    echo "✓ reset_token_hash already exists\n";
}

// Add reset_token_expires_at column if not exists
if (!in_array('reset_token_expires_at', $columns)) {
    echo "Adding reset_token_expires_at column...\n";
    $conn->query("ALTER TABLE users ADD COLUMN reset_token_expires_at DATETIME NULL");
    echo "✓ reset_token_expires_at added\n";
} else {
    echo "✓ reset_token_expires_at already exists\n";
}

// Remove old column names if they exist (for consistency)
if (in_array('reset_token', $columns)) {
    echo "\nNote: Old 'reset_token' column exists. You may want to migrate data or drop it.\n";
}
if (in_array('reset_expiry', $columns)) {
    echo "Note: Old 'reset_expiry' column exists. You may want to migrate data or drop it.\n";
}

echo "\n✅ Migration complete!\n";
