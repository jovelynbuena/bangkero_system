<?php
session_start();

if (empty($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

// Handle restore from archive
if (isset($_GET['restore'])) {
    $aid = intval($_GET['restore']);
    if ($aid > 0) {
        try {
            $conn->begin_transaction();
            
            // Restore user (map legacy password column back into password_hash)
            $stmt_insert = $conn->prepare("
                INSERT INTO users (id, username, email, password_hash, role, status, is_admin, created_at)
                SELECT original_id, username, email, password, role, status, is_admin, created_at
                FROM users_archive
                WHERE archive_id = ?
            ");
            $stmt_insert->bind_param("i", $aid);
            $stmt_insert->execute();
            $stmt_insert->close();

            // Delete from archive
            $stmt_delete = $conn->prepare("DELETE FROM users_archive WHERE archive_id = ?");
            $stmt_delete->bind_param("i", $aid);
            $stmt_delete->execute();
            $stmt_delete->close();

            $conn->commit();
            header('Location: archives_users.php?restored=1');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            header('Location: archives_users.php?error=' . urlencode($e->getMessage()));
            exit();
        }
    }
}

// Handle permanent delete
if (isset($_GET['delete_perm'])) {
    $aid = intval($_GET['delete_perm']);
    if ($aid > 0) {
        $stmt_delete = $conn->prepare("DELETE FROM users_archive WHERE archive_id = ?");
        $stmt_delete->bind_param("i", $aid);
        if ($stmt_delete->execute()) {
            header('Location: archives_users.php?deleted=1');
        } else {
            header('Location: archives_users.php?error=delete_failed');
        }
        $stmt_delete->close();
        exit();
    }
}

// Optional search
$search = $_GET['search'] ?? '';
$search_safe = $conn->real_escape_string($search);

$where_clause = "";
if ($search !== '') {
    $where_clause = "WHERE username LIKE '%$search_safe%' OR email LIKE '%$search_safe%'";
}

$sql = "SELECT * FROM users_archive $where_clause ORDER BY archived_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Archived Users | Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
    .main-content { margin-left: 250px; padding: 32px; min-height: 100vh; }
    .page-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 30px; margin-bottom: 30px; box-shadow: 0 8px 32px rgba(102, 126, 234, 0.2); color: white; }
    .table-container { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .table thead th { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; border: none; padding: 16px; font-weight: 600; font-size: 0.85rem; letter-spacing: 0.5px; }
    .btn-restore { background: #10b981; color: white; border: none; border-radius: 8px; padding: 6px 12px; }
    .btn-delete { background: #ef4444; color: white; border: none; border-radius: 8px; padding: 6px 12px; }
    @media (max-width: 991.98px) { .main-content { margin-left: 0; padding: 16px; } }
</style>
</head>
<body>

<?php include('../navbar.php'); ?>

<div class="main-content">
    <div class="page-header">
        <h2><i class="bi bi-archive-fill me-2"></i>Archived User Accounts</h2>
        <p>View, restore or permanently delete archived officer/admin accounts</p>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by username or email..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Archived At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($row['role'])) ?></span></td>
                                <td><?= date('M d, Y g:i A', strtotime($row['archived_at'])) ?></td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button onclick="confirmRestore(<?= $row['archive_id'] ?>, '<?= addslashes($row['username']) ?>')" class="btn-restore" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button>
                                        <button onclick="confirmDelete(<?= $row['archive_id'] ?>, '<?= addslashes($row['username']) ?>')" class="btn-delete" title="Delete Permanently"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No archived accounts found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmRestore(id, name) {
    Swal.fire({
        title: 'Restore Account?',
        text: `Move the account "${name}" back to active list?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Yes, Restore'
    }).then((result) => {
        if (result.isConfirmed) window.location.href = `?restore=${id}`;
    });
}

function confirmDelete(id, name) {
    Swal.fire({
        title: 'Delete Permanently?',
        text: `This will permanently delete the account "${name}". This cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) window.location.href = `?delete_perm=${id}`;
    });
}

<?php if (isset($_GET['restored'])): ?>
    Swal.fire({ icon: 'success', title: 'Restored!', text: 'Account moved to active list.', timer: 2000, showConfirmButton: false });
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
    Swal.fire({ icon: 'success', title: 'Deleted!', text: 'Account permanently removed.', timer: 2000, showConfirmButton: false });
<?php endif; ?>
</script>
</body>
</html>
