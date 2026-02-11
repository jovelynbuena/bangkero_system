<?php
include('../../config/db_connect.php');

// Fetch all galleries
$gallery_sql = "SELECT * FROM galleries ORDER BY created_at DESC";
$gallery_result = $conn->query($gallery_sql);

// Pagination
$items_per_page = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Count total galleries
$count_sql = "SELECT COUNT(*) as total FROM galleries";
$count_result = $conn->query($count_sql);
$total_items = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

// Fetch galleries with pagination
$gallery_sql = "SELECT * FROM galleries ORDER BY created_at DESC LIMIT $items_per_page OFFSET $offset";
$gallery_result = $conn->query($gallery_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event Memories Gallery - Bangkero & Fishermen Association</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <link href="../../css/main-style.css" rel="stylesheet">
  <style>
    .hero-section {
      background: linear-gradient(135deg, rgba(44, 62, 80, 0.85), rgba(26, 37, 47, 0.90)), url('../images/bg1.jpg') center/cover no-repeat;
      min-height: 320px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      text-shadow: 0 4px 12px rgba(0,0,0,0.3);
      position: relative;
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
    }
    .gallery-section {
      padding: 60px 0;
      background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }
    .gallery-item {
      position: relative;
      overflow: hidden;
      border-radius: 16px;
      box-shadow: 0 6px 24px rgba(44, 62, 80, 0.1);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      cursor: pointer;
      border: 2px solid #e2e8f0;
      height: 280px;
    }
    .gallery-item:hover {
      transform: translateY(-8px);
      box-shadow: 0 16px 48px rgba(44, 62, 80, 0.2);
      border-color: var(--primary);
    }
    .gallery-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.6s ease;
    }
    .gallery-item:hover img {
      transform: scale(1.1);
    }
    .gallery-item .overlay {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      background: linear-gradient(to top, rgba(44, 62, 80, 0.95), transparent);
      padding: 20px;
      transform: translateY(100%);
      transition: transform 0.4s ease;
    }
    .gallery-item:hover .overlay {
      transform: translateY(0);
    }
    .gallery-item .overlay h5 {
      color: white;
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      margin-bottom: 5px;
      font-size: 1.1rem;
    }
    .gallery-item .overlay p {
      color: #e2e8f0;
      font-size: 0.85rem;
      margin-bottom: 0;
    }
    .pagination .page-link {
      color: var(--primary);
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      margin: 0 5px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .pagination .page-link:hover {
      background: var(--light);
      border-color: var(--primary);
    }
    .pagination .page-item.active .page-link {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      border-color: var(--primary);
      box-shadow: 0 4px 12px rgba(44, 62, 80, 0.25);
    }
    .empty-state {
      text-align: center;
      padding: 80px 20px;
      background: linear-gradient(135deg, #f8fafc, #ffffff);
      border-radius: 24px;
      border: 2px dashed #cbd5e1;
    }
    .empty-state i {
      font-size: 5rem;
      color: #cbd5e1;
      margin-bottom: 24px;
    }
  </style>
</head>
<body>
  <?php include("partials/navbar.php"); ?>

  <!-- Hero Section -->
  <section class="hero-section text-center">
    <div class="container">
      <h1><i class="bi bi-images me-3"></i>Event Memories Gallery</h1>
      <p class="lead mb-0">Explore our collection of memorable moments from past events</p>
    </div>
  </section>

  <!-- Gallery Section -->
  <section class="gallery-section">
    <div class="container">
      <div class="row g-4">
        <?php
        if ($gallery_result && $gallery_result->num_rows > 0):
          $gallery_fs_dir = realpath(__DIR__ . '/../../uploads/gallery') . '/';
          $gallery_url_base = '../../uploads/gallery/';
          
          while($gallery = $gallery_result->fetch_assoc()):
            $images = array_filter(array_map('trim', explode(",", $gallery['images'])));
            $images = array_values(array_filter($images, function($img) use ($gallery_fs_dir) {
              return $img !== '' && file_exists($gallery_fs_dir . $img);
            }));
            
            if (empty($images)) continue;
            
            $firstImage = $images[0];
            $imgPath = $gallery_url_base . $firstImage;
            $imageCount = count($images);
        ?>
          <div class="col-md-6 col-lg-4">
            <div class="gallery-item" data-bs-toggle="modal" data-bs-target="#galleryModal<?= $gallery['id'] ?>">
              <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($gallery['title']) ?>">
              <div class="overlay">
                <h5><?= htmlspecialchars($gallery['title']) ?></h5>
                <p><i class="bi bi-images me-1"></i><?= $imageCount ?> photo<?= $imageCount > 1 ? 's' : '' ?> | <?= date('M d, Y', strtotime($gallery['created_at'])) ?></p>
              </div>
            </div>
          </div>

          <!-- Modal for Gallery -->
          <div class="modal fade" id="galleryModal<?= $gallery['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
              <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border: none;">
                  <h5 class="modal-title fw-bold"><?= htmlspecialchars($gallery['title']) ?></h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0" style="background: #f8f9fa;">
                  <div id="carousel<?= $gallery['id'] ?>" class="carousel slide" data-bs-ride="false">
                    <div class="carousel-inner">
                      <?php foreach($images as $i => $img): ?>
                        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                          <img src="<?= htmlspecialchars($gallery_url_base . $img) ?>" class="d-block w-100" alt="Gallery Image" style="max-height:85vh; object-fit: contain;">
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $gallery['id'] ?>" data-bs-slide="prev">
                      <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $gallery['id'] ?>" data-bs-slide="next">
                      <span class="carousel-control-next-icon"></span>
                    </button>
                    <div class="carousel-indicators">
                      <?php foreach($images as $i => $img): ?>
                        <button type="button" data-bs-target="#carousel<?= $gallery['id'] ?>" data-bs-slide-to="<?= $i ?>" <?= $i === 0 ? 'class="active"' : '' ?>></button>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; else: ?>
          <div class="col-12">
            <div class="empty-state">
              <i class="bi bi-images"></i>
              <h3 class="text-muted mb-3">No Galleries Yet</h3>
              <p class="text-muted">Event photos will appear here once they are uploaded.</p>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
        <nav class="mt-5">
          <ul class="pagination justify-content-center">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $page - 1 ?>"><i class="bi bi-chevron-left"></i></a>
            </li>
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $page + 1 ?>"><i class="bi bi-chevron-right"></i></a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
  </section>

  <?php include("partials/footer.php"); ?>
  <?php include 'chatbox.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
