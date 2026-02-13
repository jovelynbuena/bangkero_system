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
$search = trim($_GET['q'] ?? '');
$category_filter = trim($_GET['category'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');
$sort = $_GET['sort'] ?? 'date_desc';

if (empty($ids)) {
    die('No galleries selected');
}

if ($ids === 'all') {
    // Export based on active filters
    $where_conditions = [];
    $query_params = [];
    $param_types = '';

    if ($search !== '') {
        $where_conditions[] = "(title LIKE ? OR category LIKE ?)";
        $search_term = "%{$search}%";
        $query_params[] = $search_term;
        $query_params[] = $search_term;
        $param_types .= 'ss';
    }

    if ($category_filter !== '') {
        $where_conditions[] = "category = ?";
        $query_params[] = $category_filter;
        $param_types .= 's';
    }

    if ($date_from !== '') {
        $where_conditions[] = "DATE(created_at) >= ?";
        $query_params[] = $date_from;
        $param_types .= 's';
    }

    if ($date_to !== '') {
        $where_conditions[] = "DATE(created_at) <= ?";
        $query_params[] = $date_to;
        $param_types .= 's';
    }

    $sql = "SELECT * FROM galleries";
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }

    switch ($sort) {
        case 'date_asc': $sql .= " ORDER BY created_at ASC"; break;
        case 'title_asc': $sql .= " ORDER BY title ASC"; break;
        case 'title_desc': $sql .= " ORDER BY title DESC"; break;
        default: $sql .= " ORDER BY created_at DESC"; break;
    }

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
        die('Invalid gallery IDs');
    }

    $placeholders = str_repeat('?,', count($id_array) - 1) . '?';
    $types = str_repeat('i', count($id_array));

    $sql = "SELECT * FROM galleries WHERE id IN ($placeholders) ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$id_array);
    $stmt->execute();
    $result = $stmt->get_result();
}

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="galleries_export_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Title', 'Category', 'Image Count', 'Created At']);
    
    while ($row = $result->fetch_assoc()) {
        $images = array_filter(explode(',', $row['images'] ?? ''));
        fputcsv($output, [
            $row['id'],
            $row['title'],
            $row['category'],
            count($images),
            $row['created_at']
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
        <title>Galleries Export</title>
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
        <h1>Galleries Export</h1>
        <div class="meta">Generated: ' . date('F d, Y h:i A') . '</div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Image Count</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>';
    
    while ($row = $result->fetch_assoc()) {
        $images = array_filter(explode(',', $row['images'] ?? ''));
        echo '<tr>
                <td>' . htmlspecialchars($row['id']) . '</td>
                <td>' . htmlspecialchars($row['title']) . '</td>
                <td>' . htmlspecialchars($row['category']) . '</td>
                <td>' . count($images) . '</td>
                <td>' . date('M d, Y', strtotime($row['created_at'])) . '</td>
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
    header('Content-Disposition: attachment; filename="galleries_export_' . date('Y-m-d') . '.xls"');
    
    echo '<table border="1">
            <tr>
                <th style="background-color: #667eea; color: white;">ID</th>
                <th style="background-color: #667eea; color: white;">Title</th>
                <th style="background-color: #667eea; color: white;">Category</th>
                <th style="background-color: #667eea; color: white;">Image Count</th>
                <th style="background-color: #667eea; color: white;">Created At</th>
            </tr>';
            
    while ($row = $result->fetch_assoc()) {
        $images = array_filter(explode(',', $row['images'] ?? ''));
        echo '<tr>
                <td>' . htmlspecialchars($row['id']) . '</td>
                <td>' . htmlspecialchars($row['title']) . '</td>
                <td>' . htmlspecialchars($row['category']) . '</td>
                <td>' . count($images) . '</td>
                <td>' . htmlspecialchars($row['created_at']) . '</td>
              </tr>';
    }
    echo '</table>';
    exit;
}
?>
