<?php
session_start();

if ($_SESSION['username'] == "") {
    header('location: login.php');
}
include('../config/db_connect.php');

// Fetch Upcoming Events
$upcoming_sql = "SELECT * FROM events WHERE date >= CURDATE() ORDER BY date ASC";
$upcoming_result = $conn->query($upcoming_sql);

// Fetch Past Events
$past_sql = "SELECT * FROM events WHERE date < CURDATE() ORDER BY date DESC";
$past_result = $conn->query($past_sql);

// Fetch latest announcements for bell dropdown (limit 5, unread support)
$announcement_sql = "SELECT * FROM announcements ORDER BY date_posted DESC LIMIT 5";
$announcement_result = $conn->query($announcement_sql);
$announcement_count = $announcement_result ? $announcement_result->num_rows : 0;

// Example: Track read announcements per user (add your own logic for a real app)
if (!isset($_SESSION['read_announcements'])) {
    $_SESSION['read_announcements'] = [];
}
$unread_count = 0;
$announcements = [];
if ($announcement_result) {
    while ($row = $announcement_result->fetch_assoc()) {
        $announcements[] = $row;
        if (!in_array($row['id'], $_SESSION['read_announcements'])) {
            $unread_count++;
        }
    }
}

$memberName = isset($_SESSION['member_name']) ? $_SESSION['member_name'] : 'Member';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bangkero & Fishermen Association</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .hero-section {
            position: relative;
            background: url('../images/background.jpg') no-repeat center center;
            background-size: cover;
            height: 340px;
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
            width: 190x;
            height: 190px;
            object-fit: contain;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 4px 24px rgba(0,0,0,0.3);
            margin-bottom: 18px;
            background: #fff;
            animation: fadeInDown 1s;
        }
        .hero-text h1 {
            color: #fff;
            font-size: 2.8rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: 1.5px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.25);
            animation: fadeInUp 1.2s;
        }
        .hero-text p {
            color: #e0e0e0;
            font-size: 1.25rem;
            font-weight: 400;
            margin: 8px 0 0 0;
            text-shadow: 0 2px 8px rgba(0,0,0,0.20);
            letter-spacing: 1px;
            animation: fadeInUp 1.5s;
        }
        @keyframes fadeInDown {
          from { opacity: 0; transform: translateY(-40px);}
          to   { opacity: 1; transform: translateY(0);}
        }
        @keyframes fadeInUp {
          from { opacity: 0; transform: translateY(40px);}
          to   { opacity: 1; transform: translateY(0);}
        }
        footer {
            background-color: #f8f9fa;
        }
        .card-img-top {
            height: 300px;
            object-fit: cover;
            transition: transform 0.3s ease-in-out;
        }
        .card-img-top:hover {
            transform: scale(1.05);
        }
        .card-body {
            padding: 15px;
        }
        .card-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .card-text {
            font-size: 1rem;
            color: #555;
            margin-bottom: 10px;
        }
        .card-info {
            font-size: 0.9rem;
            color: #777;
            margin: 5px 0;
        }
        .btn-container {
            text-align: center;
        }
        .btn-container .btn {
            margin: 10px;
        }
        /* Notification Bell */
        .notification-bell {
            position: relative;
            cursor: pointer;
            transition: color 0.2s;
        }
        .notification-bell .badge {
            position: absolute;
            top: -2px;
            right: -5px;
            font-size: 0.75rem;
            background: #dc3545;
            color: white;
            animation: pulse 1.2s infinite;
        }
        @keyframes pulse {
          0% { box-shadow: 0 0 0 0 #dc354555;}
          70% { box-shadow: 0 0 0 8px #dc354500;}
          100% { box-shadow: 0 0 0 0 #dc354500;}
        }
        .notification-bell.has-unread {
            color: #dc3545;
            animation: bellring 0.8s 2;
        }
        @keyframes bellring {
          0% { transform: rotate(0deg);}
          10% { transform: rotate(-15deg);}
          20% { transform: rotate(10deg);}
          30% { transform: rotate(-10deg);}
          40% { transform: rotate(8deg);}
          50% { transform: rotate(-8deg);}
          60% { transform: rotate(6deg);}
          70% { transform: rotate(-6deg);}
          80% { transform: rotate(4deg);}
          90% { transform: rotate(-4deg);}
          100% { transform: rotate(0deg);}
        }
        .dropdown-menu {
            width: 340px;
            max-height: 400px;
            overflow-y: auto;
        }
        .notification-item {
            padding: 10px 16px;
            border-bottom: 1px solid #f1f1f1;
            background: #f9f9f9;
            transition: background 0.2s;
        }
        .notification-item.unread {
            background: #fffbea;
        }
        .notification-item:last-child { border-bottom: none; }
        .notification-title {
            font-weight: 600;
            font-size: 1rem;
            margin: 0;
        }
        .notification-date {
            font-size: 0.86rem;
            color: #999;
        }
        .notification-preview {
            margin: 3px 0 0 0;
            font-size: 0.93rem;
            color: #444;
        }
        .dropdown-header.fw-bold {
            font-size: 1.1rem;
            color: #2e3a4a;
        }
        .mark-read-btn {
            float: right;
            font-size: 0.9em;
            color: #0d6efd;
            background: none;
            border: none;
            cursor: pointer;
        }
        .mark-read-btn:hover { text-decoration: underline; }
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
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="member.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#events">Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="user_officer.php">Officers</a></li>
                    <li class="nav-item"><a class="nav-link" href="help.php">Help Page</a></li>
                    <li class="nav-item"><a class="nav-link" href="user_settings/profile.php">Settings</a></li>
                    <!-- Notification Bell Dropdown -->
                    <li class="nav-item dropdown">
                        <span class="nav-link position-relative notification-bell<?= $unread_count > 0 ? ' has-unread' : '' ?>" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell-fill fs-4"></i>
                            <?php if ($unread_count > 0): ?>
                                <span class="badge rounded-pill"><?= $unread_count ?></span>
                            <?php endif; ?>
                        </span>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notifDropdown">
                            <li class="dropdown-header fw-bold">
                                Announcements
                                <?php if ($unread_count > 0): ?>
                                    <button class="mark-read-btn" onclick="markAllRead(event)">Mark all as read</button>
                                <?php endif; ?>
                            </li>
                            <?php if (count($announcements) > 0): ?>
                                <?php foreach ($announcements as $ann): 
                                    $is_unread = !in_array($ann['id'], $_SESSION['read_announcements']);
                                ?>
                                    <li>
                                        <a class="dropdown-item notification-item<?= $is_unread ? ' unread' : '' ?>" href="announcement/view_announcement.php?id=<?= $ann['id'] ?>" onclick="markAsRead(<?= $ann['id'] ?>)">
                                            <div class="notification-title"><?= htmlspecialchars($ann['title']) ?></div>
                                            <div class="notification-date"><?= date("M j, Y", strtotime($ann['date_posted'])) ?></div>
                                            <div class="notification-preview"><?= htmlspecialchars(mb_substr($ann['content'], 0, 50)) . (mb_strlen($ann['content']) > 50 ? '...' : '') ?></div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="dropdown-item text-center text-muted">No announcements yet.</li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="announcement/member_announcement.php" class="dropdown-item text-center text-primary fw-semibold">
                                    View all announcements <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link text-danger" onclick="delayedLogout(event)">Log Out</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="hero-content">
            <img src="images/logo1.png" alt="Association Logo" class="hero-logo">
            <div class="hero-text">
                <h1>Bangkero and Fishermen Association</h1>
                <p>Barangay Barretto, Olongapo City</p>
            </div>
        </div>
    </header>

    <!-- Welcome Message -->
    <div class="container mt-4 text-center">
        <h2>Welcome, <?php echo htmlspecialchars($memberName); ?>!</h2>
        <p>Glad to have you back. Here's what's happening:</p>
    </div>

    <!-- Events Section -->
    <div class="container mt-5" id="events">

     <div class="container mt-5" id="events">

    <style>
        .event-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        .event-card img {
            height: 250px;
            object-fit: cover;
        }
        .event-card .card-body {
            padding: 1.5rem;
        }
        .event-card .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .event-card .card-text {
            color: #555;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }
        .event-card .card-info {
            font-size: 0.9rem;
            margin: 0.25rem 0;
        }
    </style>

    <!-- Upcoming Events -->
    <h3 class="text-center mb-4">Upcoming Events</h3>
    <div class="row">
        <?php if ($upcoming_result && $upcoming_result->num_rows > 0): ?>
            <?php while ($row = $upcoming_result->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="card event-card h-100 shadow-sm">
                        <?php if (!empty($row['event_poster'])): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($row['event_poster']); ?>" class="card-img-top" alt="Event Poster">
                        <?php else: ?>
                            <img src="../uploads/default.jpg" class="card-img-top" alt="Default Event Image">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['event_name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                            <p class="card-info"><strong>Date:</strong> <?php echo $row['date']; ?></p>
                            <p class="card-info"><strong>Time:</strong> <?php echo $row['time']; ?></p>
                            <p class="card-info"><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted">No upcoming events at the moment.</p>
        <?php endif; ?>
    </div>

    <!-- Past Events -->
    <h3 class="text-center mt-5 mb-4">Past Events</h3>
    <div class="row">
        <?php if ($past_result && $past_result->num_rows > 0): ?>
            <?php while ($row = $past_result->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="card event-card h-100 shadow-sm">
                        <?php if (!empty($row['event_poster'])): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($row['event_poster']); ?>" class="card-img-top" alt="Event Poster">
                        <?php else: ?>
                            <img src="../uploads/default.jpg" class="card-img-top" alt="Default Event Image">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['event_name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                            <p class="card-info"><strong>Date:</strong> <?php echo $row['date']; ?></p>
                            <p class="card-info"><strong>Time:</strong> <?php echo $row['time']; ?></p>
                            <p class="card-info"><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted">No past events available.</p>
        <?php endif; ?>
    </div>

</div>
   

    <!-- Footer -->
    <footer class="text-center p-3">
        <small>&copy; <?php echo date("Y"); ?> Bangkero & Fishermen Association. All rights reserved.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert Success for Login -->
    <?php if (isset($_GET['login']) && $_GET['login'] === 'success'): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        Toast.fire({
            icon: "success",
            title: "Signed in successfully"
        });
    </script>
    <?php endif; ?>
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
            window.location.href = 'logout.php';
        }, 1000);
    }

    // Mark as read with AJAX (for demo: session only, no db update)
    function markAsRead(annId) {
        fetch('..mark_read.php?id=' + annId)
            .then(() => {
                // Could reload or update UI here
            });
    }
    function markAllRead(event) {
        event.stopPropagation();
        fetch('mark_read.php?all=1')
        .then(() => {
            window.location.reload();
        });
    }
    </script>
</body>
</html>