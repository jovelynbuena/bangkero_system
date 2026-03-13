<?php
/**
 * EMAIL CONFIGURATION
 * Using PHPMailer with Gmail SMTP
 * 
 * For Gmail, you need to:
 * 1. Enable 2-Factor Authentication on your Google account
 * 2. Generate an "App Password" at: https://myaccount.google.com/apppasswords
 * 3. Use that App Password below (NOT your regular Gmail password)
 */

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'jovelynbuena2@gmail.com');  
define('SMTP_PASS', 'wsdy kupe wvem gxml');      
define('SMTP_FROM', 'jovelynbuena2@gmail.com');  
define('SMTP_FROM_NAME', 'Bangkero & Fishermen Association');

// Website URL (for reset links)
// For local testing: http://localhost/bangkero_system
// For production: http://yourwebsite.com
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
define('BASE_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . '/bangkero_system');
