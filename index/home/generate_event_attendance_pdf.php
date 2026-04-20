<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../../login.php');
    exit;
}

require_once("fpdf.php");
require_once("../../config/db_connect.php");

$eventId        = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$attendanceDate = isset($_GET['date'])     ? $_GET['date']          : '';
$mode           = isset($_GET['mode'])     ? $_GET['mode']          : 'report'; // 'report' or 'sheet'

if ($eventId <= 0) die('Invalid event ID.');

// ── Fetch event details ───────────────────────────────────────────────────────
$stmt = $conn->prepare("SELECT id, event_name, date, time, location, description FROM events WHERE id = ? AND is_archived = 0");
$stmt->bind_param('i', $eventId);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) die('Event not found.');

// Use provided date or event date
if (empty($attendanceDate)) $attendanceDate = $event['date'];

// ── Fetch association info ────────────────────────────────────────────────────
$configRow    = $conn->query("SELECT * FROM system_config LIMIT 1")->fetch_assoc();
$assocName    = $configRow['assoc_name']    ?? 'Bangkero & Fishermen Association';
$assocAddress = $configRow['assoc_address'] ?? 'Barreto Street, Olongapo City';
$assocPhone   = $configRow['assoc_phone']   ?? '';
$assocLogo    = $configRow['assoc_logo']    ?? '';

$logoPath = __DIR__ . '/../uploads/config/' . $assocLogo;
if (!file_exists($logoPath)) {
    $logoPath = __DIR__ . '/../../images/logo1.png';
}
$hasLogo = file_exists($logoPath);

// ── Fetch attendance records for this event & date ────────────────────────────
$attStmt = $conn->prepare("
    SELECT m.name, a.time_in, a.time_out, a.status, a.remarks
    FROM member_attendance a
    INNER JOIN members m ON a.member_id = m.id
    WHERE a.event_id = ? AND a.attendance_date = ?
    ORDER BY m.name ASC
");
$attStmt->bind_param('is', $eventId, $attendanceDate);
$attStmt->execute();
$attResult = $attStmt->get_result();
$attendees = [];
while ($row = $attResult->fetch_assoc()) $attendees[] = $row;
$attStmt->close();

// ── Fetch ALL members (for absent list) ──────────────────────────────────────
$allMembers = [];
$mRes = $conn->query("SELECT id, name FROM members ORDER BY name ASC");
while ($row = $mRes->fetch_assoc()) $allMembers[] = $row;

// Build present member names set
$presentNames = array_column($attendees, 'name');
$totalMembers = count($allMembers);
$totalPresent = count($attendees);

// ── Custom FPDF class ─────────────────────────────────────────────────────────
class EventAttPDF extends FPDF {
    public $assocName    = '';
    public $assocAddress = '';
    public $assocPhone   = '';
    public $logoPath     = '';
    public $hasLogo      = false;
    public $docTitle     = '';

    function Header() {
        $startY = 8;
        if ($this->hasLogo) {
            $this->Image($this->logoPath, 12, $startY, 22, 22);
            $this->SetX(38);
        }
        $this->SetY($startY + 1);
        if ($this->hasLogo) $this->SetX(38);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(31, 41, 55);
        $this->Cell(0, 7, $this->assocName, 0, 1, $this->hasLogo ? 'L' : 'C');

        if ($this->hasLogo) $this->SetX(38);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(0, 5, $this->assocAddress . ($this->assocPhone ? '  |  ' . $this->assocPhone : ''), 0, 1, $this->hasLogo ? 'L' : 'C');

        $this->SetY(max($this->GetY(), $startY + 24));
        $this->SetDrawColor(102, 126, 234);
        $this->SetLineWidth(0.8);
        $this->Line(12, $this->GetY(), 285, $this->GetY());
        $this->Ln(4);

        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(31, 41, 55);
        $this->Cell(0, 9, $this->docTitle, 0, 1, 'C');
        $this->Ln(3);
    }

    function Footer() {
        $this->SetY(-14);
        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.3);
        $this->Line(12, $this->GetY(), 285, $this->GetY());
        $this->Ln(2);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 6, 'Generated: ' . date('F d, Y  h:i A') . '   |   Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C');
    }
}

// ── Init PDF (Landscape A4) ───────────────────────────────────────────────────
$pdf = new EventAttPDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->assocName    = $assocName;
$pdf->assocAddress = $assocAddress;
$pdf->assocPhone   = $assocPhone;
$pdf->logoPath     = $logoPath;
$pdf->hasLogo      = $hasLogo;
$pdf->docTitle     = ($mode === 'sheet') ? 'Attendance Sheet' : 'Event Attendance Report';
$pdf->SetMargins(12, 10, 12);
$pdf->SetAutoPageBreak(true, 18);
$pdf->AddPage();

// ── Event Info Block ──────────────────────────────────────────────────────────
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(31, 41, 55);
$pdf->Cell(0, 7, $event['event_name'], 0, 1, 'L');

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(100, 116, 139);
$eventDateFmt  = date('F d, Y', strtotime($event['date']));
$eventTimeFmt  = date('h:i A', strtotime($event['time']));
$attDateFmt    = date('F d, Y', strtotime($attendanceDate));

$pdf->Cell(0, 5, 'Event Date: ' . $eventDateFmt . '   |   Time: ' . $eventTimeFmt . '   |   Venue: ' . $event['location'], 0, 1, 'L');
$pdf->Cell(0, 5, 'Attendance Date: ' . $attDateFmt, 0, 1, 'L');

if (!empty($event['description'])) {
    $pdf->SetFont('Arial', 'I', 8.5);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->MultiCell(0, 5, $event['description']);
}
$pdf->Ln(2);

if ($mode === 'sheet') {
    // ── BLANK ATTENDANCE SHEET ────────────────────────────────────────────────
    // Table header: No | Member Name | Time In | Time Out | Signature
    $pdf->SetFillColor(102, 126, 234);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetDrawColor(180, 180, 200);
    $pdf->SetLineWidth(0.3);

    $pdf->Cell(12,  10, 'No.',         1, 0, 'C', true);
    $pdf->Cell(85,  10, 'Member Name', 1, 0, 'C', true);
    $pdf->Cell(45,  10, 'Time In',     1, 0, 'C', true);
    $pdf->Cell(45,  10, 'Time Out',    1, 0, 'C', true);
    $pdf->Cell(0,   10, 'Signature',   1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(31, 41, 55);
    foreach ($allMembers as $i => $member) {
        $fill = ($i % 2 === 0);
        $pdf->SetFillColor($fill ? 248 : 255, $fill ? 250 : 255, $fill ? 252 : 255);
        $pdf->Cell(12,  10, $i + 1,          1, 0, 'C', $fill);
        $pdf->Cell(85,  10, $member['name'], 1, 0, 'L', $fill);
        $pdf->Cell(45,  10, '',              1, 0, 'C', $fill);
        $pdf->Cell(45,  10, '',              1, 0, 'C', $fill);
        $pdf->Cell(0,   10, '',              1, 1, 'C', $fill);
    }

    // Footer note
    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->Cell(0, 5, 'Total Members: ' . $totalMembers . '     Total Present: _______     Total Absent: _______', 0, 1, 'L');

} else {
    // ── FILLED ATTENDANCE REPORT ──────────────────────────────────────────────
    // Summary bar
    $pdf->SetFillColor(238, 242, 255);
    $pdf->SetDrawColor(180, 190, 240);
    $pdf->SetLineWidth(0.3);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(60, 80, 180);
    $pdf->Cell(0, 9, 'Total Present: ' . $totalPresent . ' / ' . $totalMembers . ' members', 1, 1, 'C', true);
    $pdf->Ln(3);

    // Table header
    $pdf->SetFillColor(102, 126, 234);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetDrawColor(180, 180, 200);
    $pdf->SetLineWidth(0.3);

    $colNo      = 12;
    $colName    = 75;
    $colStatus  = 30;
    $colTimeIn  = 38;
    $colTimeOut = 38;

    $pdf->Cell($colNo,     10, 'No.',         1, 0, 'C', true);
    $pdf->Cell($colName,   10, 'Member Name', 1, 0, 'C', true);
    $pdf->Cell($colStatus, 10, 'Status',      1, 0, 'C', true);
    $pdf->Cell($colTimeIn, 10, 'Time In',     1, 0, 'C', true);
    $pdf->Cell($colTimeOut,10, 'Time Out',    1, 0, 'C', true);
    $pdf->Cell(0,          10, 'Remarks',     1, 1, 'C', true);

    // Build lookup: name -> attendance data
    $attByName = [];
    foreach ($attendees as $a) {
        $attByName[$a['name']] = $a;
    }

    $pdf->SetFont('Arial', '', 9.5);
    $pdf->SetTextColor(31, 41, 55);

    $rowNum = 1;
    foreach ($allMembers as $i => $member) {
        $fill      = ($i % 2 === 0);
        $att       = $attByName[$member['name']] ?? null;
        $isPresent = $att !== null;

        $status  = $isPresent ? 'Present' : 'Absent';
        $timeIn  = $isPresent && !empty($att['time_in'])  ? date('h:i A', strtotime($att['time_in']))  : '--';
        $timeOut = $isPresent && !empty($att['time_out']) ? date('h:i A', strtotime($att['time_out'])) : '--';
        $remarks = $isPresent ? ($att['remarks'] ?? '') : '';

        $pdf->SetFillColor($fill ? 248 : 255, $fill ? 250 : 255, $fill ? 252 : 255);
        $pdf->Cell($colNo, 8, $rowNum++, 1, 0, 'C', $fill);

        $pdf->SetTextColor(31, 41, 55);
        $pdf->Cell($colName, 8, $member['name'], 1, 0, 'L', $fill);

        $pdf->SetTextColor($isPresent ? 22 : 153, $isPresent ? 101 : 27, $isPresent ? 52 : 27);
        $pdf->Cell($colStatus, 8, $status, 1, 0, 'C', $fill);

        $pdf->SetTextColor(31, 41, 55);
        $pdf->Cell($colTimeIn,  8, $timeIn,  1, 0, 'C', $fill);
        $pdf->Cell($colTimeOut, 8, $timeOut, 1, 0, 'C', $fill);
        $pdf->Cell(0,           8, $remarks, 1, 1, 'L', $fill);
    }

    // Footer counts
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(75, 85, 99);
    $pdf->Cell(0, 6, 'Total Present: ' . $totalPresent . '   |   Total Absent: ' . ($totalMembers - $totalPresent) . '   |   Total Members: ' . $totalMembers, 0, 1, 'L');
}


$pdf->Ln(8);
$pdf->SetDrawColor(120, 120, 120);
$pdf->SetLineWidth(0.2);

// Signature lines
$sigY = $pdf->GetY();
$pdf->Line(15, $sigY + 12, 95, $sigY + 12);
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(120, 120, 120);
$pdf->SetXY(15, $sigY + 13);
$pdf->Cell(80, 5, 'Prepared by / Officer-in-Charge', 0, 0, 'C');

$pdf->Line(200, $sigY + 12, 280, $sigY + 12);
$pdf->SetXY(200, $sigY + 13);
$pdf->Cell(80, 5, 'Noted by / President', 0, 0, 'C');

// ── Output ────────────────────────────────────────────────────────────────────
$safeName  = preg_replace('/[^A-Za-z0-9_\-]/', '_', $event['event_name']);
$fileLabel = ($mode === 'sheet') ? 'Attendance_Sheet' : 'Attendance_Report';
$pdf->Output('I', $assocName . ' - ' . $fileLabel . ' - ' . $safeName . ' - ' . $attendanceDate . '.pdf');
?>
