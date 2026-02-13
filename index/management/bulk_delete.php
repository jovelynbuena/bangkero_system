<?php
/**
 * BULK ARCHIVE/DELETE MEMBERS
 * Handles archiving and deletion of multiple members at once
 */
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

// Get member IDs from query string
$ids = $_GET['ids'] ?? '';
$action = $_GET['action'] ?? 'archive'; // default to archive for safety


if (empty($ids)) {
    header('location: memberlist.php?error=no_ids');
    exit;
}

// Convert to array
$id_array = explode(',', $ids);
$id_array = array_filter($id_array, 'is_numeric');

if (empty($id_array)) {
    header('location: memberlist.php?error=invalid_ids');
    exit;
}

// Prepare statement placeholders
$placeholders = str_repeat('?,', count($id_array) - 1) . '?';
$types = str_repeat('i', count($id_array));

try {
    $conn->begin_transaction();
    
    // 1. If action is archive, copy to archive table first
    if ($action === 'archive') {
        $archive_sql = "INSERT INTO member_archive (
                            member_id, name, dob, gender, phone, email, address, 
                            work_type, license_number, boat_name, fishing_area, 
                            emergency_name, emergency_phone, agreement, image, archived_at
                        )
                        SELECT id, name, dob, gender, phone, email, address, 
                               work_type, license_number, boat_name, fishing_area, 
                               emergency_name, emergency_phone, agreement, image, NOW()
                        FROM members
                        WHERE id IN ($placeholders)";
        $archive_stmt = $conn->prepare($archive_sql);
        $archive_stmt->bind_param($types, ...$id_array);
        $archive_stmt->execute();
        $archive_stmt->close();
    }

    
    // 2. Delete from main members table
    $delete_sql = "DELETE FROM members WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param($types, ...$id_array);
    
    if ($stmt->execute()) {
        $affected_count = $stmt->affected_rows;
        $conn->commit();
        
        $redirect_param = ($action === 'archive') ? "bulk_archived=$affected_count" : "bulk_deleted=$affected_count";
        header("location: memberlist.php?$redirect_param");
    } else {
        $conn->rollback();
        header('location: memberlist.php?error=operation_failed');
    }
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Bulk error: " . $e->getMessage());
    header('location: memberlist.php?error=exception');
}
?>
