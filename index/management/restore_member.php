<?php
session_start();

header('Content-Type: text/plain');

if (!isset($_SESSION['username']) || $_SESSION['username'] === "") {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo "No ID provided";
    exit;
}

require_once('../../config/db_connect.php');

// Ensure member_archive table has all required columns
$conn->query("CREATE TABLE IF NOT EXISTS member_archive (
    member_id INT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    dob DATE DEFAULT NULL,
    gender VARCHAR(20) DEFAULT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    work_type VARCHAR(50) DEFAULT NULL,
    license_number VARCHAR(100) DEFAULT NULL,
    boat_name VARCHAR(100) DEFAULT NULL,
    fishing_area VARCHAR(200) DEFAULT NULL,
    emergency_name VARCHAR(150) DEFAULT NULL,
    emergency_phone VARCHAR(50) DEFAULT NULL,
    agreement TINYINT DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$id = intval($_POST['id']); // ID ng member_archive row

try {
    $conn->begin_transaction();

    // Kunin LAHAT ng archive data
    $stmt = $conn->prepare("SELECT * FROM member_archive WHERE member_id = ? FOR UPDATE");
    if (!$stmt) {
        throw new Exception("SQL prepare failed (SELECT): " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $archived = $res->fetch_assoc();
    $stmt->close();

    if (!$archived) {
        $conn->rollback();
        http_response_code(404);
        echo "Archived member not found";
        exit;
    }

    // Ibalik LAHAT ng fields sa members table (handle NULL values with defaults)
    $name = $archived['name'] ?? '';
    $dob = $archived['dob'] ?? null;
    $gender = $archived['gender'] ?? null;
    $phone = $archived['phone'] ?? '';
    $email = $archived['email'] ?? '';
    $address = $archived['address'] ?? '';
    $work_type = $archived['work_type'] ?? null;
    $license_number = $archived['license_number'] ?? '';  // Default to empty string
    $boat_name = $archived['boat_name'] ?? '';
    $fishing_area = $archived['fishing_area'] ?? '';
    $emergency_name = $archived['emergency_name'] ?? '';
    $emergency_phone = $archived['emergency_phone'] ?? '';
    $agreement = $archived['agreement'] ?? null;
    $image = $archived['image'] ?? null;

    $stmt = $conn->prepare("INSERT INTO members (
            name, dob, gender, phone, email, address, 
            work_type, license_number, boat_name, fishing_area, 
            emergency_name, emergency_phone, agreement, image
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("SQL prepare failed (INSERT): " . $conn->error);
    }
    $stmt->bind_param(
        "ssssssssssssss",
        $name,
        $dob,
        $gender,
        $phone,
        $email,
        $address,
        $work_type,
        $license_number,
        $boat_name,
        $fishing_area,
        $emergency_name,
        $emergency_phone,
        $agreement,
        $image
    );

    if (!$stmt->execute()) {
        $conn->rollback();
        throw new Exception("Failed to restore: " . $stmt->error);
    }
    $stmt->close();

    // Burahin na sa archive
    $stmt = $conn->prepare("DELETE FROM member_archive WHERE member_id = ?");
    if (!$stmt) {
        throw new Exception("SQL prepare failed (DELETE): " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        $conn->rollback();
        throw new Exception("Failed to delete from archive: " . $stmt->error);
    }
    $stmt->close();

   $conn->commit();
// redirect to your memberlist page
header("Location: memberlist.php?restored=1");
exit;

} catch (Throwable $e) {
    if ($conn) {
        @$conn->rollback();
    }
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
