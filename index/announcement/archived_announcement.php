<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}
include('../../config/db_connect.php');

// Archive an announcement
if (isset($_GET['archive'])) {
    $id = intval($_GET['archive']);
    if ($id > 0) {
        $conn->begin_transaction();
        try {
            // Fetch the announcement
            $stmt = $conn->prepare("SELECT * FROM announcements WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $announcement = $result->fetch_assoc();
            $stmt->close();

            if ($announcement) {
                // Insert into archived_announcements
                $stmt = $conn->prepare("INSERT INTO archived_announcements (original_id, title, content, date_posted) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $announcement['id'], $announcement['title'], $announcement['content'], $announcement['date_posted']);
                $stmt->execute();
                $stmt->close();

                // Delete from announcements
                $stmt = $conn->prepare("DELETE FROM announcements WHERE id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                header("Location: admin_announcements.php?archived=1");
                exit();
            } else {
                $conn->rollback();
                die("Announcement not found.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            die("Error archiving announcement: " . $e->getMessage());
        }
    }
}

// Restore an announcement
if (isset($_GET['retrieve'])) {
    $id = intval($_GET['retrieve']);
    if ($id > 0) {
        $conn->begin_transaction();
        try {
            // Fetch from archived_announcements
            $stmt = $conn->prepare("SELECT * FROM archived_announcements WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $announcement = $result->fetch_assoc();
            $stmt->close();

            if ($announcement) {
                // Insert back into announcements
                $stmt = $conn->prepare("INSERT INTO announcements (title, content, date_posted) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $announcement['title'], $announcement['content'], $announcement['date_posted']);
                $stmt->execute();
                $stmt->close();

                // Delete from archived_announcements
                $stmt = $conn->prepare("DELETE FROM archived_announcements WHERE id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                header("Location: archived_announcement.php?retrieved=1");
                exit();
            } else {
                $conn->rollback();
                die("Archived announcement not found.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            die("Error restoring announcement: " . $e->getMessage());
        }
    }
}

// Fetch all archived announcements
$search = $_GET['search'] ?? '';
$search_sql = $conn->real_escape_string($search);
$sql = "SELECT * FROM archived_announcements 
        WHERE title LIKE '%$search_sql%' OR content LIKE '%$search_sql%'
        ORDER BY date_posted DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Archived Announcements | Bangkero & Fishermen Association</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body { font-family: 'Segoe UI', sans-serif; background: #fff; }
.main-content { margin-left:250px; padding:32px; min-height:100vh; }
.announcement-item { 
    border: 2px solid #bdbdbd; 
    border-radius: 14px; 
    background-color: #f5f5f5; 
    padding: 18px; 
    margin-bottom: 18px; 
    box-shadow: 0 2px 8px rgba(189,189,189,0.08); 
}
</style>
</head>
<body>
<?php include('../navbar.php'); ?>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Archived Announcements</h2>
        <form class="d-flex" method="GET" action="archived_announcement.php">
            <input type="text" name="search" class="form-control me-2" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-outline-primary">Search</button>
        </form>
    </div>

    <div class="table-responsive shadow-sm rounded-4">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Date Posted</th>
                    <th>Content</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if($result && $result->num_rows > 0): $count=1; ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $count++ ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['category'] ?: 'General') ?></td>
                    <td><?= htmlspecialchars(date("F j, Y", strtotime($row['date_posted']))) ?></td>
                    <td style="max-width:250px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= htmlspecialchars($row['content']) ?>"><?= htmlspecialchars($row['content']) ?></td>
                    <td class="text-center">
                        <button class="btn btn-success btn-sm" onclick="confirmRestore(<?= $row['id'] ?>)"><i class="bi bi-arrow-counterclockwise"></i> Restore</button>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="6" class="text-center text-muted">No archived announcements.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmRestore(id){
    Swal.fire({
        title:'Restore this announcement?',
        icon:'question',
        showCancelButton:true,
        confirmButtonColor:'#28a745',
        cancelButtonColor:'#6c757d',
        confirmButtonText:'Yes, restore!'
    }).then(result=>{
        if(result.isConfirmed){
            window.location.href='archived_announcement.php?retrieve='+id;
        }
    });
}

<?php if(isset($_GET['retrieved'])): ?>
Swal.fire({icon:'success', title:'Restored!', text:'Announcement moved back to active list.', timer:1800, showConfirmButton:false});
<?php endif; ?>
</script>
</body>
</html>
