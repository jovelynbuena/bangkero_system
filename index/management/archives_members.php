<?php
session_start();

if (empty($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

include('../../config/db_connect.php');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$adminName = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';

// Handle retrieve from archives back to members - RESTORE ALL FIELDS
if (isset($_GET['retrieve'])) {
    $mid = intval($_GET['retrieve']);
    if ($mid > 0) {
        try {
            $conn->begin_transaction();

            // Get ALL member info before restoring
            $stmt_get = $conn->prepare("SELECT * FROM member_archive WHERE member_id = ?");
            $stmt_get->bind_param("i", $mid);
            $stmt_get->execute();
            $res_get = $stmt_get->get_result();
            $member = $res_get->fetch_assoc();
            $stmt_get->close();

            if (!$member) {
                throw new Exception("Member not found in archive");
            }

            // Restore ALL columns to members table
            $stmt_insert = $conn->prepare("
                INSERT INTO members (
                    name, dob, gender, phone, email, address, 
                    work_type, license_number, boat_name, fishing_area, 
                    emergency_name, emergency_phone, agreement, image
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt_insert->bind_param(
                "ssssssssssssss",
                $member['name'],
                $member['dob'],
                $member['gender'],
                $member['phone'],
                $member['email'],
                $member['address'],
                $member['work_type'],
                $member['license_number'],
                $member['boat_name'],
                $member['fishing_area'],
                $member['emergency_name'],
                $member['emergency_phone'],
                $member['agreement'],
                $member['image']
            );

            if (!$stmt_insert->execute()) {
                throw new Exception("Failed to restore member");
            }
            $stmt_insert->close();

            // Delete from archive
            $stmt_delete = $conn->prepare("DELETE FROM member_archive WHERE member_id = ?");
            $stmt_delete->bind_param("i", $mid);
            $stmt_delete->execute();
            $stmt_delete->close();

            // Log restore action
            if (isset($_SESSION['user_id'])) {
                $actionText = "Restored member: {$member['name']}";
                $stmt_log = $conn->prepare("INSERT INTO activity_logs (user_id, action, description) VALUES (?, ?, ?)");
                $stmt_log->bind_param("iss", $_SESSION['user_id'], $actionText, $actionText);
                $stmt_log->execute();
                $stmt_log->close();
            }

            $conn->commit();
            header('Location: archives_members.php?retrieved=1');
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            header('Location: archives_members.php?error=' . urlencode($e->getMessage()));
            exit();
        }
    }
}

// Pagination
$limit = 15;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = "";
$work_type_filter = "";
$sort = "newest";
$whereClause = "";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = $conn->real_escape_string(trim($_GET['search']));
}

if (isset($_GET['work_type']) && !empty(trim($_GET['work_type']))) {
    $work_type_filter = $conn->real_escape_string(trim($_GET['work_type']));
}

if (isset($_GET['sort'])) {
    $sort = trim($_GET['sort']);
}

// Build WHERE clause
$conditions = [];
if ($search !== '') {
    $conditions[] = "(name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
}
if ($work_type_filter !== '') {
    $conditions[] = "(work_type = '$work_type_filter')";
}

if (!empty($conditions)) {
    $whereClause = "WHERE " . implode(" AND ", $conditions);
}

// Count total records
$countSql = "SELECT COUNT(*) as total FROM member_archive $whereClause";
$countResult = $conn->query($countSql);
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);

// Fetch archived members with pagination
$sql = "SELECT * FROM member_archive 
        $whereClause
        ORDER BY ";

if ($sort === 'oldest') {
    $sql .= "archived_at ASC";
} elseif ($sort === 'name_a') {
    $sql .= "name ASC";
} elseif ($sort === 'name_z') {
    $sql .= "name DESC";
} else {
    $sql .= "archived_at DESC";
}

$sql .= " LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Statistics
$totalArchivedQuery = $conn->query("SELECT COUNT(*) as total FROM member_archive");
$totalArchived = $totalArchivedQuery->fetch_assoc()['total'];

$thisMonthQuery = $conn->query("SELECT COUNT(*) as total FROM member_archive WHERE MONTH(archived_at) = MONTH(CURRENT_DATE()) AND YEAR(archived_at) = YEAR(CURRENT_DATE())");
$thisMonth = $thisMonthQuery->fetch_assoc()['total'];
?>
<?php include('../navbar.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Archived Members | Bangkero & Fishermen Association</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .stat-card.purple .icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-card.orange .icon {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: #1f2937;
        }

        .stat-card p {
            margin: 0;
            color: #6b7280;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .search-filter-section {
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
            padding: 12px 50px 12px 16px;
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
            padding: 8px 16px;
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
            overflow: hidden;
        }

        .table {
            margin: 0;
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
            background-color: #f8f9ff;
            transform: scale(1.01);
        }

        .table tbody td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
        }

        .btn-restore {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-restore:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-delete-perm {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-delete-perm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

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


        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 24px;
        }

        .pagination {
            gap: 8px;
        }

        .page-link {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px 16px;
            color: #667eea;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.85rem;
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

        .empty-state h5 {
            color: #6b7280;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h2><i class="bi bi-archive-fill me-2"></i>Archived Members</h2>
        <p>View and restore archived member records</p>
    </div>

    <!-- Statistics -->
    <div class="stats-container">
        <div class="stat-card purple">
            <div class="icon">
                <i class="bi bi-archive"></i>
            </div>
            <h3><?php echo number_format($totalArchived); ?></h3>
            <p>Total Archived</p>
        </div>
        <div class="stat-card orange">
            <div class="icon">
                <i class="bi bi-calendar-check"></i>
            </div>
            <h3><?php echo number_format($thisMonth); ?></h3>
            <p>Archived This Month</p>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div id="bulkActionBar" class="bulk-action-bar">
        <div class="d-flex align-items-center gap-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAllMembers" style="width: 20px; height: 20px;">
                <label class="form-check-label ms-2 fw-bold text-primary" for="selectAllMembers">Select All</label>
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

    <!-- Professional Filter Toolbar -->

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <!-- LEFT SIDE: Search & Filter -->
                <div class="d-flex gap-2 flex-wrap align-items-center" style="flex: 1; min-width: 300px;">
                    <!-- Search Input -->
                    <div class="position-relative" style="flex: 1; min-width: 200px;">
                        <i class="bi bi-search position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                        <input type="text" name="search" class="form-control rounded-pill ps-5" 
                               placeholder="Search by name, email or phone..."
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    
                    <!-- Work Type Filter -->
                    <select name="work_type" class="form-select rounded-pill shadow-sm" style="flex: 0 0 auto; min-width: 140px;">
                        <option value="">All Work Types</option>
                        <option value="Fisherman" <?= $work_type_filter === 'Fisherman' ? 'selected' : '' ?>>Fisherman</option>
                        <option value="Boat Owner" <?= $work_type_filter === 'Boat Owner' ? 'selected' : '' ?>>Boat Owner</option>
                        <option value="Trader" <?= $work_type_filter === 'Trader' ? 'selected' : '' ?>>Trader</option>
                        <option value="Processor" <?= $work_type_filter === 'Processor' ? 'selected' : '' ?>>Processor</option>
                    </select>
                </div>

                <!-- RIGHT SIDE: Sort, Reset -->
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <!-- Sort Dropdown -->
                    <select name="sort" class="form-select rounded-pill shadow-sm" style="flex: 0 0 auto; min-width: 140px;" onchange="this.form.submit();">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                        <option value="name_a" <?= $sort === 'name_a' ? 'selected' : '' ?>>Name A-Z</option>
                        <option value="name_z" <?= $sort === 'name_z' ? 'selected' : '' ?>>Name Z-A</option>
                    </select>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="bi bi-search me-2"></i>Search
                    </button>

                    <!-- Reset Button -->
                    <a href="archives_members.php" class="btn btn-light border rounded-pill px-3 shadow-sm">
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
                        <th width="40px"></th>
                        <th>Member ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Work Type</th>
                        <th>Archived Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input member-checkbox" value="<?php echo (int)$row['member_id']; ?>" onchange="updateBulkBar()">
                            </td>
                            <td><strong><?php echo (int)$row['member_id']; ?></strong></td>

                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td>
                                <?php if (!empty($row['work_type'])): ?>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($row['work_type']); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($row['archived_at'])); ?></td>
                            <td class="text-center d-flex justify-content-center gap-2">
                                <button type="button" 
                                        class="btn-restore" 
                                        onclick="confirmRestore(<?php echo (int)$row['member_id']; ?>, '<?php echo htmlspecialchars(addslashes($row['name'])); ?>')">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                </button>
                                <button type="button" 
                                        class="btn-delete-perm" 
                                        onclick="confirmDeletePerm(<?php echo (int)$row['member_id']; ?>, '<?php echo htmlspecialchars(addslashes($row['name'])); ?>')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>

                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h5>No Archived Members</h5>
                                <p>There are no archived members to display.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-container">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Live Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    const tableRows = document.querySelectorAll('tbody tr');
    const paginationContainer = document.querySelector('.pagination-container');
    
    if (searchInput && tableRows.length > 0) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;
            
            tableRows.forEach(row => {
                // Skip empty state row
                if (row.querySelector('.empty-state')) {
                    return;
                }
                
                const name = row.cells[1]?.textContent.toLowerCase() || '';
                const email = row.cells[2]?.textContent.toLowerCase() || '';
                const phone = row.cells[3]?.textContent.toLowerCase() || '';
                
                if (name.includes(searchTerm) || email.includes(searchTerm) || phone.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Hide pagination during search
            if (paginationContainer) {
                paginationContainer.style.display = searchTerm !== '' ? 'none' : 'flex';
            }
            
            // Show/hide empty state
            const tbody = document.querySelector('tbody');
            const existingEmptyRow = tbody.querySelector('.empty-state-search');
            
            if (visibleCount === 0 && searchTerm !== '') {
                if (!existingEmptyRow) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.className = 'empty-state-search';
                    emptyRow.innerHTML = `
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="bi bi-search"></i>
                                <h5>No Results Found</h5>
                                <p>No members match your search for "<strong>${searchTerm}</strong>"</p>
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

    // Bulk Selection Logic
    const selectAllBtn = document.getElementById('selectAllMembers');
    const checkboxes = document.querySelectorAll('.member-checkbox');
    const bulkBar = document.getElementById('bulkActionBar');
    const selectedCount = document.getElementById('selectedCount');

    if (selectAllBtn) {
        selectAllBtn.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkBar();
        });
    }

    window.updateBulkBar = function() {
        const checked = document.querySelectorAll('.member-checkbox:checked');
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
        const checked = document.querySelectorAll('.member-checkbox:checked');
        const ids = Array.from(checked).map(cb => cb.value);
        
        if (ids.length === 0) return;

        const actionText = action === 'restore' ? 'Restore' : 'Permanently Delete';
        const actionIcon = action === 'restore' ? 'question' : 'warning';
        const confirmColor = action === 'restore' ? '#10b981' : '#ef4444';

        Swal.fire({
            title: `${actionText} Selected Members?`,
            text: `You are about to ${actionText.toLowerCase()} ${ids.length} members. ${action === 'delete' ? 'This cannot be undone!' : ''}`,
            icon: actionIcon,
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${actionText}!`
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `bulk_manage_archives.php?type=members&ids=${ids.join(',')}&action=${action}`;
            }
        });
    }

function confirmRestore(id, name) {
    Swal.fire({
        title: 'Restore Member?',
        html: `Are you sure you want to restore <strong>${name}</strong>?<br><br>
               <small class="text-muted">All member information will be fully restored including:<br>
               Personal details, work information, emergency contacts, and profile image.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-arrow-counterclockwise"></i> Yes, Restore',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'archives_members.php?retrieve=' + id;
        }
    });
}

function confirmDeletePerm(id, name) {
    Swal.fire({
        title: 'Delete Permanently?',
        text: `Are you sure you want to permanently delete "${name}"? This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete Permanently'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `bulk_manage_archives.php?type=members&ids=${id}&action=delete`;
        }
    });
}

<?php if (isset($_GET['retrieved'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Member Restored!',
        text: 'The member has been successfully restored with all information.',
        timer: 2500,
        showConfirmButton: false
    });
<?php endif; ?>

<?php if (isset($_GET['bulk_success'])): ?>
    Swal.fire({ 
        icon: 'success', 
        title: 'Success!', 
        text: '<?= $_GET['count'] ?> members were <?= $_GET['action'] === 'restore' ? 'restored' : 'permanently deleted' ?>.', 
        timer: 3000, 
        showConfirmButton: false 
    });
<?php endif; ?>


<?php if (isset($_GET['error'])): ?>
    Swal.fire({
        icon: 'error',
        title: 'Restore Failed',
        text: '<?php echo htmlspecialchars($_GET['error']); ?>',
        confirmButtonColor: '#667eea'
    });
<?php endif; ?>
</script>
</body>
</html>
