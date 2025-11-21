<?php
session_start();

if ($_SESSION['username'] == "") {
    header('location: login.php');
}
require_once('../../config/db_connect.php');

$successMsg = $errorMsg = "";

// Add Role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_role'])) {
    $role_name = trim($_POST['role_name']);
    $description = trim($_POST['description']);
    if ($role_name == "") {
        $errorMsg = "Role name is required!";
    } else {
        $stmt = $conn->prepare("INSERT INTO officer_roles (role_name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $role_name, $description);
        if ($stmt->execute()) $successMsg = "New officer role added successfully!";
        else $errorMsg = "Error: " . $conn->error;
        $stmt->close();
    }
}

// Edit Role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_role'])) {
    $id = intval($_POST['id']);
    $role_name = trim($_POST['role_name']);
    $description = trim($_POST['description']);
    if ($role_name == "") {
        $errorMsg = "Role name cannot be empty!";
    } else {
        $stmt = $conn->prepare("UPDATE officer_roles SET role_name=?, description=? WHERE id=?");
        $stmt->bind_param("ssi", $role_name, $description, $id);
        if ($stmt->execute()) $successMsg = "Role updated successfully!";
        else $errorMsg = "Error: " . $conn->error;
        $stmt->close();
    }
}

// Delete Role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_role'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM officer_roles WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) $successMsg = "Role deleted successfully!";
    else $errorMsg = "Error: " . $conn->error;
    $stmt->close();
}

// Fetch Roles
$rolesResult = $conn->query("SELECT * FROM officer_roles ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Officer Roles | Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body {
        background: #f6f9fc;
        font-family: 'Segoe UI', sans-serif;
    }
    .main-content {
        margin-left: 250px;
        padding: 40px;
    }
    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        transition: 0.3s;
    }
    .card:hover {
        transform: translateY(-2px);
    }
    .card-header {
        border-bottom: none;
        font-weight: 600;
    }
    .btn-primary {
        background-color: #ff7043;
        border: none;
        border-radius: 8px;
    }
    .btn-primary:hover {
        background-color: #00897b;
    }
    .btn-warning, .btn-danger {
        border-radius: 8px;
    }
    .alert {
        border-radius: 10px;
    }
    table th {
        background-color: #e3f2fd;
    }
    table td, table th {
        vertical-align: middle;
    }
    @media (max-width: 992px) {
        .main-content { margin-left: 0; padding: 20px; }
    }
</style>
</head>
<body>

<?php include('../navbar.php'); ?>

<div class="main-content">
    <div class="text-center mb-5">
        <h2 class="fw-bold text-primary">Manage Officer Roles</h2>
        <p class="text-muted">Add, update, or remove officer positions with ease</p>
    </div>

    <?php if ($successMsg): ?>
        <div class="alert alert-success text-center fw-semibold"><?= $successMsg ?></div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <div class="alert alert-danger text-center fw-semibold"><?= $errorMsg ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Add Role -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white text-center text-primary fs-5">Add New Role</div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Role Name</label>
                            <input type="text" name="role_name" class="form-control" placeholder="Enter role name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" placeholder="Brief description (optional)"></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="add_role" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i> Add Role
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Roles Table -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white text-primary fs-5 d-flex justify-content-between align-items-center">
                    <span>Existing Roles</span>
                    <i class="bi bi-people-fill fs-4 text-muted"></i>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead>
                                <tr>
                                    <th width="8%">#</th>
                                    <th>Role Name</th>
                                    <th>Description</th>
                                    <th width="20%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($rolesResult->num_rows > 0): ?>
                                    <?php while ($row = $rolesResult->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= htmlspecialchars($row['role_name']) ?></td>
                                            <td><?= htmlspecialchars($row['description']) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning me-1" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-name="<?= htmlspecialchars($row['role_name']) ?>"
                                                    data-description="<?= htmlspecialchars($row['description']) ?>">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-name="<?= htmlspecialchars($row['role_name']) ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted py-4">No roles defined yet</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <form method="post">
        <div class="modal-header bg-warning text-white rounded-top-4">
          <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Role</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="id" id="edit_id">
            <div class="mb-3">
                <label class="form-label">Role Name</label>
                <input type="text" name="role_name" id="edit_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" id="edit_description" class="form-control"></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="edit_role" class="btn btn-warning text-white px-4">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Role Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <form method="post">
        <div class="modal-header bg-danger text-white rounded-top-4">
          <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <input type="hidden" name="id" id="delete_id">
          <p class="fs-5 mb-3">Are you sure you want to delete <strong id="delete_name"></strong>?</p>
          <p class="text-muted small">This action cannot be undone.</p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="delete_role" class="btn btn-danger px-4">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const editModal = document.getElementById('editModal');
editModal.addEventListener('show.bs.modal', e => {
    const b = e.relatedTarget;
    document.getElementById('edit_id').value = b.getAttribute('data-id');
    document.getElementById('edit_name').value = b.getAttribute('data-name');
    document.getElementById('edit_description').value = b.getAttribute('data-description');
});
const deleteModal = document.getElementById('deleteModal');
deleteModal.addEventListener('show.bs.modal', e => {
    const b = e.relatedTarget;
    document.getElementById('delete_id').value = b.getAttribute('data-id');
    document.getElementById('delete_name').innerText = b.getAttribute('data-name');
});
</script>
</body>
</html>
