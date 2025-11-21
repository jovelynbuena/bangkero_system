<?php
session_start();

if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

include('../../config/db_connect.php');

// Get latest announcements first
$announcements = $conn->query("SELECT * FROM announcements ORDER BY date_posted DESC");
$memberName = isset($_SESSION['member_name']) ? $_SESSION['member_name'] : 'Member';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bangkero & Fishermen Association - Announcements</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Arial', sans-serif;
        }
        .announcement-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.07);
            transition: box-shadow .2s;
        }
        .announcement-item:hover {
            box-shadow: 0 4px 24px rgba(0,0,0,0.13);
        }
        .announcement-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #343a40;
        }
        .announcement-meta {
            font-size: 0.92rem;
            color: #6c757d;
            margin-bottom: 6px;
        }
        .announcement-content {
            font-size: 0.98rem;
            color: #444;
        }
        .link-group a {
            font-size: 0.95rem;
            color: #0d6efd;
            text-decoration: none;
            margin-right: 14px;
        }
        .link-group a:hover {
            text-decoration: underline;
        }
        .navbar {
            margin-bottom: 0;
        }
        .hero-section {
            position: relative;
            background: url('../../images/background.jpg') no-repeat center center;
            background-size: cover;
            height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }
        .hero-section::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(0,30,60,0.6) 0%, rgba(0,0,0,0.3) 100%);
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .hero-logo {
            width: 110px;
            height: 110px;
            object-fit: contain;
            border-radius: 50%;
            border: 3px solid #fff;
            margin-bottom: 10px;
            background: #fff;
        }
        .hero-text h1 {
            color: #fff;
            font-size: 2rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: 1px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.17);
        }
        .footer {
            background: #f8f9fa;
            text-align: center;
            padding: 12px 0;
            margin-top: 40px;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="#">Bangkero & Fishermen Association</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="../member.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="../member.php#events">Events</a></li>
                <li class="nav-item"><a class="nav-link active" href="member_announcement.php">Announcements</a></li>
                <li class="nav-item"><a class="nav-link" href="../user_officer.php">Officers</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Help Page</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Settings</a></li>
                <li class="nav-item">
                    <a href="#" class="nav-link text-danger" onclick="delayedLogout(event)">Log Out</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<header class="hero-section mb-4">
    <div class="hero-content">
        <img src="../images/logo1.png" alt="Association Logo" class="hero-logo">
        <div class="hero-text">
            <h1>Member Announcements</h1>
        </div>
    </div>
</header>

<!-- Welcome Message -->
<div class="container text-center mt-2 mb-4">
    <h2>Welcome, <?= htmlspecialchars($memberName) ?>!</h2>
    <p>Stay updated with the latest announcements:</p>
</div>

<!-- Announcements Section -->
<div class="container pb-4">
    <?php if ($announcements && $announcements->num_rows > 0): ?>
        <?php while ($row = $announcements->fetch_assoc()): ?>
        <div class="announcement-item">
            <div class="announcement-title"><?= htmlspecialchars($row['title']) ?></div>
            <div class="announcement-meta">Posted on <?= date("F j, Y", strtotime($row['date_posted'])) ?> by Admin</div>
            <div class="announcement-content">
                <?= nl2br(htmlspecialchars(mb_substr($row['content'], 0, 150))) . (mb_strlen($row['content']) > 150 ? '...' : '') ?>
            </div>
            <div class="link-group mt-2">
                <a href="view_announcement.php?id=<?= $row['id'] ?>">Read More</a>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="text-center text-muted">No announcements at this time.</div>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="footer">
    &copy; <?= date("Y") ?> Bangkero & Fishermen Association. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function delayedLogout(event) {
    event.preventDefault();
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = 0;
    overlay.style.left = 0;
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
    overlay.style.display = 'flex';
    overlay.style.alignItems = 'center';
    overlay.style.justifyContent = 'center';
    overlay.style.zIndex = 9999;
    overlay.innerHTML = `<div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Logging out...</span>
                         </div>
                         <p class="ms-2 fw-bold">Logging out...</p>`;
    document.body.appendChild(overlay);
    setTimeout(() => {
        window.location.href = '../logout.php';
    }, 1000);
}
</script>
</body>
</html>