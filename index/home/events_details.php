<?php
include('../../config/db_connect.php');

// Get event ID safely
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $event_id = (int)$_GET['id'];
} else {
    die("Invalid event ID.");
}

// Fetch event from DB
$sql = "SELECT * FROM events WHERE id = $event_id LIMIT 1";
$result = $conn->query($sql);
if (!$result || $result->num_rows == 0) {
    die("Event not found.");
}
$event = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($event['event_name']); ?> | Event Details</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #fff;
      color: #333;
      line-height: 1.7;
    }

    /* Navbar styling */
    .navbar {
      background: #fff;
      border-bottom: 1px solid #ddd;
      position: fixed; /* Kung fixed ang navbar mo */
      top: 0;
      width: 100%;
      z-index: 1000;
    }
    .navbar .nav-link, .navbar-brand {
      color: #333 !important;
      font-weight: 500;
    }

    /* Article Layout */
    .article-container {
      max-width: 900px;
      margin: 0 auto;
      padding: 120px 15px 40px; /* 120px padding-top para hindi matakpan ng navbar */
    }
    .article-title {
      font-size: 2.2rem;
      font-weight: 700;
      color: #111;
      margin-bottom: 10px;
    }
    .article-meta {
      font-size: 0.9rem;
      color: #666;
      margin-bottom: 20px;
    }
    .article-meta span {
      margin-right: 15px;
    }
    .featured-image {
      width: 100%;
      height: auto;
      border-radius: 8px;
      margin-bottom: 30px;
    }
    .article-content h2 {
      font-size: 1.5rem;
      font-weight: 600;
      margin-top: 25px;
      margin-bottom: 15px;
      color: #003366;
    }
    .article-content p {
      margin-bottom: 20px;
      text-align: justify;
    }
    .detail-box {
      background: #f8f9fa;
      border: 1px solid #ddd;
      padding: 20px;
      border-radius: 10px;
      margin-top: 40px;
    }
    .detail-box strong {
      color: #003366;
    }
    .btn-primary {
      background: #003366;
      border: none;
      border-radius: 5px;
      padding: 10px 20px;
      font-weight: 600;
    }
    .btn-primary:hover {
      background: #00509e;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <?php include("partials/navbar.php"); ?>

  <!-- Article Container -->
  <div class="article-container">
    <!-- Title -->
    <h1 class="article-title"><?php echo htmlspecialchars($event['event_name']); ?></h1>
    <!-- Meta -->
    <div class="article-meta">
      <span>by Admin</span>
      <span><?php echo date("F d, Y", strtotime($event['date'])); ?></span>
      <span>Category: <?php echo htmlspecialchars($event['category'] ?? 'General'); ?></span>
      <span>0 comments</span>
    </div>

    <!-- Featured Image -->
    <img src="../../uploads/<?php echo !empty($event['event_poster']) ? htmlspecialchars($event['event_poster']) : 'default.jpg'; ?>" 
         alt="Event Poster" class="featured-image">

    <!-- Article Content -->
    <div class="article-content">
      <h2>About this Event</h2>
      <p><?php echo !empty($event['description']) ? nl2br(htmlspecialchars($event['description'])) : "No description provided."; ?></p>
    </div>

    <!-- Event Details -->
    <div class="detail-box">
      <p><strong>Date:</strong> <?php echo date("l, F d, Y", strtotime($event['date'])); ?></p>
      <?php if (!empty($event['time'])): ?>
        <p><strong>Time:</strong> <?php echo htmlspecialchars($event['time']); ?></p>
      <?php endif; ?>
      <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
      <p><strong>Category:</strong> <?php echo htmlspecialchars($event['category'] ?? 'General'); ?></p>
    </div>

    <!-- Action Buttons -->
    <div class="mt-4">
      <a href="events.php" class="btn btn-primary me-2">← Back to Events</a>
      <a href="#" class="btn btn-success">✔ Register / Join Event</a>
    </div>
  </div>
<?php include 'chatbox.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
