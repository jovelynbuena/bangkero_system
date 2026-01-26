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
$sql = "
    SELECT 
        o.id, o.member_id, m.name AS member_name, o.role_id, r.role_name AS position, 
        o.term_start, o.term_end, o.image, o.description
    FROM officers o
    JOIN members m ON o.member_id = m.id
    JOIN officer_roles r ON o.role_id = r.id
    ORDER BY r.role_name ASC
";
$result = $conn->query($sql);

/* add explicit count (used for quick debug / display) */
$officers_count = ($result) ? $result->num_rows : 0;
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

        /* Officer Image */
        .officer-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .officer-img:hover {
            transform: scale(1.1);
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
                    <span class="badge"><?php echo $officers_count; ?> Active</span>
                </h2>
                <p class="mb-0 mt-2" style="opacity: 0.9;">Manage organization officers and their terms</p>
            </div>
            <button class="btn-add-officer" data-bs-toggle="modal" data-bs-target="#addOfficerModal">
                <i class="bi bi-plus-circle-fill"></i>
                Assign New Officer
            </button>
        </div>
    </div>

    <!-- Officers Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table align-middle text-center">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th style="width: 100px;">Photo</th>
                        <th>Member Name</th>
                        <th>Position</th>
                        <th>Term Start</th>
                        <th>Term End</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): $i=1; while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= $i++ ?></strong></td>
                        <td>
                            <?php if ($row['image']): ?>
                                <img src="../../uploads/officers/<?= htmlspecialchars($row['image']) ?>" class="officer-img" alt="Officer">
                            <?php else: ?>
                                <div style="width: 60px; height: 60px; border-radius: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 20px; margin: 0 auto;">
                                    <?= strtoupper(substr($row['member_name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($row['member_name']) ?></strong></td>
                        <td>
                            <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 13px;">
                                <?= htmlspecialchars($row['position']) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($row['term_start'])) ?></td>
                        <td><?= date('M d, Y', strtotime($row['term_end'])) ?></td>
                        <td>
                            <button 
                                class="btn btn-edit btn-sm editBtn me-2"
                                data-id="<?= $row['id'] ?>"
                                data-member="<?= htmlspecialchars($row['member_name']) ?>"
                                data-memberid="<?= $row['member_id'] ?>"
                                data-role="<?= $row['role_id'] ?>"
                                data-start="<?= $row['term_start'] ?>"
                                data-end="<?= $row['term_end'] ?>"
                                data-desc="<?= htmlspecialchars($row['description']) ?>"
                                data-image="<?= htmlspecialchars($row['image']) ?>"
                            ><i class="bi bi-pencil-square"></i></button>
                            <button class="btn btn-archive btn-sm" onclick="confirmArchive(<?= $row['id'] ?>)">
                                <i class="bi bi-archive"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h5>No Officers Assigned Yet</h5>
                                <p>Click "Assign New Officer" to get started</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
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
// confirm archive
function confirmArchive(id) {
    Swal.fire({
        title: 'Archive Officer?',
        text: 'This will move the officer to the archive.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff7043',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, archive'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'officerslist.php?archive=' + id;
        }
    });
}

// Fill and open Edit modal
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('edit_officer_id').value = btn.dataset.id;
        document.getElementById('edit_member_name').value = btn.dataset.member;
        // set role select
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

        // reset file input
        const fileInput = document.getElementById('edit_image');
        fileInput.value = "";
        // show modal
        new bootstrap.Modal(document.getElementById('editOfficerModal')).show();
    });
});

// preview new image when selecting file (add modal)
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

// preview new image when selecting file (edit modal)
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

// SweetAlert messages after server response
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
        window.location.href = "officerslist.php";
    <?php endif; ?>
});
<?php endif; ?>

<?php if (isset($_GET['archived'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Archived!',
    text: 'Officer moved to archive successfully.',
    timer: 2500,
    showConfirmButton: false,
    confirmButtonColor: '#667eea'
}).then(() => {
    window.location.href = "officerslist.php";
});
<?php endif; ?>
</script>
</body>
</html>
