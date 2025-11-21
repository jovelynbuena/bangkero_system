<?php
session_start();
require_once('../../config/db_connect.php');

// Only allow admin
$role = strtolower($_SESSION['role'] ?? 'guest');
if ($role !== 'admin') {
    header("Location: ../login.php");
    exit;
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

// Handle form submission
if (isset($_POST['save_config'])) {
    $assocName = $conn->real_escape_string($_POST['assoc_name']);
    $assocEmail = $conn->real_escape_string($_POST['assoc_email']);
    $assocPhone = $conn->real_escape_string($_POST['assoc_phone']);
    $assocAddress = $conn->real_escape_string($_POST['assoc_address']);

    // Handle logo upload
    if (isset($_FILES['assoc_logo']) && $_FILES['assoc_logo']['error'] === 0) {
        $uploadDir = __DIR__ . '/../uploads/config/'; // corrected path
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileTmp = $_FILES['assoc_logo']['tmp_name'];
        $fileName = basename($_FILES['assoc_logo']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];

        if (in_array($fileExt, $allowed)) {
            $newFileName = 'assoc_logo.' . $fileExt; // overwrite existing
            $destination = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmp, $destination)) {
                $assocLogo = $newFileName;
            }
        }
    }

    // Update database
    $sql = "UPDATE system_config SET
            assoc_name='$assocName',
            assoc_email='$assocEmail',
            assoc_phone='$assocPhone',
            assoc_address='$assocAddress',
            assoc_logo='$assocLogo'
            WHERE id=1";

    if ($conn->query($sql)) {
        $success = true;
        $config['assoc_logo'] = $assocLogo; // refresh config for preview
    } else {
        $error = $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>System Configuration | Admin Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.main-content { margin-left: 260px; padding: 30px; min-height: 100vh; }
.card { border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 20px; }
.card-header { background-color: #2c3e50; color: white; font-weight: 500; border-radius: 12px 12px 0 0; }
form .form-control { border-radius: 8px; }
form .btn { border-radius: 8px; background-color: #ff7043; border: none; font-weight: 600; }
form .btn:hover { background-color: #e65c2f; }
.hero-logo { height: 80px; width: auto; display: block; margin-bottom: 10px; }
</style>
</head>
<body>

<?php include("../navbar.php"); ?>

<div class="main-content">
    <h2>System Configuration</h2>
    <div class="card">
        <div class="card-header">Association Details</div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3 text-center">
                    <img src="<?= !empty($config['assoc_logo']) ? BASE_URL.'uploads/config/'.htmlspecialchars($config['assoc_logo']) : BASE_URL.'images/logo1.png' ?>" alt="Logo" class="hero-logo">
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload Logo</label>
                    <input type="file" name="assoc_logo" class="form-control">
                    <small class="text-muted">Allowed: jpg, jpeg, png, gif. Logo will overwrite previous.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Association Name</label>
                    <input type="text" name="assoc_name" class="form-control" required value="<?= htmlspecialchars($assocName) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="assoc_email" class="form-control" required value="<?= htmlspecialchars($assocEmail) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="assoc_phone" class="form-control" required value="<?= htmlspecialchars($assocPhone) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="assoc_address" class="form-control" rows="3" required><?= htmlspecialchars($assocAddress) ?></textarea>
                </div>
                <button type="submit" name="save_config" class="btn btn-primary w-100">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<?php if(isset($success)): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Saved!',
    text: 'Association details updated successfully.',
    timer: 2000,
    showConfirmButton: false
});
</script>
<?php elseif(isset($error)): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Error!',
    text: '<?= htmlspecialchars($error) ?>',
});
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
