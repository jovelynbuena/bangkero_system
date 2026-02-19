<?php
session_start();
require_once('../../config/db_connect.php');

// Fetch financial summary statistics
$stats = [
    'total_donations' => 0,
    'total_donors' => 0,
    'active_campaigns' => 0,
    'funds_allocated' => 0,
    'remaining_funds' => 0,
    'last_updated' => date('F Y')
];

// Get total donations and total donors
$donationQuery = "SELECT 
    COALESCE(SUM(amount), 0) AS total_donations,
    COUNT(DISTINCT donor_name) AS total_donors,
    MAX(date_received) AS last_update
    FROM transparency_donations
    WHERE status = 'confirmed'";
$result = $conn->query($donationQuery);
if ($result && $row = $result->fetch_assoc()) {
    $stats['total_donations'] = $row['total_donations'];
    $stats['total_donors'] = $row['total_donors'];
    if ($row['last_update']) {
        $stats['last_updated'] = date('F Y', strtotime($row['last_update']));
    }
}

// Get active campaigns count
$campaignQuery = "SELECT COUNT(*) AS active_count FROM transparency_campaigns WHERE status = 'active'";
$result = $conn->query($campaignQuery);
if ($result && $row = $result->fetch_assoc()) {
    $stats['active_campaigns'] = $row['active_count'];
}

// Get funds allocated (sum of campaign targets for active campaigns)
$allocatedQuery = "SELECT COALESCE(SUM(allocated_budget), 0) AS allocated
    FROM transparency_programs
    WHERE status IN ('ongoing','completed')";
$result = $conn->query($allocatedQuery);
if ($result && $row = $result->fetch_assoc()) {
    $stats['funds_allocated'] = $row['allocated'];
}

// Calculate remaining funds
$stats['remaining_funds'] = $stats['total_donations'] - $stats['funds_allocated'];

// Fetch active campaigns
$campaignsQuery = "SELECT 
    c.id,
    c.name AS campaign_name,
    c.description,
    c.goal_amount,
    COALESCE(SUM(d.amount), 0) AS current_amount,
    c.status,
    c.created_at
    FROM transparency_campaigns c
    LEFT JOIN transparency_donations d
        ON d.campaign_id = c.id
        AND d.status = 'confirmed'
    WHERE c.status IN ('active', 'completed')
    GROUP BY c.id, c.name, c.description, c.goal_amount, c.status, c.created_at
    ORDER BY c.status ASC, c.created_at DESC 
    LIMIT 6";
$campaignsResult = $conn->query($campaignsQuery);

// Fetch recent donations (limit 10)
$donationsQuery = "SELECT 
    d.donor_name,
    COALESCE(c.name, 'General Fund') AS campaign_name,
    d.amount,
    d.date_received,
    d.status
    FROM transparency_donations d
    LEFT JOIN transparency_campaigns c ON d.campaign_id = c.id
    ORDER BY d.date_received DESC 
    LIMIT 10";
$donationsResult = $conn->query($donationsQuery);

// Helper function to format currency
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// Helper function to calculate percentage
function calculatePercentage($current, $goal) {
    if ($goal <= 0) return 0;
    $percentage = ($current / $goal) * 100;
    return min($percentage, 100); // Cap at 100%
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Transparency & Community Impact - Bankero & Fishermen Association</title>
  
  <!-- Bootstrap CSS & Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  
  <style>
    :root {
      /* Primary Theme Colors - Navy Blue */
      --primary-color: #2c3e50;
      --secondary-color: #34495e;
      --accent-color: #3498db;
      --light-blue: #5dade2;
      --pale-blue: #ebf5fb;
      
      /* Supporting Colors */
      --dark: #1a252f;
      --gray: #6c757d;
      --light-gray: #ecf0f1;
      --white: #ffffff;
      --border: #d5dbdb;
      
      /* Shadows */
      --shadow-sm: 0 2px 8px rgba(44, 62, 80, 0.08);
      --shadow-md: 0 4px 16px rgba(44, 62, 80, 0.12);
      --shadow-lg: 0 8px 32px rgba(44, 62, 80, 0.16);
    }

    * {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--light-gray);
      color: var(--primary-color);
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
      border-color: var(--accent-color);
    }

    .stat-icon {
      width: 55px;
      height: 55px;
      border-radius: 14px;
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 16px;
      box-shadow: 0 4px 12px rgba(44, 62, 80, 0.25);
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

    /* ==================== CAMPAIGNS SECTION ==================== */
    .campaigns-section {
      padding: 60px 0;
      background: white;
    }

    .campaign-card {
      background: white;
      border: 2px solid var(--border);
      border-radius: 16px;
      padding: 24px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      height: 100%;
      position: relative;
      overflow: hidden;
    }

    .campaign-card::before {
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

    .campaign-card:hover::before {
      transform: scaleX(1);
    }

    .campaign-card:hover {
      transform: translateY(-6px);
      box-shadow: var(--shadow-lg);
      border-color: var(--accent-color);
    }

    .campaign-status {
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

    .campaign-status.active {
      background: var(--pale-blue);
      color: var(--primary-color);
    }

    .campaign-status.completed {
      background: #d5f4e6;
      color: #27ae60;
    }

    .campaign-card h4 {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      color: var(--dark);
      font-size: 1.2rem;
      margin-bottom: 12px;
      padding-right: 90px;
    }

    .campaign-card p {
      color: var(--gray);
      line-height: 1.6;
      margin-bottom: 18px;
      font-size: 0.9rem;
    }

    .campaign-stats {
      display: flex;
      justify-content: space-between;
      margin-bottom: 12px;
    }

    .campaign-stat {
      text-align: center;
    }

    .campaign-stat-value {
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--primary-color);
      font-family: 'Poppins', sans-serif;
    }

    .campaign-stat-label {
      font-size: 0.75rem;
      color: var(--gray);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .progress-container {
      margin-top: 16px;
    }

    .progress-header {
      display: flex;
      justify-content: space-between;
      margin-bottom: 8px;
      font-size: 0.85rem;
    }

    .progress-label {
      color: var(--gray);
      font-weight: 600;
    }

    .progress-percentage {
      color: var(--accent-color);
      font-weight: 700;
    }

    .progress {
      height: 10px;
      border-radius: 50px;
      background-color: #e9ecef;
      overflow: hidden;
      box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
    }

    .progress-bar {
      background: linear-gradient(90deg, var(--primary-color) 0%, var(--accent-color) 100%);
      border-radius: 50px;
      transition: width 1s ease-in-out;
      box-shadow: 0 2px 6px rgba(44, 62, 80, 0.3);
    }

    /* ==================== DONATIONS TABLE ==================== */
    .donations-section {
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
      border-color: var(--accent-color);
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
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

    .donor-name {
      font-weight: 600;
      color: var(--primary-color);
    }

    .amount-cell {
      font-weight: 700;
      color: var(--accent-color);
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
    }

    .status-badge {
      padding: 6px 14px;
      border-radius: 50px;
      font-size: 0.8rem;
      font-weight: 600;
      display: inline-block;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }

    .status-badge.completed,
    .status-badge.confirmed {
      background: var(--pale-blue);
      color: var(--primary-color);
    }

    .status-badge.pending {
      background: #fff3cd;
      color: #856404;
    }

    .pagination-container {
      display: flex;
      justify-content: center;
      padding: 25px;
      background: white;
      border-top: 2px solid var(--border);
    }

    .pagination {
      display: flex;
      gap: 8px;
      margin: 0;
    }

    .page-btn {
      padding: 10px 18px;
      border: 2px solid var(--border);
      background: white;
      color: var(--dark);
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .page-btn:hover {
      background: var(--accent-color);
      color: white;
      border-color: var(--accent-color);
      transform: translateY(-2px);
    }

    .page-btn.active {
      background: var(--accent-color);
      color: white;
      border-color: var(--accent-color);
    }

    .btn-view-full {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
      color: white;
      padding: 14px 32px;
      border-radius: 50px;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(44, 62, 80, 0.3);
      border: none;
      cursor: pointer;
    }

    .btn-view-full:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(44, 62, 80, 0.4);
      color: white;
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

      .campaign-card {
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

      .campaign-card h4 {
        font-size: 1.2rem;
        padding-right: 0;
      }

      .campaign-status {
        position: static;
        display: inline-block;
        margin-bottom: 15px;
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
          <li class="breadcrumb-item active" aria-current="page">Transparency & Impact</li>
        </ol>
      </nav>

      <!-- Page Title -->
      <h1>Transparency & Community Impact Report</h1>
      
      <!-- Subtitle -->
      <p class="subtitle">
        Promoting accountability through transparent reporting of donations, programs, and community assistance initiatives.
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
      <!-- Stat 1: Total Donations -->
      <div class="col-lg col-md-6">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="bi bi-cash-stack"></i>
          </div>
          <div class="stat-value"><?= formatCurrency($stats['total_donations']) ?></div>
          <div class="stat-label">Total Donations Received</div>
        </div>
      </div>

      <!-- Stat 2: Total Donors -->
      <div class="col-lg col-md-6">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="bi bi-people-fill"></i>
          </div>
          <div class="stat-value"><?= number_format($stats['total_donors']) ?></div>
          <div class="stat-label">Total Donors</div>
        </div>
      </div>

      <!-- Stat 3: Active Campaigns -->
      <div class="col-lg col-md-6">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="bi bi-megaphone-fill"></i>
          </div>
          <div class="stat-value"><?= number_format($stats['active_campaigns']) ?></div>
          <div class="stat-label">Active Campaigns</div>
        </div>
      </div>

      <!-- Stat 4: Funds Allocated -->
      <div class="col-lg col-md-6">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="bi bi-wallet2"></i>
          </div>
          <div class="stat-value"><?= formatCurrency($stats['funds_allocated']) ?></div>
          <div class="stat-label">Funds Allocated</div>
        </div>
      </div>

      <!-- Stat 5: Remaining Funds -->
      <div class="col-lg col-md-6">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="bi bi-piggy-bank-fill"></i>
          </div>
          <div class="stat-value"><?= formatCurrency($stats['remaining_funds']) ?></div>
          <div class="stat-label">Remaining Funds</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 3: ACTIVE FUNDRAISING CAMPAIGNS -->
<section class="campaigns-section">
  <div class="container">
    <div class="section-header">
      <h2>Active Fundraising Campaigns</h2>
      <p>Support our initiatives and help us make a difference in the fishing community</p>
    </div>

    <?php if ($campaignsResult && $campaignsResult->num_rows > 0): ?>
    <div class="row g-4">
      <?php while ($campaign = $campaignsResult->fetch_assoc()): 
        $percentage = calculatePercentage($campaign['current_amount'], $campaign['goal_amount']);
      ?>
      <div class="col-lg-4 col-md-6">
        <div class="campaign-card">
          <!-- Status Badge -->
          <span class="campaign-status <?= strtolower($campaign['status']) ?>">
            <?= htmlspecialchars($campaign['status']) ?>
          </span>

          <!-- Campaign Title -->
          <h4><?= htmlspecialchars($campaign['campaign_name']) ?></h4>

          <!-- Campaign Description -->
          <p><?= htmlspecialchars(substr($campaign['description'], 0, 120)) ?>...</p>

          <!-- Campaign Stats -->
          <div class="campaign-stats">
            <div class="campaign-stat">
              <div class="campaign-stat-value"><?= formatCurrency($campaign['goal_amount']) ?></div>
              <div class="campaign-stat-label">Goal</div>
            </div>
            <div class="campaign-stat">
              <div class="campaign-stat-value"><?= formatCurrency($campaign['current_amount']) ?></div>
              <div class="campaign-stat-label">Raised</div>
            </div>
          </div>

          <!-- Progress Bar -->
          <div class="progress-container">
            <div class="progress-header">
              <span class="progress-label">Progress</span>
              <span class="progress-percentage"><?= number_format($percentage, 1) ?>%</span>
            </div>
            <div class="progress">
              <div class="progress-bar" role="progressbar" 
                   style="width: <?= $percentage ?>%"
                   aria-valuenow="<?= $percentage ?>" 
                   aria-valuemin="0" 
                   aria-valuemax="100">
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <i class="bi bi-megaphone"></i>
      <h4>No Active Campaigns</h4>
      <p>There are currently no active fundraising campaigns. Please check back later.</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- SECTION 4: RECENT DONATIONS TABLE -->
<section class="donations-section">
  <div class="container">
    <div class="section-header">
      <h2>Recent Donations</h2>
      <p>Latest contributions from our generous donors and supporters</p>
    </div>

    <!-- Table Controls -->
    <div class="table-controls">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="searchInput" placeholder="Search by donor name or campaign..." />
      </div>
      <button class="btn-view-full" onclick="window.location.href='donations-full.php'">
        <i class="bi bi-file-earmark-text"></i> View Full Donation Records
      </button>
    </div>

    <!-- Donations Table -->
    <?php if ($donationsResult && $donationsResult->num_rows > 0): ?>
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Donor Name</th>
            <th>Campaign</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="donationsTableBody">
          <?php while ($donation = $donationsResult->fetch_assoc()): ?>
          <tr>
            <td>
              <span class="donor-name"><?= htmlspecialchars($donation['donor_name']) ?></span>
            </td>
            <td><?= htmlspecialchars($donation['campaign_name']) ?></td>
            <td>
              <span class="amount-cell"><?= formatCurrency($donation['amount']) ?></span>
            </td>
            <td><?= date('M d, Y', strtotime($donation['date_received'])) ?></td>
            <td>
              <span class="status-badge <?= strtolower($donation['status']) ?>">
                <?= htmlspecialchars($donation['status']) ?>
              </span>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="pagination-container">
        <div class="pagination">
          <button class="page-btn"><i class="bi bi-chevron-left"></i></button>
          <button class="page-btn active">1</button>
          <button class="page-btn">2</button>
          <button class="page-btn">3</button>
          <button class="page-btn"><i class="bi bi-chevron-right"></i></button>
        </div>
      </div>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <i class="bi bi-inbox"></i>
      <h4>No Donations Yet</h4>
      <p>No donation records available at the moment.</p>
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
  const tableBody = document.getElementById('donationsTableBody');
  
  if (searchInput && tableBody) {
    const rows = Array.from(tableBody.querySelectorAll('tr'));
    
    searchInput.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase().trim();
      
      rows.forEach(row => {
        const donorName = row.cells[0].textContent.toLowerCase();
        const campaign = row.cells[1].textContent.toLowerCase();
        
        if (donorName.includes(searchTerm) || campaign.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  }

  // Animate progress bars on scroll
  const progressBars = document.querySelectorAll('.progress-bar');
  const observerOptions = {
    threshold: 0.5
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.width = entry.target.getAttribute('style').match(/width:\s*([^;]+)/)[1];
      }
    });
  }, observerOptions);

  progressBars.forEach(bar => {
    const targetWidth = bar.style.width;
    bar.style.width = '0%';
    observer.observe(bar);
  });

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
