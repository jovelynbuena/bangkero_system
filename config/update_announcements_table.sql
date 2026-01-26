-- SQL Script to update announcements table with new fields
-- Run this in your phpMyAdmin or MySQL client

-- Add new columns to announcements table
ALTER TABLE `announcements` 
ADD COLUMN `category` VARCHAR(50) DEFAULT 'General' AFTER `image`,
ADD COLUMN `expiry_date` DATE NULL DEFAULT NULL AFTER `category`,
ADD COLUMN `posted_by` VARCHAR(255) DEFAULT 'Admin' AFTER `expiry_date`;

-- Update existing records to have default values
UPDATE `announcements` SET `category` = 'General' WHERE `category` IS NULL;
UPDATE `announcements` SET `posted_by` = 'Admin' WHERE `posted_by` IS NULL;

-- Optional: Add index for better performance
ALTER TABLE `announcements` ADD INDEX `idx_category` (`category`);
ALTER TABLE `announcements` ADD INDEX `idx_expiry_date` (`expiry_date`);
