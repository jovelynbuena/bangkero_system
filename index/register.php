<?php
date_default_timezone_set('Asia/Manila');
session_start();
require '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    if ($password !== $confirm_password) {
        header("Location: register.php?status=error&message=Passwords do not match");
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header("Location: register.php?status=error&message=Username or email already exists");
        exit();
    }

    $stmt->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'officer';
    $status = 'pending';
    $created_at = date('Y-m-d H:i:s');

    $insert = $conn->prepare("INSERT INTO users (username, password_hash, email, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $insert->bind_param("ssssss", $username, $hashed_password, $email, $role, $status, $created_at);

    if ($insert->execute()) {
        header("Location: login.php?status=info&message=Your account is pending admin approval");
        exit();
    } else {
        header("Location: register.php?status=error&message=Registration failed, please try again");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Registration | Bankero System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: url('../images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }
        .register-card {
            width: 100%;
            max-width: 450px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(12px);
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.25);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .register-card img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin-bottom: 15px;
        }
        .register-card h3 {
            font-weight: bold;
            color: #2367b7;
        }
        .form-control {
            border-radius: 8px;
        }
        .btn-register {
            background-color: #2367b7;
            border: none;
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            color: #fff;
            font-weight: bold;
        }
        .btn-register:hover {
            background-color: #1c5498;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <img src="images/logo1.png" alt="Logo">
        <h3>Officer Registration</h3>
        <p>Bankero & Fishermen Association</p>

        <form method="POST" action="">
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email Address" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="mb-3">
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
            </div>
            <button type="submit" class="btn-register">Register</button>
        </form>
        <div class="mt-3">
            <a href="login.php">Back to Login</a>
        </div>
    </div>

    <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
    <script>
        Swal.fire({
            toast: true,
            icon: '<?php echo htmlspecialchars($_GET['status']); ?>',
            title: "<?php echo htmlspecialchars($_GET['message']); ?>",
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    </script>
    <?php endif; ?>
</body>
</html>
