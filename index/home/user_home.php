<?php
session_start();
require_once('../../config/db_connect.php');

$nextEventResult = $conn->query("SELECT * FROM events WHERE date >= CURDATE() ORDER BY date ASC LIMIT 1");
$nextEvent = $nextEventResult ? $nextEventResult->fetch_assoc() : null;

$latestAnnouncements = $conn->query("SELECT * FROM announcements ORDER BY date_posted DESC LIMIT 3");


?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Association Portal</title>
  <!-- Bootstrap CSS & Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #2c3e50;
      --secondary: #34495e;
      --accent: #5a6c7d;
      --light: #ecf0f1;
      --success: #27ae60;
      --info: #3498db;
      --bg: #f8f9fa;
      --dark: #1a252f;
      --gray: #95a5a6;
      --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg);
      color: #2c3e50;
      overflow-x: hidden;
    }
    * {
      scroll-behavior: smooth;
    }
    * {
      scroll-behavior: smooth;
    }
    
    /* Enhanced Navbar */
    .navbar {
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(10px);
      box-shadow: 0 2px 20px rgba(0,0,0,0.06);
      padding: 1.2rem 0;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    .navbar.shrink {
      padding: 0.6rem 0;
      box-shadow: 0 4px 24px rgba(0,0,0,0.1);
    }
    .navbar-brand img {
      height: 52px;
      transition: all 0.4s ease;
      filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    }
    .navbar.shrink .navbar-brand img {
      height: 40px;
    }
    .navbar-nav .nav-link {
      font-weight: 600;
      color: var(--dark) !important;
      margin: 0 10px;
      padding: 10px 16px;
      border-radius: 10px;
      position: relative;
      transition: all 0.3s ease;
    }
    .navbar-nav .nav-link::after {
      content: '';
      position: absolute;
      bottom: 6px;
      left: 50%;
      transform: translateX(-50%);
      width: 0;
      height: 3px;
      background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
      border-radius: 2px;
      transition: width 0.3s ease;
    }
    .navbar-nav .nav-link:hover::after,
    .navbar-nav .nav-link.active::after {
      width: 70%;
    }
    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link.active {
      color: var(--primary-orange) !important;
      background: var(--light-orange);
    }
    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link.active {
      color: var(--primary-orange) !important;
      background: var(--light-orange);
    }
    
    /* Modern Hero Carousel */
    .carousel-item {
      height: 75vh;
      min-height: 400px;
      background-size: cover;
      background-position: center;
      position: relative;
    }
    .carousel-item::before {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: linear-gradient(135deg, rgba(44, 62, 80, 0.75) 0%, rgba(26, 37, 47, 0.85) 100%);
      z-index: 1;
    }
    .carousel-caption {
      position: absolute;
      top: 50%;
      left: 0; right: 0;
      transform: translateY(-50%);
      z-index: 2;
      text-shadow: 2px 2px 12px rgba(0,0,0,0.4);
    }
    .carousel-caption h1 {
      font-size: 3.5rem;
      font-weight: 800;
      color: #fff;
      font-family: 'Poppins', sans-serif;
      letter-spacing: -1px;
      margin-bottom: 20px;
      animation: fadeInUp 0.8s ease-out;
    }
    .carousel-caption p {
      font-size: 1.3rem;
      margin-top: 16px;
      color: #ffffff;
      font-weight: 400;
      animation: fadeInUp 0.8s ease-out 0.2s backwards;
    }
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    /* Modern Intro Section */
    .intro-section {
      background: linear-gradient(180deg, #ffffff 0%, #fff5f0 100%);
      padding: 50px 0;
      position: relative;
      overflow: hidden;
    }
    .intro-section::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(44, 62, 80, 0.05) 0%, transparent 70%);
      border-radius: 50%;
    }
    .intro-section h2 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 700;
      margin-bottom: 1.5rem;
      font-size: 2.8rem;
      position: relative;
      display: inline-block;
    }
    .intro-section h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
      border-radius: 2px;
    }
    .intro-section p {
      color: #4b5563;
      font-size: 1.15rem;
      line-height: 1.8;
      max-width: 900px;
      margin: 0 auto;
      position: relative;
    }
    .intro-section p {
      color: #4b5563;
      font-size: 1.15rem;
      line-height: 1.8;
      max-width: 900px;
      margin: 0 auto;
      position: relative;
    }
   
    /* Enhanced Event Highlight */
    .event-highlight {
      background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
      padding: 50px 20px;
      position: relative;
    }
    .event-highlight::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 6px;
      background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 50%, var(--accent) 100%);
    }
    .event-highlight h2 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      margin-bottom: 35px;
      font-weight: 800;
      font-size: 2.8rem;
      position: relative;
      display: inline-block;
      letter-spacing: -0.5px;
    }
    .event-highlight h2::after {
      content: '';
      position: absolute;
      bottom: -12px;
      left: 0;
      width: 60%;
      height: 5px;
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      border-radius: 3px;
    }
    .event-card {
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 12px 48px rgba(44, 62, 80, 0.12);
      transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
      border: none;
      background: white;
      position: relative;
    }
    .event-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 5px;
      background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
      opacity: 0;
      transition: opacity 0.4s ease;
    }
    .event-card:hover::before {
      opacity: 1;
    }
    .event-card:hover {
      transform: translateY(-12px);
      box-shadow: 0 24px 80px rgba(44, 62, 80, 0.18);
    }
    .event-poster-wrapper {
      position: relative;
      overflow: hidden;
      height: 100%;
      min-height: 450px;
    }
    .event-poster-wrapper::after {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
      width: 50px;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1));
      pointer-events: none;
    }
    .event-card img {
      height: 100%;
      width: 100%;
      object-fit: cover;
      transition: transform 0.6s ease;
    }
    .event-card:hover img {
      transform: scale(1.08);
    }
    .event-details {
      padding: 50px 45px;
      position: relative;
    }
    .event-badge {
      display: inline-block;
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      padding: 8px 20px;
      border-radius: 50px;
      font-size: 0.85rem;
      font-weight: 700;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin-bottom: 20px;
      box-shadow: 0 4px 12px rgba(44, 62, 80, 0.25);
    }
    .event-details h3 {
      color: var(--dark);
      font-weight: 800;
      font-size: 2.2rem;
      margin-bottom: 20px;
      font-family: 'Poppins', sans-serif;
      line-height: 1.3;
      letter-spacing: -0.5px;
    }
    .event-description {
      color: #64748b;
      font-size: 1.05rem;
      line-height: 1.75;
      margin-bottom: 25px;
      font-weight: 400;
    }
    .event-info-grid {
      display: grid;
      gap: 12px;
      margin-bottom: 25px;
    }
    .event-details .icon-text {
      display: flex;
      align-items: center;
      padding: 12px 18px;
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      border-radius: 14px;
      transition: all 0.3s ease;
      border-left: 4px solid var(--primary);
    }
    .event-details .icon-text:hover {
      background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
      transform: translateX(6px);
      box-shadow: 0 4px 16px rgba(44, 62, 80, 0.1);
    }
    .event-details .icon-text i {
      color: var(--primary);
      margin-right: 16px;
      font-size: 1.4rem;
      width: 32px;
      text-align: center;
    }
    .event-details .icon-text span {
      color: var(--dark);
      font-weight: 600;
      font-size: 1.05rem;
    }
    .event-empty-state {
      text-align: center;
      padding: 60px 20px;
      background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
      border-radius: 24px;
      border: 2px dashed #cbd5e1;
    }
    .event-empty-state i {
      font-size: 5rem;
      color: #cbd5e1;
      margin-bottom: 24px;
    }
    .event-empty-state p {
      color: #94a3b8;
      font-size: 1.15rem;
      font-weight: 500;
    }
    
    /* Modern Countdown */
    .countdown-wrapper {
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      padding: 25px;
      border-radius: 20px;
      margin-top: 25px;
      border: 1px solid #e2e8f0;
      position: relative;
      overflow: hidden;
    }
    .countdown-wrapper::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
    }
    .countdown-label {
      text-align: center;
      color: var(--dark);
      font-weight: 700;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      margin-bottom: 15px;
      opacity: 0.7;
    }
    .countdown {
      display: flex;
      justify-content: center;
      gap: 20px;
      flex-wrap: wrap;
    }
    .countdown div {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: #fff;
      padding: 28px 24px;
      border-radius: 18px;
      min-width: 100px;
      box-shadow: 0 8px 24px rgba(44, 62, 80, 0.25);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }
    .countdown div::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
      opacity: 0;
      transition: opacity 0.4s ease;
    }
    .countdown div:hover::before {
      opacity: 1;
    }
    .countdown div:hover {
      transform: translateY(-6px) scale(1.05);
      box-shadow: 0 16px 40px rgba(44, 62, 80, 0.35);
    }
    .countdown div span {
      display: block;
      font-size: 2.5rem;
      font-weight: 900;
      font-family: 'Poppins', sans-serif;
      line-height: 1;
      margin-bottom: 8px;
      text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    .countdown div small {
      font-size: 0.8rem;
      opacity: 0.95;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    /* Modern Announcement Cards */
    .announcements-section {
      padding: 50px 0;
      background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    }
    .announcements-section h2 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 700;
      margin-bottom: 35px;
      font-size: 2.5rem;
    }
    .announcement-card {
      background: #ffffff;
      border: none;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      transition: all 0.4s ease;
      overflow: hidden;
      position: relative;
    }
    .announcement-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
      transform: scaleX(0);
      transition: transform 0.4s ease;
    }
    .announcement-card:hover::before {
      transform: scaleX(1);
    }
    .announcement-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 12px 40px rgba(255, 107, 53, 0.2);
    }
    .announcement-card h5 {
      color: var(--dark);
      font-weight: 700;
      font-size: 1.2rem;
    }
    .announcement-card .badge {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      padding: 6px 12px;
      border-radius: 8px;
      font-weight: 600;
    }
    .announcement-card .btn {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      border: none;
      color: white;
      font-weight: 600;
      padding: 10px 24px;
      border-radius: 10px;
      transition: all 0.3s ease;
    }
    .announcement-card .btn:hover {
      transform: translateX(4px);
      box-shadow: 0 4px 12px rgba(44, 62, 80, 0.25);
    }
    .view-all-btn {
      background: transparent;
      border: 2px solid var(--primary);
      color: var(--primary);
      font-weight: 600;
      padding: 12px 32px;
      border-radius: 12px;
      transition: all 0.3s ease;
    }
    .view-all-btn:hover {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(44, 62, 80, 0.25);
    }

    .view-all-btn:hover {
      background: linear-gradient(135deg, var(--primary-orange) 0%, var(--dark-orange) 100%);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 107, 53, 0.3);
    }
    
    /* Partnerships Section */
    .partnerships {
      padding: 50px 0;
      background: linear-gradient(135deg, #ecf0f1 0%, #ffffff 100%);
    }
    .partnerships h2 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 700;
      font-size: 2.5rem;
      margin-bottom: 20px;
    }
    .partnerships img {
      max-height: 90px;
      transition: all 0.4s ease;
      filter: grayscale(100%) brightness(0.9);
      opacity: 0.7;
    }
    .partnerships img:hover {
      transform: scale(1.12);
      filter: grayscale(0%) brightness(1);
      opacity: 1;
    }
    .partner-card {
      background: white;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.06);
      transition: all 0.3s ease;
      height: 100%;
    }
    .partner-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 40px rgba(44, 62, 80, 0.12);
    }

   /* Footer spacing */
    .bottom-space { height: 40px; }
    
    /* Responsive */
    @media (max-width: 768px) {
      .carousel-caption h1 {
        font-size: 2rem;
      }
      .carousel-caption p {
        font-size: 1rem;
      }
      .intro-section h2,
      .event-highlight h2,
      .announcements-section h2,
      .partnerships h2 {
        font-size: 2rem;
      }
      .countdown div {
        min-width: 70px;
        padding: 16px;
      }
      .countdown div span {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <main>
  <?php include("partials/navbar.php"); ?>

<!-- Hero Carousel -->
<div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
  <div class="carousel-inner">
    <div class="carousel-item active" style="background-image: url('../images/home.jpg');">
      <div class="carousel-caption text-center">
        <h1>Welcome to Our Association</h1>
        <p>Connecting members, sharing resources, and empowering leaders.</p>
      </div>
    </div>
    <div class="carousel-item" style="background-image: url('../images/slides2.jpg');">
      <div class="carousel-caption text-center">
        <h1>Together We Grow</h1>
        <p>Building a stronger community through unity.</p>
      </div>
    </div>
    <div class="carousel-item" style="background-image: url('../images/slide3.jpg');">
      <div class="carousel-caption text-center">
        <h1>Empowering Leaders</h1>
        <p>Guiding the next generation of members.</p>
      </div>
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>

<!-- Introduction Section -->
<section class="intro-section text-center">
  <div class="container">
    <h2>Who We Are</h2>
    <p>
      The <strong>Bankero & Fishermen Association</strong> is a community-driven organization dedicated to supporting local fishermen and their families. Founded in 2009, the association was established with the mission to promote sustainable fishing practices, strengthen unity among members, and provide opportunities for growth and livelihood development.<br><br>
      We serve as a platform for collaboration, leadership, and training, ensuring that every member is empowered to thrive while preserving the rich fishing heritage of our community.
    </p>
  </div>
</section>



<!-- Event Highlight Section -->
<section class="event-highlight">
    <div class="container">
        <h2 class="text-center">🎉 Upcoming Major Event</h2>
        <?php if ($nextEvent): ?>
        <div class="card event-card shadow-lg border-0">
            <div class="row g-0 align-items-stretch">
                <!-- Event Poster -->
                <div class="col-md-5">
                    <div class="event-poster-wrapper">
                        <img src="../../uploads/<?php echo htmlspecialchars($nextEvent['event_poster'] ?: 'default.jpg'); ?>" 
                             class="img-fluid" alt="Event Poster">
                    </div>
                </div>
                
                <!-- Event Details -->
                <div class="col-md-7 event-details">
                    <!-- Event Badge -->
                    <span class="event-badge">
                        <i class="bi bi-star-fill me-1"></i> Featured Event
                    </span>
                    
                    <!-- Event Title -->
                    <h3><?php echo htmlspecialchars($nextEvent['event_name']); ?></h3>
                    
                    <!-- Event Description -->
                    <p class="event-description">
                        <?php 
                            $desc = htmlspecialchars($nextEvent['description']);
                            $maxLength = 200;
                            if (strlen($desc) > $maxLength) {
                                $desc = substr($desc, 0, $maxLength) . '...';
                            }
                            echo $desc;
                        ?>
                    </p>
                    
                    <!-- Event Info Grid -->
                    <div class="event-info-grid">
                        <div class="icon-text">
                            <i class="bi bi-calendar-event-fill"></i>
                            <span><?php echo date('l, F d, Y', strtotime($nextEvent['date'])); ?></span>
                        </div>
                        <div class="icon-text">
                            <i class="bi bi-clock-fill"></i>
                            <span><?php echo date('g:i A', strtotime($nextEvent['time'])); ?></span>
                        </div>
                        <div class="icon-text">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span><?php echo htmlspecialchars($nextEvent['location']); ?></span>
                        </div>
                    </div>
                    
                    <!-- Countdown Timer -->
                    <div class="countdown-wrapper">
                        <div class="countdown-label">
                            <i class="bi bi-hourglass-split me-2"></i>Event Starts In
                        </div>
                        <div class="countdown" data-event-date="<?php echo $nextEvent['date'] . ' ' . $nextEvent['time']; ?>">
                            <div><span class="days">0</span><small>Days</small></div>
                            <div><span class="hours">0</span><small>Hours</small></div>
                            <div><span class="minutes">0</span><small>Minutes</small></div>
                            <div><span class="seconds">0</span><small>Seconds</small></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="event-empty-state">
            <i class="bi bi-calendar-x"></i>
            <p class="mb-0">No upcoming events scheduled at the moment.</p>
            <p class="text-muted small mt-2">Check back soon for exciting announcements!</p>
        </div>
        <?php endif; ?>
    </div>
</section>
<section class="container my-5">
   <section class="announcements-section">
    <div class="container">
        <h2 class="text-center">📢 Latest Announcements</h2>
        
        <div class="row justify-content-center">
            <?php while ($row = $latestAnnouncements->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="announcement-card h-100 d-flex flex-column p-4">
                        <!-- Badge -->
                        <span class="badge mb-3 align-self-start">
                            <i class="bi bi-megaphone-fill me-1"></i> Announcement
                        </span>
                        
                        <!-- Title -->
                        <h5 class="mb-3">
                            <?= htmlspecialchars($row['title']) ?>
                        </h5>
                        
                        <!-- Date -->
                        <p class="text-muted small mb-3">
                            <i class="bi bi-calendar3 me-1"></i>
                            <?= date("F j, Y", strtotime($row['date_posted'])) ?>
                        </p>
                        
                        <!-- Content Preview -->
                        <p class="flex-grow-1 text-secondary" style="min-height: 80px; line-height: 1.6;">
                            <?= nl2br(htmlspecialchars(substr($row['content'], 0, 130))) ?>...
                        </p>
                        
                        <!-- Read More Button -->
                        <div class="mt-auto pt-3">
                            <a href="announcement.php" class="btn w-100">
                                Read More <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- View All Button -->
        <div class="text-center mt-5">
            <a href="announcement.php" class="view-all-btn">
                View All Announcements <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>





<!-- Counter Script -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const countdown = document.querySelector(".countdown");
    if (!countdown) return;

    const eventDateStr = countdown.dataset.eventDate;
    const eventDate = new Date(eventDateStr).getTime();

    function updateCountdown() {
        const now = new Date().getTime();
        const diff = eventDate - now;

        if (diff <= 0) {
            countdown.innerHTML = "<strong>Event Started!</strong>";
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        countdown.querySelector(".days").textContent = days;
        countdown.querySelector(".hours").textContent = hours;
        countdown.querySelector(".minutes").textContent = minutes;
        countdown.querySelector(".seconds").textContent = seconds;
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();
});
</script>
<!-- Partnerships Section -->
<section class="partnerships">
  <div class="container">
    <div class="text-center mb-5">
      <h2>🤝 Our Partners</h2>
      <p class="text-muted" style="font-size: 1.1rem; max-width: 700px; margin: 0 auto;">
        We are proud to collaborate with institutions and organizations that share our vision of 
        supporting local fishermen and preserving marine resources.
      </p>
    </div>
    
    <div class="row justify-content-center align-items-stretch g-4">
      <!-- Partner 1 -->
      <div class="col-6 col-md-3">
        <div class="partner-card text-center">
          <img src="../uploads/partners/olongapo.png" class="img-fluid mb-3" alt="Municipality of Subic">
          <p class="mt-2 text-dark small fw-semibold mb-0">Municipality of Olongapo City</p>
        </div>
      </div>
      <!-- Partner 2 -->
      <div class="col-6 col-md-3">
        <div class="partner-card text-center">
          <img src="../uploads/partners/bfar.png" class="img-fluid mb-3" alt="BFAR">
          <p class="mt-2 text-dark small fw-semibold mb-0">Bureau of Fisheries & Aquatic Resources</p>
        </div>
      </div>
      <!-- Partner 3 -->
      <div class="col-6 col-md-3">
        <div class="partner-card text-center">
          <img src="../uploads/partners/agriculture.png" class="img-fluid mb-3" alt="SBMA">
          <p class="mt-2 text-dark small fw-semibold mb-0">Olongapo City Agriculture Department</p>
        </div>
      </div>
      <!-- Partner 4 -->
      <div class="col-6 col-md-3">
        <div class="partner-card text-center">
          <img src="../uploads/partners/usaid.png" class="img-fluid mb-3" alt="Local Business Sponsor">
          <p class="mt-2 text-dark small fw-semibold mb-0">USAID</p>
        </div>
      </div>
    </div>
  </div>
</section>
</main>

<div class="bottom-space"></div>



<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Navbar shrink effect
  window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 40) {
      navbar.classList.add('shrink');
    } else {
      navbar.classList.remove('shrink');
    }
  });
</script>
        
<?php include("partials/footer.php"); ?>

<?php include 'chatbox.php'; ?>

</body>
</html>
