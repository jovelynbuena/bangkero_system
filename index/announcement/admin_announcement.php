<?php 
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}
include('../../config/db_connect.php');

$memberName = $_SESSION['fullname'] ?? $_SESSION['member_name'] ?? ucfirst($_SESSION['role'] ?? 'Member');
$user_id = $_SESSION['user_id'] ?? 0;
$role = $_SESSION['role'] ?? 'Member';

$error = '';
$success = $_GET['success'] ?? false;

// ✅ Handle Add or Edit Announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $expiry_date = trim($_POST['expiry_date'] ?? '');
    $expiry_date = ($expiry_date === '' || $expiry_date === '0000-00-00') ? null : $expiry_date;
    $image = null;

    // 🔸 Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTemp = $_FILES['image']['tmp_name'];
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $imageDir = '../../uploads/';
        $imagePath = $imageDir . $imageName;

        if (!is_dir($imageDir)) mkdir($imageDir, 0777, true);
        if (move_uploaded_file($imageTemp, $imagePath)) $image = $imageName;
    }

    // 🟠 Add Announcement
    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO announcements (title, content, image, category, expiry_date, posted_by, date_posted) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('ssssss', $title, $content, $image, $category, $expiry_date, $memberName);
        if ($stmt->execute()) {
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES (?, 'Added announcement', ?, NOW())");
            $desc = "Title: " . $title;
            $log_stmt->bind_param("is", $user_id, $desc);
            $log_stmt->execute();
            $stmt->close();
            header('Location: admin_announcement.php?success=added');
            exit;
        } else {
            $error = "Failed to add announcement.";
        }
        $stmt->close();
    }

    // 🟣 Edit Announcement
    elseif ($action === 'edit') {
        $id = intval($_POST['announcement_id']);
        if ($image) {
            $stmt = $conn->prepare("UPDATE announcements SET title=?, content=?, image=?, category=?, expiry_date=? WHERE id=?");
            $stmt->bind_param('sssssi', $title, $content, $image, $category, $expiry_date, $id);
        } else {
            $stmt = $conn->prepare("UPDATE announcements SET title=?, content=?, category=?, expiry_date=? WHERE id=?");
            $stmt->bind_param('ssssi', $title, $content, $category, $expiry_date, $id);
        }
        if ($stmt->execute()) {
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, created_at) VALUES (?, 'Edited announcement', ?, NOW())");
            $desc = "Edited Title: " . $title;
            $log_stmt->bind_param("is", $user_id, $desc);
            $log_stmt->execute();
            $stmt->close();
            header('Location: admin_announcement.php?success=edited');
            exit;
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

            // Redirect back to the same admin page (keep current filters) + show success toast
            $redirectParams = $_GET;
            unset($redirectParams['archive']);
            $redirectParams['archived'] = 1;
            $qs = http_build_query($redirectParams);

            header('Location: admin_announcement.php' . ($qs ? ('?' . $qs) : ''));
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
$filter_category = trim($_GET['category'] ?? '');
$filter_status = trim($_GET['status'] ?? '');

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

if ($filter_category !== '') {
    $where[] = "category = ?";
    $params[] = $filter_category;
    $types .= 's';
}

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

$announcements_count = $announcements ? (int)($announcements->num_rows ?? 0) : 0;

// Helper function to determine announcement status
function getAnnouncementStatus($expiry_date) {

    if (empty($expiry_date) || $expiry_date === '0000-00-00' || $expiry_date === '0000-00-00 00:00:00') return 'ongoing';
    
    $today = new DateTime();
    $today->setTime(0, 0, 0); // compare by date only
    $expiry = new DateTime($expiry_date);
    $expiry->setTime(0, 0, 0);
    
    $diff = $today->diff($expiry);
    
    if ($expiry < $today) {
        return 'expired';
    } elseif ($diff->days <= 3) {
        return 'upcoming';
    } else {
        return 'ongoing';
    }
}

// Helper function to get icon class based on category (no emojis)
function getCategoryIcon($category) {
    $icons = [
        'Event' => 'bi-calendar-event',
        'Fishing' => 'bi-water',
        'Meeting' => 'bi-people',
        'Reminder' => 'bi-bell',
        'General' => 'bi-megaphone',
        'Emergency' => 'bi-exclamation-triangle'
    ];
    return $icons[$category] ?? 'bi-megaphone';
}


// Get time-based greeting (Philippine Time)
date_default_timezone_set('Asia/Manila');
$hour = date('G');
if ($hour >= 5 && $hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour >= 12 && $hour < 18) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="../../css/admin-theme.css">
<style>
body { 
    font-family: 'Inter', 'Segoe UI', sans-serif; 
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    color: #333;
    min-height: 100vh;
}
.main-content { 
    margin-left: 250px; 
    padding: 32px; 
    min-height: 100vh; 
}

/* Page Header */
.page-header {
    background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
    padding: 32px;
    border-radius: 20px;
    color: white;
    margin-bottom: 32px;
    box-shadow: 0 10px 30px rgba(46, 134, 171, 0.30);
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
    font-size: 1rem;
}

/* Announcement Cards */
.announcement-item { 
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
    position: relative;
}
.announcement-item:hover { 
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

/* Status Badge */
.status-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}
.status-ongoing {
    background: #e8f5e9;
    color: #2e7d32;
}
.status-upcoming {
    background: #fff3e0;
    color: #e65100;
}
.status-expired {
    background: #f5f5f5;
    color: #757575;
}

/* Expired announcement styling */
.announcement-item.expired {
    opacity: 0.7;
    background: #fafafa;
}
.announcement-item.expired h6 {
    color: #9e9e9e;
}

/* Title with Icon */
.announcement-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
    padding-right: 100px;
}
.announcement-icon {
    font-size: 1.8rem;
    line-height: 1;
    flex-shrink: 0;
}
.announcement-item h6 { 
    font-weight: 700;
    color: #222;
    margin: 0;
    font-size: 1.15rem;
    line-height: 1.4;
}

/* Meta info */
.announcement-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    color: #666;
    font-size: 0.85rem;
    margin-bottom: 12px;
    flex-wrap: wrap;
}
.announcement-meta i {
    margin-right: 4px;
}

/* Content */
.announcement-content {
    color: #555;
    line-height: 1.6;
    margin-bottom: 16px;
    font-size: 0.95rem;
}
.announcement-content.truncated {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.announcement-content.expanded {
    display: block;
}

/* Divider */
.announcement-divider {
    border-top: 1px solid #e0e0e0;
    margin: 16px 0;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.btn-action {
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-view {
    background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
    color: white;
}
.btn-view:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    color: white;
}
.btn-edit {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}
.btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
    color: white;
}
.btn-archive {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}
.btn-archive:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    color: white;
}

/* Primary Buttons with Gradient */
.btn-primary { 
    background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
    border: none;
    font-weight: 600;
    color: white;
    box-shadow: 0 4px 16px rgba(46, 134, 171, 0.30);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.btn-primary:hover { 
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(46, 134, 171, 0.40);
    background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
    border: none;
    color: white;
}

/* Add Button (light style with gradient) */
.btn-light {
    background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
    border: none;
    color: white;
    font-weight: 600;
    padding: 12px 28px;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(46, 134, 171, 0.30);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.btn-light:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(46, 134, 171, 0.40);
    background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
    color: white;
}

/* Modal */
.modal-header { 
    background-color: #1a73e8;
    color: #fff;
}

/* Filter Row */
.filter-row .form-control, .filter-row .form-select { 
    height: calc(2.25rem + 6px);
    border-radius: 6px;
}
.filter-row .btn {
    border-radius: 6px;
}

/* Export Dropdown (announcements, match memberlist style) */
.export-dropdown .btn-export {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
    transition: all 0.2s ease;
}
.export-dropdown .btn-export:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(16, 185, 129, 0.45);
    color: white;
}
.export-dropdown .dropdown-menu {
    display: none;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.18);
    border: none;
    padding: 8px;
    min-width: 210px;
    font-size: 13px;
}
.export-dropdown.show .dropdown-menu {
    display: block;
}

.export-dropdown .dropdown-item {
    border-radius: 8px;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.export-dropdown .dropdown-item i {
    width: 18px;
}
.export-dropdown .dropdown-item:hover {
    background-color: #f8fafc;
}

/* Filter Search Box (with icon, match memberlist style but compact) */
.filter-row .search-box {
    position: relative;
    width: 100%;
}
.filter-row .search-box i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 14px;
}
.filter-row .search-box input.form-control-sm {
    padding-left: 32px;
}



/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.empty-state i {
    font-size: 4rem;
    color: #bdbdbd;
    margin-bottom: 16px;
}
.empty-state h5 {
    color: #666;
    margin-bottom: 8px;
}
.empty-state p {
    color: #999;
    margin: 0;
}

/* Read More Link */
.read-more-link {
    color: #1a73e8;
    cursor: pointer;
    font-weight: 500;
    font-size: 0.85rem;
}
.read-more-link:hover {
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 20px;
    }
    .announcement-header {
        padding-right: 0;
        margin-bottom: 8px;
    }
    .status-badge {
        position: static;
        display: inline-block;
        margin-bottom: 8px;
    }
    .action-buttons {
        width: 100%;
    }
    .btn-action {
        flex: 1;
        justify-content: center;
    }
}

/* SweetAlert Custom Styling */
.swal2-popup.swal-custom {
    border-radius: 16px;
    padding: 24px;
}

.swal2-title {
    font-family: 'Inter', sans-serif !important;
    font-weight: 700 !important;
    color: #333 !important;
}

.swal2-html-container {
    font-family: 'Inter', sans-serif !important;
    color: #555 !important;
}

.btn-gradient {
    background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%) !important;
    border: none !important;
    font-weight: 600 !important;
    padding: 10px 24px !important;
    border-radius: 8px !important;
    transition: all 0.3s ease !important;
}

.btn-gradient:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(46, 134, 171, 0.40) !important;
}

.btn-cancel {
    font-weight: 600 !important;
    border-radius: 8px !important;
}

.swal2-toast {
    border-radius: 12px !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
}

.swal2-toast .swal2-title {
    font-size: 1rem !important;
    font-weight: 600 !important;
}

.colored-toast .swal2-icon {
    margin: 0 !important;
}
</style>
</head>
<body>
<?php include('../navbar.php'); ?>
<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2>
                    <i class="bi bi-megaphone-fill"></i>
                    Announcements Management
                </h2>
                <p>Create, update, and archive announcements. Use filters to narrow results.</p>

            </div>
            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                <i class="bi bi-plus-circle me-2"></i> Add Announcement
            </button>
        </div>
    </div>

    <!-- Search & Filters -->
    <form class="row g-2 align-items-center mb-4 filter-row" method="GET">
        <div class="col-auto" style="min-width:260px;">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input name="q" class="form-control form-control-sm" type="search" placeholder="Search title or content" value="<?= htmlspecialchars($search) ?>">
            </div>
        </div>

        <div class="col-auto">
            <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($date_from) ?>" title="From Date">
        </div>
        <div class="col-auto">
            <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($date_to) ?>" title="To Date">
        </div>
        <div class="col-auto">
            <select name="category" class="form-select form-select-sm">
                <option value="">All Categories</option>
                <option value="Event" <?= $filter_category === 'Event' ? 'selected' : '' ?>>Event</option>
                <option value="Fishing" <?= $filter_category === 'Fishing' ? 'selected' : '' ?>>Fishing</option>
                <option value="Meeting" <?= $filter_category === 'Meeting' ? 'selected' : '' ?>>Meeting</option>
                <option value="Reminder" <?= $filter_category === 'Reminder' ? 'selected' : '' ?>>Reminder</option>
                <option value="Emergency" <?= $filter_category === 'Emergency' ? 'selected' : '' ?>>Emergency</option>
                <option value="General" <?= $filter_category === 'General' ? 'selected' : '' ?>>General</option>
            </select>
        </div>
        <div class="col-auto">
            <select name="has_image" class="form-select form-select-sm">
                <option value="all" <?= $has_image === 'all' ? 'selected' : '' ?>>All Images</option>
                <option value="1" <?= $has_image === '1' ? 'selected' : '' ?>>With Image</option>
                <option value="0" <?= $has_image === '0' ? 'selected' : '' ?>>Without Image</option>
            </select>
        </div>

        <div class="col-auto">
            <button class="btn btn-sm btn-primary" type="submit">
                <i class="bi bi-search"></i> Filter
            </button>
            <a href="admin_announcement.php" class="btn btn-sm btn-secondary ms-1">Reset</a>
        </div>
        <div class="col-auto ms-auto">
            <div class="dropdown export-dropdown">
                <button class="btn-export dropdown-toggle" type="button" id="announcementExportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-download"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="announcementExportDropdown">
                    <li>
                        <a class="dropdown-item" href="export_announcements_csv.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8') : '' ?>">
                            <i class="bi bi-filetype-csv me-2"></i> CSV Format
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="export_announcements_print.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8') : '' ?>" target="_blank">
                            <i class="bi bi-printer me-2"></i> Print Preview
                        </a>
                    </li>
                </ul>

            </div>
        </div>
    </form>

    <?php
        // Separate active and expired
        $active_rows = [];
        $expired_rows = [];
        if ($announcements) {
            while ($row = $announcements->fetch_assoc()) {
                $st = getAnnouncementStatus($row['expiry_date'] ?? '');
                if ($st === 'expired') $expired_rows[] = $row;
                else $active_rows[] = $row;
            }
        }
        $active_count  = count($active_rows);
        $expired_count = count($expired_rows);

        $filters = [];
        if ($search !== '') $filters[] = 'Search: "' . htmlspecialchars($search) . '"';
        if ($filter_category !== '') $filters[] = 'Category: ' . htmlspecialchars($filter_category);
        if ($date_from !== '' || $date_to !== '') {
            $range  = 'Date: ';
            $range .= $date_from !== '' ? htmlspecialchars($date_from) : 'Any';
            $range .= ' to ';
            $range .= $date_to !== '' ? htmlspecialchars($date_to) : 'Any';
            $filters[] = $range;
        }
        if ($has_image === '1') $filters[] = 'With Image';
        elseif ($has_image === '0') $filters[] = 'Without Image';
    ?>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="text-muted small">
            <?php if (!empty($filters)): ?>
                Filtered: <?= implode(' | ', $filters) ?> (<?= $announcements_count ?> results)
            <?php else: ?>
                Showing all <?= $announcements_count ?> announcements
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" id="announcementTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#activeTab" type="button" role="tab">
                <i class="bi bi-megaphone me-1"></i> Active
                <span class="badge bg-success ms-1"><?= $active_count ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="expired-tab" data-bs-toggle="tab" data-bs-target="#expiredTab" type="button" role="tab">
                <i class="bi bi-clock-history me-1"></i> Expired
                <span class="badge bg-secondary ms-1"><?= $expired_count ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="announcementTabContent">

        <!-- ACTIVE TAB -->
        <div class="tab-pane fade show active" id="activeTab" role="tabpanel">
            <?php if (!empty($active_rows)): ?>
                <?php foreach ($active_rows as $row):
                    $status   = getAnnouncementStatus($row['expiry_date'] ?? '');
                    $category = $row['category'] ?? 'General';
                    $icon     = getCategoryIcon($category);
                    $postedBy = $row['posted_by'] ?? 'Admin';
                ?>
                <div class="announcement-item" data-id="<?= $row['id'] ?>">
                    <span class="status-badge status-<?= $status ?>">
                        <?= $status === 'upcoming' ? 'Expiring Soon' : 'Active' ?>
                    </span>
                    <div class="announcement-header">
                        <span class="announcement-icon"><i class="bi <?= $icon ?>"></i></span>
                        <div>
                            <h6><?= htmlspecialchars($row['title']) ?></h6>
                            <div class="announcement-meta">
                                <span><i class="bi bi-person-circle"></i> Posted by: <?= htmlspecialchars($postedBy) ?></span>
                                <span><i class="bi bi-calendar3"></i> <?= date("F j, Y", strtotime($row['date_posted'])) ?></span>
                                <?php if (!empty($row['expiry_date'])): ?>
                                    <span><i class="bi bi-clock-history"></i> Expires: <?= date("M j, Y", strtotime($row['expiry_date'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="announcement-content truncated" id="content-<?= $row['id'] ?>">
                        <?= nl2br(htmlspecialchars($row['content'])) ?>
                    </div>
                    <span class="read-more-link" onclick="toggleContent(<?= $row['id'] ?>)">
                        <span id="toggle-text-<?= $row['id'] ?>">Read more...</span>
                    </span>
                    <div class="announcement-divider"></div>
                    <div class="action-buttons">
                        <a href="#" class="btn-action btn-view view-btn"
                           title="View" data-bs-placement="top"
                           data-title="<?= htmlspecialchars($row['title']) ?>"
                           data-content="<?= htmlspecialchars($row['content']) ?>"
                           data-image="<?= htmlspecialchars($row['image'] ?? '') ?>"
                           data-category="<?= htmlspecialchars($category) ?>"
                           data-posted-by="<?= htmlspecialchars($postedBy) ?>"
                           data-date="<?= date("F j, Y", strtotime($row['date_posted'])) ?>"
                           data-expiry="<?= !empty($row['expiry_date']) ? date("F j, Y", strtotime($row['expiry_date'])) : 'N/A' ?>"
                           data-bs-toggle="modal" data-bs-target="#viewAnnouncementModal">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="#" class="btn-action btn-edit edit-btn"
                           title="Edit" data-bs-placement="top"
                           data-id="<?= $row['id'] ?>"
                           data-title="<?= htmlspecialchars($row['title']) ?>"
                           data-content="<?= htmlspecialchars($row['content']) ?>"
                           data-category="<?= htmlspecialchars($category) ?>"
                           data-expiry="<?= htmlspecialchars($row['expiry_date'] ?? '') ?>"
                           data-bs-toggle="modal" data-bs-target="#editAnnouncementModal">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a href="#" class="btn-action btn-archive archive-announcement"
                           title="Archive" data-bs-placement="top"
                           data-id="<?= intval($row['id']) ?>">
                            <i class="bi bi-archive"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-megaphone"></i>
                    <h5>No active announcements</h5>
                    <p>All announcements have expired or none have been added yet.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- EXPIRED TAB -->
        <div class="tab-pane fade" id="expiredTab" role="tabpanel">
            <?php if (!empty($expired_rows)): ?>
                <?php foreach ($expired_rows as $row):
                    $category = $row['category'] ?? 'General';
                    $icon     = getCategoryIcon($category);
                    $postedBy = $row['posted_by'] ?? 'Admin';
                ?>
                <div class="announcement-item expired" data-id="<?= $row['id'] ?>">
                    <span class="status-badge status-expired">Expired</span>
                    <div class="announcement-header">
                        <span class="announcement-icon"><i class="bi <?= $icon ?>"></i></span>
                        <div>
                            <h6><?= htmlspecialchars($row['title']) ?></h6>
                            <div class="announcement-meta">
                                <span><i class="bi bi-person-circle"></i> Posted by: <?= htmlspecialchars($postedBy) ?></span>
                                <span><i class="bi bi-calendar3"></i> <?= date("F j, Y", strtotime($row['date_posted'])) ?></span>
                                <?php if (!empty($row['expiry_date'])): ?>
                                    <span><i class="bi bi-clock-history"></i> Expired: <?= date("M j, Y", strtotime($row['expiry_date'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="announcement-content truncated" id="content-<?= $row['id'] ?>">
                        <?= nl2br(htmlspecialchars($row['content'])) ?>
                    </div>
                    <span class="read-more-link" onclick="toggleContent(<?= $row['id'] ?>)">
                        <span id="toggle-text-<?= $row['id'] ?>">Read more...</span>
                    </span>
                    <div class="announcement-divider"></div>
                    <div class="action-buttons">
                        <a href="#" class="btn-action btn-view view-btn"
                           title="View" data-bs-placement="top"
                           data-title="<?= htmlspecialchars($row['title']) ?>"
                           data-content="<?= htmlspecialchars($row['content']) ?>"
                           data-image="<?= htmlspecialchars($row['image'] ?? '') ?>"
                           data-category="<?= htmlspecialchars($category) ?>"
                           data-posted-by="<?= htmlspecialchars($postedBy) ?>"
                           data-date="<?= date("F j, Y", strtotime($row['date_posted'])) ?>"
                           data-expiry="<?= !empty($row['expiry_date']) ? date("F j, Y", strtotime($row['expiry_date'])) : 'N/A' ?>"
                           data-bs-toggle="modal" data-bs-target="#viewAnnouncementModal">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="#" class="btn-action btn-edit edit-btn"
                           title="Edit" data-bs-placement="top"
                           data-id="<?= $row['id'] ?>"
                           data-title="<?= htmlspecialchars($row['title']) ?>"
                           data-content="<?= htmlspecialchars($row['content']) ?>"
                           data-category="<?= htmlspecialchars($category) ?>"
                           data-expiry="<?= htmlspecialchars($row['expiry_date'] ?? '') ?>"
                           data-bs-toggle="modal" data-bs-target="#editAnnouncementModal">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a href="#" class="btn-action btn-archive archive-announcement"
                           title="Archive" data-bs-placement="top"
                           data-id="<?= intval($row['id']) ?>">
                            <i class="bi bi-archive"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-clock-history"></i>
                    <h5>No expired announcements</h5>
                    <p>All current announcements are still active.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Add Announcement Modal -->
<div class="modal fade" id="addAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Announcement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="Enter announcement title">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="General">General</option>
                            <option value="Event">Event</option>
                            <option value="Fishing">Fishing</option>
                            <option value="Meeting">Meeting</option>
                            <option value="Reminder">Reminder</option>
                            <option value="Emergency">Emergency</option>
                        </select>

                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Content <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control" rows="5" required placeholder="Enter announcement details..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Expiry Date (Optional)</label>
                        <input type="date" name="expiry_date" class="form-control" min="<?= date('Y-m-d') ?>">
                        <small class="text-muted">Leave blank if announcement doesn't expire</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Image (Optional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Add Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Announcement Modal -->
<div class="modal fade" id="editAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Announcement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="announcement_id" id="edit_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                        <select name="category" id="edit_category" class="form-select" required>
                            <option value="General">General</option>
                            <option value="Event">Event</option>
                            <option value="Fishing">Fishing</option>
                            <option value="Meeting">Meeting</option>
                            <option value="Reminder">Reminder</option>
                            <option value="Emergency">Emergency</option>
                        </select>

                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Content <span class="text-danger">*</span></label>
                        <textarea name="content" id="edit_content" class="form-control" rows="5" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Expiry Date (Optional)</label>
                        <input type="date" name="expiry_date" id="edit_expiry" class="form-control" min="<?= date('Y-m-d') ?>">
                        <small class="text-muted">Leave blank if announcement doesn't expire</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Image (Optional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Announcement Modal -->
<div class="modal fade" id="viewAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Announcement Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <span class="badge bg-primary mb-2" id="view_category_badge">Category</span>
                    <h4 id="view_title" class="fw-bold"></h4>
                </div>
                
                <div class="mb-3">
                    <p class="text-muted mb-1">
                        <i class="bi bi-person-circle me-1"></i> Posted by: <strong id="view_posted_by"></strong>
                    </p>
                    <p class="text-muted mb-1">
                        <i class="bi bi-calendar3 me-1"></i> Date Posted: <strong id="view_date"></strong>
                    </p>
                    <p class="text-muted mb-0">
                        <i class="bi bi-clock-history me-1"></i> Expires: <strong id="view_expiry"></strong>
                    </p>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <h6 class="fw-bold">Content:</h6>
                    <p id="view_content" style="white-space: pre-wrap; line-height: 1.8;"></p>
                </div>
                
                <div id="view_image_container" class="d-none">
                    <h6 class="fw-bold">Image:</h6>
                    <img id="view_image" src="" alt="Announcement Image" class="img-fluid rounded shadow-sm" style="max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Initialize Bootstrap tooltips (do NOT use data-bs-toggle="tooltip" on modal triggers)
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;

    document.querySelectorAll('.action-buttons [title], .export-dropdown [title]').forEach(function(el) {
        new bootstrap.Tooltip(el, {
            trigger: 'hover focus',
            boundary: 'window'
        });
    });
});

// Toggle content expansion
function toggleContent(id) {

    const contentEl = document.getElementById('content-' + id);
    const toggleText = document.getElementById('toggle-text-' + id);
    
    if (contentEl.classList.contains('truncated')) {
        contentEl.classList.remove('truncated');
        contentEl.classList.add('expanded');
        toggleText.textContent = 'Show less';
    } else {
        contentEl.classList.remove('expanded');
        contentEl.classList.add('truncated');
        toggleText.textContent = 'Read more...';
    }
}

// Use event delegation on document for all dynamic buttons
document.addEventListener('click', function(e) {

    // Edit button
    const editBtn = e.target.closest('.edit-btn');
    if (editBtn) {
        document.getElementById('edit_id').value = editBtn.dataset.id;
        document.getElementById('edit_title').value = editBtn.dataset.title;
        document.getElementById('edit_content').value = editBtn.dataset.content;
        document.getElementById('edit_category').value = editBtn.dataset.category || 'General';
        document.getElementById('edit_expiry').value = editBtn.dataset.expiry || '';
        return;
    }

    // View button
    const viewBtn = e.target.closest('.view-btn');
    if (viewBtn) {
        document.getElementById('view_title').textContent = viewBtn.dataset.title;
        document.getElementById('view_content').textContent = viewBtn.dataset.content;
        document.getElementById('view_posted_by').textContent = viewBtn.dataset.postedBy || 'Admin';
        document.getElementById('view_date').textContent = viewBtn.dataset.date;
        document.getElementById('view_expiry').textContent = viewBtn.dataset.expiry;

        const categoryBadge = document.getElementById('view_category_badge');
        categoryBadge.textContent = viewBtn.dataset.category || 'General';

        const imgContainer = document.getElementById('view_image_container');
        const imgEl = document.getElementById('view_image');
        if (viewBtn.dataset.image) {
            imgEl.src = '../../uploads/' + viewBtn.dataset.image;
            imgContainer.classList.remove('d-none');
        } else {
            imgContainer.classList.add('d-none');
        }
        return;
    }

    // Archive button
    const archiveBtn = e.target.closest('.archive-announcement');
    if (archiveBtn) {
        e.preventDefault();
        const id = archiveBtn.dataset.id;

        if (!id || parseInt(id) <= 0) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Invalid announcement ID.' });
            return;
        }

        Swal.fire({
            title: 'Archive this announcement?',
            html: '<p style="margin-bottom: 8px;">It will be moved to the archive.</p><p style="color: #6c757d; font-size: 0.9rem; margin: 0;">You can restore it later from the archived announcements page.</p>',
            icon: 'warning',
            iconColor: '#f59e0b',
            showCancelButton: true,
            confirmButtonColor: '#2E86AB',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-archive me-1"></i> Yes, archive it!',
            cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancel',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn-gradient',
                cancelButton: 'btn-cancel',
                popup: 'swal-custom'
            },
            backdrop: 'rgba(0,0,0,0.4)'
        }).then(res => {
            if (res.isConfirmed) {
                const params = new URLSearchParams(window.location.search);
                params.set('archive', id);
                window.location.href = 'admin_announcement.php?' + params.toString();
            }
        });
        return;
    }
});

// Export dropdown manual toggle (no dependency on Bootstrap JS)
(function() {
    const exportToggle = document.getElementById('announcementExportDropdown');
    const exportWrapper = exportToggle ? exportToggle.closest('.export-dropdown') : null;

    if (!exportToggle || !exportWrapper) return;

    // Toggle dropdown on button click
    exportToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const isOpen = exportWrapper.classList.contains('show');

        // Close any other export dropdowns
        document.querySelectorAll('.export-dropdown.show').forEach(dd => {
            if (dd !== exportWrapper) dd.classList.remove('show');
        });

        if (isOpen) {
            exportWrapper.classList.remove('show');
        } else {
            exportWrapper.classList.add('show');
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function() {
        exportWrapper.classList.remove('show');
    });
})();

// SweetAlert notifications
<?php if ($success === "added"): ?>
Swal.fire({ 
    icon: 'success', 
    title: 'Announcement Added!', 
    text: 'The announcement has been successfully posted.',
    timer: 2500,
    timerProgressBar: true,
    showConfirmButton: false,
    toast: true,
    position: 'top-end',
    customClass: {
        popup: 'colored-toast'
    }
});
<?php elseif ($success === "edited"): ?>
Swal.fire({ 
    icon: 'success', 
    title: 'Announcement Updated!', 
    text: 'Your changes have been saved.',
    timer: 2500,
    timerProgressBar: true,
    showConfirmButton: false,
    toast: true,
    position: 'top-end',
    customClass: {
        popup: 'colored-toast'
    }
});
<?php elseif (isset($_GET['archived'])): ?>
Swal.fire({ 
    icon: 'success', 
    title: 'Archived Successfully!', 
    text: 'The announcement has been moved to the archive.',
    timer: 2500,
    timerProgressBar: true,
    showConfirmButton: false,
    toast: true,
    position: 'top-end',
    customClass: {
        popup: 'colored-toast'
    }
});
<?php elseif (!empty($error)): ?>
Swal.fire({ 
    icon: 'error', 
    title: 'Error', 
    text: <?php echo json_encode($error); ?>,
    confirmButtonColor: '#2E86AB',
    customClass: {
        confirmButton: 'btn-gradient'
    }
});
<?php endif; ?>

</script>
</body>
</html>
