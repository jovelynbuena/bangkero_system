<?php
session_start();
require_once('../../config/db_connect.php');

// Only allow admin
$role = strtolower($_SESSION['role'] ?? 'guest');
if ($role !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Debug: Check if table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'system_config'");
if (!$tableCheck || $tableCheck->num_rows == 0) {
    // Create table if not exists
    $createTable = "CREATE TABLE IF NOT EXISTS system_config (
        id INT PRIMARY KEY,
        assoc_name VARCHAR(255) DEFAULT '',
        assoc_email VARCHAR(255) DEFAULT '',
        assoc_phone VARCHAR(100) DEFAULT '',
        assoc_address TEXT DEFAULT '',
        assoc_logo VARCHAR(255) DEFAULT ''
    )";
    $conn->query($createTable);
    
    // Insert default record
    $conn->query("INSERT IGNORE INTO system_config (id, assoc_name, assoc_email, assoc_phone, assoc_address, assoc_logo)
                 VALUES (1, 'Bangkero & Fishermen Association', 'info@association.org', '+63 912 345 6789', '123 Association Street, Olongapo City, Philippines', '')");
}

// Ensure extended columns for auto-backup exist
$colCheck = $conn->query("SHOW COLUMNS FROM system_config LIKE 'auto_backup_status'");
if (!$colCheck || $colCheck->num_rows == 0) {
    $conn->query("ALTER TABLE system_config ADD COLUMN auto_backup_status TINYINT(1) NOT NULL DEFAULT 0");
}
$colCheck = $conn->query("SHOW COLUMNS FROM system_config LIKE 'backup_storage_limit_mb'");
if (!$colCheck || $colCheck->num_rows == 0) {
    $conn->query("ALTER TABLE system_config ADD COLUMN backup_storage_limit_mb INT NOT NULL DEFAULT 100");
}
$colCheck = $conn->query("SHOW COLUMNS FROM system_config LIKE 'auto_backup_next_run'");
if (!$colCheck || $colCheck->num_rows == 0) {
    $conn->query("ALTER TABLE system_config ADD COLUMN auto_backup_next_run DATETIME NULL");
}

// Fetch existing config
$configResult = $conn->query("SELECT * FROM system_config WHERE id=1");
$config = $configResult && $configResult->num_rows > 0 ? $configResult->fetch_assoc() : null;

// Set default values
$assocName = $config['assoc_name'] ?? '';
$assocEmail = $config['assoc_email'] ?? '';
$assocPhone = $config['assoc_phone'] ?? '';
$assocAddress = $config['assoc_address'] ?? '';
$assocLogo = $config['assoc_logo'] ?? '';
$autoBackupStatus = isset($config['auto_backup_status']) ? (int)$config['auto_backup_status'] : 0;
$backupStorageLimitMb = isset($config['backup_storage_limit_mb']) ? (int)$config['backup_storage_limit_mb'] : 100;
if ($backupStorageLimitMb <= 0) { $backupStorageLimitMb = 100; }

// Handle form submission
if (isset($_POST['save_config'])) {
    $assocName = $conn->real_escape_string($_POST['assoc_name']);
    $assocEmail = $conn->real_escape_string($_POST['assoc_email']);
    $assocPhone = $conn->real_escape_string($_POST['assoc_phone']);
    $assocAddress = $conn->real_escape_string($_POST['assoc_address']);

    // Auto-backup settings
    $autoBackupStatus = isset($_POST['auto_backup_status']) ? 1 : 0;
    $backupStorageLimitMb = isset($_POST['backup_storage_limit_mb']) ? (int)$_POST['backup_storage_limit_mb'] : 100;
    if ($backupStorageLimitMb <= 0) { $backupStorageLimitMb = 100; }
    
    // Keep current logo if no new upload
    $assocLogo = $config['assoc_logo'] ?? '';

    // Handle logo upload
    if (isset($_FILES['assoc_logo']) && $_FILES['assoc_logo']['error'] === 0) {
        $uploadDir = __DIR__ . '/../uploads/config/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileTmp = $_FILES['assoc_logo']['tmp_name'];
        $fileName = basename($_FILES['assoc_logo']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];

        if (in_array($fileExt, $allowed)) {
            $newFileName = 'assoc_logo.' . $fileExt;
            $destination = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmp, $destination)) {
                $assocLogo = $newFileName;
            }
        }
    }

    // Always use UPDATE or INSERT based on record existence
    $checkSql = "SELECT id FROM system_config WHERE id=1";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        // Update existing record
        $sql = "UPDATE system_config SET
                assoc_name='$assocName',
                assoc_email='$assocEmail',
                assoc_phone='$assocPhone',
                assoc_address='$assocAddress',
                assoc_logo='$assocLogo',
                auto_backup_status=$autoBackupStatus,
                backup_storage_limit_mb=$backupStorageLimitMb
                WHERE id=1";
    } else {
        // Insert new record
        $sql = "INSERT INTO system_config (id, assoc_name, assoc_email, assoc_phone, assoc_address, assoc_logo, auto_backup_status, backup_storage_limit_mb)
                VALUES (1, '$assocName', '$assocEmail', '$assocPhone', '$assocAddress', '$assocLogo', $autoBackupStatus, $backupStorageLimitMb)";
    }

    if ($conn->query($sql)) {
        $success = true;
        // Re-fetch config to show updated values
        $configResult = $conn->query("SELECT * FROM system_config WHERE id=1");
        if ($configResult && $configResult->num_rows > 0) {
            $config = $configResult->fetch_assoc();
            $assocName = $config['assoc_name'];
            $assocEmail = $config['assoc_email'];
            $assocPhone = $config['assoc_phone'];
            $assocAddress = $config['assoc_address'];
            $assocLogo = $config['assoc_logo'];
            $autoBackupStatus = isset($config['auto_backup_status']) ? (int)$config['auto_backup_status'] : 0;
            $backupStorageLimitMb = isset($config['backup_storage_limit_mb']) ? (int)$config['backup_storage_limit_mb'] : 100;
        }
    } else {
        $error = "Database Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>System Configuration | Bangkero & Fishermen Association</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
}

.main-content { 
    margin-left: 250px; 
    padding: 32px; 
    min-height: 100vh; 
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.2);
    color: white;
}

.page-header h2 {
    font-weight: 700;
    margin: 0 0 10px 0;
    font-size: 2rem;
}

.page-header p {
    margin: 0;
    opacity: 0.95;
    font-size: 1.05rem;
}

.config-card {
    border-radius: 16px;
    background: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 24px;
    overflow: hidden;
}

.card-header-custom {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 20px 30px;
    font-weight: 600;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
}

.card-header-custom i {
    margin-right: 12px;
    font-size: 1.3rem;
}

.card-body-custom {
    padding: 32px;
}

.logo-preview-container {
    position: relative;
    width: 160px;
    height: 160px;
    margin: 0 auto 24px;
    border: 4px dashed #667eea;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    overflow: hidden;
    transition: all 0.3s ease;
}

.logo-preview-container:hover {
    border-color: #764ba2;
    transform: scale(1.02);
}

.hero-logo {
    max-height: 140px;
    max-width: 140px;
    width: auto;
    height: auto;
    object-fit: contain;
}

.upload-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(102, 126, 234, 0.9);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
    cursor: pointer;
    color: white;
}

.logo-preview-container:hover .upload-overlay {
    opacity: 1;
}

.upload-overlay i {
    font-size: 2rem;
    margin-bottom: 8px;
}

.form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
}

.form-label i {
    margin-right: 8px;
    color: #667eea;
}

.form-control, .form-select {
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px 16px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.save-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 10px;
    padding: 14px 32px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.info-box {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    border-left: 4px solid #667eea;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 24px;
}

.info-box i {
    color: #667eea;
    margin-right: 10px;
}

.file-upload-wrapper {
    position: relative;
    overflow: hidden;
    display: inline-block;
    width: 100%;
}

.file-upload-wrapper input[type=file] {
    position: absolute;
    left: -9999px;
}

.file-upload-label {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px 20px;
    background: white;
    border: 2px dashed #667eea;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #667eea;
    font-weight: 500;
}

.file-upload-label:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    border-color: #764ba2;
}

.file-upload-label i {
    margin-right: 10px;
    font-size: 1.2rem;
}

.selected-file {
    margin-top: 8px;
    padding: 8px 12px;
    background: #f3f4f6;
    border-radius: 6px;
    font-size: 0.85rem;
    display: none;
}

@media (max-width: 991.98px) {
    .main-content {
        margin-left: 0;
        padding: 16px;
    }
}
</style>
</head>
<body>

<?php include("../navbar.php"); ?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h2><i class="bi bi-gear-fill me-2"></i>System Configuration</h2>
        <p>Manage your association details and system settings</p>
    </div>

    <!-- Configuration Card -->
    <div class="config-card">
        <div class="card-header-custom">
            <i class="bi bi-building"></i> Association Information
        </div>
        <div class="card-body-custom">
            <div class="info-box">
                <i class="bi bi-info-circle-fill"></i>
                <strong>Note:</strong> These details will be displayed throughout the system and on public-facing pages.
            </div>

            <form method="POST" enctype="multipart/form-data" id="configForm" action="">
                <!-- Logo Preview -->
                <div class="text-center mb-4">
                    <div class="logo-preview-container" onclick="document.getElementById('logoInput').click()">
                        <img src="<?= !empty($config['assoc_logo']) ? BASE_URL.'uploads/config/'.htmlspecialchars($config['assoc_logo']) : BASE_URL.'images/logo1.png' ?>" 
                             alt="Association Logo" 
                             class="hero-logo"
                             id="logoPreview">
                        <div class="upload-overlay">
                            <i class="bi bi-cloud-upload"></i>
                            <span>Click to Upload</span>
                        </div>
                    </div>
                    <small class="text-muted d-block">Click on the logo to upload a new one</small>
                </div>

                <!-- Logo Upload -->
                <div class="mb-4">
                    <label class="form-label">
                        <i class="bi bi-image"></i> Association Logo
                    </label>
                    <div class="file-upload-wrapper">
                        <input type="file" name="assoc_logo" id="logoInput" accept="image/jpeg,image/png,image/gif,image/jpg" onchange="previewLogo(this)">
                        <label for="logoInput" class="file-upload-label">
                            <i class="bi bi-upload"></i> Choose Logo File
                        </label>
                        <div class="selected-file" id="selectedFile"></div>
                    </div>
                    <small class="text-muted">Allowed formats: JPG, JPEG, PNG, GIF. Maximum size: 5MB</small>
                </div>

                <hr class="my-4">

                <!-- Association Name -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-building"></i> Association Name
                    </label>
                    <input type="text" 
                           name="assoc_name" 
                           class="form-control" 
                           required 
                           value="<?= htmlspecialchars($assocName) ?>"
                           placeholder="Enter association name">
                </div>

                <!-- Email & Phone -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-envelope"></i> Email Address
                        </label>
                        <input type="email" 
                               name="assoc_email" 
                               class="form-control" 
                               required 
                               value="<?= htmlspecialchars($assocEmail) ?>"
                               placeholder="association@email.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-telephone"></i> Phone Number
                        </label>
                        <input type="text" 
                               name="assoc_phone" 
                               class="form-control" 
                               required 
                               value="<?= htmlspecialchars($assocPhone) ?>"
                               placeholder="+63 XXX XXX XXXX">
                    </div>
                </div>

                <!-- Address -->
                <div class="mb-4">
                    <label class="form-label">
                        <i class="bi bi-geo-alt"></i> Complete Address
                    </label>
                    <textarea name="assoc_address" 
                              class="form-control" 
                              rows="4" 
                              required
                              placeholder="Enter complete address including barangay, city, province"><?= htmlspecialchars($assocAddress) ?></textarea>
                </div>

                <hr class="my-4">

                <!-- Auto Backup Settings -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-cloud-check"></i> Auto Backup Settings
                    </label>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="autoBackupStatus" name="auto_backup_status" value="1" <?= $autoBackupStatus ? 'checked' : '' ?>>
                        <label class="form-check-label" for="autoBackupStatus">
                            Enable automatic daily database backup (requires Task Scheduler)
                        </label>
                    </div>
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <label class="col-form-label" style="font-size: 0.9rem;">Storage limit for backups</label>
                        </div>
                        <div class="col-auto" style="max-width: 120px;">
                            <input type="number" min="10" step="10" class="form-control" name="backup_storage_limit_mb" value="<?= (int)$backupStorageLimitMb ?>">
                        </div>
                        <div class="col-auto">
                            <span class="form-text">MB (used by Backup & Restore page)</span>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-1">You still control the actual schedule via Windows Task Scheduler. This switch only allows or blocks auto backups.</small>
                </div>

                <!-- Submit Button -->
                <div class="text-end">
                    <button type="submit" name="save_config" class="btn save-btn">
                        <i class="bi bi-check-circle me-2"></i>Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
// Preview logo before upload
function previewLogo(input) {
    const file = input.files[0];
    const selectedFileDiv = document.getElementById('selectedFile');
    
    if (file) {
        // Show selected filename
        selectedFileDiv.textContent = `Selected: ${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
        selectedFileDiv.style.display = 'block';
        
        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    } else {
        selectedFileDiv.style.display = 'none';
    }
}
</script>
</script>

<?php if(isset($success)): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Configuration Saved!',
    text: 'Association details have been updated successfully.',
    timer: 2500,
    showConfirmButton: false,
    toast: true,
    position: 'top-end'
});
</script>
<?php elseif(isset($error)): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Error!',
    text: '<?= htmlspecialchars($error) ?>',
    confirmButtonColor: '#667eea'
});
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
