<?php
include('../../config/db_connect.php');

//
// Sa maraming kaso walang lumalabas dahil nagfa-fail ang SQL kapag
// walang column/table tulad ng `archived` o `archived_events`.
// Palitan ito ng mas robust logic: gumamit ng `is_archived` kung meron,
// fallback sa "show all" kung wala, at i-check kung merong archived_events table.
//
$hasIsArchived = false;
$hasArchivedEventsTable = false;

// safe check columns/tables
$colRes = $conn->query("SHOW COLUMNS FROM `events` LIKE 'is_archived'");
if ($colRes && $colRes->num_rows > 0) $hasIsArchived = true;

$tblRes = $conn->query("SHOW TABLES LIKE 'archived_events'");
if ($tblRes && $tblRes->num_rows > 0) $hasArchivedEventsTable = true;

// build conditions
$archivedCondition = $hasIsArchived ? "events.is_archived = 0" : "1";
$excludeArchivedTable = $hasArchivedEventsTable ? "events.id NOT IN (SELECT original_id FROM archived_events)" : "1";

// Fetch Events for Carousel (Upcoming Events Only - No Past Events)
$carousel_sql = "
  SELECT events.* FROM events
  WHERE {$archivedCondition}
    AND ({$excludeArchivedTable})
    AND `date` >= CURDATE()
  ORDER BY `date` ASC
";
$carousel_result = $conn->query($carousel_sql);
if ($carousel_result === false) error_log("Carousel SQL error: " . $conn->error);

// Fetch Upcoming Events (strictly future)
$upcoming_sql = "
  SELECT events.* FROM events
  WHERE {$archivedCondition}
    AND ({$excludeArchivedTable})
    AND `date` >= CURDATE()
  ORDER BY `date` ASC
";
$upcoming_result = $conn->query($upcoming_sql);
if ($upcoming_result === false) error_log("Upcoming SQL error: " . $conn->error);

// Fetch Past Events (events that already happened)
$past_sql = "
  SELECT events.* FROM events
  WHERE {$archivedCondition}
    AND ({$excludeArchivedTable})
    AND `date` < CURDATE()
  ORDER BY `date` DESC
";
$past_result = $conn->query($past_sql);
if ($past_result === false) error_log("Past SQL error: " . $conn->error);

// Get specific event for modal (if id is set)
$modal_event = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $event_id = (int)$_GET['id'];
    $modal_sql = "SELECT * FROM events WHERE id = $event_id LIMIT 1";
    $modal_result = $conn->query($modal_sql);
    if ($modal_result && $modal_result->num_rows > 0) {
        $modal_event = $modal_result->fetch_assoc();
    }
}

// Fetch Gallery
$gallery_sql = "SELECT * FROM galleries ORDER BY created_at DESC";
$gallery_result = $conn->query($gallery_sql);

// Fetch unique categories (guard if column missing)
$categories = [];
$catCheck = $conn->query("SHOW COLUMNS FROM `events` LIKE 'category'");
if ($catCheck && $catCheck->num_rows > 0) {
  $categories_sql = "SELECT DISTINCT `category` FROM events WHERE category IS NOT NULL AND category != ''";
  $categories_result = $conn->query($categories_sql);
  if ($categories_result) {
    while ($row = $categories_result->fetch_assoc()) $categories[] = $row['category'];
  }
}

// Kunin yung pinaka next event (yung pinaka malapit na hindi pa tapos)
$sql = "
  SELECT * FROM events
  WHERE {$archivedCondition}
    AND ({$excludeArchivedTable})
    AND `date` >= CURDATE()
  ORDER BY `date` ASC
  LIMIT 1
";
$result = $conn->query($sql);
if ($result === false) error_log("Next event SQL error: " . $conn->error);
$event = $result ? $result->fetch_assoc() : null;

$eventDate = $event ? $event['date'] : null; // format: YYYY-MM-DD
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Events | Bangkero & Fishermen Association</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg);
      color: #2c3e50;
    }
    
    /* Modern Carousel */
    .carousel-item {
      height: 70vh;
      min-height: 400px;
      background-size: cover;
      background-position: center;
      position: relative;
    }
    #eventsCarousel .carousel-item::before {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: linear-gradient(135deg, rgba(44, 62, 80, 0.80) 0%, rgba(26, 37, 47, 0.90) 100%);
      z-index: 1;
    }
    .carousel-caption {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      z-index: 2;
      text-shadow: 0 4px 12px rgba(0,0,0,0.4);
    }
    .carousel-caption h2 {
      font-size: 2.8rem;
      font-weight: 800;
      font-family: 'Poppins', sans-serif;
      color: #fff;
      margin-bottom: 15px;
      letter-spacing: -0.5px;
    }
    .carousel-caption p {
      font-size: 1rem;
      color: #f1f1f1;
      max-width: 700px;
      margin: auto;
      line-height: 1.6;
    }
    .carousel-caption .btn {
      margin-top: 20px;
      padding: 12px 30px;
      border: 2px solid #fff;
      border-radius: 50px;
      font-weight: 600;
      transition: all 0.3s ease;
      background: transparent;
    }
    .carousel-caption .btn:hover {
      background: #fff;
      color: var(--primary);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255,255,255,0.3);
    }

    /* Section Titles */
    .events-section h2,
    #events h3 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 800;
      font-size: 2.2rem;
      position: relative;
      display: inline-block;
      margin-bottom: 30px;
      letter-spacing: -0.5px;
    }
    .events-section h2::after,
    #events h3::after {
      content: "";
      display: block;
      width: 60px;
      height: 4px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      margin-top: 10px;
      border-radius: 2px;
    }

    /* Event Category Pills */
    .nav-pills .nav-link {
      color: var(--dark);
      background: white;
      border: 1px solid #e2e8f0;
      border-radius: 50px;
      padding: 10px 24px;
      margin-right: 10px;
      margin-bottom: 10px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .nav-pills .nav-link:hover {
      background: var(--light);
      border-color: var(--primary);
      color: var(--primary);
    }
    .nav-pills .nav-link.active {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      border-color: var(--primary);
      color: white;
      box-shadow: 0 4px 12px rgba(44, 62, 80, 0.25);
    }

    /* Event Cards */
    .event-box {
      border-radius: 18px;
      overflow: hidden;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border: none;
      background: #fff;
      box-shadow: 0 6px 24px rgba(44, 62, 80, 0.1);
      border: 1px solid #e2e8f0;
      position: relative;
    }
    .event-box::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
      opacity: 0;
      transition: opacity 0.4s ease;
    }
    .event-box:hover::before {
      opacity: 1;
    }
    .event-box:hover {
      transform: translateY(-8px);
      box-shadow: 0 16px 48px rgba(44, 62, 80, 0.15);
    }
    .event-img {
      width: 100%;
      height: 220px;
      object-fit: cover;
      transition: transform 0.6s ease;
    }
    .event-box:hover .event-img {
      transform: scale(1.08);
    }
    .event-title a {
      font-size: 1.15rem;
      font-weight: 700;
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      transition: color 0.3s;
      text-decoration: none;
    }
    .event-title a:hover {
      color: var(--primary);
    }
    .event-meta {
      font-size: 0.85rem;
      color: #64748b;
      font-weight: 500;
    }
    .event-meta i {
      color: var(--primary);
      margin-right: 5px;
    }
    .event-desc {
      font-size: 0.95rem;
      color: #64748b;
      line-height: 1.6;
    }

    /* Sidebar */
    .sidebar-box {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: #fff;
      padding: 30px;
      border-radius: 18px;
      box-shadow: 0 8px 24px rgba(44, 62, 80, 0.2);
      position: relative;
      overflow: hidden;
    }
    .sidebar-box::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 300px;
      height: 300px;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
      border-radius: 50%;
    }
    .sidebar-box h4 {
      font-family: 'Poppins', sans-serif;
      font-size: 1.5rem;
      margin-bottom: 15px;
      font-weight: 700;
      position: relative;
      z-index: 1;
    }
    .sidebar-box p {
      position: relative;
      z-index: 1;
      line-height: 1.7;
    }
    .sidebar-box .btn {
      position: relative;
      z-index: 1;
    }

    /* Gallery / Event Memories */
    .gallery-section {
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      padding: 40px 25px;
      border-radius: 18px;
      box-shadow: 0 6px 24px rgba(44, 62, 80, 0.08);
      border: 1px solid #e2e8f0;
    }
    .gallery-section h4 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 800;
      font-size: 1.5rem;
      margin-bottom: 30px;
      position: relative;
      display: inline-block;
      letter-spacing: -0.5px;
    }
    .gallery-section h4::after {
      content: "";
      display: block;
      width: 50px;
      height: 3px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      margin-top: 8px;
      border-radius: 2px;
    }
    .gallery-section img {
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border-radius: 16px;
      border: 2px solid #e2e8f0;
      cursor: pointer;
    }
    .gallery-section img:hover {
      transform: scale(1.05) translateY(-4px);
      box-shadow: 0 16px 40px rgba(44, 62, 80, 0.25);
      border-color: var(--primary);
    }
    
    /* Countdown Section */
    .countdown-section {
      background: linear-gradient(180deg, #ffffff 0%, var(--light) 100%);
      padding: 50px 0;
    }
    .countdown-section h2 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 800;
      font-size: 2rem;
      letter-spacing: -0.5px;
    }
    .countdown-box {
      background: white;
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 6px 20px rgba(44, 62, 80, 0.1);
      transition: all 0.3s ease;
      border: 1px solid #e2e8f0;
    }
    .countdown-box:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 30px rgba(44, 62, 80, 0.15);
    }
    .countdown-box h3 {
      color: var(--primary);
      font-size: 2.5rem;
      font-weight: 800;
      font-family: 'Poppins', sans-serif;
      margin-bottom: 5px;
    }
    .countdown-box span {
      color: #64748b;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.9rem;
      letter-spacing: 0.5px;
    }
    
    /* Stats Section */
    .stats-glance {
      background: linear-gradient(135deg, var(--light) 0%, #ffffff 100%);
      padding: 50px 0;
    }
    .stats-glance h2 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 800;
      font-size: 2.2rem;
      letter-spacing: -0.5px;
      margin-bottom: 40px;
    }
    .stat-box {
      background: white;
      border-radius: 18px;
      padding: 30px;
      box-shadow: 0 6px 24px rgba(44, 62, 80, 0.1);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid #e2e8f0;
      height: 100%;
    }
    .stat-box:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 40px rgba(44, 62, 80, 0.15);
    }
    .stat-box h3 {
      font-size: 2.5rem;
      font-weight: 800;
      font-family: 'Poppins', sans-serif;
      margin-bottom: 10px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .stat-box p {
      color: #64748b;
      font-weight: 600;
      margin-bottom: 0;
      text-transform: uppercase;
      font-size: 0.9rem;
      letter-spacing: 0.5px;
    }
    
    /* Map Section */
    .map-section {
      background: #fff;
      padding: 50px 0;
    }
    .map-section h2 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 800;
      font-size: 2.2rem;
      letter-spacing: -0.5px;
    }
    #map {
      border-radius: 18px;
      border: 1px solid #e2e8f0;
    }
    
    /* FAQ Section */
    .faq-section {
      background: linear-gradient(180deg, var(--light) 0%, #ffffff 100%);
      padding: 50px 0;
    }
    .faq-section h2 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 800;
      font-size: 2.2rem;
      letter-spacing: -0.5px;
    }
    .accordion-item {
      border: 1px solid #e2e8f0;
      margin-bottom: 12px;
      border-radius: 12px;
      overflow: hidden;
      background: white;
    }
    .accordion-button {
      font-weight: 600;
      color: var(--dark);
      background: white;
      padding: 18px 24px;
    }
    .accordion-button:not(.collapsed) {
      background: linear-gradient(135deg, var(--light), #f1f5f9);
      color: var(--primary);
      box-shadow: none;
    }
    .accordion-button:focus {
      box-shadow: none;
      border-color: var(--primary);
    }
    .accordion-body {
      color: #64748b;
      line-height: 1.7;
      padding: 20px 24px;
    }
  </style>
</head>
<body>
  <?php include("partials/navbar.php"); ?>

  <!-- Carousel -->
  <div id="eventsCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
    <!-- Indicators -->
    <div class="carousel-indicators">
      <?php
      $i = 0;
      if ($carousel_result && $carousel_result->num_rows > 0) {
        mysqli_data_seek($carousel_result, 0);
        while ($row = $carousel_result->fetch_assoc()) {
          echo '<button type="button" data-bs-target="#eventsCarousel" data-bs-slide-to="'.$i.'" '.($i==0 ? 'class="active" aria-current="true"' : '').' aria-label="Slide '.($i+1).'"></button>';
          $i++;
        }
        mysqli_data_seek($carousel_result, 0);
      }
      ?>
    </div>

    <div class="carousel-inner">
      <?php
      $active = true;
      if ($carousel_result && $carousel_result->num_rows > 0) {
        while ($row = $carousel_result->fetch_assoc()) {
          $poster = !empty($row['event_poster']) ? "../../uploads/".$row['event_poster'] : "../../uploads/default.jpg";
      ?>
        <div class="carousel-item <?= $active ? 'active' : '' ?>" style="background-image: url('<?= $poster ?>');">
          <div class="carousel-caption text-center">
            <h2 class="fw-bold"><?= htmlspecialchars($row['event_name']) ?></h2>
            <p class="mb-3">
              by admin | <?= date("F d, Y", strtotime($row['date'])) ?> | <?= htmlspecialchars($row['location']) ?>
            </p>
            <p class="carousel-desc mx-auto">
              <?= htmlspecialchars(substr(strip_tags($row['description']), 0, 200)) ?>...
            </p>
            <a href="#" class="btn btn-outline-light px-4 py-2 mt-2 event-detail-link" data-event-id="<?= $row['id'] ?>">Read More</a>
          </div>
        </div>
      <?php $active = false; }} ?>
    </div>

    <!-- Controls -->
    <button class="carousel-control-prev" type="button" data-bs-target="#eventsCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#eventsCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>

  <!-- Events Section -->
  <div class="container mt-5 events-section" id="events">
    <div class="row">

      <!-- Events List -->
      <div class="col-md-8">

        <!-- Event Categories -->
        <h3>Event Categories</h3>
        <ul class="nav nav-pills mb-4" id="eventCategories">
          <li class="nav-item">
            <button class="nav-link active" data-category="all">All</button>
          </li>
          <?php foreach ($categories as $cat): ?>
            <li class="nav-item">
              <button class="nav-link" data-category="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></button>
            </li>
          <?php endforeach; ?>
        </ul>

        <!-- Upcoming / Past Events Tabs -->
        <ul class="nav nav-pills mb-4" id="eventTimeTabs">
          <li class="nav-item">
            <button class="nav-link active" data-time="upcoming">Upcoming Events</button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-time="past">Past Events</button>
          </li>
        </ul>

        <!-- Upcoming Events -->
        <div id="upcomingEventsContainer">
        <h3>Upcoming Events</h3>
        <div class="row">
          <?php if ($upcoming_result && $upcoming_result->num_rows > 0): ?>
            <?php while ($row = $upcoming_result->fetch_assoc()): ?>
              <div class="col-md-6 mb-4">
                <div class="event-box h-100" data-category="<?= htmlspecialchars($row['category'] ?? 'General') ?>">
                  <img src="../../uploads/<?php echo !empty($row['event_poster']) ? htmlspecialchars($row['event_poster']) : 'default.jpg'; ?>" 
                       alt="Event Poster" class="event-img">
                  <div class="event-content p-3">
                    <h5 class="event-title">
                      <a href="#" class="text-decoration-none event-detail-link" data-event-id="<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['event_name']); ?>
                      </a>
                    </h5>
                    <p class="event-meta mb-2">
                      <i class="bi bi-person-circle"></i> by admin | 
                      <i class="bi bi-calendar3"></i> <?php echo date("M d, Y", strtotime($row['date'])); ?> | 
                      <i class="bi bi-geo-alt-fill"></i> <?php echo htmlspecialchars($row['location']); ?>
                    </p>
                    <p class="event-desc">
                      <?php echo substr(strip_tags($row['description']), 0, 180); ?>...
                    </p>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-muted">No upcoming events at the moment.</p>
          <?php endif; ?>
        </div>
        </div>

        <!-- Past Events Section -->
        <div id="pastEventsContainer" style="display:none;">
        <h3>Past Events</h3>
        <div class="row">
          <?php if ($past_result && $past_result->num_rows > 0): ?>
            <?php while ($row = $past_result->fetch_assoc()): ?>
              <div class="col-md-6 mb-4">
                <div class="event-box h-100" data-category="<?= htmlspecialchars($row['category'] ?? 'General') ?>">
                  <img src="../../uploads/<?php echo !empty($row['event_poster']) ? htmlspecialchars($row['event_poster']) : 'default.jpg'; ?>" 
                       alt="Event Poster" class="event-img">
                  <div class="event-content p-3">
                    <h5 class="event-title">
                      <a href="#" class="text-decoration-none event-detail-link" data-event-id="<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['event_name']); ?>
                      </a>
                    </h5>
                    <p class="event-meta mb-2">
                      <i class="bi bi-person-circle"></i> by admin | 
                      <i class="bi bi-calendar3"></i> <?php echo date("M d, Y", strtotime($row['date'])); ?> | 
                      <i class="bi bi-geo-alt-fill"></i> <?php echo htmlspecialchars($row['location']); ?>
                    </p>
                    <p class="event-desc">
                      <?php echo substr(strip_tags($row['description']), 0, 180); ?>...
                    </p>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-muted">No past events to display.</p>
          <?php endif; ?>
        </div>
        </div>
      </div>
    
      <!-- Sidebar -->
      <div class="col-md-4">
        <div class="sidebar-box mb-5">
          <h4>Volunteer & Get Involved</h4>
          <p>
            Want to make a difference? Join our coastal cleanups, livelihood training, 
            and support programs for fishermen families.
          </p>
          <a href="contact.php" class="btn btn-light btn-sm px-4 rounded-pill fw-semibold">Sign Up</a>
        </div>

        <!-- Gallery -->
        <section class="gallery-section mt-5">
          <div class="text-center">
            <h4>Event Memories</h4>
          </div>

          <?php
          // filesystem dir and public url for gallery images (from this file location)
          $gallery_fs_dir = realpath(__DIR__ . '/../../uploads/gallery') ? realpath(__DIR__ . '/../../uploads/gallery') . '/' : __DIR__ . '/../../uploads/gallery/';
          $gallery_url_base = '../../uploads/gallery/';

          if ($gallery_result && $gallery_result->num_rows > 0):
            $modalIndex = 0;
            // rewind in case result was used earlier
            mysqli_data_seek($gallery_result, 0);
            while($row = $gallery_result->fetch_assoc()):
              $images = array_filter(array_map('trim', explode(",", $row['images'])));
              // filter out images that no longer exist on disk
              $images = array_values(array_filter($images, function($img) use ($gallery_fs_dir) {
                return $img !== '' && file_exists($gallery_fs_dir . $img);
              }));
              if (empty($images)) continue;
              $modalIndex++;
              $firstImage = $images[0];
              $imgPath = $gallery_url_base . $firstImage;
          ?>
              <div class="text-center mb-4">
                <img src="<?= htmlspecialchars($imgPath) ?>" class="img-fluid" 
                     style="max-width:100%; height: 220px; object-fit: cover;" 
                     data-bs-toggle="modal" data-bs-target="#modal<?= $modalIndex ?>" alt="Gallery Preview">
              </div>

              <!-- Modal -->
              <div class="modal fade" id="modal<?= $modalIndex ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                  <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden; box-shadow: 0 12px 48px rgba(44, 62, 80, 0.3);">
                    <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border: none; padding: 20px 30px;">
                      <h5 class="modal-title fw-bold" style="font-family: 'Poppins', sans-serif;">Event Memories Gallery</h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0" style="background: #f8f9fa; overflow: hidden;">
                      <div id="carouselGallery<?= $modalIndex ?>" class="carousel slide" data-bs-ride="false" style="line-height: 0;">
                        <div class="carousel-inner" style="line-height: 0;">
                          <?php foreach($images as $i => $img):
                            $imgUrl = $gallery_url_base . $img;
                          ?>
                            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>" style="line-height: 0;">
                              <img src="<?= htmlspecialchars($imgUrl) ?>" class="d-block w-100" alt="Gallery Image" style="max-height:85vh; width: 100%; height: auto; object-fit: contain; display: block;">
                            </div>
                          <?php endforeach; ?>
                        </div>
                        
                        <!-- Carousel Controls -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselGallery<?= $modalIndex ?>" data-bs-slide="prev">
                          <div style="background: rgba(44, 62, 80, 0.8); border-radius: 50%; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; margin: auto;">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                          </div>
                          <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselGallery<?= $modalIndex ?>" data-bs-slide="next">
                          <div style="background: rgba(44, 62, 80, 0.8); border-radius: 50%; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; margin: auto;">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                          </div>
                          <span class="visually-hidden">Next</span>
                        </button>
                        
                        <!-- Indicators -->
                        <div class="carousel-indicators">
                          <?php foreach($images as $i => $img): ?>
                            <button type="button" data-bs-target="#carouselGallery<?= $modalIndex ?>" data-bs-slide-to="<?= $i ?>" <?= $i === 0 ? 'class="active" aria-current="true"' : '' ?> aria-label="Slide <?= $i+1 ?>" style="width: 12px; height: 12px; border-radius: 50%; background: var(--primary);"></button>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
          <?php endwhile; else: ?>
            <p class="text-center text-muted">No memories uploaded yet.</p>
          <?php endif; ?>
        </section>
      </div>
    </div>
  </div>

  <!-- Event Details Modal -->
  <div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content" style="border-radius: 18px; border: none; box-shadow: 0 12px 48px rgba(44, 62, 80, 0.2);">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border-radius: 18px 18px 0 0;">
          <h5 class="modal-title fw-bold" id="eventDetailsModalLabel" style="font-family: 'Poppins', sans-serif;">Event Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4" id="eventDetailsBody">
          <div class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // Category Filter
      const categoryButtons = document.querySelectorAll("#eventCategories .nav-link");
      const eventCards = document.querySelectorAll(".event-box");

      categoryButtons.forEach(btn => {
        btn.addEventListener("click", () => {
          categoryButtons.forEach(b => b.classList.remove("active"));
          btn.classList.add("active");

          const category = btn.getAttribute("data-category");

          eventCards.forEach(card => {
            const cardCat = card.getAttribute("data-category");
            if (category === "all" || cardCat === category) {
              card.parentElement.style.display = "block";
            } else {
              card.parentElement.style.display = "none";
            }
          });
        });
      });

      // Upcoming / Past Events Tab
      const timeButtons = document.querySelectorAll("#eventTimeTabs .nav-link");
      const upcomingContainer = document.getElementById("upcomingEventsContainer");
      const pastContainer = document.getElementById("pastEventsContainer");

      timeButtons.forEach(btn => {
        btn.addEventListener("click", () => {
          timeButtons.forEach(b => b.classList.remove("active"));
          btn.classList.add("active");

          const timeFilter = btn.getAttribute("data-time");

          if (timeFilter === "upcoming") {
            upcomingContainer.style.display = "block";
            pastContainer.style.display = "none";
          } else if (timeFilter === "past") {
            upcomingContainer.style.display = "none";
            pastContainer.style.display = "block";
          }
        });
      });

      // Event Details Modal
      const eventModal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
      const eventDetailLinks = document.querySelectorAll('.event-detail-link');
      
      eventDetailLinks.forEach(link => {
        link.addEventListener('click', function(e) {
          e.preventDefault();
          const eventId = this.getAttribute('data-event-id');
          
          // Show modal with loading
          document.getElementById('eventDetailsBody').innerHTML = `
            <div class="text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
          `;
          eventModal.show();
          
          // Fetch event details
          fetch(`get_event_details.php?id=${eventId}`)
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                const event = data.event;
                const posterUrl = event.event_poster ? `../../uploads/${event.event_poster}` : '../../uploads/default.jpg';
                
                document.getElementById('eventDetailsBody').innerHTML = `
                  <div class="event-details-content">
                    <img src="${posterUrl}" alt="Event Poster" class="img-fluid rounded mb-4" style="width: 100%; height: 400px; object-fit: cover; border-radius: 16px;">
                    
                    <h2 class="fw-bold mb-3" style="font-family: 'Poppins', sans-serif; color: var(--dark);">${event.event_name}</h2>
                    
                    <div class="event-meta mb-4" style="color: #64748b; font-weight: 500;">
                      <span><i class="bi bi-person-circle" style="color: var(--primary);"></i> by Admin</span> | 
                      <span><i class="bi bi-calendar3" style="color: var(--primary);"></i> ${event.date_formatted}</span> | 
                      <span><i class="bi bi-tag" style="color: var(--primary);"></i> ${event.category}</span>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); padding: 25px; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
                      <h5 class="fw-bold mb-3" style="color: var(--primary); font-family: 'Poppins', sans-serif;">About this Event</h5>
                      <p style="line-height: 1.8; color: #64748b;">${event.description || 'No description provided.'}</p>
                    </div>
                    
                    <div style="background: white; padding: 25px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(44, 62, 80, 0.08);">
                      <h5 class="fw-bold mb-3" style="color: var(--primary); font-family: 'Poppins', sans-serif;">Event Information</h5>
                      <p class="mb-2"><strong>Date:</strong> ${event.date_long}</p>
                      ${event.time ? `<p class="mb-2"><strong>Time:</strong> ${event.time}</p>` : ''}
                      <p class="mb-2"><strong>Location:</strong> ${event.location}</p>
                      <p class="mb-0"><strong>Category:</strong> ${event.category}</p>
                    </div>
                  </div>
                `;
              } else {
                document.getElementById('eventDetailsBody').innerHTML = `
                  <div class="alert alert-danger">Event not found.</div>
                `;
              }
            })
            .catch(error => {
              document.getElementById('eventDetailsBody').innerHTML = `
                <div class="alert alert-danger">Error loading event details.</div>
              `;
            });
        });
      });

      // Check if modal should open on page load
      const urlParams = new URLSearchParams(window.location.search);
      const eventId = urlParams.get('id');
      if (eventId) {
        const link = document.querySelector(`[data-event-id="${eventId}"]`);
        if (link) {
          link.click();
        }
      }
    });
  </script>
  
<!-- Countdown Widget Section -->
<section class="countdown-section py-5" style="background: #f8f9fa;">
  <div class="container text-center">
    <?php if ($eventDate): ?>
    <h2 class="mb-4" style="font-family: 'Lora', serif; color: #003366; font-weight: 700;">
  Countdown to: <?php echo htmlspecialchars($event['event_name']); ?>
</h2>

      <p class="mb-5 text-muted">
        <?php echo date("F j, Y", strtotime($eventDate)); ?>
      </p>

      <div class="row justify-content-center">
        <div class="col-md-2 col-6 mb-3">
          <div class="countdown-box shadow rounded p-3 bg-white">
            <h3 id="days" class="fw-bold mb-0" style="color:#003366; font-size: 2rem;">00</h3>
            <span class="text-muted">Days</span>
          </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
          <div class="countdown-box shadow rounded p-3 bg-white">
            <h3 id="hours" class="fw-bold mb-0" style="color:#003366; font-size: 2rem;">00</h3>
            <span class="text-muted">Hours</span>
          </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
          <div class="countdown-box shadow rounded p-3 bg-white">
            <h3 id="minutes" class="fw-bold mb-0" style="color:#003366; font-size: 2rem;">00</h3>
            <span class="text-muted">Minutes</span>
          </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
          <div class="countdown-box shadow rounded p-3 bg-white">
            <h3 id="seconds" class="fw-bold mb-0" style="color:#003366; font-size: 2rem;">00</h3>
            <span class="text-muted">Seconds</span>
          </div>
        </div>
      </div>
    <?php else: ?>
      <h2 class="text-muted">No upcoming events scheduled.</h2>
    <?php endif; ?>
  </div>
</section>

<?php if ($eventDate): ?>
<script>
// Gamitin yung event date galing sa database
const targetDate = new Date("<?php echo $eventDate; ?> 00:00:00").getTime();

const countdownFunction = setInterval(function() {
  const now = new Date().getTime();
  const distance = targetDate - now;

  const days = Math.floor(distance / (1000 * 60 * 60 * 24));
  const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((distance % (1000 * 60)) / 1000);

  document.getElementById("days").innerHTML = days;
  document.getElementById("hours").innerHTML = hours;
  document.getElementById("minutes").innerHTML = minutes;
  document.getElementById("seconds").innerHTML = seconds;

  if (distance < 0) {
    clearInterval(countdownFunction);
    document.querySelector(".countdown-section .container").innerHTML =
      "<h2 class='text-success fw-bold'>🎉 The event has started!</h2>";
  }
}, 1000);
</script>
<?php endif; ?>

  <!-- Stats Section -->
<section class="stats-glance text-center">
  <div class="container">
    <h2 class="mb-4">Association at a Glance</h2>
    <div class="row g-4">
      
      <!-- Members -->
      <div class="col-md-3 col-6">
        <div class="stat-box">
          <h3 data-count="250">0</h3>
          <p>Active Members</p>
        </div>
      </div>
      
      <!-- Events -->
      <div class="col-md-3 col-6">
        <div class="stat-box">
          <h3 data-count="45">0</h3>
          <p>Events Organized</p>
        </div>
      </div>
      
      <!-- Projects -->
      <div class="col-md-3 col-6">
        <div class="stat-box">
          <h3 data-count="12">0</h3>
          <p>Community Projects</p>
        </div>
      </div>
      
      <!-- Years -->
      <div class="col-md-3 col-6">
        <div class="stat-box">
          <h3 data-count="8">0</h3>
          <p>Years of Service</p>
        </div>
      </div>
      
    </div>
  </div>
</section>

<!-- Counter Animation -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  const counters = document.querySelectorAll("[data-count]");
  counters.forEach(counter => {
    let target = +counter.getAttribute("data-count");
    let count = 0;
    let step = Math.ceil(target / 100); 
    let interval = setInterval(() => {
      count += step;
      if (count >= target) {
        counter.textContent = target;
        clearInterval(interval);
      } else {
        counter.textContent = count;
      }
    }, 30);
  });
});
</script>
<!-- Map Section -->
<section class="map-section">
  <div class="container text-center">
    <h2 class="mb-3">Visit Driftwood Beach</h2>
    <p class="mb-4 text-muted">Here's our location — zoom in/out and explore the map.</p>

    <!-- Map container -->
    <div id="map" style="height: 450px; width: 100%;" class="shadow"></div>
  </div>
</section>

<!-- Leaflet.js CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
  // Coordinates of Driftwood Beach (from your iframe link)
  const driftwoodCoords = [14.848540270959603, 120.26304617445952];

  // Initialize map
  const map = L.map('map').setView(driftwoodCoords, 15);

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  // Add marker with popup
  L.marker(driftwoodCoords).addTo(map)
    .bindPopup("<b>Driftwood Beach</b><br>Subic, Zambales")
    .openPopup();
</script>
<!-- FAQ Section -->
<section class="faq-section">
  <div class="container">
    <h2 class="text-center mb-3">Frequently Asked Questions</h2>
    <p class="text-center mb-5 text-muted">
      Here are some common questions about our association and activities.
    </p>

    <div class="accordion" id="faqAccordion">
      <!-- Question 1 -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading1">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="true" aria-controls="faqCollapse1">
            What is the Bangkero & Fishermen Association?
          </button>
        </h2>
        <div id="faqCollapse1" class="accordion-collapse collapse show" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            The Bangkero & Fishermen Association is a community organization dedicated to supporting fishermen, boatmen, and their families by organizing livelihood projects, training, and community events.
          </div>
        </div>
      </div>

      <!-- Question 2 -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading2">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
            How can I join the association?
          </button>
        </h2>
        <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            To join, simply visit our office or contact us through the website. Membership is open to all fishermen and boat operators in our community.
          </div>
        </div>
      </div>

      <!-- Question 3 -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading3">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
            Do you offer training or livelihood programs?
          </button>
        </h2>
        <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Yes! We regularly conduct training on sustainable fishing, eco-tourism, and alternative livelihood programs to help improve members' income and skills.
          </div>
        </div>
      </div>

      <!-- Question 4 -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading4">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
            How can I volunteer in your activities?
          </button>
        </h2>
        <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Volunteers are always welcome! You can sign up on our volunteer page or join during events to support our environmental cleanups, community drives, and livelihood fairs.
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include("partials/footer.php"); ?>
<?php include 'chatbox.php'; ?>

</body>
</html>
