<?php
session_start();
if (empty($_SESSION['username'])) { header('location: ../login.php'); exit; }
include('../../config/db_connect.php');

// Fetch member info (needed for member_id)
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT * FROM members WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

$successMsg = $errorMsg = "";

// Handle preferences update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pref_notifications = isset($_POST['pref_notifications']) ? 1 : 0;
    $pref_language = $_POST['pref_language'];
    $pref_theme = $_POST['pref_theme'];

    $sql_pref = "UPDATE members SET pref_notifications=?, pref_language=?, pref_theme=? WHERE username=?";
    $stmt_pref = $conn->prepare($sql_pref);
    if (!$stmt_pref) {
        $errorMsg = "Prepare failed: " . $conn->error;
    } else {
        $stmt_pref->bind_param("isss", $pref_notifications, $pref_language, $pref_theme, $username);
        if ($stmt_pref->execute()) {
            // Log to archives
            $archive_stmt = $conn->prepare("INSERT INTO member_archives (member_id, title, description, type) VALUES (?, ?, ?, ?)");
            $archive_title = ($pref_language == "Tagalog") ? "Na-update ang mga Kagustuhan" : "Preferences Updated";
            $archive_desc = ($pref_language == "Tagalog") ? "In-update mo ang iyong mga kagustuhan." : "You updated your preferences.";
            $archive_type = "Preferences";
            $archive_stmt->bind_param("isss", $member['id'], $archive_title, $archive_desc, $archive_type);
            $archive_stmt->execute();
            $successMsg = ($pref_language == "Tagalog") ? "Matagumpay na na-update ang mga kagustuhan!" : "Preferences updated successfully!";
            // Refresh member info to reflect changes
            $stmt = $conn->prepare("SELECT * FROM members WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $member = $result->fetch_assoc();
        } else {
            $errorMsg = ($pref_language == "Tagalog") ? "Nabigong i-update ang mga kagustuhan." : "Failed to update preferences.";
        }
    }
}

// Set theme class for dark mode
$themeClass = (isset($member['pref_theme']) && strtolower($member['pref_theme']) == 'dark') ? 'dark-theme' : '';

// Set language variables
$lang = strtolower($member['pref_language'] ?? 'English');
$labels = [
    'en' => [
        'preferences' => 'Preferences',
        'notifications' => 'Notifications',
        'enable_notifications' => 'Enable notifications',
        'language' => 'Language',
        'theme' => 'Theme',
        'light' => 'Light',
        'dark' => 'Dark',
        'english' => 'English',
        'tagalog' => 'Tagalog',
        'save' => 'Save Preferences',
        'tip' => 'Change your preferences here. Click "Save Preferences" to apply your changes.',
    ],
    'tl' => [
        'preferences' => 'Mga Kagustuhan',
        'notifications' => 'Abiso',
        'enable_notifications' => 'I-enable ang abiso',
        'language' => 'Wika',
        'theme' => 'Tema',
        'light' => 'Maliwanag',
        'dark' => 'Madilim',
        'english' => 'Ingles',
        'tagalog' => 'Tagalog',
        'save' => 'I-save ang Mga Kagustuhan',
        'tip' => 'Baguhin ang iyong mga kagustuhan dito. I-click ang "I-save ang Mga Kagustuhan" para ma-apply ang iyong mga pagbabago.',
    ]
];
$L = ($lang == 'tagalog') ? $labels['tl'] : $labels['en'];
?>
<!DOCTYPE html>
<html lang="<?= $lang == 'tagalog' ? 'tl' : 'en' ?>">
<head>
    <title><?= $L['preferences'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f7f7f7; }
        .settings-wrapper { max-width: 1100px; margin: 48px auto 0 auto; display: flex; gap: 32px; }
        .settings-sidebar { min-width: 240px; background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); padding: 32px 0 0 0; height: fit-content; position: sticky; top: 32px; z-index: 10; }
        .settings-sidebar ul { list-style: none; padding: 0; margin: 0; }
        .settings-sidebar li { padding: 16px 32px; font-size: 1.08rem; color: #333; border-left: 4px solid transparent; cursor: pointer; transition: background 0.2s, border-color 0.2s; display: flex; align-items: center; gap: 12px; }
        .settings-sidebar li.active, .settings-sidebar li:hover { background: #f0f6ff; border-left: 4px solid #0d6efd; color: #0d6efd; }
        .settings-main { flex: 1; background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); padding: 36px 40px 32px 40px; }
        @media (max-width: 900px) { .settings-wrapper { flex-direction: column; gap: 0; } .settings-sidebar { min-width: 0; margin-bottom: 24px; } .settings-main { padding: 24px 10px; } }
        /* Dark theme styles */
        .dark-theme { background: #18191a !important; color: #f1f1f1 !important; }
        .dark-theme .settings-main, .dark-theme .settings-sidebar { background: #242526 !important; color: #f1f1f1 !important; }
        .dark-theme .form-control, .dark-theme .form-select { background: #3a3b3c !important; color: #f1f1f1 !important; border-color: #555; }
        .dark-theme .navbar, .dark-theme .navbar-light { background: #242526 !important; color: #f1f1f1 !important; }
        .dark-theme .btn-primary { background: #3a3b3c; border-color: #555; }
    </style>
</head>
<body class="<?= $themeClass ?>">
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">Bangkero & Fishermen Association</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="../member.php"><?= $lang == 'tagalog' ? 'Home' : 'Home' ?></a></li>
                    <li class="nav-item"><a class="nav-link active" href="profile.php"><?= $lang == 'tagalog' ? 'Mga Setting' : 'Settings' ?></a></li>
                    <li class="nav-item"><a href="../logout.php" class="nav-link text-danger"><?= $lang == 'tagalog' ? 'Log Out' : 'Log Out' ?></a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="settings-wrapper">
        <aside>
            <?php include 'sidebar.php'; ?>
        </aside>
        <main class="settings-main">
            <h3 class="mb-4 text-center"><i class="bi bi-gear me-2"></i> <?= $L['preferences'] ?></h3>
            <?php if ($successMsg): ?>
                <div class="alert alert-success"><?= $successMsg ?></div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                <div class="alert alert-danger"><?= $errorMsg ?></div>
            <?php endif; ?>
            <div class="alert alert-info mb-4 text-center">
                <?= $L['tip'] ?>
            </div>
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label"><?= $L['notifications'] ?></label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="pref_notifications" id="pref_notifications"
                            <?= isset($member['pref_notifications']) && $member['pref_notifications'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="pref_notifications"><?= $L['enable_notifications'] ?></label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= $L['language'] ?></label>
                    <select name="pref_language" class="form-select">
                        <option value="English" <?= $member['pref_language']=='English'?'selected':''; ?>><?= $L['english'] ?></option>
                        <option value="Tagalog" <?= $member['pref_language']=='Tagalog'?'selected':''; ?>><?= $L['tagalog'] ?></option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= $L['theme'] ?></label>
                    <select name="pref_theme" class="form-select">
                        <option value="Light" <?= $member['pref_theme']=='Light'?'selected':''; ?>><?= $L['light'] ?></option>
                        <option value="Dark" <?= $member['pref_theme']=='Dark'?'selected':''; ?>><?= $L['dark'] ?></option>
                    </select>
                </div>
                <!-- If you have text size: -->
                <!--
                <div class="mb-3">
                    <label class="form-label"><?= $L['textsize'] ?></label>
                    <select name="pref_textsize" class="form-select">
                        <option value="small" <?= ($member['pref_textsize'] ?? 'medium')=='small'?'selected':''; ?>><?= $L['small'] ?></option>
                        <option value="medium" <?= ($member['pref_textsize'] ?? 'medium')=='medium'?'selected':''; ?>><?= $L['medium'] ?></option>
                        <option value="large" <?= ($member['pref_textsize'] ?? 'medium')=='large'?'selected':''; ?>><?= $L['large'] ?></option>
                    </select>
                </div>
                -->
                <button type="submit" class="btn btn-primary"><?= $L['save'] ?></button>
            </form>
        </main>
    </div>
</body>
</html>