<?php
session_start();
if (empty($_SESSION['username'])) { header('location: ../login.php'); exit; }
include('../../config/db_connect.php');

// Fetch member info
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT * FROM members WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

// Handle profile update
$successMsg = $errorMsg = "";
$image = $member['image'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = $_POST['address'] ?? '';

    // Handle profile image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['image']['type'], $allowed_types)) {
            $imgTmp = $_FILES['image']['tmp_name'];
            $imgName = uniqid('member_', true) . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '', basename($_FILES['image']['name']));
            $imgDir = '../../uploads/members/';
            $imgPath = $imgDir . $imgName;
            if (!is_dir($imgDir)) { mkdir($imgDir, 0777, true); }
            if (move_uploaded_file($imgTmp, $imgPath)) {
                // Optionally delete old image
                if (!empty($member['image']) && file_exists($imgDir . $member['image']) && $member['image'] !== "default_member.png") {
                    @unlink($imgDir . $member['image']);
                }
                $image = $imgName;
            }
        }
    }

    // Update the database (use the correct column names)
    $update = $conn->prepare("UPDATE members SET name=?, phone=?, dob=?, gender=?, address=?, image=? WHERE username=?");
    $update->bind_param("sssssss", $name, $phone, $dob, $gender, $address, $image, $username);
    if ($update->execute()) {
        $successMsg = "Profile updated successfully!";

        // After successful profile update
        $archive_stmt = $conn->prepare("INSERT INTO member_archives (member_id, title, description, type) VALUES (?, ?, ?, ?)");
        $archive_title = "Profile Updated";
        $archive_desc = "You updated your profile information.";
        $archive_type = "Profile Update";
        $archive_stmt->bind_param("isss", $member['id'], $archive_title, $archive_desc, $archive_type);
        $archive_stmt->execute();

        // Refresh member info
        $stmt = $conn->prepare("SELECT * FROM members WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $member = $result->fetch_assoc();
    } else {
        $errorMsg = "Failed to update profile.";
    }
}

// Image path for display
$imageSrc = (!empty($member['image']) && file_exists('../../uploads/members/' . $member['image']))
    ? '../../uploads/members/' . htmlspecialchars($member['image'])
    : '../../uploads/members/default_member.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Profile Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f7f7f7; }
        .settings-wrapper { max-width: 1100px; margin: 48px auto 0 auto; display: flex; gap: 32px; }
        .settings-sidebar { min-width: 240px; background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); padding: 32px 0 0 0; height: fit-content; position: sticky; top: 32px; z-index: 10; }
        .settings-sidebar ul { list-style: none; padding: 0; margin: 0; }
        .settings-sidebar li { padding: 16px 32px; font-size: 1.08rem; color: #333; border-left: 4px solid transparent; cursor: pointer; transition: background 0.2s, border-color 0.2s; display: flex; align-items: center; gap: 12px; }
        .settings-sidebar li.active, .settings-sidebar li:hover { background: #f0f6ff; border-left: 4px solid #0d6efd; color: #0d6efd; }
        .settings-main { flex: 1; background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); padding: 36px 40px 32px 40px; }
        .profile-img { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 3px solid #0d6efd; margin-bottom: 16px; }
        @media (max-width: 900px) { .settings-wrapper { flex-direction: column; gap: 0; } .settings-sidebar { min-width: 0; margin-bottom: 24px; } .settings-main { padding: 24px 10px; } }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">Bangkero & Fishermen Association</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="../member.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="profile.php">Settings</a></li>
                    <li class="nav-item"><a href="../logout.php" class="nav-link text-danger">Log Out</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="settings-wrapper">
        <aside>
            <?php include 'sidebar.php'; ?>
        </aside>
        <main class="settings-main">
            <h3 class="mb-4 text-center"><i class="bi bi-person-gear me-2"></i> Member Settings</h3>
            <?php if ($successMsg): ?>
                <div class="alert alert-success"><?= $successMsg ?></div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                <div class="alert alert-danger"><?= $errorMsg ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <div class="text-center mb-4">
                    <img src="<?= $imageSrc ?>" class="profile-img" alt="Profile Image">
                    <div>
                        <input type="file" name="image" accept="image/*" class="form-control mt-2" style="max-width:300px;display:inline-block;">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($member['name'] ?? '') ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($member['phone'] ?? '') ?>" class="form-control">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="dob" value="<?= htmlspecialchars($member['dob'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="Male" <?= (isset($member['gender']) && $member['gender']=='Male')?'selected':'' ?>>Male</option>
                            <option value="Female" <?= (isset($member['gender']) && $member['gender']=='Female')?'selected':'' ?>>Female</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($member['address'] ?? '') ?>" class="form-control">
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>