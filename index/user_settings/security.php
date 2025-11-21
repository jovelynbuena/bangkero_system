<?php
session_start();
if (empty($_SESSION['username'])) { header('location: ../login.php'); exit; }
include('../../config/db_connect.php');

// Fetch member info (needed for member_id)
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT * FROM members WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

$successMsg = $errorMsg = "";

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $username = $_SESSION['username'];
    // Use the correct table and column
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE username = ?");
    if (!$stmt) {
        $errorMsg = "Prepare failed: " . $conn->error;
    } else {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($db_hash);
        if ($stmt->fetch()) {
            if (!password_verify($current, $db_hash)) {
                $errorMsg = "Current password is incorrect.";
            } elseif ($new !== $confirm) {
                $errorMsg = "New passwords do not match.";
            } elseif (strlen($new) < 6) {
                $errorMsg = "New password must be at least 6 characters.";
            } else {
                $stmt->close(); // Only close here after fetch and before update
                $new_hash = password_hash($new, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password_hash=? WHERE username=?");
                $update_stmt->bind_param("ss", $new_hash, $username);
                if ($update_stmt->execute()) {
                    // After successful password change
                    $archive_stmt = $conn->prepare("INSERT INTO member_archives (member_id, title, description, type) VALUES (?, ?, ?, ?)");
                    $archive_title = "Changed Password";
                    $archive_desc = "Password changed on " . date('M d, Y h:i A');
                    $archive_type = "Changed Password";
                    $archive_stmt->bind_param("isss", $member['id'], $archive_title, $archive_desc, $archive_type);
                    $archive_stmt->execute();

                    $successMsg = "Password changed successfully!";
                } else {
                    $errorMsg = "Failed to update password.";
                }
                $update_stmt->close();
                // DO NOT close $stmt again here!
            }
        } else {
            $errorMsg = "User not found.";
            $stmt->close(); // Only close here if not found
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Security Settings</title>
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
            <h3 class="mb-4 text-center"><i class="bi bi-shield-lock me-2"></i> Security</h3>
            <?php if ($successMsg): ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?= $successMsg ?>
                </div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-x-circle-fill me-2"></i><?= $errorMsg ?>
                </div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <div class="mb-3 position-relative">
                    <label>Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="form-control" required>
                    <span class="position-absolute top-50 end-0 translate-middle-y me-3" style="cursor:pointer;" onclick="togglePassword('current_password', this)">
                        <i class="bi bi-eye-slash" id="icon_current_password"></i>
                    </span>
                </div>
                <div class="mb-3 position-relative">
                    <label>New Password</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" required>
                    <span class="position-absolute top-50 end-0 translate-middle-y me-3" style="cursor:pointer;" onclick="togglePassword('new_password', this)">
                        <i class="bi bi-eye-slash" id="icon_new_password"></i>
                    </span>
                </div>
                <div class="mb-3 position-relative">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    <span class="position-absolute top-50 end-0 translate-middle-y me-3" style="cursor:pointer;" onclick="togglePassword('confirm_password', this)">
                        <i class="bi bi-eye-slash" id="icon_confirm_password"></i>
                    </span>
                </div>
                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
            <script>
            function togglePassword(fieldId, el) {
                const input = document.getElementById(fieldId);
                const icon = el.querySelector('i');
                if (input.type === "password") {
                    input.type = "text";
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                } else {
                    input.type = "password";
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
            }
            </script>
        </main>
    </div>
</body>
</html>