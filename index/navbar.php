<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include(__DIR__ . "/../config/path.php"); // BASE_URL
include(__DIR__ . "/../config/db_connect.php"); // DB connection

// Get logged-in user info
$firstName = $_SESSION['first_name'] ?? $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'User';
$role = $_SESSION['role'] ?? 'guest';

// Format role for display (capitalize first letter)
$roleDisplay = match(strtolower($role)) {
    'admin' => 'Administrator',
    'officer' => 'Officer',
    'member' => 'Member',
    default => ucfirst($role)
};

$current_page = basename($_SERVER['PHP_SELF']);

// Fetch association settings from system_config table
// (Offline DB may not have this table yet; auto-create from backup schema if missing)
$config = [];
try {
    $configResult = $conn->query("SELECT * FROM system_config WHERE id=1");
    $config = $configResult && $configResult->num_rows > 0 ? $configResult->fetch_assoc() : [];
} catch (mysqli_sql_exception $e) {
    // Handle missing table gracefully (common after switching to a fresh local DB)
    if (stripos($e->getMessage(), 'system_config') !== false) {
        try {
            $conn->query("CREATE TABLE IF NOT EXISTS `system_config` (
              `id` int(11) NOT NULL,
              `assoc_name` varchar(255) NOT NULL,
              `assoc_email` varchar(255) NOT NULL,
              `assoc_phone` varchar(50) NOT NULL,
              `assoc_address` text NOT NULL,
              `assoc_logo` varchar(255) DEFAULT NULL,
              `auto_backup_status` tinyint(1) NOT NULL DEFAULT '0',
              `backup_storage_limit_mb` int(11) NOT NULL DEFAULT '100',
              `auto_backup_next_run` datetime DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Seed default row (id=1) if missing
            $conn->query("INSERT INTO `system_config`
                (`id`, `assoc_name`, `assoc_email`, `assoc_phone`, `assoc_address`, `assoc_logo`, `auto_backup_status`, `backup_storage_limit_mb`, `auto_backup_next_run`)
                VALUES
                (1, 'Bankero and Fishermen Association', 'info@association.org', '', '', NULL, 0, 100, NULL)
                ON DUPLICATE KEY UPDATE `id` = `id`");

            $configResult = $conn->query("SELECT * FROM system_config WHERE id=1");
            $config = $configResult && $configResult->num_rows > 0 ? $configResult->fetch_assoc() : [];
        } catch (Throwable $e2) {
            // If creation still fails, fall back to defaults
            $config = [];
        }
    } else {
        $config = [];
    }
}


// Set defaults
$assocName = htmlspecialchars($config['assoc_name'] ?? 'Your Association');
$assocLogo = !empty($config['assoc_logo']) ? BASE_URL . 'uploads/config/' . htmlspecialchars($config['assoc_logo']) : BASE_URL . 'images/logo1.png';
$assocEmail = htmlspecialchars($config['assoc_email'] ?? 'info@association.org');
$assocPhone = htmlspecialchars($config['assoc_phone'] ?? '+63 912 345 6789');
$assocAddress = htmlspecialchars($config['assoc_address'] ?? '123 Association Street, City, Philippines');

// Pages for dropdowns
$managementPages = ['officerslist.php','memberlist.php','manage_officer.php','officer_roles.php','gallery_add.php','contact_messages.php','awards.php','transparency_campaigns.php','transparency_donations.php','transparency_programs.php'];
$archivePages = ['archives_members.php','archives_officers.php','archived_events.php','archived_announcement.php','archives_awards.php','archives_galleries.php','archives_contact_messages.php','archives_officer_roles.php','archives_users.php','archives_website_content.php'];
$utilitiesPages = ['backup.php','logs.php','archives_members.php','archives_officers.php','archived_events.php','archived_announcement.php','archives_awards.php','archives_galleries.php','archives_contact_messages.php','archives_officer_roles.php','archives_users.php','archives_website_content.php'];

$settingsPages = ['system_config.php','profile_settings.php','about_association_content.php'];

// Determine if dropdowns should be open
$isManagementOpen = in_array($current_page, $managementPages);
$isArchiveOpen = in_array($current_page, $archivePages);
$isUtilitiesOpen = in_array($current_page, $utilitiesPages);
$isSettingsOpen = in_array($current_page, $settingsPages) || in_array($role, ['officer']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= BASE_URL; ?>../css/dashboard-layout.css" rel="stylesheet">
<style>
/* Google Font */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

body {
    font-family: 'Inter', 'Segoe UI', sans-serif;
}

/* ==================== SIDEBAR ==================== */
.sidebar {
    width: 270px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: #FFFFFF;
    color: #333;
    padding-top: 0;
    box-shadow: 2px 0 24px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    overflow-x: hidden;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-right: 1px solid #E8E8E8;
    z-index: 1050;
}

/* Sidebar Collapsed State */
.sidebar.collapsed {
    transform: translateX(-100%);
}

/* Scrollbar */
.sidebar::-webkit-scrollbar {
    width: 6px;
}
.sidebar::-webkit-scrollbar-track {
    background: #f8f9fa;
}
.sidebar::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}
.sidebar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%);
}

/* Logo Section */
.sidebar-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 24px 16px;
    margin-bottom: 20px;
    border-bottom: 3px solid rgba(255,255,255,0.2);
}

.sidebar h4 { 
    color: #555; 
    font-weight: 700; 
    font-size: 0.75rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding-left: 1.2rem; 
    margin-bottom: 0.75rem;
    margin-top: 1rem;
}

/* Logo */
.hero-logo {
    height: 80px;
    width: auto;
    display: block;
    filter: drop-shadow(0 4px 12px rgba(0,0,0,0.15));
}
.logo-wrapper {
    width: auto;
    height: auto;
    background-color: rgba(255,255,255,0.15);
    padding: 12px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
}

.sidebar-header h5 {
    color: white;
    font-size: 1rem;
    font-weight: 700;
    text-align: center;
    margin-top: 12px;
    margin-bottom: 0;
    text-shadow: 0 2px 8px rgba(0,0,0,0.2);
    line-height: 1.4;
}

/* Sidebar Links */
.sidebar a {
    color: #555;
    display: block;
    padding: 12px 20px;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    margin: 2px 12px;
    position: relative;
}

.sidebar a:hover {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: #667eea;
    transform: translateX(4px);
}

.sidebar a.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #FFFFFF;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.sidebar a.sidebar-dropdown-toggle {
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 600;
}

.sidebar .collapse a {
    font-size: 0.9rem;
    padding: 10px 20px 10px 48px;
    margin: 2px 12px;
    border-radius: 10px;
    font-weight: 500;
}

.sidebar .collapse a:hover {
    background: linear-gradient(90deg, #f0f2f5 0%, #e9ecef 100%);
    color: #667eea;
}

.sidebar a i {
    margin-right: 12px;
    font-size: 1.15rem;
    color: #95A5A6;
    transition: all 0.3s;
    width: 20px;
    text-align: center;
}

.sidebar a:hover i {
    color: #667eea;
    transform: scale(1.1);
}

.sidebar a.active i { 
    color: #FFFFFF;
}

.sidebar .collapse a.active { 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #FFFFFF;
    font-weight: 600;
    box-shadow: 0 3px 8px rgba(102, 126, 234, 0.25);
}

.sidebar .collapse a.active i {
    color: #FFFFFF;
}

/* Logout Button */
.sidebar a.text-danger { 
    margin-top: auto;
    margin-bottom: 20px;
    background: linear-gradient(135deg, #fee 0%, #fdd 100%);
    color: #dc3545 !important;
    border: 2px solid #dc354520;
}

.sidebar a.text-danger:hover {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: #FFFFFF !important;
    transform: translateX(0);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.sidebar a.text-danger:hover i {
    color: #FFFFFF;
}

.sidebar a.text-danger i {
    color: #dc3545;
}

/* ==================== TOP NAVBAR ==================== */
.navbar {
    margin-left: 270px;
    background: #FFFFFF;
    color: #333;
    border-bottom: 1px solid #E8E8E8;
    box-shadow: 0 2px 16px rgba(0,0,0,0.06);
    padding: 16px 24px;
    min-height: 70px;
    transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 1040;
}

.navbar.sidebar-collapsed {
    margin-left: 0;
}

.navbar-text { 
    color: #555; 
    font-weight: 500;
    font-size: 0.95rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 10px 20px;
    border-radius: 25px;
    border: 1px solid #E0E0E0;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.navbar-text .user-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1.3;
}

.navbar-text .user-name {
    color: #667eea;
    font-weight: 700;
    font-size: 0.95rem;
}

.navbar-text .user-role {
    color: #888;
    font-weight: 500;
    font-size: 0.8rem;
}

.navbar-text::before {
    content: "👤";
    font-size: 1.2rem;
    flex-shrink: 0;
}

/* ==================== HAMBURGER MENU TOGGLE ==================== */
.hamburger-toggle {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.hamburger-toggle:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.hamburger-toggle:active {
    transform: scale(0.95);
}

.hamburger-toggle i {
    transition: transform 0.3s ease;
}

.hamburger-toggle.active i {
    transform: rotate(90deg);
}

/* ==================== OVERLAY ==================== */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1045;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.sidebar-overlay.show {
    display: block;
    opacity: 1;
}

/* Dropdown Caret Animation */
.sidebar-dropdown-toggle i.float-end { 
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 0.9rem;
    display: inline-block;
}
.sidebar-dropdown-toggle[aria-expanded="true"] i.float-end { 
    transform: rotate(180deg);
}

/* Ensure dropdown toggle is clickable */
.sidebar-dropdown-toggle {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

/* ==================== RESPONSIVE DESIGN ==================== */

/* Large Screens (Desktop) - Sidebar Always Visible */
@media (min-width: 992px) {
    .sidebar {
        transform: translateX(0) !important;
    }
    
    .navbar {
        margin-left: 270px !important;
    }
    
    .hamburger-toggle {
        display: none !important;
    }
    
    .sidebar-overlay {
        display: none !important;
    }
    
    /* Ensure main content has proper margin on desktop */
    .main-content,
    .content-wrapper {
        margin-left: 270px !important;
    }
}

/* Medium and Small Screens (Tablet/Mobile) - Collapsible Sidebar */
@media (max-width: 991.98px) {
    /* Sidebar hidden by default */
    .sidebar {
        transform: translateX(-100%);
    }
    
    /* Show sidebar when active */
    .sidebar.active {
        transform: translateX(0);
    }
    
    /* Navbar takes full width */
    .navbar {
        margin-left: 0 !important;
    }
    
    /* Show hamburger button */
    .hamburger-toggle {
        display: flex !important;
    }
    
    /* Adjust sidebar width for smaller screens */
    .sidebar {
        width: 270px;
        max-width: 85vw;
    }
    
    /* Main content takes full width on mobile */
    .main-content,
    .content-wrapper {
        margin-left: 0 !important;
        padding: 20px !important;
    }
}

/* Extra Small Screens (Mobile) */
@media (max-width: 576px) {
    .sidebar {
        width: 100%;
        max-width: 100vw;
    }
    
    .navbar-text {
        font-size: 0.85rem;
        padding: 8px 14px;
    }
    
    .navbar-text .user-name {
        font-size: 0.85rem;
    }
    
    .navbar-text .user-role {
        font-size: 0.75rem;
    }
    
    .navbar-text::before {
        font-size: 1rem;
    }
    
    .main-content,
    .content-wrapper {
        padding: 16px !important;
    }
}

/* Animation */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.sidebar a {
    animation: slideIn 0.3s ease-out;
}

/* Prevent body scroll when sidebar is open on mobile */
body.sidebar-open {
    overflow: hidden;
}

@media (max-width: 991.98px) {
    body.sidebar-open {
        overflow: hidden;
    }
}
</style>
</head>
<body>

<!-- Sidebar Overlay (for mobile/tablet) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<div class="sidebar d-flex flex-column" id="mainSidebar">

    <!-- Logo + Name Header -->
    <div class="sidebar-header">
        <div class="text-center">
            <div class="logo-wrapper d-inline-flex align-items-center justify-content-center">
                <img src="<?= $assocLogo ?>" alt="<?= $assocName ?> Logo" class="hero-logo">
            </div>
            <h5 class="mt-3"><?= $assocName ?></h5>
        </div>
    </div>

    <h4><?= $roleDisplay; ?> Menu</h4>

    <!-- Dashboard + Main Shortcuts -->
    <a href="<?= BASE_URL; ?>admin.php" class="<?= ($current_page == 'admin.php') ? 'active' : ''; ?>">
        <i class="bi bi-house-door"></i> Dashboard
    </a>
    <a href="<?= BASE_URL; ?>announcement/admin_announcement.php" class="<?= ($current_page == 'admin_announcement.php') ? 'active' : ''; ?>">
        <i class="bi bi-megaphone"></i> Announcements
    </a>
    <a href="<?= BASE_URL; ?>event.php" class="<?= ($current_page == 'event.php') ? 'active' : ''; ?>">
        <i class="bi bi-calendar4-week"></i> All Events
    </a>
    <a href="<?= BASE_URL; ?>attendance_reports.php" class="<?= ($current_page == 'attendance_reports.php') ? 'active' : ''; ?>">
        <i class="bi bi-clipboard-data"></i> Attendance Reports
    </a>
    <a href="<?= BASE_URL; ?>management/galleries.php" class="<?= ($current_page == 'galleries.php') ? 'active' : ''; ?>">
        <i class="bi bi-images"></i> Galleries
    </a>


    <?php if (in_array(strtolower($role), ['admin','officer'])): ?>

    
    <!-- ================= MANAGEMENT SECTION ================= -->
   <a class="sidebar-dropdown-toggle"
   href="#managementMenu"
   aria-expanded="<?= $isManagementOpen ? 'true' : 'false'; ?>">
    <i class="bi bi-folder"></i> Management
    <i class="bi bi-caret-down-fill float-end"></i>
</a>

<div class="collapse ps-3 <?= $isManagementOpen ? 'show' : ''; ?>" id="managementMenu">

        <a href="<?= BASE_URL; ?>management/officerslist.php"
           class="<?= ($current_page == 'officerslist.php') ? 'active' : ''; ?>">
           <i class="bi bi-person-badge"></i> Officers List
        </a>

        <a href="<?= BASE_URL; ?>management/memberlist.php"
           class="<?= ($current_page == 'memberlist.php') ? 'active' : ''; ?>">
           <i class="bi bi-people-fill"></i> Member List
        </a>

        <?php if (strtolower($role) === 'admin'): ?>
        <a href="<?= BASE_URL; ?>management/manage_officer.php"
           class="<?= ($current_page == 'manage_officer.php') ? 'active' : ''; ?>">
           <i class="bi bi-shield-lock"></i> Manage Officers
        </a>

        <a href="<?= BASE_URL; ?>management/officer_roles.php"
           class="<?= ($current_page == 'officer_roles.php') ? 'active' : ''; ?>">
           <i class="bi bi-person-check"></i> Manage Roles
        </a>

        <a href="<?= BASE_URL; ?>management/awards.php"
           class="<?= ($current_page == 'awards.php') ? 'active' : ''; ?>">
           <i class="bi bi-trophy-fill"></i> Awards
        </a>
        <?php endif; ?>

        <a href="<?= BASE_URL; ?>management/contact_messages.php"
           class="<?= ($current_page == 'contact_messages.php') ? 'active' : ''; ?>">
           <i class="bi bi-envelope"></i> Contact Messages
        </a>

        <a href="<?= BASE_URL; ?>management/transparency_campaigns.php"
           class="<?= ($current_page == 'transparency_campaigns.php') ? 'active' : ''; ?>">
           <i class="bi bi-bullseye"></i> Transparency Campaigns
        </a>

        <a href="<?= BASE_URL; ?>management/transparency_donations.php"
           class="<?= ($current_page == 'transparency_donations.php') ? 'active' : ''; ?>">
           <i class="bi bi-cash-coin"></i> Transparency Donations
        </a>

        <a href="<?= BASE_URL; ?>management/transparency_programs.php"
           class="<?= ($current_page == 'transparency_programs.php') ? 'active' : ''; ?>">
           <i class="bi bi-diagram-3"></i> Transparency Programs
        </a>

    </div>
    <?php endif; ?>

    <!-- ================= ADMIN UTILITIES ================= -->
    <?php if (strtolower($role) === 'admin'): ?>
   <a class="sidebar-dropdown-toggle"
   href="#utilitiesMenu"
   aria-expanded="<?= $isUtilitiesOpen ? 'true' : 'false'; ?>">
    <i class="bi bi-hammer"></i> Utilities
    <i class="bi bi-caret-down-fill float-end"></i>
</a>

<div class="collapse ps-3 <?= $isUtilitiesOpen ? 'show' : ''; ?>" id="utilitiesMenu">

        <a href="<?= BASE_URL; ?>utilities/backup.php"
           class="<?= ($current_page == 'backup.php') ? 'active' : ''; ?>">
           <i class="bi bi-cloud-arrow-up"></i> Backup
        </a>

        <a href="<?= BASE_URL; ?>utilities/logs.php"
           class="<?= ($current_page == 'logs.php') ? 'active' : ''; ?>">
           <i class="bi bi-journal-text"></i> Logs
        </a>

        <!-- ARCHIVE INSIDE UTILITIES -->
        <a class="sidebar-dropdown-toggle"
   href="#archiveSubMenu"
   aria-expanded="<?= $isArchiveOpen ? 'true' : 'false'; ?>">
    <i class="bi bi-archive"></i> Archive
    <i class="bi bi-caret-down-fill float-end"></i>
</a>

<div class="collapse ps-3 <?= $isArchiveOpen ? 'show' : ''; ?>" id="archiveSubMenu">
            <a href="<?= BASE_URL; ?>management/archives_members.php"
               class="<?= ($current_page == 'archives_members.php') ? 'active' : ''; ?>">
               <i class="bi bi-person-x"></i> Archived Members
            </a>

            <a href="<?= BASE_URL; ?>management/archives_officers.php"
               class="<?= ($current_page == 'archives_officers.php') ? 'active' : ''; ?>">
               <i class="bi bi-person-badge-x"></i> Archived Officers
            </a>

            <a href="<?= BASE_URL; ?>management/archived_events.php"
               class="<?= ($current_page == 'archived_events.php') ? 'active' : ''; ?>">
               <i class="bi bi-calendar-x"></i> Archived Events
            </a>

            <a href="<?= BASE_URL; ?>management/archives_awards.php"
               class="<?= ($current_page == 'archives_awards.php') ? 'active' : ''; ?>">
               <i class="bi bi-trophy-fill" style="opacity: 0.7;"></i> Archived Awards
            </a>

            <a href="<?= BASE_URL; ?>management/archives_galleries.php"
               class="<?= ($current_page == 'archives_galleries.php') ? 'active' : ''; ?>">
               <i class="bi bi-images" style="opacity: 0.7;"></i> Archived Galleries
            </a>

            <a href="<?= BASE_URL; ?>management/archives_contact_messages.php"
               class="<?= ($current_page == 'archives_contact_messages.php') ? 'active' : ''; ?>">
               <i class="bi bi-envelope-fill" style="opacity: 0.7;"></i> Archived Messages
            </a>

            <a href="<?= BASE_URL; ?>management/archives_officer_roles.php"
               class="<?= ($current_page == 'archives_officer_roles.php') ? 'active' : ''; ?>">
               <i class="bi bi-person-check-fill" style="opacity: 0.7;"></i> Archived Roles
            </a>

            <a href="<?= BASE_URL; ?>management/archives_users.php"
               class="<?= ($current_page == 'archives_users.php') ? 'active' : ''; ?>">
               <i class="bi bi-shield-lock-fill" style="opacity: 0.7;"></i> Archived Accounts
            </a>

            <a href="<?= BASE_URL; ?>announcement/archived_announcement.php"
               class="<?= ($current_page == 'archived_announcement.php') ? 'active' : ''; ?>">
               <i class="bi bi-megaphone-off"></i> Archived Announcement
            </a>

            <a href="<?= BASE_URL; ?>management/archives_website_content.php"
               class="<?= ($current_page == 'archives_website_content.php') ? 'active' : ''; ?>">
               <i class="bi bi-file-text" style="opacity: 0.7;"></i> Archived Website Content
            </a>
        </div>


    </div>
    <?php endif; ?>

    <!-- SETTINGS -->
  <a class="sidebar-dropdown-toggle"
   href="#settingsMenu"
   aria-expanded="<?= $isSettingsOpen ? 'true' : 'false'; ?>">
    <i class="bi bi-gear"></i> Settings
    <i class="bi bi-caret-down-fill float-end"></i>
</a>

<div class="collapse ps-3 <?= $isSettingsOpen ? 'show' : ''; ?>" id="settingsMenu">
        <a href="<?= BASE_URL; ?>settings/profile_settings.php"
           class="<?= ($current_page == 'profile_settings.php') ? 'active' : ''; ?>">
           <i class="bi bi-person-circle"></i> Profile Settings
        </a>

        <?php if (strtolower($role) === 'admin'): ?>
        <a href="<?= BASE_URL; ?>settings/config.php"
           class="<?= ($current_page == 'config.php') ? 'active' : ''; ?>">
           <i class="bi bi-sliders"></i> System Configuration
        </a>

        <a href="<?= BASE_URL; ?>settings/about_association_content.php"
           class="<?= ($current_page == 'about_association_content.php') ? 'active' : ''; ?>">
           <i class="bi bi-file-text"></i> Website Content
        </a>
        <?php endif; ?>

    </div>

    <!-- LOGOUT -->
    <a href="<?= BASE_URL; ?>logout.php" class="text-danger">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>

</div>


<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light" id="topNavbar">
    <div class="container-fluid">
        <!-- Hamburger Toggle Button -->
        <button class="hamburger-toggle" id="sidebarToggle" type="button" aria-label="Toggle Sidebar">
            <i class="bi bi-list"></i>
        </button>
        
        <span class="navbar-text ms-auto me-3">
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($firstName); ?></span>
                <span class="user-role"><?= htmlspecialchars($roleDisplay); ?></span>
            </div>
        </span>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ==================== RESPONSIVE SIDEBAR + COLLAPSE CONTROL ====================

(function() {
    'use strict';

    // ==================== SIDEBAR MOBILE TOGGLE ====================
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggle');

    if (sidebar && overlay && toggleBtn) {
    function openSidebar() {
        sidebar.classList.add('active');
        overlay.classList.add('show');
        document.body.classList.add('sidebar-open');
        toggleBtn.classList.add('active');
        sessionStorage.setItem('sidebarOpen', 'true');
    }

    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('show');
        document.body.classList.remove('sidebar-open');
        toggleBtn.classList.remove('active');
        sessionStorage.setItem('sidebarOpen', 'false');
    }

        function toggleSidebar() {
            sidebar.classList.contains('active') ? closeSidebar() : openSidebar();
        }

        // Hamburger toggle button
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleSidebar();
        });

        // Close sidebar when clicking overlay
        overlay.addEventListener('click', closeSidebar);

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 991.98 &&
                sidebar.classList.contains('active') &&
                !sidebar.contains(e.target) &&
                !toggleBtn.contains(e.target)) {
                closeSidebar();
            }
        });

        // Close sidebar on window resize (desktop)
        window.addEventListener('resize', function() {
            if (window.innerWidth > 991.98) {
                closeSidebar();
            }
        });

        // Restore sidebar state on page load (mobile)
        window.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 991.98 &&
                sessionStorage.getItem('sidebarOpen') === 'true') {
                openSidebar();
            }
        });
    }

    // ==================== DROPDOWN COLLAPSE MANAGEMENT ====================
    // Single-source control via JS: no data-bs-toggle on the HTML
    // We create a Collapse instance per menu and toggle it manually.

    document.querySelectorAll('.sidebar-dropdown-toggle').forEach(function(toggle) {
        const targetSelector = toggle.getAttribute('href');
        const target = document.querySelector(targetSelector);
        if (!target) return;

        // Create a Collapse instance but don't auto-toggle on init
        const bsCollapse = new bootstrap.Collapse(target, { toggle: false });

        // Handle click: prevent default anchor jump and toggle collapse manually
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            bsCollapse.toggle();
        });

        // Sync aria-expanded and arrow + scroll into view on open/close
        target.addEventListener('shown.bs.collapse', function() {
            toggle.setAttribute('aria-expanded', 'true');
            target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });

        target.addEventListener('hidden.bs.collapse', function() {
            toggle.setAttribute('aria-expanded', 'false');
        });

        // Initial sync on page load
        if (target.classList.contains('show')) {
            toggle.setAttribute('aria-expanded', 'true');
        } else {
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

})();

console.log('✅ Sidebar & Collapse System Ready');
</script>
</body>
</html>
