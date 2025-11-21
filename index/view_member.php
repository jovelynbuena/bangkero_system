<?php
include('../config/db_connect.php'); // Ensure correct path

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid request.");
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM members WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Member not found.");
}

$member = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Member Details | Bangkero & Fishermen Association</title>
    <!-- Bootstrap & Icons for consistency -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 10px 20px;
            text-decoration: none;
        }
        .sidebar a:hover, .sidebar .active {
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
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .navbar {
            margin-left: 250px;
        }
    </style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4">Admin Panel</h4>
    <a href="admin.php"><i class="bi bi-house-door"></i> Dashboard</a>
    <a href="announcement/admin_announcement.php"><i class="bi bi-megaphone"></i> Announcements</a>
    <a href="officers.php"><i class="bi bi-people"></i> Officers</a>
    <a href="upload_event.php"><i class="bi bi-calendar-event"></i> Schedule Events</a>
    <a class="sidebar-dropdown-toggle" data-bs-toggle="collapse" href="#managementMenu" role="button" aria-expanded="true" aria-controls="managementMenu">
       <i class="bi bi-tools"></i> Management<i class="bi bi-caret-down-fill float-end"></i>
    </a>
    <div class="collapse show ps-3" id="managementMenu">
        <a href="officerslist.php" class="d-block py-1">Officers List</a>
        <a href="assign_officers_form.php" class="d-block py-1">Assign Officer</a>
        <a href="#" class="d-block py-1">Event Scheduling</a>
        <a href="memberlist.php" class="d-block py-1">Member List</a>
        <a href="utilities.php" class="d-block py-1">Utilities</a>
    </div>
    <a href="#"><i class="bi bi-gear"></i> Settings</a>
    <a href="#" onclick="delayedLogout(event)" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid">
        <span class="navbar-text ms-auto me-3">
            Admin Panel
        </span>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="container mt-5">
        <h2 class="text-center">Member Details</h2>
        <div class="card p-4 mx-auto" style="max-width:450px;">
            <p><strong>ID:</strong> <?php echo htmlspecialchars($member['id']); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($member['name']); ?></p>
            <p><strong>Contact:</strong> <?php echo htmlspecialchars($member['phone']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($member['address']); ?></p>
            <a href="memberlist.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Members</a>
        </div>
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