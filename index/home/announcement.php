<?php
include('../../config/db_connect.php');

// Fetch latest announcements for the carousel (limit 5)
$carousel_sql = "SELECT * FROM announcements ORDER BY date_posted DESC LIMIT 5";
$carousel_result = $conn->query($carousel_sql);

// Fetch all announcements for list view
$announcement_sql = "SELECT * FROM announcements ORDER BY date_posted DESC";
$announcement_result = $conn->query($announcement_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Announcements — Bangkero & Fishermen Association</title>

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

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
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }
    .hero-section .search-box button:hover {
      background: var(--dark);
      transform: translateY(-50%) scale(1.05);
    }

    /* Tabs Navigation */
    .tabs-nav {
      display: none;
    }

    /* Announcement List */
    .announcement-list {
      max-width: 900px;
      margin: 25px auto;
      padding: 0 20px;
    }
    .announcement-item {
      display: flex;
      gap: 20px;
      padding: 20px 0;
      border-bottom: 1px solid #e2e8f0;
      transition: all 0.3s ease;
      cursor: pointer;
    }
    .announcement-item:hover {
      background: #f8f9fa;
      padding-left: 15px;
      padding-right: 15px;
      border-radius: 12px;
      border-bottom-color: transparent;
    }
    .announcement-date {
      flex-shrink: 0;
      text-align: center;
      width: 80px;
    }
    .announcement-date .day {
      font-family: 'Poppins', sans-serif;
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary);
      line-height: 1;
      display: block;
    }
    .announcement-date .month {
      font-size: 0.9rem;
      color: #64748b;
      font-weight: 600;
      text-transform: uppercase;
      margin-top: 5px;
      display: block;
    }
    .announcement-content {
      flex: 1;
    }
    .announcement-content h3 {
      font-family: 'Poppins', sans-serif;
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--dark);
      margin: 0 0 10px;
      line-height: 1.4;
    }
    .announcement-content h3:hover {
      color: var(--primary);
    }
    .announcement-meta {
      font-size: 0.9rem;
      color: #64748b;
      margin-bottom: 12px;
    }
    .announcement-excerpt {
      color: #64748b;
      line-height: 1.7;
      margin: 0;
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 80px 20px;
      color: #64748b;
    }
    .empty-state i {
      font-size: 4rem;
      color: var(--gray);
      margin-bottom: 20px;
    }

    /* Modal Styling */
    .modal-content {
      border-radius: 18px;
      border: none;
      box-shadow: 0 12px 48px rgba(44, 62, 80, 0.2);
    }
    .modal-header {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      border-radius: 18px 18px 0 0;
      padding: 25px 30px;
      border: none;
    }
    .modal-title {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 1.5rem;
    }
    .modal-body {
      padding: 30px;
    }
    .modal-body img {
      border-radius: 12px;
      margin-bottom: 20px;
    }

    @media (max-width: 768px) {
      .hero-section h1 {
        font-size: 2rem;
      }
      .announcement-item {
        flex-direction: column;
        gap: 10px;
      }
      .announcement-date {
        width: 100%;
        text-align: left;
        display: flex;
        gap: 10px;
        align-items: center;
      }
      .announcement-date .day {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <?php include("partials/navbar.php"); ?>

  <!-- Hero Section with Search -->
  <section class="hero-section">
    <div class="container text-center">
      <i class="bi bi-megaphone-fill" style="font-size: 4rem; opacity: 0.9;"></i>
      <h1>Announcements</h1>
      <p style="font-size: 1.1rem; opacity: 0.95; max-width: 600px; margin: 0 auto;">
        Stay updated with the latest news and announcements from Bangkero & Fishermen Association
      </p>
      
      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search announcements..." aria-label="Search announcements">
        <button type="button" aria-label="Search">
          <i class="bi bi-search"></i>
        </button>
      </div>
    </div>
  </section>

  <main>
    <!-- Announcements List -->
    <div class="announcement-list">
      <?php
      mysqli_data_seek($announcement_result, 0);
      if ($announcement_result && $announcement_result->num_rows > 0):
        while ($row = $announcement_result->fetch_assoc()):
          $title = htmlspecialchars($row['title']);
          $date = date("F j, Y", strtotime($row['date_posted']));
          $day = date("d", strtotime($row['date_posted']));
          $month = date("M", strtotime($row['date_posted']));
          $year = date("Y", strtotime($row['date_posted']));
          $content = htmlspecialchars($row['content']);
          $thumb = !empty($row['image']) && file_exists(__DIR__ . "/../../uploads/" . $row['image'])
            ? "../../uploads/" . htmlspecialchars($row['image'])
            : "../images/bg.png";
          $excerptPlain = htmlspecialchars(mb_substr(strip_tags($row['content']), 0, 180));
      ?>
        <div class="announcement-item" 
             data-bs-toggle="modal" 
             data-bs-target="#announceModal"
             data-title="<?= $title ?>"
             data-date="<?= $date ?>"
             data-image="<?= $thumb ?>"
             data-content="<?= $content ?>">
          
          <div class="announcement-date">
            <span class="day"><?= $day ?></span>
            <span class="month"><?= $month ?> <?= $year ?></span>
          </div>
          
          <div class="announcement-content">
            <h3><?= $title ?></h3>
            <div class="announcement-meta">
              <i class="bi bi-person-circle"></i> by Admin | 
              <i class="bi bi-calendar3"></i> <?= $date ?>
            </div>
            <p class="announcement-excerpt">
              <?= $excerptPlain ?><?= (mb_strlen(strip_tags($row['content'])) > 180 ? '...' : '') ?>
            </p>
          </div>
        </div>
      <?php
        endwhile;
      else:
      ?>
        <div class="empty-state">
          <i class="bi bi-inbox"></i>
          <h4>No Announcements Yet</h4>
          <p>There are no announcements at the moment. Check back later for updates.</p>
        </div>
      <?php endif; ?>
  </main>

  <!-- Announcement Modal -->
  <div class="modal fade" id="announceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 id="modalTitle" class="modal-title mb-1"></h5>
            <small id="modalMeta" style="opacity: 0.9;"></small>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <img id="modalImage" src="" alt="" class="img-fluid" style="max-height:400px; width:100%; object-fit:cover; border-radius: 12px; margin-bottom: 20px;">
          <div id="modalContent" style="line-height: 1.8; color: #64748b;"></div>
        </div>
      </div>
    </div>
  </div>

  <?php include("partials/footer.php"); ?>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      // Modal population
      const announceModal = document.getElementById('announceModal');
      announceModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) return;

        const title = button.getAttribute('data-title') || '';
        const date = button.getAttribute('data-date') || '';
        const image = button.getAttribute('data-image') || '';
        const rawContent = button.getAttribute('data-content') || '';

        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalMeta').textContent = date;
        const imgEl = document.getElementById('modalImage');
        imgEl.src = image;
        imgEl.alt = title;

        const contentEl = document.getElementById('modalContent');
        contentEl.innerHTML = rawContent.replace(/\r?\n/g, '<br>');
      });

      // Search functionality
      const searchInput = document.getElementById('searchInput');
      const announcementItems = document.querySelectorAll('.announcement-item');

      searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();

        announcementItems.forEach(item => {
          const title = item.querySelector('h3').textContent.toLowerCase();
          const excerpt = item.querySelector('.announcement-excerpt').textContent.toLowerCase();

          if (title.includes(searchTerm) || excerpt.includes(searchTerm)) {
            item.style.display = 'flex';
          } else {
            item.style.display = 'none';
          }
        });
      });
    });
  </script>
  <?php include 'chatbox.php'; ?>

</body>
</html>
