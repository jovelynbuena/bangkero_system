<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: login.php');
    exit;
}

require_once('../../config/db_connect.php');

// Fetch members and roles
$membersResult = $conn->query("SELECT id, name FROM members ORDER BY name ASC");
$rolesResult   = $conn->query("SELECT id, role_name FROM officer_roles ORDER BY role_name ASC");

$alertType = $alertMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id   = $_POST['member_id'] ?? '';
    $role_id     = $_POST['role_id'] ?? '';
    $term_start  = $_POST['term_start'] ?? '';
    $term_end    = $_POST['term_end'] ?? '';
    $description = $_POST['description'] ?? '';

    if (!$member_id || !$role_id || !$term_start || !$term_end) {
        $alertType = "error";
        $alertMsg = "All required fields must be filled!";
    } else {
        $imageName = "";
        if (!empty($_FILES['image']['name'])) {
            $targetDir = "../../uploads/";
            $imageName = time() . '_' . basename($_FILES["image"]["name"]);
            $targetFile = $targetDir . $imageName;
            $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            if (getimagesize($_FILES["image"]["tmp_name"]) === false) {
                $alertType = "error";
                $alertMsg = "Invalid image file.";
            } elseif ($_FILES["image"]["size"] > 2 * 1024 * 1024) {
                $alertType = "error";
                $alertMsg = "Image must be less than 2MB.";
            } elseif (!in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                $alertType = "error";
                $alertMsg = "Allowed file types: JPG, JPEG, PNG, GIF.";
            } elseif (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $alertType = "error";
                $alertMsg = "Error uploading image.";
            }
        }

        if (!$alertMsg) {
            $stmt = $conn->prepare("
                INSERT INTO officers (member_id, role_id, term_start, term_end, image, description) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iissss", $member_id, $role_id, $term_start, $term_end, $imageName, $description);

            if ($stmt->execute()) {
                $alertType = "success";
                $alertMsg = "Officer assigned successfully!";
            } else {
                $alertType = "error";
                $alertMsg = "Database error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Officer | Bangkero & Fishermen Association</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #fff; }
        .main-content { margin-left: 250px; padding: 32px; min-height: 100vh; }
        .btn-primary { background-color: #ff7043; border-color: #ff7043; }
        .btn-primary:hover { background-color: #00897b; border-color: #00897b; }
        @media (max-width: 991.98px) { .main-content { margin-left: 0; padding: 16px; } }
    </style>
</head>
<body>
<?php include('../navbar.php'); ?>
<div class="main-content">
    <h2 class="fw-bold text-center mb-4">Assign New Officer</h2>

    <div class="form-section mx-auto" style="max-width: 600px;">
        <form method="post" enctype="multipart/form-data" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">Select Member</label>
                <select name="member_id" class="form-select" required>
                    <option value="">-- Select Member --</option>
                    <?php while ($row = $membersResult->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Position</label>
                <select name="role_id" class="form-select" required>
                    <option value="">-- Select Position --</option>
                    <?php while ($role = $rolesResult->fetch_assoc()): ?>
                        <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Term Start</label>
                <input type="date" name="term_start" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Term End</label>
                <input type="date" name="term_end" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Photo (optional)</label>
                <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.gif">
            </div>

            <div class="mb-3">
                <label class="form-label">Description (optional)</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Assign Officer</button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($alertMsg)): ?>
<script>
Swal.fire({
    icon: '<?= $alertType ?>',
    title: '<?= ucfirst($alertType) ?>',
    text: '<?= addslashes($alertMsg) ?>',
    confirmButtonColor: '<?= $alertType === "success" ? "#43a047" : "#e53935" ?>'
}).then(() => {
    <?php if ($alertType === "success"): ?>
        window.location.href = "officers_list.php"; // redirect after success
    <?php endif; ?>
});
</script>
<?php endif; ?>
</body>
</html>
