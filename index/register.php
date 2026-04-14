<?php
date_default_timezone_set('Asia/Manila');
session_start();
require '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Helper to redirect with error but keep input values
    function redirectWithError($msg, $username, $email) {
        $params = http_build_query([
            'status'   => 'error',
            'message'  => $msg,
            'username' => $username,
            'email'    => $email,
        ]);
        header("Location: register.php?" . $params);
        exit();
    }

    // Server-side validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        redirectWithError('All fields are required', $username, $email);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithError('Invalid email address', $username, $email);
    }

    if (strlen($password) < 6) {
        redirectWithError('Password must be at least 6 characters', $username, $email);
    }

    if ($password !== $confirm_password) {
        redirectWithError('Passwords do not match', $username, $email);
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        redirectWithError('Username or email already exists', $username, $email);
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
        header("Location: login.php?status=info&message=Registration successful. Please wait for administrator approval.");
        exit();
    } else {
        $insert->close();
        redirectWithError('Registration failed. Please try again', $username, $email);
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
            --card-bg: rgba(255, 255, 255, 0.18);
            --shadow: rgba(0, 0, 0, 0.15);
            --text-primary: #ffffff;
            --text-secondary: rgba(220, 240, 255, 0.88);
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
            background: #0d2d45;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            position: relative;
            min-height: 100vh;
        }

        /* Ocean background */
        .ocean-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .ocean-bg-img {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            object-fit: cover; object-position: center;
            filter: brightness(0.75) saturate(1.1);
        }

        /* Blue depth tint */
        .ocean-bg::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(5, 30, 60, 0.45) 0%,
                rgba(10, 60, 100, 0.30) 40%,
                rgba(5, 20, 45, 0.55) 100%
            );
            z-index: 1;
        }

        /* Caustic light rays */
        .ocean-bg::after {
            content: '';
            position: absolute; inset: 0;
            background: repeating-linear-gradient(
                105deg,
                transparent 0px,
                transparent 60px,
                rgba(120, 200, 255, 0.045) 60px,
                rgba(120, 200, 255, 0.045) 80px
            );
            z-index: 2;
            animation: causticShift 8s ease-in-out infinite alternate;
        }

        @keyframes causticShift {
            0%   { transform: skewX(0deg) translateX(0); opacity: 0.7; }
            100% { transform: skewX(2deg) translateX(30px); opacity: 1; }
        }

        .ocean-vignette {
            position: absolute; inset: 0;
            background: radial-gradient(ellipse at center, transparent 40%, rgba(0,10,30,0.55) 100%);
            z-index: 3;
            pointer-events: none;
        }

        .ocean-bottom {
            position: absolute; bottom: 0; left: 0; right: 0; height: 180px;
            background: linear-gradient(to top, rgba(0,10,25,0.65) 0%, transparent 100%);
            z-index: 3;
            pointer-events: none;
        }

        /* Bubbles */
        .bubbles { position: absolute; inset: 0; z-index: 4; pointer-events: none; overflow: hidden; }
        .bubble {
            position: absolute; bottom: -20px;
            width: 6px; height: 6px;
            background: rgba(180, 230, 255, 0.35);
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.25);
            animation: bubbleRise linear infinite;
        }
        .bubble:nth-child(1)  { left:  8%; width: 5px;  height: 5px;  animation-duration: 9s;  animation-delay: 0s; }
        .bubble:nth-child(2)  { left: 18%; width: 8px;  height: 8px;  animation-duration: 12s; animation-delay: 2s; }
        .bubble:nth-child(3)  { left: 30%; width: 4px;  height: 4px;  animation-duration: 8s;  animation-delay: 4s; }
        .bubble:nth-child(4)  { left: 42%; width: 7px;  height: 7px;  animation-duration: 11s; animation-delay: 1s; }
        .bubble:nth-child(5)  { left: 55%; width: 5px;  height: 5px;  animation-duration: 10s; animation-delay: 3s; }
        .bubble:nth-child(6)  { left: 65%; width: 9px;  height: 9px;  animation-duration: 13s; animation-delay: 0.5s; }
        .bubble:nth-child(7)  { left: 75%; width: 4px;  height: 4px;  animation-duration: 7s;  animation-delay: 5s; }
        .bubble:nth-child(8)  { left: 83%; width: 6px;  height: 6px;  animation-duration: 9s;  animation-delay: 2.5s; }
        .bubble:nth-child(9)  { left: 90%; width: 5px;  height: 5px;  animation-duration: 11s; animation-delay: 1.5s; }
        .bubble:nth-child(10) { left: 96%; width: 7px;  height: 7px;  animation-duration: 14s; animation-delay: 3.5s; }
        @keyframes bubbleRise {
            0%   { transform: translateY(0)   translateX(0);   opacity: 0; }
            10%  { opacity: 0.7; }
            90%  { opacity: 0.4; }
            100% { transform: translateY(-100vh) translateX(15px); opacity: 0; }
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
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.35), inset 0 0 0 1px rgba(255,255,255,0.25);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(18px) saturate(1.6);
            -webkit-backdrop-filter: blur(18px) saturate(1.6);
            position: relative;
            z-index: 10;
            animation: fadeInUp 0.6s ease-out;
            max-height: 95vh;
            overflow-y: auto;
        }
        
        .register-card::-webkit-scrollbar {
            width: 6px;
        }
        
        .register-card::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }
        
        .register-card::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
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
            color: rgba(200, 230, 255, 0.9);
            background: rgba(255, 255, 255, 0.1);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.15);
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
            border: 1.5px solid rgba(255, 255, 255, 0.35);
            font-size: 0.95rem;
            padding: 12px 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(120, 200, 255, 0.25);
            border-color: rgba(120, 200, 255, 0.7);
            outline: none;
            background: rgba(255, 255, 255, 0.22);
        }
        
        .form-control.is-invalid {
            border-color: rgba(239, 68, 68, 0.8);
        }
        
        .form-control.is-valid {
            border-color: rgba(16, 185, 129, 0.8);
        }
        
        .form-control::placeholder {
            color: rgba(200, 225, 255, 0.6);
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
            color: rgba(200, 225, 255, 0.8);
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
            color: #ffffff;
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
            background: rgba(255,255,255,0.2);
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
            color: rgba(200, 225, 255, 0.7);
            font-size: 0.85rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(255, 255, 255, 0.25);
        }
        
        .divider span {
            padding: 0 12px;
        }
        
        .back-link {
            color: rgba(160, 210, 255, 1);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            display: inline-block;
        }
        
        .back-link:hover {
            color: #ffffff;
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
    <!-- Coral ocean background -->
    <div class="ocean-bg">
        <img class="ocean-bg-img" src="images/coral_bg.jpg" alt="">
        <div class="ocean-vignette"></div>
        <div class="bubbles">
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
        </div>
        <div class="ocean-bottom"></div>
    </div>

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
                       placeholder="Choose a username" required
                       value="<?= htmlspecialchars($_GET['username'] ?? '') ?>">
                <div class="feedback-text" id="usernameFeedback"></div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input id="email" type="email" name="email" class="form-control" 
                       placeholder="your.email@example.com" required
                       value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
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

    // Trigger validation feedback for pre-filled fields (after error redirect)
    if (username.value.trim().length > 0) username.dispatchEvent(new Event('input'));
    if (email.value.trim().length > 0) email.dispatchEvent(new Event('input'));

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
