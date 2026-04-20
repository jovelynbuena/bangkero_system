<<<<<<< HEAD
<?php
=======
﻿<?php
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
// Transparency Dashboard - Combined single page with Beneficiaries & Impact Metrics
session_start();

require_once('../../config/db_connect.php');

if (empty($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

$role = strtolower($_SESSION['role'] ?? 'guest');
$isAdmin = ($role === 'admin');

function currentOfficerPosition(mysqli $conn): string {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) return '';

    try {
        $stmt = $conn->prepare("SELECT member_id FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        $memberId = (int)($row['member_id'] ?? 0);
        if ($memberId <= 0) return '';

        $today = date('Y-m-d');
        $stmt = $conn->prepare("SELECT COALESCE(r.role_name, NULLIF(o.position,'')) AS position
            FROM officers o
            LEFT JOIN officer_roles r ON r.id = o.role_id
            WHERE o.member_id = ?
            ORDER BY (? BETWEEN o.term_start AND o.term_end) DESC, o.term_end DESC, o.id DESC
            LIMIT 1");
        $stmt->bind_param('is', $memberId, $today);
        $stmt->execute();
        $res = $stmt->get_result();
        $posRow = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        return strtolower(trim((string)($posRow['position'] ?? '')));
    } catch (Throwable) {
        return '';
    }
}

$transRole = strtolower(trim((string)($_SESSION['transparency_role'] ?? '')));

$hasTransparencyRoleCol = false;
try {
    $colRes = $conn->query("SHOW COLUMNS FROM users LIKE 'transparency_role'");
    $hasTransparencyRoleCol = ($colRes && $colRes->num_rows > 0);
} catch (Throwable) {
    $hasTransparencyRoleCol = false;
}

$officerPosition = $hasTransparencyRoleCol ? '' : currentOfficerPosition($conn);

$canAccess = $isAdmin
    || ($role === 'officer' && in_array($transRole, ['treasurer','secretary','both'], true))
    || ($role === 'officer' && !$hasTransparencyRoleCol && $transRole === '' && in_array($officerPosition, ['treasurer','secretary'], true));

if (!$canAccess) {
    header('Location: ../admin.php?error=transparency_access');
    exit;
}

$canEdit = $isAdmin
    || ($role === 'officer' && in_array($transRole, ['treasurer','both'], true))
    || ($role === 'officer' && !$hasTransparencyRoleCol && $transRole === '' && $officerPosition === 'treasurer');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

<<<<<<< HEAD
=======
// ── DB Migrations (run early, before any table queries) ──────────────────────

// Add donation_type column to transparency_donations if missing
try {
    $conn->query("ALTER TABLE transparency_donations ADD COLUMN donation_type ENUM('cash','in_kind') NOT NULL DEFAULT 'cash' AFTER donor_type");
} catch (Throwable) {}

// Ensure transparency_donation_items table exists (for in-kind/gamit donations)
try {
    $conn->query("CREATE TABLE IF NOT EXISTS transparency_donation_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        donation_id INT NOT NULL,
        item_name VARCHAR(255) NOT NULL,
        quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
        unit VARCHAR(50) DEFAULT NULL,
        unit_value DECIMAL(15,2) NOT NULL DEFAULT 0,
        total_value DECIMAL(15,2) GENERATED ALWAYS AS (quantity * unit_value) STORED,
        photo VARCHAR(500) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_donation_id (donation_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable) {}

// Ensure transparency_expenses table exists
try {
    $conn->query("CREATE TABLE IF NOT EXISTS transparency_expenses (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(100) DEFAULT NULL,
        amount DECIMAL(15,2) NOT NULL DEFAULT 0,
        expense_date DATE DEFAULT NULL,
        paid_to VARCHAR(255) DEFAULT NULL,
        reference_code VARCHAR(100) DEFAULT NULL,
        notes TEXT DEFAULT NULL,
        donation_id INT DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable) {}

// ─────────────────────────────────────────────────────────────────────────────

>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_token'];

function checkCsrf(): bool {
    return !empty($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);
}

$alertType = '';
$alertMsg  = '';

// Handle form submissions
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canEdit) {
        if (!checkCsrf()) {
            throw new Exception('Security check failed.');
        }

        $action = $_POST['action'] ?? '';

        // Add/Edit Program
        if ($action === 'add_program' || $action === 'edit_program') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $budget = (float)($_POST['budget'] ?? 0);
            $status = $_POST['status'] ?? 'active';
            $start_date = $_POST['start_date'] ?: null;
            $end_date = $_POST['end_date'] ?: null;

            if ($name === '') {
                throw new Exception('Program name is required.');
            }

            if ($action === 'add_program') {
                $stmt = $conn->prepare("INSERT INTO transparency_campaigns 
                    (name, description, goal_amount, status, start_date, end_date, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param('ssdsss', $name, $description, $budget, $status, $start_date, $end_date);
                $stmt->execute();
                $stmt->close();
                $alertType = 'success';
                $alertMsg = 'Program added successfully.';
            } else {
                $id = (int)($_POST['id'] ?? 0);
                $stmt = $conn->prepare("UPDATE transparency_campaigns 
                    SET name=?, description=?, goal_amount=?, status=?, start_date=?, end_date=?, updated_at=NOW() 
                    WHERE id=?");
                $stmt->bind_param('ssdsssi', $name, $description, $budget, $status, $start_date, $end_date, $id);
                $stmt->execute();
                $stmt->close();
                $alertType = 'success';
                $alertMsg = 'Program updated successfully.';
            }
        }

        // Add/Edit Assistance
        elseif ($action === 'add_assistance' || $action === 'edit_assistance') {
<<<<<<< HEAD
            $program_id = null;
            $source_name = trim($_POST['source_name'] ?? '');
            $source_type = trim($_POST['source_type'] ?? '');
            $amount = (float)($_POST['amount'] ?? 0);
            $date_received = $_POST['date_received'] ?: null;
            $reference = trim($_POST['reference'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
=======
            $program_id    = null;
            $source_name   = trim($_POST['source_name'] ?? '');
            $source_type   = trim($_POST['source_type'] ?? '');
            $donation_type = in_array($_POST['donation_type'] ?? '', ['cash','in_kind']) ? $_POST['donation_type'] : 'cash';
            $date_received = $_POST['date_received'] ?: null;
            $reference     = trim($_POST['reference'] ?? '');
            $notes         = trim($_POST['notes'] ?? '');
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560

            if ($source_name === '') {
                throw new Exception('Source name is required.');
            }
<<<<<<< HEAD
            if ($amount < 0) {
                throw new Exception('Amount cannot be negative.');
            }
=======

            // For cash: use posted amount. For in_kind: compute from items.
            $amount = 0;
            $itemNames   = $_POST['item_name']   ?? [];
            $itemQtys    = $_POST['item_qty']    ?? [];
            $itemUnits   = $_POST['item_unit']   ?? [];
            $itemValues  = $_POST['item_value']  ?? [];

            if ($donation_type === 'in_kind') {
                foreach ($itemValues as $iv) $amount += max(0, (float)$iv) * max(0, (float)($itemQtys[array_search($iv, $itemValues)] ?? 1));
                // recompute properly
                $amount = 0;
                foreach ($itemNames as $idx => $iname) {
                    $qty = max(0, (float)($itemQtys[$idx] ?? 1));
                    $val = max(0, (float)($itemValues[$idx] ?? 0));
                    $amount += $qty * $val;
                }
            } else {
                $amount = (float)($_POST['amount'] ?? 0);
            }
            if ($amount < 0) throw new Exception('Amount cannot be negative.');
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560

            // Handle multiple image uploads
            $uploadDir = '../../uploads/assistance/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $allowed     = ['jpg','jpeg','jfif','png','webp','gif','bmp','tiff','tif','heic','heif','avif'];
            $allowedMimes = ['image/jpeg','image/png','image/webp','image/gif','image/bmp','image/tiff','image/heic','image/heif','image/avif'];

            if ($action === 'add_assistance') {
                $stmt = $conn->prepare("INSERT INTO transparency_donations 
<<<<<<< HEAD
                    (campaign_id, donor_name, donor_type, amount, date_received, reference_code, notes, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed', NOW())");
                $stmt->bind_param('issdsss', $program_id, $source_name, $source_type, $amount, $date_received, $reference, $notes);
=======
                    (campaign_id, donor_name, donor_type, donation_type, amount, date_received, reference_code, notes, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', NOW())");
                $stmt->bind_param('isssdsss', $program_id, $source_name, $source_type, $donation_type, $amount, $date_received, $reference, $notes);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                $stmt->execute();
                $newId = $conn->insert_id;
                $stmt->close();

<<<<<<< HEAD
                // Upload new images
=======
                // Save in-kind items
                if ($donation_type === 'in_kind' && !empty($itemNames)) {
                    $uploadItemDir = '../../uploads/assistance/items/';
                    if (!is_dir($uploadItemDir)) mkdir($uploadItemDir, 0755, true);
                    $iStmt = $conn->prepare("INSERT INTO transparency_donation_items (donation_id, item_name, quantity, unit, unit_value, photo) VALUES (?,?,?,?,?,?)");
                    foreach ($itemNames as $idx => $iname) {
                        $iname = trim($iname);
                        if ($iname === '') continue;
                        $qty   = max(0, (float)($itemQtys[$idx]   ?? 1));
                        $unit  = trim($itemUnits[$idx]  ?? '');
                        $val   = max(0, (float)($itemValues[$idx]  ?? 0));
                        $iPhoto = null;
                        // per-item photo
                        if (!empty($_FILES['item_photo']['name'][$idx]) && $_FILES['item_photo']['error'][$idx] === UPLOAD_ERR_OK) {
                            $ext  = strtolower(pathinfo($_FILES['item_photo']['name'][$idx], PATHINFO_EXTENSION));
                            $mime = mime_content_type($_FILES['item_photo']['tmp_name'][$idx]);
                            if (in_array($ext, $allowed) || in_array($mime, $allowedMimes)) {
                                if (in_array($ext, ['jfif','heic','heif'])) $ext = 'jpg';
                                $fn = time() . '_item_' . $idx . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                                if (move_uploaded_file($_FILES['item_photo']['tmp_name'][$idx], $uploadItemDir . $fn)) {
                                    $iPhoto = 'uploads/assistance/items/' . $fn;
                                }
                            }
                        }
                        $iStmt->bind_param('isdsds', $newId, $iname, $qty, $unit, $val, $iPhoto);
                        $iStmt->execute();
                    }
                    $iStmt->close();
                }

                // Upload general images
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                if (!empty($_FILES['assistance_images']['name'][0])) {
                    $imgStmt = $conn->prepare("INSERT INTO transparency_donation_images (donation_id, image_path, sort_order) VALUES (?, ?, ?)");
                    foreach ($_FILES['assistance_images']['name'] as $i => $fname) {
                        if (empty($fname) || $_FILES['assistance_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                        $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                        $mime = mime_content_type($_FILES['assistance_images']['tmp_name'][$i]);
                        if (!in_array($ext, $allowed) && !in_array($mime, $allowedMimes)) continue;
                        if (in_array($ext, ['jfif','heic','heif'])) $ext = 'jpg';
                        if ($ext === 'avif') $ext = 'webp';
                        $fileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        if (move_uploaded_file($_FILES['assistance_images']['tmp_name'][$i], $uploadDir . $fileName)) {
                            $imgPath = 'uploads/assistance/' . $fileName;
                            $imgStmt->bind_param('isi', $newId, $imgPath, $i);
                            $imgStmt->execute();
                        }
                    }
                    $imgStmt->close();
                }

                $alertType = 'success';
                $alertMsg  = 'Assistance recorded successfully.';
            } else {
                $id = (int)($_POST['id'] ?? 0);
                $stmt = $conn->prepare("UPDATE transparency_donations 
<<<<<<< HEAD
                    SET campaign_id=?, donor_name=?, donor_type=?, amount=?, date_received=?, reference_code=?, notes=?, updated_at=NOW() 
                    WHERE id=?");
                $stmt->bind_param('issdsssi', $program_id, $source_name, $source_type, $amount, $date_received, $reference, $notes, $id);
                $stmt->execute();
                $stmt->close();

=======
                    SET campaign_id=?, donor_name=?, donor_type=?, donation_type=?, amount=?, date_received=?, reference_code=?, notes=?, updated_at=NOW() 
                    WHERE id=?");
                $stmt->bind_param('isssdsssi', $program_id, $source_name, $source_type, $donation_type, $amount, $date_received, $reference, $notes, $id);
                $stmt->execute();
                $stmt->close();


                // Replace in-kind items
                if ($donation_type === 'in_kind') {
                    $conn->query("DELETE FROM transparency_donation_items WHERE donation_id=$id");
                    if (!empty($itemNames)) {
                        $uploadItemDir = '../../uploads/assistance/items/';
                        if (!is_dir($uploadItemDir)) mkdir($uploadItemDir, 0755, true);
                        $iStmt = $conn->prepare("INSERT INTO transparency_donation_items (donation_id, item_name, quantity, unit, unit_value, photo) VALUES (?,?,?,?,?,?)");
                        foreach ($itemNames as $idx => $iname) {
                            $iname = trim($iname);
                            if ($iname === '') continue;
                            $qty   = max(0, (float)($itemQtys[$idx]   ?? 1));
                            $unit  = trim($itemUnits[$idx]  ?? '');
                            $val   = max(0, (float)($itemValues[$idx]  ?? 0));
                            $iPhoto = null;
                            if (!empty($_FILES['item_photo']['name'][$idx]) && $_FILES['item_photo']['error'][$idx] === UPLOAD_ERR_OK) {
                                $ext  = strtolower(pathinfo($_FILES['item_photo']['name'][$idx], PATHINFO_EXTENSION));
                                $mime = mime_content_type($_FILES['item_photo']['tmp_name'][$idx]);
                                if (in_array($ext, $allowed) || in_array($mime, $allowedMimes)) {
                                    if (in_array($ext, ['jfif','heic','heif'])) $ext = 'jpg';
                                    $fn = time() . '_item_' . $idx . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                                    if (move_uploaded_file($_FILES['item_photo']['tmp_name'][$idx], $uploadItemDir . $fn)) {
                                        $iPhoto = 'uploads/assistance/items/' . $fn;
                                    }
                                }
                            }
                            $iStmt->bind_param('isdsds', $id, $iname, $qty, $unit, $val, $iPhoto);
                            $iStmt->execute();
                        }
                        $iStmt->close();
                    }
                }

>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                // Upload new images (append)
                if (!empty($_FILES['assistance_images']['name'][0])) {
                    $imgStmt = $conn->prepare("INSERT INTO transparency_donation_images (donation_id, image_path, sort_order) VALUES (?, ?, ?)");
                    $sortRes = $conn->query("SELECT COALESCE(MAX(sort_order),0) as mx FROM transparency_donation_images WHERE donation_id=$id");
                    $sortStart = $sortRes ? (int)$sortRes->fetch_assoc()['mx'] + 1 : 0;
                    foreach ($_FILES['assistance_images']['name'] as $i => $fname) {
                        if (empty($fname) || $_FILES['assistance_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                        $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                        $mime = mime_content_type($_FILES['assistance_images']['tmp_name'][$i]);
                        if (!in_array($ext, $allowed) && !in_array($mime, $allowedMimes)) continue;
                        if (in_array($ext, ['jfif','heic','heif'])) $ext = 'jpg';
                        if ($ext === 'avif') $ext = 'webp';
                        $fileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        if (move_uploaded_file($_FILES['assistance_images']['tmp_name'][$i], $uploadDir . $fileName)) {
                            $imgPath  = 'uploads/assistance/' . $fileName;
                            $sortVal  = $sortStart + $i;
                            $imgStmt->bind_param('isi', $id, $imgPath, $sortVal);
                            $imgStmt->execute();
                        }
                    }
                    $imgStmt->close();
                }

                $alertType = 'success';
                $alertMsg  = 'Assistance updated successfully.';
            }
        }

        // Add/Edit Beneficiary
        elseif ($action === 'add_beneficiary' || $action === 'edit_beneficiary') {
            $b_name = trim($_POST['b_name'] ?? '');
            $b_program_id = (int)($_POST['b_program_id'] ?? 0) ?: null;
            $b_assistance_type = trim($_POST['b_assistance_type'] ?? '');
            $b_amount = (float)($_POST['b_amount'] ?? 0) ?: null;
            $b_quantity = (int)($_POST['b_quantity'] ?? 0) ?: null;
            $b_date = $_POST['b_date'] ?: null;
            $b_barangay = trim($_POST['b_barangay'] ?? '');
            $b_status = $_POST['b_status'] ?? 'served';
            $b_featured = isset($_POST['b_featured']) ? 1 : 0;
            $b_story = trim($_POST['b_story'] ?? '');
            $b_id = (int)($_POST['b_id'] ?? 0);

            if ($b_name === '') {
                throw new Exception('Beneficiary name is required.');
            }

            if ($action === 'add_beneficiary') {
                $stmt = $conn->prepare("INSERT INTO transparency_beneficiaries 
                    (name, program_id, assistance_type, amount_value, quantity, date_assisted, barangay, status, featured, short_story, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param('sisdisssis', $b_name, $b_program_id, $b_assistance_type, $b_amount, $b_quantity, $b_date, $b_barangay, $b_status, $b_featured, $b_story);
                $stmt->execute();
                $stmt->close();
                $alertType = 'success';
                $alertMsg = 'Beneficiary added successfully.';
            } else {
                $stmt = $conn->prepare("UPDATE transparency_beneficiaries 
                    SET name=?, program_id=?, assistance_type=?, amount_value=?, quantity=?, date_assisted=?, barangay=?, status=?, featured=?, short_story=?, updated_at=NOW() 
                    WHERE id=?");
                $stmt->bind_param('sisdisssisi', $b_name, $b_program_id, $b_assistance_type, $b_amount, $b_quantity, $b_date, $b_barangay, $b_status, $b_featured, $b_story, $b_id);
                $stmt->execute();
                $stmt->close();
                $alertType = 'success';
                $alertMsg = 'Beneficiary updated successfully.';
            }
        }

        // Delete Beneficiary
        elseif ($action === 'delete_beneficiary') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $conn->prepare("DELETE FROM transparency_beneficiaries WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $alertType = 'success';
            $alertMsg = 'Beneficiary deleted.';
        }

        // Add/Edit Impact Metric
        elseif ($action === 'add_metric' || $action === 'edit_metric') {
            $m_key = trim($_POST['m_key'] ?? '');
            $m_label = trim($_POST['m_label'] ?? '');
            $m_value = (float)($_POST['m_value'] ?? 0);
            $m_unit = trim($_POST['m_unit'] ?? '');
            $m_active = isset($_POST['m_active']) ? 1 : 0;
            $m_id = (int)($_POST['m_id'] ?? 0);

            if ($m_key === '' || $m_label === '') {
                throw new Exception('Metric key and label are required.');
            }

            if ($action === 'add_metric') {
                $stmt = $conn->prepare("INSERT INTO transparency_impact_metrics 
                    (metric_key, label, value, unit, is_active, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param('ssdsi', $m_key, $m_label, $m_value, $m_unit, $m_active);
                $stmt->execute();
                $stmt->close();
                $alertType = 'success';
                $alertMsg = 'Impact metric added successfully.';
            } else {
                $stmt = $conn->prepare("UPDATE transparency_impact_metrics 
                    SET metric_key=?, label=?, value=?, unit=?, is_active=?, updated_at=NOW() 
                    WHERE id=?");
                $stmt->bind_param('ssdsii', $m_key, $m_label, $m_value, $m_unit, $m_active, $m_id);
                $stmt->execute();
                $stmt->close();
                $alertType = 'success';
                $alertMsg = 'Impact metric updated successfully.';
            }
        }

<<<<<<< HEAD
=======
        // ── Expenses ──────────────────────────────────────────────────
        elseif ($action === 'add_expense' || $action === 'edit_expense') {
            $exp_title       = trim($_POST['exp_title'] ?? '');
            $exp_category    = trim($_POST['exp_category'] ?? '');
            $exp_amount      = (float)($_POST['exp_amount'] ?? 0);
            $exp_date        = $_POST['exp_date'] ?: null;
            $exp_paid_to     = trim($_POST['exp_paid_to'] ?? '');
            $exp_reference   = trim($_POST['exp_reference'] ?? '');
            $exp_notes       = trim($_POST['exp_notes'] ?? '');
            $exp_donation_id = (int)($_POST['exp_donation_id'] ?? 0) ?: null;
            $exp_id          = (int)($_POST['exp_id'] ?? 0);

            if ($exp_title === '') throw new Exception('Expense title is required.');
            if ($exp_amount < 0)  throw new Exception('Amount cannot be negative.');

            if ($action === 'add_expense') {
                $stmt = $conn->prepare("INSERT INTO transparency_expenses
                    (title, category, amount, expense_date, paid_to, reference_code, notes, donation_id, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param('ssdsssis', $exp_title, $exp_category, $exp_amount, $exp_date, $exp_paid_to, $exp_reference, $exp_notes, $exp_donation_id);
                $stmt->execute(); $stmt->close();
                $alertType = 'success'; $alertMsg = 'Expense recorded successfully.';
            } else {
                $stmt = $conn->prepare("UPDATE transparency_expenses
                    SET title=?, category=?, amount=?, expense_date=?, paid_to=?, reference_code=?, notes=?, donation_id=?, updated_at=NOW()
                    WHERE id=?");
                $stmt->bind_param('ssdssssii', $exp_title, $exp_category, $exp_amount, $exp_date, $exp_paid_to, $exp_reference, $exp_notes, $exp_donation_id, $exp_id);
                $stmt->execute(); $stmt->close();
                $alertType = 'success'; $alertMsg = 'Expense updated successfully.';
            }
        }

        elseif ($action === 'delete_expense') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $conn->prepare("DELETE FROM transparency_expenses WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute(); $stmt->close();
            $alertType = 'success'; $alertMsg = 'Expense deleted.';
        }

>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
        // Delete Impact Metric
        elseif ($action === 'delete_metric') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $conn->prepare("DELETE FROM transparency_impact_metrics WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $alertType = 'success';
            $alertMsg = 'Impact metric deleted.';
        }

        // Archive Program
        elseif ($action === 'archive_program') {
            $id = (int)($_POST['id'] ?? 0);
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $conn->begin_transaction();
            $stmtSel = $conn->prepare("SELECT * FROM transparency_campaigns WHERE id=?");
            $stmtSel->bind_param('i', $id);
            $stmtSel->execute();
            $prog = $stmtSel->get_result()->fetch_assoc();
            $stmtSel->close();
            if ($prog) {
                $pName       = (string)($prog['name'] ?? '');
                $pSlug       = (string)($prog['slug'] ?? '');
                $pDesc       = (string)($prog['description'] ?? '');
                $pGoal       = (float)($prog['goal_amount'] ?? 0);
                $pStatus     = (string)($prog['status'] ?? '');
                $pStart      = $prog['start_date'] ?? null;
                $pEnd        = $prog['end_date'] ?? null;
                $pBanner     = (string)($prog['banner_image'] ?? '');
                $stmtIns = $conn->prepare("INSERT INTO transparency_campaigns_archive 
                    (name, slug, description, goal_amount, status, start_date, end_date, banner_image, archived_by, archived_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmtIns->bind_param('sssdssssi',
                    $pName, $pSlug, $pDesc, $pGoal, $pStatus, $pStart, $pEnd, $pBanner, $userId);
                $stmtIns->execute();
                $stmtIns->close();
                $stmtDel = $conn->prepare("DELETE FROM transparency_campaigns WHERE id=?");
                $stmtDel->bind_param('i', $id);
                $stmtDel->execute();
                $stmtDel->close();
                $conn->commit();
                $alertType = 'success';
                $alertMsg = 'Program archived successfully.';
            } else {
                $conn->rollback();
                throw new Exception('Program not found.');
            }
        }

        // Delete Program
        elseif ($action === 'delete_program') {
            $id = (int)($_POST['id'] ?? 0);
            
            $stmtName = $conn->prepare("SELECT name FROM transparency_campaigns WHERE id=?");
            $stmtName->bind_param('i', $id);
            $stmtName->execute();
            $result = $stmtName->get_result();
            $programName = ($row = $result->fetch_assoc()) ? $row['name'] : null;
            $stmtName->close();
            
            $stmt = $conn->prepare("DELETE FROM transparency_campaigns WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            
            if ($programName) {
                $stmtFeat = $conn->prepare("DELETE FROM featured_programs WHERE title = ?");
                $stmtFeat->bind_param('s', $programName);
                $stmtFeat->execute();
                $stmtFeat->close();
            }
            
            $alertType = 'success';
            $alertMsg = 'Program deleted.';
        }

        // Archive Assistance
        elseif ($action === 'archive_assistance') {
            $id = (int)($_POST['id'] ?? 0);
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $conn->begin_transaction();
            $stmtSel = $conn->prepare("SELECT * FROM transparency_donations WHERE id=?");
            $stmtSel->bind_param('i', $id);
            $stmtSel->execute();
            $don = $stmtSel->get_result()->fetch_assoc();
            $stmtSel->close();
            if ($don) {
                $dCampaign    = (int)($don['campaign_id'] ?? 0);
                $dDonor       = (string)($don['donor_name'] ?? '');
                $dType        = (string)($don['donor_type'] ?? '');
                $dAmount      = (float)($don['amount'] ?? 0);
                $dCurrency    = (string)($don['currency'] ?? 'PHP');
                $dDate        = $don['date_received'] ?? null;
                $dPayMethod   = (string)($don['payment_method'] ?? '');
                $dRef         = (string)($don['reference_code'] ?? '');
                $dStatus      = (string)($don['status'] ?? '');
                $dRestricted  = (int)($don['is_restricted'] ?? 0);
                $dNotes       = (string)($don['notes'] ?? '');
                $stmtIns = $conn->prepare("INSERT INTO transparency_donations_archive 
                    (campaign_id, donor_name, donor_type, amount, currency, date_received, payment_method, reference_code, status, is_restricted, notes, archived_by, archived_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmtIns->bind_param('issdsssssisi',
                    $dCampaign, $dDonor, $dType, $dAmount, $dCurrency,
                    $dDate, $dPayMethod, $dRef, $dStatus, $dRestricted, $dNotes, $userId);
                $stmtIns->execute();
                $stmtIns->close();
                $stmtDel = $conn->prepare("DELETE FROM transparency_donations WHERE id=?");
                $stmtDel->bind_param('i', $id);
                $stmtDel->execute();
                $stmtDel->close();
                $conn->commit();
                $alertType = 'success';
                $alertMsg = 'Assistance record archived successfully.';
            } else {
                $conn->rollback();
                throw new Exception('Assistance record not found.');
            }
        }

        // Delete Assistance
        elseif ($action === 'delete_assistance') {
            $id = (int)($_POST['id'] ?? 0);
            // Delete associated images from disk
            $imgRes = $conn->query("SELECT image_path FROM transparency_donation_images WHERE donation_id=$id");
            while ($imgRes && $imgRow = $imgRes->fetch_assoc()) {
                if ($imgRow['image_path'] && file_exists('../../' . $imgRow['image_path'])) {
                    @unlink('../../' . $imgRow['image_path']);
                }
            }
            $conn->query("DELETE FROM transparency_donation_images WHERE donation_id=$id");
            $stmt = $conn->prepare("DELETE FROM transparency_donations WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $alertType = 'success';
            $alertMsg = 'Assistance record deleted.';
        }

        // Delete single donation image
        elseif ($action === 'delete_donation_image') {
            $imgId = (int)($_POST['img_id'] ?? 0);
            $stmtSel = $conn->prepare("SELECT * FROM transparency_donation_images WHERE id=?");
            $stmtSel->bind_param('i', $imgId);
            $stmtSel->execute();
            $imgRow = $stmtSel->get_result()->fetch_assoc();
            $stmtSel->close();
            if ($imgRow) {
                if ($imgRow['image_path'] && file_exists('../../' . $imgRow['image_path'])) {
                    @unlink('../../' . $imgRow['image_path']);
                }
                $stmtDel = $conn->prepare("DELETE FROM transparency_donation_images WHERE id=?");
                $stmtDel->bind_param('i', $imgId);
                $stmtDel->execute();
                $stmtDel->close();
            }
            // Return JSON for AJAX
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            exit;
        }

        // ── Community Achievements ──────────────────────────────────────
        elseif ($action === 'add_achievement' || $action === 'edit_achievement') {
            $ach_title = trim($_POST['ach_title'] ?? '');
            $ach_caption = trim($_POST['ach_caption'] ?? '');
            $ach_tag = trim($_POST['ach_tag'] ?? '');
            $ach_sort = (int)($_POST['ach_sort'] ?? 0);
            $ach_id = (int)($_POST['ach_id'] ?? 0);

            if ($ach_title === '') throw new Exception('Title is required.');

            $uploadDir   = '../../uploads/achievements/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $allowed      = ['jpg','jpeg','jfif','png','webp','gif','bmp','tiff','tif','heic','heif','avif'];
            $allowedMimes = ['image/jpeg','image/png','image/webp','image/gif','image/bmp','image/tiff','image/heic','image/heif','image/avif'];

            $hasNewImages = !empty($_FILES['ach_images']['name'][0]);

            if ($action === 'add_achievement') {
                if (!$hasNewImages) throw new Exception('At least one image is required.');

                // Insert achievement row (image_path is now just a placeholder)
                $firstPath = '';
                $stmt = $conn->prepare("INSERT INTO community_achievements (title, caption, tag, image_path, sort_order) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('ssssi', $ach_title, $ach_caption, $ach_tag, $firstPath, $ach_sort);
                $stmt->execute();
                $newAchId = $conn->insert_id;
                $stmt->close();

                // Upload images
                $imgStmt = $conn->prepare("INSERT INTO community_achievement_images (achievement_id, image_path, sort_order) VALUES (?, ?, ?)");
                foreach ($_FILES['ach_images']['name'] as $i => $fname) {
                    if (empty($fname) || $_FILES['ach_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                    $ext  = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                    $mime = mime_content_type($_FILES['ach_images']['tmp_name'][$i]);
                    if (!in_array($ext, $allowed) && !in_array($mime, $allowedMimes)) continue;
                    if (in_array($ext, ['jfif','heic','heif'])) $ext = 'jpg';
                    if ($ext === 'avif') $ext = 'webp';
                    $fileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    if (move_uploaded_file($_FILES['ach_images']['tmp_name'][$i], $uploadDir . $fileName)) {
                        $imgPath = 'uploads/achievements/' . $fileName;
                        $imgStmt->bind_param('isi', $newAchId, $imgPath, $i);
                        $imgStmt->execute();
                        if ($firstPath === '') {
                            $firstPath = $imgPath;
                            $upd = $conn->prepare("UPDATE community_achievements SET image_path=? WHERE id=?");
                            $upd->bind_param('si', $firstPath, $newAchId);
                            $upd->execute(); $upd->close();
                        }
                    }
                }
                $imgStmt->close();
                $alertType = 'success'; $alertMsg = 'Achievement added successfully.';

            } else {
                // Edit: update main record
                $stmt = $conn->prepare("UPDATE community_achievements SET title=?, caption=?, tag=?, sort_order=? WHERE id=?");
                $stmt->bind_param('sssii', $ach_title, $ach_caption, $ach_tag, $ach_sort, $ach_id);
                $stmt->execute(); $stmt->close();

                // Append new images
                if ($hasNewImages) {
                    $sortRes  = $conn->query("SELECT COALESCE(MAX(sort_order),0) as mx FROM community_achievement_images WHERE achievement_id=$ach_id");
                    $sortStart = $sortRes ? (int)$sortRes->fetch_assoc()['mx'] + 1 : 0;
                    $imgStmt  = $conn->prepare("INSERT INTO community_achievement_images (achievement_id, image_path, sort_order) VALUES (?, ?, ?)");
                    foreach ($_FILES['ach_images']['name'] as $i => $fname) {
                        if (empty($fname) || $_FILES['ach_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                        $ext  = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                        $mime = mime_content_type($_FILES['ach_images']['tmp_name'][$i]);
                        if (!in_array($ext, $allowed) && !in_array($mime, $allowedMimes)) continue;
                        if (in_array($ext, ['jfif','heic','heif'])) $ext = 'jpg';
                        if ($ext === 'avif') $ext = 'webp';
                        $fileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        if (move_uploaded_file($_FILES['ach_images']['tmp_name'][$i], $uploadDir . $fileName)) {
                            $imgPath = 'uploads/achievements/' . $fileName;
                            $sortVal = $sortStart + $i;
                            $imgStmt->bind_param('isi', $ach_id, $imgPath, $sortVal);
                            $imgStmt->execute();
                        }
                    }
                    $imgStmt->close();
                    // Update image_path to first image if empty
                    $conn->query("UPDATE community_achievements ca SET ca.image_path = (SELECT ci.image_path FROM community_achievement_images ci WHERE ci.achievement_id=ca.id ORDER BY ci.sort_order,ci.id LIMIT 1) WHERE ca.id=$ach_id AND (ca.image_path='' OR ca.image_path IS NULL)");
                }
                $alertType = 'success'; $alertMsg = 'Achievement updated successfully.';
            }
        }

        // Delete single achievement image (AJAX)
        elseif ($action === 'delete_achievement_image') {
            $imgId = (int)($_POST['img_id'] ?? 0);
            $stmtSel = $conn->prepare("SELECT * FROM community_achievement_images WHERE id=?");
            $stmtSel->bind_param('i', $imgId);
            $stmtSel->execute();
            $imgRow = $stmtSel->get_result()->fetch_assoc();
            $stmtSel->close();
            if ($imgRow) {
                if ($imgRow['image_path'] && file_exists('../../' . $imgRow['image_path'])) {
                    @unlink('../../' . $imgRow['image_path']);
                }
                $stmtDel = $conn->prepare("DELETE FROM community_achievement_images WHERE id=?");
                $stmtDel->bind_param('i', $imgId);
                $stmtDel->execute(); $stmtDel->close();
                // Update main image_path to next available image
                $achId = (int)$imgRow['achievement_id'];
                $conn->query("UPDATE community_achievements ca SET ca.image_path = COALESCE((SELECT ci.image_path FROM community_achievement_images ci WHERE ci.achievement_id=$achId ORDER BY ci.sort_order,ci.id LIMIT 1),'') WHERE ca.id=$achId");
            }
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            exit;
        }

        elseif ($action === 'delete_achievement') {
            $id = (int)($_POST['id'] ?? 0);
            $userId = (int)($_SESSION['user_id'] ?? 0);
            // Fetch achievement to archive
            $stmtSel = $conn->prepare("SELECT * FROM community_achievements WHERE id=?");
            $stmtSel->bind_param('i', $id);
            $stmtSel->execute();
            $achRow = $stmtSel->get_result()->fetch_assoc();
            $stmtSel->close();
            if ($achRow) {
                $aTitle  = (string)($achRow['title'] ?? '');
                $aCapt   = (string)($achRow['caption'] ?? '');
                $aTag    = (string)($achRow['tag'] ?? '');
                $aImg    = (string)($achRow['image_path'] ?? '');
                $aSort   = (int)($achRow['sort_order'] ?? 0);
                $aOrig   = (int)($achRow['id']);
                $stmtIns = $conn->prepare("INSERT INTO community_achievements_archive (original_id, title, caption, tag, image_path, sort_order, archived_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtIns->bind_param('issssii', $aOrig, $aTitle, $aCapt, $aTag, $aImg, $aSort, $userId);
                $stmtIns->execute(); $stmtIns->close();
                $stmtDel = $conn->prepare("DELETE FROM community_achievements WHERE id=?");
                $stmtDel->bind_param('i', $id);
                $stmtDel->execute(); $stmtDel->close();
            }
            $alertType = 'success'; $alertMsg = 'Achievement archived.';
        }

        // Archive Beneficiary
        elseif ($action === 'archive_beneficiary') {
            $id = (int)($_POST['id'] ?? 0);
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $conn->begin_transaction();
            $stmtSel = $conn->prepare("SELECT * FROM transparency_beneficiaries WHERE id=?");
            $stmtSel->bind_param('i', $id);
            $stmtSel->execute();
            $ben = $stmtSel->get_result()->fetch_assoc();
            $stmtSel->close();
            if ($ben) {
                $bOrigId    = (int)($ben['id'] ?? 0);
                $bProgId    = (int)($ben['program_id'] ?? 0);
                $bName      = (string)($ben['name'] ?? '');
                $bAsstType  = (string)($ben['assistance_type'] ?? '');
                $bAmount    = (float)($ben['amount_value'] ?? 0);
                $bQty       = (int)($ben['quantity'] ?? 0);
                $bDate      = $ben['date_assisted'] ?? null;
                $bStatus    = (string)($ben['status'] ?? '');
                $bBarangay  = (string)($ben['barangay'] ?? '');
                $bStory     = (string)($ben['short_story'] ?? '');
                $bFeatured  = (int)($ben['featured'] ?? 0);
                $bCreated   = (string)($ben['created_at'] ?? '');
                $bUpdated   = $ben['updated_at'] ?? null;
                $stmtIns = $conn->prepare("INSERT INTO transparency_beneficiaries_archive 
                    (original_id, program_id, name, assistance_type, amount_value, quantity, date_assisted, status, barangay, short_story, featured, created_at, updated_at, archived_by, archived_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmtIns->bind_param('iissdissssissi',
                    $bOrigId, $bProgId, $bName, $bAsstType, $bAmount, $bQty,
                    $bDate, $bStatus, $bBarangay, $bStory, $bFeatured,
                    $bCreated, $bUpdated, $userId);
                $stmtIns->execute();
                $stmtIns->close();
                $stmtDel = $conn->prepare("DELETE FROM transparency_beneficiaries WHERE id=?");
                $stmtDel->bind_param('i', $id);
                $stmtDel->execute();
                $stmtDel->close();
                $conn->commit();
                $alertType = 'success';
                $alertMsg = 'Impact story archived successfully.';
            } else {
                $conn->rollback();
                throw new Exception('Impact story not found.');
            }
        }
    }
} catch (Exception $ex) {
    $alertType = 'error';
    $alertMsg = $ex->getMessage();
}

// Fetch statistics
$stats = ['programs' => 0, 'active' => 0, 'total_assistance' => 0, 'sources' => 0, 'beneficiaries' => 0, 'featured' => 0];

$res = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active FROM transparency_campaigns");
if ($res && $row = $res->fetch_assoc()) {
    $stats['programs'] = (int)$row['total'];
    $stats['active'] = (int)$row['active'];
}

<<<<<<< HEAD
$res = $conn->query("SELECT SUM(amount) as total FROM transparency_donations WHERE status='confirmed'");
if ($res && $row = $res->fetch_assoc()) {
    $stats['total_assistance'] = (float)($row['total'] ?? 0);
=======
$res = $conn->query("SELECT
    SUM(CASE WHEN COALESCE(donation_type,'cash')='cash' THEN amount ELSE 0 END) as cash_total,
    SUM(CASE WHEN donation_type='in_kind' THEN amount ELSE 0 END) as inkind_total
    FROM transparency_donations WHERE status='confirmed'");
if ($res && $row = $res->fetch_assoc()) {
    $stats['total_assistance'] = (float)($row['cash_total']  ?? 0);
    $stats['total_inkind']     = (float)($row['inkind_total'] ?? 0);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
}

$res = $conn->query("SELECT COUNT(DISTINCT donor_name) as total FROM transparency_donations");
if ($res && $row = $res->fetch_assoc()) {
    $stats['sources'] = (int)$row['total'];
}

$res = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN featured=1 THEN 1 ELSE 0 END) as featured FROM transparency_beneficiaries");
if ($res && $row = $res->fetch_assoc()) {
    $stats['beneficiaries'] = (int)$row['total'];
    $stats['featured'] = (int)$row['featured'];
}

// Fetch programs
$programs = [];
$res = $conn->query("SELECT c.*, COALESCE(SUM(d.amount),0) as received 
    FROM transparency_campaigns c 
    LEFT JOIN transparency_donations d ON c.id = d.campaign_id AND d.status='confirmed'
    GROUP BY c.id 
    ORDER BY c.created_at DESC");
while ($res && $row = $res->fetch_assoc()) {
    $programs[] = $row;
}

// Ensure multiple-images table exists (before querying it)
$conn->query("CREATE TABLE IF NOT EXISTS transparency_donation_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    donation_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_donation_id (donation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Fetch assistance records
$assistance = [];
$res = $conn->query("SELECT d.* FROM transparency_donations d ORDER BY d.date_received DESC LIMIT 50");
while ($res && $row = $res->fetch_assoc()) {
    $row['images'] = [];
<<<<<<< HEAD
=======
    $row['items']  = [];
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    $assistance[$row['id']] = $row;
}
// Fetch images for all loaded donations
if (!empty($assistance)) {
    $ids = implode(',', array_keys($assistance));
    $imgRes = $conn->query("SELECT * FROM transparency_donation_images WHERE donation_id IN ($ids) ORDER BY sort_order, id");
    while ($imgRes && $imgRow = $imgRes->fetch_assoc()) {
        $assistance[$imgRow['donation_id']]['images'][] = $imgRow;
    }
<<<<<<< HEAD
=======
    // Fetch in-kind items
    $itemRes = $conn->query("SELECT * FROM transparency_donation_items WHERE donation_id IN ($ids) ORDER BY id");
    while ($itemRes && $itemRow = $itemRes->fetch_assoc()) {
        $assistance[$itemRow['donation_id']]['items'][] = $itemRow;
    }
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
}
$assistance = array_values($assistance);

// Fetch beneficiaries
$beneficiaries = [];
$res = $conn->query("SELECT b.*, p.name as program_name 
    FROM transparency_beneficiaries b 
    LEFT JOIN transparency_programs p ON b.program_id = p.id 
    ORDER BY b.date_assisted DESC LIMIT 50");
while ($res && $row = $res->fetch_assoc()) {
    $beneficiaries[] = $row;
}

// Fetch impact metrics
$metrics = [];
$res = $conn->query("SELECT * FROM transparency_impact_metrics ORDER BY display_order, id");
while ($res && $row = $res->fetch_assoc()) {
    $metrics[] = $row;
}



// Source types for dropdown
$sourceTypes = ['DOLE', 'LGU', 'NGO', 'Private', 'Membership', 'Others'];
$assistanceTypes = ['Livelihood', 'Relief Goods', 'Training', 'Equipment', 'Financial', 'Medical', 'Educational', 'Other'];
$beneficiaryStatuses = ['served', 'in-progress', 'pending'];

// Ensure image_path column exists in transparency_donations
try {
    $colCheck = $conn->query("SHOW COLUMNS FROM transparency_donations LIKE 'image_path'");
    if ($colCheck && $colCheck->num_rows === 0) {
        $conn->query("ALTER TABLE transparency_donations ADD COLUMN image_path VARCHAR(500) DEFAULT NULL");
    }
} catch (Throwable) {}

// Migrate existing image_path rows that haven't been migrated yet
try {
    $migRes = $conn->query("SELECT id, image_path FROM transparency_donations WHERE image_path IS NOT NULL AND image_path != '' AND id NOT IN (SELECT DISTINCT donation_id FROM transparency_donation_images)");
    if ($migRes) {
        $migStmt = $conn->prepare("INSERT INTO transparency_donation_images (donation_id, image_path) VALUES (?, ?)");
        while ($mRow = $migRes->fetch_assoc()) {
            $migStmt->bind_param('is', $mRow['id'], $mRow['image_path']);
            $migStmt->execute();
        }
        $migStmt->close();
    }
} catch (Throwable) {}

<<<<<<< HEAD
=======
// Fetch expenses
$expenses = [];
$res = $conn->query("SELECT e.*, d.donor_name as linked_source
    FROM transparency_expenses e
    LEFT JOIN transparency_donations d ON e.donation_id = d.id
    ORDER BY e.expense_date DESC, e.id DESC LIMIT 100");
while ($res && $row = $res->fetch_assoc()) {
    $expenses[] = $row;
}

// Total expenses stat
$expTotal = 0;
$resExp = $conn->query("SELECT SUM(amount) as total FROM transparency_expenses");
if ($resExp && $rowExp = $resExp->fetch_assoc()) {
    $expTotal = (float)($rowExp['total'] ?? 0);
}

>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
// Ensure community_achievements table exists
$conn->query("CREATE TABLE IF NOT EXISTS community_achievements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    caption TEXT DEFAULT NULL,
    tag VARCHAR(100) DEFAULT NULL,
    image_path VARCHAR(500) NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ensure community_achievements_archive table exists
$conn->query("CREATE TABLE IF NOT EXISTS community_achievements_archive (
    id INT PRIMARY KEY AUTO_INCREMENT,
    original_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    caption TEXT DEFAULT NULL,
    tag VARCHAR(100) DEFAULT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    archived_by INT DEFAULT NULL,
    archived_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ensure community_achievement_images table exists (multi-image support)
$conn->query("CREATE TABLE IF NOT EXISTS community_achievement_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    achievement_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_achievement_id (achievement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Migrate existing single image_path to images table
try {
    $migRes = $conn->query("SELECT id, image_path FROM community_achievements WHERE image_path != '' AND id NOT IN (SELECT DISTINCT achievement_id FROM community_achievement_images)");
    if ($migRes) {
        $migStmt = $conn->prepare("INSERT INTO community_achievement_images (achievement_id, image_path) VALUES (?, ?)");
        while ($mRow = $migRes->fetch_assoc()) {
            $migStmt->bind_param('is', $mRow['id'], $mRow['image_path']);
            $migStmt->execute();
        }
        $migStmt->close();
    }
} catch (Throwable) {}

// Fetch achievements for admin table (with images)
$achievements = [];
$res = $conn->query("SELECT * FROM community_achievements ORDER BY sort_order ASC, created_at DESC");
while ($res && $row = $res->fetch_assoc()) {
    $row['images'] = [];
    $achievements[$row['id']] = $row;
}
if (!empty($achievements)) {
    $achIds = implode(',', array_keys($achievements));
    $imgRes = $conn->query("SELECT * FROM community_achievement_images WHERE achievement_id IN ($achIds) ORDER BY sort_order, id");
    while ($imgRes && $imgRow = $imgRes->fetch_assoc()) {
        $achievements[$imgRow['achievement_id']]['images'][] = $imgRow;
    }
}
$achievements = array_values($achievements);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transparency Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<<<<<<< HEAD
    <style>
        body { background: #f8f9fa; }
        .main-content { padding: 24px; }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
=======
    <link rel="stylesheet" href="../../css/admin-theme.css">
<style>
        body { background: #f8f9fa; }
        .main-content { padding: 24px; }
        .page-header {
            background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            height: 100%;
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .table-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 24px;
        }
        .badge-status { padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-active { background: #d1fae5; color: #065f46; }
<<<<<<< HEAD
        .badge-completed { background: #dbeafe; color: #1e40af; }
        .badge-planned { background: #fef3c7; color: #92400e; }
        .badge-served { background: #d1fae5; color: #065f46; }
        .badge-in-progress { background: #dbeafe; color: #1e40af; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .progress { height: 8px; border-radius: 4px; }
        .nav-tabs .nav-link { color: #495057; }
        .nav-tabs .nav-link.active { background: #667eea; color: white; border-color: #667eea; }
        .featured-star { color: #f59e0b; }
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
=======
        .badge-completed { background: #dbeafe; color: #1a6d8a; }
        .badge-planned { background: #fef3c7; color: #92400e; }
        .badge-served { background: #d1fae5; color: #065f46; }
        .badge-in-progress { background: #dbeafe; color: #1a6d8a; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .progress { height: 8px; border-radius: 4px; }
        .nav-tabs .nav-link { color: #495057; }
        .nav-tabs .nav-link.active { background: #2E86AB; color: white; border-color: #2E86AB; }
        .featured-star { color: #f59e0b; }
        .metric-card {
            background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
            color: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .metric-value { font-size: 2.5rem; font-weight: 700; }
        .metric-label { font-size: 0.9rem; opacity: 0.9; }
    </style>
</head>
<body>
<?php include('../navbar.php'); ?>

<div class="main-content">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2><i class="bi bi-shield-check"></i> Transparency Dashboard</h2>
                <p class="mb-0">Manage programs, assistance, beneficiaries, and impact metrics.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="transparency_reports.php" class="btn btn-light">
                    <i class="bi bi-file-earmark-text"></i> View Reports
                </a>


            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($alertMsg): ?>
    <div class="alert alert-<?= $alertType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
        <?= e($alertMsg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card d-flex align-items-center gap-3">
<<<<<<< HEAD
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <h4 class="mb-0">₱<?= number_format($stats['total_assistance'], 0) ?></h4>
                    <small class="text-muted">Total Assistance</small>
=======
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div>
                    <h4 class="mb-0">₱<?= number_format($stats['total_assistance'], 0) ?></h4>
                    <small class="text-muted">Cash Assistance</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <h4 class="mb-0">₱<?= number_format($stats['total_inkind'] ?? 0, 0) ?></h4>
                    <small class="text-muted">In-Kind (Gamit)</small>
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <h4 class="mb-0"><?= number_format($stats['sources']) ?></h4>
                    <small class="text-muted">Partners</small>
                </div>
            </div>
        </div>
<<<<<<< HEAD
        <div class="col-md-2">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <h4 class="mb-0"><?= number_format($stats['beneficiaries']) ?></h4>
                    <small class="text-muted">Beneficiaries</small>
=======
        <div class="col-md-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <h4 class="mb-0">₱<?= number_format($expTotal, 0) ?></h4>
                    <small class="text-muted">Total Expenses</small>
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4" id="mainTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="assistance-tab" data-bs-toggle="tab" data-bs-target="#assistance" type="button">
                <i class="bi bi-cash-coin me-1"></i> Assistance
            </button>
        </li>
        <li class="nav-item" role="presentation">
<<<<<<< HEAD
=======
            <button class="nav-link" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expenses" type="button">
                <i class="bi bi-receipt me-1"></i> Expenses
            </button>
        </li>
        <li class="nav-item" role="presentation">
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
            <button class="nav-link" id="achievements-tab" data-bs-toggle="tab" data-bs-target="#achievements" type="button">
                <i class="bi bi-image me-1"></i> Community Achievements
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="mainTabContent">
        <!-- Assistance Tab -->
        <div class="tab-pane fade show active" id="assistance" role="tabpanel">
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Assistance Received</h5>
                    <?php if ($canEdit): ?>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#assistanceModal" onclick="resetAssistanceForm()">
                        <i class="bi bi-plus"></i> Add
                    </button>
                    <?php endif; ?>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Source</th>
                                <th>Type</th>
<<<<<<< HEAD
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Image</th>
=======
                                <th>Assistance Type</th>
                                <th>Amount / Value</th>
                                <th>Date</th>
                                <th>Items / Images</th>
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                                <?php if ($canEdit): ?><th>Actions</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
<<<<<<< HEAD
                            <?php foreach ($assistance as $a): ?>
                            <tr>
                                <td><strong><?= e($a['donor_name']) ?></strong></td>
                                <td><span class="badge bg-secondary"><?= e($a['donor_type']) ?></span></td>
                                <td class="text-success fw-bold">₱<?= number_format($a['amount'], 0) ?></td>
                                <td><?= $a['date_received'] ? date('M d, Y', strtotime($a['date_received'])) : '-' ?></td>
                                <td>
                                    <?php if (!empty($a['images'])): ?>
=======
                            <?php foreach ($assistance as $a): 
                                $isInKind = ($a['donation_type'] ?? 'cash') === 'in_kind';
                            ?>
                            <tr>
                                <td><strong><?= e($a['donor_name']) ?></strong></td>
                                <td><span class="badge bg-secondary"><?= e($a['donor_type']) ?></span></td>
                                <td>
                                    <?php if ($isInKind): ?>
                                    <span class="badge bg-primary"><i class="bi bi-box-seam me-1"></i>Gamit / In-Kind</span>
                                    <?php else: ?>
                                    <span class="badge bg-success"><i class="bi bi-cash-coin me-1"></i>Pera / Cash</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-success fw-bold">₱<?= number_format($a['amount'], 0) ?></td>
                                <td><?= $a['date_received'] ? date('M d, Y', strtotime($a['date_received'])) : '-' ?></td>
                                <td>
                                    <?php if ($isInKind && !empty($a['items'])): ?>
                                    <ul class="list-unstyled mb-0 small">
                                        <?php foreach ($a['items'] as $it): ?>
                                        <li class="d-flex align-items-center gap-2 mb-1">
                                            <?php if ($it['photo']): ?>
                                            <a href="../../<?= e($it['photo']) ?>" target="_blank">
                                                <img src="../../<?= e($it['photo']) ?>" style="height:32px;width:38px;object-fit:cover;border-radius:4px;" onerror="this.style.display='none'">
                                            </a>
                                            <?php endif; ?>
                                            <span>
                                                <strong><?= e($it['item_name']) ?></strong>
                                                — <?= number_format($it['quantity'], 0) ?> <?= e($it['unit']) ?>
                                                <span class="text-muted">(₱<?= number_format($it['total_value'] ?? $it['quantity']*$it['unit_value'], 2) ?>)</span>
                                            </span>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php elseif (!empty($a['images'])): ?>
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($a['images'] as $img): ?>
                                        <a href="../../<?= e($img['image_path']) ?>" target="_blank">
                                            <img src="../../<?= e($img['image_path']) ?>" alt="image"
                                                 style="height:40px; width:48px; object-fit:cover; border-radius:5px; border:1px solid #dee2e6;"
                                                 onerror="this.style.display='none'">
                                        </a>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($canEdit): ?>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editAssistance(<?= htmlspecialchars(json_encode($a)) ?>)" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="confirmArchive('assistance', <?= $a['id'] ?>, '<?= addslashes($a['donor_name']) ?>')" title="Archive">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($assistance)): ?>
                            <tr><td colspan="<?= $canEdit ? 6 : 5 ?>" class="text-center text-muted py-4">No assistance records yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

<<<<<<< HEAD
=======
        <!-- ── Expenses Tab ── -->
        <div class="tab-pane fade" id="expenses" role="tabpanel">
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Expenses / Disbursements</h5>
                        <small class="text-muted">Kung saan napunta ang pera — purchases, payments, disbursements.</small>
                    </div>
                    <?php if ($canEdit): ?>
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#expenseModal" onclick="resetExpenseForm()">
                        <i class="bi bi-plus"></i> Add Expense
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Summary strip -->
                <div class="d-flex gap-3 mb-3 flex-wrap">
                    <div class="px-3 py-2 rounded" style="background:#fff5f5; border:1px solid #fecaca;">
                        <small class="text-muted d-block">Total Expenses</small>
                        <strong class="text-danger">₱<?= number_format($expTotal, 2) ?></strong>
                    </div>
                    <div class="px-3 py-2 rounded" style="background:#f0fdf4; border:1px solid #bbf7d0;">
                        <small class="text-muted d-block">Cash Received</small>
                        <strong class="text-success">₱<?= number_format($stats['total_assistance'], 2) ?></strong>
                    </div>
                    <?php if (($stats['total_inkind'] ?? 0) > 0): ?>
                    <div class="px-3 py-2 rounded" style="background:#eff6ff; border:1px solid #bfdbfe;">
                        <small class="text-muted d-block">In-Kind (Gamit)</small>
                        <strong class="text-primary">₱<?= number_format($stats['total_inkind'], 2) ?></strong>
                    </div>
                    <?php endif; ?>
                    <div class="px-3 py-2 rounded" style="background:#eff6ff; border:1px solid #bfdbfe;">
                        <?php $cashBalance = $stats['total_assistance'] - $expTotal; ?>
                        <small class="text-muted d-block">Cash Balance</small>
                        <strong class="<?= $cashBalance >= 0 ? 'text-primary' : 'text-danger' ?>">₱<?= number_format($cashBalance, 2) ?></strong>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title / Purpose</th>
                                <th>Category</th>
                                <th>Paid To</th>
                                <th>Date</th>
                                <th>Linked Source</th>
                                <th class="text-end">Amount</th>
                                <?php if ($canEdit): ?><th>Actions</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expenses as $exp): ?>
                            <tr>
                                <td>
                                    <strong><?= e($exp['title']) ?></strong>
                                    <?php if ($exp['notes']): ?>
                                    <br><small class="text-muted"><?= e(substr($exp['notes'], 0, 60)) ?><?= strlen($exp['notes']) > 60 ? '…' : '' ?></small>
                                    <?php endif; ?>
                                    <?php if ($exp['reference_code']): ?>
                                    <br><small class="text-muted"><i class="bi bi-hash"></i><?= e($exp['reference_code']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($exp['category']): ?>
                                    <span class="badge bg-secondary"><?= e($exp['category']) ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($exp['paid_to'] ?: '—') ?></td>
                                <td><?= $exp['expense_date'] ? date('M d, Y', strtotime($exp['expense_date'])) : '—' ?></td>
                                <td>
                                    <?php if ($exp['linked_source']): ?>
                                    <small class="text-success"><i class="bi bi-link-45deg"></i><?= e($exp['linked_source']) ?></small>
                                    <?php else: ?>
                                    <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-danger fw-bold">₱<?= number_format($exp['amount'], 2) ?></td>
                                <?php if ($canEdit): ?>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editExpense(<?= htmlspecialchars(json_encode($exp)) ?>)" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteExpense(<?= $exp['id'] ?>, '<?= addslashes($exp['title']) ?>')" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($expenses)): ?>
                            <tr><td colspan="<?= $canEdit ? 7 : 6 ?>" class="text-center text-muted py-4">
                                <i class="bi bi-receipt fs-2 d-block opacity-25 mb-2"></i>
                                No expenses recorded yet.
                            </td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($expenses)): ?>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="<?= $canEdit ? 5 : 4 ?>" class="text-end"><strong>TOTAL:</strong></td>
                                <td class="text-end text-danger fw-bold">₱<?= number_format($expTotal, 2) ?></td>
                                <?php if ($canEdit): ?><td></td><?php endif; ?>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
        <!-- ── Community Achievements Tab ── -->
        <div class="tab-pane fade" id="achievements" role="tabpanel">
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0"><i class="bi bi-image me-2"></i>Community Achievements</h5>
                        <small class="text-muted">Photo gallery of products, outcomes, and community milestones shown on the public page.</small>
                    </div>
                    <?php if ($canEdit): ?>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#achievementModal" onclick="resetAchievementForm()">
                        <i class="bi bi-plus"></i> Add Photo
                    </button>
                    <?php endif; ?>
                </div>

                <?php if (empty($achievements)): ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-images fs-1 opacity-25 d-block mb-2"></i>
                    No achievements yet. Click "Add Photo" to upload the first one.
                </div>
                <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($achievements as $ach):
                        $achImgs = $ach['images'];
                        $firstImg = !empty($achImgs) ? $achImgs[0]['image_path'] : ($ach['image_path'] ?? '');
                        $extraCount = max(0, count($achImgs) - 1);
                    ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="position-relative">
                                <img src="../../<?= e($firstImg) ?>"
                                     class="card-img-top"
                                     style="height:160px; object-fit:cover;"
                                     alt="<?= e($ach['title']) ?>"
                                     onerror="this.src='https://placehold.co/400x200?text=No+Image'">
                                <?php if ($extraCount > 0): ?>
                                <span class="position-absolute bottom-0 end-0 m-1 badge bg-dark bg-opacity-75">
                                    +<?= $extraCount ?> more
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-2">
                                <?php if (!empty($ach['tag'])): ?>
                                <span class="badge bg-success bg-opacity-10 text-success mb-1" style="font-size:.7rem;"><?= e($ach['tag']) ?></span>
                                <?php endif; ?>
                                <p class="fw-semibold mb-0" style="font-size:.85rem;"><?= e($ach['title']) ?></p>
                                <?php if (!empty($ach['caption'])): ?>
                                <p class="text-muted mb-0" style="font-size:.75rem;"><?= e(substr($ach['caption'],0,60)) ?><?= strlen($ach['caption'])>60?'…':'' ?></p>
                                <?php endif; ?>
                            </div>
                            <?php if ($canEdit): ?>
                            <div class="card-footer p-1 d-flex gap-1 justify-content-end bg-transparent border-0">
                                <button class="btn btn-sm btn-outline-primary" onclick="editAchievement(<?= htmlspecialchars(json_encode($ach)) ?>)" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" onclick="confirmDelete('achievement', <?= $ach['id'] ?>, '<?= addslashes($ach['title']) ?>')" title="Archive">
                                    <i class="bi bi-archive"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>


    </div>
</div>

<?php if ($canEdit): ?>
<!-- Assistance Modal -->
<div class="modal fade" id="assistanceModal" tabindex="-1">
<<<<<<< HEAD
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
=======
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="assistanceForm">
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" id="assistanceAction" value="add_assistance">
                <input type="hidden" name="id" id="assistanceId">
                <input type="hidden" name="active_tab" value="assistance">
                <div class="modal-header">
                    <h5 class="modal-title" id="assistanceModalTitle">Record Assistance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
<<<<<<< HEAD
                    <div class="mb-3">
                        <label class="form-label">Source Name</label>
                        <input type="text" name="source_name" id="assistanceSource" class="form-control" placeholder="e.g., DOLE Region IV" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Source Type</label>
                        <select name="source_type" id="assistanceType" class="form-select">
                            <?php foreach ($sourceTypes as $type): ?>
                            <option value="<?= $type ?>"><?= $type ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" name="amount" id="assistanceAmount" class="form-control" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date Received</label>
                            <input type="date" name="date_received" id="assistanceDate" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference/Receipt No.</label>
                        <input type="text" name="reference" id="assistanceRef" class="form-control">
                    </div>
                    <div class="mb-3">
=======

                    <!-- Step 1: Choose type -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Anong uri ng tulong?</label>
                        <div class="d-flex gap-3">
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="donation_type" id="typeCash" value="cash" checked>
                                <label class="btn btn-outline-success w-100 py-3" for="typeCash">
                                    <i class="bi bi-cash-coin d-block fs-3 mb-1"></i>
                                    <strong>Pera / Cash</strong><br>
                                    <small class="text-muted fw-normal">Monetary assistance</small>
                                </label>
                            </div>
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="donation_type" id="typeInKind" value="in_kind">
                                <label class="btn btn-outline-primary w-100 py-3" for="typeInKind">
                                    <i class="bi bi-box-seam d-block fs-3 mb-1"></i>
                                    <strong>Gamit / In-Kind</strong><br>
                                    <small class="text-muted fw-normal">Goods, equipment, supplies</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr class="mb-3">

                    <!-- Common fields -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Source Name <span class="text-danger">*</span></label>
                            <input type="text" name="source_name" id="assistanceSource" class="form-control" placeholder="e.g., DOLE Region IV" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Source Type</label>
                            <select name="source_type" id="assistanceType" class="form-select">
                                <?php foreach ($sourceTypes as $type): ?>
                                <option value="<?= $type ?>"><?= $type ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date Received</label>
                            <input type="date" name="date_received" id="assistanceDate" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reference/Receipt No.</label>
                            <input type="text" name="reference" id="assistanceRef" class="form-control">
                        </div>
                    </div>

                    <!-- Cash section -->
                    <div id="cashSection">
                        <div class="mb-3">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" name="amount" id="assistanceAmount" class="form-control" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <!-- In-Kind section -->
                    <div id="inKindSection" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0 fw-semibold">Items / Gamit na Ibinigay <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItemRow()">
                                <i class="bi bi-plus-lg"></i> Add Item
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item Name / Description</th>
                                        <th style="width:80px;">Qty</th>
                                        <th style="width:80px;">Unit</th>
                                        <th style="width:110px;">Unit Value (₱)</th>
                                        <th style="width:110px;">Subtotal</th>
                                        <th style="width:80px;">Photo</th>
                                        <th style="width:40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <!-- rows added by JS -->
                                </tbody>
                                <tfoot>
                                    <tr class="table-success">
                                        <td colspan="4" class="text-end fw-semibold">Estimated Total Value:</td>
                                        <td class="fw-bold text-success" id="itemsTotal">₱0.00</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <small class="text-muted">Pwedeng mag-attach ng picture per item (optional).</small>
                    </div>

                    <div class="mb-3 mt-3">
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="assistanceNotes" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
<<<<<<< HEAD
                        <label class="form-label">Photos / Images <small class="text-muted">(Optional — pwedeng marami, e.g., goods, tools, equipment)</small></label>
                        <input type="file" name="assistance_images[]" id="assistanceImageInput" class="form-control" accept="image/*" multiple>
                        <!-- Preview for newly selected files -->
                        <div id="assistanceNewPreviewWrap" class="d-flex flex-wrap gap-2 mt-2"></div>
                        <!-- Existing images (edit mode) -->
=======
                        <label class="form-label">Photos / Images <small class="text-muted">(Optional — overall photos)</small></label>
                        <input type="file" name="assistance_images[]" id="assistanceImageInput" class="form-control" accept="image/*" multiple>
                        <div id="assistanceNewPreviewWrap" class="d-flex flex-wrap gap-2 mt-2"></div>
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
                        <div id="assistanceExistingImages" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Achievement Modal -->
<div class="modal fade" id="achievementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" id="achAction" value="add_achievement">
                <input type="hidden" name="ach_id" id="achId">
                <input type="hidden" name="active_tab" value="achievements">
                <div class="modal-header">
                    <h5 class="modal-title" id="achModalTitle">Add Community Achievement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Photos / Images <small class="text-muted">(pwedeng marami)</small></label>
                        <input type="file" name="ach_images[]" id="achImagesInput" class="form-control" accept="image/*" multiple>
                        <!-- New file previews -->
                        <div id="achNewPreviewWrap" class="d-flex flex-wrap gap-2 mt-2"></div>
                        <!-- Existing images (edit mode) -->
                        <div id="achExistingImages" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title *</label>
                        <input type="text" name="ach_title" id="achTitle" class="form-control" placeholder="e.g., Dried Fish Products — Barangay Barretto" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Caption</label>
                        <textarea name="ach_caption" id="achCaption" class="form-control" rows="2" placeholder="Short description of the achievement..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-semibold">Tag / Label</label>
                            <input type="text" name="ach_tag" id="achTag" class="form-control" placeholder="e.g., Livelihood Program, Fish Processing">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Sort Order</label>
                            <input type="number" name="ach_sort" id="achSort" class="form-control" value="0" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
<<<<<<< HEAD
=======
// ── Assistance: type toggle ───────────────────────────────────────
(function () {
    function toggleAssistanceType(val) {
        const cashSec   = document.getElementById('cashSection');
        const kindSec   = document.getElementById('inKindSection');
        const amtInput  = document.getElementById('assistanceAmount');
        if (!cashSec || !kindSec) return;
        if (val === 'in_kind') {
            cashSec.style.display  = 'none';
            kindSec.style.display  = '';
            if (amtInput) amtInput.removeAttribute('required');
            if (document.getElementById('itemsBody').children.length === 0) addItemRow();
        } else {
            cashSec.style.display  = '';
            kindSec.style.display  = 'none';
            if (amtInput) amtInput.setAttribute('required','required');
        }
    }
    document.addEventListener('change', function(e) {
        if (e.target.name === 'donation_type') toggleAssistanceType(e.target.value);
    });
    window._toggleAssistanceType = toggleAssistanceType;
})();

let itemRowIndex = 0;
function addItemRow(data) {
    data = data || {};
    const idx  = itemRowIndex++;
    const body = document.getElementById('itemsBody');
    const tr   = document.createElement('tr');
    tr.dataset.idx = idx;
    const photoId = 'itemPhoto_' + idx;
    tr.innerHTML = `
        <td><input type="text" name="item_name[]" class="form-control form-control-sm" placeholder="e.g., Bigas 50kg" value="${data.item_name||''}" required></td>
        <td><input type="number" name="item_qty[]"   class="form-control form-control-sm item-qty"   min="0" step="0.01" value="${data.quantity||1}"     onchange="recalcItems()"></td>
        <td><input type="text"   name="item_unit[]"  class="form-control form-control-sm"             placeholder="sacks" value="${data.unit||''}"></td>
        <td><input type="number" name="item_value[]" class="form-control form-control-sm item-val"   min="0" step="0.01" value="${data.unit_value||0}"   onchange="recalcItems()"></td>
        <td class="fw-bold text-end item-sub">₱${((data.quantity||1)*(data.unit_value||0)).toFixed(2)}</td>
        <td>
            <label class="btn btn-sm btn-outline-secondary p-1" for="${photoId}" title="Attach photo">
                <i class="bi bi-camera"></i>
            </label>
            <input type="file" name="item_photo[]" id="${photoId}" class="d-none" accept="image/*" onchange="previewItemPhoto(this)">
            <div class="item-photo-preview mt-1"></div>
        </td>
        <td><button type="button" class="btn btn-sm btn-outline-danger p-1" onclick="removeItemRow(this)" title="Remove"><i class="bi bi-trash"></i></button></td>`;
    body.appendChild(tr);
    recalcItems();
}

function removeItemRow(btn) {
    btn.closest('tr').remove();
    recalcItems();
}

function recalcItems() {
    let total = 0;
    document.querySelectorAll('#itemsBody tr').forEach(tr => {
        const qty = parseFloat(tr.querySelector('.item-qty')?.value) || 0;
        const val = parseFloat(tr.querySelector('.item-val')?.value) || 0;
        const sub = qty * val;
        const subEl = tr.querySelector('.item-sub');
        if (subEl) subEl.textContent = '₱' + sub.toFixed(2);
        total += sub;
    });
    const totalEl = document.getElementById('itemsTotal');
    if (totalEl) totalEl.textContent = '₱' + total.toFixed(2);
}

function previewItemPhoto(input) {
    const preview = input.closest('td').querySelector('.item-photo-preview');
    preview.innerHTML = '';
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'height:40px;width:48px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;';
            preview.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
// Assistance functions
function resetAssistanceForm() {
    document.getElementById('assistanceAction').value = 'add_assistance';
    document.getElementById('assistanceId').value = '';
    document.getElementById('assistanceModalTitle').textContent = 'Record Assistance';
    document.getElementById('assistanceSource').value = '';
    document.getElementById('assistanceType').value = 'DOLE';
    document.getElementById('assistanceAmount').value = '';
    document.getElementById('assistanceDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('assistanceRef').value = '';
    document.getElementById('assistanceNotes').value = '';
    document.getElementById('assistanceImageInput').value = '';
    document.getElementById('assistanceNewPreviewWrap').innerHTML = '';
    document.getElementById('assistanceExistingImages').innerHTML = '';
<<<<<<< HEAD
=======
    document.getElementById('itemsBody').innerHTML = '';
    itemRowIndex = 0;
    // Default to cash
    document.getElementById('typeCash').checked = true;
    window._toggleAssistanceType('cash');
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
}

function editAssistance(data) {
    document.getElementById('assistanceAction').value = 'edit_assistance';
    document.getElementById('assistanceId').value = data.id;
    document.getElementById('assistanceModalTitle').textContent = 'Edit Assistance';
    document.getElementById('assistanceSource').value = data.donor_name;
    document.getElementById('assistanceType').value = data.donor_type || 'DOLE';
    document.getElementById('assistanceAmount').value = data.amount;
<<<<<<< HEAD
    document.getElementById('assistanceDate').value = data.date_received;
=======
    document.getElementById('assistanceDate').value = data.date_received ? data.date_received.substring(0,10) : '';
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
    document.getElementById('assistanceRef').value = data.reference_code || '';
    document.getElementById('assistanceNotes').value = data.notes || '';
    document.getElementById('assistanceImageInput').value = '';
    document.getElementById('assistanceNewPreviewWrap').innerHTML = '';
<<<<<<< HEAD
=======
    document.getElementById('itemsBody').innerHTML = '';
    itemRowIndex = 0;

    // Set type
    const dType = data.donation_type || 'cash';
    document.getElementById(dType === 'in_kind' ? 'typeInKind' : 'typeCash').checked = true;

    // Populate items BEFORE calling toggle (so toggle doesn't auto-add a blank row)
    if (dType === 'in_kind' && data.items && data.items.length > 0) {
        data.items.forEach(item => addItemRow(item));
    }

    window._toggleAssistanceType(dType);

    // (items already populated above)
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560

    // Show existing images with delete button
    const existingWrap = document.getElementById('assistanceExistingImages');
    existingWrap.innerHTML = '';
    if (data.images && data.images.length > 0) {
        data.images.forEach(img => {
            const wrapper = document.createElement('div');
            wrapper.className = 'position-relative';
            wrapper.id = 'img-wrap-' + img.id;
            wrapper.innerHTML = `
                <img src="../../${img.image_path}" style="height:72px;width:88px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;" onerror="this.style.display='none'">
                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 lh-1"
                    style="width:20px;height:20px;font-size:11px;border-radius:50%;"
                    onclick="deleteDonationImage(${img.id}, this)">
                    <i class="bi bi-x"></i>
                </button>`;
            existingWrap.appendChild(wrapper);
        });
    }

    new bootstrap.Modal(document.getElementById('assistanceModal')).show();
}

function deleteDonationImage(imgId, btn) {
    if (!confirm('Remove this image?')) return;
    const formData = new FormData();
    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
    formData.append('action', 'delete_donation_image');
    formData.append('img_id', imgId);
    fetch('', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                const wrap = document.getElementById('img-wrap-' + imgId);
                if (wrap) wrap.remove();
            }
        })
        .catch(() => alert('Failed to delete image.'));
}

function confirmArchive(type, id, name) {
    Swal.fire({
        title: 'Archive this record?',
        text: `"${name}" will be moved to the archive. You can restore it later.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Archive'
    }).then((result) => {
        if (result.isConfirmed) {
            const tabMap = { assistance: 'assistance', beneficiary: 'assistance' };
            document.getElementById('archiveType').value = 'archive_' + type;
            document.getElementById('archiveId').value = id;
            document.getElementById('archiveTab').value = tabMap[type] || 'programs';
            document.getElementById('archiveForm').submit();
        }
    });
}

function confirmDelete(type, id, name) {
    Swal.fire({
        title: 'Archive this achievement?',
        text: `"${name}" will be moved to the archive. You can restore it later.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Archive'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteType').value = 'delete_' + type;
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    });
}

// Achievement functions
function resetAchievementForm() {
    document.getElementById('achAction').value = 'add_achievement';
    document.getElementById('achId').value = '';
    document.getElementById('achModalTitle').textContent = 'Add Community Achievement';
    document.getElementById('achTitle').value = '';
    document.getElementById('achCaption').value = '';
    document.getElementById('achTag').value = '';
    document.getElementById('achSort').value = '0';
    document.getElementById('achImagesInput').value = '';
    document.getElementById('achNewPreviewWrap').innerHTML = '';
    document.getElementById('achExistingImages').innerHTML = '';
}

function editAchievement(data) {
    document.getElementById('achAction').value = 'edit_achievement';
    document.getElementById('achId').value = data.id;
    document.getElementById('achModalTitle').textContent = 'Edit Achievement';
    document.getElementById('achTitle').value = data.title || '';
    document.getElementById('achCaption').value = data.caption || '';
    document.getElementById('achTag').value = data.tag || '';
    document.getElementById('achSort').value = data.sort_order || 0;
    document.getElementById('achImagesInput').value = '';
    document.getElementById('achNewPreviewWrap').innerHTML = '';

    // Show existing images with delete button
    const existingWrap = document.getElementById('achExistingImages');
    existingWrap.innerHTML = '';
    if (data.images && data.images.length > 0) {
        data.images.forEach(img => {
            const wrapper = document.createElement('div');
            wrapper.className = 'position-relative';
            wrapper.id = 'ach-img-wrap-' + img.id;
            wrapper.innerHTML = `
                <img src="../../${img.image_path}" style="height:72px;width:88px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;" onerror="this.style.display='none'">
                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 lh-1"
                    style="width:20px;height:20px;font-size:11px;border-radius:50%;"
                    onclick="deleteAchievementImage(${img.id}, this)">
                    <i class="bi bi-x"></i>
                </button>`;
            existingWrap.appendChild(wrapper);
        });
    }

    new bootstrap.Modal(document.getElementById('achievementModal')).show();
}

function deleteAchievementImage(imgId, btn) {
    if (!confirm('Remove this image?')) return;
    const formData = new FormData();
    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
    formData.append('action', 'delete_achievement_image');
    formData.append('img_id', imgId);
    fetch('', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                const wrap = document.getElementById('ach-img-wrap-' + imgId);
                if (wrap) wrap.remove();
            } else {
                alert('Failed to delete image.');
            }
        })
        .catch(() => alert('Failed to delete image.'));
}

// Live image preview
document.addEventListener('DOMContentLoaded', function() {
    const achInput = document.getElementById('achImagesInput');
    if (achInput) {
        achInput.addEventListener('change', function() {
            const wrap = document.getElementById('achNewPreviewWrap');
            wrap.innerHTML = '';
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'height:72px;width:88px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;';
                    wrap.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    const assistanceInput = document.getElementById('assistanceImageInput');
    if (assistanceInput) {
        assistanceInput.addEventListener('change', function() {
            const wrap = document.getElementById('assistanceNewPreviewWrap');
            wrap.innerHTML = '';
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'height:72px;width:88px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;';
                    wrap.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    }
});



</script>
<<<<<<< HEAD
=======

<!-- Expense Modal -->
<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" id="expenseAction" value="add_expense">
                <input type="hidden" name="exp_id" id="expenseId">
                <input type="hidden" name="active_tab" value="expenses">
                <div class="modal-header">
                    <h5 class="modal-title" id="expenseModalTitle">Record Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title / Purpose <span class="text-danger">*</span></label>
                        <input type="text" name="exp_title" id="expTitle" class="form-control" placeholder="e.g., Fishing nets purchase, Transportation allowance" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="exp_category" id="expCategory" class="form-select">
                                <option value="">-- Select --</option>
                                <option value="Equipment">Equipment</option>
                                <option value="Livelihood">Livelihood</option>
                                <option value="Relief Goods">Relief Goods</option>
                                <option value="Transportation">Transportation</option>
                                <option value="Administrative">Administrative</option>
                                <option value="Training">Training</option>
                                <option value="Medical">Medical</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="exp_date" id="expDate" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" name="exp_amount" id="expAmount" class="form-control" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reference / OR No.</label>
                            <input type="text" name="exp_reference" id="expReference" class="form-control" placeholder="Receipt or OR number">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Paid To</label>
                        <input type="text" name="exp_paid_to" id="expPaidTo" class="form-control" placeholder="Vendor, supplier, or person paid">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Linked to Assistance Source <small class="text-muted">(optional)</small></label>
                        <select name="exp_donation_id" id="expDonationId" class="form-select">
                            <option value="">-- Not linked --</option>
                            <?php foreach ($assistance as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= e($a['donor_name']) ?> — ₱<?= number_format($a['amount'], 0) ?> (<?= $a['date_received'] ? date('M d, Y', strtotime($a['date_received'])) : '—' ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="exp_notes" id="expNotes" class="form-control" rows="2" placeholder="Additional details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-check-lg me-1"></i>Save Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetExpenseForm() {
    document.getElementById('expenseAction').value = 'add_expense';
    document.getElementById('expenseId').value = '';
    document.getElementById('expenseModalTitle').textContent = 'Record Expense';
    document.getElementById('expTitle').value = '';
    document.getElementById('expCategory').value = '';
    document.getElementById('expDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('expAmount').value = '';
    document.getElementById('expReference').value = '';
    document.getElementById('expPaidTo').value = '';
    document.getElementById('expDonationId').value = '';
    document.getElementById('expNotes').value = '';
}

function editExpense(data) {
    document.getElementById('expenseAction').value = 'edit_expense';
    document.getElementById('expenseId').value = data.id;
    document.getElementById('expenseModalTitle').textContent = 'Edit Expense';
    document.getElementById('expTitle').value = data.title || '';
    document.getElementById('expCategory').value = data.category || '';
    document.getElementById('expDate').value = data.expense_date || '';
    document.getElementById('expAmount').value = data.amount || '';
    document.getElementById('expReference').value = data.reference_code || '';
    document.getElementById('expPaidTo').value = data.paid_to || '';
    document.getElementById('expDonationId').value = data.donation_id || '';
    document.getElementById('expNotes').value = data.notes || '';
    new bootstrap.Modal(document.getElementById('expenseModal')).show();
}

function confirmDeleteExpense(id, name) {
    Swal.fire({
        title: 'Delete this expense?',
        text: `"${name}" will be permanently deleted.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteType').value = 'delete_expense';
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteTab').value = 'expenses';
            document.getElementById('deleteForm').submit();
        }
    });
}
</script>

>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
<?php else: ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php endif; ?>

<!-- Hidden forms for archive/delete actions -->
<form id="archiveForm" method="POST" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <input type="hidden" name="action" id="archiveType">
    <input type="hidden" name="id" id="archiveId">
    <input type="hidden" name="active_tab" id="archiveTab">
</form>
<form id="deleteForm" method="POST" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <input type="hidden" name="action" id="deleteType">
    <input type="hidden" name="id" id="deleteId">
    <input type="hidden" name="active_tab" id="deleteTab">
</form>

<script>
// Restore active tab after form submission
(function () {
    const tab = <?= json_encode($_POST['active_tab'] ?? '') ?>;
    if (tab && tab !== 'programs') {
<<<<<<< HEAD
        const tabEl = document.querySelector('#' + tab + '-tab');
=======
        const tabEl = document.querySelector('#' + CSS.escape(tab) + '-tab');
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
        if (tabEl) bootstrap.Tab.getOrCreateInstance(tabEl).show();
    }
})();
</script>

</body>
</html>
