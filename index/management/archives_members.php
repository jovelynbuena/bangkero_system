<?php
session_start();

if (empty($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

include('../../config/db_connect.php');

$adminName = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';

// Handle retrieve from archives back to members
if (isset($_GET['retrieve'])) {
    $mid = intval($_GET['retrieve']);
    if ($mid > 0) {
        // Get member info before restoring
        $stmt_get = $conn->prepare("SELECT name, email, phone FROM member_archive WHERE member_id = ?");
        $stmt_get->bind_param("i", $mid);
        $stmt_get->execute();
        $res_get = $stmt_get->get_result();
        $member = $res_get->fetch_assoc();
        $stmt_get->close();

        $stmt_insert = $conn->prepare("INSERT INTO members (name, email, phone) 
                                       SELECT name, email, phone 
                                       FROM member_archive WHERE member_id = ?");
        $stmt_insert->bind_param("i", $mid);

        if ($stmt_insert->execute()) {
            $stmt_insert->close();

            // Delete from archive
            $stmt_delete = $conn->prepare("DELETE FROM member_archive WHERE member_id = ?");
            $stmt_delete->bind_param("i", $mid);
            $stmt_delete->execute();
            $stmt_delete->close();

            // Log restore action
            $actionText = "Restored member: {$member['name']}";
            $stmt_log = $conn->prepare("INSERT INTO activity_logs (user_id, action, description) VALUES (?, ?, ?)");
            $stmt_log->bind_param("iss", $_SESSION['user_id'], $actionText, $actionText);
            $stmt_log->execute();
            $stmt_log->close();

            header('Location: archives.php?retrieved=1');
            exit();
        } else {
            $stmt_insert->close();
            header('Location: archives.php?error=1');
            exit();
        }
    }
}

// Optional search
$search = "";
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT member_id, name, email, phone, archived_at 
            FROM member_archive 
            WHERE name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%'
            ORDER BY archived_at DESC";
} else {
    $sql = "SELECT member_id, name, email, phone, archived_at 
            FROM member_archive 
            ORDER BY archived_at DESC";
}
$result = $conn->query($sql);
?>

<?php include('../navbar.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Archives | Bangkero & Fishermen Association</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- Main Content -->
<div class="main-content" style="margin-left:250px; margin-top:70px; padding:20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Archived Members</h3>
        <form class="d-flex" method="GET" action="archives.php">
            <input type="text" name="search" class="form-control me-2" placeholder="Search archives..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-outline-primary">Search</button>
        </form>
    </div>

    <div class="table-responsive shadow-sm rounded-4">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>Member ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Archived At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center"><?= (int)$row['member_id']; ?></td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td><?= htmlspecialchars($row['phone']); ?></td>
                        <td><?= htmlspecialchars($row['archived_at']); ?></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-success btn-sm me-1" onclick="confirmRetrieve(<?= (int)$row['member_id']; ?>)">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center text-muted">No archived members.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmRetrieve(id) {
    Swal.fire({
        title: 'Retrieve member?',
        text: "This will move the member back to the active list.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, retrieve'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'archives.php?retrieve=' + id;
        }
    });
}

<?php if (isset($_GET['retrieved'])): ?>
Swal.fire({ icon: 'success', title: 'Retrieved!', text: 'Member moved back to active list.', timer: 2000, showConfirmButton: false });
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred. Please try again.' });
<?php endif; ?>
</script>
</body>
</html>
