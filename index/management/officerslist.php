<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

$alertType = $alertMsg = "";

/* --------------------------
   ✅ ADD OFFICER HANDLER
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
    } else {
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

/* --------------------------
   ✏️ EDIT OFFICER HANDLER
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #fff; }
        .main-content { margin-left: 250px; padding: 32px; min-height: 100vh; }
        .officer-img { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; }
        .table thead th { background: #ff7043; color: #fff; font-weight: 600; }
        .btn-primary { background-color: #ff7043; border-color: #ff7043; }
        .btn-primary:hover { background-color: #00897b; border-color: #00897b; }
        .btn-danger { background-color: #ff7043; border-color: #ff7043; }
        .btn-danger:hover { background-color: #e65100; border-color: #e65100; }
        .btn-warning { background-color: #4fc3f7; border-color: #4fc3f7; color: #01579b; }
        .btn-warning:hover { background-color: #0288d1; color: #fff; }
        .img-preview { width: 80px; height: 80px; border-radius: 10px; object-fit: cover; margin-top: 5px; }
        @media (max-width: 991.98px) { .main-content { margin-left: 0; padding: 16px; } }
    </style>
</head>
<body>
<?php include('../navbar.php'); ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOfficerModal">+ Assign New Officer</button>
        <h2 class="fw-bold mb-0 text-center flex-grow-1">Admin Panel — Officers List</h2>
    </div>

    <div class="table-responsive shadow-sm rounded-4">
        <table class="table table-bordered table-hover align-middle text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Photo</th>
                    <th>Member Name</th>
                    <th>Position</th>
                    <th>Term Start</th>
                    <th>Term End</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): $i=1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td>
                        <?php if ($row['image']): ?>
                            <img src="../../uploads/officers/<?= htmlspecialchars($row['image']) ?>" class="officer-img" alt="Officer">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/60x60?text=No+Image" class="officer-img" alt="No image">
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['member_name']) ?></td>
                    <td><?= htmlspecialchars($row['position']) ?></td>
                    <td><?= htmlspecialchars($row['term_start']) ?></td>
                    <td><?= htmlspecialchars($row['term_end']) ?></td>
                    <td>
                        <button 
                            class="btn btn-warning btn-sm editBtn"
                            data-id="<?= $row['id'] ?>"
                            data-member="<?= htmlspecialchars($row['member_name']) ?>"
                            data-memberid="<?= $row['member_id'] ?>"
                            data-role="<?= $row['role_id'] ?>"
                            data-start="<?= $row['term_start'] ?>"
                            data-end="<?= $row['term_end'] ?>"
                            data-desc="<?= htmlspecialchars($row['description']) ?>"
                            data-image="<?= htmlspecialchars($row['image']) ?>"
                        >Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="confirmArchive(<?= $row['id'] ?>)">Archive</button>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="7" class="text-muted">No officers assigned yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Officer Modal (inline so button always works) -->
<div class="modal fade" id="addOfficerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content rounded-4">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title fw-bold">Assign New Officer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="add_officer" value="1">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Select Member</label>
              <select name="member_id" class="form-select" required>
                <option value="">-- Select Member --</option>
                <?php $membersResult->data_seek(0); while ($m = $membersResult->fetch_assoc()): ?>
                  <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Position</label>
              <select name="role_id" class="form-select" required>
                <option value="">-- Select Position --</option>
                <?php $rolesResult->data_seek(0); while ($r = $rolesResult->fetch_assoc()): ?>
                  <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Term Start</label>
              <input type="date" name="term_start" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Term End</label>
              <input type="date" name="term_end" class="form-control" required>
            </div>

            <div class="col-12">
              <label class="form-label">Photo (optional)</label>
              <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.gif">
            </div>

            <div class="col-12">
              <label class="form-label">Description (optional)</label>
              <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save Officer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Officer Modal -->
<div class="modal fade" id="editOfficerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content rounded-4">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title fw-bold">Edit Officer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="edit_officer" value="1">
        <input type="hidden" name="officer_id" id="edit_officer_id">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Member</label>
              <input type="text" id="edit_member_name" class="form-control" readonly>
            </div>

            <div class="col-md-6">
              <label class="form-label">Position</label>
              <select name="role_id" id="edit_role_id" class="form-select" required>
                <option value="">-- Select Position --</option>
                <?php $rolesResult->data_seek(0); while ($r = $rolesResult->fetch_assoc()): ?>
                  <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Term Start</label>
              <input type="date" name="term_start" id="edit_term_start" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Term End</label>
              <input type="date" name="term_end" id="edit_term_end" class="form-control" required>
            </div>

            <div class="col-12">
              <label class="form-label">Photo (optional)</label>
              <input type="file" name="image" id="edit_image" class="form-control" accept=".jpg,.jpeg,.png,.gif">
              <img id="edit_preview" src="" alt="Preview" class="img-preview d-none">
            </div>

            <div class="col-12">
              <label class="form-label">Description (optional)</label>
              <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save Changes</button>
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
    title: '<?= ucfirst($alertType) ?>',
    text: '<?= addslashes($alertMsg) ?>',
    confirmButtonColor: '<?= $alertType === "success" ? "#43a047" : "#e53935" ?>'
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
    timer: 2000,
    showConfirmButton: false
});
<?php endif; ?>
</script>
</body>
</html>
