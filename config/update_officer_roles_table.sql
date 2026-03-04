-- ========================================
-- UPDATE OFFICER_ROLES TABLE (MySQL compatible)
-- - Avoids "IF NOT EXISTS" (not supported in older MySQL)
-- - Avoids #1293: only ONE TIMESTAMP can use CURRENT_TIMESTAMP in DEFAULT/ON UPDATE
-- - Uses information_schema checks so the script is re-runnable
-- ========================================

-- 1) Add created_at (TIMESTAMP w/ DEFAULT CURRENT_TIMESTAMP)
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'officer_roles'
        AND COLUMN_NAME = 'created_at'
    ),
    'SELECT "created_at already exists"',
    'ALTER TABLE `officer_roles` ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Add updated_at (DATETIME; application code can set NOW() on updates)
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'officer_roles'
        AND COLUMN_NAME = 'updated_at'
    ),
    'SELECT "updated_at already exists"',
    'ALTER TABLE `officer_roles` ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Optional: backfill updated_at for existing rows
-- (Safe to run multiple times)
UPDATE `officer_roles`
SET `updated_at` = COALESCE(`updated_at`, `created_at`, NOW());

-- 4) Add indexes (skip if they already exist)
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'officer_roles'
        AND INDEX_NAME = 'idx_created_at'
    ),
    'SELECT "idx_created_at already exists"',
    'CREATE INDEX `idx_created_at` ON `officer_roles` (`created_at`)'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'officer_roles'
        AND INDEX_NAME = 'idx_role_name'
    ),
    'SELECT "idx_role_name already exists"',
    'CREATE INDEX `idx_role_name` ON `officer_roles` (`role_name`)'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ========================================
-- 5) Add display_order for hierarchy sorting
-- ========================================
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'officer_roles'
        AND COLUMN_NAME = 'display_order'
    ),
    'SELECT "display_order already exists"',
    'ALTER TABLE `officer_roles` ADD COLUMN `display_order` INT DEFAULT 0'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ========================================
-- 6) Set default display_order values for existing roles
-- ========================================
UPDATE `officer_roles` SET `display_order` = 1 WHERE `role_name` = 'President';
UPDATE `officer_roles` SET `display_order` = 2 WHERE `role_name` = 'Vice President';
UPDATE `officer_roles` SET `display_order` = 3 WHERE `role_name` = 'Secretary';
UPDATE `officer_roles` SET `display_order` = 4 WHERE `role_name` = 'Treasurer';
UPDATE `officer_roles` SET `display_order` = 5 WHERE `role_name` = 'Auditor';
UPDATE `officer_roles` SET `display_order` = 6 WHERE `role_name` = 'Business Manager';
UPDATE `officer_roles` SET `display_order` = 7 WHERE `role_name` = 'Peace Officer';
UPDATE `officer_roles` SET `display_order` = 8 WHERE `role_name` = 'Sergeant-at-Arms';

-- Add index for display_order
SET @sql := (
  SELECT IF(
    EXISTS(
      SELECT 1
      FROM information_schema.STATISTICS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'officer_roles'
        AND INDEX_NAME = 'idx_display_order'
    ),
    'SELECT "idx_display_order already exists"',
    'CREATE INDEX `idx_display_order` ON `officer_roles` (`display_order`)'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
