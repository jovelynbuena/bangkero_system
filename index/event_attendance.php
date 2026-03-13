<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../config/db_connect.php');

$eventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
if ($eventId <= 0) {
    die('Invalid event id');
}

// Fetch event details
$eventStmt = $conn->prepare("SELECT id, event_name, date, time, location, description FROM events WHERE id = ? AND is_archived = 0");
$eventStmt->bind_param('i', $eventId);
$eventStmt->execute();
$event = $eventStmt->get_result()->fetch_assoc();
$eventStmt->close();

if (!$event) {
    die('Event not found.');
}

$alert = ['type' => '', 'message' => ''];

// Handle POST (save attendance)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendanceDate = $_POST['attendance_date'] ?? $event['date'];
    $presentIds = isset($_POST['present']) && is_array($_POST['present']) ? array_map('intval', $_POST['present']) : [];
    $timeIns    = $_POST['time_in'] ?? [];
    $timeOuts   = $_POST['time_out'] ?? [];
    $remarks    = $_POST['remarks'] ?? [];

    // For simplicity: delete existing attendance for this event & date, then re-insert
    $delStmt = $conn->prepare("DELETE FROM member_attendance WHERE event_id = ? AND attendance_date = ?");
    $delStmt->bind_param('is', $eventId, $attendanceDate);
    $delStmt->execute();
    $delStmt->close();

    if (!empty($presentIds)) {
        $insStmt = $conn->prepare("INSERT INTO member_attendance (member_id, event_id, attendance_date, time_in, time_out, status, remarks, encoded_by) VALUES (?,?,?,?, ?, 'present', ?, ?)");
        $encodedBy = $_SESSION['user_id'] ?? 0;

        foreach ($presentIds as $memberId) {
            $timeInVal  = trim($timeIns[$memberId] ?? '') !== '' ? $timeIns[$memberId] : null;
            $timeOutVal = trim($timeOuts[$memberId] ?? '') !== '' ? $timeOuts[$memberId] : null;
            $remarkVal  = trim($remarks[$memberId] ?? '');
            $insStmt->bind_param('iissssi', $memberId, $eventId, $attendanceDate, $timeInVal, $timeOutVal, $remarkVal, $encodedBy);
            $insStmt->execute();
        }
        $insStmt->close();
    }

    $alert = ['type' => 'success', 'message' => 'Attendance saved successfully.'];
    $attendanceDateDefault = $attendanceDate;
} else {
    $attendanceDateDefault = $event['date'];
}

// Fetch members
$membersRes = $conn->query("SELECT id, name FROM members ORDER BY name ASC");
$members = [];
while ($row = $membersRes->fetch_assoc()) {
    $members[] = $row;
}

// Fetch existing attendance for this event & date (default date)
$attStmt = $conn->prepare("SELECT member_id, time_in, time_out, remarks FROM member_attendance WHERE event_id = ? AND attendance_date = ?");
$attStmt->bind_param('is', $eventId, $attendanceDateDefault);
$attStmt->execute();
$attRes = $attStmt->get_result();
$existing = [];
while ($row = $attRes->fetch_assoc()) {
    $existing[(int)$row['member_id']] = $row;
}
$attStmt->close();

$totalMembers = count($members);
$initialPresent = count($existing);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Event Attendance | Bangkero & Fishermen Association</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body { font-family: 'Inter', 'Segoe UI', sans-serif; background: #f9fafb; }
  .main-content { margin-left: 270px; padding: 32px; min-height: 100vh; }
  .page-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 24px 28px; border-radius: 18px; color: #fff; margin-bottom: 24px; }
  .page-header h3 { margin: 0; display: flex; align-items: center; gap: 10px; }
  .card-attendance { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); padding: 24px; }
  .table thead th { background: #f3f4f6; font-size: 0.85rem; text-transform: uppercase; letter-spacing: .03em; }
  .status-badge { font-size: 0.75rem; }
  .present-counter { font-size: 0.9rem; font-weight: 600; }
  @media (max-width: 991.98px) {
    .main-content { margin-left: 0; padding: 20px; }
  }
  @media print {
    body { background: #fff; }
    .navbar, .page-header, .btn, #memberSearch, #presentFilter, #extraFilter { display: none !important; }
    .main-content { margin: 0; padding: 0; }
    .card-attendance { box-shadow: none; border-radius: 0; }
    .print-header { display: block !important; }
  }
  .print-header { display: none; }
  .print-logo { width: 60px; height: 60px; object-fit: contain; margin-right: 15px; }
</style>
</head>
<body>
<?php include('navbar.php'); ?>
<div class="main-content">
  <!-- Print Header with Logo -->
  <div class="print-header" style="margin-bottom: 20px; border-bottom: 3px solid #0e7490; padding-bottom: 15px;">
    <div style="display: flex; align-items: center;">
      <?php
      $logoPath = __DIR__ . '/../images/logo1.png';
      if (file_exists($logoPath)) {
          echo '<img src="data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) . '" class="print-logo">';
      }
      ?>
      <div>
        <h2 style="margin: 0; color: #0e7490; font-size: 20px;">Bangkero & Fishermen Association</h2>
        <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">Event Attendance Report</p>
        <p style="margin: 3px 0 0 0; color: #999; font-size: 11px;">Generated: <?= date('Y-m-d h:i A') ?></p>
      </div>
    </div>
  </div>

  <div class="page-header">
    <h3><i class="bi bi-people-check"></i> Event Attendance</h3>
    <p class="mb-0 small">Record attendance for this association event.</p>
  </div>

  <?php if ($alert['type'] === 'success'): ?>
    <div class="alert alert-success"><?= htmlspecialchars($alert['message']) ?></div>
  <?php elseif ($alert['type'] === 'error'): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($alert['message']) ?></div>
  <?php endif; ?>

  <div class="card-attendance mb-3">
    <h5 class="mb-1"><?= htmlspecialchars($event['event_name']) ?></h5>
    <p class="text-muted mb-2">
      <i class="bi bi-calendar3"></i> <?= date('M d, Y', strtotime($event['date'])) ?>
      &nbsp;·&nbsp;
      <i class="bi bi-clock"></i> <?= date('h:i A', strtotime($event['time'])) ?>
      &nbsp;·&nbsp;
      <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($event['location']) ?>
    </p>
    <p class="small mb-0"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
  </div>

  <div class="card-attendance">
    <form method="POST">
      <div class="row mb-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Attendance Date</label>
          <input type="date" name="attendance_date" class="form-control" value="<?= htmlspecialchars($attendanceDateDefault) ?>" required>
        </div>
        <div class="col-md-8 mt-3 mt-md-0 d-flex justify-content-md-end align-items-center gap-2">
          <button type="button" class="btn btn-outline-secondary btn-sm d-print-none" onclick="window.print();">
            <i class="bi bi-printer"></i> Print Attendance
          </button>
          <span class="badge bg-primary-subtle text-primary present-counter">
            Present: <span id="presentCount"><?= $initialPresent ?></span> / <?= $totalMembers ?> members
          </span>
        </div>
      </div>

      <div class="row mb-3 filters-row">
        <div class="col-md-6 mt-2 mt-md-0">
          <label class="form-label">Search Members</label>
          <input type="text" id="memberSearch" class="form-control" placeholder="Search by name or remarks...">
        </div>
        <div class="col-md-3 mt-2 mt-md-0">
          <label class="form-label">Status Filter</label>
          <select id="presentFilter" class="form-select">
            <option value="">-- All --</option>
            <option value="present">Present only</option>
            <option value="absent">Not present</option>
          </select>
        </div>
        <div class="col-md-3 mt-2 mt-md-0">
          <label class="form-label">Remarks/Time Filter</label>
          <select id="extraFilter" class="form-select">
            <option value="">-- All --</option>
            <option value="with_remarks">With remarks</option>
            <option value="with_time">With time in/out</option>
          </select>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th style="width: 60px;">Present</th>
              <th>Member Name</th>
              <th style="width: 140px;">Time In</th>
              <th style="width: 140px;">Time Out</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!empty($members)): foreach ($members as $m): $mid = (int)$m['id']; $att = $existing[$mid] ?? null; ?>
            <tr>
              <td>
                <input type="checkbox" class="form-check-input present-checkbox" name="present[]" value="<?= $mid ?>" <?= $att ? 'checked' : '' ?>>
              </td>
              <td class="member-name"><?= htmlspecialchars($m['name']) ?></td>
              <td>
                <input type="time" name="time_in[<?= $mid ?>]" class="form-control form-control-sm time-in" value="<?= $att && $att['time_in'] ? htmlspecialchars(substr($att['time_in'],0,5)) : '' ?>">
              </td>
              <td>
                <input type="time" name="time_out[<?= $mid ?>]" class="form-control form-control-sm time-out" value="<?= $att && $att['time_out'] ? htmlspecialchars(substr($att['time_out'],0,5)) : '' ?>">
              </td>
              <td>
                <input type="text" name="remarks[<?= $mid ?>]" class="form-control form-control-sm remarks" value="<?= $att ? htmlspecialchars($att['remarks']) : '' ?>">
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No members found.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="mt-3 d-flex justify-content-between">
        <a href="event.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Events</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Attendance</button>
      </div>
    </form>
  </div>
</div>

<script>
(function() {
  function getNowTimeString() {
    const d = new Date();
    const hh = String(d.getHours()).padStart(2,'0');
    const mm = String(d.getMinutes()).padStart(2,'0');
    return hh + ':' + mm;
  }

  let rows = [];
  let searchInput = null;
  let filterSelect = null;
  let extraFilter = null;


  function applyFilters() {
    const term = (searchInput && searchInput.value ? searchInput.value : '').toLowerCase();
    const filterVal = filterSelect ? filterSelect.value : '';
    const extraVal = extraFilter ? extraFilter.value : '';
    let presentCount = 0;

    rows.forEach(row => {
      const cb = row.querySelector('.present-checkbox');
      const nameCell = row.querySelector('.member-name');
      const remarks = row.querySelector('.remarks');
      const timeIn = row.querySelector('.time-in');
      const timeOut = row.querySelector('.time-out');
      if (!cb || !nameCell) return;

      const text = (nameCell.textContent + ' ' + (remarks ? remarks.value : '')).toLowerCase();
      let visible = true;

      if (term && !text.includes(term)) visible = false;
      if (filterVal === 'present' && !cb.checked) visible = false;
      if (filterVal === 'absent' && cb.checked) visible = false;

      if (extraVal === 'with_remarks') {
        const hasRemarks = remarks && remarks.value.trim() !== '';
        if (!hasRemarks) visible = false;
      }
      if (extraVal === 'with_time') {
        const hasTime = (timeIn && timeIn.value) || (timeOut && timeOut.value);
        if (!hasTime) visible = false;
      }

      row.style.display = visible ? '' : 'none';
      if (cb.checked) presentCount++;
    });


    const label = document.getElementById('presentCount');
    if (label) label.textContent = presentCount;
  }

  document.addEventListener('DOMContentLoaded', function() {
    rows = Array.from(document.querySelectorAll('table tbody tr'));
    searchInput = document.getElementById('memberSearch');
    filterSelect = document.getElementById('presentFilter');
    extraFilter = document.getElementById('extraFilter');

    if (searchInput) {
      searchInput.addEventListener('input', applyFilters);
    }
    if (filterSelect) {
      filterSelect.addEventListener('change', applyFilters);
    }
    if (extraFilter) {
      extraFilter.addEventListener('change', applyFilters);
    }


    rows.forEach(row => {
      const cb = row.querySelector('.present-checkbox');
      const timeIn = row.querySelector('.time-in');
      const timeOut = row.querySelector('.time-out');
      const remarks = row.querySelector('.remarks');

      if (!cb) return;

      // When Present is checked: auto-fill Time In if empty
      cb.addEventListener('change', function() {
        if (cb.checked) {
          if (timeIn && !timeIn.value) {
            timeIn.value = getNowTimeString();
          }
        } else {
          // If unchecked, clear times and remarks to keep data consistent
          if (timeIn) timeIn.value = '';
          if (timeOut) timeOut.value = '';
          if (remarks) remarks.value = '';
        }
        applyFilters();
      });

      // If user types a time or remark, auto-check Present
      [timeIn, timeOut, remarks].forEach(input => {
        if (!input) return;
        input.addEventListener('input', function() {
          if (input.value && !cb.checked) {
            cb.checked = true;
            if (input === timeIn && !timeIn.value) {
              timeIn.value = getNowTimeString();
            }
          }
          applyFilters();
        });
      });
    });

    applyFilters();
  });
})();
</script>

</body>
</html>
