<?php
/**
 * FIX AUTO INCREMENT FOR ALL TABLES
 * This script will fix the auto_increment issue on all tables
 * 
 * ISSUE: When adding records, ID becomes 0 instead of auto-incrementing
 * SOLUTION: This script ensures all tables have proper AUTO_INCREMENT settings
 */

require_once 'db_connect.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Auto Increment - Database Repair Tool</title>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap' rel='stylesheet'>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', Arial, sans-serif; 
            padding: 40px 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h2 { 
            color: #667eea; 
            margin-bottom: 10px; 
            font-size: 2rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1rem;
            line-height: 1.6;
        }
        .log-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            max-height: 500px;
            overflow-y: auto;
            border: 2px solid #e9ecef;
        }
        .success { 
            color: #155724; 
            background: #d4edda; 
            padding: 12px 16px; 
            margin: 8px 0; 
            border-radius: 8px; 
            border-left: 4px solid #28a745;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .error { 
            color: #721c24; 
            background: #f8d7da; 
            padding: 12px 16px; 
            margin: 8px 0; 
            border-radius: 8px; 
            border-left: 4px solid #dc3545;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .info { 
            color: #004085; 
            background: #cce5ff; 
            padding: 12px 16px; 
            margin: 8px 0; 
            border-radius: 8px; 
            border-left: 4px solid #007bff;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .warning {
            color: #856404;
            background: #fff3cd;
            padding: 12px 16px;
            margin: 8px 0;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px;
            border-radius: 12px;
            margin: 30px 0;
        }
        .summary h3 {
            margin-bottom: 16px;
            font-size: 1.4rem;
        }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }
        .stat-box {
            background: rgba(255,255,255,0.2);
            padding: 16px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            display: block;
            margin-bottom: 4px;
        }
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
            margin-top: 20px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(102, 126, 234, 0.4);
        }
        .icon {
            font-size: 1.2em;
        }
        hr {
            border: none;
            border-top: 2px solid #e9ecef;
            margin: 30px 0;
        }
    </style>
</head>
<body>
<div class='container'>";

echo "<h2><span class='icon'>🔧</span> Database Auto-Increment Repair Tool</h2>";
echo "<p class='subtitle'>This tool fixes the AUTO_INCREMENT settings for all database tables to prevent ID=0 errors when adding new records.</p>";

echo "<div class='log-container'>";

// List of tables na kailangan i-fix
$tables = [
    'announcements',
    'members',
    'officers',
    'events',
    'galleries',
    'officer_roles',
    'archived_announcements',
    'archived_members',
    'archived_officers',
    'officers_archive',
    'archived_events',
    'contact_messages',
    'member_archive',
    'activity_logs'
];

$fixedCount = 0;
$errorCount = 0;
$skippedCount = 0;
$alreadyOkCount = 0;

foreach ($tables as $table) {
    echo "<div class='info'><span class='icon'>⚙️</span> <strong>Processing table:</strong> $table</div>";
    
    // Check if table exists
    $checkTable = $conn->query("SHOW TABLES LIKE '$table'");
    if (!$checkTable || $checkTable->num_rows == 0) {
        echo "<div class='warning'><span class='icon'>⚠️</span> Table '<strong>$table</strong>' does not exist. Skipping...</div>";
        $skippedCount++;
        continue;
    }
    
    // Get current structure
    $structure = $conn->query("DESCRIBE $table");
    $hasIdColumn = false;
    $isAutoIncrement = false;
    $currentType = '';
    
    while ($row = $structure->fetch_assoc()) {
        if ($row['Field'] == 'id') {
            $hasIdColumn = true;
            $currentType = $row['Type'];
            if (strpos($row['Extra'], 'auto_increment') !== false) {
                $isAutoIncrement = true;
            }
            break;
        }
    }
    
    if (!$hasIdColumn) {
        echo "<div class='warning'><span class='icon'>❌</span> No 'id' column found in '<strong>$table</strong>'. Skipping...</div>";
        $skippedCount++;
        continue;
    }
    
    if ($isAutoIncrement) {
        echo "<div class='success'><span class='icon'>✅</span> Table '<strong>$table</strong>' already has AUTO_INCREMENT. No fix needed.</div>";
        $alreadyOkCount++;
        continue;
    }
    
    // Fix the AUTO_INCREMENT
    try {
        // First, remove any records with id=0 (these are invalid)
        $deleteZero = $conn->query("DELETE FROM $table WHERE id = 0");
        
        // Get the maximum ID in the table
        $maxIdResult = $conn->query("SELECT MAX(id) as max_id FROM $table");
        $maxId = 0;
        if ($maxIdResult && $maxIdResult->num_rows > 0) {
            $row = $maxIdResult->fetch_assoc();
            $maxId = intval($row['max_id'] ?? 0);
        }
        
        // Set next auto_increment value
        $nextId = $maxId + 1;
        
        // Try to modify column - handle different scenarios
        $success = false;
        
        // Method 1: Try to modify with keeping primary key
        $sql1 = "ALTER TABLE $table MODIFY COLUMN id INT NOT NULL AUTO_INCREMENT";
        if (@$conn->query($sql1)) {
            $success = true;
        } else {
            // Method 2: Drop and recreate primary key
            @$conn->query("ALTER TABLE $table DROP PRIMARY KEY");
            $sql2 = "ALTER TABLE $table MODIFY COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY";
            if (@$conn->query($sql2)) {
                $success = true;
            }
        }
        
        if ($success) {
            // Set the auto_increment starting point
            $conn->query("ALTER TABLE $table AUTO_INCREMENT = $nextId");
            echo "<div class='success'><span class='icon'>✅</span> <strong>Fixed '$table'</strong> - AUTO_INCREMENT set to start at <strong>$nextId</strong></div>";
            $fixedCount++;
        } else {
            throw new Exception($conn->error);
        }
        
    } catch (Exception $e) {
        echo "<div class='error'><span class='icon'>❌</span> <strong>Error fixing '$table':</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
        $errorCount++;
    }
}

echo "</div>"; // Close log-container

// Summary
echo "<div class='summary'>";
echo "<h3>📊 Summary Report</h3>";
echo "<div class='summary-stats'>";
echo "<div class='stat-box'><span class='stat-number'>$fixedCount</span><span class='stat-label'>Tables Fixed</span></div>";
echo "<div class='stat-box'><span class='stat-number'>$alreadyOkCount</span><span class='stat-label'>Already OK</span></div>";
echo "<div class='stat-box'><span class='stat-number'>$skippedCount</span><span class='stat-label'>Skipped</span></div>";
if ($errorCount > 0) {
    echo "<div class='stat-box'><span class='stat-number'>$errorCount</span><span class='stat-label'>Errors</span></div>";
}
echo "</div>";

if ($errorCount == 0 && $fixedCount > 0) {
    echo "<p style='margin-top: 20px; font-size: 1.1rem;'>🎉 <strong>Success!</strong> All tables have been fixed. You can now add records without getting ID=0 errors.</p>";
} else if ($alreadyOkCount > 0 && $fixedCount == 0) {
    echo "<p style='margin-top: 20px; font-size: 1.1rem;'>✅ <strong>Good news!</strong> All tables already have proper AUTO_INCREMENT settings.</p>";
} else if ($errorCount > 0) {
    echo "<p style='margin-top: 20px; font-size: 1.1rem;'>⚠️ <strong>Partial fix completed.</strong> Some tables encountered errors. Please check the logs above.</p>";
}
echo "</div>";

echo "<hr>";
echo "<a href='../index/admin.php' class='btn'>← Back to Admin Panel</a>";

echo "</div></body></html>";

$conn->close();
?>
