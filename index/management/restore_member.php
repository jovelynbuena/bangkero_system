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

$id = intval($_POST['id']); // ID ng member_archive row

try {
    $conn->begin_transaction();

    // Kunin yung archive data
    $stmt = $conn->prepare("SELECT member_id, name, address, email, phone 
                            FROM member_archive WHERE member_id = ? FOR UPDATE");
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

    // Ibalik sa members table (lahat kasama address)
    $stmt = $conn->prepare("INSERT INTO members (id, name, address, email, phone) 
                            VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("SQL prepare failed (INSERT): " . $conn->error);
    }
    $stmt->bind_param(
        "issss",
        $archived['member_id'],
        $archived['name'],
        $archived['address'],
        $archived['email'],
        $archived['phone']
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
