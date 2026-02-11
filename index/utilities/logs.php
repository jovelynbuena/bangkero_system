<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}
require_once('../../config/db_connect.php'); 

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 25;
$offset = ($page - 1) * $per_page;

// Search & filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$filter_action = isset($_GET['action']) ? $conn->real_escape_string($_GET['action']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build WHERE clause
$where = [];
if ($search) {
    $where[] = "(username LIKE '%$search%' OR action LIKE '%$search%' OR description LIKE '%$search%' OR ip_address LIKE '%$search%')";
}
if ($filter_date) {
    $where[] = "DATE(created_at) = '$filter_date'";
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
$query = "SELECT * FROM activity_logs $whereSQL ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$result = $conn->query($query);

// Get action types for filter dropdown
$actions_query = "SELECT DISTINCT action FROM activity_logs ORDER BY action ASC";
$actions_result = $conn->query($actions_query);
$action_types = [];
while ($row = $actions_result->fetch_assoc()) {
    $action_types[] = $row['action'];
}

include('../navbar.php');
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Activity Logs | Bangkero & Fishermen Association</title>
  
  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
  
  <style>
    /* Modern Layout */
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
    
    .page-header p {
        margin: 8px 0 0 0;
        opacity: 0.95;
        font-size: 1rem;
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
        display: flex;
        align-items: center;
        gap: 8px;
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
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    .btn-outline-secondary {
        border: 2px solid #E0E0E0;
        color: #666;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-outline-secondary:hover {
        background: #f8f9fa;
        border-color: #667eea;
        color: #667eea;
    }
    
    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
    
    .table-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #E8E8E8;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .table-card-header h5 {
        margin: 0;
        font-weight: 600;
        color: #333;
    }
    
    /* Modern Table */
    .table {
        margin: 0;
        font-size: 0.95rem;
    }
    
    .table thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: #495057;
        font-weight: 600;
        border: none;
        padding: 16px;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    
    .table tbody td {
        padding: 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f3f5;
    }
    
    .table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .table tbody tr:last-child td {
        border-bottom: none;
    }
    
    /* Action Badges */
    .action-badge {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
    }
    
    .action-badge.success {
        background: #d4edda;
        color: #155724;
    }
    
    .action-badge.danger {
        background: #f8d7da;
        color: #721c24;
    }
    
    .action-badge.warning {
        background: #fff3cd;
        color: #856404;
    }
    
    .action-badge.info {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .action-badge.secondary {
        background: #e2e3e5;
        color: #383d41;
    }
    
    /* User Avatar */
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
    
    .user-info {
        display: inline-flex;
        align-items: center;
    }
    
    /* Pagination */
    .pagination {
        margin: 24px 0 0 0;
    }
    
    .page-link {
        border-radius: 8px;
        margin: 0 4px;
        border: 1.5px solid #E0E0E0;
        color: #667eea;
        padding: 8px 14px;
        font-weight: 500;
    }
    
    .page-link:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    
    .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #ccc;
        margin-bottom: 16px;
    }
    
    .empty-state h5 {
        color: #666;
        margin-bottom: 8px;
    }
    
    .empty-state p {
        color: #999;
    }
    
    /* IP Address Badge */
    .ip-badge {
        background: #f1f3f5;
        padding: 4px 10px;
        border-radius: 6px;
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        color: #495057;
    }
    
    /* Timestamp */
    .timestamp {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .timestamp i {
        margin-right: 4px;
    }
    
    /* Export Buttons */
    .dt-buttons {
        margin-bottom: 16px;
    }
    
    .dt-button {
        background: white !important;
        border: 2px solid #E0E0E0 !important;
        color: #666 !important;
        padding: 8px 16px !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        margin-right: 8px !important;
        transition: all 0.3s ease !important;
    }
    
    .dt-button:hover {
        border-color: #667eea !important;
        color: #667eea !important;
        background: #f8f9fa !important;
    }
    
    /* Responsive */
    @media (max-width: 991.98px) {
        .main-content {
            margin-left: 0;
            padding: 20px;
        }
    }
  </style>
</head>
<body>
  
<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h3>
            <i class="bi bi-clock-history"></i>
            Activity Logs
        </h3>
        <p>Monitor and track all system activities and user actions</p>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <h5><i class="bi bi-funnel"></i> Advanced Filters</h5>
        <form class="row g-3" method="GET" action="">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Search</label>
                <div class="input-group">
                    <span class="input-group-text" style="background: white; border-right: none;">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control" 
                           style="border-left: none;"
                           placeholder="Username, action, description..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
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
                <label class="form-label fw-semibold">Date From</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label fw-semibold">Date To</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
            </div>
            
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            
            <?php if ($search || $filter_date || $filter_action || $date_from || $date_to): ?>
            <div class="col-12">
                <a href="logs.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-card-header">
            <h5>
                <i class="bi bi-table"></i> 
                Activity Logs 
                <span class="badge bg-secondary ms-2"><?= $total_records ?> records</span>
            </h5>
            <div>
                <button class="btn btn-success btn-sm" onclick="exportToCSV()">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
                </button>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="logsTable">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 20%">User</th>
                        <th style="width: 20%">Action</th>
                        <th style="width: 30%">Description</th>
                        <th style="width: 12%">IP Address</th>
                        <th style="width: 13%">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    $i = $offset + 1;
                    while ($row = $result->fetch_assoc()) {
                        // Determine badge class
                        $badgeClass = 'secondary';
                        if (stripos($row['action'], 'login') !== false && stripos($row['action'], 'failed') === false) {
                            $badgeClass = 'success';
                        } elseif (stripos($row['action'], 'failed') !== false || stripos($row['action'], 'error') !== false) {
                            $badgeClass = 'danger';
                        } elseif (stripos($row['action'], 'update') !== false || stripos($row['action'], 'edit') !== false) {
                            $badgeClass = 'warning';
                        } elseif (stripos($row['action'], 'add') !== false || stripos($row['action'], 'create') !== false) {
                            $badgeClass = 'info';
                        }
                        
                        // Safe access to all fields
                        $username = isset($row['username']) && !empty($row['username']) ? $row['username'] : 'User ID: ' . ($row['user_id'] ?? 'Unknown');
                        $description = isset($row['description']) && !empty($row['description']) ? $row['description'] : '-';
                        $ip_address = isset($row['ip_address']) && !empty($row['ip_address']) ? $row['ip_address'] : '-';
                        $created_at = date('M d, Y g:i A', strtotime($row['created_at']));
                        
                        // Get user initials safely
                        $initials = 'U';
                        if (isset($row['username']) && !empty($row['username'])) {
                            $parts = explode(' ', trim($row['username']));
                            $initials = strtoupper(substr($parts[0], 0, 1));
                            if (isset($parts[1]) && !empty($parts[1])) {
                                $initials .= strtoupper(substr($parts[1], 0, 1));
                            }
                        }
                        
                        echo "<tr>
                                <td class='text-muted'>{$i}</td>
                                <td>
                                    <div class='user-info'>
                                        <div class='user-avatar'>{$initials}</div>
                                        <span class='fw-semibold'>" . htmlspecialchars($username) . "</span>
                                    </div>
                                </td>
                                <td>
                                    <span class='action-badge {$badgeClass}'>
                                        " . htmlspecialchars($row['action']) . "
                                    </span>
                                </td>
                                <td class='text-muted'>" . htmlspecialchars($description) . "</td>
                                <td><span class='ip-badge'>{$ip_address}</span></td>
                                <td class='timestamp'>
                                    <i class='bi bi-clock'></i>
                                    {$created_at}
                                </td>
                              </tr>";
                        $i++;
                    }
                } else {
                    echo "<tr><td colspan='6'>
                            <div class='empty-state'>
                                <i class='bi bi-inbox'></i>
                                <h5>No Activity Logs Found</h5>
                                <p>There are no logs matching your filters.</p>
                            </div>
                          </td></tr>";
                }
                ?>
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
                            <a class="page-link" href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $filter_date ? '&date=' . $filter_date : '' ?><?= $filter_action ? '&action=' . urlencode($filter_action) : '' ?><?= $date_from ? '&date_from=' . $date_from : '' ?><?= $date_to ? '&date_to=' . $date_to : '' ?>">
                                <i class="bi bi-chevron-left"></i> Previous
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    for ($p = $start; $p <= $end; $p++): ?>
                        <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $p ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $filter_date ? '&date=' . $filter_date : '' ?><?= $filter_action ? '&action=' . urlencode($filter_action) : '' ?><?= $date_from ? '&date_from=' . $date_from : '' ?><?= $date_to ? '&date_to=' . $date_to : '' ?>">
                                <?= $p ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $filter_date ? '&date=' . $filter_date : '' ?><?= $filter_action ? '&action=' . urlencode($filter_action) : '' ?><?= $date_from ? '&date_from=' . $date_from : '' ?><?= $date_to ? '&date_to=' . $date_to : '' ?>">
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="text-center mt-3 text-muted">
                Showing <?= $offset + 1 ?> to <?= min($offset + $per_page, $total_records) ?> of <?= number_format($total_records) ?> entries
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
// Export to CSV
function exportToCSV() {
    Swal.fire({
        title: 'Exporting...',
        text: 'Preparing your CSV file',
        icon: 'info',
        timer: 1000,
        showConfirmButton: false,
        timerProgressBar: true
    });
    
    setTimeout(() => {
        const table = document.getElementById('logsTable');
        let csv = [];
        
        // Headers
        const headers = [];
        table.querySelectorAll('thead th').forEach(th => {
            headers.push(th.textContent.trim());
        });
        csv.push(headers.join(','));
        
        // Rows
        table.querySelectorAll('tbody tr').forEach(tr => {
            const row = [];
            tr.querySelectorAll('td').forEach((td, index) => {
                let text = td.textContent.trim();
                // Remove extra whitespace and newlines
                text = text.replace(/\s+/g, ' ');
                // Escape quotes
                text = text.replace(/"/g, '""');
                // Wrap in quotes if contains comma
                if (text.includes(',')) {
                    text = '"' + text + '"';
                }
                row.push(text);
            });
            if (row.length > 0 && row[0] !== '') {
                csv.push(row.join(','));
            }
        });
        
        // Download
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', 'activity_logs_' + new Date().toISOString().slice(0,10) + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        Swal.fire({
            title: 'Success!',
            text: 'Activity logs exported successfully',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    }, 1000);
}

// Show success message if any
<?php if (isset($_GET['exported'])): ?>
Swal.fire({
    title: 'Exported!',
    text: 'Logs exported successfully',
    icon: 'success',
    timer: 2000,
    showConfirmButton: false
});
<?php endif; ?>

// Auto-refresh notification
let refreshInterval;
function enableAutoRefresh() {
    Swal.fire({
        title: 'Auto-Refresh Enabled',
        text: 'Page will refresh every 30 seconds',
        icon: 'info',
        timer: 2000,
        showConfirmButton: false
    });
    
    refreshInterval = setInterval(() => {
        window.location.reload();
    }, 30000);
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
</body>
</html>
