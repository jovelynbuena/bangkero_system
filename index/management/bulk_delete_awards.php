<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

$ids = $_GET['ids'] ?? '';
$action = $_GET['action'] ?? 'archive';


if (empty($ids)) {
    header('location: awards.php?error=no_ids');
    exit;
}

$id_array = explode(',', $ids);
$id_array = array_filter($id_array, 'is_numeric');

if (empty($id_array)) {
    header('location: awards.php?error=invalid_ids');
    exit;
}

$placeholders = str_repeat('?,', count($id_array) - 1) . '?';
$types = str_repeat('i', count($id_array));

try {
    $conn->begin_transaction();
    
    if ($action === 'archive') {
        // Copy to archive table
        $archive_sql = "INSERT INTO awards_archive (
                            award_id, award_title, awarding_body, category, 
                            description, year_received, date_received, 
                            award_image, certificate_file, original_created_at
                        )
                        SELECT 
                            award_id, award_title, awarding_body, category, 
                            description, year_received, date_received, 
                            award_image, certificate_file, created_at
                        FROM awards
                        WHERE award_id IN ($placeholders)";
        $archive_stmt = $conn->prepare($archive_sql);
        $archive_stmt->bind_param($types, ...$id_array);
        $archive_stmt->execute();
        $archive_stmt->close();
    } else {
        // Get file names before deleting (only for permanent delete)
        $getFileSql = "SELECT award_image, certificate_file FROM awards WHERE award_id IN ($placeholders)";
        $fileStmt = $conn->prepare($getFileSql);
        $fileStmt->bind_param($types, ...$id_array);
        $fileStmt->execute();
        $fileResult = $fileStmt->get_result();
        
        $files_to_delete = [];
        while ($row = $fileResult->fetch_assoc()) {
            if ($row['award_image']) $files_to_delete[] = $row['award_image'];
            if ($row['certificate_file']) $files_to_delete[] = $row['certificate_file'];
        }
        $fileStmt->close();
    }

    // Delete from main awards table
    $delete_sql = "DELETE FROM awards WHERE award_id IN ($placeholders)";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param($types, ...$id_array);
    
    if ($stmt->execute()) {
        $affected_count = $stmt->affected_rows;
        $conn->commit();
        
        if ($action === 'delete') {
            // Delete files from server
            $uploadDir = '../../uploads/awards/';
            foreach ($files_to_delete as $file) {
                if (file_exists($uploadDir . $file)) {
                    @unlink($uploadDir . $file);
                }
            }
            header("location: awards.php?bulk_deleted=$affected_count");
        } else {
            header("location: awards.php?bulk_archived=$affected_count");
        }
    } else {
        $conn->rollback();
        header('location: awards.php?error=operation_failed');
    }
    
} catch (Exception $e) {
    $conn->rollback();
    header('location: awards.php?error=exception');
}
?>
