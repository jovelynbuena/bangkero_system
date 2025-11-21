<?php
session_start();

if($_SESSION['username']==""){

    header('location: login.php');
}
require_once('../config/db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $member_id = $_POST['member_id'];
    $position = $_POST['position'];
    $term_start = $_POST['term_start'];  // ✅ Correct field
    $term_end = $_POST['term_end'];      // ✅ Correct field

    // Handle officer image upload
    $officer_image = '';
    if (isset($_FILES['officer_image']) && $_FILES['officer_image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp = $_FILES['officer_image']['tmp_name'];
        $officer_image = time() . '_' . basename($_FILES['officer_image']['name']);
        $target_dir = '../uploads/';
        $target_file = $target_dir . $officer_image;

        if (!move_uploaded_file($image_tmp, $target_file)) {
            echo "Error uploading image.";
            exit();
        }
    }

    // Prepare and execute insert
    $stmt = $conn->prepare("INSERT INTO officers (member_id, position, term_start, term_end, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $member_id, $position, $term_start, $term_end, $officer_image);

    if ($stmt->execute()) {
        header("Location: officerslist.php");
        exit();
    } else {
        echo "Error saving officer: " . $stmt->error;
    }

    $stmt->close();
}
?>
