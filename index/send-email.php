<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/../vendor/autoload.php";

$name = $_POST["name"];
$email = $_POST["email"];
$subject = $_POST["subject"];
$message = $_POST["message"];

$mail = new PHPMailer(true);

try {
    // Enable debugging
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = 'html';

    // SMTP settings
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com"; // Change to your SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = "your_email@gmail.com"; // Your SMTP username
    $mail->Password = "your_app_password"; // Use an app password if using Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS encryption
    $mail->Port = 587; // Use 465 for SSL

    // Fix possible DNS resolution issue
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    // Email settings
    $mail->setFrom($email, $name);
    $mail->addAddress("dave@example.com", "Dave"); 
    $mail->Subject = $subject;
    $mail->Body = $message;

    // Send email
    if ($mail->send()) {
        echo "Email sent successfully.";
    } else {
        echo "Email could not be sent.";
    }
} catch (Exception $e) {
    echo "Error: {$mail->ErrorInfo}";
}
