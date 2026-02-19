<?php
/**
 * Transparency Database Setup Script
 * Run this file once to create the required tables and sample data
 */

require_once(__DIR__ . '/config/db_connect.php');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Transparency Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #27ae60; }
        .success { color: #27ae60; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #27ae60; }
        .error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #0c5460; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; background: #27ae60; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .btn:hover { background: #229954; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🌟 Transparency Database Setup</h1>
        <p>This script will create the required database tables for the Transparency & Community Impact page.</p>";

try {
    // Read SQL file
    $sqlFile = __DIR__ . '/config/transparency_tables.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    if ($sql === false) {
        throw new Exception("Failed to read SQL file");
    }
    
    echo "<div class='info'><strong>📄 SQL File loaded successfully</strong></div>";
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', 
            preg_split('/;[\r\n]+/', $sql)
        ),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^--/', $stmt) && 
                   $stmt !== '';
        }
    );
    
    echo "<div class='info'><strong>📊 Found " . count($statements) . " SQL statements to execute</strong></div>";
    
    // Execute each statement
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        // Add semicolon back if needed
        if (!preg_match('/;$/', $statement)) {
            $statement .= ';';
        }
        
        try {
            if ($conn->query($statement) === TRUE) {
                $successCount++;
                // Show first line of statement for context
                $preview = substr($statement, 0, 60);
                echo "<div class='success'>✅ Statement " . ($index + 1) . " executed: " . htmlspecialchars($preview) . "...</div>";
            } else {
                throw new Exception($conn->error);
            }
        } catch (Exception $e) {
            $errorCount++;
            $preview = substr($statement, 0, 60);
            echo "<div class='error'>❌ Error in statement " . ($index + 1) . ": " . htmlspecialchars($e->getMessage()) . "<br><small>" . htmlspecialchars($preview) . "...</small></div>";
        }
    }
    
    echo "<hr>";
    echo "<h2>📈 Setup Summary</h2>";
    echo "<div class='info'>";
    echo "<strong>✅ Successful:</strong> $successCount statements<br>";
    echo "<strong>❌ Errors:</strong> $errorCount statements";
    echo "</div>";
    
    if ($errorCount === 0) {
        echo "<div class='success'>";
        echo "<h3>🎉 Setup Completed Successfully!</h3>";
        echo "<p>The database tables have been created and sample data has been inserted.</p>";
        echo "<p>You can now access the Transparency & Community Impact page.</p>";
        echo "</div>";
        
        echo "<a href='index/home/transparency.php' class='btn'>View Transparency Page</a>";
    } else {
        echo "<div class='error'>";
        echo "<h3>⚠️ Setup Completed with Errors</h3>";
        echo "<p>Some statements failed to execute. Please check the errors above.</p>";
        echo "<p>The page may still work if the tables were created successfully.</p>";
        echo "</div>";
    }
    
    // Show table status
    echo "<hr>";
    echo "<h2>📋 Database Tables Status</h2>";
    
    $tables = ['campaigns', 'donations'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            $count = $conn->query("SELECT COUNT(*) as count FROM `$table`")->fetch_assoc()['count'];
            echo "<div class='success'>✅ Table <strong>$table</strong> exists with <strong>$count</strong> records</div>";
        } else {
            echo "<div class='error'>❌ Table <strong>$table</strong> does not exist</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Fatal Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "
    </div>
</body>
</html>";

$conn->close();
?>
