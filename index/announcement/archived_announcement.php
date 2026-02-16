<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}
include('../../config/db_connect.php');

// Archive an announcement
if (isset($_GET['archive'])) {
    $id = intval($_GET['archive']);
    if ($id > 0) {
        $conn->begin_transaction();
        try {
            // Fetch the announcement
            $stmt = $conn->prepare("SELECT * FROM announcements WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $announcement = $result->fetch_assoc();
            $stmt->close();

            if ($announcement) {
                // Insert into archived_announcements
                $stmt = $conn->prepare("INSERT INTO archived_announcements (original_id, title, content, image, date_posted) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $announcement['id'], $announcement['title'], $announcement['content'], $announcement['image'], $announcement['date_posted']);
                $stmt->execute();
                $stmt->close();

                // Delete from announcements
                $stmt = $conn->prepare("DELETE FROM announcements WHERE id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                header("Location: admin_announcements.php?archived=1");
                exit();
            } else {
                $conn->rollback();
                die("Announcement not found.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            die("Error archiving announcement: " . $e->getMessage());
        }
    }
}

// Restore an announcement
if (isset($_GET['retrieve'])) {
    $id = intval($_GET['retrieve']);
    if ($id > 0) {
        $conn->begin_transaction();
        try {
            // Fetch from archived_announcements
            $stmt = $conn->prepare("SELECT * FROM archived_announcements WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $announcement = $result->fetch_assoc();
            $stmt->close();

            if ($announcement) {
                // Insert back into announcements (let AUTO_INCREMENT handle the ID)
                $stmt = $conn->prepare("INSERT INTO announcements (title, content, image, date_posted, category, expiry_date, posted_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                // Use safe defaults for missing columns
                $category = $announcement['category'] ?? 'General';
                $expiry_date = $announcement['expiry_date'] ?? NULL;
                $posted_by = $announcement['posted_by'] ?? 'Admin';
                $stmt->bind_param("sssssss", $announcement['title'], $announcement['content'], $announcement['image'], $announcement['date_posted'], $category, $expiry_date, $posted_by);
                $stmt->execute();
                $stmt->close();

                // Delete from archived_announcements
                $stmt = $conn->prepare("DELETE FROM archived_announcements WHERE id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                header("Location: archived_announcement.php?retrieved=1");
                exit();
            } else {
                $conn->rollback();
                die("Archived announcement not found.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            die("Error restoring announcement: " . $e->getMessage());
        }
    }
}

// Fetch all archived announcements with filtering
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$search_sql = $conn->real_escape_string($search);
$category_sql = $conn->real_escape_string($category_filter);

$conditions = ["1=1"];

if ($search !== '') {
    $conditions[] = "(title LIKE '%$search_sql%' OR content LIKE '%$search_sql%')";
}

if ($category_filter !== '') {
    $conditions[] = "category = '$category_sql'";
}

$sql = "SELECT * FROM archived_announcements WHERE " . implode(" AND ", $conditions) . " ORDER BY ";

if ($sort === 'oldest') {
    $sql .= "date_posted ASC";
} elseif ($sort === 'name_a') {
    $sql .= "title ASC";
} elseif ($sort === 'name_z') {
    $sql .= "title DESC";
} else {
    $sql .= "date_posted DESC";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Archived Announcements | Bangkero & Fishermen Association</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
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

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.2);
    color: white;
}

.page-header h2 {
    font-weight: 700;
    margin: 0 0 10px 0;
    font-size: 2rem;
}

.page-header p {
    margin: 0;
    opacity: 0.95;
    font-size: 1.05rem;
}

.stats-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 24px;
}

.stat-item {
    text-align: center;
}

.stat-item .stat-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    color: white;
    font-size: 1.4rem;
}

.stat-item h3 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 4px 0;
}

.stat-item p {
    color: #6b7280;
    font-size: 0.9rem;
    margin: 0;
}

.search-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.search-box {
    position: relative;
}

.search-box input {
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px 120px 12px 16px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.search-box input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}

.search-box button {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 8px;
    padding: 8px 20px;
    color: white;
    transition: all 0.3s ease;
}

.search-box button:hover {
    transform: translateY(-50%) scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.table-container {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.table thead th {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    padding: 16px;
    border: none;
}

.table tbody tr {
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background-color: #f5f3ff;
    transform: scale(1.01);
}

.table tbody td {
    padding: 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f3f4f6;
}

.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    max-width: 300px;
}

.btn-restore {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    color: white;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-restore:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    color: white;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #9ca3af;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

@media (max-width: 991.98px) { 
    .main-content { 
        margin-left: 0; 
        padding: 16px; 
    }
}
</style>
</head>
<body>

<?php include('../navbar.php'); ?>

<div class="main-content">
    <!-- Page Header (aligned with other archive pages) -->
    <div class="page-header d-flex align-items-center">
        <div>
            <h2><i class="bi bi-megaphone-fill me-2"></i>Archived Announcements</h2>
            <p class="mb-0">View and restore archived announcement records</p>
        </div>
    </div>

    <!-- Search & Filter Section -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <!-- LEFT SIDE: Search & Category -->
                <div class="d-flex gap-2 flex-wrap align-items-center" style="flex: 1; min-width: 300px;">
                    <!-- Search Input -->
                    <div class="position-relative" style="flex: 1; min-width: 200px;">
                        <i class="bi bi-search position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                        <input type="text" name="search" class="form-control rounded-pill ps-5" 
                               placeholder="Search by title or content..."
                               value="<?= htmlspecialchars($search) ?>">
                    </div>

                    <!-- Category Filter -->
                    <select name="category" class="form-select rounded-pill shadow-sm" style="flex: 0 0 auto; min-width: 160px;">
                        <option value="">All Categories</option>
                        <option value="Announcement" <?= $category_filter === 'Announcement' ? 'selected' : '' ?>>Announcement</option>
                        <option value="Event" <?= $category_filter === 'Event' ? 'selected' : '' ?>>Event</option>
                        <option value="Fishing" <?= $category_filter === 'Fishing' ? 'selected' : '' ?>>Fishing</option>
                        <option value="Meeting" <?= $category_filter === 'Meeting' ? 'selected' : '' ?>>Meeting</option>
                        <option value="Reminder" <?= $category_filter === 'Reminder' ? 'selected' : '' ?>>Reminder</option>
                        <option value="Emergency" <?= $category_filter === 'Emergency' ? 'selected' : '' ?>>Emergency</option>
                        <option value="General" <?= $category_filter === 'General' ? 'selected' : '' ?>>General</option>
                    </select>
                </div>

                <!-- RIGHT SIDE: Sort & Reset -->
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <!-- Sort Dropdown -->
                    <select name="sort" class="form-select rounded-pill shadow-sm" style="flex: 0 0 auto; min-width: 160px;" onchange="this.form.submit();">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                        <option value="name_a" <?= $sort === 'name_a' ? 'selected' : '' ?>>Title A-Z</option>
                        <option value="name_z" <?= $sort === 'name_z' ? 'selected' : '' ?>>Title Z-A</option>
                    </select>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="bi bi-search me-2"></i>Search
                    </button>

                    <!-- Reset Button -->
                    <a href="archived_announcement.php" class="btn btn-light border rounded-pill px-3 shadow-sm">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Title</th>
                        <th class="text-center">Category</th>
                        <th class="text-center">Date Posted</th>
                        <th>Content</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($result && $result->num_rows > 0): $count=1; ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center"><strong><?= $count++ ?></strong></td>
                        <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                        <td class="text-center">
                            <span class="badge bg-info"><?= htmlspecialchars($row['category'] ?: 'General') ?></span>
                        </td>
                        <td class="text-center"><?= date("M d, Y", strtotime($row['date_posted'])) ?></td>
                        <td>
                            <div class="text-truncate-2" title="<?= htmlspecialchars($row['content']) ?>">
                                <?= htmlspecialchars($row['content']) ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <button class="btn-restore" onclick="confirmRestore(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['title'])) ?>')">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h5>No Archived Announcements</h5>
                                <p>There are no archived announcements to display.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Live Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    const tableRows = document.querySelectorAll('tbody tr');
    
    if (searchInput && tableRows.length > 0) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;
            
            tableRows.forEach(row => {
                // Skip empty state row
                if (row.querySelector('.empty-state')) {
                    return;
                }
                
                const title = row.cells[1]?.textContent.toLowerCase() || '';
                const category = row.cells[2]?.textContent.toLowerCase() || '';
                const content = row.cells[4]?.textContent.toLowerCase() || '';
                
                if (title.includes(searchTerm) || category.includes(searchTerm) || content.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide empty state
            const tbody = document.querySelector('tbody');
            const existingEmptyRow = tbody.querySelector('.empty-state-search');
            
            if (visibleCount === 0 && searchTerm !== '') {
                if (!existingEmptyRow) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.className = 'empty-state-search';
                    emptyRow.innerHTML = `
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="bi bi-search"></i>
                                <h5>No Results Found</h5>
                                <p>No announcements match your search for "<strong>${searchTerm}</strong>"</p>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(emptyRow);
                }
            } else {
                if (existingEmptyRow) {
                    existingEmptyRow.remove();
                }
            }
        });
    }
});

function confirmRestore(id, title){
    Swal.fire({
        title: 'Restore Announcement?',
        html: `Are you sure you want to restore <strong>${title}</strong> back to active announcements list?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-arrow-counterclockwise"></i> Yes, Restore',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then(result=>{
        if(result.isConfirmed){
            window.location.href='archived_announcement.php?retrieve='+id;
        }
    });
}

<?php if(isset($_GET['retrieved'])): ?>
Swal.fire({
    icon: 'success', 
    title: 'Announcement Restored!', 
    text: 'The announcement has been successfully restored to active list.', 
    timer: 2500, 
    showConfirmButton: false
});
<?php endif; ?>
</script>
</body>
</html>
