<?php
session_start();

if (empty($_SESSION['username'])) {
    header('location: ../login.php');
    exit;
}

include('../config/db_connect.php');

// Validate member ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid or missing member ID.");
}

$memberId = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
$stmt->bind_param("i", $memberId);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

if (!$member) {
    die("Member not found.");
}

$updateSuccess = false;
$errorMessage = "";

// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST['first_name']);
    $middleInitial = trim($_POST['middle_initial']);
    $lastName = trim($_POST['last_name']);
    $fullName = "$firstName $middleInitial $lastName";

    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $workType = $_POST['work_type'];
    $licenseNumber = $_POST['license_number'];
    $boatName = $_POST['boat_name'];
    $fishingArea = $_POST['fishing_area'];
    $emergencyName = $_POST['emergency_name'];
    $emergencyPhone = $_POST['emergency_phone'];
    $agreement = isset($_POST['agreement']) ? 1 : 0;

    $imageFileName = $member['image'];
    $imgDir = "../uploads/members/";
    if (!is_dir($imgDir)) { mkdir($imgDir, 0777, true); }

    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errorMessage = "Only JPG, PNG, and GIF files are allowed.";
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errorMessage = "Image size must be less than 2MB.";
        } else {
            $tmpName = $_FILES['image']['tmp_name'];
            $originalName = preg_replace('/[^A-Za-z0-9_\-\.]/', '', basename($_FILES['image']['name']));
            $imageFileName = uniqid('member_', true) . '_' . $originalName;
            $uploadPath = $imgDir . $imageFileName;

            if (getimagesize($tmpName) !== false && move_uploaded_file($tmpName, $uploadPath)) {
                if (!empty($member['image']) && $member['image'] !== 'default_member.png' && file_exists($imgDir . $member['image'])) {
                    @unlink($imgDir . $member['image']);
                }
            } else {
                $errorMessage = "Failed to upload image.";
            }
        }
    }

    if (empty($errorMessage)) {
        $update = $conn->prepare("
            UPDATE members SET 
                name=?, dob=?, gender=?, phone=?, email=?, address=?, 
                work_type=?, license_number=?, boat_name=?, fishing_area=?, 
                emergency_name=?, emergency_phone=?, agreement=?, image=?
            WHERE id=?
        ");
        $update->bind_param(
            "ssssssssssssssi",
            $fullName, $dob, $gender, $phone, $email, $address,
            $workType, $licenseNumber, $boatName, $fishingArea,
            $emergencyName, $emergencyPhone, $agreement, $imageFileName, $memberId
        );

        if ($update->execute()) {
            $updateSuccess = true;
        } else {
            $errorMessage = "Error updating member: " . $update->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Member | Bangkero & Fishermen Association</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f7fa;
        }
        .card {
            border-radius: 18px;
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .btn-success {
            background-color: #00897b;
            border-color: #00897b;
        }
        .btn-success:hover {
            background-color: #00695c;
            border-color: #00695c;
        }
        .btn-secondary {
            background-color: #b0bec5;
            border-color: #b0bec5;
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <a href="management/memberlist.php" class="btn btn-secondary">&larr; Back</a>
                        <h4 class="m-0 text-center w-100">Edit Member Information</h4>
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" 
                                    value="<?= htmlspecialchars(explode(' ', $member['name'])[0]) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle Initial</label>
                                <input type="text" name="middle_initial" class="form-control" 
                                    value="<?= htmlspecialchars(explode(' ', $member['name'])[1] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" 
                                    value="<?= htmlspecialchars(explode(' ', $member['name'])[2] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="dob" class="form-control" 
                                value="<?= htmlspecialchars($member['dob']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="Male" <?= $member['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= $member['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= $member['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" 
                                value="<?= htmlspecialchars($member['phone']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                value="<?= htmlspecialchars($member['email']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" 
                                value="<?= htmlspecialchars($member['address']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Type of Work</label>
                            <select name="work_type" class="form-select" required>
                                <option value="Fisherman" <?= $member['work_type'] === 'Fisherman' ? 'selected' : '' ?>>Fisherman</option>
                                <option value="Bangkero" <?= $member['work_type'] === 'Bangkero' ? 'selected' : '' ?>>Bangkero</option>
                                <option value="Both" <?= $member['work_type'] === 'Both' ? 'selected' : '' ?>>Both</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">License Number</label>
                            <input type="text" name="license_number" class="form-control" 
                                value="<?= htmlspecialchars($member['license_number']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Boat Name</label>
                            <input type="text" name="boat_name" class="form-control" 
                                value="<?= htmlspecialchars($member['boat_name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fishing Area</label>
                            <input type="text" name="fishing_area" class="form-control" 
                                value="<?= htmlspecialchars($member['fishing_area']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Emergency Contact Name</label>
                            <input type="text" name="emergency_name" class="form-control" 
                                value="<?= htmlspecialchars($member['emergency_name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Emergency Contact Phone</label>
                            <input type="text" name="emergency_phone" class="form-control" 
                                value="<?= htmlspecialchars($member['emergency_phone']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Upload New Image (optional)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <?php if (!empty($member['image'])): ?>
                                <div class="mt-2">
                                    <small>Current Image:</small><br>
                                    <img src="../uploads/members/<?= htmlspecialchars($member['image']) ?>" width="120" class="rounded shadow">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" name="agreement" class="form-check-input" id="agreement" 
                                <?= $member['agreement'] ? 'checked' : '' ?>>
                            <label for="agreement" class="form-check-label">I agree to the association’s rules</label>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Update Member</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($updateSuccess): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Member updated successfully!',
                confirmButtonColor: '#00897b'
            }).then(() => {
                window.location.href = "management/memberlist.php";
            });
        </script>
    <?php elseif (!empty($errorMessage)): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?= htmlspecialchars($errorMessage) ?>',
                confirmButtonColor: '#d33'
            });
        </script>
    <?php endif; ?>
</body>
</html>
