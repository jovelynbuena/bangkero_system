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

// Fetch Events for Carousel (Upcoming + Past 1 Month)
$carousel_sql = "
  SELECT events.* FROM events
  WHERE {$archivedCondition}
    AND ({$excludeArchivedTable})
    AND `date` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
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
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f9fafb;
      color: #333;
    }
    /* Carousel */
    .carousel-item {
      height: 75vh;
      min-height: 380px;
      background-size: cover;
      background-position: center;
      position: relative;
    }
    #eventsCarousel .carousel-item::before {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 76, 109, 0.55);
      backdrop-filter: blur(2px);
      z-index: 1;
    }
    .carousel-caption {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      z-index: 2;
    }
    .carousel-caption h2 {
      font-size: 2.8rem;
      font-weight: 700;
      color: #fff;
      text-shadow: 0px 3px 12px rgba(0,0,0,0.7);
    }
    .carousel-caption p {
      font-size: 1rem;
      color: #f1f1f1;
      max-width: 800px;
      margin: auto;
    }
    .carousel-caption .btn {
      margin-top: 15px;
      padding: 10px 25px;
      border: 2px solid #fff;
      border-radius: 30px;
      font-weight: 500;
      transition: 0.3s;
    }
    .carousel-caption .btn:hover {
      background: #fff;
      color: #004c6d;
    }

    /* Section Titles */
    .events-section h2,
    #events h3 {
      font-family: 'Lora', serif;
      color: #003366;
      font-weight: 700;
      font-size: 2rem;
      position: relative;
      display: inline-block;
      margin-bottom: 25px;
    }
    .events-section h2::after,
    #events h3::after {
      content: "";
      display: block;
      width: 50px;
      height: 3px;
      background: #0f8fa9;
      margin-top: 8px;
      border-radius: 2px;
    }

    /* Event Cards */
    .event-box {
      border-radius: 12px;
      overflow: hidden;
      transition: all 0.3s ease;
      border: none;
      background: #fff;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .event-box:hover {
      transform: translateY(-6px);
      box-shadow: 0 8px 22px rgba(0,0,0,0.12);
    }
    .event-img {
      width: 100%;
      height: 230px;
      object-fit: cover;
      transition: 0.4s ease;
    }
    .event-box:hover .event-img {
      transform: scale(1.05);
    }
    .event-title a {
      font-size: 1.15rem;
      font-weight: 600;
      color: #003366;
      transition: 0.3s;
    }
    .event-title a:hover {
      color: #0f8fa9;
    }
    .event-meta {
      font-size: 0.85rem;
      color: #666;
    }
    .event-desc {
      font-size: 0.9rem;
      color: #555;
    }

    /* Sidebar */
    .sidebar-box {
      background: linear-gradient(135deg, #0f8fa9, #006f87);
      color: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    }
    .sidebar-box h4 {
      font-family: 'Lora', serif;
      font-size: 1.5rem;
      margin-bottom: 15px;
    }

    /* Gallery */
    .gallery-section img {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .gallery-section img:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
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
            <a href="events_details.php?id=<?= $row['id'] ?>" class="btn btn-outline-light px-4 py-2 mt-2">Read More</a>
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

        <!-- Upcoming Events -->
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
                      <a href="event-details.php?id=<?php echo $row['id']; ?>" class="text-decoration-none">
                        <?php echo htmlspecialchars($row['event_name']); ?>
                      </a>
                    </h5>
                    <p class="event-meta mb-2">
                      by admin | <?php echo date("M d, Y", strtotime($row['date'])); ?> | <?php echo htmlspecialchars($row['location']); ?>
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
          <h4 class="text-center mb-4">Event Memories</h4>

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
                <img src="<?= htmlspecialchars($imgPath) ?>" class="img-fluid rounded shadow-sm" 
                     style="max-width:300px; cursor:pointer;" 
                     data-bs-toggle="modal" data-bs-target="#modal<?= $modalIndex ?>" alt="Gallery Preview">
              </div>

              <!-- Modal -->
              <div class="modal fade" id="modal<?= $modalIndex ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                  <div class="modal-content bg-dark">
                    <div class="modal-body p-0">
                      <div id="carouselGallery<?= $modalIndex ?>" class="carousel slide" data-bs-ride="false">
                        <div class="carousel-inner">
                          <?php foreach($images as $i => $img):
                            $imgUrl = $gallery_url_base . $img;
                          ?>
                            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                              <img src="<?= htmlspecialchars($imgUrl) ?>" class="d-block w-100" alt="Gallery Image" style="height:500px; object-fit:cover;">
                            </div>
                          <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselGallery<?= $modalIndex ?>" data-bs-slide="prev">
                          <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselGallery<?= $modalIndex ?>" data-bs-slide="next">
                          <span class="carousel-control-next-icon"></span>
                        </button>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const buttons = document.querySelectorAll("#eventCategories .nav-link");
      const eventCards = document.querySelectorAll(".event-box");

      buttons.forEach(btn => {
        btn.addEventListener("click", () => {
          buttons.forEach(b => b.classList.remove("active"));
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
<section class="py-5" style="background:#eef3f7;">
  <div class="container text-center">
    <h2 class="fw-bold mb-4" style="color:#004085;">Association at a Glance</h2>
    <div class="row g-4">
      
      <!-- Members -->
      <div class="col-md-3">
        <div class="p-4 bg-white shadow-sm rounded-3 h-100">
          <h3 class="fw-bold text-primary" data-count="250">0</h3>
          <p class="mb-0 text-secondary">Active Members</p>
        </div>
      </div>
      
      <!-- Events -->
      <div class="col-md-3">
        <div class="p-4 bg-white shadow-sm rounded-3 h-100">
          <h3 class="fw-bold text-success" data-count="45">0</h3>
          <p class="mb-0 text-secondary">Events Organized</p>
        </div>
      </div>
      
      <!-- Projects -->
      <div class="col-md-3">
        <div class="p-4 bg-white shadow-sm rounded-3 h-100">
          <h3 class="fw-bold text-warning" data-count="12">0</h3>
          <p class="mb-0 text-secondary">Community Projects</p>
        </div>
      </div>
      
      <!-- Years -->
      <div class="col-md-3">
        <div class="p-4 bg-white shadow-sm rounded-3 h-100">
          <h3 class="fw-bold text-danger" data-count="8">0</h3>
          <p class="mb-0 text-secondary">Years of Service</p>
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
<section class="map-section py-5" style="background: #f8f9fa;">
  <div class="container text-center">
    <h2 class="mb-4" style="font-family: 'Lora', serif; color: #003366; font-weight: 700;">
      Visit Driftwood Beach
    </h2>
    <p class="mb-4 text-muted">Here’s our location — zoom in/out and explore the map.</p>

    <!-- Map container -->
    <div id="map" style="height: 450px; width: 100%;" class="shadow rounded"></div>
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
<section class="faq-section py-5" style="background-color: #f8f9fa;">
  <div class="container">
    <h2 class="text-center mb-4" style="font-family: 'Lora', serif; color: #003366; font-weight: 700;">
      Frequently Asked Questions
    </h2>
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
            Yes! We regularly conduct training on sustainable fishing, eco-tourism, and alternative livelihood programs to help improve members’ income and skills.
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

</body>
</html>
