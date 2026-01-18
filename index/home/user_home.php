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
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #1976d2;
      --secondary: #ff7043;
      --accent: #ffa726;
      --success: #28a745;
      --info: #42a5f5;
      --bg: #f8f9fa;
      --dark: #003366;
      --gray: #f9f9f9;
    }
    body {
      font-family: 'Open Sans', 'Segoe UI', Arial, sans-serif;
      background-color: var(--bg);
      color: #222;
    }
    /* Navbar */
    .navbar {
      background: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      padding: 1rem 0;
      transition: all 0.3s;
    }
    .navbar.shrink {
      padding: 0.5rem 0;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .navbar-brand img {
      height: 48px;
      transition: height 0.3s;
    }
    .navbar.shrink .navbar-brand img {
      height: 36px;
    }
    .navbar-nav .nav-link {
      font-weight: 500;
      color: var(--primary) !important;
      margin: 0 8px;
      padding: 8px 12px;
      border-bottom: 2px solid transparent;
      transition: color 0.2s, border-bottom 0.2s;
    }
    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link.active {
      color: var(--secondary) !important;
      border-bottom: 2px solid var(--secondary);
      background: transparent;
    }
    /* Hero Carousel */
    .carousel-item {
      height: 70vh;
      min-height: 340px;
      background-size: cover;
      background-position: center;
      position: relative;
    }
    .carousel-item::before {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.38);
      z-index: 1;
    }
    .carousel-caption {
      position: absolute;
      top: 50%;
      left: 0; right: 0;
      transform: translateY(-50%);
      z-index: 2;
      text-shadow: 1px 1px 6px rgba(0,0,0,0.5);
    }
    .carousel-caption h1 {
      font-size: 2.5rem;
      font-weight: 700;
      color: #fff;
      font-family: 'Lora', serif;
      letter-spacing: 1px;
    }
    .carousel-caption p {
      font-size: 1.15rem;
      margin-top: 10px;
      color: #f3f3f3;
    }
    /* Intro Section */
    .intro-section {
      background: #fff;
      padding: 56px 0 40px 0;
      border-bottom: 1px solid #eaeaea;
    }
    .intro-section h2 {
      font-family: 'Lora', serif;
      color: var(--dark);
      font-weight: 700;
      margin-bottom: 1.2rem;
      font-size: 2.1rem;
    }
    .intro-section p {
      color: #444;
      font-size: 1.08rem;
      line-height: 1.7;
      max-width: 800px;
      margin: 0 auto;
    }
   
    /* Event Highlight */
    .event-highlight {
      background: #fff;
      padding: 60px 20px;
      text-align: center;
      border-top: 1px solid #eee;
    }
    .event-highlight h2 {
      font-family: 'Lora', serif;
      color: var(--dark);
      margin-bottom: 20px;
      font-weight: 700;
    }
    .countdown {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 20px;
      flex-wrap: wrap;
    }
    .countdown div {
      background: var(--primary);
      color: #fff;
      padding: 20px;
      border-radius: 10px;
      min-width: 90px;
    }
    .countdown div span {
      display: block;
      font-size: 1.8rem;
      font-weight: bold;
    }
          /* Announcement Card Styling */
      .announcement-card {
        background-color: #ffffff;          
        border: 1px solid #e0e0e0;         
        border-radius: 12px;               
        box-shadow: 0 2px 6px rgba(0,0,0,0.05); /* Subtle shadow */
        transition: all 0.3s ease;
      }

      .announcement-card:hover {
        background-color: #f9f9f9;        
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      }

      .announcements-section h2 {
        font-family: 'Lora', serif;
        color: var(--dark);
        font-weight: 700;
        margin-bottom: 20px;
        font-size: 2.1rem;
      }

   /* Footer spacing */
    .bottom-space{height:32px}
    @media (max-width:575px){
      .hero{padding:40px 12px;border-radius:0 0 14px 14px}
      .icon-wrap{width:48px;height:48px;font-size:1.15rem;border-radius:8px}
      .btn-download{padding:8px 12px}
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
<section class="event-highlight container my-5">
    <h2 class="mb-4">Upcoming Major Event</h2>
    <?php if ($nextEvent): ?>
    <div class="card shadow-lg border-0 rounded-4">
        <div class="row g-0 align-items-center">
            <!-- Poster -->
            <div class="col-md-5">
                <img src="../../uploads/<?php echo htmlspecialchars($nextEvent['event_poster'] ?: 'default.jpg'); ?>" 
                     class="img-fluid rounded-start" alt="Event Poster">
            </div>
            <!-- Details -->
            <div class="col-md-7 p-4">
                <h3 class="fw-bold text-primary"><?php echo htmlspecialchars($nextEvent['event_name']); ?></h3>
                <p class="text-muted">
    <?php 
        $desc = htmlspecialchars($nextEvent['description']);
        $maxLength = 150; // max number of characters
        if (strlen($desc) > $maxLength) {
            $desc = substr($desc, 0, $maxLength) . '...';
        }
        echo $desc;
    ?>
</p>

                <p>
                    <i class="bi bi-calendar"></i> <?php echo $nextEvent['date']; ?><br>
                    <i class="bi bi-clock"></i> <?php echo $nextEvent['time']; ?><br>
                    <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($nextEvent['location']); ?>
                </p>
                <div class="countdown d-flex gap-3 mt-3" 
                     data-event-date="<?php echo $nextEvent['date'] . ' ' . $nextEvent['time']; ?>">
                    <div><span class="days">0</span><small class="d-block">Days</small></div>
                    <div><span class="hours">0</span><small class="d-block">Hours</small></div>
                    <div><span class="minutes">0</span><small class="d-block">Minutes</small></div>
                    <div><span class="seconds">0</span><small class="d-block">Seconds</small></div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <p class="text-muted">No upcoming events yet. Stay tuned!</p>
    <?php endif; ?>
</section>
<section class="container my-5">
   <section class="container my-5 announcements-section">
    <!-- Title -->
     <section class="event-highlight container my-5">
    <h2 class="mb-4 text-center">Latest Announcements</h2>
    
    <div class="row justify-content-center">
        <?php while ($row = $latestAnnouncements->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
    <div class="announcement-card h-100 d-flex flex-column p-3">
        <!-- Announcement Title -->
        <h5 class="fw-bold text-dark mb-2" style="font-size: 1.1rem;">
            <?= htmlspecialchars($row['title']) ?>
        </h5>
        
        <!-- Date -->
        <p class="text-muted small mb-3">
            <?= date("F j, Y", strtotime($row['date_posted'])) ?>
        </p>
        
        <!-- Short Content -->
        <p class="flex-grow-1 text-secondary" style="min-height: 70px; max-height: 90px; overflow: hidden;">
            <?= nl2br(htmlspecialchars(substr($row['content'], 0, 120))) ?>...
        </p>
        
        <!-- Read More Button -->
        <div class="mt-auto">
            <a href="announcement.php" 
               class="btn btn-primary btn-sm rounded-3 px-3">
                Read More
            </a>
        </div>
    </div>
</div>

        <?php endwhile; ?>
    </div>

    <!-- View All Button -->
    <div class="text-center mt-4">
        <a href="announcement.php" class="btn btn-outline-dark fw-semibold px-4 py-2 rounded-3">
            View All Announcements →
        </a>
    </div>
</section>

<style>
    .hover-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }
    
</style>



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
<section class="partnerships py-5 bg-light">
  <div class="container text-center">
    <h2 class="mb-4" style="font-family: 'Lora', serif; color: var(--dark); font-weight: 700;">
      Our Partners
    </h2>
    <p class="text-muted mb-5">
      We are proud to collaborate with institutions and organizations that share our vision of 
      supporting local fishermen and preserving marine resources.
    </p>
    
    <div class="row justify-content-center align-items-center g-4">
      <!-- Partner 1 -->
      <div class="col-6 col-md-3">
        <img src="../uploads/partners/olongapo.png" class="img-fluid grayscale-hover" alt="Municipality of Subic">
        <p class="mt-2 text-dark small">Municipality of Olongapo City </p>
      </div>
      <!-- Partner 2 -->
      <div class="col-6 col-md-3">
        <img src="../uploads/partners/bfar.png" class="img-fluid grayscale-hover" alt="BFAR">
        <p class="mt-2 text-dark small">Bureau of Fisheries & Aquatic Resources (BFAR)</p>
      </div>
      <!-- Partner 3 -->
      <div class="col-6 col-md-3">
        <img src="../uploads/partners/agriculture.png" class="img-fluid grayscale-hover" alt="SBMA">
        <p class="mt-2 text-dark small">Olongapo City Agriculture Department</p>
      </div>
      <!-- Partner 4 -->
      <div class="col-6 col-md-3">
        <img src="../uploads/partners/usaid.png" class="img-fluid grayscale-hover" alt="Local Business Sponsor">
        <p class="mt-2 text-dark small">USAID</p>
      </div>
    </div>
  </div>
</section>
</main>
<style>
  .partnerships img {
    max-height: 80px;
    transition: transform 0.3s ease, filter 0.3s ease;
    filter: grayscale(100%);
    opacity: 0.85;
  }
  .partnerships img:hover {
    transform: scale(1.08);
    filter: grayscale(0%);
    opacity: 1;
  }
</style>
<div class="mb-5"></div>



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
</body>
</html>
