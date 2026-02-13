<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

$alertType = $alertMsg = "";

/* --------------------------
   ✅ ADD OFFICER HANDLER (with restrictions)
-------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_officer'])) {
    $member_id   = $_POST['member_id'] ?? '';
    $role_id     = $_POST['role_id'] ?? '';
    $term_start  = $_POST['term_start'] ?? '';
    $term_end    = $_POST['term_end'] ?? '';
    $description = $_POST['description'] ?? '';

    if (!$member_id || !$role_id || !$term_start || !$term_end) {
        $alertType = "error";
        $alertMsg = "All required fields must be filled!";
    } 
    // ✅ RESTRICTION 1: Validate term dates
    elseif (strtotime($term_end) <= strtotime($term_start)) {
        $alertType = "error";
        $alertMsg = "Term end date must be after term start date!";
    }
    else {
        // ✅ RESTRICTION 2: Check if this position is already filled for overlapping terms
        $checkPosition = $conn->prepare("
            SELECT COUNT(*) as count FROM officers 
            WHERE role_id = ? 
            AND (
                (term_start <= ? AND term_end >= ?) OR
                (term_start <= ? AND term_end >= ?) OR
                (term_start >= ? AND term_end <= ?)
            )
        ");
        $checkPosition->bind_param("issssss", $role_id, $term_start, $term_start, $term_end, $term_end, $term_start, $term_end);
        $checkPosition->execute();
        $checkPosition->bind_result($positionCount);
        $checkPosition->fetch();
        $checkPosition->close();

        if ($positionCount > 0) {
            $alertType = "error";
            $alertMsg = "This position is already assigned to another member during the selected term period!";
        } else {
            // ✅ RESTRICTION 3: Check if member already has a position during this term
            $checkMember = $conn->prepare("
                SELECT COUNT(*) as count FROM officers 
                WHERE member_id = ? 
                AND (
                    (term_start <= ? AND term_end >= ?) OR
                    (term_start <= ? AND term_end >= ?) OR
                    (term_start >= ? AND term_end <= ?)
                )
            ");
            $checkMember->bind_param("issssss", $member_id, $term_start, $term_start, $term_end, $term_end, $term_start, $term_end);
            $checkMember->execute();
            $checkMember->bind_result($memberCount);
            $checkMember->fetch();
            $checkMember->close();

            if ($memberCount > 0) {
                $alertType = "error";
                $alertMsg = "This member is already assigned to a position during the selected term period!";
            } else {
                // Process image upload
                $imageName = "";
                if (!empty($_FILES['image']['name'])) {
                    $targetDir = "../../uploads/officers/";
                    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

                    $imageName = time() . '_' . basename($_FILES["image"]["name"]);
                    $targetFile = $targetDir . $imageName;
                    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

                    // Basic image validation
                    if (getimagesize($_FILES["image"]["tmp_name"]) === false) {
                        $alertType = "error";
                        $alertMsg = "Invalid image file.";
                    } elseif ($_FILES["image"]["size"] > 2 * 1024 * 1024) {
                        $alertType = "error";
                        $alertMsg = "Image must be less than 2MB.";
                    } elseif (!in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $alertType = "error";
                        $alertMsg = "Allowed file types: JPG, JPEG, PNG, GIF.";
                    } elseif (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                        $alertType = "error";
                        $alertMsg = "Error uploading image.";
                    }
                }

                if (!$alertMsg) {
                    $stmt = $conn->prepare("
                        INSERT INTO officers (member_id, role_id, term_start, term_end, image, description) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param("iissss", $member_id, $role_id, $term_start, $term_end, $imageName, $description);

                    if ($stmt->execute()) {
                        $alertType = "success";
                        $alertMsg = "Officer assigned successfully!";
                    } else {
                        $alertType = "error";
                        $alertMsg = "Database error: " . $conn->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}

/* --------------------------
   ✏️ EDIT OFFICER HANDLER (with restrictions)
   (Updates role, term, description, optional new image)
-------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_officer'])) {
    $officer_id  = intval($_POST['officer_id'] ?? 0);
    $role_id     = $_POST['role_id'] ?? '';
    $term_start  = $_POST['term_start'] ?? '';
    $term_end    = $_POST['term_end'] ?? '';
    $description = $_POST['description'] ?? '';

    if (!$officer_id || !$role_id || !$term_start || !$term_end) {
        $alertType = "error";
        $alertMsg = "All required fields must be filled!";
    } 
    // ✅ RESTRICTION 1: Validate term dates
    elseif (strtotime($term_end) <= strtotime($term_start)) {
        $alertType = "error";
        $alertMsg = "Term end date must be after term start date!";
    }
    else {
        // Get current officer data
        $getCurrentData = $conn->prepare("SELECT member_id FROM officers WHERE id = ?");
        $getCurrentData->bind_param("i", $officer_id);
        $getCurrentData->execute();
        $getCurrentData->bind_result($current_member_id);
        $getCurrentData->fetch();
        $getCurrentData->close();

        // ✅ RESTRICTION 2: Check if this position is already filled by another officer
        $checkPosition = $conn->prepare("
            SELECT COUNT(*) as count FROM officers 
            WHERE role_id = ? 
            AND id != ?
            AND (
                (term_start <= ? AND term_end >= ?) OR
                (term_start <= ? AND term_end >= ?) OR
                (term_start >= ? AND term_end <= ?)
            )
        ");
        $checkPosition->bind_param("iissssss", $role_id, $officer_id, $term_start, $term_start, $term_end, $term_end, $term_start, $term_end);
        $checkPosition->execute();
        $checkPosition->bind_result($positionCount);
        $checkPosition->fetch();
        $checkPosition->close();

        if ($positionCount > 0) {
            $alertType = "error";
            $alertMsg = "This position is already assigned to another member during the selected term period!";
        } else {
            // ✅ RESTRICTION 3: Check if member has another position during this term
            $checkMember = $conn->prepare("
                SELECT COUNT(*) as count FROM officers 
                WHERE member_id = ? 
                AND id != ?
                AND (
                    (term_start <= ? AND term_end >= ?) OR
                    (term_start <= ? AND term_end >= ?) OR
                    (term_start >= ? AND term_end <= ?)
                )
            ");
            $checkMember->bind_param("iissssss", $current_member_id, $officer_id, $term_start, $term_start, $term_end, $term_end, $term_start, $term_end);
            $checkMember->execute();
            $checkMember->bind_result($memberCount);
            $checkMember->fetch();
            $checkMember->close();

            if ($memberCount > 0) {
                $alertType = "error";
                $alertMsg = "This member already has another position during the selected term period!";
            } else {
        // Get current image
        $stmtGet = $conn->prepare("SELECT image FROM officers WHERE id = ?");
        $stmtGet->bind_param("i", $officer_id);
        $stmtGet->execute();
        $stmtGet->bind_result($currentImage);
        $stmtGet->fetch();
        $stmtGet->close();

        $newImage = $currentImage;

        if (!empty($_FILES['image']['name'])) {
            $targetDir = "../../uploads/officers/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $newImage = time() . '_' . basename($_FILES["image"]["name"]);
            $targetFile = $targetDir . $newImage;
            $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            if (getimagesize($_FILES["image"]["tmp_name"]) === false) {
                $alertType = "error";
                $alertMsg = "Invalid image file.";
            } elseif ($_FILES["image"]["size"] > 2 * 1024 * 1024) {
                $alertType = "error";
                $alertMsg = "Image must be less than 2MB.";
            } elseif (!in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                $alertType = "error";
                $alertMsg = "Allowed file types: JPG, JPEG, PNG, GIF.";
            } elseif (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $alertType = "error";
                $alertMsg = "Error uploading image.";
            } else {
                // remove old image file if exists
                if ($currentImage && file_exists($targetDir . $currentImage)) {
                    @unlink($targetDir . $currentImage);
                }
            }
        }

                if (!$alertMsg) {
                    $stmt = $conn->prepare("
                        UPDATE officers 
                        SET role_id = ?, term_start = ?, term_end = ?, image = ?, description = ?
                        WHERE id = ?
                    ");
                    $stmt->bind_param("issssi", $role_id, $term_start, $term_end, $newImage, $description, $officer_id);

                    if ($stmt->execute()) {
                        $alertType = "success";
                        $alertMsg = "Officer updated successfully!";
                    } else {
                        $alertType = "error";
                        $alertMsg = "Database error: " . $conn->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}

/* --------------------------
   🗂 ARCHIVE HANDLER
-------------------------- */
if (isset($_GET['archive'])) {
    $officer_id = intval($_GET['archive']);
    if ($officer_id > 0) {
        // insert to archive then delete
        $conn->query("INSERT INTO officers_archive (member_id, role_id, term_start, term_end, image)
                      SELECT member_id, role_id, term_start, term_end, image FROM officers WHERE id=$officer_id");
        $conn->query("DELETE FROM officers WHERE id=$officer_id");
        header('Location: officerslist.php?archived=1');
        exit;
    }
}

/* --------------------------
   📋 FETCH DATA FOR DISPLAY
-------------------------- */
$membersResult = $conn->query("SELECT id, name FROM members ORDER BY name ASC");
$rolesResult   = $conn->query("SELECT id, role_name FROM officer_roles ORDER BY role_name ASC");

// Get search and filter parameters
$search = trim($_GET['search'] ?? '');
$role_filter = $_GET['role_filter'] ?? '';
$term_status = $_GET['term_status'] ?? 'all'; // current, previous, all
$sort = $_GET['sort'] ?? 'pos_asc';

// Build base conditions
$current_where = "o.term_end >= CURDATE()";
$previous_where = "o.term_end < CURDATE()";
$common_where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $common_where[] = "(m.name LIKE ? OR r.role_name LIKE ? OR o.description LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($role_filter)) {
    $common_where[] = "o.role_id = ?";
    $params[] = $role_filter;
    $types .= 'i';
}

$common_where_sql = !empty($common_where) ? " AND " . implode(" AND ", $common_where) : "";

// Sort logic
$order_sql = "ORDER BY r.role_name ASC";
if ($sort === 'pos_desc') {
    $order_sql = "ORDER BY r.role_name DESC";
} elseif ($sort === 'name_asc') {
    $order_sql = "ORDER BY m.name ASC";
} elseif ($sort === 'name_desc') {
    $order_sql = "ORDER BY m.name DESC";
} elseif ($sort === 'term_new') {
    $order_sql = "ORDER BY o.term_start DESC";
} elseif ($sort === 'term_old') {
    $order_sql = "ORDER BY o.term_start ASC";
}

// 1. Fetch CURRENT OFFICERS
$current_result = null;
if ($term_status === 'all' || $term_status === 'current') {
    $current_sql = "
        SELECT 
            o.id, o.member_id, m.name AS member_name, o.role_id, r.role_name AS position, 
            o.term_start, o.term_end, o.image, o.description
        FROM officers o
        JOIN members m ON o.member_id = m.id
        JOIN officer_roles r ON o.role_id = r.id
        WHERE {$current_where} {$common_where_sql}
        {$order_sql}
    ";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($current_sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $current_result = $stmt->get_result();
    } else {
        $current_result = $conn->query($current_sql);
    }
}
$current_count = ($current_result) ? $current_result->num_rows : 0;

// 2. Fetch PREVIOUS OFFICERS
$previous_result = null;
if ($term_status === 'all' || $term_status === 'previous') {
    $previous_sql = "
        SELECT 
            o.id, o.member_id, m.name AS member_name, o.role_id, r.role_name AS position, 
            o.term_start, o.term_end, o.image, o.description
        FROM officers o
        JOIN members m ON o.member_id = m.id
        JOIN officer_roles r ON o.role_id = r.id
        WHERE {$previous_where} {$common_where_sql}
        {$order_sql}
    ";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($previous_sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $previous_result = $stmt->get_result();
    } else {
        $previous_result = $conn->query($previous_sql);
    }
}
$previous_count = ($previous_result) ? $previous_result->num_rows : 0;

$officers_count = $current_count + $previous_count;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Officers List | Bangkero & Fishermen Association</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSS -->
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

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 32px;
        }
        .form-label-sm {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 4px;
            display: block;
        }

        /* Add Officer Button */
        .btn-add-officer {
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
        .btn-add-officer:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
        }

        /* Filter Controls */
        .filter-select {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            background-color: white;
            height: auto;
        }
        .filter-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        /* Search Box */
        .search-box {
            position: relative;
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

        /* Action Bar */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        /* Bulk Selection */
        .officer-row.selected {
            background-color: #eff6ff;
        }
        .form-check-input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        /* Export Dropdown */
        .btn-export {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white !important;
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
        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            color: white !important;
        }
        .dropdown { position: relative; }
        .dropdown-menu {
            display: none;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border: none;
            padding: 8px;
            position: absolute;
            top: 100%;
            right: 0;
            min-width: 200px;
            z-index: 1050;
            background: white;
        }
        .dropdown.show .dropdown-menu { display: block; }

        /* Section Header */
        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 32px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e0e0e0;
        }
        .section-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
        }
        .section-header .badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 12px;
            padding: 4px 12px;
        }

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 32px;
        }
        .table { margin-bottom: 0; }
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
        .table tbody tr:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            transform: translateX(4px);
        }

        /* Officer Image */
        .officer-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Action Buttons */
        .btn-sm {
            padding: 8px 12px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
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
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        .btn-archive:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
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
        .modal-header .modal-title { font-weight: 700; font-size: 24px; }
        .modal-header .btn-close { filter: brightness(0) invert(1); opacity: 0.8; }
        .modal-body { padding: 32px; }
        .modal-footer { padding: 24px; border-top: 1px solid #f0f0f0; }

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
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(16, 185, 129, 0.5); }

        .img-preview {
            width: 120px; height: 120px; border-radius: 12px;
            object-fit: cover; margin-top: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .empty-state {
            text-align: center; padding: 60px 20px; color: #94a3b8;
        }
        .empty-state i { font-size: 64px; margin-bottom: 20px; opacity: 0.5; }

        @media (max-width: 991.98px) { 
            .main-content { margin-left: 0; padding: 16px; }
            .page-header { padding: 24px; }
            .page-header h2 { font-size: 24px; flex-direction: column; align-items: flex-start; }
            .filter-section { padding: 16px; }
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
                    Officers Management
                    <span class="badge"><?php echo $current_count; ?> Active</span>
                </h2>
                <p class="mb-0 mt-2" style="opacity: 0.9;">Manage organization officers and their terms</p>
            </div>
            <button class="btn-add-officer" data-bs-toggle="modal" data-bs-target="#addOfficerModal">
                <i class="bi bi-plus-circle-fill"></i>
                Assign New Officer
            </button>
        </div>
    </div>

    <!-- Advanced Filter Section -->
    <div class="filter-section mb-4">
        <form method="GET" id="filterForm" class="row g-3">
            <div class="col-md-3">
                <label class="form-label-sm">Search</label>
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" placeholder="Name, position, desc..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
                </div>
            </div>
            
            <div class="col-md-2">
                <label class="form-label-sm">Position</label>
                <select name="role_filter" class="form-select filter-select">
                    <option value="">All Positions</option>
                    <?php $rolesResult->data_seek(0); while ($r = $rolesResult->fetch_assoc()): ?>
                        <option value="<?= $r['id'] ?>" <?= $role_filter == $r['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['role_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label-sm">Term Status</label>
                <select name="term_status" class="form-select filter-select">
                    <option value="all" <?= $term_status === 'all' ? 'selected' : '' ?>>All Terms</option>
                    <option value="current" <?= $term_status === 'current' ? 'selected' : '' ?>>Current Only</option>
                    <option value="previous" <?= $term_status === 'previous' ? 'selected' : '' ?>>Previous Only</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label-sm">Sort By</label>
                <select name="sort" class="form-select filter-select">
                    <option value="pos_asc" <?= $sort === 'pos_asc' ? 'selected' : '' ?>>Position (A-Z)</option>
                    <option value="pos_desc" <?= $sort === 'pos_desc' ? 'selected' : '' ?>>Position (Z-A)</option>
                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                    <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                    <option value="term_new" <?= $sort === 'term_new' ? 'selected' : '' ?>>Newest Term</option>
                    <option value="term_old" <?= $sort === 'term_old' ? 'selected' : '' ?>>Oldest Term</option>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1" style="padding: 12px 16px;">
                    <i class="bi bi-funnel"></i> Apply
                </button>
                <a href="officerslist.php" class="btn btn-outline-secondary flex-grow-1" style="padding: 12px 16px;">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
        <div class="d-flex gap-2 align-items-center">
            <div id="bulkActionsContainer" style="display:none;" class="d-flex gap-2">
                <span id="selectedCount" class="badge bg-primary" style="font-size: 14px; padding: 10px 16px;">0 selected</span>
                <button id="btnBulkArchive" class="btn btn-warning btn-sm text-white">
                    <i class="bi bi-archive"></i> Archive Selected
                </button>
                <button id="btnDeselectAll" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle"></i> Clear
                </button>
            </div>
            
            <div class="dropdown" id="exportDropdownContainer">
                <button class="btn-export dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" onclick="exportData('csv'); return false;"><i class="bi bi-filetype-csv me-2"></i>CSV Format</a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportData('pdf'); return false;"><i class="bi bi-file-earmark-pdf me-2"></i>PDF Document</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="exportData('print'); return false;"><i class="bi bi-printer me-2"></i>Print Preview</a></li>
                </ul>
            </div>
        </div>
        
        <div class="text-muted small fw-semibold">
            Showing <?= $officers_count ?> officers
        </div>
    </div>

    <!-- Officers Sections -->
    <div class="table-container">
        <!-- CURRENT OFFICERS -->
        <?php if ($term_status === 'all' || $term_status === 'current'): ?>
        <div class="section-header">
            <i class="bi bi-star-fill" style="color: #f59e0b;"></i>
            <h3>Current Officers</h3>
            <span class="badge"><?php echo $current_count; ?> Active</span>
        </div>
        
        <div class="table-responsive <?= $term_status === 'all' ? 'mb-5' : '' ?>">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">
                            <input type="checkbox" class="form-check-input selectAll" title="Select All">
                        </th>
                        <th style="width: 60px;" class="text-center">#</th>
                        <th>Officer</th>
                        <th>Position</th>
                        <th>Term Duration</th>
                        <th class="text-center" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($current_result && $current_result->num_rows > 0): $i=1; while ($row = $current_result->fetch_assoc()): ?>
                    <tr class="officer-row">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input officer-checkbox" value="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['member_name']) ?>">
                        </td>
                        <td class="text-center"><strong><?= $i++ ?></strong></td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="../../uploads/officers/<?= htmlspecialchars($row['image'] ?? 'default.png') ?>" 
                                     class="officer-img" 
                                     alt="Officer"
                                     onerror="if (!this.getAttribute('data-tried-fallback')) { this.setAttribute('data-tried-fallback', 'true'); this.src='https://ui-avatars.com/api/?name=<?= urlencode($row['member_name']) ?>&background=random&color=fff'; }">
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($row['member_name']) ?></div>
                                    <div class="text-muted small">ID: #<?= $row['id'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2">
                                <?= htmlspecialchars($row['position']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="small fw-semibold text-dark"><?= date('M d, Y', strtotime($row['term_start'])) ?></div>
                            <div class="text-muted small">to <?= date('M d, Y', strtotime($row['term_end'])) ?></div>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-edit btn-sm editBtn"
                                    data-id="<?= $row['id'] ?>"
                                    data-member="<?= htmlspecialchars($row['member_name']) ?>"
                                    data-memberid="<?= $row['member_id'] ?>"
                                    data-role="<?= $row['role_id'] ?>"
                                    data-start="<?= $row['term_start'] ?>"
                                    data-end="<?= $row['term_end'] ?>"
                                    data-desc="<?= htmlspecialchars($row['description']) ?>"
                                    data-image="<?= htmlspecialchars($row['image']) ?>"
                                ><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-archive btn-sm archive-btn" data-id="<?= $row['id'] ?>">
                                    <i class="bi bi-archive"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h5>No current officers found matching your criteria</h5>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- PREVIOUS OFFICERS -->
        <?php if ($term_status === 'all' || $term_status === 'previous'): ?>
        <div class="section-header">
            <i class="bi bi-clock-history" style="color: #94a3b8;"></i>
            <h3>Previous Officers</h3>
            <span class="badge" style="background: #94a3b8;"><?php echo $previous_count; ?> History</span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">
                            <input type="checkbox" class="form-check-input selectAll" title="Select All">
                        </th>
                        <th style="width: 60px;" class="text-center">#</th>
                        <th>Officer</th>
                        <th>Position</th>
                        <th>Term Duration</th>
                        <th class="text-center" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($previous_result && $previous_result->num_rows > 0): $i=1; while ($row = $previous_result->fetch_assoc()): ?>
                    <tr class="officer-row" style="opacity: 0.85;">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input officer-checkbox" value="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['member_name']) ?>">
                        </td>
                        <td class="text-center"><strong><?= $i++ ?></strong></td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="../../uploads/officers/<?= htmlspecialchars($row['image'] ?? 'default.png') ?>" 
                                     class="officer-img" 
                                     alt="Officer"
                                     onerror="if (!this.getAttribute('data-tried-fallback')) { this.setAttribute('data-tried-fallback', 'true'); this.src='https://ui-avatars.com/api/?name=<?= urlencode($row['member_name']) ?>&background=random&color=fff'; }">
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($row['member_name']) ?></div>
                                    <div class="text-muted small">Term Ended</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2">
                                <?= htmlspecialchars($row['position']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="small fw-semibold text-muted"><?= date('M d, Y', strtotime($row['term_start'])) ?></div>
                            <div class="text-muted small">Ended <?= date('M d, Y', strtotime($row['term_end'])) ?></div>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-edit btn-sm editBtn"
                                    data-id="<?= $row['id'] ?>"
                                    data-member="<?= htmlspecialchars($row['member_name']) ?>"
                                    data-memberid="<?= $row['member_id'] ?>"
                                    data-role="<?= $row['role_id'] ?>"
                                    data-start="<?= $row['term_start'] ?>"
                                    data-end="<?= $row['term_end'] ?>"
                                    data-desc="<?= htmlspecialchars($row['description']) ?>"
                                    data-image="<?= htmlspecialchars($row['image']) ?>"
                                ><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-archive btn-sm archive-btn" data-id="<?= $row['id'] ?>">
                                    <i class="bi bi-archive"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h5>No previous officers found matching your criteria</h5>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>

<!-- Add Officer Modal -->
<div class="modal fade" id="addOfficerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            <i class="bi bi-person-plus-fill me-2"></i>
            Assign New Officer
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="add_officer" value="1">
        <div class="modal-body">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-person-circle me-2"></i>Select Member *</label>
              <select name="member_id" class="form-select" required>
                <option value="">-- Choose a member --</option>
                <?php $membersResult->data_seek(0); while ($m = $membersResult->fetch_assoc()): ?>
                  <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-award-fill me-2"></i>Position *</label>
              <select name="role_id" class="form-select" required>
                <option value="">-- Choose a position --</option>
                <?php $rolesResult->data_seek(0); while ($r = $rolesResult->fetch_assoc()): ?>
                  <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-calendar-check me-2"></i>Term Start *</label>
              <input type="date" name="term_start" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-calendar-x me-2"></i>Term End *</label>
              <input type="date" name="term_end" class="form-control" required>
            </div>

            <div class="col-12">
              <label class="form-label"><i class="bi bi-image me-2"></i>Photo (optional)</label>
              <input type="file" name="image" id="add_image" class="form-control" accept=".jpg,.jpeg,.png,.gif">
              <small class="text-muted">Accepted: JPG, JPEG, PNG, GIF (Max 2MB)</small>
              <img id="add_preview" src="" alt="Preview" class="img-preview d-none">
            </div>

            <div class="col-12">
              <label class="form-label"><i class="bi bi-card-text me-2"></i>Description (optional)</label>
              <textarea name="description" class="form-control" rows="3" placeholder="Add any additional notes..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-submit">
            <i class="bi bi-check-circle-fill me-2"></i>
            Assign Officer
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Officer Modal -->
<div class="modal fade" id="editOfficerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            <i class="bi bi-pencil-square me-2"></i>
            Edit Officer Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="edit_officer" value="1">
        <input type="hidden" name="officer_id" id="edit_officer_id">
        <div class="modal-body">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-person-circle me-2"></i>Member</label>
              <input type="text" id="edit_member_name" class="form-control" readonly style="background: #f8f9fa;">
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-award-fill me-2"></i>Position *</label>
              <select name="role_id" id="edit_role_id" class="form-select" required>
                <option value="">-- Choose a position --</option>
                <?php $rolesResult->data_seek(0); while ($r = $rolesResult->fetch_assoc()): ?>
                  <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-calendar-check me-2"></i>Term Start *</label>
              <input type="date" name="term_start" id="edit_term_start" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-calendar-x me-2"></i>Term End *</label>
              <input type="date" name="term_end" id="edit_term_end" class="form-control" required>
            </div>

            <div class="col-12">
              <label class="form-label"><i class="bi bi-image me-2"></i>Update Photo (optional)</label>
              <input type="file" name="image" id="edit_image" class="form-control" accept=".jpg,.jpeg,.png,.gif">
              <small class="text-muted">Leave empty to keep current photo</small>
              <img id="edit_preview" src="" alt="Preview" class="img-preview d-none">
            </div>

            <div class="col-12">
              <label class="form-label"><i class="bi bi-card-text me-2"></i>Description (optional)</label>
              <textarea name="description" id="edit_description" class="form-control" rows="3" placeholder="Add any additional notes..."></textarea>
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

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ==========================================
// BULK SELECTION FUNCTIONALITY
// ==========================================
let selectedOfficers = [];

// Handle "Select All" for each table
document.querySelectorAll('.selectAll').forEach(selectBtn => {
    selectBtn.addEventListener('change', function() {
        const table = this.closest('table');
        const checkboxes = table.querySelectorAll('.officer-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
            updateRowSelection(cb);
        });
        updateBulkActions();
    });
});

// Individual Checkbox Change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('officer-checkbox')) {
        updateRowSelection(e.target);
        updateBulkActions();
        
        // Update "Select All" for the specific table
        const table = e.target.closest('table');
        const checkboxes = table.querySelectorAll('.officer-checkbox');
        const checkedCount = table.querySelectorAll('.officer-checkbox:checked').length;
        const selectAllBtn = table.querySelector('.selectAll');
        if (selectAllBtn) {
            selectAllBtn.checked = checkedCount === checkboxes.length;
            selectAllBtn.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        }
    }
});

function updateRowSelection(checkbox) {
    const row = checkbox.closest('tr');
    if (checkbox.checked) {
        row.classList.add('selected');
    } else {
        row.classList.remove('selected');
    }
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.officer-checkbox:checked');
    const count = checkboxes.length;
    const bulkContainer = document.getElementById('bulkActionsContainer');
    const selectedCountBadge = document.getElementById('selectedCount');
    
    if (count > 0) {
        bulkContainer.style.setProperty('display', 'flex', 'important');
        selectedCountBadge.textContent = `${count} selected`;
        selectedOfficers = Array.from(checkboxes).map(cb => ({
            id: cb.value,
            name: cb.dataset.name
        }));
    } else {
        bulkContainer.style.setProperty('display', 'none', 'important');
        selectedOfficers = [];
    }
}

// Bulk Archive
document.addEventListener('click', function(e) {
    if (e.target.id === 'btnBulkArchive' || e.target.closest('#btnBulkArchive')) {
        if (selectedOfficers.length === 0) return;
        
        Swal.fire({
            title: 'Archive Selected Officers?',
            html: `Move <strong>${selectedOfficers.length}</strong> officers to archive?<br><br>` +
                  `<div style="max-height: 200px; overflow-y: auto; text-align: left; padding: 10px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">` +
                  selectedOfficers.map(m => `• ${m.name}`).join('<br>') +
                  `</div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, archive them!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // We'll reuse the archive logic, but for multiple IDs
                const ids = selectedOfficers.map(o => o.id).join(',');
                window.location.href = `bulk_delete_officers.php?ids=${ids}&action=archive`;
            }
        });
    }
});

// Deselect All
document.getElementById('btnDeselectAll')?.addEventListener('click', function() {
    document.querySelectorAll('.officer-checkbox:checked').forEach(cb => {
        cb.checked = false;
        updateRowSelection(cb);
    });
    document.querySelectorAll('.selectAll').forEach(s => {
        s.checked = false;
        s.indeterminate = false;
    });
    updateBulkActions();
});

// ==========================================
// DROPDOWN FIX
// ==========================================
document.addEventListener('click', function(e) {
    const dropdownToggle = e.target.closest('.dropdown-toggle');
    const openDropdowns = document.querySelectorAll('.dropdown.show');

    if (dropdownToggle) {
        const dropdown = dropdownToggle.closest('.dropdown');
        const isShow = dropdown.classList.contains('show');
        openDropdowns.forEach(d => d.classList.remove('show'));
        if (!isShow) dropdown.classList.add('show');
        e.preventDefault();
        e.stopPropagation();
    } else {
        openDropdowns.forEach(d => d.classList.remove('show'));
    }
});

// ==========================================
// EXPORT FUNCTIONALITY
// ==========================================
function exportData(format) {
    const currentParams = new URLSearchParams(window.location.search);
    let url = (format === 'csv') ? 'export_officers_csv.php' : 'export_officers_pdf.php';
    if (format === 'print') url = 'export_officers_print.php';
    
    window.open(`${url}?${currentParams.toString()}`, '_blank');
    
    Swal.fire({
        icon: 'success',
        title: 'Export Started!',
        text: `Preparing ${format.toUpperCase()} export...`,
        timer: 1500,
        showConfirmButton: false
    });
}

// Individual Archive confirmation
document.addEventListener('click', function(e) {
    const archiveBtn = e.target.closest('.archive-btn');
    if (archiveBtn) {
        const id = archiveBtn.dataset.id;
        Swal.fire({
            title: 'Archive Officer?',
            text: 'Move this officer to the history/archive?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-archive-fill me-2"></i>Yes, archive it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'officerslist.php?archive=' + id;
            }
        });
    }
});

// Fill and open Edit modal
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('edit_officer_id').value = btn.dataset.id;
        document.getElementById('edit_member_name').value = btn.dataset.member;
        document.getElementById('edit_role_id').value = btn.dataset.role;
        document.getElementById('edit_term_start').value = btn.dataset.start;
        document.getElementById('edit_term_end').value = btn.dataset.end;
        document.getElementById('edit_description').value = btn.dataset.desc;

        const imgPreview = document.getElementById('edit_preview');
        if (btn.dataset.image) {
            imgPreview.src = "../../uploads/officers/" + btn.dataset.image;
            imgPreview.classList.remove('d-none');
        } else {
            imgPreview.classList.add('d-none');
            imgPreview.src = "";
        }
        document.getElementById('edit_image').value = "";
        new bootstrap.Modal(document.getElementById('editOfficerModal')).show();
    });
});

// preview images
['add_image', 'edit_image'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', function(e) {
        const [file] = this.files;
        const preview = document.getElementById(id.replace('image', 'preview'));
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        } else {
            preview.classList.add('d-none');
        }
    });
});

// SweetAlert messages
<?php if ($alertMsg): ?>
Swal.fire({
    icon: '<?= $alertType ?>',
    title: '<?= ucfirst($alertType) ?>!',
    text: '<?= addslashes($alertMsg) ?>',
    confirmButtonColor: '<?= $alertType === "success" ? "#667eea" : "#ef4444" ?>',
    confirmButtonText: 'OK'
}).then(() => {
    <?php if ($alertType === "success"): ?> window.location.href = "officerslist.php"; <?php endif; ?>
});
<?php endif; ?>

<?php if (isset($_GET['archived'])): ?>
Swal.fire({ icon: 'success', title: 'Archived!', text: 'Officer moved to archive.', timer: 2000, showConfirmButton: false });
<?php endif; ?>

<?php if (isset($_GET['bulk_archived'])): ?>
Swal.fire({ icon: 'success', title: 'Bulk Archive Done!', text: '<?= intval($_GET['bulk_archived']) ?> officers archived.', timer: 2500, showConfirmButton: false });
<?php endif; ?>
</script>
</body>
</html>
