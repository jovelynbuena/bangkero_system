<!-- Skip to main content for keyboard users -->
<a class="visually-hidden-focusable skip-link" href="#main">Skip to content</a>

<nav class="navbar navbar-expand-lg fixed-top pro-navbar" role="navigation" aria-label="Main navigation">
  <div class="container-fluid px-4">
    
    <!-- LEFT SIDE: Logo + Association Name -->
    <a class="navbar-brand d-flex align-items-center" href="user_home.php" aria-label="Bangkero and Fisherman Association - Home">
      <img src="../images/logo1.png" alt="Association logo" class="brand-img me-3" />
      <span class="brand-text">Bankero and Fisherman Association</span>
    </a>

    <!-- Hamburger Toggler (Right side on mobile) -->
    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- RIGHT SIDE: Navigation Menu -->
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link" href="user_home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="about_us.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
        <li class="nav-item"><a class="nav-link" href="announcement.php">Announcements</a></li>
        <li class="nav-item"><a class="nav-link" href="resources.php">Resources</a></li>
        <li class="nav-item"><a class="nav-link" href="contact_us.php">Contact</a></li>
      </ul>
    </div>
  </div>
</nav>

<style>
  :root {
    --nav-height: 80px;
    --nav-shrink-height: 60px;
    --primary: #2c3e50;
    --secondary: #34495e;
    --accent: #5a6c7d;
    --light: #ecf0f1;
    --dark: #1a252f;
    --glass: rgba(255,255,255,0.98);
    --radius: 10px;
    --shadow-sm: 0 2px 20px rgba(0,0,0,0.06);
    --shadow-md: 0 4px 24px rgba(0,0,0,0.1);
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
    background: var(--primary);
    color: #fff;
    border-radius: 8px;
    z-index: 11000;
    text-decoration: none;
  }

  /* ==================== NAVBAR STRUCTURE ==================== */
  .pro-navbar {
    background: var(--glass);
    backdrop-filter: blur(10px);
    padding: 1rem 0;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border-bottom: 1px solid rgba(0,0,0,0.08);
    box-shadow: var(--shadow-sm);
    z-index: 1030;
  }

  /* ==================== LEFT SIDE: LOGO + TEXT ==================== */
  .pro-navbar .navbar-brand {
    display: flex;
    align-items: center;
    padding: 0;
    margin-right: auto;
    text-decoration: none;
    transition: all 0.3s ease;
  }

  .pro-navbar .brand-img {
    height: 50px;
    width: auto;
    transition: all 0.4s ease;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    flex-shrink: 0;
  }

  .pro-navbar .brand-text {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--dark);
    font-family: 'Inter', 'Segoe UI', sans-serif;
    letter-spacing: -0.3px;
    line-height: 1.3;
    transition: all 0.3s ease;
    white-space: nowrap;
  }

  .pro-navbar .navbar-brand:hover .brand-text {
    color: var(--primary);
  }

  /* ==================== RIGHT SIDE: NAVIGATION MENU ==================== */
  .pro-navbar .navbar-nav {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .pro-navbar .nav-link {
    color: var(--dark) !important;
    font-weight: 600;
    font-size: 0.95rem;
    padding: 10px 16px;
    border-radius: 10px;
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
  }

  /* Hover Effect with Underline */
  .pro-navbar .nav-link::after {
    content: '';
    position: absolute;
    bottom: 6px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 3px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    border-radius: 2px;
    transition: width 0.3s ease;
  }

  .pro-navbar .nav-link:hover,
  .pro-navbar .nav-link:focus {
    color: var(--primary) !important;
    background: var(--light);
    transform: translateY(-2px);
    outline: none;
  }

  .pro-navbar .nav-link:hover::after {
    width: 70%;
  }

  .pro-navbar .nav-link.active {
    color: var(--primary) !important;
    background: var(--light);
  }

  .pro-navbar .nav-link.active::after {
    width: 70%;
  }

  /* ==================== SHRINK STATE (ON SCROLL) ==================== */
  .pro-navbar.shrink {
    padding: 0.5rem 0;
    box-shadow: var(--shadow-md);
  }

  .pro-navbar.shrink .brand-img {
    height: 40px;
  }

  .pro-navbar.shrink .brand-text {
    font-size: 1rem;
  }

  /* ==================== HAMBURGER TOGGLER ==================== */
  .navbar-toggler {
    border: 2px solid var(--primary);
    border-radius: 8px;
    width: 44px;
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    padding: 0;
    background: transparent;
  }

  .navbar-toggler:hover {
    background: var(--light);
    border-color: var(--secondary);
  }

  .navbar-toggler:focus {
    box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.15);
    outline: none;
  }

  .navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(44, 62, 80, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    width: 24px;
    height: 24px;
  }

  /* ==================== ACCESSIBILITY ==================== */
  .pro-navbar .nav-link:focus-visible {
    outline: 3px solid rgba(44, 62, 80, 0.3);
    outline-offset: 2px;
    border-radius: 10px;
  }

  /* ==================== RESPONSIVE DESIGN ==================== */
  
  /* Desktop (>= 992px) */
  @media (min-width: 992px) {
    .pro-navbar .navbar-nav {
      gap: 0.3rem;
    }
  }

  /* Tablet and below (<= 991.98px) */
  @media (max-width: 991.98px) {
    .pro-navbar {
      padding: 0.75rem 0;
    }

    .pro-navbar .brand-text {
      font-size: 0.95rem;
      max-width: 200px;
      line-height: 1.2;
    }

    .pro-navbar .brand-img {
      height: 42px;
    }

    /* Mobile menu styling */
    .pro-navbar .navbar-collapse {
      margin-top: 1rem;
    }

    .pro-navbar .navbar-nav {
      flex-direction: column;
      align-items: stretch;
      gap: 0.5rem;
      padding: 0.5rem 0;
    }

    .pro-navbar .nav-link {
      padding: 12px 16px;
      border-left: 3px solid transparent;
      border-radius: 8px;
    }

    .pro-navbar .nav-link:hover,
    .pro-navbar .nav-link.active {
      border-left-color: var(--primary);
      background: var(--light);
    }

    .pro-navbar .nav-link::after {
      display: none;
    }
  }

  /* Mobile (<= 576px) */
  @media (max-width: 576px) {
    .pro-navbar .brand-text {
      font-size: 0.85rem;
      max-width: 160px;
    }

    .pro-navbar .brand-img {
      height: 38px;
    }

    .navbar-toggler {
      width: 40px;
      height: 40px;
    }
  }

  /* Extra small screens (<= 400px) */
  @media (max-width: 400px) {
    .pro-navbar .brand-text {
      font-size: 0.75rem;
      max-width: 140px;
    }

    .pro-navbar .brand-img {
      height: 35px;
      margin-right: 8px;
    }
  }
</style>

<script>
(function () {
  'use strict';

  // Debounce helper
  const debounce = (fn, ms = 50) => { 
    let t; 
    return (...args) => { 
      clearTimeout(t); 
      t = setTimeout(() => fn(...args), ms); 
    }; 
  };

  document.addEventListener('DOMContentLoaded', function () {
    const navbar = document.querySelector('.pro-navbar');
    if (!navbar) return;

    // ==================== SHRINK ON SCROLL ====================
    const onScroll = () => {
      if (window.scrollY > 50) {
        navbar.classList.add('shrink');
      } else {
        navbar.classList.remove('shrink');
      }
    };
    window.addEventListener('scroll', debounce(onScroll, 60), { passive: true });
    onScroll(); // Initial check

    // ==================== MARK ACTIVE LINK ====================
    const links = Array.from(navbar.querySelectorAll('.nav-link'));
    const current = (location.pathname || '').split('/').pop().toLowerCase() || 'user_home.php';
    
    links.forEach(link => {
      const href = (link.getAttribute('href') || '').split('/').pop().toLowerCase();
      if (!href) return;
      
      if (href === current || (current === '' && (href === 'index.php' || href === 'user_home.php'))) {
        link.classList.add('active');
        link.setAttribute('aria-current', 'page');
      } else {
        link.classList.remove('active');
        link.removeAttribute('aria-current');
      }
    });

    // ==================== CLOSE MOBILE MENU ON LINK CLICK ====================
    const toggler = document.querySelector('.navbar-toggler');
    const collapseEl = document.getElementById('mainNav');
    
    if (toggler && collapseEl) {
      links.forEach(link => {
        link.addEventListener('click', () => {
          // Check if menu is visible (mobile)
          const isVisible = window.getComputedStyle(toggler).display !== 'none';
          if (isVisible && collapseEl.classList.contains('show')) {
            // Close the menu
            const bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
            if (bsCollapse) {
              bsCollapse.hide();
            } else {
              toggler.click();
            }
          }
        });
      });
    }

    // ==================== SMOOTH SCROLL ====================
    links.forEach(link => {
      if (link.getAttribute('href').startsWith('#')) {
        link.addEventListener('click', (e) => {
          const targetId = link.getAttribute('href');
          const targetElement = document.querySelector(targetId);
          
          if (targetElement) {
            e.preventDefault();
            const offset = 80; // Account for fixed navbar
            const elementPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
            const offsetPosition = elementPosition - offset;

            window.scrollTo({
              top: offsetPosition,
              behavior: 'smooth'
            });
          }
        });
      }
    });

    // ==================== PREVENT BODY SCROLL WHEN MOBILE MENU OPEN ====================
    if (collapseEl) {
      collapseEl.addEventListener('show.bs.collapse', function () {
        document.body.style.overflow = 'hidden';
      });

      collapseEl.addEventListener('hidden.bs.collapse', function () {
        document.body.style.overflow = '';
      });
    }
  });
})();
</script>
