-- Add category column to announcements table if it doesn't exist
ALTER TABLE `announcements` 
ADD COLUMN IF NOT EXISTS `category` VARCHAR(50) DEFAULT 'general' AFTER `content`;

-- Update existing announcements to have 'general' category if NULL
UPDATE `announcements` SET `category` = 'general' WHERE `category` IS NULL OR `category` = '';

-- Example: Update some announcements to 'news' category
-- UPDATE `announcements` SET `category` = 'news' WHERE id IN (1, 2, 3);
