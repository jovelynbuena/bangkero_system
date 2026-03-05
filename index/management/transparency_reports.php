<?php
// Transparency Reports Page
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

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Association name for reports
$assocName = 'Bankero and Fishermen Association';

// Get filter values
$dateFrom = $_GET['date_from'] ?? date('Y-01-01'); // Default: Jan 1 of current year
$dateTo = $_GET['date_to'] ?? date('Y-m-d'); // Default: today
$sourceFilter = $_GET['source'] ?? 'all';
$programFilter = $_GET['program'] ?? 'all';
$reportType = $_GET['report_type'] ?? 'summary';
$exportFormat = $_GET['export'] ?? ''; // 'csv' or 'excel'

// If export requested, handle it before any HTML output
if ($exportFormat === 'csv' || $exportFormat === 'excel') {
    // Fetch all transactions for export
    $whereDate = "date_received BETWEEN '$dateFrom' AND '$dateTo'";
    
    $sql = "SELECT d.date_received, d.donor_name, d.donor_type, d.amount, 
            d.reference_code, d.notes, c.name as program_name 
            FROM transparency_donations d
            LEFT JOIN transparency_campaigns c ON d.campaign_id = c.id
            WHERE d.$whereDate";
    
    if ($sourceFilter !== 'all') {
        $sql .= " AND d.donor_type = '" . $conn->real_escape_string($sourceFilter) . "'";
    }
    if ($programFilter !== 'all') {
        $sql .= " AND d.campaign_id = " . (int)$programFilter;
    }
    $sql .= " ORDER BY d.date_received DESC";
    
    $result = $conn->query($sql);
    
    $filename = 'transparency_report_' . date('Y-m-d') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Headers
    fputcsv($output, ['Transparency Report - ' . $assocName]);
    fputcsv($output, ['Period: ' . date('M d, Y', strtotime($dateFrom)) . ' - ' . date('M d, Y', strtotime($dateTo))]);
    fputcsv($output, ['Generated: ' . date('F d, Y h:i A')]);
    fputcsv($output, []); // Empty row
    fputcsv($output, ['Date Received', 'Source Name', 'Source Type', 'Program', 'Reference', 'Amount', 'Notes']);
    
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['date_received'] ? date('M d, Y', strtotime($row['date_received'])) : '',
            $row['donor_name'],
            $row['donor_type'],
            $row['program_name'] ?: 'Not linked',
            $row['reference_code'] ?: '',
            $row['amount'],
            $row['notes'] ?: ''
        ]);
        $total += $row['amount'];
    }
    
    fputcsv($output, []); // Empty row
    fputcsv($output, ['', '', '', '', 'TOTAL:', $total, '']);
    
    fclose($output);
    exit;
}

// If PDF export requested - redirect to PDF export page
if ($exportFormat === 'pdf') {
    $params = http_build_query([
        'date_from' => $dateFrom,
        'date_to' => $dateTo,
        'source' => $sourceFilter,
        'program' => $programFilter,
        'report_type' => $reportType
    ]);
    header("Location: transparency_export_pdf.php?$params");
    exit;
}

// Fetch programs for dropdown
$programs = [];
$res = $conn->query("SELECT id, name FROM transparency_campaigns ORDER BY name");
while ($res && $row = $res->fetch_assoc()) {
    $programs[] = $row;
}

// Fetch sources for dropdown
$sources = [];
$res = $conn->query("SELECT DISTINCT donor_type FROM transparency_donations ORDER BY donor_type");
while ($res && $row = $res->fetch_assoc()) {
    $sources[] = $row['donor_type'];
}

// Build WHERE clause for date filtering
$whereDate = "date_received BETWEEN '$dateFrom' AND '$dateTo'";

// Summary Statistics
$summary = [
    'total_assistance' => 0,
    'total_records' => 0,
    'avg_amount' => 0,
    'by_source' => [],
    'by_program' => [],
    'monthly' => []
];

// Total assistance
$sql = "SELECT SUM(amount) as total, COUNT(*) as count, AVG(amount) as avg 
        FROM transparency_donations 
        WHERE $whereDate";
if ($sourceFilter !== 'all') {
    $sql .= " AND donor_type = '" . $conn->real_escape_string($sourceFilter) . "'";
}
if ($programFilter !== 'all') {
    $sql .= " AND campaign_id = " . (int)$programFilter;
}
$res = $conn->query($sql);
if ($res && $row = $res->fetch_assoc()) {
    $summary['total_assistance'] = (float)($row['total'] ?? 0);
    $summary['total_records'] = (int)($row['count'] ?? 0);
    $summary['avg_amount'] = (float)($row['avg'] ?? 0);
}

// By Source
$sql = "SELECT donor_type, SUM(amount) as total, COUNT(*) as count 
        FROM transparency_donations 
        WHERE $whereDate";
if ($programFilter !== 'all') {
    $sql .= " AND campaign_id = " . (int)$programFilter;
}
$sql .= " GROUP BY donor_type ORDER BY total DESC";
$res = $conn->query($sql);
while ($res && $row = $res->fetch_assoc()) {
    $summary['by_source'][] = $row;
}

// By Program
$sql = "SELECT c.name, SUM(d.amount) as total, COUNT(*) as count 
        FROM transparency_donations d
        JOIN transparency_campaigns c ON d.campaign_id = c.id
        WHERE d.$whereDate";
if ($sourceFilter !== 'all') {
    $sql .= " AND d.donor_type = '" . $conn->real_escape_string($sourceFilter) . "'";
}
$sql .= " GROUP BY c.id ORDER BY total DESC";
$res = $conn->query($sql);
while ($res && $row = $res->fetch_assoc()) {
    $summary['by_program'][] = $row;
}

// Monthly breakdown
$sql = "SELECT DATE_FORMAT(date_received, '%Y-%m') as month, SUM(amount) as total, COUNT(*) as count
        FROM transparency_donations
        WHERE $whereDate";
if ($sourceFilter !== 'all') {
    $sql .= " AND donor_type = '" . $conn->real_escape_string($sourceFilter) . "'";
}
if ($programFilter !== 'all') {
    $sql .= " AND campaign_id = " . (int)$programFilter;
}
$sql .= " GROUP BY DATE_FORMAT(date_received, '%Y-%m') ORDER BY month";
$res = $conn->query($sql);
while ($res && $row = $res->fetch_assoc()) {
    $summary['monthly'][] = $row;
}

// Detailed transactions
$transactions = [];
$sql = "SELECT d.*, c.name as program_name 
        FROM transparency_donations d
        LEFT JOIN transparency_campaigns c ON d.campaign_id = c.id
        WHERE d.$whereDate";
if ($sourceFilter !== 'all') {
    $sql .= " AND d.donor_type = '" . $conn->real_escape_string($sourceFilter) . "'";
}
if ($programFilter !== 'all') {
    $sql .= " AND d.campaign_id = " . (int)$programFilter;
}
$sql .= " ORDER BY d.date_received DESC";
$res = $conn->query($sql);
while ($res && $row = $res->fetch_assoc()) {
    $transactions[] = $row;
}

$generatedDate = date('F d, Y h:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transparency Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
        .report-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 24px;
        }
        .stat-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
        }
        .table-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        @media print {
            .no-print { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            body { background: white !important; }
            .report-card { box-shadow: none !important; border: 1px solid #ddd; }
        }
        .chart-bar {
            height: 30px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
<?php include('../navbar.php'); ?>

<div class="main-content">
    <!-- Header -->
    <div class="page-header no-print">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2><i class="bi bi-file-earmark-text"></i> Transparency Reports</h2>
                <p class="mb-0">Generate and export assistance reports for documentation.</p>
            </div>
            <a href="transparency.php" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="report-card no-print">
        <h5 class="mb-3"><i class="bi bi-funnel"></i> Report Filters</h5>
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Report Type</label>
                <select name="report_type" class="form-select">
                    <option value="summary" <?= $reportType === 'summary' ? 'selected' : '' ?>>Summary Report</option>
                    <option value="detailed" <?= $reportType === 'detailed' ? 'selected' : '' ?>>Detailed Transactions</option>
                    <option value="source" <?= $reportType === 'source' ? 'selected' : '' ?>>By Source</option>
                    <option value="program" <?= $reportType === 'program' ? 'selected' : '' ?>>By Program</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="<?= e($dateFrom) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="<?= e($dateTo) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Source</label>
                <select name="source" class="form-select">
                    <option value="all">All Sources</option>
                    <?php foreach ($sources as $s): ?>
                    <option value="<?= e($s) ?>" <?= $sourceFilter === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Program</label>
                <select name="program" class="form-select">
                    <option value="all">All Programs</option>
                    <?php foreach ($programs as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $programFilter == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100" title="Generate Report">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
        <div class="mt-3 d-flex gap-2 flex-wrap">
            <a href="?report_type=<?= e($reportType) ?>&date_from=<?= e($dateFrom) ?>&date_to=<?= e($dateTo) ?>&source=<?= e($sourceFilter) ?>&program=<?= e($programFilter) ?>&export=csv" class="btn btn-outline-success">
                <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </a>
            <a href="?report_type=<?= e($reportType) ?>&date_from=<?= e($dateFrom) ?>&date_to=<?= e($dateTo) ?>&source=<?= e($sourceFilter) ?>&program=<?= e($programFilter) ?>&export=pdf" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Print Header (Visible only when printing) -->
    <div class="d-none d-print-block mb-4 text-center">
        <h2><?= e($assocName) ?></h2>
        <h4>Transparency Report</h4>
        <p>Generated: <?= e($generatedDate) ?></p>
        <p>Period: <?= date('M d, Y', strtotime($dateFrom)) ?> - <?= date('M d, Y', strtotime($dateTo)) ?></p>
    </div>

    <?php if ($reportType === 'summary'): ?>
    <!-- Summary Report -->
    <div class="report-card">
        <h5 class="mb-4"><i class="bi bi-graph-up"></i> Summary Overview</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-box">
                    <div class="stat-value">₱<?= number_format($summary['total_assistance'], 0) ?></div>
                    <small class="text-muted">Total Assistance</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <div class="stat-value"><?= number_format($summary['total_records']) ?></div>
                    <small class="text-muted">Total Records</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <div class="stat-value">₱<?= number_format($summary['avg_amount'], 0) ?></div>
                    <small class="text-muted">Average Amount</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <div class="stat-value"><?= count($summary['by_source']) ?></div>
                    <small class="text-muted">Partner Sources</small>
                </div>
            </div>
        </div>

        <?php if (!empty($summary['by_source'])): 
            $maxSource = $summary['by_source'][0]['total'] ?? 1;
        ?>
        <h6 class="mb-3">Assistance by Source</h6>
        <div class="table-responsive mb-4">
            <table class="table">
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Amount</th>
                        <th>%</th>
                        <th>Count</th>
                        <th>Visual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary['by_source'] as $s): 
                        $pct = $summary['total_assistance'] > 0 ? ($s['total'] / $summary['total_assistance'] * 100) : 0;
                        $barWidth = $maxSource > 0 ? ($s['total'] / $maxSource * 100) : 0;
                    ?>
                    <tr>
                        <td><strong><?= e($s['donor_type']) ?></strong></td>
                        <td>₱<?= number_format($s['total'], 2) ?></td>
                        <td><?= number_format($pct, 1) ?>%</td>
                        <td><?= $s['count'] ?> records</td>
                        <td style="width: 30%;">
                            <div class="chart-bar" style="width: <?= $barWidth ?>%">
                                <?php if ($barWidth > 20): ?><?= number_format($pct, 0) ?>%<?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if (!empty($summary['monthly'])): ?>
        <h6 class="mb-3">Monthly Breakdown</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Amount</th>
                        <th>Records</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary['monthly'] as $m): ?>
                    <tr>
                        <td><?= date('F Y', strtotime($m['month'] . '-01')) ?></td>
                        <td>₱<?= number_format($m['total'], 2) ?></td>
                        <td><?= $m['count'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($reportType === 'detailed' || $reportType === 'source' || $reportType === 'program'): ?>
    <!-- Detailed Transactions -->
    <div class="report-card">
        <h5 class="mb-3">
            <i class="bi bi-list-ul"></i> 
            <?= $reportType === 'detailed' ? 'Detailed Transactions' : ($reportType === 'source' ? 'Transactions by Source' : 'Transactions by Program') ?>
        </h5>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Source</th>
                        <th>Program</th>
                        <th>Reference</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $groupField = $reportType === 'source' ? 'donor_type' : ($reportType === 'program' ? 'program_name' : null);
                    $currentGroup = null;
                    $groupTotal = 0;
                    
                    foreach ($transactions as $t): 
                        if ($groupField && $currentGroup !== $t[$groupField]):
                            if ($currentGroup !== null):
                    ?>
                    <tr class="table-secondary">
                        <td colspan="4" class="text-end"><strong><?= e($currentGroup) ?> Total:</strong></td>
                        <td class="text-end"><strong>₱<?= number_format($groupTotal, 2) ?></strong></td>
                    </tr>
                    <?php 
                            endif;
                            $currentGroup = $t[$groupField];
                            $groupTotal = 0;
                    ?>
                    <tr class="table-primary">
                        <td colspan="5"><strong><?= e($currentGroup ?: 'Unassigned') ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td><?= $t['date_received'] ? date('M d, Y', strtotime($t['date_received'])) : '-' ?></td>
                        <td><?= e($t['donor_name']) ?> <small class="text-muted">(<?= e($t['donor_type']) ?>)</small></td>
                        <td><?= e($t['program_name'] ?: 'Not linked') ?></td>
                        <td><?= e($t['reference_code'] ?: '-') ?></td>
                        <td class="text-end">₱<?= number_format($t['amount'], 2) ?></td>
                    </tr>
                    <?php $groupTotal += $t['amount']; endforeach; 
                    
                    if ($groupField && $currentGroup !== null):
                    ?>
                    <tr class="table-secondary">
                        <td colspan="4" class="text-end"><strong><?= e($currentGroup) ?> Total:</strong></td>
                        <td class="text-end"><strong>₱<?= number_format($groupTotal, 2) ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    
                    <?php if (empty($transactions)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No records found for the selected period</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-dark">
                        <td colspan="4" class="text-end"><strong>GRAND TOTAL:</strong></td>
                        <td class="text-end"><strong>₱<?= number_format($summary['total_assistance'], 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="d-none d-print-block mt-4 text-center">
        <p>--- End of Report ---</p>
        <p class="small text-muted">This report was generated electronically and is valid without signature.</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
