<?php
session_start();
require_once('../../config/db_connect.php'); 
include('../navbar.php');

// Search & filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

// Build query
$where = [];
if ($search) {
    $where[] = "(username LIKE '%$search%' OR action LIKE '%$search%' OR description LIKE '%$search%')";
}
if ($filter_date) {
    $where[] = "DATE(created_at) = '$filter_date'";
}

$whereSQL = '';
if (count($where) > 0) {
    $whereSQL = 'WHERE ' . implode(' AND ', $where);
}

// Fetch logs
$query = "SELECT * FROM activity_logs $whereSQL ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Activity Logs | Bangkero & Fishermen Association</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { margin:0; padding:0; background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
    .content { margin-left: 250px; padding: 30px; }
    .card { border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    th { background: #0d6efd; color: white; }
    .badge-success { background-color: #28a745; }
    .badge-danger { background-color: #dc3545; }
    .badge-warning { background-color: #ffc107; color: #000; }
    .badge-secondary { background-color: #6c757d; }
  </style>
</head>
<body>
  <div class="content">
    <h2 class="mb-4">📜 Activity Logs</h2>

    <!-- Search & single date filter -->
    <div class="card mb-3 p-3">
      <form class="row g-3" method="GET" action="">
        <div class="col-md-6">
          <input type="text" name="search" class="form-control" placeholder="Search username, action or description..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-4">
          <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
        </div>
      </form>
    </div>

    <!-- Logs Table -->
    <div class="card">
      <div class="card-body">
        <table class="table table-striped table-hover mb-0">
          <thead>
            <tr>
              <th>#</th>
              <th>User</th>
              <th>Action</th>
              <th>Description</th>
              <th>Date & Time</th>
            </tr>
          </thead>
          <tbody>
          <?php
          if ($result && $result->num_rows > 0) {
              $i = 1;
              while ($row = $result->fetch_assoc()) {
                  $badge = 'badge-secondary';
                  if (strpos($row['action'], 'Logged in') !== false) $badge = 'badge-success';
                  if (strpos($row['action'], 'Failed login') !== false) $badge = 'badge-danger';
                  if (strpos($row['action'], 'restore') !== false || strpos($row['action'], 'Retrieved') !== false) $badge = 'badge-warning';

                  $username = $row['username'] ?? 'User ID: '.$row['user_id'];
                  $description = $row['description'] ?? '-';

                  echo "<tr>
                          <td>{$i}</td>
                          <td>{$username}</td>
                          <td><span class='badge $badge' data-bs-toggle='tooltip' title='{$row['action']}'>{$row['action']}</span></td>
                          <td>{$description}</td>
                          <td>{$row['created_at']}</td>
                        </tr>";
                  $i++;
              }
          } else {
              echo "<tr><td colspan='5' class='text-center text-muted'>No activity logs found.</td></tr>";
          }
          ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
  </script>
</body>
</html>
