<?php
// ============================================
// DATABASE CONNECTION - LOCAL VERSION
// For offline/defense day backup
// ============================================

$servername = "localhost";
$username   = "root";
$password   = "";  // XAMPP default (blank)
$dbname     = "bangkero_local";
$port       = 3306;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Local database connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

/**
 * One-time bootstrap for local DB
 * If the local database is empty/missing core tables (e.g., members), auto-restore
 * from the latest SQL file under index/utilities/backups/.
 */
function local_table_exists(mysqli $conn, string $table): bool {
    $safe = $conn->real_escape_string($table);
    $res = $conn->query("SHOW TABLES LIKE '{$safe}'");
    return $res && $res->num_rows > 0;
}

function local_find_latest_backup_sql(): ?string {
    $backupDir = realpath(__DIR__ . '/../index/utilities/backups');
    if (!$backupDir || !is_dir($backupDir)) return null;

    $latest = null;
    $latestTime = 0;

    foreach (glob($backupDir . '/*.sql') ?: [] as $file) {
        $t = @filemtime($file);
        if ($t && $t > $latestTime) {
            $latestTime = $t;
            $latest = $file;
        }
    }

    return $latest;
}

function local_restore_from_sql_file(mysqli $conn, string $filePath): void {
    @set_time_limit(0);

    $sql = @file_get_contents($filePath);
    if ($sql === false || trim($sql) === '') {
        throw new RuntimeException('Backup SQL file is empty/unreadable.');
    }

    // Disable FK checks during restore
    $conn->query('SET FOREIGN_KEY_CHECKS=0');

    if (!$conn->multi_query($sql)) {
        $conn->query('SET FOREIGN_KEY_CHECKS=1');
        throw new RuntimeException('Restore failed to start: ' . $conn->error);
    }

    // Flush all results
    do {
        if ($res = $conn->store_result()) {
            $res->free();
        }
        if ($conn->more_results()) {
            $conn->next_result();
        }
    } while ($conn->more_results());

    // Re-enable FK checks
    $conn->query('SET FOREIGN_KEY_CHECKS=1');

    if ($conn->errno) {
        throw new RuntimeException('Restore finished with error: ' . $conn->error);
    }
}

// If DB looks empty, try to bootstrap once (members table is a good indicator)
try {
    if (!local_table_exists($conn, 'members')) {
        $latestSql = local_find_latest_backup_sql();
        if ($latestSql) {
            local_restore_from_sql_file($conn, $latestSql);
        }
    }
} catch (Throwable $e) {
    // Keep error readable; this is local-only
    die('Local database is missing required tables and auto-restore failed: ' . $e->getMessage());
}

// For activity logs
$connLog = new mysqli($servername, $username, $password, $dbname, $port);
if (!$connLog->connect_error) {
    $connLog->set_charset("utf8");
}

