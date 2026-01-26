-- ============================================
-- FIX AUTO_INCREMENT FOR ALL TABLES
-- This SQL script will fix the auto_increment issue on all tables
-- Run this in phpMyAdmin or via the fix_auto_increment.php script
-- ============================================

-- Fix announcements table
ALTER TABLE `announcements` 
MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);

-- Fix members table
ALTER TABLE `members` 
MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);

-- Fix officers table
ALTER TABLE `officers` 
MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);

-- Fix events table
ALTER TABLE `events` 
MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);

-- Fix galleries table
ALTER TABLE `galleries` 
MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);

-- Fix officer_roles table
ALTER TABLE `officer_roles` 
MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);

-- Fix archived_announcements table
ALTER TABLE `archived_announcements` 
MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);

-- Fix archived_members table (if exists)
ALTER TABLE `archived_members` 
MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);

-- Fix archived_officers table (if exists)
ALTER TABLE `archived_officers` 
MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);

-- Fix officers_archive table (if exists)
ALTER TABLE `officers_archive` 
MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);

-- Fix archived_events table (if exists)
ALTER TABLE `archived_events` 
MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);

-- Fix contact_messages table
ALTER TABLE `contact_messages` 
MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);

-- Fix member_archive table (if exists)
ALTER TABLE `member_archive` 
MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);

-- Fix activity_logs table (if exists)
ALTER TABLE `activity_logs` 
MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);

-- Note: system_config table usually has fixed id=1, so we skip it

-- ============================================
-- DONE! All tables now have proper AUTO_INCREMENT
-- ============================================
