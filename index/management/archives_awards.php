<?php
session_start();

if (empty($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

include('../../config/db_connect.php');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$uploadDir = '../../uploads/awards/';

// Handle restore from archives back to awards
if (isset($_GET['restore'])) {
    $aid = intval($_GET['restore']);
    if ($aid > 0) {
        try {
            $conn->begin_transaction();

            // Get award info before restoring
            $stmt_get = $conn->prepare("SELECT * FROM awards_archive WHERE archive_id = ?");
            $stmt_get->bind_param("i", $aid);
            $stmt_get->execute();
            $res_get = $stmt_get->get_result();
            $award = $res_get->fetch_assoc();
            $stmt_get->close();

            if (!$award) {
                throw new Exception("Award not found in archive");
            }

            // Restore to awards table
            $stmt_insert = $conn->prepare("
                INSERT INTO awards (
                    award_title, awarding_body, category, description, 
                    year_received, date_received, award_image, certificate_file, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt_insert->bind_param(
                "sssssssss",
                $award['award_title'],
                $award['awarding_body'],
                $award['category'],
                $award['description'],
                $award['year_received'],
                $award['date_received'],
                $award['award_image'],
                $award['certificate_file'],
                $award['original_created_at']
            );

            if (!$stmt_insert->execute()) {
                throw new Exception("Failed to restore award");
            }
            $stmt_insert->close();

            // Delete from archive
            $stmt_delete = $conn->prepare("DELETE FROM awards_archive WHERE archive_id = ?");
            $stmt_delete->bind_param("i", $aid);
            $stmt_delete->execute();
            $stmt_delete->close();

            $conn->commit();
            header('Location: archives_awards.php?restored=1');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            header('Location: archives_awards.php?error=' . urlencode($e->getMessage()));
            exit();
        }
    }
}

// Handle permanent delete
if (isset($_GET['delete_perm'])) {
    $aid = intval($_GET['delete_perm']);
    if ($aid > 0) {
        try {
            $stmt_get = $conn->prepare("SELECT award_image, certificate_file FROM awards_archive WHERE archive_id = ?");
            $stmt_get->bind_param("i", $aid);
            $stmt_get->execute();
            $res_get = $stmt_get->get_result();
            $files = $res_get->fetch_assoc();
            $stmt_get->close();

            $stmt_delete = $conn->prepare("DELETE FROM awards_archive WHERE archive_id = ?");
            $stmt_delete->bind_param("i", $aid);
            
            if ($stmt_delete->execute()) {
                if ($files['award_image'] && file_exists($uploadDir . $files['award_image'])) @unlink($uploadDir . $files['award_image']);
                if ($files['certificate_file'] && file_exists($uploadDir . $files['certificate_file'])) @unlink($uploadDir . $files['certificate_file']);
                
                header('Location: archives_awards.php?deleted=1');
                exit();
            }
        } catch (Exception $e) {
            header('Location: archives_awards.php?error=' . urlencode($e->getMessage()));
            exit();
        }
    }
}

// Pagination
$limit = 15;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Filters
$search = trim($_GET['search'] ?? '');
$category_filter = trim($_GET['category'] ?? '');
$sort = $_GET['sort'] ?? 'newest';

$where = [];
if ($search !== '') $where[] = "(award_title LIKE '%$search%' OR awarding_body LIKE '%$search%')";
if ($category_filter !== '') $where[] = "category = '$category_filter'";

$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Count
$countResult = $conn->query("SELECT COUNT(*) as total FROM awards_archive $where_sql");
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);

// Fetch
$order = "archived_at DESC";
if ($sort === 'oldest') $order = "archived_at ASC";
elseif ($sort === 'title_a') $order = "award_title ASC";
elseif ($sort === 'title_z') $order = "award_title DESC";

$sql = "SELECT * FROM awards_archive $where_sql ORDER BY $order LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

?>
<?php include('../navbar.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Archived Awards | Bangkero & Fishermen Association</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        .main-content { margin-left: 250px; padding: 32px; min-height: 100vh; }
        .page-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 30px; margin-bottom: 30px; box-shadow: 0 8px 32px rgba(102, 126, 234, 0.2); color: white; }
        .stat-card { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .table-container { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .award-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .btn-restore { background: #10b981; color: white; border: none; border-radius: 8px; padding: 6px 12px; }
        .btn-delete { background: #ef4444; color: white; border: none; border-radius: 8px; padding: 6px 12px; }
        .badge-category { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; }
        .badge-national { background: #d1fae5; color: #065f46; }
        .badge-regional { background: #dbeafe; color: #1e40af; }
        .badge-local { background: #fef3c7; color: #92400e; }

        /* Bulk Action Bar */
        .bulk-action-bar {
            background: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            display: none;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            border-left: 5px solid #667eea;
            animation: slideInDown 0.3s ease-out;
        }

        @keyframes slideInDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>

</head>
<body>

<div class="main-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="bi bi-archive-fill me-2"></i>Archived Awards</h2>
            <p>View, restore or permanently delete archived awards</p>
        </div>
        <a href="awards.php" class="btn btn-light rounded-pill px-4"><i class="bi bi-arrow-left me-2"></i>Back to Awards</a>
    </div>

    <!-- Bulk Actions -->
    <div id="bulkActionBar" class="bulk-action-bar">
        <div class="d-flex align-items-center gap-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAllAwards" style="width: 20px; height: 20px;">
                <label class="form-check-label ms-2 fw-bold text-primary" for="selectAllAwards">Select All</label>
            </div>
            <span id="selectedCount" class="badge bg-primary rounded-pill px-3 py-2" style="font-size: 14px;">0 Selected</span>
        </div>
        <div class="d-flex gap-2">
            <button onclick="bulkAction('restore')" class="btn btn-success rounded-pill px-4">
                <i class="bi bi-arrow-counterclockwise me-2"></i>Restore Selected
            </button>
            <button onclick="bulkAction('delete')" class="btn btn-danger rounded-pill px-4">
                <i class="bi bi-trash-fill me-2"></i>Delete Permanently
            </button>
            <button onclick="clearSelection()" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-x-circle me-2"></i>Clear
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control rounded-pill" placeholder="Search title or body..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select rounded-pill">
                        <option value="">All Categories</option>
                        <option value="National" <?= $category_filter === 'National' ? 'selected' : '' ?>>National</option>
                        <option value="Regional" <?= $category_filter === 'Regional' ? 'selected' : '' ?>>Regional</option>
                        <option value="Local" <?= $category_filter === 'Local' ? 'selected' : '' ?>>Local</option>
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
                        <th width="40px"></th>
                        <th>Award</th>
                        <th>Awarding Body</th>
                        <th>Category</th>
                        <th>Year</th>
                        <th>Archived Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input award-checkbox" value="<?= $row['archive_id'] ?>" onchange="updateBulkBar()">
                            </td>
                            <td>

                                <div class="d-flex align-items-center gap-3">
                                    <?php if ($row['award_image']): ?>
                                        <img src="<?= $uploadDir . $row['award_image'] ?>" class="award-thumb">
                                    <?php else: ?>
                                        <div class="award-thumb bg-secondary d-flex align-items-center justify-content-center text-white"><i class="bi bi-trophy"></i></div>
                                    <?php endif; ?>
                                    <span class="fw-bold"><?= htmlspecialchars($row['award_title']) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['awarding_body']) ?></td>
                            <td><span class="badge-category badge-<?= strtolower($row['category']) ?>"><?= $row['category'] ?></span></td>
                            <td><?= $row['year_received'] ?></td>
                            <td><?= date('M d, Y', strtotime($row['archived_at'])) ?></td>
                            <td class="text-center">
                                <button onclick="confirmRestore(<?= $row['archive_id'] ?>, '<?= addslashes($row['award_title']) ?>')" class="btn-restore me-1" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button>
                                <button onclick="confirmDelete(<?= $row['archive_id'] ?>, '<?= addslashes($row['award_title']) ?>')" class="btn-delete" title="Delete Permanently"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">No archived awards found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Bulk Selection Logic
const selectAllBtn = document.getElementById('selectAllAwards');
const checkboxes = document.querySelectorAll('.award-checkbox');
const bulkBar = document.getElementById('bulkActionBar');
const selectedCount = document.getElementById('selectedCount');

if (selectAllBtn) {
    selectAllBtn.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });
}

window.updateBulkBar = function() {
    const checked = document.querySelectorAll('.award-checkbox:checked');
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

window.bulkAction = function(action) {
    const checked = document.querySelectorAll('.award-checkbox:checked');
    const ids = Array.from(checked).map(cb => cb.value);
    
    if (ids.length === 0) return;

    const actionText = action === 'restore' ? 'Restore' : 'Permanently Delete';
    const actionIcon = action === 'restore' ? 'question' : 'warning';
    const confirmColor = action === 'restore' ? '#10b981' : '#ef4444';

    Swal.fire({
        title: `${actionText} Selected Awards?`,
        text: `You are about to ${actionText.toLowerCase()} ${ids.length} awards. ${action === 'delete' ? 'This cannot be undone!' : ''}`,
        icon: actionIcon,
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, ${actionText}!`
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `bulk_manage_archives.php?type=awards&ids=${ids.join(',')}&action=${action}`;
        }
    });
}

function confirmRestore(id, title) {

    Swal.fire({
        title: 'Restore Award?',
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
        text: `This will permanently delete "${title}" and its files.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) window.location.href = `?delete_perm=${id}`;
    });
}

<?php if (isset($_GET['restored'])): ?>
    Swal.fire({ icon: 'success', title: 'Restored!', text: 'Award has been moved back to main list.', timer: 2000, showConfirmButton: false });
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
    Swal.fire({ icon: 'success', title: 'Deleted!', text: 'Award has been permanently removed.', timer: 2000, showConfirmButton: false });
<?php endif; ?>
</script>
</body>
</html>
