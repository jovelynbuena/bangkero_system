<?php
include('../../config/db_connect.php');

// Ensure who_we_are table exists (for About Us history section)
$conn->query("CREATE TABLE IF NOT EXISTS who_we_are (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ensure core_values table exists (for Core Values section)
$conn->query("CREATE TABLE IF NOT EXISTS core_values (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Fetch Who We Are entries for history section
$whoResult = $conn->query("SELECT * FROM who_we_are ORDER BY created_at ASC");

$whoEntries = [];
if ($whoResult && $whoResult->num_rows > 0) {
    while ($row = $whoResult->fetch_assoc()) {
        $whoEntries[] = $row;
    }
}

// Fetch Mission & Vision content
$missionText = '';
$visionText = '';
$mvResult = $conn->query("SELECT * FROM mission_vision ORDER BY id ASC LIMIT 1");
if ($mvResult && $mvResult->num_rows > 0) {
    $mvRow = $mvResult->fetch_assoc();
    $missionText = $mvRow['mission'];
    $visionText = $mvRow['vision'];
}

// Fetch Core Values content
$coreValues = [];
$valuesResult = $conn->query("SELECT * FROM core_values ORDER BY sort_order ASC, created_at ASC");
if ($valuesResult && $valuesResult->num_rows > 0) {
    while ($row = $valuesResult->fetch_assoc()) {
        $coreValues[] = $row;
    }
}

// Fetch Association at a Glance content
$glanceOverview = '';
$glanceYear = 0;
$glanceMembers = 0;
$glanceProjects = 0;
$glanceEvents = 0;

$glanceResult = $conn->query("SELECT * FROM association_glance ORDER BY id ASC LIMIT 1");
if ($glanceResult && $glanceResult->num_rows > 0) {
    $glanceRow = $glanceResult->fetch_assoc();
    $glanceOverview = $glanceRow['overview'];
    $glanceYear = (int)$glanceRow['founded_year'];
    $glanceMembers = (int)$glanceRow['members_count'];
    $glanceProjects = (int)$glanceRow['projects_count'];
    $glanceEvents = (int)$glanceRow['events_count'];
}


// Fetch CURRENT officers with member names and role names (term not yet ended)
$query = "
    SELECT 
        officers.id,
        COALESCE(NULLIF(officer_roles.role_name,''), NULLIF(officers.position,''), 'Officer') AS position,
        officers.term_start,
        officers.term_end,
        officers.image,
        officers.description,
        COALESCE(NULLIF(members.name,''), 'Unknown') AS member_name
    FROM officers
    LEFT JOIN members ON officers.member_id = members.id
    LEFT JOIN officer_roles ON officers.role_id = officer_roles.id
    WHERE officers.term_end >= CURDATE()
      AND officers.member_id IS NOT NULL
      AND members.id IS NOT NULL
      AND NULLIF(TRIM(members.name), '') IS NOT NULL
    ORDER BY FIELD(
        COALESCE(NULLIF(officer_roles.role_name,''), NULLIF(officers.position,'')),
        'President', 'Vice President', 'Secretary', 'Treasurer', 'Auditor', 'Pro'
    ) ASC, officers.id ASC
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
    
    /* ===== Officer Section ===== */
    .officer-section-title {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 800;
      margin-top: 3rem;
      margin-bottom: 0.5rem;
      text-align: center;
      font-size: 2.2rem;
      letter-spacing: -0.5px;
    }
    .officer-section-desc {
      max-width: 720px;
      margin: 0 auto 2.5rem auto;
      text-align: center;
      color: #64748b;
      font-size: 1rem;
      line-height: 1.7;
    }

    /* All officer cards base */
    .officer-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(44, 62, 80, 0.08);
      padding: 28px 20px 22px;
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
      border: 1px solid #e8edf3;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      height: 100%;
    }
    .officer-card::after {
      content: '';
      position: absolute;
      bottom: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--primary), var(--accent));
      transform: scaleX(0);
      transition: transform 0.3s ease;
      transform-origin: left;
    }
    .officer-card:hover::after { transform: scaleX(1); }
    .officer-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 14px 40px rgba(44, 62, 80, 0.14);
    }

    /* President card — special highlight */
    .officer-card.president-card {
      border: 2px solid #2c3e50;
      box-shadow: 0 8px 32px rgba(44, 62, 80, 0.14);
      padding: 32px 24px 28px;
    }
    .officer-card.president-card::after {
      height: 4px;
      background: linear-gradient(90deg, var(--primary), #5a6c7d, var(--accent));
      transform: scaleX(1);
    }
    .president-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: #fff;
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 1.2px;
      text-transform: uppercase;
      padding: 4px 14px;
      border-radius: 20px;
      margin-bottom: 14px;
    }

    /* Image frame */
    .officer-frame {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      padding: 4px;
      background: linear-gradient(135deg, var(--primary), var(--accent));
      box-shadow: 0 4px 16px rgba(44, 62, 80, 0.18);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 14px;
      transition: box-shadow 0.3s ease;
    }
    .officer-card:hover .officer-frame {
      box-shadow: 0 8px 28px rgba(44, 62, 80, 0.28);
    }
    .officer-frame img {
      width: 110px;
      height: 110px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #fff;
      background: #fff;
      display: block;
    }
    .officer-card.president-card .officer-frame {
      width: 138px;
      height: 138px;
    }
    .officer-card.president-card .officer-frame img {
      width: 128px;
      height: 128px;
    }

    /* Text inside cards */
    .officer-card h5 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 700;
      font-size: 1.08rem;
      margin: 6px 0 2px;
      line-height: 1.3;
    }
    .officer-card.president-card h5 {
      font-size: 1.18rem;
    }
    .officer-card .role-label {
      display: inline-block;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 1.3px;
      text-transform: uppercase;
      color: var(--accent);
      background: #f1f5f9;
      border-radius: 12px;
      padding: 3px 12px;
      margin: 6px 0 10px;
    }
    .officer-card.president-card .role-label {
      color: var(--primary);
      background: #eef2f7;
    }
    .officer-card .desc {
      font-size: 0.875rem;
      color: #64748b;
      line-height: 1.65;
      margin-top: 4px;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .officer-card.president-card .desc {
      -webkit-line-clamp: 4;
    }

    /* Connector line between president and others */
    .officers-connector {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin: 4px 0 24px;
      gap: 0;
    }
    .officers-connector .line {
      width: 2px;
      height: 32px;
      background: linear-gradient(to bottom, var(--primary), #b0bec5);
      border-radius: 2px;
    }
    .officers-connector .dot {
      width: 10px;
      height: 10px;
      background: var(--primary);
      border-radius: 50%;
      border: 2px solid #fff;
      box-shadow: 0 0 0 2px var(--primary);
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
      .officer-frame { width: 100px; height: 100px; }
      .officer-frame img { width: 90px; height: 90px; }
      .officer-card.president-card .officer-frame { width: 116px; height: 116px; }
      .officer-card.president-card .officer-frame img { width: 106px; height: 106px; }
    }
    @media (max-width: 767.98px) {
      .about-content { padding: 30px 20px; }
      .hero-section h1 { font-size: 2.2rem; }
      .officer-frame { width: 90px; height: 90px; }
      .officer-frame img { width: 80px; height: 80px; }
      .officer-card.president-card .officer-frame { width: 106px; height: 106px; }
      .officer-card.president-card .officer-frame img { width: 96px; height: 96px; }
      .cta-section h2 { font-size: 2rem; }
      .mv-card, .value-card { margin-bottom: 20px; }
      .officers-connector { display: none; }
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

  <?php if (!empty($whoEntries)): ?>
    <?php foreach ($whoEntries as $entry): ?>
      <p class="mb-4 text-center">
        <?= nl2br(htmlspecialchars($entry['content'])) ?>
      </p>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="mb-4 text-center">
      The Bankero & Fishermen Association was founded in November 2009 in Barretto, Olongapo City under the leadership of Mr. Noliboy Cocjin. Starting with around 300–400 members, the association has since grown and organized its members into smaller groups for more effective management.
    </p>
    <p class="mb-4 text-center">
      Dedicated to supporting local boatmen and fishermen, the association serves as a vital link for their welfare and development. To strengthen communication and organizational efficiency, the association is now adopting the Bankero & Fishermen Association Management System, which will automate membership records, announcements, and event scheduling, while introducing SMS notifications for timely updates.
    </p>
    <p class="mb-4 text-center">
      Through this modernization, the association continues its mission of empowering members, enhancing participation, and preserving the livelihood of the fishing community.
    </p>
  <?php endif; ?>
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
          <p>
            <?php if ($missionText !== ''): ?>
              <?= nl2br(htmlspecialchars($missionText)) ?>
            <?php else: ?>
              To empower local fishermen and boatmen through collaboration, sustainable practices, training programs, and strong leadership, ensuring the welfare and continuous development of our members and their families.
            <?php endif; ?>
          </p>
        </div>
      </div>
      <div class="col-md-6 mb-4">
        <div class="mv-card">
          <div class="icon-wrapper">
            <i class="bi bi-eye"></i>
          </div>
          <h3>Vision</h3>
          <p>
            <?php if ($visionText !== ''): ?>
              <?= nl2br(htmlspecialchars($visionText)) ?>
            <?php else: ?>
              To be the leading fishermen association in the region, recognized for fostering unity, promoting sustainable fishing practices, and creating lasting opportunities for growth and prosperity in our community.
            <?php endif; ?>
          </p>
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

    <?php if (!empty($coreValues)): ?>
      <div class="row mt-4">
        <?php $iconClasses = ['bi-people-fill','bi-shield-check','bi-arrow-repeat','bi-trophy','bi-hand-thumbs-up','bi-heart-fill']; ?>
        <?php foreach ($coreValues as $index => $value): ?>
          <?php $iconClass = $iconClasses[$index % count($iconClasses)]; ?>
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="value-card">
              <div class="value-icon">
                <i class="bi <?= htmlspecialchars($iconClass) ?>"></i>
              </div>
              <h4><?= htmlspecialchars($value['title']) ?></h4>
              <p><?= nl2br(htmlspecialchars($value['description'])) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
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
    <?php endif; ?>
  </div>
</section>


<!-- Stats Section with "At a Glance" intro -->
<div class="container">
  <h2 class="text-center mt-5 mb-2" style="font-family: 'Poppins', sans-serif; color: var(--dark); font-weight: 800; font-size: 2.2rem; letter-spacing: -0.5px;">Bankero & Fishermen Association at a Glance</h2>
  <div class="divider"></div>
  <?php if ($glanceOverview !== ''): ?>
    <p class="text-center mb-4">
      <?= nl2br(htmlspecialchars($glanceOverview)) ?>
    </p>
  <?php else: ?>
    <p class="text-center mb-4">Since its founding in 2009, the Bankero & Fishermen Association has grown to over 250 members, successfully implementing 35 community projects and organizing 50 events to support and empower the local fishing community.</p>
  <?php endif; ?>
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
          <div class="counter" data-target="<?= $glanceYear > 0 ? (int)$glanceYear : 2009 ?>">0</div>
          <div class="stat-title">Founded</div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <div class="stat-card">
          <div class="stat-icon" style="color: var(--success);">
            <i class="bi bi-people"></i>
          </div>
          <div class="counter" data-target="<?= $glanceMembers > 0 ? (int)$glanceMembers : 250 ?>">0</div>
          <div class="stat-title">Members</div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <div class="stat-card">
          <div class="stat-icon" style="color: var(--secondary);">
            <i class="bi bi-diagram-3"></i>
          </div>
          <div class="counter" data-target="<?= $glanceProjects > 0 ? (int)$glanceProjects : 35 ?>">0</div>
          <div class="stat-title">Community Projects</div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-4">
        <div class="stat-card">
          <div class="stat-icon" style="color: var(--accent);">
            <i class="bi bi-calendar2-event"></i>
          </div>
          <div class="counter" data-target="<?= $glanceEvents > 0 ? (int)$glanceEvents : 50 ?>">0</div>
          <div class="stat-title">Events Organized</div>
        </div>
      </div>
    </div>
  </div>
</section>

  <!-- Officers Intro Section -->
  <h2 class="officer-section-title">Board of Officers</h2>
  <div class="divider"></div>
  <p class="officer-section-desc">
    Guided by strong leadership and clear responsibilities, our Board of Officers leads programs and initiatives that uphold the welfare of our members and strengthen the community.
  </p>

  <!-- Officers Hierarchy Section -->
  <div class="container mb-5 px-2">

    <!-- President — centered, highlighted -->
    <?php if (!empty($officers_by_position['president'])): ?>
    <div class="row justify-content-center mb-2">
      <?php foreach ($officers_by_position['president'] as $row): ?>
      <div class="col-12 col-sm-8 col-md-5 col-lg-4">
        <div class="officer-card president-card">
          <span class="president-badge"><i class="bi bi-star-fill" style="font-size:0.65rem;"></i> Head Officer</span>
          <div class="officer-frame">
            <?php if (!empty($row['image']) && file_exists(__DIR__ . "/../../uploads/officers/" . $row['image'])): ?>
              <img src="../../uploads/officers/<?= htmlspecialchars($row['image']) ?>" alt="President">
            <?php else: ?>
              <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['member_name']) ?>&size=128&background=2c3e50&color=fff&bold=true" alt="President">
            <?php endif; ?>
          </div>
          <h5><?= htmlspecialchars($row['member_name']) ?></h5>
          <span class="role-label"><?= htmlspecialchars($row['position']) ?></span>
          <?php if (!empty($row['description'])): ?>
            <div class="desc"><?= nl2br(htmlspecialchars($row['description'])) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Connector line -->
    <div class="officers-connector">
      <div class="dot"></div>
      <div class="line"></div>
    </div>
    <?php endif; ?>

    <!-- Other officers -->
    <div class="row justify-content-center g-3">
      <?php
      foreach ($officers_by_position as $pos_key => $officers_in_pos):
        if ($pos_key === 'president') continue;
        foreach ($officers_in_pos as $row):
      ?>
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="officer-card">
          <div class="officer-frame">
            <?php if (!empty($row['image']) && file_exists(__DIR__ . "/../../uploads/officers/" . $row['image'])): ?>
              <img src="../../uploads/officers/<?= htmlspecialchars($row['image']) ?>" alt="Officer">
            <?php else: ?>
              <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['member_name']) ?>&size=110&background=5a6c7d&color=fff&bold=true" alt="Officer">
            <?php endif; ?>
          </div>
          <h5><?= htmlspecialchars($row['member_name']) ?></h5>
          <span class="role-label"><?= htmlspecialchars($row['position']) ?></span>
          <?php if (!empty($row['description'])): ?>
            <div class="desc"><?= nl2br(htmlspecialchars($row['description'])) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php
        endforeach;
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
    <a href="contact_us.php" class="cta-btn">
      Get in Touch <i class="bi bi-arrow-right"></i>
    </a>
  </div>
</section>

<?php include("partials/footer.php"); ?>
<?php include 'chatbox.php'; ?>

</body>
</html>
