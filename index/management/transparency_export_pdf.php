<?php
// Transparency PDF Export
session_start();

if (empty($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

// Get filters
$dateFrom = $_GET['date_from'] ?? date('Y-01-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');
$sourceFilter = $_GET['source'] ?? 'all';
$programFilter = $_GET['program'] ?? 'all';
$reportType = $_GET['report_type'] ?? 'summary';

$whereDate = "date_received BETWEEN '$dateFrom' AND '$dateTo'";

// Fetch data
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
    $row['items'] = [];
    $transactions[$row['id']] = $row;
}

// Fetch in-kind items
if (!empty($transactions)) {
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
    $txnIds = implode(',', array_keys($transactions));
    $itemRes = $conn->query("SELECT * FROM transparency_donation_items WHERE donation_id IN ($txnIds) ORDER BY donation_id, id");
    while ($itemRes && $ir = $itemRes->fetch_assoc()) {
        $transactions[$ir['donation_id']]['items'][] = $ir;
    }
}
$transactions = array_values($transactions);

// Calculate totals (cash and in-kind separately)
$cashTotal   = 0;
$inkindTotal = 0;
foreach ($transactions as $t) {
    if (($t['donation_type'] ?? 'cash') === 'in_kind') {
        $inkindTotal += $t['amount'];
    } else {
        $cashTotal += $t['amount'];
    }
}
$total = $cashTotal + $inkindTotal;

require_once __DIR__ . '/../../config/logo_helper.php';
$logoPath = $assocLogoPath;
$logoData = $assocLogoB64;
$generatedDate = date('F d, Y h:i A');

// Simple HTML to PDF-like output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transparency Report - <?= htmlspecialchars($assocName) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 20px;
        }
        .header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #2E86AB;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header img {
            width: 70px;
            height: 70px;
            margin-right: 15px;
            object-fit: contain;
        }
        .header-text {
            flex: 1;
        }
        .header-text h2 {
            margin: 0 0 5px 0;
            color: #2E86AB;
        }
        .header-text p {
            margin: 0;
            color: #666;
        }
        .info {
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: linear-gradient(135deg, #2E86AB 0%, #1B4F72 100%);
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .amount {
            text-align: right;
            font-family: monospace;
        }
        .total-row {
            background: #2E86AB !important;
            color: white;
            font-weight: bold;
        }
        .total-row td {
            border-color: #2E86AB;
        }
        .inkind-row {
            background: #eff6ff !important;
        }
        .inkind-items {
            font-size: 10px;
            color: #444;
            padding-left: 8px;
            margin: 2px 0 0 0;
            list-style: none;
        }
        .inkind-items li::before {
            content: "→ ";
            color: #2E86AB;
        }
        .badge-cash   { background:#198754; color:#fff; padding:2px 6px; border-radius:4px; font-size:10px; }
        .badge-inkind { background:#0d6efd; color:#fff; padding:2px 6px; border-radius:4px; font-size:10px; }
        .subtotal-row td { background:#f1f5ff; font-weight:600; }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        @media print {
            body { margin: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <?= $logoData ? '<img src="data:image/png;base64,' . $logoData . '" alt="Logo">' : '' ?>
        <div class="header-text">
            <h2><?= htmlspecialchars($assocName) ?></h2>
            <p><strong>TRANSPARENCY REPORT</strong></p>
        </div>
    </div>

    <div class="info">
        <div class="info-row">
            <span><strong>Period:</strong> <?= date('M d, Y', strtotime($dateFrom)) ?> - <?= date('M d, Y', strtotime($dateTo)) ?></span>
            <span><strong>Generated:</strong> <?= $generatedDate ?></span>
        </div>
        <?php if ($sourceFilter !== 'all'): ?>
        <div class="info-row">
            <span><strong>Source Filter:</strong> <?= htmlspecialchars($sourceFilter) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($programFilter !== 'all'): 
            $progName = $conn->query("SELECT name FROM transparency_campaigns WHERE id = " . (int)$programFilter)->fetch_assoc()['name'] ?? 'Unknown';
        ?>
        <div class="info-row">
            <span><strong>Program Filter:</strong> <?= htmlspecialchars($progName) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Date</th>
                <th style="width: 20%;">Source Name</th>
                <th style="width: 10%;">Source Type</th>
                <th style="width: 10%;">Don. Type</th>
                <th style="width: 20%;">Program</th>
                <th style="width: 12%;">Reference</th>
                <th style="width: 18%; text-align: right;">Amount / Items</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($transactions)): ?>
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">No records found for the selected period</td>
            </tr>
            <?php else: ?>
                <?php foreach ($transactions as $t): 
                    $isInKind = ($t['donation_type'] ?? 'cash') === 'in_kind';
                ?>
                <tr class="<?= $isInKind ? 'inkind-row' : '' ?>">
                    <td><?= $t['date_received'] ? date('M d, Y', strtotime($t['date_received'])) : '-' ?></td>
                    <td><?= htmlspecialchars($t['donor_name']) ?></td>
                    <td><?= htmlspecialchars($t['donor_type']) ?></td>
                    <td>
                        <?php if ($isInKind): ?>
                        <span class="badge-inkind">Gamit/In-Kind</span>
                        <?php else: ?>
                        <span class="badge-cash">Pera/Cash</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($t['program_name'] ?: 'Not linked') ?></td>
                    <td><?= htmlspecialchars($t['reference_code'] ?: '-') ?></td>
                    <td class="amount">
                        ₱<?= number_format($t['amount'], 2) ?>
                        <?php if ($isInKind && !empty($t['items'])): ?>
                        <ul class="inkind-items">
                            <?php foreach ($t['items'] as $it): ?>
                            <li><?= htmlspecialchars($it['item_name']) ?> × <?= number_format((float)$it['quantity'], 0) ?> <?= htmlspecialchars($it['unit']) ?> @ ₱<?= number_format((float)$it['unit_value'], 2) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if ($inkindTotal > 0): ?>
                <tr class="subtotal-row">
                    <td colspan="6" style="text-align: right;">Cash Assistance Subtotal:</td>
                    <td class="amount">₱<?= number_format($cashTotal, 2) ?></td>
                </tr>
                <tr class="subtotal-row">
                    <td colspan="6" style="text-align: right;">In-Kind (Gamit) Subtotal:</td>
                    <td class="amount">₱<?= number_format($inkindTotal, 2) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td colspan="6" style="text-align: right;">CASH ASSISTANCE TOTAL:</td>
                    <td class="amount">₱<?= number_format($cashTotal, 2) ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated electronically from the <?= htmlspecialchars($assocName) ?> Transparency System.</p>
        <p>For inquiries, please contact the association treasurer.</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 14px; cursor: pointer;">
            🖨️ Print / Save as PDF
        </button>
        <p style="font-size: 11px; color: #666; margin-top: 10px;">
            Tip: Click Print and select "Save as PDF" as the destination
        </p>
    </div>

    <script>
        // Auto-trigger print dialog after a short delay
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
