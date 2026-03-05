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
    $transactions[] = $row;
}

// Calculate total
$total = 0;
foreach ($transactions as $t) {
    $total += $t['amount'];
}

$assocName = 'Bankero and Fishermen Association';
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
            text-align: center;
            border-bottom: 2px solid #667eea;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            color: #667eea;
        }
        .header p {
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: #667eea !important;
            color: white;
            font-weight: bold;
        }
        .total-row td {
            border-color: #667eea;
        }
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
        <h2><?= htmlspecialchars($assocName) ?></h2>
        <p><strong>TRANSPARENCY REPORT</strong></p>
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
                <th style="width: 12%;">Type</th>
                <th style="width: 23%;">Program</th>
                <th style="width: 15%;">Reference</th>
                <th style="width: 12%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($transactions)): ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">No records found for the selected period</td>
            </tr>
            <?php else: ?>
                <?php foreach ($transactions as $t): ?>
                <tr>
                    <td><?= $t['date_received'] ? date('M d, Y', strtotime($t['date_received'])) : '-' ?></td>
                    <td><?= htmlspecialchars($t['donor_name']) ?></td>
                    <td><?= htmlspecialchars($t['donor_type']) ?></td>
                    <td><?= htmlspecialchars($t['program_name'] ?: 'Not linked') ?></td>
                    <td><?= htmlspecialchars($t['reference_code'] ?: '-') ?></td>
                    <td class="amount">₱<?= number_format($t['amount'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="5" style="text-align: right;">TOTAL ASSISTANCE RECEIVED:</td>
                    <td class="amount">₱<?= number_format($total, 2) ?></td>
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
