<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

// Read filters from query string (same as officerslist.php)
$search      = trim($_GET['search'] ?? '');
$role_filter = $_GET['role_filter'] ?? '';
$term_status = $_GET['term_status'] ?? 'all';
$sort        = $_GET['sort'] ?? 'pos_asc';

$where  = [];
$params = [];
$types  = '';

if ($term_status === 'current') {
    $where[] = 'o.term_end >= CURDATE()';
} elseif ($term_status === 'previous') {
    $where[] = 'o.term_end < CURDATE()';
}

if ($search !== '') {
    $where[] = '(m.name LIKE ? OR r.role_name LIKE ? OR o.description LIKE ?)';
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types   .= 'sss';
}

if ($role_filter !== '') {
    $where[]  = 'o.role_id = ?';
    $params[] = $role_filter;
    $types   .= 'i';
}

$where_sql = '';
if (!empty($where)) {
    $where_sql = ' WHERE ' . implode(' AND ', $where);
}

$order_sql = ' ORDER BY r.role_name ASC';
if ($sort === 'pos_desc') {
    $order_sql = ' ORDER BY r.role_name DESC';
} elseif ($sort === 'name_asc') {
    $order_sql = ' ORDER BY m.name ASC';
} elseif ($sort === 'name_desc') {
    $order_sql = ' ORDER BY m.name DESC';
} elseif ($sort === 'term_new') {
    $order_sql = ' ORDER BY o.term_start DESC';
} elseif ($sort === 'term_old') {
    $order_sql = ' ORDER BY o.term_start ASC';
}

$sql = "
    SELECT 
        o.id,
        m.name       AS member_name,
        o.image      AS officer_image,
        r.role_name  AS position,
        o.term_start,
        o.term_end,
        o.description
    FROM officers o
    JOIN members m       ON o.member_id = m.id
    JOIN officer_roles r ON o.role_id   = r.id
    {$where_sql}
    {$order_sql}
";

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

// Load logo
require_once __DIR__ . '/../../config/logo_helper.php';
$logoPath = $assocLogoPath;
$logoData = $assocLogoB64;

// Set headers for PDF-like download
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Officers Export Report</title>
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
            border-bottom: 3px solid #2E86AB;
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
            padding: 12px 10px;
            text-align: left;
            font-size: 13px;
        }
        th {
            background-color: #2E86AB;
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
        .officer-photo {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto;
            border: 2px solid #e5e7eb;
        }
        .initials-circle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2E86AB, #1B4F72);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            margin: 0 auto;
        }
        .name-cell {
            font-weight: 600;
            color: #1f2937;
        }
        .position-cell {
            color: #6b7280;
        }
        .term-cell {
            color: #374151;
            white-space: nowrap;
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
            .header { border-bottom: 2px solid #2E86AB; }
        }
    </style>
</head>
<body>
    <div class="header">
        <?php if ($logoData): ?>
        <img src="data:image/png;base64,<?php echo $logoData; ?>" class="logo" alt="Logo">
        <?php endif; ?>
        <div class="header-text">
            <h1>Officers Export Report</h1>
            <p><?php echo htmlspecialchars($assocName); ?></p>
            <p><?php echo htmlspecialchars($assocAddress); ?></p>
        </div>
    </div>
    
    <div class="meta-bar">
        <span>Generated on: <strong><?php echo $generatedAt; ?></strong></span>
        <span>Total Officers: <strong><?php echo count($rows); ?></strong></span>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Officer Name</th>
                <th>Position</th>
                <th>Term Start</th>
                <th>Term End</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
                <td class="photo-cell">
                    <?php
                    $photoPath = $basePath . 'uploads' . DIRECTORY_SEPARATOR . 'officers' . DIRECTORY_SEPARATOR . ($row['officer_image'] ?? '');
                    if (!empty($row['officer_image']) && file_exists($photoPath)):
                        $photoData = base64_encode(file_get_contents($photoPath));
                        $ext = pathinfo($row['officer_image'], PATHINFO_EXTENSION);
                        $mime = in_array(strtolower($ext), ['png']) ? 'image/png' : 'image/jpeg';
                    ?>
                        <img src="data:<?php echo $mime; ?>;base64,<?php echo $photoData; ?>" class="officer-photo" alt="Photo">
                    <?php else: ?>
                        <div class="initials-circle"><?php echo getInitials($row['member_name']); ?></div>
                    <?php endif; ?>
                </td>
                <td class="name-cell"><?php echo htmlspecialchars($row['member_name']); ?></td>
                <td class="position-cell"><?php echo htmlspecialchars($row['position']); ?></td>
                <td class="term-cell"><?php echo !empty($row['term_start']) ? date('M d, Y', strtotime($row['term_start'])) : ''; ?></td>
                <td class="term-cell"><?php echo !empty($row['term_end']) ? date('M d, Y', strtotime($row['term_end'])) : ''; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <span>Generated by <?php echo htmlspecialchars($assocName); ?> System</span>
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
