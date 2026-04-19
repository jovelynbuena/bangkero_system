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

// ========================================
// SCHEMA FEATURE-DETECTION (avoid fatal on old DBs)
// ========================================
function tableExists(mysqli $conn, string $table): bool {
    $tableEsc = $conn->real_escape_string($table);
    $res = $conn->query("SHOW TABLES LIKE '{$tableEsc}'");
    return ($res && $res->num_rows > 0);
}

function tableHasColumn(mysqli $conn, string $table, string $column): bool {
    $tableEsc = $conn->real_escape_string($table);
    $colEsc = $conn->real_escape_string($column);
    $res = $conn->query("SHOW COLUMNS FROM `{$tableEsc}` LIKE '{$colEsc}'");
    return ($res && $res->num_rows > 0);
}

$hasOfficerRolesCreatedAt = false;
$hasOfficerRolesUpdatedAt = false;
$hasOfficerRolesDisplayOrder = false;
$hasOfficerRolesArchiveExists = false;
$hasOfficerRolesArchiveCreatedAt = false;

try {
    $hasOfficerRolesCreatedAt = tableHasColumn($conn, 'officer_roles', 'created_at');
    $hasOfficerRolesUpdatedAt = tableHasColumn($conn, 'officer_roles', 'updated_at');
    $hasOfficerRolesDisplayOrder = tableHasColumn($conn, 'officer_roles', 'display_order');
    $hasOfficerRolesArchiveExists = tableExists($conn, 'officer_roles_archive');
    $hasOfficerRolesArchiveCreatedAt = $hasOfficerRolesArchiveExists
        ? tableHasColumn($conn, 'officer_roles_archive', 'created_at')
        : false;
} catch (Throwable $e) {
    // If schema checks fail for any reason, default to safest behavior.
    $hasOfficerRolesCreatedAt = false;
    $hasOfficerRolesUpdatedAt = false;
    $hasOfficerRolesDisplayOrder = false;
    $hasOfficerRolesArchiveExists = false;
    $hasOfficerRolesArchiveCreatedAt = false;
}

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
            
            // Insert (use created_at only if the column exists)
            if ($hasOfficerRolesCreatedAt) {
                $stmt = $conn->prepare("INSERT INTO officer_roles (role_name, description, created_at) VALUES (?, ?, NOW())");
                $stmt->bind_param("ss", $role_name, $description);
            } else {
                $stmt = $conn->prepare("INSERT INTO officer_roles (role_name, description) VALUES (?, ?)");
                $stmt->bind_param("ss", $role_name, $description);
            }
            
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
            
            // Update (use updated_at only if the column exists)
            if ($hasOfficerRolesUpdatedAt) {
                $stmt = $conn->prepare("UPDATE officer_roles SET role_name=?, description=?, updated_at=NOW() WHERE id=?");
                $stmt->bind_param("ssi", $role_name, $description, $id);
            } else {
                $stmt = $conn->prepare("UPDATE officer_roles SET role_name=?, description=? WHERE id=?");
                $stmt->bind_param("ssi", $role_name, $description, $id);
            }

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
// ARCHIVE ROLE WITH RESTRICTIONS
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_role'])) {
    $id = intval($_POST['id'] ?? 0);
    
    // ✅ RESTRICTION 1: Validate ID
    if ($id <= 0) {
        $errorMsg = "Invalid role ID!";
    } else {
        // ✅ RESTRICTION 2: Cannot archive if officers are currently assigned to this role
        $checkAssigned = $conn->prepare("SELECT COUNT(*) AS cnt FROM officers WHERE role_id = ?");
        $checkAssigned->bind_param("i", $id);
        $checkAssigned->execute();
        $assignedCount = (int)$checkAssigned->get_result()->fetch_assoc()['cnt'];
        $checkAssigned->close();

        if ($assignedCount > 0) {
            $errorMsg = "Cannot archive this role — {$assignedCount} officer(s) are currently assigned to it. Please reassign them first before archiving.";
        } else {
        try {
            $conn->begin_transaction();
            
            // Move to archive (handle DBs without archive table / created_at)
            if ($hasOfficerRolesArchiveExists) {
                if ($hasOfficerRolesArchiveCreatedAt) {
                    if ($hasOfficerRolesCreatedAt) {
                        $stmt = $conn->prepare("INSERT INTO officer_roles_archive (original_id, role_name, description, created_at) SELECT id, role_name, description, created_at FROM officer_roles WHERE id=?");
                    } else {
                        $stmt = $conn->prepare("INSERT INTO officer_roles_archive (original_id, role_name, description, created_at) SELECT id, role_name, description, NOW() FROM officer_roles WHERE id=?");
                    }
                } else {
                    $stmt = $conn->prepare("INSERT INTO officer_roles_archive (original_id, role_name, description) SELECT id, role_name, description FROM officer_roles WHERE id=?");
                }
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
            }
            
            // Delete from main
            $stmt = $conn->prepare("DELETE FROM officer_roles WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
            $successMsg = "Role archived successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $errorMsg = "Error archiving role: " . $e->getMessage();
        }
        } // end else (not assigned)
    }
}

// ========================================
// FETCH ROLES WITH STATISTICS
// ========================================
// Sort by display_order (hierarchy) if column exists, otherwise fallback to created_at or id
if ($hasOfficerRolesDisplayOrder) {
    $orderBy = 'display_order ASC, id ASC';
} elseif ($hasOfficerRolesCreatedAt) {
    $orderBy = 'created_at DESC';
} else {
    $orderBy = 'id DESC';
}
$rolesResult = $conn->query("SELECT * FROM officer_roles ORDER BY {$orderBy}");
$totalRoles = $rolesResult->num_rows;

// Fetch assignment counts per role
$assignmentCounts = [];
$acRes = $conn->query("SELECT o.role_id, COUNT(*) AS cnt FROM officers o INNER JOIN members m ON o.member_id = m.id WHERE o.role_id IS NOT NULL GROUP BY o.role_id");
if ($acRes) {
    while ($acRow = $acRes->fetch_assoc()) {
        $assignmentCounts[(int)$acRow['role_id']] = (int)$acRow['cnt'];
    }
}
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

<link rel="stylesheet" href="../../css/admin-theme.css">
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
        background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
        padding: 32px;
        border-radius: 20px;
        color: white;
        margin-bottom: 32px;
        box-shadow: 0 10px 30px rgba(46, 134, 171, 0.30);
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
        background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
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
        border-color: #2E86AB;
        box-shadow: 0 0 0 4px rgba(46, 134, 171, 0.10);
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
        background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(46, 134, 171, 0.30);
    }
    
    .btn-primary:hover { 
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(46, 134, 171, 0.40);
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

    /* Small action buttons (match memberlist style) */
    .btn-sm {
        padding: 8px 12px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        transition: background-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
        border: none;
        margin-right: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-edit {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        color: white;
    }

    .btn-archive {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .btn-archive:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        color: white;
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
        background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
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

    /* Statistics Cards (more compact) */
    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 10px 14px;
        text-align: center;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.10);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .stats-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 26px rgba(0, 0, 0, 0.14);
    }
    
    .stats-card .icon {
        font-size: 22px;
        margin-bottom: 4px;
    }
    
    .stats-card h3 {
        font-size: 20px;
        font-weight: 700;
        margin: 4px 0 2px 0;
        color: #2d3748;
    }
    
    .stats-card p {
        color: #718096;
        margin: 0;
        font-size: 11px;
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
        border-color: #2E86AB;
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
    <div class="row mb-2">

        <div class="col-md-6 mb-2">
            <div class="stats-card" style="border-left: 4px solid #2E86AB;">
                <div class="icon" style="color: #2E86AB;">
                    <i class="bi bi-list-ul"></i>
                </div>
                <h3><?= $totalRoles ?></h3>
                <p>Total Roles</p>
            </div>
        </div>
        <div class="col-md-6 mb-2">
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
                                <th width="10%">Assigned</th>
                                <th width="20%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($rolesResult->num_rows > 0): ?>
                                <?php $rowNum = 1; while ($row = $rolesResult->fetch_assoc()): 
                                    $roleId = (int)$row['id'];
                                    $assignedCount = $assignmentCounts[$roleId] ?? 0;
                                    $isInUse = $assignedCount > 0;
                                ?>
                                    <tr data-role-name="<?= htmlspecialchars($row['role_name'] ?? '') ?>" data-description="<?= htmlspecialchars($row['description'] ?? '') ?>">
                                        <td style="font-weight: 600;"><?= $rowNum++ ?></td>
                                        <td style="font-weight: 600;"><?= htmlspecialchars($row['role_name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['description'] ?? 'No description') ?></td>
                                        <td>
                                            <?php if ($isInUse): ?>
                                                <span class="badge bg-success"><?= $assignedCount ?> officer<?= $assignedCount > 1 ? 's' : '' ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-edit btn-sm me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModal"
                                                data-id="<?= htmlspecialchars($row['id'] ?? '') ?>"
                                                data-name="<?= htmlspecialchars($row['role_name'] ?? '') ?>"
                                                data-description="<?= htmlspecialchars($row['description'] ?? '') ?>"
                                                title="Edit role" aria-label="Edit role">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <?php if ($isInUse): ?>
                                                <button class="btn btn-sm btn-secondary" disabled
                                                    title="Cannot archive — <?= $assignedCount ?> officer(s) assigned"
                                                    style="opacity:0.5; cursor:not-allowed;">
                                                    <i class="bi bi-lock-fill"></i>
                                                </button>
                                            <?php else: ?>
                                            <button class="btn btn-archive btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#archiveModal"
                                                data-id="<?= htmlspecialchars($row['id'] ?? '') ?>"
                                                data-name="<?= htmlspecialchars($row['role_name'] ?? '') ?>"
                                                title="Archive role" aria-label="Archive role">
                                                <i class="bi bi-archive"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>

                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No roles defined yet</td></tr>
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

<!-- Archive Role Modal -->
<div class="modal fade" id="archiveModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title"><i class="bi bi-archive-fill me-2"></i>Confirm Archive</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <input type="hidden" name="id" id="archive_id">
          <div class="mb-3">
            <i class="bi bi-archive text-info" style="font-size: 64px;"></i>
          </div>
          <p class="fs-5 mb-3">Are you sure you want to archive <strong id="archive_name"></strong>?</p>
          <p class="text-muted">This will move the role to the archive list.</p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="archive_role" class="btn btn-info text-white"><i class="bi bi-archive me-2"></i>Archive</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Initialize Bootstrap tooltips for action buttons
const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.forEach(function (tooltipTriggerEl) {
    new bootstrap.Tooltip(tooltipTriggerEl, {
        trigger: 'hover focus',
        boundary: 'window'
    });
});

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
// ARCHIVE MODAL HANDLER
// ========================================
const archiveModal = document.getElementById('archiveModal');
archiveModal.addEventListener('show.bs.modal', e => {
    const b = e.relatedTarget;
    document.getElementById('archive_id').value = b.getAttribute('data-id') ?? '';
    document.getElementById('archive_name').innerText = b.getAttribute('data-name') ?? '';
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
            confirmButtonColor: '#2E86AB'
        });
        return false;
    }
    
    if (roleName.length > 50) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Input',
            text: 'Role name must not exceed 50 characters!',
            confirmButtonColor: '#2E86AB'
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
            confirmButtonColor: '#2E86AB'
        });
        return false;
    }
    
    if (roleName.length > 50) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Input',
            text: 'Role name must not exceed 50 characters!',
            confirmButtonColor: '#2E86AB'
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
    confirmButtonColor: '#2E86AB',
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
