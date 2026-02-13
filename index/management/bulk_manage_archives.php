<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}
require_once('../../config/db_connect.php');

$type = $_GET['type'] ?? '';
$ids = $_GET['ids'] ?? '';
$action = $_GET['action'] ?? '';

if (empty($type) || empty($ids) || empty($action)) {
    header("location: archives_members.php?error=invalid_params");
    exit;
}

$id_array = explode(',', $ids);
$id_array = array_filter($id_array, 'is_numeric');
if (empty($id_array)) {
    header("location: archives_members.php?error=invalid_ids");
    exit;
}

$placeholders = str_repeat('?,', count($id_array) - 1) . '?';
$types = str_repeat('i', count($id_array));

$redirect = "archives_members.php";

try {
    $conn->begin_transaction();
    
    switch ($type) {
        case 'members':
            if ($action === 'restore') {
                $sql = "INSERT INTO members (name, dob, gender, phone, email, address, work_type, license_number, boat_name, fishing_area, emergency_name, emergency_phone, agreement, image)
                        SELECT name, dob, gender, phone, email, address, work_type, license_number, boat_name, fishing_area, emergency_name, emergency_phone, agreement, image
                        FROM member_archive WHERE member_id IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$id_array);
                $stmt->execute();
                
                $del_sql = "DELETE FROM member_archive WHERE member_id IN ($placeholders)";
                $del_stmt = $conn->prepare($del_sql);
                $del_stmt->bind_param($types, ...$id_array);
                $del_stmt->execute();
            } elseif ($action === 'delete') {
                $del_sql = "DELETE FROM member_archive WHERE member_id IN ($placeholders)";
                $del_stmt = $conn->prepare($del_sql);
                $del_stmt->bind_param($types, ...$id_array);
                $del_stmt->execute();
            }
            $redirect = "archives_members.php";
            break;
            
        case 'officers':
            if ($action === 'restore') {
                $sql = "INSERT INTO officers (member_id, role_id, term_start, term_end, image)
                        SELECT member_id, role_id, term_start, term_end, image FROM officers_archive WHERE id IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$id_array);
                $stmt->execute();
                
                $del_sql = "DELETE FROM officers_archive WHERE id IN ($placeholders)";
                $del_stmt = $conn->prepare($del_sql);
                $del_stmt->bind_param($types, ...$id_array);
                $del_stmt->execute();
            } elseif ($action === 'delete') {
                $del_sql = "DELETE FROM officers_archive WHERE id IN ($placeholders)";
                $del_stmt = $conn->prepare($del_sql);
                $del_stmt->bind_param($types, ...$id_array);
                $del_stmt->execute();
            }
            $redirect = "archives_officers.php";
            break;

        case 'awards':
            if ($action === 'restore') {
                $sql = "INSERT INTO awards (award_title, awarding_body, category, description, year_received, date_received, award_image, certificate_file, created_at)
                        SELECT award_title, awarding_body, category, description, year_received, date_received, award_image, certificate_file, original_created_at FROM awards_archive WHERE archive_id IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$id_array);
                $stmt->execute();
                
                $del_sql = "DELETE FROM awards_archive WHERE archive_id IN ($placeholders)";
                $del_stmt = $conn->prepare($del_sql);
                $del_stmt->bind_param($types, ...$id_array);
                $del_stmt->execute();
            } elseif ($action === 'delete') {
                $del_sql = "DELETE FROM awards_archive WHERE archive_id IN ($placeholders)";
                $del_stmt = $conn->prepare($del_sql);
                $del_stmt->bind_param($types, ...$id_array);
                $del_stmt->execute();
            }
            $redirect = "archives_awards.php";
            break;

        case 'galleries':
            if ($action === 'restore') {
                $sql = "INSERT INTO galleries (title, category, images, created_at)
                        SELECT title, category, images, original_created_at FROM galleries_archive WHERE archive_id IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$id_array);
                $stmt->execute();
                
                $del_sql = "DELETE FROM galleries_archive WHERE archive_id IN ($placeholders)";
                $del_stmt = $conn->prepare($del_sql);
                $del_stmt->bind_param($types, ...$id_array);
                $del_stmt->execute();
            } elseif ($action === 'delete') {
                $del_sql = "DELETE FROM galleries_archive WHERE archive_id IN ($placeholders)";
                $del_stmt = $conn->prepare($del_sql);
                $del_stmt->bind_param($types, ...$id_array);
                $del_stmt->execute();
            }
            $redirect = "archives_galleries.php";
            break;
            
        case 'contact':
            if ($action === 'restore') {
                $sql = "INSERT INTO contact_messages (name, email, message, status, created_at)
                        SELECT name, email, message, status, created_at FROM contact_messages_archive WHERE archive_id IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$id_array);
                $stmt->execute();
                
                $del_sql = "DELETE FROM contact_messages_archive WHERE archive_id IN ($placeholders)";
                $del_stmt = $conn->prepare($del_sql);
                $del_stmt->bind_param($types, ...$id_array);
                $del_stmt->execute();
            } elseif ($action === 'delete') {
                $del_sql = "DELETE FROM contact_messages_archive WHERE archive_id IN ($placeholders)";
                $del_stmt = $conn->prepare($del_sql);
                $del_stmt->bind_param($types, ...$id_array);
                $del_stmt->execute();
            }
            $redirect = "archives_contact_messages.php";
            break;
            
        case 'roles':
            if ($action === 'restore') {
                $sql = "INSERT INTO officer_roles (role_name, description, created_at)
                        SELECT role_name, description, created_at FROM officer_roles_archive WHERE archive_id IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$id_array);
                $stmt->execute();
                
                $del_sql = "DELETE FROM officer_roles_archive WHERE archive_id IN ($placeholders)";
                $del_stmt = $conn->prepare($del_sql);
                $del_stmt->bind_param($types, ...$id_array);
                $del_stmt->execute();
            } elseif ($action === 'delete') {
                $del_sql = "DELETE FROM officer_roles_archive WHERE archive_id IN ($placeholders)";
                $del_stmt = $conn->prepare($del_sql);
                $del_stmt->bind_param($types, ...$id_array);
                $del_stmt->execute();
            }
            $redirect = "archives_officer_roles.php";
            break;
            
        case 'users':
            if ($action === 'restore') {
                $sql = "INSERT INTO users (username, email, password, role, status, is_admin, created_at)
                        SELECT username, email, password, role, status, is_admin, created_at FROM users_archive WHERE archive_id IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$id_array);
                $stmt->execute();
                
                $del_sql = "DELETE FROM users_archive WHERE archive_id IN ($placeholders)";
                $del_stmt = $conn->prepare($del_sql);
                $del_stmt->bind_param($types, ...$id_array);
                $del_stmt->execute();
            } elseif ($action === 'delete') {
                $del_sql = "DELETE FROM users_archive WHERE archive_id IN ($placeholders)";
                $del_stmt = $conn->prepare($del_sql);
                $del_stmt->bind_param($types, ...$id_array);
                $del_stmt->execute();
            }
            $redirect = "archives_users.php";
            break;
    }
    
    $conn->commit();
    header("location: $redirect?bulk_success=1&action=$action&count=" . count($id_array));
    exit;
} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    header("location: $redirect?error=" . urlencode($e->getMessage()));
    exit;
}
