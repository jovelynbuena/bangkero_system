<?php 
session_start();

if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header('location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$successMsg = $errorMsg = "";

// ========================================
// ADD ROLE WITH VALIDATIONS & RESTRICTIONS
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_role'])) {
    $role_name = trim($_POST['role_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // ✅ RESTRICTION 1: Required field validation
    if (empty($role_name)) {
        $errorMsg = "Role name is required!";
    }
    // ✅ RESTRICTION 2: Role name length (min 3, max 50 characters)
    elseif (strlen($role_name) < 3) {
        $errorMsg = "Role name must be at least 3 characters long!";
    }
    elseif (strlen($role_name) > 50) {
        $errorMsg = "Role name must not exceed 50 characters!";
    }
    // ✅ RESTRICTION 3: Check for duplicate role name (case-insensitive)
    else {
        $checkStmt = $conn->prepare("SELECT id FROM officer_roles WHERE LOWER(role_name) = LOWER(?)");
        $checkStmt->bind_param("s", $role_name);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $errorMsg = "This role name already exists! Please use a different name.";
        } else {
            // ✅ RESTRICTION 4: Sanitize description length (max 500 characters)
            if (strlen($description) > 500) {
                $description = substr($description, 0, 500);
            }
            
            // Insert with timestamp
            $stmt = $conn->prepare("INSERT INTO officer_roles (role_name, description, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $role_name, $description);
            
            if ($stmt->execute()) {
                $successMsg = "New officer role added successfully!";
            } else {
                $errorMsg = "Database error: " . $conn->error;
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
}

// ========================================
// EDIT ROLE WITH VALIDATIONS & RESTRICTIONS
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_role'])) {
    $id = intval($_POST['id'] ?? 0);
    $role_name = trim($_POST['role_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // ✅ RESTRICTION 1: Validate ID
    if ($id <= 0) {
        $errorMsg = "Invalid role ID!";
    }
    // ✅ RESTRICTION 2: Required field validation
    elseif (empty($role_name)) {
        $errorMsg = "Role name cannot be empty!";
    }
    // ✅ RESTRICTION 3: Role name length
    elseif (strlen($role_name) < 3) {
        $errorMsg = "Role name must be at least 3 characters long!";
    }
    elseif (strlen($role_name) > 50) {
        $errorMsg = "Role name must not exceed 50 characters!";
    }
    // ✅ RESTRICTION 4: Check for duplicate role name (excluding current role)
    else {
        $checkStmt = $conn->prepare("SELECT id FROM officer_roles WHERE LOWER(role_name) = LOWER(?) AND id != ?");
        $checkStmt->bind_param("si", $role_name, $id);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $errorMsg = "This role name already exists! Please use a different name.";
        } else {
            // ✅ RESTRICTION 5: Sanitize description length
            if (strlen($description) > 500) {
                $description = substr($description, 0, 500);
            }
            
            // Update with timestamp
            $stmt = $conn->prepare("UPDATE officer_roles SET role_name=?, description=?, updated_at=NOW() WHERE id=?");
            $stmt->bind_param("ssi", $role_name, $description, $id);
            
            if ($stmt->execute()) {
                $successMsg = "Role updated successfully!";
            } else {
                $errorMsg = "Database error: " . $conn->error;
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
}

// ========================================
// DELETE ROLE WITH RESTRICTIONS
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_role'])) {
    $id = intval($_POST['id'] ?? 0);
    
    // ✅ RESTRICTION 1: Validate ID
    if ($id <= 0) {
        $errorMsg = "Invalid role ID!";
    } else {
        // ✅ RESTRICTION 2: Check if role is assigned to any officers
        // (Assuming there's a relationship between officers and roles)
        // Uncomment if you have officer_users table with role_id
        /*
        $checkUsage = $conn->prepare("SELECT COUNT(*) as count FROM officer_users WHERE role_id = ?");
        $checkUsage->bind_param("i", $id);
        $checkUsage->execute();
        $result = $checkUsage->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            $errorMsg = "Cannot delete this role! It is currently assigned to " . $row['count'] . " officer(s).";
        } else {
        */
            $stmt = $conn->prepare("DELETE FROM officer_roles WHERE id=?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $successMsg = "Role deleted successfully!";
            } else {
                $errorMsg = "Database error: " . $conn->error;
            }
            $stmt->close();
        /*}
        $checkUsage->close();
        */
    }
}

// ========================================
// FETCH ROLES WITH STATISTICS
// ========================================
$rolesResult = $conn->query("SELECT * FROM officer_roles ORDER BY created_at DESC");
$totalRoles = $rolesResult->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Officer Roles | Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { 
        font-family: 'Inter', sans-serif; 
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }
    .main-content { 
        margin-left: 250px; 
        padding: 32px; 
        min-height: 100vh; 
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 32px;
        border-radius: 20px;
        color: white;
        margin-bottom: 32px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    .page-header h2 {
        font-size: 32px;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    /* Card Styles */
    .card { 
        border: none; 
        border-radius: 20px; 
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12); 
        margin-bottom: 24px;
        background: white;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.16);
    }
    
    .card-header { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white; 
        font-weight: 600; 
        font-size: 18px;
        padding: 20px 24px;
        border-bottom: none;
    }
    
    .card-header i {
        margin-right: 10px;
    }
    
    .card-body { 
        padding: 24px; 
    }

    /* Form Styles */
    .form-select, .form-control, textarea { 
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 12px 16px;
        transition: all 0.3s ease;
        font-size: 15px;
    }
    
    .form-select:focus, .form-control:focus, textarea:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        outline: none;
    }
    
    .form-label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 8px;
        font-size: 14px;
    }

    /* Button Styles */
    .btn { 
        font-size: 15px;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .btn-primary { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .btn-primary:hover { 
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    }
    
    .btn-warning {
        background: #f59e0b;
        color: white;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);
    }
    
    .btn-warning:hover {
        background: #d97706;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }
    
    .btn-danger {
        background: #ef4444;
        color: white;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
    }
    
    .btn-danger:hover {
        background: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }
    
    .btn-secondary { 
        background: #6b7280;
        color: white;
        box-shadow: 0 2px 8px rgba(107, 114, 128, 0.2);
    }
    
    .btn-secondary:hover { 
        background: #4b5563;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
    }

    /* Table Styles */
    .table { 
        font-size: 15px;
        margin: 0;
    }
    
    .table thead th { 
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white; 
        border: none;
        padding: 16px;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table tbody td {
        padding: 16px;
        vertical-align: middle;
        border-color: #f1f3f5;
        color: #000;
        font-weight: 500;
    }
    
    .table-hover tbody tr {
        transition: all 0.3s ease;
    }
    
    .table-hover tbody tr:hover { 
        background-color: #f8f9ff;
        transform: scale(1.005);
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }
    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px 20px 0 0;
        padding: 24px;
        border: none;
    }
    .modal-header .modal-title {
        font-weight: 700;
        font-size: 20px;
    }
    .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }
    .modal-body {
        padding: 32px;
    }
    .modal-footer {
        padding: 20px 32px;
        border-top: 1px solid #f0f0f0;
    }

    /* Warning Modal Header */
    .modal-header.bg-warning-custom {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    /* Danger Modal Header */
    .modal-header.bg-danger-custom {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    /* Statistics Cards */
    .stats-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        text-align: center;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .stats-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.16);
    }
    
    .stats-card .icon {
        font-size: 48px;
        margin-bottom: 12px;
    }
    
    .stats-card h3 {
        font-size: 36px;
        font-weight: 700;
        margin: 12px 0 8px 0;
        color: #2d3748;
    }
    
    .stats-card p {
        color: #718096;
        margin: 0;
        font-size: 14px;
        font-weight: 500;
    }

    /* Input Group for Search */
    .input-group .input-group-text {
        border: 2px solid #e0e0e0;
        border-radius: 10px 0 0 10px;
        padding: 8px 12px;
        background: white;
    }
    
    .input-group .form-control {
        border: 2px solid #e0e0e0;
        border-radius: 0 10px 10px 0;
        padding: 8px 12px;
    }
    
    .input-group .form-control:focus {
        border-color: #667eea;
        box-shadow: none;
    }

    /* Character Counter */
    .char-counter {
        font-size: 12px;
        color: #718096;
        margin-top: 4px;
    }

    @media (max-width: 991.98px) { 
        .main-content { 
            margin-left: 0; 
            padding: 20px; 
        }
        
        .page-header h2 {
            font-size: 24px;
        }
    }
</style>
</head>
<body>



<?php include('../navbar.php'); ?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h2><i class="bi bi-person-badge-fill"></i>Manage Officer Roles</h2>
            <p class="mb-0 mt-2" style="opacity: 0.9;">Add, update, or remove officer positions and responsibilities</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="stats-card" style="border-left: 4px solid #667eea;">
                <div class="icon" style="color: #667eea;">
                    <i class="bi bi-list-ul"></i>
                </div>
                <h3><?= $totalRoles ?></h3>
                <p>Total Roles</p>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="stats-card" style="border-left: 4px solid #f59e0b;">
                <div class="icon" style="color: #f59e0b;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <h3>Active</h3>
                <p>Role Management</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Add Role -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-plus-circle-fill"></i> Add New Role
                </div>
                <div class="card-body">
                    <form method="post" id="addRoleForm">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-tag-fill me-2"></i>Role Name 
                                <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="role_name" 
                                id="role_name_add" 
                                class="form-control" 
                                placeholder="e.g., President, Secretary" 
                                required 
                                minlength="3"
                                maxlength="50">
                            <div class="char-counter">
                                <span id="add_name_count">0</span>/50 characters
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-textarea-t me-2"></i>Description
                            </label>
                            <textarea 
                                name="description" 
                                id="description_add"
                                class="form-control" 
                                rows="5" 
                                placeholder="Brief description of role responsibilities (optional)"
                                maxlength="500"></textarea>
                            <div class="char-counter">
                                <span id="add_desc_count">0</span>/500 characters
                            </div>
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
            <!-- Search Bar -->
            <div class="card mb-3">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <h6 class="mb-0 text-muted"><i class="bi bi-list-ul me-2"></i>Officer Roles List</h6>
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input 
                                type="text" 
                                id="searchInput" 
                                class="form-control border-start-0 ps-0" 
                                placeholder="Search roles...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Roles Table Card -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-table"></i> Existing Roles
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center" id="rolesTable">
                        <thead>
                            <tr>
                                <th width="8%">ID</th>
                                <th>Role Name</th>
                                <th>Description</th>
                                <th width="20%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($rolesResult->num_rows > 0): ?>
                                <?php while ($row = $rolesResult->fetch_assoc()): ?>
                                    <tr data-role-name="<?= htmlspecialchars($row['role_name'] ?? '') ?>" data-description="<?= htmlspecialchars($row['description'] ?? '') ?>">
                                        <td style="font-weight: 600;"><?= htmlspecialchars($row['id'] ?? '') ?></td>
                                        <td style="font-weight: 600;"><?= htmlspecialchars($row['role_name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['description'] ?? 'No description') ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm me-1" 
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModal"
                                                data-id="<?= htmlspecialchars($row['id'] ?? '') ?>"
                                                data-name="<?= htmlspecialchars($row['role_name'] ?? '') ?>"
                                                data-description="<?= htmlspecialchars($row['description'] ?? '') ?>">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                            <button class="btn btn-danger btn-sm" 
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-id="<?= htmlspecialchars($row['id'] ?? '') ?>"
                                                data-name="<?= htmlspecialchars($row['role_name'] ?? '') ?>">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No roles defined yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" id="editRoleForm">
        <div class="modal-header bg-warning-custom">
          <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Role</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="id" id="edit_id">
            <div class="mb-3">
                <label class="form-label">
                    <i class="bi bi-tag-fill me-2"></i>Role Name 
                    <span class="text-danger">*</span>
                </label>
                <input 
                    type="text" 
                    name="role_name" 
                    id="edit_name" 
                    class="form-control" 
                    required
                    minlength="3"
                    maxlength="50">
                <div class="char-counter">
                    <span id="edit_name_count">0</span>/50 characters
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-textarea-t me-2"></i>Description</label>
                <textarea 
                    name="description" 
                    id="edit_description" 
                    class="form-control" 
                    rows="5"
                    maxlength="500"></textarea>
                <div class="char-counter">
                    <span id="edit_desc_count">0</span>/500 characters
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="edit_role" class="btn btn-warning"><i class="bi bi-save me-2"></i>Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Role Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header bg-danger-custom">
          <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <input type="hidden" name="id" id="delete_id">
          <div class="mb-3">
            <i class="bi bi-exclamation-circle text-danger" style="font-size: 64px;"></i>
          </div>
          <p class="fs-5 mb-3">Are you sure you want to delete <strong id="delete_name"></strong>?</p>
          <p class="text-muted">This action cannot be undone.</p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="delete_role" class="btn btn-danger"><i class="bi bi-trash me-2"></i>Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ========================================
// CHARACTER COUNTER FUNCTIONS
// ========================================
function updateCharCount(inputId, counterId, maxLength) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    if (input && counter) {
        input.addEventListener('input', function() {
            const length = this.value.length;
            counter.textContent = length;
            
            // Change color based on length
            if (length > maxLength * 0.9) {
                counter.style.color = '#ef4444'; // Red
            } else if (length > maxLength * 0.7) {
                counter.style.color = '#f59e0b'; // Orange
            } else {
                counter.style.color = '#718096'; // Gray
            }
        });
    }
}

// Initialize character counters
document.addEventListener('DOMContentLoaded', function() {
    updateCharCount('role_name_add', 'add_name_count', 50);
    updateCharCount('description_add', 'add_desc_count', 500);
    updateCharCount('edit_name', 'edit_name_count', 50);
    updateCharCount('edit_description', 'edit_desc_count', 500);
});

// ========================================
// SEARCH FUNCTIONALITY
// ========================================
document.getElementById('searchInput')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#rolesTable tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length < 4) return; // Skip empty state row
        
        const roleName = row.dataset.roleName?.toLowerCase() || '';
        const description = row.dataset.description?.toLowerCase() || '';
        const id = row.cells[0]?.textContent.toLowerCase() || '';
        
        const matches = roleName.includes(searchTerm) || 
                       description.includes(searchTerm) || 
                       id.includes(searchTerm);
        
        row.style.display = matches ? '' : 'none';
    });
});

// ========================================
// EDIT MODAL HANDLER
// ========================================
const editModal = document.getElementById('editModal');
editModal.addEventListener('show.bs.modal', e => {
    const b = e.relatedTarget;
    const id = b.getAttribute('data-id') ?? '';
    const name = b.getAttribute('data-name') ?? '';
    const description = b.getAttribute('data-description') ?? '';
    
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = description;
    
    // Update character counters
    document.getElementById('edit_name_count').textContent = name.length;
    document.getElementById('edit_desc_count').textContent = description.length;
});

// ========================================
// DELETE MODAL HANDLER
// ========================================
const deleteModal = document.getElementById('deleteModal');
deleteModal.addEventListener('show.bs.modal', e => {
    const b = e.relatedTarget;
    document.getElementById('delete_id').value = b.getAttribute('data-id') ?? '';
    document.getElementById('delete_name').innerText = b.getAttribute('data-name') ?? '';
});

// ========================================
// FORM VALIDATION
// ========================================
document.getElementById('addRoleForm')?.addEventListener('submit', function(e) {
    const roleName = document.getElementById('role_name_add').value.trim();
    
    if (roleName.length < 3) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Input',
            text: 'Role name must be at least 3 characters long!',
            confirmButtonColor: '#667eea'
        });
        return false;
    }
    
    if (roleName.length > 50) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Input',
            text: 'Role name must not exceed 50 characters!',
            confirmButtonColor: '#667eea'
        });
        return false;
    }
});

document.getElementById('editRoleForm')?.addEventListener('submit', function(e) {
    const roleName = document.getElementById('edit_name').value.trim();
    
    if (roleName.length < 3) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Input',
            text: 'Role name must be at least 3 characters long!',
            confirmButtonColor: '#667eea'
        });
        return false;
    }
    
    if (roleName.length > 50) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Input',
            text: 'Role name must not exceed 50 characters!',
            confirmButtonColor: '#667eea'
        });
        return false;
    }
});

// ========================================
// SWEETALERT MESSAGES
// ========================================
<?php if ($successMsg): ?>
Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: '<?= addslashes($successMsg) ?>',
    confirmButtonColor: '#667eea',
    timer: 2000,
    showConfirmButton: false
}).then(() => {
    window.location.href = "officer_roles.php";
});
<?php endif; ?>

<?php if ($errorMsg): ?>
Swal.fire({
    icon: 'error',
    title: 'Error!',
    text: '<?= addslashes($errorMsg) ?>',
    confirmButtonColor: '#ef4444'
});
<?php endif; ?>
</script>
</body>
</html>
