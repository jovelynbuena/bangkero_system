<?php
date_default_timezone_set('Asia/Manila');
session_start();
require '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $remember = isset($_POST['remember']);

    // Fetch user including status
    $stmt = $conn->prepare("SELECT id, username, password_hash, role, status FROM users WHERE username = ?");
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    $ip_address = $_SERVER['REMOTE_ADDR']; // user IP

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // ✅ Check account approval (case-insensitive)
        if (strtolower($row['status']) !== 'approved') {
            $action = "Failed login attempt (not approved)";
            $description = "Attempted username: $username";
            $user_id = $row['id'];

            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
            $log_stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
            $log_stmt->execute();
            $log_stmt->close();

            header("Location: login.php?login=failed&message=Account pending approval");
            exit();
        }

        // ✅ Password verification
        if (password_verify($password, $row["password_hash"]) || $password === $row["password_hash"]) {
            $_SESSION["user_id"] = $row["id"];
            $_SESSION["username"] = $row["username"];
            $_SESSION["role"] = $row["role"];

            // Log successful login
            $action = "Logged in";
            $description = null;
            $user_id = $row['id'];

            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
            $log_stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
            $log_stmt->execute();
            $log_stmt->close();

            if ($remember) {
                setcookie("remembered_user", $username, time() + (86400 * 30), "/");
            } else {
                setcookie("remembered_user", "", time() - 3600, "/");
            }

            // Role-based redirect
            $redirectUrl = match ($row["role"]) {
                "admin" => "admin.php",
                "officer" => "admin.php",
                default => "index.php"
            };

            header("Location: $redirectUrl?login=success");
            exit();
        } else {
            // Log failed login (wrong password)
            $action = "Failed login attempt (wrong password)";
            $description = "Attempted username: $username";
            $user_id = $row['id'];

            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
            $log_stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
            $log_stmt->execute();
            $log_stmt->close();

            header("Location: login.php?login=failed&message=Incorrect password");
            exit();
        }
    } else {
        // Log failed login (user not found)
        $action = "Failed login attempt (user not found)";
        $description = "Attempted username: $username";
        $user_id = null;

        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $log_stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
        $log_stmt->execute();
        $log_stmt->close();

        header("Location: login.php?login=failed&message=User not found");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Bankero System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: url('../images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            background-color: #f4f4f4;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.25);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .login-card img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin-bottom: 15px;
        }
        .login-card h3 {
            font-weight: bold;
            margin-bottom: 6px;
            color: #2367b7;
        }
        .login-card p {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 8px;
        }
        .btn-login {
            background-color: #2367b7;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 8px;
            font-weight: bold;
            color: #fff;
            transition: 0.3s;
        }
        .btn-login:hover {
            background-color: #1c5498;
        }
        .options-container {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        .options-container a {
            text-decoration: none;
            color: #2367b7;
        }
        .options-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="images/logo1.png" alt="Association Logo" class="hero-logo">
        <h3>Bankero & Fishermen</h3>
        <p>Barangay Barretto, Olongapo City</p>

        <form method="POST" action="">
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Username" 
                    value="<?php echo isset($_COOKIE['remembered_user']) ? htmlspecialchars($_COOKIE['remembered_user']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>

            <div class="options-container">
                <div>
                    <input type="checkbox" name="remember" id="remember" 
                        <?php echo isset($_COOKIE['remembered_user']) ? 'checked' : ''; ?>>
                    <label for="remember">Remember Me</label>
                </div>
                <a href="forgot-password.php">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-login mb-2">Login</button>
            <a href="register.php" class="btn btn-outline-primary w-100 fw-bold" style="border-radius: 8px;">Register as Officer</a>
        </form>
    </div>

    <?php if (isset($_GET['login']) && $_GET['login'] === 'failed' && !empty($_GET['message'])): ?>
    <script>
        Swal.fire({
            toast: true,
            icon: 'error',
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
