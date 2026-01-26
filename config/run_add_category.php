<?php
/**
 * Run this file once to add category column to announcements table
 * Access via: http://localhost/bangkero_system/config/run_add_category.php
 */

include('db_connect.php');

// Read and execute SQL file
$sql = file_get_contents(__DIR__ . '/add_category_to_announcements.sql');

// Split by semicolon to execute multiple statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success = true;
$messages = [];

foreach ($statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) continue;
    
    if ($conn->query($statement) === TRUE) {
        $messages[] = "✓ Statement executed successfully";
    } else {
        $success = false;
        $messages[] = "✗ Error: " . $conn->error;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Category Column Migration</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; }
        .success { color: #27ae60; padding: 10px; background: #e8f8f0; border-radius: 4px; margin: 10px 0; }
        .error { color: #e74c3c; padding: 10px; background: #fdecea; border-radius: 4px; margin: 10px 0; }
        .message { padding: 8px; margin: 5px 0; background: #ecf0f1; border-radius: 4px; }
        .info { color: #34495e; margin: 20px 0; padding: 15px; background: #e3f2fd; border-left: 4px solid #2196f3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Add Category Column to Announcements</h1>
        
        <?php if ($success): ?>
            <div class="success">
                <strong>✓ Migration completed successfully!</strong>
            </div>
        <?php else: ?>
            <div class="error">
                <strong>✗ Migration failed. Please check the errors below.</strong>
            </div>
        <?php endif; ?>
        
        <h3>Execution Log:</h3>
        <?php foreach ($messages as $msg): ?>
            <div class="message"><?= $msg ?></div>
        <?php endforeach; ?>
        
        <div class="info">
            <strong>ℹ️ Next Steps:</strong><br>
            <ol>
                <li>Go to your admin panel</li>
                <li>Edit announcements and set their category to 'news' or 'general'</li>
                <li>The News filter will now work on the announcements page</li>
            </ol>
            <p><strong>Note:</strong> By default, all announcements are set to 'general' category.</p>
        </div>
        
        <p><a href="../index/home/announcement.php" style="color: #2c3e50;">← Back to Announcements</a></p>
    </div>
</body>
</html>
