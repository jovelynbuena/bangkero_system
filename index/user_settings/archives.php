<?php
session_start();
if (empty($_SESSION['username'])) { header('location: ../login.php'); exit; }
include('../../config/db_connect.php');

// Fetch member info
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT * FROM members WHERE username = ?");
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Archives</title>
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">Bangkero & Fishermen Association</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="../member.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="profile.php">Settings</a></li>
                    <li class="nav-item"><a href="../logout.php" class="nav-link text-danger">Log Out</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="settings-wrapper">
        <aside>
            <?php include 'sidebar.php'; ?>
        </aside>
        <main class="settings-main">
            <h3 class="mb-4 text-center"><i class="bi bi-archive me-2"></i> Archives</h3>
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
                    No activity yet. Your actions on the website (like profile updates, uploads, etc.) will appear here.
                </div>
            <?php endif; ?>
            <div class="mt-4 text-muted small">
                <i class="bi bi-info-circle"></i>
                This is your personal activity log. Here you can see your actions and changes made on the website.
            </div>
        </main>
    </div>
</body>
</html>