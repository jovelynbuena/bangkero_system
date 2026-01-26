<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include(__DIR__ . "/../config/path.php"); // BASE_URL
include(__DIR__ . "/../config/db_connect.php"); // DB connection

// Get logged-in user info
$memberName = $_SESSION['member_name'] ?? 'Admin';
$role = strtolower($_SESSION['role'] ?? 'guest');
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch association settings from system_config table
$configResult = $conn->query("SELECT * FROM system_config WHERE id=1");
$config = $configResult && $configResult->num_rows > 0 ? $configResult->fetch_assoc() : [];

// Set defaults
$assocName = htmlspecialchars($config['assoc_name'] ?? 'Your Association');
$assocLogo = !empty($config['assoc_logo']) ? BASE_URL . 'uploads/config/' . htmlspecialchars($config['assoc_logo']) : BASE_URL . 'images/logo1.png';
$assocEmail = htmlspecialchars($config['assoc_email'] ?? 'info@association.org');
$assocPhone = htmlspecialchars($config['assoc_phone'] ?? '+63 912 345 6789');
$assocAddress = htmlspecialchars($config['assoc_address'] ?? '123 Association Street, City, Philippines');

// Pages for dropdowns
$managementPages = ['officerslist.php','memberlist.php','manage_officer.php','officer_roles.php','gallery_add.php','contact_messages.php'];
$archivePages = ['archives_members.php','archives_officers.php','archived_events.php','archived_announcement.php'];
$utilitiesPages = ['backup.php','logs.php','archives_members.php','archives_officers.php','archived_events.php','archived_announcement.php'];
$settingsPages = ['system_config.php','profile_settings.php'];

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
<style>
/* Google Font */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

body {
    font-family: 'Inter', 'Segoe UI', sans-serif;
}

/* Sidebar */
.sidebar {
    width: 270px;
    height: 100vh;
    position: fixed;
    background: #FFFFFF;
    color: #333;
    padding-top: 0;
    box-shadow: 2px 0 24px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-right: 1px solid #E8E8E8;
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

/* Navbar */
.navbar {
    margin-left: 270px;
    background: #FFFFFF;
    color: #333;
    border-bottom: 1px solid #E8E8E8;
    box-shadow: 0 2px 16px rgba(0,0,0,0.06);
    padding: 16px 24px;
    min-height: 70px;
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
    gap: 8px;
}

.navbar-text strong {
    color: #667eea;
    font-weight: 700;
}

.navbar-text::before {
    content: "👤";
    font-size: 1.2rem;
}

/* Dropdown Caret Animation */
.sidebar-dropdown-toggle i.float-end { 
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 0.9rem;
}
.sidebar-dropdown-toggle[aria-expanded="true"] i.float-end { 
    transform: rotate(180deg);
}

/* Responsive */
@media (max-width: 991.98px) { 
    .sidebar, .navbar { 
        margin-left: 0 !important; 
    }
    .sidebar {
        width: 250px;
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
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar d-flex flex-column">

    <!-- Logo + Name Header -->
    <div class="sidebar-header">
        <div class="text-center">
            <div class="logo-wrapper d-inline-flex align-items-center justify-content-center">
                <img src="<?= $assocLogo ?>" alt="<?= $assocName ?> Logo" class="hero-logo">
            </div>
            <h5 class="mt-3"><?= $assocName ?></h5>
        </div>
    </div>

    <h4><?= ucfirst($role); ?> Menu</h4>

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
    <a href="<?= BASE_URL; ?>management/galleries.php" class="<?= ($current_page == 'galleries.php') ? 'active' : ''; ?>">
        <i class="bi bi-images"></i> Galleries
    </a>

    <?php if (in_array($role, ['admin','officer'])): ?>

    
    <!-- ================= MANAGEMENT SECTION ================= -->
    <a class="sidebar-dropdown-toggle" data-bs-toggle="collapse" href="#managementMenu"
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

        <?php if ($role === 'admin'): ?>
        <a href="<?= BASE_URL; ?>management/manage_officer.php"
           class="<?= ($current_page == 'manage_officer.php') ? 'active' : ''; ?>">
           <i class="bi bi-shield-lock"></i> Manage Officers
        </a>

        <a href="<?= BASE_URL; ?>management/officer_roles.php"
           class="<?= ($current_page == 'officer_roles.php') ? 'active' : ''; ?>">
           <i class="bi bi-person-check"></i> Manage Roles
        </a>
        <?php endif; ?>

        <a href="<?= BASE_URL; ?>management/contact_messages.php"
           class="<?= ($current_page == 'contact_messages.php') ? 'active' : ''; ?>">
           <i class="bi bi-envelope"></i> Contact Messages
        </a>

    </div>
    <?php endif; ?>

    <!-- ================= ADMIN UTILITIES ================= -->
    <?php if ($role === 'admin'): ?>
    <a class="sidebar-dropdown-toggle" data-bs-toggle="collapse" href="#utilitiesMenu"
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
        <a class="sidebar-dropdown-toggle" data-bs-toggle="collapse" href="#archiveSubMenu"
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

            <a href="<?= BASE_URL; ?>announcement/archived_announcement.php"
               class="<?= ($current_page == 'archived_announcement.php') ? 'active' : ''; ?>">
               <i class="bi bi-megaphone-off"></i> Archived Announcement
            </a>
        </div>

    </div>
    <?php endif; ?>

    <!-- SETTINGS -->
    <a class="sidebar-dropdown-toggle" data-bs-toggle="collapse" href="#settingsMenu"
       aria-expanded="<?= $isSettingsOpen ? 'true' : 'false'; ?>">
        <i class="bi bi-gear"></i> Settings
        <i class="bi bi-caret-down-fill float-end"></i>
    </a>

    <div class="collapse ps-3 <?= $isSettingsOpen ? 'show' : ''; ?>" id="settingsMenu">

        <a href="<?= BASE_URL; ?>settings/profile_settings.php"
           class="<?= ($current_page == 'profile_settings.php') ? 'active' : ''; ?>">
           <i class="bi bi-person-circle"></i> Profile Settings
        </a>

        <?php if ($role === 'admin'): ?>
        <a href="<?= BASE_URL; ?>settings/config.php"
           class="<?= ($current_page == 'config.php') ? 'active' : ''; ?>">
           <i class="bi bi-sliders"></i> System Configuration
        </a>
        <?php endif; ?>

    </div>

    <!-- LOGOUT -->
    <a href="<?= BASE_URL; ?>logout.php" class="text-danger">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>

</div>


<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <span class="navbar-text ms-auto me-3">
            Logged in as <strong><?= htmlspecialchars($memberName); ?></strong> (<?= ucfirst($role); ?>)
        </span>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.sidebar-dropdown-toggle').forEach(function(toggle){
    toggle.addEventListener('click', function(){
        setTimeout(function(){
            const menu = toggle.nextElementSibling;
            if(menu.classList.contains('show')) {
                menu.scrollIntoView({behavior: 'smooth', block: 'nearest'});
            }
        }, 300);
    });
});
</script>
</body>
</html>
