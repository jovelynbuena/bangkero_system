<?php
require_once("fpdf.php"); // make sure fpdf.php is in the same folder

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

$fileType = $_GET['file'] ?? 'sample';

// Header
$pdf->Cell(0, 10, 'Bangkero & Fishermen Association', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Official Document', 0, 1, 'C');
$pdf->Ln(10);

switch ($fileType) {
    // 🧾 MEMBERSHIP FORM
    case 'membership_form':
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Membership Form', 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(60, 8, 'Full Name:', 0, 0);
        $pdf->Cell(120, 8, '_______________________________', 0, 1);
        $pdf->Cell(60, 8, 'Address:', 0, 0);
        $pdf->Cell(120, 8, '_______________________________', 0, 1);
        $pdf->Cell(60, 8, 'Contact Number:', 0, 0);
        $pdf->Cell(120, 8, '_______________________________', 0, 1);
        $pdf->Cell(60, 8, 'Occupation:', 0, 0);
        $pdf->Cell(120, 8, '_______________________________', 0, 1);
        $pdf->Ln(10);
        $pdf->Cell(0, 8, 'Signature: ____________________________', 0, 1);
        break;

    // 📘 EVENT GUIDELINES
    case 'event_guidelines':
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Event Guidelines', 0, 1, 'C');
        $pdf->Ln(8);
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 8, 
            "1. All members must register before attending the event.\n" .
            "2. Follow the time schedule strictly.\n" .
            "3. Maintain cleanliness and discipline during activities.\n" .
            "4. Coordinate with officers for any concerns.\n" .
            "5. Non-compliance may result in disciplinary actions."
        );
        break;

    // 📊 ATTENDANCE SHEET
    case 'attendance_sheet':
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Attendance Sheet', 0, 1, 'C');
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(10, 10, 'No', 1, 0, 'C');
        $pdf->Cell(80, 10, 'Name', 1, 0, 'C');
        $pdf->Cell(50, 10, 'Signature', 1, 0, 'C');
        $pdf->Cell(50, 10, 'Remarks', 1, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        for ($i = 1; $i <= 10; $i++) {
            $pdf->Cell(10, 10, $i, 1, 0, 'C');
            $pdf->Cell(80, 10, '', 1, 0);
            $pdf->Cell(50, 10, '', 1, 0);
            $pdf->Cell(50, 10, '', 1, 1);
        }
        break;

    // 👥 OFFICERS LIST
    case 'officers_list':
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'List of Association Officers', 0, 1, 'C');
        $pdf->Ln(8);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(80, 10, 'Name', 1, 0, 'C');
        $pdf->Cell(60, 10, 'Position', 1, 0, 'C');
        $pdf->Cell(50, 10, 'Contact', 1, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $officers = [
            ['Juan Dela Cruz', 'President', '0912 345 6789'],
            ['Maria Santos', 'Vice President', '0918 234 5678'],
            ['Jose Ramos', 'Secretary', '0920 111 2222'],
            ['Ana Lopez', 'Treasurer', '0933 555 8888'],
        ];
        foreach ($officers as $row) {
            $pdf->Cell(80, 10, $row[0], 1, 0);
            $pdf->Cell(60, 10, $row[1], 1, 0);
            $pdf->Cell(50, 10, $row[2], 1, 1);
        }
        break;

    default:
        $pdf->Cell(0, 10, 'Invalid file type.', 0, 1, 'C');
}

$pdf->Output("I", ucfirst(str_replace('_', ' ', $fileType)) . ".pdf");
?>
