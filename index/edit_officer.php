<?php
session_start();
if(empty($_SESSION['username'])){
    header('location: login.php');
    exit;
}
require_once('../config/db_connect.php');

$id = $_GET['id'] ?? null;
if(!$id) die("Invalid officer ID.");

// Fetch officer details
$query = "SELECT officers.*, members.name FROM officers JOIN members ON officers.member_id = members.id WHERE officers.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$officer = $result->fetch_assoc();
if(!$officer) die("Officer not found.");

$memberName = $_SESSION['member_name'] ?? 'Admin';
$errorMsg = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $position = htmlspecialchars($_POST['position'], ENT_QUOTES, 'UTF-8');
    $term_start = $_POST['term_start'];
    $term_end = $_POST['term_end'];

    $image = $officer['image'];

    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK){
        $allowed_types = ['jpg','jpeg','png','gif'];
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if(!in_array($file_ext, $allowed_types)){
            $errorMsg = "Only JPG, JPEG, PNG, GIF files are allowed.";
        } elseif($_FILES['image']['size'] > 2*1024*1024){
            $errorMsg = "File too large. Max 2MB.";
        } else {
            $imageName = time().'_'.preg_replace("/[^a-zA-Z0-9\-_\.]/", "_", basename($_FILES['image']['name']));
            $targetPath = "../uploads/".$imageName;
            if(move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)){
                $image = $imageName;
            } else {
                $errorMsg = "Failed to upload image.";
            }
        }
    }

    if(!$errorMsg){
        $updateQuery = "UPDATE officers SET position=?, term_start=?, term_end=?, image=? WHERE id=?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssssi", $position, $term_start, $term_end, $image, $id);
        $stmt->execute();
        header("Location: management/officerslist.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Officer | Admin Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family:'Segoe UI', sans-serif; background:#f5f7fa; }
.main-content { margin-left:250px; padding:32px; min-height:100vh; }
.card { border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.1); }
.btn-primary { background:#ff7043; border:none; }
.btn-success { background:#4caf50; border:none; }
.alert-danger { border-radius:12px; }
@media(max-width:991.98px){ .main-content { margin-left:0; padding:16px; } }
</style>
</head>
<body>
<?php include('navbar.php'); ?>

<!-- Main Content -->
<div class="main-content">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card shadow p-4 rounded-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="management/officerslist.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
                    <h4 class="text-center w-100 m-0">Edit Officer</h4>
                </div>
                <?php if($errorMsg): ?>
                    <div class="alert alert-danger"><?= $errorMsg ?></div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Member Name</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($officer['name']) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <input type="text" name="position" class="form-control" value="<?= htmlspecialchars($officer['position']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Term Start</label>
                        <input type="date" name="term_start" class="form-control" value="<?= htmlspecialchars($officer['term_start']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Term End</label>
                        <input type="date" name="term_end" class="form-control" value="<?= htmlspecialchars($officer['term_end']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image</label><br>
                        <?php if($officer['image']): ?>
                            <img src="../uploads/<?= htmlspecialchars($officer['image']) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:10px;">
                        <?php else: ?>
                            <p class="text-muted">No image uploaded</p>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control mt-2">
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="management/officerslist.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Activity Logs Link -->
    <div class="mt-4 text-center">
        <a href="management/logs.php" class="btn btn-outline-info"><i class="bi bi-clock-history"></i> View Activity Logs</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
