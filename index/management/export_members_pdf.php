<<<<<<< HEAD
<?php
=======
﻿<?php
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

// Read filters from query string (same as memberlist.php)
$search = trim($_GET['search'] ?? '');
$role = $_GET['role'] ?? '';
$sort = $_GET['sort'] ?? 'name_asc';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$where_clauses = [];
$params = [];
$types = '';

// Search filter
if (!empty($search)) {
    $where_clauses[] = "(name LIKE ? OR phone LIKE ? OR email LIKE ? OR id LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ssss';
}

// Role filter
if ($role === 'officer') {
    $where_clauses[] = "id IN (SELECT member_id FROM officers)";
} elseif ($role === 'member') {
    $where_clauses[] = "id NOT IN (SELECT member_id FROM officers)";
}

// Date range filter
if (!empty($date_from) && !empty($date_to)) {
    $where_clauses[] = "DATE(created_at) BETWEEN ? AND ?";
    $params[] = $date_from;
    $params[] = $date_to;
    $types .= 'ss';
} elseif (!empty($date_from)) {
    $where_clauses[] = "DATE(created_at) >= ?";
    $params[] = $date_from;
    $types .= 's';
} elseif (!empty($date_to)) {
    $where_clauses[] = "DATE(created_at) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

// Build WHERE clause
$where_sql = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";

// Sort order
$order_sql = "ORDER BY name ASC";
if ($sort === 'name_desc') {
    $order_sql = "ORDER BY name DESC";
} elseif ($sort === 'date_new') {
    $order_sql = "ORDER BY created_at DESC";
} elseif ($sort === 'date_old') {
    $order_sql = "ORDER BY created_at ASC";
}

// Fetch all members (no pagination for PDF)
$sql = "SELECT * FROM members {$where_sql} {$order_sql}";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$rows = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

$generatedAt = date('F d, Y h:i A');

// Get initials from name
function getInitials($name) {
    $name = trim($name);
    if (empty($name)) return '?';
    $parts = explode(' ', $name);
    if (count($parts) >= 2) {
        return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts)-1], 0, 1));
    }
    return strtoupper(substr($name, 0, 2));
}

// Check if member is an officer
function getMemberRole($conn, $member_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM officers WHERE member_id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return ($result['count'] > 0) ? 'Officer' : 'Member';
}

// Load logo
<<<<<<< HEAD
$basePath = realpath(__DIR__ . '/../..') . DIRECTORY_SEPARATOR;
$logoPath = $basePath . 'images' . DIRECTORY_SEPARATOR . 'logo1.png';
$logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
=======
require_once __DIR__ . '/../../config/logo_helper.php';
$logoPath = $assocLogoPath;
$logoData = $assocLogoB64;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560

// Set headers
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Members Export Report</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            padding: 30px;
            color: #333;
            margin: 0;
            background: #fff;
        }
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
<<<<<<< HEAD
            border-bottom: 3px solid #667eea;
=======
            border-bottom: 3px solid #2E86AB;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
            padding-bottom: 20px;
        }
        .logo {
            width: 70px;
            height: 70px;
            margin-right: 20px;
            object-fit: contain;
        }
        .header-text h1 {
            margin: 0 0 5px 0;
            color: #1f2937;
            font-size: 24px;
        }
        .header-text p {
            margin: 2px 0;
            color: #6b7280;
            font-size: 13px;
        }
        .meta-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px 0;
            color: #6b7280;
            font-size: 13px;
        }
        .meta-bar strong { color: #1f2937; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: left;
            font-size: 12px;
        }
        th {
<<<<<<< HEAD
            background-color: #667eea;
=======
            background-color: #2E86AB;
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .photo-cell {
            width: 60px;
            text-align: center;
        }
        .member-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto;
            border: 2px solid #e5e7eb;
        }
        .initials-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
<<<<<<< HEAD
            background: linear-gradient(135deg, #667eea, #764ba2);
=======
            background: linear-gradient(135deg, #2E86AB, #1B4F72);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
            margin: 0 auto;
        }
        .name-cell {
            font-weight: 600;
            color: #1f2937;
        }
        .contact-cell {
            color: #6b7280;
            font-size: 11px;
        }
        .role-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .role-officer {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .role-member {
            background-color: #d1fae5;
            color: #065f46;
        }
        .date-cell {
            color: #6b7280;
            white-space: nowrap;
            font-size: 11px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #9ca3af;
        }
        @media print {
            body { padding: 20px; }
            .no-print { display: none; }
<<<<<<< HEAD
            .header { border-bottom: 2px solid #667eea; }
=======
            .header { border-bottom: 2px solid #2E86AB; }
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
        }
    </style>
</head>
<body>
    <div class="header">
        <?php if ($logoData): ?>
        <img src="data:image/png;base64,<?php echo $logoData; ?>" class="logo" alt="Logo">
        <?php endif; ?>
        <div class="header-text">
            <h1>Members Export Report</h1>
<<<<<<< HEAD
            <p>Bankero and Fisherman Association</p>
            <p>Barangay Barretto, Olongapo City</p>
=======
            <p><?php echo htmlspecialchars($assocName); ?></p>
            <p><?php echo htmlspecialchars($assocAddress); ?></p>
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
        </div>
    </div>
    
    <div class="meta-bar">
        <span>Generated on: <strong><?php echo $generatedAt; ?></strong></span>
        <span>Total Members: <strong><?php echo count($rows); ?></strong></span>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Member Name</th>
                <th>Contact</th>
                <th>Role</th>
                <th>Joined Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): 
                $memberRole = getMemberRole($conn, $row['id']);
                $roleClass = ($memberRole === 'Officer') ? 'role-officer' : 'role-member';
            ?>
            <tr>
                <td class="photo-cell">
                    <?php
                    $photoPath = $basePath . 'uploads' . DIRECTORY_SEPARATOR . 'members' . DIRECTORY_SEPARATOR . ($row['image'] ?? '');
                    if (!empty($row['image']) && file_exists($photoPath) && !is_dir($photoPath)):
                        $photoData = base64_encode(file_get_contents($photoPath));
                        $ext = pathinfo($row['image'], PATHINFO_EXTENSION);
                        $mime = in_array(strtolower($ext), ['png']) ? 'image/png' : 'image/jpeg';
                    ?>
                        <img src="data:<?php echo $mime; ?>;base64,<?php echo $photoData; ?>" class="member-photo" alt="Photo">
                    <?php else: ?>
                        <div class="initials-circle"><?php echo getInitials($row['name']); ?></div>
                    <?php endif; ?>
                </td>
                <td class="name-cell"><?php echo htmlspecialchars($row['name']); ?></td>
                <td class="contact-cell">
                    <div><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></div>
                    <div style="margin-top: 3px; color: #9ca3af;"><?php echo htmlspecialchars($row['email'] ?? 'N/A'); ?></div>
                </td>
                <td>
                    <span class="role-badge <?php echo $roleClass; ?>"><?php echo $memberRole; ?></span>
                </td>
                <td class="date-cell">
                    <?php echo !empty($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : 'N/A'; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="footer">
<<<<<<< HEAD
        <span>Generated by Bankero and Fisherman Association System</span>
=======
        <span>Generated by <?php echo htmlspecialchars($assocName); ?> System</span>
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
        <span>Page 1 of 1</span>
    </div>
    
    <script>
        // Auto print for PDF download experience
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
