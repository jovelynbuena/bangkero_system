<?php
include("../../config/db_connect.php"); // Database connection

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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fa;
      color: #333;
    }

    /* Hero Section */
    .hero-section {
      background: linear-gradient(90deg, #004C6D 0%, #006E96 100%);
      color: white;
      padding: 140px 20px 90px 20px;
      text-align: center;
    }
    .hero-section h1 {
      font-weight: 700;
      font-size: 2.8rem;
      margin-bottom: 10px;
    }
    .hero-section p {
      font-size: 1.2rem;
      opacity: 0.9;
    }

    /* Contact Info */
    .contact-info h4 {
      margin-top: 20px;
      font-weight: 600;
      color: #004C6D;
    }
    .contact-info p {
      font-size: 0.95rem;
      color: #555;
    }

    /* Map */
    .map-container {
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      margin-top: 20px;
    }

    /* Form */
    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
    }

    .form-control {
      border-radius: 8px;
      padding: 10px;
    }

    .btn {
      border-radius: 8px;
      background-color: #004C6D;
      border: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .btn:hover {
      background-color: #006E96;
      transform: translateY(-1px);
    }

    .alert {
      border-radius: 8px;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
      .hero-section { padding-top: 180px; }
    }
  </style>
</head>
<body>

<?php include("partials/navbar.php"); ?>

<!-- Hero Section -->
<section class="hero-section">
  <h1>Contact Us</h1>
  <p>We’d love to hear from you. Reach out to us with your questions or feedback.</p>
</section>

<!-- Main Content -->
<div class="container my-5 pt-4">
  <div class="row g-5">
    <!-- Contact Info & Map -->
    <div class="col-md-6">
      <div class="contact-info">
        <h4><i class="bi bi-geo-alt-fill text-primary me-1"></i> Address</h4>
        <p>123 Association Street, Olongapo City, Philippines</p>

        <h4><i class="bi bi-telephone-fill text-primary me-1"></i> Phone</h4>
        <p>+63 912 345 6789</p>

        <h4><i class="bi bi-envelope-fill text-primary me-1"></i> Email</h4>
        <p>info@association.org</p>
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
    <div class="col-md-6">
      <div class="card p-4">
        <?php if(!empty($success_msg)): ?>
          <div class="alert alert-success"><?= $success_msg ?></div>
        <?php elseif(!empty($error_msg)): ?>
          <div class="alert alert-danger"><?= $error_msg ?></div>
        <?php endif; ?>

        <h4 class="mb-3 text-center" style="color:#004C6D;">Send Us a Message</h4>
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
          <button type="submit" class="btn btn-primary w-100">Send Message</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include("partials/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
