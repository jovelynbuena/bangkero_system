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
        :root{
          --primary:#2367b7;
          --card-bg: rgba(255,255,255,0.92);
        }
        html,body { height:100%; }
        body {
                       background: linear-gradient(135deg,#0fb8ad 0%, #1fc8db 50%, #2cb5e8 100%);

            font-family: 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
        }

        /* Compact, professional card */
        .register-card {
            width: 100%;
            max-width: 380px;          /* smaller width */
            background: var(--card-bg);
            padding: 20px;             /* less padding */
            border-radius: 12px;
            box-shadow: 0 8px 28px rgba(3,20,34,0.22);
            text-align: center;
            border: 1px solid rgba(255,255,255,0.28);
            backdrop-filter: blur(6px);
            font-size: 14px;
        }

        .register-card img {
            width: 56px;              /* smaller logo */
            height: 56px;
            border-radius: 10px;
            margin-bottom: 10px;
            object-fit:cover;
            box-shadow: 0 6px 14px rgba(0,0,0,0.10);
        }

        .register-card h3 {
            font-weight: 700;
            color: #07385a;
            margin: 0 0 6px 0;
            font-size: 1.05rem;       /* slightly smaller title */
        }

        .register-card p {
            margin-bottom: 12px;
            color: #2b4b60;
            opacity: .85;
            font-size: 0.94rem;
        }

        /* form controls slightly smaller and tighter */
        .form-control {
            border-radius: 8px;
            height: 40px;
            border:1px solid rgba(11,63,105,0.08);
            font-size: 0.95rem;
            padding: 6px 10px;
        }
        .mb-3 { margin-bottom: 10px !important; } /* tighter spacing */

        .input-group .form-control:focus {
            box-shadow: 0 0 0 0.12rem rgba(35,103,183,0.10);
            border-color: var(--primary);
        }

        .btn-register {
            background: linear-gradient(90deg,var(--primary), #1c5498);
            border: none;
            width: 100%;
            padding: 9px;
            height: 42px;
            border-radius: 8px;
            color: #fff;
            font-weight: 700;
            font-size: 0.95rem;
            box-shadow: 0 6px 12px rgba(11,63,105,0.10);
        }

        .options-row { display:flex; justify-content:space-between; align-items:center; margin-top:10px; font-size:0.88rem; }
        .options-row a { color:var(--primary); text-decoration:none; }
        .options-row a:hover { text-decoration:underline; }

        .btn-toggle-pass {
          background: transparent;
          border: none;
          color: #576b7a;
          font-size: 1rem;
          padding: 6px 8px;
        }
        .btn-toggle-pass:focus { outline: none; box-shadow:none; }

        @media (max-width:420px){
            .register-card { padding:16px; max-width: 92%; }
            .register-card img { width:50px; height:50px; }
            .form-control { height: 40px; }
            .btn-register { height: 42px; padding:8px; }
        }
    </style>
</head>
<body>
    <div class="register-card">
        <img src="images/logo1.png" alt="Logo">
        <h3>Officer Registration</h3>
        <p>Bankero & Fishermen Association</p>

        <form method="POST" action="" id="registerForm" class="mt-3" novalidate>
            <div class="mb-3 text-start">
                <label class="form-label small text-muted">Username</label>
                <input id="username" type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label small text-muted">Email</label>
                <input id="email" type="email" name="email" class="form-control" placeholder="Email Address" required>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label small text-muted">Password</label>
                <div class="input-group">
                    <input id="password" type="password" name="password" class="form-control" placeholder="Password" required aria-describedby="togglePass1">
                    <button class="btn btn-toggle-pass" type="button" id="togglePass1" aria-pressed="false" title="Show password">
                        <i class="bi bi-eye" id="togglePassIcon1" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label small text-muted">Confirm Password</label>
                <div class="input-group">
                    <input id="confirm_password" type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required aria-describedby="togglePass2">
                    <button class="btn btn-toggle-pass" type="button" id="togglePass2" aria-pressed="false" title="Show password">
                        <i class="bi bi-eye" id="togglePassIcon2" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-register mb-2">Register</button>
        </form>

        <div class="options-row">
            <div><a href="login.php">Back to Login</a></div>
            <div class="small text-muted">Accounts require admin approval</div>
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

<script>
document.addEventListener('DOMContentLoaded', function(){
  // Toggle password visibility (both fields)
  const pass1 = document.getElementById('password');
  const pass2 = document.getElementById('confirm_password');
  const toggle1 = document.getElementById('togglePass1');
  const toggle2 = document.getElementById('togglePass2');
  const icon1 = document.getElementById('togglePassIcon1');
  const icon2 = document.getElementById('togglePassIcon2');

  function bindToggle(input, toggleBtn, icon){
    toggleBtn.addEventListener('click', function(){
      const showing = input.type === 'text';
      input.type = showing ? 'password' : 'text';
      icon.classList.toggle('bi-eye');
      icon.classList.toggle('bi-eye-slash');
      toggleBtn.setAttribute('aria-pressed', (!showing).toString());
      toggleBtn.title = showing ? 'Show password' : 'Hide password';
    });
  }
  bindToggle(pass1, toggle1, icon1);
  bindToggle(pass2, toggle2, icon2);

  // usability: Enter on username -> focus email; Enter on email -> focus password
  document.getElementById('username').addEventListener('keydown', function(e){
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('email').focus(); }
  });
  document.getElementById('email').addEventListener('keydown', function(e){
    if (e.key === 'Enter') { e.preventDefault(); pass1.focus(); }
  });

  // prevent double submit, show feedback
  const form = document.getElementById('registerForm');
  form.addEventListener('submit', function(){
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Registering...';
  });
});
</script>
</body>
</html>
