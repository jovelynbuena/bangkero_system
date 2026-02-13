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
    header('location: galleries.php?error=no_ids');
    exit;
}

$id_array = explode(',', $ids);
$id_array = array_filter($id_array, 'is_numeric');

if (empty($id_array)) {
    header('location: galleries.php?error=invalid_ids');
    exit;
}

$placeholders = str_repeat('?,', count($id_array) - 1) . '?';
$types = str_repeat('i', count($id_array));

try {
    $conn->begin_transaction();
    
    if ($action === 'archive') {
        // Copy to archive table
        $archive_sql = "INSERT INTO galleries_archive (gallery_id, title, category, images, original_created_at)
                        SELECT id, title, category, images, created_at FROM galleries WHERE id IN ($placeholders)";
        $archive_stmt = $conn->prepare($archive_sql);
        $archive_stmt->bind_param($types, ...$id_array);
        $archive_stmt->execute();
        $archive_stmt->close();
    }

    // Get image names if deleting permanently (for physical file cleanup)
    $images_to_delete = [];
    if ($action === 'delete') {
        $getFileSql = "SELECT images FROM galleries WHERE id IN ($placeholders)";
        $fileStmt = $conn->prepare($getFileSql);
        $fileStmt->bind_param($types, ...$id_array);
        $fileStmt->execute();
        $fileResult = $fileStmt->get_result();
        
        while ($row = $fileResult->fetch_assoc()) {
            if ($row['images']) {
                $imgs = explode(',', $row['images']);
                foreach ($imgs as $img) {
                    if (!empty(trim($img))) {
                        $images_to_delete[] = trim($img);
                    }
                }
            }
        }
        $fileStmt->close();
    }

    // Delete from main table
    $delete_sql = "DELETE FROM galleries WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param($types, ...$id_array);
    
    if ($stmt->execute()) {
        $affected_count = $stmt->affected_rows;
        $conn->commit();
        
        if ($action === 'delete') {
            // Delete files from server
            $uploadDir = '../../uploads/gallery/';
            foreach ($images_to_delete as $file) {
                $path = $uploadDir . $file;
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
            header("location: archives_galleries.php?deleted=$affected_count");
        } else {
            header("location: galleries.php?success=Archived $affected_count gallery(ies)");
        }
    } else {
        $conn->rollback();
        header('location: galleries.php?error=operation_failed');
    }
    
} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    header('location: galleries.php?error=exception');
}
?>
