<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include(__DIR__ . "/../config/path.php"); // BASE_URL
include(__DIR__ . "/../config/db_connect.php"); // DB connection

// Get logged-in user info
$firstName = $_SESSION['first_name'] ?? $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'User';
$role = $_SESSION['role'] ?? 'guest';

// Fetch user avatar from DB
$navbarAvatar = '';
$navbarUsername = $_SESSION['username'] ?? '';
if ($navbarUsername && isset($conn)) {
    $avatarStmt = $conn->prepare("SELECT avatar FROM users WHERE username = ? LIMIT 1");
    if ($avatarStmt) {
        $avatarStmt->bind_param("s", $navbarUsername);
        $avatarStmt->execute();
        $avatarRow = $avatarStmt->get_result()->fetch_assoc();
        $navbarAvatar = $avatarRow['avatar'] ?? '';
        $avatarStmt->close();
    }
}

// Simple per-user transparency flag (preferred)
$transRole = strtolower(trim((string)($_SESSION['transparency_role'] ?? '')));

// Detect if users.transparency_role exists; if it exists we should NOT fallback to officer position
$hasTransparencyRoleCol = false;
try {
    $colRes = $conn->query("SHOW COLUMNS FROM users LIKE 'transparency_role'");
    $hasTransparencyRoleCol = ($colRes && $colRes->num_rows > 0);
} catch (Throwable) {
    $hasTransparencyRoleCol = false;
}

// Officer position fallback (ONLY for older DBs where transparency_role column doesn't exist)
$officerPosition = '';
try {
    if (!$hasTransparencyRoleCol && !$transRole && strtolower($role) === 'officer' && !empty($_SESSION['user_id'])) {
        $uid = (int)$_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT member_id FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        $memberId = (int)($row['member_id'] ?? 0);
        if ($memberId > 0) {
            $today = date('Y-m-d');
            $stmt = $conn->prepare("SELECT COALESCE(r.role_name, NULLIF(o.position,'')) AS position
                FROM officers o
                LEFT JOIN officer_roles r ON r.id = o.role_id
                WHERE o.member_id = ?
                ORDER BY (? BETWEEN o.term_start AND o.term_end) DESC, o.term_end DESC, o.id DESC
                LIMIT 1");
            $stmt->bind_param('is', $memberId, $today);
            $stmt->execute();
            $res = $stmt->get_result();
            $posRow = $res ? $res->fetch_assoc() : null;
            $stmt->close();

            $officerPosition = strtolower(trim((string)($posRow['position'] ?? '')));
        }
    }
} catch (Throwable) {
    $officerPosition = '';
}

$canTransCampaigns = in_array(strtolower($role), ['admin'], true)
    || (strtolower($role) === 'officer' && in_array($transRole, ['treasurer','secretary','both'], true))
    || (strtolower($role) === 'officer' && !$transRole && in_array($officerPosition, ['treasurer','secretary'], true));

$canTransDonations = in_array(strtolower($role), ['admin'], true)
    || (strtolower($role) === 'officer' && in_array($transRole, ['treasurer','both'], true))
    || (strtolower($role) === 'officer' && !$transRole && $officerPosition === 'treasurer');

$canTransPrograms  = in_array(strtolower($role), ['admin'], true)
    || (strtolower($role) === 'officer' && in_array($transRole, ['secretary','both'], true))
    || (strtolower($role) === 'officer' && !$transRole && $officerPosition === 'secretary');

// Combined access for single transparency page
$canAccessTransparency = $canTransCampaigns || $canTransDonations || $canTransPrograms;

// Format role for display (capitalize first letter)
$roleDisplay = match(strtolower($role)) {
    'admin' => 'Administrator',
    'officer' => 'Officer',
    'member' => 'Member',
    default => ucfirst($role)
};

$current_page = basename($_SERVER['PHP_SELF']);

// Fetch association settings from system_config table
$config = [];
try {
    $configResult = $conn->query("SELECT * FROM system_config WHERE id=1");
    $config = $configResult && $configResult->num_rows > 0 ? $configResult->fetch_assoc() : [];
} catch (mysqli_sql_exception $e) {
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

            $conn->query("INSERT INTO `system_config`
                (`id`, `assoc_name`, `assoc_email`, `assoc_phone`, `assoc_address`, `assoc_logo`, `auto_backup_status`, `backup_storage_limit_mb`, `auto_backup_next_run`)
                VALUES
                (1, 'Bankero and Fishermen Association', 'info@association.org', '', '', NULL, 0, 100, NULL)
                ON DUPLICATE KEY UPDATE `id` = `id`");

            $configResult = $conn->query("SELECT * FROM system_config WHERE id=1");
            $config = $configResult && $configResult->num_rows > 0 ? $configResult->fetch_assoc() : [];
        } catch (Throwable $e2) {
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

// Unread contact messages count (admin/officer only)
$unreadContactCount = 0;
if (in_array(strtolower($role), ['admin', 'officer'])) {
    try {
        $unreadRes = $conn->query("SELECT COUNT(*) as cnt FROM contact_messages WHERE status='unread'");
        if ($unreadRes) $unreadContactCount = (int)($unreadRes->fetch_assoc()['cnt'] ?? 0);
    } catch (Throwable) {
        $unreadContactCount = 0;
    }
}

// Pages for active state detection
$archivePages = ['archives_members.php','archives_officers.php','archived_events.php','archived_announcement.php','archives_awards.php','archives_galleries.php','archives_contact_messages.php','archives_officer_roles.php','archives_users.php','archives_website_content.php','archives_transparency.php'];
$settingsPages = ['system_config.php','profile_settings.php','about_association_content.php','config.php'];
$systemPages   = ['backup.php','logs.php'];

$isArchiveOpen   = in_array($current_page, $archivePages);
$isSettingsOpen  = in_array($current_page, $settingsPages) || in_array($role, ['officer']);
$isSystemOpen    = in_array($current_page, $systemPages);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<link href="<?= BASE_URL; ?>../css/dashboard-layout.css" rel="stylesheet">
<script>
  // Apply collapsed state BEFORE page renders to prevent layout flash
  (function() {
    if (localStorage.getItem('sidebarCollapsed') === '1') {
      document.documentElement.classList.add('sidebar-pre-collapsed');
    }
  })();
</script>
<style>
  html.sidebar-pre-collapsed .main-content,
  html.sidebar-pre-collapsed .content-wrapper {
    margin-left: 72px !important;
  }
</style>
<style>
* { box-sizing: border-box; }

/* ===== GLOBAL SENIOR-FRIENDLY BASE ===== */
body {
  font-family: 'Inter', 'Segoe UI', sans-serif;
  background: #f0f2f5;
  font-size: 16px;
  line-height: 1.6;
}

/* ========== SIDEBAR ========== */
.sidebar {
  width: 280px;
  height: 100vh;
  position: fixed;
  top: 0; left: 0;
  background: #ffffff;
  display: flex;
  flex-direction: column;
  border-right: 1px solid #e8ecf0;
  box-shadow: 4px 0 24px rgba(0,0,0,0.07);
  overflow: visible;
  transition: width 0.3s ease, transform 0.3s ease;
  z-index: 1050;
}

.sidebar.icon-only {
  width: 72px;
}

/* ========== SIDEBAR BRAND ========== */
.sidebar-brand {
  padding: 22px 16px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  display: flex;
  align-items: center;
  gap: 13px;
  flex-shrink: 0;
  overflow: visible;
  position: relative;
}

.brand-logo-img {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  object-fit: cover;
  background: rgba(255,255,255,0.2);
  flex-shrink: 0;
}

.brand-text {
  overflow: hidden;
  transition: opacity 0.2s ease, width 0.3s ease;
  min-width: 0;
}

.brand-text h6 {
  color: white;
  font-family: 'Poppins', sans-serif;
  font-size: 0.95rem;
  font-weight: 700;
  margin: 0;
  line-height: 1.3;
  white-space: normal;
  word-break: break-word;
  overflow: visible;
  text-overflow: unset;
}

.brand-text span {
  color: rgba(255,255,255,0.85);
  font-size: 0.85rem;
  white-space: nowrap;
}

.sidebar.icon-only .brand-text {
  opacity: 0;
  width: 0;
  pointer-events: none;
}

/* Icon-only: shrink brand area to just the logo */
.sidebar.icon-only .sidebar-brand {
  padding: 6px 0;
  justify-content: center;
  gap: 0;
  min-height: unset;
  height: 56px;
}

.sidebar.icon-only .brand-logo-img {
  width: 34px;
  height: 34px;
}

/* Toggle button - inside top navbar */
.sidebar-toggle-btn {
  width: 42px;
  height: 42px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border: none;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 1060;
  box-shadow: 0 3px 10px rgba(99,102,241,0.4);
  transition: background 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
  font-size: 1.3rem;
  color: #ffffff;
  flex-shrink: 0;
}

.sidebar-toggle-btn:hover {
  background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
  box-shadow: 0 5px 18px rgba(99,102,241,0.55);
  transform: scale(1.08);
}

/* When sidebar is icon-only, move the button accordingly */
.sidebar.icon-only ~ * .sidebar-toggle-btn,
body.sidebar-icon-only .sidebar-toggle-btn {
  left: 60px;
}

/* ========== SCROLL AREA ========== */
.sidebar-scroll {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 12px 0 10px;
  min-height: 0;
}

.sidebar-scroll::-webkit-scrollbar { width: 3px; }
.sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
.sidebar-scroll::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 4px;
}

/* ========== SECTION LABELS ========== */
.nav-section-label {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 1.4px;
  text-transform: uppercase;
  color: #94a3b8;
  padding: 16px 20px 6px;
  white-space: nowrap;
  overflow: hidden;
  transition: opacity 0.2s ease;
}

.sidebar.icon-only .nav-section-label {
  opacity: 0;
  height: 0;
  padding: 0;
}

/* ========== NAV ITEMS ========== */
.nav-item-wrap {
  padding: 3px 10px;
  position: relative;
}

.nav-link-item {
  display: flex;
  align-items: center;
  gap: 13px;
  padding: 13px 13px;
  border-radius: 10px;
  color: #475569;
  text-decoration: none;
  font-size: 1.02rem;
  font-weight: 500;
  transition: all 0.2s ease;
  cursor: pointer;
  white-space: nowrap;
  overflow: hidden;
  position: relative;
  min-height: 52px;
}

.nav-link-item:hover {
  background: #f1f5f9;
  color: #6366f1;
  transform: translateX(2px);
}

.nav-link-item.active {
  background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 100%);
  color: #6366f1;
  font-weight: 700;
  border-left: 4px solid #6366f1;
  padding-left: 9px;
  box-shadow: 0 2px 8px rgba(99,102,241,0.12);
}

/* ========== ICONS ========== */
.nav-icon {
  font-size: 1.3rem;
  width: 28px;
  text-align: center;
  flex-shrink: 0;
  transition: color 0.2s ease;
}

.nav-link-item:hover .nav-icon,
.nav-link-item.active .nav-icon {
  color: #6366f1;
}

.nav-label {
  flex: 1;
  transition: opacity 0.2s ease;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.35;
}

.sidebar.icon-only .nav-label,
.sidebar.icon-only .caret-icon {
  opacity: 0;
  pointer-events: none;
  width: 0;
}

/* ========== DROPDOWN CARET ========== */
.caret-icon {
  font-size: 0.75rem;
  transition: transform 0.25s ease, opacity 0.2s ease;
  color: #94a3b8;
  flex-shrink: 0;
  transform: rotate(0deg);
}

/* ========== SUB MENU ========== */
.sub-menu {
  padding: 2px 0 4px;
  overflow: hidden;
  height: 0;
  transition: height 0.25s ease;
}

.sub-menu.show {
  height: auto;
}

.sub-menu .nav-item-wrap {
  padding: 2px 10px 2px 24px;
}

.sub-menu .nav-link-item {
  font-size: 0.95rem;
  padding: 10px 13px;
  color: #64748b;
  min-height: 44px;
}

.sub-menu .nav-link-item:hover {
  background: #f8faff;
  color: #6366f1;
}

.sub-menu .nav-link-item.active {
  background: #eef2ff;
  color: #6366f1;
  font-weight: 700;
  border-left: 4px solid #6366f1;
  padding-left: 9px;
}

/* ========== TOOLTIP (icon-only mode) ========== */
.sidebar.icon-only .nav-item-wrap [data-tooltip] {
  position: relative;
}

.sidebar.icon-only .nav-item-wrap [data-tooltip]:hover::after {
  content: attr(data-tooltip);
  position: absolute;
  left: 58px;
  top: 50%;
  transform: translateY(-50%);
  background: #1e293b;
  color: white;
  font-size: 0.82rem;
  padding: 6px 12px;
  border-radius: 8px;
  white-space: nowrap;
  z-index: 9999;
  box-shadow: 0 4px 12px rgba(0,0,0,0.18);
}

/* ========== NAV BADGE ========== */
.nav-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #ef4444;
  color: #ffffff;
  font-size: 0.68rem;
  font-weight: 700;
  min-width: 20px;
  height: 20px;
  border-radius: 10px;
  padding: 0 5px;
  line-height: 1;
  flex-shrink: 0;
  box-shadow: 0 2px 6px rgba(239,68,68,0.4);
  animation: badgePulse 2s infinite;
}

@keyframes badgePulse {
  0%, 100% { box-shadow: 0 2px 6px rgba(239,68,68,0.4); }
  50%       { box-shadow: 0 2px 12px rgba(239,68,68,0.7); }
}

.sidebar.icon-only .nav-badge {
  position: absolute;
  top: 6px;
  right: 6px;
  min-width: 16px;
  height: 16px;
  font-size: 0.6rem;
}

/* ========== DIVIDER ========== */
.sidebar-divider {
  height: 1px;
  background: #f1f5f9;
  margin: 8px 16px;
}

/* ========== LOGOUT / FOOTER ========== */
.sidebar-footer {
  padding: 10px 10px 16px;
  flex-shrink: 0;
  border-top: 1px solid #f1f5f9;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 13px;
  padding: 12px 13px;
  border-radius: 10px;
  color: #ef4444;
  text-decoration: none;
  font-size: 1rem;
  font-weight: 600;
  background: #fef2f2;
  transition: all 0.2s ease;
  white-space: nowrap;
  overflow: hidden;
  min-height: 48px;
}

.logout-btn:hover {
  background: #ef4444;
  color: white;
}

.logout-btn .nav-icon { color: inherit; font-size: 1.25rem; }

/* ========== TOP NAVBAR ========== */
.top-navbar {
  position: fixed;
  top: 0;
  left: 280px;
  right: 0;
  height: 66px;
  background: #ffffff;
  border-bottom: 1px solid #e8ecf0;
  box-shadow: 0 2px 12px rgba(0,0,0,0.05);
  display: flex;
  align-items: center;
  padding: 0 28px;
  z-index: 1040;
  transition: left 0.3s ease;
  gap: 12px;
}

.top-navbar.icon-only {
  left: 72px;
}

.hamburger-toggle {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border: none;
  color: white;
  width: 46px;
  height: 46px;
  border-radius: 11px;
  display: none;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
  cursor: pointer;
  box-shadow: 0 3px 10px rgba(99,102,241,0.3);
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.hamburger-toggle:hover { transform: scale(1.05); }

.navbar-user-pill {
  margin-left: auto;
  display: inline-flex;
  align-items: center;
  gap: 11px;
  background: #f8fafc;
  border: 1px solid #e8ecf0;
  border-radius: 50px;
  padding: 8px 18px 8px 10px;
}

.user-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  object-fit: cover;
  overflow: hidden;
  flex-shrink: 0;
}
.user-avatar-img {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
  display: block;
}
.user-info-text { line-height: 1.3; }
.user-info-text .u-name { font-size: 1rem; font-weight: 700; color: #1e293b; }
.user-info-text .u-role { font-size: 0.82rem; color: #94a3b8; font-weight: 500; }

/* ========== OVERLAY ========== */
.sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
  z-index: 1045;
  transition: opacity 0.3s ease;
}

.sidebar-overlay.show { display: block; }

/* ========== MAIN CONTENT OFFSET ========== */
.main-content,
.content-wrapper {
  margin-left: 280px;
  padding-top: 66px;
  transition: margin-left 0.3s ease;
}

/* ========== RESPONSIVE ========== */
@media (min-width: 992px) {
  .sidebar { transform: translateX(0) !important; }
  .hamburger-toggle { display: none !important; }
  .sidebar-overlay { display: none !important; }

  .sidebar.icon-only ~ .top-navbar { left: 72px; }
  .sidebar.icon-only ~ * .main-content,
  .main-content.icon-only { margin-left: 72px; }
}

@media (max-width: 991.98px) {
  .sidebar { transform: translateX(-100%); width: 280px !important; }
  .sidebar.mobile-open { transform: translateX(0); }
  .top-navbar { left: 0 !important; }
  .hamburger-toggle { display: flex !important; }
  .main-content, .content-wrapper { margin-left: 0 !important; }
}

@media (max-width: 576px) {
  .navbar-user-pill { padding: 6px 12px 6px 8px; }
  .u-name { font-size: 0.9rem; }
}

body.sidebar-open { overflow: hidden; }
</style>
</head>
<body>

<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ========== SIDEBAR ========== -->
<div class="sidebar d-flex flex-column" id="mainSidebar">

  <!-- Brand Header -->
  <div class="sidebar-brand">
    <img src="<?= $assocLogo ?>" alt="Logo" class="brand-logo-img">
    <div class="brand-text">
      <h6><?= $assocName ?></h6>
      <span><?= $roleDisplay ?> Panel</span>
    </div>
  </div>

  <!-- Scrollable Nav -->
  <div class="sidebar-scroll">

    <!-- MAIN -->
    <div class="nav-section-label">Main</div>
    <div class="nav-item-wrap">
      <a href="<?= BASE_URL ?>admin.php"
         class="nav-link-item <?= ($current_page == 'admin.php') ? 'active' : '' ?>"
         data-tooltip="Dashboard">
        <i class="bi bi-grid-1x2 nav-icon"></i>
        <span class="nav-label">Dashboard</span>
      </a>
    </div>

    <?php if (in_array(strtolower($role), ['admin','officer'])): ?>

    <!-- MANAGEMENT -->
    <div class="nav-section-label">Management</div>
    <div class="nav-item-wrap">
      <a href="<?= BASE_URL ?>management/memberlist.php"
         class="nav-link-item <?= ($current_page == 'memberlist.php') ? 'active' : '' ?>"
         data-tooltip="Members">
        <i class="bi bi-people nav-icon"></i>
        <span class="nav-label">Members</span>
      </a>
    </div>
    <div class="nav-item-wrap">
      <a href="<?= BASE_URL ?>management/officerslist.php"
         class="nav-link-item <?= ($current_page == 'officerslist.php') ? 'active' : '' ?>"
         data-tooltip="Officers">
        <i class="bi bi-person-badge nav-icon"></i>
        <span class="nav-label">Officers</span>
      </a>
    </div>
    <?php if (strtolower($role) === 'admin'): ?>
    <div class="nav-item-wrap">
      <a href="<?= BASE_URL ?>management/manage_officer.php"
         class="nav-link-item <?= ($current_page == 'manage_officer.php') ? 'active' : '' ?>"
         data-tooltip="Manage Officers">
        <i class="bi bi-shield-lock nav-icon"></i>
        <span class="nav-label">Manage Officers</span>
      </a>
    </div>
    <div class="nav-item-wrap">
      <a href="<?= BASE_URL ?>management/officer_roles.php"
         class="nav-link-item <?= ($current_page == 'officer_roles.php') ? 'active' : '' ?>"
         data-tooltip="Roles">
        <i class="bi bi-shield-check nav-icon"></i>
        <span class="nav-label">Roles</span>
      </a>
    </div>
    <div class="nav-item-wrap">
      <a href="<?= BASE_URL ?>management/awards.php"
         class="nav-link-item <?= ($current_page == 'awards.php') ? 'active' : '' ?>"
         data-tooltip="Awards">
        <i class="bi bi-trophy nav-icon"></i>
        <span class="nav-label">Awards</span>
      </a>
    </div>
    <?php endif; ?>

    <!-- ACTIVITIES -->
    <div class="nav-section-label">Activities</div>
    <div class="nav-item-wrap">
      <a href="<?= BASE_URL ?>event.php"
         class="nav-link-item <?= ($current_page == 'event.php') ? 'active' : '' ?>"
         data-tooltip="Events">
        <i class="bi bi-calendar-event nav-icon"></i>
        <span class="nav-label">Events</span>
      </a>
    </div>
    <div class="nav-item-wrap">
      <a href="<?= BASE_URL ?>attendance_reports.php"
         class="nav-link-item <?= ($current_page == 'attendance_reports.php') ? 'active' : '' ?>"
         data-tooltip="Attendance Reports">
        <i class="bi bi-clipboard-data nav-icon"></i>
        <span class="nav-label">Attendance Reports</span>
      </a>
    </div>
    <div class="nav-item-wrap">
      <a href="<?= BASE_URL ?>management/galleries.php"
         class="nav-link-item <?= ($current_page == 'galleries.php') ? 'active' : '' ?>"
         data-tooltip="Galleries">
        <i class="bi bi-images nav-icon"></i>
        <span class="nav-label">Galleries</span>
      </a>
    </div>
    <div class="nav-item-wrap">
      <a href="<?= BASE_URL ?>announcement/admin_announcement.php"
         class="nav-link-item <?= ($current_page == 'admin_announcement.php') ? 'active' : '' ?>"
         data-tooltip="Announcements">
        <i class="bi bi-megaphone nav-icon"></i>
        <span class="nav-label">Announcements</span>
      </a>
    </div>

    <!-- COMMUNICATION -->
    <div class="nav-section-label">Communication</div>
    <div class="nav-item-wrap">
      <a href="<?= BASE_URL ?>management/contact_messages.php"
         class="nav-link-item <?= ($current_page == 'contact_messages.php') ? 'active' : '' ?>"
         data-tooltip="Contact Messages">
        <i class="bi bi-envelope nav-icon"></i>
        <span class="nav-label">Contact Messages</span>
        <?php if ($unreadContactCount > 0): ?>
        <span class="nav-badge"><?= $unreadContactCount > 99 ? '99+' : $unreadContactCount ?></span>
        <?php endif; ?>
      </a>
    </div>

    <!-- TRANSPARENCY -->
    <?php if ($canAccessTransparency): ?>
    <div class="nav-section-label">Transparency</div>
    <div class="nav-item-wrap">
      <a href="<?= BASE_URL ?>management/transparency.php"
         class="nav-link-item <?= in_array($current_page, ['transparency.php','transparency_reports.php']) ? 'active' : '' ?>"
         data-tooltip="Transparency">
        <i class="bi bi-eye nav-icon"></i>
        <span class="nav-label">Transparency</span>
      </a>
    </div>
    <?php endif; ?>

    <?php endif; // end admin/officer ?>

    <?php if (strtolower($role) === 'admin'): ?>

    <!-- SYSTEM -->
    <div class="nav-section-label">System</div>
    <div class="nav-item-wrap">
      <a href="<?= BASE_URL ?>utilities/backup.php"
         class="nav-link-item <?= ($current_page == 'backup.php') ? 'active' : '' ?>"
         data-tooltip="Backup">
        <i class="bi bi-cloud-arrow-up nav-icon"></i>
        <span class="nav-label">Backup</span>
      </a>
    </div>
    <div class="nav-item-wrap">
      <a href="<?= BASE_URL ?>utilities/logs.php"
         class="nav-link-item <?= ($current_page == 'logs.php') ? 'active' : '' ?>"
         data-tooltip="Logs">
        <i class="bi bi-journal-text nav-icon"></i>
        <span class="nav-label">Logs</span>
      </a>
    </div>

    <div class="sidebar-divider"></div>

    <!-- ARCHIVE (Collapsible) -->
    <div class="nav-item-wrap">
      <a class="nav-link-item" href="#archiveMenu"
         data-bs-toggle="collapse"
         aria-expanded="<?= $isArchiveOpen ? 'true' : 'false' ?>"
         aria-controls="archiveMenu"
         data-tooltip="Archives">
        <i class="bi bi-archive nav-icon"></i>
        <span class="nav-label">Archives</span>
        <i class="bi bi-chevron-down caret-icon ms-auto"></i>
      </a>
    </div>
    <div class="collapse sub-menu <?= $isArchiveOpen ? 'show' : '' ?>" id="archiveMenu">

      <div class="nav-item-wrap">
        <a href="<?= BASE_URL ?>management/archives_members.php"
           class="nav-link-item <?= ($current_page == 'archives_members.php') ? 'active' : '' ?>">
          <i class="bi bi-people nav-icon"></i><span class="nav-label">Members</span>
        </a>
      </div>
      <div class="nav-item-wrap">
        <a href="<?= BASE_URL ?>management/archives_officers.php"
           class="nav-link-item <?= ($current_page == 'archives_officers.php') ? 'active' : '' ?>">
          <i class="bi bi-person-badge nav-icon"></i><span class="nav-label">Officers</span>
        </a>
      </div>
      <div class="nav-item-wrap">
        <a href="<?= BASE_URL ?>management/archived_events.php"
           class="nav-link-item <?= ($current_page == 'archived_events.php') ? 'active' : '' ?>">
          <i class="bi bi-calendar-x nav-icon"></i><span class="nav-label">Events</span>
        </a>
      </div>
      <div class="nav-item-wrap">
        <a href="<?= BASE_URL ?>management/archives_awards.php"
           class="nav-link-item <?= ($current_page == 'archives_awards.php') ? 'active' : '' ?>">
          <i class="bi bi-trophy nav-icon"></i><span class="nav-label">Awards</span>
        </a>
      </div>
      <div class="nav-item-wrap">
        <a href="<?= BASE_URL ?>management/archives_galleries.php"
           class="nav-link-item <?= ($current_page == 'archives_galleries.php') ? 'active' : '' ?>">
          <i class="bi bi-images nav-icon"></i><span class="nav-label">Galleries</span>
        </a>
      </div>
      <div class="nav-item-wrap">
        <a href="<?= BASE_URL ?>management/archives_contact_messages.php"
           class="nav-link-item <?= ($current_page == 'archives_contact_messages.php') ? 'active' : '' ?>">
          <i class="bi bi-envelope nav-icon"></i><span class="nav-label">Messages</span>
        </a>
      </div>
      <div class="nav-item-wrap">
        <a href="<?= BASE_URL ?>management/archives_officer_roles.php"
           class="nav-link-item <?= ($current_page == 'archives_officer_roles.php') ? 'active' : '' ?>">
          <i class="bi bi-shield-check nav-icon"></i><span class="nav-label">Roles</span>
        </a>
      </div>
      <div class="nav-item-wrap">
        <a href="<?= BASE_URL ?>management/archives_users.php"
           class="nav-link-item <?= ($current_page == 'archives_users.php') ? 'active' : '' ?>">
          <i class="bi bi-person-lock nav-icon"></i><span class="nav-label">Accounts</span>
        </a>
      </div>
      <div class="nav-item-wrap">
        <a href="<?= BASE_URL ?>announcement/archived_announcement.php"
           class="nav-link-item <?= ($current_page == 'archived_announcement.php') ? 'active' : '' ?>">
          <i class="bi bi-megaphone nav-icon"></i><span class="nav-label">Announcements</span>
        </a>
      </div>
      <div class="nav-item-wrap">
        <a href="<?= BASE_URL ?>management/archives_website_content.php"
           class="nav-link-item <?= ($current_page == 'archives_website_content.php') ? 'active' : '' ?>">
          <i class="bi bi-file-text nav-icon"></i><span class="nav-label">Website Content</span>
        </a>
      </div>
      <div class="nav-item-wrap">
        <a href="<?= BASE_URL ?>management/archives_transparency.php"
           class="nav-link-item <?= ($current_page == 'archives_transparency.php') ? 'active' : '' ?>">
          <i class="bi bi-eye-slash nav-icon"></i><span class="nav-label">Transparency</span>
        </a>
      </div>

    </div><!-- end archiveMenu -->

    <?php endif; // end admin-only ?>

    <!-- SETTINGS -->
    <div class="nav-section-label">Settings</div>
    <div class="nav-item-wrap">
      <a class="nav-link-item" href="#settingsMenu"
         data-bs-toggle="collapse"
         aria-expanded="<?= $isSettingsOpen ? 'true' : 'false' ?>"
         aria-controls="settingsMenu"
         data-tooltip="Settings">
        <i class="bi bi-gear nav-icon"></i>
        <span class="nav-label">Settings</span>
        <i class="bi bi-chevron-down caret-icon ms-auto"></i>
      </a>
    </div>
    <div class="collapse sub-menu <?= $isSettingsOpen ? 'show' : '' ?>" id="settingsMenu">

      <div class="nav-item-wrap">
        <a href="<?= BASE_URL ?>settings/profile_settings.php"
           class="nav-link-item <?= ($current_page == 'profile_settings.php') ? 'active' : '' ?>">
          <i class="bi bi-person-circle nav-icon"></i><span class="nav-label">Profile Settings</span>
        </a>
      </div>
      <?php if (strtolower($role) === 'admin'): ?>
      <div class="nav-item-wrap">
        <a href="<?= BASE_URL ?>settings/config.php"
           class="nav-link-item <?= in_array($current_page, ['config.php','system_config.php']) ? 'active' : '' ?>">
          <i class="bi bi-sliders nav-icon"></i><span class="nav-label">System Configuration</span>
        </a>
      </div>
      <div class="nav-item-wrap">
        <a href="<?= BASE_URL ?>settings/about_association_content.php"
           class="nav-link-item <?= ($current_page == 'about_association_content.php') ? 'active' : '' ?>">
          <i class="bi bi-file-text nav-icon"></i><span class="nav-label">Website Content</span>
        </a>
      </div>
      <?php endif; ?>

    </div><!-- end settingsMenu -->

  </div><!-- end sidebar-scroll -->

  <!-- Logout -->
  <div class="sidebar-footer">
    <a href="<?= BASE_URL ?>logout.php" class="logout-btn" data-tooltip="Logout">
      <i class="bi bi-box-arrow-right nav-icon"></i>
      <span class="nav-label">Logout</span>
    </a>
  </div>

</div><!-- end sidebar -->

<!-- ========== TOP NAVBAR ========== -->
<nav class="top-navbar" id="topNavbar">
  <!-- Desktop sidebar toggle (burger) -->
  <button class="sidebar-toggle-btn d-none d-lg-flex" id="desktopToggleBtn" title="Toggle Sidebar">
    <i class="bi bi-list" id="desktopToggleIcon"></i>
  </button>

  <!-- Mobile hamburger -->
  <button class="hamburger-toggle" id="mobileToggleBtn" type="button" aria-label="Toggle Sidebar">
    <i class="bi bi-list"></i>
  </button>

  <!-- User pill -->
  <div class="navbar-user-pill ms-auto">
    <?php if ($navbarAvatar): ?>
      <img src="/bangkero_system/index/uploads/avatars/<?= htmlspecialchars($navbarAvatar) ?>"
           class="user-avatar-img"
           alt="<?= htmlspecialchars($firstName) ?>"
           onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
      <div class="user-avatar" style="display:none; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:1rem;">
        <?= strtoupper(substr($firstName, 0, 1)) ?>
      </div>
    <?php else: ?>
      <div class="user-avatar" style="display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:1rem;">
        <?= strtoupper(substr($firstName, 0, 1)) ?>
      </div>
    <?php endif; ?>
    <div class="user-info-text">
      <div class="u-name"><?= htmlspecialchars($firstName) ?></div>
      <div class="u-role"><?= htmlspecialchars($roleDisplay) ?></div>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  'use strict';

  const sidebar   = document.getElementById('mainSidebar');
  const overlay   = document.getElementById('sidebarOverlay');
  const topNavbar = document.getElementById('topNavbar');

  // ── Desktop collapse (icon-only) ─────────────────────────────────────────
  const deskBtn  = document.getElementById('desktopToggleBtn');
  const deskIcon = document.getElementById('desktopToggleIcon');

  function applyIconOnly(collapsed, animate) {
    if (!animate) sidebar.style.transition = 'none';
    sidebar.classList.toggle('icon-only', collapsed);
    topNavbar.classList.toggle('icon-only', collapsed);
    document.querySelectorAll('.main-content, .content-wrapper').forEach(el => {
      if (!animate) el.style.transition = 'none';
      el.style.marginLeft = collapsed ? '72px' : '280px';
      if (!animate) setTimeout(() => el.style.transition = '', 50);
    });
    if (!animate) setTimeout(() => sidebar.style.transition = '', 50);
    localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
  }

  if (deskBtn) {
    deskBtn.addEventListener('click', () => {
      applyIconOnly(!sidebar.classList.contains('icon-only'), true);
    });
  }

  // Restore desktop state — run after full page is loaded so .main-content exists
  function restoreSidebarState() {
    if (window.innerWidth >= 992) {
      const saved = localStorage.getItem('sidebarCollapsed');
      if (saved === '1') applyIconOnly(true, false);
    }
    document.documentElement.classList.remove('sidebar-pre-collapsed');
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', restoreSidebarState);
  } else {
    restoreSidebarState();
  }

  // ── Preserve sidebar scroll position ─────────────────────────────────────
  const sidebarScroll = document.querySelector('.sidebar-scroll');
  if (sidebarScroll) {
    const savedScrollTop = localStorage.getItem('sidebarScrollTop');
    if (savedScrollTop !== null) {
      sidebarScroll.scrollTop = parseInt(savedScrollTop, 10);
    }
    sidebarScroll.addEventListener('scroll', function () {
      localStorage.setItem('sidebarScrollTop', sidebarScroll.scrollTop);
    });
  }

  // ── Mobile hamburger ─────────────────────────────────────────────────────
  const mobileBtn = document.getElementById('mobileToggleBtn');

  function openMobile()  {
    sidebar.classList.add('mobile-open');
    overlay.classList.add('show');
    document.body.classList.add('sidebar-open');
  }
  function closeMobile() {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('show');
    document.body.classList.remove('sidebar-open');
  }

  if (mobileBtn) mobileBtn.addEventListener('click', e => { e.stopPropagation(); openMobile(); });
  overlay.addEventListener('click', closeMobile);

  window.addEventListener('resize', () => {
    if (window.innerWidth >= 992) closeMobile();
  });

  // ── Sidebar Dropdowns: fully custom (no Bootstrap collapse dependency) ────
  document.querySelectorAll('#archiveMenu, #settingsMenu').forEach(function(panel) {
    // Find the toggle button for this panel
    var toggleBtn = document.querySelector('[href="#' + panel.id + '"], [data-bs-target="#' + panel.id + '"]');
    if (!toggleBtn) return;

    var caret = toggleBtn.querySelector('.caret-icon');

    function isOpen() { return panel.classList.contains('show'); }

    function openPanel() {
      panel.classList.add('show');
      panel.style.height = panel.scrollHeight + 'px';
      toggleBtn.setAttribute('aria-expanded', 'true');
      if (caret) caret.style.transform = 'rotate(180deg)';
      panel.addEventListener('transitionend', function onEnd() {
        panel.style.height = '';
        panel.removeEventListener('transitionend', onEnd);
      });
    }

    function closePanel() {
      panel.style.height = panel.scrollHeight + 'px';
      panel.offsetHeight; // force reflow
      panel.style.height = '0px';
      toggleBtn.setAttribute('aria-expanded', 'false');
      if (caret) caret.style.transform = 'rotate(0deg)';
      panel.addEventListener('transitionend', function onEnd() {
        panel.classList.remove('show');
        panel.style.height = '';
        panel.removeEventListener('transitionend', onEnd);
      });
    }

    // Strip data-bs-toggle so Bootstrap doesn't interfere
    toggleBtn.removeAttribute('data-bs-toggle');
    toggleBtn.removeAttribute('data-bs-target');

    toggleBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      if (isOpen()) { closePanel(); } else { openPanel(); }
    });

    // Set initial arrow state
    if (isOpen()) {
      toggleBtn.setAttribute('aria-expanded', 'true');
      if (caret) caret.style.transform = 'rotate(180deg)';
    } else {
      toggleBtn.setAttribute('aria-expanded', 'false');
      if (caret) caret.style.transform = 'rotate(0deg)';
    }
  });

})();
</script>
</body>
</html>
