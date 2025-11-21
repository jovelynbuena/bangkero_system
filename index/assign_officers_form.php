<?php
require_once('../config/db_connect.php');

// Get member ID from the query string
if (!isset($_GET['member_id'])) {
    die("No member selected.");
}

$member_id = intval($_GET['member_id']);

// Fetch member details
$stmt = $conn->prepare("SELECT id, name FROM members WHERE id = ?");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

if (!$member) {
    die("Member not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Officer Role</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <div class="card shadow rounded-4">
            <div class="card-body p-5">
                <h3 class="text-center mb-4">Assign Officer Role</h3>

                <form action="save_officer.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="member_id" value="<?= htmlspecialchars($member['id']) ?>">

                    <!-- Officer Image Upload -->
                    <div class="mb-3 text-center">
                        <label for="officer_image" class="form-label">Upload Officer Image</label>
                        <input type="file" name="officer_image" id="officer_image" class="form-control" accept="image/*" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Member Name</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($member['name']) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="position" class="form-label">Position</label>
                        <select name="position" id="position" class="form-select" required>
                            <option value="">-- Select Position --</option>
                            <option value="President">President</option>
                            <option value="Vice President">Vice President</option>
                            <option value="Secretary">Secretary</option>
                            <option value="Treasurer">Treasurer</option>
                            <option value="Auditor">Auditor</option>
                            <option value="Board Member">Board Member</option>
                        </select>
                    </div>

                    <div class="row mb-3">
                       <div class="col-md-6">
                       <label for="term_start" class="form-label">Start Term</label>
                       <input type="date" name="term_start" id="term_start" class="form-control" required>
                    </div>

                   <div class="col-md-6">
                    <label for="term_end" class="form-label">End Term</label>
                   <input type="date" name="term_end" id="term_end" class="form-control" required>
                  </div>
                  </div>


                    

                    <button type="submit" class="btn btn-success w-100">Save Officer</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
