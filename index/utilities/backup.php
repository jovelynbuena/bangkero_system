<?php 
include('../navbar.php'); // sidebar + top navbar
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Restore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* Para hindi matakpan ng sidebar */
        .main-content {
            margin-left: 250px; /* sidebar width */
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
    </style>
</head>
<body>

<div class="main-content">
    <div class="container-fluid">
        <h2 class="mb-4"><i class="bi bi-tools"></i> Backup & Restore</h2>

        <!-- Backup Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-cloud-arrow-down"></i> Backup Database</h5>
                <p class="text-muted">Click the button below to generate a backup of your database.</p>
                <form method="post" action="backup_action.php">
                    <button type="submit" name="backup" class="btn btn-primary">
                        <i class="bi bi-download"></i> Backup Now
                    </button>
                </form>
            </div>
        </div>

        <!-- Restore Section -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-arrow-repeat"></i> Restore Database</h5>
                <p class="text-muted">Upload an SQL file to restore your database.</p>
                <form method="post" action="restore_action.php" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="sql_file" class="form-label">Choose SQL File</label>
                        <input type="file" name="sql_file" id="sql_file" class="form-control" required>
                    </div>
                    <button type="submit" name="restore" class="btn btn-success">
                        <i class="bi bi-upload"></i> Restore Now
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
