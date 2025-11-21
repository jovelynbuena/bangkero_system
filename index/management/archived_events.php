<?php
session_start();
if(empty($_SESSION['username'])){
    header('location: ../login.php');
    exit;
}
include('../../config/db_connect.php');

// Archive an event
if(isset($_GET['archive'])){
    $id = intval($_GET['archive']);
    if($id>0){
        $stmt = $conn->prepare("UPDATE events SET is_archived=1 WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $stmt->close();
        header("Location: archived_events.php?archived=1");
        exit();
    }
}

// Restore an event
if(isset($_GET['retrieve'])){
    $id = intval($_GET['retrieve']);
    if($id>0){
        $stmt = $conn->prepare("UPDATE events SET is_archived=0 WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $stmt->close();
        header("Location: archived_events.php?retrieved=1");
        exit();
    }
}

// Fetch archived events
$search = $_GET['search'] ?? '';
$search_sql = $conn->real_escape_string($search);
$sql = "SELECT * FROM events WHERE is_archived=1 AND 
       (event_name LIKE '%$search_sql%' OR category LIKE '%$search_sql%' OR location LIKE '%$search_sql%') 
       ORDER BY date DESC, time DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Archived Events | Bangkero & Fishermen Association</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body { font-family: 'Segoe UI', sans-serif; background: #fff; }
.main-content { margin-left:250px; padding:32px; min-height:100vh; }
.event-poster { width:60px;height:60px;object-fit:cover;border-radius:6px; }
</style>
</head>
<body>
<?php include('../navbar.php'); ?>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Archived Events</h2>
        <form class="d-flex" method="GET" action="archived_events.php">
            <input type="text" name="search" class="form-control me-2" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-outline-primary">Search</button>
        </form>
    </div>

    <div class="table-responsive shadow-sm rounded-4">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>#</th>
                    <th>Poster</th>
                    <th>Event Name</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if($result && $result->num_rows>0): $count=1; ?>
                <?php while($row=$result->fetch_assoc()): ?>
                <tr>
                    <td><?= $count++ ?></td>
                    <td><img src="../../uploads/<?= htmlspecialchars($row['event_poster'] ?: 'default.jpg') ?>" class="event-poster"></td>
                    <td><?= htmlspecialchars($row['event_name']) ?></td>
                    <td><?= htmlspecialchars($row['category'] ?: 'General') ?></td>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td><?= htmlspecialchars($row['time']) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= htmlspecialchars($row['description']) ?>"><?= htmlspecialchars($row['description']) ?></td>
                    <td class="text-center">
                        <button class="btn btn-success btn-sm" onclick="confirmRestore(<?= $row['id'] ?>)"><i class="bi bi-arrow-counterclockwise"></i> Restore</button>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="9" class="text-center text-muted">No archived events.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmRestore(id){
    Swal.fire({
        title:'Restore this event?',
        icon:'question',
        showCancelButton:true,
        confirmButtonColor:'#28a745',
        cancelButtonColor:'#6c757d',
        confirmButtonText:'Yes, restore!'
    }).then(result=>{
        if(result.isConfirmed){
            window.location.href='archived_events.php?retrieve='+id;
        }
    });
}

<?php if(isset($_GET['retrieved'])): ?>
Swal.fire({icon:'success', title:'Restored!', text:'Event moved back to active list.', timer:1800, showConfirmButton:false});
<?php endif; ?>
<?php if(isset($_GET['archived'])): ?>
Swal.fire({icon:'success', title:'Archived!', text:'Event moved to archive.', timer:1800, showConfirmButton:false});
<?php endif; ?>
</script>
</body>
</html>
