<?php
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
            $rawProgramId = (int)($_POST['program_id'] ?? 0);
            // Validate campaign exists to avoid FK constraint failure; use NULL if not found
            $program_id = null;
            if ($rawProgramId > 0) {
                $chk = $conn->prepare("SELECT id FROM transparency_campaigns WHERE id = ?");
                $chk->bind_param('i', $rawProgramId);
                $chk->execute();
                $chk->store_result();
                if ($chk->num_rows > 0) {
                    $program_id = $rawProgramId;
                }
                $chk->close();
            }
            $source_name = trim($_POST['source_name'] ?? '');
            $source_type = trim($_POST['source_type'] ?? '');
            $amount = (float)($_POST['amount'] ?? 0);
            $date_received = $_POST['date_received'] ?: null;
            $reference = trim($_POST['reference'] ?? '');
            $notes = trim($_POST['notes'] ?? '');

            if ($source_name === '' || $amount <= 0) {
                throw new Exception('Source name and valid amount are required.');
            }

            if ($action === 'add_assistance') {
                $stmt = $conn->prepare("INSERT INTO transparency_donations 
                    (campaign_id, donor_name, donor_type, amount, date_received, reference_code, notes, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed', NOW())");
                $stmt->bind_param('issdsss', $program_id, $source_name, $source_type, $amount, $date_received, $reference, $notes);
                $stmt->execute();
                $stmt->close();
                $alertType = 'success';
                $alertMsg = 'Assistance recorded successfully.';
            } else {
                $id = (int)($_POST['id'] ?? 0);
                $stmt = $conn->prepare("UPDATE transparency_donations 
                    SET campaign_id=?, donor_name=?, donor_type=?, amount=?, date_received=?, reference_code=?, notes=?, updated_at=NOW() 
                    WHERE id=?");
                $stmt->bind_param('issdsssi', $program_id, $source_name, $source_type, $amount, $date_received, $reference, $notes, $id);
                $stmt->execute();
                $stmt->close();
                $alertType = 'success';
                $alertMsg = 'Assistance updated successfully.';
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
            $stmt = $conn->prepare("DELETE FROM transparency_donations WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $alertType = 'success';
            $alertMsg = 'Assistance record deleted.';
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

$res = $conn->query("SELECT SUM(amount) as total FROM transparency_donations WHERE status='confirmed'");
if ($res && $row = $res->fetch_assoc()) {
    $stats['total_assistance'] = (float)($row['total'] ?? 0);
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

// Fetch assistance records
$assistance = [];
$res = $conn->query("SELECT d.*, c.name as program_name 
    FROM transparency_donations d 
    LEFT JOIN transparency_campaigns c ON d.campaign_id = c.id 
    ORDER BY d.date_received DESC LIMIT 50");
while ($res && $row = $res->fetch_assoc()) {
    $assistance[] = $row;
}

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transparency Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background: #f8f9fa; }
        .main-content { padding: 24px; }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        <div class="col-md-2">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-folder"></i>
                </div>
                <div>
                    <h4 class="mb-0"><?= number_format($stats['programs']) ?></h4>
                    <small class="text-muted">Programs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <h4 class="mb-0">₱<?= number_format($stats['total_assistance'], 0) ?></h4>
                    <small class="text-muted">Total Assistance</small>
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
        <div class="col-md-2">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <h4 class="mb-0"><?= number_format($stats['beneficiaries']) ?></h4>
                    <small class="text-muted">Beneficiaries</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div>
                    <h4 class="mb-0"><?= number_format($stats['featured']) ?></h4>
                    <small class="text-muted">Featured Stories</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4" id="mainTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="programs-tab" data-bs-toggle="tab" data-bs-target="#programs" type="button">
                <i class="bi bi-bullseye me-1"></i> Programs
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="assistance-tab" data-bs-toggle="tab" data-bs-target="#assistance" type="button">
                <i class="bi bi-cash-coin me-1"></i> Assistance
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="impact-tab" data-bs-toggle="tab" data-bs-target="#impact" type="button">
                <i class="bi bi-heart-pulse me-1"></i> Impact Stories
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="mainTabContent">
        <!-- Programs Tab -->
        <div class="tab-pane fade show active" id="programs" role="tabpanel">
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="bi bi-bullseye me-2"></i>Programs & Projects</h5>
                    <?php if ($canEdit): ?>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#programModal" onclick="resetProgramForm()">
                        <i class="bi bi-plus"></i> Add Program
                    </button>
                    <?php endif; ?>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Program</th>
                                <th>Budget</th>
                                <th>Received</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <?php if ($canEdit): ?><th>Actions</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($programs as $p): 
                                $progress = $p['goal_amount'] > 0 ? min(100, ($p['received'] / $p['goal_amount']) * 100) : 0;
                            ?>
                            <tr>
                                <td>
                                    <strong><?= e($p['name']) ?></strong>
                                    <br><small class="text-muted"><?= e(substr($p['description'] ?? '', 0, 50)) ?><?= strlen($p['description'] ?? '') > 50 ? '...' : '' ?></small>
                                </td>
                                <td>₱<?= number_format($p['goal_amount'], 0) ?></td>
                                <td>₱<?= number_format($p['received'], 0) ?></td>
                                <td style="min-width: 120px;">
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: <?= $progress ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?= number_format($progress, 0) ?>%</small>
                                </td>
                                <td><span class="badge-status badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                                <?php if ($canEdit): ?>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editProgram(<?= htmlspecialchars(json_encode($p)) ?>)" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="confirmArchive('program', <?= $p['id'] ?>, '<?= addslashes($p['name']) ?>')" title="Archive">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($programs)): ?>
                            <tr><td colspan="<?= $canEdit ? 6 : 5 ?>" class="text-center text-muted py-4">No programs yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Assistance Tab -->
        <div class="tab-pane fade" id="assistance" role="tabpanel">
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
                                <th>Amount</th>
                                <th>Program</th>
                                <th>Date</th>
                                <?php if ($canEdit): ?><th>Actions</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assistance as $a): ?>
                            <tr>
                                <td><strong><?= e($a['donor_name']) ?></strong></td>
                                <td><span class="badge bg-secondary"><?= e($a['donor_type']) ?></span></td>
                                <td class="text-success fw-bold">₱<?= number_format($a['amount'], 0) ?></td>
                                <td><?= e($a['program_name'] ?? '-') ?></td>
                                <td><?= $a['date_received'] ? date('M d, Y', strtotime($a['date_received'])) : '-' ?></td>
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

        <!-- Impact Stories Tab (Combined Beneficiaries + Impact) -->
        <div class="tab-pane fade" id="impact" role="tabpanel">
            <!-- Impact Summary Cards -->
            <?php
            // Calculate impact stats from beneficiaries
            $impactStats = [
                'total_beneficiaries' => $stats['beneficiaries'],
                'total_amount' => 0,
                'featured_stories' => $stats['featured'],
                'barangays_reached' => 0
            ];
            $barangays = [];
            foreach ($beneficiaries as $b) {
                $impactStats['total_amount'] += (float)($b['amount_value'] ?? 0);
                if (!empty($b['barangay'])) {
                    $barangays[$b['barangay']] = true;
                }
            }
            $impactStats['barangays_reached'] = count($barangays);
            ?>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="metric-card">
                        <div class="metric-value"><?= number_format($impactStats['total_beneficiaries']) ?></div>
                        <div class="metric-label"><i class="bi bi-people me-1"></i> Beneficiaries</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <div class="metric-value">₱<?= number_format($impactStats['total_amount'], 0) ?></div>
                        <div class="metric-label"><i class="bi bi-cash me-1"></i> Total Impact Value</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <div class="metric-value"><?= number_format($impactStats['barangays_reached']) ?></div>
                        <div class="metric-label"><i class="bi bi-geo-alt me-1"></i> Barangays Reached</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">
                        <div class="metric-value"><?= number_format($impactStats['featured_stories']) ?></div>
                        <div class="metric-label"><i class="bi bi-star me-1"></i> Featured Stories</div>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="bi bi-heart-pulse me-2"></i>Impact Stories (Beneficiaries)</h5>
                    <?php if ($canEdit): ?>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#beneficiaryModal" onclick="resetBeneficiaryForm()">
                        <i class="bi bi-plus"></i> Add Impact Story
                    </button>
                    <?php endif; ?>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Beneficiary</th>
                                <th>Assistance Type</th>
                                <th>Amount/Qty</th>
                                <th>Barangay</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Featured</th>
                                <?php if ($canEdit): ?><th>Actions</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($beneficiaries as $b): ?>
                            <tr>
                                <td>
                                    <strong><?= e($b['name']) ?></strong>
                                    <?php if (!empty($b['short_story'])): ?>
                                    <br><small class="text-muted"><?= e(substr($b['short_story'], 0, 50)) ?><?= strlen($b['short_story']) > 50 ? '...' : '' ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($b['assistance_type'] ?? '-') ?></td>
                                <td>
                                    <?php if ($b['amount_value']): ?>
                                        <span class="text-success fw-bold">₱<?= number_format($b['amount_value'], 0) ?></span>
                                    <?php elseif ($b['quantity']): ?>
                                        <?= $b['quantity'] ?> items
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= e($b['barangay'] ?? '-') ?></td>
                                <td><?= $b['date_assisted'] ? date('M d, Y', strtotime($b['date_assisted'])) : '-' ?></td>
                                <td><span class="badge-status badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                                <td>
                                    <?php if ($b['featured']): ?>
                                        <i class="bi bi-star-fill featured-star"></i>
                                    <?php else: ?>
                                        <i class="bi bi-star text-muted"></i>
                                    <?php endif; ?>
                                </td>
                                <?php if ($canEdit): ?>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editBeneficiary(<?= htmlspecialchars(json_encode($b)) ?>)" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" onclick="confirmArchive('beneficiary', <?= $b['id'] ?>, '<?= addslashes($b['name']) ?>')" title="Archive">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($beneficiaries)): ?>
                            <tr><td colspan="<?= $canEdit ? 8 : 7 ?>" class="text-center text-muted py-4">No impact stories yet. Add beneficiaries to see impact statistics.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


    </div>
</div>

<?php if ($canEdit): ?>
<!-- Program Modal -->
<div class="modal fade" id="programModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" id="programAction" value="add_program">
                <input type="hidden" name="id" id="programId">
                <input type="hidden" name="active_tab" value="programs">
                <div class="modal-header">
                    <h5 class="modal-title" id="programModalTitle">Add Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Program Name</label>
                        <input type="text" name="name" id="programName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="programDesc" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Budget Allocation</label>
                            <input type="number" name="budget" id="programBudget" class="form-control" min="0" step="0.01">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="programStatus" class="form-select">
                                <option value="active">Active</option>
                                <option value="planned">Planned</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="programStart" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" id="programEnd" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assistance Modal -->
<div class="modal fade" id="assistanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" id="assistanceAction" value="add_assistance">
                <input type="hidden" name="id" id="assistanceId">
                <input type="hidden" name="active_tab" value="assistance">
                <div class="modal-header">
                    <h5 class="modal-title" id="assistanceModalTitle">Record Assistance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Link to Program (Optional)</label>
                        <select name="program_id" id="assistanceProgram" class="form-select">
                            <option value="0">-- Not linked --</option>
                            <?php foreach ($programs as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
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
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="assistanceNotes" class="form-control" rows="2"></textarea>
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

<!-- Beneficiary Modal -->
<div class="modal fade" id="beneficiaryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" id="beneficiaryAction" value="add_beneficiary">
                <input type="hidden" name="b_id" id="beneficiaryId">
                <input type="hidden" name="active_tab" value="impact">
                <div class="modal-header">
                    <h5 class="modal-title" id="beneficiaryModalTitle">Add Impact Story</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Beneficiary Name *</label>
                            <input type="text" name="b_name" id="beneficiaryName" class="form-control" placeholder="Full name of beneficiary" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Assistance Type</label>
                            <select name="b_assistance_type" id="beneficiaryAssistanceType" class="form-select">
                                <option value="">-- Select --</option>
                                <?php foreach ($assistanceTypes as $type): ?>
                                <option value="<?= $type ?>"><?= $type ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Amount Value</label>
                            <input type="number" name="b_amount" id="beneficiaryAmount" class="form-control" min="0" step="0.01" placeholder="e.g., 5000">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Quantity (if items)</label>
                            <input type="number" name="b_quantity" id="beneficiaryQuantity" class="form-control" min="0" placeholder="e.g., 5 kits">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date Assisted</label>
                            <input type="date" name="b_date" id="beneficiaryDate" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Barangay</label>
                            <input type="text" name="b_barangay" id="beneficiaryBarangay" class="form-control" placeholder="e.g., Barretto">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="b_status" id="beneficiaryStatus" class="form-select">
                                <?php foreach ($beneficiaryStatuses as $status): ?>
                                <option value="<?= $status ?>"><?= ucfirst($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Impact Story / Notes</label>
                        <textarea name="b_story" id="beneficiaryStory" class="form-control" rows="3" placeholder="Brief story about the impact of assistance..."></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="b_featured" id="beneficiaryFeatured" class="form-check-input" value="1">
                        <label class="form-check-label" for="beneficiaryFeatured">
                            <i class="bi bi-star-fill text-warning"></i> Featured Story (show on public transparency page)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Impact Story</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Program functions
function resetProgramForm() {
    document.getElementById('programAction').value = 'add_program';
    document.getElementById('programId').value = '';
    document.getElementById('programModalTitle').textContent = 'Add Program';
    document.getElementById('programName').value = '';
    document.getElementById('programDesc').value = '';
    document.getElementById('programBudget').value = '';
    document.getElementById('programStatus').value = 'active';
    document.getElementById('programStart').value = '';
    document.getElementById('programEnd').value = '';
}

function editProgram(data) {
    document.getElementById('programAction').value = 'edit_program';
    document.getElementById('programId').value = data.id;
    document.getElementById('programModalTitle').textContent = 'Edit Program';
    document.getElementById('programName').value = data.name;
    document.getElementById('programDesc').value = data.description || '';
    document.getElementById('programBudget').value = data.goal_amount;
    document.getElementById('programStatus').value = data.status;
    document.getElementById('programStart').value = data.start_date || '';
    document.getElementById('programEnd').value = data.end_date || '';
    new bootstrap.Modal(document.getElementById('programModal')).show();
}

// Assistance functions
function resetAssistanceForm() {
    document.getElementById('assistanceAction').value = 'add_assistance';
    document.getElementById('assistanceId').value = '';
    document.getElementById('assistanceModalTitle').textContent = 'Record Assistance';
    document.getElementById('assistanceProgram').value = '0';
    document.getElementById('assistanceSource').value = '';
    document.getElementById('assistanceType').value = 'DOLE';
    document.getElementById('assistanceAmount').value = '';
    document.getElementById('assistanceDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('assistanceRef').value = '';
    document.getElementById('assistanceNotes').value = '';
}

function editAssistance(data) {
    document.getElementById('assistanceAction').value = 'edit_assistance';
    document.getElementById('assistanceId').value = data.id;
    document.getElementById('assistanceModalTitle').textContent = 'Edit Assistance';
    document.getElementById('assistanceProgram').value = data.campaign_id || '0';
    document.getElementById('assistanceSource').value = data.donor_name;
    document.getElementById('assistanceType').value = data.donor_type || 'DOLE';
    document.getElementById('assistanceAmount').value = data.amount;
    document.getElementById('assistanceDate').value = data.date_received;
    document.getElementById('assistanceRef').value = data.reference_code || '';
    document.getElementById('assistanceNotes').value = data.notes || '';
    new bootstrap.Modal(document.getElementById('assistanceModal')).show();
}

// Beneficiary / Impact Story functions
function resetBeneficiaryForm() {
    document.getElementById('beneficiaryAction').value = 'add_beneficiary';
    document.getElementById('beneficiaryId').value = '';
    document.getElementById('beneficiaryModalTitle').textContent = 'Add Impact Story';
    document.getElementById('beneficiaryName').value = '';
    document.getElementById('beneficiaryAssistanceType').value = '';
    document.getElementById('beneficiaryAmount').value = '';
    document.getElementById('beneficiaryQuantity').value = '';
    document.getElementById('beneficiaryDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('beneficiaryBarangay').value = '';
    document.getElementById('beneficiaryStatus').value = 'served';
    document.getElementById('beneficiaryStory').value = '';
    document.getElementById('beneficiaryFeatured').checked = false;
}

function editBeneficiary(data) {
    document.getElementById('beneficiaryAction').value = 'edit_beneficiary';
    document.getElementById('beneficiaryId').value = data.id;
    document.getElementById('beneficiaryModalTitle').textContent = 'Edit Impact Story';
    document.getElementById('beneficiaryName').value = data.name;
    document.getElementById('beneficiaryAssistanceType').value = data.assistance_type || '';
    document.getElementById('beneficiaryAmount').value = data.amount_value || '';
    document.getElementById('beneficiaryQuantity').value = data.quantity || '';
    document.getElementById('beneficiaryDate').value = data.date_assisted || '';
    document.getElementById('beneficiaryBarangay').value = data.barangay || '';
    document.getElementById('beneficiaryStatus').value = data.status || 'served';
    document.getElementById('beneficiaryStory').value = data.short_story || '';
    document.getElementById('beneficiaryFeatured').checked = data.featured == 1;
    new bootstrap.Modal(document.getElementById('beneficiaryModal')).show();
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
            const tabMap = { program: 'programs', assistance: 'assistance', beneficiary: 'impact' };
            document.getElementById('archiveType').value = 'archive_' + type;
            document.getElementById('archiveId').value = id;
            document.getElementById('archiveTab').value = tabMap[type] || 'programs';
            document.getElementById('archiveForm').submit();
        }
    });
}

function confirmDelete(type, id, name) {
    Swal.fire({
        title: 'Delete permanently?',
        text: `"${name}" will be permanently deleted and cannot be recovered!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteType').value = 'delete_' + type;
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    });
}



</script>
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
    if (tab) {
        const tabEl = document.querySelector('#' + tab + '-tab');
        if (tabEl) bootstrap.Tab.getOrCreateInstance(tabEl).show();
    }
})();
</script>

</body>
</html>
