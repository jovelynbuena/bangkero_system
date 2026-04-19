<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Helper function to check if column exists
function tableHasColumn(mysqli $conn, string $table, string $column): bool {
    $tableEsc = $conn->real_escape_string($table);
    $colEsc = $conn->real_escape_string($column);
    $res = $conn->query("SHOW COLUMNS FROM `{$tableEsc}` LIKE '{$colEsc}'");
    return ($res && $res->num_rows > 0);
}

$alertType = $alertMsg = "";
$memberName = $_SESSION['member_name'] ?? 'Admin';

// Helper function to retain form values after error
function old($field, $default = '') {
    return isset($_POST[$field]) ? htmlspecialchars($_POST[$field]) : $default;
}

// Helper function to check if option should be selected
function selected($field, $value) {
    return (isset($_POST[$field]) && $_POST[$field] === $value) ? 'selected' : '';
}

// Helper function to check if checkbox should be checked
function checked($field) {
    return isset($_POST[$field]) ? 'checked' : '';
}

/* --------------------------
   ✅ ADD MEMBER HANDLER (with restrictions)
-------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
    // Personal Information
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_initial = trim($_POST['middle_initial'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $name = trim("$first_name $middle_initial $last_name");
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $civil_status = $_POST['civil_status'] ?? '';
    
    // Contact Information
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Address (structured)
    $street = trim($_POST['street'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $municipality = trim($_POST['municipality'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $region = $_POST['region'] ?? '';
    $zip_code = trim($_POST['zip_code'] ?? '');
    $address = trim("$street, $barangay, $municipality, $province, $region $zip_code");
    
    // Work Information
    $work_type = $_POST['work_type'] ?? '';
    $license_number = trim($_POST['license_number'] ?? '');
    $municipal_permit_no = trim($_POST['municipal_permit_no'] ?? '');
    $bfar_fisherfolk_id = trim($_POST['bfar_fisherfolk_id'] ?? '');
    $boat_name = trim($_POST['boat_name'] ?? '');
    $boat_registration = trim($_POST['boat_registration'] ?? '');
    
    // Emergency Contact
    $emergency_name = trim($_POST['emergency_name'] ?? '');
    $emergency_phone = trim($_POST['emergency_phone'] ?? '');
    
    $agreement = isset($_POST['agreement']) ? 1 : 0;

    // ✅ RESTRICTION 1: Validate required fields
    $required_fields = [
        'First Name' => $first_name,
        'Last Name' => $last_name,
        'Date of Birth' => $dob,
        'Gender' => $gender,
        'Civil Status' => $civil_status,
        'Phone Number' => $phone,
        'Email Address' => $email,
        'Street/House No.' => $street,
        'Barangay' => $barangay,
        'Municipality/City' => $municipality,
        'Province' => $province,
        'Region' => $region,
        'Zip Code' => $zip_code,
        'Type of Work' => $work_type,
        'Emergency Contact Name' => $emergency_name,
        'Emergency Contact Phone' => $emergency_phone
    ];
    
    $missing_fields = [];
    foreach ($required_fields as $field_name => $value) {
        if (empty($value)) {
            $missing_fields[] = $field_name;
        }
    }
    
    if (!empty($missing_fields)) {
        $alertType = "error";
        $alertMsg = "Please fill in all required fields: " . implode(", ", $missing_fields);
    }
    // ✅ RESTRICTION 2: Validate email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $alertType = "error";
        $alertMsg = "Please enter a valid email address!";
    }
    // ✅ RESTRICTION 3: Validate age (must be at least 18 years old)
    elseif (!empty($dob)) {
        $dobDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($dobDate)->y;
        if ($age < 18) {
            $alertType = "error";
            $alertMsg = "Member must be at least 18 years old!";
        }
    }
    // ✅ RESTRICTION 4: Validate phone format (Philippine mobile)
    elseif (!preg_match('/^(09|\+639)\d{9}$/', str_replace(['-', ' '], '', $phone))) {
        $alertType = "error";
        $alertMsg = "Please enter a valid Philippine mobile number (e.g., 09XX-XXX-XXXX)!";
    }
    // ✅ RESTRICTION 5: Remove - License is now optional (Municipal Permit or BFAR ID can be provided instead)
    
    if (empty($alertMsg)) {
        // ✅ RESTRICTION 6: Check for duplicate email
        $checkEmail = $conn->prepare("SELECT id FROM members WHERE LOWER(email) = LOWER(?)");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();
        
        if ($checkEmail->num_rows > 0) {
            $alertType = "error";
            $alertMsg = "Email already exists! Please use a different email address.";
        }
        $checkEmail->close();

        // ✅ RESTRICTION 7: Check for duplicate phone number
        if (empty($alertMsg)) {
            $phone_clean = str_replace(['-', ' '], '', $phone);
            $checkPhone = $conn->prepare("SELECT id FROM members WHERE REPLACE(REPLACE(phone, '-', ''), ' ', '') = ?");
            $checkPhone->bind_param("s", $phone_clean);
            $checkPhone->execute();
            $checkPhone->store_result();
            
            if ($checkPhone->num_rows > 0) {
                $alertType = "error";
                $alertMsg = "Phone number already exists! Please use a different phone number.";
            }
            $checkPhone->close();
        }
    }

    // Handle image upload
    $image_name = 'default_member.png';
    if (empty($alertMsg) && !empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imgDir = '../../uploads/members/';
        if (!is_dir($imgDir)) mkdir($imgDir, 0777, true);

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];

        if (!in_array($file_type, $allowed_types)) {
            $alertType = "error";
            $alertMsg = "Invalid image format! Only JPG, PNG, and GIF are allowed.";
        } elseif ($file_size > 2 * 1024 * 1024) {
            $alertType = "error";
            $alertMsg = "Image size must be less than 2MB!";
        } else {
            $image_name = uniqid('member_', true) . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '', basename($_FILES['image']['name']));
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $imgDir . $image_name)) {
                $alertType = "error";
                $alertMsg = "Error uploading image. Please try again.";
            }
        }
    }

    // Insert into database
    if (empty($alertMsg)) {
        // Check if new columns exist
        $hasNewColumns = tableHasColumn($conn, 'members', 'street');
        $hasPermitColumns = tableHasColumn($conn, 'members', 'municipal_permit_no');
        $hasBoatRegColumn = tableHasColumn($conn, 'members', 'boat_registration');
        
        if ($hasNewColumns && $hasPermitColumns && $hasBoatRegColumn) {
            $stmt = $conn->prepare("
                INSERT INTO members (name, dob, gender, civil_status, phone, email, 
                    street, barangay, municipality, province, region, zip_code, address,
                    work_type, license_number, municipal_permit_no, bfar_fisherfolk_id, boat_name, boat_registration,
                    emergency_name, emergency_phone, agreement, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssssssssssssssssssssis", 
                $name, $dob, $gender, $civil_status, $phone, $email,
                $street, $barangay, $municipality, $province, $region, $zip_code, $address,
                $work_type, $license_number, $municipal_permit_no, $bfar_fisherfolk_id, $boat_name, $boat_registration,
                $emergency_name, $emergency_phone, $agreement, $image_name
            );
        } elseif ($hasNewColumns && $hasPermitColumns) {
            $stmt = $conn->prepare("
                INSERT INTO members (name, dob, gender, civil_status, phone, email, 
                    street, barangay, municipality, province, region, zip_code, address,
                    work_type, license_number, municipal_permit_no, bfar_fisherfolk_id, boat_name,
                    emergency_name, emergency_phone, agreement, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssssssssssssssssssssis", 
                $name, $dob, $gender, $civil_status, $phone, $email,
                $street, $barangay, $municipality, $province, $region, $zip_code, $address,
                $work_type, $license_number, $municipal_permit_no, $bfar_fisherfolk_id, $boat_name,
                $emergency_name, $emergency_phone, $agreement, $image_name
            );
        } elseif ($hasNewColumns) {
            $stmt = $conn->prepare("
                INSERT INTO members (name, dob, gender, civil_status, phone, email, 
                    street, barangay, municipality, province, region, zip_code, address,
                    work_type, license_number, boat_name,
                    emergency_name, emergency_phone, agreement, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssssssssssssssssssis", 
                $name, $dob, $gender, $civil_status, $phone, $email,
                $street, $barangay, $municipality, $province, $region, $zip_code, $address,
                $work_type, $license_number, $boat_name,
                $emergency_name, $emergency_phone, $agreement, $image_name
            );
        } else {
            // Fallback to old query for backward compatibility
            $stmt = $conn->prepare("
                INSERT INTO members (name, dob, gender, phone, email, address, work_type, license_number, boat_name, fishing_area, emergency_name, emergency_phone, agreement, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssssssssssssis", $name, $dob, $gender, $phone, $email, $address, $work_type, $license_number, $boat_name, $fishing_area, $emergency_name, $emergency_phone, $agreement, $image_name);
        }

        if ($stmt->execute()) {
            $alertType = "success";
            $alertMsg = "Member added successfully!";
        } else {
            $alertType = "error";
            $alertMsg = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
}

/* --------------------------
   ✏️ EDIT MEMBER HANDLER (with restrictions)
-------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_member'])) {
    $member_id = intval($_POST['member_id'] ?? 0);
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_initial = trim($_POST['middle_initial'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $name = trim("$first_name $middle_initial $last_name");
    
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $work_type = $_POST['work_type'] ?? '';
    $license_number = trim($_POST['license_number'] ?? '');
    $municipal_permit_no = trim($_POST['municipal_permit_no'] ?? '');
    $bfar_fisherfolk_id = trim($_POST['bfar_fisherfolk_id'] ?? '');
    $boat_name = trim($_POST['boat_name'] ?? '');
    $boat_registration = trim($_POST['boat_registration'] ?? '');
    $fishing_area = trim($_POST['fishing_area'] ?? '');
    $emergency_name = trim($_POST['emergency_name'] ?? '');
    $emergency_phone = trim($_POST['emergency_phone'] ?? '');
    $agreement = isset($_POST['agreement']) ? 1 : 0;

    // ✅ RESTRICTION 1: Validate required fields
    if (!$member_id || empty($first_name) || empty($last_name) || empty($dob) || empty($phone) || empty($email) || empty($address)) {
        $alertType = "error";
        $alertMsg = "Please fill in all required fields!";
    }
    // ✅ RESTRICTION 2: Validate age
    elseif (!empty($dob)) {
        $dobDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($dobDate)->y;
        if ($age < 18) {
            $alertType = "error";
            $alertMsg = "Member must be at least 18 years old!";
        }
    }

    if (empty($alertMsg)) {
        // Get current member data
        $getCurrent = $conn->prepare("SELECT image, email, phone FROM members WHERE id = ?");
        $getCurrent->bind_param("i", $member_id);
        $getCurrent->execute();
        $getCurrent->bind_result($current_image, $current_email, $current_phone);
        $getCurrent->fetch();
        $getCurrent->close();

        // ✅ RESTRICTION 3: Check for duplicate email (excluding current member)
        if (strtolower($email) !== strtolower($current_email ?? '')) {
            $checkEmail = $conn->prepare("SELECT id FROM members WHERE LOWER(email) = LOWER(?) AND id != ?");
            $checkEmail->bind_param("si", $email, $member_id);
            $checkEmail->execute();
            $checkEmail->store_result();
            
            if ($checkEmail->num_rows > 0) {
                $alertType = "error";
                $alertMsg = "Email already exists! Please use a different email address.";
            }
            $checkEmail->close();
        }

        // ✅ RESTRICTION 4: Check for duplicate phone (excluding current member)
        if (empty($alertMsg) && $phone !== $current_phone) {
            $checkPhone = $conn->prepare("SELECT id FROM members WHERE phone = ? AND id != ?");
            $checkPhone->bind_param("si", $phone, $member_id);
            $checkPhone->execute();
            $checkPhone->store_result();
            
            if ($checkPhone->num_rows > 0) {
                $alertType = "error";
                $alertMsg = "Phone number already exists! Please use a different phone number.";
            }
            $checkPhone->close();
        }

        $new_image = $current_image;

        // Handle image upload
        if (empty($alertMsg) && !empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imgDir = '../../uploads/members/';
            if (!is_dir($imgDir)) mkdir($imgDir, 0777, true);

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image']['type'];
            $file_size = $_FILES['image']['size'];

            if (!in_array($file_type, $allowed_types)) {
                $alertType = "error";
                $alertMsg = "Invalid image format! Only JPG, PNG, and GIF are allowed.";
            } elseif ($file_size > 2 * 1024 * 1024) {
                $alertType = "error";
                $alertMsg = "Image size must be less than 2MB!";
            } else {
                $new_image = uniqid('member_', true) . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '', basename($_FILES['image']['name']));
                if (move_uploaded_file($_FILES['image']['tmp_name'], $imgDir . $new_image)) {
                    // Delete old image
                    if ($current_image && $current_image !== 'default_member.png' && file_exists($imgDir . $current_image)) {
                        @unlink($imgDir . $current_image);
                    }
                } else {
                    $alertType = "error";
                    $alertMsg = "Error uploading image. Please try again.";
                    $new_image = $current_image;
                }
            }
        }

        // Update database
        if (empty($alertMsg)) {
            $hasPermitColumns = tableHasColumn($conn, 'members', 'municipal_permit_no');
            $hasBoatRegColumn = tableHasColumn($conn, 'members', 'boat_registration');
            
            if ($hasPermitColumns && $hasBoatRegColumn) {
                $stmt = $conn->prepare("
                    UPDATE members SET 
                        name=?, dob=?, gender=?, phone=?, email=?, address=?, 
                        work_type=?, license_number=?, municipal_permit_no=?, bfar_fisherfolk_id=?, boat_name=?, boat_registration=?, fishing_area=?, 
                        emergency_name=?, emergency_phone=?, agreement=?, image=?
                    WHERE id=?
                ");
                $stmt->bind_param("sssssssssssssssisi", $name, $dob, $gender, $phone, $email, $address, $work_type, $license_number, $municipal_permit_no, $bfar_fisherfolk_id, $boat_name, $boat_registration, $fishing_area, $emergency_name, $emergency_phone, $agreement, $new_image, $member_id);
            } elseif ($hasPermitColumns) {
                $stmt = $conn->prepare("
                    UPDATE members SET 
                        name=?, dob=?, gender=?, phone=?, email=?, address=?, 
                        work_type=?, license_number=?, municipal_permit_no=?, bfar_fisherfolk_id=?, boat_name=?, fishing_area=?, 
                        emergency_name=?, emergency_phone=?, agreement=?, image=?
                    WHERE id=?
                ");
                $stmt->bind_param("ssssssssssssssisi", $name, $dob, $gender, $phone, $email, $address, $work_type, $license_number, $municipal_permit_no, $bfar_fisherfolk_id, $boat_name, $fishing_area, $emergency_name, $emergency_phone, $agreement, $new_image, $member_id);
            } else {
                $stmt = $conn->prepare("
                    UPDATE members SET 
                        name=?, dob=?, gender=?, phone=?, email=?, address=?, 
                        work_type=?, license_number=?, boat_name=?, fishing_area=?, 
                        emergency_name=?, emergency_phone=?, agreement=?, image=?
                    WHERE id=?
                ");
                $stmt->bind_param("ssssssssssssisi", $name, $dob, $gender, $phone, $email, $address, $work_type, $license_number, $boat_name, $fishing_area, $emergency_name, $emergency_phone, $agreement, $new_image, $member_id);
            }

            if ($stmt->execute()) {
                $alertType = "success";
                $alertMsg = "Member updated successfully!";
            } else {
                $alertType = "error";
                $alertMsg = "Database error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

/* --------------------------
   🗂 ARCHIVE HANDLER
-------------------------- */
if (isset($_GET['archive'])) {
    $id = intval($_GET['archive']);
    if ($id > 0) {
        // First check if member exists
        $checkStmt = $conn->prepare("SELECT id FROM members WHERE id = ?");
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $checkStmt->close();
            
            // Check if member_archive table exists and has the right structure
            $tableCheck = $conn->query("SHOW TABLES LIKE 'member_archive'");
            
            if ($tableCheck && $tableCheck->num_rows > 0) {
                // Archive exists, insert data
                try {
                    $stmt = $conn->prepare("
                        INSERT INTO member_archive (
                            member_id, name, dob, gender, phone, email, address, 
                            work_type, license_number, boat_name, fishing_area, 
                            emergency_name, emergency_phone, agreement, image, archived_at
                        )
                        SELECT id, name, dob, gender, phone, email, address, 
                               work_type, license_number, boat_name, fishing_area, 
                               emergency_name, emergency_phone, agreement, image, NOW()
                        FROM members
                        WHERE id = ?
                    ");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $stmt->close();
                } catch (Exception $e) {
                    // If insert fails (e.g., duplicate), just continue to remove
                }
            }
            
            // Remove from members table after archiving
            $deleteStmt = $conn->prepare("DELETE FROM members WHERE id = ?");

            $deleteStmt->bind_param("i", $id);
            
            if ($deleteStmt->execute()) {
                $deleteStmt->close();
                header("Location: memberlist.php?archived=1");
                exit();
            } else {
                $deleteStmt->close();
                header("Location: memberlist.php?error=1");
                exit();
            }
        } else {
            $checkStmt->close();
            header("Location: memberlist.php?error=1");
            exit();
        }
    }
}

/* --------------------------
   📋 FETCH STATISTICS
-------------------------- */
$stats_members = $conn->query("SELECT COUNT(*) AS total FROM members");
$total_members = $stats_members->fetch_assoc()['total'] ?? 0;

// Officers must always be members; count distinct officer members only
$stats_officers = $conn->query("SELECT COUNT(DISTINCT o.member_id) AS total
                                FROM officers o
                                INNER JOIN members m ON m.id = o.member_id");
$total_officers = $stats_officers->fetch_assoc()['total'] ?? 0;

/* --------------------------
   📋 FETCH MEMBERS WITH FILTERING
-------------------------- */
$search = trim($_GET['search'] ?? '');
$role = $_GET['role'] ?? '';
$sort = $_GET['sort'] ?? 'name_asc';

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

// Role filter (only Member, Officer handled by subquery)
if ($role === 'officer') {
    $where_clauses[] = "id IN (SELECT member_id FROM officers)";
} elseif ($role === 'member') {
    $where_clauses[] = "id NOT IN (SELECT member_id FROM officers)";
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

// Date range filter
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
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

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM members {$where_sql}";
if (!empty($params)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_records = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_records = $conn->query($count_sql)->fetch_assoc()['total'];
}
$total_pages = ceil($total_records / $per_page);

// Fetch members with pagination
$sql = "SELECT * FROM members {$where_sql} {$order_sql} LIMIT {$per_page} OFFSET {$offset}";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$members_count = $total_records;

// 🔄 Prepare attendance summary statement (counts and last attendance for each member)
$attendanceStmt = $conn->prepare("SELECT COUNT(*) AS total_present, MAX(attendance_date) AS last_attendance FROM member_attendance WHERE member_id = ? AND status = 'present'");
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Member List | Bangkero & Fishermen Association</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<link rel="stylesheet" href="../../css/admin-theme.css">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { 
        font-family: 'Inter', sans-serif; 
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
    }
    .main-content { 
        margin-left: 250px; 
        padding: 32px; 
        min-height: 100vh; 
        max-width: 1320px;
        margin-right: auto;
    }


    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
        padding: 28px 28px 24px;
        border-radius: 18px;
        color: white;
        margin-bottom: 24px;
        box-shadow: 0 14px 32px rgba(88, 101, 242, 0.32);
    }


    .page-header h2 {
        font-size: 32px;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .page-header .badge {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
    }

    /* Statistics Dashboard */
    .statistics-dashboard {
        margin-bottom: 24px;
    }

    .statistics-dashboard .row {
        justify-content: center;
        gap: 20px;
    }
    .statistics-dashboard .stat-col {
        flex: 0 0 auto;
        max-width: 360px;
    }
    .stat-card {
        background: white;
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        display: flex;
        align-items: center;
        gap: 16px;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.16);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .stat-primary .stat-icon {
        background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
        color: white;
    }
    .stat-success .stat-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    .stat-info .stat-icon {
        background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
        color: white;
    }
    .stat-warning .stat-icon {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    .stat-content h3 {
        font-size: 34px;
        font-weight: 800;
        margin: 0;
        color: #0f172a;
    }
    .stat-content p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 14px;
        font-weight: 500;
        opacity: 0.9;
    }


    /* Filter Section */
    .filter-section {
        background: rgba(255, 255, 255, 0.98);
        padding: 16px 20px;
        border-radius: 14px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
        border: 1px solid #e2e8f0;
        margin-bottom: 20px;
    }

    .filter-section .row.g-3 > [class^="col-"],
    .filter-section .row.g-3 > [class*=" col-"] {
        margin-bottom: 8px;
    }
    .filter-section #filterForm {
        row-gap: 10px;
    }


    .form-label-sm {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 4px;
        display: block;
    }
    .filter-select,
    .filter-section .search-box input {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 9px 14px 9px 42px; /* extra left space for icon */
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
        background-color: white;
    }


    .filter-select:focus,
    .filter-section .search-box input:focus {
        border-color: #2E86AB;
        box-shadow: 0 0 0 3px rgba(46, 134, 171, 0.18);
        outline: none;
    }
    .filter-info {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        font-weight: 500;
        font-size: 14px;
        opacity: 0.9;
    }


    /* Bulk Selection & Actions */
    .member-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    #selectAll {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    .member-row {
        transition: all 0.2s ease;
    }
    .member-row:hover {
        background-color: #f8fafc;
    }
    .member-row.selected {
        background-color: #eff6ff;
    }

    /* Export Dropdown */
    .btn-export {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white !important;
        padding: 10px 20px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-export:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        color: white !important;
    }
    .dropdown {
        position: relative;
    }
    .dropdown-menu {
        display: none;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        border: none;
        padding: 8px;
        position: absolute;
        top: 100%;
        right: 0;
        min-width: 200px;
        z-index: 1000;
        background: white;
    }
    .dropdown.show .dropdown-menu {
        display: block;
    }
    .dropdown-item {
        border-radius: 8px;
        padding: 10px 16px;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    .dropdown-item:hover {
        background-color: #f8fafc;
        color: #2E86AB;
    }
    .dropdown-item i {
        width: 20px;
    }

    /* Action Bar / Table Toolbar */
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0;
        flex-wrap: wrap;
        gap: 16px;
    }
    .table-toolbar {
        padding-bottom: 8px;
        margin-bottom: 4px;
        border-bottom: 1px solid #e5e7eb;
    }



    #bulkActionsContainer {
        display: none !important;
        opacity: 0;
    }

    #bulkActionsContainer.bulk-visible {
        opacity: 1;
    }

    /* Add Member Button */
    .btn-add-member {
        background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
        color: white;
        padding: 14px 28px;
        border-radius: 12px;
        border: none;
        font-weight: 600;
        font-size: 16px;
        box-shadow: 0 6px 20px rgba(46, 134, 171, 0.40);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .btn-add-member:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
        background: linear-gradient(135deg, #1B4F72 0%, #2E86AB 100%);
    }

    /* Export Buttons */
    .btn-export {
        padding: 10px 20px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-csv {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    .btn-csv:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    }
    .btn-pdf {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    .btn-pdf:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
    }

    /* Search Box */
    .search-box {
        position: relative;
        width: 100%;
        max-width: 100%;
    }
    .search-box input {
        width: 100%;
        padding: 12px 16px 12px 45px;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    .search-box input:focus {
        outline: none;
        border-color: #2E86AB;
        box-shadow: 0 0 0 4px rgba(46, 134, 171, 0.10);
    }
    .search-box i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 18px;
    }

    /* Table Container */
    .table-container {
        background: rgba(255, 255, 255, 0.99);
        border-radius: 16px;
        padding: 16px 20px;
        box-shadow: 0 10px 36px rgba(15, 23, 42, 0.10);
        border: 1px solid #e2e8f0;
        overflow: auto;
    }



    .table {
        margin-bottom: 0;
    }
    .table thead th {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
        padding: 14px 12px;
        border: none;
        vertical-align: middle;
        position: sticky;
        top: 0;
        z-index: 2;
    }


    .table tbody td {
        padding: 12px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    .table tbody tr {
        transition: background-color 0.25s ease, transform 0.2s ease;
        cursor: default;
    }
    .table tbody tr:hover {
        background-color: #f3f4ff;
        transform: translateX(2px);
        cursor: pointer;
    }


    /* Action Buttons */
    .btn-sm {
        padding: 8px 12px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        transition: background-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
        border: none;
        margin-right: 6px;
    }
    .btn-sm:focus-visible,
    .btn-add-member:focus-visible,
    .btn-export:focus-visible,
    .btn-submit:focus-visible {
        outline: 2px solid #1B4F72;
        outline-offset: 2px;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.35);
    }

    .btn-view {
        background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
        color: white;
    }
    .btn-view:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        color: white;
    }
    .btn-edit {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        color: white;
    }
    .btn-archive {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    .btn-archive:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        color: white;
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }
    .modal-header {
        background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
        color: white;
        border-radius: 20px 20px 0 0;
        padding: 24px;
        border: none;
    }
    .modal-header .modal-title {
        font-weight: 700;
        font-size: 24px;
    }
    .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }
    .modal-body {
        padding: 32px;
        max-height: 70vh;
        overflow-y: auto;
    }
    .modal-footer {
        padding: 24px;
        border-top: 1px solid #f0f0f0;
    }

    /* Form Controls */
    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        font-size: 14px;
    }
    .form-label .text-danger {
        color: #ef4444;
    }
    .form-select, .form-control {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 12px 16px;
        transition: all 0.3s ease;
        font-size: 14px;
    }
    .form-select:focus, .form-control:focus {
        border-color: #2E86AB;
        box-shadow: 0 0 0 4px rgba(46, 134, 171, 0.10);
    }
    .form-check-input:checked {
        background-color: #2E86AB;
        border-color: #2E86AB;
    }

    /* Submit Button */
    .btn-submit {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 12px 32px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        font-size: 16px;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        transition: all 0.3s ease;
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.5);
    }

    /* Form Section Styling */
    .form-section {
        margin-bottom: 24px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e2e8f0;
    }
    .form-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 16px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 2px solid #2E86AB;
    }
    .section-header i {
        font-size: 20px;
        color: #2E86AB;
    }

    /* Form Control Consistency */
    .form-control, .form-select {
        height: 42px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    .form-control:focus, .form-select:focus {
        border-color: #2E86AB;
        box-shadow: 0 0 0 3px rgba(46, 134, 171, 0.15);
    }
    textarea.form-control {
        height: auto;
    }

    /* Image Preview */
    .img-preview {
        width: 120px;
        height: 120px;
        border-radius: 12px;
        object-fit: cover;
        margin-top: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #94a3b8;
    }
    .empty-state i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    /* Pagination */
    .pagination {
        gap: 4px;
    }
    .pagination-info {
        font-size: 14px;
        font-weight: 500;
    }
    .page-link {
        border: 2px solid #e0e0e0;
        color: #2E86AB;
        border-radius: 8px;
        margin: 0 2px;
        font-weight: 600;
        transition: all 0.3s ease;
        padding: 8px 12px;
    }
    .page-link:hover {
        background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
        color: white;
        border-color: #2E86AB;
        transform: translateY(-2px);
    }
    .page-item.active .page-link {
        background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
        border-color: #2E86AB;
        color: white;
        box-shadow: 0 4px 12px rgba(46, 134, 171, 0.30);
    }
    .page-item.disabled .page-link {
        border-color: #f0f0f0;
        color: #cbd5e1;
        background: #f8fafc;
    }

    @media (max-width: 991.98px) { 
        .main-content { 
            margin-left: 0; 
            padding: 16px; 
        }
        .page-header {
            padding: 24px;
        }
        .page-header h2 {
            font-size: 24px;
            flex-direction: column;
            align-items: flex-start;
        }
        .action-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }
        .search-box {
            width: 100%;
        }
        .stat-card {
            flex-direction: column;
            text-align: center;
        }
        .filter-section {
            padding: 16px 18px;
        }
        #bulkActionsContainer {
            width: 100%;
            justify-content: space-between !important;
        }
        .pagination {
            flex-wrap: wrap;
            gap: 4px;
        }
        .pagination-info {
            font-size: 12px;
            margin-bottom: 10px;
        }
    }

    @media (max-width: 575.98px) {
        .filter-section .row.g-3 > [class^="col-"],
        .filter-section .row.g-3 > [class*=" col-"] {
            margin-bottom: 10px;
        }
        .btn-add-member {
            width: 100%;
            justify-content: center;
        }
        .action-bar > .d-flex {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
        }
        .table-container {
            padding: 16px;
        }
    }

</style>
</head>
<body>
<?php include('../navbar.php'); ?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2>
                    <i class="bi bi-people-fill"></i>
                    Members Management
                    <span class="badge"><?php echo $members_count; ?> Members</span>
                    <span class="badge ms-2"><?php echo $total_officers; ?> Officers</span>
                </h2>

                <p class="mb-0 mt-2" style="opacity: 0.9;">Manage association members and their information</p>
            </div>
            <button class="btn-add-member" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                <i class="bi bi-person-plus-fill"></i>
                Add New Member
            </button>
        </div>
    </div>



    <!-- Filter Section (manual toggle) -->
    <div class="filter-section mb-2">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-funnel text-muted"></i>
                <span class="fw-semibold small text-muted">Filters</span>
            </div>
            <button type="button" class="btn btn-link btn-sm text-muted p-0 d-inline-flex align-items-center" id="filterToggleBtn">
                <span class="me-1" id="filterPanelLabel">Hide filters</span>
                <i class="bi bi-chevron-up small" id="filterPanelIcon"></i>
            </button>
        </div>

        <div id="filterPanelBody" class="filter-panel-body">

            <form method="GET" id="filterForm">
                <div class="row g-3 align-items-end">
                    <!-- Search -->
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label-sm">Search</label>
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" name="search" id="searchInput" placeholder="Name, email, phone, ID..." autocomplete="off" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    
                    <!-- Role Filter -->
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label-sm">Role</label>
                        <select name="role" id="roleFilter" class="form-select filter-select">
                            <option value="">All Roles</option>
                            <option value="officer" <?php echo $role === 'officer' ? 'selected' : ''; ?>>Officer</option>
                            <option value="member" <?php echo $role === 'member' ? 'selected' : ''; ?>>Member</option>
                        </select>
                    </div>
                    
                    <!-- Date From -->
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label-sm">Date From</label>
                        <input type="date" name="date_from" id="dateFrom" class="form-control filter-select" value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">
                    </div>
                    
                    <!-- Date To -->
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label-sm">Date To</label>
                        <input type="date" name="date_to" id="dateTo" class="form-control filter-select" value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>">
                    </div>
                    
                    <!-- Sort -->
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label-sm">Sort</label>
                        <select name="sort" id="sortFilter" class="form-select filter-select">
                            <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>A-Z</option>
                            <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Z-A</option>
                            <option value="date_new" <?php echo $sort === 'date_new' ? 'selected' : ''; ?>>Newest</option>
                            <option value="date_old" <?php echo $sort === 'date_old' ? 'selected' : ''; ?>>Oldest</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <!-- Attendance Status Filter (client-side) -->
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label-sm">Attendance Status</label>
                        <select id="attendanceStatusFilter" class="form-select filter-select">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="occasional">Occasional</option>
                            <option value="inactive">Inactive</option>
                            <option value="inactive_old">Inactive (no recent attendance)</option>
                        </select>
                    </div>
                    <!-- Action Buttons -->
                    <div class="col-md-6 d-flex gap-2 align-items-end justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel"></i> Apply Filters
                        </button>
                        <a href="memberlist.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset All
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>





    <!-- Members Table & Toolbar -->
    <div class="table-container">
        <div class="action-bar table-toolbar">
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <!-- Bulk Actions (Hidden by default, shown when items selected) -->
                <div id="bulkActionsContainer" style="display:none;" class="d-flex gap-2">
                    <span id="selectedCount" class="badge bg-primary" style="font-size: 14px; padding: 10px 16px;"></span>
                    <button id="btnBulkArchive" class="btn btn-warning btn-sm text-white">
                        <i class="bi bi-archive"></i> Archive Selected
                    </button>
                    <button id="btnBulkExport" class="btn btn-success btn-sm">
                        <i class="bi bi-download"></i> Export Selected
                    </button>
                    <button id="btnDeselectAll" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i> Clear
                    </button>
                </div>
                
                <!-- Export Dropdown -->
                <div class="dropdown">
                    <button class="btn-export dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download me-1"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown" style="z-index: 1050;">
                        <li><a class="dropdown-item" href="#" onclick="exportData('csv'); return false;">
                            <i class="bi bi-filetype-csv me-2"></i>CSV Format
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportData('pdf'); return false;">
                            <i class="bi bi-file-earmark-pdf me-2"></i>PDF Document
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportData('excel'); return false;">
                            <i class="bi bi-file-earmark-excel me-2"></i>Excel Spreadsheet
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="exportData('print'); return false;">
                            <i class="bi bi-printer me-2"></i>Print Preview
                        </a></li>
                    </ul>
                </div>
            </div>
            
            <div class="filter-info">
                <span id="filterInfoText">
                    <?php 
                    if (!empty($search) || !empty($role)) {
                        $active_filters = [];
                        if (!empty($search)) $active_filters[] = "Search: \"" . htmlspecialchars($search) . "\"";
                        if (!empty($role)) $active_filters[] = "Role: " . ucfirst($role);
                        echo "Filtered: " . implode(" | ", $active_filters) . " (" . $members_count . " results)";
                    } else {
                        echo "Showing all " . $members_count . " members";
                    }
                    ?>
                </span>
            </div>
        </div>

        <div class="table-responsive">

            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">
                            <input type="checkbox" id="selectAll" class="form-check-input" title="Select All">
                        </th>
                        <th style="width: 60px;" class="text-center">#</th>
                        <th>Member Info</th>
                        <th>Contact</th>
                        <th>Role</th>
                        <th>Attendance</th>
                        <th>Joined</th>
                        <th class="text-center" style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="membersTableBody">
                <?php if ($result && $result->num_rows > 0): $count = 1 + $offset; ?>
                    <?php while ($row = $result->fetch_assoc()): 
                        $nameParts = explode(' ', $row['name']);
                        $first_name = $nameParts[0] ?? '';
                        $middle_initial = $nameParts[1] ?? '';
                        $last_name = $nameParts[2] ?? '';

                        // 🔢 Fetch attendance summary for this member
                        $attendanceStmt->bind_param("i", $row['id']);
                        $attendanceStmt->execute();
                        $attSummary = $attendanceStmt->get_result()->fetch_assoc();
                        $totalPresent = (int)($attSummary['total_present'] ?? 0);
                        $lastAttendance = $attSummary['last_attendance'] ?? null;

                        // 🧮 Compute attendance status (rule for defense explanation)
                        // Rule:
                        //   - Inactive        = 0 events attended in the last 6 months (or never attended)
                        //   - Occasional      = 1–2 events attended, and last attendance is within 6 months
                        //   - Active          = 3 or more events attended, and last attendance is within 6 months
                        //   - Inactive (old)  = has attended before, but last attendance is more than 6 months ago
                        $statusLabel = 'Inactive';
                        $statusClass = 'bg-secondary';
                        $statusKey   = 'inactive';

                        if ($totalPresent > 0 && $lastAttendance) {
                            $lastTs = strtotime($lastAttendance);
                            $sixMonthsAgo = strtotime('-6 months');

                            if ($lastTs >= $sixMonthsAgo) {
                                if ($totalPresent >= 3) {
                                    $statusLabel = 'Active';
                                    $statusClass = 'bg-success';
                                    $statusKey   = 'active';
                                } else {
                                    $statusLabel = 'Occasional';
                                    $statusClass = 'bg-warning text-dark';
                                    $statusKey   = 'occasional';
                                }
                            } else {
                                $statusLabel = 'Inactive (no recent attendance)';
                                $statusClass = 'bg-secondary';
                                $statusKey   = 'inactive_old';
                            }
                        }
                    ?>
                        <tr class="member-row" 
                            data-att-status="<?= htmlspecialchars($statusKey) ?>">
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input member-checkbox" value="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['name']) ?>">
                            </td>
                            <td class="text-center"><strong><?= $count++ ?></strong></td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="../../uploads/members/<?= htmlspecialchars($row['image'] ?? 'default_member.png') ?>" 
                                         alt="<?= htmlspecialchars($row['name']) ?>" 
                                         style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;"
                                         onerror="if (!this.getAttribute('data-tried-fallback')) { this.setAttribute('data-tried-fallback', 'true'); this.src='https://ui-avatars.com/api/?name=<?= urlencode($row['name']) ?>&background=random&color=fff'; }">
                                    <div>
                                        <strong style="font-size: 15px;"><?= htmlspecialchars($row['name']) ?></strong>
                                        <div class="text-muted small">#<?= $row['id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><i class="bi bi-telephone-fill text-muted me-2"></i><?= htmlspecialchars($row['phone']) ?></div>
                                <div class="mt-1"><i class="bi bi-envelope-fill text-muted me-2"></i><small><?= htmlspecialchars($row['email']) ?></small></div>
                            </td>
                            <td>
                                <?php 
                                $check_officer = $conn->prepare("SELECT id FROM officers WHERE member_id = ? LIMIT 1");
                                $check_officer->bind_param("i", $row['id']);
                                $check_officer->execute();
                                $check_officer->store_result();
                                if ($check_officer->num_rows > 0) {
                                    echo '<span class="badge bg-primary">Officer</span>';
                                } else {
                                    echo '<span class="badge bg-info">Member</span>';
                                }
                                $check_officer->close();
                                ?>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= $totalPresent ?> event<?= $totalPresent === 1 ? '' : 's' ?></div>
                                <?php if ($lastAttendance): ?>
                                    <div class="text-muted small">Last: <?= date('M d, Y', strtotime($lastAttendance)) ?></div>
                                <?php else: ?>
                                    <div class="text-muted small">No attendance yet</div>
                                <?php endif; ?>
                                <div class="mt-1">
                                    <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                </div>
                            </td>
                            <td class="text-muted small">
                                <?= isset($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : 'N/A' ?>
                            </td>
                            <td class="text-center">
                                <a href="../view_member_info.php?id=<?= $row['id'] ?>" class="btn btn-view btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="View member" aria-label="View member">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button 
                                    class="btn btn-edit btn-sm editBtn"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Edit member" aria-label="Edit member"

                                    data-id="<?= $row['id'] ?>"
                                    data-firstname="<?= htmlspecialchars($first_name) ?>"
                                    data-middleinitial="<?= htmlspecialchars($middle_initial) ?>"
                                    data-lastname="<?= htmlspecialchars($last_name) ?>"
                                    data-dob="<?= htmlspecialchars($row['dob']) ?>"
                                    data-gender="<?= htmlspecialchars($row['gender']) ?>"
                                    data-phone="<?= htmlspecialchars($row['phone']) ?>"
                                    data-email="<?= htmlspecialchars($row['email']) ?>"
                                    data-address="<?= htmlspecialchars($row['address']) ?>"
                                    data-worktype="<?= htmlspecialchars($row['work_type']) ?>"
                                    data-municipal="<?= htmlspecialchars($row['municipal_permit_no'] ?? '') ?>"
                                    data-bfar="<?= htmlspecialchars($row['bfar_fisherfolk_id'] ?? '') ?>"
                                    data-boat="<?= htmlspecialchars($row['boat_name']) ?>"
                                    data-boatreg="<?= htmlspecialchars($row['boat_registration'] ?? '') ?>"
                                    data-fishing="<?= htmlspecialchars($row['fishing_area']) ?>"
                                    data-emergency="<?= htmlspecialchars($row['emergency_name']) ?>"
                                    data-emergencyphone="<?= htmlspecialchars($row['emergency_phone']) ?>"
                                    data-agreement="<?= $row['agreement'] ?>"
                                    data-image="<?= htmlspecialchars($row['image']) ?>"
                                ><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-warning btn-sm archive-btn text-white" data-id="<?= $row['id'] ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Archive member" aria-label="Archive member">
                                    <i class="bi bi-archive"></i>
                                </button>

                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h5>No Members Found</h5>
                                <p>Click "Add New Member" to get started</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-4 px-3">
            <div class="pagination-info text-muted small">
                Showing <?= $offset + 1 ?> to <?= min($offset + $per_page, $total_records) ?> of <?= $total_records ?> members
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <!-- Previous Button -->
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if ($start_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '">1</a></li>';
                        if ($start_page > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        $active = $i === $page ? 'active' : '';
                        echo '<li class="page-item ' . $active . '">';
                        echo '<a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => $i])) . '">' . $i . '</a>';
                        echo '</li>';
                    }
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => $total_pages])) . '">' . $total_pages . '</a></li>';
                    }
                    ?>
                    
                    <!-- Next Button -->
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%); color: white;">
        <h5 class="modal-title">
            <i class="bi bi-person-plus-fill me-2"></i>
            Add New Member
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" enctype="multipart/form-data" autocomplete="off" id="addMemberForm">
        <input type="hidden" name="add_member" value="1">
        <div class="modal-body p-4">
          
          <!-- Personal Information Section -->
          <div class="form-section">
            <div class="section-header">
              <i class="bi bi-person-vcard"></i>
              <span>Personal Information</span>
            </div>
            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" name="first_name" class="form-control" required placeholder="Juan" value="<?= old('first_name') ?>">
              </div>
              <div class="col-md-2">
                <label class="form-label">M.I.</label>
                <input type="text" name="middle_initial" class="form-control" maxlength="2" placeholder="M" value="<?= old('middle_initial') ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" name="last_name" class="form-control" required placeholder="Dela Cruz" value="<?= old('last_name') ?>">
              </div>
              <div class="col-md-2">
                <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                <input type="date" name="dob" id="add_dob" class="form-control" required max="<?= date('Y-m-d', strtotime('-18 years')) ?>" value="<?= old('dob') ?>">
              </div>
              <div class="col-md-2">
                <label class="form-label">Age</label>
                <input type="text" id="add_age" class="form-control" readonly placeholder="Auto">
              </div>
              
              <div class="col-md-4">
                <label class="form-label">Gender <span class="text-danger">*</span></label>
                <select name="gender" class="form-select" required>
                  <option value="">Select Gender</option>
                  <option value="Male" <?= selected('gender', 'Male') ?>>Male</option>
                  <option value="Female" <?= selected('gender', 'Female') ?>>Female</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Civil Status <span class="text-danger">*</span></label>
                <select name="civil_status" class="form-select" required>
                  <option value="">Select Status</option>
                  <option value="Single" <?= selected('civil_status', 'Single') ?>>Single</option>
                  <option value="Married" <?= selected('civil_status', 'Married') ?>>Married</option>
                  <option value="Widowed" <?= selected('civil_status', 'Widowed') ?>>Widowed</option>
                  <option value="Separated" <?= selected('civil_status', 'Separated') ?>>Separated</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Upload Photo</label>
                <input type="file" name="image" id="add_image" class="form-control" accept="image/*">
                <small class="text-muted">JPG, PNG, GIF (Max 2MB)</small>
              </div>
            </div>
          </div>

          <!-- Contact Information Section -->
          <div class="form-section">
            <div class="section-header">
              <i class="bi bi-telephone"></i>
              <span>Contact Information</span>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                <input type="tel" name="phone" id="add_phone" class="form-control" required placeholder="09XX-XXX-XXXX" maxlength="13" value="<?= old('phone') ?>">
                <small class="text-muted">Format: 09XX-XXX-XXXX</small>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required placeholder="member@example.com" value="<?= old('email') ?>">
              </div>
            </div>
          </div>

          <!-- Address Section -->
          <div class="form-section">
            <div class="section-header">
              <i class="bi bi-geo-alt"></i>
              <span>Address</span>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Street / House No. <span class="text-danger">*</span></label>
                <input type="text" name="street" class="form-control" required placeholder="123 Main St." value="<?= old('street') ?>">
                <small class="text-muted">Enter complete house number and street</small>
              </div>
              <div class="col-md-6">
                <label class="form-label">Barangay <span class="text-danger">*</span></label>
                <input type="text" name="barangay" class="form-control" required placeholder="Barangay 123" value="<?= old('barangay') ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Municipality / City <span class="text-danger">*</span></label>
                <input type="text" name="municipality" class="form-control" required placeholder="City/Municipality" value="<?= old('municipality') ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Province <span class="text-danger">*</span></label>
                <input type="text" name="province" class="form-control" required placeholder="Province" value="<?= old('province') ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Region <span class="text-danger">*</span></label>
                <select name="region" class="form-select" required>
                  <option value="">Select Region</option>
                  <option value="NCR" <?= selected('region', 'NCR') ?>>NCR - National Capital Region</option>
                  <option value="CAR" <?= selected('region', 'CAR') ?>>CAR - Cordillera Administrative Region</option>
                  <option value="Region I" <?= selected('region', 'Region I') ?>>Region I - Ilocos Region</option>
                  <option value="Region II" <?= selected('region', 'Region II') ?>>Region II - Cagayan Valley</option>
                  <option value="Region III" <?= selected('region', 'Region III') ?>>Region III - Central Luzon</option>
                  <option value="Region IV-A" <?= selected('region', 'Region IV-A') ?>>Region IV-A - CALABARZON</option>
                  <option value="Region IV-B" <?= selected('region', 'Region IV-B') ?>>Region IV-B - MIMAROPA</option>
                  <option value="Region V" <?= selected('region', 'Region V') ?>>Region V - Bicol Region</option>
                  <option value="Region VI" <?= selected('region', 'Region VI') ?>>Region VI - Western Visayas</option>
                  <option value="Region VII" <?= selected('region', 'Region VII') ?>>Region VII - Central Visayas</option>
                  <option value="Region VIII" <?= selected('region', 'Region VIII') ?>>Region VIII - Eastern Visayas</option>
                  <option value="Region IX" <?= selected('region', 'Region IX') ?>>Region IX - Zamboanga Peninsula</option>
                  <option value="Region X" <?= selected('region', 'Region X') ?>>Region X - Northern Mindanao</option>
                  <option value="Region XI" <?= selected('region', 'Region XI') ?>>Region XI - Davao Region</option>
                  <option value="Region XII" <?= selected('region', 'Region XII') ?>>Region XII - SOCCSKSARGEN</option>
                  <option value="Region XIII" <?= selected('region', 'Region XIII') ?>>Region XIII - Caraga</option>
                  <option value="BARMM" <?= selected('region', 'BARMM') ?>>BARMM - Bangsamoro</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Zip Code <span class="text-danger">*</span></label>
                <input type="text" name="zip_code" class="form-control" required placeholder="1000" maxlength="4" pattern="\d{4}" value="<?= old('zip_code') ?>">
              </div>
            </div>
          </div>

          <!-- Work Information Section -->
          <div class="form-section">
            <div class="section-header">
              <i class="bi bi-briefcase"></i>
              <span>Work Information</span>
            </div>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Type of Work <span class="text-danger">*</span></label>
                <select name="work_type" id="add_work_type" class="form-select" required onchange="toggleWorkTypeFields()">
                  <option value="">Select Type</option>
                  <option value="Fisherman" <?= selected('work_type', 'Fisherman') ?>>Fisherman</option>
                  <option value="Bangkero" <?= selected('work_type', 'Bangkero') ?>>Bangkero</option>
                  <option value="Both" <?= selected('work_type', 'Both') ?>>Both</option>
                </select>
                <small class="text-muted">Select your main type of work. License fields depend on your selection.</small>
              </div>
            </div>

            <!-- Licenses / Permits Section -->
            <div id="licenses_section" class="mt-4 d-none">
              <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2">
                <i class="bi bi-card-checklist me-2"></i>Licenses / Permits
              </h6>
              <div class="row g-3">
                <div class="col-md-6" id="municipal_field">
                  <label class="form-label">Municipal Permit No.</label>
                  <input type="text" name="municipal_permit_no" id="add_municipal_permit" class="form-control" placeholder="Municipal Fisherfolk Permit" value="<?= old('municipal_permit_no') ?>">
                  <small class="text-muted">Enter your Municipal Fisherfolk Permit from the Municipal Agriculture Office.</small>
                </div>
                <div class="col-md-6" id="bfar_field">
                  <label class="form-label">BFAR Fisherfolk ID</label>
                  <input type="text" name="bfar_fisherfolk_id" id="add_bfar_id" class="form-control" placeholder="BFAR Fisherfolk ID" value="<?= old('bfar_fisherfolk_id') ?>">
                  <small class="text-muted">Enter your BFAR Fisherfolk ID from the Bureau of Fisheries. Optional if you have a Municipal Permit.</small>
                </div>
              </div>
            </div>

            <!-- Boat Information Section -->
            <div id="boat_section" class="mt-4 d-none">
              <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2">
                <i class="bi bi-water me-2"></i>Boat Information
              </h6>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Boat Name / Primary Vessel</label>
                  <input type="text" name="boat_name" id="add_boat_name" class="form-control" placeholder="Enter boat name" value="<?= old('boat_name') ?>">
                  <small class="text-muted">Required for Bangkero. Enter the name of the boat you operate (owned or managed).</small>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Boat Registration / License</label>
                  <input type="text" name="boat_registration" id="add_boat_registration" class="form-control" placeholder="Enter boat registration number" value="<?= old('boat_registration') ?>">
                  <small class="text-muted">Enter your boat registration or license number from the Maritime Industry Authority (MARINA) or local port authority.</small>
                </div>
              </div>
            </div>
          </div>

          <!-- Emergency Contact Section -->
          <div class="form-section">
            <div class="section-header">
              <i class="bi bi-heart-pulse"></i>
              <span>Emergency Contact</span>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Contact Name <span class="text-danger">*</span></label>
                <input type="text" name="emergency_name" class="form-control" required placeholder="Full name of emergency contact" value="<?= old('emergency_name') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Contact Phone <span class="text-danger">*</span></label>
                <input type="tel" name="emergency_phone" id="add_emergency_phone" class="form-control" required placeholder="09XX-XXX-XXXX" maxlength="13" value="<?= old('emergency_phone') ?>">
              </div>
            </div>
          </div>

          <!-- Agreement Section -->
          <div class="form-section mb-0">
            <div class="form-check p-3 border rounded" style="background: #f8f9fa;">
              <input type="checkbox" name="agreement" class="form-check-input" id="add_agreement" value="1" required <?= checked('agreement') ?>>
              <label for="add_agreement" class="form-check-label fw-semibold">
                I agree to follow the association's rules and regulations <span class="text-danger">*</span>
              </label>
            </div>
          </div>

        </div>
        <div class="modal-footer" style="justify-content: flex-end; gap: 10px; padding: 20px;">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="padding: 10px 24px;">Cancel</button>
          <button type="submit" class="btn btn-success" style="padding: 12px 32px; font-weight: 600; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
            <i class="bi bi-check-circle-fill me-2"></i>
            Add Member
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Member Modal -->
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            <i class="bi bi-pencil-square me-2"></i>
            Edit Member Information
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="edit_member" value="1">
        <input type="hidden" name="member_id" id="edit_member_id">
        <div class="modal-body">
          <div class="row g-4">
            <div class="col-md-4">
              <label class="form-label"><i class="bi bi-person me-2"></i>First Name <span class="text-danger">*</span></label>
              <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label"><i class="bi bi-person me-2"></i>Middle Initial</label>
              <input type="text" name="middle_initial" id="edit_middle_initial" class="form-control" maxlength="2">
            </div>
            <div class="col-md-4">
              <label class="form-label"><i class="bi bi-person me-2"></i>Last Name <span class="text-danger">*</span></label>
              <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-calendar-event me-2"></i>Date of Birth <span class="text-danger">*</span></label>
              <input type="date" name="dob" id="edit_dob" class="form-control" required max="<?= date('Y-m-d', strtotime('-18 years')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-gender-ambiguous me-2"></i>Gender <span class="text-danger">*</span></label>
              <select name="gender" id="edit_gender" class="form-select" required>
                <option value="">-- Select Gender --</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-telephone-fill me-2"></i>Phone Number <span class="text-danger">*</span></label>
              <input type="tel" name="phone" id="edit_phone" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-envelope-fill me-2"></i>Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" id="edit_email" class="form-control" required>
            </div>

            <div class="col-12">
              <label class="form-label"><i class="bi bi-geo-alt-fill me-2"></i>Address <span class="text-danger">*</span></label>
              <input type="text" name="address" id="edit_address" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-briefcase-fill me-2"></i>Type of Work</label>
              <select name="work_type" id="edit_work_type" class="form-select" onchange="toggleEditWorkTypeFields()">
                <option value="Fisherman">Fisherman</option>
                <option value="Bangkero">Bangkero</option>
                <option value="Both">Both</option>
              </select>
              <small class="text-muted">Select your main type of work. License fields depend on your selection.</small>
            </div>

            <!-- Edit: Licenses / Permits Section -->
            <div id="edit_licenses_section" class="col-12 mt-3 d-none">
              <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2">
                <i class="bi bi-card-checklist me-2"></i>Licenses / Permits
              </h6>
              <div class="row g-3">
                <div class="col-md-6" id="edit_municipal_field">
                  <label class="form-label">Municipal Permit No.</label>
                  <input type="text" name="municipal_permit_no" id="edit_municipal_permit" class="form-control">
                  <small class="text-muted">Enter your Municipal Fisherfolk Permit from the Municipal Agriculture Office.</small>
                </div>
                <div class="col-md-6" id="edit_bfar_field">
                  <label class="form-label">BFAR Fisherfolk ID</label>
                  <input type="text" name="bfar_fisherfolk_id" id="edit_bfar_id" class="form-control">
                  <small class="text-muted">Enter your BFAR Fisherfolk ID from the Bureau of Fisheries. Optional if you have a Municipal Permit.</small>
                </div>
              </div>
            </div>

            <!-- Edit: Boat Information Section -->
            <div id="edit_boat_section" class="col-12 mt-3 d-none">
              <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2">
                <i class="bi bi-water me-2"></i>Boat Information
              </h6>
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Boat Name / Primary Vessel</label>
                  <input type="text" name="boat_name" id="edit_boat_name" class="form-control">
                  <small class="text-muted">Required for Bangkero. Enter the name of the boat you operate (owned or managed).</small>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Boat Registration / License</label>
                  <input type="text" name="boat_registration" id="edit_boat_registration" class="form-control">
                  <small class="text-muted">Enter your boat registration or license number from MARINA or local port authority.</small>
                </div>
                <div class="col-md-4">
                  <label class="form-label"><i class="bi bi-pin-map-fill me-2"></i>Fishing Area / Route</label>
                  <input type="text" name="fishing_area" id="edit_fishing_area" class="form-control">
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-person-badge me-2"></i>Emergency Contact Name</label>
              <input type="text" name="emergency_name" id="edit_emergency_name" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-telephone me-2"></i>Emergency Contact Phone</label>
              <input type="tel" name="emergency_phone" id="edit_emergency_phone" class="form-control">
            </div>

            <div class="col-12">
              <label class="form-label"><i class="bi bi-image me-2"></i>Update Photo (optional)</label>
              <input type="file" name="image" id="edit_image" class="form-control" accept="image/*">
              <small class="text-muted">Leave empty to keep current photo</small>
              <img id="edit_preview" src="" alt="Preview" class="img-preview d-none">
            </div>

            <div class="col-12">
              <div class="form-check">
                <input type="checkbox" name="agreement" class="form-check-input" id="edit_agreement" value="1">
                <label for="edit_agreement" class="form-check-label">
                  <strong>I agree to follow the association's rules and regulations</strong>
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-submit">
            <i class="bi bi-save-fill me-2"></i>
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Initialize custom-styled Bootstrap tooltips
const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.forEach(function (tooltipTriggerEl) {
    new bootstrap.Tooltip(tooltipTriggerEl, {
        trigger: 'hover focus',
        boundary: 'window'
    });
});

// ==========================================
// ADD MEMBER FORM ENHANCEMENTS
// ==========================================

// Phone number auto-formatting (Philippine format)
function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, ''); // Remove non-digits
    
    // Ensure starts with 09
    if (value.length > 0 && !value.startsWith('0')) {
        if (value.startsWith('9')) {
            value = '0' + value;
        }
    }
    
    // Limit to 11 digits
    value = value.substring(0, 11);
    
    // Format as 09XX-XXX-XXXX
    if (value.length >= 4 && value.length <= 7) {
        value = value.slice(0, 4) + '-' + value.slice(4);
    } else if (value.length > 7) {
        value = value.slice(0, 4) + '-' + value.slice(4, 7) + '-' + value.slice(7);
    }
    
    input.value = value;
}

// Calculate age from date of birth
function calculateAge(dobInput, ageInput) {
    const dob = new Date(dobInput.value);
    if (isNaN(dob.getTime())) {
        ageInput.value = '';
        return;
    }
    
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    
    ageInput.value = age >= 0 ? age + ' years old' : '';
}

// Show/hide fields based on work type selection (Add Member)
function toggleWorkTypeFields() {
    const workType = document.getElementById('add_work_type').value;
    const licensesSection = document.getElementById('licenses_section');
    const boatSection = document.getElementById('boat_section');
    const municipalField = document.getElementById('municipal_field');
    const bfarField = document.getElementById('bfar_field');
    
    // Hide all sections first
    licensesSection.classList.add('d-none');
    boatSection.classList.add('d-none');
    municipalField.classList.add('d-none');
    bfarField.classList.add('d-none');
    
    if (workType === 'Fisherman') {
        // Show licenses section with municipal and BFAR fields
        licensesSection.classList.remove('d-none');
        municipalField.classList.remove('d-none');
        bfarField.classList.remove('d-none');
    } else if (workType === 'Bangkero') {
        // Show boat section only
        boatSection.classList.remove('d-none');
        // Also show general license in licenses section
        licensesSection.classList.remove('d-none');
    } else if (workType === 'Both') {
        // Show everything
        licensesSection.classList.remove('d-none');
        boatSection.classList.remove('d-none');
        municipalField.classList.remove('d-none');
        bfarField.classList.remove('d-none');
    }
}

// Show/hide fields based on work type selection (Edit Member)
function toggleEditWorkTypeFields() {
    const workType = document.getElementById('edit_work_type').value;
    const licensesSection = document.getElementById('edit_licenses_section');
    const boatSection = document.getElementById('edit_boat_section');
    const municipalField = document.getElementById('edit_municipal_field');
    const bfarField = document.getElementById('edit_bfar_field');
    
    // Hide all sections first
    licensesSection.classList.add('d-none');
    boatSection.classList.add('d-none');
    municipalField.classList.add('d-none');
    bfarField.classList.add('d-none');
    
    if (workType === 'Fisherman') {
        // Show licenses section with municipal and BFAR fields
        licensesSection.classList.remove('d-none');
        municipalField.classList.remove('d-none');
        bfarField.classList.remove('d-none');
    } else if (workType === 'Bangkero') {
        // Show boat section only
        boatSection.classList.remove('d-none');
        // Also show general license in licenses section
        licensesSection.classList.remove('d-none');
    } else if (workType === 'Both') {
        // Show everything
        licensesSection.classList.remove('d-none');
        boatSection.classList.remove('d-none');
        municipalField.classList.remove('d-none');
        bfarField.classList.remove('d-none');
    }
}

// Initialize Add Member Form enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Phone formatting
    const phoneInput = document.getElementById('add_phone');
    const emergencyPhoneInput = document.getElementById('add_emergency_phone');
    
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            formatPhoneNumber(this);
        });
    }
    
    if (emergencyPhoneInput) {
        emergencyPhoneInput.addEventListener('input', function() {
            formatPhoneNumber(this);
        });
    }
    
    // Age calculation
    const dobInput = document.getElementById('add_dob');
    const ageInput = document.getElementById('add_age');
    
    if (dobInput && ageInput) {
        dobInput.addEventListener('change', function() {
            calculateAge(this, ageInput);
        });
    }
    
    // Work type conditional fields
    const workTypeSelect = document.getElementById('add_work_type');
    if (workTypeSelect) {
        workTypeSelect.addEventListener('change', toggleWorkTypeFields);
        // Initialize on load (for form retention after error)
        toggleWorkTypeFields();
    }
    
    // Form validation feedback
    const form = document.getElementById('addMemberForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Required Fields Missing',
                    text: 'Please fill in all required fields marked with *',
                    confirmButtonColor: '#2E86AB'
                });
            }
        });
        
        // Remove invalid class on input
        form.querySelectorAll('input, select').forEach(field => {
            field.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        });
    }
    
    // Reopen modal if there was an error (form retention)
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member']) && $alertType === 'error'): ?>
        const addModal = new bootstrap.Modal(document.getElementById('addMemberModal'));
        addModal.show();
    <?php endif; ?>
});

// Filter panel manual toggle (no Bootstrap dependency)
const filterToggleBtn = document.getElementById('filterToggleBtn');
const filterPanelBody = document.getElementById('filterPanelBody');
const filterPanelLabel = document.getElementById('filterPanelLabel');
const filterPanelIcon = document.getElementById('filterPanelIcon');
if (filterToggleBtn && filterPanelBody && filterPanelLabel && filterPanelIcon) {
    filterToggleBtn.addEventListener('click', () => {
        const isHidden = filterPanelBody.classList.contains('d-none');
        if (isHidden) {
            filterPanelBody.classList.remove('d-none');
            filterPanelLabel.textContent = 'Hide filters';
            filterPanelIcon.classList.remove('bi-chevron-down');
            filterPanelIcon.classList.add('bi-chevron-up');
        } else {
            filterPanelBody.classList.add('d-none');
            filterPanelLabel.textContent = 'Show filters';
            filterPanelIcon.classList.remove('bi-chevron-up');
            filterPanelIcon.classList.add('bi-chevron-down');
        }
    });
}



// Fill and open Edit modal

document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('edit_member_id').value = btn.dataset.id;
        document.getElementById('edit_first_name').value = btn.dataset.firstname;
        document.getElementById('edit_middle_initial').value = btn.dataset.middleinitial;
        document.getElementById('edit_last_name').value = btn.dataset.lastname;
        document.getElementById('edit_dob').value = btn.dataset.dob;
        document.getElementById('edit_gender').value = btn.dataset.gender;
        document.getElementById('edit_phone').value = btn.dataset.phone;
        document.getElementById('edit_email').value = btn.dataset.email;
        document.getElementById('edit_address').value = btn.dataset.address;
        document.getElementById('edit_work_type').value = btn.dataset.worktype;
        document.getElementById('edit_municipal_permit').value = btn.dataset.municipal || '';
        document.getElementById('edit_bfar_id').value = btn.dataset.bfar || '';
        document.getElementById('edit_boat_name').value = btn.dataset.boat;
        document.getElementById('edit_boat_registration').value = btn.dataset.boatreg || '';
        
        // Trigger conditional field display
        toggleEditWorkTypeFields();
        document.getElementById('edit_fishing_area').value = btn.dataset.fishing;
        document.getElementById('edit_emergency_name').value = btn.dataset.emergency;
        document.getElementById('edit_emergency_phone').value = btn.dataset.emergencyphone;
        document.getElementById('edit_agreement').checked = (btn.dataset.agreement == '1');

        const imgPreview = document.getElementById('edit_preview');
        if (btn.dataset.image && btn.dataset.image !== 'default_member.png') {
            imgPreview.src = "../../uploads/members/" + btn.dataset.image;
            imgPreview.classList.remove('d-none');
        } else {
            imgPreview.classList.add('d-none');
            imgPreview.src = "";
        }

        document.getElementById('edit_image').value = "";
        new bootstrap.Modal(document.getElementById('editMemberModal')).show();
    });
});

// Preview image on add modal
document.getElementById('add_image')?.addEventListener('change', function(e) {
    const [file] = this.files;
    const preview = document.getElementById('add_preview');
    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
});

// Preview image on edit modal
document.getElementById('edit_image')?.addEventListener('change', function(e) {
    const [file] = this.files;
    const preview = document.getElementById('edit_preview');
    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
});

// ==========================================
// BULK SELECTION FUNCTIONALITY
// ==========================================
let selectedMembers = [];

// Select All Checkbox
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.member-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
        updateRowSelection(checkbox);
    });
    updateBulkActions();
});

// Individual Checkbox Change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('member-checkbox')) {
        updateRowSelection(e.target);
        updateBulkActions();
        
        // Update "Select All" checkbox state
        const checkboxes = document.querySelectorAll('.member-checkbox');
        const checkedCount = document.querySelectorAll('.member-checkbox:checked').length;
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = checkedCount === checkboxes.length;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        }
    }
});

// Update Row Visual Selection
function updateRowSelection(checkbox) {
    const row = checkbox.closest('tr');
    if (checkbox.checked) {
        row.classList.add('selected');
    } else {
        row.classList.remove('selected');
    }
}

// Update Bulk Actions Visibility
function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.member-checkbox:checked');
    const count = checkboxes.length;
    const bulkContainer = document.getElementById('bulkActionsContainer');
    const selectedCountBadge = document.getElementById('selectedCount');
    
    if (!bulkContainer || !selectedCountBadge) return;

    if (count > 0) {
        bulkContainer.style.setProperty('display', 'flex', 'important');
        bulkContainer.classList.add('bulk-visible');
        selectedCountBadge.textContent = `${count} selected`;
        selectedMembers = Array.from(checkboxes).map(cb => ({
            id: cb.value,
            name: cb.dataset.name
        }));
    } else {
        bulkContainer.style.setProperty('display', 'none', 'important');
        bulkContainer.classList.remove('bulk-visible');
        selectedMembers = [];
        selectedCountBadge.textContent = '';
    }
}

// Ensure a clean initial state on page load (no preselected rows)
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }

    document.querySelectorAll('.member-checkbox').forEach(cb => {
        cb.checked = false;
        updateRowSelection(cb);
    });

    updateBulkActions();
});

// Client-side filters for attendance status
(function() {
    const statusFilter = document.getElementById('attendanceStatusFilter');

    function applyMemberRowFilters() {
        const statusVal = statusFilter ? statusFilter.value : '';

        document.querySelectorAll('#membersTableBody tr.member-row').forEach(row => {
            const rowStatus = row.getAttribute('data-att-status') || '';

            let visible = true;
            if (statusVal && rowStatus !== statusVal) visible = false;

            row.style.display = visible ? '' : 'none';
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', applyMemberRowFilters);
    }
})();

// Bulk Archive
document.addEventListener('click', function(e) {
    if (e.target.id === 'btnBulkArchive' || e.target.closest('#btnBulkArchive')) {
        if (selectedMembers.length === 0) return;
        
        Swal.fire({
            title: 'Archive Selected Members?',
            html: `You are about to archive <strong>${selectedMembers.length}</strong> member(s):<br><br>` +
                  `<div style="max-height: 200px; overflow-y: auto; text-align: left; padding: 10px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">` +
                  selectedMembers.map(m => `• ${m.name}`).join('<br>') +
                  `</div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, archive them!',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                const ids = selectedMembers.map(m => m.id).join(',');
                window.location.href = `bulk_delete.php?ids=${ids}&action=archive`;
            }
        });
    }
});

// Bulk Export
document.getElementById('btnBulkExport')?.addEventListener('click', function() {
    if (selectedMembers.length === 0) return;
    
    const ids = selectedMembers.map(m => m.id).join(',');
    window.open(`export_selected.php?ids=${ids}&format=csv`, '_blank');
    
    Swal.fire({
        icon: 'success',
        title: 'Export Started!',
        text: `Exporting ${selectedMembers.length} member(s)...`,
        timer: 2000,
        showConfirmButton: false
    });
});

// Deselect All
document.getElementById('btnDeselectAll')?.addEventListener('click', function() {
    document.querySelectorAll('.member-checkbox:checked').forEach(cb => {
        cb.checked = false;
        updateRowSelection(cb);
    });
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
});

// ==========================================
// EXPORT FUNCTIONALITY
// ==========================================
function exportData(format) {
    const currentParams = new URLSearchParams(window.location.search);
    
    let url = '';
    switch(format) {
        case 'csv':
            url = 'export_members_csv.php';
            break;
        case 'pdf':
            url = 'export_members_pdf.php';
            break;
        case 'excel':
            url = 'export_members_excel.php';
            break;
        case 'print':
            url = 'export_members_print.php';
            break;
    }
    
    // Append current filters to export
    if (url) {
        window.open(`${url}?${currentParams.toString()}`, '_blank');
    }
    
    Swal.fire({
        icon: 'success',
        title: 'Export Started!',
        text: `Preparing ${format.toUpperCase()} export...`,
        timer: 1500,
        showConfirmButton: false
    });
}

// Dropdown Toggle Fix
document.addEventListener('click', function(e) {
    const dropdownToggle = e.target.closest('.dropdown-toggle');
    const openDropdowns = document.querySelectorAll('.dropdown.show');

    if (dropdownToggle) {
        const dropdown = dropdownToggle.closest('.dropdown');
        const isShow = dropdown.classList.contains('show');
        
        // Close all dropdowns
        openDropdowns.forEach(d => d.classList.remove('show'));
        
        if (!isShow) {
            dropdown.classList.add('show');
        }
        e.preventDefault();
        e.stopPropagation();
    } else {
        // Close dropdowns when clicking outside
        openDropdowns.forEach(d => d.classList.remove('show'));
    }
});

// Archive confirmation
document.querySelectorAll('.archive-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const memberId = this.getAttribute('data-id');

        Swal.fire({
            title: 'Archive Member?',
            text: "Are you sure you want to move this member to the archive?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-archive-fill me-2"></i>Yes, archive it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `?archive=${memberId}`;
            }
        });
    });
});

// SweetAlert messages
<?php if ($alertMsg): ?>
Swal.fire({
    icon: '<?= $alertType ?>',
    title: '<?= ucfirst($alertType) ?>!',
    text: '<?= addslashes($alertMsg) ?>',
    confirmButtonColor: '<?= $alertType === "success" ? "#2E86AB" : "#ef4444" ?>',
    confirmButtonText: 'OK',
    allowOutsideClick: false
}).then(() => {
    <?php if ($alertType === "success"): ?>
        window.location.href = "memberlist.php";
    <?php endif; ?> });
<?php endif; ?>

<?php if (isset($_GET['archived'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Archived!',
    text: 'Member moved to archive successfully.',
    timer: 2500,
    showConfirmButton: false,
    confirmButtonColor: '#2E86AB'
}).then(() => {
    window.location.href = "memberlist.php";
});
<?php endif; ?>

<?php if (isset($_GET['bulk_archived'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Bulk Archive Successful!',
    text: '<?= intval($_GET['bulk_archived']) ?> member(s) have been moved to archive.',
    timer: 3000,
    showConfirmButton: false,
    confirmButtonColor: '#2E86AB'
}).then(() => {
    window.location.href = "memberlist.php";
});
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
Swal.fire({
    icon: 'error',
    title: 'Error!',
    text: 'An error occurred. Please try again.',
    confirmButtonColor: '#ef4444'
}).then(() => {
    window.location.href = "memberlist.php";
});
<?php endif; ?>
</script>
</body>
</html>
