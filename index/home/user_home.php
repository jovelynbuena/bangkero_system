<?php
session_start();
require_once('../../config/db_connect.php');

$nextEventResult = $conn->query("SELECT * FROM events WHERE date >= CURDATE() ORDER BY date ASC LIMIT 1");
$nextEvent = $nextEventResult ? $nextEventResult->fetch_assoc() : null;

$latestAnnouncements = $conn->query("SELECT * FROM announcements ORDER BY date_posted DESC LIMIT 3");


?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home - Bangkero & Fishermen Association</title>
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
    * {
      scroll-behavior: smooth;
    }
    * {
      scroll-behavior: smooth;
    }
    
    /* Enhanced Navbar */
    .navbar {
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(10px);
      box-shadow: 0 2px 20px rgba(0,0,0,0.06);
      padding: 1.2rem 0;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    .navbar.shrink {
      padding: 0.6rem 0;
      box-shadow: 0 4px 24px rgba(0,0,0,0.1);
    }
    .navbar-brand img {
      height: 52px;
      transition: all 0.4s ease;
      filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    }
    .navbar.shrink .navbar-brand img {
      height: 40px;
    }
    .navbar-nav .nav-link {
      font-weight: 600;
      color: var(--dark) !important;
      margin: 0 10px;
      padding: 10px 16px;
      border-radius: 10px;
      position: relative;
      transition: all 0.3s ease;
    }
    .navbar-nav .nav-link::after {
      content: '';
      position: absolute;
      bottom: 6px;
      left: 50%;
      transform: translateX(-50%);
      width: 0;
      height: 3px;
      background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
      border-radius: 2px;
      transition: width 0.3s ease;
    }
    .navbar-nav .nav-link:hover::after,
    .navbar-nav .nav-link.active::after {
      width: 70%;
    }
    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link.active {
      color: var(--primary-orange) !important;
      background: var(--light-orange);
    }
    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link.active {
      color: var(--primary-orange) !important;
      background: var(--light-orange);
    }
    
    /* Modern Hero Carousel */
    .carousel-item {
      height: 75vh;
      min-height: 400px;
      background-size: cover;
      background-position: center;
      position: relative;
    }
    .carousel-item::before {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: linear-gradient(135deg, rgba(44, 62, 80, 0.75) 0%, rgba(26, 37, 47, 0.85) 100%);
      z-index: 1;
    }
    .carousel-caption {
      position: absolute;
      top: 50%;
      left: 0; right: 0;
      transform: translateY(-50%);
      z-index: 2;
      text-shadow: 2px 2px 12px rgba(0,0,0,0.4);
    }
    .carousel-caption h1 {
      font-size: 3.5rem;
      font-weight: 800;
      color: #fff;
      font-family: 'Poppins', sans-serif;
      letter-spacing: -1px;
      margin-bottom: 20px;
      animation: fadeInUp 0.8s ease-out;
    }
    .carousel-caption p {
      font-size: 1.3rem;
      margin-top: 16px;
      color: #ffffff;
      font-weight: 400;
      animation: fadeInUp 0.8s ease-out 0.2s backwards;
    }
    .carousel-control-prev,
    .carousel-control-next {
      z-index: 3;
      width: 50px;
      opacity: 0.8;
    }
    .carousel-control-prev:hover,
    .carousel-control-next:hover {
      opacity: 1;
    }
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
      width: 40px;
      height: 40px;
      background-color: rgba(44, 62, 80, 0.7);
      border-radius: 50%;
      padding: 10px;
    }
    .carousel-indicators {
      z-index: 3;
      margin-bottom: 2rem;
    }
    .carousel-indicators button {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      border: 2px solid white;
      background-color: rgba(255, 255, 255, 0.5);
      transition: all 0.3s ease;
      margin: 0 6px;
    }
    .carousel-indicators button.active {
      width: 14px;
      height: 14px;
      background-color: white;
      box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
    }
    .carousel-indicators button:hover {
      background-color: rgba(255, 255, 255, 0.8);
      transform: scale(1.1);
    }
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    /* Modern Intro Section */
    .intro-section {
      background: linear-gradient(180deg, #ffffff 0%, #fff5f0 100%);
      padding: 50px 0;
      position: relative;
      overflow: hidden;
    }
    .intro-section::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(44, 62, 80, 0.05) 0%, transparent 70%);
      border-radius: 50%;
    }
    .intro-section h2 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 700;
      margin-bottom: 1.5rem;
      font-size: 2.8rem;
      position: relative;
      display: inline-block;
    }
    .intro-section h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
      border-radius: 2px;
    }
    .intro-section p {
      color: #4b5563;
      font-size: 1.15rem;
      line-height: 1.8;
      max-width: 900px;
      margin: 0 auto;
      position: relative;
    }
    .intro-section p {
      color: #4b5563;
      font-size: 1.15rem;
      line-height: 1.8;
      max-width: 900px;
      margin: 0 auto;
      position: relative;
    }
   
    /* Compact Event Highlight */
    .event-highlight {
      background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
      padding: 40px 20px;
      position: relative;
    }
    .event-highlight h2 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      margin-bottom: 20px;
      font-weight: 700;
      font-size: 1.5rem;
    }
    .event-card {
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(44, 62, 80, 0.08);
      transition: all 0.3s ease;
      border: none;
      background: white;
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    .event-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 32px rgba(44, 62, 80, 0.12);
    }
    .event-poster-wrapper {
      position: relative;
      overflow: hidden;
      height: 320px;
      flex-shrink: 0;
    }
    .event-card img {
      height: 100%;
      width: 100%;
      object-fit: cover;
      transition: transform 0.4s ease;
    }
    .event-card:hover img {
      transform: scale(1.05);
    }
    .event-details {
      padding: 24px;
      position: relative;
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    .event-badge {
      display: inline-block;
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 0.7rem;
      font-weight: 600;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin-bottom: 10px;
      align-self: flex-start;
    }
    .event-details h3 {
      color: var(--dark);
      font-weight: 700;
      font-size: 1.3rem;
      margin-bottom: 10px;
      font-family: 'Poppins', sans-serif;
      line-height: 1.3;
    }
    .event-description {
      color: #64748b;
      font-size: 0.9rem;
      line-height: 1.5;
      margin-bottom: 15px;
      font-weight: 400;
    }
    .event-info-grid {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 15px;
    }
    .event-details .icon-text {
      display: flex;
      align-items: center;
      padding: 8px 12px;
      background: #f8fafc;
      border-radius: 8px;
      transition: all 0.2s ease;
      border-left: 3px solid var(--primary);
    }
    .event-details .icon-text:hover {
      background: #f1f5f9;
    }
    .event-details .icon-text i {
      color: var(--primary);
      margin-right: 10px;
      font-size: 1rem;
    }
    .event-details .icon-text span {
      color: var(--dark);
      font-weight: 600;
      font-size: 0.85rem;
    }
    .event-empty-state {
      text-align: center;
      padding: 50px 20px;
      background: #f8fafc;
      border-radius: 16px;
      border: 2px dashed #cbd5e1;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }
    .event-empty-state i {
      font-size: 3.5rem;
      color: #cbd5e1;
      margin-bottom: 16px;
    }
    .event-empty-state p {
      color: #94a3b8;
      font-size: 1rem;
      font-weight: 500;
    }
    
    /* Compact Countdown */
    .countdown-wrapper {
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      padding: 15px;
      border-radius: 12px;
      margin-top: auto;
      border: 1px solid #e2e8f0;
    }
    .countdown-label {
      text-align: center;
      color: var(--dark);
      font-weight: 600;
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 10px;
      opacity: 0.7;
    }
    .countdown {
      display: flex;
      justify-content: center;
      gap: 8px;
      flex-wrap: wrap;
    }
    .countdown div {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: #fff;
      padding: 12px 8px;
      border-radius: 8px;
      min-width: 60px;
      box-shadow: 0 4px 12px rgba(44, 62, 80, 0.2);
      transition: all 0.3s ease;
    }
    .countdown div:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(44, 62, 80, 0.25);
    }
    .countdown div span {
      display: block;
      font-size: 1.4rem;
      font-weight: 800;
      font-family: 'Poppins', sans-serif;
      line-height: 1;
      margin-bottom: 4px;
    }
    .countdown div small {
      font-size: 0.65rem;
      opacity: 0.9;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    /* Cleaner Countdown Pro */
    .countdown-pro {
      display: flex;
      justify-content: center;
      gap: 6px;
    }
    .countdown-pro div {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      padding: 10px 6px;
      border-radius: 8px;
      min-width: 55px;
      text-align: center;
      box-shadow: 0 4px 12px rgba(44, 62, 80, 0.2);
    }
    .countdown-pro div span {
      display: block;
      font-size: 1.3rem;
      font-weight: 800;
      line-height: 1;
      font-family: 'Poppins', sans-serif;
    }
    .countdown-pro div small {
      display: block;
      font-size: 0.6rem;
      opacity: 0.9;
      margin-top: 4px;
      font-weight: 500;
      text-transform: uppercase;
    }
    
    /* Awards & Recognition Widget */
    .awards-widget {
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(44, 62, 80, 0.08);
      height: 100%;
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
    }
    .awards-widget:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 30px rgba(44, 62, 80, 0.15);
    }
    .awards-header {
      background: linear-gradient(135deg, #d4af37 0%, #f4e5a1 100%);
      color: #1a252f;
      padding: 16px;
      text-align: center;
      flex-shrink: 0;
    }
    .awards-header h4 {
      font-size: 1rem;
      font-weight: 700;
      margin: 0;
      font-family: 'Poppins', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }
    .awards-header i {
      font-size: 1.1rem;
    }
    .awards-header .subtitle {
      font-size: 0.7rem;
      opacity: 0.85;
      margin: 4px 0 0 0;
      font-weight: 500;
    }
    
    /* Featured Award */
    .featured-award {
      padding: 18px;
      background: white;
      flex-shrink: 0;
    }
    .award-image-container {
      position: relative;
      border-radius: 12px;
      overflow: hidden;
      margin-bottom: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .award-image {
      width: 100%;
      height: 180px;
      object-fit: cover;
      transition: transform 0.3s ease;
    }
    .award-image-container:hover .award-image {
      transform: scale(1.05);
    }
    .award-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      background: linear-gradient(135deg, #d4af37 0%, #f4e5a1 100%);
      width: 35px;
      height: 35px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .award-badge i {
      font-size: 1rem;
      color: #1a252f;
    }
    .award-info h5 {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      color: var(--dark);
      font-size: 0.95rem;
      margin-bottom: 4px;
    }
    .award-year {
      color: #d4af37;
      font-weight: 700;
      font-size: 0.8rem;
      margin-bottom: 6px;
    }
    .award-desc {
      color: #64748b;
      font-size: 0.8rem;
      line-height: 1.4;
      margin: 0;
    }
    
    /* Award Highlights List */
    .award-highlights {
      padding: 0 18px 18px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      flex: 1;
    }
    .award-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px;
      background: #f8fafc;
      border-radius: 8px;
      transition: all 0.3s ease;
    }
    .award-item:hover {
      background: #f1f5f9;
      transform: translateX(5px);
    }
    .award-icon {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .award-icon i {
      font-size: 1rem;
    }
    .award-icon.gold {
      background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
      box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
    }
    .award-icon.gold i {
      color: #1a252f;
    }
    .award-icon.silver {
      background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%);
      box-shadow: 0 2px 8px rgba(192, 192, 192, 0.3);
    }
    .award-icon.silver i {
      color: #1a252f;
    }
    .award-icon.bronze {
      background: linear-gradient(135deg, #cd7f32 0%, #e9b982 100%);
      box-shadow: 0 2px 8px rgba(205, 127, 50, 0.3);
    }
    .award-icon.bronze i {
      color: #fff;
    }
    .award-text {
      flex: 1;
    }
    .award-text strong {
      display: block;
      color: var(--dark);
      font-size: 0.85rem;
      font-weight: 600;
      margin-bottom: 2px;
    }
    .award-text small {
      color: #94a3b8;
      font-size: 0.7rem;
    }
    
    /* Awards Footer */
    .awards-footer {
      padding: 14px 18px;
      background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
      text-align: center;
      flex-shrink: 0;
      border-top: 1px solid #e2e8f0;
    }
    .btn-view-awards {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 9px 18px;
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.85rem;
      transition: all 0.3s ease;
    }
    .btn-view-awards:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(44, 62, 80, 0.3);
      color: white;
    }
    
    /* Modern Announcement Cards */
    .announcements-section {
      padding: 50px 0;
      background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    }
    .announcements-section h2 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 700;
      margin-bottom: 35px;
      font-size: 2.5rem;
    }
    .announcement-card {
      background: #ffffff;
      border: none;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      transition: all 0.4s ease;
      overflow: hidden;
      position: relative;
    }
    .announcement-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
      transform: scaleX(0);
      transition: transform 0.4s ease;
    }
    .announcement-card:hover::before {
      transform: scaleX(1);
    }
    .announcement-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 12px 40px rgba(255, 107, 53, 0.2);
    }
    .announcement-card h5 {
      color: var(--dark);
      font-weight: 700;
      font-size: 1.2rem;
    }
    .announcement-card .badge {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      padding: 6px 12px;
      border-radius: 8px;
      font-weight: 600;
    }
    .announcement-card .btn {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      border: none;
      color: white;
      font-weight: 600;
      padding: 10px 24px;
      border-radius: 10px;
      transition: all 0.3s ease;
    }
    .announcement-card .btn:hover {
      transform: translateX(4px);
      box-shadow: 0 4px 12px rgba(44, 62, 80, 0.25);
    }
    .view-all-btn {
      background: transparent;
      border: 2px solid var(--primary);
      color: var(--primary);
      font-weight: 600;
      padding: 12px 32px;
      border-radius: 12px;
      transition: all 0.3s ease;
    }
    .view-all-btn:hover {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(44, 62, 80, 0.25);
    }

    .view-all-btn:hover {
      background: linear-gradient(135deg, var(--primary-orange) 0%, var(--dark-orange) 100%);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 107, 53, 0.3);
    }
    
    /* Partnerships Section */
    .partnerships {
      padding: 50px 0;
      background: linear-gradient(135deg, #ecf0f1 0%, #ffffff 100%);
    }
    .partnerships h2 {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      font-weight: 700;
      font-size: 2.5rem;
      margin-bottom: 20px;
    }
    .partnerships img {
      max-height: 90px;
      transition: all 0.4s ease;
      filter: grayscale(100%) brightness(0.9);
      opacity: 0.7;
    }
    .partnerships img:hover {
      transform: scale(1.12);
      filter: grayscale(0%) brightness(1);
      opacity: 1;
    }
    .partner-card {
      background: white;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.06);
      transition: all 0.3s ease;
      height: 100%;
    }
    .partner-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 40px rgba(44, 62, 80, 0.12);
    }

   /* Footer spacing */
    .bottom-space { height: 40px; }
    
    /* Responsive */
    @media (max-width: 991px) {
      .col-lg-7, .col-lg-5 {
        flex: 0 0 100%;
        max-width: 100%;
      }
      .awards-widget {
        margin-top: 0;
      }
    }
    @media (max-width: 768px) {
      .carousel-caption h1 {
        font-size: 2rem;
      }
      .carousel-caption p {
        font-size: 1rem;
      }
      .intro-section h2,
      .event-highlight h2,
      .announcements-section h2,
      .partnerships h2 {
        font-size: 1.5rem;
      }
      .event-poster-wrapper {
        height: 280px;
      }
      .event-details {
        padding: 18px;
      }
      .event-details h3 {
        font-size: 1.2rem;
      }
      .countdown div {
        min-width: 55px;
        padding: 10px 6px;
      }
      .countdown div span {
        font-size: 1.2rem;
      }
      .countdown div small {
        font-size: 0.6rem;
      }
      .award-image {
        height: 160px;
      }
      .award-highlights {
        gap: 8px;
      }
    }
  </style>
</head>
<body>
  <main>
  <?php include("partials/navbar.php"); ?>

<!-- Hero Carousel -->
<div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
  <!-- Carousel Indicators (Dots) -->
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
  </div>

  <div class="carousel-inner">
    <div class="carousel-item active" style="background-image: url('../images/home.jpg');">
      <div class="carousel-caption text-center">
        <h1>Welcome to Our Association</h1>
        <p>Connecting members, sharing resources, and empowering leaders.</p>
      </div>
    </div>
    <div class="carousel-item" style="background-image: url('../images/slides2.jpg');">
      <div class="carousel-caption text-center">
        <h1>Together We Grow</h1>
        <p>Building a stronger community through unity.</p>
      </div>
    </div>
    <div class="carousel-item" style="background-image: url('../images/slide3.jpg');">
      <div class="carousel-caption text-center">
        <h1>Empowering Leaders</h1>
        <p>Guiding the next generation of members.</p>
      </div>
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>

<!-- Introduction Section -->
<section class="intro-section text-center py-5">
  <div class="container">
    <h2 class="section-title mb-4">Who We Are</h2>
    <p class="lead" style="max-width: 900px; margin: 0 auto; font-size: 1.15rem; line-height: 1.8; color: #4b5563;">
      The <strong>Bankero & Fishermen Association</strong> is a community-driven organization dedicated to supporting local fishermen and their families. Founded in 2009, we promote sustainable fishing practices, strengthen unity among members, and provide opportunities for growth and livelihood development.
    </p>
    <a href="about_us.php" class="btn btn-outline-primary mt-4">
      Learn More About Us <i class="bi bi-arrow-right ms-2"></i>
    </a>
  </div>
</section>



<!-- Event Highlight Section -->
<section class="event-highlight">
    <div class="container">
        <div class="row g-3 align-items-stretch">
            <!-- Event Card (Left Side - 7 columns) -->
            <div class="col-lg-7">
                <h2 class="mb-3">📅 Upcoming Event</h2>
                <?php if ($nextEvent): ?>
                <div class="card event-card border-0">
                    <!-- Event Poster -->
                    <div class="event-poster-wrapper">
                        <img src="../../uploads/<?php echo htmlspecialchars($nextEvent['event_poster'] ?: 'default.jpg'); ?>" 
                             class="img-fluid" alt="Event Poster">
                    </div>
                    
                    <!-- Event Details -->
                    <div class="event-details">
                        <!-- Event Badge & Title -->
                        <span class="event-badge">
                            <i class="bi bi-star-fill me-1"></i> Featured
                        </span>
                        
                        <h3><?php echo htmlspecialchars($nextEvent['event_name']); ?></h3>
                        
                        <!-- Event Description -->
                        <p class="event-description">
                            <?php 
                                $desc = htmlspecialchars($nextEvent['description']);
                                $maxLength = 120;
                                if (strlen($desc) > $maxLength) {
                                    $desc = substr($desc, 0, $maxLength) . '...';
                                }
                                echo $desc;
                            ?>
                        </p>
                        
                        <!-- Event Info Grid -->
                        <div class="event-info-grid">
                            <div class="icon-text">
                                <i class="bi bi-calendar-event-fill"></i>
                                <span><?php echo date('M d, Y', strtotime($nextEvent['date'])); ?></span>
                            </div>
                            <div class="icon-text">
                                <i class="bi bi-clock-fill"></i>
                                <span><?php echo date('g:i A', strtotime($nextEvent['time'])); ?></span>
                            </div>
                            <div class="icon-text">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span><?php echo htmlspecialchars($nextEvent['location']); ?></span>
                            </div>
                        </div>
                        
                        <!-- Countdown Timer -->
                        <div class="countdown-wrapper">
                            <div class="countdown-label">
                                <i class="bi bi-hourglass-split me-1"></i>Starts In
                            </div>
                            <div class="countdown countdown-pro" data-event-date="<?php echo $nextEvent['date'] . ' ' . $nextEvent['time']; ?>">
                                <div><span class="days">0</span><small>Days</small></div>
                                <div><span class="hours">0</span><small>Hours</small></div>
                                <div><span class="minutes">0</span><small>Min</small></div>
                                <div><span class="seconds">0</span><small>Sec</small></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="event-empty-state">
                    <i class="bi bi-calendar-x"></i>
                    <p class="mb-0">No upcoming events scheduled at the moment.</p>
                    <p class="text-muted small mt-2">Check back soon for exciting announcements!</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Awards & Recognition (Right Side - 5 columns) -->
            <div class="col-lg-5 d-flex">
                <div class="w-100">
                    <h2 class="mb-3">🏆 Awards & Recognition</h2>
                    <div class="awards-widget">
                        <!-- Header -->
                        <div class="awards-header">
                            <h4><i class="bi bi-trophy-fill"></i> Recent Achievements</h4>
                            <p class="subtitle">Celebrating Our Success</p>
                        </div>
                        
                        <!-- Featured Award/Certificate -->
                        <div class="featured-award">
                            <div class="award-image-container">
                                <img src="../../uploads/awards/Screenshot 2026-02-12 015634.png" alt="Featured Award" class="award-image">
                                <div class="award-badge">
                                    <i class="bi bi-award-fill"></i>
                                </div>
                            </div>
                            <div class="award-info">
                                <h5>Outstanding Community Service</h5>
                                <p class="award-year">2025</p>
                                <p class="award-desc">Recognized by the City Government for exceptional service to the fishing community.</p>
                            </div>
                        </div>
                        
                        <!-- Award Highlights -->
                        <div class="award-highlights">
                            <div class="award-item">
                                <div class="award-icon gold">
                                    <i class="bi bi-trophy-fill"></i>
                                </div>
                                <div class="award-text">
                                    <strong>Best Association Award</strong>
                                    <small>BFAR Region 3 - 2024</small>
                                </div>
                            </div>
                            
                            <div class="award-item">
                                <div class="award-icon silver">
                                    <i class="bi bi-patch-check-fill"></i>
                                </div>
                                <div class="award-text">
                                    <strong>Environmental Excellence</strong>
                                    <small>DENR Recognition - 2024</small>
                                </div>
                            </div>
                            
                            <div class="award-item">
                                <div class="award-icon bronze">
                                    <i class="bi bi-star-fill"></i>
                                </div>
                                <div class="award-text">
                                    <strong>Safety Champion</strong>
                                    <small>Coast Guard Citation - 2023</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- View All Button -->
                        <div class="awards-footer">
                            <a href="awards.php" class="btn-view-awards">
                                <i class="bi bi-grid-3x3-gap"></i> View All Awards
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="container my-5">
   <section class="announcements-section">
    <div class="container">
        <h2 class="text-center">📢 Latest Announcements</h2>
        
        <div class="row justify-content-center">
            <?php while ($row = $latestAnnouncements->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="announcement-card h-100 d-flex flex-column p-4">
                        <!-- Badge -->
                        <span class="badge mb-3 align-self-start">
                            <i class="bi bi-megaphone-fill me-1"></i> Announcement
                        </span>
                        
                        <!-- Title -->
                        <h5 class="mb-3">
                            <?= htmlspecialchars($row['title']) ?>
                        </h5>
                        
                        <!-- Date -->
                        <p class="text-muted small mb-3">
                            <i class="bi bi-calendar3 me-1"></i>
                            <?= date("F j, Y", strtotime($row['date_posted'])) ?>
                        </p>
                        
                        <!-- Content Preview -->
                        <p class="flex-grow-1 text-secondary" style="min-height: 80px; line-height: 1.6;">
                            <?= nl2br(htmlspecialchars(substr($row['content'], 0, 130))) ?>...
                        </p>
                        
                        <!-- Read More Button -->
                        <div class="mt-auto pt-3">
                            <a href="announcement.php" class="btn w-100">
                                Read More <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- View All Button -->
        <div class="text-center mt-5">
            <a href="announcement.php" class="view-all-btn">
                View All Announcements <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>





<!-- Counter Script -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const countdown = document.querySelector(".countdown-pro");
    if (!countdown) return;

    const eventDateStr = countdown.dataset.eventDate;
    const eventDate = new Date(eventDateStr).getTime();

    function updateCountdown() {
        const now = new Date().getTime();
        const diff = eventDate - now;

        if (diff <= 0) {
            countdown.innerHTML = "<strong style='text-align:center; width:100%; color:white;'>Event Started!</strong>";
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        countdown.querySelector(".days").textContent = days;
        countdown.querySelector(".hours").textContent = hours;
        countdown.querySelector(".minutes").textContent = minutes;
        countdown.querySelector(".seconds").textContent = seconds;
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();
});
</script>

<!-- Live Weather Script -->
<script>
// OpenWeatherMap API Configuration
const WEATHER_CONFIG = {
    apiKey: '', // Leave empty to use demo mode, or add your API key from openweathermap.org
    city: 'Olongapo',
    country: 'PH',
    units: 'metric',
    demoMode: true // Set to false when you have API key
};

// Weather icon mapping
const weatherIcons = {
    'clear': 'bi-sun-fill',
    'clouds': 'bi-cloud-sun',
    'rain': 'bi-cloud-rain-fill',
    'drizzle': 'bi-cloud-drizzle-fill',
    'thunderstorm': 'bi-cloud-lightning-fill',
    'snow': 'bi-cloud-snow-fill',
    'mist': 'bi-cloud-haze',
    'fog': 'bi-cloud-fog'
};

// Determine sea condition based on wind speed
function getSeaCondition(windSpeed) {
    if (windSpeed < 10) return { text: 'Calm', safe: true };
    if (windSpeed < 20) return { text: 'Moderate', safe: true };
    if (windSpeed < 30) return { text: 'Rough', safe: false };
    return { text: 'Very Rough', safe: false };
}

// Determine fishing safety
function getFishingStatus(windSpeed, weather) {
    const dangerousWeather = ['thunderstorm', 'rain', 'storm'];
    const weatherType = weather.toLowerCase();
    
    if (windSpeed > 25 || dangerousWeather.some(w => weatherType.includes(w))) {
        return { safe: false, text: 'UNSAFE', icon: 'bi-x-circle-fill' };
    } else if (windSpeed > 15) {
        return { safe: false, text: 'CAUTION', icon: 'bi-exclamation-circle-fill' };
    }
    return { safe: true, text: 'SAFE', icon: 'bi-check-circle-fill' };
}

// Get cardinal direction from degrees
function getWindDirection(degrees) {
    const directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
    const index = Math.round(degrees / 45) % 8;
    return directions[index];
}

// Fetch and update weather data
async function updateWeatherData() {
    // Demo/Default data
    const demoData = {
        temp: 28,
        weatherMain: 'Clouds',
        weatherDesc: 'Partly Cloudy',
        windSpeed: 10,
        windDeg: 45
    };
    
    try {
        let weatherData = demoData;
        
        // Try to fetch live data if API key is provided
        if (WEATHER_CONFIG.apiKey && !WEATHER_CONFIG.demoMode) {
            const url = `https://api.openweathermap.org/data/2.5/weather?q=${WEATHER_CONFIG.city},${WEATHER_CONFIG.country}&appid=${WEATHER_CONFIG.apiKey}&units=${WEATHER_CONFIG.units}`;
            
            const response = await fetch(url);
            
            if (response.ok) {
                const data = await response.json();
                weatherData = {
                    temp: Math.round(data.main.temp),
                    weatherMain: data.weather[0].main,
                    weatherDesc: data.weather[0].description,
                    windSpeed: Math.round(data.wind.speed * 3.6),
                    windDeg: data.wind.deg
                };
            }
        }
        
        // Update temperature
        document.getElementById('tempValue').textContent = weatherData.temp;
        
        // Update weather description
        const weatherDesc = weatherData.weatherDesc.charAt(0).toUpperCase() + weatherData.weatherDesc.slice(1);
        document.getElementById('weatherDesc').textContent = weatherDesc;
        
        // Update weather icon
        const iconClass = weatherIcons[weatherData.weatherMain.toLowerCase()] || 'bi-cloud-sun';
        const weatherIconElement = document.getElementById('weatherIcon');
        weatherIconElement.className = `bi ${iconClass}`;
        
        // Update wind speed
        const windDir = getWindDirection(weatherData.windDeg);
        document.getElementById('windSpeed').textContent = `${weatherData.windSpeed} km/h ${windDir}`;
        
        // Update sea condition
        const seaCondition = getSeaCondition(weatherData.windSpeed);
        document.getElementById('seaCondition').textContent = seaCondition.text;
        
        // Update fishing status
        const fishingStatus = getFishingStatus(weatherData.windSpeed, weatherData.weatherMain);
        const fishingStatusElement = document.getElementById('fishingStatus');
        const fishingTextElement = document.getElementById('fishingText');
        const fishingIconElement = fishingStatusElement.querySelector('i');
        
        fishingTextElement.textContent = fishingStatus.text;
        fishingIconElement.className = `bi ${fishingStatus.icon}`;
        
        if (fishingStatus.safe) {
            fishingStatusElement.className = 'weather-item fishing-safe';
            fishingStatusElement.style.borderLeftColor = 'var(--success)';
            fishingStatusElement.style.background = 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)';
            fishingIconElement.style.color = 'var(--success)';
            fishingTextElement.style.color = 'var(--success)';
        } else {
            fishingStatusElement.className = 'weather-item fishing-unsafe';
            fishingStatusElement.style.borderLeftColor = '#ef4444';
            fishingStatusElement.style.background = 'linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%)';
            fishingIconElement.style.color = '#ef4444';
            fishingTextElement.style.color = '#ef4444';
        }
        
        // Update advisory
        let advisory = 'None';
        if (weatherData.windSpeed > 30) {
            advisory = 'Strong winds warning';
        } else if (weatherData.weatherMain.toLowerCase().includes('rain') || weatherData.weatherMain.toLowerCase().includes('storm')) {
            advisory = 'Rain/Storm advisory';
        } else if (weatherData.windSpeed > 20) {
            advisory = 'Moderate wind caution';
        }
        document.getElementById('advisory').textContent = advisory;
        
        // Update last update time
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        const modeText = WEATHER_CONFIG.demoMode ? ' (Demo Mode)' : '';
        document.getElementById('updateTime').textContent = `Last updated: ${timeStr}${modeText}`;
        
    } catch (error) {
        console.error('Error fetching weather data:', error);
        
        // Use demo data as fallback
        document.getElementById('tempValue').textContent = demoData.temp;
        document.getElementById('weatherDesc').textContent = demoData.weatherDesc;
        document.getElementById('seaCondition').textContent = 'Calm';
        document.getElementById('windSpeed').textContent = `${demoData.windSpeed} km/h NE`;
        document.getElementById('fishingText').textContent = 'SAFE';
        document.getElementById('advisory').textContent = 'None';
        
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        document.getElementById('updateTime').textContent = `${timeStr} (Demo Mode)`;
    }
}

// Initialize weather on page load
document.addEventListener('DOMContentLoaded', () => {
    updateWeatherData();
    // Update every 10 minutes
    setInterval(updateWeatherData, 600000);
});
</script>


<!-- Partnerships Section with Slider -->
<section class="partnerships py-5" style="background: linear-gradient(135deg, #ecf0f1 0%, #ffffff 100%);">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-title">🤝 Our Partners</h2>
      <p class="text-muted" style="font-size: 1.1rem; max-width: 700px; margin: 0 auto;">
        We are proud to collaborate with institutions and organizations that share our vision of 
        supporting local fishermen and preserving marine resources.
      </p>
    </div>
    
    <!-- Partner Slider -->
    <div class="partner-slider-container position-relative" style="overflow: hidden; padding: 20px 0;">
      <div class="partner-track-wrapper d-flex align-items-center justify-content-center flex-wrap gap-4">
        <!-- Partner 1 -->
        <div class="partner-card">
          <img src="../uploads/partners/olongapo.png" class="img-fluid mb-3" alt="Municipality of Olongapo" style="max-height: 90px;">
          <p class="mt-2 text-dark small fw-semibold mb-0">Municipality of Olongapo City</p>
        </div>
        
        <!-- Partner 2 -->
        <div class="partner-card">
          <img src="../uploads/partners/bfar.png" class="img-fluid mb-3" alt="BFAR" style="max-height: 90px;">
          <p class="mt-2 text-dark small fw-semibold mb-0">Bureau of Fisheries & Aquatic Resources</p>
        </div>
        
        <!-- Partner 3 -->
        <div class="partner-card">
          <img src="../uploads/partners/agriculture.png" class="img-fluid mb-3" alt="Agriculture" style="max-height: 90px;">
          <p class="mt-2 text-dark small fw-semibold mb-0">Olongapo City Agriculture Department</p>
        </div>
        
        <!-- Partner 4 -->
        <div class="partner-card">
          <img src="../uploads/partners/usaid.png" class="img-fluid mb-3" alt="USAID" style="max-height: 90px;">
          <p class="mt-2 text-dark small fw-semibold mb-0">USAID</p>
        </div>
      </div>
    </div>
  </div>
</section>
</main>

<div class="bottom-space"></div>



<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Navbar shrink effect
  window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 40) {
      navbar.classList.add('shrink');
    } else {
      navbar.classList.remove('shrink');
    }
  });
</script>
        
<?php include("partials/footer.php"); ?>

<?php include 'chatbox.php'; ?>

</body>
</html>
