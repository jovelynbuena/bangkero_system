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

// Fetch beneficiaries / impact stories
$beneficiariesQuery = "SELECT
    b.id,
    b.name,
    b.assistance_type,
    b.amount_value,
    b.quantity,
    b.date_assisted,
    b.barangay,
    b.municipality,
    b.status,
    b.short_story,
    b.featured,
    c.name AS program_name
    FROM transparency_beneficiaries b
    LEFT JOIN transparency_campaigns c ON b.program_id = c.id
    ORDER BY b.featured DESC, b.date_assisted DESC
    LIMIT 12";
$beneficiariesResult = $conn->query($beneficiariesQuery);

// Stats for beneficiaries — includes all records regardless of status
$benStatsResult = $conn->query(
    "SELECT COUNT(*) AS total_ben, COALESCE(SUM(amount_value), 0) AS total_val FROM transparency_beneficiaries"
);
$benStats = $benStatsResult ? $benStatsResult->fetch_assoc() : ['total_ben' => 0, 'total_val' => 0];

// Combined grand total: donations + beneficiary amounts
$grandTotalResult = $conn->query(
    "SELECT
        (SELECT COALESCE(SUM(amount), 0) FROM transparency_donations WHERE status = 'confirmed')
        + (SELECT COALESCE(SUM(amount_value), 0) FROM transparency_beneficiaries)
        AS grand_total"
);
$grandTotal = $grandTotalResult ? (float)$grandTotalResult->fetch_assoc()['grand_total'] : 0;

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
      border-color: var(--accent-color);
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
      color: var(--accent-color);
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

    /* ==================== BENEFICIARIES SECTION ==================== */
    .beneficiaries-section {
      padding: 60px 0;
      background: white;
    }

    /* 2×2 grid wrapper — max 2 columns, center last odd card */
    .ben-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 24px;
    }

    @media (max-width: 767px) {
      .ben-grid {
        grid-template-columns: 1fr;
      }
    }

    /* Center last card when odd total */
    .ben-grid-item:last-child:nth-child(odd) {
      grid-column: 1 / -1;
      max-width: calc(50% - 12px);
      margin: 0 auto;
    }

    @media (max-width: 767px) {
      .ben-grid-item:last-child:nth-child(odd) {
        grid-column: unset;
        max-width: 100%;
        margin: 0;
      }
    }

    .ben-card {
      background: #fff;
      border: 1.5px solid var(--border);
      border-radius: 14px;
      padding: 22px 24px;
      display: flex;
      flex-direction: column;
      transition: box-shadow 0.25s ease, transform 0.25s ease;
      position: relative;
      overflow: hidden;
    }

    .ben-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 100%;
      height: 3px;
      border-radius: 14px 14px 0 0;
    }

    /* Top-border color per category */
    .ben-card.cat-financial::before   { background: #1565c0; }
    .ben-card.cat-relief::before      { background: #2e7d32; }
    .ben-card.cat-educational::before { background: #7b1fa2; }
    .ben-card.cat-training::before    { background: #ef6c00; }
    .ben-card.cat-livelihood::before  { background: #c2185b; }
    .ben-card.cat-default::before     { background: var(--accent-color); }

    .ben-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg);
    }

    /* Category badge */
    .ben-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 4px 11px;
      border-radius: 50px;
      font-size: 0.73rem;
      font-weight: 700;
      letter-spacing: 0.3px;
      text-transform: uppercase;
      margin-bottom: 14px;
      width: fit-content;
    }

    .ben-badge.cat-financial   { background: #e3f2fd; color: #1565c0; }
    .ben-badge.cat-relief      { background: #e8f5e9; color: #2e7d32; }
    .ben-badge.cat-educational { background: #f3e5f5; color: #7b1fa2; }
    .ben-badge.cat-training    { background: #fff3e0; color: #ef6c00; }
    .ben-badge.cat-livelihood  { background: #fce4ec; color: #c2185b; }
    .ben-badge.cat-default     { background: var(--pale-blue); color: var(--primary-color); }

    .ben-name {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 1.05rem;
      color: var(--dark);
      margin-bottom: 3px;
    }

    .ben-location {
      font-size: 0.8rem;
      color: var(--gray);
      display: flex;
      align-items: center;
      gap: 4px;
      margin-bottom: 10px;
    }

    .ben-program-tag {
      font-size: 0.78rem;
      color: var(--accent-color);
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .ben-description {
      font-size: 0.87rem;
      color: var(--gray);
      line-height: 1.65;
      flex: 1;
      margin-bottom: 16px;
    }

    .ben-description em {
      display: block;
      font-style: italic;
      color: #7f8c8d;
      margin-top: 6px;
      padding-left: 10px;
      border-left: 2px solid var(--border);
    }

    .ben-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 12px;
      border-top: 1px solid var(--border);
      gap: 8px;
      flex-wrap: wrap;
    }

    .ben-amount {
      font-weight: 700;
      color: var(--primary-color);
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
    }

    .ben-date {
      font-size: 0.76rem;
      color: var(--gray);
    }

    .ben-status-pill {
      font-size: 0.71rem;
      font-weight: 600;
      padding: 3px 10px;
      border-radius: 50px;
      text-transform: capitalize;
      white-space: nowrap;
    }

    .ben-status-pill.served       { background: #d5f4e6; color: #1a8a50; }
    .ben-status-pill.in-progress  { background: #fff8e1; color: #d48806; }
    .ben-status-pill.pending      { background: #fde8e8; color: #c0392b; }

    /* ==================== BENEFICIARY SUMMARY BAR ==================== */
    .ben-summary-bar {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      border-radius: 14px;
      padding: 22px 32px;
      color: white;
      display: flex;
      justify-content: space-around;
      align-items: center;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 40px;
    }

    .ben-summary-item {
      text-align: center;
    }

    .ben-summary-value {
      font-family: 'Poppins', sans-serif;
      font-size: 1.9rem;
      font-weight: 800;
      line-height: 1;
      margin-bottom: 4px;
    }

    .ben-summary-label {
      font-size: 0.78rem;
      font-weight: 500;
      opacity: 0.85;
      text-transform: uppercase;
      letter-spacing: 0.6px;
    }

    .ben-summary-divider {
      width: 1px;
      height: 40px;
      background: rgba(255,255,255,0.25);
    }

    @media (max-width: 576px) {
      .ben-summary-divider { display: none; }
      .ben-summary-bar { padding: 18px 20px; }
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
          <div class="stat-value"><?= formatCurrency($grandTotal) ?></div>
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

      <!-- Stat 5: Total Beneficiaries -->
      <div class="col-lg col-md-6">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="bi bi-people-fill"></i>
          </div>
          <div class="stat-value"><?= number_format($benStats['total_ben']) ?></div>
          <div class="stat-label">Beneficiaries Served</div>
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

<!-- SECTION 4: BENEFICIARIES & IMPACT STORIES -->
<section class="beneficiaries-section">
  <div class="container">
    <div class="section-header">
      <h2>Beneficiaries & Impact</h2>
      <p>Community members and fisherfolk who received assistance through our programs</p>
    </div>

    <!-- Summary Bar -->
    <?php
      $distinctBarangayResult = $conn->query("SELECT COUNT(DISTINCT barangay) AS cnt FROM transparency_beneficiaries WHERE barangay != ''");
      $barangayCount = $distinctBarangayResult ? (int)$distinctBarangayResult->fetch_assoc()['cnt'] : 0;
    ?>
    <div class="ben-summary-bar">
      <div class="ben-summary-item">
        <div class="ben-summary-value"><?= number_format($benStats['total_ben']) ?></div>
        <div class="ben-summary-label">Total Beneficiaries</div>
      </div>
      <div class="ben-summary-divider"></div>
      <div class="ben-summary-item">
        <div class="ben-summary-value"><?= '₱' . number_format($benStats['total_val'], 0) ?></div>
        <div class="ben-summary-label">Total Assistance Value</div>
      </div>
      <div class="ben-summary-divider"></div>
      <div class="ben-summary-item">
        <div class="ben-summary-value"><?= number_format($barangayCount) ?></div>
        <div class="ben-summary-label">Barangays Reached</div>
      </div>
    </div>

    <?php
    // Map assistance type to CSS category class
    function getBenCatClass(string $type): string {
      $t = strtolower($type);
      if (str_contains($t, 'financial'))                      return 'cat-financial';
      if (str_contains($t, 'relief') || str_contains($t, 'goods')) return 'cat-relief';
      if (str_contains($t, 'educ'))                           return 'cat-educational';
      if (str_contains($t, 'train'))                          return 'cat-training';
      if (str_contains($t, 'livelihood'))                     return 'cat-livelihood';
      return 'cat-default';
    }

    // Map category to Bootstrap icon
    function getBenCatIcon(string $type): string {
      $t = strtolower($type);
      if (str_contains($t, 'financial'))                      return 'bi-cash-coin';
      if (str_contains($t, 'relief') || str_contains($t, 'goods')) return 'bi-box-seam';
      if (str_contains($t, 'educ'))                           return 'bi-mortarboard';
      if (str_contains($t, 'train'))                          return 'bi-person-workspace';
      if (str_contains($t, 'livelihood'))                     return 'bi-tools';
      return 'bi-gift';
    }

    // Build a short description: "Type assistance via [Program]" + story excerpt
    function buildBenDescription(array $ben): string {
      $parts = [];
      $type    = htmlspecialchars($ben['assistance_type'] ?? '');
      $program = htmlspecialchars($ben['program_name'] ?? '');
      $qty     = (int)($ben['quantity'] ?? 0);

      if ($type) {
        $line = $type . ' assistance';
        if ($qty > 0) $line .= ' (' . $qty . ' unit' . ($qty > 1 ? 's' : '') . ')';
        if ($program) $line .= ' under <strong>' . $program . '</strong>';
        $parts[] = $line . '.';
      } elseif ($program) {
        $parts[] = 'Assisted under <strong>' . $program . '</strong>.';
      }

      $story = trim($ben['short_story'] ?? '');
      if ($story !== '') {
        $excerpt = htmlspecialchars(mb_substr($story, 0, 110));
        if (mb_strlen($story) > 110) $excerpt .= '&hellip;';
        $parts[] = '<em>&ldquo;' . $excerpt . '&rdquo;</em>';
      }

      return implode(' ', $parts);
    }
    ?>

    <?php if ($beneficiariesResult && $beneficiariesResult->num_rows > 0): ?>
    <div class="ben-grid">
      <?php while ($ben = $beneficiariesResult->fetch_assoc()):
        $catClass   = getBenCatClass($ben['assistance_type'] ?? '');
        $catIcon    = getBenCatIcon($ben['assistance_type'] ?? '');
        $statusSlug = strtolower(str_replace([' ', '_'], '-', $ben['status'] ?? ''));
        $desc       = buildBenDescription($ben);
      ?>
      <div class="ben-grid-item">
        <div class="ben-card <?= $catClass ?>">

          <!-- Category Badge -->
          <span class="ben-badge <?= $catClass ?>">
            <i class="bi <?= $catIcon ?>"></i>
            <?= htmlspecialchars($ben['assistance_type'] ?? 'Assistance') ?>
          </span>

          <!-- Name -->
          <div class="ben-name"><?= htmlspecialchars($ben['name']) ?></div>

          <!-- Location -->
          <?php if (!empty($ben['barangay'])): ?>
          <div class="ben-location">
            <i class="bi bi-geo-alt"></i>
            <?= htmlspecialchars($ben['barangay']) ?>
            <?= !empty($ben['municipality']) ? ', ' . htmlspecialchars($ben['municipality']) : '' ?>
          </div>
          <?php endif; ?>

          <!-- Description (type + program + story) -->
          <?php if ($desc): ?>
          <div class="ben-description"><?= $desc ?></div>
          <?php endif; ?>

          <!-- Footer: amount, date, status -->
          <div class="ben-footer">
            <div>
              <?php if ($ben['amount_value'] > 0): ?>
              <div class="ben-amount"><?= '₱' . number_format($ben['amount_value'], 2) ?></div>
              <?php endif; ?>
              <?php if (!empty($ben['date_assisted'])): ?>
              <div class="ben-date"><?= date('M d, Y', strtotime($ben['date_assisted'])) ?></div>
              <?php endif; ?>
            </div>
            <?php if (!empty($ben['status'])): ?>
            <span class="ben-status-pill <?= $statusSlug ?>">
              <?= htmlspecialchars(ucfirst($ben['status'])) ?>
            </span>
            <?php endif; ?>
          </div>

        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <i class="bi bi-people"></i>
      <h4>No Beneficiary Records Yet</h4>
      <p>Beneficiary and impact information will appear here once records are available.</p>
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
