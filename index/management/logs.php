<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}
require_once('../../config/db_connect.php');

$memberName = $_SESSION['member_name'] ?? 'Admin';

// Optional: Search/filter
$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? '';

$sql = "SELECT a.*, m.username FROM member_archives a
        LEFT JOIN members m ON a.member_id = m.id
        WHERE 1";
$params = [];
$types = '';

if ($search) {
    $sql .= " AND m.username LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}
if ($type) {
    $sql .= " AND a.type = ?";
    $params[] = $type;
    $types .= 's';
}
$sql .= " ORDER BY a.archived_at DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error . "<br>SQL: " . htmlspecialchars($sql));
}
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Logs | Bangkero & Fishermen Association</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #fff;
        }

       
  

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 32px 32px 16px 32px;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            margin-left: 250px;
            background: #fff !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }

        /* Table Styling */
        .table thead th {
            background: #00897b;
            color: #fff;
        }
        .table-striped>tbody>tr:nth-of-type(odd) {
            background-color: #f1fdfb;
        }
        .table-hover tbody tr:hover {
            background-color: #e0f7fa;
        }

        /* Buttons */
        .btn-primary {
            background-color: #ff7043;
            border-color: #ff7043;
        }
        .btn-primary:hover {
            background-color: #00897b;
            border-color: #00897b;
        }
        .hero-logo {
            height: 70px;
            width: auto;
            display: block;
        }

        .logo-wrapper {
            width: 70px;
            height: 70px;
        }
    </style>
</head>
<body>
<?php include('../navbar.php'); ?>

<!-- Main Content -->
<div class="main-content">
    <h2 class="fw-bold mb-4 text-center"><i class="bi bi-clock-history me-2"></i> Activity Logs</h2>

    <form class="row g-2 mb-3" method="get">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by username..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="type" class="form-select">
                <option value="">All Types</option>
                <option value="Profile Update" <?= $type=='Profile Update'?'selected':''; ?>>Profile Update</option>
                <option value="Changed Password" <?= $type=='Changed Password'?'selected':''; ?>>Changed Password</option>
                <option value="Preferences" <?= $type=='Preferences'?'selected':''; ?>>Preferences</option>
                <option value="Login" <?= $type=='Login'?'selected':''; ?>>Login</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['archived_at'] ? date('M d, Y h:i A', strtotime($row['archived_at'])) : '' ?></td>
                        <td><?= htmlspecialchars($row['username'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= htmlspecialchars($row['type']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted">No logs found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
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
        window.location.href = '../logout.php';
    }, 1000);
}
</script>
</body>
</html>
