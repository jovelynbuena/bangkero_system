<?php
session_start();
if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

require_once('../config/db_connect.php');

// Auto-create events table if it doesn't exist
$checkTable = $conn->query("SHOW TABLES LIKE 'events'");
if ($checkTable->num_rows === 0) {
    $conn->query("CREATE TABLE IF NOT EXISTS `events` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `event_name` varchar(255) NOT NULL,
        `date` date NOT NULL,
        `time` time DEFAULT NULL,
        `location` varchar(255) DEFAULT NULL,
        `description` text,
        `is_archived` tinyint(1) DEFAULT '0',
        `created_by` int(11) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_date` (`date`),
        KEY `idx_is_archived` (`is_archived`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// Auto-create attendance table if it doesn't exist
$checkAttendance = $conn->query("SHOW TABLES LIKE 'attendance'");
if ($checkAttendance->num_rows === 0) {
    $conn->query("CREATE TABLE IF NOT EXISTS `attendance` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `event_id` int(11) NOT NULL,
        `member_id` int(11) NOT NULL,
        `attendance_date` date NOT NULL,
        `status` enum('present','absent','excused') DEFAULT 'present',
        `time_in` time DEFAULT NULL,
        `notes` text,
        `recorded_by` int(11) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_attendance` (`event_id`,`member_id`,`attendance_date`),
        KEY `idx_member` (`member_id`),
        KEY `idx_event` (`event_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// Fetch list of active (non-archived) events for selector
$events = [];
$evRes = $conn->query("SELECT id, event_name, date, time, location FROM events WHERE is_archived = 0 ORDER BY date DESC, time DESC");
if ($evRes) {
    while ($row = $evRes->fetch_assoc()) {
        $events[] = $row;
    }
}

$selectedEventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$selectedDate    = isset($_GET['attendance_date']) ? trim($_GET['attendance_date']) : '';

if ($selectedEventId <= 0 && !empty($events)) {
    $selectedEventId = (int)$events[0]['id'];
}

$eventDetails = null;
$dateOptions  = [];

if ($selectedEventId > 0) {
    // Load event details
    $evtStmt = $conn->prepare("SELECT id, event_name, date, time, location, description, category FROM events WHERE id = ?");
    $evtStmt->bind_param('i', $selectedEventId);
    $evtStmt->execute();
    $eventDetails = $evtStmt->get_result()->fetch_assoc();
    $evtStmt->close();

    // Load available attendance dates for this event (distinct)
    $dateStmt = $conn->prepare("SELECT DISTINCT attendance_date FROM member_attendance WHERE event_id = ? ORDER BY attendance_date DESC");
    $dateStmt->bind_param('i', $selectedEventId);
    $dateStmt->execute();
    $dateRes = $dateStmt->get_result();
    while ($d = $dateRes->fetch_assoc()) {
        if (!empty($d['attendance_date'])) {
            $dateOptions[] = $d['attendance_date'];
        }
    }
    $dateStmt->close();

    // If there are no attendance records yet, still expose the event date as a selectable option
    if (empty($dateOptions) && !empty($eventDetails['date'])) {
        $dateOptions[] = $eventDetails['date'];
    }

    // Decide which attendance date to use for this event
    if (empty($selectedDate)) {
        // No date requested in URL: use first attendance date if available (includes event date fallback)
        if (!empty($dateOptions)) {
            $selectedDate = $dateOptions[0];
        }
    } elseif (!in_array($selectedDate, $dateOptions, true)) {
        // A date was requested (from previous event), but it doesn't belong to this event
        if (!empty($dateOptions)) {
            $selectedDate = $dateOptions[0];
        } else {
            $selectedDate = '';
        }
    }
}


// If user requested CSV export, handle it before any HTML output
if ($selectedEventId > 0 && !empty($selectedDate) && isset($_GET['export']) && $_GET['export'] === 'csv') {
    $filename = 'attendance_report_event_' . $selectedEventId . '_' . $selectedDate . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Member ID', 'Member Name', 'Status', 'Time In', 'Time Out', 'Remarks']);

    $csvStmt = $conn->prepare("SELECT m.id, m.name, ma.status, ma.time_in, ma.time_out, ma.remarks
                               FROM members m
                               LEFT JOIN member_attendance ma
                                 ON ma.member_id = m.id
                                AND ma.event_id = ?
                                AND ma.attendance_date = ?
                               ORDER BY m.name ASC");
    $csvStmt->bind_param('is', $selectedEventId, $selectedDate);
    $csvStmt->execute();
    $csvRes = $csvStmt->get_result();
    while ($row = $csvRes->fetch_assoc()) {
        $status  = $row['status'] === 'present' ? 'Present' : 'Absent';
        $timeIn  = $row['time_in'] ? date('H:i', strtotime($row['time_in'])) : '';
        $timeOut = $row['time_out'] ? date('H:i', strtotime($row['time_out'])) : '';
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $status,
            $timeIn,
            $timeOut,
            $row['remarks'] ?? ''
        ]);
    }
    $csvStmt->close();
    fclose($output);
    exit;
}

// Attendance data for on-screen report
$rows          = [];
$totalMembers  = 0;
$presentCount  = 0;
$absentCount   = 0;
$attendancePct = 0;

if ($selectedEventId > 0 && !empty($selectedDate)) {
    $attStmt = $conn->prepare("SELECT m.id, m.name, ma.status, ma.time_in, ma.time_out, ma.remarks
                               FROM members m
                               LEFT JOIN member_attendance ma
                                 ON ma.member_id = m.id
                                AND ma.event_id = ?
                                AND ma.attendance_date = ?
                               ORDER BY m.name ASC");
    $attStmt->bind_param('is', $selectedEventId, $selectedDate);
    $attStmt->execute();
    $attRes = $attStmt->get_result();
    while ($r = $attRes->fetch_assoc()) {
        $rows[] = $r;
        if ($r['status'] === 'present') {
            $presentCount++;
        } else {
            $absentCount++;
        }
    }
    $attStmt->close();

    $totalMembers = count($rows);
    if ($totalMembers > 0) {
        $attendancePct = round(($presentCount / $totalMembers) * 100, 1);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Attendance Reports | Bangkero & Fishermen Association</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/admin-theme.css">
<style>
    body { font-family: 'Inter', 'Segoe UI', sans-serif; background: #f9fafb; }
    .main-content { margin-left: 270px; padding: 32px; min-height: 100vh; }
    .page-header { background: linear-gradient(135deg, #1B4F72 0%, #2E86AB 100%); padding: 24px 28px; border-radius: 18px; color: #fff; margin-bottom: 24px; box-shadow: 0 10px 30px rgba(14, 116, 144, 0.25); }
    .page-header h3 { margin: 0; display: flex; align-items: center; gap: 10px; }
    .page-header p { margin: 0; opacity: 0.9; font-size: 0.9rem; }

    .card-report { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); padding: 24px; }
    .filter-row { border-bottom: 1px solid #e5e7eb; padding-bottom: 16px; margin-bottom: 16px; }

    .summary-cards { margin-top: 12px; }
    .summary-card { background: #f9fafb; border-radius: 14px; padding: 14px 16px; border: 1px solid #e5e7eb; }
    .summary-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; }
    .summary-value { font-size: 1.2rem; font-weight: 700; color: #111827; }

    .table thead th { background: #f3f4f6; font-size: 0.8rem; text-transform: uppercase; letter-spacing: .03em; border-bottom: none; }
    .status-pill { border-radius: 999px; padding: 4px 10px; font-size: 0.75rem; font-weight: 600; }
    .status-present { background: rgba(22, 163, 74, 0.08); color: #15803d; border: 1px solid rgba(22, 163, 74, 0.4); }
    .status-absent { background: rgba(148, 163, 184, 0.15); color: #475569; border: 1px solid rgba(148, 163, 184, 0.5); }

    .small-muted { font-size: 0.8rem; color: #6b7280; }

    @media (max-width: 991.98px) {
      .main-content { margin-left: 0; padding: 20px; }
    }

    @media print {
      body { background: #fff; }
      .navbar, .page-header, .btn, .d-print-none { display: none !important; }
      .main-content { margin: 0; padding: 0; }
      .card-report { box-shadow: none; border-radius: 0; border: none; }
      .table { font-size: 0.85rem; }
      .print-header { display: block !important; }
    }
    .print-header { display: none; margin-bottom: 20px; border-bottom: 3px solid #2E86AB; padding-bottom: 15px; }
    .print-logo { width: 60px; height: 60px; object-fit: contain; margin-right: 15px; }
  </style>
</head>
<body>
<?php include('navbar.php'); ?>
<div class="main-content">
  <!-- Print Header with Logo -->
  <div class="print-header">
    <div style="display: flex; align-items: center;">
      <?php
      require_once __DIR__ . '/../config/logo_helper.php';
      if ($assocLogoB64) {
          $ext = pathinfo($assocLogoPath, PATHINFO_EXTENSION) ?: 'png';
          echo '<img src="data:image/' . $ext . ';base64,' . $assocLogoB64 . '" class="print-logo">';
      }
      ?>
      <div>
        <h2 style="margin: 0; color: #2E86AB; font-size: 20px;">Bangkero & Fishermen Association</h2>
        <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">Attendance Report</p>
        <p style="margin: 3px 0 0 0; color: #999; font-size: 11px;">Generated: <?= date('Y-m-d h:i A') ?></p>
      </div>
    </div>
  </div>

  <div class="page-header d-print-none">
    <h3><i class="bi bi-clipboard-data"></i> Attendance Reports</h3>
    <p class="mb-0">Generate printable attendance summaries per event and date.</p>
  </div>

  <div class="card-report">
    <form method="GET" class="filter-row row g-3 align-items-end d-print-none">
      <div class="col-md-5">
        <label class="form-label">Event</label>
        <input type="text" id="eventSearch" class="form-control mb-2" placeholder="Search event by name or date...">
        <select name="event_id" id="eventSelect" class="form-select" required>
          <option value="">-- Select Event --</option>
          <?php foreach ($events as $ev): ?>
            <option value="<?= (int)$ev['id'] ?>" <?= $selectedEventId === (int)$ev['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($ev['event_name']) ?> (<?= date('M d, Y', strtotime($ev['date'])) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Attendance Date</label>
        <select name="attendance_date" class="form-select" required>
          <?php foreach ($dateOptions as $d): ?>
            <option value="<?= htmlspecialchars($d) ?>" <?= $d === $selectedDate ? 'selected' : '' ?>>
              <?= date('M d, Y', strtotime($d)) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 text-md-end d-flex gap-2 justify-content-md-end mt-3 mt-md-0">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-funnel me-1"></i> Load Report
        </button>
        <?php if ($selectedEventId > 0 && !empty($selectedDate)): ?>
        <a href="attendance_reports.php?event_id=<?= (int)$selectedEventId ?>&attendance_date=<?= urlencode($selectedDate) ?>&export=csv" class="btn btn-outline-success">
          <i class="bi bi-filetype-csv me-1"></i> Export CSV
        </a>
        <a href="export_attendance_pdf.php?event_id=<?= (int)$selectedEventId ?>&attendance_date=<?= urlencode($selectedDate) ?>" class="btn btn-outline-danger">
          <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
        </a>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print();">
          <i class="bi bi-printer me-1"></i> Print
        </button>
        <?php endif; ?>
      </div>
    </form>

    <?php if ($eventDetails && $selectedEventId > 0 && !empty($selectedDate)): ?>
      <div class="row align-items-center mb-3">
        <div class="col-md-7">
          <h5 class="mb-1"><?= htmlspecialchars($eventDetails['event_name']) ?></h5>
          <div class="small-muted">
            <i class="bi bi-calendar3"></i>
            <?= date('F j, Y', strtotime($eventDetails['date'])) ?>
            &nbsp;&middot;&nbsp;
            <i class="bi bi-clock"></i>
            <?= date('h:i A', strtotime($eventDetails['time'])) ?>
            &nbsp;&middot;&nbsp;
            <i class="bi bi-geo-alt"></i>
            <?= htmlspecialchars($eventDetails['location']) ?>
          </div>
          <div class="small-muted mt-1">
            Report for attendance date: <strong><?= date('F j, Y', strtotime($selectedDate)) ?></strong>
            <?php if (!empty($eventDetails['category'])): ?>
              &nbsp;&middot;&nbsp; Category: <span class="badge bg-light text-dark"><?= htmlspecialchars($eventDetails['category']) ?></span>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-md-5">
          <div class="row g-2 summary-cards">
            <div class="col-4">
              <div class="summary-card text-center">
                <div class="summary-label">Present</div>
                <div class="summary-value" id="summaryPresent"><?= (int)$presentCount ?></div>
              </div>
            </div>
            <div class="col-4">
              <div class="summary-card text-center">
                <div class="summary-label">Absent</div>
                <div class="summary-value" id="summaryAbsent"><?= (int)$absentCount ?></div>
              </div>
            </div>
            <div class="col-4">
              <div class="summary-card text-center">
                <div class="summary-label">Attendance</div>
                <div class="summary-value" id="summaryAttendance"><?= $attendancePct ?>%</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Table-level filters -->
      <?php if (!empty($rows)): ?>
      <div class="row g-2 align-items-end d-print-none mb-2">
        <div class="col-md-6">
          <label class="form-label">Search Member / Remarks</label>
          <input type="text" id="reportSearch" class="form-control" placeholder="Search by member name or remarks...">
        </div>
        <div class="col-md-3">
          <label class="form-label">Status Filter</label>
          <select id="reportStatusFilter" class="form-select">
            <option value="">All statuses</option>
            <option value="present">Present only</option>
            <option value="absent">Absent only</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Remarks Filter</label>
          <select id="reportRemarksFilter" class="form-select">
            <option value="">All</option>
            <option value="with_remarks">With remarks only</option>
          </select>
        </div>
      </div>

        <div class="table-responsive mt-3">
          <table class="table align-middle">
            <thead>
              <tr>
                <th style="width: 60px;" class="text-center">#</th>
                <th>Member Name</th>
                <th style="width: 120px;" class="text-center">Status</th>
                <th style="width: 120px;" class="text-center">Time In</th>
                <th style="width: 120px;" class="text-center">Time Out</th>
                <th>Remarks</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1; foreach ($rows as $row): ?>
                <tr>
                  <td class="text-center text-muted"><?= $i++ ?></td>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td class="text-center">
                    <?php if ($row['status'] === 'present'): ?>
                      <span class="status-pill status-present">Present</span>
                    <?php else: ?>
                      <span class="status-pill status-absent">Absent</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <?= $row['time_in'] ? date('h:i A', strtotime($row['time_in'])) : '-' ?>
                  </td>
                  <td class="text-center">
                    <?= $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '-' ?>
                  </td>
                  <td><?= htmlspecialchars($row['remarks'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="alert alert-light border mt-3">No attendance records found for this event and date.</div>
      <?php endif; ?>
    <?php else: ?>
      <div class="alert alert-light border">Select an event and attendance date, then click <strong>Load Report</strong> to view attendance details.</div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Event dropdown search filter + auto-load on change
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    const eventSelect = document.getElementById('eventSelect');
    const eventSearch = document.getElementById('eventSearch');
    const dateSelect  = document.querySelector('select[name="attendance_date"]');
    const filterForm  = eventSelect ? eventSelect.closest('form') : null;

    if (!eventSelect || !eventSearch) return;

    const originalOptions = Array.from(eventSelect.options);

    // Type to filter events in the dropdown
    eventSearch.addEventListener('input', function() {
      const term = this.value.toLowerCase();
      eventSelect.innerHTML = '';
      originalOptions.forEach(function(opt) {
        if (!term || opt.text.toLowerCase().includes(term)) {
          eventSelect.appendChild(opt);
        }
      });
    });

    // When changing event, reset date and auto-submit to load new attendance data
    eventSelect.addEventListener('change', function() {
      if (dateSelect) {
        dateSelect.selectedIndex = 0; // let the server choose appropriate default date
      }
      if (filterForm) {
        filterForm.submit();
      }
    });

    // When changing attendance date, auto-submit as well
    if (dateSelect && filterForm) {
      dateSelect.addEventListener('change', function() {
        filterForm.submit();
      });
    }
  });
})();

// Report table filters + summary sync
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    const table = document.querySelector('.card-report table');
    if (!table) return;


    const rows = Array.from(table.querySelectorAll('tbody tr'));
    const searchInput = document.getElementById('reportSearch');
    const statusFilter = document.getElementById('reportStatusFilter');
    const remarksFilter = document.getElementById('reportRemarksFilter');

    const summaryPresentEl = document.getElementById('summaryPresent');
    const summaryAbsentEl = document.getElementById('summaryAbsent');
    const summaryAttendanceEl = document.getElementById('summaryAttendance');

    function applyFilters() {
      const term = (searchInput && searchInput.value ? searchInput.value : '').toLowerCase();
      const statusVal = statusFilter ? statusFilter.value : '';
      const remarksVal = remarksFilter ? remarksFilter.value : '';

      let presentVisible = 0;
      let absentVisible = 0;

      rows.forEach(function(row) {
        const nameCell = row.querySelector('td:nth-child(2)');
        const statusPill = row.querySelector('.status-pill');
        const remarksCell = row.querySelector('td:last-child');

        if (!nameCell || !statusPill) return;

        const nameText = nameCell.textContent.toLowerCase();
        const remarksText = (remarksCell ? remarksCell.textContent : '').toLowerCase();
        const statusText = statusPill.classList.contains('status-present') ? 'present' : 'absent';

        let visible = true;

        if (term && !(nameText.includes(term) || remarksText.includes(term))) {
          visible = false;
        }

        if (statusVal && statusVal !== statusText) {
          visible = false;
        }

        if (remarksVal === 'with_remarks') {
          if (!remarksText.trim()) visible = false;
        }

        row.style.display = visible ? '' : 'none';

        if (visible) {
          if (statusText === 'present') presentVisible++;
          else absentVisible++;
        }
      });

      const totalVisible = presentVisible + absentVisible;
      if (summaryPresentEl) summaryPresentEl.textContent = presentVisible;
      if (summaryAbsentEl) summaryAbsentEl.textContent = absentVisible;
      if (summaryAttendanceEl) {
        summaryAttendanceEl.textContent = totalVisible > 0
          ? ((presentVisible / totalVisible) * 100).toFixed(1) + '%'
          : '0%';
      }
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
    if (remarksFilter) remarksFilter.addEventListener('change', applyFilters);

    applyFilters();
  });
})();
</script>
</body>
</html>
