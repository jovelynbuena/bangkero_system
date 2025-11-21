<?php
session_start();

if ($_SESSION['username'] == "") {
    header('location: login.php');
}
require_once('../config/db_connect.php');

// Fetch members for dropdown
$membersResult = $conn->query("SELECT id, name FROM members ORDER BY name ASC");

$positions = [
    'President', 'Vice President', 'Secretary', 'Treasurer',
    'Auditor', 'PRO', 'Sgt at Arms', 'Member'
];

$successMsg = $errorMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate
    $member_id = $_POST['member_id'] ?? '';
    $position = $_POST['position'] ?? '';
    $term_start = $_POST['term_start'] ?? '';
    $term_end = $_POST['term_end'] ?? '';

    if (!$member_id || !$position || !$term_start || !$term_end) {
        $errorMsg = "All fields are required!";
    } else {
        // Handle image upload
        $imageName = "";
        if (!empty($_FILES['image']['name'])) {
            $targetDir = "../uploads/";
            $imageName = time() . '_' . basename($_FILES["image"]["name"]);
            $targetFile = $targetDir . $imageName;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check === false) {
                $errorMsg = "File is not an image.";
            } elseif ($_FILES["image"]["size"] > 2 * 1024 * 1024) {
                $errorMsg = "Image must be less than 2MB.";
            } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                $errorMsg = "Only JPG, JPEG, PNG & GIF files are allowed.";
            } else {
                move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
            }
        }

        if (!$errorMsg) {
            // Insert
            $stmt = $conn->prepare("INSERT INTO officers (member_id, position, term_start, term_end, image, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $member_id, $position, $term_start, $term_end, $imageName, $description);
            if ($stmt->execute()) {
                $successMsg = "Officer assigned successfully!";
                // Optionally redirect:
                // header("Location: officerslist.php?added=1");
                // exit;
            } else {
                $errorMsg = "Error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

$memberName = isset($_SESSION['member_name']) ? $_SESSION['member_name'] : 'Admin';
$description = $_POST['description'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Officer | Bangkero & Fishermen Association</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #f4f6f9;
        margin: 0;
    }

    /* Sidebar */
    .sidebar {
        width: 250px;
        height: 100vh;
        position: fixed;
        background-color: #343a40;
        color: white;
        padding-top: 20px;
        overflow-y: auto;
    }

    .sidebar a {
        color: white;
        display: block;
        padding: 10px 20px;
        text-decoration: none;
        transition: background-color 0.2s;
    }

    .sidebar a:hover,
    .sidebar .active {
        background-color: #495057;
    }

    .sidebar a.sidebar-dropdown-toggle {
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 20px;
        color: white;
        text-decoration: none;
    }

    .sidebar a.sidebar-dropdown-toggle:hover {
        background-color: #495057;
    }

    .sidebar .collapse a {
        color: white;
        padding-left: 35px;
        text-decoration: none;
    }

    .sidebar .collapse a:hover {
        background-color: #495057;
    }

    /* Main content */
    .main-content {
        margin-left: 250px;
        padding: 20px;
    }

    /* Navbar */
    .navbar {
        margin-left: 250px;
    }

    /* Form styling */
    .form-label {
        font-weight: 500;
    }

    .form-section {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        padding: 32px;
    }
</style>

</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4">Admin Panel</h4>
    <a href="admin.php"><i class="bi bi-house-door text-primary"></i> Dashboard</a>
    <a href="announcement/admin_announcement.php"><i class="bi bi-megaphone text-danger"></i> Announcements</a>
    <a href="officers.php"><i class="bi bi-people text-success"></i> Officers</a>
    <a href="upload_event.php"><i class="bi bi-calendar-event text-info"></i> Schedule Events</a>
    <a class="sidebar-dropdown-toggle" data-bs-toggle="collapse" href="#managementMenu" role="button" aria-expanded="true" aria-controls="managementMenu">
       <i class="bi bi-tools text-secondary"></i> Management <i class="bi bi-caret-down-fill float-end"></i>
    </a>
    <div class="collapse show ps-3" id="managementMenu">
        <a href="officerslist.php" class="d-block py-1"><i class="bi bi-person-badge text-primary"></i> Officers List</a>
        <a href="officer_roles.php" class="d-block py-1 active"><i class="bi bi-person-plus text-success"></i>Officer Roles</a>
        <a href="upload_event.php" class="d-block py-1"><i class="bi bi-calendar2-plus text-info"></i> Event Scheduling</a>
        <a href="memberlist.php" class="d-block py-1"><i class="bi bi-people-fill text-warning"></i> Member List</a>
        <a href="#" class="d-block py-1"><i class="bi bi-tools text-secondary"></i> Utilities</a>
    </div>
    <!-- Settings Dropdown Start -->
    <a class="sidebar-dropdown-toggle" data-bs-toggle="collapse" href="#settingsMenu" role="button" aria-expanded="false" aria-controls="settingsMenu">
        <i class="bi bi-gear text-dark"></i> Settings <i class="bi bi-caret-down-fill float-end"></i>
    </a>
    <div class="collapse ps-3" id="settingsMenu">
        <a href="admin_settings.php" class="d-block py-1"><i class="bi bi-sliders text-info"></i> Site Settings</a>
        <a href="admin_preferences.php" class="d-block py-1"><i class="bi bi-person-gear text-success"></i> Admin Preferences</a>
        <a href="admin_logs.php" class="d-block py-1"><i class="bi bi-clipboard-data text-warning"></i> System Logs</a>
    </div>
    <!-- Settings Dropdown End -->
    <a href="#" onclick="delayedLogout(event)" class="text-danger"><i class="bi bi-box-arrow-right text-danger"></i> Logout</a>
</div>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid">
        <span class="navbar-text ms-auto me-3">
            Logged in as <strong><?php echo htmlspecialchars($memberName); ?></strong>
        </span>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <a href="officerslist.php" class="btn btn-secondary">&larr; Back to Officers List</a>
        <h2 class="fw-bold mb-0 flex-grow-1 text-center">Assign New Officer</h2>
        <div></div>
    </div>
    <div class="form-section mx-auto" style="max-width: 600px;">
        <?php if ($successMsg): ?>
            <div class="alert alert-success"><?= $successMsg ?></div>
        <?php endif; ?>
        <?php if ($errorMsg): ?>
            <div class="alert alert-danger"><?= $errorMsg ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">Select Member</label>
                <select name="member_id" class="form-select" required>
                    <option value="">-- Select Member --</option>
                    <?php while ($row = $membersResult->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>" 
                            <?= (isset($_POST['member_id']) && $_POST['member_id'] == $row['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Position</label>
                <select name="position" class="form-select" required>
                    <option value="">-- Select Position --</option>
                    <?php foreach ($positions as $pos): ?>
                        <option value="<?= $pos ?>" 
                            <?= (isset($_POST['position']) && $_POST['position'] == $pos) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pos) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Term Start</label>
                <input type="date" name="term_start" class="form-control" required value="<?= $_POST['term_start'] ?? '' ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Term End</label>
                <input type="date" name="term_end" class="form-control" required value="<?= $_POST['term_end'] ?? '' ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Photo (optional)</label>
                <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.gif">
            </div>
            <div class="mb-3">
                <label class="form-label">Description (optional)</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Assign Officer</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function delayedLogout(event) {
        event.preventDefault();
        const overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = 0;
        overlay.style.left = 0;
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.zIndex = 9999;
        overlay.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="spinner-border text-primary me-2" role="status"></div>
                <strong class="fs-5">Logging out...</strong>
            </div>`;
        document.body.appendChild(overlay);
        setTimeout(() => {
            window.location.href = 'logout.php';
        }, 1000);
    }
</script>
</body>
</html>