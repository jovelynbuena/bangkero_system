<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../config/db_connect.php');

$eventId      = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$selectedDate = isset($_GET['attendance_date']) ? trim($_GET['attendance_date']) : '';

if ($eventId <= 0 || $selectedDate === '') {
    die('Invalid event or attendance date.');
}

// Fetch event details
$evtStmt = $conn->prepare("SELECT id, event_name, date, time, location, category FROM events WHERE id = ?");
$evtStmt->bind_param('i', $eventId);
$evtStmt->execute();
$eventDetails = $evtStmt->get_result()->fetch_assoc();
$evtStmt->close();

if (!$eventDetails) {
    die('Event not found.');
}

// Fetch attendance rows for this event & date (same logic as attendance_reports.php)
$rows = [];
$presentCount = 0;
$absentCount  = 0;

$attStmt = $conn->prepare("SELECT m.id, m.name, ma.status, ma.time_in, ma.time_out, ma.remarks
                           FROM members m
                           LEFT JOIN member_attendance ma
                             ON ma.member_id = m.id
                            AND ma.event_id = ?
                            AND ma.attendance_date = ?
                           ORDER BY m.name ASC");
$attStmt->bind_param('is', $eventId, $selectedDate);
$attStmt->execute();
$attRes = $attStmt->get_result();
while ($r = $attRes->fetch_assoc()) {
    $rows[] = $r;
    if ($r['status'] === 'present') {
        $presentCount++;
    } else {
        $absentCount++;
    }
}
$attStmt->close();

$totalMembers  = count($rows);
$attendancePct = 0;
if ($totalMembers > 0) {
    $attendancePct = round(($presentCount / $totalMembers) * 100, 1);
}

$generatedAt = date('F d, Y h:i A');
$systemName  = 'Bankero and Fisherman Association';
$logoPath    = __DIR__ . '/images/logo1.png';

// -------------------------
// PDF helper functions (no external library)
// -------------------------
function pdf_escape_text($text) {
    $text = (string)$text;
    $text = str_replace('\\', '\\\\', $text);
    $text = str_replace('(', '\\(', $text);
    $text = str_replace(')', '\\)', $text);
    $text = str_replace(["\r\n", "\r", "\n"], ' ', $text);
    return $text;
}

function pdf_color_rgb($r, $g, $b) {
    return sprintf('%.3F %.3F %.3F', $r/255, $g/255, $b/255);
}

function load_logo_as_jpeg_bytes($path, &$w, &$h) {
    $w = 0;
    $h = 0;
    if (!is_file($path)) return null;

    $info = @getimagesize($path);
    if (!$info) return null;

    $w = (int)$info[0];
    $h = (int)$info[1];
    $type = (int)$info[2];

    // JPEG
    if ($type === IMAGETYPE_JPEG) {
        return @file_get_contents($path) ?: null;
    }

    // PNG/GIF -> convert to JPEG via GD if available
    if (!function_exists('imagecreatetruecolor') || !function_exists('imagejpeg')) {
        return null;
    }

    $src = null;
    if ($type === IMAGETYPE_PNG && function_exists('imagecreatefrompng')) {
        $src = @imagecreatefrompng($path);
    } elseif ($type === IMAGETYPE_GIF && function_exists('imagecreatefromgif')) {
        $src = @imagecreatefromgif($path);
    }
    if (!$src) return null;

    $dst = imagecreatetruecolor($w, $h);
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefilledrectangle($dst, 0, 0, $w, $h, $white);

    if (function_exists('imagealphablending')) imagealphablending($dst, true);
    if (function_exists('imagesavealpha')) imagesavealpha($dst, false);
    imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);

    ob_start();
    imagejpeg($dst, null, 90);
    $jpeg = ob_get_clean();

    imagedestroy($src);
    imagedestroy($dst);

    return $jpeg ?: null;
}

function build_attendance_report_pdf(array $rows, $generatedAt, $logoBytes, $logoPxW, $logoPxH, $systemName, array $eventDetails, $selectedDate, $totalMembers, $presentCount, $absentCount, $attendancePct) {
    // A4 in points
    $pageW = 595.28;
    $pageH = 841.89;

    $marginX      = 50;
    $marginTop    = 40;
    $marginBottom = 50;

    $headerH = 120;
    $footerH = 35;

    $usableW = $pageW - ($marginX * 2);

    // Table config: ID, Member, Status, Time In, Time Out, Remarks
    $colW = [
        38,                  // ID
        $usableW * 0.30,     // Member Name
        $usableW * 0.12,     // Status
        $usableW * 0.14,     // Time In
        $usableW * 0.14,     // Time Out
        $usableW - (38 + ($usableW * 0.30) + ($usableW * 0.12) + ($usableW * 0.14) + ($usableW * 0.14)) // Remarks
    ];
    $colW = array_map(fn($v) => (float)$v, $colW);

    $rowH       = 22;
    $headerRowH = 24;

    $tableTopY    = $marginTop + $headerH;
    $tableBottomY = $pageH - $marginBottom - $footerH;
    $rowsPerPage  = (int) floor(($tableBottomY - ($tableTopY + $headerRowH)) / $rowH);
    if ($rowsPerPage < 1) $rowsPerPage = 1;

    $totalPages = (int) ceil(max(count($rows), 1) / $rowsPerPage);

    // PDF object builder
    $objects = [];
    $addObj = function($content) use (&$objects) {
        $objects[] = $content;
        return count($objects);
    };

    // Fonts
    $fontRegular = $addObj("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>");
    $fontBold    = $addObj("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>");

    // Optional logo image (JPEG stream)
    $imageObj = null;
    if (!empty($logoBytes) && $logoPxW > 0 && $logoPxH > 0) {
        $imgLen  = strlen($logoBytes);
        $imgDict = "<< /Type /XObject /Subtype /Image /Width {$logoPxW} /Height {$logoPxH} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length {$imgLen} >>\nstream\n";
        $imgDict .= $logoBytes . "endstream";
        $imageObj = $addObj($imgDict);
    }

    // Pages root placeholder
    $pagesRootObj = $addObj("<< /Type /Pages /Kids [] /Count 0 >>");
    $pageObjNums  = [];

    $reportTitle = 'Attendance Report';

    // Helper to convert y from top-based to PDF bottom-based
    $toPdfY = function($yFromTop) use ($pageH) {
        return $pageH - $yFromTop;
    };

    // Approximate string width for centering
    $approxTextWidth = function($text, $fontSizePt) {
        $len = strlen((string)$text);
        return $len * ($fontSizePt * 0.52);
    };

    // Color palette
    $colorPrimary = [14, 116, 144]; // teal-ish, matches attendance header
    $colorDark    = [31, 41, 55];
    $colorMuted   = [107, 114, 128];
    $colorBg      = [249, 250, 251];
    $colorBorder  = [229, 231, 235];

    $eventTitle   = $eventDetails['event_name'] ?? '';
    $eventDateStr = !empty($eventDetails['date']) ? date('M d, Y', strtotime($eventDetails['date'])) : '';
    $eventTimeStr = !empty($eventDetails['time']) ? date('h:i A', strtotime($eventDetails['time'])) : '';
    $eventLocStr  = $eventDetails['location'] ?? '';
    $reportDateStr = !empty($selectedDate) ? date('M d, Y', strtotime($selectedDate)) : '';

    for ($p = 1; $p <= $totalPages; $p++) {
        $startIndex = ($p - 1) * $rowsPerPage;
        $pageRows   = array_slice($rows, $startIndex, $rowsPerPage);

        $c = '';

        // White background
        $c .= "q\n";
        $c .= "1 1 1 rg\n";
        $c .= sprintf("%.2F %.2F %.2F %.2F re f\n", 0, 0, $pageW, $pageH);
        $c .= "Q\n";

        // Header background
        $headerBgY = $toPdfY(0);
        $c .= pdf_color_rgb(248, 250, 252) . " rg\n";
        $c .= sprintf("%.2F %.2F %.2F %.2F re f\n", 0, $headerBgY - 90, $pageW, 90);

        // Accent line at top
        $c .= pdf_color_rgb($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]) . " rg\n";
        $c .= sprintf("%.2F %.2F %.2F %.2F re f\n", 0, $pageH - 4, $pageW, 4);

        // Logo
        $logoSize = 56;
        $logoX    = $marginX;
        $logoYTop = $marginTop;

        if ($imageObj) {
            $scale = min($logoSize / $logoPxW, $logoSize / $logoPxH);
            $drawW = $logoPxW * $scale;
            $drawH = $logoPxH * $scale;
            $imgX  = $logoX;
            $imgY  = $toPdfY($logoYTop + $drawH);

            $c .= sprintf("q %.2F 0 0 %.2F %.2F %.2F cm /Im1 Do Q\n", $drawW, $drawH, $imgX, $imgY);
        } else {
            // Fallback blue square with BFA text
            $badgeSize = $logoSize;
            $badgeY    = $toPdfY($logoYTop + $badgeSize);

            $c .= pdf_color_rgb($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]) . " rg\n";
            $c .= sprintf("%.2F %.2F %.2F %.2F re f\n", $logoX, $badgeY, $badgeSize, $badgeSize);

            $badgeText = 'BFA';
            $badgeFont = 18;
            $btw       = $approxTextWidth($badgeText, $badgeFont);
            $btX       = $logoX + ($badgeSize / 2) - ($btw / 2);
            $btY       = $toPdfY($logoYTop + ($badgeSize / 2) + 6);
            $c .= "BT /F2 {$badgeFont} Tf 1 1 1 rg 1 0 0 1 " . sprintf("%.2F %.2F", $btX, $btY) . " Tm (" . pdf_escape_text($badgeText) . ") Tj ET\n";
        }

        // Header text
        $textStartX = $logoX + $logoSize + 16;

        // Report title
        $titleFont = 16;
        $titleX    = $textStartX;
        $titleY    = $toPdfY($marginTop + 16);
        $c .= "BT /F2 {$titleFont} Tf " . pdf_color_rgb($colorDark[0], $colorDark[1], $colorDark[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $titleX, $titleY) . " Tm (" . pdf_escape_text($reportTitle) . ") Tj ET\n";

        // System name
        $sysFont = 10;
        $sysX    = $textStartX;
        $sysY    = $toPdfY($marginTop + 33);
        $c .= "BT /F1 {$sysFont} Tf " . pdf_color_rgb($colorMuted[0], $colorMuted[1], $colorMuted[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $sysX, $sysY) . " Tm (" . pdf_escape_text($systemName) . ") Tj ET\n";

        // Event line
        $eventFont = 9;
        $eventLine = trim($eventTitle) !== ''
            ? $eventTitle
            : ('Event ID: ' . $eventDetails['id']);
        $eventX = $textStartX;
        $eventY = $toPdfY($marginTop + 47);
        $c .= "BT /F1 {$eventFont} Tf " . pdf_color_rgb($colorMuted[0], $colorMuted[1], $colorMuted[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $eventX, $eventY) . " Tm (" . pdf_escape_text($eventLine) . ") Tj ET\n";

        // Meta info area (no solid box to avoid white bar)
        $metaBoxY = $toPdfY($marginTop + 72);
        $metaBoxH = 30; // reserved height for layout only

        $metaFont   = 9;
        $metaTextY1 = $toPdfY($marginTop + 82);
        $metaTextY2 = $toPdfY($marginTop + 94);


        // First line: Generated on (left)
        $generatedLabel = 'Generated on: ';
        $c .= "BT /F1 {$metaFont} Tf " . pdf_color_rgb($colorMuted[0], $colorMuted[1], $colorMuted[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $marginX + 12, $metaTextY1) . " Tm (" . pdf_escape_text($generatedLabel) . ") Tj ET\n";

        $genLabelW = $approxTextWidth($generatedLabel, $metaFont);
        $c .= "BT /F2 {$metaFont} Tf " . pdf_color_rgb($colorDark[0], $colorDark[1], $colorDark[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $marginX + 12 + $genLabelW, $metaTextY1) . " Tm (" . pdf_escape_text($generatedAt) . ") Tj ET\n";

        // First line: Attendance date (right)
        if ($reportDateStr !== '') {
            $dateText = 'Attendance date: ' . $reportDateStr;
            $dateW    = $approxTextWidth($dateText, $metaFont);
            $dateX    = $pageW - $marginX - $dateW - 12;
            $c .= "BT /F1 {$metaFont} Tf " . pdf_color_rgb($colorMuted[0], $colorMuted[1], $colorMuted[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $dateX, $metaTextY1) . " Tm (" . pdf_escape_text($dateText) . ") Tj ET\n";
        }

        // Second line: totals (left)
        $totalsText = 'Total: ' . $totalMembers . '   Present: ' . $presentCount . '   Absent: ' . $absentCount . '   Attendance: ' . $attendancePct . '%';
        $c .= "BT /F1 {$metaFont} Tf " . pdf_color_rgb($colorMuted[0], $colorMuted[1], $colorMuted[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $marginX + 12, $metaTextY2) . " Tm (" . pdf_escape_text($totalsText) . ") Tj ET\n";

        // Divider line
        $lineY = $toPdfY($marginTop + 105);
        $c .= pdf_color_rgb($colorBorder[0], $colorBorder[1], $colorBorder[2]) . " RG 1 w\n";
        $c .= sprintf("%.2F %.2F m %.2F %.2F l S\n", $marginX, $lineY, $pageW - $marginX, $lineY);

        // Table header
        $thYTop = $tableTopY;
        $thY    = $toPdfY($thYTop + $headerRowH);

        $c .= pdf_color_rgb($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]) . " rg\n";
        $c .= sprintf("%.2F %.2F %.2F %.2F re f\n", $marginX, $thY, $usableW, $headerRowH);

        $headers = ['ID', 'Member', 'Status', 'Time In', 'Time Out', 'Remarks'];
        $x       = $marginX;
        $textY   = $toPdfY($thYTop + 16);

        for ($i = 0; $i < count($headers); $i++) {
            $hdrX = $x + 6;
            // Center align some columns
            if (in_array($i, [0, 2, 3, 4], true)) {
                $txtW = $approxTextWidth($headers[$i], 10);
                $hdrX = $x + ($colW[$i] / 2) - ($txtW / 2);
            }
            $c .= "BT /F2 10 Tf 1 1 1 rg 1 0 0 1 " . sprintf("%.2F %.2F", $hdrX, $textY) . " Tm (" . pdf_escape_text($headers[$i]) . ") Tj ET\n";
            $x += $colW[$i];
        }

        // Table rows
        $rowYTop = $thYTop + $headerRowH;
        $alt     = false;

        foreach ($pageRows as $r) {
            $bg  = $alt ? $colorBg : [255, 255, 255];
            $alt = !$alt;

            $y = $toPdfY($rowYTop + $rowH);
            $c .= pdf_color_rgb($bg[0], $bg[1], $bg[2]) . " rg\n";
            $c .= sprintf("%.2F %.2F %.2F %.2F re f\n", $marginX, $y, $usableW, $rowH);

            // Row bottom border
            $c .= pdf_color_rgb($colorBorder[0], $colorBorder[1], $colorBorder[2]) . " RG 0.5 w\n";
            $c .= sprintf("%.2F %.2F m %.2F %.2F l S\n", $marginX, $y, $pageW - $marginX, $y);

            $id      = (string)($r['id'] ?? '');
            $name    = (string)($r['name'] ?? '');
            $status  = $r['status'] === 'present' ? 'Present' : 'Absent';
            $timeIn  = !empty($r['time_in']) ? date('h:i A', strtotime($r['time_in'])) : '';
            $timeOut = !empty($r['time_out']) ? date('h:i A', strtotime($r['time_out'])) : '';
            $remarks = (string)($r['remarks'] ?? '');

            if (strlen($name) > 40)   $name    = substr($name, 0, 37) . '...';
            if (strlen($remarks) > 40) $remarks = substr($remarks, 0, 37) . '...';

            $cells = [$id, $name, $status, $timeIn, $timeOut, $remarks];

            $x         = $marginX;
            $cellTextY = $toPdfY($rowYTop + 14);

            // ID centered
            $idW = $approxTextWidth($cells[0], 9);
            $idX = $x + ($colW[0] / 2) - ($idW / 2);
            $c  .= "BT /F2 9 Tf " . pdf_color_rgb($colorDark[0], $colorDark[1], $colorDark[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $idX, $cellTextY) . " Tm (" . pdf_escape_text($cells[0]) . ") Tj ET\n";
            $x  += $colW[0];

            // Member name
            $c .= "BT /F1 9 Tf " . pdf_color_rgb($colorDark[0], $colorDark[1], $colorDark[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $x + 6, $cellTextY) . " Tm (" . pdf_escape_text($cells[1]) . ") Tj ET\n";
            $x += $colW[1];

            // Status (primary color, centered-ish)
            $statusW = $approxTextWidth($cells[2], 9);
            $statusX = $x + ($colW[2] / 2) - ($statusW / 2);
            $c .= "BT /F2 9 Tf " . pdf_color_rgb($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $statusX, $cellTextY) . " Tm (" . pdf_escape_text($cells[2]) . ") Tj ET\n";
            $x += $colW[2];

            // Time In
            $timeInW = $approxTextWidth($cells[3], 9);
            $timeInX = $x + ($colW[3] / 2) - ($timeInW / 2);
            $c .= "BT /F1 9 Tf " . pdf_color_rgb($colorDark[0], $colorDark[1], $colorDark[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $timeInX, $cellTextY) . " Tm (" . pdf_escape_text($cells[3]) . ") Tj ET\n";
            $x += $colW[3];

            // Time Out
            $timeOutW = $approxTextWidth($cells[4], 9);
            $timeOutX = $x + ($colW[4] / 2) - ($timeOutW / 2);
            $c .= "BT /F1 9 Tf " . pdf_color_rgb($colorDark[0], $colorDark[1], $colorDark[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $timeOutX, $cellTextY) . " Tm (" . pdf_escape_text($cells[4]) . ") Tj ET\n";
            $x += $colW[4];

            // Remarks (left-aligned)
            $c .= "BT /F1 9 Tf " . pdf_color_rgb($colorMuted[0], $colorMuted[1], $colorMuted[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $x + 6, $cellTextY) . " Tm (" . pdf_escape_text($cells[5]) . ") Tj ET\n";

            $rowYTop += $rowH;
        }

        // Outer table border
        $tableRowsCount  = max(count($pageRows), 1);
        $tableHeight     = $headerRowH + ($rowH * $tableRowsCount);
        $tableBottomPdfY = $toPdfY($thYTop + $tableHeight);

        $c .= pdf_color_rgb($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]) . " RG 1.5 w\n";
        $c .= sprintf("%.2F %.2F %.2F %.2F re S\n", $marginX, $tableBottomPdfY, $usableW, $tableHeight);

        // Footer
        $footerY      = $pageH - $marginBottom - 20;
        $footerLineY  = $toPdfY($footerY - 8);
        $c .= pdf_color_rgb($colorBorder[0], $colorBorder[1], $colorBorder[2]) . " RG 1 w\n";
        $c .= sprintf("%.2F %.2F m %.2F %.2F l S\n", $marginX, $footerLineY, $pageW - $marginX, $footerLineY);

        $footerTextY = $toPdfY($footerY);

        $footerText = 'Generated by ' . $systemName;
        $c .= "BT /F1 8 Tf " . pdf_color_rgb($colorMuted[0], $colorMuted[1], $colorMuted[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $marginX, $footerTextY) . " Tm (" . pdf_escape_text($footerText) . ") Tj ET\n";

        $pageText = 'Page ' . $p . ' of ' . $totalPages;
        $pw       = $approxTextWidth($pageText, 8);
        $px       = $pageW - $marginX - $pw;
        $c       .= "BT /F1 8 Tf " . pdf_color_rgb($colorMuted[0], $colorMuted[1], $colorMuted[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $px, $footerTextY) . " Tm (" . pdf_escape_text($pageText) . ") Tj ET\n";

        // Content stream
        $stream     = "<< /Length " . strlen($c) . " >>\nstream\n" . $c . "endstream";
        $contentObj = $addObj($stream);

        // Page object with resources
        $resources = "/Resources << /Font << /F1 {$fontRegular} 0 R /F2 {$fontBold} 0 R >>";
        if ($imageObj) {
            $resources .= " /XObject << /Im1 {$imageObj} 0 R >>";
        }
        $resources .= " >>";

        $pageObj = $addObj("<< /Type /Page /Parent {$pagesRootObj} 0 R /MediaBox [0 0 {$pageW} {$pageH}] {$resources} /Contents {$contentObj} 0 R >>");
        $pageObjNums[] = $pageObj;
    }

    // Update Pages root with Kids
    $kids = implode(' ', array_map(fn($n) => $n . ' 0 R', $pageObjNums));
    $objects[$pagesRootObj - 1] = "<< /Type /Pages /Kids [{$kids}] /Count " . count($pageObjNums) . " >>";

    // Catalog
    $catalogObj = $addObj("<< /Type /Catalog /Pages {$pagesRootObj} 0 R >>");

    // Info
    $creation = date('YmdHis');
    $infoObj  = $addObj("<< /Title (" . pdf_escape_text('Attendance Report') . ") /Producer (bangkero_system) /CreationDate (D:{$creation}) >>");

    // Build PDF with xref
    $pdf    = "%PDF-1.4\n";
    $offsets = [0];
    $count   = count($objects);

    for ($i = 1; $i <= $count; $i++) {
        $offsets[$i] = strlen($pdf);
        $pdf .= $i . " 0 obj\n" . $objects[$i - 1] . "\nendobj\n";
    }

    $xrefPos = strlen($pdf);
    $pdf .= "xref\n0 " . ($count + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i <= $count; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }

    $pdf .= "trailer\n<< /Size " . ($count + 1) . " /Root {$catalogObj} 0 R /Info {$infoObj} 0 R >>\n";
    $pdf .= "startxref\n{$xrefPos}\n%%EOF";

    return $pdf;
}

// Load logo bytes (best-effort)
$logoW = 0;
$logoH = 0;
$logoBytes = null;
if ($logoPath && is_file($logoPath)) {
    $logoBytes = load_logo_as_jpeg_bytes($logoPath, $logoW, $logoH);
}

$pdf = build_attendance_report_pdf($rows, $generatedAt, $logoBytes, $logoW, $logoH, $systemName, $eventDetails, $selectedDate, $totalMembers, $presentCount, $absentCount, $attendancePct);

// Output PDF download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="attendance_report_event_' . $eventId . '_' . $selectedDate . '.pdf"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

echo $pdf;
exit;
