<?php
session_start();
require_once('../../config/db_connect.php');

// Load transparency hero settings
$hero_settings = [
    'title'    => 'Transparency & Association Progress',
    'subtitle' => 'Promoting accountability through transparent reporting of assistance received, programs implemented, and sustainable initiatives that empower our fishing community.',
    'bg_image' => ''
];
$hsRes = $conn->query("SELECT setting_key, setting_value FROM transparency_hero_settings");
while ($hsRes && $hsRow = $hsRes->fetch_assoc()) {
    $hero_settings[$hsRow['setting_key']] = $hsRow['setting_value'];
}

// Ensure images table exists before querying
$conn->query("CREATE TABLE IF NOT EXISTS transparency_donation_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    donation_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_donation_id (donation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Fetch financial summary statistics
$stats = [
    'total_assistance' => 0,
    'partner_count' => 0,
    'last_updated' => date('F Y')
];

$result = $conn->query("SELECT 
    COALESCE(SUM(amount), 0) AS total_assistance,
    COUNT(DISTINCT donor_name) AS partner_count,
    MAX(date_received) AS last_update
    FROM transparency_donations
    WHERE status = 'confirmed'");
if ($result && $row = $result->fetch_assoc()) {
    $stats['total_assistance'] = $row['total_assistance'];
    $stats['partner_count'] = $row['partner_count'];
    if ($row['last_update']) {
        $stats['last_updated'] = date('F Y', strtotime($row['last_update']));
    }
}

// Fetch recent assistance records with their images
$assistanceList = [];
<<<<<<< HEAD
$aRes = $conn->query("SELECT d.id, d.donor_name, d.donor_type, d.amount, d.date_received, d.notes
=======
$aRes = $conn->query("SELECT d.id, d.donor_name, d.donor_type, d.donation_type, d.amount, d.date_received, d.notes
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    FROM transparency_donations d
    WHERE d.status = 'confirmed'
    ORDER BY d.date_received DESC
    LIMIT 20");
while ($aRes && $aRow = $aRes->fetch_assoc()) {
    $aRow['images'] = [];
<<<<<<< HEAD
=======
    $aRow['items']  = [];
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    $assistanceList[$aRow['id']] = $aRow;
}
if (!empty($assistanceList)) {
    $ids = implode(',', array_keys($assistanceList));
    $imgRes = $conn->query("SELECT * FROM transparency_donation_images WHERE donation_id IN ($ids) ORDER BY sort_order, id");
    while ($imgRes && $imgRow = $imgRes->fetch_assoc()) {
        $assistanceList[$imgRow['donation_id']]['images'][] = $imgRow;
    }
<<<<<<< HEAD
=======
    // Fetch in-kind items
    try {
        $itmRes = $conn->query("SELECT * FROM transparency_donation_items WHERE donation_id IN ($ids) ORDER BY id");
        while ($itmRes && $itmRow = $itmRes->fetch_assoc()) {
            $assistanceList[$itmRow['donation_id']]['items'][] = $itmRow;
        }
    } catch (Throwable) {}
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
}
$assistanceList = array_values($assistanceList);

// Fetch community achievements with all their images
$achievementsList = [];
$achRes = $conn->query("SELECT * FROM community_achievements WHERE is_active = 1 ORDER BY sort_order ASC, created_at DESC");
while ($achRes && $achRow = $achRes->fetch_assoc()) {
    $achRow['images'] = [];
    $achievementsList[$achRow['id']] = $achRow;
}
if (!empty($achievementsList)) {
    $achIds = implode(',', array_keys($achievementsList));
    // Ensure images table exists before querying
    $conn->query("CREATE TABLE IF NOT EXISTS community_achievement_images (
        id INT PRIMARY KEY AUTO_INCREMENT,
        achievement_id INT NOT NULL,
        image_path VARCHAR(500) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_achievement_id (achievement_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $achImgRes = $conn->query("SELECT * FROM community_achievement_images WHERE achievement_id IN ($achIds) ORDER BY sort_order, id");
    while ($achImgRes && $achImgRow = $achImgRes->fetch_assoc()) {
        $achievementsList[$achImgRow['achievement_id']]['images'][] = $achImgRow;
    }
}
$achievementsList = array_values($achievementsList);

// Helper function to format currency
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// Helper: source type badge label & color class
function getSourceTypeBadge($type) {
    $map = [
        'DOLE'       => ['label' => 'DOLE',       'class' => 'DOLE'],
        'LGU'        => ['label' => 'LGU',         'class' => 'LGU'],
        'NGO'        => ['label' => 'NGO',         'class' => 'NGO'],
        'Private'    => ['label' => 'Private',     'class' => 'PRIVATE'],
        'Membership' => ['label' => 'Membership',  'class' => 'MEMBERSHIP'],
        'Others'     => ['label' => 'Others',      'class' => 'OTHER'],
    ];
    $key = ucfirst(strtolower($type));
    // Try exact match first, then case-insensitive
    foreach ($map as $k => $v) {
        if (strcasecmp($k, $type) === 0) return $v;
    }
    return ['label' => $type ?: 'Other', 'class' => 'OTHER'];
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
      background: linear-gradient(150deg, #1a6b8a 0%, #1B4F72 55%, #12344d 100%);
      color: white;
      padding: 90px 0 110px;
      position: relative;
      overflow: hidden;
    }

    /* Subtle dot-grid texture */
    .hero-section::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px);
      background-size: 28px 28px;
      pointer-events: none;
    }

    /* Floating decorative blobs */
    .hero-blob {
      position: absolute;
      border-radius: 50%;
      filter: blur(60px);
      pointer-events: none;
      opacity: 0.18;
      animation: blobFloat 8s ease-in-out infinite alternate;
    }

    .hero-blob-1 {
      width: 380px; height: 380px;
      background: #A8DADC;
      top: -80px; right: -60px;
      animation-delay: 0s;
    }

    .hero-blob-2 {
      width: 260px; height: 260px;
      background: #F4A261;
      bottom: 0px; left: -40px;
      animation-delay: 3s;
      opacity: 0.12;
    }

    @keyframes blobFloat {
      0%   { transform: translateY(0) scale(1); }
      100% { transform: translateY(-22px) scale(1.05); }
    }

    /* Wave divider at bottom */
    .hero-wave {
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 100%;
      line-height: 0;
      z-index: 2;
    }

    .hero-wave svg {
      display: block;
      width: 100%;
    }

    .hero-content {
      position: relative;
      z-index: 3;
    }

    /* Page label chip above title */
    .hero-chip {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: rgba(255,255,255,0.12);
      border: 1px solid rgba(255,255,255,0.22);
      backdrop-filter: blur(8px);
      padding: 6px 16px;
      border-radius: 50px;
      font-size: 0.78rem;
      font-weight: 600;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      color: rgba(255,255,255,0.9);
      margin-bottom: 20px;
    }

    .hero-chip i { font-size: 0.85rem; color: var(--accent-color); }

    .hero-section h1 {
      font-family: 'Poppins', sans-serif;
      font-size: 2.9rem;
      font-weight: 700;
      margin-bottom: 1rem;
      line-height: 1.2;
      letter-spacing: -0.5px;
    }

    .hero-section h1 span {
      background: linear-gradient(90deg, #A8DADC, #F4A261);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .hero-section .subtitle {
      font-size: 1.05rem;
      font-weight: 400;
      color: rgba(255,255,255,0.8);
      max-width: 680px;
      margin: 0 auto 2.2rem;
      line-height: 1.75;
    }

    /* Breadcrumb — minimal inline text, no pill */
    .breadcrumb {
      background: transparent;
      padding: 0;
      border-radius: 0;
      display: inline-flex;
      margin-bottom: 18px;
      gap: 0;
    }

    .breadcrumb-item {
      color: rgba(255,255,255,0.55);
      font-weight: 500;
      font-size: 0.82rem;
    }

    .breadcrumb-item a { color: rgba(255,255,255,0.55); text-decoration: none; }
    .breadcrumb-item a:hover { color: rgba(255,255,255,0.85); }

    .breadcrumb-item.active {
      color: rgba(255,255,255,0.9);
      font-weight: 600;
    }

    .breadcrumb-item + .breadcrumb-item::before {
      color: rgba(255,255,255,0.35);
      content: "/";
      padding: 0 8px;
    }

    .last-updated {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: rgba(255,255,255,0.1);
      border: 1px solid rgba(255,255,255,0.18);
      padding: 7px 18px;
      border-radius: 50px;
      font-size: 0.82rem;
      font-weight: 500;
      color: rgba(255,255,255,0.82);
    }

    .last-updated i { color: var(--accent-color); }

    /* ==================== STATISTICS CARDS ==================== */
    .stats-section {
      margin-top: -56px;
      padding: 0 0 64px;
      position: relative;
      z-index: 10;
    }

    .stat-card {
      background: white;
      border-radius: 20px;
      padding: 28px 24px 24px;
      box-shadow: 0 8px 30px rgba(46,134,171,0.13);
<<<<<<< HEAD
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid rgba(46,134,171,0.1);
=======
      transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1.5px solid rgba(46,134,171,0.10);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
      height: 100%;
      position: relative;
      overflow: hidden;
      text-align: center;
    }

<<<<<<< HEAD
    /* Soft tinted background circle behind icon */
    .stat-card::after {
      content: '';
      position: absolute;
      top: -20px; right: -20px;
      width: 100px; height: 100px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(46,134,171,0.07) 0%, transparent 70%);
=======
    /* Soft gradient wash per card */
    .stat-card::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(145deg, rgba(46,134,171,0.04) 0%, rgba(255,255,255,0) 60%);
      pointer-events: none;
      border-radius: 20px;
    }

    /* Decorative circle behind icon */
    .stat-card::after {
      content: '';
      position: absolute;
      top: -28px; right: -28px;
      width: 120px; height: 120px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(46,134,171,0.09) 0%, transparent 70%);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
      pointer-events: none;
    }

    .stat-card:hover {
<<<<<<< HEAD
      transform: translateY(-7px);
      box-shadow: 0 20px 50px rgba(46,134,171,0.18);
      border-color: rgba(46,134,171,0.25);
    }

    .stat-icon {
      width: 60px;
      height: 60px;
      border-radius: 16px;
      background: linear-gradient(135deg, rgba(46,134,171,0.12) 0%, rgba(27,79,114,0.1) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
      border: 1.5px solid rgba(46,134,171,0.18);
    }

    .stat-icon i {
      font-size: 1.55rem;
      color: var(--primary-color);
    }

    .stat-value {
      font-size: 2.4rem;
      font-weight: 800;
      color: var(--dark);
      font-family: 'Poppins', sans-serif;
      margin-bottom: 6px;
      line-height: 1;
    }

    .stat-label {
      font-size: 0.8rem;
      color: var(--gray);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      line-height: 1.4;
    }

=======
      transform: translateY(-8px) scale(1.01);
      box-shadow: 0 22px 52px rgba(46,134,171,0.20);
      border-color: rgba(46,134,171,0.28);
    }

    .stat-icon {
      width: 64px;
      height: 64px;
      border-radius: 18px;
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 18px;
      box-shadow: 0 6px 18px rgba(46,134,171,0.30);
      position: relative;
      z-index: 1;
    }

    .stat-icon i {
      font-size: 1.65rem;
      color: #fff;
    }

    .stat-value {
      font-size: 2.5rem;
      font-weight: 800;
      color: var(--dark);
      font-family: 'Poppins', sans-serif;
      margin-bottom: 8px;
      line-height: 1;
      letter-spacing: -1px;
      position: relative;
      z-index: 1;
    }

    .stat-label {
      font-size: 0.78rem;
      color: var(--gray);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.7px;
      line-height: 1.4;
      position: relative;
      z-index: 1;
    }

    /* Thin colored bottom border per card for variety */
    .stat-card.card-total { border-bottom: 3px solid #2E86AB; }
    .stat-card.card-partners { border-bottom: 3px solid #198754; }
    .stat-card.card-achievements { border-bottom: 3px solid #F4A261; }

>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
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
<<<<<<< HEAD
      max-width: 350px;
=======
      max-width: 360px;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
      flex: 1;
    }

    .search-box input {
      width: 100%;
<<<<<<< HEAD
      padding: 12px 20px 12px 45px;
      border: 2px solid var(--border);
      border-radius: 50px;
      font-size: 0.95rem;
      transition: all 0.3s ease;
=======
      padding: 12px 20px 12px 46px;
      border: 2px solid var(--border);
      border-radius: 50px;
      font-size: 0.92rem;
      transition: all 0.3s ease;
      background: #fff;
      color: var(--dark);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    }

    .search-box input:focus {
      outline: none;
      border-color: var(--primary-color);
<<<<<<< HEAD
      box-shadow: 0 0 0 3px rgba(46, 134, 171, 0.15);
=======
      box-shadow: 0 0 0 4px rgba(46, 134, 171, 0.12);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    }

    .search-box i {
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--gray);
<<<<<<< HEAD
      font-size: 1.1rem;
=======
      font-size: 1rem;
      pointer-events: none;
    }

    /* ── Filter chips ── */
    .filter-chips {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      align-items: center;
    }

    .filter-chip {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 5px 14px;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.3px;
      cursor: pointer;
      border: 1.5px solid transparent;
      transition: all 0.2s ease;
      user-select: none;
    }

    .filter-chip.all    { background:#eef2f7; color:#475569; border-color:#cbd5e1; }
    .filter-chip.dole   { background:#dbeafe; color:#1d4ed8; border-color:#bfdbfe; }
    .filter-chip.ngo    { background:#dcfce7; color:#15803d; border-color:#bbf7d0; }
    .filter-chip.lgu    { background:#ede9fe; color:#6d28d9; border-color:#ddd6fe; }
    .filter-chip.others { background:#f1f5f9; color:#475569; border-color:#e2e8f0; }

    .filter-chip:hover,
    .filter-chip.active {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.10);
      filter: brightness(0.95);
    }

    .filter-chip.active {
      font-weight: 800;
      box-shadow: 0 4px 14px rgba(0,0,0,0.13);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
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
<<<<<<< HEAD
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
=======
      padding: 4px 12px;
      border-radius: 50px;
      font-size: 0.72rem;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      letter-spacing: 0.3px;
      border: 1px solid transparent;
    }

    .source-type-badge.DOLE {
      background: #dbeafe;
      color: #1d4ed8;
      border-color: #bfdbfe;
    }

    .source-type-badge.LGU {
      background: #ede9fe;
      color: #6d28d9;
      border-color: #ddd6fe;
    }

    .source-type-badge.NGO {
      background: #dcfce7;
      color: #15803d;
      border-color: #bbf7d0;
    }

    .source-type-badge.PRIVATE {
      background: #ffedd5;
      color: #c2410c;
      border-color: #fed7aa;
    }

    .source-type-badge.MEMBERSHIP {
      background: #fce7f3;
      color: #be185d;
      border-color: #fbcfe8;
    }

    .source-type-badge.OTHER {
      background: #f1f5f9;
      color: #475569;
      border-color: #e2e8f0;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    }

    .amount-cell {
      font-weight: 700;
      color: var(--primary-color);
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
    }

    /* ==================== ASSISTANCE CARDS ==================== */

    .assistance-section {
      background: #f0f5fb;
    }

    /* ── Grid: 2 columns on md+ ── */
    #assistanceGrid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
<<<<<<< HEAD
      gap: 20px;
=======
      gap: 24px;
      align-items: start;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    }

    @media (max-width: 767px) {
      #assistanceGrid { grid-template-columns: 1fr; }
    }

<<<<<<< HEAD
    /* ── Compact card ── */
    .assistance-card {
      background: #fff;
      border-radius: 14px;
      border: 1px solid var(--border);
      box-shadow: 0 4px 18px rgba(46,134,171,0.09);
=======
    /* ── Unified card ── */
    .assistance-card {
      background: #fff;
      border-radius: 16px;
      border: 1px solid var(--border);
      box-shadow: 0 4px 20px rgba(46,134,171,0.10);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
      overflow: hidden;
      display: flex;
      flex-direction: column;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
<<<<<<< HEAD
    }

    .assistance-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 32px rgba(46,134,171,0.16);
=======
      height: 100%;
    }

    .assistance-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 14px 36px rgba(46,134,171,0.17);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    }

    /* ── Top accent bar ── */
    .assistance-card-accent {
      height: 4px;
      background: linear-gradient(90deg, var(--primary-color) 0%, var(--accent-color) 100%);
<<<<<<< HEAD
=======
      flex-shrink: 0;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    }

    /* ── Image strip (only when images exist) ── */
    .assistance-img-strip {
      width: 100%;
<<<<<<< HEAD
      height: 160px;
=======
      height: 190px;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 4px;
      padding: 10px 10px 0;
      box-sizing: border-box;
<<<<<<< HEAD
=======
      flex-shrink: 0;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    }

    .assistance-img-strip.one-img {
      grid-template-columns: 1fr;
    }

    .astrip-primary {
<<<<<<< HEAD
      border-radius: 8px;
=======
      border-radius: 10px;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
      overflow: hidden;
      cursor: pointer;
      position: relative;
    }

    .astrip-primary img {
      width: 100%; height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.3s ease;
    }

    .astrip-primary:hover img { transform: scale(1.04); }

    .astrip-primary-overlay {
      position: absolute; inset: 0;
      background: linear-gradient(to top, rgba(0,0,0,0.55) 0%, transparent 60%);
<<<<<<< HEAD
      border-radius: 8px;
=======
      border-radius: 10px;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
      pointer-events: none;
    }

    .astrip-thumbs {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .astrip-thumb {
      flex: 1;
<<<<<<< HEAD
      border-radius: 8px;
=======
      border-radius: 10px;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
      overflow: hidden;
      cursor: pointer;
    }

    .astrip-thumb img {
      width: 100%; height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.3s ease;
    }

    .astrip-thumb:hover img { transform: scale(1.05); }

<<<<<<< HEAD
    /* ── Cash-only icon banner (no images) ── */
=======
    /* ── No-image placeholder banner (same height as img strip) ── */
    .assistance-no-img-banner {
      width: 100%;
      height: 190px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #eaf4fb 0%, #d8eef8 100%);
      flex-shrink: 0;
    }

    .assistance-no-img-banner .anim-icon {
      width: 72px; height: 72px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 6px 20px rgba(46,134,171,0.30);
    }

    .assistance-no-img-banner .anim-icon i {
      font-size: 2rem;
      color: #fff;
    }

    /* ── Cash-only icon banner (no images) — KEPT for fallback ── */
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    .assistance-cash-banner {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 16px 18px 0;
    }

    .acash-icon {
      width: 48px; height: 48px;
      border-radius: 12px;
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      box-shadow: 0 4px 12px rgba(46,134,171,0.28);
    }

    .acash-icon i {
      font-size: 1.4rem;
      color: #fff;
    }

    .acash-amount-wrap .acash-label {
      font-size: 0.68rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: var(--gray);
    }

    .acash-amount-wrap .acash-value {
      font-family: 'Poppins', sans-serif;
      font-weight: 800;
      font-size: 1.35rem;
      color: var(--primary-color);
      line-height: 1.1;
    }

    .acash-amount-wrap .acash-goods {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 0.95rem;
      color: var(--gray);
      line-height: 1.2;
    }

    /* ── Card body ── */
    .assistance-body {
<<<<<<< HEAD
      padding: 14px 16px 16px;
=======
      padding: 16px 18px 18px;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
      display: flex;
      flex-direction: column;
      gap: 0;
      flex: 1;
    }

    .assistance-body .src-name {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
<<<<<<< HEAD
      font-size: 0.95rem;
      color: var(--dark);
      margin-bottom: 6px;
      line-height: 1.3;
=======
      font-size: 1rem;
      color: var(--dark);
      margin-bottom: 8px;
      line-height: 1.35;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    }

    .assistance-meta {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
<<<<<<< HEAD
      gap: 5px;
      margin-bottom: 10px;
=======
      gap: 6px;
      margin-bottom: 12px;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    }

    /* Amount shown inside body when images are present */
    .assistance-amount {
      font-family: 'Poppins', sans-serif;
      font-weight: 800;
      color: var(--primary-color);
<<<<<<< HEAD
      font-size: 1.05rem;
=======
      font-size: 1.1rem;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
      margin-bottom: 2px;
    }

    .assistance-amount.zero {
      font-size: 0.82rem;
      font-weight: 600;
      color: var(--gray);
    }

    .assistance-divider {
      border: none;
      border-top: 1px solid var(--border);
<<<<<<< HEAD
      margin: 8px 0;
=======
      margin: 10px 0;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    }

    .assistance-detail-row {
      display: flex;
<<<<<<< HEAD
      align-items: flex-start;
      gap: 8px;
      margin-bottom: 4px;
    }

    .assistance-detail-row .det-label {
      font-size: 0.67rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: var(--gray);
      min-width: 38px;
      padding-top: 1px;
    }

    .assistance-detail-row .det-value {
      font-size: 0.8rem;
      color: var(--dark);
      line-height: 1.4;
      opacity: 0.85;
    }

    .assistance-notes-text {
      font-size: 0.77rem;
      color: var(--gray);
      line-height: 1.5;
      margin-top: 4px;
=======
      align-items: center;
      gap: 10px;
      margin-bottom: 5px;
    }

    .assistance-detail-row .det-label {
      font-size: 0.65rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      color: #94a3b8;
      min-width: 36px;
    }

    .assistance-detail-row .det-value {
      font-size: 0.82rem;
      color: var(--dark);
      font-weight: 500;
      line-height: 1.4;
    }

    /* Date pill style */
    .det-date-pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: var(--pale-blue);
      color: var(--primary-color);
      font-size: 0.72rem;
      font-weight: 600;
      padding: 3px 10px;
      border-radius: 20px;
      border: 1px solid var(--border);
    }

    .assistance-notes-text {
      font-size: 0.78rem;
      color: var(--gray);
      line-height: 1.6;
      margin-top: 4px;
      border-left: 3px solid var(--border);
      padding-left: 10px;
      font-style: italic;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    }

    /* Lightbox overlay */
    #assistanceLightbox {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.88);
      z-index: 9999;
      align-items: center;
      justify-content: center;
    }

    #assistanceLightbox.show { display: flex; }

    #assistanceLightbox img {
      max-width: 92vw;
      max-height: 88vh;
      border-radius: 10px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.5);
    }

    #assistanceLightbox .lb-close {
      position: absolute;
      top: 18px;
      right: 24px;
      color: #fff;
      font-size: 2rem;
      cursor: pointer;
      line-height: 1;
    }

    #assistanceLightbox .lb-prev,
    #assistanceLightbox .lb-next {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      color: #fff;
      font-size: 2.5rem;
      cursor: pointer;
      background: rgba(255,255,255,0.1);
      border: none;
      border-radius: 50%;
      width: 52px;
      height: 52px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s;
    }

    #assistanceLightbox .lb-prev:hover,
    #assistanceLightbox .lb-next:hover { background: rgba(255,255,255,0.25); }

    #assistanceLightbox .lb-prev { left: 16px; }
    #assistanceLightbox .lb-next { right: 16px; }

    /* ==================== RESPONSIVE DESIGN ==================== */
    @media (max-width: 991px) {
      .hero-section h1 {
        font-size: 2.1rem;
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
        padding: 60px 0 90px;
      }

      .hero-section h1 {
        font-size: 1.75rem;
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

    /* Outer grid — one card per row, full-width showcase */
    .achievements-grid {
      display: flex;
      flex-direction: column;
      gap: 36px;
    }

    /* Card wrapper */
    .ach-card {
      border-radius: 18px;
      overflow: hidden;
      background: #fff;
      border: 1.5px solid var(--border);
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
      transition: box-shadow 0.3s ease, transform 0.3s ease;
    }

    .ach-card:hover {
      box-shadow: 0 18px 40px rgba(46,134,171,0.16);
      transform: translateY(-4px);
    }

    /* ── IMAGE CLUSTER (primary + two stacked secondaries) ── */
    .ach-image-cluster {
      display: grid;
      grid-template-columns: 65fr 35fr;
      grid-template-rows: 1fr 1fr;
      gap: 8px;
      height: 340px;
      padding: 12px 12px 0;
    }

    /* Primary image spans both rows on the left */
    .ach-primary {
      grid-row: 1 / 3;
      position: relative;
      border-radius: 14px;
      overflow: hidden;
      cursor: pointer;
    }

    .ach-primary img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      filter: brightness(1.02) contrast(1.03);
      transition: transform 0.35s ease;
    }

    .ach-primary:hover img {
      transform: scale(1.04);
    }

    /* Gradient overlay on primary image */
    .ach-primary-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(0,0,0,0.62) 0%, rgba(0,0,0,0.18) 45%, transparent 100%);
      pointer-events: none;
      border-radius: 14px;
    }

    /* Text overlay — bottom-left of primary image */
    .ach-primary-text {
      position: absolute;
      bottom: 18px;
      left: 18px;
      right: 14px;
      pointer-events: none;
    }

    .ach-primary-text .ach-overlay-title {
      font-family: 'Poppins', sans-serif;
      font-size: 1.05rem;
      font-weight: 700;
      color: #fff;
      line-height: 1.3;
      margin-bottom: 4px;
      text-shadow: 0 1px 4px rgba(0,0,0,0.4);
    }

    .ach-primary-text .ach-overlay-sub {
      font-size: 0.8rem;
      font-weight: 400;
      color: rgba(255,255,255,0.82);
      line-height: 1.4;
      text-shadow: 0 1px 3px rgba(0,0,0,0.35);
    }

    /* "View Gallery" button on primary image */
    .ach-view-btn {
      position: absolute;
      top: 14px;
      right: 14px;
      background: rgba(255,255,255,0.18);
      backdrop-filter: blur(6px);
      color: #fff;
      border: 1px solid rgba(255,255,255,0.35);
      border-radius: 50px;
      padding: 5px 14px;
      font-size: 0.75rem;
      font-weight: 600;
      cursor: pointer;
      letter-spacing: 0.3px;
      transition: background 0.2s ease;
      pointer-events: all;
    }

    .ach-view-btn:hover {
      background: rgba(255,255,255,0.32);
    }

    /* Secondary images — right column */
    .ach-secondary {
      position: relative;
      border-radius: 12px;
      overflow: hidden;
      cursor: pointer;
    }

    .ach-secondary img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      filter: brightness(1.01) contrast(1.02);
      transition: transform 0.35s ease;
    }

    .ach-secondary:hover img {
      transform: scale(1.05);
    }

    /* No photo placeholder */
    .ach-no-photo {
      height: 220px;
      background: var(--pale-blue);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary-color);
      opacity: 0.35;
      font-size: 3rem;
    }

    /* Card body */
    .ach-body {
      padding: 18px 20px 22px;
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
      font-size: 1.05rem;
      color: var(--dark);
      margin-bottom: 6px;
      line-height: 1.35;
    }

    .ach-caption {
      font-size: 0.85rem;
      color: var(--gray);
      line-height: 1.6;
    }

    /* Achievement Lightbox */
    #achLightbox {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.92);
      z-index: 9999;
      align-items: center;
      justify-content: center;
    }

    #achLightbox.show { display: flex; }

    #achLightbox img {
      max-width: 92vw;
      max-height: 88vh;
      border-radius: 10px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.5);
    }

    #achLightbox .lb-close {
      position: absolute;
      top: 18px;
      right: 24px;
      color: #fff;
      font-size: 2rem;
      cursor: pointer;
      line-height: 1;
    }

    #achLightbox .lb-prev,
    #achLightbox .lb-next {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      color: #fff;
      cursor: pointer;
      background: rgba(255,255,255,0.1);
      border: none;
      border-radius: 50%;
      width: 52px;
      height: 52px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      transition: background 0.2s;
    }

    #achLightbox .lb-prev:hover,
    #achLightbox .lb-next:hover { background: rgba(255,255,255,0.25); }

    #achLightbox .lb-prev { left: 16px; }
    #achLightbox .lb-next { right: 16px; }

    #achLightbox .lb-counter {
      position: absolute;
      bottom: 18px;
      left: 50%;
      transform: translateX(-50%);
      color: rgba(255,255,255,0.8);
      font-size: 0.9rem;
      background: rgba(0,0,0,0.4);
      padding: 4px 14px;
      border-radius: 20px;
    }

    #achLightbox .lb-caption {
      position: absolute;
      bottom: 50px;
      left: 50%;
      transform: translateX(-50%);
      color: #fff;
      font-size: 0.95rem;
      font-weight: 600;
      background: rgba(0,0,0,0.5);
      padding: 6px 18px;
      border-radius: 20px;
      white-space: nowrap;
      max-width: 90vw;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* Responsive */
    @media (max-width: 767px) {
      .ach-image-cluster {
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 180px 120px;
        height: auto;
      }
      .ach-primary {
        grid-column: 1 / 3;
        grid-row: 1 / 2;
      }
      .ach-secondary {
        grid-row: 2 / 3;
      }
    }

    @media (max-width: 480px) {
      .ach-image-cluster {
        grid-template-columns: 1fr;
        grid-template-rows: 200px 120px 120px;
        height: auto;
      }
      .ach-primary {
        grid-column: 1;
        grid-row: 1 / 2;
      }
      .ach-secondary:nth-child(2) { grid-row: 2; }
      .ach-secondary:nth-child(3) { grid-row: 3; }
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
  <!-- Background image layer (from DB) -->
  <div id="heroBgLayer" style="position:absolute;inset:0;background-size:cover;background-position:center;<?= !empty($hero_settings['bg_image']) ? 'background-image:url(\'../../' . htmlspecialchars($hero_settings['bg_image']) . '\');opacity:0.28;' : 'opacity:0;' ?>z-index:0;transition:opacity 0.4s ease;pointer-events:none;"></div>
  <!-- Floating blobs -->
  <div class="hero-blob hero-blob-1"></div>
  <div class="hero-blob hero-blob-2"></div>

  <div class="container">
    <div class="hero-content text-center">

      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb justify-content-center">
          <li class="breadcrumb-item"><a href="user_home.php">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Transparency & Progress</li>
        </ol>
      </nav>

      <!-- Page label chip -->
      <div class="hero-chip">
        <i class="bi bi-bar-chart-line-fill"></i>
        Association Report
      </div>

      <!-- Page Title -->
      <h1><?= htmlspecialchars($hero_settings['title']) ?></h1>

      <!-- Subtitle -->
      <p class="subtitle">
        <?= htmlspecialchars($hero_settings['subtitle']) ?>
      </p>

      <!-- Last Updated -->
      <div class="last-updated">
        <i class="bi bi-calendar-check-fill"></i>
        Last Updated: <?= htmlspecialchars($stats['last_updated']) ?>
      </div>
    </div>
  </div>

  <!-- Wave SVG divider -->
  <div class="hero-wave">
    <svg viewBox="0 0 1440 60" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
      <path d="M0,30 C360,70 1080,-10 1440,30 L1440,60 L0,60 Z" fill="#f0f5fb"/>
    </svg>
  </div>
</section>

<!-- SECTION 2: FINANCIAL OVERVIEW (Statistics Dashboard) -->
<section class="stats-section">
  <div class="container">
    <div class="row g-4">
      <!-- Stat 1: Total Assistance Received -->
      <div class="col-lg col-md-6">
<<<<<<< HEAD
        <div class="stat-card">
=======
        <div class="stat-card card-total">
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
          <div class="stat-icon">
            <i class="bi bi-cash-stack"></i>
          </div>
          <div class="stat-value"><?= formatCurrency($stats['total_assistance']) ?></div>
          <div class="stat-label">Total Assistance Received</div>
        </div>
      </div>

      <!-- Stat 2: Partner Organizations -->
      <div class="col-lg col-md-6">
<<<<<<< HEAD
        <div class="stat-card">
=======
        <div class="stat-card card-partners">
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
          <div class="stat-icon">
            <i class="bi bi-building"></i>
          </div>
          <div class="stat-value"><?= number_format($stats['partner_count']) ?></div>
          <div class="stat-label">Partner Organizations</div>
        </div>
      </div>

      <!-- Stat 3: Community Achievements -->
      <div class="col-lg col-md-6">
<<<<<<< HEAD
        <div class="stat-card">
=======
        <div class="stat-card card-achievements">
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
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

<!-- SECTION 3: RECENT ASSISTANCE RECEIVED -->
<section class="assistance-section">
  <div class="container">
    <div class="section-header">
      <h2>Recent Assistance Received</h2>
      <p>Support from government agencies, NGOs, and partners — including goods, tools, and financial aid</p>
    </div>

<<<<<<< HEAD
    <!-- Search -->
=======
    <!-- Search + Filter chips -->
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    <div class="table-controls">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="searchInput" placeholder="Search by source or type..." />
      </div>
<<<<<<< HEAD
=======
      <div class="filter-chips" id="filterChips">
        <span class="filter-chip all active" data-filter="all"><i class="bi bi-grid-fill"></i> All</span>
        <span class="filter-chip dole"   data-filter="dole"><i class="bi bi-bank2"></i> DOLE</span>
        <span class="filter-chip ngo"    data-filter="ngo"><i class="bi bi-globe2"></i> NGO</span>
        <span class="filter-chip lgu"    data-filter="lgu"><i class="bi bi-building"></i> LGU</span>
        <span class="filter-chip others" data-filter="private"><i class="bi bi-person-workspace"></i> Private</span>
      </div>
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    </div>

    <?php if (!empty($assistanceList)): ?>
    <div id="assistanceGrid">
      <?php foreach ($assistanceList as $a):
<<<<<<< HEAD
        $badge     = getSourceTypeBadge($a['donor_type']);
        $hasAmount = (float)$a['amount'] > 0;
        $imgs      = $a['images'];
        $primaryImg    = $imgs[0]['image_path'] ?? '';
        $secondaryImg1 = $imgs[1]['image_path'] ?? '';
        $secondaryImg2 = $imgs[2]['image_path'] ?? '';
        $imgCount  = count($imgs);
        $dateLabel = $a['date_received'] ? date('M d, Y', strtotime($a['date_received'])) : '';
=======
        $badge      = getSourceTypeBadge($a['donor_type']);
        $isInKind   = ($a['donation_type'] ?? 'cash') === 'in_kind';
        $hasAmount  = (float)$a['amount'] > 0;
        $imgs       = $a['images'];
        $items      = $a['items'] ?? [];
        $primaryImg    = $imgs[0]['image_path'] ?? '';
        $secondaryImg1 = $imgs[1]['image_path'] ?? '';
        $secondaryImg2 = $imgs[2]['image_path'] ?? '';
        $imgCount   = count($imgs);
        $dateLabel  = $a['date_received'] ? date('M d, Y', strtotime($a['date_received'])) : '';
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
      ?>
      <div class="assistance-item"
           data-name="<?= htmlspecialchars(strtolower($a['donor_name'])) ?>"
           data-type="<?= htmlspecialchars(strtolower($a['donor_type'])) ?>">
        <div class="assistance-card">

          <!-- Top accent bar -->
          <div class="assistance-card-accent"></div>

          <?php if ($primaryImg): ?>
<<<<<<< HEAD
          <!-- ── Image strip (compact, fixed height) ── -->
=======
          <!-- ── Image strip (fixed height, same as no-img banner) ── -->
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
          <div class="assistance-img-strip <?= $imgCount === 1 ? 'one-img' : '' ?>">

            <!-- Primary -->
            <div class="astrip-primary" onclick="openLightbox(<?= $a['id'] ?>, 0)">
              <img src="../../<?= htmlspecialchars($primaryImg) ?>"
                   alt="<?= htmlspecialchars($a['donor_name']) ?>"
                   onerror="this.parentElement.style.background='var(--pale-blue)'; this.style.display='none';">
              <div class="astrip-primary-overlay"></div>
<<<<<<< HEAD
=======
              <!-- Label overlay -->
              <span style="position:absolute;top:8px;left:8px;background:rgba(0,0,0,0.52);color:#fff;font-size:0.65rem;font-weight:700;padding:2px 9px;border-radius:20px;pointer-events:none;letter-spacing:0.4px;backdrop-filter:blur(4px);">
                <i class="bi bi-camera-fill me-1"></i>Documentation
              </span>
              <!-- Photo count badge -->
              <?php if ($imgCount > 1): ?>
              <span style="position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,0.55);color:#fff;font-size:0.68rem;font-weight:600;padding:2px 9px;border-radius:20px;pointer-events:none;">
                <i class="bi bi-images me-1"></i><?= $imgCount ?> photos
              </span>
              <?php endif; ?>
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
            </div>

            <?php if ($imgCount > 1): ?>
            <!-- Thumbnail stack -->
            <div class="astrip-thumbs">
              <div class="astrip-thumb" onclick="openLightbox(<?= $a['id'] ?>, 1)">
                <img src="../../<?= htmlspecialchars($secondaryImg1 ?: $primaryImg) ?>"
                     alt=""
                     onerror="this.parentElement.style.background='var(--pale-blue)'; this.style.display='none';">
              </div>
              <?php if ($imgCount > 2): ?>
              <div class="astrip-thumb" onclick="openLightbox(<?= $a['id'] ?>, 2)">
                <img src="../../<?= htmlspecialchars($secondaryImg2 ?: $primaryImg) ?>"
                     alt=""
                     onerror="this.parentElement.style.background='var(--pale-blue)'; this.style.display='none';">
              </div>
              <?php endif; ?>
            </div>
            <?php endif; ?>

          </div>
<<<<<<< HEAD
          <?php elseif ($hasAmount): ?>
          <!-- ── Cash-only banner (no images) ── -->
          <div class="assistance-cash-banner">
            <div class="acash-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="acash-amount-wrap">
              <div class="acash-label">Amount Received</div>
              <div class="acash-value"><?= formatCurrency($a['amount']) ?></div>
            </div>
          </div>
          <?php else: ?>
          <!-- ── Goods/Tools banner (no images, no amount) ── -->
          <div class="assistance-cash-banner">
            <div class="acash-icon"><i class="bi bi-box-seam"></i></div>
            <div class="acash-amount-wrap">
              <div class="acash-label">Assistance Type</div>
              <div class="acash-goods">Goods / Tools</div>
            </div>
=======
          <?php else: ?>
          <!-- ── No-image banner (same height as image strip) ── -->
          <div class="assistance-no-img-banner">
            <?php if ($isInKind): ?>
            <div class="text-center">
              <div class="anim-icon mx-auto mb-3"><i class="bi bi-box-seam"></i></div>
              <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--primary-color);opacity:0.8;">In-Kind / Gamit</div>
              <?php if (!empty($items)): ?>
              <div style="margin-top:8px;font-size:0.78rem;color:#444;line-height:1.6;max-width:180px;">
                <?php foreach (array_slice($items,0,3) as $it): ?>
                <div>• <?= htmlspecialchars($it['item_name']) ?>
                  <?php if ((float)$it['quantity'] > 0): ?>
                    <span style="color:#888">(<?= rtrim(rtrim(number_format((float)$it['quantity'],2,'.',','),'0'),'.') ?> <?= htmlspecialchars($it['unit'] ?? '') ?>)</span>
                  <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if (count($items) > 3): ?>
                <div style="color:#888">+<?= count($items)-3 ?> more…</div>
                <?php endif; ?>
              </div>
              <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="text-center">
              <div class="anim-icon mx-auto mb-3"><i class="bi bi-cash-stack"></i></div>
              <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--primary-color);opacity:0.8;">Amount Received</div>
              <?php if ($hasAmount): ?>
              <div style="font-family:'Poppins',sans-serif;font-weight:800;font-size:1.4rem;color:var(--primary-color);margin-top:4px;"><?= formatCurrency($a['amount']) ?></div>
              <?php endif; ?>
            </div>
            <?php endif; ?>
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
          </div>
          <?php endif; ?>

          <!-- ── Card body (details) ── -->
          <div class="assistance-body">

            <div class="src-name"><?= htmlspecialchars($a['donor_name']) ?></div>

            <div class="assistance-meta">
              <span class="source-type-badge <?= htmlspecialchars($badge['class']) ?>">
<<<<<<< HEAD
                <?= htmlspecialchars($badge['label']) ?>
              </span>
            </div>

            <?php if ($primaryImg): ?>
              <?php if ($hasAmount): ?>
              <span class="assistance-amount"><?= formatCurrency($a['amount']) ?></span>
              <?php else: ?>
              <span class="assistance-amount zero"><i class="bi bi-box-seam me-1"></i>Goods / Tools</span>
              <?php endif; ?>
              <hr class="assistance-divider">
            <?php endif; ?>

            <?php if ($dateLabel): ?>
            <div class="assistance-detail-row">
              <span class="det-label">Date</span>
              <span class="det-value"><?= $dateLabel ?></span>
=======
                <i class="bi bi-circle-fill" style="font-size:0.45rem;opacity:0.7;"></i>
                <?= htmlspecialchars($badge['label']) ?>
              </span>
              <?php if ($primaryImg && $hasAmount && !$isInKind): ?>
              <span class="assistance-amount" style="margin-left:4px;"><?= formatCurrency($a['amount']) ?></span>
              <?php elseif ($primaryImg && $isInKind): ?>
              <span class="assistance-amount zero" style="margin-left:4px;"><i class="bi bi-box-seam me-1"></i>In-Kind</span>
              <?php endif; ?>
            </div>

            <hr class="assistance-divider">

            <?php if ($dateLabel): ?>
            <div class="assistance-detail-row" style="margin-bottom:7px;">
              <span class="det-label">Date</span>
              <span class="det-date-pill"><i class="bi bi-calendar3"></i><?= $dateLabel ?></span>
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
            </div>
            <?php endif; ?>

            <div class="assistance-detail-row">
              <span class="det-label">Type</span>
              <span class="det-value"><?= htmlspecialchars($a['donor_type'] ?: '—') ?></span>
            </div>

            <?php if (!empty($a['notes'])): ?>
            <hr class="assistance-divider">
            <div class="assistance-notes-text"><?= htmlspecialchars($a['notes']) ?></div>
            <?php endif; ?>

          </div>
        </div>
      </div>

      <!-- Hidden image list for lightbox -->
      <div id="lbImages-<?= $a['id'] ?>" style="display:none">
        <?php foreach ($imgs as $img): ?>
        <span>../../<?= htmlspecialchars($img['image_path']) ?></span>
        <?php endforeach; ?>
      </div>

      <?php endforeach; ?>
    </div>

    <div id="noResults" style="display:none">
      <div class="empty-state mt-3">
        <i class="bi bi-search"></i>
        <h4>No matches found</h4>
        <p>Try a different search term.</p>
      </div>
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

<!-- Lightbox -->
<div id="assistanceLightbox">
  <span class="lb-close" onclick="closeLightbox()">&times;</span>
  <button class="lb-prev" onclick="lbNav(-1)"><i class="bi bi-chevron-left"></i></button>
  <img id="lbImg" src="" alt="">
  <button class="lb-next" onclick="lbNav(1)"><i class="bi bi-chevron-right"></i></button>
</div>

<!-- SECTION 4: COMMUNITY ACHIEVEMENTS GALLERY -->
<section class="achievements-section">
  <div class="container">
    <div class="section-header">
      <h2>Community Achievements</h2>
      <p>Products, outcomes, and milestones from our livelihood programs — grown and made by our fisherfolk community</p>
    </div>

    <?php if (!empty($achievementsList)): ?>
    <div class="achievements-grid">
      <?php foreach ($achievementsList as $ach):
        $achImgs = !empty($ach['images']) ? $ach['images'] : ((!empty($ach['image_path'])) ? [['image_path'=>$ach['image_path']]] : []);
        $primaryImg   = $achImgs[0]['image_path'] ?? '';
        $secondaryImg1 = $achImgs[1]['image_path'] ?? '';
        $secondaryImg2 = $achImgs[2]['image_path'] ?? '';
        // fallback: if only 1 image, reuse it for secondaries so the layout doesn't collapse
        if (!$secondaryImg1) $secondaryImg1 = $primaryImg;
        if (!$secondaryImg2) $secondaryImg2 = $primaryImg;
      ?>
      <div class="ach-card">

        <?php if ($primaryImg): ?>
        <!-- ── Image cluster: 65% primary + 35% two stacked secondaries ── -->
        <div class="ach-image-cluster">

          <!-- Primary (dominant) image -->
          <div class="ach-primary" onclick="openAchLightbox(<?= $ach['id'] ?>, 0)">
            <img src="../../<?= htmlspecialchars($primaryImg) ?>"
                 alt="<?= htmlspecialchars($ach['title']) ?>"
                 onerror="this.parentElement.style.background='var(--pale-blue)'; this.style.display='none';">
            <div class="ach-primary-overlay"></div>
            <div class="ach-primary-text">
              <div class="ach-overlay-title"><?= htmlspecialchars($ach['title']) ?></div>
              <?php if (!empty($ach['caption'])): ?>
              <div class="ach-overlay-sub"><?= htmlspecialchars(mb_substr($ach['caption'], 0, 90)) ?><?= mb_strlen($ach['caption']) > 90 ? '…' : '' ?></div>
              <?php endif; ?>
            </div>
            <?php if (count($achImgs) > 1): ?>
            <button class="ach-view-btn" onclick="event.stopPropagation(); openAchLightbox(<?= $ach['id'] ?>, 0)">
              <i class="bi bi-images me-1"></i>View Gallery
            </button>
            <?php endif; ?>
          </div>

          <!-- Secondary image 1 -->
          <div class="ach-secondary" onclick="openAchLightbox(<?= $ach['id'] ?>, <?= isset($achImgs[1]) ? 1 : 0 ?>)">
            <img src="../../<?= htmlspecialchars($secondaryImg1) ?>"
                 alt="<?= htmlspecialchars($ach['title']) ?>"
                 onerror="this.parentElement.style.background='var(--pale-blue)'; this.style.display='none';">
          </div>

          <!-- Secondary image 2 -->
          <div class="ach-secondary" onclick="openAchLightbox(<?= $ach['id'] ?>, <?= isset($achImgs[2]) ? 2 : 0 ?>)">
            <img src="../../<?= htmlspecialchars($secondaryImg2) ?>"
                 alt="<?= htmlspecialchars($ach['title']) ?>"
                 onerror="this.parentElement.style.background='var(--pale-blue)'; this.style.display='none';">
          </div>

        </div>
        <?php else: ?>
        <div class="ach-no-photo"><i class="bi bi-image"></i></div>
        <?php endif; ?>

        <!-- Hidden image list for lightbox (all images) -->
        <div id="achLbImages-<?= $ach['id'] ?>" style="display:none"
             data-title="<?= htmlspecialchars($ach['title']) ?>">
          <?php foreach ($achImgs as $achImg): ?>
          <span>../../<?= htmlspecialchars($achImg['image_path']) ?></span>
          <?php endforeach; ?>
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

<!-- Achievement Lightbox -->
<div id="achLightbox">
  <span class="lb-close" onclick="closeAchLightbox()">&times;</span>
  <button class="lb-prev" onclick="achLbNav(-1)"><i class="bi bi-chevron-left"></i></button>
  <img id="achLbImg" src="" alt="">
  <button class="lb-next" onclick="achLbNav(1)"><i class="bi bi-chevron-right"></i></button>
  <div class="lb-caption" id="achLbCaption"></div>
  <div class="lb-counter" id="achLbCounter"></div>
</div>

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

<div style="height: 60px;"></div>

<?php include("partials/footer.php"); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
<<<<<<< HEAD
  // ── Search ──────────────────────────────────────────────
  const searchInput = document.getElementById('searchInput');
  const items = document.querySelectorAll('.assistance-item');
  const noResults = document.getElementById('noResults');

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const q = this.value.toLowerCase().trim();
      let visible = 0;
      items.forEach(item => {
        const match = item.dataset.name.includes(q) || item.dataset.type.includes(q);
        item.style.display = match ? '' : 'none';
        if (match) visible++;
      });
      if (noResults) noResults.style.display = (visible === 0 && q !== '') ? '' : 'none';
    });
  }

  // ── Animate stat cards ───────────────────────────────────
  document.querySelectorAll('.stat-value').forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    setTimeout(() => {
      el.style.transition = 'all 0.6s ease';
      el.style.opacity = '1';
      el.style.transform = 'translateY(0)';
    }, i * 100);
=======
  // ── Search + Filter chips ────────────────────────────────
  const searchInput = document.getElementById('searchInput');
  const items       = document.querySelectorAll('.assistance-item');
  const noResults   = document.getElementById('noResults');
  const chips       = document.querySelectorAll('.filter-chip');
  let activeFilter  = 'all';

  function applyFilters() {
    const q = searchInput ? searchInput.value.toLowerCase().trim() : '';
    let visible = 0;
    items.forEach(item => {
      const name  = item.dataset.name  || '';
      const type  = item.dataset.type  || '';
      const matchSearch = !q || name.includes(q) || type.includes(q);
      const matchChip   = activeFilter === 'all' || type.includes(activeFilter);
      const show = matchSearch && matchChip;
      item.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    if (noResults) noResults.style.display = (visible === 0 && (q !== '' || activeFilter !== 'all')) ? '' : 'none';
  }

  if (searchInput) {
    searchInput.addEventListener('input', applyFilters);
  }

  chips.forEach(chip => {
    chip.addEventListener('click', function() {
      chips.forEach(c => c.classList.remove('active'));
      this.classList.add('active');
      activeFilter = this.dataset.filter;
      applyFilters();
    });
  });

  // ── Animate stat cards ───────────────────────────────────
  document.querySelectorAll('.stat-card').forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(24px)';
    setTimeout(() => {
      el.style.transition = 'opacity 0.55s ease, transform 0.55s ease';
      el.style.opacity = '1';
      el.style.transform = 'translateY(0)';
    }, 80 + i * 100);
  });

  // ── Animate assistance cards on scroll ──────────────────
  const cards = document.querySelectorAll('.assistance-card');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, idx) => {
      if (entry.isIntersecting) {
        entry.target.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.08 });

  cards.forEach((card, i) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    observer.observe(card);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
  });
});

// ── Lightbox ──────────────────────────────────────────────
let lbCurrentId = null;
let lbCurrentIdx = 0;
let lbImages = [];

function openLightbox(donationId, startIdx) {
  const container = document.getElementById('lbImages-' + donationId);
  if (!container) return;
  lbImages = Array.from(container.querySelectorAll('span')).map(s => s.textContent.trim());
  if (!lbImages.length) return;
  lbCurrentId = donationId;
  lbCurrentIdx = startIdx;
  document.getElementById('lbImg').src = lbImages[lbCurrentIdx];
  document.getElementById('assistanceLightbox').classList.add('show');
}

function closeLightbox() {
  document.getElementById('assistanceLightbox').classList.remove('show');
  document.getElementById('lbImg').src = '';
}

function lbNav(dir) {
  lbCurrentIdx = (lbCurrentIdx + dir + lbImages.length) % lbImages.length;
  document.getElementById('lbImg').src = lbImages[lbCurrentIdx];
}

document.getElementById('assistanceLightbox').addEventListener('click', function(e) {
  if (e.target === this) closeLightbox();
});

document.addEventListener('keydown', function(e) {
  const lb = document.getElementById('assistanceLightbox');
  const achLb = document.getElementById('achLightbox');
  if (lb.classList.contains('show')) {
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') lbNav(-1);
    if (e.key === 'ArrowRight') lbNav(1);
  }
  if (achLb.classList.contains('show')) {
    if (e.key === 'Escape') closeAchLightbox();
    if (e.key === 'ArrowLeft') achLbNav(-1);
    if (e.key === 'ArrowRight') achLbNav(1);
  }
});

// ── Achievement Lightbox ──────────────────────────────────
let achLbCurrentId = null;
let achLbCurrentIdx = 0;
let achLbImagesArr = [];
let achLbTitle = '';

function openAchLightbox(achId, startIdx) {
  const container = document.getElementById('achLbImages-' + achId);
  if (!container) return;
  achLbImagesArr = Array.from(container.querySelectorAll('span')).map(s => s.textContent.trim());
  if (!achLbImagesArr.length) return;
  achLbCurrentId = achId;
  achLbCurrentIdx = startIdx;
  achLbTitle = container.dataset.title || '';
  updateAchLb();
  document.getElementById('achLightbox').classList.add('show');
}

function updateAchLb() {
  document.getElementById('achLbImg').src = achLbImagesArr[achLbCurrentIdx];
  document.getElementById('achLbCaption').textContent = achLbTitle;
  document.getElementById('achLbCounter').textContent = (achLbCurrentIdx + 1) + ' / ' + achLbImagesArr.length;
}

function closeAchLightbox() {
  document.getElementById('achLightbox').classList.remove('show');
  document.getElementById('achLbImg').src = '';
}

function achLbNav(dir) {
  achLbCurrentIdx = (achLbCurrentIdx + dir + achLbImagesArr.length) % achLbImagesArr.length;
  updateAchLb();
}

document.getElementById('achLightbox').addEventListener('click', function(e) {
  if (e.target === this) closeAchLightbox();
});


</script>

</body>
</html>
