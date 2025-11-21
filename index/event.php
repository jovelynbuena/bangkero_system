<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

include('../config/db_connect.php'); // your DB connection

$flash = ['type' => '', 'message' => ''];

// Handle Add/Edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = intval($_POST['event_id'] ?? 0);
    $event_name = trim($_POST['event_name'] ?? '');
    $category = trim($_POST['event_category'] ?? 'General');
    $date = $_POST['event_date'] ?? '';
    $time = $_POST['event_time'] ?? '';
    $location = trim($_POST['event_location'] ?? '');
    $description = trim($_POST['event_description'] ?? '');
    $uploadedPoster = '';

    // Upload Poster
    if (isset($_FILES['event_poster']) && $_FILES['event_poster']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['event_poster'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (!in_array(strtolower($ext), $allowed)) {
                $flash = ['type'=>'error','message'=>'Invalid poster file type.'];
            } else {
                $targetDir = __DIR__ . '/../uploads/';
                if (!is_dir($targetDir)) mkdir($targetDir,0777,true);
                $uploadedPoster = time().'_'.preg_replace('/[^A-Za-z0-9_\-\.]/','_',$file['name']);
                if (!move_uploaded_file($file['tmp_name'],$targetDir.$uploadedPoster)) {
                    $flash = ['type'=>'error','message'=>'Failed to upload poster.'];
                }
            }
        }
    }

    // Validate required fields
    if (!$event_name || !$date || !$time || !$location || !$description) {
        if ($flash['type'] !== 'error') $flash = ['type'=>'error','message'=>'Fill all required fields.'];
    } else {
        if ($event_id > 0) {
            // Update
            if ($uploadedPoster) {
                $stmt = $conn->prepare("UPDATE events SET event_name=?, category=?, date=?, time=?, location=?, description=?, event_poster=? WHERE id=?");
                $stmt->bind_param("sssssssi",$event_name,$category,$date,$time,$location,$description,$uploadedPoster,$event_id);
            } else {
                $stmt = $conn->prepare("UPDATE events SET event_name=?, category=?, date=?, time=?, location=?, description=? WHERE id=?");
                $stmt->bind_param("ssssssi",$event_name,$category,$date,$time,$location,$description,$event_id);
            }
            if ($stmt->execute()) $flash = ['type'=>'success','message'=>'Event updated successfully.'];
            else $flash = ['type'=>'error','message'=>'Update failed: '.$conn->error];
            $stmt->close();
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO events (event_poster,event_name,description,date,time,location,category,is_archived) VALUES (?,?,?,?,?,?,?,0)");
            $posterValue = $uploadedPoster ?: '';
            $stmt->bind_param("sssssss",$posterValue,$event_name,$description,$date,$time,$location,$category);
            if ($stmt->execute()) $flash = ['type'=>'success','message'=>'Event added successfully.'];
            else $flash = ['type'=>'error','message'=>'Insert failed: '.$conn->error];
            $stmt->close();
        }
    }
}

// Handle Archive
if (isset($_GET['archive'])) {
    $id = intval($_GET['archive']);
    $stmt = $conn->prepare("UPDATE events SET is_archived=1 WHERE id=?");
    $stmt->bind_param("i",$id);
    if ($stmt->execute()) $flash = ['type'=>'success','message'=>'Event archived.'];
    else $flash = ['type'=>'error','message'=>'Archive failed: '.$conn->error];
    $stmt->close();
}

// Fetch active events
$res = $conn->query("SELECT * FROM events WHERE is_archived=0 ORDER BY date DESC");
if ($res===false) die("DB query failed: ".$conn->error);

// Get categories
$catRes = $conn->query("SELECT DISTINCT IFNULL(category,'General') AS category FROM events");
$categories = [];
while($c=$catRes->fetch_assoc()) $categories[]=$c['category'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Events | Bangkero & Fishermen Association</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Bootstrap & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<style>
body { font-family:'Segoe UI',sans-serif; background:#fff; }
.main-content { margin-left:250px; padding:20px; min-height:100vh; }
.event-badge { font-size:.8rem; padding:4px 10px; border-radius:12px; font-weight:600; }
.btn-primary { background:#ff7043; border:none; border-radius:10px; }
.btn-primary:hover { background:#ff5722; }
.modal-content { border-radius:12px; }
.form-control,.form-select { border-radius:8px; }
td img { border-radius:6px; object-fit:cover; }
.dt-buttons .dt-button { border-radius:8px; margin-right:5px; background:#ff7043; color:#fff; border:none; }
.dt-buttons .dt-button:hover { background:#ff5722; }
</style>
</head>
<body>
<?php include('navbar.php'); ?>
<div class="main-content">
<div class="d-flex justify-content-between align-items-center mb-3">
<h3>Events</h3>
<div>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEditModal" id="openAdd"><i class="bi bi-plus-circle me-1"></i> Add Event</button>
</div>
</div>

<div class="row mb-2">
<div class="col-md-8">
<input id="tableSearch" type="search" class="form-control" placeholder="Search events...">
</div>
<div class="col-md-4">
<select id="categoryFilter" class="form-select">
<option value="">-- All Categories --</option>
<?php foreach($categories as $cat): ?>
<option value="<?=htmlspecialchars($cat)?>"><?=htmlspecialchars($cat)?></option>
<?php endforeach; ?>
</select>
</div>
</div>

<div class="table-responsive">
<table id="eventsTable" class="display table table-bordered table-hover" style="width:100%">
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
<?php if($res->num_rows>0): $count=1; while($row=$res->fetch_assoc()): ?>
<tr>
<td class="text-center"><?=$count++?></td>
<td class="text-center"><img src="../uploads/<?=htmlspecialchars($row['event_poster']?:'default.jpg')?>" width="60" height="60"></td>
<td><?=htmlspecialchars($row['event_name'])?></td>
<td class="text-center">
<span class="badge bg-success event-badge"><?=htmlspecialchars($row['category']?:'General')?></span>
</td>
<td><?=htmlspecialchars($row['date'])?></td>
<td><?=htmlspecialchars($row['time'])?></td>
<td><?=htmlspecialchars($row['location'])?></td>
<td style="max-width:250px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?=htmlspecialchars($row['description'])?>"><?=htmlspecialchars($row['description'])?></td>
<td class="text-center">
<button class="btn btn-info btn-sm me-1 view-btn" data-bs-toggle="tooltip" title="View Event"
data-name="<?=htmlspecialchars($row['event_name'])?>" data-category="<?=htmlspecialchars($row['category'])?>"
data-date="<?=htmlspecialchars($row['date'])?>" data-time="<?=htmlspecialchars($row['time'])?>"
data-location="<?=htmlspecialchars($row['location'])?>" data-description="<?=htmlspecialchars($row['description'])?>"
data-poster="<?=htmlspecialchars($row['event_poster'])?>"><i class="bi bi-eye"></i></button>

<button class="btn btn-warning btn-sm me-1 edit-btn" data-bs-toggle="tooltip" title="Edit Event"
data-id="<?=$row['id']?>" data-name="<?=htmlspecialchars($row['event_name'])?>"
data-category="<?=htmlspecialchars($row['category'])?>" data-date="<?=htmlspecialchars($row['date'])?>"
data-time="<?=htmlspecialchars($row['time'])?>" data-location="<?=htmlspecialchars($row['location'])?>"
data-description="<?=htmlspecialchars($row['description'])?>" data-poster="<?=htmlspecialchars($row['event_poster'])?>"><i class="bi bi-pencil-square"></i></button>

<button class="btn btn-danger btn-sm archive-btn" data-bs-toggle="tooltip" title="Archive Event" data-id="<?=$row['id']?>"><i class="bi bi-archive"></i></button>
</td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="9" class="text-center text-muted">No events found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="addEditModal" tabindex="-1">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content p-3">
<div class="modal-header border-0">
<h5 class="modal-title fw-bold text-dark" id="modalTitle">Add Event</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<form id="eventForm" method="POST" enctype="multipart/form-data">
<input type="hidden" name="event_id" id="event_id">
<div class="modal-body">
<div class="row g-3">
<div class="col-md-6">
<label class="form-label">Event Name</label>
<input type="text" name="event_name" id="event_name" class="form-control" required>
</div>
<div class="col-md-6">
<label class="form-label">Category</label>
<select name="event_category" id="event_category" class="form-select" required>
<option value="">-- Select --</option>
<option>Training</option><option>Cleanup</option><option>Festival</option><option>Livelihood</option><option>General</option>
</select>
</div>
<div class="col-md-6">
<label class="form-label">Date</label>
<input type="date" name="event_date" id="event_date" class="form-control" required>
</div>
<div class="col-md-6">
<label class="form-label">Time</label>
<input type="time" name="event_time" id="event_time" class="form-control" required>
</div>
<div class="col-md-12">
<label class="form-label">Location</label>
<input type="text" name="event_location" id="event_location" class="form-control" required>
</div>
<div class="col-md-12">
<label class="form-label">Description</label>
<textarea name="event_description" id="event_description" rows="3" class="form-control" required></textarea>
</div>
<div class="col-md-12">
<label class="form-label">Poster (image)</label>
<input type="file" name="event_poster" id="event_poster" accept="image/*" class="form-control">
<img id="poster_preview" class="mt-2 rounded" style="width:150px;display:none;">
</div>
</div>
</div>
<div class="modal-footer border-0">
<button type="submit" class="btn btn-primary w-100" id="modalSubmit">Save</button>
</div>
</form>
</div>
</div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content p-3">
<div class="modal-header">
<h5 class="modal-title" id="viewTitle">Event Details</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<div class="row g-3">
<div class="col-md-5 text-center">
<img id="viewPoster" src="" class="img-fluid rounded" style="max-height:320px;">
</div>
<div class="col-md-7">
<h4 id="viewName"></h4>
<p><strong>Category:</strong> <span id="viewCategory"></span></p>
<p><strong>Date:</strong> <span id="viewDate"></span> <strong>Time:</strong> <span id="viewTime"></span></p>
<p><strong>Location:</strong> <span id="viewLocation"></span></p>
<p id="viewDescription"></p>
</div>
</div>
</div>
<div class="modal-footer border-0">
<button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
</div>
</div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom Style -->
<style>
  .dt-buttons .btn {
    border-radius: 10px !important;
    padding: 6px 10px !important;
    border: 1.5px solid transparent;
    background-color: white !important;
    box-shadow: none !important;
  }

  .btn-outline-success {
    border-color: #198754 !important;
    color: #198754 !important;
  }

  .btn-outline-success:hover {
    background-color: #198754 !important;
    color: white !important;
  }

  .btn-outline-danger {
    border-color: #dc3545 !important;
    color: #dc3545 !important;
  }

  .btn-outline-danger:hover {
    background-color: #dc3545 !important;
    color: white !important;
  }

  .btn-outline-secondary {
    border-color: #6c757d !important;
    color: #6c757d !important;
  }

  .btn-outline-secondary:hover {
    background-color: #6c757d !important;
    color: white !important;
  }

  .dt-buttons i {
    font-size: 1.2rem;
    vertical-align: middle;
  }
</style>

<script>
$(document).ready(function() {
  // Show flash messages
  const flash = <?php echo json_encode($flash); ?>;
  if (flash && flash.type) {
    if (flash.type === 'success') {
      Swal.fire({ icon: 'success', title: 'Success', text: flash.message, timer: 1600, showConfirmButton: false });
    } else if (flash.type === 'error') {
      Swal.fire({ icon: 'error', title: 'Error', text: flash.message });
    }
  }

  // Initialize DataTable (only once)
  let table = $('#eventsTable').DataTable({
    responsive: true,
    dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
    buttons: [
      {
        extend: 'csvHtml5',
        text: '<i class="bi bi-filetype-csv"></i>',
        className: 'btn btn-sm btn-outline-success me-2',
        titleAttr: 'Export to CSV'
      },
      {
        extend: 'excelHtml5',
        text: '<i class="bi bi-file-earmark-excel"></i>',
        className: 'btn btn-sm btn-outline-success me-2',
        titleAttr: 'Export to Excel'
      },
      {
        extend: 'pdfHtml5',
        text: '<i class="bi bi-file-earmark-pdf"></i>',
        className: 'btn btn-sm btn-outline-danger me-2',
        titleAttr: 'Export to PDF'
      },
      {
        extend: 'print',
        text: '<i class="bi bi-printer"></i>',
        className: 'btn btn-sm btn-outline-secondary',
        titleAttr: 'Print Table'
      }
    ],
    language: {
      search: "",
      searchPlaceholder: "Search events..."
    },
    columnDefs: [
      { orderable: false, targets: [1, 8] } // Example: disable sort for poster & actions
    ],
    pageLength: 10
  });

  // Custom search box
  $('#tableSearch').on('input', function() {
    table.search(this.value).draw();
  });

  // Category filter
  $('#categoryFilter').on('change', function() {
    const val = this.value;
    if (val) {
      table.column(3).search('^' + val + '$', true, false).draw();
    } else {
      table.column(3).search('').draw();
    }
  });
 });
  // Add/Edit modal handlers
  document.getElementById('openAdd').addEventListener('click', () => {
    document.getElementById('modalTitle').textContent = 'Add Event';
    document.getElementById('modalSubmit').textContent = 'Add Event';
    document.getElementById('eventForm').reset();
    document.getElementById('event_id').value = '';
    document.getElementById('poster_preview').style.display = 'none';
  });

  document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('modalTitle').textContent = 'Edit Event';
      document.getElementById('modalSubmit').textContent = 'Save Changes';
      document.getElementById('event_id').value = btn.dataset.id;
      document.getElementById('event_name').value = btn.dataset.name;
      document.getElementById('event_category').value = btn.dataset.category || 'General';
      document.getElementById('event_date').value = btn.dataset.date;
      document.getElementById('event_time').value = btn.dataset.time;
      document.getElementById('event_location').value = btn.dataset.location;
      document.getElementById('event_description').value = btn.dataset.description;
      const poster = btn.dataset.poster;
      const img = document.getElementById('poster_preview');
      if (poster) {
        img.src = '../uploads/' + poster;
        img.style.display = 'block';
      } else img.style.display = 'none';
      new bootstrap.Modal(document.getElementById('addEditModal')).show();
    });
  });

  // View modal
  document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('viewTitle').textContent = btn.dataset.name;
      document.getElementById('viewName').textContent = btn.dataset.name;
      document.getElementById('viewCategory').textContent = btn.dataset.category || 'General';
      document.getElementById('viewDate').textContent = btn.dataset.date;
      document.getElementById('viewTime').textContent = btn.dataset.time;
      document.getElementById('viewLocation').textContent = btn.dataset.location;
      document.getElementById('viewDescription').textContent = btn.dataset.description;
      const poster = btn.dataset.poster;
      document.getElementById('viewPoster').src = poster ? '../uploads/' + poster : '../uploads/default.jpg';
      new bootstrap.Modal(document.getElementById('viewModal')).show();
    });
  });

  // Archive with SweetAlert confirm
  document.querySelectorAll('.archive-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      Swal.fire({
        title: 'Archive this event?',
        text: 'This will move the event to the archived list.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff7043',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, archive it!'
      }).then(result => {
        if (result.isConfirmed) {
          window.location.href = window.location.pathname + '?archive=' + id;
        }
      });
    });
  });

  // Poster preview
  document.getElementById('event_poster').addEventListener('change', function() {
    const file = this.files[0];
    const img = document.getElementById('poster_preview');
    if (file) {
      img.style.display = 'block';
      img.src = URL.createObjectURL(file);
    } else img.style.display = 'none';
  });
</script>

</body>
</html>
