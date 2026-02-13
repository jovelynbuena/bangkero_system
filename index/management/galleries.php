<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once('../../config/db_connect.php');

$errors = [];
$success = $_GET['success'] ?? '';
$error_get = $_GET['error'] ?? '';
if ($error_get) $errors[] = $error_get;

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
    $category = trim($_POST['category'] ?? '');
    if ($title === '') $errors[] = "Gallery title is required.";
    if ($category === '') $errors[] = "Category is required.";

    if (empty($_FILES['images']['name'][0])) {
        $errors[] = "Please upload at least one image.";
    }

    if (empty($errors)) {
        $saved = saveUploadedImages($_FILES['images'], $uploadDir, $errors);
        if (!empty($saved)) {
            $imagesStr = implode(',', $saved);
            $stmt = $conn->prepare("INSERT INTO galleries (title, category, images, created_at) VALUES (?,?,?,NOW())");
            $stmt->bind_param("sss", $title, $category, $imagesStr);
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
// ARCHIVE GALLERY
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'archive') {
    if (!checkCsrf()) { $errors[] = "Invalid session token."; }
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) $errors[] = "Invalid gallery id.";

    if (empty($errors)) {
        try {
            $conn->begin_transaction();

            // Copy to archive
            $stmt = $conn->prepare("INSERT INTO galleries_archive (gallery_id, title, category, images, original_created_at) SELECT id, title, category, images, created_at FROM galleries WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            // Delete from main table
            $stmt = $conn->prepare("DELETE FROM galleries WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $success = "Gallery moved to archive successfully.";
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Failed to archive: " . $e->getMessage();
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
    $category = trim($_POST['category'] ?? '');
    if ($id <= 0) $errors[] = "Invalid gallery id.";
    if ($title === '') $errors[] = "Gallery title is required.";
    if ($category === '') $errors[] = "Category is required.";

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

        $u = $conn->prepare("UPDATE galleries SET title = ?, category = ?, images = ? WHERE id = ?");
        $u->bind_param("sssi", $title, $category, $imagesStr, $id);
        if ($u->execute()) $success = "Gallery updated.";
        else $errors[] = "DB error: " . $conn->error;
    }
}

// -------------------------
// SEARCH & FILTER params
// -------------------------
$searchQ = trim($_GET['q'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$sort = $_GET['sort'] ?? 'date_desc';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// -------------------------
// FETCH GALLERIES (with search, category, and sort)
// -------------------------
$where = [];
$params = [];
$types = '';

if ($searchQ !== '') {
    $where[] = "(title LIKE ? OR category LIKE ?)";
    $search_param = "%{$searchQ}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if ($categoryFilter !== '') {
    $where[] = "category = ?";
    $params[] = $categoryFilter;
    $types .= 's';
}

if ($date_from !== '') {
    $where[] = "DATE(created_at) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if ($date_to !== '') {
    $where[] = "DATE(created_at) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

$where_sql = !empty($where) ? " WHERE " . implode(' AND ', $where) : "";

// Get total for pagination
$count_sql = "SELECT COUNT(*) as total FROM galleries" . $where_sql;
if (!empty($params)) {
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total_records = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_records = $conn->query($count_sql)->fetch_assoc()['total'];
}
$total_pages = ceil($total_records / $limit);

$sql = "SELECT * FROM galleries" . $where_sql;

// Apply sorting
switch ($sort) {
    case 'date_asc':
        $sql .= " ORDER BY created_at ASC";
        break;
    case 'title_asc':
        $sql .= " ORDER BY title ASC";
        break;
    case 'title_desc':
        $sql .= " ORDER BY title DESC";
        break;
    case 'date_desc':
    default:
        $sql .= " ORDER BY created_at DESC";
        break;
}

$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$galleries = [];
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($r = $result->fetch_assoc()) {
        $imgs = array_filter(array_map('trim', explode(',', $r['images'] ?? '')));
        $galleries[] = [
            'id' => $r['id'],
            'title' => $r['title'],
            'category' => $r['category'] ?? 'Uncategorized',
            'images' => $imgs,
            'created_at' => $r['created_at']
        ];
    }
}

// Get all available categories
$categories_result = $conn->query("SELECT DISTINCT category FROM galleries WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
$available_categories = [];
if ($categories_result && $categories_result->num_rows > 0) {
    while ($cat_row = $categories_result->fetch_assoc()) {
        $available_categories[] = $cat_row['category'];
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

  /* Filter Section */
  .filter-section {
    background: white;
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    margin-bottom: 32px;
    border: 1px solid #E8E8E8;
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

  /* Bulk Action Bar */
  .bulk-action-bar {
    position: sticky;
    top: 20px;
    z-index: 1000;
    background: white;
    padding: 16px 24px;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: 1px solid #E8E8E8;
    margin-bottom: 24px;
    display: none;
    align-items: center;
    justify-content: space-between;
    animation: slideInDown 0.3s ease-out;
  }

  @keyframes slideInDown {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }

  .gallery-checkbox {
    width: 24px;
    height: 24px;
    cursor: pointer;
    border: 2px solid #667eea;
    border-radius: 6px;
  }

  .btn-export-group .btn {
    border-radius: 10px;
    font-weight: 600;
    padding: 10px 20px;
  }

  .pagination-container {
    background: white;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    margin-top: 32px;
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
      <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalAdd" style="background: white; color: #667eea; border: none; padding: 12px 28px; border-radius: 12px; font-weight: 600; box-shadow: 0 4px 16px rgba(0,0,0,0.1);">
        <i class="bi bi-plus-circle me-2"></i> New Gallery
      </button>
    </div>
  </div>

  <!-- Advanced Filter Section -->
  <div class="filter-section">
    <form method="GET" id="filterForm" class="row g-3">
      <div class="col-md-3">
        <label class="form-label-sm">Search</label>
        <div class="search-box">
          <i class="bi bi-search"></i>
          <input type="text" name="q" placeholder="Search gallery title..." value="<?= e($searchQ) ?>" autocomplete="off">
        </div>
      </div>
      
      <div class="col-md-2">
        <label class="form-label-sm">Category</label>
        <select name="category" class="form-select filter-select">
          <option value="">All Categories</option>
          <?php foreach ($available_categories as $cat): ?>
            <option value="<?= e($cat) ?>" <?= $categoryFilter === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label-sm">Date From</label>
        <input type="date" name="date_from" class="form-control filter-select" value="<?= e($date_from) ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label-sm">Date To</label>
        <input type="date" name="date_to" class="form-control filter-select" value="<?= e($date_to) ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label-sm">Sort By</label>
        <select name="sort" class="form-select filter-select">
          <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Newest First</option>
          <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>Oldest First</option>
          <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Title (A-Z)</option>
          <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Title (Z-A)</option>
        </select>
      </div>

      <div class="col-12 d-flex justify-content-between align-items-center mt-3">
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary" style="padding: 10px 24px; background: #667eea; border: none; border-radius: 12px; font-weight: 600;">
            <i class="bi bi-funnel me-2"></i> Apply Filters
          </button>
          <a href="galleries.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="padding: 10px 24px; border-radius: 12px; border: 2px solid #e0e0e0; color: #64748b; font-weight: 600;">
            <i class="bi bi-arrow-clockwise me-2"></i> Reset
          </a>
        </div>
        
        <div class="dropdown">
          <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown" style="padding: 10px 24px; border-radius: 12px; font-weight: 600; border: 2px solid #10b981; color: #10b981;">
            <i class="bi bi-download me-2"></i> Export Data
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkExport('csv')"><i class="bi bi-filetype-csv me-2"></i>Export as CSV</a></li>
            <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkExport('pdf')"><i class="bi bi-file-earmark-pdf me-2"></i>Export as PDF</a></li>
            <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkExport('excel')"><i class="bi bi-file-earmark-excel me-2"></i>Export as Excel</a></li>
          </ul>
        </div>
      </div>
    </form>
  </div>

  <!-- Bulk Actions Bar -->
  <div id="bulkActionBar" class="bulk-action-bar">
    <div class="d-flex align-items-center gap-3">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="selectAllGalleries" style="width: 20px; height: 20px;">
        <label class="form-check-label ms-2 fw-600" for="selectAllGalleries">Select All</label>
      </div>
      <span id="selectedCount" class="badge bg-primary rounded-pill px-3 py-2" style="font-size: 14px;">0 Selected</span>
    </div>
    <div class="d-flex gap-2">
      <button onclick="bulkArchive()" class="btn btn-warning d-flex align-items-center gap-2 text-white">
        <i class="bi bi-archive"></i> Archive Selected
      </button>
      <div class="dropdown">
        <button class="btn btn-success dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
          <i class="bi bi-download"></i> Export Selected
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkExport('csv', true)">CSV Format</a></li>
          <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkExport('pdf', true)">PDF Format</a></li>
          <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkExport('excel', true)">Excel Format</a></li>
        </ul>
      </div>
      <button onclick="clearSelection()" class="btn btn-outline-secondary">
        <i class="bi bi-x-circle"></i> Clear
      </button>
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
          <input type="checkbox" class="form-check-input gallery-checkbox-item" value="<?= $g['id'] ?>" onchange="updateBulkBar()">
          <h5 class="gallery-title mb-0">
            <i class="bi bi-folder-fill"></i> 
            <?= e($g['title']) ?>
          </h5>
          <span class="image-count-badge">
            <i class="bi bi-images me-1"></i><?= count($g['images']) ?> <?= count($g['images']) === 1 ? 'Image' : 'Images' ?>
          </span>
          <span class="badge bg-info" style="padding: 6px 12px; font-size: 0.85rem;">
            <i class="bi bi-tag me-1"></i><?= e($g['category']) ?>
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
          <form method="POST" class="d-inline" onsubmit="return confirm('Move this gallery to the archive?');">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="action" value="archive">
            <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
            <button type="submit" class="btn btn-sm btn-warning text-white">
              <i class="bi bi-archive me-1"></i> Archive
            </button>
          </form>
        </div>
      </div>

      <div class="row g-4">
        <?php foreach ($g['images'] as $i => $img): ?>
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
              <div id="carousel<?= $g['id'] ?>" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                  <?php foreach ($g['images'] as $i => $img): ?>
                    <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                      <img src="<?= $uploadDirRel . e($img) ?>" class="d-block w-100" style="max-height:70vh;object-fit:contain;">
                    </div>
                  <?php endforeach; ?>
                </div>
                <?php if (count($g['images']) > 1): ?>
                  <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $g['id'] ?>" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $g['id'] ?>" data-bs-slide="next">
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
                  <label class="form-label">Category</label>
                  <select name="category" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    <option value="Events" <?= $g['category'] === 'Events' ? 'selected' : '' ?>>Events</option>
                    <option value="Meetings" <?= $g['category'] === 'Meetings' ? 'selected' : '' ?>>Meetings</option>
                    <option value="Trainings" <?= $g['category'] === 'Trainings' ? 'selected' : '' ?>>Trainings</option>
                    <option value="Activities" <?= $g['category'] === 'Activities' ? 'selected' : '' ?>>Activities</option>
                    <option value="Awards" <?= $g['category'] === 'Awards' ? 'selected' : '' ?>>Awards</option>
                  </select>
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

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination-container d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div class="text-muted small">
        Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $total_records) ?> of <?= $total_records ?> galleries
      </div>
      <nav>
        <ul class="pagination mb-0">
          <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"><i class="bi bi-chevron-left"></i></a>
          </li>
          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
              <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"><i class="bi bi-chevron-right"></i></a>
          </li>
        </ul>
      </nav>
    </div>
    <?php endif; ?>

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
            <label class="form-label">Category</label>
            <select name="category" class="form-select" required>
              <option value="">-- Select Category --</option>
              <option value="Events">Events</option>
              <option value="Meetings">Meetings</option>
              <option value="Trainings">Trainings</option>
              <option value="Activities">Activities</option>
              <option value="Awards">Awards</option>
            </select>
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

  // Bulk Selection Logic
  const selectAllBtn = document.getElementById('selectAllGalleries');
  const checkboxes = document.querySelectorAll('.gallery-checkbox-item');
  const bulkBar = document.getElementById('bulkActionBar');
  const selectedCount = document.getElementById('selectedCount');

  if (selectAllBtn) {
    selectAllBtn.addEventListener('change', function() {
      checkboxes.forEach(cb => cb.checked = this.checked);
      updateBulkBar();
    });
  }

  window.updateBulkBar = function() {
    const checked = document.querySelectorAll('.gallery-checkbox-item:checked');
    const count = checked.length;
    
    if (count > 0) {
      bulkBar.style.display = 'flex';
      selectedCount.textContent = count + ' Selected';
    } else {
      bulkBar.style.display = 'none';
      if (selectAllBtn) selectAllBtn.checked = false;
    }
  }

  window.clearSelection = function() {
    checkboxes.forEach(cb => cb.checked = false);
    if (selectAllBtn) selectAllBtn.checked = false;
    updateBulkBar();
  }

  window.bulkArchive = function() {
    const checked = document.querySelectorAll('.gallery-checkbox-item:checked');
    const ids = Array.from(checked).map(cb => cb.value);
    
    if (ids.length === 0) return;

    Swal.fire({
      title: 'Archive Galleries?',
      text: `You are about to move ${ids.length} galleries to the archive.`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#f59e0b',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, archive them!'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = `bulk_delete_galleries.php?ids=${ids.join(',')}&action=archive`;
      }
    });
  }

  window.bulkExport = function(format, selectedOnly = false) {
    let ids = 'all';
    if (selectedOnly) {
      const checked = document.querySelectorAll('.gallery-checkbox-item:checked');
      ids = Array.from(checked).map(cb => cb.value).join(',');
    }
    
    const searchParams = new URLSearchParams(window.location.search);
    const q = searchParams.get('q') || '';
    const category = searchParams.get('category') || '';
    const date_from = searchParams.get('date_from') || '';
    const date_to = searchParams.get('date_to') || '';
    const sort = searchParams.get('sort') || '';
    
    const url = `export_selected_galleries.php?ids=${ids}&format=${format}&q=${encodeURIComponent(q)}&category=${encodeURIComponent(category)}&date_from=${date_from}&date_to=${date_to}&sort=${sort}`;
    window.open(url, '_blank');
    
    Swal.fire({
      icon: 'info',
      title: 'Export Started',
      text: 'Your file is being generated and will download shortly.',
      timer: 2000,
      showConfirmButton: false
    });
  }

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
