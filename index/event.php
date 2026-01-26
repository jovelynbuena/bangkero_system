<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

include('../config/db_connect.php');

$flash = ['type'=>'','message'=>''];

// Handle Add/Edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $event_id = isset($_POST['event_id']) && $_POST['event_id'] !== '' ? intval($_POST['event_id']) : null;
    $event_name = trim($_POST['event_name'] ?? '');
    $category = trim($_POST['event_category'] ?? 'General');
    $date = $_POST['event_date'] ?? '';
    $time = $_POST['event_time'] ?? '';
    $location = trim($_POST['event_location'] ?? '');
    $description = trim($_POST['event_description'] ?? '');
    $uploadedPoster = '';

    // Upload Poster
    if (isset($_FILES['event_poster']) && $_FILES['event_poster']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['event_poster'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (!in_array(strtolower($ext), $allowed)) {
                $flash = ['type'=>'error','message'=>'Invalid poster file type.'];
            } else {
                $targetDir = __DIR__ . '/../uploads/';
                if (!is_dir($targetDir)) mkdir($targetDir,0777,true);
                $uploadedPoster = time().'_'.preg_replace('/[^A-Za-z0-9_\-\.]/','_',$file['name']);
                if (!move_uploaded_file($file['tmp_name'],$targetDir.$uploadedPoster)) {
                    $flash = ['type'=>'error','message'=>'Failed to upload poster.'];
                }
            }
        }
    }

    // Validate required fields
    if (!$event_name || !$date || !$time || !$location || !$description) {
        if ($flash['type'] !== 'error') $flash = ['type'=>'error','message'=>'Fill all required fields.'];
    } else {
        if ($event_id) {
            // ✅ Update existing event
            if ($uploadedPoster) {
                $stmt = $conn->prepare("UPDATE events SET event_name=?, category=?, date=?, time=?, location=?, description=?, event_poster=? WHERE id=?");
                $stmt->bind_param("sssssssi",$event_name,$category,$date,$time,$location,$description,$uploadedPoster,$event_id);
            } else {
                $stmt = $conn->prepare("UPDATE events SET event_name=?, category=?, date=?, time=?, location=?, description=? WHERE id=?");
                $stmt->bind_param("ssssssi",$event_name,$category,$date,$time,$location,$description,$event_id);
            }
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: event.php?updated=1");
                exit;
            } else {
                $flash = ['type'=>'error','message'=>'Update failed: '.$conn->error];
                $stmt->close();
            }
        } else {
            // ✅ Insert new event, AUTO_INCREMENT handles ID
            $stmt = $conn->prepare("INSERT INTO events (event_name, description, date, time, location, category, event_poster, is_archived) VALUES (?,?,?,?,?,?,?,0)");
            $posterValue = $uploadedPoster ?: '';
            $stmt->bind_param("sssssss",$event_name,$description,$date,$time,$location,$category,$posterValue);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: event.php?added=1");
                exit;
            } else {
                $flash = ['type'=>'error','message'=>'Insert failed: '.$conn->error];
                $stmt->close();
            }
        }
    }
}


// Handle Archive
if (isset($_GET['archive'])) {
    $id = intval($_GET['archive']);
    $stmt = $conn->prepare("UPDATE events SET is_archived=1 WHERE id=?");
    $stmt->bind_param("i",$id);
    if ($stmt->execute()) $flash = ['type'=>'success','message'=>'Event archived.'];
    else $flash = ['type'=>'error','message'=>'Archive failed: '.$conn->error];
    $stmt->close();
}

// -------------------------
// Fetch upcoming and completed events separately
// -------------------------
$today = date('Y-m-d');

// Upcoming: today and future
$upcomingRes = $conn->query("SELECT * FROM events WHERE is_archived=0 AND `date` >= '{$today}' ORDER BY `date` ASC");
if ($upcomingRes === false) die("DB query failed (upcoming): ".$conn->error);

// Completed: past dates
$completedRes = $conn->query("SELECT * FROM events WHERE is_archived=0 AND `date` < '{$today}' ORDER BY `date` DESC");
if ($completedRes === false) die("DB query failed (completed): ".$conn->error);

// Get categories (used for client-side filter)
$catRes = $conn->query("SELECT DISTINCT IFNULL(category,'General') AS category FROM events");
$categories = [];
while($c=$catRes->fetch_assoc()) $categories[]=$c['category'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Events | Bangkero & Fishermen Association</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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

/* Modern Tabs */
.event-tabs-container {
    background: white;
    padding: 8px;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    border: 1px solid #E8E8E8;
    display: inline-flex;
    gap: 6px;
}

.event-tabs-container .btn {
    padding: 12px 28px;
    border-radius: 12px;
    font-weight: 600;
    border: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: transparent;
    color: #666;
}

.event-tabs-container .btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.event-tabs-container .btn:hover:not(.active) {
    background: #f8f9fa;
    color: #667eea;
}

/* Search & Filter Bar */
.search-filter-bar {
    background: white;
    padding: 20px 24px;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    border: 1px solid #E8E8E8;
    margin-bottom: 24px;
}

#tableSearch {
    border-radius: 12px;
    border: 2px solid #E8E8E8;
    padding: 12px 16px;
    padding-left: 45px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: #f9fafb;
}

#tableSearch:focus {
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    outline: none;
}

.search-icon {
    position: absolute;
    left: 28px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 1.2rem;
    pointer-events: none;
}

#categoryFilter {
    border-radius: 12px;
    border: 2px solid #E8E8E8;
    padding: 12px 16px;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s ease;
    background: #f9fafb;
}

#categoryFilter:focus {
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    outline: none;
}

/* Section Headers */
.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 3px solid #E8E8E8;
}

.section-header h5 {
    font-weight: 700;
    font-size: 1.3rem;
    margin: 0;
    color: #333;
}

.section-header .badge {
    font-size: 0.9rem;
    padding: 6px 12px;
    border-radius: 8px;
}

/* Modern Table Container */
.table-container {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    border: 1px solid #E8E8E8;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table-container::-webkit-scrollbar {
    height: 10px;
}

.table-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.table-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.table-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* DataTables Styling */
table.dataTable {
    border-collapse: separate !important;
    border-spacing: 0;
}

table.dataTable thead th {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    font-weight: 600;
    padding: 16px 12px;
    border: none;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

table.dataTable tbody td {
    padding: 16px 12px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f0;
}

table.dataTable tbody tr {
    transition: all 0.3s ease;
}

table.dataTable tbody tr:hover {
    background: linear-gradient(90deg, #f8f9fa 0%, #ffffff 100%);
    transform: scale(1.01);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* Poster Images */
table.dataTable tbody td img {
    border-radius: 12px;
    object-fit: cover;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

table.dataTable tbody td img:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
}

/* Category Badges */
.event-badge {
    padding: 6px 14px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge.bg-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
}

.badge.bg-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
}

/* Action Buttons */
.action-btn-group {
    display: flex;
    gap: 6px;
    justify-content: center;
}

.btn-sm {
    padding: 8px 12px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
}

.btn-info {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

.btn-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

/* Add Event Button */
.btn-primary, .btn-light {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 12px 28px;
    border-radius: 12px;
    font-weight: 600;
    color: white;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-primary:hover, .btn-light:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(102, 126, 234, 0.4);
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    color: white;
}

/* Export Buttons */
.dt-buttons {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.dt-buttons .btn {
    border-radius: 12px !important;
    padding: 10px 20px !important;
    font-weight: 600 !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    border: 2px solid transparent !important;
    font-size: 0.9rem !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
}

/* CSV Button - Green */
.buttons-csv {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    color: white !important;
    border-color: #10b981 !important;
}

.buttons-csv:hover {
    transform: translateY(-3px) !important;
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4) !important;
}

/* Excel Button - Green (darker) */
.buttons-excel {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%) !important;
    color: white !important;
    border-color: #22c55e !important;
}

.buttons-excel:hover {
    transform: translateY(-3px) !important;
    box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4) !important;
}

/* PDF Button - Red */
.buttons-pdf {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    color: white !important;
    border-color: #ef4444 !important;
}

.buttons-pdf:hover {
    transform: translateY(-3px) !important;
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4) !important;
}

/* Print Button - Gray/Blue */
.buttons-print {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
    color: white !important;
    border-color: #6366f1 !important;
}

.buttons-print:hover {
    transform: translateY(-3px) !important;
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4) !important;
}

.dt-buttons .btn:active {
    transform: translateY(-1px) !important;
}

/* DataTables Pagination */
.dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius: 8px;
    padding: 8px 14px;
    margin: 0 4px;
    font-weight: 600;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white !important;
    border: none;
}

/* Modal Improvements */
.modal-content {
    border-radius: 20px;
    border: none;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px 20px 0 0;
    padding: 24px 32px;
}

.modal-header .btn-close {
    filter: brightness(0) invert(1);
}

.modal-body {
    padding: 32px;
}

.modal-footer {
    padding: 20px 32px;
}

.form-label {
    font-weight: 600;
    color: #555;
    margin-bottom: 8px;
}

.form-control, .form-select {
    border-radius: 10px;
    border: 2px solid #E8E8E8;
    padding: 10px 16px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

/* Responsive */
@media (max-width: 991.98px) {
    .main-content { 
        margin-left: 0; 
        padding: 20px; 
    }
    .page-header h3 {
        font-size: 1.5rem;
    }
    .event-tabs-container {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 767.98px) {
    .main-content { 
        padding: 16px; 
    }
    .page-header {
        padding: 24px 20px;
    }
    .event-tabs-container .btn {
        padding: 10px 20px;
        font-size: 0.9rem;
    }
    .table-container {
        overflow-x: auto;
    }
}

/* Hide default DataTables search */
.dataTables_wrapper .dataTables_filter { 
    display: none !important; 
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.table-container {
    animation: fadeIn 0.5s ease-out;
}

/* Professional Table Styling */
.table thead th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    padding: 16px 12px;
    border: none;
    vertical-align: middle;
}

.table tbody tr {
    background: white;
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background: #f8f9fa;
    transform: scale(1.01);
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.table tbody td {
    padding: 16px 12px;
    vertical-align: middle;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.9rem;
}

/* Event Poster Thumbnail */
.event-poster-thumb {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
    cursor: pointer;
}

.event-poster-thumb:hover {
    transform: scale(1.5);
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    z-index: 1000;
    position: relative;
}

/* Badge Categories */
.badge-category {
    padding: 6px 14px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-festival {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.badge-cleanup {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

.badge-general {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    color: white;
}

.badge-tournament {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.badge-training {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
}

/* Event Details in Table */
.event-date {
    color: #374151;
    font-weight: 500;
    font-size: 0.85rem;
}

.event-time {
    color: #6b7280;
    font-weight: 500;
    font-size: 0.85rem;
}

.event-location {
    color: #374151;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 4px;
}

.event-description {
    color: #6b7280;
    font-size: 0.85rem;
    line-height: 1.5;
}

/* Action Buttons - Professional Style */
.btn-group {
    display: flex;
    gap: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border-radius: 8px;
    overflow: hidden;
}

.btn-outline-primary {
    background: white;
    border: 1.5px solid #667eea;
    color: #667eea;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-primary:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-outline-warning {
    background: white;
    border: 1.5px solid #f59e0b;
    color: #f59e0b;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-warning:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border-color: transparent;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.btn-outline-danger {
    background: white;
    border: 1.5px solid #ef4444;
    color: #ef4444;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-danger:hover {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border-color: transparent;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.btn-sm {
    padding: 8px 14px;
    border-radius: 0;
    font-size: 0.875rem;
}

.btn-group .btn-sm:first-child {
    border-top-left-radius: 8px;
    border-bottom-left-radius: 8px;
}

.btn-group .btn-sm:last-child {
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
}

/* DataTables Customization */
.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 8px 16px;
    margin: 0 4px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: white;
    color: #374151;
    font-weight: 600;
    transition: all 0.3s ease;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
    color: white !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
    color: white !important;
}

.dataTables_wrapper .dataTables_info {
    color: #6b7280;
    font-weight: 500;
    padding: 12px 0;
}

/* SweetAlert Custom Styling */
.swal2-popup {
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border: none !important;
    font-weight: 600 !important;
    padding: 10px 24px !important;
    border-radius: 8px !important;
    transition: all 0.3s ease !important;
}

.btn-gradient:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4) !important;
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
<?php include('navbar.php'); ?>
<div class="main-content">
  
  <!-- Page Header -->
  <div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <h3><i class="bi bi-calendar-event"></i> Events Management</h3>
      <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addEditModal" id="openAdd">
        <i class="bi bi-plus-circle me-2"></i> Add Event
      </button>
    </div>
  </div>

  <!-- Tabs & Filters -->
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div class="event-tabs-container">
      <button class="btn active" id="tabUpcoming">
        <i class="bi bi-calendar-check me-2"></i>Upcoming
      </button>
      <button class="btn" id="tabCompleted">
        <i class="bi bi-calendar-x me-2"></i>Completed
      </button>
    </div>
  </div>

  <!-- Search & Filter Bar -->
  <div class="search-filter-bar">
    <div class="row g-3 align-items-center">
      <div class="col-md-7">
        <div class="position-relative">
          <i class="bi bi-search search-icon"></i>
          <input id="tableSearch" type="search" class="form-control" placeholder="Search events by name, location, description...">
        </div>
      </div>
      <div class="col-md-5">
        <select id="categoryFilter" class="form-select">
          <option value="">-- All Categories --</option>
          <?php foreach($categories as $cat): ?>
            <option value="<?=htmlspecialchars($cat)?>"><?=htmlspecialchars($cat)?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>

  <!-- Upcoming Table -->
  <div id="upcomingSection">
    <div class="section-header">
      <h5><i class="bi bi-calendar-check"></i> Upcoming Events</h5>
      <span class="badge bg-success"><?= $upcomingRes->num_rows ?> Events</span>
    </div>
    <div class="table-container">
      <table id="eventsTableUpcoming" class="display table table-hover" style="width:100%">
        <thead>
          <tr>
            <th class="text-center" style="width: 50px;">#</th>
            <th class="text-center" style="width: 80px;">Poster</th>
            <th style="width: 200px;">Event Name</th>
            <th class="text-center" style="width: 120px;">Category</th>
            <th class="text-center" style="width: 120px;">Date</th>
            <th class="text-center" style="width: 100px;">Time</th>
            <th style="width: 180px;">Location</th>
            <th style="width: 250px;">Description</th>
            <th class="text-center" style="width: 130px;">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if($upcomingRes->num_rows>0): $count=1; while($row=$upcomingRes->fetch_assoc()): ?>
          <tr>
            <td class="text-center fw-bold text-muted"><?=$count++?></td>
            <td class="text-center">
              <img src="../uploads/<?=htmlspecialchars($row['event_poster']?:'default.jpg')?>" 
                   class="event-poster-thumb" 
                   alt="Event Poster">
            </td>
            <td>
              <div class="fw-semibold text-dark"><?=htmlspecialchars($row['event_name'])?></div>
            </td>
            <td class="text-center">
              <span class="badge badge-category badge-<?=strtolower($row['category']?:'general')?>">
                <?=htmlspecialchars($row['category']?:'General')?>
              </span>
            </td>
            <td class="text-center">
              <div class="event-date">
                <i class="bi bi-calendar3 me-1"></i><?=date('M d, Y', strtotime($row['date']))?>
              </div>
            </td>
            <td class="text-center">
              <div class="event-time">
                <i class="bi bi-clock me-1"></i><?=date('h:i A', strtotime($row['time']))?>
              </div>
            </td>
            <td>
              <div class="event-location">
                <i class="bi bi-geo-alt me-1 text-danger"></i><?=htmlspecialchars($row['location'])?>
              </div>
            </td>
            <td>
              <div class="event-description" title="<?=htmlspecialchars($row['description'])?>">
                <?=htmlspecialchars(substr($row['description'], 0, 80))?><?=strlen($row['description']) > 80 ? '...' : ''?>
              </div>
            </td>
            <td class="text-center">
              <div class="btn-group" role="group">
                <button class="btn btn-sm btn-info view-btn" 
                        data-name="<?=htmlspecialchars($row['event_name'])?>" 
                        data-category="<?=htmlspecialchars($row['category'])?>" 
                        data-date="<?=htmlspecialchars($row['date'])?>" 
                        data-time="<?=htmlspecialchars($row['time'])?>" 
                        data-location="<?=htmlspecialchars($row['location'])?>" 
                        data-description="<?=htmlspecialchars($row['description'])?>" 
                        data-poster="<?=htmlspecialchars($row['event_poster'])?>">
                  <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-sm btn-warning edit-btn" 
                        data-id="<?=$row['id']?>" 
                        data-name="<?=htmlspecialchars($row['event_name'])?>" 
                        data-category="<?=htmlspecialchars($row['category'])?>" 
                        data-date="<?=htmlspecialchars($row['date'])?>" 
                        data-time="<?=htmlspecialchars($row['time'])?>" 
                        data-location="<?=htmlspecialchars($row['location'])?>" 
                        data-description="<?=htmlspecialchars($row['description'])?>" 
                        data-poster="<?=htmlspecialchars($row['event_poster'])?>">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-sm btn-danger archive-btn" 
                        data-id="<?=$row['id']?>">
                  <i class="bi bi-archive"></i>
                </button>
              </div>
            </td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="9" class="text-center text-muted py-4"><i class="bi bi-inbox" style="font-size:2rem;"></i><br>No upcoming events.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Completed Table -->
  <div id="completedSection" class="d-none mt-5">
    <div class="section-header">
      <h5><i class="bi bi-calendar-x"></i> Completed Events</h5>
      <span class="badge bg-secondary"><?= $completedRes->num_rows ?> Events</span>
    </div>
    <div class="table-container">
      <table id="eventsTableCompleted" class="display table table-hover" style="width:100%">
        <thead>
          <tr>
            <th class="text-center" style="width: 50px;">#</th>
            <th class="text-center" style="width: 80px;">Poster</th>
            <th style="width: 200px;">Event Name</th>
            <th class="text-center" style="width: 120px;">Category</th>
            <th class="text-center" style="width: 120px;">Date</th>
            <th class="text-center" style="width: 100px;">Time</th>
            <th style="width: 180px;">Location</th>
            <th style="width: 250px;">Description</th>
            <th class="text-center" style="width: 130px;">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if($completedRes->num_rows>0): $count=1; while($row=$completedRes->fetch_assoc()): ?>
          <tr>
            <td class="text-center fw-bold text-muted"><?=$count++?></td>
            <td class="text-center">
              <img src="../uploads/<?=htmlspecialchars($row['event_poster']?:'default.jpg')?>" 
                   class="event-poster-thumb" 
                   alt="Event Poster">
            </td>
            <td>
              <div class="fw-semibold text-dark"><?=htmlspecialchars($row['event_name'])?></div>
            </td>
            <td class="text-center">
              <span class="badge badge-category badge-<?=strtolower($row['category']?:'general')?>">
                <?=htmlspecialchars($row['category']?:'General')?>
              </span>
            </td>
            <td class="text-center">
              <div class="event-date">
                <i class="bi bi-calendar3 me-1"></i><?=date('M d, Y', strtotime($row['date']))?>
              </div>
            </td>
            <td class="text-center">
              <div class="event-time">
                <i class="bi bi-clock me-1"></i><?=date('h:i A', strtotime($row['time']))?>
              </div>
            </td>
            <td>
              <div class="event-location">
                <i class="bi bi-geo-alt me-1 text-danger"></i><?=htmlspecialchars($row['location'])?>
              </div>
            </td>
            <td>
              <div class="event-description" title="<?=htmlspecialchars($row['description'])?>">
                <?=htmlspecialchars(substr($row['description'], 0, 80))?><?=strlen($row['description']) > 80 ? '...' : ''?>
              </div>
            </td>
            <td class="text-center">
              <div class="btn-group" role="group">
                <button class="btn btn-sm btn-info view-btn" 
                        data-name="<?=htmlspecialchars($row['event_name'])?>" 
                        data-category="<?=htmlspecialchars($row['category'])?>" 
                        data-date="<?=htmlspecialchars($row['date'])?>" 
                        data-time="<?=htmlspecialchars($row['time'])?>" 
                        data-location="<?=htmlspecialchars($row['location'])?>" 
                        data-description="<?=htmlspecialchars($row['description'])?>" 
                        data-poster="<?=htmlspecialchars($row['event_poster'])?>">
                  <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-sm btn-warning edit-btn" 
                        data-id="<?=$row['id']?>" 
                        data-name="<?=htmlspecialchars($row['event_name'])?>" 
                        data-category="<?=htmlspecialchars($row['category'])?>" 
                        data-date="<?=htmlspecialchars($row['date'])?>" 
                        data-time="<?=htmlspecialchars($row['time'])?>" 
                        data-location="<?=htmlspecialchars($row['location'])?>" 
                        data-description="<?=htmlspecialchars($row['description'])?>" 
                        data-poster="<?=htmlspecialchars($row['event_poster'])?>">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-sm btn-danger archive-btn" 
                        data-id="<?=$row['id']?>">
                  <i class="bi bi-archive"></i>
                </button>
              </div>
            </td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="9" class="text-center text-muted py-4"><i class="bi bi-inbox" style="font-size:2rem;"></i><br>No completed events.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add/Edit Modal -->
  <div class="modal fade" id="addEditModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content p-3">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold text-dark" id="modalTitle">Add Event</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="eventForm" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="event_id" id="event_id">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Event Name</label>
                <input type="text" name="event_name" id="event_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="event_category" id="event_category" class="form-select" required>
                  <option value="">-- Select --</option>
                  <option>Training</option><option>Cleanup</option><option>Festival</option><option>Livelihood</option><option>General</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Date</label>
                <input type="date" name="event_date" id="event_date" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Time</label>
                <input type="time" name="event_time" id="event_time" class="form-control" required>
              </div>
              <div class="col-md-12">
                <label class="form-label">Location</label>
                <input type="text" name="event_location" id="event_location" class="form-control" required>
              </div>
              <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="event_description" id="event_description" rows="3" class="form-control" required></textarea>
              </div>
              <div class="col-md-12">
                <label class="form-label">Poster (image)</label>
                <input type="file" name="event_poster" id="event_poster" accept="image/*" class="form-control">
                <img id="poster_preview" class="mt-2 rounded" style="width:150px;display:none;">
              </div>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="submit" class="btn btn-primary w-100" id="modalSubmit">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View Modal -->
  <div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content p-3">
        <div class="modal-header">
          <h5 class="modal-title" id="viewTitle">Event Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-5 text-center">
              <img id="viewPoster" src="" class="img-fluid rounded" style="max-height:320px;">
            </div>
            <div class="col-md-7">
              <h4 id="viewName"></h4>
              <p><strong>Category:</strong> <span id="viewCategory"></span></p>
              <p><strong>Date:</strong> <span id="viewDate"></span> <strong>Time:</strong> <span id="viewTime"></span></p>
              <p><strong>Location:</strong> <span id="viewLocation"></span></p>
              <p id="viewDescription"></p>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

 <script>
$(document).ready(function() {
  // --- Flash messages ---
  const flash = <?php echo json_encode($flash); ?>;
  if (flash && flash.type) {
    Swal.fire({
      icon: flash.type,
      title: flash.type === 'success' ? 'Success' : 'Error',
      text: flash.message,
      timer: flash.type === 'success' ? 1600 : undefined,
      showConfirmButton: flash.type === 'error'
    });
  }

  // --- Initialize DataTables once ---
  const tableUpcoming = $('#eventsTableUpcoming').DataTable({
    dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
    pageLength: 10,
    buttons: [
      {
        extend: 'csv',
        text: '<i class="bi bi-filetype-csv me-2"></i>CSV',
        className: 'btn buttons-csv',
        exportOptions: { columns: ':not(:last-child)' }
      },
      {
        extend: 'excel',
        text: '<i class="bi bi-file-earmark-excel me-2"></i>Excel',
        className: 'btn buttons-excel',
        exportOptions: { columns: ':not(:last-child)' }
      },
      {
        extend: 'pdf',
        text: '<i class="bi bi-file-earmark-pdf me-2"></i>PDF',
        className: 'btn buttons-pdf',
        exportOptions: { columns: ':not(:last-child)' }
      },
      {
        extend: 'print',
        text: '<i class="bi bi-printer me-2"></i>Print',
        className: 'btn buttons-print',
        exportOptions: { columns: ':not(:last-child)' }
      }
    ],
    columnDefs: [{ orderable: false, targets: [1,8] }],
    language: { search: "", searchPlaceholder: "Search events..." }
  });

  const tableCompleted = $('#eventsTableCompleted').DataTable({
    dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
    pageLength: 10,
    buttons: [
      {
        extend: 'csv',
        text: '<i class="bi bi-filetype-csv me-2"></i>CSV',
        className: 'btn buttons-csv',
        exportOptions: { columns: ':not(:last-child)' }
      },
      {
        extend: 'excel',
        text: '<i class="bi bi-file-earmark-excel me-2"></i>Excel',
        className: 'btn buttons-excel',
        exportOptions: { columns: ':not(:last-child)' }
      },
      {
        extend: 'pdf',
        text: '<i class="bi bi-file-earmark-pdf me-2"></i>PDF',
        className: 'btn buttons-pdf',
        exportOptions: { columns: ':not(:last-child)' }
      },
      {
        extend: 'print',
        text: '<i class="bi bi-printer me-2"></i>Print',
        className: 'btn buttons-print',
        exportOptions: { columns: ':not(:last-child)' }
      }
    ],
    columnDefs: [{ orderable: false, targets: [1,8] }],
    language: { search: "", searchPlaceholder: "Search events..." }
  });

  // --- Shared search ---
  $('#tableSearch').on('input', function() {
    tableUpcoming.search(this.value).draw();
    tableCompleted.search(this.value).draw();
  });

  // --- Category filter ---
  $('#categoryFilter').on('change', function() {
    const val = this.value;
    tableUpcoming.column(3).search(val ? '^' + val + '$' : '', val ? true : false, false).draw();
    tableCompleted.column(3).search(val ? '^' + val + '$' : '', val ? true : false, false).draw();
  });

  // --- Tab switching ---
  function showUpcoming() {
    $('#tabUpcoming').addClass('active');
    $('#tabCompleted').removeClass('active');
    $('#upcomingSection').removeClass('d-none');
    $('#completedSection').addClass('d-none');
  }
  function showCompleted() {
    $('#tabCompleted').addClass('active');
    $('#tabUpcoming').removeClass('active');
    $('#completedSection').removeClass('d-none');
    $('#upcomingSection').addClass('d-none');
  }

  $('#tabUpcoming').on('click', showUpcoming);
  $('#tabCompleted').on('click', showCompleted);

  // Show upcoming by default
  showUpcoming();

  // --- Add/Edit modal ---
  const addEditModal = new bootstrap.Modal(document.getElementById('addEditModal'));
  $('#openAdd').on('click', function() {
    $('#modalTitle').text('Add Event');
    $('#modalSubmit').text('Add Event');
    $('#eventForm')[0].reset();
    $('#event_id').val('');
    $('#poster_preview').hide();
    addEditModal.show();
  });

  $('.edit-btn').on('click', function() {
    const btn = $(this);
    $('#modalTitle').text('Edit Event');
    $('#modalSubmit').text('Save Changes');
    $('#event_id').val(btn.data('id'));
    $('#event_name').val(btn.data('name'));
    $('#event_category').val(btn.data('category') || 'General');
    $('#event_date').val(btn.data('date'));
    $('#event_time').val(btn.data('time'));
    $('#event_location').val(btn.data('location'));
    $('#event_description').val(btn.data('description'));
    const poster = btn.data('poster');
    if (poster) {
      $('#poster_preview').attr('src','../uploads/'+poster).show();
    } else $('#poster_preview').hide();
    addEditModal.show();
  });

  // --- View modal ---
  $('.view-btn').on('click', function() {
    const btn = $(this);
    $('#viewTitle').text(btn.data('name'));
    $('#viewName').text(btn.data('name'));
    $('#viewCategory').text(btn.data('category') || 'General');
    $('#viewDate').text(btn.data('date'));
    $('#viewTime').text(btn.data('time'));
    $('#viewLocation').text(btn.data('location'));
    $('#viewDescription').text(btn.data('description'));
    const poster = btn.data('poster');
    $('#viewPoster').attr('src', poster ? '../uploads/'+poster : '../uploads/default.jpg');
    new bootstrap.Modal(document.getElementById('viewModal')).show();
  });

  // --- Archive with confirmation ---
  $('.archive-btn').on('click', function() {
    const id = $(this).data('id');
    Swal.fire({
      title: 'Archive this event?',
      html: '<p style="margin-bottom: 8px;">This will move the event to the archived list.</p><p style="color: #6c757d; font-size: 0.9rem; margin: 0;">You can restore it later from the archived events page.</p>',
      icon: 'warning',
      iconColor: '#f59e0b',
      showCancelButton: true,
      confirmButtonColor: '#667eea',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="bi bi-archive me-1"></i> Yes, archive it!',
      cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancel',
      reverseButtons: true,
      customClass: {
        confirmButton: 'btn-gradient',
        cancelButton: 'btn-cancel'
      },
      backdrop: `rgba(0,0,0,0.4)`
    }).then(result => {
      if (result.isConfirmed) {
        window.location.href = window.location.pathname + '?archive=' + id;
      }
    });
  });

  // --- Poster preview ---
  $('#event_poster').on('change', function() {
    const file = this.files[0];
    if (file) $('#poster_preview').attr('src', URL.createObjectURL(file)).show();
    else $('#poster_preview').hide();
  });
});

// SweetAlert Notifications
<?php if (isset($_GET['added'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Event Added!',
    text: 'The event has been successfully added.',
    timer: 2500,
    timerProgressBar: true,
    showConfirmButton: false,
    toast: true,
    position: 'top-end',
    customClass: {
        popup: 'colored-toast'
    }
});
<?php elseif (isset($_GET['updated'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Event Updated!',
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
<?php elseif ($flash['type'] === 'error'): ?>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: '<?= htmlspecialchars($flash['message']) ?>',
    confirmButtonColor: '#667eea',
    customClass: {
        confirmButton: 'btn-gradient'
    }
});
<?php endif; ?>

<?php if (isset($_GET['archive'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Event Archived!',
    text: 'The event has been moved to the archive.',
    timer: 2500,
    timerProgressBar: true,
    showConfirmButton: false,
    toast: true,
    position: 'top-end'
});
<?php endif; ?>
</script>

</body>
</html>
