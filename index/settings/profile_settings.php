<?php
session_start();
include('../../config/db_connect.php');

if (!isset($_SESSION['username'])) {
  header('location: login.php');
  exit;
}

$username = $_SESSION['username'];

// Fetch user info
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$active_tab = 'profile'; // default tab
$pass_error = $pass_success = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $first = $_POST['first_name'];
  $last = $_POST['last_name'];
  $email = $_POST['email'];
  $mobile = $_POST['mobile'];
  $gender = $_POST['gender'];
  $address = $_POST['address'];

  // Avatar upload
  $avatar = $user['avatar'];
  if (!empty($_FILES['avatar']['name'])) {
    $target_dir = "../uploads/avatars/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $file_name = time() . "_" . basename($_FILES["avatar"]["name"]);
    $target_file = $target_dir . $file_name;
    move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file);
    $avatar = $file_name;
  }

  $update = "UPDATE users SET first_name=?, last_name=?, email=?, mobile=?, gender=?, address=?, avatar=? WHERE username=?";
  $stmt = $conn->prepare($update);
  $stmt->bind_param("ssssssss", $first, $last, $email, $mobile, $gender, $address, $avatar, $username);
  $stmt->execute();
  header("Location: profile_settings.php?success=1");
  exit;
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
  // Refetch user to ensure latest password hash
  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();

  $current = $_POST['current_password'];
  $new = $_POST['new_password'];
  $confirm = $_POST['confirm_password'];

  $active_tab = 'password'; // stay in password tab

  if (!password_verify(trim($current), $user['password_hash'])) {
    $pass_error = "Current password is incorrect.";
  } elseif ($new !== $confirm) {
    $pass_error = "New passwords do not match.";
  } else {
    $hashed = password_hash($new, PASSWORD_DEFAULT);
    $update = "UPDATE users SET password_hash=? WHERE username=?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ss", $hashed, $username);
    $stmt->execute();
    $pass_success = "Password updated successfully!";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile Settings | Bangkero & Fishermen Association</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {
  font-family: 'Inter', sans-serif;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 100vh;
}
.main-content {
  margin-left: 250px;
  padding: 32px;
  min-height: 100vh;
}

.page-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  padding: 30px;
  margin-bottom: 30px;
  box-shadow: 0 8px 32px rgba(102, 126, 234, 0.2);
  color: white;
}

.page-header h2 {
  font-weight: 700;
  margin: 0 0 10px 0;
  font-size: 2rem;
}

.page-header p {
  margin: 0;
  opacity: 0.95;
  font-size: 1.05rem;
}

.settings-sidebar {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  height: fit-content;
  position: sticky;
  top: 32px;
}

.settings-sidebar h5 {
  color: #1f2937;
  font-weight: 700;
  margin-bottom: 20px;
  font-size: 1.1rem;
}

.settings-sidebar .nav-link {
  color: #4b5563;
  padding: 12px 16px;
  border-radius: 10px;
  margin-bottom: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  font-weight: 500;
}

.settings-sidebar .nav-link i {
  margin-right: 10px;
  font-size: 1.2rem;
}

.settings-sidebar .nav-link:hover {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  color: #667eea;
  transform: translateX(5px);
}

.settings-sidebar .nav-link.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: #fff;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.profile-card {
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  padding: 32px;
  margin-bottom: 24px;
}

.profile-card h4 {
  color: #1f2937;
  font-weight: 700;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
}

.profile-card h4 i {
  margin-right: 12px;
  color: #667eea;
}

.avatar-container {
  position: relative;
  width: 140px;
  height: 140px;
  margin: 0 auto 24px;
}

.profile-avatar {
  width: 140px;
  height: 140px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.avatar-upload-btn {
  position: absolute;
  bottom: 5px;
  right: 5px;
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
  transition: all 0.3s ease;
}

.avatar-upload-btn:hover {
  transform: scale(1.1);
}

.avatar-upload-btn input[type="file"] {
  position: absolute;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.form-label {
  font-weight: 600;
  color: #374151;
  margin-bottom: 8px;
  font-size: 0.9rem;
}

.form-control, .form-select {
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  padding: 12px 16px;
  transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  outline: none;
}

.save-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 10px;
  padding: 12px 32px;
  color: white;
  font-weight: 600;
  transition: all 0.3s ease;
}

.save-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.info-badge {
  display: inline-block;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  margin-bottom: 8px;
}

.strength-bar {
  height: 8px;
  border-radius: 5px;
  margin-top: 8px;
  transition: all 0.3s ease;
}
.strength-weak { background-color: #ef4444; width: 33%; }
.strength-medium { background-color: #f59e0b; width: 66%; }
.strength-strong { background-color: #10b981; width: 100%; }

.password-toggle {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  color: #9ca3af;
  transition: all 0.3s ease;
}

.password-toggle:hover {
  color: #667eea;
}

.position-relative { 
  position: relative; 
}

.username-display {
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  border: 2px solid #667eea;
  border-radius: 10px;
  padding: 12px 16px;
  color: #667eea;
  font-weight: 600;
  display: flex;
  align-items: center;
}

.username-display i {
  margin-right: 8px;
}

.password-requirements {
  background: #f3f4f6;
  border-radius: 10px;
  padding: 16px;
  margin-top: 12px;
  font-size: 0.85rem;
}

.password-requirements ul {
  margin: 8px 0 0 0;
  padding-left: 20px;
}

.password-requirements li {
  color: #6b7280;
  margin-bottom: 4px;
}

@media (max-width: 991.98px) {
  .main-content {
    margin-left: 0;
    padding: 16px;
  }
  .settings-sidebar {
    position: static;
    margin-bottom: 24px;
  }
}
</style>
</head>
<body>
<?php include('../navbar.php'); ?>


<div class="main-content">
  <!-- Page Header -->
  <div class="page-header">
    <h2><i class="bi bi-person-circle me-2"></i>Profile Settings</h2>
    <p>Manage your account information and security settings</p>
  </div>

  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-3">
        <div class="settings-sidebar">
          <h5><i class="bi bi-gear-fill me-2"></i>Settings</h5>
          <nav class="nav flex-column">
            <a class="nav-link <?php echo $active_tab=='profile'?'active':''; ?>" onclick="showSection('profile')">
              <i class="bi bi-person-fill"></i> Profile Information
            </a>
            <a class="nav-link <?php echo $active_tab=='password'?'active':''; ?>" onclick="showSection('password')">
              <i class="bi bi-shield-lock-fill"></i> Security & Password
            </a>
          </nav>
        </div>
      </div>

      <div class="col-lg-9">
        <!-- Profile Section -->
        <div id="profile-section" class="profile-card" style="<?php echo $active_tab=='password'?'display:none;':''; ?>">
          <h4><i class="bi bi-person-badge-fill"></i>Profile Information</h4>

          <form method="POST" enctype="multipart/form-data" id="profileForm">
            <input type="hidden" name="update_profile" value="1">
            
            <!-- Avatar Upload -->
            <div class="text-center mb-4">
              <div class="avatar-container">
                <img src="<?php echo $user['avatar'] ? '../uploads/avatars/'.$user['avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($user['first_name'].' '.$user['last_name']).'&size=140&background=667eea&color=fff'; ?>" 
                     class="profile-avatar" 
                     id="avatarPreview"
                     alt="Profile Avatar">
                <div class="avatar-upload-btn" title="Change Avatar">
                  <i class="bi bi-camera-fill"></i>
                  <input type="file" name="avatar" accept="image/*" onchange="previewAvatar(this)">
                </div>
              </div>
              <h5 class="mt-2"><?php echo htmlspecialchars($user['first_name'].' '.$user['last_name']); ?></h5>
              <span class="info-badge"><?php echo htmlspecialchars($user['role'] ?: 'Admin'); ?></span>
            </div>

            <!-- Username (Read-only) -->
            <div class="row mb-3">
              <div class="col-12">
                <label class="form-label"><i class="bi bi-person-circle me-1"></i> Username</label>
                <div class="username-display">
                  <i class="bi bi-lock-fill"></i>
                  <?php echo htmlspecialchars($user['username']); ?>
                  <span class="ms-2 text-muted" style="font-size:0.8rem;font-weight:400;">(Cannot be changed)</span>
                </div>
              </div>
            </div>

            <!-- Name Fields -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label"><i class="bi bi-person me-1"></i> First Name</label>
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label"><i class="bi bi-person me-1"></i> Last Name</label>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="form-control" required>
              </div>
            </div>

            <!-- Contact Fields -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label"><i class="bi bi-envelope me-1"></i> Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" placeholder="your@email.com">
              </div>
              <div class="col-md-6">
                <label class="form-label"><i class="bi bi-phone me-1"></i> Mobile Number</label>
                <input type="text" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" class="form-control" placeholder="+63 XXX XXX XXXX">
              </div>
            </div>

            <!-- Gender & Address -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label d-block"><i class="bi bi-gender-ambiguous me-1"></i> Gender</label>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="gender" id="male" value="Male" <?php echo ($user['gender']=='Male')?'checked':''; ?>>
                  <label class="form-check-label" for="male">Male</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="gender" id="female" value="Female" <?php echo ($user['gender']=='Female')?'checked':''; ?>>
                  <label class="form-check-label" for="female">Female</label>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label"><i class="bi bi-geo-alt me-1"></i> Address</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" class="form-control" placeholder="Your complete address">
              </div>
            </div>

            <div class="text-end">
              <button type="submit" class="btn save-btn">
                <i class="bi bi-check-circle me-2"></i>Save Changes
              </button>
            </div>
          </form>
        </div>

        <!-- Password Section -->
        <div id="password-section" class="profile-card" style="<?php echo $active_tab=='profile'?'display:none;':''; ?>">
          <h4><i class="bi bi-shield-lock-fill"></i>Change Password</h4>

          <div class="password-requirements">
            <strong><i class="bi bi-info-circle me-2"></i>Password Requirements:</strong>
            <ul>
              <li>At least 6 characters long</li>
              <li>Include uppercase letters (A-Z)</li>
              <li>Include numbers (0-9)</li>
              <li>Include special characters (!@#$%)</li>
            </ul>
          </div>

          <form method="POST" id="passwordForm" class="mt-4">
            <input type="hidden" name="update_password" value="1">

            <div class="mb-3 position-relative">
              <label class="form-label"><i class="bi bi-key me-1"></i> Current Password</label>
              <input type="password" name="current_password" class="form-control" required placeholder="Enter your current password">
              <i class="bi bi-eye password-toggle" onclick="togglePassword(this)"></i>
            </div>

            <div class="mb-3 position-relative">
              <label class="form-label"><i class="bi bi-key-fill me-1"></i> New Password</label>
              <input type="password" id="new_password" name="new_password" class="form-control" required onkeyup="checkStrength()" placeholder="Enter new password">
              <i class="bi bi-eye password-toggle" onclick="togglePassword(this)"></i>
              <div id="strength-bar" class="strength-bar bg-secondary" style="width:0;"></div>
              <small id="strength-text" class="text-muted"></small>
            </div>

            <div class="mb-3 position-relative">
              <label class="form-label"><i class="bi bi-check2-circle me-1"></i> Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control" required placeholder="Re-enter new password">
              <i class="bi bi-eye password-toggle" onclick="togglePassword(this)"></i>
            </div>

            <div class="text-end">
              <button type="submit" class="btn save-btn">
                <i class="bi bi-shield-check me-2"></i>Update Password
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Preview avatar before upload
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('avatarPreview').src = e.target.result;
    }
    reader.readAsDataURL(input.files[0]);
  }
}

// Show/hide sections
function showSection(section) {
  document.getElementById('profile-section').style.display = (section === 'profile') ? 'block' : 'none';
  document.getElementById('password-section').style.display = (section === 'password') ? 'block' : 'none';
  const links = document.querySelectorAll('.settings-sidebar .nav-link');
  links.forEach(l => l.classList.remove('active'));
  event.target.classList.add('active');
}

// Toggle password visibility
function togglePassword(icon) {
  const input = icon.previousElementSibling;
  if (input.type === "password") {
    input.type = "text";
    icon.classList.replace('bi-eye', 'bi-eye-slash');
  } else {
    input.type = "password";
    icon.classList.replace('bi-eye-slash', 'bi-eye');
  }
}

// Check password strength
function checkStrength() {
  const pwd = document.getElementById('new_password').value;
  const bar = document.getElementById('strength-bar');
  const text = document.getElementById('strength-text');
  let strength = 0;
  
  if (pwd.length >= 6) strength++;
  if (/[A-Z]/.test(pwd)) strength++;
  if (/[0-9]/.test(pwd)) strength++;
  if (/[^A-Za-z0-9]/.test(pwd)) strength++;

  if (strength <= 1) {
    bar.className = 'strength-bar strength-weak';
    text.textContent = 'Weak password';
    text.style.color = '#ef4444';
  } else if (strength <= 3) {
    bar.className = 'strength-bar strength-medium';
    text.textContent = 'Medium strength';
    text.style.color = '#f59e0b';
  } else {
    bar.className = 'strength-bar strength-strong';
    text.textContent = 'Strong password!';
    text.style.color = '#10b981';
  }
}

// Success message with SweetAlert
<?php if (isset($_GET['success'])): ?>
Swal.fire({
  icon: 'success',
  title: 'Profile Updated!',
  text: 'Your profile information has been updated successfully.',
  timer: 2500,
  showConfirmButton: false,
  toast: true,
  position: 'top-end'
});
<?php endif; ?>

// Password update messages
<?php if ($pass_error): ?>
Swal.fire({
  icon: 'error',
  title: 'Error',
  text: '<?php echo addslashes($pass_error); ?>',
  confirmButtonColor: '#667eea'
});
<?php elseif ($pass_success): ?>
Swal.fire({
  icon: 'success',
  title: 'Password Updated!',
  text: 'Your password has been changed successfully.',
  timer: 2500,
  showConfirmButton: false,
  toast: true,
  position: 'top-end'
});
<?php endif; ?>

// Form validation
document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
  const newPass = document.querySelector('input[name="new_password"]').value;
  const confirmPass = document.querySelector('input[name="confirm_password"]').value;
  
  if (newPass !== confirmPass) {
    e.preventDefault();
    Swal.fire({
      icon: 'error',
      title: 'Passwords do not match',
      text: 'Please make sure both password fields match.',
      confirmButtonColor: '#667eea'
    });
    return false;
  }
  
  if (newPass.length < 6) {
    e.preventDefault();
    Swal.fire({
      icon: 'warning',
      title: 'Password too short',
      text: 'Password must be at least 6 characters long.',
      confirmButtonColor: '#667eea'
    });
    return false;
  }
});
</script>
</body>
</html>
