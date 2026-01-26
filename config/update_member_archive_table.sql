-- Migration: Update member_archive table to include ALL member fields
-- This ensures complete backup/restore of member data

-- Add missing columns to member_archive table
ALTER TABLE `member_archive` 
ADD COLUMN IF NOT EXISTS `dob` DATE DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `gender` VARCHAR(20) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `address` TEXT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `work_type` VARCHAR(50) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `license_number` VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `boat_name` VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `fishing_area` VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `emergency_name` VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `emergency_phone` VARCHAR(20) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `agreement` TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `image` VARCHAR(255) DEFAULT 'default_member.png';

-- Update existing records with default image if NULL
UPDATE `member_archive` SET `image` = 'default_member.png' WHERE `image` IS NULL OR `image` = '';
