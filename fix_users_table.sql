-- Fix for missing users table
-- Run this in phpMyAdmin or MySQL console

USE `bangkero_local`;

-- Drop table if exists (for clean setup)
DROP TABLE IF EXISTS `users`;

-- Create users table
CREATE TABLE `users` (
  `username` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `id` int(11) NOT NULL,
  `role` enum('admin','member','officer') NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin users (from latest backup)
INSERT INTO `users` (`username`, `password_hash`, `id`, `role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES 
('argie2', '$2y$10$PXmeV0TETc4CasIO.PUGYe4s18MgWUyQOcwmCYDtimhT.By3nXXhC', 321211, 'admin', 'approved', '2025-10-04 03:58:05', 'argie2@gmail.com', '096204334624', 'Male', 'bulacan, bulacan ', '1760856293_cybernetic-cool-anime-cyborg-girl-9y-1920x1080.jpg', 'argie', 'buena', NULL, 0),
('admin', '$2y$10$T1cQRA8Y2SqmVCfXM08dF.u.DFPWWu75Cbu0tF5Q86n1mtCQOQA4O', 321219, 'admin', 'approved', '2025-10-18 23:31:04', 'admin@gmail.com', '09876543456', 'Female', 'Sitio Bukid, Calapacuan Subic Zambales', '1760859106_photo_2024-08-13_09-05-00.jpg', 'Jovelyn', 'San Jose', NULL, 0),
('klare', '$2y$10$T.AmLFH16NOFokbIch7Oxu4Da9r4NQjp5DlUOmcQhQVbwZtxMlBb2', 321220, 'admin', 'approved', '2025-10-19 00:41:30', 'klare@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('John', '$2y$10$hTbYydbNq/9embkicVkbHO/8uA3g.MsCL3CdgpL24mzzQ2fTxp2fi', 321221, 'admin', 'approved', '2025-11-30 23:11:15', 'johncarlmangino2@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);

-- Verify table was created
SELECT 'Users table created successfully!' AS message;
SELECT COUNT(*) AS total_users FROM users;
