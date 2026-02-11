<?php
date_default_timezone_set('Asia/Manila');
session_start();
require '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Server-side validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        header("Location: register.php?status=error&message=All fields are required");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register.php?status=error&message=Invalid email address");
        exit();
    }

    if (strlen($password) < 6) {
        header("Location: register.php?status=error&message=Password must be at least 6 characters");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: register.php?status=error&message=Passwords do not match");
        exit();
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: register.php?status=error&message=Username or email already exists");
        exit();
    }

    $stmt->close();

    // Create new user account
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'officer';
    $status = 'pending';
    $created_at = date('Y-m-d H:i:s');

    $insert = $conn->prepare("INSERT INTO users (username, password_hash, email, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $insert->bind_param("ssssss", $username, $hashed_password, $email, $role, $status, $created_at);

    if ($insert->execute()) {
        $insert->close();
        header("Location: login.php?status=info&message=Registration successful! Your account is pending admin approval");
        exit();
    } else {
        $insert->close();
        header("Location: register.php?status=error&message=Registration failed. Please try again");
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
        :root {
            --primary: #2367b7;
            --primary-dark: #1c5498;
            --card-bg: rgba(255, 255, 255, 0.95);
            --shadow: rgba(0, 0, 0, 0.15);
            --text-primary: #0b3f69;
            --text-secondary: #2b4b60;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            overflow-x: hidden;
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
        
        .register-card {
            width: 100%;
            max-width: 480px;
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
            max-height: 95vh;
            overflow-y: auto;
        }
        
        .register-card::-webkit-scrollbar {
            width: 6px;
        }
        
        .register-card::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .register-card::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .register-card img {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            margin-bottom: 12px;
            transition: transform 0.3s ease;
        }
        
        .register-card img:hover {
            transform: scale(1.05);
        }
        
        .register-card h3 {
            font-weight: 700;
            color: var(--text-primary);
            font-size: 1.4rem;
            margin: 0 0 6px 0;
        }
        
        .subtitle {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        
        .info-text {
            font-size: 0.85rem;
            color: var(--text-secondary);
            opacity: 0.85;
            background: rgba(35, 103, 183, 0.05);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        
        .form-control.is-invalid {
            border-color: var(--danger);
        }
        
        .form-control.is-valid {
            border-color: var(--success);
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
        
        .password-strength {
            margin-top: 8px;
            font-size: 0.85rem;
            text-align: left;
        }
        
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: #e5e7eb;
            margin-top: 6px;
            overflow: hidden;
        }
        
        .strength-bar-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        
        .strength-weak { background: var(--danger); width: 33%; }
        .strength-medium { background: var(--warning); width: 66%; }
        .strength-strong { background: var(--success); width: 100%; }
        
        .feedback-text {
            font-size: 0.8rem;
            margin-top: 4px;
            text-align: left;
        }
        
        .text-danger { color: var(--danger); }
        .text-success { color: var(--success); }
        .text-warning { color: var(--warning); }
        
        .btn-register {
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
            margin-top: 8px;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(35, 103, 183, 0.4);
        }
        
        .btn-register:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(35, 103, 183, 0.3);
        }
        
        .btn-register:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
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
        
        .back-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            display: inline-block;
        }
        
        .back-link:hover {
            color: var(--primary-dark);
            transform: translateX(-3px);
        }
        
        .mb-3 {
            margin-bottom: 18px !important;
        }
        
        @media (max-width: 480px) {
            .register-card {
                padding: 32px 24px;
                border-radius: 16px;
            }
            
            .register-card img {
                width: 60px;
                height: 60px;
            }
            
            .register-card h3 {
                font-size: 1.2rem;
            }
            
            .form-control {
                height: 46px;
            }
            
            .btn-register {
                height: 48px;
            }
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="logo-wrap">
            <img src="images/logo1.png" alt="Logo">
            <h3>Officer Registration</h3>
            <p class="subtitle">Bankero & Fishermen Association</p>
            <p class="info-text">
                <i class="bi bi-info-circle me-1"></i>
                Your account will be pending approval by an administrator
            </p>
        </div>

        <form method="POST" action="" id="registerForm" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input id="username" type="text" name="username" class="form-control" 
                       placeholder="Choose a username" required>
                <div class="feedback-text" id="usernameFeedback"></div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input id="email" type="email" name="email" class="form-control" 
                       placeholder="your.email@example.com" required>
                <div class="feedback-text" id="emailFeedback"></div>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input id="password" type="password" name="password" class="form-control" 
                           placeholder="Create a strong password" required>
                    <button class="btn btn-toggle-pass" type="button" id="togglePass1" 
                            aria-label="Toggle password visibility">
                        <i class="bi bi-eye" id="togglePassIcon1"></i>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-bar-fill" id="strengthBar"></div>
                    </div>
                    <div class="feedback-text" id="passwordFeedback"></div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <input id="confirm_password" type="password" name="confirm_password" 
                           class="form-control" placeholder="Re-enter your password" required>
                    <button class="btn btn-toggle-pass" type="button" id="togglePass2" 
                            aria-label="Toggle password visibility">
                        <i class="bi bi-eye" id="togglePassIcon2"></i>
                    </button>
                </div>
                <div class="feedback-text" id="confirmFeedback"></div>
            </div>

            <button type="submit" class="btn-register">Create Account</button>
        </form>

        <div class="divider">
            <span>Already have an account?</span>
        </div>

        <a href="login.php" class="back-link">
            <i class="bi bi-arrow-left me-2"></i>Back to Login
        </a>
    </div>

    <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
    <script>
        Swal.fire({
            icon: '<?php echo htmlspecialchars($_GET['status']); ?>',
            title: '<?php echo $_GET['status'] === 'error' ? 'Registration Failed' : 'Notice'; ?>',
            text: "<?php echo htmlspecialchars($_GET['message']); ?>",
            confirmButtonColor: '#2367b7',
            confirmButtonText: 'OK'
        });
    </script>
    <?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const username = document.getElementById('username');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Feedback elements
    const usernameFeedback = document.getElementById('usernameFeedback');
    const emailFeedback = document.getElementById('emailFeedback');
    const passwordFeedback = document.getElementById('passwordFeedback');
    const confirmFeedback = document.getElementById('confirmFeedback');
    const strengthBar = document.getElementById('strengthBar');

    // Password toggle functionality
    function setupPasswordToggle(inputId, toggleId, iconId) {
        const input = document.getElementById(inputId);
        const toggle = document.getElementById(toggleId);
        const icon = document.getElementById(iconId);
        
        toggle.addEventListener('click', function() {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
    }
    
    setupPasswordToggle('password', 'togglePass1', 'togglePassIcon1');
    setupPasswordToggle('confirm_password', 'togglePass2', 'togglePassIcon2');

    // Username validation
    username.addEventListener('input', function() {
        const value = this.value.trim();
        if (value.length === 0) {
            usernameFeedback.textContent = '';
            username.classList.remove('is-valid', 'is-invalid');
        } else if (value.length < 3) {
            usernameFeedback.textContent = 'Username must be at least 3 characters';
            usernameFeedback.className = 'feedback-text text-danger';
            username.classList.add('is-invalid');
            username.classList.remove('is-valid');
        } else {
            usernameFeedback.textContent = 'Username looks good!';
            usernameFeedback.className = 'feedback-text text-success';
            username.classList.add('is-valid');
            username.classList.remove('is-invalid');
        }
    });

    // Email validation
    email.addEventListener('input', function() {
        const value = this.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (value.length === 0) {
            emailFeedback.textContent = '';
            email.classList.remove('is-valid', 'is-invalid');
        } else if (!emailRegex.test(value)) {
            emailFeedback.textContent = 'Please enter a valid email address';
            emailFeedback.className = 'feedback-text text-danger';
            email.classList.add('is-invalid');
            email.classList.remove('is-valid');
        } else {
            emailFeedback.textContent = 'Email looks good!';
            emailFeedback.className = 'feedback-text text-success';
            email.classList.add('is-valid');
            email.classList.remove('is-invalid');
        }
    });

    // Password strength checker
    password.addEventListener('input', function() {
        const value = this.value;
        let strength = 0;
        
        if (value.length === 0) {
            strengthBar.className = 'strength-bar-fill';
            strengthBar.style.width = '0%';
            passwordFeedback.textContent = '';
            password.classList.remove('is-valid', 'is-invalid');
            return;
        }
        
        // Check password strength
        if (value.length >= 6) strength++;
        if (value.length >= 10) strength++;
        if (/[a-z]/.test(value) && /[A-Z]/.test(value)) strength++;
        if (/[0-9]/.test(value)) strength++;
        if (/[^a-zA-Z0-9]/.test(value)) strength++;
        
        // Update strength bar and feedback
        strengthBar.className = 'strength-bar-fill';
        if (strength <= 2) {
            strengthBar.classList.add('strength-weak');
            passwordFeedback.textContent = 'Weak password';
            passwordFeedback.className = 'feedback-text text-danger';
            password.classList.add('is-invalid');
            password.classList.remove('is-valid');
        } else if (strength <= 3) {
            strengthBar.classList.add('strength-medium');
            passwordFeedback.textContent = 'Medium strength password';
            passwordFeedback.className = 'feedback-text text-warning';
            password.classList.remove('is-invalid', 'is-valid');
        } else {
            strengthBar.classList.add('strength-strong');
            passwordFeedback.textContent = 'Strong password!';
            passwordFeedback.className = 'feedback-text text-success';
            password.classList.add('is-valid');
            password.classList.remove('is-invalid');
        }
        
        // Check confirm password match
        if (confirmPassword.value.length > 0) {
            checkPasswordMatch();
        }
    });

    // Confirm password validation
    function checkPasswordMatch() {
        const value = confirmPassword.value;
        
        if (value.length === 0) {
            confirmFeedback.textContent = '';
            confirmPassword.classList.remove('is-valid', 'is-invalid');
        } else if (value !== password.value) {
            confirmFeedback.textContent = 'Passwords do not match';
            confirmFeedback.className = 'feedback-text text-danger';
            confirmPassword.classList.add('is-invalid');
            confirmPassword.classList.remove('is-valid');
        } else {
            confirmFeedback.textContent = 'Passwords match!';
            confirmFeedback.className = 'feedback-text text-success';
            confirmPassword.classList.add('is-valid');
            confirmPassword.classList.remove('is-invalid');
        }
    }
    
    confirmPassword.addEventListener('input', checkPasswordMatch);

    // Form submission validation
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const usernameValue = username.value.trim();
        const emailValue = email.value.trim();
        const passwordValue = password.value;
        const confirmValue = confirmPassword.value;
        
        // Validate all fields
        if (!usernameValue || !emailValue || !passwordValue || !confirmValue) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please fill in all required fields',
                confirmButtonColor: '#2367b7'
            });
            return false;
        }
        
        if (usernameValue.length < 3) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Username',
                text: 'Username must be at least 3 characters long',
                confirmButtonColor: '#2367b7'
            });
            username.focus();
            return false;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailValue)) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Email',
                text: 'Please enter a valid email address',
                confirmButtonColor: '#2367b7'
            });
            email.focus();
            return false;
        }
        
        if (passwordValue.length < 6) {
            Swal.fire({
                icon: 'warning',
                title: 'Weak Password',
                text: 'Password must be at least 6 characters long',
                confirmButtonColor: '#2367b7'
            });
            password.focus();
            return false;
        }
        
        if (passwordValue !== confirmValue) {
            Swal.fire({
                icon: 'warning',
                title: 'Password Mismatch',
                text: 'Passwords do not match',
                confirmButtonColor: '#2367b7'
            });
            confirmPassword.focus();
            return false;
        }
        
        // Disable submit button and show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Account...';
        
        // Submit the form
        form.submit();
    });

    // Enter key navigation
    username.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            email.focus();
        }
    });
    
    email.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            password.focus();
        }
    });
    
    password.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            confirmPassword.focus();
        }
    });
});
</script>
</body>
</html>
