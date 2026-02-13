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

// Create uploads directory if not exists
$uploadDir = '../../uploads/awards/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/* --------------------------
   ✅ ADD AWARD HANDLER
-------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_award'])) {
    $award_title = trim($_POST['award_title'] ?? '');
    $awarding_body = trim($_POST['awarding_body'] ?? '');
    $category = $_POST['category'] ?? '';
    $year_received = intval($_POST['year_received'] ?? 0);
    $date_received = $_POST['date_received'] ?? '';
    $description = trim($_POST['description'] ?? '');

    // Validate required fields
    if (empty($award_title) || empty($awarding_body) || empty($category) || empty($year_received) || empty($date_received)) {
        $alertType = "error";
        $alertMsg = "Please fill in all required fields!";
    }

    // Handle image upload
    $award_image = '';
    if (empty($alertMsg) && !empty($_FILES['award_image']['name']) && $_FILES['award_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['award_image']['type'];
        $file_size = $_FILES['award_image']['size'];

        if (!in_array($file_type, $allowed_types)) {
            $alertType = "error";
            $alertMsg = "Invalid image format! Only JPG, PNG, GIF, and WEBP are allowed.";
        } elseif ($file_size > 5 * 1024 * 1024) {
            $alertType = "error";
            $alertMsg = "Image size must be less than 5MB!";
        } else {
            $award_image = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '', basename($_FILES['award_image']['name']));
            if (!move_uploaded_file($_FILES['award_image']['tmp_name'], $uploadDir . $award_image)) {
                $alertType = "error";
                $alertMsg = "Error uploading image. Please try again.";
                $award_image = '';
            }
        }
    }

    // Handle certificate upload
    $certificate_file = '';
    if (empty($alertMsg) && !empty($_FILES['certificate_file']['name']) && $_FILES['certificate_file']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $file_type = $_FILES['certificate_file']['type'];
        $file_size = $_FILES['certificate_file']['size'];

        if (!in_array($file_type, $allowed_types)) {
            $alertType = "error";
            $alertMsg = "Invalid certificate format! Only JPG, PNG, and PDF are allowed.";
        } elseif ($file_size > 5 * 1024 * 1024) {
            $alertType = "error";
            $alertMsg = "Certificate file size must be less than 5MB!";
        } else {
            $certificate_file = 'cert_' . time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '', basename($_FILES['certificate_file']['name']));
            if (!move_uploaded_file($_FILES['certificate_file']['tmp_name'], $uploadDir . $certificate_file)) {
                $alertType = "error";
                $alertMsg = "Error uploading certificate. Please try again.";
                $certificate_file = '';
            }
        }
    }

    // Insert into database
    if (empty($alertMsg)) {
        $stmt = $conn->prepare("
            INSERT INTO awards (award_title, awarding_body, category, description, year_received, date_received, award_image, certificate_file) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssssss", $award_title, $awarding_body, $category, $description, $year_received, $date_received, $award_image, $certificate_file);

        if ($stmt->execute()) {
            $alertType = "success";
            $alertMsg = "Award added successfully!";
        } else {
            $alertType = "error";
            $alertMsg = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
}

/* --------------------------
   ✏️ EDIT AWARD HANDLER
-------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_award'])) {
    $award_id = intval($_POST['award_id'] ?? 0);
    $award_title = trim($_POST['award_title'] ?? '');
    $awarding_body = trim($_POST['awarding_body'] ?? '');
    $category = $_POST['category'] ?? '';
    $year_received = intval($_POST['year_received'] ?? 0);
    $date_received = $_POST['date_received'] ?? '';
    $description = trim($_POST['description'] ?? '');

    // Validate required fields
    if (!$award_id || empty($award_title) || empty($awarding_body) || empty($category) || empty($year_received) || empty($date_received)) {
        $alertType = "error";
        $alertMsg = "Please fill in all required fields!";
    }

    if (empty($alertMsg)) {
        // Get current award data
        $getCurrent = $conn->prepare("SELECT award_image, certificate_file FROM awards WHERE award_id = ?");
        $getCurrent->bind_param("i", $award_id);
        $getCurrent->execute();
        $getCurrent->bind_result($current_image, $current_cert);
        $getCurrent->fetch();
        $getCurrent->close();

        $new_image = $current_image;
        $new_cert = $current_cert;

        // Handle image upload
        if (!empty($_FILES['award_image']['name']) && $_FILES['award_image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['award_image']['type'];
            $file_size = $_FILES['award_image']['size'];

            if (!in_array($file_type, $allowed_types)) {
                $alertType = "error";
                $alertMsg = "Invalid image format! Only JPG, PNG, GIF, and WEBP are allowed.";
            } elseif ($file_size > 5 * 1024 * 1024) {
                $alertType = "error";
                $alertMsg = "Image size must be less than 5MB!";
            } else {
                $new_image = time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '', basename($_FILES['award_image']['name']));
                if (move_uploaded_file($_FILES['award_image']['tmp_name'], $uploadDir . $new_image)) {
                    // Delete old image
                    if ($current_image && file_exists($uploadDir . $current_image)) {
                        @unlink($uploadDir . $current_image);
                    }
                } else {
                    $alertType = "error";
                    $alertMsg = "Error uploading image. Please try again.";
                    $new_image = $current_image;
                }
            }
        }

        // Handle certificate upload
        if (empty($alertMsg) && !empty($_FILES['certificate_file']['name']) && $_FILES['certificate_file']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
            $file_type = $_FILES['certificate_file']['type'];
            $file_size = $_FILES['certificate_file']['size'];

            if (!in_array($file_type, $allowed_types)) {
                $alertType = "error";
                $alertMsg = "Invalid certificate format! Only JPG, PNG, and PDF are allowed.";
            } elseif ($file_size > 5 * 1024 * 1024) {
                $alertType = "error";
                $alertMsg = "Certificate file size must be less than 5MB!";
            } else {
                $new_cert = 'cert_' . time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '', basename($_FILES['certificate_file']['name']));
                if (move_uploaded_file($_FILES['certificate_file']['tmp_name'], $uploadDir . $new_cert)) {
                    // Delete old certificate
                    if ($current_cert && file_exists($uploadDir . $current_cert)) {
                        @unlink($uploadDir . $current_cert);
                    }
                } else {
                    $alertType = "error";
                    $alertMsg = "Error uploading certificate. Please try again.";
                    $new_cert = $current_cert;
                }
            }
        }

        // Update database
        if (empty($alertMsg)) {
            $stmt = $conn->prepare("
                UPDATE awards SET 
                    award_title=?, awarding_body=?, category=?, description=?, 
                    year_received=?, date_received=?, award_image=?, certificate_file=?
                WHERE award_id=?
            ");
            $stmt->bind_param("ssssssssi", $award_title, $awarding_body, $category, $description, $year_received, $date_received, $new_image, $new_cert, $award_id);

            if ($stmt->execute()) {
                $alertType = "success";
                $alertMsg = "Award updated successfully!";
            } else {
                $alertType = "error";
                $alertMsg = "Database error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

/* --------------------------
   🗂️ ARCHIVE HANDLER
-------------------------- */
if (isset($_GET['archive'])) {
    $id = intval($_GET['archive']);
    if ($id > 0) {
        try {
            $conn->begin_transaction();

            // Move to archive
            $stmt = $conn->prepare("INSERT INTO awards_archive (
                            award_id, award_title, awarding_body, category, 
                            description, year_received, date_received, 
                            award_image, certificate_file, original_created_at
                        )
                        SELECT 
                            award_id, award_title, awarding_body, category, 
                            description, year_received, date_received, 
                            award_image, certificate_file, created_at
                        FROM awards
                        WHERE award_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            // Delete from main
            $stmt = $conn->prepare("DELETE FROM awards WHERE award_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            header("Location: awards.php?archived=1");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            header("Location: awards.php?error=" . urlencode($e->getMessage()));
            exit();
        }
    }
}


/* --------------------------
   📋 FILTER & SEARCH PARAMS
-------------------------- */
$search = trim($_GET['search'] ?? '');
$category_filter = trim($_GET['category'] ?? '');
$year_filter = trim($_GET['year'] ?? '');
$body_filter = trim($_GET['awarding_body'] ?? '');
$sort = trim($_GET['sort'] ?? 'newest');

/* --------------------------
   📋 BUILD DYNAMIC QUERY
-------------------------- */
$where_conditions = [];
$query_params = [];
$param_types = '';

if ($search !== '') {
    $where_conditions[] = "(award_title LIKE ? OR awarding_body LIKE ? OR description LIKE ?)";
    $search_term = "%{$search}%";
    $query_params[] = $search_term;
    $query_params[] = $search_term;
    $query_params[] = $search_term;
    $param_types .= 'sss';
}

if ($category_filter !== '') {
    $where_conditions[] = "category = ?";
    $query_params[] = $category_filter;
    $param_types .= 's';
}

if ($year_filter !== '') {
    $where_conditions[] = "year_received = ?";
    $query_params[] = $year_filter;
    $param_types .= 'i';
}

if ($body_filter !== '') {
    $where_conditions[] = "awarding_body = ?";
    $query_params[] = $body_filter;
    $param_types .= 's';
}

// Build WHERE clause
$where_sql = !empty($where_conditions) ? " WHERE " . implode(" AND ", $where_conditions) : "";

// Count total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM awards" . $where_sql;
if (!empty($query_params)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param($param_types, ...$query_params);
    $count_stmt->execute();
    $total_records = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_records = $conn->query($count_sql)->fetch_assoc()['total'];
}

// Pagination settings
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;
$total_pages = ceil($total_records / $per_page);

// Sorting logic
$order_sql = " ORDER BY year_received DESC, date_received DESC";
if ($sort === 'oldest') {
    $order_sql = " ORDER BY year_received ASC, date_received ASC";
} elseif ($sort === 'title_a') {
    $order_sql = " ORDER BY award_title ASC";
} elseif ($sort === 'title_z') {
    $order_sql = " ORDER BY award_title DESC";
} elseif ($sort === 'category') {
    $order_sql = " ORDER BY category ASC, award_title ASC";
}

// Final Query
$sql = "SELECT * FROM awards" . $where_sql . $order_sql . " LIMIT ? OFFSET ?";
$final_params = $query_params;
$final_params[] = $per_page;
$final_params[] = $offset;
$final_types = $param_types . 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($final_types, ...$final_params);
$stmt->execute();
$result = $stmt->get_result();

$awards_count = $total_records;

// Fetch unique awarding bodies for filter
$bodies_res = $conn->query("SELECT DISTINCT awarding_body FROM awards ORDER BY awarding_body ASC");
$awarding_bodies = [];
while ($b = $bodies_res->fetch_assoc()) $awarding_bodies[] = $b['awarding_body'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Awards Management | Bangkero & Fishermen Association</title>
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
        margin-left: 270px; 
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
  .page-header .subtitle {
      font-size: 15px;
      opacity: 0.95;
      margin: 8px 0 0 0;
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
  .filter-select {
      border: 2px solid #e0e0e0;
      border-radius: 12px;
      padding: 12px 16px;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.3s ease;
      background-color: white;
      height: auto;
      width: 100%;
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

  /* Add Button */
    .btn-add-award {
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
    .btn-add-award:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
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

    /* Thumbnail */
    .award-thumb {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    .award-thumb:hover {
        transform: scale(1.5);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        z-index: 10;
    }

    /* Category Badges */
    .badge-category {
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 12px;
    }
    .badge-national {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #10b981;
    }
    .badge-regional {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #3b82f6;
    }
    .badge-local {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #f59e0b;
    }
    .badge-other {
        background: #f3e8ff;
        color: #6b21a8;
        border: 1px solid #8b5cf6;
    }

    /* Circle Action Buttons */
    .btn-circle {
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        margin: 0 4px;
    }
    .btn-edit {
        background: #ff9800;
        color: white;
    }
    .btn-edit:hover {
        transform: translateY(-2px) rotate(15deg);
        box-shadow: 0 4px 12px rgba(255, 152, 0, 0.4);
        color: white;
    }
    .btn-view {
        background: #3b82f6;
        color: white;
    }
    .btn-view:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        color: white;
    }

    /* Bulk Actions Bar */
    .bulk-actions-bar {
        display: none;
        background: white;
        padding: 15px 25px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
        align-items: center;
        justify-content: space-between;
        border-left: 5px solid #667eea;
        animation: slideDown 0.3s ease;
    }
    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    /* Pagination */
    .pagination {
        margin-top: 24px;
        gap: 8px;
    }
    .page-link {
        border: none;
        border-radius: 10px !important;
        padding: 10px 18px;
        color: #64748b;
        font-weight: 600;
        transition: all 0.2s;
    }
    .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    /* Checkbox */
    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }

    /* Mobile Cards */
    .award-card {
        display: none;
        background: white;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .award-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 12px;
        margin-bottom: 12px;
    }

    @media (max-width: 768px) {
        .table-container { display: none; }
        .award-card { display: block; }
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
        width: 150px;
        height: 150px;
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
                    <i class="bi bi-trophy-fill"></i>
                    Awards Management
                    <span class="badge"><?php echo $awards_count; ?> Awards</span>
                </h2>
                <p class="subtitle">Manage association awards and recognitions</p>
            </div>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" style="padding: 14px 28px; border-radius: 12px; font-weight: 600; border: 2px solid rgba(255,255,255,0.3);">
                        <i class="bi bi-download me-2"></i>Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2">
                        <li><a class="dropdown-item" href="export_selected_awards.php?ids=all&format=csv&search=<?=urlencode($search)?>&category=<?=urlencode($category_filter)?>&year=<?=$year_filter?>&awarding_body=<?=urlencode($body_filter)?>"><i class="bi bi-filetype-csv me-2 text-primary"></i>Export CSV</a></li>
                        <li><a class="dropdown-item" href="export_selected_awards.php?ids=all&format=excel&search=<?=urlencode($search)?>&category=<?=urlencode($category_filter)?>&year=<?=$year_filter?>&awarding_body=<?=urlencode($body_filter)?>"><i class="bi bi-file-earmark-excel me-2 text-success"></i>Export Excel</a></li>
                        <li><a class="dropdown-item" href="export_selected_awards.php?ids=all&format=pdf&search=<?=urlencode($search)?>&category=<?=urlencode($category_filter)?>&year=<?=$year_filter?>&awarding_body=<?=urlencode($body_filter)?>"><i class="bi bi-filetype-pdf me-2 text-danger"></i>Export PDF</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="export_selected_awards.php?ids=all&format=print&search=<?=urlencode($search)?>&category=<?=urlencode($category_filter)?>&year=<?=$year_filter?>&awarding_body=<?=urlencode($body_filter)?>" target="_blank"><i class="bi bi-printer me-2 text-secondary"></i>Print Current List</a></li>
                    </ul>
                </div>
                <button class="btn-add-award" data-bs-toggle="modal" data-bs-target="#addAwardModal">
                    <i class="bi bi-plus-circle-fill"></i>
                    Add Award
                </button>
            </div>

        </div>
    </div>

    <!-- Advanced Filter Section -->
    <div class="filter-section">
        <form method="GET" id="filterForm" class="row g-3">
            <div class="col-md-3">
                <label class="form-label-sm">Search</label>
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" placeholder="Search title, body, or info..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                </div>
            </div>
            
            <div class="col-md-2">
                <label class="form-label-sm">Category</label>
                <select name="category" class="form-select filter-select">
                    <option value="">All Categories</option>
                    <option value="National" <?= $category_filter === 'National' ? 'selected' : '' ?>>National</option>
                    <option value="Regional" <?= $category_filter === 'Regional' ? 'selected' : '' ?>>Regional</option>
                    <option value="Local" <?= $category_filter === 'Local' ? 'selected' : '' ?>>Local</option>
                    <option value="Other" <?= $category_filter === 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label-sm">Year</label>
                <select name="year" class="form-select filter-select">
                    <option value="">All Years</option>
                    <?php 
                    $years_res = $conn->query("SELECT DISTINCT year_received FROM awards ORDER BY year_received DESC");
                    while($yr = $years_res->fetch_assoc()): ?>
                        <option value="<?= $yr['year_received'] ?>" <?= $year_filter == $yr['year_received'] ? 'selected' : '' ?>><?= $yr['year_received'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label-sm">Awarding Body</label>
                <select name="awarding_body" class="form-select filter-select">
                    <option value="">All Bodies</option>
                    <?php foreach($awarding_bodies as $body): ?>
                        <option value="<?= htmlspecialchars($body) ?>" <?= $body_filter === $body ? 'selected' : '' ?>><?= htmlspecialchars($body) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label-sm">Sort By</label>
                <select name="sort" class="form-select filter-select">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="title_a" <?= $sort === 'title_a' ? 'selected' : '' ?>>Title (A-Z)</option>
                    <option value="title_z" <?= $sort === 'title_z' ? 'selected' : '' ?>>Title (Z-A)</option>
                    <option value="category" <?= $sort === 'category' ? 'selected' : '' ?>>Category</option>
                </select>
            </div>

            <div class="col-md-1 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100" style="padding: 12px; background: #667eea; border: none; border-radius: 12px; font-weight: 600;">
                    <i class="bi bi-funnel"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Bulk Actions Bar -->
    <div id="bulkActionsBar" class="bulk-actions-bar">
        <div class="d-flex align-items-center gap-3">
            <span class="fw-bold text-primary" id="selectedCount">0 Awards Selected</span>
            <div class="vr"></div>
            <button class="btn btn-warning btn-sm rounded-pill px-3 text-white" onclick="bulkArchive()">
                <i class="bi bi-archive-fill me-2"></i>Archive Selected
            </button>
            <div class="dropdown">
                <button class="btn btn-outline-primary btn-sm rounded-pill px-3 dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-2"></i>Export Selected
                </button>
                <ul class="dropdown-menu shadow border-0 rounded-4 mt-2">
                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkExport('csv')"><i class="bi bi-filetype-csv me-2 text-primary"></i>CSV Format</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkExport('excel')"><i class="bi bi-file-earmark-excel me-2 text-success"></i>Excel Format</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkExport('pdf')"><i class="bi bi-filetype-pdf me-2 text-danger"></i>PDF Format</a></li>
                </ul>
            </div>
        </div>
        <button class="btn-close" onclick="deselectAll()"></button>
    </div>


    <!-- Awards Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table align-middle" id="awardsTable">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th style="width: 80px;">Image</th>
                        <th>Award Title</th>
                        <th>Awarding Body</th>
                        <th class="text-center">Category</th>
                        <th class="text-center">Year</th>
                        <th class="text-center">Date Received</th>
                        <th class="text-center" style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input row-checkbox" value="<?= $row['award_id'] ?>">
                            </td>
                            <td>
                                <?php if (!empty($row['award_image'])): ?>
                                    <img src="<?= $uploadDir . htmlspecialchars($row['award_image']) ?>" 
                                         alt="Award" class="award-thumb view-award" 
                                         data-id="<?= $row['award_id'] ?>"
                                         data-title="<?= htmlspecialchars($row['award_title']) ?>"
                                         data-body="<?= htmlspecialchars($row['awarding_body']) ?>"
                                         data-category="<?= htmlspecialchars($row['category']) ?>"
                                         data-year="<?= htmlspecialchars($row['year_received']) ?>"
                                         data-date="<?= date('M d, Y', strtotime($row['date_received'])) ?>"
                                         data-desc="<?= htmlspecialchars($row['description']) ?>"
                                         data-image="<?= $uploadDir . htmlspecialchars($row['award_image']) ?>"
                                         style="cursor: pointer;">
                                <?php else: ?>
                                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;"
                                         class="view-award"
                                         data-title="<?= htmlspecialchars($row['award_title']) ?>"
                                         data-body="<?= htmlspecialchars($row['awarding_body']) ?>"
                                         data-category="<?= htmlspecialchars($row['category']) ?>"
                                         data-year="<?= htmlspecialchars($row['year_received']) ?>"
                                         data-date="<?= date('M d, Y', strtotime($row['date_received'])) ?>"
                                         data-desc="<?= htmlspecialchars($row['description']) ?>"
                                         data-image="">
                                        <i class="bi bi-trophy text-white" style="font-size: 24px;"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="javascript:void(0)" class="text-decoration-none text-dark fw-bold view-award"
                                   data-title="<?= htmlspecialchars($row['award_title']) ?>"
                                   data-body="<?= htmlspecialchars($row['awarding_body']) ?>"
                                   data-category="<?= htmlspecialchars($row['category']) ?>"
                                   data-year="<?= htmlspecialchars($row['year_received']) ?>"
                                   data-date="<?= date('M d, Y', strtotime($row['date_received'])) ?>"
                                   data-desc="<?= htmlspecialchars($row['description']) ?>"
                                   data-image="<?= !empty($row['award_image']) ? $uploadDir . htmlspecialchars($row['award_image']) : '' ?>">
                                    <?= htmlspecialchars($row['award_title']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($row['awarding_body']) ?></td>
                            <td class="text-center">
                                <span class="badge-category badge-<?= strtolower($row['category']) ?>">
                                    <?= htmlspecialchars($row['category']) ?>
                                </span>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($row['year_received']) ?></td>
                            <td class="text-center">
                                <small class="text-muted d-block" style="font-size: 11px;">
                                    <?= date('M d, Y', strtotime($row['created_at'])) ?><br>
                                    <?= date('h:i A', strtotime($row['created_at'])) ?>
                                </small>
                            </td>
                            <td class="text-center">

                                <div class="d-flex justify-content-center">
                                    <button 
                                        class="btn-circle btn-view view-award"
                                        data-title="<?= htmlspecialchars($row['award_title']) ?>"
                                        data-body="<?= htmlspecialchars($row['awarding_body']) ?>"
                                        data-category="<?= htmlspecialchars($row['category']) ?>"
                                        data-year="<?= htmlspecialchars($row['year_received']) ?>"
                                        data-date="<?= date('M d, Y', strtotime($row['date_received'])) ?>"
                                        data-desc="<?= htmlspecialchars($row['description']) ?>"
                                        data-image="<?= !empty($row['award_image']) ? $uploadDir . htmlspecialchars($row['award_image']) : '' ?>"
                                    ><i class="bi bi-eye"></i></button>
                                    <button 
                                        class="btn-circle btn-edit editBtn"
                                        data-id="<?= $row['award_id'] ?>"
                                        data-title="<?= htmlspecialchars($row['award_title']) ?>"
                                        data-body="<?= htmlspecialchars($row['awarding_body']) ?>"
                                        data-category="<?= htmlspecialchars($row['category']) ?>"
                                        data-year="<?= htmlspecialchars($row['year_received']) ?>"
                                        data-date="<?= htmlspecialchars($row['date_received']) ?>"
                                        data-description="<?= htmlspecialchars($row['description']) ?>"
                                        data-image="<?= htmlspecialchars($row['award_image']) ?>"
                                        data-cert="<?= htmlspecialchars($row['certificate_file']) ?>"
                                    ><i class="bi bi-pencil"></i></button>
                                    <button class="btn-circle btn-archive archive-btn" data-id="<?= $row['award_id'] ?>" style="background: #f59e0b; color: white;">
                                        <i class="bi bi-archive"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-trophy"></i>
                                <h5>No Awards Found</h5>
                                <p>Try adjusting your filters or add a new award</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination UI -->
        <?php if ($total_pages > 1): ?>
        <nav class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted small">
                Showing <?= $offset + 1 ?> to <?= min($offset + $per_page, $total_records) ?> of <?= $total_records ?> awards
            </div>
            <ul class="pagination mb-0">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&sort=<?= $sort ?>&year=<?= $year_filter ?>&awarding_body=<?= urlencode($body_filter) ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 1 && $i <= $page + 1)): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&sort=<?= $sort ?>&year=<?= $year_filter ?>&awarding_body=<?= urlencode($body_filter) ?>"><?= $i ?></a>
                        </li>
                    <?php elseif ($i == $page - 2 || $i == $page + 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&sort=<?= $sort ?>&year=<?= $year_filter ?>&awarding_body=<?= urlencode($body_filter) ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Mobile Responsive Cards -->
    <div class="mobile-cards d-md-none mt-4">
        <?php 
        $result->data_seek(0); // Reset result pointer
        while ($row = $result->fetch_assoc()): 
        ?>
            <div class="award-card">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <input type="checkbox" class="form-check-input row-checkbox" value="<?= $row['award_id'] ?>">
                    <span class="badge-category badge-<?= strtolower($row['category']) ?>">
                        <?= htmlspecialchars($row['category']) ?>
                    </span>
                </div>
                <?php if (!empty($row['award_image'])): ?>
                    <img src="<?= $uploadDir . htmlspecialchars($row['award_image']) ?>" alt="Award">
                <?php endif; ?>
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($row['award_title']) ?></h5>
                <p class="text-muted small mb-2"><i class="bi bi-building me-1"></i><?= htmlspecialchars($row['awarding_body']) ?></p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-primary fw-bold"><i class="bi bi-calendar-event me-1"></i><?= htmlspecialchars($row['year_received']) ?></span>
                    <span class="text-muted small"><?= date('M d, Y', strtotime($row['date_received'])) ?></span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm flex-grow-1 view-award"
                            data-title="<?= htmlspecialchars($row['award_title']) ?>"
                            data-body="<?= htmlspecialchars($row['awarding_body']) ?>"
                            data-category="<?= htmlspecialchars($row['category']) ?>"
                            data-year="<?= htmlspecialchars($row['year_received']) ?>"
                            data-date="<?= date('M d, Y', strtotime($row['date_received'])) ?>"
                            data-desc="<?= htmlspecialchars($row['description']) ?>"
                            data-image="<?= !empty($row['award_image']) ? $uploadDir . htmlspecialchars($row['award_image']) : '' ?>">View</button>
                    <button class="btn btn-warning btn-sm editBtn" 
                            data-id="<?= $row['award_id'] ?>"
                            data-title="<?= htmlspecialchars($row['award_title']) ?>"
                            data-body="<?= htmlspecialchars($row['awarding_body']) ?>"
                            data-category="<?= htmlspecialchars($row['category']) ?>"
                            data-year="<?= htmlspecialchars($row['year_received']) ?>"
                            data-date="<?= htmlspecialchars($row['date_received']) ?>"
                            data-description="<?= htmlspecialchars($row['description']) ?>"
                            data-image="<?= htmlspecialchars($row['award_image']) ?>"
                            data-cert="<?= htmlspecialchars($row['certificate_file']) ?>"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-warning btn-sm archive-btn text-white" data-id="<?= $row['award_id'] ?>"><i class="bi bi-archive"></i></button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

</div>

<!-- Add Award Modal -->
<div class="modal fade" id="addAwardModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            <i class="bi bi-plus-circle-fill me-2"></i>
            Add New Award
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="add_award" value="1">
        <div class="modal-body">
          <div class="row g-4">
            <div class="col-md-8">
              <label class="form-label"><i class="bi bi-trophy me-2"></i>Award Title <span class="text-danger">*</span></label>
              <input type="text" name="award_title" class="form-control" required placeholder="Enter award title">
            </div>
            <div class="col-md-4">
              <label class="form-label"><i class="bi bi-tag me-2"></i>Category <span class="text-danger">*</span></label>
              <select name="category" class="form-select" required>
                <option value="">-- Select Category --</option>
                <option value="National">National</option>
                <option value="Regional">Regional</option>
                <option value="Local">Local</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label"><i class="bi bi-building me-2"></i>Awarding Body <span class="text-danger">*</span></label>
              <input type="text" name="awarding_body" class="form-control" required placeholder="e.g., Bureau of Fisheries and Aquatic Resources">
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-calendar-event me-2"></i>Year Received <span class="text-danger">*</span></label>
              <input type="number" name="year_received" class="form-control" required 
                     min="1900" max="<?= date('Y') ?>" placeholder="<?= date('Y') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-calendar-check me-2"></i>Date Received <span class="text-danger">*</span></label>
              <input type="date" name="date_received" class="form-control" required max="<?= date('Y-m-d') ?>">
            </div>

            <div class="col-12">
              <label class="form-label"><i class="bi bi-text-paragraph me-2"></i>Description</label>
              <textarea name="description" class="form-control" rows="4" 
                        placeholder="Describe the award and its significance..."></textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-image me-2"></i>Award Image</label>
              <input type="file" name="award_image" id="add_image" class="form-control" accept="image/*">
              <small class="text-muted">JPG, PNG, GIF, WEBP (Max 5MB)</small>
              <img id="add_preview" src="" alt="Preview" class="img-preview d-none">
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-file-earmark-pdf me-2"></i>Certificate File</label>
              <input type="file" name="certificate_file" id="add_cert" class="form-control" accept="image/*,application/pdf">
              <small class="text-muted">JPG, PNG, PDF (Max 5MB)</small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-submit">
            <i class="bi bi-check-circle-fill me-2"></i>
            Add Award
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Award View Modal -->
<div class="modal fade" id="viewAwardModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-0">
        <div class="row g-4">
            <div class="col-md-5">
                <div class="rounded-4 overflow-hidden shadow-sm h-100" style="background: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 300px;">
                    <img id="view_image" src="" alt="Award Image" style="width: 100%; height: 100%; object-fit: contain;">
                    <div id="view_no_image" class="text-muted d-none">
                        <i class="bi bi-image" style="font-size: 64px;"></i>
                        <p>No image available</p>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="mb-3">
                    <span id="view_category" class="badge-category mb-2 d-inline-block"></span>
                    <h2 id="view_title" class="fw-bold text-dark mb-1"></h2>
                    <p id="view_body" class="text-muted fs-5 mb-0"></p>
                </div>
                
                <hr class="my-4" style="opacity: 0.1;">
                
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 rounded-3 bg-light">
                            <small class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 11px;">Year Received</small>
                            <span id="view_year" class="fw-bold fs-5"></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3 bg-light">
                            <small class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 11px;">Date Received</small>
                            <span id="view_date" class="fw-bold fs-5"></span>
                        </div>
                    </div>
                </div>

                <div class="mb-0">
                    <small class="text-uppercase text-muted fw-bold d-block mb-2" style="font-size: 11px;">Description / Notes</small>
                    <p id="view_desc" class="text-secondary lh-base" style="font-size: 15px;"></p>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editAwardModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            <i class="bi bi-pencil-square me-2"></i>
            Edit Award Information
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="edit_award" value="1">
        <input type="hidden" name="award_id" id="edit_award_id">
        <div class="modal-body">
          <div class="row g-4">
            <div class="col-md-8">
              <label class="form-label"><i class="bi bi-trophy me-2"></i>Award Title <span class="text-danger">*</span></label>
              <input type="text" name="award_title" id="edit_title" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label"><i class="bi bi-tag me-2"></i>Category <span class="text-danger">*</span></label>
              <select name="category" id="edit_category" class="form-select" required>
                <option value="">-- Select Category --</option>
                <option value="National">National</option>
                <option value="Regional">Regional</option>
                <option value="Local">Local</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label"><i class="bi bi-building me-2"></i>Awarding Body <span class="text-danger">*</span></label>
              <input type="text" name="awarding_body" id="edit_body" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-calendar-event me-2"></i>Year Received <span class="text-danger">*</span></label>
              <input type="number" name="year_received" id="edit_year" class="form-control" required 
                     min="1900" max="<?= date('Y') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-calendar-check me-2"></i>Date Received <span class="text-danger">*</span></label>
              <input type="date" name="date_received" id="edit_date" class="form-control" required max="<?= date('Y-m-d') ?>">
            </div>

            <div class="col-12">
              <label class="form-label"><i class="bi bi-text-paragraph me-2"></i>Description</label>
              <textarea name="description" id="edit_description" class="form-control" rows="4"></textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-image me-2"></i>Update Award Image</label>
              <input type="file" name="award_image" id="edit_image" class="form-control" accept="image/*">
              <small class="text-muted">Leave empty to keep current image</small>
              <img id="edit_preview" src="" alt="Preview" class="img-preview d-none">
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-file-earmark-pdf me-2"></i>Update Certificate File</label>
              <input type="file" name="certificate_file" id="edit_cert" class="form-control" accept="image/*,application/pdf">
              <small class="text-muted">Leave empty to keep current certificate</small>
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
// Modal instances
const viewModal = new bootstrap.Modal(document.getElementById('viewAwardModal'));
const editModal = new bootstrap.Modal(document.getElementById('editAwardModal'));

// Fill and open View modal
document.querySelectorAll('.view-award').forEach(el => {
    el.addEventListener('click', (e) => {
        e.stopPropagation();
        const data = el.dataset;
        document.getElementById('view_title').innerText = data.title;
        document.getElementById('view_body').innerText = data.body;
        document.getElementById('view_year').innerText = data.year;
        document.getElementById('view_date').innerText = data.date;
        document.getElementById('view_desc').innerText = data.desc || 'No description provided.';
        
        const catEl = document.getElementById('view_category');
        catEl.innerText = data.category;
        catEl.className = `badge-category badge-${data.category.toLowerCase()}`;

        const imgEl = document.getElementById('view_image');
        const noImgEl = document.getElementById('view_no_image');
        if (data.image) {
            imgEl.src = data.image;
            imgEl.classList.remove('d-none');
            noImgEl.classList.add('d-none');
        } else {
            imgEl.classList.add('d-none');
            noImgEl.classList.remove('d-none');
        }
        viewModal.show();
    });
});

// Fill and open Edit modal
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const data = btn.dataset;
        document.getElementById('edit_award_id').value = data.id;
        document.getElementById('edit_title').value = data.title;
        document.getElementById('edit_body').value = data.body;
        document.getElementById('edit_category').value = data.category;
        document.getElementById('edit_year').value = data.year;
        document.getElementById('edit_date').value = data.date;
        document.getElementById('edit_description').value = data.description;

        const imgPreview = document.getElementById('edit_preview');
        if (data.image) {
            imgPreview.src = "<?= $uploadDir ?>" + data.image;
            imgPreview.classList.remove('d-none');
        } else {
            imgPreview.classList.add('d-none');
            imgPreview.src = "";
        }

        document.getElementById('edit_image').value = "";
        document.getElementById('edit_cert').value = "";
        editModal.show();
    });
});

/* --------------------------
   ✅ BULK SELECTION LOGIC
-------------------------- */
const selectAll = document.getElementById('selectAll');
const rowCheckboxes = document.querySelectorAll('.row-checkbox');
const bulkBar = document.getElementById('bulkActionsBar');
const selectedCount = document.getElementById('selectedCount');

function updateBulkBar() {
    const checked = document.querySelectorAll('.row-checkbox:checked');
    if (checked.length > 0) {
        bulkBar.style.display = 'flex';
        selectedCount.innerText = `${checked.length} Award${checked.length > 1 ? 's' : ''} Selected`;
    } else {
        bulkBar.style.display = 'none';
        selectAll.checked = false;
    }
}

selectAll?.addEventListener('change', function() {
    rowCheckboxes.forEach(cb => cb.checked = this.checked);
    updateBulkBar();
});

rowCheckboxes.forEach(cb => {
    cb.addEventListener('change', updateBulkBar);
});

function deselectAll() {
    rowCheckboxes.forEach(cb => cb.checked = false);
    selectAll.checked = false;
    updateBulkBar();
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value).join(',');
}

function bulkArchive() {
    const ids = getSelectedIds();
    Swal.fire({
        title: 'Archive Selected Awards?',
        text: `You are about to move ${ids.split(',').length} awards to the archive.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        confirmButtonText: 'Yes, archive them!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `bulk_delete_awards.php?ids=${ids}&action=archive`;
        }
    });
}

function bulkExport(format) {
    const ids = getSelectedIds();
    if (!ids) {
        Swal.fire('No Selection', 'Please select at least one award to export.', 'info');
        return;
    }
    window.location.href = `export_selected_awards.php?ids=${ids}&format=${format}`;
}


// Preview image logic
['add_image', 'edit_image'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', function() {
        const [file] = this.files;
        const previewId = id === 'add_image' ? 'add_preview' : 'edit_preview';
        const preview = document.getElementById(previewId);
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        } else {
            preview.classList.add('d-none');
        }
    });
});

// Archive confirmation for single award
document.querySelectorAll('.archive-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const id = this.dataset.id;
        Swal.fire({
            title: 'Archive Award?',
            text: "Move this award to the archives.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            confirmButtonText: 'Yes, archive it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `?archive=${id}`;
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
    confirmButtonColor: '#667eea'
}).then(() => {
    if ('<?= $alertType ?>' === 'success') window.location.href = "awards.php";
});
<?php endif; ?>



<?php if (isset($_GET['archived'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Award Archived!',
    text: 'The award has been moved to the archives.',
    timer: 2500,
    showConfirmButton: false
}).then(() => { window.location.href = "awards.php"; });
<?php endif; ?>

<?php if (isset($_GET['bulk_archived'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Bulk Archived!',
    text: '<?= $_GET['bulk_archived'] ?> awards were moved to the archive.',
    timer: 3000,
    showConfirmButton: false
}).then(() => { window.location.href = "awards.php"; });
<?php endif; ?>
</script>

</script>
</body>
</html>
