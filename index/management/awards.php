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
   🗑️ DELETE HANDLER
-------------------------- */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id > 0) {
        // Get file names before deleting
        $getFiles = $conn->prepare("SELECT award_image, certificate_file FROM awards WHERE award_id = ?");
        $getFiles->bind_param("i", $id);
        $getFiles->execute();
        $getFiles->bind_result($img, $cert);
        $getFiles->fetch();
        $getFiles->close();

        // Delete from database
        $deleteStmt = $conn->prepare("DELETE FROM awards WHERE award_id = ?");
        $deleteStmt->bind_param("i", $id);
        
        if ($deleteStmt->execute()) {
            // Delete files from server
            if ($img && file_exists($uploadDir . $img)) {
                @unlink($uploadDir . $img);
            }
            if ($cert && file_exists($uploadDir . $cert)) {
                @unlink($uploadDir . $cert);
            }
            $deleteStmt->close();
            header("Location: awards.php?deleted=1");
            exit();
        } else {
            $deleteStmt->close();
            header("Location: awards.php?error=1");
            exit();
        }
    }
}

/* --------------------------
   📋 FETCH AWARDS
-------------------------- */
$sql = "SELECT * FROM awards ORDER BY year_received DESC, date_received DESC";
$result = $conn->query($sql);
$awards_count = $result ? $result->num_rows : 0;
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
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    .badge-regional {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }
    .badge-local {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    .badge-other {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
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
    .btn-edit {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        color: white;
    }
    .btn-delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    .btn-delete:hover {
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
            <button class="btn-add-award" data-bs-toggle="modal" data-bs-target="#addAwardModal">
                <i class="bi bi-plus-circle-fill"></i>
                Add Award
            </button>
        </div>
    </div>

    <!-- Awards Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;" class="text-center">#</th>
                        <th style="width: 80px;">Image</th>
                        <th>Award Title</th>
                        <th>Awarding Body</th>
                        <th class="text-center">Category</th>
                        <th class="text-center">Year</th>
                        <th class="text-center">Date Received</th>
                        <th class="text-center" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): $count = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center"><strong><?= $count++ ?></strong></td>
                            <td>
                                <?php if (!empty($row['award_image'])): ?>
                                    <img src="<?= $uploadDir . htmlspecialchars($row['award_image']) ?>" 
                                         alt="Award" class="award-thumb">
                                <?php else: ?>
                                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-trophy text-white" style="font-size: 24px;"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($row['award_title']) ?></strong></td>
                            <td><?= htmlspecialchars($row['awarding_body']) ?></td>
                            <td class="text-center">
                                <span class="badge-category badge-<?= strtolower($row['category']) ?>">
                                    <?= htmlspecialchars($row['category']) ?>
                                </span>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($row['year_received']) ?></td>
                            <td class="text-center"><?= date('M d, Y', strtotime($row['date_received'])) ?></td>
                            <td class="text-center">
                                <button 
                                    class="btn btn-edit btn-sm editBtn"
                                    data-id="<?= $row['award_id'] ?>"
                                    data-title="<?= htmlspecialchars($row['award_title']) ?>"
                                    data-body="<?= htmlspecialchars($row['awarding_body']) ?>"
                                    data-category="<?= htmlspecialchars($row['category']) ?>"
                                    data-year="<?= htmlspecialchars($row['year_received']) ?>"
                                    data-date="<?= htmlspecialchars($row['date_received']) ?>"
                                    data-description="<?= htmlspecialchars($row['description']) ?>"
                                    data-image="<?= htmlspecialchars($row['award_image']) ?>"
                                    data-cert="<?= htmlspecialchars($row['certificate_file']) ?>"
                                ><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-delete btn-sm delete-btn" data-id="<?= $row['award_id'] ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-trophy"></i>
                                <h5>No Awards Found</h5>
                                <p>Click "Add Award" to get started</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
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

<!-- Edit Award Modal -->
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
// Fill and open Edit modal
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('edit_award_id').value = btn.dataset.id;
        document.getElementById('edit_title').value = btn.dataset.title;
        document.getElementById('edit_body').value = btn.dataset.body;
        document.getElementById('edit_category').value = btn.dataset.category;
        document.getElementById('edit_year').value = btn.dataset.year;
        document.getElementById('edit_date').value = btn.dataset.date;
        document.getElementById('edit_description').value = btn.dataset.description;

        const imgPreview = document.getElementById('edit_preview');
        if (btn.dataset.image) {
            imgPreview.src = "<?= $uploadDir ?>" + btn.dataset.image;
            imgPreview.classList.remove('d-none');
        } else {
            imgPreview.classList.add('d-none');
            imgPreview.src = "";
        }

        // reset file inputs
        document.getElementById('edit_image').value = "";
        document.getElementById('edit_cert').value = "";
        
        // show modal
        new bootstrap.Modal(document.getElementById('editAwardModal')).show();
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

// Delete confirmation
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const awardId = this.getAttribute('data-id');

        Swal.fire({
            title: 'Delete Award?',
            text: "This action cannot be undone. All related files will be deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `?delete=${awardId}`;
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
        window.location.href = "awards.php";
    <?php endif; ?>
});
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Deleted!',
    text: 'Award deleted successfully.',
    timer: 2500,
    showConfirmButton: false,
    confirmButtonColor: '#667eea'
}).then(() => {
    window.location.href = "awards.php";
});
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
Swal.fire({
    icon: 'error',
    title: 'Error!',
    text: 'Failed to delete award. Please try again.',
    confirmButtonColor: '#ef4444'
}).then(() => {
    window.location.href = "awards.php";
});
<?php endif; ?>
</script>
</body>
</html>
