<?php
require_once("fpdf.php");
require_once("../../config/db_connect.php");

$fileType = $_GET['file'] ?? 'sample';

// ── Fetch association info ────────────────────────────────────────────────────
$configRow = $conn->query("SELECT * FROM system_config LIMIT 1")->fetch_assoc();
$assocName    = $configRow['assoc_name']    ?? 'Bankero and Fishermen Association';
$assocAddress = $configRow['assoc_address'] ?? 'Barreto Street, Olongapo City';
$assocPhone   = $configRow['assoc_phone']   ?? '';
$assocLogo    = $configRow['assoc_logo']    ?? '';

$logoPath = __DIR__ . '/../../uploads/' . $assocLogo;
if (!file_exists($logoPath)) {
    $logoPath = __DIR__ . '/../../images/logo1.png';
}
$hasLogo = file_exists($logoPath);

// ── Custom FPDF class with professional header/footer ────────────────────────
class AssocPDF extends FPDF {
    public $assocName    = '';
    public $assocAddress = '';
    public $assocPhone   = '';
    public $logoPath     = '';
    public $hasLogo      = false;
    public $docTitle     = '';

    function Header() {
        $startY = 8;

        // Logo
        if ($this->hasLogo) {
            $this->Image($this->logoPath, 12, $startY, 22, 22);
            $this->SetX(38);
        }

        // Association name
        $this->SetY($startY + 1);
        if ($this->hasLogo) $this->SetX(38);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(31, 41, 55);
        $this->Cell(0, 7, $this->assocName, 0, 1, $this->hasLogo ? 'L' : 'C');

        // Address & phone
        if ($this->hasLogo) $this->SetX(38);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(0, 5, $this->assocAddress . ($this->assocPhone ? '  |  ' . $this->assocPhone : ''), 0, 1, $this->hasLogo ? 'L' : 'C');

        // Divider line
        $this->SetY(max($this->GetY(), $startY + 24));
        $this->SetDrawColor(102, 126, 234);
        $this->SetLineWidth(0.8);
        $this->Line(12, $this->GetY(), 198, $this->GetY());
        $this->Ln(4);

        // Document title
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(31, 41, 55);
        $this->Cell(0, 9, $this->docTitle, 0, 1, 'C');
        $this->Ln(3);
    }

    function Footer() {
        $this->SetY(-14);
        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.3);
        $this->Line(12, $this->GetY(), 198, $this->GetY());
        $this->Ln(2);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 6, 'Generated: ' . date('F d, Y  h:i A') . '   |   Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C');
    }

    // Helper: section label
    function SectionLabel($text) {
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(102, 126, 234);
        $this->Cell(0, 7, strtoupper($text), 0, 1, 'L');
        $this->SetDrawColor(200, 210, 255);
        $this->SetLineWidth(0.3);
        $this->Line(12, $this->GetY(), 198, $this->GetY());
        $this->Ln(3);
        $this->SetTextColor(31, 41, 55);
    }

    // Helper: field row
    function FieldRow($label, $lineWidth = 110) {
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(75, 85, 99);
        $this->Cell(58, 9, $label, 0, 0);
        $this->SetDrawColor(150, 150, 150);
        $this->SetLineWidth(0.2);
        $this->Line($this->GetX(), $this->GetY() + 8, $this->GetX() + $lineWidth, $this->GetY() + 8);
        $this->Ln(10);
    }
}

// ── Init PDF ──────────────────────────────────────────────────────────────────
$pdf = new AssocPDF();
$pdf->AliasNbPages();
$pdf->assocName    = $assocName;
$pdf->assocAddress = $assocAddress;
$pdf->assocPhone   = $assocPhone;
$pdf->logoPath     = $logoPath;
$pdf->hasLogo      = $hasLogo;

// ── CASES ─────────────────────────────────────────────────────────────────────
switch ($fileType) {

    // ── MEMBERSHIP FORM ──────────────────────────────────────────────────────
    case 'membership_form':
        $pdf->docTitle = 'Membership Application Form';
        $pdf->AddPage();

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(0, 6, 'Please fill in all fields clearly. All information will be kept confidential.', 0, 1, 'C');
        $pdf->Ln(4);

        $pdf->SectionLabel('Personal Information');
        $pdf->FieldRow('Full Name:');
        $pdf->FieldRow('Date of Birth:',        80);
        $pdf->FieldRow('Gender:',               80);
        $pdf->FieldRow('Civil Status:',         80);
        $pdf->FieldRow('Contact Number:');
        $pdf->FieldRow('Email Address:');
        $pdf->Ln(2);

        $pdf->SectionLabel('Address');
        $pdf->FieldRow('House No. / Street:');
        $pdf->FieldRow('Barangay:');
        $pdf->FieldRow('Municipality / City:');
        $pdf->FieldRow('Province:');
        $pdf->Ln(2);

        $pdf->SectionLabel('Fishing / Livelihood Information');
        $pdf->FieldRow('Work Type:');
        $pdf->FieldRow('Boat Name (if any):');
        $pdf->FieldRow('Fishing Area:');
        $pdf->FieldRow('License No.:',          80);
        $pdf->FieldRow('BFAR Fisherfolk ID:',   80);
        $pdf->FieldRow('Municipal Permit No.:', 80);
        $pdf->Ln(2);

        $pdf->SectionLabel('Emergency Contact');
        $pdf->FieldRow('Contact Person:');
        $pdf->FieldRow('Relationship:',         80);
        $pdf->FieldRow('Phone Number:');
        $pdf->Ln(6);

        $pdf->SectionLabel('Declaration');
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(75, 85, 99);
        $pdf->MultiCell(0, 5,
            "I hereby certify that all information provided above is true and correct to the best of my knowledge. ".
            "I agree to abide by the rules and regulations of the " . $assocName . "."
        );
        $pdf->Ln(8);

        // Signature boxes
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(75, 85, 99);
        $col = 15;
        foreach (['Applicant\'s Signature', 'Date', 'Approved by'] as $label) {
            $pdf->SetXY($col, $pdf->GetY());
            $pdf->SetDrawColor(150, 150, 150);
            $pdf->SetLineWidth(0.2);
            $pdf->Line($col, $pdf->GetY() + 12, $col + 55, $pdf->GetY() + 12);
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(120, 120, 120);
            $pdf->SetXY($col, $pdf->GetY() + 13);
            $pdf->Cell(55, 5, $label, 0, 0, 'C');
            $col += 65;
        }
        break;

    // ── EVENT GUIDELINES ─────────────────────────────────────────────────────
    case 'event_guidelines':
        $pdf->docTitle = 'Event Guidelines';
        $pdf->AddPage();

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(0, 6, 'Please read and follow all guidelines before, during, and after the event.', 0, 1, 'C');
        $pdf->Ln(4);

        $sections = [
            'Before the Event' => [
                'All members must pre-register through the officer-in-charge or online portal.',
                'Confirm attendance at least 2 days before the event date.',
                'Wear proper and appropriate attire as instructed by the committee.',
                'Bring valid association ID or any government-issued identification.',
            ],
            'During the Event' => [
                'Follow the event schedule and be present during roll call.',
                'Maintain decorum, discipline, and respect toward all participants.',
                'Refrain from using mobile phones unless for emergency purposes.',
                'All concerns must be directed to the designated officer or committee head.',
                'Comply with all safety protocols and venue rules at all times.',
            ],
            'After the Event' => [
                'Assist in the clean-up and restoration of the event venue.',
                'Return borrowed materials or equipment to the appropriate officer.',
                'Sign out properly from the attendance sheet before leaving.',
            ],
            'General Reminders' => [
                'Non-compliance with these guidelines may result in disciplinary action.',
                'The association reserves the right to amend these guidelines as necessary.',
                'Report any incidents or concerns to the Board of Officers immediately.',
            ],
        ];

        foreach ($sections as $heading => $items) {
            $pdf->SectionLabel($heading);
            foreach ($items as $i => $item) {
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetTextColor(55, 65, 81);
                $pdf->SetX(15);
                $pdf->Cell(8, 7, ($i + 1) . '.', 0, 0);
                $pdf->MultiCell(163, 7, $item);
            }
            $pdf->Ln(2);
        }

        // Signature
        $pdf->Ln(4);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(31, 41, 55);
        $pdf->Cell(0, 7, 'Prepared and Approved by:', 0, 1);
        $pdf->Ln(10);
        $pdf->SetDrawColor(100, 100, 100);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(15, $pdf->GetY(), 90, $pdf->GetY());
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->SetX(15);
        $pdf->Cell(75, 5, 'Signature over Printed Name', 0, 0, 'C');
        $pdf->Cell(20, 5, '', 0, 0);
        $pdf->SetDrawColor(100, 100, 100);
        $pdf->Line(110, $pdf->GetY() - 5, 185, $pdf->GetY() - 5);
        $pdf->Cell(75, 5, 'Position / Designation', 0, 1, 'C');
        break;

    // ── ATTENDANCE SHEET ─────────────────────────────────────────────────────
    case 'attendance_sheet':
        $pdf->docTitle = 'Attendance Sheet';
        $pdf->AddPage('L'); // Landscape

        // Meta fields
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(75, 85, 99);
        $fields = ['Event / Activity:', 'Date:', 'Venue:', 'Officer-in-Charge:'];
        $x = 15;
        foreach ($fields as $idx => $f) {
            if ($idx == 2) { $pdf->Ln(0); $x = 15; $pdf->SetY($pdf->GetY() + 1); }
            $pdf->SetX($x);
            $pdf->Cell(40, 8, $f, 0, 0);
            $pdf->SetDrawColor(150,150,150);
            $pdf->SetLineWidth(0.2);
            $pdf->Line($pdf->GetX(), $pdf->GetY() + 7, $pdf->GetX() + 90, $pdf->GetY() + 7);
            $x += 140;
            if ($idx % 2 == 1) { $pdf->Ln(10); $x = 15; }
        }
        $pdf->Ln(5);

        // Table header
        $pdf->SetFillColor(102, 126, 234);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetDrawColor(180, 180, 200);
        $pdf->SetLineWidth(0.3);
        $pdf->Cell(12,  10, 'No.',         1, 0, 'C', true);
        $pdf->Cell(70,  10, 'Full Name',   1, 0, 'C', true);
        $pdf->Cell(40,  10, 'Member ID',   1, 0, 'C', true);
        $pdf->Cell(45,  10, 'Time In',     1, 0, 'C', true);
        $pdf->Cell(45,  10, 'Time Out',    1, 0, 'C', true);
        $pdf->Cell(55,  10, 'Signature',   1, 1, 'C', true);

        // Rows — try to pull active members
        $members = [];
        $res = $conn->query("SELECT name FROM members WHERE membership_status='active' ORDER BY name LIMIT 30");
        if ($res) {
            while ($mrow = $res->fetch_assoc()) $members[] = $mrow['name'];
        }
        // Pad to at least 25 rows
        while (count($members) < 25) $members[] = '';

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(31, 41, 55);
        foreach ($members as $i => $name) {
            $fill = ($i % 2 === 0);
            $pdf->SetFillColor(248, 250, 252);
            $pdf->Cell(12,  9, $i + 1,   1, 0, 'C', $fill);
            $pdf->Cell(70,  9, $name,    1, 0, 'L', $fill);
            $pdf->Cell(40,  9, '',       1, 0, 'C', $fill);
            $pdf->Cell(45,  9, '',       1, 0, 'C', $fill);
            $pdf->Cell(45,  9, '',       1, 0, 'C', $fill);
            $pdf->Cell(55,  9, '',       1, 1, 'C', $fill);
        }

        // Footer note
        $pdf->Ln(4);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->Cell(0, 5, 'Total Attendees: ___________     Noted by: _______________________________     Date: _______________', 0, 1, 'L');
        break;

    // ── OFFICERS LIST ────────────────────────────────────────────────────────
    case 'officers_list':
        $pdf->docTitle = 'List of Association Officers';
        $pdf->AddPage();

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(0, 6, 'Current Board of Officers - ' . date('Y'), 0, 1, 'C');
        $pdf->Ln(4);

        // Pull live data — latest term per officer only, no duplicates
        $officers = [];
        $res = $conn->query("
            SELECT m.name, o.position, m.phone, o.term_start, o.term_end, r.role_name
            FROM officers o
            LEFT JOIN members m ON o.member_id = m.id
            LEFT JOIN officer_roles r ON o.role_id = r.id
            INNER JOIN (
                SELECT member_id, MAX(term_start) AS latest_start
                FROM officers
                GROUP BY member_id
            ) latest ON o.member_id = latest.member_id AND o.term_start = latest.latest_start
            WHERE m.name IS NOT NULL
            ORDER BY r.display_order ASC, m.name ASC
        ");
        if ($res) {
            while ($orow = $res->fetch_assoc()) $officers[] = $orow;
        }

        if (!empty($officers)) {
            // Table header
            $pdf->SetFillColor(102, 126, 234);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetDrawColor(180, 180, 200);
            $pdf->SetLineWidth(0.3);
            $pdf->Cell(7,  10, '#',          1, 0, 'C', true);
            $pdf->Cell(60, 10, 'Name',        1, 0, 'C', true);
            $pdf->Cell(50, 10, 'Position',    1, 0, 'C', true);
            $pdf->Cell(40, 10, 'Contact',     1, 0, 'C', true);
            $pdf->Cell(20, 10, 'Term Start',  1, 0, 'C', true);
            $pdf->Cell(20, 10, 'Term End',    1, 1, 'C', true);

            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(31, 41, 55);
            foreach ($officers as $i => $o) {
                $fill = ($i % 2 === 0);
                $pdf->SetFillColor(248, 250, 252);
                $termStart = !empty($o['term_start']) ? date('M Y', strtotime($o['term_start'])) : 'N/A';
                $termEnd   = !empty($o['term_end'])   ? date('M Y', strtotime($o['term_end']))   : 'Present';
                $name      = !empty($o['name'])      ? $o['name']                                            : 'N/A';
                $position  = !empty($o['position']) ? $o['position'] : (!empty($o['role_name']) ? $o['role_name'] : 'N/A');
                $phone     = !empty($o['phone'])    ? $o['phone']    : 'N/A';
                $pdf->Cell(7,  9, $i + 1,   1, 0, 'C', $fill);
                $pdf->Cell(60, 9, $name,     1, 0, 'L', $fill);
                $pdf->Cell(50, 9, $position, 1, 0, 'L', $fill);
                $pdf->Cell(40, 9, $phone,    1, 0, 'C', $fill);
                $pdf->Cell(20, 9, $termStart,1, 0, 'C', $fill);
                $pdf->Cell(20, 9, $termEnd,  1, 1, 'C', $fill);
            }
        } else {
            // Blank template if no data
            $pdf->SectionLabel('Officer Roster');
            $pdf->SetFillColor(102, 126, 234);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(8,  10, '#',        1, 0, 'C', true);
            $pdf->Cell(65, 10, 'Name',     1, 0, 'C', true);
            $pdf->Cell(55, 10, 'Position', 1, 0, 'C', true);
            $pdf->Cell(55, 10, 'Contact',  1, 1, 'C', true);
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(31, 41, 55);
            for ($i = 1; $i <= 10; $i++) {
                $fill = ($i % 2 === 0);
                $pdf->SetFillColor(248, 250, 252);
                $pdf->Cell(8,  9, $i, 1, 0, 'C', $fill);
                $pdf->Cell(65, 9, '', 1, 0, 'L', $fill);
                $pdf->Cell(55, 9, '', 1, 0, 'L', $fill);
                $pdf->Cell(55, 9, '', 1, 1, 'C', $fill);
            }
        }

        // Certification
        $pdf->Ln(8);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(75, 85, 99);
        $pdf->MultiCell(0, 5,
            "This is to certify that the above list constitutes the duly elected/appointed officers of the " .
            $assocName . " for the current term."
        );
        $pdf->Ln(10);

        // Signature line
        $pdf->SetDrawColor(100, 100, 100);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(15, $pdf->GetY(), 90, $pdf->GetY());
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->SetX(15);
        $pdf->Cell(75, 5, 'Secretary\'s Signature', 0, 0, 'C');
        $pdf->Cell(30, 5, '', 0, 0);
        $pdf->Line(120, $pdf->GetY() - 5, 195, $pdf->GetY() - 5);
        $pdf->Cell(75, 5, 'President\'s Signature', 0, 1, 'C');
        break;

    default:
        $pdf->docTitle = 'Document';
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->Cell(0, 10, 'Invalid or unsupported document type.', 0, 1, 'C');
}

$pdf->Output("I", $assocName . ' - ' . ucwords(str_replace('_', ' ', $fileType)) . '.pdf');
?>
