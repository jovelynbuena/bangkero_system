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
  ['file_key' => 'membership_form','title' => 'Membership Form','icon_class' => 'bi-file-earmark-person','color_hex' => '#1a3a5c'],
  ['file_key' => 'event_guidelines','title' => 'Event Guidelines','icon_class' => 'bi-journal-text','color_hex' => '#1a3a5c'],
  ['file_key' => 'attendance_sheet','title' => 'Attendance Sheet','icon_class' => 'bi-file-earmark-spreadsheet','color_hex' => '#1a3a5c'],
  ['file_key' => 'officers_list','title' => 'Officers List','icon_class' => 'bi-people','color_hex' => '#1a3a5c'],
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

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

  <style>
    /* ── Root Variables ─────────────────────────────── */
    :root {
      --primary:      #1a3a5c;
      --primary-dark: #122840;
      --primary-light:#2a5298;
      --accent:       #2980b9;
      --bg:           #f0f2f5;
      --card-bg:      #ffffff;
      --border:       #dde3ec;
      --text-dark:    #1a2533;
      --text-muted:   #6b7a8d;
      --shadow-sm:    0 2px 8px rgba(26,58,92,0.08);
      --shadow-md:    0 6px 24px rgba(26,58,92,0.13);
      --shadow-lg:    0 12px 36px rgba(26,58,92,0.18);
      --radius:       14px;
      --radius-sm:    8px;
    }

    /* ── Base ───────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg);
      color: var(--text-dark);
      margin: 0;
      padding: 0;
      -webkit-font-smoothing: antialiased;
    }

    main { padding-bottom: 60px; }

    /* ── Hero / Header Section ──────────────────────── */
    .hero-section {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
      padding: 64px 0 56px;
      color: #fff;
      position: relative;
      overflow: hidden;
      margin-top: 60px;
    }

    /* Decorative circles */
    .hero-section::before,
    .hero-section::after {
      content: '';
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
    }
    .hero-section::before {
      width: 480px;
      height: 480px;
      top: -160px;
      right: -80px;
      background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
    }
    .hero-section::after {
      width: 300px;
      height: 300px;
      bottom: -120px;
      left: -60px;
      background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
    }

    .hero-inner { position: relative; z-index: 1; }

    /* Hero badge */
    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(255,255,255,0.15);
      border: 1px solid rgba(255,255,255,0.25);
      color: rgba(255,255,255,0.92);
      font-size: 0.8rem;
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      padding: 6px 14px;
      border-radius: 50px;
      margin-bottom: 18px;
    }

    .hero-section h1 {
      font-family: 'Poppins', sans-serif;
      font-size: clamp(2.2rem, 5vw, 2.8rem);
      font-weight: 800;
      letter-spacing: -0.5px;
      margin-bottom: 10px;
      line-height: 1.15;
    }

    .hero-subtitle {
      font-size: 1rem;
      color: rgba(255,255,255,0.78);
      margin-bottom: 32px;
      font-weight: 400;
    }

    /* ── Search Bar ─────────────────────────────────── */
    .search-wrapper {
      max-width: 580px;
      margin: 0 auto;
    }

    .search-box {
      position: relative;
      display: flex;
      align-items: center;
    }

    .search-icon {
      position: absolute;
      left: 20px;
      color: var(--text-muted);
      font-size: 1.1rem;
      pointer-events: none;
      z-index: 2;
    }

    .search-box input {
      width: 100%;
      padding: 17px 52px 17px 52px;
      border: 2px solid transparent;
      border-radius: 50px;
      font-size: 1rem;
      font-family: 'Inter', sans-serif;
      color: var(--text-dark);
      background: #fff;
      box-shadow: var(--shadow-md);
      outline: none;
      transition: border-color 0.25s, box-shadow 0.25s;
    }

    .search-box input::placeholder {
      color: #9aa5b4;
    }

    .search-box input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 4px rgba(41,128,185,0.18), var(--shadow-md);
    }

    .search-box .clear-btn {
      position: absolute;
      right: 12px;
      background: var(--primary);
      border: none;
      width: 38px;
      height: 38px;
      border-radius: 50%;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.9rem;
      cursor: pointer;
      transition: background 0.25s, transform 0.2s;
      z-index: 2;
    }

    .search-box .clear-btn:hover {
      background: var(--primary-dark);
      transform: scale(1.08);
    }

    /* ── Stats Bar ──────────────────────────────────── */
    .stats-bar {
      background: #fff;
      border-bottom: 1px solid var(--border);
      padding: 14px 0;
    }

    .stats-inner {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 10px;
    }

    .stats-label {
      font-size: 0.875rem;
      color: var(--text-muted);
      font-weight: 500;
    }

    .stats-label strong {
      color: var(--primary);
      font-weight: 700;
    }

    .stats-tag {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #eef2f8;
      color: var(--primary);
      font-size: 0.78rem;
      font-weight: 600;
      padding: 5px 12px;
      border-radius: 50px;
      letter-spacing: 0.03em;
    }

    /* ── Resources Container ────────────────────────── */
    .resources-container {
      max-width: 1200px;
      margin: 32px auto;
      padding: 0 24px;
    }

    /* Section Heading */
    .section-heading {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 20px;
    }

    .section-heading .heading-icon {
      width: 36px;
      height: 36px;
      background: var(--primary);
      color: #fff;
      border-radius: var(--radius-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      flex-shrink: 0;
    }

    .section-heading h2 {
      font-family: 'Poppins', sans-serif;
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--primary);
      margin: 0;
    }

    .section-heading .divider {
      flex: 1;
      height: 1px;
      background: var(--border);
      margin-left: 12px;
    }

    /* ── Resources Grid ─────────────────────────────── */
    .resources-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 18px;
    }

    /* ── Resource Card ──────────────────────────────── */
    .resource-card {
      background: var(--card-bg);
      border-radius: var(--radius);
      padding: 22px 24px;
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border);
      display: flex;
      align-items: center;
      gap: 18px;
      transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
      position: relative;
      overflow: hidden;
    }

    /* Left accent line */
    .resource-card::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 4px;
      background: var(--primary);
      opacity: 0;
      transition: opacity 0.25s ease;
      border-radius: var(--radius) 0 0 var(--radius);
    }

    .resource-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-lg);
      border-color: rgba(26,58,92,0.18);
    }

    .resource-card:hover::before {
      opacity: 1;
    }

    /* Icon */
    .icon-wrap {
      width: 60px;
      height: 60px;
      border-radius: var(--radius-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      flex-shrink: 0;
      /* background and color set per-card via inline style */
    }

    /* Content */
    .resource-content {
      flex: 1;
      min-width: 0;
    }

    .resource-title {
      font-family: 'Poppins', sans-serif;
      font-size: 1.05rem;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 5px;
      line-height: 1.3;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .resource-meta {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }

    .meta-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 0.75rem;
      font-weight: 600;
      padding: 3px 9px;
      border-radius: 50px;
    }

    .meta-badge.pdf {
      background: #fde8e8;
      color: #c0392b;
    }

    .meta-badge.official {
      background: #e8f4fd;
      color: var(--primary);
    }

    /* Download Button */
    .btn-download {
      background: var(--primary);
      color: #fff;
      border: none;
      padding: 12px 20px;
      border-radius: var(--radius-sm);
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      font-size: 0.875rem;
      display: inline-flex;
      align-items: center;
      gap: 7px;
      text-decoration: none;
      white-space: nowrap;
      flex-shrink: 0;
      transition: background 0.25s, transform 0.2s, box-shadow 0.25s;
      box-shadow: 0 3px 10px rgba(26,58,92,0.22);
      letter-spacing: 0.02em;
    }

    .btn-download i {
      font-size: 1rem;
    }

    .btn-download:hover {
      background: var(--primary-dark);
      color: #fff;
      transform: translateY(-2px) scale(1.03);
      box-shadow: 0 6px 18px rgba(26,58,92,0.28);
    }

    .btn-download:active {
      transform: translateY(0) scale(0.98);
    }

    /* ── Empty State ────────────────────────────────── */
    .empty-state {
      text-align: center;
      padding: 60px 24px;
      color: var(--text-muted);
      display: none;
    }

    .empty-state .empty-icon {
      width: 72px;
      height: 72px;
      background: #eef2f8;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      color: var(--text-muted);
      margin: 0 auto 20px;
    }

    .empty-state h3 {
      font-family: 'Poppins', sans-serif;
      font-size: 1.15rem;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 8px;
    }

    .empty-state p {
      font-size: 0.9rem;
      margin: 0;
    }

    /* ── Responsive ─────────────────────────────────── */
    @media (max-width: 768px) {
      .hero-section {
        padding: 48px 0 44px;
        margin-top: 56px;
      }

      .hero-section h1 { font-size: 2rem; }
      .hero-subtitle   { font-size: 0.9rem; }

      .resources-grid {
        grid-template-columns: 1fr;
        gap: 14px;
      }

      .resource-card {
        padding: 18px 20px;
        gap: 14px;
      }

      .resource-card:hover {
        transform: translateY(-3px);
      }

      .icon-wrap {
        width: 52px;
        height: 52px;
        font-size: 1.75rem;
      }

      .resource-title { font-size: 0.975rem; }

      .btn-download {
        padding: 10px 16px;
        font-size: 0.82rem;
      }

      .resources-container { padding: 0 16px; }
      .stats-inner          { padding: 0 16px; }
    }

    @media (max-width: 480px) {
      .resource-card {
        flex-wrap: wrap;
      }

      .btn-download {
        width: 100%;
        justify-content: center;
        margin-top: 8px;
      }
    }
  </style>
</head>
<body>
<?php include("partials/navbar.php"); ?>

<main>
  <!-- ── Hero Section ─────────────────────────────── -->
  <div class="hero-section">
    <div class="container text-center hero-inner">
      <div class="hero-badge">
        <i class="bi bi-folder2-open"></i>
        Association Documents
      </div>
      <h1>Resources</h1>
      <p class="hero-subtitle">Download official forms and documents of the association</p>

      <!-- Search -->
      <div class="search-wrapper">
        <div class="search-box">
          <i class="bi bi-search search-icon"></i>
          <input
            id="search"
            type="search"
            placeholder="Search forms, documents..."
            aria-label="Search resources"
            autocomplete="off"
          />
          <button id="clearBtn" class="clear-btn" type="button" title="Clear search">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Stats Bar ──────────────────────────────── -->
  <div class="stats-bar">
    <div class="stats-inner">
      <span class="stats-label">
        Showing <strong id="visibleCount"><?= count($resources) ?></strong> of <strong><?= count($resources) ?></strong> resources
      </span>
      <span class="stats-tag">
        <i class="bi bi-folder2-open"></i>
        Association Documents
      </span>
    </div>
  </div>

  <!-- ── Resources Grid ─────────────────────────── -->
  <div class="resources-container">

    <!-- Section Heading -->
    <div class="section-heading">
      <div class="heading-icon"><i class="bi bi-file-earmark-text"></i></div>
      <h2>Available Documents</h2>
      <div class="divider"></div>
    </div>

    <div id="resourcesGrid" class="resources-grid">
      <?php foreach($resources as $r): ?>
        <?php
          $title       = (string)($r['title']      ?? '');
          $iconClass   = (string)($r['icon_class'] ?? $r['icon']  ?? 'bi-file-earmark-pdf');
          $downloadKey = (string)($r['file_key']   ?? $r['id']    ?? '');

          // Map icon class → display icon + color + soft background
          $iconMap = [
            'bi-file-earmark-pdf'         => ['display' => 'bi-file-earmark-pdf',         'icon' => '#dc3545', 'bg' => '#fdecea'],
            'bi-file-earmark-person'      => ['display' => 'bi-file-earmark-person',      'icon' => '#1a3a5c', 'bg' => '#e8eef6'],
            'bi-file-earmark-spreadsheet' => ['display' => 'bi-file-earmark-spreadsheet', 'icon' => '#198754', 'bg' => '#e6f4ed'],
            'bi-file-earmark-word'        => ['display' => 'bi-file-earmark-word',        'icon' => '#0d6efd', 'bg' => '#e7f0fe'],
            'bi-file-earmark-text'        => ['display' => 'bi-file-earmark-text',        'icon' => '#6c757d', 'bg' => '#f1f3f5'],
            'bi-journal-text'             => ['display' => 'bi-journal-text',             'icon' => '#0d6efd', 'bg' => '#e7f0fe'],
            'bi-people'                   => ['display' => 'bi-people',                   'icon' => '#1a3a5c', 'bg' => '#e8eef6'],
            'bi-file-earmark-zip'         => ['display' => 'bi-file-earmark-zip',         'icon' => '#fd7e14', 'bg' => '#fff3e6'],
            'bi-file-earmark-image'       => ['display' => 'bi-file-earmark-image',       'icon' => '#6f42c1', 'bg' => '#f0ebfd'],
          ];

          // Always fall back to PDF icon so the card never shows an empty gray box
          $colors      = $iconMap[$iconClass] ?? ['display' => 'bi-file-earmark-pdf', 'icon' => '#dc3545', 'bg' => '#fdecea'];
          $displayIcon = $colors['display'];
          $iconColor   = $colors['icon'];
          $iconBg      = $colors['bg'];
        ?>
        <div class="resource-card" data-title="<?= htmlspecialchars(strtolower($title)) ?>">

          <!-- File Icon -->
          <div class="icon-wrap" style="background:<?= $iconBg ?>; color:<?= $iconColor ?>;">
            <i class="bi <?= htmlspecialchars($displayIcon) ?>"></i>
          </div>

          <!-- Title + Meta -->
          <div class="resource-content">
            <div class="resource-title" title="<?= htmlspecialchars($title) ?>">
              <?= htmlspecialchars($title) ?>
            </div>
            <div class="resource-meta">
              <span class="meta-badge pdf"><i class="bi bi-file-earmark-pdf"></i> PDF</span>
              <span class="meta-badge official"><i class="bi bi-building"></i> Association</span>
            </div>
          </div>

          <!-- Download Button -->
          <?php
            // If an actual PDF was uploaded, serve it directly; otherwise use the generator
            $downloadHref = (!empty($r['file_path']))
              ? '../../' . htmlspecialchars($r['file_path'])
              : 'generate_pdfs.php?file=' . urlencode($downloadKey);
          ?>
          <a class="btn-download"
             href="<?= $downloadHref ?>"
             target="_blank"
             rel="noopener noreferrer"
             aria-label="Download <?= htmlspecialchars($title) ?>">
            <i class="bi bi-download"></i>
            Download
          </a>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="empty-state">
      <div class="empty-icon"><i class="bi bi-search"></i></div>
      <h3>No results found</h3>
      <p>No documents match your search. Try a different keyword.</p>
    </div>

  </div>
</main>

<?php include("partials/footer.php"); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const search       = document.getElementById('search');
  const clearBtn     = document.getElementById('clearBtn');
  const cards        = document.querySelectorAll('.resource-card');
  const emptyState   = document.getElementById('emptyState');
  const visibleCount = document.getElementById('visibleCount');
  const total        = cards.length;

  function updateCount(visible) {
    if (visibleCount) visibleCount.textContent = visible;
  }

  function filterCards(query) {
    let visible = 0;
    cards.forEach(card => {
      const title   = card.getAttribute('data-title') || '';
      const matched = !query || title.includes(query);
      card.style.display = matched ? '' : 'none';
      if (matched) visible++;
    });

    if (emptyState) emptyState.style.display = visible === 0 ? 'block' : 'none';
    updateCount(visible);
  }

  // Search input
  search.addEventListener('input', function () {
    filterCards(this.value.toLowerCase().trim());
  });

  // Clear button
  clearBtn.addEventListener('click', function () {
    search.value = '';
    filterCards('');
    search.focus();
  });

  // Escape key
  search.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') clearBtn.click();
  });

  // Initial count
  updateCount(total);
});
</script>

<?php include 'chatbox.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
