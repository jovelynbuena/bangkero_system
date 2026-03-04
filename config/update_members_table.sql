-- ========================================
-- UPDATE MEMBERS TABLE
-- Add structured address fields, civil status, membership type
-- ========================================

-- 1) Add structured address columns
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND COLUMN_NAME = 'street'
    ),
    'SELECT "street column already exists"',
    'ALTER TABLE `members` ADD COLUMN `street` VARCHAR(255) NULL AFTER `address`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND COLUMN_NAME = 'barangay'
    ),
    'SELECT "barangay column already exists"',
    'ALTER TABLE `members` ADD COLUMN `barangay` VARCHAR(100) NULL AFTER `street`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND COLUMN_NAME = 'municipality'
    ),
    'SELECT "municipality column already exists"',
    'ALTER TABLE `members` ADD COLUMN `municipality` VARCHAR(100) NULL AFTER `barangay`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND COLUMN_NAME = 'province'
    ),
    'SELECT "province column already exists"',
    'ALTER TABLE `members` ADD COLUMN `province` VARCHAR(100) NULL AFTER `municipality`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND COLUMN_NAME = 'region'
    ),
    'SELECT "region column already exists"',
    'ALTER TABLE `members` ADD COLUMN `region` VARCHAR(50) NULL AFTER `province`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND COLUMN_NAME = 'zip_code'
    ),
    'SELECT "zip_code column already exists"',
    'ALTER TABLE `members` ADD COLUMN `zip_code` VARCHAR(10) NULL AFTER `region`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) Add civil_status column
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND COLUMN_NAME = 'civil_status'
    ),
    'SELECT "civil_status column already exists"',
    'ALTER TABLE `members` ADD COLUMN `civil_status` VARCHAR(20) NULL AFTER `gender`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3) Add membership_type column
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND COLUMN_NAME = 'membership_type'
    ),
    'SELECT "membership_type column already exists"',
    'ALTER TABLE `members` ADD COLUMN `membership_type` VARCHAR(20) NULL AFTER `work_type`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4) Migrate existing address data (optional - split old address into street)
-- UPDATE members SET street = address WHERE street IS NULL AND address IS NOT NULL;

-- 5) Add indexes for new columns
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND INDEX_NAME = 'idx_civil_status'
    ),
    'SELECT "idx_civil_status already exists"',
    'CREATE INDEX `idx_civil_status` ON `members` (`civil_status`)'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND INDEX_NAME = 'idx_membership_type'
    ),
    'SELECT "idx_membership_type already exists"',
    'CREATE INDEX `idx_membership_type` ON `members` (`membership_type`)'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND INDEX_NAME = 'idx_municipality'
    ),
    'SELECT "idx_municipality already exists"',
    'CREATE INDEX `idx_municipality` ON `members` (`municipality`)'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 6) Add municipal_permit_no column
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND COLUMN_NAME = 'municipal_permit_no'
    ),
    'SELECT "municipal_permit_no column already exists"',
    'ALTER TABLE `members` ADD COLUMN `municipal_permit_no` VARCHAR(50) NULL AFTER `license_number`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 7) Add bfar_fisherfolk_id column
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND COLUMN_NAME = 'bfar_fisherfolk_id'
    ),
    'SELECT "bfar_fisherfolk_id column already exists"',
    'ALTER TABLE `members` ADD COLUMN `bfar_fisherfolk_id` VARCHAR(50) NULL AFTER `municipal_permit_no`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 8) Add boat_registration column
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1 FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'members' AND COLUMN_NAME = 'boat_registration'
    ),
    'SELECT "boat_registration column already exists"',
    'ALTER TABLE `members` ADD COLUMN `boat_registration` VARCHAR(50) NULL AFTER `boat_name`'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT 'Members table update completed successfully!' AS status;
