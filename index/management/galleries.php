<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once('../../config/db_connect.php');

$errors = [];
$success = '';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_token'];

// Paths
$uploadDirRel = '../../uploads/gallery/';
$uploadDir = realpath(__DIR__ . '/../../uploads') ? __DIR__ . '/../../uploads/gallery/' : __DIR__ . '/../../uploads/gallery/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Helpers
function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function saveUploadedImages($files, $dstDir, &$errors) {
    $allowed = ['image/jpeg','image/png','image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    $saved = [];

    foreach ($files['tmp_name'] as $i => $tmp) {
        if (empty($tmp)) continue;
        $name = basename($files['name'][$i]);
        $type = $files['type'][$i];
        $size = $files['size'][$i];

        if (!in_array($type, $allowed)) {
            $errors[] = "File '{$name}' has unsupported type.";
            continue;
        }
        if ($size > $maxSize) {
            $errors[] = "File '{$name}' exceeds 5MB limit.";
            continue;
        }

        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $target = rtrim($dstDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        if (move_uploaded_file($tmp, $target)) {
            $saved[] = $filename;
        } else {
            $errors[] = "Failed to upload '{$name}'.";
        }
    }
    return $saved;
}

// Validate POST & CSRF helper
function checkCsrf() {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        return false;
    }
    return true;
}

// -------------------------
// ADD GALLERY
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!checkCsrf()) { $errors[] = "Invalid session token."; }
    $title = trim($_POST['title'] ?? '');
    if ($title === '') $errors[] = "Gallery title is required.";

    if (empty($_FILES['images']['name'][0])) {
        $errors[] = "Please upload at least one image.";
    }

    if (empty($errors)) {
        $saved = saveUploadedImages($_FILES['images'], $uploadDir, $errors);
        if (!empty($saved)) {
            $imagesStr = implode(',', $saved);
            $stmt = $conn->prepare("INSERT INTO galleries (title, images, created_at) VALUES (?,?,NOW())");
            $stmt->bind_param("ss", $title, $imagesStr);
            if ($stmt->execute()) {
                $success = "Gallery created successfully.";
            } else {
                $errors[] = "Database error: " . $conn->error;
                // rollback uploaded files on DB failure
                foreach ($saved as $f) if (file_exists($uploadDir.$f)) unlink($uploadDir.$f);
            }
        } else {
            $errors[] = "No images were saved.";
        }
    }
}

// -------------------------
// DELETE GALLERY
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!checkCsrf()) { $errors[] = "Invalid session token."; }
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) $errors[] = "Invalid gallery id.";

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT images FROM galleries WHERE id = ?");
        $stmt->bind_param("i", $id); $stmt->execute(); $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $imgs = array_filter(array_map('trim', explode(',', $row['images'] ?? '')));
            foreach ($imgs as $img) {
                $path = $uploadDir . $img;
                if (file_exists($path)) @unlink($path);
            }
            $del = $conn->prepare("DELETE FROM galleries WHERE id = ?");
            $del->bind_param("i", $id);
            if ($del->execute()) $success = "Gallery deleted.";
            else $errors[] = "DB error: " . $conn->error;
        } else {
            $errors[] = "Gallery not found.";
        }
    }
}

// -------------------------
// EDIT GALLERY
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    if (!checkCsrf()) { $errors[] = "Invalid session token."; }
    $id = intval($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    if ($id <= 0) $errors[] = "Invalid gallery id.";
    if ($title === '') $errors[] = "Gallery title is required.";

    if (empty($errors)) {
        // fetch existing images
        $stmt = $conn->prepare("SELECT images FROM galleries WHERE id = ?");
        $stmt->bind_param("i", $id); $stmt->execute(); $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $oldImgs = $row ? array_filter(array_map('trim', explode(',', $row['images'] ?? ''))) : [];

        // Save new uploads
        $newSaved = [];
        if (!empty($_FILES['images']['name'][0])) {
            $newSaved = saveUploadedImages($_FILES['images'], $uploadDir, $errors);
        }

        $allImgs = array_values(array_filter(array_merge($oldImgs, $newSaved)));
        $imagesStr = implode(',', $allImgs);

        $u = $conn->prepare("UPDATE galleries SET title = ?, images = ? WHERE id = ?");
        $u->bind_param("ssi", $title, $imagesStr, $id);
        if ($u->execute()) $success = "Gallery updated.";
        else $errors[] = "DB error: " . $conn->error;
    }
}

// -------------------------
// SEARCH params
// -------------------------
$searchQ = trim($_GET['q'] ?? '');

// -------------------------
// FETCH GALLERIES (with search)
// -------------------------
$where = [];
$params = [];
$types = '';

if ($searchQ !== '') {
    $where[] = "(title LIKE ? OR images LIKE ?)";
    $params[] = "%{$searchQ}%";
    $params[] = "%{$searchQ}%";
    $types .= 'ss';
}

$sql = "SELECT * FROM galleries";
if (!empty($where)) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY created_at DESC";

$galleries = [];
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

if ($result && $result->num_rows > 0) {
    while ($r = $result->fetch_assoc()) {
        $imgs = array_filter(array_map('trim', explode(',', $r['images'] ?? '')));
        $galleries[] = [
            'id' => $r['id'],
            'title' => $r['title'],
            'images' => $imgs,
            'created_at' => $r['created_at']
        ];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Galleries — Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background:#f4f6f9; font-family:"Segoe UI",system-ui,Arial; }
  .content-wrapper { margin-left: 0; padding:30px; max-width:1200px; margin:30px auto; }
  .gallery-card { border:none; border-radius:12px; overflow:hidden; box-shadow:0 6px 18px rgba(6,12,34,0.06); cursor:pointer; background:#fff; }
  .gallery-card img { height:200px; width:100%; object-fit:cover; display:block; }
  .upload-box { border:2px dashed #e2e8f0; padding:28px; text-align:center; cursor:pointer; border-radius:10px; background:#fff; }
  .meta { color:#6b7280; font-size:.92rem; }
  .thumb { height:120px; object-fit:cover; border-radius:8px; }
  @media (min-width: 992px) { .content-wrapper { margin-left:260px; } }
</style>
</head>
<body>

<?php include('../navbar.php'); ?>

<div class="content-wrapper">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h2 class="fw-bold">📸 Galleries</h2>
      <p class="meta mb-0">Manage gallery collections — upload, edit, delete</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <form class="d-flex" method="GET" style="gap:.5rem;">
        <input name="q" class="form-control form-control-sm" type="search" placeholder="Search title or filename" value="<?= e($searchQ) ?>">
        <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
      </form>
      <button class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#modalAdd">
        <i class="bi bi-plus-circle me-1"></i> New Gallery
      </button>
    </div>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $err) echo '<div>'.e($err).'</div>'; ?>
    </div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?= e($success) ?></div>
  <?php endif; ?>

  <?php if (!empty($galleries)): ?>
    <?php foreach ($galleries as $idx => $g):
      $allImages = $g['images'];
      $first = count($allImages) ? $uploadDirRel . e($allImages[0]) : '../images/default-event.jpg';
      $carouselId = 'carousel' . $g['id'];
    ?>
    <div class="mb-4">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
          <h5 class="mb-0"><i class="bi bi-folder-fill text-warning me-1"></i> <?= e($g['title']) ?></h5>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $g['id'] ?>">
            <i class="bi bi-pencil"></i> Edit
          </button>
          <form method="POST" class="d-inline" onsubmit="return confirm('Delete this gallery?');">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
            <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Delete</button>
          </form>
        </div>
      </div>

      <div class="row g-3">
        <?php foreach ($allImages as $i => $img): ?>
          <div class="col-md-3 col-sm-4">
            <div class="card gallery-card" data-bs-toggle="modal" data-bs-target="#modalView<?= $g['id'] ?>">
              <img src="<?= $uploadDirRel . e($img) ?>" alt="<?= e($g['title']) ?>">
              <div class="card-body small">
                <div class="text-muted">Uploaded: <?= date("M d, Y", strtotime($g['created_at'])) ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- View modal (carousel) -->
      <div class="modal fade" id="modalView<?= $g['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"><?= e($g['title']) ?></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
              <div id="<?= $carouselId ?>" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                  <?php foreach ($allImages as $i => $img): ?>
                    <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                      <img src="<?= $uploadDirRel . e($img) ?>" class="d-block w-100" style="max-height:70vh;object-fit:contain;">
                    </div>
                  <?php endforeach; ?>
                </div>
                <?php if (count($allImages) > 1): ?>
                  <button class="carousel-control-prev" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit modal -->
      <div class="modal fade" id="modalEdit<?= $g['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content p-3">
            <h5>Edit Gallery</h5>
            <form method="POST" enctype="multipart/form-data">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <input type="hidden" name="action" value="edit">
              <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">

              <div class="mb-3">
                <label class="form-label">Gallery Title</label>
                <input type="text" name="title" class="form-control" value="<?= e($g['title']) ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Add More Images</label>
                <div class="upload-box" onclick="document.getElementById('editFiles<?= $g['id'] ?>').click();">Click to add images</div>
                <input type="file" id="editFiles<?= $g['id'] ?>" name="images[]" class="d-none" accept="image/*" multiple>
                <div id="previewEdit<?= $g['id'] ?>" class="mt-3 d-flex gap-2 flex-wrap"></div>
              </div>

              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Update</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
    <?php endforeach; ?>

  <?php else: ?>
    <div class="card p-4 text-center">
      <p class="mb-0">No galleries yet. Create one to get started.</p>
    </div>
  <?php endif; ?>

</div>

<!-- Add modal -->
<div class="modal fade" id="modalAdd" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content p-3">
      <h5>New Gallery</h5>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" value="add">

        <div class="mb-3">
          <label class="form-label">Gallery Title</label>
          <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Images</label>
          <div class="upload-box" onclick="document.getElementById('addFiles').click();">Click to add images (JPEG, PNG, WEBP)</div>
          <input type="file" id="addFiles" name="images[]" class="d-none" accept="image/*" multiple required>
          <div id="previewAdd" class="mt-3 d-flex gap-2 flex-wrap"></div>
        </div>

        <div class="d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  // File preview helper
  function setupPreview(inputId, previewId) {
    const inp = document.getElementById(inputId);
    const prev = document.getElementById(previewId);
    if (!inp || !prev) return;
    inp.addEventListener('change', function(e){
      prev.innerHTML = '';
      [...e.target.files].forEach(f => {
        if (!f.type.startsWith('image/')) return;
        const r = new FileReader();
        r.onload = ev => {
          const img = document.createElement('img');
          img.src = ev.target.result;
          img.className = 'thumb';
          img.style.width = '120px';
          img.style.height = '120px';
          img.style.objectFit = 'cover';
          prev.appendChild(img);
        };
        r.readAsDataURL(f);
      });
    });
  }
  setupPreview('addFiles','previewAdd');
  <?php foreach ($galleries as $g): ?>
  setupPreview('editFiles<?= $g['id'] ?>','previewEdit<?= $g['id'] ?>');
  <?php endforeach; ?>

  // Close mobile nav automatically if open (if using bootstrap collapse in this layout)
  document.querySelectorAll('.navbar-collapse .nav-link').forEach(a => {
    a.addEventListener('click', () => {
      const toggler = document.querySelector('.navbar-toggler');
      const collapse = document.querySelector('.navbar-collapse');
      if (toggler && window.getComputedStyle(toggler).display !== 'none' && collapse.classList.contains('show')) toggler.click();
    });
  });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
