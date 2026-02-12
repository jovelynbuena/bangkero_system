<?php
session_start();
require_once('../../config/db_connect.php');

// Create awards table if it doesn't exist
$createTableQuery = "CREATE TABLE IF NOT EXISTS awards (
    award_id INT PRIMARY KEY AUTO_INCREMENT,
    award_title VARCHAR(255) NOT NULL,
    awarding_body VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    description TEXT,
    year_received INT,
    date_received DATE,
    award_image VARCHAR(255),
    certificate_file VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($createTableQuery);

// Handle search and filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$year_filter = isset($_GET['year']) ? intval($_GET['year']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build WHERE clause
$whereConditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $whereConditions[] = "(award_title LIKE ? OR awarding_body LIKE ? OR description LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

if (!empty($year_filter)) {
    $whereConditions[] = "year_received = ?";
    $params[] = $year_filter;
    $types .= 'i';
}

if (!empty($category_filter)) {
    $whereConditions[] = "category = ?";
    $params[] = $category_filter;
    $types .= 's';
}

$whereSQL = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Sorting
$orderBy = $sort === 'oldest' ? "ORDER BY year_received ASC, date_received ASC" : "ORDER BY year_received DESC, date_received DESC";

// Get statistics
$statsQuery = "SELECT 
    COUNT(*) as total_awards,
    SUM(CASE WHEN category = 'National' THEN 1 ELSE 0 END) as national_count,
    SUM(CASE WHEN category = 'Regional' THEN 1 ELSE 0 END) as regional_count,
    SUM(CASE WHEN category = 'Local' THEN 1 ELSE 0 END) as local_count
FROM awards";
$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

// Get all unique years for filter dropdown
$yearsQuery = "SELECT DISTINCT year_received FROM awards WHERE year_received IS NOT NULL ORDER BY year_received DESC";
$yearsResult = $conn->query($yearsQuery);

// Get all unique categories for filter dropdown
$categoriesQuery = "SELECT DISTINCT category FROM awards WHERE category IS NOT NULL AND category != '' ORDER BY category";
$categoriesResult = $conn->query($categoriesQuery);

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM awards {$whereSQL}";
if (!empty($params)) {
    $countStmt = $conn->prepare($countQuery);
    if (!empty($types)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $totalResult = $countStmt->get_result();
} else {
    $totalResult = $conn->query($countQuery);
}
$totalRow = $totalResult->fetch_assoc();
$totalAwards = $totalRow['total'];
$totalPages = ceil($totalAwards / $perPage);

// Get awards with pagination
$awardsQuery = "SELECT * FROM awards {$whereSQL} {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
if (!empty($params)) {
    $awardsStmt = $conn->prepare($awardsQuery);
    if (!empty($types)) {
        $awardsStmt->bind_param($types, ...$params);
    }
    $awardsStmt->execute();
    $awardsResult = $awardsStmt->get_result();
} else {
    $awardsResult = $conn->query($awardsQuery);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Awards & Recognition - Bangkero & Fishermen Association</title>
  <!-- Bootstrap CSS & Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- Main Stylesheet -->
  <link href="../../css/main-style.css" rel="stylesheet">
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
      --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg);
      color: #2c3e50;
      overflow-x: hidden;
    }

    /* Page Header */
    .page-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      padding: 60px 0 80px;
      position: relative;
      overflow: hidden;
    }
    .page-header::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
      border-radius: 50%;
    }
    .page-header h1 {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 2.5rem;
      color: white;
      margin-bottom: 10px;
      position: relative;
    }
    .page-header p {
      color: rgba(255,255,255,0.9);
      font-size: 1.1rem;
      margin-bottom: 20px;
      position: relative;
    }
    .breadcrumb-custom {
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
      padding: 10px 20px;
      border-radius: 50px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      position: relative;
    }
    .breadcrumb-custom a,
    .breadcrumb-custom span {
      color: white;
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
    }
    .breadcrumb-custom a:hover {
      text-decoration: underline;
    }
    .breadcrumb-custom i {
      font-size: 0.7rem;
      opacity: 0.7;
    }

    /* Statistics Cards */
    .stats-section {
      margin-top: -50px;
      margin-bottom: 40px;
      position: relative;
      z-index: 10;
    }
    .stat-card {
      background: white;
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 4px 20px rgba(44, 62, 80, 0.08);
      transition: all 0.3s ease;
      border: none;
      position: relative;
      overflow: hidden;
      height: 100%;
    }
    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 30px rgba(44, 62, 80, 0.15);
    }
    .stat-card:hover::before {
      opacity: 1;
    }
    .stat-icon {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
      margin-bottom: 15px;
    }
    .stat-icon.total {
      background: linear-gradient(135deg, #d4af37 0%, #f4e5a1 100%);
      color: #1a252f;
    }
    .stat-icon.national {
      background: linear-gradient(135deg, #3498db 0%, #5dade2 100%);
      color: white;
    }
    .stat-icon.regional {
      background: linear-gradient(135deg, #27ae60 0%, #52be80 100%);
      color: white;
    }
    .stat-icon.local {
      background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
      color: white;
    }
    .stat-number {
      font-family: 'Poppins', sans-serif;
      font-size: 2.2rem;
      font-weight: 800;
      color: var(--dark);
      line-height: 1;
      margin-bottom: 5px;
    }
    .stat-label {
      color: #64748b;
      font-size: 0.9rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* Filter Section */
    .filter-section {
      background: white;
      padding: 25px;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(44, 62, 80, 0.08);
      margin-bottom: 35px;
    }
    .filter-section .form-control,
    .filter-section .form-select {
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 0.95rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    .filter-section .form-control:focus,
    .filter-section .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.1);
    }
    .filter-section label {
      font-weight: 600;
      color: var(--dark);
      font-size: 0.9rem;
      margin-bottom: 8px;
    }
    .btn-filter {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      border: none;
      padding: 12px 28px;
      border-radius: 10px;
      font-weight: 600;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .btn-filter:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(44, 62, 80, 0.3);
      color: white;
    }
    .btn-reset {
      background: white;
      color: var(--gray);
      border: 2px solid #e2e8f0;
      padding: 12px 28px;
      border-radius: 10px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .btn-reset:hover {
      background: #f8f9fa;
      border-color: var(--gray);
      color: var(--dark);
    }

    /* Awards Grid */
    .awards-grid {
      margin-bottom: 40px;
    }
    .award-card {
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(44, 62, 80, 0.08);
      transition: all 0.3s ease;
      border: none;
      height: 100%;
      display: flex;
      flex-direction: column;
      position: relative;
    }
    .award-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(135deg, #d4af37 0%, #f4e5a1 100%);
      transform: scaleX(0);
      transition: transform 0.3s ease;
    }
    .award-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 40px rgba(44, 62, 80, 0.15);
    }
    .award-card:hover::before {
      transform: scaleX(1);
    }
    .award-image-wrapper {
      position: relative;
      height: 200px;
      overflow: hidden;
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }
    .award-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.4s ease;
    }
    .award-card:hover .award-image {
      transform: scale(1.08);
    }
    .award-badge {
      position: absolute;
      top: 12px;
      right: 12px;
      background: linear-gradient(135deg, #d4af37 0%, #f4e5a1 100%);
      color: #1a252f;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .award-body {
      padding: 20px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    .award-category {
      display: inline-block;
      background: #f8fafc;
      color: var(--primary);
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 10px;
      align-self: flex-start;
    }
    .award-title {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 1.1rem;
      color: var(--dark);
      margin-bottom: 8px;
      line-height: 1.3;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .award-organization {
      color: #d4af37;
      font-size: 0.9rem;
      font-weight: 600;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .award-year {
      color: #64748b;
      font-size: 0.85rem;
      font-weight: 600;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .award-description {
      color: #64748b;
      font-size: 0.88rem;
      line-height: 1.5;
      margin-bottom: 15px;
      flex: 1;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .btn-view-details {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      text-decoration: none;
      width: 100%;
    }
    .btn-view-details:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(44, 62, 80, 0.3);
      color: white;
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 80px 20px;
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(44, 62, 80, 0.08);
    }
    .empty-state i {
      font-size: 5rem;
      color: #cbd5e1;
      margin-bottom: 20px;
    }
    .empty-state h3 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 700;
      font-size: 1.5rem;
      margin-bottom: 10px;
    }
    .empty-state p {
      color: #64748b;
      font-size: 1rem;
      margin-bottom: 0;
    }

    /* Pagination */
    .pagination-wrapper {
      display: flex;
      justify-content: center;
      margin-top: 40px;
    }
    .pagination {
      gap: 8px;
    }
    .page-link {
      border: 2px solid #e2e8f0;
      border-radius: 8px;
      color: var(--dark);
      font-weight: 600;
      padding: 10px 16px;
      transition: all 0.3s ease;
    }
    .page-link:hover {
      background: var(--light);
      border-color: var(--primary);
      color: var(--primary);
    }
    .page-item.active .page-link {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      border-color: var(--primary);
      color: white;
    }
    .page-item.disabled .page-link {
      background: #f8f9fa;
      border-color: #e2e8f0;
      color: #cbd5e1;
    }

    /* Modal Styling */
    .modal-content {
      border-radius: 16px;
      border: none;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    .modal-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      border-radius: 16px 16px 0 0;
      padding: 20px 30px;
      border: none;
    }
    .modal-header .btn-close {
      filter: brightness(0) invert(1);
      opacity: 0.8;
    }
    .modal-title {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 1.3rem;
    }
    .modal-body {
      padding: 30px;
    }
    .modal-image {
      width: 100%;
      border-radius: 12px;
      margin-bottom: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .detail-row {
      margin-bottom: 18px;
    }
    .detail-label {
      font-weight: 700;
      color: var(--dark);
      font-size: 0.9rem;
      margin-bottom: 5px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .detail-label i {
      color: var(--primary);
      font-size: 1.1rem;
    }
    .detail-value {
      color: #64748b;
      font-size: 0.95rem;
      line-height: 1.6;
    }
    .badge-category {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }

    /* Responsive */
    @media (max-width: 991px) {
      .page-header h1 {
        font-size: 2rem;
      }
      .stat-card {
        margin-bottom: 15px;
      }
    }
    @media (max-width: 768px) {
      .page-header {
        padding: 40px 0 60px;
      }
      .page-header h1 {
        font-size: 1.8rem;
      }
      .stats-section {
        margin-top: -40px;
      }
      .stat-number {
        font-size: 1.8rem;
      }
      .filter-section {
        padding: 20px;
      }
      .btn-filter,
      .btn-reset {
        width: 100%;
        margin-top: 10px;
      }
    }
  </style>
</head>
<body>

<?php include("partials/navbar.php"); ?>

<!-- Page Header -->
<section class="page-header">
  <div class="container">
    <div class="text-center">
      <h1><i class="bi bi-trophy-fill me-2"></i>Awards & Recognition</h1>
      <p>Celebrating the achievements and recognitions of the association</p>
      <div class="breadcrumb-custom">
        <a href="user_home.php"><i class="bi bi-house-fill"></i> Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <span>Awards</span>
      </div>
    </div>
  </div>
</section>

<!-- Statistics Section -->
<section class="stats-section">
  <div class="container">
    <div class="row g-3">
      <div class="col-lg-3 col-md-6">
        <div class="stat-card">
          <div class="stat-icon total">
            <i class="bi bi-trophy-fill"></i>
          </div>
          <div class="stat-number" data-target="<?php echo $stats['total_awards']; ?>">0</div>
          <div class="stat-label">Total Awards</div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="stat-card">
          <div class="stat-icon national">
            <i class="bi bi-flag-fill"></i>
          </div>
          <div class="stat-number" data-target="<?php echo $stats['national_count']; ?>">0</div>
          <div class="stat-label">National Level</div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="stat-card">
          <div class="stat-icon regional">
            <i class="bi bi-map-fill"></i>
          </div>
          <div class="stat-number" data-target="<?php echo $stats['regional_count']; ?>">0</div>
          <div class="stat-label">Regional Level</div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="stat-card">
          <div class="stat-icon local">
            <i class="bi bi-geo-alt-fill"></i>
          </div>
          <div class="stat-number" data-target="<?php echo $stats['local_count']; ?>">0</div>
          <div class="stat-label">Local Level</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Filter Section -->
<section class="container">
  <div class="filter-section">
    <form method="GET" action="" id="filterForm">
      <div class="row g-3 align-items-end">
        <div class="col-lg-3 col-md-6">
          <label for="search"><i class="bi bi-search me-1"></i> Search</label>
          <input type="text" class="form-control" id="search" name="search" 
                 placeholder="Search awards..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-lg-2 col-md-6">
          <label for="year"><i class="bi bi-calendar-event me-1"></i> Year</label>
          <select class="form-select" id="year" name="year">
            <option value="">All Years</option>
            <?php while($year = $yearsResult->fetch_assoc()): ?>
              <option value="<?php echo $year['year_received']; ?>" 
                      <?php echo $year_filter == $year['year_received'] ? 'selected' : ''; ?>>
                <?php echo $year['year_received']; ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-lg-2 col-md-6">
          <label for="category"><i class="bi bi-tag me-1"></i> Category</label>
          <select class="form-select" id="category" name="category">
            <option value="">All Categories</option>
            <?php while($cat = $categoriesResult->fetch_assoc()): ?>
              <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                      <?php echo $category_filter == $cat['category'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat['category']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-lg-2 col-md-6">
          <label for="sort"><i class="bi bi-sort-down me-1"></i> Sort By</label>
          <select class="form-select" id="sort" name="sort">
            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
            <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
          </select>
        </div>
        <div class="col-lg-3 col-md-12">
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-filter flex-fill">
              <i class="bi bi-funnel-fill"></i> Apply Filters
            </button>
            <a href="awards.php" class="btn btn-reset">
              <i class="bi bi-arrow-clockwise"></i>
            </a>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>

<!-- Awards Grid -->
<section class="container awards-grid">
  <?php if($awardsResult && $awardsResult->num_rows > 0): ?>
    <div class="row g-4">
      <?php while($award = $awardsResult->fetch_assoc()): ?>
        <div class="col-lg-4 col-md-6">
          <div class="award-card">
            <div class="award-image-wrapper">
              <?php if(!empty($award['award_image']) && file_exists("../../uploads/awards/".$award['award_image'])): ?>
                <img src="../../uploads/awards/<?php echo htmlspecialchars($award['award_image']); ?>" 
                     alt="<?php echo htmlspecialchars($award['award_title']); ?>" class="award-image">
              <?php else: ?>
                <img src="../../uploads/awards/default-award.png" alt="Default Award" class="award-image" 
                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22200%22%3E%3Crect fill=%22%23f1f5f9%22 width=%22400%22 height=%22200%22/%3E%3Ctext fill=%22%2394a3b8%22 font-family=%22Arial%22 font-size=%2218%22 dy=%2210.5%22 font-weight=%22bold%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22%3E%F0%9F%8F%86 Award%3C/text%3E%3C/svg%3E'">
              <?php endif; ?>
              <span class="award-badge"><?php echo $award['year_received']; ?></span>
            </div>
            <div class="award-body">
              <?php if(!empty($award['category'])): ?>
                <span class="award-category"><?php echo htmlspecialchars($award['category']); ?></span>
              <?php endif; ?>
              <h5 class="award-title"><?php echo htmlspecialchars($award['award_title']); ?></h5>
              <div class="award-organization">
                <i class="bi bi-building"></i>
                <?php echo htmlspecialchars($award['awarding_body']); ?>
              </div>
              <div class="award-year">
                <i class="bi bi-calendar3"></i>
                <?php echo date('F j, Y', strtotime($award['date_received'] ?: $award['year_received'].'-01-01')); ?>
              </div>
              <p class="award-description">
                <?php 
                  $desc = htmlspecialchars($award['description']);
                  echo strlen($desc) > 120 ? substr($desc, 0, 120) . '...' : $desc;
                ?>
              </p>
              <a href="#" class="btn-view-details" data-bs-toggle="modal" 
                 data-bs-target="#awardModal<?php echo $award['award_id']; ?>">
                <i class="bi bi-eye-fill"></i> View Details
              </a>
            </div>
          </div>
        </div>

        <!-- Award Detail Modal -->
        <div class="modal fade" id="awardModal<?php echo $award['award_id']; ?>" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">
                  <i class="bi bi-trophy-fill me-2"></i><?php echo htmlspecialchars($award['award_title']); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <?php if(!empty($award['award_image']) && file_exists("../../uploads/awards/".$award['award_image'])): ?>
                  <img src="../../uploads/awards/<?php echo htmlspecialchars($award['award_image']); ?>" 
                       alt="<?php echo htmlspecialchars($award['award_title']); ?>" class="modal-image">
                <?php endif; ?>
                
                <div class="detail-row">
                  <div class="detail-label">
                    <i class="bi bi-building"></i> Awarding Organization
                  </div>
                  <div class="detail-value"><?php echo htmlspecialchars($award['awarding_body']); ?></div>
                </div>

                <?php if(!empty($award['category'])): ?>
                <div class="detail-row">
                  <div class="detail-label">
                    <i class="bi bi-tag"></i> Category
                  </div>
                  <div class="detail-value">
                    <span class="badge-category"><?php echo htmlspecialchars($award['category']); ?></span>
                  </div>
                </div>
                <?php endif; ?>

                <div class="detail-row">
                  <div class="detail-label">
                    <i class="bi bi-calendar-event"></i> Date Received
                  </div>
                  <div class="detail-value">
                    <?php echo date('F j, Y', strtotime($award['date_received'] ?: $award['year_received'].'-01-01')); ?>
                  </div>
                </div>

                <div class="detail-row">
                  <div class="detail-label">
                    <i class="bi bi-file-text"></i> Description
                  </div>
                  <div class="detail-value"><?php echo nl2br(htmlspecialchars($award['description'])); ?></div>
                </div>

                <?php if(!empty($award['certificate_file']) && file_exists("../../uploads/awards/".$award['certificate_file'])): ?>
                <div class="detail-row">
                  <div class="detail-label">
                    <i class="bi bi-file-earmark-pdf"></i> Certificate
                  </div>
                  <div class="detail-value">
                    <a href="../../uploads/awards/<?php echo htmlspecialchars($award['certificate_file']); ?>" 
                       target="_blank" class="btn btn-sm btn-primary">
                      <i class="bi bi-download me-1"></i> Download Certificate
                    </a>
                  </div>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <?php if($totalPages > 1): ?>
    <div class="pagination-wrapper">
      <nav>
        <ul class="pagination">
          <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&year=<?php echo $year_filter; ?>&category=<?php echo urlencode($category_filter); ?>&sort=<?php echo $sort; ?>">
              <i class="bi bi-chevron-left"></i>
            </a>
          </li>
          
          <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <?php if($i == 1 || $i == $totalPages || abs($i - $page) <= 2): ?>
              <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&year=<?php echo $year_filter; ?>&category=<?php echo urlencode($category_filter); ?>&sort=<?php echo $sort; ?>">
                  <?php echo $i; ?>
                </a>
              </li>
            <?php elseif(abs($i - $page) == 3): ?>
              <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>
          <?php endfor; ?>

          <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&year=<?php echo $year_filter; ?>&category=<?php echo urlencode($category_filter); ?>&sort=<?php echo $sort; ?>">
              <i class="bi bi-chevron-right"></i>
            </a>
          </li>
        </ul>
      </nav>
    </div>
    <?php endif; ?>

  <?php else: ?>
    <!-- Empty State -->
    <div class="empty-state">
      <i class="bi bi-trophy"></i>
      <h3>No Awards Found</h3>
      <p>
        <?php if(!empty($search) || !empty($year_filter) || !empty($category_filter)): ?>
          No awards match your current filters. Try adjusting your search criteria.
        <?php else: ?>
          There are currently no awards to display.
        <?php endif; ?>
      </p>
      <?php if(!empty($search) || !empty($year_filter) || !empty($category_filter)): ?>
        <a href="awards.php" class="btn btn-primary mt-3">
          <i class="bi bi-arrow-clockwise me-2"></i>Clear Filters
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>

<div style="height: 60px;"></div>

<?php include("partials/footer.php"); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Counter Animation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Animate stat counters
  const counters = document.querySelectorAll('.stat-number');
  const speed = 200;

  counters.forEach(counter => {
    const target = +counter.getAttribute('data-target');
    const increment = target / speed;

    const updateCount = () => {
      const count = +counter.innerText;
      if (count < target) {
        counter.innerText = Math.ceil(count + increment);
        setTimeout(updateCount, 10);
      } else {
        counter.innerText = target;
      }
    };

    updateCount();
  });

  // Navbar shrink effect
  window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (navbar && window.scrollY > 40) {
      navbar.classList.add('shrink');
    } else if (navbar) {
      navbar.classList.remove('shrink');
    }
  });
});
</script>

<?php include 'chatbox.php'; ?>

</body>
</html>
