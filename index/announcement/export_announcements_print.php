<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

$search         = trim($_GET['q'] ?? '');
$date_from      = trim($_GET['from'] ?? '');
$date_to        = trim($_GET['to'] ?? '');
$has_image      = $_GET['has_image'] ?? 'all';
$filter_category = trim($_GET['category'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where[] = "(title LIKE ? OR content LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types   .= 'ss';
}

if ($date_from !== '') {
    $where[]  = "date_posted >= ?";
    $params[] = $date_from . " 00:00:00";
    $types   .= 's';
}

if ($date_to !== '') {
    $where[]  = "date_posted <= ?";
    $params[] = $date_to . " 23:59:59";
    $types   .= 's';
}

if ($has_image === '1') {
    $where[] = "image IS NOT NULL AND image <> ''";
} elseif ($has_image === '0') {
    $where[] = "(image IS NULL OR image = '')";
}

if ($filter_category !== '') {
    $where[]  = "category = ?";
    $params[] = $filter_category;
    $types   .= 's';
}

$sql = "SELECT id, title, category, date_posted, expiry_date, posted_by FROM announcements";
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " ORDER BY date_posted DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $bind = [$types];
    foreach ($params as $k => $v) {
        $bind[] = &$params[$k];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

function getAnnouncementStatus($expiry_date) {
    if (empty($expiry_date)) return 'Ongoing';

    $today  = new DateTime();
    $expiry = new DateTime($expiry_date);
    $diff   = $today->diff($expiry);

    if ($expiry < $today) {
        return 'Expired';
    } elseif ($diff->days <= 3) {
        return 'Expiring Soon';
    } else {
        return 'Active';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Announcements - Print</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --brand-primary: #00897b;
            --brand-accent: #ff7043;
        }
        body { padding: 28px; color: #222; }
        .doc-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 14px;
        }
        .doc-header img {
            height: 64px;
            width: auto;
        }
        .doc-header .titles {
            line-height: 1.2;
        }
        .doc-header .titles h1 {
            font-size: 1.35rem;
            margin: 0;
            color: var(--brand-primary);
            font-weight: 800;
        }
        .doc-header .titles .sub {
            font-size: 0.92rem;
            color: #666;
        }
        .doc-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            border-left: 4px solid var(--brand-accent);
            border-radius: 8px;
            margin-bottom: 16px;
            background: #fafafa;
        }
        .table { font-size: 12px; }
        .table thead th { background: #f3f6f8; color: #333; border-bottom: 2px solid #e0e0e0; }
        .table td, .table th { vertical-align: middle; }
        .footer-note { margin-top: 18px; font-size: 0.85rem; color: #666; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .doc-meta { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
    <script>
        window.addEventListener('load', function(){
            setTimeout(function(){ window.print(); }, 300);
        });
    </script>
</head>
<body>
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <h4 class="m-0">Announcements</h4>
        <button class="btn btn-secondary" onclick="window.close()">Close</button>
    </div>

    <div class="doc-header">
<<<<<<< HEAD
        <img src="../images/logo1.png" alt="Association Logo">
=======
        <?php require_once __DIR__ . '/../../config/logo_helper.php'; ?>
        <img src="<?= $assocLogoUrl ?>" alt="Association Logo">
>>>>>>> 5443c480df76631363d13229f44bcb08f4d23560
        <div class="titles">
            <h1>Bangkero & Fishermen Association</h1>
            <div class="sub">Announcements Report</div>
        </div>
    </div>
    <div class="doc-meta">
        <div>
            Printed on: <strong><?php echo date('F j, Y g:i A'); ?></strong>
        </div>
        <div>
            <?php
            $filters = [];
            if ($search !== '') $filters[] = 'Search: "' . htmlspecialchars($search) . '"';
            if ($filter_category !== '') $filters[] = 'Category: ' . htmlspecialchars($filter_category);
            if ($date_from !== '' || $date_to !== '') {
                $range = 'Date: ';
                $range .= $date_from !== '' ? htmlspecialchars($date_from) : 'Any';
                $range .= ' to ';
                $range .= $date_to !== '' ? htmlspecialchars($date_to) : 'Any';
                $filters[] = $range;
            }
            if ($has_image === '1') $filters[] = 'With Image Only';
            elseif ($has_image === '0') $filters[] = 'Without Image Only';

            echo !empty($filters) ? implode(' | ', $filters) : 'All announcements';
            ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date Posted</th>
                    <th>Expiry Date</th>
                    <th>Posted By</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 1; if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center"><?php echo $count++; ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><?php echo htmlspecialchars(getAnnouncementStatus($row['expiry_date'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['date_posted']))); ?></td>
                            <td><?php echo !empty($row['expiry_date']) ? htmlspecialchars(date('Y-m-d', strtotime($row['expiry_date']))) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($row['posted_by'] ?? 'Admin'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted">No announcements found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="footer-note">
        Generated by BFA Admin System.
    </div>
</body>
</html>
