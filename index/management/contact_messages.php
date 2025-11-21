<?php
session_start();
require_once('../../config/db_connect.php');

// Only allow admin/officers
$role = strtolower($_SESSION['role'] ?? 'guest');
if (!in_array($role, ['admin', 'officer'])) {
    header("Location: ../login.php");
    exit;
}

// Handle mark as read action
if (isset($_GET['action']) && $_GET['action'] == 'mark_read' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sqlUpdate = "UPDATE contact_messages SET status='read' WHERE id=$id";
    if ($conn->query($sqlUpdate)) {
        $_SESSION['swal'] = ['type' => 'success', 'message' => 'Message marked as read!'];
        header("Location: contact_messages.php");
        exit;
    }
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sqlDelete = "DELETE FROM contact_messages WHERE id=$id";
    if ($conn->query($sqlDelete)) {
        $_SESSION['swal'] = ['type' => 'success', 'message' => 'Message deleted successfully!'];
        header("Location: contact_messages.php");
        exit;
    } else {
        $_SESSION['swal'] = ['type' => 'error', 'message' => 'Error: ' . $conn->error];
        header("Location: contact_messages.php");
        exit;
    }
}

// Fetch all contact messages
$result = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Messages | Admin Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.main-content { margin-left: 260px; padding: 30px; min-height: 100vh; }
h2 { font-weight: 600; color: #2c3e50; margin-bottom: 1.5rem; }
.card { border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 20px; }
.card-header { background-color: #2c3e50; color: white; font-weight: 500; border-radius: 12px 12px 0 0; }
.table thead th { background-color: #2c3e50; color: white; border: none; }
.table-hover tbody tr:hover { background-color: #f0f0f0; }
.badge-read { background-color: #28a745; }
.badge-unread { background-color: #ffc107; color: #212529; }
.search-box { max-width: 300px; }
@media (max-width: 991.98px) { .main-content { margin-left: 0; padding: 20px; } }
</style>
</head>
<body>

<?php include("../navbar.php"); ?>

<div class="main-content">
    <h2>Contact Messages</h2>

    <!-- Search -->
    <div class="d-flex justify-content-between mb-3 align-items-center">
        <input type="text" id="searchInput" class="form-control search-box" placeholder="Search messages...">
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle text-center" id="messagesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                                <td>
                                    <?php if ($row['status'] == 'read'): ?>
                                        <span class="badge badge-read">Read</span>
                                    <?php else: ?>
                                        <span class="badge badge-unread">Unread</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $row['created_at'] ?></td>
                                <td>
                                    <?php if ($row['status'] == 'unread'): ?>
                                        <button class="btn btn-success btn-sm mark-read" data-id="<?= $row['id'] ?>">
                                            <i class="bi bi-envelope-open"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-danger btn-sm delete-message" data-id="<?= $row['id'] ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted">No messages found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Live Search
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#messagesTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});

// SweetAlert for Delete
document.querySelectorAll('.delete-message').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This message will be deleted permanently!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "?action=delete&id=" + id;
            }
        });
    });
});

// SweetAlert for Mark as Read
document.querySelectorAll('.mark-read').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        Swal.fire({
            title: 'Mark as Read?',
            text: "This message will be marked as read.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, mark it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "?action=mark_read&id=" + id;
            }
        });
    });
});

// Show server messages (after action)
<?php if(isset($_SESSION['swal'])): ?>
Swal.fire({
    icon: '<?= $_SESSION['swal']['type'] ?>',
    title: '<?= $_SESSION['swal']['message'] ?>',
    showConfirmButton: false,
    timer: 2000
});
<?php unset($_SESSION['swal']); endif; ?>
</script>

</body>
</html>
