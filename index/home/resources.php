<?php
// Minimal server-side setup (no DB needed for static resources)
$resources = [
  ['id'=>'membership_form','title'=>'Membership Form','icon'=>'bi-file-earmark-person','color'=>'#0d6efd'],
  ['id'=>'event_guidelines','title'=>'Event Guidelines','icon'=>'bi-journal-text','color'=>'#0dcaf0'],
  ['id'=>'attendance_sheet','title'=>'Attendance Sheet','icon'=>'bi-file-earmark-spreadsheet','color'=>'#6c757d'],
  ['id'=>'officers_list','title'=>'Officers List','icon'=>'bi-people','color'=>'#0d6efd'],
];
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

  <style>
    :root{
      --bg:#f6f8fb;
      --card:#ffffff;
      --muted:#6b7280;
      --accent:#0d6efd;
      --radius:12px;
      --shadow: 0 6px 18px rgba(15,23,42,0.06);
    }
    html,body{height:100%}
    body{
      margin:0;
      font-family: Inter, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      background:var(--bg);
      color:#0f172a;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
      line-height:1.45;
    }
    a{color:inherit;text-decoration:none}
    main{padding-top:88px;padding-bottom:48px}

    /* Hero */
    .hero{
      background: linear-gradient(90deg,#072f5f 0%, #0d6efd 100%);
      color:#fff;
      padding:64px 16px;
      border-radius:0 0 20px 20px;
      box-shadow: var(--shadow);
    }
    .hero h1{font-weight:700;margin:0 0 8px;font-size:1.9rem}
    .hero p{margin:0;color:rgba(255,255,255,0.9)}

    /* Container */
    .container-narrow{max-width:1100px;margin:0 auto;padding:32px 16px}

    /* Search */
    .search-wrap{
      display:flex;gap:12px;align-items:center;margin:18px 0 28px;
    }
    .search-input{
      flex:1;padding:12px 14px;border-radius:10px;border:1px solid #e6eef9;background:#fff;
      box-shadow: 0 1px 2px rgba(2,6,23,0.02);
      font-size:0.95rem;
    }
    .search-input:focus{outline:none;box-shadow:0 6px 18px rgba(13,110,253,0.08);border-color:var(--accent)}

    /* Resource list */
    .resources-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px}
    .resource-card{
      background:var(--card);border-radius:var(--radius);padding:18px;display:flex;align-items:center;justify-content:space-between;
      gap:12px;box-shadow:var(--shadow);transition:transform .16s ease,box-shadow .16s ease;
      border:1px solid rgba(15,23,42,0.03);
    }
    .resource-card:hover{transform:translateY(-6px);box-shadow:0 18px 40px rgba(2,6,23,0.08)}
    .resource-left{display:flex;gap:12px;align-items:center}
    .icon-wrap{width:56px;height:56px;border-radius:10px;display:grid;place-items:center;color:#fff;font-size:1.4rem;flex-shrink:0}
    .resource-title{font-weight:600;font-size:1rem}
    .resource-sub{font-size:0.88rem;color:var(--muted);margin-top:4px}

    /* Download button */
    .btn-download{
      display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:10px;background:#0d6efd;color:#fff;border:none;
      font-weight:600;font-size:0.95rem;box-shadow:0 6px 18px rgba(13,110,253,0.08);
    }
    .btn-download svg{opacity:0.95}
    .btn-download:focus{outline:3px solid rgba(13,110,253,0.12);outline-offset:2px}
    .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}

    /* Footer spacing */
    .bottom-space{height:32px}
    @media (max-width:575px){
      .hero{padding:40px 12px;border-radius:0 0 14px 14px}
      .icon-wrap{width:48px;height:48px;font-size:1.15rem;border-radius:8px}
      .btn-download{padding:8px 12px}
    }
  </style>
</head>
<body>
<?php include("partials/navbar.php"); ?>

<main>
  <header class="hero" role="banner" aria-labelledby="resourcesHeading">
    <div class="container-narrow">
      <h1 id="resourcesHeading"><span class="bi bi-folder2-open me-2" aria-hidden="true"></span>Resources</h1>
      <p>Download official forms, guides, and templates. Files open in a new tab.</p>
    </div>
  </header>

  <div class="container-narrow" role="main">
    <!-- Search + actions -->
    <div class="search-wrap" role="search" aria-label="Search resources">
      <label for="search" class="sr-only">Search resources</label>
      <input id="search" class="search-input" type="search" placeholder="Search resources (e.g. membership, attendance, officers)" aria-label="Search resources" />
      <button id="clearBtn" class="btn btn-light" type="button" title="Clear search" aria-label="Clear search">
        <span class="bi bi-x-lg"></span>
      </button>
    </div>

    <!-- Resource grid -->
    <section aria-labelledby="availableResources">
      <h2 id="availableResources" class="sr-only">Available resources</h2>
      <div id="resourcesGrid" class="resources-grid" aria-live="polite">
        <?php foreach($resources as $r): ?>
          <article class="resource-card" data-title="<?= htmlspecialchars(strtolower($r['title'])) ?>">
            <div class="resource-left">
              <div class="icon-wrap" style="background: <?= htmlspecialchars($r['color']) ?>;">
                <i class="bi <?= htmlspecialchars($r['icon']) ?>" aria-hidden="true"></i>
              </div>
              <div>
                <div class="resource-title"><?= htmlspecialchars($r['title']) ?></div>
                <div class="resource-sub">PDF · Official</div>
              </div>
            </div>

            <div class="resource-actions">
              <a class="btn-download" href="generate_pdfs.php?file=<?= urlencode($r['id']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Download <?= htmlspecialchars($r['title']) ?>">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                  <path d="M.75 11.5v2A1.75 1.75 0 0 0 2.5 15h11a1.75 1.75 0 0 0 1.75-1.5v-2" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M8 1v9" stroke="#fff" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M4.5 5.5L8 9l3.5-3.5" stroke="#fff" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Download
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <div class="bottom-space" aria-hidden="true"></div>
  </div>
</main>

<?php include("partials/footer.php"); ?>

<!-- Scripts (deferred) -->
<script>
  (function(){
    // DOM refs
    const search = document.getElementById('search');
    const grid = document.getElementById('resourcesGrid');
    const clearBtn = document.getElementById('clearBtn');
    const cards = Array.from(grid.querySelectorAll('.resource-card'));

    // Debounce helper
    function debounce(fn, delay){ let t; return (...args)=>{ clearTimeout(t); t=setTimeout(()=>fn(...args),delay); }; }

    // Filter logic
    const filter = debounce(function(ev){
      const q = (ev?.target?.value || '').trim().toLowerCase();
      cards.forEach(card => {
        const title = card.getAttribute('data-title') || '';
        card.style.display = (!q || title.includes(q)) ? '' : 'none';
      });
      grid.setAttribute('aria-busy','false');
    }, 150);

    // Events
    search.addEventListener('input', function(e){
      grid.setAttribute('aria-busy','true');
      filter(e);
    });

    clearBtn.addEventListener('click', function(){
      search.value = '';
      search.dispatchEvent(new Event('input'));
      search.focus();
    });

    // Accessibility: support Escape to clear
    search.addEventListener('keydown', function(e){
      if(e.key === 'Escape'){ clearBtn.click(); }
    });
  })();
</script>
<?php include 'chatbox.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
