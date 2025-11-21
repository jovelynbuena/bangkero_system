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
/* Sidebar */
.sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    background: linear-gradient(180deg, #2cd1e7ff 0%, #2C3E50 100%);
    color: #FFF;
    padding-top: 24px;
    box-shadow: 3px 0 15px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    transition: width 0.3s;
}
.sidebar h4 { color: #FF7043; font-weight: 500; padding-left: 1rem; margin-bottom: 1rem; }
.sidebar a {
    color: #FFF;
    display: block;
    padding: 12px 24px;
    text-decoration: none;
    font-size: 1.04rem;
    border-radius: 10px 0 0 10px;
    transition: all 0.3s ease;
}
.sidebar a:hover, .sidebar a.active {
    background: linear-gradient(90deg, #FF7043 80%, transparent 100%);
    color: #1F2A38;
}
.sidebar a.sidebar-dropdown-toggle {
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 500;
}
.sidebar .collapse a {
    font-size: 0.96rem;
    padding-left: 50px;
    padding-top: 7px;
    padding-bottom: 7px;
    border-radius: 8px 0 0 8px;
}
.sidebar .collapse a:hover {
    background: linear-gradient(90deg, #FFA040 80%, transparent 100%);
    color: #1F2A38;
}
.sidebar a i {
    margin-right: 0.75rem;
    font-size: 1.25rem;
    color: #FF7043;
    transition: color 0.3s;
}
.sidebar a.active i { color: #1F2A38; }
.sidebar .collapse a.active { background-color: #FFB74D; color: #1F2A38; }
.sidebar a.text-danger { margin-top: auto; display: block; }

/* Navbar */
.navbar {
    margin-left: 260px;
    background-color: #F8F9FA;
    color: #2C3E50;
    border-bottom: 1px solid #DDE2E5;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
.navbar-text { color: #2C3E50; font-weight: 500; }

/* Logo */
.hero-logo {
    height: 120px;
    width: auto;
    display: block;
}
.logo-wrapper {
    width: auto;
    height: auto;
    background-color: transparent;
    padding: 0;
    box-shadow: none;
    display: flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 991.98px) { .sidebar, .navbar { margin-left: 0 !important; } }
.sidebar-dropdown-toggle i.float-end { transition: transform 0.3s ease; }
.sidebar-dropdown-toggle[aria-expanded="true"] i.float-end { transform: rotate(180deg); }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar d-flex flex-column">
    <div class="text-center mb-4 px-3">
        <div class="logo-wrapper d-inline-flex align-items-center justify-content-center">
            <img src="<?= $assocLogo ?>" alt="<?= $assocName ?> Logo" class="hero-logo">
        </div>
        <h5 class="mt-2 fw-bold text-white"><?= $assocName ?> (<?= ucfirst($role); ?> Panel)</h5>
    </div>

    <h4><?= ucfirst($role); ?> Menu</h4>

    <!-- Common Links -->
    <a href="<?= BASE_URL; ?>admin.php" class="<?= ($current_page == 'admin.php') ? 'active' : ''; ?>"><i class="bi bi-house-door"></i> Dashboard</a>
    <a href="<?= BASE_URL; ?>announcement/admin_announcement.php" class="<?= ($current_page == 'admin_announcement.php') ? 'active' : ''; ?>"><i class="bi bi-megaphone"></i> Announcements</a>
    <a href="<?= BASE_URL; ?>event.php" class="<?= ($current_page == 'event.php') ? 'active' : ''; ?>"><i class="bi bi-calendar4-week"></i> All Events</a>
    <a href="<?= BASE_URL; ?>management/galleries.php" class="<?= ($current_page == 'galleries.php') ? 'active' : ''; ?>"><i class="bi bi-images"></i> Galleries</a>

    <?php if (in_array($role, ['admin','officer'])): ?>
        <a href="<?= BASE_URL; ?>officers.php" class="<?= ($current_page == 'officers.php') ? 'active' : ''; ?>"><i class="bi bi-people"></i> Officers</a>

        <!-- Management Dropdown -->
        <a class="sidebar-dropdown-toggle" data-bs-toggle="collapse" href="#managementMenu" role="button" aria-expanded="<?= $isManagementOpen ? 'true' : 'false'; ?>" aria-controls="managementMenu">
            <i class="bi bi-folder"></i> Management <i class="bi bi-caret-down-fill float-end"></i>
        </a>
        <div class="collapse ps-3 <?= $isManagementOpen ? 'show' : ''; ?>" id="managementMenu">
            <a href="<?= BASE_URL; ?>management/officerslist.php" class="<?= ($current_page == 'officerslist.php') ? 'active' : ''; ?>"><i class="bi bi-person-badge"></i> Officers List</a>
            <a href="<?= BASE_URL; ?>management/memberlist.php" class="<?= ($current_page == 'memberlist.php') ? 'active' : ''; ?>"><i class="bi bi-people-fill"></i> Member List</a>

            <?php if ($role === 'admin'): ?>
                <a href="<?= BASE_URL; ?>management/manage_officer.php" class="<?= ($current_page == 'manage_officer.php') ? 'active' : ''; ?>"><i class="bi bi-shield-lock"></i> Manage Officers</a>
                <a href="<?= BASE_URL; ?>management/officer_roles.php" class="<?= ($current_page == 'officer_roles.php') ? 'active' : ''; ?>"><i class="bi bi-person-check"></i> Manage Roles</a>
            <?php endif; ?>

            <a href="<?= BASE_URL; ?>management/gallery_add.php" class="<?= ($current_page == 'gallery_add.php') ? 'active' : ''; ?>"><i class="bi bi-image"></i> Add Gallery</a>
            <a href="<?= BASE_URL; ?>management/contact_messages.php" class="<?= ($current_page == 'contact_messages.php') ? 'active' : ''; ?>"><i class="bi bi-envelope"></i> Contact Messages</a>
        </div>
    <?php endif; ?>
    <?php if ($role === 'admin'): ?>

   <!-- Utilities Dropdown -->
<a class="sidebar-dropdown-toggle" data-bs-toggle="collapse" href="#utilitiesMenu" role="button" aria-expanded="<?= $isUtilitiesOpen ? 'true' : 'false'; ?>" aria-controls="utilitiesMenu">
    <i class="bi bi-hammer"></i> Utilities <i class="bi bi-caret-down-fill float-end"></i>
</a>
<div class="collapse ps-3 <?= $isUtilitiesOpen ? 'show' : ''; ?>" id="utilitiesMenu">
    <a href="<?= BASE_URL; ?>utilities/backup.php" class="<?= ($current_page == 'backup.php') ? 'active' : ''; ?>">
        <i class="bi bi-cloud-arrow-up"></i> Backup
    </a>
    <a href="<?= BASE_URL; ?>utilities/logs.php" class="<?= ($current_page == 'logs.php') ? 'active' : ''; ?>">
        <i class="bi bi-journal-text"></i> Logs
    </a>

    <!-- Archive Submenu inside Utilities -->
    <a class="sidebar-dropdown-toggle" data-bs-toggle="collapse" href="#archiveSubMenu" role="button" aria-expanded="<?= $isArchiveOpen ? 'true' : 'false'; ?>" aria-controls="archiveSubMenu">
        <i class="bi bi-archive"></i> Archive <i class="bi bi-caret-down-fill float-end"></i>
    </a>
    <div class="collapse ps-3 <?= $isArchiveOpen ? 'show' : ''; ?>" id="archiveSubMenu">
        <a href="<?= BASE_URL; ?>management/archives_members.php" class="<?= ($current_page == 'archives_members.php') ? 'active' : ''; ?>">
            <i class="bi bi-person-x"></i> Archived Members
        </a>
        <a href="<?= BASE_URL; ?>management/archives_officers.php" class="<?= ($current_page == 'archives_officers.php') ? 'active' : ''; ?>">
            <i class="bi bi-person-badge-x"></i> Archived Officers
        </a>
        <a href="<?= BASE_URL; ?>management/archived_events.php" class="<?= ($current_page == 'archived_events.php') ? 'active' : ''; ?>">
            <i class="bi bi-calendar-x"></i> Archived Events
        </a>
        <a href="<?= BASE_URL; ?>announcement/archived_announcement.php" class="<?= ($current_page == 'archived_announcement.php') ? 'active' : ''; ?>">
            <i class="bi bi-megaphone-off"></i> Archived Announcement
        </a>
    </div>
</div>

<?php endif; ?>


    <!-- Settings Dropdown -->
    <a class="sidebar-dropdown-toggle" data-bs-toggle="collapse" href="#settingsMenu" role="button" aria-expanded="<?= $isSettingsOpen ? 'true' : 'false'; ?>" aria-controls="settingsMenu">
        <i class="bi bi-gear"></i> Settings <i class="bi bi-caret-down-fill float-end"></i>
    </a>
    <div class="collapse ps-3 <?= $isSettingsOpen ? 'show' : ''; ?>" id="settingsMenu">
        <a href="<?= BASE_URL; ?>settings/profile_settings.php" class="<?= ($current_page == 'profile_settings.php') ? 'active' : ''; ?>"><i class="bi bi-person-circle"></i> Profile Settings</a>
        <?php if ($role === 'admin'): ?>
            <a href="<?= BASE_URL; ?>settings/config.php" class="<?= ($current_page == 'config.php') ? 'active' : ''; ?>"><i class="bi bi-sliders"></i> System Configuration</a>
        <?php endif; ?>
    </div>

    <!-- Logout -->
    <a href="<?= BASE_URL; ?>logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
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
