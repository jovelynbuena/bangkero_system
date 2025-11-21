<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Help & Support | Bangkero & Fishermen Association</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f7f7f7; }
        .main-header {
            background: linear-gradient(90deg, #0d6efd 0%, #0a58ca 100%);
            color: #fff;
            padding: 32px 0 18px 0;
            text-align: center;
            margin-bottom: 0;
        }
        .main-header h1 {
            font-weight: 800;
            font-size: 2.1rem;
            margin-bottom: 6px;
        }
        .main-header p {
            font-size: 1.05rem;
            color: #e0e0e0;
            margin-bottom: 0;
        }
        .help-card {
            max-width: 600px;
            margin: 32px auto 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
            padding: 32px 24px 24px 24px;
        }
        .accordion-button:focus {
            box-shadow: none;
        }
        .contact-card {
            background: #e7f1ff;
            border-radius: 10px;
            padding: 18px;
            margin-top: 24px;
            box-shadow: 0 2px 12px rgba(13,110,253,0.06);
        }
        .contact-card h5 {
            color: #0d6efd;
            font-weight: 700;
        }
        .contact-card i {
            color: #0d6efd;
            font-size: 1.3rem;
            margin-right: 8px;
        }
        @media (max-width: 600px) {
            .help-card { padding: 12px 2vw; }
            .main-header { padding: 18px 0 8px 0; }
        }
    </style>
</head>
<body>
    <div class="main-header">
        <h1><i class="bi bi-question-circle"></i> Help & Support</h1>
        <p>Find answers to common questions or contact us for assistance.</p>
    </div>
    <div class="help-card shadow">
        <h4 class="mb-4"><i class="bi bi-info-circle"></i> Frequently Asked Questions</h4>
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="faq1-heading">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">
                        <i class="bi bi-person-circle me-2"></i> How do I update my profile?
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show" aria-labelledby="faq1-heading" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Go to <a href="member_settings.php#profile"><b>Profile</b></a>, update your information, and click <b>Save Changes</b>.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="faq2-heading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                        <i class="bi bi-lock me-2"></i> How do I change my password?
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" aria-labelledby="faq2-heading" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Go to <a href="member_settings.php#security"><b>Security</b></a>, enter your current and new password, then click <b>Change Password</b>.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="faq3-heading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                        <i class="bi bi-archive me-2"></i> What
                    </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" aria-labelledby="faq3-heading" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        The <b>Archives</b> shows your activity log, such as logins, profile updates, and other actions.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="faq4-heading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4">
                        <i class="bi bi-gear me-2"></i> How do I change my language or theme?
                    </button>
                </h2>
                <div id="faq4" class="accordion-collapse collapse" aria-labelledby="faq4-heading" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Go to <b>Settings</b> &gt; <b>Preferences</b> and select your preferred language or theme, then save.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="faq5-heading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5" aria-expanded="false" aria-controls="faq5">
                        <i class="bi bi-envelope me-2"></i> Who do I contact for support?
                    </button>
                </h2>
                <div id="faq5" class="accordion-collapse collapse" aria-labelledby="faq5-heading" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        You can email us at <a href="mailto:support@bangkero.org">support@bangkero.org</a> or contact your association officer.
                    </div>
                </div>
            </div>
        </div>
        <div class="contact-card mt-4">
            <h5><i class="bi bi-headset"></i> Need more help?</h5>
            <p class="mb-2">Our support team is here for you. Reach out via:</p>
            <ul class="list-unstyled mb-2">
                <li><i class="bi bi-envelope"></i> <a href="mailto:support@bangkero.org">support@bangkero.org</a></li>
                <li><i class="bi bi-telephone"></i> 0912-345-6789</li>
                <li><i class="bi bi-facebook"></i> <a href="https://facebook.com/bangkero" target="_blank">facebook.com/bangkero</a></li>
            </ul>
            <div class="text-muted small">Office hours: Mon-Fri, 8:00AM - 5:00PM</div>
        </div>
        <div class="text-center mt-4">
            <a href="member.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
    <!-- Profile Panel -->
    <div id="profile" class="container mt-5">
        <h2 class="mb-4"><i class="bi bi-person-circle"></i> Profile Panel</h2>
        <!-- Profile content here -->
    </div>

    <!-- Security Panel -->
    <div id="security" class="container mt-5">
        <h2 class="mb-4"><i class="bi bi-lock"></i> Security Panel</h2>
        <!-- Security content here -->
    </div>

    <!-- Archives Panel -->
    <div id="archives" class="container mt-5">
        <h2 class="mb-4"><i class="bi bi-archive"></i> Archives Panel</h2>
        <!-- Archives content here -->
    </div>

    <!-- Preferences Panel -->
    <div id="preferences" class="container mt-5">
        <h2 class="mb-4"><i class="bi bi-gear"></i> Preferences Panel</h2>
        <!-- Preferences content here -->
    </div>
    <footer class="text-center p-3 mt-5">
        <small>&copy; <?= date("Y"); ?> Bangkero & Fishermen Association. All rights reserved.</small>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>