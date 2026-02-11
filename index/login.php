<?php
date_default_timezone_set('Asia/Manila');
session_start();
require '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $remember = isset($_POST['remember']);

    // Fetch user including status and name
    $stmt = $conn->prepare("SELECT id, username, password_hash, role, status, first_name, last_name FROM users WHERE username = ?");
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
            
            // ✅ Store first name and last name in session
            $_SESSION["first_name"] = $row["first_name"];
            $_SESSION["last_name"] = $row["last_name"];
            
            // ✅ Create display name: use first_name if available, otherwise use username
            if (!empty($row["first_name"])) {
                $_SESSION["fullname"] = ucfirst($row["first_name"]);
            } else {
                // Fallback to username
                $displayName = $row["username"];
                if (strpos($displayName, '@') !== false) {
                    $displayName = explode('@', $displayName)[0];
                }
                $_SESSION["fullname"] = ucfirst($displayName);
            }

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
        :root {
            --primary: #2367b7;
            --primary-dark: #1c5498;
            --card-bg: rgba(255, 255, 255, 0.95);
            --shadow: rgba(0, 0, 0, 0.15);
            --text-primary: #0b3f69;
            --text-secondary: #2b4b60;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            overflow: hidden;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Animated background particles */
        body::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12); }
            50% { box-shadow: 0 12px 40px rgba(0, 0, 0, 0.18); }
        }
        
        .login-card {
            width: 100%;
            max-width: 440px;
            background: var(--card-bg);
            padding: 40px 36px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease-out;
        }
        
        .logo-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
            justify-content: center;
            margin-bottom: 24px;
        }
        
        .login-card img {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .login-card img:hover {
            transform: scale(1.05);
        }
        
        .logo-text {
            text-align: left;
        }
        
        .login-card h3 {
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
            font-size: 1.4rem;
            line-height: 1.2;
        }
        
        .small-muted {
            font-size: 0.85rem;
            color: var(--text-secondary);
            opacity: 0.85;
            margin-top: 4px;
        }
        
        .welcome-text {
            margin: 0 0 28px 0;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .form-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
            text-align: left;
            display: block;
        }
        
        .form-control {
            border-radius: 12px;
            height: 48px;
            border: 1.5px solid #e5e7eb;
            font-size: 0.95rem;
            padding: 12px 16px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(35, 103, 183, 0.1);
            border-color: var(--primary);
            outline: none;
        }
        
        .form-control::placeholder {
            color: #9ca3af;
        }
        
        .input-group {
            position: relative;
            display: block !important;
        }
        
        .input-group .form-control {
            padding-right: 48px;
            width: 100%;
            display: block;
        }
        
        .btn-toggle-pass {
            position: absolute !important;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #6b7280;
            font-size: 1.1rem;
            width: 36px;
            height: 36px;
            cursor: pointer;
            transition: color 0.2s ease;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            padding: 0;
            margin: 0;
        }
        
        .btn-toggle-pass:hover {
            color: var(--primary);
        }
        
        .btn-toggle-pass:focus {
            outline: none;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            padding: 14px;
            width: 100%;
            height: 52px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(35, 103, 183, 0.3);
            cursor: pointer;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(35, 103, 183, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(35, 103, 183, 0.3);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 18px 0;
            font-size: 0.9rem;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-check-input {
            cursor: pointer;
            width: 18px;
            height: 18px;
        }
        
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .form-check-label {
            cursor: pointer;
            user-select: none;
            color: var(--text-secondary);
        }
        
        .options-row a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .options-row a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .btn-register-link {
            margin-top: 16px;
            padding: 12px;
            border-radius: 12px;
            border: 2px solid rgba(35, 103, 183, 0.2);
            background: transparent;
            color: var(--primary);
            font-weight: 700;
            text-decoration: none;
            display: block;
            transition: all 0.3s ease;
        }
        
        .btn-register-link:hover {
            border-color: var(--primary);
            background: rgba(35, 103, 183, 0.05);
            transform: translateY(-2px);
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
            color: #9ca3af;
            font-size: 0.85rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .divider span {
            padding: 0 12px;
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 32px 24px;
                border-radius: 16px;
            }
            
            .login-card img {
                width: 60px;
                height: 60px;
            }
            
            .login-card h3 {
                font-size: 1.2rem;
            }
            
            .form-control {
                height: 46px;
            }
            
            .btn-login {
                height: 48px;
            }
            
            .options-row {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-wrap">
            <img src="images/logo1.png" alt="Association Logo">
            <div class="logo-text">
                <h3>Bankero & Fishermen</h3>
                <div class="small-muted">Barangay Barretto, Olongapo City</div>
            </div>
        </div>
        
        <p class="welcome-text">Sign in to access your account</p>

        <form method="POST" action="" id="loginForm">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input id="username" type="text" name="username" class="form-control" placeholder="Enter your username"
                    value="<?php echo isset($_COOKIE['remembered_user']) ? htmlspecialchars($_COOKIE['remembered_user']) : ''; ?>" 
                    required autofocus>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input id="password" type="password" name="password" class="form-control" 
                           placeholder="Enter your password" required>
                    <button class="btn btn-toggle-pass" type="button" id="togglePass" 
                            aria-label="Toggle password visibility">
                        <i class="bi bi-eye" id="togglePassIcon"></i>
                    </button>
                </div>
            </div>

            <div class="options-row">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" 
                           <?php echo isset($_COOKIE['remembered_user']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <a href="forgot-password.php">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-login">Sign In</button>
            
            <div class="divider">
                <span>or</span>
            </div>
            
            <a href="register.php" class="btn-register-link">Register as Officer</a>
        </form>
    </div>

    <?php if (isset($_GET['login']) && $_GET['login'] === 'failed' && !empty($_GET['message'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: "<?php echo htmlspecialchars($_GET['message']); ?>",
            confirmButtonColor: '#2367b7',
            confirmButtonText: 'Try Again'
        });
    </script>
    <?php endif; ?>
    
    <?php if (isset($_GET['status']) && $_GET['status'] === 'info' && !empty($_GET['message'])): ?>
    <script>
        Swal.fire({
            icon: 'info',
            title: 'Registration Successful',
            text: "<?php echo htmlspecialchars($_GET['message']); ?>",
            confirmButtonColor: '#2367b7',
            confirmButtonText: 'OK'
        });
    </script>
    <?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pass = document.getElementById('password');
    const toggle = document.getElementById('togglePass');
    const icon = document.getElementById('togglePassIcon');
    const form = document.getElementById('loginForm');
    const submitBtn = form.querySelector('button[type="submit"]');

    // Password visibility toggle
    toggle.addEventListener('click', function() {
        const isPassword = pass.type === 'password';
        pass.type = isPassword ? 'text' : 'password';
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
        toggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    });

    // Form validation and submit handling
    form.addEventListener('submit', function(e) {
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;

        // Basic validation
        if (!username || !password) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please enter both username and password',
                confirmButtonColor: '#2367b7'
            });
            return false;
        }

        // Disable submit button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in...';
    });

    // Enter key navigation for better UX
    document.getElementById('username').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            pass.focus();
        }
    });
});
</script>
</body>
</html>
