<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}
require_once('../../config/db_connect.php');

$memberName = $_SESSION['member_name'] ?? 'Admin';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 25;
$offset = ($page - 1) * $per_page;

// Search & filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$filter_action = isset($_GET['action']) ? $conn->real_escape_string($_GET['action']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build WHERE clause
$where = [];
if ($search) {
    $where[] = "(username LIKE '%$search%' OR action LIKE '%$search%' OR description LIKE '%$search%' OR ip_address LIKE '%$search%')";
}
if ($filter_action) {
    $where[] = "action LIKE '%$filter_action%'";
}
if ($date_from && $date_to) {
    $where[] = "DATE(created_at) BETWEEN '$date_from' AND '$date_to'";
} elseif ($date_from) {
    $where[] = "DATE(created_at) >= '$date_from'";
} elseif ($date_to) {
    $where[] = "DATE(created_at) <= '$date_to'";
}

$whereSQL = '';
if (count($where) > 0) {
    $whereSQL = 'WHERE ' . implode(' AND ', $where);
}

// Count total records for pagination
$count_query = "SELECT COUNT(*) as total FROM activity_logs $whereSQL";
$count_result = $conn->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $per_page);

// Fetch logs with pagination
$sql = "SELECT * FROM activity_logs $whereSQL ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$result = $conn->query($sql);

// Get action types for filter dropdown
$actions_query = "SELECT DISTINCT action FROM activity_logs ORDER BY action ASC";
$actions_result = $conn->query($actions_query);
$action_types = [];
while ($row = $actions_result->fetch_assoc()) {
    $action_types[] = $row['action'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Logs | Bangkero & Fishermen Association</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: #f9fafb;
            color: #333;
        }

        .main-content {
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
        
        .page-header h3 {
            font-weight: 700;
            font-size: 2rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .page-header h3 i {
            font-size: 2.5rem;
        }
        
        /* Filter Section */
        .filter-section {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            border: 1px solid #E8E8E8;
            margin-bottom: 24px;
        }
        
        .filter-section h5 {
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 1.5px solid #E0E0E0;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
        }
        
        /* Table Card */
        .table-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            border: 1px solid #E8E8E8;
            overflow: hidden;
        }
        
        .table thead th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #495057;
            font-weight: 600;
            border: none;
            padding: 16px;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        
        .table tbody td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f5;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .action-badge {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .action-badge.success { background: #d4edda; color: #155724; }
        .action-badge.danger { background: #f8d7da; color: #721c24; }
        .action-badge.warning { background: #fff3cd; color: #856404; }
        .action-badge.info { background: #d1ecf1; color: #0c5460; }
        .action-badge.secondary { background: #e2e3e5; color: #383d41; }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            margin-right: 10px;
        }
        
        .ip-badge {
            background: #f1f3f5;
            padding: 4px 10px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            color: #495057;
        }
        
        .timestamp {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 16px;
        }
        
        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<?php include('../navbar.php'); ?>

<!-- Main Content -->
<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h3>
            <i class="bi bi-clock-history"></i>
            Activity Logs
        </h3>
        <p class="mb-0 mt-2 opacity-90">Monitor and track all system activities</p>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <h5><i class="bi bi-funnel"></i> Filters</h5>
        <form class="row g-3" method="get">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search username, action..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Action Type</label>
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    <?php foreach ($action_types as $action): ?>
                        <option value="<?= htmlspecialchars($action) ?>" <?= $filter_action == $action ? 'selected' : '' ?>>
                            <?= htmlspecialchars($action) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">From</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">To</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows): ?>
                    <?php 
                    $i = $offset + 1;
                    while($row = $result->fetch_assoc()): 
                        // Badge class
                        $badgeClass = 'secondary';
                        if (stripos($row['action'], 'login') !== false && stripos($row['action'], 'failed') === false) {
                            $badgeClass = 'success';
                        } elseif (stripos($row['action'], 'failed') !== false) {
                            $badgeClass = 'danger';
                        } elseif (stripos($row['action'], 'update') !== false || stripos($row['action'], 'edit') !== false) {
                            $badgeClass = 'warning';
                        } elseif (stripos($row['action'], 'add') !== false) {
                            $badgeClass = 'info';
                        }
                        
                        // Safe access to all fields
                        $username = isset($row['username']) && !empty($row['username']) ? $row['username'] : 'User ID: ' . ($row['user_id'] ?? 'Unknown');
                        $description = isset($row['description']) && !empty($row['description']) ? $row['description'] : '-';
                        $ip = isset($row['ip_address']) && !empty($row['ip_address']) ? $row['ip_address'] : '-';
                        
                        // Get user initials safely
                        $initials = 'U';
                        if (isset($row['username']) && !empty($row['username'])) {
                            $parts = explode(' ', trim($row['username']));
                            $initials = strtoupper(substr($parts[0], 0, 1));
                            if (isset($parts[1]) && !empty($parts[1])) {
                                $initials .= strtoupper(substr($parts[1], 0, 1));
                            }
                        }
                    ?>
                        <tr>
                            <td class="text-muted"><?= $i ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar"><?= $initials ?></div>
                                    <span class="fw-semibold"><?= htmlspecialchars($username) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="action-badge <?= $badgeClass ?>">
                                    <?= htmlspecialchars($row['action']) ?>
                                </span>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($description) ?></td>
                            <td><span class="ip-badge"><?= $ip ?></span></td>
                            <td class="timestamp">
                                <i class="bi bi-clock"></i>
                                <?= date('M d, Y g:i A', strtotime($row['created_at'])) ?>
                            </td>
                        </tr>
                    <?php 
                    $i++;
                    endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h5>No Activity Logs Found</h5>
                                <p class="text-muted">There are no logs matching your filters.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="p-4">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $filter_action ? '&action=' . urlencode($filter_action) : '' ?><?= $date_from ? '&date_from=' . $date_from : '' ?><?= $date_to ? '&date_to=' . $date_to : '' ?>">
                                Previous
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $p ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $filter_action ? '&action=' . urlencode($filter_action) : '' ?><?= $date_from ? '&date_from=' . $date_from : '' ?><?= $date_to ? '&date_to=' . $date_to : '' ?>">
                                <?= $p ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $filter_action ? '&action=' . urlencode($filter_action) : '' ?><?= $date_from ? '&date_from=' . $date_from : '' ?><?= $date_to ? '&date_to=' . $date_to : '' ?>">
                                Next
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="text-center mt-3 text-muted">
                Showing <?= $offset + 1 ?> to <?= min($offset + $per_page, $total_records) ?> of <?= $total_records ?> entries
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
