<?php
session_start();

if (empty($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

include('../../config/db_connect.php');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$uploadDirRel = '../../uploads/gallery/';
$uploadDir = __DIR__ . '/../../uploads/gallery/';

// Helpers
function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// Handle restore from archives back to galleries
if (isset($_GET['restore'])) {
    $gid = intval($_GET['restore']);
    if ($gid > 0) {
        try {
            $conn->begin_transaction();

            // Get gallery info before restoring
            $stmt_get = $conn->prepare("SELECT * FROM galleries_archive WHERE archive_id = ?");
            $stmt_get->bind_param("i", $gid);
            $stmt_get->execute();
            $res_get = $stmt_get->get_result();
            $gallery = $res_get->fetch_assoc();
            $stmt_get->close();

            if (!$gallery) {
                throw new Exception("Gallery not found in archive");
            }

            // Restore to galleries table
            $stmt_insert = $conn->prepare("
                INSERT INTO galleries (
                    title, category, images, created_at
                ) VALUES (?, ?, ?, ?)
            ");
            
            $stmt_insert->bind_param(
                "ssss",
                $gallery['title'],
                $gallery['category'],
                $gallery['images'],
                $gallery['original_created_at']
            );

            if (!$stmt_insert->execute()) {
                throw new Exception("Failed to restore gallery");
            }
            $stmt_insert->close();

            // Delete from archive
            $stmt_delete = $conn->prepare("DELETE FROM galleries_archive WHERE archive_id = ?");
            $stmt_delete->bind_param("i", $gid);
            $stmt_delete->execute();
            $stmt_delete->close();

            $conn->commit();
            header('Location: archives_galleries.php?restored=1');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            header('Location: archives_galleries.php?error=' . urlencode($e->getMessage()));
            exit();
        }
    }
}

// Handle permanent delete
if (isset($_GET['delete_perm'])) {
    $gid = intval($_GET['delete_perm']);
    if ($gid > 0) {
        try {
            $stmt_get = $conn->prepare("SELECT images FROM galleries_archive WHERE archive_id = ?");
            $stmt_get->bind_param("i", $gid);
            $stmt_get->execute();
            $res_get = $stmt_get->get_result();
            $row = $res_get->fetch_assoc();
            $stmt_get->close();

            if ($row) {
                $images = array_filter(array_map('trim', explode(',', $row['images'] ?? '')));
                
                $stmt_delete = $conn->prepare("DELETE FROM galleries_archive WHERE archive_id = ?");
                $stmt_delete->bind_param("i", $gid);
                
                if ($stmt_delete->execute()) {
                    foreach ($images as $img) {
                        $path = $uploadDir . $img;
                        if (file_exists($path)) @unlink($path);
                    }
                    header('Location: archives_galleries.php?deleted=1');
                    exit();
                }
            }
        } catch (Exception $e) {
            header('Location: archives_galleries.php?error=' . urlencode($e->getMessage()));
            exit();
        }
    }
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Filters
$search = trim($_GET['search'] ?? '');
$category_filter = trim($_GET['category'] ?? '');
$sort = $_GET['sort'] ?? 'newest';

$where = [];
if ($search !== '') $where[] = "(title LIKE '%$search%' OR category LIKE '%$search%')";
if ($category_filter !== '') $where[] = "category = '$category_filter'";

$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Count
$countResult = $conn->query("SELECT COUNT(*) as total FROM galleries_archive $where_sql");
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);

// Fetch
$order = "archived_at DESC";
if ($sort === 'oldest') $order = "archived_at ASC";
elseif ($sort === 'title_a') $order = "title ASC";
elseif ($sort === 'title_z') $order = "title DESC";

$sql = "SELECT * FROM galleries_archive $where_sql ORDER BY $order LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

?>
<?php include('../navbar.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Archived Galleries | Bangkero & Fishermen Association</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        .main-content { margin-left: 270px; padding: 32px; min-height: 100vh; }
        .page-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 30px; margin-bottom: 30px; box-shadow: 0 8px 32px rgba(102, 126, 234, 0.2); color: white; }
        .table-container { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .gallery-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .btn-restore { background: #10b981; color: white; border: none; border-radius: 8px; padding: 6px 12px; }
        .btn-delete { background: #ef4444; color: white; border: none; border-radius: 8px; padding: 6px 12px; }
        .badge-category { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; }
        
        @media (max-width: 991.98px) { .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>

<div class="main-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="bi bi-archive-fill me-2"></i>Archived Galleries</h2>
            <p>View, restore or permanently delete archived photo galleries</p>
        </div>
        <a href="galleries.php" class="btn btn-light rounded-pill px-4"><i class="bi bi-arrow-left me-2"></i>Back to Galleries</a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control rounded-pill" placeholder="Search title..." value="<?= e($search) ?>">
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select rounded-pill">
                        <option value="">All Categories</option>
                        <option value="Events" <?= $category_filter === 'Events' ? 'selected' : '' ?>>Events</option>
                        <option value="Meetings" <?= $category_filter === 'Meetings' ? 'selected' : '' ?>>Meetings</option>
                        <option value="Trainings" <?= $category_filter === 'Trainings' ? 'selected' : '' ?>>Trainings</option>
                        <option value="Activities" <?= $category_filter === 'Activities' ? 'selected' : '' ?>>Activities</option>
                        <option value="Awards" <?= $category_filter === 'Awards' ? 'selected' : '' ?>>Awards</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="sort" class="form-select rounded-pill">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Recently Archived</option>
                        <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest Archived</option>
                        <option value="title_a" <?= $sort === 'title_a' ? 'selected' : '' ?>>Title A-Z</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary rounded-pill w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Gallery</th>
                        <th>Category</th>
                        <th>Images</th>
                        <th>Archived Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): 
                        $imgs = array_filter(array_map('trim', explode(',', $row['images'] ?? '')));
                        $firstImg = count($imgs) > 0 ? $uploadDirRel . $imgs[0] : '';
                    ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <?php if ($firstImg): ?>
                                        <img src="<?= $firstImg ?>" class="gallery-thumb">
                                    <?php else: ?>
                                        <div class="gallery-thumb bg-secondary d-flex align-items-center justify-content-center text-white"><i class="bi bi-images"></i></div>
                                    <?php endif; ?>
                                    <span class="fw-bold"><?= e($row['title']) ?></span>
                                </div>
                            </td>
                            <td><span class="badge bg-info"><?= e($row['category']) ?></span></td>
                            <td><?= count($imgs) ?> images</td>
                            <td><?= date('M d, Y', strtotime($row['archived_at'])) ?></td>
                            <td class="text-center">
                                <button onclick="confirmRestore(<?= $row['archive_id'] ?>, '<?= addslashes($row['title']) ?>')" class="btn-restore me-1" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button>
                                <button onclick="confirmDelete(<?= $row['archive_id'] ?>, '<?= addslashes($row['title']) ?>')" class="btn-delete" title="Delete Permanently"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">No archived galleries found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination UI -->
        <?php if ($totalPages > 1): ?>
        <nav class="d-flex justify-content-center mt-4">
            <ul class="pagination mb-0">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&sort=<?= $sort ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&sort=<?= $sort ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&sort=<?= $sort ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmRestore(id, title) {
    Swal.fire({
        title: 'Restore Gallery?',
        text: `Are you sure you want to restore "${title}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Yes, Restore'
    }).then((result) => {
        if (result.isConfirmed) window.location.href = `?restore=${id}`;
    });
}

function confirmDelete(id, title) {
    Swal.fire({
        title: 'Delete Permanently?',
        text: `This will permanently delete "${title}" and all its photos. This cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) window.location.href = `?delete_perm=${id}`;
    });
}

<?php if (isset($_GET['restored'])): ?>
    Swal.fire({ icon: 'success', title: 'Restored!', text: 'Gallery has been moved back to main list.', timer: 2000, showConfirmButton: false });
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
    Swal.fire({ icon: 'success', title: 'Deleted!', text: 'Gallery has been permanently removed.', timer: 2000, showConfirmButton: false });
<?php endif; ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>