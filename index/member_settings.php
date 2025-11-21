<?php
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['username'])) { header('location: login.php'); exit; }
include('../config/db_connect.php');

$username = $_SESSION['username'];
$sql = "SELECT * FROM members WHERE username = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error . " | SQL: $sql");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
if (!$member) die("No member found for username: " . htmlspecialchars($username));

// Fetch member archives
$archives = [];
$archive_stmt = $conn->prepare("SELECT * FROM member_archives WHERE member_id = ? ORDER BY archived_at DESC");
$archive_stmt->bind_param("i", $member['id']);
$archive_stmt->execute();
$archive_result = $archive_stmt->get_result();
while ($row = $archive_result->fetch_assoc()) {
    $archives[] = $row;
}

// Map activity types to icons and colors
$activityIcons = [
    'Profile Update' => ['bi-person-lines-fill', 'primary'],
    'Uploaded Photo' => ['bi-image', 'success'],
    'Changed Password' => ['bi-shield-lock', 'warning'],
    'Archived Item' => ['bi-archive', 'secondary'],
    'Other' => ['bi-info-circle', 'info'],
];

$update_success = false; $update_error = null;

// PROFILE UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $new_name = trim($_POST['name']);
    $new_phone = trim($_POST['phone']);
    $new_address = trim($_POST['address']);
    $new_dob = $_POST['dob'];
    $new_gender = $_POST['gender'];
    $new_work_type = $_POST['work_type'];
    $new_license_number = trim($_POST['license_number']);
    $new_boat_name = trim($_POST['boat_name']);
    $new_fishing_area = trim($_POST['fishing_area']);
    $new_emergency_name = trim($_POST['emergency_name']);
    $new_emergency_phone = trim($_POST['emergency_phone']);
    $image = $member['image'];

    // Handle member image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $update_error = "Only JPG, PNG, and GIF files are allowed.";
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $update_error = "Image size must be less than 2MB.";
        } else {
            $imgTmp = $_FILES['image']['tmp_name'];
            $imgName = uniqid('member_', true) . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '', basename($_FILES['image']['name']));
            $imgDir = '../uploads/members/';
            $imgPath = $imgDir . $imgName;

            if (!is_dir($imgDir)) { mkdir($imgDir, 0777, true); }
            if (move_uploaded_file($imgTmp, $imgPath)) {
                if (!empty($image) && file_exists($imgDir . $image) && $image !== "default_member.png") {
                    @unlink($imgDir . $image);
                }
                $image = $imgName;
            } else {
                $update_error = "Image upload failed.";
            }
        }
    }

    if (!$update_error) {
        $sql_update = "UPDATE members SET name=?, phone=?, address=?, dob=?, gender=?, work_type=?, license_number=?, boat_name=?, fishing_area=?, emergency_name=?, emergency_phone=?, image=? WHERE username=?";
        $stmt_update = $conn->prepare($sql_update);
        if (!$stmt_update) {
            $update_error = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        } else {
            $stmt_update->bind_param(
                "sssssssssssss",
                $new_name, $new_phone, $new_address, $new_dob, $new_gender, $new_work_type,
                $new_license_number, $new_boat_name, $new_fishing_area, $new_emergency_name, $new_emergency_phone, $image, $username
            );
            if ($stmt_update->execute()) {
                $update_success = true;
                // Reload member info
                $stmt = $conn->prepare($sql); $stmt->bind_param("s", $username); $stmt->execute();
                $result = $stmt->get_result(); $member = $result->fetch_assoc();

                // Log to archives
                $archive_stmt = $conn->prepare("INSERT INTO member_archives (member_id, title, description, type) VALUES (?, ?, ?, ?)");
                $archive_title = "Profile Updated";
                $archive_desc = "You updated your profile information.";
                $archive_type = "Profile Update";
                $archive_stmt->bind_param("isss", $member['id'], $archive_title, $archive_desc, $archive_type);
                $archive_stmt->execute();
            } else {
                $update_error = "Failed to update profile. Please try again.";
            }
        }
    }
}

// PREFERENCES UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_preferences'])) {
    $pref_notifications = isset($_POST['pref_notifications']) ? 1 : 0;
    $pref_language = $_POST['pref_language'];
    $pref_theme = $_POST['pref_theme'];

    $sql_pref = "UPDATE members SET pref_notifications=?, pref_language=?, pref_theme=? WHERE username=?";
    $stmt_pref = $conn->prepare($sql_pref);
    $stmt_pref->bind_param("isss", $pref_notifications, $pref_language, $pref_theme, $username);
    if ($stmt_pref->execute()) {
        $update_success = true;
        // Reload member info
        $stmt = $conn->prepare($sql); $stmt->bind_param("s", $username); $stmt->execute();
        $result = $stmt->get_result(); $member = $result->fetch_assoc();
    } else {
        $update_error = "Failed to update preferences.";
    }
}

// PASSWORD CHANGE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Fetch current password hash from DB
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($db_hash);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current, $db_hash)) {
        $update_error = "Current password is incorrect.";
    } elseif ($new !== $confirm) {
        $update_error = "New passwords do not match.";
    } elseif (strlen($new) < 6) {
        $update_error = "New password must be at least 6 characters.";
    } else {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password_hash=? WHERE username=?");
        $update_stmt->bind_param("ss", $new_hash, $username);
        if ($update_stmt->execute()) {
            $update_success = true;
            // Optionally log to archives
            if (isset($member['id'])) {
                $archive_stmt = $conn->prepare("INSERT INTO member_archives (member_id, title, description, type) VALUES (?, ?, ?, ?)");
                $archive_title = "Changed Password";
                $archive_desc = "Password changed on " . date('M d, Y h:i A');
                $archive_type = "Changed Password";
                $archive_stmt->bind_param("isss", $member['id'], $archive_title, $archive_desc, $archive_type);
                $archive_stmt->execute();
            }
        } else {
            $update_error = "Failed to update password.";
        }
        $update_stmt->close();
    }
}

// Set member image URL
$imageSrc = (!empty($member['image']) && file_exists('../uploads/members/' . $member['image']))
    ? '../uploads/members/' . htmlspecialchars($member['image'])
    : '../uploads/members/default_member.png';

// Set theme class for body
$themeClass = (isset($member['pref_theme']) && $member['pref_theme'] === 'Dark') ? 'dark-theme' : '';

$lang = isset($member['pref_language']) ? $member['pref_language'] : 'English';

$translations = [
    'Member Settings' => ['English' => 'Member Settings', 'Tagalog' => 'Mga Setting ng Miyembro'],
    'Changes saved successfully!' => ['English' => 'Changes saved successfully!', 'Tagalog' => 'Matagumpay na na-save ang mga pagbabago!'],
    'Personal Information' => ['English' => 'Personal Information', 'Tagalog' => 'Personal na Impormasyon'],
    'Full Name' => ['English' => 'Full Name', 'Tagalog' => 'Buong Pangalan'],
    'Phone' => ['English' => 'Phone', 'Tagalog' => 'Telepono'],
    'Date of Birth' => ['English' => 'Date of Birth', 'Tagalog' => 'Araw ng Kapanganakan'],
    'Gender' => ['English' => 'Gender', 'Tagalog' => 'Kasarian'],
    'Address' => ['English' => 'Address', 'Tagalog' => 'Tirahan'],
    'Work & License' => ['English' => 'Work & License', 'Tagalog' => 'Trabaho at Lisensya'],
    'Work Type' => ['English' => 'Work Type', 'Tagalog' => 'Uri ng Trabaho'],
    'License Number' => ['English' => 'License Number', 'Tagalog' => 'Numero ng Lisensya'],
    'Boat Name' => ['English' => 'Boat Name', 'Tagalog' => 'Pangalan ng Bangka'],
    'Fishing Area' => ['English' => 'Fishing Area', 'Tagalog' => 'Lugar ng Pangingisda'],
    'Emergency Contact' => ['English' => 'Emergency Contact', 'Tagalog' => 'Pangunahing Kontak'],
    'Contact Name' => ['English' => 'Contact Name', 'Tagalog' => 'Pangalan ng Kontak'],
    'Contact Phone' => ['English' => 'Contact Phone', 'Tagalog' => 'Telepono ng Kontak'],
    'Username (cannot change)' => ['English' => 'Username (cannot change)', 'Tagalog' => 'Username (hindi mababago)'],
    'Save Changes' => ['English' => 'Save Changes', 'Tagalog' => 'I-save ang mga Pagbabago'],
    'Archives' => ['English' => 'Archives', 'Tagalog' => 'Mga Archive'],
    'Activity Log' => ['English' => 'Activity Log', 'Tagalog' => 'Talaan ng Aktibidad'],
    'No activity yet. Your actions on the website (like profile updates, uploads, etc.) will appear here.' => [
        'English' => 'No activity yet. Your actions on the website (like profile updates, uploads, etc.) will appear here.',
        'Tagalog' => 'Wala pang aktibidad. Ang iyong mga gawain sa website (tulad ng pag-update ng profile, pag-upload, atbp.) ay lalabas dito.'
    ],
    'This is your personal activity log. Here you can see your actions and changes made on the website.' => [
        'English' => 'This is your personal activity log. Here you can see your actions and changes made on the website.',
        'Tagalog' => 'Ito ang iyong personal na talaan ng aktibidad. Makikita mo dito ang iyong mga gawain at pagbabago sa website.'
    ],
    'Security' => ['English' => 'Security', 'Tagalog' => 'Seguridad'],
    'Preferences' => ['English' => 'Preferences', 'Tagalog' => 'Mga Preference'],
    'Notifications' => ['English' => 'Notifications', 'Tagalog' => 'Mga Notipikasyon'],
    'Enable notifications' => ['English' => 'Enable notifications', 'Tagalog' => 'I-enable ang mga notipikasyon'],
    'Language' => ['English' => 'Language', 'Tagalog' => 'Wika'],
    'Theme' => ['English' => 'Theme', 'Tagalog' => 'Tema'],
    'Light' => ['English' => 'Light', 'Tagalog' => 'Maliwanag'],
    'Dark' => ['English' => 'Dark', 'Tagalog' => 'Madilim'],
    'Save Preferences' => ['English' => 'Save Preferences', 'Tagalog' => 'I-save ang Mga Preference'],
    // Add more as needed...
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Member Settings - Bangkero & Fishermen Association</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f7f7f7;
        }
        .settings-wrapper {
            max-width: 1100px;
            margin: 48px auto 0 auto;
            display: flex;
            gap: 32px;
        }
        .settings-sidebar {
            min-width: 240px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.06);
            padding: 32px 0 0 0;
            height: fit-content;
            position: sticky;
            top: 32px; /* Adjust as needed for your header */
            z-index: 10;
        }
        .settings-sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .settings-sidebar li {
            padding: 16px 32px;
            font-size: 1.08rem;
            color: #333;
            border-left: 4px solid transparent;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .settings-sidebar li.active, .settings-sidebar li:hover {
            background: #f0f6ff;
            border-left: 4px solid #0d6efd;
            color: #0d6efd;
        }
        .settings-main {
            flex: 1;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.06);
            padding: 36px 40px 32px 40px;
        }
        @media (max-width: 900px) {
            .settings-wrapper { flex-direction: column; gap: 0; }
            .settings-sidebar { min-width: 0; margin-bottom: 24px; }
            .settings-main { padding: 24px 10px; }
        }
        .avatar-container {
            position: relative;
            width: 120px;
            margin: 0 auto 1.5rem auto;
        }
        .avatar-img {
            width: 120px; height: 120px; border-radius: 50%;
            border: 4px solid #0d6efd; object-fit: cover; background: #e9ecef;
            box-shadow: 0 2px 16px rgba(0,0,0,0.10);
            display: block;
            margin: 0 auto;
        }
        .avatar-overlay {
            position: absolute;
            bottom: 0; left: 50%; transform: translateX(-50%);
            background: rgba(13,110,253,0.85);
            color: #fff;
            border-radius: 0 0 50% 50%;
            width: 120px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s;
            cursor: pointer;
        }
        .avatar-container:hover .avatar-overlay,
        .avatar-container:focus-within .avatar-overlay {
            opacity: 1;
        }
        .avatar-overlay i {
            font-size: 1.2rem;
            margin-right: 6px;
        }
        .section-title {
            font-weight: 600;
            font-size: 1.08rem;
            margin-bottom: 10px;
            margin-top: 30px;
            color: #0d6efd;
        }
        .form-label { font-weight: 500; }
        .btn-primary { background: #0d6efd; }
        .alert { margin-bottom: 1.5rem; }

        /* Dark Theme Styles */
        .dark-theme {
            background: #181a1b !important;
            color: #e0e0e0 !important;
        }
        .dark-theme .settings-main,
        .dark-theme .settings-sidebar {
            background: #23272b !important;
            color: #e0e0e0 !important;
            box-shadow: 0 2px 16px rgba(0,0,0,0.30);
        }
        .dark-theme .form-control,
        .dark-theme .form-select {
            background: #23272b !important;
            color: #e0e0e0 !important;
            border-color: #444;
        }
        .dark-theme .navbar,
        .dark-theme .navbar-light,
        .dark-theme .bg-light {
            background: #23272b !important;
            color: #e0e0e0 !important;
        }
        .dark-theme .btn-primary {
            background: #0d6efd;
            border-color: #0d6efd;
        }
        .dark-theme .settings-sidebar li.active,
        .dark-theme .settings-sidebar li:hover {
            background: #23272b !important;
            color: #0d6efd !important;
        }
        .dark-theme .alert {
            background: #23272b;
            color: #e0e0e0;
            border-color: #444;
        }

        /* Custom Styles */
        #archivesPanel .list-group-item {
            background: transparent;
            border-left: 3px solid #0d6efd;
            border-radius: 0;
            margin-bottom: 0.5rem;
        }
        #archivesPanel .list-group-item:last-child {
            border-left: 3px solid transparent;
        }
    </style>
</head>
<body class="<?= $themeClass ?>">
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">Bangkero & Fishermen Association</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="member.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="member_settings.php">Settings</a></li>
                    <li class="nav-item"><a href="logout.php" class="nav-link text-danger">Log Out</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="settings-wrapper">
        <!-- Sidebar Panel -->
        <aside class="settings-sidebar">
            <ul>
                <li class="active"><i class="bi bi-person-circle"></i> Profile</li>
                <li><i class="bi bi-archive"></i> Archives</li>
                <li><i class="bi bi-shield-lock"></i> Security</li>
                <li><i class="bi bi-gear"></i> Preferences</li>
            </ul>
        </aside>
        <!-- Main Content -->
        <main class="settings-main">
            <h3 class="mb-4 text-center"><i class="bi bi-person-gear me-2"></i> Member Settings</h3>
            <?php if ($update_success): ?>
                <div class="alert alert-success" role="alert"><i class="bi bi-check-circle me-1"></i> Changes saved successfully!</div>
            <?php elseif ($update_error): ?>
                <div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-triangle me-1"></i> <?= htmlspecialchars($update_error) ?></div>
            <?php endif; ?>
            <!-- Profile Form (default visible) -->
            <form method="POST" autocomplete="off" enctype="multipart/form-data" id="profileForm">
                <div class="avatar-container mb-3">
                    <img src="<?= htmlspecialchars($imageSrc) ?>" alt="Member Image" class="avatar-img" id="imagePreview">
                    <label class="avatar-overlay" for="image" tabindex="0">
                        <i class="bi bi-camera"></i> Change Photo
                    </label>
                    <input type="file" id="image" name="image" accept="image/*" style="display:none" onchange="previewImg(event)">
                </div>
                <div class="section-title"><?= $translations['Personal Information'][$lang] ?></div>
                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <label class="form-label"><?= $translations['Full Name'][$lang] ?></label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($member['name']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label"><?= $translations['Phone'][$lang] ?></label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($member['phone']) ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <label class="form-label"><?= $translations['Date of Birth'][$lang] ?></label>
                        <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($member['dob']) ?>">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label"><?= $translations['Gender'][$lang] ?></label>
                        <select name="gender" class="form-select">
                            <option value="">-- Select --</option>
                            <option value="Male" <?= $member['gender']=='Male'?'selected':''; ?>>Male</option>
                            <option value="Female" <?= $member['gender']=='Female'?'selected':''; ?>>Female</option>
                            <option value="Other" <?= $member['gender']=='Other'?'selected':''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= $translations['Address'][$lang] ?></label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($member['address']) ?>">
                </div>
                <div class="section-title"><?= $translations['Work & License'][$lang] ?></div>
                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <label class="form-label"><?= $translations['Work Type'][$lang] ?></label>
                        <select name="work_type" class="form-select">
                            <option value="">-- Select --</option>
                            <option value="Fisherman" <?= $member['work_type']=='Fisherman'?'selected':''; ?>>Fisherman</option>
                            <option value="Bangkero" <?= $member['work_type']=='Bangkero'?'selected':''; ?>>Bangkero</option>
                            <option value="Both" <?= $member['work_type']=='Both'?'selected':''; ?>>Both</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label"><?= $translations['License Number'][$lang] ?></label>
                        <input type="text" name="license_number" class="form-control" value="<?= htmlspecialchars($member['license_number']) ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <label class="form-label"><?= $translations['Boat Name'][$lang] ?></label>
                        <input type="text" name="boat_name" class="form-control" value="<?= htmlspecialchars($member['boat_name']) ?>">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label"><?= $translations['Fishing Area'][$lang] ?></label>
                        <input type="text" name="fishing_area" class="form-control" value="<?= htmlspecialchars($member['fishing_area']) ?>">
                    </div>
                </div>
                <div class="section-title"><?= $translations['Emergency Contact'][$lang] ?></div>
                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <label class="form-label"><?= $translations['Contact Name'][$lang] ?></label>
                        <input type="text" name="emergency_name" class="form-control" value="<?= htmlspecialchars($member['emergency_name']) ?>">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label"><?= $translations['Contact Phone'][$lang] ?></label>
                        <input type="text" name="emergency_phone" class="form-control" value="<?= htmlspecialchars($member['emergency_phone']) ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= $translations['Username (cannot change)'][$lang] ?></label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($member['username']) ?>" readonly>
                </div>
                <button type="submit" name="save_profile" class="btn btn-primary w-100 mt-2"><i class="bi bi-save me-1"></i><?= $translations['Save Changes'][$lang] ?></button>
            </form>
            <!-- Archives and other panels can be added here, hidden by default -->
            <div id="archivesPanel" style="display:none;">
                <h4><i class="bi bi-archive me-2"></i><?= $translations['Activity Log'][$lang] ?></h4>
                <?php if (count($archives) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($archives as $archive): 
                            $type = $archive['type'] ?? 'Other';
                            $icon = $activityIcons[$type][0] ?? 'bi-info-circle';
                            $color = $activityIcons[$type][1] ?? 'info';
                        ?>
                        <li class="list-group-item d-flex align-items-start border-0 ps-0">
                            <span class="me-3 mt-1">
                                <i class="bi <?= $icon ?> text-<?= $color ?>" style="font-size:1.6rem"></i>
                            </span>
                            <div>
                                <div>
                                    <strong><?= htmlspecialchars($archive['title']) ?></strong>
                                    <span class="badge bg-<?= $color ?> ms-2"><?= htmlspecialchars($type) ?></span>
                                </div>
                                <div class="small text-muted mb-1"><?= date('M d, Y h:i A', strtotime($archive['archived_at'])) ?></div>
                                <div><?= nl2br(htmlspecialchars($archive['description'])) ?></div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <?= $translations['No activity yet. Your actions on the website (like profile updates, uploads, etc.) will appear here.'][$lang] ?>
                    </div>
                <?php endif; ?>
                <div class="mt-4 text-muted small">
                    <i class="bi bi-info-circle"></i>
                    <?= $translations['This is your personal activity log. Here you can see your actions and changes made on the website.'][$lang] ?>
                </div>
            </div>
            <div id="securityPanel" style="display:none;">
                <h4><i class="bi bi-shield-lock me-2"></i><?= $translations['Security'][$lang] ?></h4>
                <form method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                </form>
            </div>
            <div id="preferencesPanel" style="display:none;">
                <h4><i class="bi bi-gear me-2"></i><?= $translations['Preferences'][$lang] ?></h4>
                <form method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label"><?= $translations['Notifications'][$lang] ?></label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="pref_notifications" id="pref_notifications"
                                <?= isset($member['pref_notifications']) && $member['pref_notifications'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="pref_notifications"><?= $translations['Enable notifications'][$lang] ?></label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= $translations['Language'][$lang] ?></label>
                        <select name="pref_language" class="form-select">
                            <option value="English" <?= $member['pref_language']=='English'?'selected':''; ?>>English</option>
                            <option value="Tagalog" <?= $member['pref_language']=='Tagalog'?'selected':''; ?>>Tagalog</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= $translations['Theme'][$lang] ?></label>
                        <select name="pref_theme" class="form-select">
                            <option value="Light" <?= $member['pref_theme']=='Light'?'selected':''; ?>>Light</option>
                            <option value="Dark" <?= $member['pref_theme']=='Dark'?'selected':''; ?>>Dark</option>
                        </select>
                    </div>
                    <button type="submit" name="save_preferences" class="btn btn-primary"><i class="bi bi-save me-1"></i><?= $translations['Save Preferences'][$lang] ?></button>
                </form>
            </div>
        </main>
    </div>
    <footer class="text-center p-3 mt-5">
        <small>&copy; <?= date("Y"); ?> Bangkero & Fishermen Association. All rights reserved.</small>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Sidebar navigation logic
    const sidebarLinks = document.querySelectorAll('.settings-sidebar li');
    const panels = {
        0: document.getElementById('profileForm'),
        1: document.getElementById('archivesPanel'),
        2: document.getElementById('securityPanel'),
        3: document.getElementById('preferencesPanel')
    };
    sidebarLinks.forEach((li, idx) => {
        li.addEventListener('click', () => {
            sidebarLinks.forEach(l => l.classList.remove('active'));
            li.classList.add('active');
            Object.values(panels).forEach(p => p.style.display = 'none');
            if (panels[idx]) panels[idx].style.display = '';
        });
    });

    // Avatar upload preview
    document.querySelector('.avatar-overlay').onclick = function() {
        document.getElementById('image').click();
    };
    function previewImg(event) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Get the hash from the URL (e.g., #security)
        const hash = window.location.hash;
        if (hash) {
            // Remove 'active' from all sidebar links and panels
            document.querySelectorAll('.settings-nav-link, .settings-panel').forEach(el => el.classList.remove('active', 'show'));
            // Activate the correct sidebar link
            const navLink = document.querySelector('.settings-nav-link[href="' + hash + '"]');
            if (navLink) navLink.classList.add('active');
            // Show the correct panel
            const panel = document.querySelector(hash + '.settings-panel');
            if (panel) panel.classList.add('active', 'show');
        }
    });
    </script>
</body>
</html>