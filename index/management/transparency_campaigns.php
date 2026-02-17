<?php
// Transparency Campaigns Management
session_start();

require_once('../../config/db_connect.php');

// Enforce authentication
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

// Simple escape helper
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// CSRF token
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!checkCsrf()) {
            $alertType = 'error';
            $alertMsg  = 'Security check failed. Please refresh the page and try again.';
        } else {
            $action = $_POST['action'] ?? '';

            if ($action === 'add_campaign') {
                $name        = trim($_POST['name'] ?? '');
                $slug        = trim($_POST['slug'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $goal_amount = (float) ($_POST['goal_amount'] ?? 0);
                $status      = $_POST['status'] ?? 'planned';
                $start_date  = $_POST['start_date'] ?: null;
                $end_date    = $_POST['end_date'] ?: null;
                $banner      = trim($_POST['banner_image'] ?? '');

                if ($name === '') {
                    throw new Exception('Campaign name is required.');
                }

                if ($slug === '') {
                    // Simple slug generator from name
                    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
                    $slug = trim($slug, '-');
                }

                $stmt = $conn->prepare("INSERT INTO transparency_campaigns
                    (name, slug, description, goal_amount, status, start_date, end_date, banner_image, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt->bind_param(
                    'sssdsiss',
                    $name,
                    $slug,
                    $description,
                    $goal_amount,
                    $status,
                    $start_date,
                    $end_date,
                    $banner
                );
                $stmt->execute();
                $stmt->close();

                $alertType = 'success';
                $alertMsg  = 'Campaign created successfully.';

            } elseif ($action === 'edit_campaign') {
                $id          = (int) ($_POST['id'] ?? 0);
                $name        = trim($_POST['name'] ?? '');
                $slug        = trim($_POST['slug'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $goal_amount = (float) ($_POST['goal_amount'] ?? 0);
                $status      = $_POST['status'] ?? 'planned';
                $start_date  = $_POST['start_date'] ?: null;
                $end_date    = $_POST['end_date'] ?: null;
                $banner      = trim($_POST['banner_image'] ?? '');

                if ($id <= 0) {
                    throw new Exception('Invalid campaign ID.');
                }
                if ($name === '') {
                    throw new Exception('Campaign name is required.');
                }
                if ($slug === '') {
                    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
                    $slug = trim($slug, '-');
                }

                $stmt = $conn->prepare("UPDATE transparency_campaigns
                    SET name = ?, slug = ?, description = ?, goal_amount = ?, status = ?, start_date = ?, end_date = ?, banner_image = ?, updated_at = NOW()
                    WHERE id = ?");
                $stmt->bind_param(
                    'sssdsissi',
                    $name,
                    $slug,
                    $description,
                    $goal_amount,
                    $status,
                    $start_date,
                    $end_date,
                    $banner,
                    $id
                );
                $stmt->execute();
                $stmt->close();

                $alertType = 'success';
                $alertMsg  = 'Campaign updated successfully.';
            }
        }
    }
} catch (Exception $ex) {
    $alertType = 'error';
    $alertMsg  = $ex->getMessage();
}

// Filters
$search     = trim($_GET['search'] ?? '');
$statusFilt = trim($_GET['status'] ?? '');
$dateFrom   = $_GET['date_from'] ?? '';
$dateTo     = $_GET['date_to'] ?? '';
$sort       = $_GET['sort'] ?? 'date_new';

$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where[] = "(name LIKE CONCAT('%', ?, '%') OR description LIKE CONCAT('%', ?, '%'))";
    $params[] = $search;
    $params[] = $search;
    $types   .= 'ss';
}

if ($statusFilt !== '') {
    $where[]  = "status = ?";
    $params[] = $statusFilt;
    $types   .= 's';
}

if ($dateFrom !== '') {
    $where[]  = "(start_date >= ? OR start_date IS NULL)";
    $params[] = $dateFrom;
    $types   .= 's';
}

if ($dateTo !== '') {
    $where[]  = "(end_date <= ? OR end_date IS NULL)";
    $params[] = $dateTo;
    $types   .= 's';
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

// Sorting
switch ($sort) {
    case 'name_asc':
        $orderSql = 'ORDER BY name ASC';
        break;
    case 'name_desc':
        $orderSql = 'ORDER BY name DESC';
        break;
    case 'goal_high':
        $orderSql = 'ORDER BY goal_amount DESC';
        break;
    case 'goal_low':
        $orderSql = 'ORDER BY goal_amount ASC';
        break;
    case 'date_old':
        $orderSql = 'ORDER BY created_at ASC';
        break;
    case 'date_new':
    default:
        $orderSql = 'ORDER BY created_at DESC';
        break;
}

// Pagination
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 10;
$offset   = ($page - 1) * $perPage;
$total    = 0;
$totalPages = 1;

// Count query
$countSql = "SELECT COUNT(*) AS total FROM transparency_campaigns {$whereSql}";
if ($types) {
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $total = (int) ($row['total'] ?? 0);
    $stmt->close();
} else {
    $res = $conn->query($countSql);
    $row = $res->fetch_assoc();
    $total = (int) ($row['total'] ?? 0);
}

if ($total > 0) {
    $totalPages = max(1, (int) ceil($total / $perPage));
    if ($page > $totalPages) {
        $page   = $totalPages;
        $offset = ($page - 1) * $perPage;
    }
}

// Fetch campaigns
$listSql = "SELECT * FROM transparency_campaigns {$whereSql} {$orderSql} LIMIT {$perPage} OFFSET {$offset}";

if ($types) {
    $stmt = $conn->prepare($listSql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $campaignsRes = $stmt->get_result();
} else {
    $campaignsRes = $conn->query($listSql);
}

$campaigns = [];
while ($row = $campaignsRes->fetch_assoc()) {
    $campaigns[] = $row;
}

// Quick stats
$stats = [
    'total'   => $total,
    'active'  => 0,
    'raised'  => 0.0,
    'goalSum' => 0.0,
];

$statSql = "SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active,
    SUM(goal_amount) AS goal_sum
    FROM transparency_campaigns";
$statRes = $conn->query($statSql);
if ($statRes && $statRes->num_rows > 0) {
    $sRow = $statRes->fetch_assoc();
    $stats['total']   = (int) ($sRow['total'] ?? 0);
    $stats['active']  = (int) ($sRow['active'] ?? 0);
    $stats['goalSum'] = (float) ($sRow['goal_sum'] ?? 0);
}

// Total raised (across all confirmed donations)
$raisedRes = $conn->query("SELECT SUM(amount) AS total_raised FROM transparency_donations WHERE status = 'confirmed'");
if ($raisedRes && $raisedRes->num_rows > 0) {
    $rRow = $raisedRes->fetch_assoc();
    $stats['raised'] = (float) ($rRow['total_raised'] ?? 0);
}

$queryString = $_GET;
unset($queryString['page']);
$baseQuery = http_build_query($queryString);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transparency Campaigns</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f3f4f6;
        }
        .main-content {
            padding: 24px;
        }
        .page-header {
            background: linear-gradient(135deg, #4f46e5, #0ea5e9);
            color: #fff;
            padding: 24px 24px;
            border-radius: 18px;
            margin-bottom: 24px;
            box-shadow: 0 12px 30px rgba(79,70,229,0.25);
        }
        .page-header h2 {
            font-weight: 700;
            margin-bottom: 4px;
        }
        .page-header p {
            margin: 0;
            opacity: 0.9;
        }
        .stat-card {
            background: #fff;
            border-radius: 18px;
            padding: 18px 20px;
            box-shadow: 0 8px 24px rgba(15,23,42,0.06);
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            color: #4f46e5;
            font-size: 20px;
        }
        .stat-card h3 {
            margin: 0;
            font-weight: 700;
        }
        .stat-card span {
            font-size: 13px;
            color: #6b7280;
        }
        .filter-section {
            background: #fff;
            border-radius: 18px;
            padding: 18px 20px;
            box-shadow: 0 8px 24px rgba(15,23,42,0.04);
            margin-bottom: 18px;
        }
        .table-card {
            background: #fff;
            border-radius: 18px;
            padding: 0;
            box-shadow: 0 8px 24px rgba(15,23,42,0.04);
        }
        .table thead {
            background: #f9fafb;
        }
        .badge-status {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
        }
        .badge-status.planned {
            background: #eef2ff;
            color: #4f46e5;
        }
        .badge-status.active {
            background: #ecfdf3;
            color: #16a34a;
        }
        .badge-status.completed {
            background: #e0f2fe;
            color: #0284c7;
        }
        .badge-status.paused {
            background: #fef3c7;
            color: #d97706;
        }
        .badge-status.cancelled {
            background: #fee2e2;
            color: #b91c1c;
        }
        .pagination-summary {
            font-size: 13px;
            color: #6b7280;
        }
    </style>
</head>
<body>
<?php include('../navbar.php'); ?>

<div class="main-content">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2><i class="bi bi-bullseye"></i> Transparency Campaigns</h2>
                <p>Manage fundraising campaigns that power your community programs.</p>
            </div>
            <button class="btn btn-light text-primary" data-bs-toggle="modal" data-bs-target="#addCampaignModal" style="border-radius: 999px; font-weight: 600; padding: 10px 22px;">
                <i class="bi bi-plus-circle"></i> New Campaign
            </button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-bullseye"></i></div>
                <div>
                    <h3><?= number_format($stats['total']); ?></h3>
                    <span>Total campaigns</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ecfdf3;color:#16a34a;"><i class="bi bi-lightning-charge"></i></div>
                <div>
                    <h3><?= number_format($stats['active']); ?></h3>
                    <span>Active campaigns</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eff6ff;color:#0ea5e9;"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <h3>₱<?= number_format($stats['raised'], 2); ?></h3>
                    <span>Total confirmed donations</span>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-section mb-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" value="<?= e($search); ?>" class="form-control" placeholder="Name or description...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <?php
                    $statusOptions = ['planned','active','completed','paused','cancelled'];
                    foreach ($statusOptions as $st) {
                        $sel = ($statusFilt === $st) ? 'selected' : '';
                        echo "<option value=\"{$st}\" {$sel}>" . ucfirst($st) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Start From</label>
                <input type="date" name="date_from" value="<?= e($dateFrom); ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">End Until</label>
                <input type="date" name="date_to" value="<?= e($dateTo); ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sort</label>
                <select name="sort" class="form-select">
                    <option value="date_new" <?= $sort === 'date_new' ? 'selected' : ''; ?>>Newest</option>
                    <option value="date_old" <?= $sort === 'date_old' ? 'selected' : ''; ?>>Oldest</option>
                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : ''; ?>>Name A-Z</option>
                    <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : ''; ?>>Name Z-A</option>
                    <option value="goal_high" <?= $sort === 'goal_high' ? 'selected' : ''; ?>>Goal High-Low</option>
                    <option value="goal_low" <?= $sort === 'goal_low' ? 'selected' : ''; ?>>Goal Low-High</option>
                </select>
            </div>
            <div class="col-12 d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Apply Filters
                </button>
                <a href="transparency_campaigns.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;" class="text-center">#</th>
                        <th>Campaign</th>
                        <th style="width:160px;">Goal Amount</th>
                        <th style="width:130px;">Status</th>
                        <th style="width:190px;">Timeline</th>
                        <th style="width:160px;">Created</th>
                        <th style="width:130px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($campaigns) === 0): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No campaigns found yet. Try adjusting filters or create a new campaign.</td>
                    </tr>
                <?php else: ?>
                    <?php $i = 1 + $offset; foreach ($campaigns as $c): ?>
                    <tr>
                        <td class="text-center"><strong><?= $i++; ?></strong></td>
                        <td>
                            <div class="fw-semibold mb-1"><?= e($c['name']); ?></div>
                            <div class="text-muted small mb-1">Slug: <code><?= e($c['slug']); ?></code></div>
                            <?php if (!empty($c['description'])): ?>
                                <div class="text-muted small" style="max-width:480px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <?= e($c['description']); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-semibold">₱<?= number_format((float)$c['goal_amount'], 2); ?></div>
                        </td>
                        <td>
                            <?php $st = strtolower($c['status']); ?>
                            <span class="badge-status <?= e($st); ?>"><?= ucfirst($st); ?></span>
                        </td>
                        <td>
                            <div class="small text-muted">
                                <?php if (!empty($c['start_date']) && $c['start_date'] !== '0000-00-00'): ?>
                                    <i class="bi bi-play-fill"></i> <?= date('M d, Y', strtotime($c['start_date'])); ?>
                                <?php else: ?>
                                    <span class="text-muted">No start date</span>
                                <?php endif; ?>
                            </div>
                            <div class="small text-muted">
                                <?php if (!empty($c['end_date']) && $c['end_date'] !== '0000-00-00'): ?>
                                    <i class="bi bi-flag-fill"></i> <?= date('M d, Y', strtotime($c['end_date'])); ?>
                                <?php else: ?>
                                    <span class="text-muted">Open ended</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="small text-muted">
                            <?= date('M d, Y H:i', strtotime($c['created_at'])); ?>
                        </td>
                        <td class="text-center">
                            <button
                                class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#editCampaignModal"
                                data-id="<?= (int)$c['id']; ?>"
                                data-name="<?= e($c['name']); ?>"
                                data-slug="<?= e($c['slug']); ?>"
                                data-description="<?= e($c['description']); ?>"
                                data-goal="<?= (float)$c['goal_amount']; ?>"
                                data-status="<?= e($c['status']); ?>"
                                data-start="<?= e($c['start_date']); ?>"
                                data-end="<?= e($c['end_date']); ?>"
                                data-banner="<?= e($c['banner_image']); ?>"
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
            <div class="pagination-summary">
                <?php if ($total > 0): ?>
                    Showing <strong><?= 1 + $offset; ?></strong> to <strong><?= min($offset + $perPage, $total); ?></strong> of <strong><?= $total; ?></strong> campaigns
                <?php else: ?>
                    No campaigns to show
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
                        <a class="page-link" href="<?= $page > 1 ? 'transparency_campaigns.php?' . $qs . 'page=' . ($page - 1) : '#'; ?>">Previous</a>
                    </li>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?= 'transparency_campaigns.php?' . $qs . 'page=' . $p; ?>"><?= $p; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $nextDisabled; ?>">
                        <a class="page-link" href="<?= $page < $totalPages ? 'transparency_campaigns.php?' . $qs . 'page=' . ($page + 1) : '#'; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Add Campaign Modal -->
<div class="modal fade" id="addCampaignModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf); ?>">
        <input type="hidden" name="action" value="add_campaign">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-plus-circle"></i> New Campaign</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Campaign Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Slug (optional)</label>
            <input type="text" name="slug" class="form-control" placeholder="auto-generated if empty">
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
          </div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Goal Amount (₱)</label>
              <input type="number" step="0.01" name="goal_amount" class="form-control" value="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="planned">Planned</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="paused">Paused</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Banner Image Path (optional)</label>
              <input type="text" name="banner_image" class="form-control" placeholder="e.g. uploads/transparency/campaigns/banner.jpg">
            </div>
          </div>
          <div class="row g-3 mt-2">
            <div class="col-md-6">
              <label class="form-label">Start Date</label>
              <input type="date" name="start_date" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">End Date</label>
              <input type="date" name="end_date" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Campaign</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Campaign Modal -->
<div class="modal fade" id="editCampaignModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf); ?>">
        <input type="hidden" name="action" value="edit_campaign">
        <input type="hidden" name="id" id="edit-id" value="">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Campaign</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Campaign Name</label>
            <input type="text" name="name" id="edit-name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" id="edit-slug" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" id="edit-description" class="form-control" rows="3"></textarea>
          </div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Goal Amount (₱)</label>
              <input type="number" step="0.01" name="goal_amount" id="edit-goal" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select name="status" id="edit-status" class="form-select">
                <option value="planned">Planned</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="paused">Paused</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Banner Image Path</label>
              <input type="text" name="banner_image" id="edit-banner" class="form-control">
            </div>
          </div>
          <div class="row g-3 mt-2">
            <div class="col-md-6">
              <label class="form-label">Start Date</label>
              <input type="date" name="start_date" id="edit-start" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">End Date</label>
              <input type="date" name="end_date" id="edit-end" class="form-control">
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
// Fill edit modal
const editModal = document.getElementById('editCampaignModal');
if (editModal) {
  editModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    if (!button) return;

    document.getElementById('edit-id').value          = button.getAttribute('data-id');
    document.getElementById('edit-name').value        = button.getAttribute('data-name');
    document.getElementById('edit-slug').value        = button.getAttribute('data-slug');
    document.getElementById('edit-description').value = button.getAttribute('data-description');
    document.getElementById('edit-goal').value        = button.getAttribute('data-goal');
    document.getElementById('edit-status').value      = button.getAttribute('data-status');
    document.getElementById('edit-start').value       = button.getAttribute('data-start') || '';
    document.getElementById('edit-end').value         = button.getAttribute('data-end') || '';
    document.getElementById('edit-banner').value      = button.getAttribute('data-banner');
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
