<?php
include('../config/db_connect.php');

// Get member ID from URL
$member_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch member from database
$sql = "SELECT * FROM members WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

// Image handling
$serverPath = __DIR__ . '/uploads/members/'; // Server path for file_exists
$defaultImage = '../uploads/members/default_member.png'; // Browser path for default image
$imgFile = $defaultImage;

if ($member && !empty($member['image']) && file_exists($serverPath . $member['image'])) {
    $imgFile = '../uploads/members/' . $member['image']; // Browser path for member image
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Member Information Sheet | Bangkero & Fishermen Association</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --brand: #00897b; --accent: #ff7043; }
    body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; margin: 0; padding: 30px; color: #222; }
    .sheet { background: #fff; max-width: 960px; margin: auto; padding: 34px 36px; border: 1px solid #e3e6ea; box-shadow: 0 6px 16px rgba(0,0,0,0.06); border-radius: 12px; position: relative; }
    .toolbar { display: flex; justify-content: space-between; margin-bottom: 18px; }
    .doc-header { display: flex; align-items: center; gap: 16px; margin-bottom: 10px; }
    .doc-header img { height: 60px; width: auto; }
    .doc-header .titles h2 { margin: 0; font-weight: 800; color: var(--brand); }
    .doc-header .titles small { color: #6b7280; }
    .doc-meta { display: flex; justify-content: space-between; font-size: 0.92rem; padding: 8px 12px; background: #fafafa; border: 1px solid #ececec; border-left: 4px solid var(--accent); border-radius: 8px; margin-bottom: 18px; }
    .photo-box { float: right; width: 150px; height: 150px; border: 1px solid #e0e0e0; border-radius: 10px; margin-left: 20px; background-color: #f2f4f7; overflow: hidden; }
    .photo-box img { width: 100%; height: 100%; object-fit: cover; }
    .section { margin-bottom: 24px; clear: both; }
    .section-title { font-weight: 700; color: #374151; border-bottom: 2px solid #e5e7eb; padding-bottom: 6px; margin-bottom: 14px; font-size: 1.05rem; letter-spacing: .2px; }
    .info-row { display: flex; gap: 10px; margin-bottom: 8px; }
    .label { min-width: 220px; font-weight: 600; color: #334155; }
    .value { color: #111827; }
    @media print {
      body { background-color: white; margin: 0; }
      .sheet { box-shadow: none; border: none; }
      .toolbar { display: none; }
      .doc-meta { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
  </style>
</head>
<body>

<?php if ($member): ?>
<div class="sheet">
    <div class="toolbar">
        <a href="../index/management/memberlist.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
        <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="bi bi-printer"></i> Print</button>
    </div>

    <div class="doc-header">
        <img src="images/logo1.png" alt="Association Logo">
        <div class="titles">
            <h2 class="h4 mb-1">Member Information Sheet</h2>
            <small>Bangkero & Fishermen Association</small>
            <div class="address mt-1">
                <small class="text-muted">Barangay Baretto, Olongapo City</small>
            </div>
        </div>
    </div>

    <div class="doc-meta">
        <div>Generated on: <strong><?php echo date('F j, Y g:i A'); ?></strong></div>
        <div>Member ID: <strong><?php echo (int)$member_id; ?></strong></div>
    </div>

    <div class="section">
        <div class="photo-box">
<img src="../uploads/members/<?= htmlspecialchars($member['image']) ?>" width="120" class="rounded shadow">
        </div>

        <div class="section-title">Personal Information</div>
        <div class="info-row"><span class="label">Full Name:</span><span class="value"><?php echo htmlspecialchars($member['name']); ?></span></div>
        <div class="info-row"><span class="label">Date of Birth:</span><span class="value"><?php echo htmlspecialchars($member['dob']); ?></span></div>
        <div class="info-row"><span class="label">Gender:</span><span class="value"><?php echo htmlspecialchars($member['gender']); ?></span></div>
        <div class="info-row"><span class="label">Phone:</span><span class="value"><?php echo htmlspecialchars($member['phone']); ?></span></div>
        <div class="info-row"><span class="label">Email:</span><span class="value"><?php echo htmlspecialchars($member['email']); ?></span></div>
        <div class="info-row"><span class="label">Address:</span><span class="value"><?php echo htmlspecialchars($member['address']); ?></span></div>
    </div>

    <div class="section">
        <div class="section-title">Work & License Information</div>
        <div class="info-row"><span class="label">Work Type:</span><span class="value"><?php echo htmlspecialchars($member['work_type']); ?></span></div>
        <div class="info-row"><span class="label">License Number:</span><span class="value"><?php echo htmlspecialchars($member['license_number']); ?></span></div>
        <div class="info-row"><span class="label">Boat Name:</span><span class="value"><?php echo htmlspecialchars($member['boat_name']); ?></span></div>
        <div class="info-row"><span class="label">Fishing Area:</span><span class="value"><?php echo htmlspecialchars($member['fishing_area']); ?></span></div>
    </div>

    <div class="section">
        <div class="section-title">Emergency Contact</div>
        <div class="info-row"><span class="label">Contact Name:</span><span class="value"><?php echo htmlspecialchars($member['emergency_name']); ?></span></div>
        <div class="info-row"><span class="label">Contact Phone:</span><span class="value"><?php echo htmlspecialchars($member['emergency_phone']); ?></span></div>
    </div>

    <div class="section">
        <div class="section-title">Other</div>
        <div class="info-row"><span class="label">Agreement Status:</span><span class="value"><?php echo $member['agreement'] ? 'Agreed' : 'Not Agreed'; ?></span></div>
    </div>
</div>

<?php else: ?>
<div class="sheet">
    <div class="btn-top">
        <a href="memberlist.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <p style="text-align:center;">Member not found.</p>
</div>
<?php endif; ?>

</body>
</html>
