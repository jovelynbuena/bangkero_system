<?php
session_start();
require_once('../../config/db_connect.php');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ========================================
// SECURITY CHECK
// ========================================
if (empty($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

$role = strtolower($_SESSION['role'] ?? 'guest');
if (!in_array($role, ['admin', 'officer'])) {
    header("Location: ../login.php");
    exit;
}

$successMsg = $errorMsg = "";

// ========================================
// MARK AS READ ACTION (SECURE)
// ========================================
if (isset($_GET['action']) && $_GET['action'] == 'mark_read' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    if ($id <= 0) {
        $errorMsg = "Invalid message ID!";
    } else {
        $stmt = $conn->prepare("UPDATE contact_messages SET status='read' WHERE id=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $successMsg = "Message marked as read!";
        } else {
            $errorMsg = "Error marking message as read!";
        }
        $stmt->close();
    }
    
    $_SESSION['swal'] = ['type' => ($successMsg ? 'success' : 'error'), 'message' => ($successMsg ?: $errorMsg)];
    header("Location: contact_messages.php");
    exit;
}

// ========================================
// ARCHIVE ACTION (SECURE)
// ========================================
if (isset($_GET['action']) && $_GET['action'] == 'archive' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    if ($id <= 0) {
        $errorMsg = "Invalid message ID!";
    } else {
        try {
            $conn->begin_transaction();
            
            // Move to archive
            $stmt = $conn->prepare("INSERT INTO contact_messages_archive (original_id, name, email, message, status, created_at) SELECT id, name, email, message, status, created_at FROM contact_messages WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            
            // Delete from main
            $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
            $successMsg = "Message archived successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $errorMsg = "Error archiving message: " . $e->getMessage();
        }
    }
    
    $_SESSION['swal'] = ['type' => ($successMsg ? 'success' : 'error'), 'message' => ($successMsg ?: $errorMsg)];
    header("Location: contact_messages.php");
    exit;
}

// ========================================
// FETCH STATISTICS
// ========================================
$totalMessagesQuery = $conn->query("SELECT COUNT(*) as count FROM contact_messages");
$totalMessages = $totalMessagesQuery->fetch_assoc()['count'];

$unreadMessagesQuery = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status='unread'");
$unreadMessages = $unreadMessagesQuery->fetch_assoc()['count'];

$readMessages = $totalMessages - $unreadMessages;

// ========================================
// FETCH ALL MESSAGES
// ========================================
$result = $conn->query("SELECT * FROM contact_messages ORDER BY 
    CASE WHEN status='unread' THEN 0 ELSE 1 END, created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Messages | Admin Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
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

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 32px;
        border-radius: 20px;
        color: white;
        margin-bottom: 32px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    .page-header h2 {
        font-size: 32px;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .page-header p {
        margin: 8px 0 0 0;
        opacity: 0.9;
        font-size: 15px;
    }

    /* Statistics Cards */
    .stats-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-left: 4px solid;
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .stats-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }
    .stats-card .icon {
        font-size: 48px;
        opacity: 0.8;
    }
    .stats-card h3 {
        font-size: 36px;
        font-weight: 700;
        margin: 0;
        color: #2d3748;
    }
    .stats-card p {
        margin: 0;
        color: #718096;
        font-size: 14px;
        font-weight: 500;
    }

    /* Card Styles */
    .card { 
        border: none; 
        border-radius: 20px; 
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12); 
        margin-bottom: 24px;
        background: white;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.16);
    }
    
    .card-header { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white; 
        font-weight: 600; 
        font-size: 18px;
        padding: 20px 24px;
        border-bottom: none;
    }
    
    .card-header i {
        margin-right: 10px;
    }
    
    .card-body { 
        padding: 24px; 
    }

    /* Toolbar */
    .toolbar-card {
        background: white;
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .toolbar-card .d-flex {
        gap: 16px;
    }

    /* Form Controls */
    .form-select-sm, .form-control {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 10px 14px;
        transition: all 0.3s ease;
        font-size: 14px;
    }
    
    .form-select-sm:focus, .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    /* Search Input Group */
    .input-group-text {
        background: white;
        border: 2px solid #e9ecef;
        border-right: none;
        border-radius: 12px 0 0 12px;
    }
    .input-group .form-control {
        border-left: none;
        border-radius: 0 12px 12px 0;
    }

    /* Table Styles */
    .table { 
        font-size: 14px;
        margin: 0;
    }
    
    .table thead th { 
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white; 
        border: none;
        padding: 16px 12px;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        vertical-align: middle;
    }
    
    .table tbody td {
        padding: 16px 12px;
        vertical-align: middle;
        border-color: #f1f3f5;
        color: #2d3748;
        font-weight: 500;
    }
    
    .table-hover tbody tr {
        transition: all 0.3s ease;
    }
    
    .table-hover tbody tr:hover { 
        background-color: #f8f9ff;
        transform: scale(1.002);
    }

    /* Message Text */
    .message-text {
        max-width: 300px;
        white-space: normal;
        word-wrap: break-word;
        font-size: 13px;
        line-height: 1.5;
    }

    /* Badges */
    .badge {
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-read { 
        background: #10b981; 
        color: white;
    }
    .badge-unread { 
        background: #f59e0b; 
        color: white;
    }

    /* Button Styles */
    .btn { 
        font-size: 13px;
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    
    .btn-success {
        background: #10b981;
        color: white;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
    }
    
    .btn-success:hover {
        background: #059669;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    
    .btn-danger {
        background: #ef4444;
        color: white;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
    }
    
    .btn-danger:hover {
        background: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    /* Pagination */
    .pagination {
        gap: 8px;
        margin-top: 20px;
    }
    .page-item .page-link {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        color: #667eea;
        font-weight: 600;
        padding: 8px 14px;
        transition: all 0.3s ease;
    }
    .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
    }
    .page-item .page-link:hover {
        background: #f8f9ff;
        border-color: #667eea;
        transform: translateY(-2px);
    }

    /* Responsive */
    @media (max-width: 991.98px) { 
        .main-content { 
            margin-left: 0; 
            padding: 20px; 
        }
        .page-header h2 {
            font-size: 24px;
        }
        .stats-card {
            margin-bottom: 16px;
        }
    }
</style>
</head>
<body>

<?php include("../navbar.php"); ?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h2><i class="bi bi-envelope-fill"></i>Contact Messages</h2>
            <p>View and manage messages from website visitors</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stats-card" style="border-left-color: #667eea;">
                <div class="icon" style="color: #667eea;">
                    <i class="bi bi-inbox-fill"></i>
                </div>
                <div>
                    <h3><?= $totalMessages ?></h3>
                    <p>Total Messages</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stats-card" style="border-left-color: #f59e0b;">
                <div class="icon" style="color: #f59e0b;">
                    <i class="bi bi-envelope-exclamation-fill"></i>
                </div>
                <div>
                    <h3><?= $unreadMessages ?></h3>
                    <p>Unread Messages</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stats-card" style="border-left-color: #10b981;">
                <div class="icon" style="color: #10b981;">
                    <i class="bi bi-envelope-check-fill"></i>
                </div>
                <div>
                    <h3><?= $readMessages ?></h3>
                    <p>Read Messages</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0 text-muted" style="font-size: 14px; font-weight: 500;">Show</label>
                <select class="form-select-sm" id="entriesPerPage" style="width: auto;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <label class="mb-0 text-muted" style="font-size: 14px; font-weight: 500;">entries</label>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <select class="form-select-sm" id="filterStatus" style="width: 140px;">
                    <option value="">All Status</option>
                    <option value="unread">Unread</option>
                    <option value="read">Read</option>
                </select>
                
                <div class="input-group" style="width: 280px;">
                    <span class="input-group-text">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search messages...">
                </div>
            </div>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-table"></i> All Contact Messages
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0" id="messagesTable">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 13%;">Name</th>
                        <th style="width: 15%;">Email</th>
                        <th style="width: 30%;">Message</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 12%;">Date</th>
                        <th style="width: 15%;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr data-status="<?= htmlspecialchars($row['status']) ?>">
                                <td style="font-weight: 600;"><?= htmlspecialchars($row['id']) ?></td>
                                <td style="font-weight: 600;"><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td class="message-text"><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                                <td>
                                    <?php if ($row['status'] == 'read'): ?>
                                        <span class="badge badge-read"><i class="bi bi-check-circle me-1"></i>Read</span>
                                    <?php else: ?>
                                        <span class="badge badge-unread"><i class="bi bi-exclamation-circle me-1"></i>Unread</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 12px;"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <?php if ($row['status'] == 'unread'): ?>
                                        <button class="btn btn-success btn-sm mark-read" data-id="<?= $row['id'] ?>" title="Mark as Read">
                                            <i class="bi bi-envelope-open"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-warning btn-sm archive-message" data-id="<?= $row['id'] ?>" title="Archive Message">
                                        <i class="bi bi-archive"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No messages found
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center" id="pagination"></ul>
    </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ========================================
// PAGINATION & FILTERING
// ========================================
let currentPage = 1;
let entriesPerPage = 10;

function filterAndPaginate() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('filterStatus').value.toLowerCase();
    const allRows = Array.from(document.querySelectorAll('#tableBody tr'));
    
    // Filter rows
    const filteredRows = allRows.filter(row => {
        if (row.cells.length < 7) return false; // Skip empty state row
        
        const rowText = row.textContent.toLowerCase();
        const rowStatus = row.dataset.status || '';
        
        const matchesSearch = rowText.includes(searchTerm);
        const matchesStatus = !statusFilter || rowStatus === statusFilter;
        
        return matchesSearch && matchesStatus;
    });
    
    // Hide all rows first
    allRows.forEach(row => row.style.display = 'none');
    
    // Calculate pagination
    const totalFiltered = filteredRows.length;
    const totalPages = Math.ceil(totalFiltered / entriesPerPage);
    currentPage = Math.min(currentPage, Math.max(1, totalPages));
    
    // Show current page rows
    const start = (currentPage - 1) * entriesPerPage;
    const end = start + entriesPerPage;
    filteredRows.slice(start, end).forEach(row => row.style.display = '');
    
    // Update pagination
    updatePagination(totalPages);
}

function updatePagination(totalPages) {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#"><i class="bi bi-chevron-left"></i></a>`;
    prevLi.onclick = (e) => { e.preventDefault(); if (currentPage > 1) { currentPage--; filterAndPaginate(); }};
    pagination.appendChild(prevLi);
    
    // Page numbers (smart display)
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);
    
    if (startPage > 1) {
        const li = document.createElement('li');
        li.className = 'page-item';
        li.innerHTML = `<a class="page-link" href="#">1</a>`;
        li.onclick = (e) => { e.preventDefault(); currentPage = 1; filterAndPaginate(); };
        pagination.appendChild(li);
        
        if (startPage > 2) {
            const ellipsis = document.createElement('li');
            ellipsis.className = 'page-item disabled';
            ellipsis.innerHTML = `<span class="page-link">...</span>`;
            pagination.appendChild(ellipsis);
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
        li.onclick = (e) => { e.preventDefault(); currentPage = i; filterAndPaginate(); };
        pagination.appendChild(li);
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const ellipsis = document.createElement('li');
            ellipsis.className = 'page-item disabled';
            ellipsis.innerHTML = `<span class="page-link">...</span>`;
            pagination.appendChild(ellipsis);
        }
        
        const li = document.createElement('li');
        li.className = 'page-item';
        li.innerHTML = `<a class="page-link" href="#">${totalPages}</a>`;
        li.onclick = (e) => { e.preventDefault(); currentPage = totalPages; filterAndPaginate(); };
        pagination.appendChild(li);
    }
    
    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#"><i class="bi bi-chevron-right"></i></a>`;
    nextLi.onclick = (e) => { e.preventDefault(); if (currentPage < totalPages) { currentPage++; filterAndPaginate(); }};
    pagination.appendChild(nextLi);
}

// Event listeners
document.getElementById('searchInput').addEventListener('input', () => {
    currentPage = 1;
    filterAndPaginate();
});

document.getElementById('filterStatus').addEventListener('change', () => {
    currentPage = 1;
    filterAndPaginate();
});

document.getElementById('entriesPerPage').addEventListener('change', function() {
    entriesPerPage = parseInt(this.value);
    currentPage = 1;
    filterAndPaginate();
});

// Initial load
filterAndPaginate();

// ========================================
// SWEETALERT ACTIONS
// ========================================
document.querySelectorAll('.archive-message').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        Swal.fire({
            title: 'Archive Message?',
            text: "This message will be moved to the archive.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-archive me-2"></i>Yes, Archive',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "?action=archive&id=" + id;
            }
        });
    });
});

document.querySelectorAll('.mark-read').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        Swal.fire({
            title: 'Mark as Read?',
            text: "This message will be marked as read.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="bi bi-check-circle me-2"></i>Mark as Read',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "?action=mark_read&id=" + id;
            }
        });
    });
});

// ========================================
// SERVER MESSAGES
// ========================================
<?php if(isset($_SESSION['swal'])): ?>
Swal.fire({
    icon: '<?= $_SESSION['swal']['type'] ?>',
    title: '<?= $_SESSION['swal']['message'] ?>',
    showConfirmButton: false,
    timer: 2000
});
<?php unset($_SESSION['swal']); endif; ?>
</script>

</body>
</html>
