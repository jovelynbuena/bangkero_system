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
<title>Profile Settings</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  background: #f9f9f9;
}
.main-content {
  margin-left: 250px;
  padding: 40px;
  min-height: 100vh;
}
.settings-sidebar {
  border-right: 1px solid #e0e0e0;
}
.settings-sidebar .nav-link {
  color: #333;
  padding: 12px 16px;
  border-radius: 6px;
  margin-bottom: 4px;
  cursor: pointer;
}
.settings-sidebar .nav-link.active {
  background-color: #00897b;
  color: #fff;
}
.profile-card {
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
  padding: 30px;
}
.profile-avatar {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #00897b;
}
.upload-btn {
  background-color: #00897b;
  color: #fff;
}
.upload-btn:hover {
  background-color: #00695c;
}
.save-btn {
  background-color: #ff7043;
  border: none;
}
.save-btn:hover {
  background-color: #e64a19;
}
.strength-bar {
  height: 8px;
  border-radius: 5px;
  margin-top: 6px;
}
.strength-weak { background-color: #e53935; width: 33%; }
.strength-medium { background-color: #fb8c00; width: 66%; }
.strength-strong { background-color: #43a047; width: 100%; }
.password-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  color: #888;
}
.position-relative { position: relative; }
</style>
</head>
<body>
<?php include('../navbar.php'); ?>

<div class="main-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-3 settings-sidebar">
        <h5 class="mb-3 fw-bold">Account Settings</h5>
        <nav class="nav flex-column">
          <a class="nav-link <?php echo $active_tab=='profile'?'active':''; ?>" onclick="showSection('profile')">Profile Settings</a>
          <a class="nav-link <?php echo $active_tab=='password'?'active':''; ?>" onclick="showSection('password')">Password</a>
        </nav>
      </div>

      <div class="col-md-9">
        <div id="profile-section" class="profile-card" style="<?php echo $active_tab=='password'?'display:none;':''; ?>">
          <h4 class="fw-bold mb-4">Profile Settings</h4>
          <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Profile updated successfully!</div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_profile" value="1">
            <div class="text-center mb-4">
              <img src="<?php echo $user['avatar'] ? '../uploads/avatars/'.$user['avatar'] : 'https://via.placeholder.com/120'; ?>" class="profile-avatar mb-2">
              <div>
                <input type="file" name="avatar" class="form-control mt-2" style="max-width:300px;display:inline-block;">
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="form-control" required>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label">Mobile Number</label>
                <input type="text" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" class="form-control">
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label d-block">Gender</label>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="gender" value="Male" <?php echo ($user['gender']=='Male')?'checked':''; ?>>
                  <label class="form-check-label">Male</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="gender" value="Female" <?php echo ($user['gender']=='Female')?'checked':''; ?>>
                  <label class="form-check-label">Female</label>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Address</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" class="form-control">
              </div>
            </div>

            <div class="text-end">
              <button type="submit" class="btn save-btn px-4 py-2">Save Changes</button>
            </div>
          </form>
        </div>

        <div id="password-section" class="profile-card" style="<?php echo $active_tab=='profile'?'display:none;':''; ?>">
          <h4 class="fw-bold mb-4">Change Password</h4>

          <?php if ($pass_error): ?>
            <div class="alert alert-danger"><?php echo $pass_error; ?></div>
          <?php elseif ($pass_success): ?>
            <div class="alert alert-success"><?php echo $pass_success; ?></div>
          <?php endif; ?>

          <form method="POST">
            <input type="hidden" name="update_password" value="1">

            <div class="mb-3 position-relative">
              <label class="form-label">Current Password</label>
              <input type="password" name="current_password" class="form-control" required>
              <i class="bi bi-eye password-toggle" onclick="togglePassword(this)"></i>
            </div>

            <div class="mb-3 position-relative">
              <label class="form-label">New Password</label>
              <input type="password" id="new_password" name="new_password" class="form-control" required onkeyup="checkStrength()">
              <i class="bi bi-eye password-toggle" onclick="togglePassword(this)"></i>
              <div id="strength-bar" class="strength-bar bg-secondary" style="width:0;"></div>
            </div>

            <div class="mb-3 position-relative">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="confirm_password" class="form-control" required>
              <i class="bi bi-eye password-toggle" onclick="togglePassword(this)"></i>
            </div>

            <div class="text-end">
              <button type="submit" class="btn save-btn px-4 py-2">Save Password</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function showSection(section) {
  document.getElementById('profile-section').style.display = (section === 'profile') ? 'block' : 'none';
  document.getElementById('password-section').style.display = (section === 'password') ? 'block' : 'none';
  const links = document.querySelectorAll('.settings-sidebar .nav-link');
  links.forEach(l => l.classList.remove('active'));
  event.target.classList.add('active');
}

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

function checkStrength() {
  const pwd = document.getElementById('new_password').value;
  const bar = document.getElementById('strength-bar');
  let strength = 0;
  if (pwd.length >= 6) strength++;
  if (/[A-Z]/.test(pwd)) strength++;
  if (/[0-9]/.test(pwd)) strength++;
  if (/[^A-Za-z0-9]/.test(pwd)) strength++;

  if (strength <= 1) {
    bar.style.width = '33%';
    bar.className = 'strength-bar strength-weak';
  } else if (strength <= 3) {
    bar.style.width = '66%';
    bar.className = 'strength-bar strength-medium';
  } else {
    bar.style.width = '100%';
    bar.className = 'strength-bar strength-strong';
  }
}
</script>
</body>
</html>
