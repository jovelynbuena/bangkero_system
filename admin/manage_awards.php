<!-- Quick Admin Panel for Awards Management -->
<!-- Save this as: admin/manage_awards.php -->

<?php
session_start();
require_once('../config/db_connect.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $conn->prepare("INSERT INTO awards (award_title, awarding_body, category, description, year_received, date_received) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssis", $_POST['title'], $_POST['body'], $_POST['category'], $_POST['description'], $_POST['year'], $_POST['date']);
                $stmt->execute();
                $message = "Award added successfully!";
                break;
                
            case 'delete':
                $stmt = $conn->prepare("DELETE FROM awards WHERE award_id = ?");
                $stmt->bind_param("i", $_POST['award_id']);
                $stmt->execute();
                $message = "Award deleted successfully!";
                break;
        }
    }
}

// Fetch all awards
$awards = $conn->query("SELECT * FROM awards ORDER BY year_received DESC, award_title ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Awards - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-trophy-fill text-warning me-2"></i>Manage Awards</h2>
                <a href="../index/home/awards.php" class="btn btn-primary">
                    <i class="bi bi-eye me-2"></i>View Public Page
                </a>
            </div>

            <?php if (isset($message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Add New Award Form -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add New Award</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Award Title *</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Awarding Body *</label>
                                <input type="text" name="body" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Category *</label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <option value="National">National</option>
                                    <option value="Regional">Regional</option>
                                    <option value="Local">Local</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Year *</label>
                                <input type="number" name="year" class="form-control" min="2000" max="2099" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Date Received *</label>
                                <input type="date" name="date" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Add Award
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Awards List -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>All Awards (<?php echo $awards->num_rows; ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="25%">Title</th>
                                    <th width="20%">Awarding Body</th>
                                    <th width="10%">Category</th>
                                    <th width="10%">Year</th>
                                    <th width="15%">Date</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($award = $awards->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $award['award_id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($award['award_title']); ?></strong>
                                        <?php if ($award['description']): ?>
                                            <br><small class="text-muted">
                                                <?php echo substr(htmlspecialchars($award['description']), 0, 60); ?>...
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($award['awarding_body']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $award['category'] === 'National' ? 'primary' : 
                                                ($award['category'] === 'Regional' ? 'success' : 'warning'); 
                                        ?>">
                                            <?php echo htmlspecialchars($award['category']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $award['year_received']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($award['date_received'])); ?></td>
                                    <td>
                                        <form method="POST" action="" style="display: inline;" 
                                              onsubmit="return confirm('Delete this award?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="award_id" value="<?php echo $award['award_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
