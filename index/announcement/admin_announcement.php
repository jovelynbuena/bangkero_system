<?php 
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}
include('../../config/db_connect.php');

$memberName = $_SESSION['member_name'] ?? 'Admin';
$user_id = $_SESSION['user_id'] ?? 0;

$error = '';
$success = false;

// ✅ Handle Add or Edit Announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image = null;

    // 🔸 Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTemp = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $imageDir = '../uploads/';
        $imagePath = $imageDir . $imageName;

        if (!is_dir($imageDir)) mkdir($imageDir, 0777, true);
        if (move_uploaded_file($imageTemp, $imagePath)) $image = $imageName;
    }

    // 🟠 Add Announcement
    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO announcements (title, content, image, date_posted) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('sss', $title, $content, $image);
        if ($stmt->execute()) {
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES (?, 'Added announcement', ?, NOW())");
            $desc = "Title: " . $title;
            $log_stmt->bind_param("is", $user_id, $desc);
            $log_stmt->execute();
            $success = "added";
        } else {
            $error = "Failed to add announcement.";
        }
        $stmt->close();
    }

    // 🟣 Edit Announcement
    elseif ($action === 'edit') {
        $id = intval($_POST['announcement_id']);
        if ($image) {
            $stmt = $conn->prepare("UPDATE announcements SET title=?, content=?, image=? WHERE id=?");
            $stmt->bind_param('sssi', $title, $content, $image, $id);
        } else {
            $stmt = $conn->prepare("UPDATE announcements SET title=?, content=? WHERE id=?");
            $stmt->bind_param('ssi', $title, $content, $id);
        }
        if ($stmt->execute()) {
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES (?, 'Edited announcement', ?, NOW())");
            $desc = "Edited Title: " . $title;
            $log_stmt->bind_param("is", $user_id, $desc);
            $log_stmt->execute();
            $success = "edited";
        } else {
            $error = "Failed to update announcement.";
        }
        $stmt->close();
    }
}

// ✅ Archive Announcement
if (isset($_GET['archive'])) {
    $id = intval($_GET['archive']);
    if ($id > 0) {
        $conn->begin_transaction();
        try {
            // Get announcement
            $stmt = $conn->prepare("SELECT * FROM announcements WHERE id=?");
            if (!$stmt) throw new Exception("Prepare SELECT failed: " . $conn->error);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $a = $res->fetch_assoc();
            $stmt->close();

            if (!$a) throw new Exception("Announcement not found");

            // Insert into archive
            $stmt = $conn->prepare("
                INSERT INTO archived_announcements 
                (original_id, title, content, image, category, date_posted)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            if (!$stmt) throw new Exception("Prepare INSERT failed: " . $conn->error);

            $category = 'Announcement';
            $stmt->bind_param(
                "isssss",
                $a['id'],
                $a['title'],
                $a['content'],
                $a['image'],
                $category,
                $a['date_posted']
            );

            if (!$stmt->execute()) throw new Exception("Insert failed: " . $stmt->error);
            $stmt->close();

            // Delete original
            $stmt = $conn->prepare("DELETE FROM announcements WHERE id=?");
            if (!$stmt) throw new Exception("Prepare DELETE failed: " . $conn->error);
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) throw new Exception("Delete failed: " . $stmt->error);
            $stmt->close();

            $conn->commit();
            header("Location: archived_announcement.php?archived=1");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            die("Archive error: " . $e->getMessage());
        }
    }
}

// -------------------------
// Search & Filter params
// -------------------------
$search = trim($_GET['q'] ?? '');
$date_from = trim($_GET['from'] ?? '');
$date_to = trim($_GET['to'] ?? '');
$has_image = isset($_GET['has_image']) ? $_GET['has_image'] : 'all';

// Build query with safe bindings
$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(title LIKE ? OR content LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}
if ($date_from !== '') {
    $where[] = "date_posted >= ?";
    $params[] = $date_from . " 00:00:00";
    $types .= 's';
}
if ($date_to !== '') {
    $where[] = "date_posted <= ?";
    $params[] = $date_to . " 23:59:59";
    $types .= 's';
}
if ($has_image === '1') $where[] = "image IS NOT NULL AND image <> ''";
elseif ($has_image === '0') $where[] = "(image IS NULL OR image = '')";

$sql = "SELECT * FROM announcements";
if (!empty($where)) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY date_posted DESC";

$announcements = null;
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $bind_names = array($types);
    for ($i = 0; $i < count($params); $i++) $bind_names[] = &$params[$i];
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
    $stmt->execute();
    $announcements = $stmt->get_result();
} else {
    $announcements = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Announcements | Bangkero & Fishermen Association</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body { font-family: 'Segoe UI', sans-serif; background: #fff; }
.main-content { margin-left: 250px; padding: 32px; min-height: 100vh; }
.announcement-item { border: 2px solid #bdbdbd; border-radius: 14px; background-color: #f5f5f5; padding: 18px; margin-bottom: 18px; box-shadow: 0 2px 8px rgba(189,189,189,0.08); }
.announcement-item h6 { font-weight: 700; color: #424242; margin: 0 0 6px; }
.link-group a { font-size: 0.92rem; color: #ff7043; margin-right: 14px; font-weight: 500; cursor: pointer; text-decoration: none; }
.link-group a:hover { text-decoration: underline; color: #00897b; }
.btn-primary { background-color: #ff7043; border-color: #ff7043; }
.btn-primary:hover { background-color: #00897b; border-color: #00897b; }
.modal-header { background-color: #ff7043; color: #fff; }
.filter-row .form-control, .filter-row .form-select { height: calc(2.25rem + 6px); }
</style>
</head>
<body>
<?php include('../navbar.php'); ?>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="fw-bold">Announcements</h4>
            <p class="text-muted mb-0">Manage announcements. Use search and filters to find items.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal"><i class="bi bi-plus-circle me-1"></i> Add Announcement</button>
        </div>
    </div>

    <!-- Search & Filters -->
    <form class="row g-2 align-items-center mb-4 filter-row" method="GET">
        <div class="col-auto" style="min-width:260px;">
            <input name="q" class="form-control form-control-sm" type="search" placeholder="Search title or content" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-auto"><input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($date_from) ?>" title="From"></div>
        <div class="col-auto"><input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($date_to) ?>" title="To"></div>
        <div class="col-auto">
            <select name="has_image" class="form-select form-select-sm">
                <option value="all" <?= $has_image === 'all' ? 'selected' : '' ?>>All</option>
                <option value="1" <?= $has_image === '1' ? 'selected' : '' ?>>With Image</option>
                <option value="0" <?= $has_image === '0' ? 'selected' : '' ?>>Without Image</option>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-search"></i> Filter</button>
            <a href="admin_announcement.php" class="btn btn-sm btn-secondary ms-1">Reset</a>
        </div>
    </form>

    <?php if ($announcements && $announcements->num_rows > 0): ?>
        <?php while ($row = $announcements->fetch_assoc()): ?>
            <div class="announcement-item">
                <h6><?= htmlspecialchars($row['title']) ?></h6>
                <p class="mb-1"><small>Posted on <?= date("F j, Y", strtotime($row['date_posted'])) ?></small></p>
                <p><?= nl2br(htmlspecialchars(substr($row['content'], 0, 90))) ?>...</p>
                <div class="link-group">
                    <a href="#" class="view-btn" 
                       data-title="<?= htmlspecialchars($row['title']) ?>"
                       data-content="<?= htmlspecialchars($row['content']) ?>"
                       data-image="<?= htmlspecialchars($row['image'] ?? '') ?>"
                       data-bs-toggle="modal" data-bs-target="#viewAnnouncementModal">View</a>
                    <a class="edit-btn" 
                       data-id="<?= $row['id'] ?>"
                       data-title="<?= htmlspecialchars($row['title']) ?>"
                       data-content="<?= htmlspecialchars($row['content']) ?>"
                       data-bs-toggle="modal" data-bs-target="#editAnnouncementModal">Edit</a>
                    <a href="#" class="archive-announcement" data-id="<?= intval($row['id']) ?>">Archive</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card p-4 text-center"><p class="mb-0">No announcements found.</p></div>
    <?php endif; ?>
</div>

<!-- Modals omitted for brevity, same as your previous code -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Edit modal fill
document.querySelectorAll('.edit-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('edit_id').value = btn.dataset.id;
    document.getElementById('edit_title').value = btn.dataset.title;
    document.getElementById('edit_content').value = btn.dataset.content;
  });
});

// View modal fill
document.querySelectorAll('.view-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('view_title').textContent = btn.dataset.title;
    document.getElementById('view_content').textContent = btn.dataset.content;
    const imgContainer = document.getElementById('view_image_container');
    const imgEl = document.getElementById('view_image');
    if (btn.dataset.image) {
      imgEl.src = '../../uploads/' + btn.dataset.image;
      imgContainer.classList.remove('d-none');
    } else imgContainer.classList.add('d-none');
  });
});

// Archive confirmation
document.querySelectorAll('.archive-announcement').forEach(btn => {
  btn.addEventListener('click', e => {
    e.preventDefault();
    const id = btn.dataset.id;
    console.log('Archive button clicked, ID:', id);
    
    if (!id || id == 0) {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Invalid ID: ' + id });
      return;
    }
    
    Swal.fire({
      title: 'Archive this announcement?',
      text: 'It will be moved to the archive and can be restored later.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, archive it!'
    }).then(res => {
      if (res.isConfirmed) {
        const url = 'admin_announcement.php?archive=' + id;
        console.log('Redirecting to:', url);
        window.location.href = url;
      }
    });
  });
});

// SweetAlert notifications
<?php if ($success === "added"): ?>
Swal.fire({ icon: 'success', title: 'Announcement Added!', timer: 1800, showConfirmButton: false });
<?php elseif ($success === "edited"): ?>
Swal.fire({ icon: 'success', title: 'Announcement Updated!', timer: 1800, showConfirmButton: false });
<?php elseif (isset($_GET['archived'])): ?>
Swal.fire({ icon: 'success', title: 'Success!', text: 'Announcement archived successfully.', timer: 2000, showConfirmButton: false });
<?php elseif (!empty($error)): ?>
Swal.fire({ icon: 'error', title: 'Error', text: <?php echo json_encode($error); ?> });
<?php endif; ?>
</script>
</body>
</html>
