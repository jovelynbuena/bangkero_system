<?php
include('../../config/db_connect.php');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Initialize variables for sticky form
$first_name = $middle_initial = $last_name = $dob = $gender = $phone = $address = "";
$work_type = $license_number = $boat_name = $fishing_area = $emergency_name = $emergency_phone = $email = "";
$agreement = 0;
$errorMessage = "";
$success = false;

// Handle POST submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $first_name = trim($_POST['first_name']);
    $middle_initial = trim($_POST['middle_initial']);
    $last_name = trim($_POST['last_name']);
    $name = trim($first_name . ' ' . $middle_initial . ' ' . $last_name);

    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $work_type = $_POST['work_type'];
    $license_number = $_POST['license_number'] ?? '';
    $boat_name = $_POST['boat_name'];
    $fishing_area = $_POST['fishing_area'];
    $emergency_name = $_POST['emergency_name'];
    $emergency_phone = $_POST['emergency_phone'];
    $email = trim($_POST['email']);
    $agreement = isset($_POST['agreement']) ? 1 : 0;

    // Check for existing email
    $checkStmt = $conn->prepare("SELECT id FROM members WHERE LOWER(email) = LOWER(?)");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
        $errorMessage = "Email already exists. Please use a different one.";
    }
    $checkStmt->close();

    // Handle image upload
    $imgDir = __DIR__ . '/../../uploads/members/'; // absolute path for server
    $imgUrl = '../../uploads/members/'; // browser-accessible relative path

    $image_name = 'default_member.png';
    if (empty($errorMessage) && !empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowed_types) || $_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errorMessage = "Please upload a valid image file (jpg, png, gif) under 2MB.";
        } else {
            $tmp = $_FILES['image']['tmp_name'];
            $image_name = uniqid('member_', true) . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '', basename($_FILES['image']['name']));
            if (!move_uploaded_file($tmp, $imgDir . $image_name)) {
    $errorMessage = "Error uploading image. Please try again.";
}

        }
    }

    // Insert into database if no errors
    if (empty($errorMessage)) {
        $stmt = $conn->prepare("INSERT INTO members (name, dob, gender, phone, address, work_type, license_number, boat_name, fishing_area, emergency_name, emergency_phone, agreement, image, email) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssssss", $name, $dob, $gender, $phone, $address, $work_type, $license_number, $boat_name, $fishing_area, $emergency_name, $emergency_phone, $agreement, $image_name, $email);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errorMessage = "Error inserting into members table: {$stmt->error}";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Member | Bangkero & Fishermen Association</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body { font-family: "Segoe UI", sans-serif; background: #f4f6f9; }
.main-content { margin-left: 250px; padding: 32px; min-height: 100vh; }

.form-sheet {
    max-width: 900px;
    margin: auto;
    padding: 30px 36px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    position: relative;
}

.form-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
}

.form-header img { height: 60px; width: auto; }
.form-header .titles h2 { margin:0; font-weight:700; color: #00897b; }
.form-header .titles small { color:#6b7280; }

.section-title {
    font-weight: 700;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 6px;
    margin-bottom: 14px;
    font-size: 1.05rem;
    letter-spacing: .2px;
}
</style>
</head>
<body>
<?php include('../navbar.php'); ?>

<div class="main-content">
<div class="form-sheet">

    <div class="form-header">
        <img src="../images/logo1.png" alt="Association Logo">
        <div class="titles">
            <h2>Add Member Form</h2>
            <small>Bangkero & Fishermen Association</small>
        </div>
    </div>

    <form action="add_member.php" method="POST" enctype="multipart/form-data">
        <div class="row mb-3">
            <div class="col-md-4"><label class="form-label">First Name</label>
                <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($first_name) ?>" required>
            </div>
            <div class="col-md-4"><label class="form-label">Middle Initial</label>
                <input type="text" class="form-control" name="middle_initial" value="<?= htmlspecialchars($middle_initial) ?>">
            </div>
            <div class="col-md-4"><label class="form-label">Last Name</label>
                <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($last_name) ?>" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6"><label class="form-label">Date of Birth</label>
                <input type="date" class="form-control" name="dob" value="<?= htmlspecialchars($dob) ?>" required>
            </div>
            <div class="col-md-6"><label class="form-label">Gender</label>
                <select class="form-select" name="gender">
                    <option value="Male" <?= $gender=='Male'?'selected':'' ?>>Male</option>
                    <option value="Female" <?= $gender=='Female'?'selected':'' ?>>Female</option>
                    <option value="Other" <?= $gender=='Other'?'selected':'' ?>>Other</option>
                </select>
            </div>
        </div>

        <div class="mb-3"><label class="form-label">Phone Number</label>
            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($phone) ?>" required>
        </div>
        <div class="mb-3"><label class="form-label">Email Address</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" required>
        </div>
        <div class="mb-3"><label class="form-label">Address</label>
            <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($address) ?>" required>
        </div>

        <div class="row mb-3">
            <div class="col-md-6"><label class="form-label">Type of Work</label>
                <select class="form-select" name="work_type">
                    <option value="Fisherman" <?= $work_type=='Fisherman'?'selected':'' ?>>Fisherman</option>
                    <option value="Bangkero" <?= $work_type=='Bangkero'?'selected':'' ?>>Bangkero</option>
                    <option value="Both" <?= $work_type=='Both'?'selected':'' ?>>Both</option>
                </select>
            </div>
            <div class="col-md-6"><label class="form-label">License Number</label>
                <input type="number" class="form-control" name="license_number" value="<?= htmlspecialchars($license_number) ?>">
            </div>
        </div>

        <div class="mb-3"><label class="form-label">Boat Name (if any)</label>
            <input type="text" class="form-control" name="boat_name" value="<?= htmlspecialchars($boat_name) ?>">
        </div>
        <div class="mb-3"><label class="form-label">Fishing Area / Route</label>
            <input type="text" class="form-control" name="fishing_area" value="<?= htmlspecialchars($fishing_area) ?>">
        </div>

        <div class="row mb-3">
            <div class="col-md-6"><label class="form-label">Emergency Contact Name</label>
                <input type="text" class="form-control" name="emergency_name" value="<?= htmlspecialchars($emergency_name) ?>">
            </div>
            <div class="col-md-6"><label class="form-label">Emergency Contact Phone</label>
                <input type="text" class="form-control" name="emergency_phone" value="<?= htmlspecialchars($emergency_phone) ?>">
            </div>
        </div>

        <div class="mb-3"><label class="form-label">Upload Image</label>
            <input type="file" class="form-control" name="image" accept="image/*">
        </div>

        <div class="form-check mb-4">
            <input type="checkbox" class="form-check-input" name="agreement" value="1" <?= $agreement?'checked':'' ?> required>
            <label class="form-check-label">I agree to follow the association’s rules</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Submit</button>
    </form>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert handling after page load -->
<script>
window.onload = function() {
    <?php if(!empty($errorMessage)): ?>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '<?= $errorMessage ?>'
        });
    <?php elseif(!empty($success) && $success === true): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Member has been added successfully.'
        }).then(() => {
            window.location.href = 'memberlist.php';
        });
    <?php endif; ?>
};
</script>

</body>
</html>
