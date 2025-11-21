<?php
session_start();

if (empty($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

require_once('../../config/db_connect.php');

$userId = $_SESSION['user_id'] ?? 0;

// Handle restore from archive
if (isset($_GET['retrieve']) && $userId) {
    $oid = intval($_GET['retrieve']);
    if ($oid > 0) {
        // Fetch officer info before restoring
        $stmt_get = $conn->prepare("
            SELECT o.id, m.name AS member_name 
            FROM officers_archive o 
            JOIN members m ON o.member_id = m.id 
            WHERE o.id = ?
        ");
        $stmt_get->bind_param("i", $oid);
        $stmt_get->execute();
        $res_get = $stmt_get->get_result();
        $officer = $res_get->fetch_assoc();
        $stmt_get->close();

        // Restore officer
        $stmt_insert = $conn->prepare("
            INSERT INTO officers (member_id, role_id, term_start, term_end, image)
            SELECT member_id, role_id, term_start, term_end, image
            FROM officers_archive
            WHERE id = ?
        ");
        $stmt_insert->bind_param("i", $oid);

        if ($stmt_insert->execute()) {
            $stmt_insert->close();

            // Delete from archive
            $stmt_delete = $conn->prepare("DELETE FROM officers_archive WHERE id = ?");
            $stmt_delete->bind_param("i", $oid);
            $stmt_delete->execute();
            $stmt_delete->close();

            // Log action
            $actionText = "Restored officer: {$officer['member_name']}";
            $stmt_log = $conn->prepare("INSERT INTO activity_logs (user_id, action, description) VALUES (?, ?, ?)");
            $stmt_log->bind_param("iss", $userId, $actionText, $actionText);
            $stmt_log->execute();
            $stmt_log->close();

            // Redirect to active officers list with success message
            header('Location: officerslist.php?retrieved=1');
            exit();
        } else {
            $stmt_insert->close();
            header('Location: officerslist.php?error=1');
            exit();
        }
    }
}

// Optional search
$search = $_GET['search'] ?? '';
$search_safe = $conn->real_escape_string($search);

$sql = "
    SELECT 
        o.id,
        o.term_start,
        o.term_end,
        o.image,
        r.role_name AS position,
        m.name AS member_name
    FROM officers_archive o
    JOIN members m ON o.member_id = m.id
    JOIN officer_roles r ON o.role_id = r.id
    WHERE m.name LIKE '%$search_safe%' OR r.role_name LIKE '%$search_safe%'
    ORDER BY r.role_name ASC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Officer Archives | Bangkero & Fishermen Association</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body { font-family: 'Segoe UI', sans-serif; background: #fff; }
.main-content { margin-left: 250px; padding: 32px 32px 16px 32px; min-height: 100vh; }

.officer-img { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; }

.table thead th {
    background: #ff7043 !important;
    color: #fff !important;
    font-weight: 600;
    border: none;
}
.table-bordered > :not(caption) > * > * { border-width: 2px; border-color: #80cbc4; }
.table-hover tbody tr:hover { background: #ffe0b2; }

.btn-success { background-color: #FF7043; border-color: #FF7043; }
.btn-success:hover { background-color: #FFA040; border-color: #FFA040; }
</style>
</head>
<body>

<?php include('../navbar.php'); ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Officer Archive</h2>
        <form class="d-flex" method="GET" action="officer_archive.php">
            <input type="text" name="search" class="form-control me-2" placeholder="Search archives..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-outline-primary">Search</button>
        </form>
    </div>

    <div class="table-responsive shadow-sm rounded-4">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>#</th>
                    <th>Photo</th>
                    <th>Member Name</th>
                    <th>Position</th>
                    <th>Term Start</th>
                    <th>Term End</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php $count = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center"><?= $count++ ?></td>
                            <td class="text-center">
                                <?php if (!empty($row['image'])): ?>
                                    <img src="../../uploads/<?= htmlspecialchars($row['image']) ?>" class="officer-img" alt="Officer Image">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/60x60?text=No+Image" class="officer-img" alt="No Image">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['member_name']) ?></td>
                            <td><?= htmlspecialchars($row['position']) ?></td>
                            <td class="text-center"><?= ($row['term_start'] !== "0000-00-00") ? htmlspecialchars($row['term_start']) : 'N/A' ?></td>
                            <td class="text-center"><?= ($row['term_end'] !== "0000-00-00") ? htmlspecialchars($row['term_end']) : 'N/A' ?></td>
                            <td class="text-center">
                                <button class="btn btn-success btn-sm" onclick="confirmRetrieve(<?= $row['id'] ?>)">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted">No archived officers.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmRetrieve(id) {
    Swal.fire({
        title: 'Retrieve officer?',
        text: "This will move the officer back to active list.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#FF7043',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, retrieve'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'archives_officers.php?retrieve=' + id;
        }
    });
}
</script>

</body>
</html>
