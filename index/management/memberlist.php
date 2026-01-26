<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$alertType = $alertMsg = "";
$memberName = $_SESSION['member_name'] ?? 'Admin';

/* --------------------------
   ✅ ADD MEMBER HANDLER (with restrictions)
-------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_initial = trim($_POST['middle_initial'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $name = trim("$first_name $middle_initial $last_name");
    
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $work_type = $_POST['work_type'] ?? '';
    $license_number = trim($_POST['license_number'] ?? '');
    $boat_name = trim($_POST['boat_name'] ?? '');
    $fishing_area = trim($_POST['fishing_area'] ?? '');
    $emergency_name = trim($_POST['emergency_name'] ?? '');
    $emergency_phone = trim($_POST['emergency_phone'] ?? '');
    $agreement = isset($_POST['agreement']) ? 1 : 0;

    // ✅ RESTRICTION 1: Validate required fields
    if (empty($first_name) || empty($last_name) || empty($dob) || empty($phone) || empty($email) || empty($address)) {
        $alertType = "error";
        $alertMsg = "Please fill in all required fields!";
    }
    // ✅ RESTRICTION 2: Validate age (must be at least 18 years old)
    elseif (!empty($dob)) {
        $dobDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($dobDate)->y;
        if ($age < 18) {
            $alertType = "error";
            $alertMsg = "Member must be at least 18 years old!";
        }
    }
    
    if (empty($alertMsg)) {
        // ✅ RESTRICTION 3: Check for duplicate email
        $checkEmail = $conn->prepare("SELECT id FROM members WHERE LOWER(email) = LOWER(?)");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();
        
        if ($checkEmail->num_rows > 0) {
            $alertType = "error";
            $alertMsg = "Email already exists! Please use a different email address.";
        }
        $checkEmail->close();

        // ✅ RESTRICTION 4: Check for duplicate phone number
        if (empty($alertMsg)) {
            $checkPhone = $conn->prepare("SELECT id FROM members WHERE phone = ?");
            $checkPhone->bind_param("s", $phone);
            $checkPhone->execute();
            $checkPhone->store_result();
            
            if ($checkPhone->num_rows > 0) {
                $alertType = "error";
                $alertMsg = "Phone number already exists! Please use a different phone number.";
            }
            $checkPhone->close();
        }
    }

    // Handle image upload
    $image_name = 'default_member.png';
    if (empty($alertMsg) && !empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imgDir = '../../uploads/members/';
        if (!is_dir($imgDir)) mkdir($imgDir, 0777, true);

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];

        if (!in_array($file_type, $allowed_types)) {
            $alertType = "error";
            $alertMsg = "Invalid image format! Only JPG, PNG, and GIF are allowed.";
        } elseif ($file_size > 2 * 1024 * 1024) {
            $alertType = "error";
            $alertMsg = "Image size must be less than 2MB!";
        } else {
            $image_name = uniqid('member_', true) . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '', basename($_FILES['image']['name']));
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $imgDir . $image_name)) {
                $alertType = "error";
                $alertMsg = "Error uploading image. Please try again.";
            }
        }
    }

    // Insert into database
    if (empty($alertMsg)) {
        $stmt = $conn->prepare("
            INSERT INTO members (name, dob, gender, phone, email, address, work_type, license_number, boat_name, fishing_area, emergency_name, emergency_phone, agreement, image) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssssssssssis", $name, $dob, $gender, $phone, $email, $address, $work_type, $license_number, $boat_name, $fishing_area, $emergency_name, $emergency_phone, $agreement, $image_name);

        if ($stmt->execute()) {
            $alertType = "success";
            $alertMsg = "Member added successfully!";
        } else {
            $alertType = "error";
            $alertMsg = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
}

/* --------------------------
   ✏️ EDIT MEMBER HANDLER (with restrictions)
-------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_member'])) {
    $member_id = intval($_POST['member_id'] ?? 0);
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_initial = trim($_POST['middle_initial'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $name = trim("$first_name $middle_initial $last_name");
    
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $work_type = $_POST['work_type'] ?? '';
    $license_number = trim($_POST['license_number'] ?? '');
    $boat_name = trim($_POST['boat_name'] ?? '');
    $fishing_area = trim($_POST['fishing_area'] ?? '');
    $emergency_name = trim($_POST['emergency_name'] ?? '');
    $emergency_phone = trim($_POST['emergency_phone'] ?? '');
    $agreement = isset($_POST['agreement']) ? 1 : 0;

    // ✅ RESTRICTION 1: Validate required fields
    if (!$member_id || empty($first_name) || empty($last_name) || empty($dob) || empty($phone) || empty($email) || empty($address)) {
        $alertType = "error";
        $alertMsg = "Please fill in all required fields!";
    }
    // ✅ RESTRICTION 2: Validate age
    elseif (!empty($dob)) {
        $dobDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($dobDate)->y;
        if ($age < 18) {
            $alertType = "error";
            $alertMsg = "Member must be at least 18 years old!";
        }
    }

    if (empty($alertMsg)) {
        // Get current member data
        $getCurrent = $conn->prepare("SELECT image, email, phone FROM members WHERE id = ?");
        $getCurrent->bind_param("i", $member_id);
        $getCurrent->execute();
        $getCurrent->bind_result($current_image, $current_email, $current_phone);
        $getCurrent->fetch();
        $getCurrent->close();

        // ✅ RESTRICTION 3: Check for duplicate email (excluding current member)
        if (strtolower($email) !== strtolower($current_email ?? '')) {
            $checkEmail = $conn->prepare("SELECT id FROM members WHERE LOWER(email) = LOWER(?) AND id != ?");
            $checkEmail->bind_param("si", $email, $member_id);
            $checkEmail->execute();
            $checkEmail->store_result();
            
            if ($checkEmail->num_rows > 0) {
                $alertType = "error";
                $alertMsg = "Email already exists! Please use a different email address.";
            }
            $checkEmail->close();
        }

        // ✅ RESTRICTION 4: Check for duplicate phone (excluding current member)
        if (empty($alertMsg) && $phone !== $current_phone) {
            $checkPhone = $conn->prepare("SELECT id FROM members WHERE phone = ? AND id != ?");
            $checkPhone->bind_param("si", $phone, $member_id);
            $checkPhone->execute();
            $checkPhone->store_result();
            
            if ($checkPhone->num_rows > 0) {
                $alertType = "error";
                $alertMsg = "Phone number already exists! Please use a different phone number.";
            }
            $checkPhone->close();
        }

        $new_image = $current_image;

        // Handle image upload
        if (empty($alertMsg) && !empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imgDir = '../../uploads/members/';
            if (!is_dir($imgDir)) mkdir($imgDir, 0777, true);

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image']['type'];
            $file_size = $_FILES['image']['size'];

            if (!in_array($file_type, $allowed_types)) {
                $alertType = "error";
                $alertMsg = "Invalid image format! Only JPG, PNG, and GIF are allowed.";
            } elseif ($file_size > 2 * 1024 * 1024) {
                $alertType = "error";
                $alertMsg = "Image size must be less than 2MB!";
            } else {
                $new_image = uniqid('member_', true) . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '', basename($_FILES['image']['name']));
                if (move_uploaded_file($_FILES['image']['tmp_name'], $imgDir . $new_image)) {
                    // Delete old image
                    if ($current_image && $current_image !== 'default_member.png' && file_exists($imgDir . $current_image)) {
                        @unlink($imgDir . $current_image);
                    }
                } else {
                    $alertType = "error";
                    $alertMsg = "Error uploading image. Please try again.";
                    $new_image = $current_image;
                }
            }
        }

        // Update database
        if (empty($alertMsg)) {
            $stmt = $conn->prepare("
                UPDATE members SET 
                    name=?, dob=?, gender=?, phone=?, email=?, address=?, 
                    work_type=?, license_number=?, boat_name=?, fishing_area=?, 
                    emergency_name=?, emergency_phone=?, agreement=?, image=?
                WHERE id=?
            ");
            $stmt->bind_param("ssssssssssssisi", $name, $dob, $gender, $phone, $email, $address, $work_type, $license_number, $boat_name, $fishing_area, $emergency_name, $emergency_phone, $agreement, $new_image, $member_id);

            if ($stmt->execute()) {
                $alertType = "success";
                $alertMsg = "Member updated successfully!";
            } else {
                $alertType = "error";
                $alertMsg = "Database error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

/* --------------------------
   🗂 ARCHIVE HANDLER
-------------------------- */
if (isset($_GET['archive'])) {
    $id = intval($_GET['archive']);
    if ($id > 0) {
        // First check if member exists
        $checkStmt = $conn->prepare("SELECT id FROM members WHERE id = ?");
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $checkStmt->close();
            
            // Check if member_archive table exists and has the right structure
            $tableCheck = $conn->query("SHOW TABLES LIKE 'member_archive'");
            
            if ($tableCheck && $tableCheck->num_rows > 0) {
                // Archive exists, insert data
                try {
                    $stmt = $conn->prepare("
                        INSERT INTO member_archive (member_id, name, email, phone, archived_at)
                        SELECT id, name, email, phone, NOW()
                        FROM members
                        WHERE id = ?
                    ");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $stmt->close();
                } catch (Exception $e) {
                    // If insert fails (e.g., duplicate), just continue to delete
                }
            }
            
            // Delete from members table
            $deleteStmt = $conn->prepare("DELETE FROM members WHERE id = ?");
            $deleteStmt->bind_param("i", $id);
            
            if ($deleteStmt->execute()) {
                $deleteStmt->close();
                header("Location: memberlist.php?archived=1");
                exit();
            } else {
                $deleteStmt->close();
                header("Location: memberlist.php?error=1");
                exit();
            }
        } else {
            $checkStmt->close();
            header("Location: memberlist.php?error=1");
            exit();
        }
    }
}

/* --------------------------
   📋 FETCH MEMBERS
-------------------------- */
$sql = "SELECT * FROM members ORDER BY name ASC";
$result = $conn->query($sql);
$members_count = $result ? $result->num_rows : 0;
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Member List | Bangkero & Fishermen Association</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

    /* Header Section */
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
    .page-header .badge {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
    }

    /* Action Bar */
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    /* Add Member Button */
    .btn-add-member {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 14px 28px;
        border-radius: 12px;
        border: none;
        font-weight: 600;
        font-size: 16px;
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .btn-add-member:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    }

    /* Export Buttons */
    .btn-export {
        padding: 10px 20px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-csv {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    .btn-csv:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    }
    .btn-pdf {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    .btn-pdf:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
    }

    /* Search Box */
    .search-box {
        position: relative;
        width: 300px;
    }
    .search-box input {
        width: 100%;
        padding: 12px 16px 12px 45px;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    .search-box input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    .search-box i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 18px;
    }

    /* Table Container */
    .table-container {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    .table {
        margin-bottom: 0;
    }
    .table thead th {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
        padding: 16px;
        border: none;
        vertical-align: middle;
    }
    .table tbody td {
        padding: 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }
    .table tbody tr {
        transition: all 0.3s ease;
    }
    .table tbody tr:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        transform: translateX(4px);
    }

    /* Action Buttons */
    .btn-sm {
        padding: 8px 12px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        margin-right: 6px;
    }
    .btn-view {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }
    .btn-view:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        color: white;
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
        font-size: 24px;
    }
    .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }
    .modal-body {
        padding: 32px;
        max-height: 70vh;
        overflow-y: auto;
    }
    .modal-footer {
        padding: 24px;
        border-top: 1px solid #f0f0f0;
    }

    /* Form Controls */
    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 14px;
    }
    .form-label .text-danger {
        color: #ef4444;
    }
    .form-select, .form-control {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 12px 16px;
        transition: all 0.3s ease;
        font-size: 14px;
    }
    .form-select:focus, .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }

    /* Submit Button */
    .btn-submit {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 12px 32px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        font-size: 16px;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        transition: all 0.3s ease;
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.5);
    }

    /* Image Preview */
    .img-preview {
        width: 120px;
        height: 120px;
        border-radius: 12px;
        object-fit: cover;
        margin-top: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #94a3b8;
    }
    .empty-state i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    @media (max-width: 991.98px) { 
        .main-content { 
            margin-left: 0; 
            padding: 16px; 
        }
        .page-header {
            padding: 24px;
        }
        .page-header h2 {
            font-size: 24px;
            flex-direction: column;
            align-items: flex-start;
        }
        .action-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .search-box {
            width: 100%;
        }
    }
</style>
</head>
<body>
<?php include('../navbar.php'); ?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2>
                    <i class="bi bi-people-fill"></i>
                    Members Management
                    <span class="badge"><?php echo $members_count; ?> Members</span>
                </h2>
                <p class="mb-0 mt-2" style="opacity: 0.9;">Manage association members and their information</p>
            </div>
            <button class="btn-add-member" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                <i class="bi bi-person-plus-fill"></i>
                Add New Member
            </button>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
        <div class="d-flex gap-2">
            <a href="export_members_csv.php" class="btn-export btn-csv" target="_blank">
                <i class="bi bi-filetype-csv"></i>
                Export CSV
            </a>
            <a href="export_members_print.php" class="btn-export btn-pdf" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i>
                Export PDF
            </a>
        </div>
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" placeholder="Search members..." autocomplete="off">
        </div>
    </div>

    <!-- Members Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;" class="text-center">#</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th class="text-center" style="width: 250px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="membersTableBody">
                <?php if ($result && $result->num_rows > 0): $count = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): 
                        $nameParts = explode(' ', $row['name']);
                        $first_name = $nameParts[0] ?? '';
                        $middle_initial = $nameParts[1] ?? '';
                        $last_name = $nameParts[2] ?? '';
                    ?>
                        <tr>
                            <td class="text-center"><strong><?= $count++ ?></strong></td>
                            <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                            <td>
                                <div><i class="bi bi-telephone-fill text-muted me-2"></i><?= htmlspecialchars($row['phone']) ?></div>
                                <div class="mt-1"><i class="bi bi-envelope-fill text-muted me-2"></i><?= htmlspecialchars($row['email']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($row['address']) ?></td>
                            <td class="text-center">
                                <a href="../view_member_info.php?id=<?= $row['id'] ?>" class="btn btn-view btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button 
                                    class="btn btn-edit btn-sm editBtn"
                                    data-id="<?= $row['id'] ?>"
                                    data-firstname="<?= htmlspecialchars($first_name) ?>"
                                    data-middleinitial="<?= htmlspecialchars($middle_initial) ?>"
                                    data-lastname="<?= htmlspecialchars($last_name) ?>"
                                    data-dob="<?= htmlspecialchars($row['dob']) ?>"
                                    data-gender="<?= htmlspecialchars($row['gender']) ?>"
                                    data-phone="<?= htmlspecialchars($row['phone']) ?>"
                                    data-email="<?= htmlspecialchars($row['email']) ?>"
                                    data-address="<?= htmlspecialchars($row['address']) ?>"
                                    data-worktype="<?= htmlspecialchars($row['work_type']) ?>"
                                    data-license="<?= htmlspecialchars($row['license_number']) ?>"
                                    data-boat="<?= htmlspecialchars($row['boat_name']) ?>"
                                    data-fishing="<?= htmlspecialchars($row['fishing_area']) ?>"
                                    data-emergency="<?= htmlspecialchars($row['emergency_name']) ?>"
                                    data-emergencyphone="<?= htmlspecialchars($row['emergency_phone']) ?>"
                                    data-agreement="<?= $row['agreement'] ?>"
                                    data-image="<?= htmlspecialchars($row['image']) ?>"
                                ><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-archive btn-sm archive-btn" data-id="<?= $row['id'] ?>">
                                    <i class="bi bi-archive"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h5>No Members Found</h5>
                                <p>Click "Add New Member" to get started</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            <i class="bi bi-person-plus-fill me-2"></i>
            Add New Member
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="add_member" value="1">
        <div class="modal-body">
          <div class="row g-4">
            <div class="col-md-4">
              <label class="form-label"><i class="bi bi-person me-2"></i>First Name <span class="text-danger">*</span></label>
              <input type="text" name="first_name" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label"><i class="bi bi-person me-2"></i>Middle Initial</label>
              <input type="text" name="middle_initial" class="form-control" maxlength="2">
            </div>
            <div class="col-md-4">
              <label class="form-label"><i class="bi bi-person me-2"></i>Last Name <span class="text-danger">*</span></label>
              <input type="text" name="last_name" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-calendar-event me-2"></i>Date of Birth <span class="text-danger">*</span></label>
              <input type="date" name="dob" class="form-control" required max="<?= date('Y-m-d', strtotime('-18 years')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-gender-ambiguous me-2"></i>Gender <span class="text-danger">*</span></label>
              <select name="gender" class="form-select" required>
                <option value="">-- Select Gender --</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-telephone-fill me-2"></i>Phone Number <span class="text-danger">*</span></label>
              <input type="tel" name="phone" class="form-control" required pattern="[0-9]{10,11}" placeholder="09XXXXXXXXX">
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-envelope-fill me-2"></i>Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" required placeholder="member@example.com">
            </div>

            <div class="col-12">
              <label class="form-label"><i class="bi bi-geo-alt-fill me-2"></i>Address <span class="text-danger">*</span></label>
              <input type="text" name="address" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-briefcase-fill me-2"></i>Type of Work</label>
              <select name="work_type" class="form-select">
                <option value="Fisherman">Fisherman</option>
                <option value="Bangkero">Bangkero</option>
                <option value="Both">Both</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-card-heading me-2"></i>License Number</label>
              <input type="text" name="license_number" class="form-control" placeholder="Optional">
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-water me-2"></i>Boat Name</label>
              <input type="text" name="boat_name" class="form-control" placeholder="Optional">
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-pin-map-fill me-2"></i>Fishing Area / Route</label>
              <input type="text" name="fishing_area" class="form-control" placeholder="Optional">
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-person-badge me-2"></i>Emergency Contact Name</label>
              <input type="text" name="emergency_name" class="form-control" placeholder="Optional">
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-telephone me-2"></i>Emergency Contact Phone</label>
              <input type="tel" name="emergency_phone" class="form-control" placeholder="Optional">
            </div>

            <div class="col-12">
              <label class="form-label"><i class="bi bi-image me-2"></i>Upload Photo</label>
              <input type="file" name="image" id="add_image" class="form-control" accept="image/*">
              <small class="text-muted">Accepted: JPG, PNG, GIF (Max 2MB)</small>
              <img id="add_preview" src="" alt="Preview" class="img-preview d-none">
            </div>

            <div class="col-12">
              <div class="form-check">
                <input type="checkbox" name="agreement" class="form-check-input" id="add_agreement" value="1" required>
                <label for="add_agreement" class="form-check-label">
                  <strong>I agree to follow the association's rules and regulations</strong>
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-submit">
            <i class="bi bi-check-circle-fill me-2"></i>
            Add Member
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Member Modal -->
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            <i class="bi bi-pencil-square me-2"></i>
            Edit Member Information
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="edit_member" value="1">
        <input type="hidden" name="member_id" id="edit_member_id">
        <div class="modal-body">
          <div class="row g-4">
            <div class="col-md-4">
              <label class="form-label"><i class="bi bi-person me-2"></i>First Name <span class="text-danger">*</span></label>
              <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label"><i class="bi bi-person me-2"></i>Middle Initial</label>
              <input type="text" name="middle_initial" id="edit_middle_initial" class="form-control" maxlength="2">
            </div>
            <div class="col-md-4">
              <label class="form-label"><i class="bi bi-person me-2"></i>Last Name <span class="text-danger">*</span></label>
              <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-calendar-event me-2"></i>Date of Birth <span class="text-danger">*</span></label>
              <input type="date" name="dob" id="edit_dob" class="form-control" required max="<?= date('Y-m-d', strtotime('-18 years')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-gender-ambiguous me-2"></i>Gender <span class="text-danger">*</span></label>
              <select name="gender" id="edit_gender" class="form-select" required>
                <option value="">-- Select Gender --</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-telephone-fill me-2"></i>Phone Number <span class="text-danger">*</span></label>
              <input type="tel" name="phone" id="edit_phone" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-envelope-fill me-2"></i>Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" id="edit_email" class="form-control" required>
            </div>

            <div class="col-12">
              <label class="form-label"><i class="bi bi-geo-alt-fill me-2"></i>Address <span class="text-danger">*</span></label>
              <input type="text" name="address" id="edit_address" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-briefcase-fill me-2"></i>Type of Work</label>
              <select name="work_type" id="edit_work_type" class="form-select">
                <option value="Fisherman">Fisherman</option>
                <option value="Bangkero">Bangkero</option>
                <option value="Both">Both</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-card-heading me-2"></i>License Number</label>
              <input type="text" name="license_number" id="edit_license_number" class="form-control">
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-water me-2"></i>Boat Name</label>
              <input type="text" name="boat_name" id="edit_boat_name" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-pin-map-fill me-2"></i>Fishing Area / Route</label>
              <input type="text" name="fishing_area" id="edit_fishing_area" class="form-control">
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-person-badge me-2"></i>Emergency Contact Name</label>
              <input type="text" name="emergency_name" id="edit_emergency_name" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-telephone me-2"></i>Emergency Contact Phone</label>
              <input type="tel" name="emergency_phone" id="edit_emergency_phone" class="form-control">
            </div>

            <div class="col-12">
              <label class="form-label"><i class="bi bi-image me-2"></i>Update Photo (optional)</label>
              <input type="file" name="image" id="edit_image" class="form-control" accept="image/*">
              <small class="text-muted">Leave empty to keep current photo</small>
              <img id="edit_preview" src="" alt="Preview" class="img-preview d-none">
            </div>

            <div class="col-12">
              <div class="form-check">
                <input type="checkbox" name="agreement" class="form-check-input" id="edit_agreement" value="1">
                <label for="edit_agreement" class="form-check-label">
                  <strong>I agree to follow the association's rules and regulations</strong>
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-submit">
            <i class="bi bi-save-fill me-2"></i>
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Fill and open Edit modal
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('edit_member_id').value = btn.dataset.id;
        document.getElementById('edit_first_name').value = btn.dataset.firstname;
        document.getElementById('edit_middle_initial').value = btn.dataset.middleinitial;
        document.getElementById('edit_last_name').value = btn.dataset.lastname;
        document.getElementById('edit_dob').value = btn.dataset.dob;
        document.getElementById('edit_gender').value = btn.dataset.gender;
        document.getElementById('edit_phone').value = btn.dataset.phone;
        document.getElementById('edit_email').value = btn.dataset.email;
        document.getElementById('edit_address').value = btn.dataset.address;
        document.getElementById('edit_work_type').value = btn.dataset.worktype;
        document.getElementById('edit_license_number').value = btn.dataset.license;
        document.getElementById('edit_boat_name').value = btn.dataset.boat;
        document.getElementById('edit_fishing_area').value = btn.dataset.fishing;
        document.getElementById('edit_emergency_name').value = btn.dataset.emergency;
        document.getElementById('edit_emergency_phone').value = btn.dataset.emergencyphone;
        document.getElementById('edit_agreement').checked = (btn.dataset.agreement == '1');

        const imgPreview = document.getElementById('edit_preview');
        if (btn.dataset.image && btn.dataset.image !== 'default_member.png') {
            imgPreview.src = "../../uploads/members/" + btn.dataset.image;
            imgPreview.classList.remove('d-none');
        } else {
            imgPreview.classList.add('d-none');
            imgPreview.src = "";
        }

        // reset file input
        document.getElementById('edit_image').value = "";
        
        // show modal
        new bootstrap.Modal(document.getElementById('editMemberModal')).show();
    });
});

// Preview image on add modal
document.getElementById('add_image')?.addEventListener('change', function(e) {
    const [file] = this.files;
    const preview = document.getElementById('add_preview');
    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
});

// Preview image on edit modal
document.getElementById('edit_image')?.addEventListener('change', function(e) {
    const [file] = this.files;
    const preview = document.getElementById('edit_preview');
    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
});

// Live search/filter
const searchInput = document.getElementById('searchInput');
const tableBody = document.getElementById('membersTableBody');
const rows = tableBody.getElementsByTagName('tr');

searchInput.addEventListener('input', function() {
    const filter = this.value.toLowerCase();

    Array.from(rows).forEach(row => {
        if (row.cells.length < 4) return; // Skip empty state row
        
        const name = row.cells[1].textContent.toLowerCase();
        const contact = row.cells[2].textContent.toLowerCase();
        const address = row.cells[3].textContent.toLowerCase();

        if (name.includes(filter) || contact.includes(filter) || address.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Archive confirmation
document.querySelectorAll('.archive-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const memberId = this.getAttribute('data-id');

        Swal.fire({
            title: 'Archive Member?',
            text: "Are you sure you want to move this member to the archive?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, archive it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `?archive=${memberId}`;
            }
        });
    });
});

// SweetAlert messages
<?php if ($alertMsg): ?>
Swal.fire({
    icon: '<?= $alertType ?>',
    title: '<?= ucfirst($alertType) ?>!',
    text: '<?= addslashes($alertMsg) ?>',
    confirmButtonColor: '<?= $alertType === "success" ? "#667eea" : "#ef4444" ?>',
    confirmButtonText: 'OK',
    allowOutsideClick: false
}).then(() => {
    <?php if ($alertType === "success"): ?>
        window.location.href = "memberlist.php";
    <?php endif; ?>
});
<?php endif; ?>

<?php if (isset($_GET['archived'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Archived!',
    text: 'Member moved to archive successfully.',
    timer: 2500,
    showConfirmButton: false,
    confirmButtonColor: '#667eea'
}).then(() => {
    window.location.href = "memberlist.php";
});
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
Swal.fire({
    icon: 'error',
    title: 'Error!',
    text: 'Failed to archive member. Please try again.',
    confirmButtonColor: '#ef4444'
}).then(() => {
    window.location.href = "memberlist.php";
});
<?php endif; ?>
</script>
</body>
</html>
