<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

// Must be admin
$role = strtolower($_SESSION['role'] ?? '');
if ($role !== 'admin') {
    echo json_encode(['count' => 0, 'accounts' => []]);
    exit;
}

include(__DIR__ . '/../../../../config/db_connect.php');

$count = 0;
$accounts = [];

try {
    // Same as manage_officer.php — pending officers/admins awaiting approval
    $res = $conn->query("SELECT id, username, first_name, last_name, created_at FROM users WHERE role IN ('officer','admin') AND status='pending' ORDER BY created_at DESC LIMIT 10");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $accounts[] = [
                'id'         => $row['id'],
                'username'   => $row['username'],
                'first_name' => $row['first_name'] ?? '',
                'last_name'  => $row['last_name']  ?? '',
                'created_at' => $row['created_at']  ?? '',
            ];
        }
        $count = count($accounts);
    }
} catch (Throwable) {
    $count = 0;
}

echo json_encode(['count' => $count, 'accounts' => $accounts]);
