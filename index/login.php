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
            
            // ✅ Use username as display name for now
            // Extract first part of username (before @ if email, or full username)
            $displayName = $row["username"];
            if (strpos($displayName, '@') !== false) {
                $displayName = explode('@', $displayName)[0];
            }
            // Capitalize first letter
            $_SESSION["fullname"] = ucfirst($displayName);

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
        :root{
          --primary:#2367b7;
          --card-bg: rgba(255,255,255,0.85);
          --glass: rgba(255,255,255,0.18);
        }
        html,body { height:100%; }
        body {
            background: linear-gradient(135deg,#0fb8ad 0%, #1fc8db 50%, #2cb5e8 100%);
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: var(--card-bg);
            padding: 28px;
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.18);
            text-align: center;
            border: 1px solid rgba(255,255,255,0.35);
        }
        .login-card .logo-wrap {
            display:flex; align-items:center; gap:12px; justify-content:center; margin-bottom:10px;
        }
        .login-card img {
            width:64px; height:64px; border-radius:10px; object-fit:cover;
            box-shadow: 0 6px 14px rgba(0,0,0,0.12);
        }
        .login-card h3 { font-weight:700; margin:0; color: #0b3f69; }
        .login-card p { margin:0 0 16px 0; color:#08324b; opacity:0.9; }
        .form-control {
            border-radius:10px;
            height:44px;
            box-shadow:none;
            border:1px solid rgba(11,63,105,0.08);
        }
        .input-group .form-control:focus { box-shadow: 0 0 0 0.15rem rgba(35,103,183,0.12); border-color: var(--primary); }
        .btn-login {
            background: linear-gradient(90deg,var(--primary), #1c5498);
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 10px;
            font-weight: 700;
            color: #fff;
            transition: transform .08s ease, box-shadow .08s ease;
            box-shadow: 0 6px 14px rgba(11,63,105,0.12);
        }
        .btn-login:active { transform: translateY(1px); box-shadow: 0 4px 10px rgba(11,63,105,0.12); }
        .options-row { display:flex; justify-content:space-between; align-items:center; margin:12px 0; font-size:0.95rem; }
        .options-row a { color:var(--primary); text-decoration:none; }
        .options-row a:hover { text-decoration:underline; }
        .remember-label { user-select:none; }
        .small-muted { font-size:.9rem; color:#2b4b60; opacity:.8; margin-top:10px; }

        /* password toggle button */
        .btn-toggle-pass {
          background: transparent;
          border: none;
          color: #576b7a;
          font-size: 1.05rem;
        }
        .btn-toggle-pass:focus { outline: none; box-shadow:none; }

        @media (max-width:420px){
            .login-card { padding:18px; border-radius:12px; }
            .login-card img { width:56px; height:56px; }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-wrap">
            <img src="images/logo1.png" alt="Association Logo" class="hero-logo">
            <div style="text-align:left;">
              <h3>Bankero & Fishermen</h3>
              <div class="small-muted">Barangay Barretto, Olongapo City</div>
            </div>
        </div>

        <form method="POST" action="" id="loginForm" class="mt-3">
            <div class="mb-3">
                <input id="username" type="text" name="username" class="form-control" placeholder="Username"
                    value="<?php echo isset($_COOKIE['remembered_user']) ? htmlspecialchars($_COOKIE['remembered_user']) : ''; ?>" required autofocus>
            </div>

            <div class="mb-3">
                <label class="visually-hidden" for="password">Password</label>
                <div class="input-group">
                    <input id="password" type="password" name="password" class="form-control" placeholder="Password" required aria-describedby="togglePass">
                    <button class="btn btn-toggle-pass" type="button" id="togglePass" aria-pressed="false" title="Show password">
                        <i class="bi bi-eye" id="togglePassIcon" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="options-row">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" <?php echo isset($_COOKIE['remembered_user']) ? 'checked' : ''; ?>>
                    <label class="form-check-label remember-label" for="remember">Remember me</label>
                </div>
                <a href="forgot-password.php">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-login mb-2">Login</button>

            <a href="register.php" class="btn btn-outline-light w-100 fw-bold" style="border-radius: 10px; border:1px solid rgba(255,255,255,0.18); color:#07385a;">Register as Officer</a>
        </form>

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
    </div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const pass = document.getElementById('password');
  const toggle = document.getElementById('togglePass');
  const icon = document.getElementById('togglePassIcon');

  toggle.addEventListener('click', function(){
    const showing = pass.type === 'text';
    pass.type = showing ? 'password' : 'text';
    icon.classList.toggle('bi-eye');
    icon.classList.toggle('bi-eye-slash');
    toggle.setAttribute('aria-pressed', (!showing).toString());
    toggle.title = showing ? 'Show password' : 'Hide password';
  });

  // Enter key on username focuses password for quicker login
  document.getElementById('username').addEventListener('keydown', function(e){
    if (e.key === 'Enter') {
      e.preventDefault();
      pass.focus();
    }
  });

  // small improvement: disable submit button briefly on submit to avoid double-post
  const form = document.getElementById('loginForm');
  form.addEventListener('submit', function(){
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Signing in...';
  });
});
</script>
</body>
</html>
