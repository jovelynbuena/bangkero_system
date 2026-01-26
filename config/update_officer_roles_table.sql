-- ========================================
-- UPDATE OFFICER_ROLES TABLE
-- Add created_at and updated_at columns for audit trail
-- ========================================

-- Check if columns exist before adding them
ALTER TABLE `officer_roles` 
ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add index for better query performance
CREATE INDEX IF NOT EXISTS `idx_created_at` ON `officer_roles` (`created_at`);
CREATE INDEX IF NOT EXISTS `idx_role_name` ON `officer_roles` (`role_name`);
