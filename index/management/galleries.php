<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../../config/db_connect.php');

// Fetch gallery items (ordered by created_at DESC)
$sql = "SELECT * FROM galleries ORDER BY created_at DESC";
$result = $conn->query($sql);

$galleryGroups = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $images = array_filter(explode(",", $row['images']));
        $galleryGroups[$row['title']][] = [
            'created_at' => $row['created_at'],
            'images' => $images
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Gallery | BFA Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f4f6f9;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }
    .content-wrapper {
      margin-left: 260px;
      padding: 30px;
      min-height: 100vh;
    }
    .page-header {
      margin-bottom: 25px;
    }
    .gallery-section {
      margin-bottom: 40px;
    }
    .gallery-title-main {
      font-weight: 700;
      font-size: 1.3rem;
      color: #2c3e50;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .gallery-card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      overflow: hidden;
      background: #fff;
      cursor: pointer;
    }
    .gallery-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
    }
    .gallery-card img {
      height: 200px;
      width: 100%;
      object-fit: cover;
    }
    .gallery-card .card-body {
      padding: 12px 16px;
    }
    .gallery-date {
      font-size: 0.85rem;
      color: #777;
    }
    /* Lightbox modal image */
    .modal-body img {
      width: 100%;
      height: auto;
      border-radius: 10px;
    }
  </style>
</head>
<body>
<?php include('../navbar.php'); ?>

<div class="content-wrapper">
  <div class="page-header">
    <h2 class="fw-bold">📸 Galleries</h2>
    <p class="text-muted">Browse uploaded photos and memories from events</p>
  </div>

  <?php if (!empty($galleryGroups)): ?>
    <?php 
    $modalIndex = 0; // unique modal per folder
    foreach ($galleryGroups as $title => $items): 
      $modalIndex++;
      $carouselId = "carouselGallery".$modalIndex;
    ?>
      <div class="gallery-section">
        <h4 class="gallery-title-main"><i class="bi bi-folder-fill text-warning"></i> <?= htmlspecialchars($title); ?></h4>
        <div class="row g-4">
          <?php 
          $imgIndex = 0;
          $allImages = [];
          foreach ($items as $item) {
              foreach ($item['images'] as $img) {
                  $allImages[] = [
                      'src' => "../uploads/gallery/".htmlspecialchars(trim($img)),
                      'date' => date("M d, Y", strtotime($item['created_at']))
                  ];
              }
          }
          foreach ($allImages as $i => $imgData): 
          ?>
            <div class="col-md-4 col-lg-3">
              <div class="card gallery-card" data-bs-toggle="modal" data-bs-target="#modal<?= $modalIndex ?>" data-index="<?= $i ?>">
                <img src="<?= $imgData['src'] ?>" alt="Gallery Image">
                <div class="card-body">
                  <p class="gallery-date">Uploaded on: <?= $imgData['date'] ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Modal with carousel -->
        <div class="modal fade" id="modal<?= $modalIndex ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark">
              <div class="modal-body p-0">
                <div id="<?= $carouselId ?>" class="carousel slide" data-bs-ride="false">
                  <div class="carousel-inner">
                    <?php foreach ($allImages as $i => $imgData): ?>
                      <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                        <img src="<?= $imgData['src'] ?>" class="d-block w-100" alt="Image">
                        <div class="carousel-caption d-none d-md-block">
                          <p class="small"><?= $imgData['date'] ?></p>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <button class="carousel-control-prev" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="text-muted">No images uploaded yet.</p>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
