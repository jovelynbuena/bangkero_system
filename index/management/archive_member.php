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

$id = intval($_POST['id']);

try {
    $conn->begin_transaction();

    // Fetch ALL member data (make sure column names match your members table!)
    $stmt = $conn->prepare("SELECT * FROM members WHERE id = ? FOR UPDATE");
    if (!$stmt) {
        throw new Exception("SQL prepare failed (SELECT): " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $member = $res->fetch_assoc();
    $stmt->close();

    if (!$member) {
        $conn->rollback();
        http_response_code(404);
        echo "Member not found";
        exit;
    }

    // Insert ALL fields into archive table
    $stmt = $conn->prepare("
        INSERT INTO member_archive (
            member_id, name, dob, gender, phone, email, address, 
            work_type, license_number, boat_name, fishing_area, 
            emergency_name, emergency_phone, agreement, image, archived_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    if (!$stmt) {
        throw new Exception("SQL prepare failed (INSERT): " . $conn->error);
    }
    
    $stmt->bind_param(
        "issssssssssssis",
        $member['id'],
        $member['name'],
        $member['dob'],
        $member['gender'],
        $member['phone'],
        $member['email'],
        $member['address'],
        $member['work_type'],
        $member['license_number'],
        $member['boat_name'],
        $member['fishing_area'],
        $member['emergency_name'],
        $member['emergency_phone'],
        $member['agreement'],
        $member['image']
    );
    
    if (!$stmt->execute()) {
        $conn->rollback();
        throw new Exception("Failed to archive: " . $stmt->error);
    }
    $stmt->close();

    // Delete from active members
    $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
    if (!$stmt) {
        throw new Exception("SQL prepare failed (DELETE): " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        $conn->rollback();
        throw new Exception("Failed to delete after archive: " . $stmt->error);
    }
    $stmt->close();

    $conn->commit();
    echo "Success";
} catch (Throwable $e) {
    if ($conn) {
        @$conn->rollback();
    }
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
