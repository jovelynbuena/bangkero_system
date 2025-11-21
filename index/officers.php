<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['username']) || $_SESSION['username'] === "") {
    header("Location: login.php");
    exit;
}

include('../config/db_connect.php');

// Fetch officers with member names and roles
$sql = "
    SELECT 
        officers.id,
        officer_roles.role_name AS position,
        officers.term_start,
        officers.term_end,
        officers.image,
        members.name AS member_name
    FROM officers
    JOIN members ON officers.member_id = members.id
    JOIN officer_roles ON officers.role_id = officer_roles.id
    ORDER BY 
        CASE 
            WHEN officer_roles.role_name = 'President' THEN 1
            WHEN officer_roles.role_name = 'Vice President' THEN 2
            ELSE 3
        END, officer_roles.role_name ASC
";
$result = $conn->query($sql);

$memberName = isset($_SESSION['member_name']) ? $_SESSION['member_name'] : 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Officers | Bankero & Fishermen Association</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
body { font-family: 'Segoe UI', sans-serif; background: #fdfdfd; }

.main-content { margin-left: 250px; padding: 32px; min-height: 100vh; }
.navbar { margin-left: 250px; background: #fff !important; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }

.hero-section { position: relative; background: url('../images/background.jpg') no-repeat center center; background-size: cover; height: 220px; border-radius: 18px; margin-bottom: 32px; display: flex; align-items: center; justify-content: center; text-align: center; overflow: hidden; }
.hero-section::after { content: ""; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(0,30,60,0.6) 0%, rgba(0,0,0,0.3) 100%); z-index: 1; }
.hero-logo { height: 70px; width: auto; display: block; }
.hero-text { position: relative; z-index: 2; }
.hero-text h1 { color: #fff; font-size: 2.4rem; font-weight: bold; margin: 0; text-shadow: 0 2px 8px rgba(0,0,0,0.25); }
.hero-text p { color: #e0e0e0; font-size: 1.1rem; font-weight: 500; margin: 0; text-shadow: 0 2px 8px rgba(0,0,0,0.20); }

.page-title { font-weight: 700; font-size: 2.1rem; margin: 18px 0 30px 0; text-align: left; }

/* Organizational chart style */
.org-chart { display: flex; flex-direction: column; align-items: center; gap: 30px; }
.org-top { display: flex; justify-content: center; gap: 20px; }
.org-row { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; }

.officer-card { border: 2px solid #00897b; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 14px rgba(2,136,209,0.08); transition: transform 0.2s; background-color: #f5f5f5; width: 220px; color: #4e342e; position: relative; }
.officer-card:hover { transform: translateY(-4px) scale(1.025); box-shadow: 0 8px 28px rgba(2,136,209,0.13); }
.officer-img { width: 100%; height: 180px; object-fit: cover; border-bottom: 1px solid #ddd; background: #f0f0f0; }
.officer-details { padding: 14px 10px 16px; text-align: center; }
.officer-details h5 { margin-bottom: 6px; font-weight: bold; font-size: 1.15rem; }
.position { color: #00897b; font-size: 1rem; font-weight: 600; margin-bottom: 3px; }
.term { font-size: 0.92rem; color: #555; }
.badge-role { position: absolute; top: 10px; left: 10px; background: #ff7043; color: #fff; padding: 5px 12px; border-radius: 8px 0 12px 0; font-size: 0.85rem; font-weight: 600; box-shadow: 0 2px 12px rgba(0,0,0,0.07); }

.footer { margin-top: 40px; padding: 16px 0 8px; background: transparent; text-align: center; }

@media (max-width: 991.98px) {
  .sidebar, .navbar { margin-left: 0 !important; }
  .main-content { margin-left: 0; padding: 16px; }
  .hero-section { border-radius: 10px; height: 140px; }
  .org-top, .org-row { flex-direction: column; align-items: center; }
}
</style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="main-content">
  <header class="hero-section mb-4">
    <img src="images/logo1.png" alt="Association Logo" class="hero-logo">
    <div class="hero-text">
      <h1>Bankero and Fishermen Association</h1>
      <p>Barangay Barretto, Olongapo City</p>
    </div>
  </header>

  <h2 class="page-title"><i class="bi bi-person-badge"></i> Meet Our Officers</h2>

  <div class="org-chart">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php
        $top_officers = [];
        $other_officers = [];

        while ($row = $result->fetch_assoc()) {
            if (strtolower($row['position']) === 'president') {
                $top_officers[] = $row;
            } else {
                $other_officers[] = $row;
            }
        }
        ?>
        <!-- Top Officers (President) -->
        <div class="org-top">
            <?php foreach($top_officers as $off): ?>
                <div class="officer-card">
                    <div class="badge-role"><?= ucwords(htmlspecialchars($off['position'])) ?></div>
                    <img src="<?= !empty($off['image']) ? "../uploads/officers/".htmlspecialchars($off['image']) : "https://via.placeholder.com/220x180?text=No+Image" ?>" class="officer-img" alt="Officer Image">
                    <div class="officer-details">
                        <h5><?= htmlspecialchars($off['member_name']) ?></h5>
                        <div class="term">
                            <?= ($off['term_start'] !== "0000-00-00") ? htmlspecialchars($off['term_start']) : 'N/A' ?> —
                            <?= ($off['term_end'] !== "0000-00-00") ? htmlspecialchars($off['term_end']) : 'N/A' ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Other Officers -->
        <div class="org-row">
            <?php foreach($other_officers as $off): ?>
                <div class="officer-card">
                    <div class="badge-role"><?= ucwords(htmlspecialchars($off['position'])) ?></div>
                    <img src="<?= !empty($off['image']) ? "../uploads/officers/".htmlspecialchars($off['image']) : "https://via.placeholder.com/220x180?text=No+Image" ?>" class="officer-img" alt="Officer Image">
                    <div class="officer-details">
                        <h5><?= htmlspecialchars($off['member_name']) ?></h5>
                        <div class="term">
                            <?= ($off['term_start'] !== "0000-00-00") ? htmlspecialchars($off['term_start']) : 'N/A' ?> —
                            <?= ($off['term_end'] !== "0000-00-00") ? htmlspecialchars($off['term_end']) : 'N/A' ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <div class="text-center text-muted">No officers have been assigned yet.</div>
    <?php endif; ?>
  </div>

  <footer class="footer">
    <small>&copy; <?= date("Y"); ?> Bankero & Fishermen Association. All rights reserved.</small>
  </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
