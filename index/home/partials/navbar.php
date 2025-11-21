<!-- Skip to main content for keyboard users -->
<a class="visually-hidden-focusable skip-link" href="#main">Skip to content</a>

<nav class="navbar navbar-expand-lg fixed-top pro-navbar" role="navigation" aria-label="Main navigation">
  <div class="container">
    <!-- Mobile logo -->
    <a class="navbar-brand d-lg-none" href="user_home.php" aria-label="Home">
      <img src="../images/logo1.png" alt="Association logo" class="brand-img" />
    </a>

    <!-- Toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-center" id="mainNav">
      <ul class="navbar-nav align-items-center mx-auto">
        <li class="nav-item"><a class="nav-link" href="user_home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="about_us.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>

        <!-- Center logo for larger screens -->
        <li class="nav-item d-none d-lg-flex px-3">
          <a class="navbar-brand d-flex align-items-center" href="user_home.php" aria-label="Home">
            <img src="../images/logo1.png" alt="Association logo" class="brand-img desktop" />
          </a>
        </li>

        <li class="nav-item"><a class="nav-link" href="announcement.php">Announcements</a></li>
        <li class="nav-item"><a class="nav-link" href="resources.php">Resources</a></li>
        <li class="nav-item"><a class="nav-link" href="contact_us.php">Contact</a></li>
      </ul>
    </div>
  </div>
</nav>

<style>
  :root {
    --nav-height: 72px;
    --nav-shrink-height: 52px;
    --primary: #0b5ed7;
    --accent: #ff7043;
    --muted: #6b7280;
    --glass: rgba(255,255,255,0.92);
    --radius: 10px;
    --shadow-sm: 0 6px 18px rgba(8,24,48,0.06);
  }

  /* Skip link */
  .skip-link {
    position: absolute;
    left: -999px;
    top: auto;
    width: 1px;
    height: 1px;
    overflow: hidden;
    z-index: 10000;
  }
  .skip-link:focus, .visually-hidden-focusable:focus {
    position: fixed;
    left: 16px;
    top: 16px;
    width: auto;
    height: auto;
    padding: 10px 14px;
    background: #0b5ed7;
    color: #fff;
    border-radius: 8px;
    z-index: 11000;
    text-decoration: none;
  }

  /* Navbar */
  .pro-navbar {
    background: var(--glass);
    backdrop-filter: blur(6px);
    padding: 0.9rem 0;
    transition: padding .2s ease, box-shadow .2s ease;
    border-bottom: 1px solid rgba(15,23,42,0.04);
    box-shadow: var(--shadow-sm);
    z-index: 1030;
  }
  .pro-navbar .brand-img { height: 48px; transition: height .18s ease; display:block; }
  .pro-navbar .brand-img.desktop { height: 64px; }

  /* Links */
  .pro-navbar .nav-link {
    color: var(--primary) !important;
    font-weight: 600;
    padding: 8px 12px;
    border-radius: 8px;
    transition: transform .12s ease, background .12s ease, color .12s ease;
  }
  .pro-navbar .nav-link:hover,
  .pro-navbar .nav-link:focus {
    color: var(--primary) !important;
    background: rgba(11,94,215,0.06);
    transform: translateY(-2px);
    outline: none;
  }
  .pro-navbar .nav-link.active {
    color: var(--accent) !important;
    box-shadow: inset 0 -3px 0 var(--accent);
    background: transparent;
  }

  /* Shrink */
  .pro-navbar.shrink {
    padding: 0.35rem 0;
    box-shadow: 0 8px 28px rgba(8,24,48,0.08);
  }
  .pro-navbar.shrink .brand-img { height: 40px; }
  .pro-navbar.shrink .brand-img.desktop { height: 44px; }

  /* Toggler */
  .navbar-toggler {
    border: 1px solid rgba(2,6,23,0.06);
    border-radius: 8px;
    width: 44px; height: 44px;
    display: inline-grid; place-items: center;
    transition: box-shadow .15s ease;
  }
  .navbar-toggler:focus { box-shadow: 0 6px 18px rgba(11,94,215,0.12); outline: none; }

  /* Accessibility focus-visible */
  .pro-navbar .nav-link:focus-visible {
    outline: 3px solid rgba(13,110,253,0.18);
    border-radius: 8px;
  }

  /* Responsive */
  @media (max-width: 991.98px) {
    .pro-navbar { padding: 0.55rem 0; }
    .pro-navbar .brand-img.desktop { display: none; }
  }
</style>

<script>
(function () {
  'use strict';

  // helpers
  const debounce = (fn, ms = 50) => { let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); }; };

  document.addEventListener('DOMContentLoaded', function () {
    const navbar = document.querySelector('.pro-navbar');
    if (!navbar) return;

    // Shrink on scroll
    const onScroll = () => {
      if (window.scrollY > 48) navbar.classList.add('shrink'); else navbar.classList.remove('shrink');
    };
    window.addEventListener('scroll', debounce(onScroll, 60), { passive: true });
    onScroll();

    // Mark active link (by filename)
    const links = Array.from(navbar.querySelectorAll('.nav-link'));
    const current = (location.pathname || '').split('/').pop().toLowerCase() || 'user_home.php';
    links.forEach(a => {
      const href = (a.getAttribute('href') || '').split('/').pop().toLowerCase();
      if (!href) return;
      if (href === current || (current === '' && (href === 'index.php' || href === 'user_home.php'))) {
        a.classList.add('active');
        a.setAttribute('aria-current', 'page');
      } else {
        a.classList.remove('active');
        a.removeAttribute('aria-current');
      }
    });

    // Close mobile menu on link click
    const toggler = document.querySelector('.navbar-toggler');
    const collapseEl = document.getElementById('mainNav');
    links.forEach(link => {
      link.addEventListener('click', () => {
        if (!toggler) return;
        const isVisible = window.getComputedStyle(toggler).display !== 'none';
        if (isVisible && collapseEl && collapseEl.classList.contains('show')) {
          toggler.click();
        }
      });
    });
  });
})();
</script>
