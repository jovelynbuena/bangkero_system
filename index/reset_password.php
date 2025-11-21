<?php
require "config.php";

if (isset($_GET["token"])) {
    $token = $_GET["token"];

    // Check if token is valid
    $stmt = $conn->prepare("SELECT email FROM users WHERE reset_token=? AND reset_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Invalid or expired token.");
    }

    $row = $result->fetch_assoc();
    $email = $row["email"];
} else {
    die("No token provided.");
}

if (isset($_POST["submit"])) {
    $new_password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Update the password
    $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expiry=NULL WHERE email=?");
    $stmt->bind_param("ss", $new_password, $email);
    $stmt->execute();

    echo "Password reset successful. <a href='login.php'>Login here</a>";
}
?>

<form method="post">
    <input type="password" name="password" required placeholder="Enter new password">
    <button type="submit" name="submit">Reset Password</button>
</form>
