<?php
// Transparency Dashboard - Combined single page
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
            $program_id = (int)($_POST['program_id'] ?? 0);
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

        // Delete Program
        elseif ($action === 'delete_program') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $conn->prepare("DELETE FROM transparency_campaigns WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $alertType = 'success';
            $alertMsg = 'Program deleted.';
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
    }
} catch (Exception $ex) {
    $alertType = 'error';
    $alertMsg = $ex->getMessage();
}

// Handle Archive Program (via GET)
if (isset($_GET['archive_program']) && $canEdit) {
    $id = (int)$_GET['archive_program'];
    try {
        // Get program data
        $stmt = $conn->prepare("SELECT * FROM transparency_campaigns WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $program = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($program) {
            // Insert to archive
            $stmt = $conn->prepare("INSERT INTO transparency_campaigns_archive 
                (original_id, name, slug, description, goal_amount, status, start_date, end_date, banner_image, created_at, updated_at, archived_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $archivedBy = $_SESSION['user_id'] ?? 0;
            $stmt->bind_param('isssdssssssi', 
                $program['id'], $program['name'], $program['slug'], $program['description'],
                $program['goal_amount'], $program['status'], $program['start_date'], 
                $program['end_date'], $program['banner_image'], $program['created_at'],
                $program['updated_at'], $archivedBy
            );
            $stmt->execute();
            $stmt->close();

            // Delete from main table
            $stmt = $conn->prepare("DELETE FROM transparency_campaigns WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            $alertType = 'success';
            $alertMsg = 'Program archived successfully.';
        }
    } catch (Exception $e) {
        $alertType = 'error';
        $alertMsg = 'Failed to archive program: ' . $e->getMessage();
    }
}

// Handle Archive Assistance (via GET)
if (isset($_GET['archive_assistance']) && $canEdit) {
    $id = (int)$_GET['archive_assistance'];
    try {
        // Get assistance data
        $stmt = $conn->prepare("SELECT * FROM transparency_donations WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $assistance = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($assistance) {
            // Insert to archive
            $stmt = $conn->prepare("INSERT INTO transparency_donations_archive 
                (original_id, campaign_id, donor_name, donor_type, amount, currency, date_received, payment_method, reference_code, status, is_restricted, notes, archived_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $archivedBy = $_SESSION['user_id'] ?? 0;
            $stmt->bind_param('iissdsssssiis', 
                $assistance['id'], $assistance['campaign_id'], $assistance['donor_name'],
                $assistance['donor_type'], $assistance['amount'], $assistance['currency'],
                $assistance['date_received'], $assistance['payment_method'],
                $assistance['reference_code'], $assistance['status'],
                $assistance['is_restricted'], $assistance['notes'], $archivedBy
            );
            $stmt->execute();
            $stmt->close();

            // Delete from main table
            $stmt = $conn->prepare("DELETE FROM transparency_donations WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            $alertType = 'success';
            $alertMsg = 'Assistance record archived successfully.';
        }
    } catch (Exception $e) {
        $alertType = 'error';
        $alertMsg = 'Failed to archive assistance: ' . $e->getMessage();
    }
}

// Fetch statistics
$stats = ['programs' => 0, 'active' => 0, 'total_assistance' => 0, 'sources' => 0];

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

// Source types for dropdown
$sourceTypes = ['DOLE', 'LGU', 'NGO', 'Private', 'Membership', 'Others'];
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
        }
        .badge-status { padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-active { background: #d1fae5; color: #065f46; }
        .badge-completed { background: #dbeafe; color: #1e40af; }
        .badge-planned { background: #fef3c7; color: #92400e; }
        .progress { height: 8px; border-radius: 4px; }
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
                <p class="mb-0">Manage programs and track assistance received from partners.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="transparency_reports.php" class="btn btn-light">
                    <i class="bi bi-file-earmark-text"></i> View Reports
                </a>
                <a href="archives_transparency.php" class="btn btn-light">
                    <i class="bi bi-archive"></i> Archive
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
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-folder"></i>
                </div>
                <div>
                    <h4 class="mb-0"><?= number_format($stats['programs']) ?></h4>
                    <small class="text-muted">Total Programs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-lightning-charge"></i>
                </div>
                <div>
                    <h4 class="mb-0"><?= number_format($stats['active']) ?></h4>
                    <small class="text-muted">Active Programs</small>
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
        <div class="col-md-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <h4 class="mb-0"><?= number_format($stats['sources']) ?></h4>
                    <small class="text-muted">Partner Sources</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Programs Section -->
        <div class="col-lg-7">
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="bi bi-bullseye me-2"></i>Programs</h5>
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
                                    <br><small class="text-muted"><?= e(substr($p['description'], 0, 50)) ?><?= strlen($p['description']) > 50 ? '...' : '' ?></small>
                                </td>
                                <td>₱<?= number_format($p['goal_amount'], 0) ?></td>
                                <td>
                                    ₱<?= number_format($p['received'], 0) ?>
                                    <div class="progress mt-1">
                                        <div class="progress-bar" style="width: <?= $progress ?>%"></div>
                                    </div>
                                </td>
                                <td><span class="badge-status badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                                <?php if ($canEdit): ?>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editProgram(<?= htmlspecialchars(json_encode($p)) ?>)" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="confirmArchiveProgram(<?= $p['id'] ?>, '<?= addslashes($p['name']) ?>')" title="Archive">
                                        <i class="bi bi-archive"></i>
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($programs)): ?>
                            <tr><td colspan="<?= $canEdit ? 5 : 4 ?>" class="text-center text-muted py-4">No programs yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Assistance Section -->
        <div class="col-lg-5">
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
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Source</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <?php if ($canEdit): ?><th></th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assistance as $a): ?>
                            <tr>
                                <td>
                                    <strong><?= e($a['donor_name']) ?></strong>
                                    <br><small class="text-muted"><?= e($a['donor_type']) ?><?= $a['program_name'] ? ' • ' . e($a['program_name']) : '' ?></small>
                                </td>
                                <td>₱<?= number_format($a['amount'], 0) ?></td>
                                <td><?= $a['date_received'] ? date('M d, Y', strtotime($a['date_received'])) : '-' ?></td>
                                <?php if ($canEdit): ?>
                                <td>
                                    <button class="btn btn-sm btn-link text-primary p-0" onclick="editAssistance(<?= htmlspecialchars(json_encode($a)) ?>)" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-link text-warning p-0" onclick="confirmArchiveAssistance(<?= $a['id'] ?>, '<?= addslashes($a['donor_name']) ?>')" title="Archive">
                                        <i class="bi bi-archive"></i>
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($assistance)): ?>
                            <tr><td colspan="<?= $canEdit ? 4 : 3 ?>" class="text-center text-muted py-4">No assistance records yet</td></tr>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
    document.getElementById('programDesc').value = data.description;
    document.getElementById('programBudget').value = data.goal_amount;
    document.getElementById('programStatus').value = data.status;
    document.getElementById('programStart').value = data.start_date;
    document.getElementById('programEnd').value = data.end_date;
    new bootstrap.Modal(document.getElementById('programModal')).show();
}

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
    document.getElementById('assistanceType').value = data.donor_type;
    document.getElementById('assistanceAmount').value = data.amount;
    document.getElementById('assistanceDate').value = data.date_received;
    document.getElementById('assistanceRef').value = data.reference_code || '';
    document.getElementById('assistanceNotes').value = data.notes || '';
    new bootstrap.Modal(document.getElementById('assistanceModal')).show();
}

function confirmArchiveProgram(id, name) {
    Swal.fire({
        title: 'Archive Program?',
        text: `Are you sure you want to archive "${name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Archive'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?archive_program=${id}`;
        }
    });
}

function confirmArchiveAssistance(id, name) {
    Swal.fire({
        title: 'Archive Assistance Record?',
        text: `Are you sure you want to archive the assistance from "${name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Archive'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?archive_assistance=${id}`;
        }
    });
}
</script>
<?php else: ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php endif; ?>

<?php if (isset($_GET['archive_program']) || isset($_GET['archive_assistance'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Archived!',
        text: 'Item has been moved to archive.',
        timer: 2000,
        showConfirmButton: false
    });
</script>
<?php endif; ?>
</body>
</html>
