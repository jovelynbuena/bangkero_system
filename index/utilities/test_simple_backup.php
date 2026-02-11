<?php
session_start();
require_once('../../config/db_connect.php');

// Display any session messages first
echo "<!DOCTYPE html>
<html>
<head>
    <title>Simple Backup Test</title>
    <style>
        body { font-family: Arial; padding: 30px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        form { margin: 20px 0; }
        button { padding: 12px 24px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>";

echo "<h1>🧪 Simple Backup Test</h1>";

// Show session messages
if (isset($_SESSION['success'])) {
    echo "<div class='success'><strong>✅ Success:</strong> " . $_SESSION['success'] . "</div>";
    if (isset($_SESSION['download_file'])) {
        $file = $_SESSION['download_file'];
        echo "<p><a href='download_backup.php?file=" . urlencode($file) . "' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>📥 Download: {$file}</a></p>";
        unset($_SESSION['download_file']);
    }
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo "<div class='error'><strong>❌ Error:</strong> " . $_SESSION['error'];
    if (isset($_SESSION['debug_error'])) {
        echo "<br><small>Debug: " . $_SESSION['debug_error'] . "</small>";
        unset($_SESSION['debug_error']);
    }
    echo "</div>";
    unset($_SESSION['error']);
}

// Show form
echo "<div class='info'>
    <strong>Test Instructions:</strong>
    <ul>
        <li>Click the button below to create a backup</li>
        <li>This will call backup_action.php directly</li>
        <li>You should see a success or error message after redirect</li>
    </ul>
</div>";

echo "<form method='post' action='backup_action.php'>
    <button type='submit' name='backup'>🔄 Create Backup via backup_action.php</button>
</form>";

echo "<hr>";
echo "<p><a href='backup.php'>← Back to Main Backup Page</a> | <a href='test_backup_complete.php'>Full Test Page</a></p>";

echo "</body></html>";
?>
