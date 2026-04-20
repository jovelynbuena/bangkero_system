<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('location: ../login.php');
    exit;
}

include('../../config/db_connect.php');

// AJAX Handler for officer management actions
if (isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    $action = $_POST['ajax_action'];
    $officer_id = isset($_POST['officer_id']) ? intval($_POST['officer_id']) : 0;
    
    // Validate officer_id only for actions that actually use it
    if (in_array($action, ['approve', 'reject', 'archive', 'demote', 'set_transparency_role'], true) && $officer_id <= 0) {
        $response['message'] = 'Invalid officer ID.';
        echo json_encode($response);
        exit;
    }
    
    try {
        switch($action) {
            case 'set_transparency_role':
                $transRole = strtolower(trim((string)($_POST['transparency_role'] ?? '')));
                $allowed = ['', 'treasurer', 'secretary', 'both'];
                if (!in_array($transRole, $allowed, true)) {
                    $response['message'] = 'Invalid transparency role.';
                    break;
                }

                // Only allow assigning transparency access to APPROVED officer/admin accounts
                $checkStmt = $conn->prepare("SELECT status, role FROM users WHERE id = ? AND role IN ('officer','admin') LIMIT 1");
                $checkStmt->bind_param('i', $officer_id);
                $checkStmt->execute();
                $checkRes = $checkStmt->get_result();
                $checkRow = $checkRes ? $checkRes->fetch_assoc() : null;
                $checkStmt->close();

                if (!$checkRow) {
                    $response['message'] = 'Officer not found.';
                    break;
                }

                $status = strtolower(trim((string)($checkRow['status'] ?? '')));
                if ($status !== 'approved') {
                    $response['message'] = 'Cannot set Transparency access while account is not approved.';
                    break;
                }

                // Ensure column exists
                $col = $conn->query("SHOW COLUMNS FROM users LIKE 'transparency_role'");
                if (!$col || $col->num_rows === 0) {
                    $response['message'] = 'Missing users.transparency_role column. Please run the migration tool first.';
                    break;
                }

                // Enforce uniqueness: only 1 Treasurer and only 1 Secretary (approved accounts)
                if ($transRole !== '') {
                    $confStmt = null;
                    if ($transRole === 'treasurer') {
                        $confStmt = $conn->prepare("SELECT id, username, transparency_role FROM users WHERE id <> ? AND role IN ('officer','admin') AND status='approved' AND transparency_role IN ('treasurer','both') LIMIT 1");
                    } elseif ($transRole === 'secretary') {
                        $confStmt = $conn->prepare("SELECT id, username, transparency_role FROM users WHERE id <> ? AND role IN ('officer','admin') AND status='approved' AND transparency_role IN ('secretary','both') LIMIT 1");
                    } elseif ($transRole === 'both') {
                        $confStmt = $conn->prepare("SELECT id, username, transparency_role FROM users WHERE id <> ? AND role IN ('officer','admin') AND status='approved' AND transparency_role IN ('treasurer','secretary','both') LIMIT 1");
                    }

                    if ($confStmt) {
                        $confStmt->bind_param('i', $officer_id);
                        $confStmt->execute();
                        $confRes = $confStmt->get_result();
                        $confRow = $confRes ? $confRes->fetch_assoc() : null;
                        $confStmt->close();

                        if ($confRow) {
                            $who = (string)($confRow['username'] ?? 'another account');
                            $what = strtolower(trim((string)($confRow['transparency_role'] ?? '')));
                            $response['message'] = "Cannot set this role. Already assigned to {$who} ({$what}). Set the other account to Not set first.";
                            break;
                        }
                    }
                }

                $value = ($transRole === '') ? null : $transRole;
                $stmt = $conn->prepare("UPDATE users SET transparency_role = ? WHERE id = ?");
                $stmt->bind_param('si', $value, $officer_id);
                if ($stmt->execute() && $stmt->affected_rows >= 0) {
                    $response['success'] = true;
                    $response['message'] = 'Transparency role updated.';
                } else {
                    $response['message'] = 'Failed to update transparency role.';
                }
                $stmt->close();
                break;

            case 'approve':
                // Check if officer exists
                $stmt = $conn->prepare("SELECT status FROM users WHERE id=? AND role IN ('officer', 'admin')");
                $stmt->bind_param("i", $officer_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    // Normalize status to lowercase for comparison
                    $currentStatus = strtolower(trim($row['status']));
                    
                    if ($currentStatus === 'approved') {
                        $response['message'] = 'Officer is already approved!';
                    } else {
                        // Update status to approved
                        $updateStmt = $conn->prepare("UPDATE users SET status='approved' WHERE id=? AND role IN ('officer', 'admin')");
                        $updateStmt->bind_param("i", $officer_id);
                        if ($updateStmt->execute() && $updateStmt->affected_rows > 0) {
                            $response['success'] = true;
                            $response['message'] = 'Officer approved successfully!';
                        } else {
                            $response['message'] = 'Failed to approve officer.';
                        }
                        $updateStmt->close();
                    }
                } else {
                    $response['message'] = 'Officer not found!';
                }
                $stmt->close();
                break;
                
            case 'reject':
                $stmt = $conn->prepare("UPDATE users SET status='rejected' WHERE id=? AND role IN ('officer', 'admin')");
                $stmt->bind_param("i", $officer_id);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Officer rejected successfully!';
                } else {
                    $response['message'] = 'Failed to reject officer or officer not found.';
                }
                $stmt->close();
                break;
                
            case 'archive':
                // Prevent archiving the last admin
                $admin_check = $conn->query("SELECT COUNT(*) as admin_count FROM users WHERE role IN ('officer', 'admin') AND is_admin=1");
                $admin_row = $admin_check->fetch_assoc();
                
                $officer_check = $conn->query("SELECT is_admin, status FROM users WHERE id=$officer_id");
                $officer_data = $officer_check->fetch_assoc();

                // Block archiving if account is approved/active
                if ($officer_data && strtolower(trim($officer_data['status'])) === 'approved') {
                    $response['message'] = 'Cannot archive an officer with an active (approved) account. Please deactivate or reject their account first before archiving.';
                    break;
                }
                
                if ($officer_data['is_admin'] == 1 && $admin_row['admin_count'] <= 1) {
                    $response['message'] = 'Cannot archive the last admin account!';
                } else {
                    try {
                        $conn->begin_transaction();
                        
                        // Move to archive (map password_hash into legacy password column for archive)
                        $stmt = $conn->prepare("INSERT INTO users_archive (original_id, username, email, password, role, status, is_admin, created_at) SELECT id, username, email, password_hash, role, status, is_admin, created_at FROM users WHERE id=?");
                        $stmt->bind_param("i", $officer_id);
                        $stmt->execute();
                        $stmt->close();
                        
                        // Delete from main
                        $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role IN ('officer', 'admin')");
                        $stmt->bind_param("i", $officer_id);
                        $stmt->execute();
                        $stmt->close();
                        
                        $conn->commit();
                        $response['success'] = true;
                        $response['message'] = 'Officer archived successfully!';
                    } catch (Exception $e) {
                        $conn->rollback();
                        $response['message'] = 'Error archiving officer: ' . $e->getMessage();
                    }
                }
                break;
                
            case 'demote':
                // Prevent demoting the last admin
                $admin_check = $conn->query("SELECT COUNT(*) as admin_count FROM users WHERE role IN ('officer', 'admin') AND is_admin=1");
                $admin_row = $admin_check->fetch_assoc();
                
                if ($admin_row['admin_count'] <= 1) {
                    $response['message'] = 'Cannot demote the last admin account!';
                } else {
                    $stmt = $conn->prepare("UPDATE users SET is_admin=0, role='officer' WHERE id=?");
                    $stmt->bind_param("i", $officer_id);
                    if ($stmt->execute() && $stmt->affected_rows > 0) {
                        $response['success'] = true;
                        $response['message'] = 'Admin privileges removed successfully!';
                    } else {
                        $response['message'] = 'Failed to demote officer.';
                    }
                    $stmt->close();
                }
                break;
                
            case 'promote':
                $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

                if ($user_id <= 0) {
                    $response['message'] = 'Invalid officer ID.';
                    break;
                }
                
                // Validation: Check if user exists and is approved
                $check = $conn->query("SELECT status, is_admin FROM users WHERE id=$user_id AND role='officer'");
                if ($check && $check->num_rows > 0) {
                    $row = $check->fetch_assoc();
                    
                    if ($row['is_admin'] == 1) {
                        $response['message'] = 'This officer is already an admin!';
                    } elseif ($row['status'] !== 'approved') {
                        $response['message'] = 'Only approved officers can be promoted to admin!';
                    } else {
                        $stmt = $conn->prepare("UPDATE users SET is_admin=1, role='admin' WHERE id=? AND status='approved'");
                        $stmt->bind_param("i", $user_id);
                        if ($stmt->execute() && $stmt->affected_rows > 0) {
                            $response['success'] = true;
                            $response['message'] = 'Officer promoted to admin successfully!';
                        } else {
                            $response['message'] = 'Failed to promote officer.';
                        }
                        $stmt->close();
                    }
                } else {
                    $response['message'] = 'Officer not found!';
                }
                break;
                
            default:
                $response['message'] = 'Invalid action!';
        }
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// Fetch all officers (including those promoted to admin)
$result = $conn->query("SELECT * FROM users WHERE role IN ('officer', 'admin') ORDER BY created_at DESC");

// Check if transparency_role column exists (for UI)
$hasTransparencyRoleCol = false;
try {
    $colRes = $conn->query("SHOW COLUMNS FROM users LIKE 'transparency_role'");
    $hasTransparencyRoleCol = ($colRes && $colRes->num_rows > 0);
} catch (Throwable) {
    $hasTransparencyRoleCol = false;
}

// Enforce single Treasurer / single Secretary assignments (approved accounts only)
$currentTreasurerId = 0;
$currentTreasurerName = '';
$currentSecretaryId = 0;
$currentSecretaryName = '';
if ($hasTransparencyRoleCol) {
    try {
        $res = $conn->query("SELECT id, username, transparency_role FROM users WHERE role IN ('officer','admin') AND status='approved' AND transparency_role IN ('treasurer','secretary','both')");
        while ($r = $res ? $res->fetch_assoc() : null) {
            if (!$r) break;
            $rid = (int)($r['id'] ?? 0);
            $rname = (string)($r['username'] ?? '');
            $rrole = strtolower(trim((string)($r['transparency_role'] ?? '')));

            if (in_array($rrole, ['treasurer','both'], true) && $currentTreasurerId === 0) {
                $currentTreasurerId = $rid;
                $currentTreasurerName = $rname;
            }
            if (in_array($rrole, ['secretary','both'], true) && $currentSecretaryId === 0) {
                $currentSecretaryId = $rid;
                $currentSecretaryName = $rname;
            }
        }
    } catch (Throwable) {
        // Keep defaults
    }
}

// Fetch approved officers for Add Admin dropdown (excluding already admins)
$approved_officers = $conn->query("SELECT id, username FROM users WHERE role='officer' AND status='approved' AND is_admin=0 ORDER BY username ASC");
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<<<<<<< HEAD
    <style>
=======
    <link rel="stylesheet" href="../../css/admin-theme.css">
<style>
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
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
<<<<<<< HEAD
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
=======
            background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
            padding: 24px 32px;
            border-radius: 16px;
            color: white;
            margin-bottom: 24px;
<<<<<<< HEAD
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
=======
            box-shadow: 0 10px 25px rgba(46, 134, 171, 0.20);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
        }
        .page-header h2 {
            font-size: 26px;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .page-header p {
            font-size: 14px;
            margin-top: 4px;
            opacity: 0.9;
        }


        /* Card Styles */
        .card { 
            border: none; 
            border-radius: 16px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); 
            margin-bottom: 24px;
            background: white;
            overflow: visible;
            transition: all 0.3s ease;
        }

        /* Officers table card needs scroll, not clip */
        #officersTable-card {
            overflow: visible;
        }
        
        .card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }
        
        .card-header { 
            background: #f8fafc;
            color: #1e293b; 
            font-weight: 700; 
            font-size: 16px;
            padding: 16px 24px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .card-header i {
            margin-right: 8px;
<<<<<<< HEAD
            color: #667eea;
=======
            color: #2E86AB;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
        }
        
        .card-body { 
            padding: 24px; 
        }


        /* Form Styles */
        .form-select, .form-control { 
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 16px;
            transition: all 0.2s ease;
            font-size: 14px;
            color: #1e293b;
        }
        
        .form-select:focus, .form-control:focus {
<<<<<<< HEAD
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
=======
            border-color: #2E86AB;
            box-shadow: 0 0 0 4px rgba(46, 134, 171, 0.10);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
            outline: none;
        }
        
        .form-label {
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }


        /* Button Styles */
        .btn { 
            font-size: 14px;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary { 
<<<<<<< HEAD
            background: #667eea;
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
=======
            background: #2E86AB;
            color: white;
            box-shadow: 0 4px 12px rgba(46, 134, 171, 0.20);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
        }
        
        .btn-primary:hover { 
            background: #5a6fd6;
            transform: translateY(-1px);
<<<<<<< HEAD
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.3);
=======
            box-shadow: 0 6px 16px rgba(46, 134, 171, 0.30);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
            color: white;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
            color: white;
        }
        
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background: #d97706;
            color: white;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
            color: white;
        }
        
        .btn-secondary { 
            background: #64748b;
            color: white;
        }
        
        .btn-secondary:hover { 
            background: #475569;
            color: white;
        }


        /* Icon-only Action Buttons */
        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s ease;
            padding: 0;
        }
        .btn-icon:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .btn-icon-success  { background: #10b981; color: white; }
        .btn-icon-success:hover  { background: #059669; }
        .btn-icon-warning  { background: #f59e0b; color: white; }
        .btn-icon-warning:hover  { background: #d97706; }
        .btn-icon-secondary { background: #64748b; color: white; }
        .btn-icon-secondary:hover { background: #475569; }
        .btn-icon-archive  { background: #f59e0b; color: white; }
        .btn-icon-archive:hover  { background: #d97706; }

        /* Table Styles */
        .table { 
            font-size: 17px;
            margin: 0;
        }
        
        .table thead th { 
            background: #f8fafc;
            color: #64748b; 
            border-bottom: 2px solid #f1f5f9;
            padding: 14px 16px;
            font-weight: 700;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            border-color: #f1f5f9;
            color: #1e293b;
            font-size: 17px;
        }
        
        .table-hover tbody tr:hover { 
            background-color: #f8faff;
        }
        
        .badge { 
            font-size: 14px; 
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
        }

        .admin-row { 
            background: rgba(102, 126, 234, 0.04) !important;
<<<<<<< HEAD
            border-left: 4px solid #667eea;
=======
            border-left: 4px solid #2E86AB;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
        }

        .action-btn-group {
            display: flex;
            gap: 4px;
            justify-content: center;
        }
        
        .action-btn-group .btn {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 8px;
        }
        
        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 12px 16px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid #f1f5f9;
        }
        
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
<<<<<<< HEAD
            border-color: rgba(102, 126, 234, 0.2);
=======
            border-color: rgba(46, 134, 171, 0.20);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
        }
        
        .stats-card .icon {
            font-size: 22px;
            margin-bottom: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #f8fafc;
        }
        
        .stats-card h3 {
            font-size: 20px;
            font-weight: 800;
            margin: 4px 0 2px 0;
            color: #1e293b;
        }
        
        .stats-card p {
            color: #64748b;
            margin: 0;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Input Group Styling */
        .input-group .input-group-text {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px 0 0 10px;
            padding: 8px 12px;
            background: #f8fafc;
            color: #64748b;
        }
        
        .input-group .form-control {
            border: 1.5px solid #e2e8f0;
            border-radius: 0 10px 10px 0;
        }
        
        .input-group .form-control:focus {
<<<<<<< HEAD
            border-color: #667eea;
=======
            border-color: #2E86AB;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
            box-shadow: none;
        }
        
        .form-select-sm, .form-control-sm {
            font-size: 13px;
            padding: 8px 12px;
            border-radius: 8px;
        }
        
        .form-select-sm:focus {
<<<<<<< HEAD
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
=======
            border-color: #2E86AB;
            box-shadow: 0 0 0 3px rgba(46, 134, 171, 0.10);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
        }
        
        .form-control-sm {
            font-size: 14px;
        }

        /* Pagination Styles */
        .pagination {
            display: flex;
            gap: 6px;
        }
        
        .pagination .page-item .page-link {
            border: 2px solid #e0e0e0;
<<<<<<< HEAD
            color: #667eea;
=======
            color: #2E86AB;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
            font-weight: 600;
            border-radius: 8px;
            padding: 8px 14px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .pagination .page-item .page-link:hover {
<<<<<<< HEAD
            background: #667eea;
            color: white;
            border-color: #667eea;
=======
            background: #2E86AB;
            color: white;
            border-color: #2E86AB;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
            transform: translateY(-2px);
        }
        
        .pagination .page-item.active .page-link {
<<<<<<< HEAD
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
=======
            background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
            border-color: #2E86AB;
            color: white;
            box-shadow: 0 4px 12px rgba(46, 134, 171, 0.40);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
        }
        
        .pagination .page-item.disabled .page-link {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f8f9fa;
        }
        
        .card-footer {
            padding: 20px 24px;
            border-top: 2px solid #f0f0f0;
        }

        @media (max-width: 991.98px) { 
            .main-content { 
                margin-left: 0; 
                padding: 20px; 
            }
            
            .page-header h2 {
                font-size: 24px;
            }
            
            .stats-card h3 {
                font-size: 28px;
            }
        }

        /* Responsive table fixes for smaller screens */
        .table-responsive-wrapper {
            width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            display: block;
        }

        @media (max-width: 1400px) {
            #officersTable th,
            #officersTable td {
                font-size: 15px;
                padding: 10px 8px;
                white-space: nowrap;
            }

            .transRoleSelect {
                min-width: 110px !important;
                font-size: 13px !important;
            }

            .action-btn-group {
                display: flex;
                flex-wrap: nowrap;
                gap: 3px;
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
            <h2><i class="bi bi-people-fill"></i> Manage Officers</h2>
            <p class="mb-0">Manage officer accounts, permissions, and administrative access</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-2 mb-2">
        <?php
        $total_officers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role IN ('officer', 'admin')")->fetch_assoc()['count'];
        $pending_officers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role IN ('officer', 'admin') AND status='pending'")->fetch_assoc()['count'];
        $approved_officers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role IN ('officer', 'admin') AND status='approved'")->fetch_assoc()['count'];
        $total_admins = $conn->query("SELECT COUNT(*) as count FROM users WHERE role IN ('officer', 'admin') AND is_admin=1")->fetch_assoc()['count'];
        ?>
        <div class="col-xl-3 col-md-6">
<<<<<<< HEAD
            <div class="stats-card" style="border-top: 3px solid #667eea;">
                <div class="icon" style="color: #667eea; background: rgba(102, 126, 234, 0.1);">
=======
            <div class="stats-card" style="border-top: 3px solid #2E86AB;">
                <div class="icon" style="color: #2E86AB; background: rgba(46, 134, 171, 0.10);">
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                    <i class="bi bi-people"></i>
                </div>
                <h3><?= $total_officers ?></h3>
                <p>Total Officers</p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card" style="border-top: 3px solid #ef4444;">
                <div class="icon" style="color: #ef4444; background: rgba(239, 68, 68, 0.1);">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h3><?= $pending_officers ?></h3>
                <p>Pending Approval</p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card" style="border-top: 3px solid #10b981;">
                <div class="icon" style="color: #10b981; background: rgba(16, 185, 129, 0.1);">
                    <i class="bi bi-check-circle"></i>
                </div>
                <h3><?= $approved_officers ?></h3>
                <p>Approved Officers</p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
<<<<<<< HEAD
            <div class="stats-card" style="border-top: 3px solid #764ba2;">
                <div class="icon" style="color: #764ba2; background: rgba(118, 75, 162, 0.1);">
=======
            <div class="stats-card" style="border-top: 3px solid #1B4F72;">
                <div class="icon" style="color: #1B4F72; background: rgba(118, 75, 162, 0.1);">
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3><?= $total_admins ?></h3>
                <p>Admin Officers</p>
            </div>
        </div>
    </div>

    <!-- Promote Officer to Admin -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-shield-lock"></i> Promote Officer to Admin
        </div>
        <div class="card-body">
            <form id="promoteForm" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Select Officer</label>
                    <select name="user_id" id="officer_select" class="form-select" required>
                        <option value="">-- Choose an approved officer --</option>
                        <?php 
                        $approved_officers_reset = $conn->query("SELECT id, username FROM users WHERE role='officer' AND status='approved' AND is_admin=0 ORDER BY username ASC");
                        while($row = $approved_officers_reset->fetch_assoc()): 
                        ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['username']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-up-circle me-1"></i>Promote to Admin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Search and Filter - Single Line -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <!-- Left side: Entries selector -->
                <div class="d-flex align-items-center gap-2">
                    <label class="mb-0 text-muted fw-semibold" style="font-size: 14px; white-space: nowrap;">Show</label>
                    <select id="entriesPerPage" class="form-select form-select-sm" style="width: 70px;">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <label class="mb-0 text-muted fw-semibold" style="font-size: 14px;">entries</label>
                </div>
                
                <!-- Right side: Filters and search in one line -->
                <div class="d-flex gap-2 align-items-center">
                    <select id="roleFilter" class="form-select form-select-sm" style="width: 130px;">
                        <option value="">All Roles</option>
                        <option value="officer">Officers</option>
                        <option value="admin">Admins</option>
                    </select>
                    <select id="statusFilter" class="form-select form-select-sm" style="width: 140px;">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <div class="input-group" style="width: 280px;">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control form-control-sm border-start-0 ps-0" placeholder="Search officers...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Officers Table -->
    <div class="card">
        <div class="card-body">
        <div class="table-responsive-wrapper">
            <table class="table table-bordered table-hover align-middle text-center" id="officersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Transparency</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="<?= ($row['is_admin']==1) ? 'admin-row' : '' ?>" data-officer-id="<?= $row['id'] ?>" data-status="<?= $row['status'] ?>" data-role="<?= ($row['is_admin']==1) ? 'admin' : 'officer' ?>">
                                <td style="color: #000; font-weight: 600;"><?= $row['id'] ?></td>
                                <td style="color: #000; font-weight: 600;"><?= htmlspecialchars($row['username']) ?></td>
                                <td style="color: #000;"><?= htmlspecialchars($row['email']) ?></td>
                                <td>
                                    <?php
                                        switch($row['status']){
                                            case 'pending': echo "<span class='badge bg-warning text-dark'><i class='bi bi-clock me-1'></i>Pending</span>"; break;
                                            case 'approved': echo "<span class='badge bg-success'><i class='bi bi-check-circle me-1'></i>Approved</span>"; break;
                                            case 'rejected': echo "<span class='badge bg-danger'><i class='bi bi-x-circle me-1'></i>Rejected</span>"; break;
                                            default: echo "<span class='badge bg-secondary'>Unknown</span>";
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php if($row['is_admin']==1): ?>
                                        <span class='badge bg-info text-dark'><i class='bi bi-shield-fill-check me-1'></i>Admin</span>
                                    <?php else: ?>
                                        <span class='badge bg-secondary'><i class='bi bi-person me-1'></i>Officer</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color: #000;"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <?php if ($hasTransparencyRoleCol): ?>
                                        <?php
                                            $tr = strtolower(trim((string)($row['transparency_role'] ?? '')));
                                            $isApproved = (strtolower(trim((string)($row['status'] ?? ''))) === 'approved');
                                        ?>
                                        <?php
                                            $rowId = (int)($row['id'] ?? 0);
                                            $treasurerTakenByOther = ($currentTreasurerId > 0 && $currentTreasurerId !== $rowId);
                                            $secretaryTakenByOther = ($currentSecretaryId > 0 && $currentSecretaryId !== $rowId);
                                            $isRowHoldingTreasurer = in_array($tr, ['treasurer','both'], true);
                                            $isRowHoldingSecretary = in_array($tr, ['secretary','both'], true);

                                            $disableTreasurerOpt = ($treasurerTakenByOther && !$isRowHoldingTreasurer);
                                            $disableSecretaryOpt = ($secretaryTakenByOther && !$isRowHoldingSecretary);
                                            $disableBothOpt = (($treasurerTakenByOther && !$isRowHoldingTreasurer) || ($secretaryTakenByOther && !$isRowHoldingSecretary));
                                        ?>
                                        <select class="form-select form-select-sm transRoleSelect" style="min-width: 140px;" data-id="<?= $row['id'] ?>" <?= $isApproved ? '' : 'disabled' ?>>
                                            <option value="" <?= $tr === '' ? 'selected' : '' ?>>Not set</option>
                                            <option value="treasurer" <?= $tr === 'treasurer' ? 'selected' : '' ?> <?= $disableTreasurerOpt ? 'disabled' : '' ?>>Treasurer</option>
                                            <option value="secretary" <?= $tr === 'secretary' ? 'selected' : '' ?> <?= $disableSecretaryOpt ? 'disabled' : '' ?>>Secretary</option>
                                            <option value="both" <?= $tr === 'both' ? 'selected' : '' ?> <?= $disableBothOpt ? 'disabled' : '' ?>>Both</option>
                                        </select>
                                        <?php if ($isApproved): ?>
                                            <small class="text-muted d-block" style="font-size: 11px;">Controls Transparency access</small>
                                        <?php else: ?>
                                            <small class="text-danger d-block" style="font-size: 11px;">Approve account first</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Run migration</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btn-group">
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <button class="btn-icon btn-icon-success actionBtn" data-action="approve" data-id="<?= $row['id'] ?>" data-bs-toggle="tooltip" title="Approve Officer">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button class="btn-icon btn-icon-warning actionBtn" data-action="reject" data-id="<?= $row['id'] ?>" data-bs-toggle="tooltip" title="Reject Officer">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        <?php elseif ($row['status'] == 'approved'): ?>
                                            <button class="btn-icon btn-icon-warning actionBtn" data-action="reject" data-id="<?= $row['id'] ?>" data-bs-toggle="tooltip" title="Reject Officer">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        <?php elseif ($row['status'] == 'rejected'): ?>
                                            <button class="btn-icon btn-icon-success actionBtn" data-action="approve" data-id="<?= $row['id'] ?>" data-bs-toggle="tooltip" title="Approve Officer">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php if($row['is_admin']==1): ?>
                                            <button class="btn-icon btn-icon-secondary actionBtn" data-action="demote" data-id="<?= $row['id'] ?>" data-bs-toggle="tooltip" title="Demote from Admin">
                                                <i class="bi bi-arrow-down-circle"></i>
                                            </button>
                                        <?php endif; ?>

                                        <button class="btn-icon btn-icon-archive actionBtn" data-action="archive" data-id="<?= $row['id'] ?>" data-status="<?= htmlspecialchars($row['status']) ?>" data-bs-toggle="tooltip" title="Archive Officer">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No officers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div><!-- end table-responsive-wrapper -->
        </div><!-- end card-body -->
        
        <!-- Pagination Footer -->
        <div class="card-footer bg-white border-0 pt-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="text-muted" style="font-size: 14px;">
                    Showing <strong id="showingStart">1</strong> to <strong id="showingEnd">10</strong> of <strong id="totalEntries">0</strong> entries
                </div>
                <nav>
                    <ul class="pagination mb-0" id="pagination">
                        <!-- Pagination will be generated by JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Initialize Bootstrap Tooltips
document.addEventListener('DOMContentLoaded', function () {
    const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipEls.forEach(el => new bootstrap.Tooltip(el, { trigger: 'hover' }));
});
// Promote Officer to Admin Form Handler
document.getElementById('promoteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const userId = document.getElementById('officer_select').value;
    const userName = document.getElementById('officer_select').options[document.getElementById('officer_select').selectedIndex].text;
    
    if (!userId) {
        Swal.fire({
            icon: 'warning',
            title: 'No Officer Selected',
            text: 'Please select an officer to promote.',
<<<<<<< HEAD
            confirmButtonColor: '#667eea'
=======
            confirmButtonColor: '#2E86AB'
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
        });
        return;
    }
    
    Swal.fire({
        title: 'Promote to Admin?',
        html: `Are you sure you want to promote <strong>${userName}</strong> to Admin?<br><small class="text-muted">This will grant full administrative privileges.</small>`,
        icon: 'question',
        showCancelButton: true,
<<<<<<< HEAD
        confirmButtonColor: '#667eea',
=======
        confirmButtonColor: '#2E86AB',
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-arrow-up-circle me-1"></i>Yes, Promote',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Promoting...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Send AJAX request
            const formData = new FormData();
            formData.append('ajax_action', 'promote');
            formData.append('user_id', userId);
            
            fetch('manage_officer.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
<<<<<<< HEAD
                        confirmButtonColor: '#667eea'
=======
                        confirmButtonColor: '#2E86AB'
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: data.message,
<<<<<<< HEAD
                        confirmButtonColor: '#667eea'
=======
                        confirmButtonColor: '#2E86AB'
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
<<<<<<< HEAD
                    confirmButtonColor: '#667eea'
=======
                    confirmButtonColor: '#2E86AB'
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                });
                console.error('Error:', error);
            });
        }
    });
});

// Action buttons handler (Approve, Reject, Delete, Demote)
document.querySelectorAll('.actionBtn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        
        const action = this.dataset.action;
        const id = this.dataset.id;
        const status = this.dataset.status || '';
        const row = this.closest('tr');
        const username = row.querySelector('td:nth-child(2)').textContent;

        // Block archive if account is approved/active
        if (action === 'archive' && status.toLowerCase() === 'approved') {
            Swal.fire({
                icon: 'error',
                title: 'Cannot Archive Active Account',
                html: `<strong>${username.trim()}</strong> has an <span class="text-success fw-bold">approved/active</span> account.<br><br>Please <strong>reject</strong> their account first before archiving.`,
<<<<<<< HEAD
                confirmButtonColor: '#667eea',
=======
                confirmButtonColor: '#2E86AB',
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                confirmButtonText: 'Understood'
            });
            return;
        }
        
        let title = '';
        let text = '';
        let html = '';
        let icon = 'warning';
        let confirmButton = 'Yes';
<<<<<<< HEAD
        let confirmColor = '#667eea';
=======
        let confirmColor = '#2E86AB';
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560

        switch(action) {
            case 'approve':
                title = 'Approve Officer?';
                text = `Approve ${username} as an officer?`;
                icon = 'question';
                confirmButton = '<i class="bi bi-check-lg me-1"></i>Approve';
                confirmColor = '#11998e';
                break;
            case 'reject':
                title = 'Reject Officer?';
                text = `Reject ${username}'s officer status?`;
                confirmButton = '<i class="bi bi-x-lg me-1"></i>Reject';
                confirmColor = '#f5576c';
                break;
            case 'archive':
                title = 'Archive Officer?';
                html = `Are you sure you want to archive <strong>${username}</strong>?<br><small class="text-muted">This will move the account to the archive list.</small>`;
                confirmButton = '<i class="bi bi-archive me-1"></i>Archive';
                confirmColor = '#f59e0b';
                break;
            case 'demote':
                title = 'Remove Admin Privileges?';
                text = `Demote ${username} back to officer role?`;
                confirmButton = '<i class="bi bi-arrow-down-circle me-1"></i>Demote';
                confirmColor = '#6c757d';
                break;
        }

        Swal.fire({
            title: title,
            text: action === 'delete' ? '' : text,
            html: action === 'delete' ? html : undefined,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmButton,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Send AJAX request
                const formData = new FormData();
                formData.append('ajax_action', action);
                formData.append('officer_id', id);
                
                fetch('manage_officer.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
<<<<<<< HEAD
                            confirmButtonColor: '#667eea',
=======
                            confirmButtonColor: '#2E86AB',
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: data.message,
<<<<<<< HEAD
                            confirmButtonColor: '#667eea'
=======
                            confirmButtonColor: '#2E86AB'
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred. Please try again.',
<<<<<<< HEAD
                        confirmButtonColor: '#667eea'
=======
                        confirmButtonColor: '#2E86AB'
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                    });
                    console.error('Error:', error);
                });
            }
        });
    });
});

// Transparency role change handler (confirm + revert on cancel/fail)
document.querySelectorAll('.transRoleSelect').forEach(sel => {
    // Track current value so we can revert when user cancels
    sel.dataset.prev = sel.value;

    sel.addEventListener('change', function() {
        const id = this.dataset.id;
        const newValue = this.value;
        const oldValue = this.dataset.prev ?? '';

        const row = this.closest('tr');
        const status = (row?.dataset?.status || '').toLowerCase();
        const username = row ? (row.querySelector('td:nth-child(2)')?.textContent || 'this user') : 'this user';

        // Extra client-side guard: don’t allow changes for non-approved
        if (status && status !== 'approved') {
            Swal.fire({
                icon: 'info',
                title: 'Not allowed yet',
                text: 'Approve the account first before setting Transparency access.',
<<<<<<< HEAD
                confirmButtonColor: '#667eea'
=======
                confirmButtonColor: '#2E86AB'
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
            });
            this.value = oldValue;
            return;
        }

        const labelMap = {
            '': 'Not set',
            'treasurer': 'Treasurer',
            'secretary': 'Secretary',
            'both': 'Both'
        };

        Swal.fire({
            title: 'Apply Transparency Access?',
            html: `Set <strong>${username}</strong> to <strong>${labelMap[newValue] ?? newValue}</strong>?`,
            icon: 'question',
            showCancelButton: true,
<<<<<<< HEAD
            confirmButtonColor: '#667eea',
=======
            confirmButtonColor: '#2E86AB',
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, apply',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (!result.isConfirmed) {
                this.value = oldValue;
                return;
            }

            const formData = new FormData();
            formData.append('ajax_action', 'set_transparency_role');
            formData.append('officer_id', id);
            formData.append('transparency_role', newValue);

            fetch('manage_officer.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.dataset.prev = newValue;
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                } else {
                    this.value = oldValue;
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: data.message,
<<<<<<< HEAD
                        confirmButtonColor: '#667eea'
=======
                        confirmButtonColor: '#2E86AB'
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                    });
                }
            })
            .catch(() => {
                this.value = oldValue;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
<<<<<<< HEAD
                    confirmButtonColor: '#667eea'
=======
                    confirmButtonColor: '#2E86AB'
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                });
            });
        });
    });
});

// Live Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    filterAndPaginate();
});

// Status Filter functionality
document.getElementById('statusFilter').addEventListener('change', function() {
    filterAndPaginate();
});

// Role Filter functionality
document.getElementById('roleFilter').addEventListener('change', function() {
    filterAndPaginate();
});

// Entries per page functionality
document.getElementById('entriesPerPage').addEventListener('change', function() {
    currentPage = 1;
    filterAndPaginate();
});

// ========================================
// PAGINATION & FILTERING SYSTEM
// ========================================
let currentPage = 1;
let entriesPerPage = 10;

function filterAndPaginate() {
    const searchFilter = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
    const roleFilter = document.getElementById('roleFilter').value.toLowerCase();
    entriesPerPage = parseInt(document.getElementById('entriesPerPage').value);
    
    const rows = Array.from(document.querySelectorAll('#officersTable tbody tr'));
    let filteredRows = [];
    
    rows.forEach(row => {
        if (row.cells.length < 8) return; // Skip empty state row
        
        const text = row.textContent.toLowerCase();
        const status = row.dataset.status ? row.dataset.status.toLowerCase() : '';
        const role = row.dataset.role ? row.dataset.role.toLowerCase() : '';
        
        const matchesSearch = text.includes(searchFilter);
        const matchesStatus = !statusFilter || status === statusFilter;
        const matchesRole = !roleFilter || role === roleFilter;
        
        if (matchesSearch && matchesStatus && matchesRole) {
            filteredRows.push(row);
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update pagination info
    const totalEntries = filteredRows.length;
    document.getElementById('totalEntries').textContent = totalEntries;
    
    if (totalEntries === 0) {
        document.getElementById('showingStart').textContent = '0';
        document.getElementById('showingEnd').textContent = '0';
        renderPagination(0);
        return;
    }
    
    // Calculate pagination
    const totalPages = Math.ceil(totalEntries / entriesPerPage);
    if (currentPage > totalPages) currentPage = totalPages;
    
    const startIndex = (currentPage - 1) * entriesPerPage;
    const endIndex = Math.min(startIndex + entriesPerPage, totalEntries);
    
    document.getElementById('showingStart').textContent = startIndex + 1;
    document.getElementById('showingEnd').textContent = endIndex;
    
    // Show only current page rows
    filteredRows.forEach((row, index) => {
        if (index >= startIndex && index < endIndex) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    renderPagination(totalPages);
}

function renderPagination(totalPages) {
    const paginationEl = document.getElementById('pagination');
    paginationEl.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#"><i class="bi bi-chevron-left"></i></a>`;
    if (currentPage > 1) {
        prevLi.addEventListener('click', (e) => {
            e.preventDefault();
            currentPage--;
            filterAndPaginate();
        });
    }
    paginationEl.appendChild(prevLi);
    
    // Page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    // First page
    if (startPage > 1) {
        const firstLi = document.createElement('li');
        firstLi.className = 'page-item';
        firstLi.innerHTML = `<a class="page-link" href="#">1</a>`;
        firstLi.addEventListener('click', (e) => {
            e.preventDefault();
            currentPage = 1;
            filterAndPaginate();
        });
        paginationEl.appendChild(firstLi);
        
        if (startPage > 2) {
            const dotsLi = document.createElement('li');
            dotsLi.className = 'page-item disabled';
            dotsLi.innerHTML = `<a class="page-link" href="#">...</a>`;
            paginationEl.appendChild(dotsLi);
        }
    }
    
    // Visible page numbers
    for (let i = startPage; i <= endPage; i++) {
        const pageLi = document.createElement('li');
        pageLi.className = `page-item ${i === currentPage ? 'active' : ''}`;
        pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`;
        if (i !== currentPage) {
            pageLi.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage = i;
                filterAndPaginate();
            });
        }
        paginationEl.appendChild(pageLi);
    }
    
    // Last page
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const dotsLi = document.createElement('li');
            dotsLi.className = 'page-item disabled';
            dotsLi.innerHTML = `<a class="page-link" href="#">...</a>`;
            paginationEl.appendChild(dotsLi);
        }
        
        const lastLi = document.createElement('li');
        lastLi.className = 'page-item';
        lastLi.innerHTML = `<a class="page-link" href="#">${totalPages}</a>`;
        lastLi.addEventListener('click', (e) => {
            e.preventDefault();
            currentPage = totalPages;
            filterAndPaginate();
        });
        paginationEl.appendChild(lastLi);
    }
    
    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#"><i class="bi bi-chevron-right"></i></a>`;
    if (currentPage < totalPages) {
        nextLi.addEventListener('click', (e) => {
            e.preventDefault();
            currentPage++;
            filterAndPaginate();
        });
    }
    paginationEl.appendChild(nextLi);
}

// Initialize pagination on page load
document.addEventListener('DOMContentLoaded', function() {
    filterAndPaginate();
});

// Initialize pagination on page load
document.addEventListener('DOMContentLoaded', function() {
    filterAndPaginate();
});

</script>
</body>
</html>
