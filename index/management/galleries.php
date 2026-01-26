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
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  /* Modern Layout */
  body { 
    background: #f9fafb; 
    font-family: 'Inter', 'Segoe UI', system-ui, Arial; 
    color: #333;
  }
  
  .content-wrapper { 
    margin-left: 270px; 
    padding: 32px; 
    min-height: 100vh; 
  }

  /* Page Header */
  .page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 32px;
    border-radius: 20px;
    margin-bottom: 32px;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.25);
    color: white;
  }

  .page-header h2 {
    font-weight: 700;
    font-size: 2rem;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .page-header h2 i {
    font-size: 2.5rem;
  }

  .page-header .meta {
    color: rgba(255,255,255,0.95);
    font-size: 1.05rem;
    margin: 0;
  }

  /* Search Bar */
  .search-container {
    position: relative;
  }

  .search-container input {
    border-radius: 12px;
    border: 2px solid rgba(255,255,255,0.3);
    padding: 10px 16px 10px 45px;
    background: rgba(255,255,255,0.15);
    color: white;
    backdrop-filter: blur(10px);
  }

  .search-container input::placeholder {
    color: rgba(255,255,255,0.7);
  }

  .search-container input:focus {
    background: rgba(255,255,255,0.25);
    border-color: rgba(255,255,255,0.5);
    outline: none;
    box-shadow: 0 0 0 4px rgba(255,255,255,0.1);
  }

  .search-container i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,0.8);
    font-size: 1.1rem;
  }

  .btn-search {
    background: rgba(255,255,255,0.25);
    border: 2px solid rgba(255,255,255,0.4);
    color: white;
    border-radius: 12px;
    padding: 10px 20px;
    font-weight: 600;
  }

  .btn-search:hover {
    background: rgba(255,255,255,0.35);
    border-color: rgba(255,255,255,0.6);
    color: white;
  }

  .btn-light {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 12px 28px;
    border-radius: 12px;
    font-weight: 600;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .btn-light:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(102, 126, 234, 0.4);
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    color: white;
  }

  /* Gallery Section */
  .gallery-section {
    background: white;
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 28px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    border: 1px solid #E8E8E8;
  }

  .gallery-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f0f0f0;
  }

  .gallery-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.4rem;
    font-weight: 700;
    color: #333;
    margin: 0;
  }

  .gallery-title i {
    font-size: 1.6rem;
    color: #f59e0b;
  }

  .image-count-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
  }

  .gallery-date {
    color: #999;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  /* Gallery Cards */
  .gallery-card { 
    border: none; 
    border-radius: 16px; 
    overflow: hidden; 
    box-shadow: 0 4px 16px rgba(0,0,0,0.08); 
    cursor: pointer; 
    background: #fff;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
  }

  .gallery-card:hover { 
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.15);
  }

  .gallery-card img { 
    height: 220px; 
    width: 100%; 
    object-fit: cover; 
    display: block;
    transition: all 0.4s ease;
  }

  .gallery-card:hover img {
    transform: scale(1.1);
  }

  .gallery-card .card-body {
    padding: 16px;
  }

  .image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(0deg, rgba(0,0,0,0.7) 0%, transparent 50%);
    opacity: 0;
    transition: all 0.3s ease;
    display: flex;
    align-items: flex-end;
    padding: 16px;
  }

  .gallery-card:hover .image-overlay {
    opacity: 1;
  }

  .overlay-info {
    color: white;
    font-size: 0.85rem;
    font-weight: 600;
  }

  /* Action Buttons */
  .btn-outline-primary {
    border: 2px solid #667eea;
    color: #667eea;
    border-radius: 10px;
    padding: 8px 16px;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .btn-outline-primary:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  }

  .btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border: none;
    border-radius: 10px;
    padding: 8px 16px;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
  }

  .btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    border-radius: 10px;
    padding: 10px 24px;
    font-weight: 600;
  }

  .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
  }

  .btn-secondary {
    background: #6c757d;
    border: none;
    border-radius: 10px;
    padding: 10px 24px;
    font-weight: 600;
  }

  /* Upload Box */
  .upload-box { 
    border: 3px dashed #667eea; 
    padding: 48px; 
    text-align: center; 
    cursor: pointer; 
    border-radius: 16px; 
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    transition: all 0.3s ease;
  }

  .upload-box:hover {
    border-color: #764ba2;
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    transform: scale(1.02);
  }

  .upload-box i {
    font-size: 3rem;
    color: #667eea;
    margin-bottom: 12px;
  }

  .upload-box p {
    margin: 0;
    font-weight: 600;
    color: #555;
    font-size: 1.05rem;
  }

  .upload-box small {
    color: #999;
    display: block;
    margin-top: 8px;
  }

  /* Preview Thumbnails */
  .thumb { 
    height: 120px; 
    width: 120px;
    object-fit: cover; 
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
  }

  .thumb:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
  }

  /* Modals */
  .modal-content {
    border-radius: 20px;
    border: none;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
  }

  .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px 20px 0 0;
    padding: 24px 32px;
    border: none;
  }

  .modal-header .btn-close {
    filter: brightness(0) invert(1);
  }

  .modal-body {
    padding: 32px;
  }

  .modal-title {
    font-weight: 700;
    font-size: 1.5rem;
  }

  /* Form Controls */
  .form-label {
    font-weight: 600;
    color: #555;
    margin-bottom: 8px;
  }

  .form-control {
    border-radius: 10px;
    border: 2px solid #E8E8E8;
    padding: 10px 16px;
    transition: all 0.3s ease;
  }

  .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
  }

  /* Carousel Controls */
  .carousel-control-prev,
  .carousel-control-next {
    width: 60px;
    height: 60px;
    background: rgba(102, 126, 234, 0.8);
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
  }

  .carousel-control-prev:hover,
  .carousel-control-next:hover {
    background: rgba(102, 126, 234, 1);
  }

  /* Alerts */
  .alert {
    border-radius: 12px;
    border: none;
    padding: 16px 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  .alert-success {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
  }

  .alert-danger {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 64px 32px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
  }

  .empty-state i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 16px;
  }

  .empty-state p {
    color: #999;
    font-size: 1.1rem;
    margin: 0;
  }

  /* Responsive */
  @media (max-width: 991.98px) { 
    .content-wrapper { 
      margin-left: 0; 
      padding: 20px; 
    }
    .page-header h2 {
      font-size: 1.5rem;
    }
    .gallery-card img {
      height: 180px;
    }
  }

  /* Animation */
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .gallery-section {
    animation: fadeInUp 0.5s ease-out;
  }
</style>
</head>
<body>

<?php include('../navbar.php'); ?>

<div class="content-wrapper">
  
  <!-- Page Header -->
  <div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h2><i class="bi bi-images"></i> Gallery Management</h2>
        <p class="meta">Upload, organize, and manage your photo galleries</p>
      </div>
      <div class="d-flex gap-2 align-items-center flex-wrap">
        <form class="d-flex search-container" method="GET" style="gap:.5rem;">
          <i class="bi bi-search"></i>
          <input name="q" class="form-control" type="search" placeholder="Search galleries..." value="<?= e($searchQ) ?>">
          <button class="btn btn-search" type="submit"><i class="bi bi-search"></i></button>
        </form>
        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalAdd">
          <i class="bi bi-plus-circle me-2"></i> New Gallery
        </button>
      </div>
    </div>
  </div>

  <?php if (!empty($galleries)): ?>
    <?php foreach ($galleries as $idx => $g):
      $allImages = $g['images'];
      $first = count($allImages) ? $uploadDirRel . e($allImages[0]) : '../images/default-event.jpg';
      $carouselId = 'carousel' . $g['id'];
      $imageCount = count($allImages);
    ?>
    <div class="gallery-section">
      <div class="gallery-header">
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <h5 class="gallery-title mb-0">
            <i class="bi bi-folder-fill"></i> 
            <?= e($g['title']) ?>
          </h5>
          <span class="image-count-badge">
            <i class="bi bi-images me-1"></i><?= $imageCount ?> <?= $imageCount === 1 ? 'Image' : 'Images' ?>
          </span>
          <span class="gallery-date">
            <i class="bi bi-calendar-event"></i>
            <?= date("M d, Y", strtotime($g['created_at'])) ?>
          </span>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $g['id'] ?>">
            <i class="bi bi-pencil me-1"></i> Edit
          </button>
          <form method="POST" class="d-inline" onsubmit="return confirm('Delete this entire gallery and all its images?');">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
            <button type="submit" class="btn btn-sm btn-danger">
              <i class="bi bi-trash me-1"></i> Delete
            </button>
          </form>
        </div>
      </div>

      <div class="row g-4">
        <?php foreach ($allImages as $i => $img): ?>
          <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="gallery-card" data-bs-toggle="modal" data-bs-target="#modalView<?= $g['id'] ?>">
              <img src="<?= $uploadDirRel . e($img) ?>" alt="<?= e($g['title']) ?>">
              <div class="image-overlay">
                <div class="overlay-info">
                  <i class="bi bi-eye me-2"></i>Click to view full size
                </div>
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
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Gallery</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">

                <div class="mb-4">
                  <label class="form-label">Gallery Title</label>
                  <input type="text" name="title" class="form-control" value="<?= e($g['title']) ?>" required>
                </div>

                <div class="mb-4">
                  <label class="form-label">Add More Images</label>
                  <div class="upload-box" onclick="document.getElementById('editFiles<?= $g['id'] ?>').click();">
                    <i class="bi bi-cloud-upload"></i>
                    <p>Click to add more images</p>
                    <small>JPEG, PNG, WEBP • Max 5MB each</small>
                  </div>
                  <input type="file" id="editFiles<?= $g['id'] ?>" name="images[]" class="d-none" accept="image/*" multiple>
                  <div id="previewEdit<?= $g['id'] ?>" class="mt-3 d-flex gap-2 flex-wrap"></div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle me-2"></i>Update Gallery
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

    </div>
    <?php endforeach; ?>

  <?php else: ?>
    <div class="empty-state">
      <i class="bi bi-images"></i>
      <p class="mb-3">No galleries yet. Create your first gallery to get started!</p>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdd">
        <i class="bi bi-plus-circle me-2"></i>Create First Gallery
      </button>
    </div>
  <?php endif; ?>

</div>

<!-- Add modal -->
<div class="modal fade" id="modalAdd" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Create New Gallery</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
          <input type="hidden" name="action" value="add">

          <div class="mb-4">
            <label class="form-label">Gallery Title</label>
            <input type="text" name="title" class="form-control" placeholder="Enter gallery name..." required>
          </div>

          <div class="mb-4">
            <label class="form-label">Upload Images</label>
            <div class="upload-box" onclick="document.getElementById('addFiles').click();">
              <i class="bi bi-cloud-upload"></i>
              <p>Click or drag & drop to upload images</p>
              <small>JPEG, PNG, WEBP • Max 5MB each • Multiple files supported</small>
            </div>
            <input type="file" id="addFiles" name="images[]" class="d-none" accept="image/*" multiple required>
            <div id="previewAdd" class="mt-3 d-flex gap-2 flex-wrap"></div>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">
              <i class="bi bi-check-circle me-2"></i>Create Gallery
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  // SweetAlert for success/error messages
  <?php if ($success): ?>
  Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: '<?= e($success) ?>',
    timer: 2000,
    showConfirmButton: false
  });
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
  Swal.fire({
    icon: 'error',
    title: 'Error!',
    html: '<?php foreach ($errors as $err) echo e($err) . "<br>"; ?>',
    confirmButtonText: 'OK'
  });
  <?php endif; ?>

  // File preview helper
  function setupPreview(inputId, previewId) {
    const inp = document.getElementById(inputId);
    const prev = document.getElementById(previewId);
    if (!inp || !prev) return;
    
    inp.addEventListener('change', function(e){
      prev.innerHTML = '';
      const files = [...e.target.files];
      
      if (files.length > 0) {
        files.forEach((f, idx) => {
          if (!f.type.startsWith('image/')) return;
          
          const wrapper = document.createElement('div');
          wrapper.className = 'position-relative';
          wrapper.style.display = 'inline-block';
          
          const r = new FileReader();
          r.onload = ev => {
            const img = document.createElement('img');
            img.src = ev.target.result;
            img.className = 'thumb';
            wrapper.appendChild(img);
            
            // Add remove button
            const removeBtn = document.createElement('button');
            removeBtn.className = 'btn btn-danger btn-sm position-absolute top-0 end-0 m-1';
            removeBtn.innerHTML = '<i class="bi bi-x"></i>';
            removeBtn.style.padding = '2px 6px';
            removeBtn.style.fontSize = '0.8rem';
            removeBtn.onclick = function(e) {
              e.preventDefault();
              wrapper.remove();
            };
            wrapper.appendChild(removeBtn);
          };
          r.readAsDataURL(f);
          prev.appendChild(wrapper);
        });
        
        // Show count
        const countBadge = document.createElement('div');
        countBadge.className = 'badge bg-primary mt-2';
        countBadge.textContent = files.length + ' image(s) selected';
        prev.appendChild(countBadge);
      }
    });
    
    // Drag & drop support
    const uploadBox = inp.closest('.modal-body')?.querySelector('.upload-box');
    if (uploadBox) {
      ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadBox.addEventListener(eventName, preventDefaults, false);
      });
      
      function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
      }
      
      ['dragenter', 'dragover'].forEach(eventName => {
        uploadBox.addEventListener(eventName, () => {
          uploadBox.style.borderColor = '#764ba2';
          uploadBox.style.backgroundColor = '#f0f0f0';
        });
      });
      
      ['dragleave', 'drop'].forEach(eventName => {
        uploadBox.addEventListener(eventName, () => {
          uploadBox.style.borderColor = '#667eea';
          uploadBox.style.backgroundColor = '';
        });
      });
      
      uploadBox.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        inp.files = files;
        inp.dispatchEvent(new Event('change'));
      });
    }
  }
  
  setupPreview('addFiles','previewAdd');
  <?php foreach ($galleries as $g): ?>
  setupPreview('editFiles<?= $g['id'] ?>','previewEdit<?= $g['id'] ?>');
  <?php endforeach; ?>

  // Close mobile nav automatically if open
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
