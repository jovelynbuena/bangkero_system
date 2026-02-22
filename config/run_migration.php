<?php
/**
 * Database Migration Script
 * Run this file once to update database tables
 * Access via: http://localhost/bangkero_system/config/run_migration.php
 */

include('db_connect.php');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Migration</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; padding: 12px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        .warning { background: #fff3cd; color: #856404; padding: 12px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #ffc107; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-size: 0.9em; }
        .section { margin: 20px 0; padding: 20px; background: #f9f9f9; border-radius: 6px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Database Migration Tool</h1>
        <p>This script will update your database tables with the latest schema changes.</p>
";

$allSuccess = true;

// ==================== ANNOUNCEMENTS TABLE MIGRATION ====================
echo "<div class='section'>";
echo "<h2>📢 Announcements Table Migration</h2>";

$checkQuery = "SHOW COLUMNS FROM announcements LIKE 'category'";
$result = $conn->query($checkQuery);

if ($result && $result->num_rows > 0) {
    echo "<div class='info'>✓ Announcements table is already up to date.</div>";
} else {
    echo "<div class='info'>Starting announcements migration...</div>";
    
    $migrations = [
        ["ALTER TABLE `announcements` ADD COLUMN `category` VARCHAR(50) DEFAULT 'General' AFTER `image`", "category"],
        ["ALTER TABLE `announcements` ADD COLUMN `expiry_date` DATE NULL DEFAULT NULL AFTER `category`", "expiry_date"],
        ["ALTER TABLE `announcements` ADD COLUMN `posted_by` VARCHAR(255) DEFAULT 'Admin' AFTER `expiry_date`", "posted_by"],
        ["UPDATE `announcements` SET `category` = 'General' WHERE `category` IS NULL", "default category values"],
        ["UPDATE `announcements` SET `posted_by` = 'Admin' WHERE `posted_by` IS NULL", "default posted_by values"],
        ["ALTER TABLE `announcements` ADD INDEX `idx_category` (`category`)", "category index"],
        ["ALTER TABLE `announcements` ADD INDEX `idx_expiry_date` (`expiry_date`)", "expiry_date index"]
    ];
    
    foreach ($migrations as $migration) {
        if ($conn->query($migration[0]) === TRUE) {
            echo "<div class='success'>✓ Applied: <code>{$migration[1]}</code></div>";
        } else {
            if (strpos($conn->error, 'Duplicate') !== false) {
                echo "<div class='warning'>⚠ Already exists: <code>{$migration[1]}</code></div>";
            } else {
                echo "<div class='error'>✗ Error with {$migration[1]}: " . $conn->error . "</div>";
                $allSuccess = false;
            }
        }
    }
}
echo "</div>";

// ==================== OFFICER ROLES TABLE MIGRATION ====================
echo "<div class='section'>";
echo "<h2>👔 Officer Roles Table Migration</h2>";

$checkQuery = "SHOW COLUMNS FROM officer_roles LIKE 'created_at'";
$result = $conn->query($checkQuery);

if ($result && $result->num_rows > 0) {
    echo "<div class='info'>✓ Officer roles table is already up to date.</div>";
} else {
    echo "<div class='info'>Starting officer roles migration...</div>";
    
    $migrations = [
        ["ALTER TABLE `officer_roles` ADD COLUMN `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "updated_at column"],
        ["UPDATE `officer_roles` SET `updated_at` = NOW() WHERE `updated_at` IS NULL", "timestamp defaults"],
        ["CREATE INDEX `idx_created_at` ON `officer_roles` (`created_at`)", "created_at index"],
        ["CREATE INDEX `idx_role_name` ON `officer_roles` (`role_name`)", "role_name index"]
    ];
    
    foreach ($migrations as $migration) {
        if ($conn->query($migration[0]) === TRUE) {
            echo "<div class='success'>✓ Applied: <code>{$migration[1]}</code></div>";
        } else {
            if (strpos($conn->error, 'Duplicate') !== false) {
                echo "<div class='warning'>⚠ Already exists: <code>{$migration[1]}</code></div>";
            } else {
                echo "<div class='error'>✗ Error with {$migration[1]}: " . $conn->error . "</div>";
                $allSuccess = false;
            }
        }
    }
}
echo "</div>";

// ==================== MEMBER ARCHIVE TABLE MIGRATION ====================
echo "<div class='section'>";
echo "<h2>🗄️ Member Archive Table Migration</h2>";

$checkArchiveQuery = "SHOW COLUMNS FROM member_archive LIKE 'dob'";
$result = $conn->query($checkArchiveQuery);

if ($result && $result->num_rows > 0) {
    echo "<div class='info'>✓ Member archive table is already up to date.</div>";
} else {
    echo "<div class='info'>Starting member_archive migration to include all member fields...</div>";
    
    $archiveMigrations = [
        ["ALTER TABLE `member_archive` ADD COLUMN `dob` DATE DEFAULT NULL", "dob column"],
        ["ALTER TABLE `member_archive` ADD COLUMN `gender` VARCHAR(20) DEFAULT NULL", "gender column"],
        ["ALTER TABLE `member_archive` ADD COLUMN `address` TEXT DEFAULT NULL", "address column"],
        ["ALTER TABLE `member_archive` ADD COLUMN `work_type` VARCHAR(50) DEFAULT NULL", "work_type column"],
        ["ALTER TABLE `member_archive` ADD COLUMN `license_number` VARCHAR(100) DEFAULT NULL", "license_number column"],
        ["ALTER TABLE `member_archive` ADD COLUMN `boat_name` VARCHAR(100) DEFAULT NULL", "boat_name column"],
        ["ALTER TABLE `member_archive` ADD COLUMN `fishing_area` VARCHAR(100) DEFAULT NULL", "fishing_area column"],
        ["ALTER TABLE `member_archive` ADD COLUMN `emergency_name` VARCHAR(100) DEFAULT NULL", "emergency_name column"],
        ["ALTER TABLE `member_archive` ADD COLUMN `emergency_phone` VARCHAR(20) DEFAULT NULL", "emergency_phone column"],
        ["ALTER TABLE `member_archive` ADD COLUMN `agreement` TINYINT(1) DEFAULT 0", "agreement column"],
        ["ALTER TABLE `member_archive` ADD COLUMN `image` VARCHAR(255) DEFAULT 'default_member.png'", "image column"],
        ["UPDATE `member_archive` SET `image` = 'default_member.png' WHERE `image` IS NULL OR `image` = ''", "default image values"]
    ];
    
    foreach ($archiveMigrations as $migration) {
        list($sql, $description) = $migration;
        
        try {
            if ($conn->query($sql) === TRUE) {
                echo "<div class='success'>✓ Added/Updated $description</div>";
            } else {
                // Check if column already exists
                if (strpos($conn->error, 'Duplicate column name') !== false) {
                    echo "<div class='info'>✓ $description already exists</div>";
                } else {
                    echo "<div class='error'>✗ Failed to add $description: " . $conn->error . "</div>";
                    $allSuccess = false;
                }
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "<div class='info'>✓ $description already exists</div>";
            } else {
                echo "<div class='error'>✗ Error: " . $e->getMessage() . "</div>";
                $allSuccess = false;
            }
        }
    }
    
    echo "<div class='success'><strong>✓ Member archive table migration completed!</strong><br>
          <small>Now archives will preserve ALL member information including work details, emergency contacts, and profile images.</small></div>";
}
echo "</div>";

// ==================== MEMBER ATTENDANCE TABLE MIGRATION ====================
echo "<div class='section'>";
echo "<h2>📝 Member Attendance Table Migration</h2>";

$checkAttendance = $conn->query("SHOW TABLES LIKE 'member_attendance'");
if ($checkAttendance && $checkAttendance->num_rows > 0) {
    echo "<div class='info'>✓ member_attendance table already exists.</div>";

    // Ensure time_out column exists for older installations
    $timeOutCol = $conn->query("SHOW COLUMNS FROM member_attendance LIKE 'time_out'");
    if ($timeOutCol && $timeOutCol->num_rows == 0) {
        echo "<div class='info'>Adding time_out column to member_attendance...</div>";
        $alterAttendance = "ALTER TABLE member_attendance ADD COLUMN time_out TIME NULL AFTER time_in";
        if ($conn->query($alterAttendance) === TRUE) {
            echo "<div class='success'>✓ Added time_out column to member_attendance.</div>";
        } else {
            echo "<div class='error'>✗ Failed to add time_out column: " . $conn->error . "</div>";
            $allSuccess = false;
        }
    }
} else {
    echo "<div class='info'>Creating member_attendance table...</div>";
    $attendanceSql = "CREATE TABLE IF NOT EXISTS member_attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        member_id INT NOT NULL,
        event_id INT NOT NULL,
        attendance_date DATE NOT NULL,
        time_in TIME NULL,
        time_out TIME NULL,
        status ENUM('present','absent','excused') NOT NULL DEFAULT 'present',
        remarks TEXT NULL,
        encoded_by INT NULL,
        encoded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_member_event (member_id, event_id),
        INDEX idx_attendance_date (attendance_date),
        CONSTRAINT fk_att_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
        CONSTRAINT fk_att_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if ($conn->query($attendanceSql) === TRUE) {
        echo "<div class='success'>✓ Created member_attendance table.</div>";
    } else {
        echo "<div class='error'>✗ Failed to create member_attendance table: " . $conn->error . "</div>";
        $allSuccess = false;
    }
}
echo "</div>";

// ==================== FINAL STATUS ====================
echo "<hr style='margin: 30px 0;'>";
if ($allSuccess) {
    echo "<div class='success'><strong>🎉 All migrations completed successfully!</strong></div>";
} else {
    echo "<div class='warning'><strong>⚠ Migrations completed with some warnings/errors.</strong> Please check the messages above.</div>";
}

echo "
        <div style='text-align: center; margin-top: 30px;'>
            <a href='../index/admin.php' style='display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; margin: 5px;'>
                Go to Admin Dashboard →
            </a>
            <a href='../index/management/officer_roles.php' style='display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; margin: 5px;'>
                Manage Officer Roles →
            </a>
        </div>
    </div>
</body>
</html>
";

$conn->close();
?>

