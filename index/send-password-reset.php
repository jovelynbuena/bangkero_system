<?php

if (!isset($_POST["email"]) || empty($_POST["email"])) {
    die("Error: Email is required.");
}

$email = trim($_POST["email"]);

$token = bin2hex(random_bytes(16));
$token_hash = hash("sha256", $token);
$expiry = date("Y-m-d H:i:s", time() + 60 * 30); // Fixed datetime format

require __DIR__ . "/../config/db_connect.php";

$sql = "UPDATE users 
        SET reset_token_hash = ?, 
            reset_token_expires_at = ? 
        WHERE email = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("sss", $token_hash, $expiry, $email);

if ($stmt->execute()) {
    echo "Password reset link has been sent if the email exists in our records.";
} else {
    echo "Error updating record: " . $stmt->error;
}

$stmt->close();
$conn->close();

?>
