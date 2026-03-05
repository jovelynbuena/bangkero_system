<?php
// Transparency Archive Page
session_start();

if (empty($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

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

$canEdit = $isAdmin
    || ($role === 'officer' && in_array($transRole, ['treasurer','both'], true))
    || ($role === 'officer' && !$hasTransparencyRoleCol && $transRole === '' && $officerPosition === 'treasurer');

if (!$canAccess) {
    header('Location: ../admin.php?error=transparency_access');
    exit;
}

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$alertType = '';
$alertMsg = '';

// Handle Restore Program
if (isset($_GET['restore_program']) && $canEdit) {
    $archiveId = (int)$_GET['restore_program'];
    try {
        $conn->begin_transaction();

        // Get archived data
        $stmt = $conn->prepare("SELECT * FROM transparency_campaigns_archive WHERE archive_id = ?");
        $stmt->bind_param('i', $archiveId);
        $stmt->execute();
        $program = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($program) {
            // Restore to main table
            $stmt = $conn->prepare("INSERT INTO transparency_campaigns 
                (name, slug, description, goal_amount, status, start_date, end_date, banner_image, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param('sssdssss', 
                $program['name'], $program['slug'], $program['description'],
                $program['goal_amount'], $program['status'], $program['start_date'],
                $program['end_date'], $program['banner_image']
            );
            $stmt->execute();
            $stmt->close();

            // Delete from archive
            $stmt = $conn->prepare("DELETE FROM transparency_campaigns_archive WHERE archive_id = ?");
            $stmt->bind_param('i', $archiveId);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $alertType = 'success';
            $alertMsg = 'Program restored successfully.';
        }
    } catch (Exception $e) {
        $conn->rollback();
        $alertType = 'error';
        $alertMsg = 'Failed to restore program: ' . $e->getMessage();
    }
}

// Handle Restore Assistance
if (isset($_GET['restore_assistance']) && $canEdit) {
    $archiveId = (int)$_GET['restore_assistance'];
    try {
        $conn->begin_transaction();

        // Get archived data
        $stmt = $conn->prepare("SELECT * FROM transparency_donations_archive WHERE archive_id = ?");
        $stmt->bind_param('i', $archiveId);
        $stmt->execute();
        $assistance = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($assistance) {
            // Restore to main table
            $stmt = $conn->prepare("INSERT INTO transparency_donations 
                (campaign_id, donor_name, donor_type, amount, currency, date_received, payment_method, reference_code, status, is_restricted, notes, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param('isssdsssiis',
                $assistance['campaign_id'], $assistance['donor_name'],
                $assistance['donor_type'], $assistance['amount'], $assistance['currency'],
                $assistance['date_received'], $assistance['payment_method'],
                $assistance['reference_code'], $assistance['status'],
                $assistance['is_restricted'], $assistance['notes']
            );
            $stmt->execute();
            $stmt->close();

            // Delete from archive
            $stmt = $conn->prepare("DELETE FROM transparency_donations_archive WHERE archive_id = ?");
            $stmt->bind_param('i', $archiveId);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $alertType = 'success';
            $alertMsg = 'Assistance record restored successfully.';
        }
    } catch (Exception $e) {
        $conn->rollback();
        $alertType = 'error';
        $alertMsg = 'Failed to restore assistance: ' . $e->getMessage();
    }
}

// Handle Permanent Delete
if (isset($_GET['delete_permanent']) && $canEdit) {
    $type = $_GET['type'] ?? '';
    $id = (int)$_GET['delete_permanent'];
    
    try {
        if ($type === 'program') {
            $stmt = $conn->prepare("DELETE FROM transparency_campaigns_archive WHERE archive_id = ?");
        } else {
            $stmt = $conn->prepare("DELETE FROM transparency_donations_archive WHERE archive_id = ?");
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        
        $alertType = 'success';
        $alertMsg = 'Record permanently deleted.';
    } catch (Exception $e) {
        $alertType = 'error';
        $alertMsg = 'Failed to delete: ' . $e->getMessage();
    }
}

// Fetch archived programs
$archivedPrograms = [];
$res = $conn->query("SELECT a.*, u.username as archived_by_name 
    FROM transparency_campaigns_archive a 
    LEFT JOIN users u ON a.archived_by = u.id 
    ORDER BY a.archived_at DESC");
while ($res && $row = $res->fetch_assoc()) {
    $archivedPrograms[] = $row;
}

// Fetch archived assistance
$archivedAssistance = [];
$res = $conn->query("SELECT a.*, u.username as archived_by_name, c.name as program_name
    FROM transparency_donations_archive a 
    LEFT JOIN users u ON a.archived_by = u.id 
    LEFT JOIN transparency_campaigns c ON a.campaign_id = c.id
    ORDER BY a.archived_at DESC");
while ($res && $row = $res->fetch_assoc()) {
    $archivedAssistance[] = $row;
}

$totalArchived = count($archivedPrograms) + count($archivedAssistance);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transparency Archive</title>
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
        .archive-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 24px;
        }
        .badge-archived {
            background: #fee2e2;
            color: #991b1b;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }
    </style>
</head>
<body>
<?php include('../navbar.php'); ?>

<div class="main-content">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2><i class="bi bi-archive"></i> Transparency Archive</h2>
                <p class="mb-0">View and restore archived programs and assistance records.</p>
            </div>
            <a href="transparency.php" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($alertMsg): ?>
    <div class="alert alert-<?= $alertType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
        <?= e($alertMsg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="archive-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Archived Programs</h5>
                        <h3 class="mb-0"><?= count($archivedPrograms) ?></h3>
                    </div>
                    <i class="bi bi-folder-x text-danger" style="font-size: 2.5rem;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="archive-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Archived Assistance</h5>
                        <h3 class="mb-0"><?= count($archivedAssistance) ?></h3>
                    </div>
                    <i class="bi bi-cash-stack text-warning" style="font-size: 2.5rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <?php if ($totalArchived === 0): ?>
    <div class="archive-card">
        <div class="empty-state">
            <i class="bi bi-archive" style="font-size: 4rem; color: #d1d5db;"></i>
            <h4 class="mt-3">No Archived Records</h4>
            <p>Archive is empty. Records you archive from the Transparency Dashboard will appear here.</p>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($archivedPrograms)): ?>
    <!-- Archived Programs -->
    <div class="archive-card">
        <h5 class="mb-3"><i class="bi bi-folder-x text-danger me-2"></i>Archived Programs</h5>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Program</th>
                        <th>Budget</th>
                        <th>Status</th>
                        <th>Archived</th>
                        <th>By</th>
                        <?php if ($canEdit): ?><th>Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archivedPrograms as $p): ?>
                    <tr>
                        <td>
                            <strong><?= e($p['name']) ?></strong>
                            <br><small class="text-muted"><?= e(substr($p['description'], 0, 50)) ?><?= strlen($p['description']) > 50 ? '...' : '' ?></small>
                        </td>
                        <td>₱<?= number_format($p['goal_amount'], 0) ?></td>
                        <td><span class="badge-archived"><?= ucfirst($p['status']) ?></span></td>
                        <td><?= date('M d, Y', strtotime($p['archived_at'])) ?></td>
                        <td><?= e($p['archived_by_name'] ?: 'System') ?></td>
                        <?php if ($canEdit): ?>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="confirmRestoreProgram(<?= $p['archive_id'] ?>, '<?= addslashes($p['name']) ?>')" title="Restore">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="confirmDeleteProgram(<?= $p['archive_id'] ?>, '<?= addslashes($p['name']) ?>')" title="Delete Permanent">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($archivedAssistance)): ?>
    <!-- Archived Assistance -->
    <div class="archive-card">
        <h5 class="mb-3"><i class="bi bi-cash-stack text-warning me-2"></i>Archived Assistance Records</h5>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Archived</th>
                        <th>By</th>
                        <?php if ($canEdit): ?><th>Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archivedAssistance as $a): ?>
                    <tr>
                        <td>
                            <strong><?= e($a['donor_name']) ?></strong>
                            <br><small class="text-muted"><?= e($a['donor_type']) ?><?= $a['program_name'] ? ' • ' . e($a['program_name']) : '' ?></small>
                        </td>
                        <td>₱<?= number_format($a['amount'], 0) ?></td>
                        <td><?= $a['date_received'] ? date('M d, Y', strtotime($a['date_received'])) : '-' ?></td>
                        <td><?= date('M d, Y', strtotime($a['archived_at'])) ?></td>
                        <td><?= e($a['archived_by_name'] ?: 'System') ?></td>
                        <?php if ($canEdit): ?>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="confirmRestoreAssistance(<?= $a['archive_id'] ?>, '<?= addslashes($a['donor_name']) ?>')" title="Restore">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="confirmDeleteAssistance(<?= $a['archive_id'] ?>, '<?= addslashes($a['donor_name']) ?>')" title="Delete Permanent">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function confirmRestoreProgram(id, name) {
    Swal.fire({
        title: 'Restore Program?',
        text: `Are you sure you want to restore "${name}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Restore'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?restore_program=${id}`;
        }
    });
}

function confirmDeleteProgram(id, name) {
    Swal.fire({
        title: 'Delete Permanently?',
        text: `This will permanently delete "${name}". This cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?delete_permanent=${id}&type=program`;
        }
    });
}

function confirmRestoreAssistance(id, name) {
    Swal.fire({
        title: 'Restore Assistance Record?',
        text: `Are you sure you want to restore the assistance from "${name}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Restore'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?restore_assistance=${id}`;
        }
    });
}

function confirmDeleteAssistance(id, name) {
    Swal.fire({
        title: 'Delete Permanently?',
        text: `This will permanently delete the assistance from "${name}". This cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `?delete_permanent=${id}&type=assistance`;
        }
    });
}
</script>

<?php if (isset($_GET['restore_program']) || isset($_GET['restore_assistance'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Restored!',
        text: 'Item has been restored successfully.',
        timer: 2000,
        showConfirmButton: false
    });
</script>
<?php endif; ?>

<?php if (isset($_GET['delete_permanent'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Deleted!',
        text: 'Item has been permanently deleted.',
        timer: 2000,
        showConfirmButton: false
    });
</script>
<?php endif; ?>

</body>
</html>
