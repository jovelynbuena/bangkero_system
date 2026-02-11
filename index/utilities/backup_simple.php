<?php 
include('../navbar.php');

// Get existing backups
$backupDir = __DIR__ . '/backups/';
$backups = array();

if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $filePath = $backupDir . $file;
            $backups[] = array(
                'name' => $file,
                'size' => filesize($filePath),
                'date' => filemtime($filePath)
            );
        }
    }
    usort($backups, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Backup Test - Simple Version</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #f5f5f5; }
        .main-content { margin-left: 250px; padding: 30px; min-height: 100vh; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 20px; }
        h1 { font-size: 28px; font-weight: 700; color: #1a1a1a; margin-bottom: 30px; }
        .btn-test { padding: 12px 30px; font-size: 16px; font-weight: 600; border-radius: 8px; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="main-content">
    <div class="container-fluid">
        <h1>🧪 Backup Test - Simple Version (NO DIALOGS)</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <strong>✅ SUCCESS!</strong><br>
                <?php echo $_SESSION['success']; ?>
                <?php if (isset($_SESSION['download_file'])): ?>
                    <br><a href="download_backup.php?file=<?php echo urlencode($_SESSION['download_file']); ?>" class="btn btn-sm btn-primary mt-2">Download: <?php echo $_SESSION['download_file']; ?></a>
                    <?php unset($_SESSION['download_file']); ?>
                <?php endif; ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <strong>❌ ERROR!</strong><br>
                <?php echo $_SESSION['error']; ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="card">
            <h3>Create Backup (Direct - No Confirmation)</h3>
            <p>This will immediately create a backup when you click the button.</p>
            <form method="post" action="backup_action.php">
                <button type="submit" name="backup" class="btn btn-primary btn-test">
                    <i class="bi bi-download"></i> Create Backup NOW
                </button>
            </form>
        </div>
        
        <div class="card">
            <h3>Backup History (<?php echo count($backups); ?> files)</h3>
            <?php if (count($backups) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Size</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($backup['name']); ?></td>
                                <td><?php echo formatFileSize($backup['size']); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', $backup['date']); ?></td>
                                <td>
                                    <a href="download_backup.php?file=<?php echo urlencode($backup['name']); ?>" class="btn btn-sm btn-primary">Download</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No backups found.</p>
            <?php endif; ?>
        </div>
        
        <a href="backup.php" class="btn btn-secondary">← Back to Fancy Version</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
