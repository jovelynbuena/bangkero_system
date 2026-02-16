<?php
require_once('../../config/db_connect.php');

// Ensure downloadable_resources table exists (for Resources page)
$conn->query("CREATE TABLE IF NOT EXISTS downloadable_resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    file_key VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    icon_class VARCHAR(100) DEFAULT NULL,
    color_hex VARCHAR(20) DEFAULT '#0d6efd',
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Default static resources (fallback)
$defaultResources = [
  ['file_key' => 'membership_form','title' => 'Membership Form','icon_class' => 'bi-file-earmark-person','color_hex' => '#0d6efd'],
  ['file_key' => 'event_guidelines','title' => 'Event Guidelines','icon_class' => 'bi-journal-text','color_hex' => '#0dcaf0'],
  ['file_key' => 'attendance_sheet','title' => 'Attendance Sheet','icon_class' => 'bi-file-earmark-spreadsheet','color_hex' => '#6c757d'],
  ['file_key' => 'officers_list','title' => 'Officers List','icon_class' => 'bi-people','color_hex' => '#0d6efd'],
];

// Try to fetch dynamic resources from DB
$resources = [];
$result = $conn->query("SELECT * FROM downloadable_resources WHERE is_active = 1 ORDER BY sort_order ASC, created_at ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $resources[] = $row;
    }
}

// If none configured yet, use fallback static list
if (empty($resources)) {
    $resources = $defaultResources;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Resources — Bangkero & Fishermen Association</title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary: #2c3e50;
      --secondary: #34495e;
      --accent: #5a6c7d;
      --light: #ecf0f1;
      --bg: #f8f9fa;
      --dark: #1a252f;
      --gray: #95a5a6;
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg);
      color: #2c3e50;
      margin: 0;
      padding: 0;
    }

    main {
      padding-top: 0;
      padding-bottom: 40px;
    }

    /* Hero Section with Search */
    .hero-section {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      padding: 60px 0 50px;
      color: white;
      position: relative;
      overflow: hidden;
      margin-top: 60px;
    }
    .hero-section::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
      border-radius: 50%;
    }
    .hero-section h1 {
      font-family: 'Poppins', sans-serif;
      font-size: 3rem;
      font-weight: 800;
      margin-bottom: 20px;
      letter-spacing: -1px;
      position: relative;
      z-index: 1;
    }
    .hero-section .search-box {
      position: relative;
      max-width: 600px;
      margin: 30px auto 0;
      z-index: 1;
    }
    .hero-section .search-box input {
      width: 100%;
      padding: 16px 50px 16px 20px;
      border: none;
      border-radius: 50px;
      font-size: 1rem;
      box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }
    .hero-section .search-box button {
      position: absolute;
      right: 8px;
      top: 50%;
      transform: translateY(-50%);
      background: var(--primary);
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      color: white;
      transition: background 0.3s;
    }
    .hero-section .search-box button:hover {
      background: var(--dark);
    }

    /* Resources Container */
    .resources-container {
      max-width: 1200px;
      margin: 25px auto;
      padding: 0 15px;
    }

    /* Resources Grid */
    .resources-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }

    .resource-card {
      background: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .resource-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }

    .icon-wrap {
      width: 55px;
      height: 55px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.6rem;
      color: white;
      flex-shrink: 0;
    }

    .resource-content {
      flex: 1;
    }

    .resource-title {
      font-size: 1rem;
      font-weight: 600;
      color: var(--primary);
      margin-bottom: 4px;
      font-family: 'Poppins', sans-serif;
    }

    .resource-sub {
      font-size: 0.85rem;
      color: var(--gray);
    }

    .btn-download {
      background: var(--primary);
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.3s;
      text-decoration: none;
      font-size: 0.9rem;
    }

    .btn-download:hover {
      background: var(--secondary);
      color: white;
      transform: translateY(-2px);
    }

    .btn-download i {
      font-size: 1rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .hero-section {
        padding: 40px 0 35px;
      }
      .hero-section h1 {
        font-size: 2rem;
      }
      .resources-grid {
        grid-template-columns: 1fr;
        gap: 15px;
      }
      .resource-card {
        padding: 18px;
      }
      .icon-wrap {
        width: 50px;
        height: 50px;
        font-size: 1.4rem;
      }
    }
  </style>
</head>
<body>
<?php include("partials/navbar.php"); ?>

<main>
  <!-- Hero Section -->
  <div class="hero-section">
    <div class="container text-center">
      <h1><i class="bi bi-folder2-open"></i> Resources</h1>
      <div class="search-box">
        <input 
          id="search" 
          type="search" 
          placeholder="Search resources..." 
          aria-label="Search resources"
        />
        <button id="clearBtn" type="button" title="Clear search">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Resources Grid -->
  <div class="resources-container">
    <div id="resourcesGrid" class="resources-grid">
      <?php foreach($resources as $r): ?>
        <div class="resource-card" data-title="<?= htmlspecialchars(strtolower($r['title'])) ?>">
          <div class="icon-wrap" style="background: <?= htmlspecialchars($r['color']) ?>;">
            <i class="bi <?= htmlspecialchars($r['icon']) ?>"></i>
          </div>
          <div class="resource-content">
            <div class="resource-title"><?= htmlspecialchars($r['title']) ?></div>
            <div class="resource-sub">PDF · Official</div>
          </div>
          <a class="btn-download" href="generate_pdfs.php?file=<?= urlencode($r['id']) ?>" target="_blank" rel="noopener noreferrer">
            <i class="bi bi-download"></i>
            Download
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</main>

<?php include("partials/footer.php"); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const search = document.getElementById('search');
  const clearBtn = document.getElementById('clearBtn');
  const cards = document.querySelectorAll('.resource-card');

  // Search functionality
  search.addEventListener('input', function() {
    const query = this.value.toLowerCase().trim();
    
    cards.forEach(card => {
      const title = card.getAttribute('data-title');
      if (title.includes(query)) {
        card.style.display = '';
      } else {
        card.style.display = 'none';
      }
    });
  });

  // Clear button
  clearBtn.addEventListener('click', function() {
    search.value = '';
    cards.forEach(card => {
      card.style.display = '';
    });
    search.focus();
  });

  // Escape key to clear
  search.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      clearBtn.click();
    }
  });
});
</script>
<?php include 'chatbox.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
