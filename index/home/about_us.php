<?php
include('../../config/db_connect.php');

// Fetch officers with member names and role names
$query = "
    SELECT 
        officers.id,
        officer_roles.role_name AS position,
        officers.term_start,
        officers.term_end,
        officers.image,
        officers.description,
        members.name AS member_name
    FROM officers
    JOIN members ON officers.member_id = members.id
    JOIN officer_roles ON officers.role_id = officer_roles.id
    ORDER BY FIELD(officer_roles.role_name, 'President', 'Vice President', 'Secretary', 'Treasurer', 'Auditor', 'Pro') ASC
";

$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Group officers by position for hierarchy
$officers_by_position = [];
while ($row = $result->fetch_assoc()) {
    $pos_key = strtolower($row['position']);
    $officers_by_position[$pos_key][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - Bankero & Fishermen Association</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Open Sans', sans-serif; background: #f8f9fa; }
    .hero-section {
      background: linear-gradient(rgba(0,0,0,0.18),rgba(0,0,0,0.18)), url('../images/bg1.jpg') center/cover no-repeat;
      min-height: 380px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      text-shadow: 0 2px 8px rgba(0,0,0,0.18);
      flex-direction: column;
    }
    .hero-section h1 {
      font-family: 'Lora', serif;
      font-size: 3.2rem;
      font-weight: 700;
      letter-spacing: 2px;
      margin-bottom: 0.5rem;
    }
    .hero-section .subtitle {
      font-size: 1.3rem;
      opacity: 0.95;
    }
    .about-content {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 2px 16px rgba(2,136,209,0.06);
      padding: 48px 32px 32px 32px;
      margin-top: -70px;
      margin-bottom: 32px;
      position: relative;
      z-index: 2;
    }
    .about-content h2 {
      font-family: 'Lora', serif;
      color: #01579b;
      font-weight: 700;
      margin-bottom: 1rem;
      letter-spacing: 1px;
    }
    .about-content p {
      color: #333;
      font-size: 1.08rem;
      line-height: 1.6;
    }
    .divider {
      width: 80px;
      height: 4px;
      background: #ff7043;
      border-radius: 2px;
      margin: 1.5rem auto 2rem auto;
      opacity: 0.8;
    }
    .officer-section-title {
      font-family: 'Lora', serif;
      color: #01579b;
      font-weight: 700;
      margin-top: 3rem;
      margin-bottom: 2rem;
      text-align: center;
      font-size: 2.2rem;
      letter-spacing: 1px;
    }
    .officer-card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.08);
      padding: 28px 18px 18px 18px;
      margin-bottom: 24px;
      text-align: center;
      transition: transform 0.2s, box-shadow 0.2s;
      min-height: 340px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
    }
    .officer-card:hover {
      transform: translateY(-4px) scale(1.03);
      box-shadow: 0 8px 32px rgba(255,112,67,0.13);
    }
    .officer-frame {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      background: conic-gradient(from 0deg, #ff7043 0deg 120deg, #fff 120deg 240deg, #1976d2 240deg 360deg);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 18px;
      box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    }
    .officer-frame img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 50%;
      border: 4px solid #fff;
      background: #fff;
    }
    .officer-card h5 {
      font-family: 'Lora', serif;
      color: #01579b;
      font-weight: 700;
      font-size: 1.18rem;
      margin-bottom: 0.2rem;
      margin-top: 0.5rem;
    }
    .officer-card p {
      color: #ff7043;
      font-weight: 600;
      margin-bottom: 0.5rem;
      font-size: 1rem;
      letter-spacing: 0.5px;
    }
    .officer-card .desc {
      font-size: 0.97rem;
      color: #444;
      margin-top: 0.5rem;
      font-family: 'Open Sans', sans-serif;
      min-height: 40px;
    }
      /* Stats Section */
    .stats-section {
      background: var(--gray);
      padding: 56px 0 40px 0;
    }
    .stats-section h2 {
      font-family: 'Lora', serif;
      color: var(--primary);
      font-weight: 700;
      margin-bottom: 2.5rem;
      font-size: 2rem;
      letter-spacing: 1px;
    }
    .stat-card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 2px 12px rgba(33,150,243,0.06);
      padding: 2.2rem 1.2rem 1.2rem 1.2rem;
      margin-bottom: 24px;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 180px;
      transition: box-shadow 0.2s, transform 0.2s;
    }
    .stat-card:hover {
      box-shadow: 0 8px 32px rgba(33,150,243,0.10);
      transform: translateY(-4px) scale(1.03);
    }
    .stat-card .stat-icon {
      font-size: 2.5rem;
      margin-bottom: 0.7rem;
      border-radius: 50%;
      background: #f3f7fa;
      padding: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .stat-card .stat-title {
      font-size: 1.1rem;
      color: #888;
      margin-bottom: 0;
      font-weight: 500;
      letter-spacing: 0.5px;
    }
    .stat-card .counter {
      font-size: 2.1rem;
      font-weight: 700;
      margin-bottom: 0.2rem;
    }
    @media (max-width: 991.98px) {
      .officer-frame { width: 110px; height: 110px; }
      .officer-frame img { width: 90px; height: 90px; }
      .officer-card { min-height: 0; }
    }
    @media (max-width: 767.98px) {
      .about-content { padding: 24px 8px; }
      .hero-section h1 { font-size: 2.1rem; }
      .officer-frame { width: 90px; height: 90px; }
      .officer-frame img { width: 70px; height: 70px; }
    }
   
  .stat-card.animate {
    transform: translateY(0);
    opacity: 1;
    transition: transform 0.6s ease-out, opacity 0.6s ease-out;
  }

  .stat-card {
    transform: translateY(50px);
    opacity: 0;
  }


  </style>
</head>
<body>

<?php include("partials/navbar.php"); ?>

<!-- Hero Section -->
<section class="hero-section text-center">
  <h1>ABOUT US</h1>
  <div class="subtitle mb-2">Bankero & Fishermen Association</div>
</section>

<!-- Main Content -->
<div class="container about-content">
  <h2 class="text-center mb-2">A Brief History</h2>
  <div class="divider"></div>
  <p class="mb-4 text-center">
    The Bankero & Fishermen Association was founded in November 2009 in Barretto, Olongapo City under the leadership of Mr. Noliboy Cocjin. Starting with around 300–400 members, the association has since grown and organized its members into smaller groups for more effective management.
  </p>
  <p class="mb-4 text-center">
    Dedicated to supporting local boatmen and fishermen, the association serves as a vital link for their welfare and development. To strengthen communication and organizational efficiency, the association is now adopting the Bankero & Fishermen Association Management System, which will automate membership records, announcements, and event scheduling, while introducing SMS notifications for timely updates.
  </p>
  <p class="mb-4 text-center">
    Through this modernization, the association continues its mission of empowering members, enhancing participation, and preserving the livelihood of the fishing community.
  </p>

  <h2 class="text-center mt-5 mb-2">Our Mission</h2>
  <div class="divider"></div>
  <p class="text-center mb-5">To empower members through collaboration, training, and strong leadership.</p>
  
  <h2 class="text-center mt-5 mb-2">Bankero & Fishermen Association at a Glance</h2>
  <div class="divider"></div>
  <p class="text-center mb-5">Since its founding in 2009, the Bankero & Fishermen Association has grown to over 250 members, successfully implementing 35 community projects and organizing 50 events to support and empower the local fishing community.
</p>
  <!-- Stats Section -->
<section class="stats-section text-center">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-6 col-md-3 mb-4">
        <div class="stat-card">
          <div class="stat-icon" style="color:#1976d2;">
            <i class="bi bi-calendar3"></i>
          </div>
          <div class="counter" data-target="2009">0</div>
          <div class="stat-title">Founded</div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <div class="stat-card">
          <div class="stat-icon" style="color:#28a745;">
            <i class="bi bi-people"></i>
          </div>
          <div class="counter" data-target="250">0</div>
          <div class="stat-title">Members</div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <div class="stat-card">
          <div class="stat-icon" style="color:#ff7043;">
            <i class="bi bi-diagram-3"></i>
          </div>
          <div class="counter" data-target="35">0</div>
          <div class="stat-title">Community Projects</div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <div class="stat-card">
          <div class="stat-icon" style="color:#ffa726;">
            <i class="bi bi-calendar2-event"></i>
          </div>
          <div class="counter" data-target="50">0</div>
          <div class="stat-title">Events Organized</div>
        </div>
      </div>
    </div>
  </div>
  
</section>
  <!-- Officers Intro Section -->
  <h2 class="officer-section-title">Board of Officers</h2>
  <div class="divider"></div>
  <p class="text-center mb-5" style="max-width:800px; margin:auto;">
    The Bankero & Fishermen Association ensures that its organizational structure is guided 
    by strong leadership and clear responsibilities. Our Board of Officers is tasked with 
    leading programs, projects, and initiatives that uphold the welfare of our members and 
    strengthen the community. Each officer plays a vital role in fostering collaboration, 
    transparency, and commitment to our shared mission.
  </p>

  <!-- Officers Hierarchy Section -->
  <div class="container mb-5 px-0">
    <!-- President at the top, centered -->
    <div class="row justify-content-center mb-4">
      <?php
      if (!empty($officers_by_position['president'])):
        foreach ($officers_by_position['president'] as $row):
      ?>
        <div class="col-12 col-md-4 col-lg-3">
          <div class="officer-card">
            <div class="officer-frame mx-auto mb-3">
              <?php if (!empty($row['image']) && file_exists(__DIR__ . "/../../uploads/officers/" . $row['image'])): ?>
                <img src="../../uploads/officers/<?= htmlspecialchars($row['image']) ?>" alt="Officer">
              <?php else: ?>
                <img src="https://via.placeholder.com/150?text=No+Image" alt="No Image">
              <?php endif; ?>
            </div>
            <h5><?= htmlspecialchars($row['member_name']) ?></h5>
            <p><?= htmlspecialchars($row['position']) ?></p>
            <?php if (!empty($row['description'])): ?>
              <div class="desc"><?= nl2br(htmlspecialchars($row['description'])) ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php
        endforeach;
      endif;
      ?>
    </div>
    <!-- Other officers in a row below -->
    <div class="row justify-content-center">
      <?php
      $other_positions = ['vice president','secretary','treasurer','auditor','pro','member'];
      foreach ($other_positions as $pos_key):
        if (!empty($officers_by_position[$pos_key])):
          foreach ($officers_by_position[$pos_key] as $row):
      ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
          <div class="officer-card">
            <div class="officer-frame mx-auto mb-3">
              <?php if (!empty($row['image']) && file_exists(__DIR__ . "/../../uploads/officers/" . $row['image'])): ?>
                <img src="../../uploads/officers/<?= htmlspecialchars($row['image']) ?>" alt="Officer">
              <?php else: ?>
                <img src="https://via.placeholder.com/150?text=No+Image" alt="No Image">
              <?php endif; ?>
            </div>
            <h5><?= htmlspecialchars($row['member_name']) ?></h5>
            <p><?= htmlspecialchars($row['position']) ?></p>
            <?php if (!empty($row['description'])): ?>
              <div class="desc"><?= nl2br(htmlspecialchars($row['description'])) ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php
          endforeach;
        endif;
      endforeach;
      ?>
    </div>
  </div>
</div>
<script>
// Animate counters when in viewport
document.addEventListener('DOMContentLoaded', () => {
  const counters = document.querySelectorAll('.counter');
  const statCards = document.querySelectorAll('.stat-card');

  const isInViewport = (el) => {
    const rect = el.getBoundingClientRect();
    return rect.top <= (window.innerHeight || document.documentElement.clientHeight) - 50;
  };

  const animateStats = () => {
    statCards.forEach((card, index) => {
      if (isInViewport(card) && !card.classList.contains('animate')) {
        card.classList.add('animate');

        // Animate number
        const counter = card.querySelector('.counter');
        const target = +counter.getAttribute('data-target');
        let count = 0;
        const increment = target / 200;

        const updateCount = () => {
          count += increment;
          if (count < target) {
            counter.innerText = Math.ceil(count);
            requestAnimationFrame(updateCount);
          } else {
            counter.innerText = target;
          }
        };
        updateCount();
      }
    });
  };

  window.addEventListener('scroll', animateStats);
  animateStats(); // also run on load
});
</script>

<!-- Multi-layered Blue & Orange Wave SVG Background directly below officers -->
<div style="position:relative; width:100%; height:180px; overflow:hidden; background:transparent; margin-top:-40px;">
  <svg viewBox="0 0 1440 180" fill="none" xmlns="http://www.w3.org/2000/svg" style="position:absolute;top:0;left:0;width:100%;height:100%;">
    <path d="M0,60 C360,140 1080,-20 1440,60 L1440,180 L0,180 Z" fill="#ff7043"/>
    <path d="M0,100 C400,180 1040,20 1440,100 L1440,180 L0,180 Z" fill="#ffa726" opacity="0.85"/>
    <path d="M0,130 C360,210 1080,50 1440,130 L1440,180 L0,180 Z" fill="#42a5f5" opacity="0.85"/>
    <path d="M0,150 C400,230 1040,70 1440,150 L1440,180 L0,180 Z" fill="#1976d2" opacity="0.7"/>
    <path d="M0,180 C360,260 1080,100 1440,180 L1440,180 L0,180 Z" fill="#90caf9" opacity="0.5"/>
  </svg>
</div>

<?php include("partials/footer.php"); ?>

</body>
</html>
