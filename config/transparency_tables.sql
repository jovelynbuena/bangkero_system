-- =====================================================
-- Transparency & Community Impact Database Tables
-- =====================================================

-- Table: campaigns
-- Stores fundraising campaign information
CREATE TABLE IF NOT EXISTS `campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `goal_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `current_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('Active','Completed','Paused') NOT NULL DEFAULT 'Active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: donations
-- Stores donation records from donors
CREATE TABLE IF NOT EXISTS `donations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `donor_name` varchar(255) NOT NULL,
  `donor_email` varchar(255) DEFAULT NULL,
  `donor_phone` varchar(50) DEFAULT NULL,
  `campaign` varchar(255) NOT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `donation_date` date NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `status` enum('Completed','Pending','Failed') NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `donor_name` (`donor_name`),
  KEY `donation_date` (`donation_date`),
  KEY `status` (`status`),
  CONSTRAINT `fk_donations_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Sample Data for Testing
-- =====================================================

-- Insert sample campaigns
INSERT INTO `campaigns` (`campaign_name`, `description`, `goal_amount`, `current_amount`, `status`, `start_date`, `end_date`) VALUES
('Fishing Equipment Assistance Program', 'Providing essential fishing gear and equipment to underprivileged fishermen to improve their livelihood and productivity.', 100000.00, 75000.00, 'Active', '2026-01-01', '2026-06-30'),
('Community Boat Repair Fund', 'Funding repairs and maintenance for community fishing boats to ensure safety and operational efficiency.', 150000.00, 120000.00, 'Active', '2026-01-15', '2026-07-15'),
('Marine Safety Training Program', 'Comprehensive maritime safety training including first aid, navigation, and emergency response for all members.', 80000.00, 65000.00, 'Active', '2026-02-01', '2026-08-31'),
('Education Scholarship Fund', 'Scholarship assistance for children of fishermen to support their education and build a better future.', 200000.00, 180000.00, 'Active', '2026-01-10', '2026-12-31'),
('Coastal Clean-up Initiative', 'Regular coastal and marine environment cleanup drives to protect our ecosystem and promote sustainability.', 50000.00, 50000.00, 'Completed', '2025-11-01', '2026-01-31'),
('Emergency Relief Fund', 'Rapid response fund for fishermen affected by natural disasters, accidents, or emergencies.', 120000.00, 45000.00, 'Active', '2026-01-01', '2026-12-31');

-- Insert sample donations
INSERT INTO `donations` (`donor_name`, `donor_email`, `donor_phone`, `campaign`, `campaign_id`, `amount`, `donation_date`, `payment_method`, `reference_number`, `status`, `notes`) VALUES
('Juan Dela Cruz', 'juan.delacruz@email.com', '09171234567', 'Fishing Equipment Assistance Program', 1, 5000.00, '2026-02-10', 'GCash', 'GC2026021012345', 'Completed', 'Donation for fishing nets'),
('Maria Santos', 'maria.santos@email.com', '09281234567', 'Community Boat Repair Fund', 2, 10000.00, '2026-02-11', 'Bank Transfer', 'BT2026021198765', 'Completed', 'Supporting boat maintenance'),
('Pedro Reyes', 'pedro.reyes@email.com', '09391234567', 'Marine Safety Training Program', 3, 3000.00, '2026-02-12', 'Cash', 'CASH202602120001', 'Completed', 'Safety training support'),
('Anonymous Donor', NULL, NULL, 'Education Scholarship Fund', 4, 20000.00, '2026-02-12', 'Bank Transfer', 'BT2026021254321', 'Completed', 'Scholarship for 4 students'),
('Roberto Garcia', 'roberto.garcia@email.com', '09451234567', 'Fishing Equipment Assistance Program', 1, 7500.00, '2026-02-13', 'PayMaya', 'PM2026021387654', 'Completed', 'Equipment donation'),
('Lina Aquino', 'lina.aquino@email.com', '09561234567', 'Emergency Relief Fund', 6, 5000.00, '2026-02-13', 'GCash', 'GC2026021365432', 'Completed', 'Emergency assistance'),
('Carlos Mendoza', 'carlos.mendoza@email.com', '09671234567', 'Community Boat Repair Fund', 2, 15000.00, '2026-02-14', 'Bank Transfer', 'BT2026021445678', 'Completed', 'Boat engine repair support'),
('Isabel Cruz', 'isabel.cruz@email.com', '09781234567', 'Marine Safety Training Program', 3, 8000.00, '2026-02-14', 'Cash', 'CASH202602140002', 'Completed', 'Training materials'),
('Tito Villanueva', 'tito.v@email.com', '09891234567', 'Education Scholarship Fund', 4, 12000.00, '2026-02-15', 'GCash', 'GC2026021523456', 'Completed', 'Education support'),
('Carmen Lopez', 'carmen.lopez@email.com', '09901234567', 'Fishing Equipment Assistance Program', 1, 4500.00, '2026-02-15', 'PayMaya', 'PM2026021534567', 'Completed', 'Fishing line and hooks');

-- Update campaign current amounts based on donations
UPDATE `campaigns` c 
SET `current_amount` = (
    SELECT COALESCE(SUM(d.amount), 0) 
    FROM `donations` d 
    WHERE d.campaign_id = c.id 
    AND d.status = 'Completed'
);
