<?php
// Transparency Programs Management
session_start();

require_once('../../config/db_connect.php');

if (empty($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

$role = strtolower($_SESSION['role'] ?? 'guest');
if (!in_array($role, ['admin', 'officer'])) {
    header('Location: ../login.php');
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf_token'];

function checkCsrf(): bool {
    return !empty($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);
}

$alertType = '';
$alertMsg  = '';

// Load campaigns for linking
$campaignOptions = [];
$campRes = $conn->query("SELECT id, name FROM transparency_campaigns ORDER BY name ASC");
while ($r = $campRes->fetch_assoc()) {
    $campaignOptions[] = $r;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!checkCsrf()) {
            throw new Exception('Security check failed. Please refresh and try again.');
        }

        $action = $_POST['action'] ?? '';

        if ($action === 'add_program') {
            $name        = trim($_POST['name'] ?? '');
            $category    = trim($_POST['category'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $allocated   = (float)($_POST['allocated_budget'] ?? 0);
            $utilized    = (float)($_POST['utilized_budget'] ?? 0);
            $status      = $_POST['status'] ?? 'planned';
            $start_date  = $_POST['start_date'] ?: null;
            $end_date    = $_POST['end_date'] ?: null;
            $linked_campaign_id = (int)($_POST['linked_campaign_id'] ?? 0);
            $location    = trim($_POST['location'] ?? '');
            $sort_order  = max(1, (int)($_POST['sort_order'] ?? 1));

            if ($name === '') {
                throw new Exception('Program name is required.');
            }

            $stmt = $conn->prepare("INSERT INTO transparency_programs
                (name, category, description, allocated_budget, utilized_budget, status, start_date, end_date, linked_campaign_id, location, sort_order, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $linked = $linked_campaign_id > 0 ? $linked_campaign_id : null;
            $stmt->bind_param(
                'sssddsssisi',
                $name,
                $category,
                $description,
                $allocated,
                $utilized,
                $status,
                $start_date,
                $end_date,
                $linked,
                $location,
                $sort_order
            );
            $stmt->execute();
            $stmt->close();

            $alertType = 'success';
            $alertMsg  = 'Program created successfully.';

        } elseif ($action === 'edit_program') {
            $id          = (int)($_POST['id'] ?? 0);
            $name        = trim($_POST['name'] ?? '');
            $category    = trim($_POST['category'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $allocated   = (float)($_POST['allocated_budget'] ?? 0);
            $utilized    = (float)($_POST['utilized_budget'] ?? 0);
            $status      = $_POST['status'] ?? 'planned';
            $start_date  = $_POST['start_date'] ?: null;
            $end_date    = $_POST['end_date'] ?: null;
            $linked_campaign_id = (int)($_POST['linked_campaign_id'] ?? 0);
            $location    = trim($_POST['location'] ?? '');
            $sort_order  = max(1, (int)($_POST['sort_order'] ?? 1));

            if ($id <= 0) {
                throw new Exception('Invalid program ID.');
            }
            if ($name === '') {
                throw new Exception('Program name is required.');
            }

            $stmt = $conn->prepare("UPDATE transparency_programs
                SET name = ?, category = ?, description = ?, allocated_budget = ?, utilized_budget = ?, status = ?, start_date = ?, end_date = ?, linked_campaign_id = ?, location = ?, sort_order = ?, updated_at = NOW()
                WHERE id = ?");
            $linked = $linked_campaign_id > 0 ? $linked_campaign_id : null;
            $stmt->bind_param(
                'sssddsssisii',
                $name,
                $category,
                $description,
                $allocated,
                $utilized,
                $status,
                $start_date,
                $end_date,
                $linked,
                $location,
                $sort_order,
                $id
            );
            $stmt->execute();
            $stmt->close();

            $alertType = 'success';
            $alertMsg  = 'Program updated successfully.';
        }
    }
} catch (Exception $ex) {
    $alertType = 'error';
    $alertMsg  = $ex->getMessage();
}

// Filters
$search     = trim($_GET['search'] ?? '');
$categoryF  = trim($_GET['category'] ?? '');
$statusF    = trim($_GET['status'] ?? '');
$campaignId = (int)($_GET['campaign_id'] ?? 0);
$sort       = $_GET['sort'] ?? 'status_then_name';

$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where[]  = "(p.name LIKE CONCAT('%', ?, '%') OR p.description LIKE CONCAT('%', ?, '%'))";
    $params[] = $search;
    $params[] = $search;
    $types   .= 'ss';
}

if ($categoryF !== '') {
    $where[]  = "p.category = ?";
    $params[] = $categoryF;
    $types   .= 's';
}

if ($statusF !== '') {
    $where[]  = "p.status = ?";
    $params[] = $statusF;
    $types   .= 's';
}

if ($campaignId > 0) {
    $where[]  = "p.linked_campaign_id = ?";
    $params[] = $campaignId;
    $types   .= 'i';
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

switch ($sort) {
    case 'budget_high':
        $orderSql = 'ORDER BY p.allocated_budget DESC';
        break;
    case 'budget_low':
        $orderSql = 'ORDER BY p.allocated_budget ASC';
        break;
    case 'name_asc':
        $orderSql = 'ORDER BY p.name ASC';
        break;
    case 'name_desc':
        $orderSql = 'ORDER BY p.name DESC';
        break;
    case 'status_then_name':
    default:
        $orderSql = "ORDER BY FIELD(p.status,'ongoing','planned','completed','on-hold'), p.sort_order ASC, p.name ASC";
        break;
}

$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 10;
$offset   = ($page - 1) * $perPage;
$total    = 0;
$totalPages = 1;

$countSql = "SELECT COUNT(*) AS total FROM transparency_programs p {$whereSql}";
if ($types) {
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res  = $stmt->get_result();
    $row  = $res->fetch_assoc();
    $total = (int)($row['total'] ?? 0);
    $stmt->close();
} else {
    $res = $conn->query($countSql);
    $row = $res->fetch_assoc();
    $total = (int)($row['total'] ?? 0);
}

if ($total > 0) {
    $totalPages = max(1, (int)ceil($total / $perPage));
    if ($page > $totalPages) {
        $page   = $totalPages;
        $offset = ($page - 1) * $perPage;
    }
}

$listSql = "SELECT p.*, c.name AS campaign_name
            FROM transparency_programs p
            LEFT JOIN transparency_campaigns c ON p.linked_campaign_id = c.id
            {$whereSql}
            {$orderSql}
            LIMIT {$perPage} OFFSET {$offset}";

if ($types) {
    $stmt = $conn->prepare($listSql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $progRes = $stmt->get_result();
} else {
    $progRes = $conn->query($listSql);
}

$programs = [];
while ($row = $progRes->fetch_assoc()) {
    $programs[] = $row;
}

// Quick stats
$stats = [
    'count'      => $total,
    'ongoing'    => 0,
    'completed'  => 0,
    'allocated'  => 0.0,
    'utilized'   => 0.0,
];
$statSql = "SELECT
    COUNT(*) AS cnt,
    SUM(CASE WHEN status = 'ongoing' THEN 1 ELSE 0 END) AS ongoing,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
    SUM(allocated_budget) AS alloc_sum,
    SUM(utilized_budget) AS util_sum
    FROM transparency_programs";
$statRes = $conn->query($statSql);
if ($statRes && $statRes->num_rows > 0) {
    $s = $statRes->fetch_assoc();
    $stats['count']     = (int)($s['cnt'] ?? 0);
    $stats['ongoing']   = (int)($s['ongoing'] ?? 0);
    $stats['completed'] = (int)($s['completed'] ?? 0);
    $stats['allocated'] = (float)($s['alloc_sum'] ?? 0);
    $stats['utilized']  = (float)($s['util_sum'] ?? 0);
}

$queryString = $_GET;
unset($queryString['page']);
$baseQuery = http_build_query($queryString);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transparency Programs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#f3f4f6; }
        .main-content { padding:24px; }
        .page-header { background:linear-gradient(135deg,#4f46e5,#0ea5e9);color:#fff;padding:24px;border-radius:18px;margin-bottom:24px;box-shadow:0 12px 30px rgba(79,70,229,0.25); }
        .page-header h2 { font-weight:700;margin-bottom:4px; }
        .stat-card{background:#fff;border-radius:18px;padding:18px 20px;box-shadow:0 8px 24px rgba(15,23,42,0.06);display:flex;align-items:center;gap:14px;}
        .stat-icon{width:44px;height:44px;border-radius:999px;display:flex;align-items:center;justify-content:center;font-size:20px;}
        .filter-section{background:#fff;border-radius:18px;padding:18px 20px;box-shadow:0 8px 24px rgba(15,23,42,0.04);margin-bottom:18px;}
        .table-card{background:#fff;border-radius:18px;padding:0;box-shadow:0 8px 24px rgba(15,23,42,0.04);}
        .badge-status{padding:4px 10px;border-radius:999px;font-size:12px;}
        .badge-status.planned{background:#eef2ff;color:#4f46e5;}
        .badge-status.ongoing{background:#ecfdf3;color:#16a34a;}
        .badge-status.completed{background:#e0f2fe;color:#0284c7;}
        .badge-status.on-hold{background:#fef3c7;color:#d97706;}
    </style>
</head>
<body>
<?php include('../navbar.php'); ?>

<div class="main-content">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2><i class="bi bi-diagram-3"></i> Transparency Programs</h2>
                <p>Map how campaigns and funds flow into concrete livelihood and relief programs.</p>
            </div>
            <button class="btn btn-light text-primary" data-bs-toggle="modal" data-bs-target="#addProgramModal" style="border-radius:999px;font-weight:600;padding:10px 22px;">
                <i class="bi bi-plus-circle"></i> New Program
            </button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eef2ff;color:#4f46e5;"><i class="bi bi-diagram-3"></i></div>
                <div>
                    <h3><?= number_format($stats['count']); ?></h3>
                    <span>Total programs</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ecfdf3;color:#16a34a;"><i class="bi bi-play-circle"></i></div>
                <div>
                    <h3><?= number_format($stats['ongoing']); ?></h3>
                    <span>Ongoing</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e0f2fe;color:#0284c7;"><i class="bi bi-check2-circle"></i></div>
                <div>
                    <h3><?= number_format($stats['completed']); ?></h3>
                    <span>Completed</span>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-section mb-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" value="<?= e($search); ?>" class="form-control" placeholder="Program name or description...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-select">
                    <option value="">All</option>
                    <?php $cats = ['Livelihood','Emergency Relief','Training','Environmental','Other'];
                    foreach ($cats as $cat) {
                        $val = strtolower(str_replace(' ','_',$cat));
                        $sel = ($categoryF === $val) ? 'selected' : '';
                        echo "<option value=\"{$val}\" {$sel}>{$cat}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <?php $sts = ['planned','ongoing','completed','on-hold'];
                    foreach ($sts as $st) {
                        $sel = ($statusF === $st) ? 'selected' : '';
                        echo "<option value=\"{$st}\" {$sel}>" . ucfirst($st) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Linked Campaign</label>
                <select name="campaign_id" class="form-select">
                    <option value="0">All</option>
                    <?php foreach ($campaignOptions as $co): ?>
                        <option value="<?= (int)$co['id']; ?>" <?= $campaignId === (int)$co['id'] ? 'selected' : ''; ?>><?= e($co['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Sort</label>
                <select name="sort" class="form-select">
                    <option value="status_then_name" <?= $sort === 'status_then_name' ? 'selected' : ''; ?>>Status & Name</option>
                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : ''; ?>>Name A-Z</option>
                    <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : ''; ?>>Name Z-A</option>
                    <option value="budget_high" <?= $sort === 'budget_high' ? 'selected' : ''; ?>>Budget High-Low</option>
                    <option value="budget_low" <?= $sort === 'budget_low' ? 'selected' : ''; ?>>Budget Low-High</option>
                </select>
            </div>
            <div class="col-12 d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Apply Filters</button>
                <a href="transparency_programs.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i> Reset</a>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;" class="text-center">#</th>
                        <th>Program</th>
                        <th>Category & Campaign</th>
                        <th style="width:190px;">Budget</th>
                        <th style="width:130px;">Status</th>
                        <th style="width:200px;">Timeline</th>
                        <th style="width:120px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($programs) === 0): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No programs found yet. Try adjusting filters or add a new program.</td></tr>
                <?php else: ?>
                    <?php $i = 1 + $offset; foreach ($programs as $p): ?>
                    <tr>
                        <td class="text-center"><strong><?= $i++; ?></strong></td>
                        <td>
                            <div class="fw-semibold mb-1"><?= e($p['name']); ?></div>
                            <?php if (!empty($p['description'])): ?>
                                <div class="text-muted small" style="max-width:420px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <?= e($p['description']); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="text-muted small mb-1">Category: <?= e($p['category'] ?: 'N/A'); ?></div>
                            <div class="text-muted small">
                                Campaign: <strong><?= e($p['campaign_name'] ?: 'Not linked'); ?></strong>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">Allocated: ₱<?= number_format((float)$p['allocated_budget'], 2); ?></div>
                            <div class="text-muted small">Utilized: ₱<?= number_format((float)$p['utilized_budget'], 2); ?></div>
                        </td>
                        <td>
                            <?php $st = strtolower($p['status']); ?>
                            <span class="badge-status <?= e($st); ?>"><?= ucfirst($st); ?></span>
                        </td>
                        <td class="small text-muted">
                            <div>
                                <?php if (!empty($p['start_date']) && $p['start_date'] !== '0000-00-00'): ?>
                                    <i class="bi bi-play-fill"></i> <?= date('M d, Y', strtotime($p['start_date'])); ?>
                                <?php else: ?>
                                    <span>No start date</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php if (!empty($p['end_date']) && $p['end_date'] !== '0000-00-00'): ?>
                                    <i class="bi bi-flag-fill"></i> <?= date('M d, Y', strtotime($p['end_date'])); ?>
                                <?php else: ?>
                                    <span>Open ended</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#editProgramModal"
                                data-id="<?= (int)$p['id']; ?>"
                                data-name="<?= e($p['name']); ?>"
                                data-category="<?= e($p['category']); ?>"
                                data-description="<?= e($p['description']); ?>"
                                data-allocated="<?= (float)$p['allocated_budget']; ?>"
                                data-utilized="<?= (float)$p['utilized_budget']; ?>"
                                data-status="<?= e($p['status']); ?>"
                                data-start="<?= e($p['start_date']); ?>"
                                data-end="<?= e($p['end_date']); ?>"
                                data-campaign_id="<?= (int)$p['linked_campaign_id']; ?>"
                                data-location="<?= e($p['location']); ?>"
                                data-sort_order="<?= (int)$p['sort_order']; ?>"
                            >
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
            <div class="text-muted small">
                <?php if ($total > 0): ?>
                    Showing <strong><?= 1 + $offset; ?></strong> to <strong><?= min($offset + $perPage, $total); ?></strong> of <strong><?= $total; ?></strong> programs
                <?php else: ?>
                    No programs to show
                <?php endif; ?>
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php
                    $qs = $baseQuery ? $baseQuery . '&' : '';
                    $prevDisabled = ($page <= 1) ? 'disabled' : '';
                    $nextDisabled = ($page >= $totalPages) ? 'disabled' : '';
                    ?>
                    <li class="page-item <?= $prevDisabled; ?>">
                        <a class="page-link" href="<?= $page > 1 ? 'transparency_programs.php?' . $qs . 'page=' . ($page - 1) : '#'; ?>">Previous</a>
                    </li>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?= 'transparency_programs.php?' . $qs . 'page=' . $p; ?>"><?= $p; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $nextDisabled; ?>">
                        <a class="page-link" href="<?= $page < $totalPages ? 'transparency_programs.php?' . $qs . 'page=' . ($page + 1) : '#'; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Add Program Modal -->
<div class="modal fade" id="addProgramModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf); ?>">
        <input type="hidden" name="action" value="add_program">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-plus-circle"></i> New Program</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Program Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
              <option value="">Select category</option>
              <option value="livelihood">Livelihood Assistance</option>
              <option value="emergency_relief">Emergency Relief</option>
              <option value="training">Training & Capacity Building</option>
              <option value="environmental">Environmental Initiative</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
          </div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Allocated Budget (₱)</label>
              <input type="number" step="0.01" name="allocated_budget" class="form-control" value="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Utilized Budget (₱)</label>
              <input type="number" step="0.01" name="utilized_budget" class="form-control" value="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="planned">Planned</option>
                <option value="ongoing">Ongoing</option>
                <option value="completed">Completed</option>
                <option value="on-hold">On-hold</option>
              </select>
            </div>
          </div>
          <div class="row g-3 mt-2">
            <div class="col-md-4">
              <label class="form-label">Start Date</label>
              <input type="date" name="start_date" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">End Date</label>
              <input type="date" name="end_date" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Linked Campaign</label>
              <select name="linked_campaign_id" class="form-select">
                <option value="0">Not linked</option>
                <?php foreach ($campaignOptions as $co): ?>
                  <option value="<?= (int)$co['id']; ?>"><?= e($co['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="row g-3 mt-2">
            <div class="col-md-8">
              <label class="form-label">Location</label>
              <input type="text" name="location" class="form-control" placeholder="e.g. Barangay X, Municipality Y">
            </div>
            <div class="col-md-4">
              <label class="form-label">Sort Order</label>
              <input type="number" name="sort_order" class="form-control" min="1" value="1">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Program</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Program Modal -->
<div class="modal fade" id="editProgramModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf); ?>">
        <input type="hidden" name="action" value="edit_program">
        <input type="hidden" name="id" id="edit-id" value="">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Program</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Program Name</label>
            <input type="text" name="name" id="edit-name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category" id="edit-category" class="form-select">
              <option value="">Select category</option>
              <option value="livelihood">Livelihood Assistance</option>
              <option value="emergency_relief">Emergency Relief</option>
              <option value="training">Training & Capacity Building</option>
              <option value="environmental">Environmental Initiative</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" id="edit-description" class="form-control" rows="3"></textarea>
          </div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Allocated Budget (₱)</label>
              <input type="number" step="0.01" name="allocated_budget" id="edit-allocated" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Utilized Budget (₱)</label>
              <input type="number" step="0.01" name="utilized_budget" id="edit-utilized" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select name="status" id="edit-status" class="form-select">
                <option value="planned">Planned</option>
                <option value="ongoing">Ongoing</option>
                <option value="completed">Completed</option>
                <option value="on-hold">On-hold</option>
              </select>
            </div>
          </div>
          <div class="row g-3 mt-2">
            <div class="col-md-4">
              <label class="form-label">Start Date</label>
              <input type="date" name="start_date" id="edit-start" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">End Date</label>
              <input type="date" name="end_date" id="edit-end" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Linked Campaign</label>
              <select name="linked_campaign_id" id="edit-campaign_id" class="form-select">
                <option value="0">Not linked</option>
                <?php foreach ($campaignOptions as $co): ?>
                  <option value="<?= (int)$co['id']; ?>"><?= e($co['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="row g-3 mt-2">
            <div class="col-md-8">
              <label class="form-label">Location</label>
              <input type="text" name="location" id="edit-location" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Sort Order</label>
              <input type="number" name="sort_order" id="edit-sort_order" class="form-control" min="1">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const editModal = document.getElementById('editProgramModal');
if (editModal) {
  editModal.addEventListener('show.bs.modal', function (event) {
    const b = event.relatedTarget;
    if (!b) return;
    document.getElementById('edit-id').value          = b.getAttribute('data-id');
    document.getElementById('edit-name').value        = b.getAttribute('data-name') || '';
    document.getElementById('edit-category').value    = b.getAttribute('data-category') || '';
    document.getElementById('edit-description').value = b.getAttribute('data-description') || '';
    document.getElementById('edit-allocated').value   = b.getAttribute('data-allocated') || '0';
    document.getElementById('edit-utilized').value    = b.getAttribute('data-utilized') || '0';
    document.getElementById('edit-status').value      = b.getAttribute('data-status') || 'planned';
    document.getElementById('edit-start').value       = b.getAttribute('data-start') || '';
    document.getElementById('edit-end').value         = b.getAttribute('data-end') || '';
    document.getElementById('edit-campaign_id').value = b.getAttribute('data-campaign_id') || '0';
    document.getElementById('edit-location').value    = b.getAttribute('data-location') || '';
    document.getElementById('edit-sort_order').value  = b.getAttribute('data-sort_order') || '0';
  });
}

<?php if ($alertMsg): ?>
Swal.fire({
  icon: '<?= $alertType === 'success' ? 'success' : 'error'; ?>',
  title: '<?= $alertType === 'success' ? 'Success' : 'Error'; ?>',
  text: '<?= addslashes($alertMsg); ?>',
  confirmButtonColor: '#4f46e5'
});
<?php endif; ?>
</script>
</body>
</html>
