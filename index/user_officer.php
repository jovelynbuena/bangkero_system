<?php
session_start();

// Redirect to login if username is not set
if (!isset($_SESSION['username']) || $_SESSION['username'] === "") {
    header('location: login.php');
    exit;
}

// Default role if not set
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'member';
}

include('../config/db_connect.php');

// Fetch officers with member names and role names
$query = "
    SELECT 
        officers.id,
        COALESCE(officer_roles.role_name, 'Member') AS position,
        officers.term_start,
        officers.term_end,
        officers.image,
        members.name AS member_name
    FROM officers
    JOIN members ON officers.member_id = members.id
    LEFT JOIN officer_roles ON officers.role_id = officer_roles.id
    ORDER BY FIELD(officer_roles.role_name, 'President', 'Vice President', 'Secretary', 'Treasurer', 'Auditor', 'Pro') ASC
";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Group officers by position for display
$officers_by_position = [];
while ($row = $result->fetch_assoc()) {
    $pos_key = strtolower($row['position']);
    $officers_by_position[$pos_key][] = $row;
}

// Define display order for hierarchy
$display_order = ['president','vice president','secretary','treasurer','auditor','pro','member'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bangkero & Fishermen Association</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* Body */
body {
    background-color: #f6f9fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Hero Section */
.hero-section {
    position: relative;
    background: url('../images/background.jpg') no-repeat center center;
    background-size: cover;
    height: 240px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 1rem;
    overflow: hidden;
}

.hero-section::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, rgba(0,30,60,0.5) 0%, rgba(0,0,0,0.3) 100%);
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
    width: 145px;
    height: 145px;
    object-fit: contain;
    border-radius: 50%;
    border: 4px solid #fff;
    background: #fff;
    box-shadow: 0 4px 24px rgba(0,0,0,0.22);
    margin-bottom: 15px;
    animation: fadeInDown 1s;
}

.hero-text h1 {
    color: #fff;
    font-size: 2.3rem;
    font-weight: 800;
    margin: 0;
    letter-spacing: 1.2px;
    text-shadow: 0 2px 8px rgba(0,0,0,0.25);
    animation: fadeInUp 1.1s;
}

.hero-text p {
    color: #e0e0e0;
    font-size: 1.05rem;
    font-weight: 400;
    margin-top: 8px;
    text-shadow: 0 2px 8px rgba(0,0,0,0.20);
    letter-spacing: 1px;
    animation: fadeInUp 1.3s;
}

/* Animations */
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-40px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Page Title */
.page-title {
    font-weight: 700;
    font-size: 2.4rem;
    text-align: center;
    margin: 35px 0 30px;
    color: #12306a;
    letter-spacing: 1px;
}

/* Officer List */
.officer-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 32px;
    margin-bottom: 60px;
}

/* Officer Card */
.officer-card {
    background: #fff;
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid #e6e6e6;
    box-shadow: 0 6px 18px rgba(0,0,0,0.10);
    transition: transform 0.21s ease, box-shadow 0.21s ease;
    width: 255px;
    min-height: 360px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.officer-card:hover {
    transform: scale(1.04) translateY(-8px);
    box-shadow: 0 16px 32px rgba(0,0,0,0.12);
    z-index: 2;
}

/* Officer Image */
.officer-img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-bottom: 1px solid #eee;
}

/* Officer Details */
.officer-details {
    padding: 18px 16px 10px;
    text-align: center;
    display: flex;
    flex-direction: column;
    flex: 1;
    justify-content: flex-start;
}

.officer-details h5 {
    margin-bottom: 5px;
    font-weight: 700;
    font-size: 1.19rem;
    color: #162955;
}

.position {
    color: #0d6efd;
    font-size: 1.08rem;
    font-weight: 700;
    margin-bottom: 4px;
    text-transform: uppercase;
}

.term {
    font-size: 0.93rem;
    color: #888;
    margin-bottom: 8px;
}

/* Responsive Design */
@media (max-width: 900px) {
    .officer-list { gap: 15px; }
    .officer-card { width: 175px; min-height: 280px; }
}

@media (max-width: 600px) {
    .officer-list { gap: 8px; }
    .officer-card { width: 98vw; min-height: 190px; }
    .page-title { font-size: 1.4rem; }
}
</style>

</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
<div class="container">
<a class="navbar-brand fw-bold" href="#">Bangkero & Fishermen Association</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav ms-auto">
<li class="nav-item"><a class="nav-link" href="member.php">Home</a></li>
<li class="nav-item"><a class="nav-link" href="#events">Events</a></li>
<li class="nav-item"><a class="nav-link" href="#">Announcement</a></li>
<li class="nav-item"><a class="nav-link active" href="user_officer.php">Officers</a></li>
<li class="nav-item"><a class="nav-link" href="#">Help Page</a></li>
<li class="nav-item"><a class="nav-link" href="#">Settings</a></li>
<li class="nav-item"><a class="nav-link text-danger" href="logout.php">Log Out</a></li>
</ul>
</div>
</div>
</nav>

<header class="hero-section">
<div class="hero-content">
<img src="images/logo1.png" alt="Association Logo" class="hero-logo">
<div class="hero-text">
<h1>Bangkero and Fishermen Association</h1>
<p>Barangay Barretto, Olongapo City</p>
</div>
</div>
</header>

<div class="container">
<h2 class="page-title">Meet Our Officers</h2>
<div class="officer-list">
<?php
$has_officer = false;
foreach ($display_order as $pos_key):
    if (!empty($officers_by_position[$pos_key])):
        foreach ($officers_by_position[$pos_key] as $row):
            $has_officer = true;
?>
<div class="officer-card">
<?php if (!empty($row['image']) && file_exists("../uploads/".$row['image'])): ?>
<img src="../uploads/<?= htmlspecialchars($row['image']) ?>" alt="Officer Image" class="officer-img">
<?php else: ?>
<img src="https://via.placeholder.com/300x180?text=No+Image" alt="No Image" class="officer-img">
<?php endif; ?>
<div class="officer-details">
<div class="position"><?= strtoupper(htmlspecialchars($row['position'])) ?></div>
<h5><?= htmlspecialchars($row['member_name']) ?></h5>
<div class="term">
<?= ($row['term_start'] !== "0000-00-00") ? htmlspecialchars($row['term_start']) : 'N/A' ?>
–
<?= ($row['term_end'] !== "0000-00-00") ? htmlspecialchars($row['term_end']) : 'N/A' ?>
</div>
</div>
</div>
<?php
        endforeach;
    endif;
endforeach;

if (!$has_officer):
    echo '<div class="text-center text-muted w-100">No officers have been assigned yet.</div>';
endif;
?>
</div>
</div>

<footer class="text-center p-3 mt-5">
<small>&copy; <?= date("Y"); ?> Bangkero & Fishermen Association. All rights reserved.</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
