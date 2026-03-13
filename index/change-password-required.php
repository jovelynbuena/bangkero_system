<?php
/**
 * FORCE PASSWORD CHANGE PAGE
 * Users with force_password_change flag must change password before accessing system
 */

session_start();
require_once __DIR__ . '/../config/db_connect.php';

// Check if user is logged in and needs password change
if (!isset($_SESSION['user_id']) || !isset($_SESSION['force_password_change'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Hash new password
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password and clear force_password_change flag
        $stmt = $conn->prepare("UPDATE users 
                               SET password_hash = ?, 
                                   force_password_change = 0,
                                   temp_password = NULL 
                               WHERE id = ?");
        $stmt->bind_param("si", $password_hash, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            // Clear all session data
            session_destroy();
            
            $success = 'Password changed successfully! Please login with your new password...';
            
            // Redirect to login page after 2 seconds
            header("Refresh: 2; URL=login.php?login=success&message=" . urlencode("Password changed successfully. Please login with your new password."));
            exit();
        } else {
            $error = 'Failed to update password. Please try again.';
        }
    }
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password Required | Bankero System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .change-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 450px;
            width: 100%;
            text-align: center;
        }
        .icon-container {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 36px;
        }
        h2 {
            color: #1f2937;
            margin-bottom: 10px;
            font-size: 1.5rem;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }
        .alert {
            border-radius: 10px;
            text-align: left;
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            height: 50px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-change {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 14px;
            color: white;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        .btn-change:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .requirement-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
            font-size: 0.85rem;
            margin-bottom: 6px;
        }
        .requirement-item i {
            font-size: 0.8rem;
        }
        .user-info {
            background: #f3f4f6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .user-info strong {
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="change-card">
        <div class="icon-container">
            <i class="bi bi-shield-lock"></i>
        </div>
        
        <h2>Password Change Required</h2>
        <p class="subtitle">For security reasons, you must create a new password before continuing.</p>
        
        <?php if (!empty($user['first_name'])): ?>
        <div class="user-info">
            <i class="bi bi-person-circle me-2"></i>
            <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
        <form method="POST">
            <div class="mb-3 text-start">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" 
                       placeholder="Enter new password" required minlength="6" id="newPass">
            </div>
            
            <div class="mb-3 text-start">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" 
                       placeholder="Confirm new password" required minlength="6" id="confirmPass">
            </div>
            
            <div class="text-start mb-3">
                <div class="requirement-item">
                    <i class="bi bi-check-circle"></i>
                    <span>Minimum 6 characters</span>
                </div>
            </div>
            
            <button type="submit" class="btn btn-change">
                <i class="bi bi-check-lg me-2"></i>Change Password
            </button>
        </form>
        <?php endif; ?>
    </div>

    <script>
        // Simple password match validation
        document.getElementById('confirmPass').addEventListener('input', function() {
            const newPass = document.getElementById('newPass').value;
            const confirmPass = this.value;
            
            if (confirmPass && newPass !== confirmPass) {
                this.style.borderColor = '#ef4444';
            } else {
                this.style.borderColor = '#e5e7eb';
            }
        });
    </script>
</body>
</html>
