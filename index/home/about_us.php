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
      background: var(--bg);
      color: #2c3e50;
    }
    
    /* Modern Hero Section */
    .hero-section {
      background: linear-gradient(135deg, rgba(44, 62, 80, 0.85) 0%, rgba(26, 37, 47, 0.90) 100%), url('../images/bg1.jpg') center/cover no-repeat;
      min-height: 320px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      text-shadow: 0 4px 12px rgba(0,0,0,0.3);
      flex-direction: column;
      position: relative;
      overflow: hidden;
    }
    .hero-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
    }
    .hero-section h1 {
      font-family: 'Poppins', sans-serif;
      font-size: 3rem;
      font-weight: 800;
      letter-spacing: 1px;
      margin-bottom: 0.5rem;
      animation: fadeInUp 0.8s ease-out;
    }
    .hero-section .subtitle {
      font-size: 1.2rem;
      opacity: 0.95;
      font-weight: 500;
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
    
    /* About Content Section */
    .about-content {
      background: #fff;
      border-radius: 24px;
      box-shadow: 0 8px 32px rgba(44, 62, 80, 0.1);
      padding: 40px 35px;
      margin-top: -60px;
      margin-bottom: 30px;
      position: relative;
      z-index: 2;
      border: 1px solid #e2e8f0;
    }
    .about-content h2 {
      font-family: 'Poppins', sans-serif;
      color: var(--primary);
      font-weight: 800;
      margin-bottom: 1rem;
      letter-spacing: -0.5px;
      font-size: 2.2rem;
    }
    .about-content p {
      color: #4b5563;
      font-size: 1.05rem;
      line-height: 1.75;
      font-weight: 400;
    }
    .divider {
      width: 70px;
      height: 4px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      border-radius: 2px;
      margin: 1rem auto 1.5rem auto;
    }
    
    /* Officer Section */
    .officer-section-title {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 800;
      margin-top: 2.5rem;
      margin-bottom: 1.5rem;
      text-align: center;
      font-size: 2.2rem;
      letter-spacing: -0.5px;
    }
    .officer-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 6px 24px rgba(44, 62, 80, 0.1);
      padding: 25px 18px 20px 18px;
      margin-bottom: 24px;
      text-align: center;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      min-height: 320px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      border: 1px solid #e2e8f0;
      position: relative;
    }
    .officer-card::before {
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
    .officer-card:hover::before {
      opacity: 1;
    }
    .officer-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 16px 48px rgba(44, 62, 80, 0.15);
    }
    .officer-frame {
      width: 130px;
      height: 130px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 50%, var(--accent) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 16px;
      box-shadow: 0 6px 20px rgba(44, 62, 80, 0.2);
      position: relative;
    }
    .officer-frame::after {
      content: '';
      position: absolute;
      inset: -3px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent));
      filter: blur(8px);
      opacity: 0;
      transition: opacity 0.4s ease;
      z-index: -1;
    }
    .officer-card:hover .officer-frame::after {
      opacity: 0.6;
    }
    .officer-frame img {
      width: 112px;
      height: 112px;
      object-fit: cover;
      border-radius: 50%;
      border: 4px solid #fff;
      background: #fff;
    }
    .officer-card h5 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 700;
      font-size: 1.15rem;
      margin-bottom: 0.3rem;
      margin-top: 0.5rem;
    }
    .officer-card p {
      color: var(--primary);
      font-weight: 600;
      margin-bottom: 0.5rem;
      font-size: 0.95rem;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }
    .officer-card .desc {
      font-size: 0.92rem;
      color: #64748b;
      margin-top: 0.5rem;
      line-height: 1.6;
      min-height: 40px;
    }
    
    /* Stats Section */
    .stats-section {
      background: linear-gradient(180deg, var(--light) 0%, #ffffff 100%);
      padding: 40px 0;
    }
    .stats-section h2 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 800;
      margin-bottom: 2rem;
      font-size: 2rem;
      letter-spacing: -0.5px;
    }
    .stat-card {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 6px 24px rgba(44, 62, 80, 0.08);
      padding: 30px 20px;
      margin-bottom: 24px;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 170px;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid #e2e8f0;
      transform: translateY(30px);
      opacity: 0;
    }
    .stat-card.animate {
      transform: translateY(0);
      opacity: 1;
    }
    .stat-card:hover {
      box-shadow: 0 12px 40px rgba(44, 62, 80, 0.15);
      transform: translateY(-6px);
    }
    .stat-card .stat-icon {
      font-size: 2.5rem;
      margin-bottom: 0.8rem;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--light) 0%, #f1f5f9 100%);
      padding: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(44, 62, 80, 0.1);
    }
    .stat-card .stat-title {
      font-size: 1rem;
      color: #64748b;
      margin-bottom: 0;
      font-weight: 600;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }
    .stat-card .counter {
      font-size: 2.5rem;
      font-weight: 800;
      margin-bottom: 0.3rem;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    /* Mission & Vision Cards */
    .mission-vision-section {
      background: linear-gradient(180deg, #ffffff 0%, var(--light) 100%);
      padding: 50px 0;
    }
    .mv-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 8px 32px rgba(44, 62, 80, 0.1);
      padding: 35px 30px;
      height: 100%;
      border: 1px solid #e2e8f0;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }
    .mv-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
    }
    .mv-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 16px 48px rgba(44, 62, 80, 0.15);
    }
    .mv-card .icon-wrapper {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      box-shadow: 0 6px 20px rgba(44, 62, 80, 0.2);
    }
    .mv-card .icon-wrapper i {
      font-size: 2rem;
      color: white;
    }
    .mv-card h3 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 700;
      font-size: 1.5rem;
      margin-bottom: 15px;
      text-align: center;
    }
    .mv-card p {
      color: #64748b;
      font-size: 1rem;
      line-height: 1.75;
      text-align: center;
      margin-bottom: 0;
    }
    
    /* Core Values Section */
    .values-section {
      background: #fff;
      padding: 50px 0;
    }
    .values-section h2 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 800;
      margin-bottom: 2rem;
      font-size: 2.2rem;
      letter-spacing: -0.5px;
    }
    .value-card {
      text-align: center;
      padding: 25px 20px;
      background: linear-gradient(135deg, var(--light) 0%, #f1f5f9 100%);
      border-radius: 18px;
      margin-bottom: 24px;
      transition: all 0.3s ease;
      border: 1px solid #e2e8f0;
      height: 100%;
    }
    .value-card:hover {
      background: white;
      box-shadow: 0 8px 24px rgba(44, 62, 80, 0.12);
      transform: translateY(-4px);
    }
    .value-card .value-icon {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 15px;
      box-shadow: 0 4px 12px rgba(44, 62, 80, 0.2);
    }
    .value-card .value-icon i {
      font-size: 1.5rem;
      color: white;
    }
    .value-card h4 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 700;
      font-size: 1.1rem;
      margin-bottom: 10px;
    }
    .value-card p {
      color: #64748b;
      font-size: 0.9rem;
      line-height: 1.6;
      margin-bottom: 0;
    }
    
    /* Call to Action Section */
    .cta-section {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      padding: 60px 0;
      color: white;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .cta-section::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
      border-radius: 50%;
    }
    .cta-section h2 {
      font-family: 'Poppins', sans-serif;
      font-weight: 800;
      font-size: 2.5rem;
      margin-bottom: 20px;
      color: white;
      position: relative;
      z-index: 1;
    }
    .cta-section p {
      font-size: 1.15rem;
      margin-bottom: 30px;
      opacity: 0.95;
      max-width: 700px;
      margin-left: auto;
      margin-right: auto;
      position: relative;
      z-index: 1;
    }
    .cta-btn {
      background: white;
      color: var(--primary);
      padding: 15px 40px;
      border-radius: 50px;
      font-weight: 700;
      font-size: 1.1rem;
      border: none;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease;
      display: inline-block;
      text-decoration: none;
      position: relative;
      z-index: 1;
    }
    .cta-btn:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
      color: var(--primary);
    }
    .cta-btn i {
      margin-left: 8px;
    }
    
    @media (max-width: 991.98px) {
      .officer-frame { width: 110px; height: 110px; }
      .officer-frame img { width: 92px; height: 92px; }
      .officer-card { min-height: 0; }
    }
    @media (max-width: 767.98px) {
      .about-content { padding: 30px 20px; }
      .hero-section h1 { font-size: 2.2rem; }
      .officer-frame { width: 95px; height: 95px; }
      .officer-frame img { width: 77px; height: 77px; }
      .cta-section h2 { font-size: 2rem; }
      .mv-card, .value-card { margin-bottom: 20px; }
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
</div>

<!-- Mission & Vision Section -->
<section class="mission-vision-section">
  <div class="container">
    <h2 class="text-center mb-2" style="font-family: 'Poppins', sans-serif; color: var(--dark); font-weight: 800; font-size: 2.2rem; letter-spacing: -0.5px;">Our Mission & Vision</h2>
    <div class="divider"></div>
    <div class="row justify-content-center mt-4">
      <div class="col-md-6 mb-4">
        <div class="mv-card">
          <div class="icon-wrapper">
            <i class="bi bi-bullseye"></i>
          </div>
          <h3>Mission</h3>
          <p>To empower local fishermen and boatmen through collaboration, sustainable practices, training programs, and strong leadership, ensuring the welfare and continuous development of our members and their families.</p>
        </div>
      </div>
      <div class="col-md-6 mb-4">
        <div class="mv-card">
          <div class="icon-wrapper">
            <i class="bi bi-eye"></i>
          </div>
          <h3>Vision</h3>
          <p>To be the leading fishermen association in the region, recognized for fostering unity, promoting sustainable fishing practices, and creating lasting opportunities for growth and prosperity in our community.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Core Values Section -->
<section class="values-section">
  <div class="container">
    <h2 class="text-center mb-2">Our Core Values</h2>
    <div class="divider"></div>
    <div class="row mt-4">
      <div class="col-md-4 col-sm-6 mb-3">
        <div class="value-card">
          <div class="value-icon">
            <i class="bi bi-people-fill"></i>
          </div>
          <h4>Unity</h4>
          <p>We stand together as one community, supporting each other in times of need.</p>
        </div>
      </div>
      <div class="col-md-4 col-sm-6 mb-3">
        <div class="value-card">
          <div class="value-icon">
            <i class="bi bi-shield-check"></i>
          </div>
          <h4>Integrity</h4>
          <p>We uphold honesty and transparency in all our actions and decisions.</p>
        </div>
      </div>
      <div class="col-md-4 col-sm-6 mb-3">
        <div class="value-card">
          <div class="value-icon">
            <i class="bi bi-arrow-repeat"></i>
          </div>
          <h4>Sustainability</h4>
          <p>We promote responsible fishing practices to preserve marine resources for future generations.</p>
        </div>
      </div>
      <div class="col-md-4 col-sm-6 mb-3">
        <div class="value-card">
          <div class="value-icon">
            <i class="bi bi-trophy"></i>
          </div>
          <h4>Excellence</h4>
          <p>We strive for the highest standards in everything we do, from leadership to community service.</p>
        </div>
      </div>
      <div class="col-md-4 col-sm-6 mb-3">
        <div class="value-card">
          <div class="value-icon">
            <i class="bi bi-hand-thumbs-up"></i>
          </div>
          <h4>Accountability</h4>
          <p>We take responsibility for our commitments and deliver on our promises to members.</p>
        </div>
      </div>
      <div class="col-md-4 col-sm-6 mb-3">
        <div class="value-card">
          <div class="value-icon">
            <i class="bi bi-heart-fill"></i>
          </div>
          <h4>Compassion</h4>
          <p>We care deeply for our members' welfare and work to improve their quality of life.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Stats Section with "At a Glance" intro -->
<div class="container">
  <h2 class="text-center mt-5 mb-2" style="font-family: 'Poppins', sans-serif; color: var(--dark); font-weight: 800; font-size: 2.2rem; letter-spacing: -0.5px;">Bankero & Fishermen Association at a Glance</h2>
  <div class="divider"></div>
  <p class="text-center mb-4">Since its founding in 2009, the Bankero & Fishermen Association has grown to over 250 members, successfully implementing 35 community projects and organizing 50 events to support and empower the local fishing community.</p>
</div>

<!-- Stats Section -->
<section class="stats-section text-center">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-6 col-md-3 mb-4">
        <div class="stat-card">
          <div class="stat-icon" style="color: var(--primary);">
            <i class="bi bi-calendar3"></i>
          </div>
          <div class="counter" data-target="2009">0</div>
          <div class="stat-title">Founded</div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <div class="stat-card">
          <div class="stat-icon" style="color: var(--success);">
            <i class="bi bi-people"></i>
          </div>
          <div class="counter" data-target="250">0</div>
          <div class="stat-title">Members</div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <div class="stat-card">
          <div class="stat-icon" style="color: var(--secondary);">
            <i class="bi bi-diagram-3"></i>
          </div>
          <div class="counter" data-target="35">0</div>
          <div class="stat-title">Community Projects</div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <div class="stat-card">
          <div class="stat-icon" style="color: var(--accent);">
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

<!-- Call to Action Section -->
<section class="cta-section">
  <div class="container">
    <h2>Join Our Community Today</h2>
    <p>Become part of a thriving association dedicated to the welfare and development of local fishermen. Together, we create opportunities, share resources, and build a stronger future for our community.</p>
    <a href="contact.php" class="cta-btn">
      Get in Touch <i class="bi bi-arrow-right"></i>
    </a>
  </div>
</section>

<?php include("partials/footer.php"); ?>
<?php include 'chatbox.php'; ?>

</body>
</html>
