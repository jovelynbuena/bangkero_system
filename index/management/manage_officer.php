<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('location: ../login.php');
    exit;
}

include('../../config/db_connect.php');

// Handle Approve/Reject/Delete/Demote actions for officers (server-side now redirects with notice)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    $statusMessage = "";

    if ($action === 'approve') {
        $sql = "UPDATE users SET status='approved' WHERE id=$id";
        $statusMessage = "Officer approved successfully!";
    } elseif ($action === 'reject') {
        $sql = "UPDATE users SET status='rejected' WHERE id=$id";
        $statusMessage = "Officer rejected successfully!";
    } elseif ($action === 'delete') {
        $sql = "DELETE FROM users WHERE id=$id";
        $statusMessage = "Officer deleted successfully!";
    } elseif ($action === 'demote') {
        $sql = "UPDATE users SET is_admin=0, role='officer' WHERE id=$id";
        $statusMessage = "Admin privileges removed successfully!";
    } else {
        header("Location: manage_officer.php?notice=" . urlencode("Invalid action") . "&type=error");
        exit;
    }

    if ($conn->query($sql)) {
        header("Location: manage_officer.php?notice=" . urlencode($statusMessage) . "&type=success");
    } else {
        header("Location: manage_officer.php?notice=" . urlencode("Error: " . $conn->error) . "&type=error");
    }
    exit;
}

// Handle Promote to Admin (POST) — redirect with notice
if (isset($_POST['promote_admin'])) {
    $user_id = intval($_POST['user_id']);
    $sql = "UPDATE users SET is_admin=1, role='admin' WHERE id=$user_id AND status='approved'";

    if ($conn->query($sql)) {
        header("Location: manage_officer.php?notice=" . urlencode("Officer promoted to admin successfully!") . "&type=success");
    } else {
        header("Location: manage_officer.php?notice=" . urlencode("Error: " . $conn->error) . "&type=error");
    }
    exit;
}

// Fetch all officers
$result = $conn->query("SELECT * FROM users WHERE role='officer' ORDER BY created_at DESC");

// Fetch approved officers for Add Admin dropdown
$approved_officers = $conn->query("SELECT id, username FROM users WHERE role='officer' AND status='approved' ORDER BY username ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Officers | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f5f6fa; }
        .main-content { margin-left: 260px; padding: 30px; min-height: 100vh; }

        h2 { font-weight: 600; color: #2c3e50; margin-bottom: 1.5rem; }

        .card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .card-header { background-color: #2c3e50; color: white; font-weight: 500; border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .card-body { padding: 20px; }

        .form-select, .btn { font-size: 0.95rem; }
        .btn-primary { background-color: #ff7043; border: none; }
        .btn-primary:hover { background-color: #e65c2f; }
        .btn-secondary { background-color: #6c757d; border: none; }
        .btn-secondary:hover { background-color: #5a6268; }

        table { font-size: 0.95rem; }
        .table thead th { background-color: #2c3e50; color: white; border: none; }
        .table-hover tbody tr:hover { background-color: #f0f0f0; }
        .badge { font-size: 0.85rem; padding: 0.4em 0.65em; }

        .admin-row { background-color: #d1ecf1 !important; } /* Highlight promoted admins */
        .search-box { max-width: 300px; }

        @media (max-width: 991.98px) { .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>

<?php include('../navbar.php'); ?>

<div class="main-content">
    <h2 class="text-center">Manage Officers</h2>

    <!-- Promote Officer to Admin -->
    <div class="card">
        <div class="card-header"><i class="bi bi-shield-lock"></i> Promote Officer to Admin</div>
        <div class="card-body">
            <form method="POST" class="row g-3 align-items-center">
                <div class="col-md-6">
                    <select name="user_id" class="form-select" required>
                        <option value="">Select Officer</option>
                        <?php while($row = $approved_officers->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['username']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" name="promote_admin" class="btn btn-primary w-100">Promote to Admin</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Search -->
    <div class="d-flex justify-content-between mb-2 align-items-center">
        <h5>Officers List</h5>
        <input type="text" id="searchInput" class="form-control search-box" placeholder="Search officers...">
    </div>

    <!-- Officers Table -->
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle text-center" id="officersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Admin</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="<?= ($row['is_admin']==1) ? 'admin-row' : '' ?>">
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td>
                                    <?php
                                        switch($row['status']){
                                            case 'pending': echo "<span class='badge bg-warning text-dark'>Pending</span>"; break;
                                            case 'approved': echo "<span class='badge bg-success'>Approved</span>"; break;
                                            case 'rejected': echo "<span class='badge bg-danger'>Rejected</span>"; break;
                                            default: echo "<span class='badge bg-secondary'>Unknown</span>";
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?= ($row['is_admin']==1) ? "<span class='badge bg-info text-dark'>Admin</span>" : "" ?>
                                </td>
                                <td><?= $row['created_at'] ?></td>
                                <td>
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <button class="btn btn-success btn-sm actionBtn" data-action="approve" data-id="<?= $row['id'] ?>">Approve</button>
                                        <button class="btn btn-warning btn-sm actionBtn" data-action="reject" data-id="<?= $row['id'] ?>">Reject</button>
                                    <?php elseif ($row['status'] == 'approved'): ?>
                                        <button class="btn btn-warning btn-sm actionBtn" data-action="reject" data-id="<?= $row['id'] ?>">Reject</button>
                                    <?php elseif ($row['status'] == 'rejected'): ?>
                                        <button class="btn btn-success btn-sm actionBtn" data-action="approve" data-id="<?= $row['id'] ?>">Approve</button>
                                    <?php endif; ?>

                                    <?php if($row['is_admin']==1): ?>
                                        <button class="btn btn-secondary btn-sm actionBtn" data-action="demote" data-id="<?= $row['id'] ?>" title="Remove admin privileges">Demote</button>
                                    <?php endif; ?>

                                    <button class="btn btn-danger btn-sm actionBtn" data-action="delete" data-id="<?= $row['id'] ?>" title="Delete officer">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted">No officers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// read URL notice param and show toast
(function(){
    const params = new URLSearchParams(window.location.search);
    const notice = params.get('notice');
    const type = params.get('type') || 'info';
    if (notice) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type === 'success' ? 'success' : (type === 'error' ? 'error' : 'info'),
            title: decodeURIComponent(notice),
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        // remove params from URL without reload
        history.replaceState(null, '', window.location.pathname);
    }
})();

// bind action buttons to SweetAlert confirm dialogs
document.querySelectorAll('.actionBtn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        const action = btn.dataset.action;
        const id = btn.dataset.id;
        let title = '';
        let text = '';
        let confirmButton = 'Yes';

        switch(action) {
            case 'approve':
                title = 'Approve officer?';
                text = 'This will approve the officer account.';
                break;
            case 'reject':
                title = 'Reject officer?';
                text = 'This will reject the officer account.';
                break;
            case 'delete':
                title = 'Delete officer?';
                text = 'This will permanently delete the officer (cannot be undone).';
                confirmButton = 'Delete';
                break;
            case 'demote':
                title = 'Remove admin privileges?';
                text = 'This will demote the user back to officer role.';
                break;
            default:
                title = 'Confirm action';
                text = 'Proceed?';
        }

        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: (action === 'delete' ? '#d33' : '#3085d6'),
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmButton
        }).then((res) => {
            if (res.isConfirmed) {
                window.location.href = '?action=' + encodeURIComponent(action) + '&id=' + encodeURIComponent(id);
            }
        });
    });
});

// Live Search (unchanged)
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#officersTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});
</script>
</body>
</html>
