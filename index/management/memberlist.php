<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

include('../../config/db_connect.php');

$memberName = $_SESSION['member_name'] ?? 'Admin';

// Handle archive request
if (isset($_GET['archive'])) {
    $id = intval($_GET['archive']);
    if ($id > 0) {
        $stmt = $conn->prepare("
            INSERT INTO member_archive (member_id, name, email, phone, archived_at)
            SELECT id, name, email, phone, NOW()
            FROM members
            WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            header("Location: memberlist.php?archived=1");
            exit();
        } else {
            header("Location: memberlist.php?error=1");
            exit();
        }
    }
}

// Fetch members
$sql = "SELECT * FROM members ORDER BY name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Member List | Bangkero & Fishermen Association</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    body { font-family: 'Segoe UI', sans-serif; background: #fff; }
    .main-content { margin-left: 250px; padding: 32px; min-height: 100vh; }
    .table thead th { background: #ff7043 !important; color: #fff !important; font-weight: 600; border: none; }
    .table-bordered > :not(caption) > * > * { border-width: 2px; border-color: #80cbc4; }
    .table-hover tbody tr:hover { background: #ffe0b2; }
    .btn-primary { background-color: #ff7043; border-color: #ff7043; }
    .btn-primary:hover { background-color: #00897b; border-color: #00897b; }
    .btn-warning { background-color: #4fc3f7; border-color: #4fc3f7; color: #01579b; }
    .btn-warning:hover { background-color: #0288d1; border-color: #0288d1; color: #fff; }
    .btn-danger { background-color: #d32f2f; border-color: #d32f2f; }
    .btn-danger:hover { background-color: #b71c1c; border-color: #b71c1c; }
    .btn-info { background-color: #26c6da; border-color: #26c6da; color: #fff; }
    .btn-info:hover { background-color: #00838f; border-color: #00838f; }
    .action-buttons .btn { margin-right: 5px; }
    @media (max-width: 991.98px) { .main-content { margin-left: 0; padding: 16px; } }
</style>
</head>
<body>
<?php include('../navbar.php'); ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="add_member.php" class="btn btn-primary">+ Add New Member</a>
        <div class="d-flex align-items-center">
            <a href="export_members_csv.php" class="btn btn-outline-success me-2" title="Export to Excel (CSV)" target="_blank">
                <i class="bi bi-file-earmark-excel"></i>
            </a>
            <a href="export_members_print.php" class="btn btn-outline-danger me-3" title="Print / Save as PDF" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i>
            </a>
            <input type="text" id="searchInput" class="form-control" placeholder="Search members...">
        </div>
    </div>

    <div class="table-responsive shadow-sm rounded-4">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>More Info</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="membersTableBody">
                <?php if ($result->num_rows > 0): $count = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="text-center"><?= $count++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td><?= htmlspecialchars($row['address']) ?></td>
                            <td class="text-center">
                                <a href="../view_member_info.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">View</a>
                            </td>
                            <td class="text-center action-buttons">
                                <a href="../edit_member.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="#" class="btn btn-danger btn-sm archive-btn" data-id="<?= $row['id'] ?>">Archive</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted">No members found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Archive buttons
document.addEventListener('DOMContentLoaded', function() {
    const archiveButtons = document.querySelectorAll('.archive-btn');

    archiveButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const memberId = this.getAttribute('data-id');

            Swal.fire({
                title: 'Archive Member?',
                text: "Are you sure you want to move this member to the archive?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, archive it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?archive=${memberId}`;
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Cancelled',
                        text: 'Action cancelled.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        });
    });
});

// Live search/filter
const searchInput = document.getElementById('searchInput');
const tableBody = document.getElementById('membersTableBody');
const rows = tableBody.getElementsByTagName('tr');

searchInput.addEventListener('input', function() {
    const filter = this.value.toLowerCase();

    Array.from(rows).forEach(row => {
        const cells = row.getElementsByTagName('td');
        const name = cells[1].textContent.toLowerCase();
        const phone = cells[2].textContent.toLowerCase();
        const address = cells[3].textContent.toLowerCase();

        if (name.includes(filter) || phone.includes(filter) || address.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Flash messages
<?php if (isset($_GET['archived'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Archived!',
    text: 'Member moved to archive.',
    timer: 2000,
    showConfirmButton: false
});
<?php endif; ?>
</script>
</body>
</html>
