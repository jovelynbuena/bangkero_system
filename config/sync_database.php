<?php
// ============================================
// DATABASE RESET & SYNC TOOL
// Replace localhost database with online backup
// ============================================

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Optional: Uncomment to require login
// if (!isset($_SESSION['user_id'])) {
//     die("Please login first");
// }

$step = $_GET['step'] ?? 'start';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Sync Tool</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f5f5f5; padding: 40px 20px; }
        .container { max-width: 700px; margin: 0 auto; background: white; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { font-size: 28px; margin-bottom: 10px; color: #1a1a1a; display: flex; align-items: center; gap: 12px; }
        .subtitle { color: #666; margin-bottom: 30px; font-size: 14px; }
        .step { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #0d6efd; }
        .step h3 { font-size: 16px; margin-bottom: 10px; color: #1a1a1a; }
        .step p { color: #666; font-size: 14px; margin-bottom: 15px; }
        .btn { padding: 12px 30px; background: #0d6efd; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; transition: all 0.3s; }
        .btn:hover { background: #0b5ed7; transform: translateY(-2px); }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; color: #856404; margin-bottom: 20px; }
        .success { background: #d4edda; border: 1px solid #28a745; padding: 15px; border-radius: 8px; color: #155724; margin-bottom: 20px; }
        .error { background: #f8d7da; border: 1px solid #dc3545; padding: 15px; border-radius: 8px; color: #721c24; margin-bottom: 20px; }
        .progress { margin: 20px 0; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 8px; overflow-x: auto; font-size: 13px; }
        .back-link { display: inline-block; margin-top: 20px; color: #0d6efd; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($step === 'start'): ?>
            
            <h1>🔄 Database Sync Tool</h1>
            <p class="subtitle">Replace old localhost database with fresh online backup</p>
            
            <div class="warning">
                <strong>⚠️ WARNING:</strong> This will permanently delete all data in your LOCAL database (bangkero_local) and replace it with online data.
            </div>
            
            <div class="step">
                <h3>📋 What this tool does:</h3>
                <p>
                    1️⃣ Create fresh backup from ONLINE database<br>
                    2️⃣ Drop all tables in LOCAL database<br>
                    3️⃣ Import fresh data to LOCAL database<br>
                    4️⃣ Verify sync is successful
                </p>
            </div>
            
            <div class="step">
                <h3>✅ Requirements:</h3>
                <p>
                    • XAMPP MySQL must be running<br>
                    • "bangkero_local" database must exist<br>
                    • Internet connection (to backup online DB)<br>
                    • You must be logged in to the system
                </p>
            </div>
            
            <a href="?step=backup" class="btn">Start Sync Process →</a>
            <a href="../index/utilities/backup.php" class="back-link">← Back to Backup Page</a>
            
        <?php elseif ($step === 'backup'): ?>
            
            <h1>📥 Step 1: Create Online Backup</h1>
            <p class="subtitle">Backing up current online database...</p>
            
            <?php
            try {
                // Connect to ONLINE database
                require_once(__DIR__ . '/db_connect_online.php');
                
                $backupFileName = 'sync_backup_' . date('Y-m-d_H-i-s') . '.sql';
                $backupDir = __DIR__ . '/../index/utilities/backups/';
                $backupFilePath = $backupDir . $backupFileName;
                
                if (!file_exists($backupDir)) {
                    mkdir($backupDir, 0755, true);
                }
                
                echo "<div class='progress'>Creating backup...<br>";
                
                // Get all tables
                $tables = array();
                $result = $conn->query("SHOW TABLES");
                if ($result) {
                    while ($row = $result->fetch_row()) {
                        $tables[] = $row[0];
                    }
                }
                
                echo "Found " . count($tables) . " tables<br>";
                
                // Build SQL content
                $sqlContent = "-- Database Sync Backup\n";
                $sqlContent .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
                $sqlContent .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
                $sqlContent .= "SET time_zone = \"+00:00\";\n\n";
                
                foreach ($tables as $table) {
                    echo "Processing: {$table}<br>";
                    
                    $sqlContent .= "\n-- Table: {$table}\n";
                    $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n";
                    
                    $createResult = $conn->query("SHOW CREATE TABLE `{$table}`");
                    if ($createResult) {
                        $row = $createResult->fetch_row();
                        $sqlContent .= $row[1] . ";\n\n";
                    }
                    
                    $dataResult = $conn->query("SELECT * FROM `{$table}`");
                    if ($dataResult && $dataResult->num_rows > 0) {
                        while ($row = $dataResult->fetch_assoc()) {
                            $columns = array_keys($row);
                            $values = array_values($row);
                            
                            $escapedValues = array_map(function($value) use ($conn) {
                                if ($value === null) return 'NULL';
                                return "'" . $conn->real_escape_string($value) . "'";
                            }, $values);
                            
                            $sqlContent .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n";
                        }
                    }
                }
                
                file_put_contents($backupFilePath, $sqlContent);
                $conn->close();
                
                echo "<br>✅ Backup created: " . $backupFileName . "</div>";
                
                echo "<div class='success'>";
                echo "<strong>✅ Step 1 Complete!</strong><br>";
                echo "Backup file: " . $backupFileName . "<br>";
                echo "Size: " . number_format(filesize($backupFilePath) / 1024, 2) . " KB";
                echo "</div>";
                
                echo "<a href='?step=import&file=" . urlencode($backupFileName) . "' class='btn'>Next: Import to Localhost →</a>";
                
            } catch (Exception $e) {
                echo "<div class='error'>";
                echo "<strong>❌ Error:</strong><br>";
                echo $e->getMessage();
                echo "</div>";
                echo "<a href='?step=start' class='back-link'>← Back</a>";
            }
            ?>
            
        <?php elseif ($step === 'import'): ?>
            
            <h1>📤 Step 2: Import to Localhost</h1>
            <p class="subtitle">Importing data to local database...</p>
            
            <?php
            try {
                $backupFileName = $_GET['file'] ?? '';
                if (empty($backupFileName)) {
                    throw new Exception("No backup file specified");
                }
                
                $backupDir = __DIR__ . '/../index/utilities/backups/';
                $backupFilePath = $backupDir . basename($backupFileName);
                
                if (!file_exists($backupFilePath)) {
                    throw new Exception("Backup file not found");
                }
                
                echo "<div class='progress'>";
                echo "Reading backup file...<br>";
                
                $sqlContent = file_get_contents($backupFilePath);
                
                // Connect to LOCAL database
                $localConn = new mysqli('localhost', 'root', '', 'bangkero_local', 3306);
                
                if ($localConn->connect_error) {
                    throw new Exception("Cannot connect to localhost: " . $localConn->connect_error);
                }
                
                echo "Connected to localhost database<br>";
                echo "Dropping old tables...<br>";
                
                // Drop existing tables
                $localConn->query("SET FOREIGN_KEY_CHECKS = 0");
                $result = $localConn->query("SHOW TABLES");
                if ($result) {
                    while ($row = $result->fetch_row()) {
                        $localConn->query("DROP TABLE IF EXISTS `{$row[0]}`");
                        echo "Dropped: {$row[0]}<br>";
                    }
                }
                
                echo "<br>Importing new data...<br>";
                
                // Import new data
                $queries = array_filter(
                    array_map('trim', explode(';', $sqlContent)),
                    function($query) {
                        return !empty($query) && 
                               !preg_match('/^--/', $query) && 
                               !preg_match('/^\/\*/', $query);
                    }
                );
                
                $successCount = 0;
                foreach ($queries as $query) {
                    if ($localConn->query($query)) {
                        $successCount++;
                    }
                }
                
                $localConn->query("SET FOREIGN_KEY_CHECKS = 1");
                $localConn->close();
                
                echo "<br>✅ Imported {$successCount} queries</div>";
                
                echo "<div class='success'>";
                echo "<strong>✅ Sync Complete!</strong><br>";
                echo "Your localhost database is now up-to-date with online data.<br>";
                echo "Total queries executed: {$successCount}";
                echo "</div>";
                
                echo "<a href='test_connection.php' class='btn btn-success'>Test Connection</a> ";
                echo "<a href='switch_mode.php' class='btn'>Switch to Offline Mode</a>";
                
            } catch (Exception $e) {
                echo "<div class='error'>";
                echo "<strong>❌ Error:</strong><br>";
                echo $e->getMessage();
                echo "</div>";
                echo "<a href='?step=start' class='back-link'>← Start Over</a>";
            }
            ?>
            
        <?php endif; ?>
        
        <a href="../index/utilities/backup.php" class="back-link" style="display: block; margin-top: 20px;">← Back to Backup Page</a>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
