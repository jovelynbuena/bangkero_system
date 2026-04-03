<?php
session_start();
require_once('../../config/db_connect.php');

// Fetch financial summary statistics
$stats = [
    'total_assistance' => 0,
    'partner_count' => 0,
    'active_programs' => 0,
    'funds_utilized' => 0,
    'last_updated' => date('F Y')
];

// Get total assistance received (using donations table for now)
$assistanceQuery = "SELECT 
    COALESCE(SUM(amount), 0) AS total_assistance,
    COUNT(DISTINCT donor_name) AS partner_count,
    MAX(date_received) AS last_update
    FROM transparency_donations
    WHERE status = 'confirmed'";
$result = $conn->query($assistanceQuery);
if ($result && $row = $result->fetch_assoc()) {
    $stats['total_assistance'] = $row['total_assistance'];
    $stats['partner_count'] = $row['partner_count'];
    if ($row['last_update']) {
        $stats['last_updated'] = date('F Y', strtotime($row['last_update']));
    }
}

// Get active programs count
$programsQuery = "SELECT COUNT(*) AS active_count FROM transparency_campaigns WHERE status IN ('ongoing', 'active')";
$result = $conn->query($programsQuery);
if ($result && $row = $result->fetch_assoc()) {
    $stats['active_programs'] = $row['active_count'];
}

// Get funds utilized (using goal_amount from campaigns)
$utilizedQuery = "SELECT COALESCE(SUM(goal_amount), 0) AS utilized
    FROM transparency_campaigns
    WHERE status IN ('ongoing','completed')";
$result = $conn->query($utilizedQuery);
if ($result && $row = $result->fetch_assoc()) {
    $stats['funds_utilized'] = $row['utilized'];
}

// Fetch active programs/projects from transparency_campaigns
$programsQuery = "SELECT 
    c.id,
    c.name AS program_name,
    c.description,
    c.goal_amount AS allocated_budget,
    c.status,
    c.created_at,
    COALESCE(SUM(d.amount), 0) AS received_amount
    FROM transparency_campaigns c
    LEFT JOIN transparency_donations d ON c.id = d.campaign_id AND d.status='confirmed'
    WHERE c.status IN ('active', 'ongoing', 'completed')
    GROUP BY c.id
    ORDER BY 
        CASE c.status 
            WHEN 'active' THEN 1 
            WHEN 'ongoing' THEN 2 
            ELSE 3 
        END,
        c.created_at DESC 
    LIMIT 6";
$programsResult = $conn->query($programsQuery);

// Fetch recent assistance received (using donations table - limit 10)
$assistanceQuery = "SELECT 
    d.donor_name AS source_name,
    'OTHER' AS source_type,
    d.amount,
    d.date_received,
    COALESCE(c.name, 'General Support') AS description
    FROM transparency_donations d
    LEFT JOIN transparency_campaigns c ON d.campaign_id = c.id
    WHERE d.status = 'confirmed'
    ORDER BY d.date_received DESC 
    LIMIT 10";
$assistanceResult = $conn->query($assistanceQuery);

// Fetch community achievements for photo-wall gallery
$achievementsResult = $conn->query("SELECT * FROM community_achievements WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC");
$achievementsList = [];
if ($achievementsResult && $achievementsResult->num_rows > 0) {
    while ($row = $achievementsResult->fetch_assoc()) {
        $achievementsList[] = $row;
    }
}

// Helper function to format currency
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// Helper function to get source type label
function getSourceTypeLabel($type) {
    $labels = [
        'DOLE' => 'DOLE Program',
        'LGU' => 'LGU Support',
        'NGO' => 'NGO Partner',
        'PRIVATE' => 'Private Sponsor',
        'MEMBERSHIP' => 'Membership Fees',
        'OTHER' => 'Other Source'
    ];
    return $labels[$type] ?? $type;
}

// Helper function to get status badge class
function getStatusClass($status) {
    $status = strtolower($status);
    if ($status === 'active' || $status === 'ongoing') return 'active';
    if ($status === 'completed') return 'completed';
    return 'pending';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Transparency & Association Progress - Bankero & Fishermen Association</title>
  
  <!-- Bootstrap CSS & Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  
  <style>
    :root {
      /* Modern Coastal Fresh Theme — matches user_home.php */
      --primary-color: #2E86AB;
      --secondary-color: #1B4F72;
      --accent-color: #F4A261;
      --light-blue: #A8DADC;
      --pale-blue: #e8f6f8;

      /* Supporting Colors */
      --dark: #1a252f;
      --gray: #6b7280;
      --light-gray: #f0f9fb;
      --white: #ffffff;
      --border: #c9e8ec;

      /* Shadows */
      --shadow-sm: 0 2px 8px rgba(46, 134, 171, 0.08);
      --shadow-md: 0 4px 16px rgba(46, 134, 171, 0.12);
      --shadow-lg: 0 8px 32px rgba(46, 134, 171, 0.18);
    }

    * {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--light-gray);
      color: var(--dark);
      overflow-x: hidden;
      padding-top: 80px;
    }

    /* ==================== HERO SECTION ==================== */
    .hero-section {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      color: white;
      padding: 80px 0 60px;
      position: relative;
      overflow: hidden;
    }

    .hero-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
      opacity: 0.3;
    }

    .hero-content {
      position: relative;
      z-index: 1;
    }

    .hero-section h1 {
      font-family: 'Poppins', sans-serif;
      font-size: 3rem;
      font-weight: 800;
      margin-bottom: 1rem;
      text-shadow: 0 2px 12px rgba(0,0,0,0.2);
    }

    .hero-section .subtitle {
      font-size: 1.2rem;
      font-weight: 400;
      opacity: 0.95;
      max-width: 800px;
      margin: 0 auto 2rem;
      line-height: 1.7;
    }

    .breadcrumb {
      background: rgba(255,255,255,0.15);
      backdrop-filter: blur(10px);
      padding: 12px 24px;
      border-radius: 50px;
      display: inline-flex;
      margin-bottom: 2rem;
    }

    .breadcrumb-item {
      color: rgba(255,255,255,0.8);
      font-weight: 500;
    }

    .breadcrumb-item.active {
      color: white;
      font-weight: 600;
    }

    .breadcrumb-item + .breadcrumb-item::before {
      color: rgba(255,255,255,0.6);
      content: ">";
    }

    .last-updated {
      background: rgba(255,255,255,0.2);
      backdrop-filter: blur(10px);
      padding: 10px 24px;
      border-radius: 50px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-weight: 600;
    }

    /* ==================== STATISTICS CARDS ==================== */
    .stats-section {
      margin-top: -40px;
      padding: 0 0 60px;
      position: relative;
      z-index: 10;
    }

    .stat-card {
      background: white;
      border-radius: 16px;
      padding: 24px 20px;
      box-shadow: var(--shadow-md);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border: 2px solid transparent;
      height: 100%;
      position: relative;
      overflow: hidden;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: linear-gradient(180deg, var(--primary-color) 0%, var(--accent-color) 100%);
    }

    .stat-card:hover {
      transform: translateY(-6px);
      box-shadow: var(--shadow-lg);
      border-color: var(--primary-color);
    }

    .stat-icon {
      width: 55px;
      height: 55px;
      border-radius: 14px;
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 16px;
      box-shadow: 0 4px 12px rgba(46, 134, 171, 0.30);
    }

    .stat-icon i {
      font-size: 1.6rem;
      color: white;
    }

    .stat-value {
      font-size: 2.2rem;
      font-weight: 800;
      color: var(--primary-color);
      font-family: 'Poppins', sans-serif;
      margin-bottom: 8px;
      line-height: 1;
    }

    .stat-label {
      font-size: 0.88rem;
      color: var(--gray);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      line-height: 1.3;
    }

    /* ==================== SECTION HEADERS ==================== */
    .section-header {
      margin-bottom: 3rem;
      text-align: center;
    }

    .section-header h2 {
      font-family: 'Poppins', sans-serif;
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 1rem;
      position: relative;
      display: inline-block;
    }

    .section-header h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-color) 0%, var(--accent-color) 100%);
      border-radius: 2px;
    }

    .section-header p {
      color: var(--gray);
      font-size: 1.1rem;
      max-width: 700px;
      margin: 1.5rem auto 0;
    }

    /* ==================== PROGRAMS SECTION ==================== */
    .programs-section {
      padding: 60px 0;
      background: white;
    }

    .program-card {
      background: white;
      border: 2px solid var(--border);
      border-radius: 16px;
      padding: 24px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      height: 100%;
      position: relative;
      overflow: hidden;
    }

    .program-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, var(--primary-color) 0%, var(--accent-color) 100%);
      transform: scaleX(0);
      transition: transform 0.3s ease;
    }

    .program-card:hover::before {
      transform: scaleX(1);
    }

    .program-card:hover {
      transform: translateY(-6px);
      box-shadow: var(--shadow-lg);
      border-color: var(--primary-color);
    }

    .program-status {
      position: absolute;
      top: 20px;
      right: 20px;
      padding: 6px 14px;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .program-status.active, .program-status.ongoing {
      background: var(--pale-blue);
      color: var(--primary-color);
    }

    .program-status.completed {
      background: #d5f4e6;
      color: #27ae60;
    }

    .program-card h4 {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      color: var(--dark);
      font-size: 1.2rem;
      margin-bottom: 12px;
      padding-right: 90px;
    }

    .program-card p {
      color: var(--gray);
      line-height: 1.6;
      margin-bottom: 18px;
      font-size: 0.9rem;
    }

    .program-stats {
      display: flex;
      justify-content: space-between;
      margin-bottom: 12px;
    }

    .program-stat {
      text-align: center;
    }

    .program-stat-value {
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--primary-color);
      font-family: 'Poppins', sans-serif;
    }

    .program-stat-label {
      font-size: 0.75rem;
      color: var(--gray);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* ==================== ASSISTANCE TABLE ==================== */
    .assistance-section {
      padding: 60px 0;
      background: var(--light-gray);
    }

    .table-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      flex-wrap: wrap;
      gap: 15px;
    }

    .search-box {
      position: relative;
      max-width: 350px;
      flex: 1;
    }

    .search-box input {
      width: 100%;
      padding: 12px 20px 12px 45px;
      border: 2px solid var(--border);
      border-radius: 50px;
      font-size: 0.95rem;
      transition: all 0.3s ease;
    }

    .search-box input:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(46, 134, 171, 0.15);
    }

    .search-box i {
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--gray);
      font-size: 1.1rem;
    }

    .table-container {
      background: white;
      border-radius: 16px;
      box-shadow: var(--shadow-md);
      overflow: hidden;
      border: 2px solid var(--border);
    }

    .data-table {
      width: 100%;
      margin: 0;
    }

    .data-table thead {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      color: white;
    }

    .data-table thead th {
      padding: 14px 16px;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.8rem;
      letter-spacing: 0.5px;
      border: none;
    }

    .data-table tbody tr {
      border-bottom: 1px solid var(--border);
      transition: all 0.2s ease;
    }

    .data-table tbody tr:hover {
      background-color: var(--pale-blue);
    }

    .data-table tbody td {
      padding: 14px 16px;
      vertical-align: middle;
      font-size: 0.9rem;
    }

    .source-name {
      font-weight: 600;
      color: var(--primary-color);
    }

    .source-type-badge {
      padding: 4px 10px;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 600;
      display: inline-block;
    }

    .source-type-badge.DOLE {
      background: #e3f2fd;
      color: #1565c0;
    }

    .source-type-badge.LGU {
      background: #f3e5f5;
      color: #7b1fa2;
    }

    .source-type-badge.NGO {
      background: #e8f5e9;
      color: #2e7d32;
    }

    .source-type-badge.PRIVATE {
      background: #fff3e0;
      color: #ef6c00;
    }

    .source-type-badge.MEMBERSHIP {
      background: #fce4ec;
      color: #c2185b;
    }

    .amount-cell {
      font-weight: 700;
      color: var(--primary-color);
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
    }

    /* ==================== RESPONSIVE DESIGN ==================== */
    @media (max-width: 991px) {
      .hero-section h1 {
        font-size: 2.2rem;
      }

      .section-header h2 {
        font-size: 2rem;
      }

      .stat-card {
        margin-bottom: 20px;
      }

      .program-card {
        margin-bottom: 25px;
      }
    }

    @media (max-width: 768px) {
      body {
        padding-top: 70px;
      }

      .hero-section {
        padding: 50px 0 40px;
      }

      .hero-section h1 {
        font-size: 1.8rem;
      }

      .hero-section .subtitle {
        font-size: 1rem;
      }

      .stat-value {
        font-size: 2rem;
      }

      .section-header h2 {
        font-size: 1.7rem;
      }

      .table-container {
        overflow-x: auto;
      }

      .data-table {
        min-width: 600px;
      }

      .search-box {
        max-width: 100%;
      }
    }

    @media (max-width: 576px) {
      .hero-section h1 {
        font-size: 1.5rem;
      }

      .stat-card {
        padding: 18px 16px;
      }

      .stat-value {
        font-size: 1.7rem;
      }

      .program-card h4 {
        font-size: 1.2rem;
        padding-right: 0;
      }

      .program-status {
        position: static;
        display: inline-block;
        margin-bottom: 15px;
      }
    }

    /* ==================== ACHIEVEMENTS GALLERY ==================== */
    .achievements-section {
      padding: 60px 0;
      background: white;
    }

    .achievements-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 22px;
    }

    .ach-card {
      border-radius: 16px;
      overflow: hidden;
      background: #fff;
      border: 1.5px solid var(--border);
      box-shadow: var(--shadow-sm);
      transition: transform 0.28s ease, box-shadow 0.28s ease;
    }

    .ach-card:hover {
      transform: translateY(-6px);
      box-shadow: var(--shadow-lg);
    }

    .ach-img-wrap {
      width: 100%;
      height: 200px;
      overflow: hidden;
      background: var(--pale-blue);
    }

    .ach-img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.4s ease;
    }

    .ach-card:hover .ach-img-wrap img {
      transform: scale(1.05);
    }

    .ach-body {
      padding: 16px 18px 20px;
    }

    .ach-tag {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: var(--pale-blue);
      color: var(--primary-color);
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      padding: 4px 10px;
      border-radius: 50px;
      margin-bottom: 10px;
    }

    .ach-title {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 0.97rem;
      color: var(--dark);
      margin-bottom: 6px;
      line-height: 1.35;
    }

    .ach-caption {
      font-size: 0.83rem;
      color: var(--gray);
      line-height: 1.55;
    }

    @media (max-width: 767px) {
      .achievements-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
      }
      .ach-img-wrap { height: 150px; }
    }

    @media (max-width: 480px) {
      .achievements-grid {
        grid-template-columns: 1fr;
      }
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 40px 20px;
      background: white;
      border-radius: 16px;
      border: 2px dashed var(--border);
    }

    .empty-state i {
      font-size: 3rem;
      color: var(--gray);
      margin-bottom: 16px;
      opacity: 0.5;
    }

    .empty-state h4 {
      color: var(--dark);
      font-weight: 600;
      margin-bottom: 8px;
      font-size: 1.1rem;
    }

    .empty-state p {
      color: var(--gray);
      font-size: 0.9rem;
    }

    /* Values Section */
    .values-section {
      padding: 60px 0;
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      color: white;
    }

    .values-section .section-header h2 {
      color: white;
    }

    .values-section .section-header h2::after {
      background: linear-gradient(90deg, var(--accent-color) 0%, var(--light-blue) 100%);
    }

    .values-section .section-header p {
      color: rgba(255,255,255,0.85);
    }

    .value-card {
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
      border-radius: 16px;
      padding: 30px;
      text-align: center;
      border: 1px solid rgba(255,255,255,0.2);
    }

    .value-icon {
      width: 70px;
      height: 70px;
      background: rgba(255,255,255,0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
    }

    .value-icon i {
      font-size: 2rem;
      color: white;
    }

    .value-card h4 {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      margin-bottom: 10px;
    }

    .value-card p {
      color: rgba(255,255,255,0.85);
      font-size: 0.9rem;
      margin: 0;
    }
  </style>
</head>
<body>

<?php include("partials/navbar.php"); ?>

<!-- SECTION 1: HERO / PAGE HEADER -->
<section class="hero-section">
  <div class="container">
    <div class="hero-content text-center">
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb justify-content-center">
          <li class="breadcrumb-item"><a href="user_home.php" style="color: rgba(255,255,255,0.8); text-decoration: none;">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Transparency & Progress</li>
        </ol>
      </nav>

      <!-- Page Title -->
      <h1>Transparency & Association Progress</h1>
      
      <!-- Subtitle -->
      <p class="subtitle">
        Promoting accountability through transparent reporting of assistance received, programs implemented, 
        and sustainable initiatives that empower our fishing community.
      </p>

      <!-- Last Updated -->
      <div class="last-updated">
        <i class="bi bi-calendar-check"></i>
        Last Updated: <?= htmlspecialchars($stats['last_updated']) ?>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 2: FINANCIAL OVERVIEW (Statistics Dashboard) -->
<section class="stats-section">
  <div class="container">
    <div class="row g-4">
      <!-- Stat 1: Total Assistance Received -->
      <div class="col-lg col-md-6">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="bi bi-cash-stack"></i>
          </div>
          <div class="stat-value"><?= formatCurrency($stats['total_assistance']) ?></div>
          <div class="stat-label">Total Assistance Received</div>
        </div>
      </div>

      <!-- Stat 2: Partner Organizations -->
      <div class="col-lg col-md-6">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="bi bi-building"></i>
          </div>
          <div class="stat-value"><?= number_format($stats['partner_count']) ?></div>
          <div class="stat-label">Partner Organizations</div>
        </div>
      </div>

      <!-- Stat 3: Active Programs -->
      <div class="col-lg col-md-6">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="bi bi-kanban"></i>
          </div>
          <div class="stat-value"><?= number_format($stats['active_programs']) ?></div>
          <div class="stat-label">Active Programs</div>
        </div>
      </div>

      <!-- Stat 4: Funds Utilized -->
      <div class="col-lg col-md-6">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="bi bi-wallet2"></i>
          </div>
          <div class="stat-value"><?= formatCurrency($stats['funds_utilized']) ?></div>
          <div class="stat-label">Funds Utilized</div>
        </div>
      </div>

      <!-- Stat 5: Community Achievements -->
      <div class="col-lg col-md-6">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="bi bi-images"></i>
          </div>
          <div class="stat-value"><?= number_format(count($achievementsList)) ?></div>
          <div class="stat-label">Community Achievements</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 3: ACTIVE PROGRAMS & PROJECTS -->
<section class="programs-section">
  <div class="container">
    <div class="section-header">
      <h2>Programs & Projects</h2>
      <p>Initiatives and livelihood programs that support and empower our fishing community</p>
    </div>

    <?php if ($programsResult && $programsResult->num_rows > 0): ?>
    <div class="row g-4">
      <?php while ($program = $programsResult->fetch_assoc()): ?>
      <div class="col-lg-4 col-md-6">
        <div class="program-card">
          <!-- Status Badge -->
          <span class="program-status <?= getStatusClass($program['status']) ?>">
            <?= htmlspecialchars(ucfirst($program['status'])) ?>
          </span>

          <!-- Program Title -->
          <h4><?= htmlspecialchars($program['program_name']) ?></h4>

          <!-- Program Description -->
          <p><?= htmlspecialchars(substr($program['description'], 0, 120)) ?>...</p>

          <!-- Program Budget -->
          <div class="program-stats">
            <div class="program-stat">
              <div class="program-stat-value"><?= formatCurrency($program['allocated_budget']) ?></div>
              <div class="program-stat-label">Budget Allocated</div>
            </div>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <i class="bi bi-kanban"></i>
      <h4>No Active Programs</h4>
      <p>There are currently no active programs. Please check back later for updates.</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- SECTION 4: COMMUNITY ACHIEVEMENTS GALLERY -->
<section class="achievements-section">
  <div class="container">
    <div class="section-header">
      <h2>Community Achievements</h2>
      <p>Products, outcomes, and milestones from our livelihood programs — grown and made by our fisherfolk community</p>
    </div>

    <?php if (!empty($achievementsList)): ?>
    <div class="achievements-grid">
      <?php foreach ($achievementsList as $ach): ?>
      <div class="ach-card">
        <div class="ach-img-wrap">
          <img src="../../<?= htmlspecialchars($ach['image_path']) ?>"
               alt="<?= htmlspecialchars($ach['title']) ?>"
               onerror="this.parentElement.style.background='#e8f6f8'; this.style.display='none';">
        </div>
        <div class="ach-body">
          <?php if (!empty($ach['tag'])): ?>
          <span class="ach-tag"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($ach['tag']) ?></span>
          <?php endif; ?>
          <div class="ach-title"><?= htmlspecialchars($ach['title']) ?></div>
          <?php if (!empty($ach['caption'])): ?>
          <div class="ach-caption"><?= htmlspecialchars($ach['caption']) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <i class="bi bi-images"></i>
      <h4>Coming Soon</h4>
      <p>Photos of community products and achievements will be shared here.</p>
    </div>
    <?php endif; ?>
  </div>
</section>


<!-- SECTION 5: CORE VALUES -->
<section class="values-section">
  <div class="container">
    <div class="section-header">
      <h2>Our Core Values</h2>
      <p>The principles that guide our association from leadership to membership</p>
    </div>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="value-card">
          <div class="value-icon">
            <i class="bi bi-eye"></i>
          </div>
          <h4>Transparency</h4>
          <p>Open and honest reporting of all assistance received and how funds are utilized for the benefit of our community.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="value-card">
          <div class="value-icon">
            <i class="bi bi-shield-check"></i>
          </div>
          <h4>Discipline</h4>
          <p>Committed to proper documentation, accountability, and responsible management of all resources entrusted to us.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="value-card">
          <div class="value-icon">
            <i class="bi bi-heart"></i>
          </div>
          <h4>Honesty</h4>
          <p>Integrity in all our dealings, from the chairman to every member, ensuring trust within our association.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 6: RECENT ASSISTANCE RECEIVED -->
<section class="assistance-section">
  <div class="container">
    <div class="section-header">
      <h2>Recent Assistance Received</h2>
      <p>Support from government agencies, NGOs, and partners that help sustain our programs</p>
    </div>

    <!-- Table Controls -->
    <div class="table-controls">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="searchInput" placeholder="Search by source or type..." />
      </div>
    </div>

    <!-- Assistance Table -->
    <?php if ($assistanceResult && $assistanceResult->num_rows > 0): ?>
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Source</th>
            <th>Type</th>
            <th>Amount/Description</th>
            <th>Date Received</th>
          </tr>
        </thead>
        <tbody id="assistanceTableBody">
          <?php while ($assistance = $assistanceResult->fetch_assoc()): ?>
          <tr>
            <td>
              <span class="source-name"><?= htmlspecialchars($assistance['source_name']) ?></span>
            </td>
            <td>
              <span class="source-type-badge OTHER">
                Support/Assistance
              </span>
            </td>
            <td>
              <span class="amount-cell"><?= formatCurrency($assistance['amount']) ?></span>
            </td>
            <td><?= date('M d, Y', strtotime($assistance['date_received'])) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <i class="bi bi-inbox"></i>
      <h4>No Records Yet</h4>
      <p>No assistance records available at the moment.</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<div style="height: 60px;"></div>

<?php include("partials/footer.php"); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Search Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('searchInput');
  const tableBody = document.getElementById('assistanceTableBody');
  
  if (searchInput && tableBody) {
    const rows = Array.from(tableBody.querySelectorAll('tr'));
    
    searchInput.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase().trim();
      
      rows.forEach(row => {
        const sourceName = row.cells[0].textContent.toLowerCase();
        const sourceType = row.cells[1].textContent.toLowerCase();
        
        if (sourceName.includes(searchTerm) || sourceType.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  }

  // Animate stat values on scroll
  const statValues = document.querySelectorAll('.stat-value');
  statValues.forEach((stat, index) => {
    stat.style.opacity = '0';
    stat.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
      stat.style.transition = 'all 0.6s ease';
      stat.style.opacity = '1';
      stat.style.transform = 'translateY(0)';
    }, index * 100);
  });
});
</script>

</body>
</html>
