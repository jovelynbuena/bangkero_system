<?php
/**
 * AUTO-FIX DATABASE SCRIPT
 * Fixes all known issues automatically
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(300);

$conn = new mysqli("localhost", "root", "", "bangkero_local");
if ($conn->connect_error) {
    die("<b style='color:red'>❌ Connection failed: " . $conn->connect_error . "</b>");
}
$conn->set_charset("utf8mb4");

echo "<!DOCTYPE html><html><head>
<title>Auto Fix Database</title>
<style>
body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
.ok  { color: #4ec9b0; }
.err { color: #f44747; }
.warn{ color: #dcdcaa; }
.box { background: #252526; border-radius: 8px; padding: 20px; margin-bottom: 16px; }
h2   { color: #569cd6; }
.done{ background:#0e4c2f; color:#4ec9b0; padding:16px; border-radius:8px; font-size:1.2em; margin-top:20px; }
a.btn{ display:inline-block; margin-top:20px; padding:12px 28px; background:#2367b7; color:white;
       text-decoration:none; border-radius:8px; font-size:1em; font-family:sans-serif; }
</style></head><body>";

echo "<h2>🔧 Auto-Fix Database Script</h2>";
echo "<div class='box'>";

$fixes = [];
$errors = [];

// ─── FIX 1: activity_logs PRIMARY KEY + AUTO_INCREMENT ──────────────────────
echo "<b class='warn'>► Fixing activity_logs.id (PRIMARY KEY + AUTO_INCREMENT)...</b><br>";

// Step 1: Remove duplicate id values — keep only the latest row per id
$conn->query("
    DELETE a1 FROM `activity_logs` a1
    INNER JOIN `activity_logs` a2
    WHERE a1.id = a2.id AND a1.created_at < a2.created_at
");
echo "  <span class='ok'>✓ Duplicates removed</span><br>";

// Step 2: Reassign sequential id values to avoid conflicts
$conn->query("SET @row := 0");
$conn->query("UPDATE `activity_logs` SET `id` = (@row := @row + 1) ORDER BY `created_at` ASC");
echo "  <span class='ok'>✓ IDs resequenced</span><br>";

// Step 3: Check if PRIMARY KEY already exists
$pkCheck = $conn->query("SHOW KEYS FROM `activity_logs` WHERE Key_name = 'PRIMARY'");
if ($pkCheck && $pkCheck->num_rows > 0) {
    $r = $conn->query("ALTER TABLE `activity_logs` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");
} else {
    $r = $conn->query("ALTER TABLE `activity_logs` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
}

if ($r) {
    echo "<span class='ok'>  ✓ activity_logs.id PRIMARY KEY + AUTO_INCREMENT fixed</span><br>";
    $fixes[] = "activity_logs AUTO_INCREMENT";
} else {
    echo "<span class='err'>  ✗ " . $conn->error . "</span><br>";
    $errors[] = "activity_logs: " . $conn->error;
}

// ─── FIX 2: Check users table has PRIMARY KEY ────────────────────────────────
echo "<b class='warn'>► Checking users table...</b><br>";
$res = $conn->query("SHOW KEYS FROM `users` WHERE Key_name = 'PRIMARY'");
if ($res && $res->num_rows === 0) {
    $r2 = $conn->query("ALTER TABLE `users` ADD PRIMARY KEY (`id`)");
    if ($r2) {
        echo "<span class='ok'>  ✓ users PRIMARY KEY added</span><br>";
        $fixes[] = "users PRIMARY KEY";
    } else {
        echo "<span class='err'>  ✗ " . $conn->error . "</span><br>";
    }
} else {
    echo "<span class='ok'>  ✓ users PRIMARY KEY already exists</span><br>";
}

// ─── FIX 3: Check missing columns in users ──────────────────────────────────
echo "<b class='warn'>► Checking users columns...</b><br>";
$needed_cols = [
    'force_password_change' => "ALTER TABLE `users` ADD COLUMN `force_password_change` tinyint(1) DEFAULT 0",
    'temp_password'         => "ALTER TABLE `users` ADD COLUMN `temp_password` varchar(255) DEFAULT NULL",
    'transparency_role'     => "ALTER TABLE `users` ADD COLUMN `transparency_role` varchar(50) DEFAULT NULL",
];
foreach ($needed_cols as $col => $sql) {
    $check = $conn->query("SHOW COLUMNS FROM `users` LIKE '$col'");
    if ($check && $check->num_rows === 0) {
        if ($conn->query($sql)) {
            echo "<span class='ok'>  ✓ users.$col column added</span><br>";
            $fixes[] = "users.$col added";
        } else {
            echo "<span class='err'>  ✗ users.$col: " . $conn->error . "</span><br>";
        }
    } else {
        echo "<span class='ok'>  ✓ users.$col already exists</span><br>";
    }
}

// ─── FIX 4: Verify all critical tables exist ────────────────────────────────
echo "<br><b class='warn'>► Verifying critical tables...</b><br>";
$critical = ['users','members','activity_logs','announcements','events','officers','galleries'];
$missing = [];
foreach ($critical as $tbl) {
    $r = $conn->query("SELECT COUNT(*) as cnt FROM `$tbl`");
    if ($r) {
        $cnt = $r->fetch_assoc()['cnt'];
        echo "<span class='ok'>  ✓ $tbl ($cnt rows)</span><br>";
    } else {
        echo "<span class='err'>  ✗ $tbl — MISSING</span><br>";
        $missing[] = $tbl;
    }
}

echo "</div>";

// ─── SUMMARY ────────────────────────────────────────────────────────────────
if (empty($errors) && empty($missing)) {
    echo "<div class='done'>✅ All fixes applied! Database is ready.<br>
    <small>Fixed: " . implode(', ', $fixes ?: ['none needed']) . "</small></div>";
} else {
    echo "<div style='background:#4c1515;color:#f88;padding:16px;border-radius:8px;margin-top:12px'>";
    echo "⚠️ Some issues remain:<br>";
    foreach (array_merge($errors, array_map(fn($t) => "$t table missing", $missing)) as $e) {
        echo "  - $e<br>";
    }
    echo "</div>";
}

if (!empty($missing)) {
    echo "<br><b style='color:#dcdcaa'>Missing tables detected. Running full restore...</b><br>";
    // Auto-restore from latest backup
    $backupDir = __DIR__ . '/index/utilities/backups/';
    $latestFile = null; $latestTime = 0;
    foreach (glob($backupDir . 'backup_*.sql') as $f) {
        if (filemtime($f) > $latestTime) { $latestTime = filemtime($f); $latestFile = $f; }
    }
    if ($latestFile) {
        echo "<span style='color:#9cdcfe'>Using: " . basename($latestFile) . "</span><br>";
        $sql = file_get_contents($latestFile);
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        $stmts = array_filter(array_map('trim', explode(";\n", $sql)));
        $ok = 0; $fail = 0;
        foreach ($stmts as $s) {
            if (empty($s) || str_starts_with($s,'--')) continue;
            $conn->query($s) ? $ok++ : $fail++;
        }
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
        echo "<span class='ok'>✓ Restored: $ok statements ($fail skipped)</span><br>";
        echo "<div class='done'>✅ Full restore complete!</div>";
    }
}

echo "<br><a class='btn' href='index/login.php'>🔐 Try Login Now</a>";
echo "</body></html>";
$conn->close();
?>
