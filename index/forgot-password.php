<?php
if (isset($_POST["submit"])) {
    require '../config/db_connect.php';

    $email = $_POST["email"];
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate a token
        $token = bin2hex(random_bytes(50));

        // Store the token in the database
        $stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_expiry=DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE email=?");
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        // Send the email with reset link
        $reset_link = "http://yourwebsite.com/reset_password.php?token=$token";
        $subject = "Password Reset Request";
        $message = "Click the link to reset your password: $reset_link";
        $headers = "From: no-reply@yourwebsite.com\r\n";
        
        mail($email, $subject, $message, $headers);

        echo "Password reset link sent to your email.";
    } else {
        echo "No account found with that email.";
    }
}
?>

<form method="post">
    <input type="email" name="email" required placeholder="Enter your email">
    <button type="submit" name="submit">Reset Password</button>
</form>
