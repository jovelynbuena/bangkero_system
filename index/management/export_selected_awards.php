<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

$ids = $_GET['ids'] ?? '';
$format = $_GET['format'] ?? 'csv';

// Filters for "all" export
$search = trim($_GET['search'] ?? '');
$category_filter = trim($_GET['category'] ?? '');
$year_filter = trim($_GET['year'] ?? '');
$body_filter = trim($_GET['awarding_body'] ?? '');

if (empty($ids)) {
    die('No awards selected');
}

if ($ids === 'all') {
    // Export based on active filters
    $where_conditions = [];
    $query_params = [];
    $param_types = '';

    if ($search !== '') {
        $where_conditions[] = "(award_title LIKE ? OR awarding_body LIKE ? OR description LIKE ?)";
        $search_term = "%{$search}%";
        $query_params[] = $search_term;
        $query_params[] = $search_term;
        $query_params[] = $search_term;
        $param_types .= 'sss';
    }

    if ($category_filter !== '') {
        $where_conditions[] = "category = ?";
        $query_params[] = $category_filter;
        $param_types .= 's';
    }

    if ($year_filter !== '') {
        $where_conditions[] = "year_received = ?";
        $query_params[] = $year_filter;
        $param_types .= 'i';
    }

    if ($body_filter !== '') {
        $where_conditions[] = "awarding_body = ?";
        $query_params[] = $body_filter;
        $param_types .= 's';
    }

    $sql = "SELECT * FROM awards";
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }
    $sql .= " ORDER BY year_received DESC, date_received DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($query_params)) {
        $stmt->bind_param($param_types, ...$query_params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Export selected IDs
    $id_array = explode(',', $ids);
    $id_array = array_filter($id_array, 'is_numeric');

    if (empty($id_array)) {
        die('Invalid award IDs');
    }

    $placeholders = str_repeat('?,', count($id_array) - 1) . '?';
    $types = str_repeat('i', count($id_array));

    $sql = "SELECT * FROM awards WHERE award_id IN ($placeholders) ORDER BY year_received DESC, date_received DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$id_array);
    $stmt->execute();
    $result = $stmt->get_result();
}

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="awards_export_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Award Title', 'Awarding Body', 'Category', 'Year', 'Date Received', 'Description']);
    
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['award_id'],
            $row['award_title'],
            $row['awarding_body'],
            $row['category'],
            $row['year_received'],
            $row['date_received'],
            $row['description']
        ]);
    }
    
    fclose($output);
    exit;
}

if ($format === 'pdf' || $format === 'print') {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Awards Export</title>
        <style>
            body { font-family: "Inter", sans-serif; padding: 40px; color: #333; }
            table { width: 100%; border-collapse: collapse; margin-top: 30px; }
            th, td { border: 1px solid #e0e0e0; padding: 12px; text-align: left; font-size: 14px; }
            th { background-color: #667eea; color: white; text-transform: uppercase; letter-spacing: 0.5px; }
            tr:nth-child(even) { background-color: #f8fafc; }
            h1 { color: #667eea; margin-bottom: 5px; }
            .meta { color: #64748b; font-size: 13px; margin-bottom: 20px; }
        </style>
    </head>
    <body onload="' . ($format === 'print' ? 'window.print()' : '') . '">
        <h1>Awards Export</h1>
        <div class="meta">Generated: ' . date('F d, Y h:i A') . '</div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Award Title</th>
                    <th>Awarding Body</th>
                    <th>Category</th>
                    <th>Year</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>';
    
    while ($row = $result->fetch_assoc()) {
        echo '<tr>
                <td>' . htmlspecialchars($row['award_id']) . '</td>
                <td>' . htmlspecialchars($row['award_title']) . '</td>
                <td>' . htmlspecialchars($row['awarding_body']) . '</td>
                <td>' . htmlspecialchars($row['category']) . '</td>
                <td>' . htmlspecialchars($row['year_received']) . '</td>
                <td>' . date('M d, Y', strtotime($row['date_received'])) . '</td>
              </tr>';
    }
    
    echo '</tbody>
        </table>
    </body>
    </html>';
    exit;
}

if ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="awards_export_' . date('Y-m-d') . '.xls"');
    
    echo '<table border="1">
            <tr>
                <th style="background-color: #667eea; color: white;">ID</th>
                <th style="background-color: #667eea; color: white;">Award Title</th>
                <th style="background-color: #667eea; color: white;">Awarding Body</th>
                <th style="background-color: #667eea; color: white;">Category</th>
                <th style="background-color: #667eea; color: white;">Year</th>
                <th style="background-color: #667eea; color: white;">Date Received</th>
                <th style="background-color: #667eea; color: white;">Description</th>
            </tr>';
            
    while ($row = $result->fetch_assoc()) {
        echo '<tr>
                <td>' . htmlspecialchars($row['award_id']) . '</td>
                <td>' . htmlspecialchars($row['award_title']) . '</td>
                <td>' . htmlspecialchars($row['awarding_body']) . '</td>
                <td>' . htmlspecialchars($row['category']) . '</td>
                <td>' . htmlspecialchars($row['year_received']) . '</td>
                <td>' . htmlspecialchars($row['date_received']) . '</td>
                <td>' . htmlspecialchars($row['description']) . '</td>
              </tr>';
    }
    echo '</table>';
    exit;
}
?>
