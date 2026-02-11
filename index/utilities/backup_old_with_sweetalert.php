<?php 
include('../navbar.php'); // sidebar + top navbar

// Create tables if they don't exist
try {
    require_once('../../config/db_connect.php');
    
    // Create backups table
    $conn->query("CREATE TABLE IF NOT EXISTS `backups` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `filename` varchar(255) NOT NULL,
        `filesize` bigint(20) NOT NULL,
        `created_by` int(11) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    
    // Create activity_logs table
    $conn->query("CREATE TABLE IF NOT EXISTS `activity_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `action` varchar(100) NOT NULL,
        `description` text,
        `ip_address` varchar(50),
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
} catch (Exception $e) {
    // Continue even if table creation fails
}

// Get existing backups from file system
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
    // Sort by date, newest first
    usort($backups, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

// Helper function to format file size
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Restore - Bangkero System</title>
    
    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons 1.11.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f5f5f5;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
        }

        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }

        .action-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        .action-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }

        .action-card h5 {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .btn-action {
            padding: 10px 24px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .backup-list-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 25px;
        }

        .backup-list-card h5 {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .backup-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
        }

        .backup-item:hover {
            background: #e9ecef;
            border-color: #dee2e6;
        }

        .backup-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .backup-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            flex-shrink: 0;
        }

        .backup-details {
            flex-grow: 1;
        }

        .backup-name {
            font-weight: 600;
            color: #1a1a1a;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .backup-meta {
            font-size: 12px;
            color: #666;
        }

        .backup-actions {
            display: flex;
            gap: 8px;
        }

        .btn-sm-action {
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-sm-action:hover {
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-input-label {
            display: inline-block;
            padding: 10px 20px;
            cursor: pointer;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            width: 100%;
            text-align: center;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .file-input-label.has-file {
            background: #e7f5ff;
            border-color: #339af0;
            color: #1971c2;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .backup-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .backup-actions {
                width: 100%;
                margin-top: 10px;
            }

            .backup-actions .btn {
                flex: 1;
            }
        }
    </style>
</head>
<body>

<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1>
                <i class="bi bi-shield-check" style="color: #667eea;"></i>
                Backup & Restore
            </h1>
            <p>Manage database backups and restore points for data protection and recovery</p>
        </div>

        <div class="row">
            <!-- Backup Section -->
            <div class="col-lg-6 mb-4">
                <div class="action-card">
                    <h5>
                        <i class="bi bi-cloud-arrow-down text-primary"></i>
                        Create Database Backup
                    </h5>
                    <p>Generate a complete backup of your database. The backup file will be downloaded automatically.</p>
                    <form method="post" action="backup_action.php" id="backupForm">
                        <button type="submit" name="backup" class="btn btn-primary btn-action">
                            <i class="bi bi-download"></i>
                            Create Backup Now
                        </button>
                    </form>
                </div>
            </div>

            <!-- Restore Section -->
            <div class="col-lg-6 mb-4">
                <div class="action-card">
                    <h5>
                        <i class="bi bi-arrow-counterclockwise text-success"></i>
                        Restore Database
                    </h5>
                    <p>Upload a backup SQL file to restore your database to a previous state.</p>
                    <form method="post" action="restore_action.php" enctype="multipart/form-data" id="restoreForm">
                        <div class="mb-3">
                            <div class="file-input-wrapper">
                                <input type="file" name="sql_file" id="sql_file" accept=".sql" required onchange="updateFileName(this)">
                                <label for="sql_file" class="file-input-label" id="fileLabel">
                                    <i class="bi bi-cloud-upload"></i>
                                    <span id="fileName">Choose SQL backup file...</span>
                                </label>
                            </div>
                        </div>
                        <button type="submit" name="restore" class="btn btn-success btn-action">
                            <i class="bi bi-upload"></i>
                            Restore Database
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Backup History -->
        <div class="backup-list-card">
            <h5>
                <i class="bi bi-clock-history text-info"></i>
                Backup History
            </h5>
            
            <?php if (count($backups) > 0): ?>
                <div class="backup-list">
                    <?php foreach ($backups as $backup): ?>
                        <div class="backup-item">
                            <div class="backup-info">
                                <div class="backup-icon">
                                    <i class="bi bi-database"></i>
                                </div>
                                <div class="backup-details">
                                    <div class="backup-name"><?php echo htmlspecialchars($backup['name']); ?></div>
                                    <div class="backup-meta">
                                        <i class="bi bi-calendar3"></i> <?php echo date('F j, Y - g:i A', $backup['date']); ?>
                                        <span class="ms-3"><i class="bi bi-hdd"></i> <?php echo formatFileSize($backup['size']); ?></span>
                                    </div>
                                </div>
                                <div class="backup-actions">
                                    <a href="download_backup.php?file=<?php echo urlencode($backup['name']); ?>" 
                                       class="btn btn-sm btn-outline-primary btn-sm-action">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger btn-sm-action" 
                                            onclick="deleteBackup('<?php echo htmlspecialchars($backup['name']); ?>')">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>No backup files found. Create your first backup to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Update file name display
function updateFileName(input) {
    const fileLabel = document.getElementById('fileLabel');
    const fileName = document.getElementById('fileName');
    
    if (input.files && input.files[0]) {
        fileName.textContent = input.files[0].name;
        fileLabel.classList.add('has-file');
    } else {
        fileName.textContent = 'Choose SQL backup file...';
        fileLabel.classList.remove('has-file');
    }
}

// Delete backup
function deleteBackup(filename) {
    Swal.fire({
        title: 'Delete Backup?',
        text: `Are you sure you want to delete "${filename}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send delete request
            fetch('delete_backup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'filename=' + encodeURIComponent(filename)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Backup file has been deleted.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to delete backup file.'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while deleting the backup.'
                });
            });
        }
    });
}

// Backup form confirmation
const backupForm = document.getElementById('backupForm');
if (backupForm) {
    backupForm.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const formElement = e.target; // Get form from event
        
        Swal.fire({
            title: 'Create Backup?',
            text: 'This will create a complete backup of your database.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, create backup',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Creating Backup...',
                    text: 'Please wait while we backup your database',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit form directly using HTMLFormElement.submit()
                setTimeout(() => {
                    formElement.submit();
                }, 500);
            }
        });
    });
}

// Restore form confirmation
document.getElementById('restoreForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('sql_file');
    if (!fileInput.files || !fileInput.files[0]) {
        Swal.fire({
            icon: 'warning',
            title: 'No File Selected',
            text: 'Please select an SQL file to restore.'
        });
        return;
    }

    Swal.fire({
        title: 'Restore Database?',
        html: '<strong style="color: #dc3545;">WARNING:</strong> This will replace your current database with the backup file.<br><br>Make sure you have a recent backup before proceeding.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, restore it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Restoring Database...',
                text: 'Please wait, this may take a few moments',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit form
            this.submit();
        }
    });
});

// Show session messages
<?php if (isset($_SESSION['success'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?php echo addslashes($_SESSION['success']); ?>',
        timer: 3000,
        showConfirmButton: false
    }).then(() => {
        // Auto-download the backup file if available
        <?php if (isset($_SESSION['download_file'])): ?>
            window.location.href = 'download_backup.php?file=<?php echo urlencode($_SESSION['download_file']); ?>';
            <?php unset($_SESSION['download_file']); ?>
        <?php endif; ?>
    });
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        html: '<?php echo addslashes($_SESSION['error']); ?><?php if (isset($_SESSION['debug_error'])): ?><br><br><small style="color: #999;">Debug: <?php echo addslashes($_SESSION['debug_error']); ?></small><?php endif; ?>'
    });
    <?php unset($_SESSION['error']); ?>
    <?php unset($_SESSION['debug_error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['warning'])): ?>
    Swal.fire({
        icon: 'warning',
        title: 'Warning',
        text: '<?php echo addslashes($_SESSION['warning']); ?>'
    });
    <?php unset($_SESSION['warning']); ?>
<?php endif; ?>
</script>

</body>
</html>
