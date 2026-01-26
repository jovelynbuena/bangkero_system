<?php
require_once('db_connect.php');

echo "<h3>Checking system_config table...</h3>";

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'system_config'");
if ($result->num_rows == 0) {
    echo "<p style='color: red;'>Table 'system_config' does NOT exist!</p>";
    echo "<p>Creating table...</p>";
    
    $createTable = "CREATE TABLE IF NOT EXISTS system_config (
        id INT PRIMARY KEY,
        assoc_name VARCHAR(255) DEFAULT '',
        assoc_email VARCHAR(255) DEFAULT '',
        assoc_phone VARCHAR(100) DEFAULT '',
        assoc_address TEXT DEFAULT '',
        assoc_logo VARCHAR(255) DEFAULT ''
    )";
    
    if ($conn->query($createTable)) {
        echo "<p style='color: green;'>Table created successfully!</p>";
        
        // Insert default record
        $insertDefault = "INSERT INTO system_config (id, assoc_name, assoc_email, assoc_phone, assoc_address, assoc_logo)
                         VALUES (1, 'Bangkero & Fishermen Association', 'info@association.org', '+63 912 345 6789', '123 Association Street, Olongapo City, Philippines', '')";
        
        if ($conn->query($insertDefault)) {
            echo "<p style='color: green;'>Default record inserted!</p>";
        }
    }
} else {
    echo "<p style='color: green;'>Table 'system_config' exists!</p>";
}

// Check data
$result = $conn->query("SELECT * FROM system_config WHERE id=1");
if ($result && $result->num_rows > 0) {
    $config = $result->fetch_assoc();
    echo "<h4>Current Configuration:</h4>";
    echo "<pre>";
    print_r($config);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>No configuration record found!</p>";
    echo "<p>Inserting default record...</p>";
    
    $insertDefault = "INSERT INTO system_config (id, assoc_name, assoc_email, assoc_phone, assoc_address, assoc_logo)
                     VALUES (1, 'Bangkero & Fishermen Association', 'info@association.org', '+63 912 345 6789', '123 Association Street, Olongapo City, Philippines', '')";
    
    if ($conn->query($insertDefault)) {
        echo "<p style='color: green;'>Default record inserted successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
    }
}

echo "<br><a href='../index/settings/config.php'>Go to Config Page</a>";
?>
