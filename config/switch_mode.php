<?php
// ============================================
// QUICK MODE SWITCHER
// Quickly switch between ONLINE and OFFLINE
// ============================================

session_start();

// Check if user is admin (optional security)
// Uncomment if you want only logged in users to switch
// if (!isset($_SESSION['user_id'])) {
//     die("Access denied. Please login first.");
// }

$configFile = __DIR__ . '/db_connect.php';
$mode = $_GET['mode'] ?? '';

if ($mode === 'online' || $mode === 'offline') {
    
    if ($mode === 'online') {
        $newContent = "<?php
// ============================================
// DATABASE CONNECTION SWITCHER
// Change between ONLINE and OFFLINE modes
// ============================================

// 🌐 ONLINE MODE (default) - Uses freesqldatabase.com
require_once(__DIR__ . '/db_connect_online.php');

// 💻 OFFLINE MODE - Uses localhost (uncomment for defense day)
// require_once(__DIR__ . '/db_connect_local.php');

// ============================================
// INSTRUCTIONS:
// - For normal use: Use ONLINE mode (current)
// - For defense/offline: Comment ONLINE, uncomment OFFLINE
// ============================================
?>";
    } else {
        $newContent = "<?php
// ============================================
// DATABASE CONNECTION SWITCHER
// Change between ONLINE and OFFLINE modes
// ============================================

// 🌐 ONLINE MODE (default) - Uses freesqldatabase.com
// require_once(__DIR__ . '/db_connect_online.php');

// 💻 OFFLINE MODE - Uses localhost (uncomment for defense day)
require_once(__DIR__ . '/db_connect_local.php');

// ============================================
// INSTRUCTIONS:
// - For normal use: Use ONLINE mode (current)
// - For defense/offline: Comment ONLINE, uncomment OFFLINE
// ============================================
?>";
    }
    
    file_put_contents($configFile, $newContent);
    $message = "✅ Switched to " . strtoupper($mode) . " mode successfully!";
    $success = true;
} else {
    $message = "Invalid mode. Use ?mode=online or ?mode=offline";
    $success = false;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Mode Switcher</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .container { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 500px; width: 100%; }
        h1 { font-size: 24px; margin-bottom: 10px; color: #1a1a1a; }
        p { color: #666; margin-bottom: 30px; }
        .mode-buttons { display: flex; gap: 15px; margin-bottom: 30px; }
        .btn { flex: 1; padding: 15px; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-online { background: #28a745; color: white; }
        .btn-online:hover { background: #218838; transform: translateY(-2px); }
        .btn-offline { background: #0d6efd; color: white; }
        .btn-offline:hover { background: #0b5ed7; transform: translateY(-2px); }
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .links { display: flex; gap: 10px; margin-top: 20px; }
        .link { flex: 1; padding: 10px; background: #f8f9fa; border-radius: 8px; text-align: center; text-decoration: none; color: #495057; font-size: 14px; transition: all 0.3s; }
        .link:hover { background: #e9ecef; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Database Mode Switcher</h1>
        <p>Switch between online and offline database modes</p>
        
        <?php if ($mode): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="mode-buttons">
            <a href="?mode=online" class="btn btn-online">
                🌐 ONLINE Mode
            </a>
            <a href="?mode=offline" class="btn btn-offline">
                💻 OFFLINE Mode
            </a>
        </div>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; font-size: 14px; color: #495057;">
            <strong>Current Modes:</strong><br>
            • <strong>ONLINE</strong> - freesqldatabase.com (with internet)<br>
            • <strong>OFFLINE</strong> - localhost (no internet needed)
        </div>
        
        <div class="links">
            <a href="test_connection.php" class="link">🔍 Test Connection</a>
            <a href="../index/login.php" class="link">🏠 Go to System</a>
        </div>
    </div>
</body>
</html>
