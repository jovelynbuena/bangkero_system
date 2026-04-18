<?php
ob_start();
mysqli_report(MYSQLI_REPORT_OFF); // keep off globally to avoid HTML exceptions
session_start();
require_once('../../config/db_connect.php');

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
// MARK AS REPLIED ACTION (AJAX)
// ========================================
if (isset($_GET['action']) && $_GET['action'] == 'mark_replied') {
    ob_end_clean();
    header('Content-Type: application/json');

    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid message ID']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE contact_messages SET status='replied' WHERE id=?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
    exit;
}

// ========================================
// MARK AS READ ACTION (AJAX - single ID only)
// ========================================
if (isset($_GET['action']) && $_GET['action'] == 'mark_read') {
    ob_end_clean();
    header('Content-Type: application/json');

    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid message ID']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE contact_messages SET status='read' WHERE id=?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
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
// ENSURE 'replied' IS A VALID ENUM VALUE
// ========================================
$conn->query("ALTER TABLE contact_messages MODIFY status ENUM('unread','read','replied') NOT NULL DEFAULT 'unread'");

// ========================================
// FETCH STATISTICS
// ========================================
$totalMessagesQuery = $conn->query("SELECT COUNT(*) as count FROM contact_messages");
$totalMessages = $totalMessagesQuery->fetch_assoc()['count'];

$unreadMessagesQuery = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status='unread'");
$unreadMessages = $unreadMessagesQuery->fetch_assoc()['count'];

$readMessages = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status IN ('read','replied')")->fetch_assoc()['count'];

// ========================================
// FETCH ALL MESSAGES
// ========================================
$msgResult = $conn->query("SELECT id, name, email, message, status, created_at FROM contact_messages ORDER BY 
    CASE WHEN status='unread' THEN 0 WHEN status='replied' THEN 1 ELSE 2 END, created_at DESC");
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
    .badge-replied {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #5a6fd6 0%, #6a4295 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        color: white;
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
                    <h3 id="stat-total"><?= $totalMessages ?></h3>
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
                    <h3 id="stat-unread"><?= $unreadMessages ?></h3>
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
                    <h3 id="stat-read"><?= $readMessages ?></h3>
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
                    <option value="replied">Replied</option>
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
                    <?php if ($msgResult && $msgResult->num_rows > 0): ?>
                        <?php while ($msgRow = $msgResult->fetch_assoc()): ?>
                            <tr data-status="<?= htmlspecialchars($msgRow['status']) ?>" data-prev-status="<?= htmlspecialchars($msgRow['status']) ?>">
                                <td style="font-weight: 600;"><?= htmlspecialchars($msgRow['id']) ?></td>
                                <td style="font-weight: 600;"><?= htmlspecialchars($msgRow['name']) ?></td>
                                <td><?= htmlspecialchars($msgRow['email']) ?></td>
                                <td class="message-text"><?= nl2br(htmlspecialchars($msgRow['message'])) ?></td>
                                <td>
                                    <?php if ($msgRow['status'] == 'read'): ?>
                                        <span class="badge badge-read"><i class="bi bi-check-circle me-1"></i>Read</span>
                                    <?php elseif ($msgRow['status'] == 'replied'): ?>
                                        <span class="badge badge-replied"><i class="bi bi-reply-fill me-1"></i>Replied</span>
                                    <?php else: ?>
                                        <span class="badge badge-unread"><i class="bi bi-exclamation-circle me-1"></i>Unread</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 12px;"><?= date('M d, Y', strtotime($msgRow['created_at'])) ?></td>
                                <td>
                                    <?php if ($msgRow['status'] == 'unread'): ?>
                                        <button class="btn btn-success btn-sm mark-read" data-id="<?= (int)$msgRow['id'] ?>" title="Mark as Read">
                                            <i class="bi bi-envelope-open"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-primary btn-sm reply-message"
                                        data-id="<?= (int)$msgRow['id'] ?>"
                                        data-name="<?= htmlspecialchars($msgRow['name'], ENT_QUOTES) ?>"
                                        data-email="<?= htmlspecialchars($msgRow['email'], ENT_QUOTES) ?>"
                                        data-message="<?= htmlspecialchars($msgRow['message'], ENT_QUOTES) ?>"
                                        title="Reply">
                                        <i class="bi bi-reply-fill"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm archive-message" data-id="<?= (int)$msgRow['id'] ?>" title="Archive Message">
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

<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius: 18px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
      <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 18px 18px 0 0; color: white; border: none;">
        <h5 class="modal-title fw-bold" id="replyModalLabel">
          <i class="bi bi-reply-fill me-2"></i>Reply to Message
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <!-- Original Message Preview -->
        <div class="mb-3 p-3" style="background: #f8f9ff; border-radius: 12px; border-left: 4px solid #667eea;">
          <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi bi-person-fill text-primary"></i>
            <strong id="replyOriginalName" class="text-primary"></strong>
            <span class="text-muted" style="font-size: 13px;">wrote:</span>
          </div>
          <p id="replyOriginalMessage" class="mb-0 text-muted" style="font-size: 13px; font-style: italic;"></p>
        </div>

        <!-- Reply Form -->
        <div class="mb-3">
          <label class="form-label fw-semibold">To:</label>
          <input type="email" id="replyToEmail" class="form-control" readonly
                 style="background: #f8f9ff; border-radius: 10px;">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Subject:</label>
          <input type="text" id="replySubject" class="form-control"
                 style="border-radius: 10px;"
                 value="Re: Your message to Bankero & Fishermen Association">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Message:</label>
          <textarea id="replyBody" class="form-control" rows="6"
                    style="border-radius: 10px; resize: vertical;"
                    placeholder="Type your reply here..."></textarea>
          <small class="text-muted mt-1 d-block">
            <i class="bi bi-info-circle me-1"></i>
            Your reply will be auto-formatted with the association's signature before sending.
          </small>
        </div>

        <!-- Step 2: Confirmation panel (hidden by default) -->
        <div id="replyConfirmPanel" style="display:none; border-radius: 12px; border: 2px solid #667eea; background: #f0f2ff; padding: 16px; margin-top: 4px;">
          <p class="fw-semibold mb-1" style="color: #667eea; font-size: 14px;">
            <i class="bi bi-send-check me-2"></i>Ready to send this reply?
          </p>
          <p class="mb-3 text-muted" style="font-size: 13px;">
            Gmail will open in a new tab with your message pre-filled.<br>
            Please click <strong>Send</strong> inside Gmail to complete the delivery.
          </p>
          <div class="d-flex gap-2 flex-wrap">
            <button type="button" id="confirmSendGmailBtn" class="btn btn-sm fw-semibold"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; min-width: 160px;">
              <i class="bi bi-google me-1"></i><span id="gmailBtnLabel">Send via Gmail</span>
            </button>
            <button type="button" id="cancelConfirmBtn" class="btn btn-sm btn-outline-secondary fw-semibold" style="border-radius: 10px;">
              <i class="bi bi-arrow-left me-1"></i>Go Back
            </button>
          </div>
        </div>
      </div>

      <div class="modal-footer" style="border: none; padding: 16px 24px 20px;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px;">
          <i class="bi bi-x-circle me-1"></i>Cancel
        </button>
        <button type="button" id="sendReplyBtn" class="btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; min-width: 140px;">
          <i class="bi bi-send-fill me-1"></i>Send Reply
        </button>
      </div>
    </div>
  </div>
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
// REPLY MESSAGE — IMPROVED FLOW
// ========================================
let currentReplyId   = null;
let currentReplyRow  = null;

function resetReplyModal() {
    document.getElementById('replyConfirmPanel').style.display = 'none';
    document.getElementById('sendReplyBtn').style.display      = '';
    document.getElementById('replyBody').disabled              = false;
    document.getElementById('replySubject').disabled           = false;
    const lbl = document.getElementById('gmailBtnLabel');
    if (lbl) lbl.textContent = 'Send via Gmail';
    const btn = document.getElementById('confirmSendGmailBtn');
    if (btn) btn.disabled = false;
}

// Reset modal state when closed
document.getElementById('replyModal').addEventListener('hidden.bs.modal', resetReplyModal);

// Open modal on Reply button click
document.querySelectorAll('.reply-message').forEach(btn => {
    btn.addEventListener('click', function() {
        const name    = this.getAttribute('data-name');
        const email   = this.getAttribute('data-email');
        const message = this.getAttribute('data-message');
        currentReplyId  = this.getAttribute('data-id');
        currentReplyRow = this.closest('tr');

        document.getElementById('replyOriginalName').textContent    = name;
        document.getElementById('replyOriginalMessage').textContent = message;
        document.getElementById('replyToEmail').value               = email;
        document.getElementById('replyBody').value                  = '';
        resetReplyModal();

        new bootstrap.Modal(document.getElementById('replyModal')).show();
    });
});

// Step 1 — "Send Reply" validates and shows confirmation panel
document.getElementById('sendReplyBtn').addEventListener('click', function() {
    const body = document.getElementById('replyBody').value.trim();
    if (!body) {
        Swal.fire({ icon: 'warning', title: 'Empty Reply', text: 'Please type your reply message before sending.', confirmButtonColor: '#667eea' });
        return;
    }

    // Show confirmation panel, hide the Send Reply button
    document.getElementById('replyConfirmPanel').style.display = 'block';
    document.getElementById('sendReplyBtn').style.display      = 'none';
    document.getElementById('replyBody').disabled              = true;
    document.getElementById('replySubject').disabled           = true;

    // Scroll confirmation into view smoothly
    document.getElementById('replyConfirmPanel').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
});

// Step 1b — "Go Back" hides confirmation panel
document.getElementById('cancelConfirmBtn').addEventListener('click', function() {
    document.getElementById('replyConfirmPanel').style.display = 'none';
    document.getElementById('sendReplyBtn').style.display      = '';
    document.getElementById('replyBody').disabled              = false;
    document.getElementById('replySubject').disabled           = false;
});

// Step 2 — "Send via Gmail" opens Gmail, marks as replied, updates UI
document.getElementById('confirmSendGmailBtn').addEventListener('click', function() {
    const to      = document.getElementById('replyToEmail').value;
    const subject = document.getElementById('replySubject').value.trim();
    const rawBody = document.getElementById('replyBody').value.trim();

    // Auto-format email with association signature template
    const formattedBody = `Hello,\n\n${rawBody}\n\nRegards,\nBangkero & Fishermen Association`;

    // Loading state
    const btn = document.getElementById('confirmSendGmailBtn');
    btn.disabled = true;
    document.getElementById('gmailBtnLabel').textContent = 'Opening Gmail...';

    // Build Gmail compose link (fs=1 forces full compose window)
    const gmailLink = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(to)}&su=${encodeURIComponent(subject)}&body=${encodeURIComponent(formattedBody)}`;
    window.open(gmailLink, '_blank');

    // Mark message as "replied" in the database via AJAX
    if (currentReplyId && currentReplyRow) {
        const row = currentReplyRow;
        fetch(`contact_messages.php?action=mark_replied&id=${currentReplyId}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const badgeCell = row.querySelector('td:nth-child(5)');
                    if (badgeCell) badgeCell.innerHTML = '<span class="badge badge-replied"><i class="bi bi-reply-fill me-1"></i>Replied</span>';
                    row.dataset.status = 'replied';
                    // Remove "Mark as Read" button if present (no longer needed)
                    const markReadBtn = row.querySelector('.mark-read');
                    if (markReadBtn) markReadBtn.remove();
                    // Update stat counters
                    const unreadEl = document.getElementById('stat-unread');
                    const readEl   = document.getElementById('stat-read');
                    if (unreadEl && row.dataset.prevStatus === 'unread')
                        unreadEl.textContent = Math.max(0, parseInt(unreadEl.textContent) - 1);
                    if (readEl) readEl.textContent = parseInt(readEl.textContent) + 1;
                }
            }).catch(() => {});
    }

    // Close modal, show success feedback
    bootstrap.Modal.getInstance(document.getElementById('replyModal')).hide();
    Swal.fire({
        icon: 'success',
        title: 'Redirecting to Gmail...',
        html: 'Your reply has been pre-filled.<br><small class="text-muted">Click <strong>Send</strong> inside Gmail to complete delivery.</small>',
        confirmButtonColor: '#667eea',
        confirmButtonText: 'Got it'
    });
});

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
        const row = this.closest('tr');
        const self = this;

        // Directly call fetch — no Swal confirm to avoid CSP eval issues
        fetch('/bangkero_system/index/management/mark_read_ajax.php?id=' + id)
            .then(r => r.text())
            .then(raw => {
                let data;
                try { data = JSON.parse(raw); } catch(e) {
                    console.error('Non-JSON response:', raw);
                    alert('Error: Server returned unexpected response.');
                    return;
                }
                if (data.success) {
                    const badgeCell = row.querySelector('td:nth-child(5)');
                    if (badgeCell) badgeCell.innerHTML = '<span class="badge badge-read"><i class="bi bi-check-circle me-1"></i>Read</span>';
                    row.dataset.status = 'read';
                    self.remove();
                    const unreadEl = document.getElementById('stat-unread');
                    const readEl   = document.getElementById('stat-read');
                    if (unreadEl) unreadEl.textContent = Math.max(0, parseInt(unreadEl.textContent) - 1);
                    if (readEl)   readEl.textContent   = parseInt(readEl.textContent) + 1;
                    Swal.fire({ icon: 'success', title: 'Marked as Read!', showConfirmButton: false, timer: 1500 });
                } else {
                    alert('Failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                alert('Network error: ' + err.message);
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
