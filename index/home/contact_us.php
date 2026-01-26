<?php
include("../../config/db_connect.php"); // Database connection

// Fetch system config for contact info
$configResult = $conn->query("SELECT * FROM system_config WHERE id=1");
$config = $configResult && $configResult->num_rows > 0 ? $configResult->fetch_assoc() : null;

// Set contact info from config or use defaults
$assocAddress = $config['assoc_address'] ?? '123 Association Street, Olongapo City, Philippines';
$assocPhone = $config['assoc_phone'] ?? '+63 912 345 6789';
$assocEmail = $config['assoc_email'] ?? 'info@association.org';

// Handle form submission securely
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));

    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
        $success_msg = "Your message has been sent successfully!";
    } else {
        $error_msg = "Oops! Something went wrong. Please try again later.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us | Bangkero & Fishermen Association</title>
  
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

    /* Hero Section */
    .hero-section {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      padding: 60px 0 50px;
      color: white;
      position: relative;
      overflow: hidden;
      margin-top: 60px;
      text-align: center;
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
      margin-bottom: 15px;
      letter-spacing: -1px;
      position: relative;
      z-index: 1;
    }
    .hero-section p {
      font-size: 1.1rem;
      opacity: 0.95;
      position: relative;
      z-index: 1;
      margin-bottom: 0;
    }

    /* Contact Container */
    .contact-container {
      max-width: 1200px;
      margin: 25px auto;
      padding: 0 15px;
    }

    /* Contact Info */
    .contact-info-card {
      background: white;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      margin-bottom: 20px;
    }

    .info-item {
      display: flex;
      align-items: start;
      margin-bottom: 25px;
      padding-bottom: 20px;
      border-bottom: 1px solid #e9ecef;
    }
    .info-item:last-child {
      margin-bottom: 0;
      padding-bottom: 0;
      border-bottom: none;
    }

    .info-icon {
      width: 45px;
      height: 45px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.2rem;
      flex-shrink: 0;
      margin-right: 15px;
    }

    .info-content h4 {
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
      font-weight: 600;
      color: var(--primary);
      margin-bottom: 5px;
    }

    .info-content p {
      font-size: 0.95rem;
      color: var(--gray);
      margin: 0;
    }

    /* Map */
    .map-container {
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    /* Form */
    .form-card {
      background: white;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .form-card h4 {
      font-family: 'Poppins', sans-serif;
      font-size: 1.4rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 25px;
      text-align: center;
    }

    .form-label {
      font-weight: 600;
      color: var(--primary);
      margin-bottom: 8px;
      font-size: 0.9rem;
    }

    .form-control {
      border-radius: 8px;
      padding: 12px 15px;
      border: 1px solid #dee2e6;
      font-size: 0.95rem;
      transition: all 0.3s;
    }

    .form-control:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(90,108,125,0.1);
    }

    .btn-submit {
      background: var(--primary);
      border: none;
      border-radius: 8px;
      padding: 12px;
      font-weight: 600;
      font-size: 1rem;
      transition: all 0.3s;
      width: 100%;
      color: white;
    }

    .btn-submit:hover {
      background: var(--secondary);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .alert {
      border-radius: 8px;
      border: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .hero-section {
        padding: 40px 0 35px;
      }
      .hero-section h1 {
        font-size: 2rem;
      }
      .hero-section p {
        font-size: 1rem;
      }
      .contact-info-card, .form-card {
        padding: 20px;
      }
    }
  </style>
</head>
<body>

<?php include("partials/navbar.php"); ?>

<main>
  <!-- Hero Section -->
  <div class="hero-section">
    <div class="container">
      <h1><i class="bi bi-envelope"></i> Contact Us</h1>
      <p>We'd love to hear from you. Reach out to us with your questions or feedback.</p>
    </div>
  </div>

  <!-- Main Content -->
  <div class="contact-container">
    <div class="row g-4">
      <!-- Contact Info & Map -->
      <div class="col-lg-5">
        <div class="contact-info-card">
          <div class="info-item">
            <div class="info-icon">
              <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div class="info-content">
              <h4>Address</h4>
              <p><?= htmlspecialchars($assocAddress) ?></p>
            </div>
          </div>

          <div class="info-item">
            <div class="info-icon">
              <i class="bi bi-telephone-fill"></i>
            </div>
            <div class="info-content">
              <h4>Phone</h4>
              <p><?= htmlspecialchars($assocPhone) ?></p>
            </div>
          </div>

          <div class="info-item">
            <div class="info-icon">
              <i class="bi bi-envelope-fill"></i>
            </div>
            <div class="info-content">
              <h4>Email</h4>
              <p><?= htmlspecialchars($assocEmail) ?></p>
            </div>
          </div>
        </div>

        <div class="map-container">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3859.123456789!2d120.2833!3d14.8397!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397c7abc1234567%3A0xabcdef123456789!2s123%20Association%20Street%2C%20Olongapo%2C%20Philippines!5e0!3m2!1sen!2sph!4v1697720400000!5m2!1sen!2sph"
            width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
      </div>

      <!-- Contact Form -->
      <div class="col-lg-7">
        <div class="form-card">
          <?php if(!empty($success_msg)): ?>
            <div class="alert alert-success"><?= $success_msg ?></div>
          <?php elseif(!empty($error_msg)): ?>
            <div class="alert alert-danger"><?= $error_msg ?></div>
          <?php endif; ?>

          <h4>Send Us a Message</h4>
          <form method="POST" action="">
            <div class="mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email Address</label>
              <input type="email" name="email" class="form-control" placeholder="example@email.com" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Message</label>
              <textarea name="message" class="form-control" rows="5" placeholder="Type your message here..." required></textarea>
            </div>
            <button type="submit" class="btn-submit">
              <i class="bi bi-send-fill me-2"></i>Send Message
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include("partials/footer.php"); ?>
<?php include 'chatbox.php'; ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
