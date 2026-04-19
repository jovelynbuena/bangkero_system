<?php
/**
 * EXPORT SELECTED MEMBERS
 * Exports only the selected members to CSV/PDF
 */
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

// Get parameters
$ids = $_GET['ids'] ?? '';
$format = $_GET['format'] ?? 'csv';

if (empty($ids)) {
    die('No members selected');
}

// Convert to array
$id_array = explode(',', $ids);
$id_array = array_filter($id_array, 'is_numeric');

if (empty($id_array)) {
    die('Invalid member IDs');
}

// Fetch selected members
$placeholders = str_repeat('?,', count($id_array) - 1) . '?';
$types = str_repeat('i', count($id_array));

$sql = "SELECT * FROM members WHERE id IN ($placeholders) ORDER BY name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$id_array);
$stmt->execute();
$result = $stmt->get_result();

// CSV Export
if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="selected_members_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Header row
    fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Address', 'Date of Birth', 'Gender', 'Work Type', 'Date Joined']);
    
    // Data rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['email'],
            $row['phone'],
            $row['address'],
            $row['dob'],
            $row['gender'],
            $row['work_type'],
            $row['created_at'] ?? ''
        ]);
    }
    
    fclose($output);
    exit;
}

// PDF Export (basic implementation)
if ($format === 'pdf') {
    require_once __DIR__ . '/../../config/logo_helper.php';
    $logoPath = $assocLogoPath;
    $logoData = $assocLogoB64;
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Selected Members Export</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            .header { display: flex; align-items: center; margin-bottom: 20px; border-bottom: 3px solid #0e7490; padding-bottom: 15px; }
            .logo { width: 70px; height: 70px; margin-right: 15px; object-fit: contain; }
            .header-text h1 { margin: 0; color: #1f2937; font-size: 22px; }
            .header-text p { margin: 3px 0; color: #6b7280; font-size: 12px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #0e7490; color: white; }
            h1 { color: #2E86AB; }
        </style>
    </head>
    <body>
        <div class="header">
            ' . ($logoData ? '<img src="data:image/png;base64,' . $logoData . '" class="logo" alt="Logo">' : '') . '
            <div class="header-text">
                <h1>Selected Members Export</h1>
                <p>' . htmlspecialchars($assocName) . '</p>
                <p>Generated: ' . date('F d, Y h:i A') . '</p>
            </div>
        </div>';
    echo '<table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>';
    
    while ($row = $result->fetch_assoc()) {
        echo '<tr>
                <td>' . htmlspecialchars($row['id']) . '</td>
                <td>' . htmlspecialchars($row['name']) . '</td>
                <td>' . htmlspecialchars($row['email']) . '</td>
                <td>' . htmlspecialchars($row['phone']) . '</td>
                <td>' . htmlspecialchars($row['address']) . '</td>
              </tr>';
    }
    
    echo '</tbody>
        </table>
        <script>window.print();</script>
    </body>
    </html>';
    exit;
}
?>
