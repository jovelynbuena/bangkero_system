<?php
session_start();
if (empty($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('location: login.php');
    exit;
}
include('../config/db_connect.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Settings | Bangkero & Fishermen Association</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f7f7f7; }
        .settings-header {
            background: linear-gradient(90deg, #0d6efd 0%, #0a58ca 100%);
            color: #fff;
            padding: 32px 0 18px 0;
            text-align: center;
            margin-bottom: 0;
        }
        .settings-header h1 {
            font-weight: 800;
            font-size: 2.1rem;
            margin-bottom: 6px;
        }
        .settings-header p {
            font-size: 1.05rem;
            color: #e0e0e0;
            margin-bottom: 0;
        }
        .settings-card {
            max-width: 600px;
            margin: 32px auto 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
            padding: 32px 24px 24px 24px;
        }
        .settings-btn {
            width: 100%;
            margin-bottom: 18px;
            font-size: 1.15rem;
            font-weight: 500;
            padding: 14px 0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: background 0.2s, color 0.2s;
        }
        .settings-btn i { font-size: 1.5rem; }
        .settings-btn.users { background: #e7f1ff; color: #0d6efd; }
        .settings-btn.users:hover { background: #0d6efd; color: #fff; }
        .settings-btn.logs { background: #fffbe7; color: #f7b500; }
        .settings-btn.logs:hover { background: #f7b500; color: #fff; }
        .settings-btn.site { background: #e7fff2; color: #0dfd7a; }
        .settings-btn.site:hover { background: #0dfd7a; color: #fff; }
        .settings-btn.pref { background: #f9e7ff; color: #a20dfd; }
        .settings-btn.pref:hover { background: #a20dfd; color: #fff; }
        @media (max-width: 600px) {
            .settings-card { padding: 12px 2vw; }
            .settings-header { padding: 18px 0 8px 0; }
        }
    </style>
</head>
<body>
    <div class="settings-header">
        <h1><i class="bi bi-gear"></i> Admin Settings</h1>
        <p>Manage users, system preferences, and more</p>
    </div>
    <div class="settings-card shadow">
        <h4 class="mb-4">Welcome, Admin!</h4>
        <a href="admin_users.php" class="btn settings-btn users"><i class="bi bi-people"></i> User Management</a>
        <a href="admin_logs.php" class="btn settings-btn logs"><i class="bi bi-clipboard-data"></i> System Logs</a>
        <a href="admin_site.php" class="btn settings-btn site"><i class="bi bi-globe"></i> Site Settings</a>
        <a href="admin_preferences.php" class="btn settings-btn pref"><i class="bi bi-sliders"></i> Admin Preferences</a>
        <a href="admin.php" class="btn btn-outline-secondary mt-3"><i class="bi bi-arrow-left"></i> Back to Admin Dashboard</a>
    </div>
    <footer class="text-center p-3 mt-5">
        <small>&copy; <?= date("Y"); ?> Bangkero & Fishermen Association. All rights reserved.</small>
    </footer>
</body>
</html>