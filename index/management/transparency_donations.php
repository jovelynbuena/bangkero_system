<?php
// Transparency Donations Management
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

// Load campaigns for dropdowns
$campaignOptions = [];
$campRes = $conn->query("SELECT id, name FROM transparency_campaigns ORDER BY name ASC");
while ($r = $campRes->fetch_assoc()) {
    $campaignOptions[] = $r;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!checkCsrf()) {
            throw new Exception('Security check failed. Please refresh the page and try again.');
        }

        $action = $_POST['action'] ?? '';

        if ($action === 'add_donation') {
            $campaign_id    = (int)($_POST['campaign_id'] ?? 0);
            $donor_name     = trim($_POST['donor_name'] ?? '');
            $donor_type     = trim($_POST['donor_type'] ?? '');
            $amount         = (float)($_POST['amount'] ?? 0);
            $currency       = $_POST['currency'] ?? 'PHP';
            $date_received  = $_POST['date_received'] ?: null;
            $payment_method = trim($_POST['payment_method'] ?? '');
            $reference_code = trim($_POST['reference_code'] ?? '');
            $status         = $_POST['status'] ?? 'confirmed';
            $is_restricted  = isset($_POST['is_restricted']) ? 1 : 0;
            $notes          = trim($_POST['notes'] ?? '');

            if ($amount <= 0) {
                throw new Exception('Amount must be greater than zero.');
            }
            if (empty($date_received)) {
                throw new Exception('Date received is required.');
            }

            $stmt = $conn->prepare("INSERT INTO transparency_donations
                (campaign_id, donor_name, donor_type, amount, currency, date_received, payment_method, reference_code, status, is_restricted, notes, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param(
                'issdsssssis',
                $campaign_id,
                $donor_name,
                $donor_type,
                $amount,
                $currency,
                $date_received,
                $payment_method,
                $reference_code,
                $status,
                $is_restricted,
                $notes
            );
            $stmt->execute();
            $stmt->close();

            $alertType = 'success';
            $alertMsg  = 'Donation recorded successfully.';

        } elseif ($action === 'edit_donation') {
            $id            = (int)($_POST['id'] ?? 0);
            $campaign_id    = (int)($_POST['campaign_id'] ?? 0);
            $donor_name     = trim($_POST['donor_name'] ?? '');
            $donor_type     = trim($_POST['donor_type'] ?? '');
            $amount         = (float)($_POST['amount'] ?? 0);
            $currency       = $_POST['currency'] ?? 'PHP';
            $date_received  = $_POST['date_received'] ?: null;
            $payment_method = trim($_POST['payment_method'] ?? '');
            $reference_code = trim($_POST['reference_code'] ?? '');
            $status         = $_POST['status'] ?? 'confirmed';
            $is_restricted  = isset($_POST['is_restricted']) ? 1 : 0;
            $notes          = trim($_POST['notes'] ?? '');

            if ($id <= 0) {
                throw new Exception('Invalid donation ID.');
            }
            if ($amount <= 0) {
                throw new Exception('Amount must be greater than zero.');
            }
            if (empty($date_received)) {
                throw new Exception('Date received is required.');
            }

            $stmt = $conn->prepare("UPDATE transparency_donations
                SET campaign_id = ?, donor_name = ?, donor_type = ?, amount = ?, currency = ?, date_received = ?, payment_method = ?, reference_code = ?, status = ?, is_restricted = ?, notes = ?, updated_at = NOW()
                WHERE id = ?");
            $stmt->bind_param(
                'issdssssissi',
                $campaign_id,
                $donor_name,
                $donor_type,
                $amount,
                $currency,
                $date_received,
                $payment_method,
                $reference_code,
                $status,
                $is_restricted,
                $notes,
                $id
            );
            $stmt->execute();
            $stmt->close();

            $alertType = 'success';
            $alertMsg  = 'Donation updated successfully.';
        }
    }
} catch (Exception $ex) {
    $alertType = 'error';
    $alertMsg  = $ex->getMessage();
}

// Filters
$search     = trim($_GET['search'] ?? '');
$campaignId = (int)($_GET['campaign_id'] ?? 0);
$statusFilt = trim($_GET['status'] ?? '');
$dateFrom   = $_GET['date_from'] ?? '';
$dateTo     = $_GET['date_to'] ?? '';
$minAmount  = $_GET['min_amount'] ?? '';
$maxAmount  = $_GET['max_amount'] ?? '';
$sort       = $_GET['sort'] ?? 'date_new';

$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where[] = "(donor_name LIKE CONCAT('%', ?, '%') OR reference_code LIKE CONCAT('%', ?, '%'))";
    $params[] = $search;
    $params[] = $search;
    $types   .= 'ss';
}

if ($campaignId > 0) {
    $where[]  = "campaign_id = ?";
    $params[] = $campaignId;
    $types   .= 'i';
}

if ($statusFilt !== '') {
    $where[]  = "status = ?";
    $params[] = $statusFilt;
    $types   .= 's';
}

if ($dateFrom !== '') {
    $where[]  = "date_received >= ?";
    $params[] = $dateFrom;
    $types   .= 's';
}

if ($dateTo !== '') {
    $where[]  = "date_received <= ?";
    $params[] = $dateTo;
    $types   .= 's';
}

if ($minAmount !== '' && is_numeric($minAmount)) {
    $where[]  = "amount >= ?";
    $params[] = (float)$minAmount;
    $types   .= 'd';
}

if ($maxAmount !== '' && is_numeric($maxAmount)) {
    $where[]  = "amount <= ?";
    $params[] = (float)$maxAmount;
    $types   .= 'd';
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

switch ($sort) {
    case 'amount_high':
        $orderSql = 'ORDER BY amount DESC';
        break;
    case 'amount_low':
        $orderSql = 'ORDER BY amount ASC';
        break;
    case 'name_asc':
        $orderSql = 'ORDER BY donor_name ASC';
        break;
    case 'name_desc':
        $orderSql = 'ORDER BY donor_name DESC';
        break;
    case 'date_old':
        $orderSql = 'ORDER BY date_received ASC';
        break;
    case 'date_new':
    default:
        $orderSql = 'ORDER BY date_received DESC';
        break;
}

$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 10;
$offset   = ($page - 1) * $perPage;
$total    = 0;
$totalPages = 1;

$countSql = "SELECT COUNT(*) AS total FROM transparency_donations {$whereSql}";
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

$listSql = "SELECT d.*, c.name AS campaign_name
            FROM transparency_donations d
            LEFT JOIN transparency_campaigns c ON d.campaign_id = c.id
            {$whereSql}
            {$orderSql}
            LIMIT {$perPage} OFFSET {$offset}";

if ($types) {
    $stmt = $conn->prepare($listSql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $donRes = $stmt->get_result();
} else {
    $donRes = $conn->query($listSql);
}

$donations = [];
while ($row = $donRes->fetch_assoc()) {
    $donations[] = $row;
}

// Quick stats for top cards
$stats = [
    'count'         => 0,
    'sum_confirmed' => 0.0,
    'sum_pending'   => 0.0,
];
$statSql = "SELECT
    COUNT(*) AS cnt,
    SUM(CASE WHEN status = 'confirmed' THEN amount ELSE 0 END) AS sum_confirmed,
    SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) AS sum_pending
    FROM transparency_donations";
$statRes = $conn->query($statSql);
if ($statRes && $statRes->num_rows > 0) {
    $s = $statRes->fetch_assoc();
    $stats['count']         = (int)($s['cnt'] ?? 0);
    $stats['sum_confirmed'] = (float)($s['sum_confirmed'] ?? 0);
    $stats['sum_pending']   = (float)($s['sum_pending'] ?? 0);
}

$queryString = $_GET;
unset($queryString['page']);
$baseQuery = http_build_query($queryString);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transparency Donations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#f3f4f6; }
        .main-content { padding:24px; }
        .page-header {
            background:linear-gradient(135deg,#4f46e5,#0ea5e9);
            color:#fff;
            padding:24px;
            border-radius:18px;
            margin-bottom:24px;
            box-shadow:0 12px 30px rgba(79,70,229,0.25);
        }
        .page-header h2 { font-weight:700; margin-bottom:4px; }
        .stat-card{background:#fff;border-radius:18px;padding:18px 20px;box-shadow:0 8px 24px rgba(15,23,42,0.06);display:flex;align-items:center;gap:14px;}
        .stat-icon{width:44px;height:44px;border-radius:999px;display:flex;align-items:center;justify-content:center;font-size:20px;}
        .filter-section{background:#fff;border-radius:18px;padding:18px 20px;box-shadow:0 8px 24px rgba(15,23,42,0.04);margin-bottom:18px;}
        .table-card{background:#fff;border-radius:18px;padding:0;box-shadow:0 8px 24px rgba(15,23,42,0.04);}
        .badge-status{padding:4px 10px;border-radius:999px;font-size:12px;}
        .badge-status.confirmed{background:#ecfdf3;color:#16a34a;}
        .badge-status.pending{background:#fef3c7;color:#d97706;}
        .badge-status.failed{background:#fee2e2;color:#b91c1c;}
        .badge-status.refunded{background:#e0f2fe;color:#0284c7;}
        .badge-status.cancelled{background:#fee2e2;color:#b91c1c;}
    </style>
</head>
<body>
<?php include('../navbar.php'); ?>

<div class="main-content">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2><i class="bi bi-cash-coin"></i> Transparency Donations</h2>
                <p>Record and review all donations linked to transparency campaigns.</p>
            </div>
            <button class="btn btn-light text-primary" data-bs-toggle="modal" data-bs-target="#addDonationModal" style="border-radius:999px;font-weight:600;padding:10px 22px;">
                <i class="bi bi-plus-circle"></i> New Donation
            </button>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eef2ff;color:#4f46e5;"><i class="bi bi-receipt"></i></div>
                <div>
                    <h3><?= number_format($stats['count']); ?></h3>
                    <span>Total donations</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ecfdf3;color:\#16a34a;"><i class="bi bi-check-circle"></i></div>
                <div>
                    <h3>₱<?= number_format($stats['sum_confirmed'], 2); ?></h3>
                    <span>Confirmed donations</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <h3>₱<?= number_format($stats['sum_pending'], 2); ?></h3>
                    <span>Pending donations</span>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-section mb-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search (Donor / Ref No.)</label>
                <input type="text" name="search" value="<?= e($search); ?>" class="form-control" placeholder="Name or reference code...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Campaign</label>
                <select name="campaign_id" class="form-select">
                    <option value="0">All campaigns</option>
                    <?php foreach ($campaignOptions as $co): ?>
                        <option value="<?= (int)$co['id']; ?>" <?= $campaignId === (int)$co['id'] ? 'selected' : ''; ?>>
                            <?= e($co['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <?php
                    $stOptions = ['confirmed','pending','failed','refunded','cancelled'];
                    foreach ($stOptions as $st) {
                        $sel = ($statusFilt === $st) ? 'selected' : '';
                        echo "<option value=\"{$st}\" {$sel}>" . ucfirst($st) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" value="<?= e($dateFrom); ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" value="<?= e($dateTo); ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Min Amount</label>
                <input type="number" step="0.01" name="min_amount" value="<?= e($minAmount); ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Max Amount</label>
                <input type="number" step="0.01" name="max_amount" value="<?= e($maxAmount); ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sort</label>
                <select name="sort" class="form-select">
                    <option value="date_new" <?= $sort === 'date_new' ? 'selected' : ''; ?>>Newest</option>
                    <option value="date_old" <?= $sort === 'date_old' ? 'selected' : ''; ?>>Oldest</option>
                    <option value="amount_high" <?= $sort === 'amount_high' ? 'selected' : ''; ?>>Amount High-Low</option>
                    <option value="amount_low" <?= $sort === 'amount_low' ? 'selected' : ''; ?>>Amount Low-High</option>
                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : ''; ?>>Donor A-Z</option>
                    <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : ''; ?>>Donor Z-A</option>
                </select>
            </div>
            <div class="col-12 d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Apply Filters</button>
                <a href="transparency_donations.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i> Reset</a>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;" class="text-center">#</th>
                        <th>Donor</th>
                        <th>Campaign</th>
                        <th style="width:150px;">Amount</th>
                        <th style="width:140px;">Status</th>
                        <th style="width:160px;">Date Received</th>
                        <th style="width:160px;">Reference</th>
                        <th style="width:120px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($donations) === 0): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No donations found yet. Try adjusting filters or add a new donation.</td>
                    </tr>
                <?php else: ?>
                    <?php $i = 1 + $offset; foreach ($donations as $d): ?>
                    <tr>
                        <td class="text-center"><strong><?= $i++; ?></strong></td>
                        <td>
                            <div class="fw-semibold mb-1"><?= e($d['donor_name'] ?: 'Anonymous'); ?></div>
                            <?php if (!empty($d['donor_type'])): ?>
                                <div class="text-muted small">Type: <?= e(ucfirst($d['donor_type'])); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="small fw-semibold"><?= e($d['campaign_name'] ?: 'Unassigned'); ?></div>
                        </td>
                        <td>
                            <div class="fw-semibold">₱<?= number_format((float)$d['amount'], 2); ?></div>
                            <?php if (!empty($d['currency']) && strtoupper($d['currency']) !== 'PHP'): ?>
                                <div class="text-muted small">Currency: <?= e(strtoupper($d['currency'])); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php $st = strtolower($d['status']); ?>
                            <span class="badge-status <?= e($st); ?>"><?= ucfirst($st); ?></span>
                            <?php if ((int)$d['is_restricted'] === 1): ?>
                                <div class="text-muted small"><i class="bi bi-lock"></i> Restricted</div>
                            <?php else: ?>
                                <div class="text-muted small"><i class="bi bi-unlock"></i> General fund</div>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted">
                            <?= !empty($d['date_received']) && $d['date_received'] !== '0000-00-00'
                                ? date('M d, Y', strtotime($d['date_received']))
                                : 'N/A'; ?>
                        </td>
                        <td>
                            <div class="small fw-semibold"><?= e($d['reference_code']); ?></div>
                            <?php if (!empty($d['payment_method'])): ?>
                                <div class="text-muted small"><i class="bi bi-credit-card"></i> <?= e(ucfirst($d['payment_method'])); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#editDonationModal"
                                data-id="<?= (int)$d['id']; ?>"
                                data-campaign_id="<?= (int)$d['campaign_id']; ?>"
                                data-donor_name="<?= e($d['donor_name']); ?>"
                                data-donor_type="<?= e($d['donor_type']); ?>"
                                data-amount="<?= (float)$d['amount']; ?>"
                                data-currency="<?= e($d['currency']); ?>"
                                data-date_received="<?= e($d['date_received']); ?>"
                                data-payment_method="<?= e($d['payment_method']); ?>"
                                data-reference_code="<?= e($d['reference_code']); ?>"
                                data-status="<?= e($d['status']); ?>"
                                data-is_restricted="<?= (int)$d['is_restricted']; ?>"
                                data-notes="<?= e($d['notes']); ?>"
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
                    Showing <strong><?= 1 + $offset; ?></strong> to <strong><?= min($offset + $perPage, $total); ?></strong> of <strong><?= $total; ?></strong> donations
                <?php else: ?>
                    No donations to show
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
                        <a class="page-link" href="<?= $page > 1 ? 'transparency_donations.php?' . $qs . 'page=' . ($page - 1) : '#'; ?>">Previous</a>
                    </li>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?= 'transparency_donations.php?' . $qs . 'page=' . $p; ?>"><?= $p; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $nextDisabled; ?>">
                        <a class="page-link" href="<?= $page < $totalPages ? 'transparency_donations.php?' . $qs . 'page=' . ($page + 1) : '#'; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Add Donation Modal -->
<div class="modal fade" id="addDonationModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf); ?>">
        <input type="hidden" name="action" value="add_donation">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-plus-circle"></i> New Donation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Campaign</label>
              <select name="campaign_id" class="form-select">
                <option value="0">Unassigned / General fund</option>
                <?php foreach ($campaignOptions as $co): ?>
                  <option value="<?= (int)$co['id']; ?>"><?= e($co['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Donor Name</label>
              <input type="text" name="donor_name" class="form-control" placeholder="Leave blank for Anonymous">
            </div>
            <div class="col-md-4">
              <label class="form-label">Donor Type</label>
              <select name="donor_type" class="form-select">
                <option value="">Not specified</option>
                <option value="individual">Individual</option>
                <option value="organization">Organization</option>
                <option value="corporate">Corporate</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Amount (₱)</label>
              <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Currency</label>
              <input type="text" name="currency" value="PHP" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Date Received</label>
              <input type="date" name="date_received" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Payment Method</label>
              <input type="text" name="payment_method" class="form-control" placeholder="Cash, GCash, bank transfer...">
            </div>
            <div class="col-md-4">
              <label class="form-label">Reference Code</label>
              <input type="text" name="reference_code" class="form-control" placeholder="OR#, transaction ID, etc.">
            </div>
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="confirmed">Confirmed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
                <option value="refunded">Refunded</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="col-md-4 d-flex align-items-center">
              <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" name="is_restricted" id="addRestrictedChk">
                <label class="form-check-label" for="addRestrictedChk">Restricted fund</label>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes about this donation..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Donation</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Donation Modal -->
<div class="modal fade" id="editDonationModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= e($csrf); ?>">
        <input type="hidden" name="action" value="edit_donation">
        <input type="hidden" name="id" id="edit-id" value="">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Donation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Campaign</label>
              <select name="campaign_id" id="edit-campaign_id" class="form-select">
                <option value="0">Unassigned / General fund</option>
                <?php foreach ($campaignOptions as $co): ?>
                  <option value="<?= (int)$co['id']; ?>"><?= e($co['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Donor Name</label>
              <input type="text" name="donor_name" id="edit-donor_name" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Donor Type</label>
              <select name="donor_type" id="edit-donor_type" class="form-select">
                <option value="">Not specified</option>
                <option value="individual">Individual</option>
                <option value="organization">Organization</option>
                <option value="corporate">Corporate</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Amount (₱)</label>
              <input type="number" step="0.01" name="amount" id="edit-amount" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Currency</label>
              <input type="text" name="currency" id="edit-currency" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Date Received</label>
              <input type="date" name="date_received" id="edit-date_received" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Payment Method</label>
              <input type="text" name="payment_method" id="edit-payment_method" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Reference Code</label>
              <input type="text" name="reference_code" id="edit-reference_code" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select name="status" id="edit-status" class="form-select">
                <option value="confirmed">Confirmed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
                <option value="refunded">Refunded</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="col-md-4 d-flex align-items-center">
              <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" name="is_restricted" id="edit-is_restricted">
                <label class="form-check-label" for="edit-is_restricted">Restricted fund</label>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea name="notes" id="edit-notes" class="form-control" rows="3"></textarea>
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
const editModal = document.getElementById('editDonationModal');
if (editModal) {
  editModal.addEventListener('show.bs.modal', function (event) {
    const b = event.relatedTarget;
    if (!b) return;
    document.getElementById('edit-id').value              = b.getAttribute('data-id');
    document.getElementById('edit-campaign_id').value     = b.getAttribute('data-campaign_id') || '0';
    document.getElementById('edit-donor_name').value      = b.getAttribute('data-donor_name') || '';
    document.getElementById('edit-donor_type').value      = b.getAttribute('data-donor_type') || '';
    document.getElementById('edit-amount').value          = b.getAttribute('data-amount') || '';
    document.getElementById('edit-currency').value        = b.getAttribute('data-currency') || 'PHP';
    document.getElementById('edit-date_received').value   = b.getAttribute('data-date_received') || '';
    document.getElementById('edit-payment_method').value  = b.getAttribute('data-payment_method') || '';
    document.getElementById('edit-reference_code').value  = b.getAttribute('data-reference_code') || '';
    document.getElementById('edit-status').value          = b.getAttribute('data-status') || 'confirmed';
    document.getElementById('edit-notes').value           = b.getAttribute('data-notes') || '';
    const isRes = parseInt(b.getAttribute('data-is_restricted') || '0', 10) === 1;
    document.getElementById('edit-is_restricted').checked = isRes;
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
