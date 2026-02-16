<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

$ids = $_GET['ids'] ?? '';
$format = $_GET['format'] ?? 'csv';

// Filters for "all" export
$search = trim($_GET['q'] ?? '');
$category_filter = trim($_GET['category'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');
$sort = $_GET['sort'] ?? 'date_desc';

if (empty($ids)) {
    die('No galleries selected');
}

if ($ids === 'all') {
    // Export based on active filters
    $where_conditions = [];
    $query_params = [];
    $param_types = '';

    if ($search !== '') {
        $where_conditions[] = "(title LIKE ? OR category LIKE ?)";
        $search_term = "%{$search}%";
        $query_params[] = $search_term;
        $query_params[] = $search_term;
        $param_types .= 'ss';
    }

    if ($category_filter !== '') {
        $where_conditions[] = "category = ?";
        $query_params[] = $category_filter;
        $param_types .= 's';
    }

    if ($date_from !== '') {
        $where_conditions[] = "DATE(created_at) >= ?";
        $query_params[] = $date_from;
        $param_types .= 's';
    }

    if ($date_to !== '') {
        $where_conditions[] = "DATE(created_at) <= ?";
        $query_params[] = $date_to;
        $param_types .= 's';
    }

    $sql = "SELECT * FROM galleries";
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(" AND ", $where_conditions);
    }

    switch ($sort) {
        case 'date_asc': $sql .= " ORDER BY created_at ASC"; break;
        case 'title_asc': $sql .= " ORDER BY title ASC"; break;
        case 'title_desc': $sql .= " ORDER BY title DESC"; break;
        default: $sql .= " ORDER BY created_at DESC"; break;
    }

    $stmt = $conn->prepare($sql);
    if (!empty($query_params)) {
        $stmt->bind_param($param_types, ...$query_params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Export selected IDs
    $id_array = explode(',', $ids);
    $id_array = array_filter($id_array, 'is_numeric');

    if (empty($id_array)) {
        die('Invalid gallery IDs');
    }

    $placeholders = str_repeat('?,', count($id_array) - 1) . '?';
    $types = str_repeat('i', count($id_array));

    $sql = "SELECT * FROM galleries WHERE id IN ($placeholders) ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$id_array);
    $stmt->execute();
    $result = $stmt->get_result();
}

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="galleries_export_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Title', 'Category', 'Image Count', 'Created At']);
    
    while ($row = $result->fetch_assoc()) {
        $images = array_filter(explode(',', $row['images'] ?? ''));
        fputcsv($output, [
            $row['id'],
            $row['title'],
            $row['category'],
            count($images),
            $row['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}

// -------------------------
// PDF Export (professional report, forced download)
// -------------------------
if ($format === 'pdf') {
    // Collect rows first (mysqli result is forward-only)
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $generatedAt = date('F d, Y h:i A');

    // Use standardized association name and real logo for this report
    $systemName = 'Bankero and Fisherman Association';
    // Prefer JPEG logo for PDF compatibility (no GD required)
    // This points to index/images/logo1.png
    $logoPath = __DIR__ . '/../images/logo.jpg';

    // --- PDF helpers (no external libs) ---
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

        // PNG/GIF -> convert to JPEG via GD (common in XAMPP)
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

        // Preserve alpha by flattening onto white background
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

    function build_galleries_report_pdf(array $rows, $generatedAt, $logoBytes, $logoPxW, $logoPxH, $systemName) {
        // A4 in points
        $pageW = 595.28;
        $pageH = 841.89;

        $marginX = 50;
        $marginTop = 40;
        $marginBottom = 50;

        $headerH = 120;
        $footerH = 35;

        $usableW = $pageW - ($marginX * 2);

        // Table config - improved proportions
        $colW = [
            38,                 // ID (narrower)
            $usableW * 0.38,    // Title
            $usableW * 0.23,    // Category
            $usableW * 0.15,    // Image Count
            $usableW - (38 + ($usableW * 0.38) + ($usableW * 0.23) + ($usableW * 0.15)) // Created At
        ];
        $colW = array_map(fn($v) => (float)$v, $colW);

        $rowH = 22;           // Taller rows for better readability
        $headerRowH = 24;

        $tableTopY = $marginTop + $headerH;
        $tableBottomY = $pageH - $marginBottom - $footerH;
        $rowsPerPage = (int) floor(($tableBottomY - ($tableTopY + $headerRowH)) / $rowH);
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
        $fontBold = $addObj("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>");

        // Optional logo image (JPEG stream)
        $imageObj = null;
        if (!empty($logoBytes) && $logoPxW > 0 && $logoPxH > 0) {
            $imgLen = strlen($logoBytes);
            $imgDict = "<< /Type /XObject /Subtype /Image /Width {$logoPxW} /Height {$logoPxH} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length {$imgLen} >>\nstream\n";
            // Append raw bytes without extra newline so /Length matches exactly
            $imgDict .= $logoBytes . "endstream";
            $imageObj = $addObj($imgDict);
        }




        // Pages root placeholder
        $pagesRootObj = $addObj("<< /Type /Pages /Kids [] /Count 0 >>");

        $pageObjNums = [];

        $reportTitle = 'Galleries Export Report';

        // Helper to convert y from top-based to PDF bottom-based
        $toPdfY = function($yFromTop) use ($pageH) {
            return $pageH - $yFromTop;
        };

        // Approximate string width for centering (Helvetica avg width ~0.52em)
        $approxTextWidth = function($text, $fontSizePt) {
            $len = strlen((string)$text);
            return $len * ($fontSizePt * 0.52);
        };

        // Professional color scheme
        $colorPrimary = [41, 98, 255];       // Modern blue
        $colorDark = [31, 41, 55];           // Dark text
        $colorMuted = [107, 114, 128];       // Muted text
        $colorBg = [249, 250, 251];          // Light background
        $colorBorder = [229, 231, 235];      // Borders

        for ($p = 1; $p <= $totalPages; $p++) {
            $startIndex = ($p - 1) * $rowsPerPage;
            $pageRows = array_slice($rows, $startIndex, $rowsPerPage);

            $c = '';

            // White background
            $c .= "q\n";
            $c .= "1 1 1 rg\n";
            $c .= sprintf("%.2F %.2F %.2F %.2F re f\n", 0, 0, $pageW, $pageH);
            $c .= "Q\n";

            // Modern header background with gradient effect (simulated with rectangles)
            $headerBgY = $toPdfY(0);
            $c .= pdf_color_rgb(248, 250, 252) . " rg\n";
            $c .= sprintf("%.2F %.2F %.2F %.2F re f\n", 0, $headerBgY - 90, $pageW, 90);
            
            // Accent line at top
            $c .= pdf_color_rgb($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]) . " rg\n";
            $c .= sprintf("%.2F %.2F %.2F %.2F re f\n", 0, $pageH - 4, $pageW, 4);

            // Logo section: prefer real logo image, fallback to BFA square
            $logoSize = 56;
            $logoX = $marginX;
            $logoYTop = $marginTop;

            if ($imageObj) {
                // Fit logo while maintaining aspect ratio
                $scale = min($logoSize / $logoPxW, $logoSize / $logoPxH);
                $drawW = $logoPxW * $scale;
                $drawH = $logoPxH * $scale;
                $imgX = $logoX;
                $imgY = $toPdfY($logoYTop + $drawH);

                $c .= sprintf("q %.2F 0 0 %.2F %.2F %.2F cm /Im1 Do Q\n", $drawW, $drawH, $imgX, $imgY);
            } else {
                // Fallback: blue square with "BFA" text
                $badgeSize = $logoSize;
                $badgeY = $toPdfY($logoYTop + $badgeSize);

                $c .= pdf_color_rgb($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]) . " rg\n";
                $c .= sprintf("%.2F %.2F %.2F %.2F re f\n", $logoX, $badgeY, $badgeSize, $badgeSize);

                $badgeText = 'BFA';
                $badgeFont = 18;
                $btw = $approxTextWidth($badgeText, $badgeFont);
                $btX = $logoX + ($badgeSize / 2) - ($btw / 2);
                $btY = $toPdfY($logoYTop + ($badgeSize / 2) + 6);
                $c .= "BT /F2 {$badgeFont} Tf 1 1 1 rg 1 0 0 1 " . sprintf("%.2F %.2F", $btX, $btY) . " Tm (" . pdf_escape_text($badgeText) . ") Tj ET\n";
            }

            // Header text (aligned to the right of logo)
            $textStartX = $logoX + $logoSize + 16;
            
            // Report title (larger, bold) - main heading
            $titleFont = 16;
            $titleX = $textStartX;
            $titleY = $toPdfY($marginTop + 16);
            $c .= "BT /F2 {$titleFont} Tf " . pdf_color_rgb($colorDark[0], $colorDark[1], $colorDark[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $titleX, $titleY) . " Tm (" . pdf_escape_text($reportTitle) . ") Tj ET\n";

            // System name (smaller, below title)
            $sysFont = 10;
            $sysX = $textStartX;
            $sysY = $toPdfY($marginTop + 33);
            $c .= "BT /F1 {$sysFont} Tf " . pdf_color_rgb($colorMuted[0], $colorMuted[1], $colorMuted[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $sysX, $sysY) . " Tm (" . pdf_escape_text($systemName) . ") Tj ET\n";

            // Address (below system name)
            $addressFont = 9;
            $addressText = 'Barangay Baretto, Olongapo City';
            $addressX = $textStartX;
            $addressY = $toPdfY($marginTop + 47);
            $c .= "BT /F1 {$addressFont} Tf " . pdf_color_rgb($colorMuted[0], $colorMuted[1], $colorMuted[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $addressX, $addressY) . " Tm (" . pdf_escape_text($addressText) . ") Tj ET\n";

            // Meta info box (similar to doc-meta in member sheet)
            $metaBoxY = $toPdfY($marginTop + 72);
            $metaBoxH = 22;
            
            // Light background box with accent border
            $c .= pdf_color_rgb(250, 250, 250) . " rg\n";
            $c .= sprintf("%.2F %.2F %.2F %.2F re f\n", $marginX, $metaBoxY, $usableW, $metaBoxH);
            
            // (Removed orange left accent strip)
            
            // Border outline
            $c .= pdf_color_rgb(236, 236, 236) . " RG 1 w\n";
            $c .= sprintf("%.2F %.2F %.2F %.2F re S\n", $marginX, $metaBoxY, $usableW, $metaBoxH);
            
            // Generated on (left side)
            $metaFont = 9;
            $metaTextY = $toPdfY($marginTop + 84);
            $generatedLabel = 'Generated on: ';
            $c .= "BT /F1 {$metaFont} Tf " . pdf_color_rgb($colorMuted[0], $colorMuted[1], $colorMuted[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $marginX + 12, $metaTextY) . " Tm (" . pdf_escape_text($generatedLabel) . ") Tj ET\n";
            
            $genLabelW = $approxTextWidth($generatedLabel, $metaFont);
            $c .= "BT /F2 {$metaFont} Tf " . pdf_color_rgb($colorDark[0], $colorDark[1], $colorDark[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $marginX + 12 + $genLabelW, $metaTextY) . " Tm (" . pdf_escape_text($generatedAt) . ") Tj ET\n";
            
            // Total galleries (right side)
            $totalLabel = 'Total Galleries: ';
            $totalValue = (string)count($rows);
            $totalText = $totalLabel . $totalValue;
            $totalW = $approxTextWidth($totalText, $metaFont);
            $totalX = $pageW - $marginX - $totalW - 12;
            
            $c .= "BT /F1 {$metaFont} Tf " . pdf_color_rgb($colorMuted[0], $colorMuted[1], $colorMuted[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $totalX, $metaTextY) . " Tm (" . pdf_escape_text($totalLabel) . ") Tj ET\n";
            
            $totalLabelW = $approxTextWidth($totalLabel, $metaFont);
            $c .= "BT /F2 {$metaFont} Tf " . pdf_color_rgb($colorDark[0], $colorDark[1], $colorDark[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $totalX + $totalLabelW, $metaTextY) . " Tm (" . pdf_escape_text($totalValue) . ") Tj ET\n";

            // Horizontal divider line (cleaner separator)
            $lineY = $toPdfY($marginTop + 105);
            $c .= pdf_color_rgb($colorBorder[0], $colorBorder[1], $colorBorder[2]) . " RG 1 w\n";
            $c .= sprintf("%.2F %.2F m %.2F %.2F l S\n", $marginX, $lineY, $pageW - $marginX, $lineY);



            // Table header (professional styling)
            $thYTop = $tableTopY;
            $thY = $toPdfY($thYTop + $headerRowH);
            
            // Header background (primary color)
            $c .= pdf_color_rgb($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]) . " rg\n";
            $c .= sprintf("%.2F %.2F %.2F %.2F re f\n", $marginX, $thY, $usableW, $headerRowH);

            // Header text (white, bold, slightly larger)
            $headers = ['ID','Title','Category','Images','Created'];
            $x = $marginX;
            $textY = $toPdfY($thYTop + 16);
            
            for ($i=0;$i<count($headers);$i++) {
                $hdrX = $x + 6;
                // Center align ID and Images columns
                if ($i === 0 || $i === 3) {
                    $txtW = $approxTextWidth($headers[$i], 10);
                    $hdrX = $x + ($colW[$i] / 2) - ($txtW / 2);
                }
                $c .= "BT /F2 10 Tf 1 1 1 rg 1 0 0 1 " . sprintf("%.2F %.2F", $hdrX, $textY) . " Tm (" . pdf_escape_text($headers[$i]) . ") Tj ET\n";
                $x += $colW[$i];
            }

            // Table rows with improved styling
            $rowYTop = $thYTop + $headerRowH;
            $alt = false;

            foreach ($pageRows as $r) {
                $bg = $alt ? $colorBg : [255,255,255];
                $alt = !$alt;

                $y = $toPdfY($rowYTop + $rowH);
                $c .= pdf_color_rgb($bg[0],$bg[1],$bg[2]) . " rg\n";
                $c .= sprintf("%.2F %.2F %.2F %.2F re f\n", $marginX, $y, $usableW, $rowH);

                // Row bottom border (subtle)
                $c .= pdf_color_rgb($colorBorder[0], $colorBorder[1], $colorBorder[2]) . " RG 0.5 w\n";
                $c .= sprintf("%.2F %.2F m %.2F %.2F l S\n", $marginX, $y, $pageW - $marginX, $y);

                $id = (string)($r['id'] ?? '');
                $title = (string)($r['title'] ?? '');
                $cat = (string)($r['category'] ?? '');
                $images = array_filter(explode(',', $r['images'] ?? ''));
                $imgCount = (string)count($images);
                $created = !empty($r['created_at']) ? date('M d, Y', strtotime($r['created_at'])) : '';

                // Smart truncation with ellipsis
                if (strlen($title) > 48) $title = substr($title, 0, 45) . '...';
                if (strlen($cat) > 22) $cat = substr($cat, 0, 19) . '...';

                $cells = [$id, $title, $cat, $imgCount, $created];

                $x = $marginX;
                $cellTextY = $toPdfY($rowYTop + 14);

                // ID (centered, slightly bolder color)
                $idW = $approxTextWidth($cells[0], 9);
                $idX = $x + ($colW[0] / 2) - ($idW / 2);
                $c .= "BT /F2 9 Tf " . pdf_color_rgb($colorDark[0], $colorDark[1], $colorDark[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $idX, $cellTextY) . " Tm (" . pdf_escape_text($cells[0]) . ") Tj ET\n";
                $x += $colW[0];

                // Title (main content, darker)
                $c .= "BT /F1 9 Tf " . pdf_color_rgb($colorDark[0], $colorDark[1], $colorDark[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $x + 6, $cellTextY) . " Tm (" . pdf_escape_text($cells[1]) . ") Tj ET\n";
                $x += $colW[1];

                // Category (muted)
                $c .= "BT /F1 9 Tf " . pdf_color_rgb($colorMuted[0], $colorMuted[1], $colorMuted[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $x + 6, $cellTextY) . " Tm (" . pdf_escape_text($cells[2]) . ") Tj ET\n";
                $x += $colW[2];

                // Image count (centered, with badge effect)
                $cntW = $approxTextWidth($cells[3], 9);
                $cntX = $x + ($colW[3] / 2) - ($cntW / 2);
                $c .= "BT /F2 9 Tf " . pdf_color_rgb($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $cntX, $cellTextY) . " Tm (" . pdf_escape_text($cells[3]) . ") Tj ET\n";
                $x += $colW[3];

                // Created at (muted, smaller)
                $c .= "BT /F1 8 Tf " . pdf_color_rgb($colorMuted[0], $colorMuted[1], $colorMuted[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $x + 6, $cellTextY) . " Tm (" . pdf_escape_text($cells[4]) . ") Tj ET\n";

                $rowYTop += $rowH;
            }

            // Outer table border (professional finish)
            $tableRowsCount = max(count($pageRows), 1);
            $tableHeight = $headerRowH + ($rowH * $tableRowsCount);
            $tableBottomPdfY = $toPdfY($thYTop + $tableHeight);

            $c .= pdf_color_rgb($colorPrimary[0], $colorPrimary[1], $colorPrimary[2]) . " RG 1.5 w\n";
            $c .= sprintf("%.2F %.2F %.2F %.2F re S\n", $marginX, $tableBottomPdfY, $usableW, $tableHeight);

            // Footer with enhanced styling
            $footerY = $pageH - $marginBottom - 20;
            
            // Footer divider
            $footerLineY = $toPdfY($footerY - 8);
            $c .= pdf_color_rgb($colorBorder[0], $colorBorder[1], $colorBorder[2]) . " RG 1 w\n";
            $c .= sprintf("%.2F %.2F m %.2F %.2F l S\n", $marginX, $footerLineY, $pageW - $marginX, $footerLineY);

            $footerTextY = $toPdfY($footerY);
            
            // Footer text (left)
            $footerText = 'Generated by ' . $systemName;
            $c .= "BT /F1 8 Tf " . pdf_color_rgb($colorMuted[0], $colorMuted[1], $colorMuted[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $marginX, $footerTextY) . " Tm (" . pdf_escape_text($footerText) . ") Tj ET\n";

            // Page number (right)
            $pageText = 'Page ' . $p . ' of ' . $totalPages;
            $pw = $approxTextWidth($pageText, 8);
            $px = $pageW - $marginX - $pw;
            $c .= "BT /F1 8 Tf " . pdf_color_rgb($colorMuted[0], $colorMuted[1], $colorMuted[2]) . " rg 1 0 0 1 " . sprintf("%.2F %.2F", $px, $footerTextY) . " Tm (" . pdf_escape_text($pageText) . ") Tj ET\n";

            // Content stream
            $stream = "<< /Length " . strlen($c) . " >>\nstream\n" . $c . "endstream";
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
        $infoObj = $addObj("<< /Title (" . pdf_escape_text('Galleries Export Report') . ") /Producer (bangkero_system) /CreationDate (D:{$creation}) >>");

        // Build PDF with xref
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        $count = count($objects);

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

    // Load logo bytes (best-effort). If loading fails, PDF falls back to BFA block.
    $logoW = 0;
    $logoH = 0;
    $logoBytes = null;
    if ($logoPath && is_file($logoPath)) {
        $logoBytes = load_logo_as_jpeg_bytes($logoPath, $logoW, $logoH);
    }

    $pdf = build_galleries_report_pdf($rows, $generatedAt, $logoBytes, $logoW, $logoH, $systemName);

    // Force download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="galleries_export_' . date('Y-m-d') . '.pdf"');
    header('Content-Length: ' . strlen($pdf));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    echo $pdf;
    exit;
}


// -------------------------
// Print Export (browser preview)
// -------------------------
if ($format === 'print') {
    $generatedAt = date('F d, Y h:i A');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Galleries Export Report</title>
        <style>
            :root {
                --bg: #f3f4f6;
                --surface: #ffffff;
                --border: #e5e7eb;
                --border-subtle: #e5e7eb;
                --text-main: #111827;
                --text-muted: #6b7280;
                --accent: #2563eb;
                --accent-soft: #eff6ff;
                --shadow-soft: 0 10px 25px rgba(15, 23, 42, 0.08);
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                padding: 32px;
                background: var(--bg);
                font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                color: var(--text-main);
            }
            .report-shell {
                max-width: 1024px;
                margin: 0 auto;
            }
            .report-card {
                background: var(--surface);
                border-radius: 16px;
                box-shadow: var(--shadow-soft);
                padding: 24px 28px 28px;
                border: 1px solid var(--border);
            }
            .report-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 18px;
                border-bottom: 1px solid var(--border-subtle);
                padding-bottom: 16px;
                margin-bottom: 16px;
            }
            .brand-block {
                display: flex;
                align-items: center;
                gap: 14px;
            }
            .brand-logo {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                background: #2563eb;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-weight: 800;
                font-size: 16px;
                letter-spacing: 1px;
            }
            .brand-text h1 {
                font-size: 17px;
                margin: 0 0 2px;
                font-weight: 700;
            }
            .brand-text .org {
                font-size: 13px;
                color: var(--text-muted);
            }
            .brand-text .loc {
                font-size: 12px;
                color: var(--text-muted);
            }
            .report-title {
                text-align: right;
            }
            .report-title-main {
                margin: 0;
                font-size: 19px;
                font-weight: 800;
                letter-spacing: .3px;
            }
            .report-title-sub {
                margin: 4px 0 0;
                font-size: 12px;
                color: var(--text-muted);
            }
            .meta-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                margin-bottom: 16px;
                padding: 10px 12px;
                border-radius: 10px;
                background: #f9fafb;
                border: 1px solid var(--border-subtle);
            }
            .meta-left, .meta-right {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 12px;
                color: var(--text-muted);
            }
            .meta-label { font-weight: 500; }
            .meta-value { font-weight: 600; color: var(--text-main); }
            .meta-chip {
                padding: 3px 8px;
                border-radius: 999px;
                background: var(--accent-soft);
                color: var(--accent);
                font-weight: 600;
                font-size: 11px;
            }
            .table-card {
                border-radius: 12px;
                overflow: hidden;
                border: 1px solid var(--border);
            }
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 13px;
            }
            thead {
                background: var(--accent);
                color: #fff;
            }
            th {
                padding: 10px 12px;
                text-align: left;
                font-weight: 600;
                font-size: 11px;
                letter-spacing: .04em;
                text-transform: uppercase;
                border-bottom: 1px solid rgba(15,23,42,0.18);
            }
            th.numeric { text-align: center; }
            th.date { text-align: right; }
            tbody tr {
                background: #ffffff;
                transition: background-color 0.12s ease, transform 0.08s ease;
            }
            tbody tr:nth-child(even) { background: #f9fafb; }
            tbody tr:hover {
                background: #eef2ff;
            }
            td {
                padding: 9px 12px;
                border-top: 1px solid var(--border-subtle);
                color: #111827;
            }
            td.title {
                font-weight: 600;
                color: #111827;
            }
            td.numeric { text-align: center; }
            td.date { text-align: right; white-space: nowrap; }
            .badge {
                display: inline-flex;
                align-items: center;
                padding: 3px 8px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 600;
                border: 1px solid transparent;
            }
            .badge-meetings { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
            .badge-awards { background: #fef3c7; color: #92400e; border-color: #facc15; }
            .badge-activities { background: #ecfdf3; color: #166534; border-color: #6ee7b7; }
            .badge-default { background: #f3f4f6; color: #374151; border-color: #e5e7eb; }
            .table-foot-note {
                margin-top: 8px;
                font-size: 11px;
                color: var(--text-muted);
                text-align: right;
            }
            @media print {
                body { background: #ffffff; padding: 16px; }
                .report-card { box-shadow: none; border-radius: 0; }
                .table-card { border-radius: 0; }
                .meta-row { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                thead, .badge, .brand-logo { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            }
        </style>
    </head>
    <body onload="window.print()">
    <div class="report-shell">
        <div class="report-card">
            <div class="report-header">
                <div class="brand-block">
                    <div class="brand-logo">BFA</div>
                    <div class="brand-text">
                        <h1>Galleries Export Report</h1>
                        <div class="org">Bankero and Fisherman Association</div>
                        <div class="loc">Barangay Baretto, Olongapo City</div>
                    </div>
                </div>
                <div class="report-title">
                    <p class="report-title-main">Summary Report</p>
                    <p class="report-title-sub">Administrative Dashboard View</p>
                </div>
            </div>

            <div class="meta-row">
                <div class="meta-left">
                    <span class="meta-label">Generated on:</span>
                    <span class="meta-value"><?php echo htmlspecialchars($generatedAt); ?></span>
                </div>
                <div class="meta-right">
                    <span class="meta-label">Total Galleries:</span>
                    <span class="meta-value meta-chip"><?php echo count($rows); ?></span>
                </div>
            </div>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th class="numeric">Images</th>
                            <th class="date">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                            $images = array_filter(explode(',', $row['images'] ?? ''));
                            $category = trim($row['category'] ?? '');
                            $badgeClass = 'badge-default';
                            if (strcasecmp($category, 'Meetings') === 0) {
                                $badgeClass = 'badge-meetings';
                            } elseif (strcasecmp($category, 'Awards') === 0) {
                                $badgeClass = 'badge-awards';
                            } elseif (strcasecmp($category, 'Activities') === 0) {
                                $badgeClass = 'badge-activities';
                            }
                        ?>
                        <tr>
                            <td class="numeric"><?php echo htmlspecialchars($row['id']); ?></td>
                            <td class="title"><?php echo htmlspecialchars($row['title']); ?></td>
                            <td>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($category ?: 'Uncategorized'); ?></span>
                            </td>
                            <td class="numeric"><?php echo count($images); ?></td>
                            <td class="date"><?php echo !empty($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : ''; ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-foot-note">
                Export prepared by Bangkero System &mdash; dashboard-style report view.
            </div>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}


if ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="galleries_export_' . date('Y-m-d') . '.xls"');
    
    echo '<table border="1">
            <tr>
                <th style="background-color: #667eea; color: white;">ID</th>
                <th style="background-color: #667eea; color: white;">Title</th>
                <th style="background-color: #667eea; color: white;">Category</th>
                <th style="background-color: #667eea; color: white;">Image Count</th>
                <th style="background-color: #667eea; color: white;">Created At</th>
            </tr>';
            
    while ($row = $result->fetch_assoc()) {
        $images = array_filter(explode(',', $row['images'] ?? ''));
        echo '<tr>
                <td>' . htmlspecialchars($row['id']) . '</td>
                <td>' . htmlspecialchars($row['title']) . '</td>
                <td>' . htmlspecialchars($row['category']) . '</td>
                <td>' . count($images) . '</td>
                <td>' . htmlspecialchars($row['created_at']) . '</td>
              </tr>';
    }
    echo '</table>';
    exit;
}
?>
