<?php
// Transparency Reports Page
session_start();

require_once('../../config/db_connect.php');
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

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

// Get filter values — validate dates to prevent injection
$rawFrom   = $_GET['date_from'] ?? date('Y-01-01');
$rawTo     = $_GET['date_to']   ?? date('Y-m-d');
$dateFrom  = (strtotime($rawFrom)  !== false) ? date('Y-m-d', strtotime($rawFrom))  : date('Y-01-01');
$dateTo    = (strtotime($rawTo)    !== false) ? date('Y-m-d', strtotime($rawTo))    : date('Y-m-d');
$sourceFilter  = $_GET['source']      ?? 'all';
$programFilter = $_GET['program']     ?? 'all';
$reportType    = $_GET['report_type'] ?? 'summary';
$exportFormat  = $_GET['export']      ?? '';

// If export requested, handle it before any HTML output
if ($exportFormat === 'csv' || $exportFormat === 'excel') {
    // ── Fetch donations ─────────────────────────────────────────────────────
    $exportTypes = 's';
    $exportVals  = [$dateFrom . ' 00:00:00'];
    $exportSql   = "SELECT d.id, d.date_received, d.donor_name, d.donor_type, d.donation_type,
            d.amount, d.reference_code, d.notes, c.name as program_name
            FROM transparency_donations d
            LEFT JOIN transparency_campaigns c ON d.campaign_id = c.id
            WHERE d.date_received BETWEEN ? AND ?";
    $exportTypes .= 's';
    $exportVals[] = $dateTo . ' 23:59:59';
    if ($sourceFilter !== 'all') { $exportSql .= " AND d.donor_type = ?";    $exportTypes .= 's'; $exportVals[] = $sourceFilter; }
    if ($programFilter !== 'all') { $exportSql .= " AND d.campaign_id = ?";  $exportTypes .= 'i'; $exportVals[] = (int)$programFilter; }
    $exportSql .= " ORDER BY d.date_received DESC";
    $stmt = $conn->prepare($exportSql);
    $stmt->bind_param($exportTypes, ...$exportVals);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    // ── Fetch in-kind items ──────────────────────────────────────────────────
    $exportItems = [];
    $itemExRes = $conn->query("SELECT i.* FROM transparency_donation_items i
        JOIN transparency_donations d ON i.donation_id = d.id
        WHERE d.date_received BETWEEN '{$dateFrom} 00:00:00' AND '{$dateTo} 23:59:59'
        ORDER BY i.donation_id, i.id");
    while ($itemExRes && $ir = $itemExRes->fetch_assoc()) {
        $exportItems[$ir['donation_id']][] = $ir;
    }

    // ── Separate cash vs in-kind ─────────────────────────────────────────────
    $cashRows = []; $inkindRows = [];
    $cashTotal = 0; $inkindTotal = 0;
    while ($row = $result->fetch_assoc()) {
        if (($row['donation_type'] ?? 'cash') === 'in_kind') {
            $inkindRows[]  = $row;
            $inkindTotal  += $row['amount'];
        } else {
            $cashRows[]   = $row;
            $cashTotal    += $row['amount'];
        }
    }

    // ── Fetch expenses ───────────────────────────────────────────────────────
    $expStmt = $conn->prepare("SELECT e.*, d.donor_name as linked_source
        FROM transparency_expenses e
        LEFT JOIN transparency_donations d ON e.donation_id = d.id
        WHERE (e.expense_date BETWEEN ? AND ?) ORDER BY e.expense_date DESC");
    $expStmt->bind_param('ss', $dateFrom, $dateTo);
    $expStmt->execute();
    $expRows = $expStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $expStmt->close();
    $expTotal = array_sum(array_column($expRows, 'amount'));

    // ════════════════════════════════════════════════════════════════════════
    // XLSX export using PhpSpreadsheet
    // ════════════════════════════════════════════════════════════════════════
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Transparency Report');

    // ── Helper: apply style to a range ──────────────────────────────────────
    $styleHeader = function(string $range, string $bgColor, string $fontColor = 'FFFFFF', bool $bold = true) use ($sheet) {
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => $bold, 'color' => ['argb' => 'FF' . $fontColor]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $bgColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFAAAAAA']]],
        ]);
    };
    $styleSectionTitle = function(string $cell, string $label, string $bg, string $fg = 'FFFFFF') use ($sheet, $styleHeader) {
        $sheet->setCellValue($cell, $label);
        $col = preg_replace('/\d/', '', $cell);
        $row = preg_replace('/\D/', '', $cell);
        $sheet->mergeCells($col . $row . ':G' . $row);
        $styleHeader($col . $row . ':G' . $row, $bg, $fg);
        $sheet->getRowDimension((int)$row)->setRowHeight(20);
    };

    $r = 1; // current row tracker

    // ── Title block ──────────────────────────────────────────────────────────
    $sheet->mergeCells("A{$r}:G{$r}");
    $sheet->setCellValue("A{$r}", 'TRANSPARENCY REPORT — ' . strtoupper($assocName));
    $sheet->getStyle("A{$r}")->applyFromArray([
        'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A3C5E']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);
    $sheet->getRowDimension($r)->setRowHeight(28);
    $r++;

    $sheet->mergeCells("A{$r}:G{$r}");
    $sheet->setCellValue("A{$r}", 'Period: ' . date('M d, Y', strtotime($dateFrom)) . ' — ' . date('M d, Y', strtotime($dateTo)) . '   |   Generated: ' . date('F d, Y h:i A'));
    $sheet->getStyle("A{$r}")->applyFromArray([
        'font'  => ['italic' => true, 'color' => ['argb' => 'FF555555']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);
    $r += 2;

    // ════════════════════════════════════════════════════════════════════════
    // SECTION 1 — CASH ASSISTANCE
    // ════════════════════════════════════════════════════════════════════════
    $styleSectionTitle("A{$r}", '💰  CASH ASSISTANCE', '1A3C5E');
    $r++;

    $cashCols = ['Date Received', 'Source Name', 'Source Type', 'Program', 'Reference', 'Amount (₱)', 'Notes'];
    $sheet->fromArray($cashCols, null, "A{$r}");
    $styleHeader("A{$r}:G{$r}", '2E6DA4');
    $sheet->getRowDimension($r)->setRowHeight(18);
    $r++;

    foreach ($cashRows as $i => $row) {
        $bg = ($i % 2 === 0) ? 'EAF3FC' : 'FFFFFF';
        $vals = [
            $row['date_received'] ? date('M d, Y', strtotime($row['date_received'])) : '',
            $row['donor_name'],
            $row['donor_type'],
            $row['program_name'] ?: 'Not linked',
            $row['reference_code'] ?: '',
            (float)$row['amount'],
            $row['notes'] ?: '',
        ];
        $sheet->fromArray($vals, null, "A{$r}");
        $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $bg]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
        ]);
        $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $r++;
    }
    // Cash total row
    $sheet->fromArray(['', '', '', '', 'CASH TOTAL:', $cashTotal, ''], null, "A{$r}");
    $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E6DA4']],
    ]);
    $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $r += 2;

    // ════════════════════════════════════════════════════════════════════════
    // SECTION 2 — IN-KIND DONATIONS
    // ════════════════════════════════════════════════════════════════════════
    if (!empty($inkindRows)) {
        $styleSectionTitle("A{$r}", '📦  IN-KIND / GAMIT DONATIONS', '0F5132');
        $r++;

        $ikCols = ['Date Received', 'Source Name', 'Source Type', 'Program', 'Reference', 'Est. Value (₱)', 'Notes'];
        $sheet->fromArray($ikCols, null, "A{$r}");
        $styleHeader("A{$r}:G{$r}", '198754');
        $sheet->getRowDimension($r)->setRowHeight(18);
        $r++;

        foreach ($inkindRows as $i => $row) {
            $bg = ($i % 2 === 0) ? 'D1EAE0' : 'EDFAF3';
            $vals = [
                $row['date_received'] ? date('M d, Y', strtotime($row['date_received'])) : '',
                $row['donor_name'],
                $row['donor_type'],
                $row['program_name'] ?: 'Not linked',
                $row['reference_code'] ?: '',
                (float)$row['amount'],
                $row['notes'] ?: '',
            ];
            $sheet->fromArray($vals, null, "A{$r}");
            $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                'font'    => ['bold' => true],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $bg]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBBBBBB']]],
            ]);
            $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $r++;

            // Item sub-rows
            if (!empty($exportItems[$row['id']])) {
                // Item header
                $sheet->fromArray(['', 'Item Name', 'Quantity', 'Unit', 'Unit Value (₱)', 'Total Value (₱)', ''], null, "A{$r}");
                $sheet->getStyle("B{$r}:F{$r}")->applyFromArray([
                    'font'  => ['bold' => true, 'italic' => true, 'color' => ['argb' => 'FF0F5132']],
                    'fill'  => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC3E6CB']],
                ]);
                $r++;

                foreach ($exportItems[$row['id']] as $it) {
                    $totalVal = (float)($it['total_value'] ?? ($it['quantity'] * $it['unit_value']));
                    $sheet->fromArray([
                        '', $it['item_name'],
                        (float)$it['quantity'], $it['unit'],
                        (float)$it['unit_value'], $totalVal, ''
                    ], null, "A{$r}");
                    $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                        'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0FFF4']],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
                    ]);
                    $sheet->getStyle("E{$r}:F{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("C{$r}:F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $r++;
                }
            }
        }
        // In-kind total row
        $sheet->fromArray(['', '', '', '', 'IN-KIND TOTAL:', $inkindTotal, ''], null, "A{$r}");
        $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF198754']],
        ]);
        $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $r += 2;
    }

    // ════════════════════════════════════════════════════════════════════════
    // SECTION 3 — EXPENSES
    // ════════════════════════════════════════════════════════════════════════
    $styleSectionTitle("A{$r}", '💸  EXPENSES / DISBURSEMENTS', '7B2D00');
    $r++;

    $expCols = ['Date', 'Title / Purpose', 'Category', 'Paid To', 'Reference / OR', 'Linked Source', 'Amount (₱)'];
    $sheet->fromArray($expCols, null, "A{$r}");
    $styleHeader("A{$r}:G{$r}", 'C0392B');
    $sheet->getRowDimension($r)->setRowHeight(18);
    $r++;

    foreach ($expRows as $i => $ex) {
        $bg = ($i % 2 === 0) ? 'FDECEA' : 'FFFFFF';
        $vals = [
            $ex['expense_date'] ? date('M d, Y', strtotime($ex['expense_date'])) : '',
            $ex['title'],
            $ex['category'] ?: '',
            $ex['paid_to'] ?: '',
            $ex['reference_code'] ?: '',
            $ex['linked_source'] ?: '',
            (float)$ex['amount'],
        ];
        $sheet->fromArray($vals, null, "A{$r}");
        $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $bg]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
        ]);
        $sheet->getStyle("G{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $r++;
    }
    // Expense total row
    $sheet->fromArray(['', '', '', '', '', 'TOTAL EXPENSES:', $expTotal], null, "A{$r}");
    $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC0392B']],
    ]);
    $sheet->getStyle("G{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle("G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $r += 2;

    // ════════════════════════════════════════════════════════════════════════
    // SUMMARY block
    // ════════════════════════════════════════════════════════════════════════
    $summaryData = [
        ['Cash Assistance Total:', $cashTotal],
    ];
    if ($inkindTotal > 0) $summaryData[] = ['In-Kind (Gamit) Total:', $inkindTotal];
    $summaryData[] = ['Total Expenses:', $expTotal];
    $summaryData[] = ['CASH BALANCE:', $cashTotal - $expTotal];

    $styleSectionTitle("A{$r}", 'SUMMARY', '1A3C5E');
    $r++;
    foreach ($summaryData as $si => $sd) {
        $isBal = $sd[0] === 'CASH BALANCE:';
        $bg = $isBal ? ($sd[1] >= 0 ? '198754' : 'C0392B') : ($si % 2 === 0 ? 'EAF3FC' : 'FFFFFF');
        $fg = $isBal ? 'FFFFFF' : '000000';
        $sheet->mergeCells("A{$r}:E{$r}");
        $sheet->setCellValue("A{$r}", $sd[0]);
        $sheet->setCellValue("F{$r}", $sd[1]);
        $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
            'font'      => ['bold' => $isBal, 'color' => ['argb' => 'FF' . $fg]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $bg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);
        $sheet->getStyle("F{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
        if ($inkindTotal > 0 && $sd[0] === 'CASH BALANCE:') {
            $sheet->setCellValue("G{$r}", '(In-Kind not included)');
            $sheet->getStyle("G{$r}")->applyFromArray(['font' => ['italic' => true, 'color' => ['argb' => 'FF' . $fg]]]);
        }
        $r++;
    }

    // ── Column widths ────────────────────────────────────────────────────────
    foreach (['A' => 14, 'B' => 28, 'C' => 18, 'D' => 24, 'E' => 20, 'F' => 16, 'G' => 28] as $col => $w) {
        $sheet->getColumnDimension($col)->setWidth($w);
    }

    // ── Output ───────────────────────────────────────────────────────────────
    $filename = 'transparency_report_' . date('Y-m-d') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer = new XlsxWriter($spreadsheet);
    $writer->save('php://output');
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

// Summary Statistics
$summary = [
    'total_assistance' => 0,  // cash only
    'total_inkind'     => 0,  // in-kind only
    'total_all'        => 0,  // all combined
    'total_records'    => 0,
    'avg_amount'       => 0,
    'by_source'        => [],
    'by_program'       => [],
    'monthly'          => []
];

// Helper: build type string + values array for common date+filters
function buildParams(string $dateFrom, string $dateTo, string $sourceFilter, string $programFilter): array {
    $types = 'ss';
    $vals  = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];
    if ($sourceFilter !== 'all')  { $types .= 's'; $vals[] = $sourceFilter; }
    if ($programFilter !== 'all') { $types .= 'i'; $vals[] = (int)$programFilter; }
    return [$types, $vals];
}
function addDateCond(string $alias = ''): string {
    $col = $alias ? "$alias.date_received" : "date_received";
    return "$col BETWEEN ? AND ?";
}

// Total assistance (cash only — in-kind tracked separately)
{
    $q = "SELECT
              SUM(CASE WHEN COALESCE(donation_type,'cash')='cash' THEN amount ELSE 0 END) as cash_total,
              SUM(CASE WHEN donation_type='in_kind' THEN amount ELSE 0 END) as inkind_total,
              SUM(amount) as total_all,
              COUNT(*) as count,
              AVG(CASE WHEN COALESCE(donation_type,'cash')='cash' THEN amount END) as avg_cash
          FROM transparency_donations
          WHERE " . addDateCond();
    $extraTypes = 'ss'; $extraVals = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];
    if ($sourceFilter  !== 'all') { $q .= " AND donor_type = ?";  $extraTypes .= 's'; $extraVals[] = $sourceFilter; }
    if ($programFilter !== 'all') { $q .= " AND campaign_id = ?"; $extraTypes .= 'i'; $extraVals[] = (int)$programFilter; }
    $stmt = $conn->prepare($q);
    $stmt->bind_param($extraTypes, ...$extraVals);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $summary['total_assistance']       = (float)($row['cash_total']  ?? 0); // cash only
    $summary['total_inkind']           = (float)($row['inkind_total'] ?? 0); // in-kind only
    $summary['total_all']              = (float)($row['total_all']    ?? 0); // all combined
    $summary['total_records']          = (int)($row['count']          ?? 0);
    $summary['avg_amount']             = (float)($row['avg_cash']     ?? 0);
}

// By Source
{
    $q = "SELECT donor_type, SUM(amount) as total, COUNT(*) as count
          FROM transparency_donations
          WHERE " . addDateCond();
    $extraTypes = 'ss'; $extraVals = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];
    if ($programFilter !== 'all') { $q .= " AND campaign_id = ?"; $extraTypes .= 'i'; $extraVals[] = (int)$programFilter; }
    $q .= " GROUP BY donor_type ORDER BY total DESC";
    $stmt = $conn->prepare($q);
    $stmt->bind_param($extraTypes, ...$extraVals);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $summary['by_source'][] = $row;
    $stmt->close();
}

// By Program
{
    $q = "SELECT c.name, SUM(d.amount) as total, COUNT(*) as count
          FROM transparency_donations d
          JOIN transparency_campaigns c ON d.campaign_id = c.id
          WHERE " . addDateCond('d');
    $extraTypes = 'ss'; $extraVals = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];
    if ($sourceFilter !== 'all') { $q .= " AND d.donor_type = ?"; $extraTypes .= 's'; $extraVals[] = $sourceFilter; }
    $q .= " GROUP BY c.id ORDER BY total DESC";
    $stmt = $conn->prepare($q);
    $stmt->bind_param($extraTypes, ...$extraVals);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $summary['by_program'][] = $row;
    $stmt->close();
}

// Monthly breakdown
{
    $q = "SELECT DATE_FORMAT(date_received, '%Y-%m') as month, SUM(amount) as total, COUNT(*) as count
          FROM transparency_donations
          WHERE " . addDateCond();
    $extraTypes = 'ss'; $extraVals = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];
    if ($sourceFilter  !== 'all') { $q .= " AND donor_type = ?";  $extraTypes .= 's'; $extraVals[] = $sourceFilter; }
    if ($programFilter !== 'all') { $q .= " AND campaign_id = ?"; $extraTypes .= 'i'; $extraVals[] = (int)$programFilter; }
    $q .= " GROUP BY DATE_FORMAT(date_received, '%Y-%m') ORDER BY month";
    $stmt = $conn->prepare($q);
    $stmt->bind_param($extraTypes, ...$extraVals);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $summary['monthly'][] = $row;
    $stmt->close();
}

// Detailed transactions
$transactions = [];
{
    $q = "SELECT d.*, c.name as program_name
          FROM transparency_donations d
          LEFT JOIN transparency_campaigns c ON d.campaign_id = c.id
          WHERE " . addDateCond('d');
    $extraTypes = 'ss'; $extraVals = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];
    if ($sourceFilter  !== 'all') { $q .= " AND d.donor_type = ?";  $extraTypes .= 's'; $extraVals[] = $sourceFilter; }
    if ($programFilter !== 'all') { $q .= " AND d.campaign_id = ?"; $extraTypes .= 'i'; $extraVals[] = (int)$programFilter; }
    $q .= " ORDER BY d.date_received DESC";
    $stmt = $conn->prepare($q);
    $stmt->bind_param($extraTypes, ...$extraVals);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $row['items'] = [];
        $transactions[$row['id']] = $row;
    }
    $stmt->close();

    // Fetch in-kind items for loaded transactions
    if (!empty($transactions)) {
        $txnIds = implode(',', array_keys($transactions));
        // Ensure table exists before querying
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
        $itemRes = $conn->query("SELECT * FROM transparency_donation_items WHERE donation_id IN ($txnIds) ORDER BY donation_id, id");
        while ($itemRes && $iRow = $itemRes->fetch_assoc()) {
            $transactions[$iRow['donation_id']]['items'][] = $iRow;
        }
    }
    $transactions = array_values($transactions);
}

// Expenses data — filtered by same date range
$expenses = [];
$expTotal = 0;
{
    // Ensure table exists before querying
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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $q = "SELECT e.*, d.donor_name as linked_source
          FROM transparency_expenses e
          LEFT JOIN transparency_donations d ON e.donation_id = d.id
          WHERE (e.expense_date BETWEEN ? AND ? OR e.expense_date IS NULL)
          ORDER BY e.expense_date DESC, e.id DESC";
    $stmt = $conn->prepare($q);
    $stmt->bind_param('ss', $dateFrom, $dateTo);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $expenses[] = $row;
    $stmt->close();

    $q2 = "SELECT SUM(amount) as total FROM transparency_expenses WHERE expense_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($q2);
    $stmt->bind_param('ss', $dateFrom, $dateTo);
    $stmt->execute();
    $row2 = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $expTotal = (float)($row2['total'] ?? 0);
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

        <!-- Quick Date Shortcuts -->
        <div class="d-flex gap-2 flex-wrap mb-3" id="quickDates">
            <span class="text-muted small align-self-center me-1">Quick:</span>
            <button type="button" class="btn btn-sm btn-outline-secondary quick-date" data-range="this_month">This Month</button>
            <button type="button" class="btn btn-sm btn-outline-secondary quick-date" data-range="last_month">Last Month</button>
            <button type="button" class="btn btn-sm btn-outline-secondary quick-date" data-range="last3">Last 3 Months</button>
            <button type="button" class="btn btn-sm btn-outline-secondary quick-date" data-range="this_year">This Year</button>
            <button type="button" class="btn btn-sm btn-outline-secondary quick-date" data-range="last_year">Last Year</button>
        </div>

        <form method="GET" id="filterForm" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Report Type</label>
                <select name="report_type" class="form-select">
                    <option value="summary"  <?= $reportType === 'summary'  ? 'selected' : '' ?>>Summary Report</option>
                    <option value="detailed" <?= $reportType === 'detailed' ? 'selected' : '' ?>>Detailed Transactions</option>
                    <option value="source"   <?= $reportType === 'source'   ? 'selected' : '' ?>>By Source</option>
                    <option value="program"  <?= $reportType === 'program'  ? 'selected' : '' ?>>By Program</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" id="dateFrom" name="date_from" class="form-control" value="<?= e($dateFrom) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" id="dateTo" name="date_to" class="form-control" value="<?= e($dateTo) ?>">
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
            <div class="col-md-1 d-flex align-items-end gap-1">
                <button type="submit" class="btn btn-primary flex-fill" title="Generate Report">
                    <i class="bi bi-search"></i>
                </button>
                <a href="transparency_reports.php" class="btn btn-outline-secondary flex-fill" title="Reset Filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>

        <div class="mt-3 d-flex gap-2 flex-wrap align-items-center">
            <a href="?report_type=<?= e($reportType) ?>&date_from=<?= e($dateFrom) ?>&date_to=<?= e($dateTo) ?>&source=<?= e($sourceFilter) ?>&program=<?= e($programFilter) ?>&export=csv" class="btn btn-outline-success">
                <i class="bi bi-filetype-csv me-1"></i> Export CSV
                <span class="badge bg-success ms-1"><?= number_format($summary['total_records']) ?></span>
            </a>
            <a href="?report_type=<?= e($reportType) ?>&date_from=<?= e($dateFrom) ?>&date_to=<?= e($dateTo) ?>&source=<?= e($sourceFilter) ?>&program=<?= e($programFilter) ?>&export=pdf" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                <span class="badge bg-danger ms-1"><?= number_format($summary['total_records']) ?></span>
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

    <?php if ($reportType === 'summary'): 
        $balance = $summary['total_assistance'] - $expTotal; // balance = cash only - expenses
    ?>
    <!-- Summary Report -->
    <div class="report-card">
        <h5 class="mb-4"><i class="bi bi-graph-up"></i> Summary Overview</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="stat-box">
                    <div class="stat-value" style="color:#198754;">₱<?= number_format($summary['total_assistance'], 0) ?></div>
                    <small class="text-muted">Cash Assistance</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-box">
                    <div class="stat-value" style="color:#0d6efd;">₱<?= number_format($summary['total_inkind'], 0) ?></div>
                    <small class="text-muted">In-Kind (Gamit)</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-box">
                    <div class="stat-value" style="color:#dc3545;">₱<?= number_format($expTotal, 0) ?></div>
                    <small class="text-muted">Total Expenses</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-box">
                    <div class="stat-value" style="color:<?= $balance >= 0 ? '#0d6efd' : '#dc3545' ?>;">₱<?= number_format(abs($balance), 0) ?><?= $balance < 0 ? ' <small style="font-size:1rem;">(deficit)</small>' : '' ?></div>
                    <small class="text-muted">Balance (Cash)</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-box">
                    <div class="stat-value"><?= number_format($summary['total_records']) ?></div>
                    <small class="text-muted">Total Records</small>
                </div>
            </div>
            <div class="col-md-2">
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

        <?php if (!empty($summary['monthly'])): 
            $maxMonthly = max(array_column($summary['monthly'], 'total')) ?: 1;
        ?>
        <h6 class="mb-3">Monthly Breakdown</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Amount</th>
                        <th>Records</th>
                        <th>Visual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary['monthly'] as $m): 
                        $mBarWidth = $maxMonthly > 0 ? ($m['total'] / $maxMonthly * 100) : 0;
                    ?>
                    <tr>
                        <td><?= date('F Y', strtotime($m['month'] . '-01')) ?></td>
                        <td>₱<?= number_format($m['total'], 2) ?></td>
                        <td><?= $m['count'] ?></td>
                        <td style="width: 35%;">
                            <div class="chart-bar" style="width: <?= $mBarWidth ?>%; background: linear-gradient(90deg, #0d6efd 0%, #0dcaf0 100%);">
                                <?php if ($mBarWidth > 15): ?>₱<?= number_format($m['total'] / 1000, 0) ?>k<?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php elseif ($reportType === 'summary'): ?>
        <div class="text-center text-muted py-4">
            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
            No records found for the selected period and filters.
        </div>
        <?php endif; ?>

        <!-- In-Kind Donations Breakdown -->
        <?php 
        $inkindList = array_filter($transactions, fn($t) => ($t['donation_type'] ?? 'cash') === 'in_kind');
        if (!empty($inkindList)): 
        ?>
        <hr class="my-4">
        <h6 class="mb-3 text-primary"><i class="bi bi-box-seam me-1"></i>In-Kind / Gamit Donations</h6>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-bordered align-middle" style="font-size:0.85rem;">
                <thead class="table-primary text-center">
                    <tr>
                        <th style="width:100px;">Date</th>
                        <th>Donor</th>
                        <th style="width:80px;">Type</th>
                        <th>Item Name</th>
                        <th style="width:70px;" class="text-end">Qty</th>
                        <th style="width:60px;" class="text-center">Unit</th>
                        <th style="width:110px;" class="text-end">Unit Value</th>
                        <th style="width:110px;" class="text-end">Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inkindList as $ik):
                        $itemCount = count($ik['items'] ?? []);
                        $rowspan   = max(1, $itemCount);
                    ?>
                    <?php if (!empty($ik['items'])): ?>
                        <?php foreach ($ik['items'] as $idx => $it):
                            $itTotal = (float)($it['total_value'] ?? ($it['quantity'] * $it['unit_value']));
                        ?>
                        <tr class="<?= $idx === 0 ? 'table-light' : '' ?>">
                            <?php if ($idx === 0): ?>
                            <td rowspan="<?= $rowspan ?>" style="white-space:nowrap; vertical-align:middle;">
                                <?= $ik['date_received'] ? date('M d, Y', strtotime($ik['date_received'])) : '-' ?>
                            </td>
                            <td rowspan="<?= $rowspan ?>" style="vertical-align:middle;">
                                <strong><?= e($ik['donor_name']) ?></strong>
                                <?php if ($ik['reference_code']): ?>
                                <br><small class="text-muted"><?= e($ik['reference_code']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td rowspan="<?= $rowspan ?>" class="text-center" style="vertical-align:middle;">
                                <span class="badge bg-secondary"><?= e($ik['donor_type']) ?></span>
                            </td>
                            <?php endif; ?>
                            <td>
                                <?php if ($it['photo']): ?>
                                <img src="../../<?= e($it['photo']) ?>" style="height:18px;width:22px;object-fit:cover;border-radius:2px;vertical-align:middle;margin-right:4px;" onerror="this.style.display='none'">
                                <?php endif; ?>
                                <?= e($it['item_name']) ?>
                            </td>
                            <td class="text-end"><?= number_format((float)$it['quantity'], 0) ?></td>
                            <td class="text-center text-muted"><?= e($it['unit']) ?></td>
                            <td class="text-end">₱<?= number_format((float)$it['unit_value'], 2) ?></td>
                            <td class="text-end text-primary fw-semibold">₱<?= number_format($itTotal, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <!-- Donor subtotal row -->
                        <tr class="table-primary">
                            <td colspan="7" class="text-end pe-2"><small><strong>Est. Total — <?= e($ik['donor_name']) ?>:</strong></small></td>
                            <td class="text-end fw-bold">₱<?= number_format((float)$ik['amount'], 2) ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td style="white-space:nowrap;"><?= $ik['date_received'] ? date('M d, Y', strtotime($ik['date_received'])) : '-' ?></td>
                            <td><strong><?= e($ik['donor_name']) ?></strong></td>
                            <td class="text-center"><span class="badge bg-secondary"><?= e($ik['donor_type']) ?></span></td>
                            <td colspan="4" class="text-muted fst-italic">— walang items na naka-record —</td>
                            <td class="text-end text-primary fw-bold">₱<?= number_format((float)$ik['amount'], 2) ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-primary fw-bold">
                        <td colspan="7" class="text-end">TOTAL IN-KIND (Gamit):</td>
                        <td class="text-end">₱<?= number_format($summary['total_inkind'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>

        <!-- Expenses Breakdown -->
        <?php if (!empty($expenses)): 
            $maxExp = max(array_column($expenses, 'amount')) ?: 1;
            // group by category
            $expByCategory = [];
            foreach ($expenses as $ex) {
                $cat = $ex['category'] ?: 'Uncategorized';
                if (!isset($expByCategory[$cat])) $expByCategory[$cat] = 0;
                $expByCategory[$cat] += $ex['amount'];
            }
            arsort($expByCategory);
        ?>
        <hr class="my-4">
        <h6 class="mb-3 text-danger"><i class="bi bi-receipt me-1"></i>Expenses / Disbursements</h6>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="stat-box" style="background:linear-gradient(135deg,#fff5f5,#fecaca);">
                    <div class="stat-value" style="color:#dc3545;">₱<?= number_format($expTotal, 0) ?></div>
                    <small class="text-muted">Total Disbursed</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box" style="background:linear-gradient(135deg,#f0fdf4,#bbf7d0);">
                    <div class="stat-value" style="color:#198754;">₱<?= number_format($summary['total_assistance'], 0) ?></div>
                    <small class="text-muted">Cash Received</small>
                </div>
            </div>
            <div class="col-md-4">
                <?php $bal2 = $summary['total_assistance'] - $expTotal; ?>
                <div class="stat-box" style="background:linear-gradient(135deg,#eff6ff,#bfdbfe);">
                    <div class="stat-value" style="color:<?= $bal2 >= 0 ? '#0d6efd' : '#dc3545' ?>;">
                        <?= $bal2 < 0 ? '-' : '' ?>₱<?= number_format(abs($bal2), 0) ?>
                    </div>
                    <small class="text-muted">Cash Balance (<?= $bal2 >= 0 ? 'Surplus' : 'Deficit' ?>)</small>
                </div>
            </div>
        </div>

        <?php if (!empty($expByCategory)): ?>
        <h6 class="mb-2 small text-muted">By Category</h6>
        <div class="table-responsive mb-3">
            <table class="table table-sm">
                <thead>
                    <tr><th>Category</th><th>Amount</th><th>%</th><th>Visual</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($expByCategory as $cat => $catAmt):
                        $catPct = $expTotal > 0 ? ($catAmt / $expTotal * 100) : 0;
                        $catBar = $expTotal > 0 ? ($catAmt / $expTotal * 100) : 0;
                    ?>
                    <tr>
                        <td><strong><?= e($cat) ?></strong></td>
                        <td>₱<?= number_format($catAmt, 2) ?></td>
                        <td><?= number_format($catPct, 1) ?>%</td>
                        <td style="width:35%;">
                            <div style="height:24px;background:linear-gradient(90deg,#dc3545,#f97316);border-radius:4px;width:<?= $catBar ?>%;display:flex;align-items:center;justify-content:flex-end;padding-right:6px;color:#fff;font-size:.8rem;font-weight:600;">
                                <?php if ($catBar > 20): ?><?= number_format($catPct, 0) ?>%<?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <h6 class="mb-2 small text-muted">Expense Records</h6>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead class="table-danger">
                    <tr>
                        <th>Date</th>
                        <th>Title / Purpose</th>
                        <th>Category</th>
                        <th>Paid To</th>
                        <th>Reference / OR</th>
                        <th>Linked Source</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $ex): ?>
                    <tr>
                        <td><?= $ex['expense_date'] ? date('M d, Y', strtotime($ex['expense_date'])) : '-' ?></td>
                        <td>
                            <strong><?= e($ex['title']) ?></strong>
                            <?php if ($ex['notes']): ?><br><small class="text-muted"><?= e($ex['notes']) ?></small><?php endif; ?>
                        </td>
                        <td><?= e($ex['category'] ?: '-') ?></td>
                        <td><?= e($ex['paid_to'] ?: '-') ?></td>
                        <td><?= e($ex['reference_code'] ?: '-') ?></td>
                        <td><?= e($ex['linked_source'] ?: '-') ?></td>
                        <td class="text-end text-danger">₱<?= number_format($ex['amount'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-danger">
                        <td colspan="6" class="text-end"><strong>TOTAL EXPENSES:</strong></td>
                        <td class="text-end"><strong>₱<?= number_format($expTotal, 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
        <hr class="my-4">
        <div class="text-center text-muted py-3">
            <i class="bi bi-receipt fs-2 d-block opacity-25 mb-2"></i>
            <small>No expenses recorded for this period.</small>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($reportType === 'detailed' || $reportType === 'source' || $reportType === 'program'): ?>
    <!-- Detailed Transactions -->
    <div class="report-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="bi bi-list-ul"></i>
                <?= $reportType === 'detailed' ? 'Detailed Transactions' : ($reportType === 'source' ? 'Transactions by Source' : 'Transactions by Program') ?>
                <span class="badge bg-secondary ms-2 fs-6"><?= number_format(count($transactions)) ?></span>
            </h5>
            <div class="input-group no-print" style="max-width: 280px;">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="txnSearch" class="form-control" placeholder="Search transactions...">
            </div>
        </div>
                <div class="table-responsive">
            <table class="table table-striped table-hover" id="txnTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Source</th>
                        <th>Program</th>
                        <th>Reference</th>
                        <th>Items (In-Kind)</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $groupField = $reportType === 'source' ? 'donor_type' : ($reportType === 'program' ? 'program_name' : null);
                    $currentGroup = null;
                    $groupTotal = 0;
                    
                    foreach ($transactions as $t):
                        $isInKind = ($t['donation_type'] ?? 'cash') === 'in_kind';
                        if ($groupField && $currentGroup !== $t[$groupField]):
                            if ($currentGroup !== null):
                    ?>
                    <tr class="table-secondary">
                        <td colspan="5" class="text-end"><strong><?= e($currentGroup) ?> Total:</strong></td>
                        <td class="text-end"><strong>₱<?= number_format($groupTotal, 2) ?></strong></td>
                    </tr>
                    <?php 
                            endif;
                            $currentGroup = $t[$groupField];
                            $groupTotal = 0;
                    ?>
                    <tr class="table-primary">
                        <td colspan="6"><strong><?= e($currentGroup ?: 'Unassigned') ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td><?= $t['date_received'] ? date('M d, Y', strtotime($t['date_received'])) : '-' ?></td>
                        <td>
                            <?= e($t['donor_name']) ?> <small class="text-muted">(<?= e($t['donor_type']) ?>)</small><br>
                            <?php if ($isInKind): ?>
                            <span class="badge bg-primary" style="font-size:.7rem;"><i class="bi bi-box-seam"></i> Gamit/In-Kind</span>
                            <?php else: ?>
                            <span class="badge bg-success" style="font-size:.7rem;"><i class="bi bi-cash-coin"></i> Cash</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($t['program_name'] ?: 'Not linked') ?></td>
                        <td><?= e($t['reference_code'] ?: '-') ?></td>
                        <td>
                            <?php if ($isInKind && !empty($t['items'])): ?>
                            <ul class="list-unstyled mb-0 small">
                                <?php foreach ($t['items'] as $it): ?>
                                <li>
                                    <?php if ($it['photo']): ?>
                                    <img src="../../<?= e($it['photo']) ?>" style="height:24px;width:28px;object-fit:cover;border-radius:3px;vertical-align:middle;" onerror="this.style.display='none'">
                                    <?php endif; ?>
                                    <strong><?= e($it['item_name']) ?></strong>
                                    × <?= number_format($it['quantity'], 0) ?> <?= e($it['unit']) ?>
                                    <span class="text-muted">@ ₱<?= number_format($it['unit_value'], 2) ?></span>
                                    = <strong>₱<?= number_format($it['total_value'] ?? ($it['quantity']*$it['unit_value']), 2) ?></strong>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">₱<?= number_format($t['amount'], 2) ?></td>
                    </tr>
                    <?php $groupTotal += $t['amount']; endforeach; 
                    
                    if ($groupField && $currentGroup !== null):
                    ?>
                    <tr class="table-secondary">
                        <td colspan="5" class="text-end"><strong><?= e($currentGroup) ?> Total:</strong></td>
                        <td class="text-end"><strong>₱<?= number_format($groupTotal, 2) ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    
                    <?php if (empty($transactions)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No records found for the selected period</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <?php if ($summary['total_inkind'] > 0): ?>
                    <tr class="table-secondary">
                        <td colspan="5" class="text-end"><small>Cash Subtotal:</small></td>
                        <td class="text-end"><small>₱<?= number_format($summary['total_assistance'], 2) ?></small></td>
                    </tr>
                    <tr class="table-secondary">
                        <td colspan="5" class="text-end"><small>In-Kind (Gamit) Subtotal:</small></td>
                        <td class="text-end"><small>₱<?= number_format($summary['total_inkind'], 2) ?></small></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="table-dark">
                        <td colspan="5" class="text-end"><strong>GRAND TOTAL (Cash Only):</strong></td>
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
<script>
// ── Quick Date Shortcuts ──────────────────────────────────────────
(function () {
    const dfrom = document.getElementById('dateFrom');
    const dto   = document.getElementById('dateTo');
    if (!dfrom || !dto) return;

    function fmt(d) {
        return d.toISOString().slice(0, 10);
    }

    document.querySelectorAll('.quick-date').forEach(btn => {
        btn.addEventListener('click', function () {
            const now   = new Date();
            const y     = now.getFullYear();
            const m     = now.getMonth(); // 0-indexed

            let from, to;
            switch (this.dataset.range) {
                case 'this_month':
                    from = new Date(y, m, 1);
                    to   = new Date(y, m + 1, 0);
                    break;
                case 'last_month':
                    from = new Date(y, m - 1, 1);
                    to   = new Date(y, m, 0);
                    break;
                case 'last3':
                    from = new Date(y, m - 2, 1);
                    to   = new Date(y, m + 1, 0);
                    break;
                case 'this_year':
                    from = new Date(y, 0, 1);
                    to   = new Date(y, 11, 31);
                    break;
                case 'last_year':
                    from = new Date(y - 1, 0, 1);
                    to   = new Date(y - 1, 11, 31);
                    break;
            }
            if (from && to) {
                dfrom.value = fmt(from);
                dto.value   = fmt(to);
            }
            // Highlight active button
            document.querySelectorAll('.quick-date').forEach(b => b.classList.remove('active', 'btn-secondary'));
            this.classList.add('active', 'btn-secondary');
            this.classList.remove('btn-outline-secondary');
        });
    });
})();

// ── Transaction Table Search ──────────────────────────────────────
(function () {
    const input = document.getElementById('txnSearch');
    const table = document.getElementById('txnTable');
    if (!input || !table) return;

    input.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            if (row.classList.contains('table-primary') || row.classList.contains('table-secondary')) return;
            row.style.display = (!q || row.textContent.toLowerCase().includes(q)) ? '' : 'none';
        });
    });
})();
</script>
</body>
</html>
