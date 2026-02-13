<?php
/**
 * BULK ARCHIVE/DELETE OFFICERS
 */
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

$ids = $_GET['ids'] ?? '';
$action = $_GET['action'] ?? 'archive';

if (empty($ids)) {
    header('location: officerslist.php?error=no_ids');
    exit;
}

$id_array = explode(',', $ids);
$id_array = array_filter($id_array, 'is_numeric');

if (empty($id_array)) {
    header('location: officerslist.php?error=invalid_ids');
    exit;
}

$placeholders = str_repeat('?,', count($id_array) - 1) . '?';
$types = str_repeat('i', count($id_array));

try {
    $conn->begin_transaction();
    
    if ($action === 'archive') {
        // Copy to archive table
        $archive_sql = "INSERT INTO officers_archive (member_id, role_id, term_start, term_end, image)
                       SELECT member_id, role_id, term_start, term_end, image
                       FROM officers
                       WHERE id IN ($placeholders)";
        $archive_stmt = $conn->prepare($archive_sql);
        $archive_stmt->bind_param($types, ...$id_array);
        $archive_stmt->execute();
        $archive_stmt->close();
    }
    
    // Delete from main table
    $delete_sql = "DELETE FROM officers WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param($types, ...$id_array);
    
    if ($stmt->execute()) {
        $affected_count = $stmt->affected_rows;
        $conn->commit();
        header("location: officerslist.php?bulk_archived=$affected_count");
    } else {
        $conn->rollback();
        header('location: officerslist.php?error=operation_failed');
    }
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Bulk officers error: " . $e->getMessage());
    header('location: officerslist.php?error=exception');
}
?>
