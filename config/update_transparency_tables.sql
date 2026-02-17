-- ========================================
-- TRANSPARENCY & COMMUNITY IMPACT TABLES
--
-- Run this script once in phpMyAdmin or your MySQL client
-- to create the core tables for the Transparency & Impact module.
--
-- All tables are prefixed with `transparency_` to avoid conflicts
-- and keep related data grouped together.
-- ========================================

-- Make sure we are using the correct database
-- (uncomment and set if needed)
-- USE `bangkero_db_name_here`;

-- ========================================
-- 1) FUNDRAISING CAMPAIGNS
-- ========================================

CREATE TABLE IF NOT EXISTS `transparency_campaigns` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(191) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `goal_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `status` VARCHAR(20) NOT NULL DEFAULT 'planned', -- planned, active, completed, paused, cancelled
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `banner_image` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_transparency_campaigns_slug` (`slug`),
  KEY `idx_transparency_campaigns_status` (`status`),
  KEY `idx_transparency_campaigns_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========================================
-- 2) DONATIONS
-- ========================================

CREATE TABLE IF NOT EXISTS `transparency_donations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` INT UNSIGNED DEFAULT NULL,
  `donor_name` VARCHAR(255) DEFAULT NULL,
  `donor_type` VARCHAR(50) DEFAULT NULL,      -- individual, organization, anonymous, etc.
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'PHP',
  `date_received` DATE NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT NULL,  -- cash, gcash, bank, etc.
  `reference_code` VARCHAR(100) DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'confirmed', -- pending, confirmed, failed, refunded, cancelled
  `is_restricted` TINYINT(1) NOT NULL DEFAULT 0,     -- 0 = general fund, 1 = restricted
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_transparency_donations_campaign_date` (`campaign_id`, `date_received`),
  KEY `idx_transparency_donations_status` (`status`),
  KEY `idx_transparency_donations_reference` (`reference_code`),
  CONSTRAINT `fk_transparency_donations_campaign`
    FOREIGN KEY (`campaign_id`) REFERENCES `transparency_campaigns`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========================================
-- 3) PROGRAMS / PROJECTS
-- ========================================

CREATE TABLE IF NOT EXISTS `transparency_programs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `category` VARCHAR(50) DEFAULT NULL,        -- livelihood, relief, training, environmental, etc.
  `description` TEXT DEFAULT NULL,
  `allocated_budget` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `utilized_budget` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `status` VARCHAR(20) NOT NULL DEFAULT 'planned', -- planned, ongoing, completed, on-hold
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `linked_campaign_id` INT UNSIGNED DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_transparency_programs_category_status` (`category`, `status`),
  KEY `idx_transparency_programs_linked_campaign` (`linked_campaign_id`),
  CONSTRAINT `fk_transparency_programs_campaign`
    FOREIGN KEY (`linked_campaign_id`) REFERENCES `transparency_campaigns`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========================================
-- 4) BENEFICIARIES
-- ========================================

CREATE TABLE IF NOT EXISTS `transparency_beneficiaries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,                 -- individual or primary contact
  `household_name` VARCHAR(255) DEFAULT NULL,   -- household or community name
  `assistance_type` VARCHAR(50) DEFAULT NULL,   -- livelihood, relief, training, etc.
  `category` VARCHAR(50) DEFAULT NULL,          -- optional finer-grained category
  `amount_value` DECIMAL(15,2) DEFAULT NULL,    -- monetary value of assistance, if applicable
  `quantity` INT DEFAULT NULL,                  -- e.g., number of kits, equipment
  `date_assisted` DATE NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'served', -- served, in-progress, pending, etc.
  `barangay` VARCHAR(100) DEFAULT NULL,
  `municipality` VARCHAR(100) DEFAULT NULL,
  `province` VARCHAR(100) DEFAULT NULL,
  `photo_path` VARCHAR(255) DEFAULT NULL,
  `short_story` TEXT DEFAULT NULL,
  `featured` TINYINT(1) NOT NULL DEFAULT 0,     -- 1 = show in featured stories on public page
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_transparency_beneficiaries_program_date` (`program_id`, `date_assisted`),
  KEY `idx_transparency_beneficiaries_assistance_type` (`assistance_type`),
  KEY `idx_transparency_beneficiaries_featured` (`featured`),
  CONSTRAINT `fk_transparency_beneficiaries_program`
    FOREIGN KEY (`program_id`) REFERENCES `transparency_programs`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========================================
-- 5) IMPACT METRICS
-- ========================================

CREATE TABLE IF NOT EXISTS `transparency_impact_metrics` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `metric_key` VARCHAR(100) NOT NULL,          -- e.g., fishermen_assisted, families_supported
  `label` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `value` DECIMAL(18,2) NOT NULL DEFAULT 0.00, -- can represent counts or amounts
  `unit` VARCHAR(50) DEFAULT NULL,             -- families, fishermen, trainings, PHP, etc.
  `calculation_mode` VARCHAR(20) NOT NULL DEFAULT 'manual', -- manual or auto
  `auto_source` VARCHAR(100) DEFAULT NULL,     -- optional hint on how to compute when auto
  `last_computed_at` DATETIME DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `display_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_transparency_impact_metrics_key` (`metric_key`),
  KEY `idx_transparency_impact_metrics_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========================================
-- 6) TRANSPARENCY SETTINGS
-- ========================================

CREATE TABLE IF NOT EXISTS `transparency_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `hero_title` VARCHAR(255) DEFAULT NULL,
  `hero_subtitle` TEXT DEFAULT NULL,
  `hero_last_updated_override` DATE DEFAULT NULL,
  `transparency_statement` TEXT DEFAULT NULL,
  `disclaimer_text` TEXT DEFAULT NULL,
  `show_downloads` TINYINT(1) NOT NULL DEFAULT 1,
  `show_activity_gallery` TINYINT(1) NOT NULL DEFAULT 1,
  `primary_color` VARCHAR(20) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========================================
-- 7) DOWNLOADABLE TRANSPARENCY REPORTS
-- ========================================

CREATE TABLE IF NOT EXISTS `transparency_reports` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `report_type` VARCHAR(50) DEFAULT NULL,      -- financial, impact, annual, etc.
  `year` INT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `display_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_transparency_reports_type_year` (`report_type`, `year`),
  KEY `idx_transparency_reports_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ========================================
-- OPTIONAL: SEED DEFAULT SETTINGS ROW
-- (Uncomment if you want to auto-insert a default settings row)
-- ========================================

-- INSERT INTO `transparency_settings` (
--   `hero_title`, `hero_subtitle`, `transparency_statement`, `disclaimer_text`
-- ) VALUES (
--   'Transparency & Community Impact Report',
--   'Showing how donations flow into real programs and beneficiary outcomes.',
--   'We are committed to responsible stewardship of all funds and transparent reporting.',
--   'Note: Initial data may include sample or partial records while the system is being populated.'
-- );
