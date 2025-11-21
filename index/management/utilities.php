<?php
session_start();

if ($_SESSION['username'] == "") {
    header('location: ../login.php');
    exit;
}
require_once('../../config/db_connect.php');

$memberName = isset($_SESSION['member_name']) ? $_SESSION['member_name'] : 'Admin';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Utilities | Bangkero & Fishermen Association</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #fff;
        }

    

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 32px 32px 16px 32px;
            min-height: 100vh;
        }


        /* Utility Cards */
        .utility-card {
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            border: 2px solid #80cbc4;
            margin-bottom: 24px;
            transition: transform 0.2s;
        }

        .utility-card:hover {
            transform: translateY(-4px);
        }

        .utility-card .card-body {
            min-height: 120px;
            display: flex;
            align-items: center;
        }

        .utility-icon {
            font-size: 2.2rem;
            margin-right: 18px;
            color: #ff7043;
        }

        .btn-primary {
            background-color: #ff7043;
            border-color: #ff7043;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #00897b;
            border-color: #00897b;
        }

        .btn-warning {
            background-color: #4fc3f7;
            border-color: #4fc3f7;
            color: #01579b;
        }
        .btn-warning:hover, .btn-warning:focus {
            background-color: #0288d1;
            border-color: #0288d1;
            color: #fff;
        }

        .btn-info {
            background-color: #26c6da;
            border-color: #26c6da;
            color: #fff;
        }
        .btn-info:hover {
            background-color: #00838f;
            border-color: #00838f;
        }

        .btn-secondary {
            background-color: #80cbc4;
            border-color: #80cbc4;
            color: #004d40;
        }
        .btn-secondary:hover {
            background-color: #00897b;
            border-color: #00897b;
            color: #fff;
        }

        .hero-logo { 
            height: 70px; 
            width: auto; 
            display: block; 
        }
        .logo-wrapper { 
            width: 70px; 
            height: 70px; 
        }

    </style>
</head>
<body>

<?php include('../navbar.php'); ?>

<!-- Main Content -->
<div class="main-content">
    <h2 class="fw-bold mb-4 text-center">Admin Panel — Utilities</h2>
    <div class="row">
        <!-- Utility: Database Backup -->
        <div class="col-md-6">
            <div class="card utility-card">
                <div class="card-body">
                    <span class="utility-icon"><i class="bi bi-hdd-stack"></i></span>
                    <div>
                        <div class="fw-semibold">Database Backup</div>
                        <div class="text-muted mb-2">Download a backup of the database for safekeeping.</div>
                        <a href="../database/backup.php" class="btn btn-sm btn-primary">Download Backup</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Utility: Password Reset -->
        <div class="col-md-6">
            <div class="card utility-card">
                <div class="card-body">
                    <span class="utility-icon"><i class="bi bi-key"></i></span>
                    <div>
                        <div class="fw-semibold">Reset User Password</div>
                        <div class="text-muted mb-2">Manually reset a user's password in case of account issues.</div>
                        <a href="reset_password.php" class="btn btn-sm btn-warning">Reset Password</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Utility: Calculator -->
        <div class="col-md-6">
            <div class="card utility-card">
                <div class="card-body">
                    <span class="utility-icon"><i class="bi bi-calculator"></i></span>
                    <div>
                        <div class="fw-semibold">Simple Calculator</div>
                        <div class="text-muted mb-2">Use a calculator for quick computations.</div>
                        <a href="calculator.php" class="btn btn-sm btn-secondary">Open Calculator</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Utility: Activity Logs -->
        <div class="col-md-6">
            <div class="card utility-card">
                <div class="card-body">
                    <span class="utility-icon"><i class="bi bi-clock-history"></i></span>
                    <div>
                        <div class="fw-semibold">Activity Logs</div>
                        <div class="text-muted mb-2">View recent admin and user activities.</div>
                        <a href="logs.php" class="btn btn-sm btn-info">View Logs</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function delayedLogout(event) {
        event.preventDefault();
        const overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = 0;
        overlay.style.left = 0;
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.zIndex = 9999;
        overlay.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="spinner-border text-primary me-2" role="status"></div>
                <strong class="fs-5">Logging out...</strong>
            </div>`;
        document.body.appendChild(overlay);
        setTimeout(() => {
            window.location.href = '../logout.php';
        }, 1000);
    }
</script>
</body>
</html>
