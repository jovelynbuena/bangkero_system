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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Lora:wght@600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg: #f6f8fb;
      --card: #ffffff;
      --muted: #667085;
      --accent: #0d6efd;
      --primary-dark: #063b57;
      --radius: 12px;
      --shadow-sm: 0 4px 18px rgba(2,6,23,0.06);
    }
    html,body{height:100%}
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background:var(--bg);
      color:#0f172a;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
      line-height:1.5;
    }

    /* Navbar spacer if navbar is fixed in partials */
    main{padding-top:88px;padding-bottom:48px}

    /* Carousel */
    .announcement-carousel .carousel-item{
      height: 56vh;
      min-height: 220px;
      background-size: cover;
      background-position: center;
      border-radius: 14px;
      overflow: hidden;
      position: relative;
    }
    .announcement-carousel .overlay{
      position:absolute; inset:0;
      background: linear-gradient(180deg, rgba(3,37,65,0.18) 0%, rgba(3,37,65,0.6) 70%);
      z-index:1;
    }
    .announcement-carousel .carousel-caption{
      z-index:2;
      left: 48px;
      right: 48px;
      bottom: 32px;
      text-align:left;
    }
    .announcement-carousel h2{
      font-family: Lora, serif;
      font-size: clamp(1.2rem, 2.6vw, 2.0rem);
      color: #fff;
      margin:0 0 8px;
      font-weight:700;
    }
    .announcement-carousel p{
      margin:0;color:rgba(255,255,255,0.92);max-width:70ch;font-size:0.98rem;
    }

    /* Announcements list */
    .container-narrow{max-width:1100px;margin:0 auto;padding:24px 16px}
    .announcement-list{margin-top:24px}
    .announce-card{
      display:flex;gap:18px;align-items:flex-start;background:var(--card);padding:18px;border-radius:12px;
      box-shadow:var(--shadow-sm);border:1px solid rgba(2,6,23,0.03);
      transition:transform .14s ease,box-shadow .14s ease;
    }
    .announce-card:hover{transform:translateY(-6px);box-shadow:0 18px 40px rgba(2,6,23,0.06)}
    .announce-thumb{
      width:120px;height:84px;border-radius:8px;flex-shrink:0;background:#e9eefb;background-size:cover;background-position:center;
      display:block;
    }
    .announce-body{flex:1}
    .announce-title{font-family:Lora,serif;font-size:1.05rem;margin:0;color:var(--primary-dark);font-weight:600}
    .announce-meta{font-size:0.88rem;color:var(--muted);margin:6px 0}
    .announce-excerpt{color:#374151;margin:0 0 10px}

    .btn-read{
      --bs-btn-padding-y: .45rem;
      --bs-btn-padding-x: .75rem;
      font-size:0.92rem;border-radius:8px;padding:.4rem .8rem;
    }

    /* Empty state */
    .empty-state{padding:48px 0;text-align:center;color:var(--muted)}

    @media (max-width:720px){
      .announcement-carousel .carousel-caption{left:16px;right:16px;bottom:18px}
      .announce-card{flex-direction:column;align-items:stretch}
      .announce-thumb{width:100%;height:160px}
    }
  </style>
</head>
<body>
  <?php include("partials/navbar.php"); ?>

  <main>
    <div class="container-narrow">

      <!-- Top: Carousel -->
      <section class="announcement-carousel" aria-label="Featured announcements">
        <div id="announcementCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner">
            <?php
            $active = true;
            if ($carousel_result && $carousel_result->num_rows > 0):
              while ($row = $carousel_result->fetch_assoc()):
                // build image path (fallback to default)
                $imgPath = !empty($row['image']) && file_exists(__DIR__ . "/../../uploads/" . $row['image'])
                  ? "../../uploads/" . htmlspecialchars($row['image'])
                  : "../images/bg.png";

                $titleEsc = htmlspecialchars($row['title']);
                $excerpt = htmlspecialchars(mb_substr(strip_tags($row['content']), 0, 220));
            ?>
              <div class="carousel-item <?= $active ? 'active' : '' ?>" style="background-image: url('<?= $imgPath ?>');">
                <div class="overlay" aria-hidden="true"></div>
                <div class="carousel-caption">
                  <h2><?= $titleEsc ?></h2>
                  <p><?= $excerpt ?><?= (mb_strlen(strip_tags($row['content'])) > 220 ? '...' : '') ?></p>
                </div>
              </div>
            <?php
                $active = false;
              endwhile;
            else:
            ?>
              <div class="carousel-item active" style="background-image: url('../images/bg.png');">
                <div class="overlay" aria-hidden="true"></div>
                <div class="carousel-caption">
                  <h2>No Announcements</h2>
                  <p>There are no featured announcements at the moment. Check back later for updates.</p>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <?php if ($carousel_result && $carousel_result->num_rows > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#announcementCarousel" data-bs-slide="prev" aria-label="Previous slide">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#announcementCarousel" data-bs-slide="next" aria-label="Next slide">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
          <?php endif; ?>
        </div>
      </section>

      <!-- List: Announcements -->
      <section class="announcement-list" aria-labelledby="announcementsHeading">
        <h3 id="announcementsHeading" class="mt-4 mb-3" style="font-family:Lora,serif;color:var(--primary-dark)">Latest Announcements</h3>

        <?php if ($announcement_result && $announcement_result->num_rows > 0): ?>
          <div class="d-grid gap-3">
            <?php while ($row = $announcement_result->fetch_assoc()): ?>
              <?php
                $title = htmlspecialchars($row['title']);
                $date = date("F j, Y", strtotime($row['date_posted']));
                $content = nl2br(htmlspecialchars($row['content']));
                // thumbnail fallback
                $thumb = !empty($row['image']) && file_exists(__DIR__ . "/../../uploads/" . $row['image'])
                  ? "../../uploads/" . htmlspecialchars($row['image'])
                  : "../images/bg.png";
                $excerptPlain = htmlspecialchars(mb_substr(strip_tags($row['content']), 0, 220));
              ?>
              <article class="announce-card" aria-labelledby="ann-<?= $row['id'] ?>">
                <img class="announce-thumb" src="<?= $thumb ?>" alt="<?= $title ?>" loading="lazy" width="320" height="180">
                <div class="announce-body">
                  <h4 id="ann-<?= $row['id'] ?>" class="announce-title"><?= $title ?></h4>
                  <div class="announce-meta">Posted on <?= $date ?> · by Admin</div>
                  <p class="announce-excerpt"><?= $excerptPlain ?><?= (mb_strlen(strip_tags($row['content'])) > 220 ? '...' : '') ?></p>

                  <div class="d-flex gap-2 align-items-center">
                    <button
                      class="btn btn-outline-primary btn-read"
                      data-bs-toggle="modal"
                      data-bs-target="#announceModal"
                      data-title="<?= $title ?>"
                      data-date="<?= $date ?>"
                      data-image="<?= $thumb ?>"
                      data-content="<?= htmlspecialchars($row['content']) ?>">
                      <i class="bi bi-eye me-1"></i> Read more
                    </button>

                    <a class="btn btn-link text-muted" href="mailto:info@example.com?subject=Inquiry about: <?= rawurlencode($title) ?>" aria-label="Contact about <?= $title ?>">
                      <i class="bi bi-envelope me-1"></i> Contact
                    </a>
                  </div>
                </div>
              </article>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <p class="mb-2">No announcements available.</p>
            <small class="text-muted">Please check back later or contact the administrator.</small>
          </div>
        <?php endif; ?>
      </section>

    </div>
  </main>

  <!-- Announcement Modal (re-usable) -->
  <div class="modal fade" id="announceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="border-radius:12px;">
        <div class="modal-header border-0">
          <div>
            <h5 id="modalTitle" class="mb-1"></h5>
            <small id="modalMeta" class="text-muted"></small>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <img id="modalImage" src="" alt="" class="img-fluid rounded mb-3" style="max-height:320px;object-fit:cover;width:100%;">
          <div id="modalContent" class="fs-6 text-gray-700"></div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <?php include("partials/footer.php"); ?>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  <script>
    // DOM ready
    document.addEventListener('DOMContentLoaded', function(){
      // Populate modal with announcement data
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

        // sanitize & convert newlines to <br> (content comes escaped server-side)
        const contentEl = document.getElementById('modalContent');
        contentEl.innerHTML = rawContent.replace(/\r?\n/g, '<br>');
      });

      // Improve carousel behavior: pause on hover
      const carouselEl = document.querySelector('#announcementCarousel');
      if (carouselEl) {
        carouselEl.addEventListener('mouseenter', () => {
          const bs = bootstrap.Carousel.getInstance(carouselEl);
          if (bs) bs.pause();
        });
        carouselEl.addEventListener('mouseleave', () => {
          const bs = bootstrap.Carousel.getInstance(carouselEl);
          if (bs) bs.cycle();
        });
      }
    });
  </script>
</body>
</html>
