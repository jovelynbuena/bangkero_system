<?php
/**
 * ADMIN USER MANAGEMENT
 * Manage user accounts and reset passwords
 */

session_start();
if (empty($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once('../config/db_connect.php');

$message = '';
$error = '';

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $user_id = intval($_POST['user_id']);
    $temp_password = $_POST['temp_password'] ?? '';
    
    if (empty($temp_password) || strlen($temp_password) < 6) {
        $error = 'Temporary password must be at least 6 characters.';
    } else {
        // Hash the temporary password
        $temp_hash = password_hash($temp_password, PASSWORD_DEFAULT);
        
        // Update user with temporary password and force change flag
        $stmt = $conn->prepare("UPDATE users 
                               SET temp_password = ?, 
                                   force_password_change = 1,
                                   reset_token_hash = NULL,
                                   reset_token_expires_at = NULL
                               WHERE id = ?");
        $stmt->bind_param("si", $temp_hash, $user_id);
        
        if ($stmt->execute()) {
            // Get user info for display
            $user_stmt = $conn->prepare("SELECT username, email, first_name, last_name FROM users WHERE id = ?");
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $user_info = $user_stmt->get_result()->fetch_assoc();
            
            $message = "Password reset successful for <strong>" . htmlspecialchars($user_info['username']) . "</strong>.<br>
                       Temporary password: <code>" . htmlspecialchars($temp_password) . "</code><br>
                       <span class='text-muted'>The user will be required to change this password on next login.</span>";
        } else {
            $error = 'Failed to reset password. Please try again.';
        }
    }
}



if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role_filter = isset($_GET['role_filter']) ? $_GET['role_filter'] : '';

// Build the query with filters
$current_user_id = $_SESSION['user_id'];
$params = [$current_user_id];
$types = "i";

$sql = "SELECT id, username, email, first_name, last_name, role, status, force_password_change, created_at 
        FROM users 
        WHERE id != ? 
        AND status = 'approved'
        AND role IN ('admin', 'officer')";

// Add role filter
if (!empty($role_filter) && in_array($role_filter, ['admin', 'officer'])) {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

// Add search filter
if (!empty($search)) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR username LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssss";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$users_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Accounts | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: #f5f7fa;
            min-height: 100vh;
        }
        .main-content {
            margin-left: 270px;
            padding: 30px;
        }
        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }
        .header-section {
            background: linear-gradient(90deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        .header-section h1 {
            font-weight: 700;
            margin: 0;
        }
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 20px 24px;
            border-radius: 16px 16px 0 0 !important;
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            border-top: none;
            padding: 16px;
        }
        .table td {
            padding: 16px;
            vertical-align: middle;
        }
        .badge-status {
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .badge-approved {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .btn-action {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-reset {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: none;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
            color: white;
        }
        .btn-approve {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
        }
        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
            color: white;
        }
        .alert-custom {
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        .force-change-badge {
            background: #fee2e2;
            color: #dc2626;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 8px;
        }
        .filter-section {
            background: #f8f9fa;
            padding: 20px 24px;
            border-bottom: 1px solid #e9ecef;
        }
        .search-input {
            border-radius: 10px;
            border: 1px solid #dee2e6;
            padding: 10px 16px;
        }
        .search-input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }
        .filter-select {
            border-radius: 10px;
            border: 1px solid #dee2e6;
            padding: 10px 16px;
        }
        .btn-filter {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
        }
        .results-count {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="main-content">

    <div class="header-section">
        <div class="container">
            <div>
                <h1><i class="bi bi-key-fill me-2"></i>Officer Accounts</h1>
                <p class="mb-0 mt-2 opacity-75">Manage approved officer accounts and reset passwords</p>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <?php if ($message): ?>
            <div class="alert alert-success alert-custom">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-custom">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Approved Officer Accounts</h5>
                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Only approved accounts shown</span>
            </div>
            
            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold text-muted small mb-1">
                            <i class="bi bi-search me-1"></i>Search
                        </label>
                        <input type="text" name="search" class="form-control search-input" 
                               placeholder="Search by name, username, or email..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-muted small mb-1">
                            <i class="bi bi-funnel me-1"></i>Role Filter
                        </label>
                        <select name="role_filter" class="form-select filter-select">
                            <option value="">All Roles</option>
                            <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="officer" <?php echo $role_filter === 'officer' ? 'selected' : ''; ?>>Officer</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-filter flex-fill">
                                <i class="bi bi-funnel-fill me-1"></i>Apply Filters
                            </button>
                            <?php if (!empty($search) || !empty($role_filter)): ?>
                                <a href="admin_users.php" class="btn btn-outline-secondary btn-filter">
                                    <i class="bi bi-x-lg"></i> Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="card-body p-0">
                <?php 
                $total_results = $users_result ? $users_result->num_rows : 0;
                if ($users_result && $total_results > 0): 
                ?>
                    <div class="p-3 border-bottom bg-light">
                        <span class="results-count">
                            <i class="bi bi-people-fill me-1"></i>
                            Showing <strong><?php echo $total_results; ?></strong> account<?php echo $total_results !== 1 ? 's' : ''; ?>
                            <?php if (!empty($search)): ?>
                                matching "<strong><?php echo htmlspecialchars($search); ?></strong>"
                            <?php endif; ?>
                            <?php if (!empty($role_filter)): ?>
                                with role "<strong><?php echo ucfirst($role_filter); ?></strong>"
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Created</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $users_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 42px; height: 42px; font-weight: 600;">
                                                <?php echo strtoupper(substr($user['first_name'] ?? $user['username'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">
                                                    <?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?>
                                                    <?php if ($user['force_password_change']): ?>
                                                        <span class="force-change-badge">
                                                            <i class="bi bi-key-fill"></i> Reset Required
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-muted small">
                                                    <?php echo htmlspecialchars($user['username']); ?> • 
                                                    <?php echo htmlspecialchars($user['email']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'officer' ? 'primary' : 'secondary'); ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td class="text-muted">
                                        <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-action btn-reset" 
                                                onclick="showResetModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')"
                                                title="Reset Password">
                                            <i class="bi bi-key"></i> Reset Password
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-key"></i>
                        <h5><?php echo (!empty($search) || !empty($role_filter)) ? 'No Matching Accounts' : 'No Approved Officer Accounts'; ?></h5>
                        <p>
                            <?php if (!empty($search) || !empty($role_filter)): ?>
                                No accounts match your search criteria.
                                <br><a href="admin_users.php" class="btn btn-outline-primary btn-sm mt-2">Clear Filters</a>
                            <?php else: ?>
                                There are no approved officer accounts other than your own.
                            <?php endif; ?>
                        </p>
                        <?php if (empty($search) && empty($role_filter)): ?>
                            <small class="text-muted">Pending officer registrations are managed in <strong>Manage Officers</strong>.</small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none;">
                <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title"><i class="bi bi-key me-2"></i>Reset User Password</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="user_id" id="resetUserId">
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle me-2"></i>
                            You are about to reset the password for: <strong id="resetUsername"></strong>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Temporary Password</label>
                            <div class="input-group">
                                <input type="text" name="temp_password" id="tempPassword" class="form-control" 
                                       placeholder="Enter temporary password" required minlength="6"
                                       style="border-radius: 10px 0 0 10px;">
                                <button type="button" class="btn btn-outline-secondary" onclick="generateTempPassword()"
                                        style="border-radius: 0 10px 10px 0;">
                                    <i class="bi bi-shuffle"></i> Generate
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="bi bi-shield-check me-1"></i>
                                User will be required to change this password on next login.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: none; padding: 0 24px 24px;">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="reset_password" class="btn btn-warning text-white" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none;">
                            <i class="bi bi-check-lg me-1"></i> Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
        
        function showResetModal(userId, username) {
            document.getElementById('resetUserId').value = userId;
            document.getElementById('resetUsername').textContent = username;
            document.getElementById('tempPassword').value = '';
            resetModal.show();
        }
        
        function generateTempPassword() {
            const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let password = '';
            for (let i = 0; i < 10; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('tempPassword').value = password;
        }
    </script>
</body>
</html>
