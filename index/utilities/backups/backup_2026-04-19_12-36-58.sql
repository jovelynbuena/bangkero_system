-- Database Backup
-- Generated on: 2026-04-19 12:36:58
-- Database: bangkero_local

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


-- Table: activity_logs
DROP TABLE IF EXISTS `activity_logs`;

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `activity_logs`
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('1', '321192', 'Logged in', NULL, '::1', '2025-10-02 16:06:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('2', '321192', 'Logged in', NULL, '::1', '2025-10-02 16:11:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('3', '321192', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-10-02 16:30:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('4', '321192', 'Logged in', NULL, '::1', '2025-10-02 16:31:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('5', '321192', 'Restored member', 'Restored member: dfgdg dfdgdf dfdfd', '::1', '2025-10-02 16:38:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('6', '321192', 'Restored member: dfgdg dfdgdf dfdfd', 'Restored member: dfgdg dfdgdf dfdfd', NULL, '2025-10-02 16:45:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('7', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-02 16:55:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('8', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-02 16:57:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('9', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-02 16:58:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('10', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-02 17:02:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('11', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-02 17:06:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('12', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-02 17:06:58');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('13', '321192', 'Added announcement', 'Title: fgdfg', NULL, '2025-10-02 17:16:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('14', '321192', 'Added announcement', 'Title: erd', NULL, '2025-10-02 17:21:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('15', '321192', 'Added announcement', 'Title: tert', NULL, '2025-10-02 17:25:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('16', '321192', 'Updated announcement', 'Title: tert', NULL, '2025-10-02 17:25:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('17', '321192', 'Updated announcement', 'Title: tert', NULL, '2025-10-02 17:26:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('18', '321192', 'Updated event', 'Event: Red Sea International Sport Fishing Tournament', NULL, '2025-10-02 17:32:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('19', '321192', 'Added event', 'Event: Gone Fishing 2025!', NULL, '2025-10-02 17:34:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('20', '321192', 'Logged in', NULL, '::1', '2025-10-04 12:00:14');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('21', '321192', 'Logged in', NULL, '::1', '2025-10-04 12:23:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('22', '321211', 'Failed login attempt (not approved)', 'Attempted username: argie2', '::1', '2025-10-04 12:26:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('23', '321211', 'Failed login attempt (not approved)', 'Attempted username: argie2', '::1', '2025-10-04 12:27:20');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('24', '321211', 'Logged in', NULL, '::1', '2025-10-04 12:29:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('25', '321211', 'Logged in', NULL, '::1', '2025-10-04 12:42:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('26', '321192', 'Logged in', NULL, '::1', '2025-10-04 12:55:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('27', '321192', 'Restored member: dfgdg dfdgdf dfdfd', 'Restored member: dfgdg dfdgdf dfdfd', NULL, '2025-10-04 13:00:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('28', '321192', 'Archived officer ID: 35', 'Archived officer ID: 35', NULL, '2025-10-04 13:36:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('29', '321192', 'Restored officer: Jovelyn S Buena', 'Restored officer: Jovelyn S Buena', NULL, '2025-10-04 13:43:51');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('30', '321192', 'Archived officer ID: 43', 'Archived officer ID: 43', NULL, '2025-10-04 13:43:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('31', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 13:56:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('32', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 13:57:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('33', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 14:00:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('34', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 14:00:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('35', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 14:00:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('36', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 14:02:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('37', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 14:02:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('38', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 14:03:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('39', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 14:04:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('40', '321192', 'Logged in', NULL, '::1', '2025-10-04 16:58:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('41', '321211', 'Logged in', NULL, '::1', '2025-10-04 17:16:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('42', '321192', 'Logged in', NULL, '::1', '2025-10-04 17:16:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('43', '321192', 'Logged in', NULL, '::1', '2025-10-05 11:46:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('44', '321192', 'Logged in', NULL, '::1', '2025-10-10 08:09:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('45', '321192', 'Logged in', NULL, '::1', '2025-10-11 08:06:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('46', '321192', 'Added announcement', 'Title: sadasd', NULL, '2025-10-11 08:49:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('47', '321192', 'Edited announcement', 'Edited Title: sadasd', NULL, '2025-10-11 08:52:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('48', '321192', 'Added announcement', 'Title: sdfsdf', NULL, '2025-10-11 08:53:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('49', '321192', 'Added announcement', 'Title: sdfsdf', NULL, '2025-10-11 08:56:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('50', '321192', 'Edited announcement', 'Edited Title: sadasd', NULL, '2025-10-11 09:13:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('51', '321192', 'Edited announcement', 'Edited Title: sadasd', NULL, '2025-10-11 09:19:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('52', '321192', 'Edited announcement', 'Edited Title: sadasd', NULL, '2025-10-11 09:20:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('53', '321192', 'Edited announcement', 'Edited Title: Community Fishing Day', NULL, '2025-10-11 10:49:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('54', '321192', 'Edited announcement', 'Edited Title: Clean-Up Drive', NULL, '2025-10-11 10:49:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('55', '321192', 'Logged in', NULL, '::1', '2025-10-12 03:28:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('56', '321192', 'Logged in', NULL, '::1', '2025-10-12 05:23:12');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('57', '321216', 'Failed login attempt (not approved)', 'Attempted username: alexa', '::1', '2025-10-15 09:43:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('58', '321218', 'Failed login attempt (not approved)', 'Attempted username: burn', '::1', '2025-10-15 09:46:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('59', NULL, 'Failed login attempt (user not found)', 'Attempted username: burnw', '::1', '2025-10-15 09:47:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('60', '321216', 'Failed login attempt (not approved)', 'Attempted username: alexa', '::1', '2025-10-15 14:58:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('61', '321192', 'Logged in', NULL, '::1', '2025-10-19 05:17:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('62', '321192', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-10-19 06:22:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('63', '321192', 'Logged in', NULL, '::1', '2025-10-19 06:22:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('64', '321192', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-10-19 06:33:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('65', '321192', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-10-19 06:33:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('66', '321192', 'Logged in', NULL, '::1', '2025-10-19 06:33:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('67', '321192', 'Logged in', NULL, '::1', '2025-10-19 06:34:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('68', '321211', 'Logged in', NULL, '::1', '2025-10-19 06:35:30');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('69', '321211', 'Failed login attempt (wrong password)', 'Attempted username: argie2', '::1', '2025-10-19 06:45:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('70', '321211', 'Logged in', NULL, '::1', '2025-10-19 06:45:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('71', '321192', 'Logged in', NULL, '::1', '2025-10-19 07:09:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('72', '321192', 'Logged in', NULL, '::1', '2025-10-19 07:27:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('73', '321197', 'Failed login attempt (wrong password)', 'Attempted username: jovelyn', '::1', '2025-10-19 07:28:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('74', '321197', 'Failed login attempt (wrong password)', 'Attempted username: jovelyn', '::1', '2025-10-19 07:28:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('75', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-19 07:28:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('76', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-19 07:29:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('77', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-19 07:29:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('78', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-19 07:29:14');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('79', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-19 07:29:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('80', '321219', 'Logged in', NULL, '::1', '2025-10-19 07:31:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('81', NULL, 'Failed login attempt (user not found)', 'Attempted username: paimon', '::1', '2025-10-19 07:33:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('82', '321219', 'Logged in', NULL, '::1', '2025-10-19 07:36:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('83', '321211', 'Failed login attempt (wrong password)', 'Attempted username: argie2', '::1', '2025-10-19 08:24:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('84', '321211', 'Logged in', NULL, '::1', '2025-10-19 08:25:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('85', '321211', 'Logged in', NULL, '::1', '2025-10-19 08:41:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('86', '321220', 'Logged in', NULL, '::1', '2025-10-19 08:42:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('87', '321211', 'Logged in', NULL, '::1', '2025-10-19 08:42:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('88', '321211', 'Logged in', NULL, '::1', '2025-10-19 09:09:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('89', '321211', 'Logged in', NULL, '::1', '2025-10-19 09:57:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('90', '321211', 'Logged in', NULL, '::1', '2025-10-19 10:03:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('91', '321215', 'Failed login attempt (not approved)', 'Attempted username: avina', '::1', '2025-10-19 10:19:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('92', '321216', 'Logged in', NULL, '::1', '2025-10-19 10:19:51');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('93', '321220', 'Logged in', NULL, '::1', '2025-10-19 10:37:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('94', '321220', 'Logged in', NULL, '::1', '2025-10-19 10:41:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('95', '321219', 'Logged in', NULL, '::1', '2025-10-19 10:43:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('96', '321220', 'Logged in', NULL, '::1', '2025-10-19 10:45:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('97', '321220', 'Logged in', NULL, '::1', '2025-10-19 10:48:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('98', '321219', 'Logged in', NULL, '::1', '2025-10-19 10:49:14');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('99', '321219', 'Logged in', NULL, '::1', '2025-10-19 10:49:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('100', '321219', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-11-30 10:36:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('101', '321219', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-11-30 10:36:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('102', '321219', 'Logged in', NULL, '::1', '2025-11-30 10:36:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('103', '321219', 'Logged in', NULL, '::1', '2025-11-30 11:17:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('104', '321219', 'Logged in', NULL, '::1', '2025-11-30 13:16:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('105', '321220', 'Logged in', NULL, '::1', '2025-11-30 14:09:37');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('106', '321219', 'Logged in', NULL, '::1', '2025-11-30 14:15:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('107', '321219', 'Logged in', NULL, '::1', '2025-11-30 14:33:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('108', '321219', 'Logged in', NULL, '::1', '2025-11-30 14:33:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('109', '321220', 'Logged in', NULL, '::1', '2025-11-30 15:09:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('110', '321219', 'Logged in', NULL, '::1', '2025-12-01 07:03:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('111', '321219', 'Logged in', NULL, '::1', '2025-12-01 07:11:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('112', '321222', 'Logged in', NULL, '::1', '2025-12-01 07:20:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('113', '321219', 'Logged in', NULL, '::1', '2025-12-01 07:40:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('114', '321219', 'Logged in', NULL, '::1', '2025-12-01 12:19:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('115', '321219', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2026-01-13 18:35:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('116', '321219', 'Logged in', NULL, '::1', '2026-01-13 18:35:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('117', '321219', 'Added announcement', 'Title: titen', NULL, '2026-01-13 18:37:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('118', '321219', 'Logged in', NULL, '::1', '2026-01-17 14:38:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('119', '321215', 'Failed login attempt (not approved)', 'Attempted username: avina', '::1', '2026-01-17 17:55:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('120', '321220', 'Logged in', NULL, '::1', '2026-01-17 17:55:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('121', '321220', 'Logged in', NULL, '::1', '2026-01-17 17:57:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('122', '0', 'Logged in', NULL, '::1', '2026-01-17 17:57:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('123', '321219', 'Logged in', NULL, '::1', '2026-01-20 12:02:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('124', '321219', 'Logged in', NULL, '::1', '2026-01-20 12:43:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('125', '321219', 'Logged in', NULL, '::1', '2026-01-22 04:43:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('126', '321219', 'Logged in', NULL, '::1', '2026-01-25 23:58:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('127', '321219', 'Logged in', NULL, '::1', '2026-01-26 05:12:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('128', '0', 'Logged in', NULL, '::1', '2026-01-26 05:14:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('129', '321220', 'Logged in', NULL, '::1', '2026-01-26 05:15:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('130', '321220', 'Logged in', NULL, '::1', '2026-01-26 05:42:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('131', '321220', 'Added announcement', 'Title: Qui exercitation sun', NULL, '2026-01-26 05:45:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('132', '321220', 'Added announcement', 'Title: Qui adipisicing minu', NULL, '2026-01-26 05:59:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('133', '321220', 'Added announcement', 'Title: Optio mollitia duci', NULL, '2026-01-26 05:59:54');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('134', '321220', 'Edited announcement', 'Edited Title: Fishing Permit Renewal', NULL, '2026-01-26 06:01:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('135', '321219', 'Logged in', NULL, '::1', '2026-01-26 13:15:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('136', '321219', 'Logged in', NULL, '::1', '2026-01-28 06:17:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('137', '321219', 'Logged in', NULL, '::1', '2026-01-28 06:33:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('138', '321220', 'Logged in', NULL, '::1', '2026-01-28 06:33:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('139', '321220', 'Restored officer: Cristopher M. De Jesus', 'Restored officer: Cristopher M. De Jesus', NULL, '2026-01-28 06:41:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('140', '321219', 'Logged in', NULL, '::1', '2026-02-09 10:40:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('141', '321220', 'Logged in', NULL, '::1', '2026-02-09 10:42:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('142', '0', 'Failed login attempt (not approved)', 'Attempted username: katkat', '::1', '2026-02-09 11:02:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('143', '321220', 'Logged in', NULL, '::1', '2026-02-09 11:38:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('144', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-19-29.sql', '::1', '2026-02-09 12:39:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('145', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_13-44-50.sql (61,563 bytes)', '::1', '2026-02-09 12:46:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('146', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-44-50.sql', '::1', '2026-02-09 12:46:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('147', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_13-48-02.sql (62,034 bytes)', '::1', '2026-02-09 12:49:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('148', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-48-02.sql', '::1', '2026-02-09 12:49:51');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('149', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_13-50-19.sql (62,505 bytes)', '::1', '2026-02-09 12:52:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('150', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-50-19.sql', '::1', '2026-02-09 12:52:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('151', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-50-19.sql', '::1', '2026-02-09 13:00:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('152', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_14-05-11.sql (63,208 bytes)', '::1', '2026-02-09 13:06:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('153', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_14-05-11.sql', '::1', '2026-02-09 13:06:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('154', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_14-06-39.sql (63,679 bytes)', '::1', '2026-02-09 13:08:24');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('155', '321220', 'Delete Backup', 'Deleted backup file: backup_2025-10-02-16-43-24.sql', '::1', '2026-02-09 13:19:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('156', '321220', 'Delete Backup', 'Deleted backup file: test_backup_2026-02-09_14-35-02.sql', '::1', '2026-02-09 13:37:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('157', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_14-36-04.sql (64,233 bytes)', '::1', '2026-02-09 13:37:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('158', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_14-36-10.sql (64,687 bytes)', '::1', '2026-02-09 13:37:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('159', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_14-36-10.sql', '::1', '2026-02-09 13:38:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('160', '321220', 'Logged in', NULL, '::1', '2026-02-10 04:08:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('161', '321220', 'Logged in', NULL, '::1', '2026-02-11 14:22:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('162', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-11 14:26:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('163', '321220', 'Logged in', NULL, '::1', '2026-02-11 14:27:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('164', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_15-53-25.sql (67,247 bytes)', '::1', '2026-02-11 14:55:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('165', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_15-53-25.sql', '::1', '2026-02-11 14:55:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('166', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_15-53-25.sql', '::1', '2026-02-11 14:56:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('167', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_15-55-15.sql (68,121 bytes)', '::1', '2026-02-11 14:57:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('168', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_15-55-15.sql', '::1', '2026-02-11 14:57:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('169', '321220', 'Database Restore', 'Restored database from: backup_2026-02-11_16-00-21.sql', '::1', '2026-02-11 23:11:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('170', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-09-29.sql', '::1', '2026-02-11 23:11:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('171', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-10-06.sql', '::1', '2026-02-11 23:11:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('172', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-16-31.sql', '::1', '2026-02-11 23:18:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('173', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-23-19.sql', '::1', '2026-02-11 23:25:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('174', '321220', 'Database Restore', 'Restored database from: backup_2026-02-11_16-28-52.sql', '::1', '2026-02-12 07:44:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('175', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-46-34.sql', '::1', '2026-02-12 07:48:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('176', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-54-56.sql', '::1', '2026-02-12 07:56:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('177', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_16-54-56.sql', '::1', '2026-02-12 07:56:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('178', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-58-41.sql', '::1', '2026-02-12 08:00:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('179', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_17-08-19.sql', '::1', '2026-02-12 08:10:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('180', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_17-08-19.sql', '::1', '2026-02-12 08:10:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('181', '321220', 'Logged in', NULL, '::1', '2026-02-12 10:14:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('182', '321220', 'Logged in', NULL, '::1', '2026-02-12 10:24:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('183', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-12 10:30:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('184', '321220', 'Logged in', NULL, '::1', '2026-02-12 10:30:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('185', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_19-35-07.sql', '::1', '2026-02-12 10:36:54');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('186', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_19-35-07.sql', '::1', '2026-02-12 10:37:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('187', '321220', 'Database Backup', 'Created backup: backup_2026-02-12_02-02-25.sql', '::1', '2026-02-12 17:04:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('188', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-12_02-02-25.sql', '::1', '2026-02-12 17:04:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('189', '0', 'Failed login attempt (not approved)', 'Attempted username: johncarl', '::1', '2026-02-12 17:08:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('190', '321219', 'Logged in', NULL, '::1', '2026-02-12 17:09:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('191', '0', 'Failed login attempt (wrong password)', 'Attempted username: officer', '::1', '2026-02-12 17:10:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('192', '0', 'Logged in', NULL, '::1', '2026-02-12 17:10:58');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('193', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-12 17:12:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('194', '321220', 'Logged in', NULL, '::1', '2026-02-12 17:13:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('195', '321220', 'Logged in', NULL, '::1', '2026-02-12 18:00:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('196', '321220', 'Logged in', NULL, '::1', '2026-02-13 07:22:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('197', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-14 06:24:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('198', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-14 06:24:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('199', '321220', 'Logged in', NULL, '::1', '2026-02-14 06:24:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('200', '321220', 'Logged in', NULL, '::1', '2026-02-14 07:36:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('201', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-17 06:14:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('202', '321220', 'Logged in', NULL, '::1', '2026-02-17 06:14:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('203', '321220', 'Logged in', NULL, '::1', '2026-02-17 07:41:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('204', '321220', 'Logged in', NULL, '::1', '2026-02-17 08:56:30');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('205', '321220', 'Logged in', NULL, '::1', '2026-02-17 22:57:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('206', '321220', 'Database Backup', 'Created backup: backup_2026-02-17_15-05-51.sql', '::1', '2026-02-17 23:07:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('207', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-17_15-05-51.sql', '::1', '2026-02-17 23:07:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('208', '321220', 'Database Backup', 'Created backup: backup_2026-02-17_15-15-23.sql', '::1', '2026-02-17 23:17:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('209', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-17_15-15-23.sql', '::1', '2026-02-17 23:17:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('210', '321220', 'Logged in', NULL, '::1', '2026-02-18 07:25:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('211', '321220', 'Database Restore', 'Restored database from backup file: backup_2026-02-17_23-23-59.sql (415 queries executed)', '::1', '2026-02-18 15:28:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('212', '321220', 'Database Restore', 'Restored database from backup file: backup_2026-02-17_23-44-27.sql (416 queries executed)', '::1', '2026-02-18 23:46:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('213', '321220', 'Database Backup', 'Created backup: backup_2026-02-18_00-07-30.sql', '::1', '2026-02-19 00:09:24');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('214', '321220', 'Database Backup', 'Created backup: backup_2026-02-18_00-44-19.sql', '::1', '2026-02-19 00:46:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('215', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-18_00-44-19.sql', '::1', '2026-02-19 00:46:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('216', '321220', 'Logged in', NULL, '::1', '2026-02-19 01:01:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('217', '321220', 'Database Restore', 'Restored database from backup file: backup_2026-02-18_01-53-12.sql (445 queries executed)', '::1', '2026-02-19 10:12:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('218', '321220', 'Logged in', NULL, '::1', '2026-02-20 03:47:58');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('219', '321219', 'Logged in', NULL, '::1', '2026-02-20 22:01:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('220', '321220', 'Logged in', NULL, '::1', '2026-02-20 22:35:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('221', '321220', 'Logged in', NULL, '::1', '2026-02-23 00:50:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('222', '321220', 'Logged in', NULL, '::1', '2026-02-23 23:25:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('223', '321220', 'Logged in', NULL, '::1', '2026-02-23 23:52:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('224', '321220', 'Logged in', NULL, '::1', '2026-02-23 23:52:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('225', '321220', 'Logged in', NULL, '::1', '2026-02-24 01:37:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('226', '321220', 'Logged in', NULL, '::1', '2026-02-24 08:48:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('227', '321220', 'Logged in', NULL, '::1', '2026-02-27 23:59:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('228', '321220', 'Database Backup', 'Created backup: backup_2026-02-26_15-57-29.sql', '::1', '2026-02-27 23:59:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('229', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-26_15-57-29.sql', '::1', '2026-02-27 23:59:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('230', '321220', 'Logged in', NULL, '::1', '2026-02-28 01:33:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('231', '321220', 'Added announcement', 'Title: Aspernatur dolor ea', NULL, '2026-02-28 03:24:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('232', '321220', 'Added announcement', 'Title: Aspernatur dolor ea', NULL, '2026-02-28 03:24:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('233', '321220', 'Added announcement', 'Title: Aspernatur dolor ea', NULL, '2026-02-28 03:26:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('234', '321220', 'Added announcement', 'Title: General Assembly Meeting', NULL, '2026-02-28 03:31:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('235', '321216', 'Logged in', NULL, '::1', '2026-02-28 03:32:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('236', '321216', 'Added announcement', 'Title: Fishing Schedule & Safety Reminder', NULL, '2026-02-28 03:33:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('237', '321216', 'Added announcement', 'Title: Community Clean-Up Drive', NULL, '2026-02-28 03:34:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('238', '321216', 'Edited announcement', 'Edited Title: Community Clean-Up Drive', NULL, '2026-02-28 03:34:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('239', '321216', 'Added announcement', 'Title: Deadline for Membership Dues', NULL, '2026-02-28 03:35:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('240', '321216', 'Added announcement', 'Title: Weather Advisory (High Waves)', NULL, '2026-02-28 03:35:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('241', '321216', 'Logged in', NULL, '::1', '2026-02-28 04:20:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('242', '321216', 'Logged in', NULL, '::1', '2026-02-28 04:25:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('243', '321223', 'Logged in', NULL, '::1', '2026-02-28 12:57:12');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('244', '321220', 'Logged in', NULL, '::1', '2026-02-28 13:29:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('245', '321220', 'Logged in', NULL, '::1', '2026-02-28 13:30:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('246', '321223', 'Logged in', NULL, '::1', '2026-02-28 13:31:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('247', '321220', 'Logged in', NULL, '::1', '2026-02-28 13:35:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('248', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-03-06 15:26:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('249', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-03-06 15:26:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('250', '321220', 'Logged in', NULL, '::1', '2026-03-06 15:26:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('251', '321220', 'Logged in', NULL, '::1', '2026-03-06 15:35:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('252', '321220', 'Logged in', NULL, '::1', '2026-03-06 16:48:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('253', '321220', 'Database Backup', 'Created backup: backup_2026-03-04_20-25-22.sql', '::1', '2026-03-06 19:25:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('254', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-03-04_20-25-22.sql', '::1', '2026-03-06 19:25:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('255', '321220', 'Logged in', NULL, '::1', '2026-03-07 03:37:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('256', '321220', 'Database Backup', 'Created backup: backup_2026-03-05_17-13-40.sql', '::1', '2026-03-07 16:13:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('257', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-03-05_17-13-40.sql', '::1', '2026-03-07 16:13:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('258', '321220', 'Database Restore', 'Restored database from backup file: backup_2026-03-05_17-26-29.sql (523 queries executed)', '::1', '2026-03-07 08:29:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('1', '321192', 'Logged in', NULL, '::1', '2025-10-02 08:06:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('2', '321192', 'Logged in', NULL, '::1', '2025-10-02 08:11:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('3', '321192', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-10-02 08:30:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('4', '321192', 'Logged in', NULL, '::1', '2025-10-02 08:31:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('5', '321192', 'Restored member', 'Restored member: dfgdg dfdgdf dfdfd', '::1', '2025-10-02 08:38:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('6', '321192', 'Restored member: dfgdg dfdgdf dfdfd', 'Restored member: dfgdg dfdgdf dfdfd', NULL, '2025-10-02 08:45:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('7', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-02 08:55:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('8', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-02 08:57:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('9', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-02 08:58:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('10', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-02 09:02:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('11', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-02 09:06:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('12', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-02 09:06:58');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('13', '321192', 'Added announcement', 'Title: fgdfg', NULL, '2025-10-02 09:16:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('14', '321192', 'Added announcement', 'Title: erd', NULL, '2025-10-02 09:21:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('15', '321192', 'Added announcement', 'Title: tert', NULL, '2025-10-02 09:25:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('16', '321192', 'Updated announcement', 'Title: tert', NULL, '2025-10-02 09:25:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('17', '321192', 'Updated announcement', 'Title: tert', NULL, '2025-10-02 09:26:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('18', '321192', 'Updated event', 'Event: Red Sea International Sport Fishing Tournament', NULL, '2025-10-02 09:32:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('19', '321192', 'Added event', 'Event: Gone Fishing 2025!', NULL, '2025-10-02 09:34:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('20', '321192', 'Logged in', NULL, '::1', '2025-10-04 04:00:14');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('21', '321192', 'Logged in', NULL, '::1', '2025-10-04 04:23:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('22', '321211', 'Failed login attempt (not approved)', 'Attempted username: argie2', '::1', '2025-10-04 04:26:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('23', '321211', 'Failed login attempt (not approved)', 'Attempted username: argie2', '::1', '2025-10-04 04:27:20');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('24', '321211', 'Logged in', NULL, '::1', '2025-10-04 04:29:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('25', '321211', 'Logged in', NULL, '::1', '2025-10-04 04:42:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('26', '321192', 'Logged in', NULL, '::1', '2025-10-04 04:55:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('27', '321192', 'Restored member: dfgdg dfdgdf dfdfd', 'Restored member: dfgdg dfdgdf dfdfd', NULL, '2025-10-04 05:00:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('28', '321192', 'Archived officer ID: 35', 'Archived officer ID: 35', NULL, '2025-10-04 05:36:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('29', '321192', 'Restored officer: Jovelyn S Buena', 'Restored officer: Jovelyn S Buena', NULL, '2025-10-04 05:43:51');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('30', '321192', 'Archived officer ID: 43', 'Archived officer ID: 43', NULL, '2025-10-04 05:43:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('31', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 05:56:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('32', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 05:57:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('33', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 06:00:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('34', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 06:00:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('35', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 06:00:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('36', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 06:02:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('37', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 06:02:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('38', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 06:03:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('39', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-04 06:04:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('40', '321192', 'Logged in', NULL, '::1', '2025-10-04 08:58:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('41', '321211', 'Logged in', NULL, '::1', '2025-10-04 09:16:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('42', '321192', 'Logged in', NULL, '::1', '2025-10-04 09:16:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('43', '321192', 'Logged in', NULL, '::1', '2025-10-05 03:46:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('44', '321192', 'Logged in', NULL, '::1', '2025-10-10 00:09:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('45', '321192', 'Logged in', NULL, '::1', '2025-10-11 00:06:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('46', '321192', 'Added announcement', 'Title: sadasd', NULL, '2025-10-11 00:49:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('47', '321192', 'Edited announcement', 'Edited Title: sadasd', NULL, '2025-10-11 00:52:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('48', '321192', 'Added announcement', 'Title: sdfsdf', NULL, '2025-10-11 00:53:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('49', '321192', 'Added announcement', 'Title: sdfsdf', NULL, '2025-10-11 00:56:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('50', '321192', 'Edited announcement', 'Edited Title: sadasd', NULL, '2025-10-11 01:13:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('51', '321192', 'Edited announcement', 'Edited Title: sadasd', NULL, '2025-10-11 01:19:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('52', '321192', 'Edited announcement', 'Edited Title: sadasd', NULL, '2025-10-11 01:20:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('53', '321192', 'Edited announcement', 'Edited Title: Community Fishing Day', NULL, '2025-10-11 02:49:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('54', '321192', 'Edited announcement', 'Edited Title: Clean-Up Drive', NULL, '2025-10-11 02:49:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('55', '321192', 'Logged in', NULL, '::1', '2025-10-11 19:28:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('56', '321192', 'Logged in', NULL, '::1', '2025-10-11 21:23:12');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('57', '321216', 'Failed login attempt (not approved)', 'Attempted username: alexa', '::1', '2025-10-15 01:43:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('58', '321218', 'Failed login attempt (not approved)', 'Attempted username: burn', '::1', '2025-10-15 01:46:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('59', NULL, 'Failed login attempt (user not found)', 'Attempted username: burnw', '::1', '2025-10-15 01:47:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('60', '321216', 'Failed login attempt (not approved)', 'Attempted username: alexa', '::1', '2025-10-15 06:58:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('61', '321192', 'Logged in', NULL, '::1', '2025-10-18 21:17:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('62', '321192', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-10-18 22:22:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('63', '321192', 'Logged in', NULL, '::1', '2025-10-18 22:22:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('64', '321192', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-10-18 22:33:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('65', '321192', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-10-18 22:33:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('66', '321192', 'Logged in', NULL, '::1', '2025-10-18 22:33:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('67', '321192', 'Logged in', NULL, '::1', '2025-10-18 22:34:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('68', '321211', 'Logged in', NULL, '::1', '2025-10-18 22:35:30');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('69', '321211', 'Failed login attempt (wrong password)', 'Attempted username: argie2', '::1', '2025-10-18 22:45:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('70', '321211', 'Logged in', NULL, '::1', '2025-10-18 22:45:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('71', '321192', 'Logged in', NULL, '::1', '2025-10-18 23:09:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('72', '321192', 'Logged in', NULL, '::1', '2025-10-18 23:27:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('73', '321197', 'Failed login attempt (wrong password)', 'Attempted username: jovelyn', '::1', '2025-10-18 23:28:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('74', '321197', 'Failed login attempt (wrong password)', 'Attempted username: jovelyn', '::1', '2025-10-18 23:28:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('75', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-18 23:28:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('76', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-18 23:29:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('77', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-18 23:29:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('78', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-18 23:29:14');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('79', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-18 23:29:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('80', '321219', 'Logged in', NULL, '::1', '2025-10-18 23:31:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('81', NULL, 'Failed login attempt (user not found)', 'Attempted username: paimon', '::1', '2025-10-18 23:33:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('82', '321219', 'Logged in', NULL, '::1', '2025-10-18 23:36:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('83', '321211', 'Failed login attempt (wrong password)', 'Attempted username: argie2', '::1', '2025-10-19 00:24:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('84', '321211', 'Logged in', NULL, '::1', '2025-10-19 00:25:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('85', '321211', 'Logged in', NULL, '::1', '2025-10-19 00:41:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('86', '321220', 'Logged in', NULL, '::1', '2025-10-19 00:42:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('87', '321211', 'Logged in', NULL, '::1', '2025-10-19 00:42:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('88', '321211', 'Logged in', NULL, '::1', '2025-10-19 01:09:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('89', '321211', 'Logged in', NULL, '::1', '2025-10-19 01:57:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('90', '321211', 'Logged in', NULL, '::1', '2025-10-19 02:03:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('91', '321215', 'Failed login attempt (not approved)', 'Attempted username: avina', '::1', '2025-10-19 02:19:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('92', '321216', 'Logged in', NULL, '::1', '2025-10-19 02:19:51');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('93', '321220', 'Logged in', NULL, '::1', '2025-10-19 02:37:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('94', '321220', 'Logged in', NULL, '::1', '2025-10-19 02:41:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('95', '321219', 'Logged in', NULL, '::1', '2025-10-19 02:43:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('96', '321220', 'Logged in', NULL, '::1', '2025-10-19 02:45:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('97', '321220', 'Logged in', NULL, '::1', '2025-10-19 02:48:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('98', '321219', 'Logged in', NULL, '::1', '2025-10-19 02:49:14');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('99', '321219', 'Logged in', NULL, '::1', '2025-10-19 02:49:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('100', '321219', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-11-30 02:36:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('101', '321219', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-11-30 02:36:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('102', '321219', 'Logged in', NULL, '::1', '2025-11-30 02:36:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('103', '321219', 'Logged in', NULL, '::1', '2025-11-30 03:17:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('104', '321219', 'Logged in', NULL, '::1', '2025-11-30 05:16:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('105', '321220', 'Logged in', NULL, '::1', '2025-11-30 06:09:37');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('106', '321219', 'Logged in', NULL, '::1', '2025-11-30 06:15:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('107', '321219', 'Logged in', NULL, '::1', '2025-11-30 06:33:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('108', '321219', 'Logged in', NULL, '::1', '2025-11-30 06:33:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('109', '321220', 'Logged in', NULL, '::1', '2025-11-30 07:09:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('110', '321219', 'Logged in', NULL, '::1', '2025-11-30 23:03:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('111', '321219', 'Logged in', NULL, '::1', '2025-11-30 23:11:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('112', '321222', 'Logged in', NULL, '::1', '2025-11-30 23:20:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('113', '321219', 'Logged in', NULL, '::1', '2025-11-30 23:40:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('114', '321219', 'Logged in', NULL, '::1', '2025-12-01 04:19:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('115', '321219', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2026-01-13 10:35:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('116', '321219', 'Logged in', NULL, '::1', '2026-01-13 10:35:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('117', '321219', 'Added announcement', 'Title: titen', NULL, '2026-01-13 10:37:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('118', '321219', 'Logged in', NULL, '::1', '2026-01-17 06:38:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('119', '321215', 'Failed login attempt (not approved)', 'Attempted username: avina', '::1', '2026-01-17 09:55:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('120', '321220', 'Logged in', NULL, '::1', '2026-01-17 09:55:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('121', '321220', 'Logged in', NULL, '::1', '2026-01-17 09:57:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('122', '0', 'Logged in', NULL, '::1', '2026-01-17 09:57:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('123', '321219', 'Logged in', NULL, '::1', '2026-01-20 04:02:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('124', '321219', 'Logged in', NULL, '::1', '2026-01-20 04:43:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('125', '321219', 'Logged in', NULL, '::1', '2026-01-21 20:43:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('126', '321219', 'Logged in', NULL, '::1', '2026-01-25 15:58:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('127', '321219', 'Logged in', NULL, '::1', '2026-01-25 21:12:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('128', '0', 'Logged in', NULL, '::1', '2026-01-25 21:14:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('129', '321220', 'Logged in', NULL, '::1', '2026-01-25 21:15:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('130', '321220', 'Logged in', NULL, '::1', '2026-01-25 21:42:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('131', '321220', 'Added announcement', 'Title: Qui exercitation sun', NULL, '2026-01-25 21:45:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('132', '321220', 'Added announcement', 'Title: Qui adipisicing minu', NULL, '2026-01-25 21:59:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('133', '321220', 'Added announcement', 'Title: Optio mollitia duci', NULL, '2026-01-25 21:59:54');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('134', '321220', 'Edited announcement', 'Edited Title: Fishing Permit Renewal', NULL, '2026-01-25 22:01:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('135', '321219', 'Logged in', NULL, '::1', '2026-01-26 05:15:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('136', '321219', 'Logged in', NULL, '::1', '2026-01-27 22:17:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('137', '321219', 'Logged in', NULL, '::1', '2026-01-27 22:33:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('138', '321220', 'Logged in', NULL, '::1', '2026-01-27 22:33:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('139', '321220', 'Restored officer: Cristopher M. De Jesus', 'Restored officer: Cristopher M. De Jesus', NULL, '2026-01-27 22:41:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('140', '321219', 'Logged in', NULL, '::1', '2026-02-09 02:40:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('141', '321220', 'Logged in', NULL, '::1', '2026-02-09 02:42:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('142', '0', 'Failed login attempt (not approved)', 'Attempted username: katkat', '::1', '2026-02-09 03:02:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('143', '321220', 'Logged in', NULL, '::1', '2026-02-09 03:38:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('144', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-19-29.sql', '::1', '2026-02-09 04:39:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('145', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_13-44-50.sql (61,563 bytes)', '::1', '2026-02-09 04:46:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('146', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-44-50.sql', '::1', '2026-02-09 04:46:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('147', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_13-48-02.sql (62,034 bytes)', '::1', '2026-02-09 04:49:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('148', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-48-02.sql', '::1', '2026-02-09 04:49:51');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('149', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_13-50-19.sql (62,505 bytes)', '::1', '2026-02-09 04:52:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('150', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-50-19.sql', '::1', '2026-02-09 04:52:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('151', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-50-19.sql', '::1', '2026-02-09 05:00:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('152', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_14-05-11.sql (63,208 bytes)', '::1', '2026-02-09 05:06:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('153', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_14-05-11.sql', '::1', '2026-02-09 05:06:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('154', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_14-06-39.sql (63,679 bytes)', '::1', '2026-02-09 05:08:24');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('155', '321220', 'Delete Backup', 'Deleted backup file: backup_2025-10-02-16-43-24.sql', '::1', '2026-02-09 05:19:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('156', '321220', 'Delete Backup', 'Deleted backup file: test_backup_2026-02-09_14-35-02.sql', '::1', '2026-02-09 05:37:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('157', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_14-36-04.sql (64,233 bytes)', '::1', '2026-02-09 05:37:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('158', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_14-36-10.sql (64,687 bytes)', '::1', '2026-02-09 05:37:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('159', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_14-36-10.sql', '::1', '2026-02-09 05:38:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('160', '321220', 'Logged in', NULL, '::1', '2026-02-09 20:08:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('161', '321220', 'Logged in', NULL, '::1', '2026-02-11 06:22:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('162', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-11 06:26:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('163', '321220', 'Logged in', NULL, '::1', '2026-02-11 06:27:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('164', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_15-53-25.sql (67,247 bytes)', '::1', '2026-02-11 06:55:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('165', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_15-53-25.sql', '::1', '2026-02-11 06:55:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('166', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_15-53-25.sql', '::1', '2026-02-11 06:56:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('167', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_15-55-15.sql (68,121 bytes)', '::1', '2026-02-11 06:57:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('168', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_15-55-15.sql', '::1', '2026-02-11 06:57:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('169', '321220', 'Database Restore', 'Restored database from: backup_2026-02-11_16-00-21.sql', '::1', '2026-02-11 15:11:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('170', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-09-29.sql', '::1', '2026-02-11 15:11:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('171', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-10-06.sql', '::1', '2026-02-11 15:11:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('172', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-16-31.sql', '::1', '2026-02-11 15:18:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('173', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-23-19.sql', '::1', '2026-02-11 15:25:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('174', '321220', 'Database Restore', 'Restored database from: backup_2026-02-11_16-28-52.sql', '::1', '2026-02-11 23:44:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('175', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-46-34.sql', '::1', '2026-02-11 23:48:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('176', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-54-56.sql', '::1', '2026-02-11 23:56:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('177', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_16-54-56.sql', '::1', '2026-02-11 23:56:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('178', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-58-41.sql', '::1', '2026-02-12 00:00:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('179', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_17-08-19.sql', '::1', '2026-02-12 00:10:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('180', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_17-08-19.sql', '::1', '2026-02-12 00:10:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('181', '321220', 'Logged in', NULL, '::1', '2026-02-12 02:14:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('182', '321220', 'Logged in', NULL, '::1', '2026-02-12 02:24:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('183', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-12 02:30:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('184', '321220', 'Logged in', NULL, '::1', '2026-02-12 02:30:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('185', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_19-35-07.sql', '::1', '2026-02-12 02:36:54');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('186', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_19-35-07.sql', '::1', '2026-02-12 02:37:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('187', '321220', 'Database Backup', 'Created backup: backup_2026-02-12_02-02-25.sql', '::1', '2026-02-12 09:04:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('188', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-12_02-02-25.sql', '::1', '2026-02-12 09:04:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('189', '0', 'Failed login attempt (not approved)', 'Attempted username: johncarl', '::1', '2026-02-12 09:08:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('190', '321219', 'Logged in', NULL, '::1', '2026-02-12 09:09:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('191', '0', 'Failed login attempt (wrong password)', 'Attempted username: officer', '::1', '2026-02-12 09:10:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('192', '0', 'Logged in', NULL, '::1', '2026-02-12 09:10:58');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('193', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-12 09:12:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('194', '321220', 'Logged in', NULL, '::1', '2026-02-12 09:13:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('195', '321220', 'Logged in', NULL, '::1', '2026-02-12 10:00:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('196', '321220', 'Logged in', NULL, '::1', '2026-02-12 23:22:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('197', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-13 22:24:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('198', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-13 22:24:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('199', '321220', 'Logged in', NULL, '::1', '2026-02-13 22:24:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('200', '321220', 'Logged in', NULL, '::1', '2026-02-13 23:36:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('201', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-16 22:14:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('202', '321220', 'Logged in', NULL, '::1', '2026-02-16 22:14:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('203', '321220', 'Logged in', NULL, '::1', '2026-02-16 23:41:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('204', '321220', 'Logged in', NULL, '::1', '2026-02-17 00:56:30');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('205', '321220', 'Logged in', NULL, '::1', '2026-02-17 14:57:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('206', '321220', 'Database Backup', 'Created backup: backup_2026-02-17_15-05-51.sql', '::1', '2026-02-17 15:07:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('207', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-17_15-05-51.sql', '::1', '2026-02-17 15:07:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('208', '321220', 'Database Backup', 'Created backup: backup_2026-02-17_15-15-23.sql', '::1', '2026-02-17 15:17:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('209', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-17_15-15-23.sql', '::1', '2026-02-17 15:17:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('210', '321220', 'Logged in', NULL, '::1', '2026-02-17 23:25:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('211', '321220', 'Database Restore', 'Restored database from backup file: backup_2026-02-17_23-23-59.sql (415 queries executed)', '::1', '2026-02-18 07:28:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('212', '321220', 'Database Restore', 'Restored database from backup file: backup_2026-02-17_23-44-27.sql (416 queries executed)', '::1', '2026-02-18 15:46:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('213', '321220', 'Database Backup', 'Created backup: backup_2026-02-18_00-07-30.sql', '::1', '2026-02-18 16:09:24');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('214', '321220', 'Database Backup', 'Created backup: backup_2026-02-18_00-44-19.sql', '::1', '2026-02-18 16:46:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('215', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-18_00-44-19.sql', '::1', '2026-02-18 16:46:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('216', '321220', 'Logged in', NULL, '::1', '2026-02-18 17:01:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('217', '321220', 'Database Restore', 'Restored database from backup file: backup_2026-02-18_01-53-12.sql (445 queries executed)', '::1', '2026-02-19 02:12:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('218', '321220', 'Logged in', NULL, '::1', '2026-02-19 19:47:58');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('219', '321219', 'Logged in', NULL, '::1', '2026-02-20 14:01:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('220', '321220', 'Logged in', NULL, '::1', '2026-02-20 14:35:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('221', '321220', 'Logged in', NULL, '::1', '2026-02-22 16:50:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('222', '321220', 'Logged in', NULL, '::1', '2026-02-23 15:25:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('223', '321220', 'Logged in', NULL, '::1', '2026-02-23 15:52:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('224', '321220', 'Logged in', NULL, '::1', '2026-02-23 15:52:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('225', '321220', 'Logged in', NULL, '::1', '2026-02-23 17:37:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('226', '321220', 'Logged in', NULL, '::1', '2026-02-24 00:48:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('227', '321220', 'Logged in', NULL, '::1', '2026-02-27 15:59:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('228', '321220', 'Database Backup', 'Created backup: backup_2026-02-26_15-57-29.sql', '::1', '2026-02-27 15:59:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('229', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-26_15-57-29.sql', '::1', '2026-02-27 15:59:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('230', '321220', 'Logged in', NULL, '::1', '2026-02-27 17:33:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('231', '321220', 'Added announcement', 'Title: Aspernatur dolor ea', NULL, '2026-02-27 19:24:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('232', '321220', 'Added announcement', 'Title: Aspernatur dolor ea', NULL, '2026-02-27 19:24:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('233', '321220', 'Added announcement', 'Title: Aspernatur dolor ea', NULL, '2026-02-27 19:26:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('234', '321220', 'Added announcement', 'Title: General Assembly Meeting', NULL, '2026-02-27 19:31:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('235', '321216', 'Logged in', NULL, '::1', '2026-02-27 19:32:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('236', '321216', 'Added announcement', 'Title: Fishing Schedule & Safety Reminder', NULL, '2026-02-27 19:33:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('237', '321216', 'Added announcement', 'Title: Community Clean-Up Drive', NULL, '2026-02-27 19:34:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('238', '321216', 'Edited announcement', 'Edited Title: Community Clean-Up Drive', NULL, '2026-02-27 19:34:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('239', '321216', 'Added announcement', 'Title: Deadline for Membership Dues', NULL, '2026-02-27 19:35:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('240', '321216', 'Added announcement', 'Title: Weather Advisory (High Waves)', NULL, '2026-02-27 19:35:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('241', '321216', 'Logged in', NULL, '::1', '2026-02-27 20:20:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('242', '321216', 'Logged in', NULL, '::1', '2026-02-27 20:25:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('243', '321223', 'Logged in', NULL, '::1', '2026-02-28 04:57:12');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('244', '321220', 'Logged in', NULL, '::1', '2026-02-28 05:29:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('245', '321220', 'Logged in', NULL, '::1', '2026-02-28 05:30:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('246', '321223', 'Logged in', NULL, '::1', '2026-02-28 05:31:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('247', '321220', 'Logged in', NULL, '::1', '2026-02-28 05:35:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('248', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-03-06 07:26:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('249', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-03-06 07:26:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('250', '321220', 'Logged in', NULL, '::1', '2026-03-06 07:26:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('251', '321220', 'Logged in', NULL, '::1', '2026-03-06 07:35:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('252', '321220', 'Logged in', NULL, '::1', '2026-03-06 08:48:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('253', '321220', 'Database Backup', 'Created backup: backup_2026-03-04_20-25-22.sql', '::1', '2026-03-06 11:25:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('254', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-03-04_20-25-22.sql', '::1', '2026-03-06 11:25:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('255', '321220', 'Logged in', NULL, '::1', '2026-03-06 19:37:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('256', '321220', 'Database Backup', 'Created backup: backup_2026-03-05_17-13-40.sql', '::1', '2026-03-07 08:13:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('257', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-03-05_17-13-40.sql', '::1', '2026-03-07 08:13:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('258', '321220', 'Database Restore', 'Restored database from backup file: backup_2026-03-05_17-26-29.sql (523 queries executed)', '::1', '2026-03-07 00:29:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-03-14 09:27:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321220', 'Logged in', NULL, '::1', '2026-03-14 09:27:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-03-26 12:02:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321219', 'Logged in', NULL, '::1', '2026-03-26 12:03:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-03-26 12:09:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321219', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2026-03-26 12:10:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321219', 'Logged in', NULL, '::1', '2026-03-26 12:10:37');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-03-26 12:11:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-03-26 16:24:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-01 14:49:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-01 22:45:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321223', 'Failed login attempt (wrong password)', 'Attempted username: officer', '::1', '2026-04-01 23:27:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321223', 'Logged in', NULL, '::1', '2026-04-01 23:33:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-02 00:00:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Database Backup', 'Created backup: backup_2026-03-31_18-59-35.sql', '::1', '2026-04-02 00:59:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Download Backup', 'Downloaded backup file: backup_2026-03-31_18-59-35.sql', '::1', '2026-04-02 00:59:37');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-02 01:05:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-02 18:52:20');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-04 00:22:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-04 01:14:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Restored member: Lev B Washington', 'Restored member: Lev B Washington', NULL, '2026-04-04 02:33:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Restored member: Robert V takasa', 'Restored member: Robert V takasa', NULL, '2026-04-04 02:35:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-04 21:45:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Database Backup', 'Created backup: backup_2026-04-03_19-05-09.sql', '::1', '2026-04-05 01:05:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Download Backup', 'Downloaded backup file: backup_2026-04-03_19-05-09.sql', '::1', '2026-04-05 01:05:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-06 23:13:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Database Backup', 'Created backup: backup_2026-04-05_18-27-59.sql', '::1', '2026-04-07 00:27:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Download Backup', 'Downloaded backup file: backup_2026-04-05_18-27-59.sql', '::1', '2026-04-07 00:28:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Database Backup', 'Created backup: backup_2026-04-05_19-24-09.sql', '::1', '2026-04-07 01:24:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Download Backup', 'Downloaded backup file: backup_2026-04-05_19-24-09.sql', '::1', '2026-04-07 01:24:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-07 01:52:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-11 03:30:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321223', 'Failed login attempt (wrong password)', 'Attempted username: officer', '::1', '2026-04-11 09:32:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Failed login attempt (wrong password)', 'Attempted username: jovelyn', '::1', '2026-04-11 09:32:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321223', 'Logged in', NULL, '::1', '2026-04-11 09:32:58');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-11 09:33:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Database Backup', 'Created backup: backup_2026-04-10_03-36-09.sql', '::1', '2026-04-11 09:36:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-11 13:23:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-11 20:01:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-14 22:08:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-15 01:06:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-15 01:06:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321219', 'Logged in', NULL, '::1', '2026-04-15 02:28:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321219', 'Logged in', NULL, '::1', '2026-04-15 02:29:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321219', 'Logged in', NULL, '::1', '2026-04-15 02:29:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321219', 'Database Backup', 'Created backup: backup_2026-04-14_05-07-58.sql', '::1', '2026-04-15 11:07:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321219', 'Download Backup', 'Downloaded backup file: backup_2026-04-14_05-07-58.sql', '::1', '2026-04-15 11:08:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-15 15:40:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321219', 'Logged in', NULL, '::1', '2026-04-15 19:32:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321219', 'Logged in', NULL, '::1', '2026-04-15 19:50:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321223', 'Failed login attempt (wrong password)', 'Attempted username: officer', '::1', '2026-04-17 22:28:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321219', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2026-04-17 22:28:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321223', 'Logged in', NULL, '::1', '2026-04-17 22:29:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321219', 'Logged in', NULL, '::1', '2026-04-17 22:56:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Failed login attempt (wrong password)', 'Attempted username: jovelyn', '::1', '2026-04-17 23:06:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Failed login attempt (wrong password)', 'Attempted username: jovelyn', '::1', '2026-04-17 23:06:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321219', 'Logged in', NULL, '::1', '2026-04-17 23:07:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321219', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2026-04-18 01:25:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321219', 'Logged in', NULL, '::1', '2026-04-18 01:25:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-18 19:19:20');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Failed login attempt (wrong password)', 'Attempted username: jovelyn', '::1', '2026-04-19 18:53:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-19 18:53:58');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-20 14:07:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-20 15:54:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-20 15:56:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Added announcement', 'Title: Natus ut enim quibus', NULL, '2026-04-20 16:06:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Edited announcement', 'Edited Title: General Assembly Meeting', NULL, '2026-04-20 16:06:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Added announcement', 'Title: Boat Safety Training', NULL, '2026-04-20 16:18:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Edited announcement', 'Edited Title: Boat Safety Training', NULL, '2026-04-20 16:20:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Added announcement', 'Title: Disaster Preparedness Seminar', NULL, '2026-04-20 16:21:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Failed login attempt (wrong password)', 'Attempted username: jovelyn', '::1', '2026-04-20 16:36:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-20 16:36:12');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'System Backup', 'Created system backup: system_backup_2026-04-19_11-32-30.zip', '::1', '2026-04-20 17:32:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Download Backup', 'Downloaded backup file: system_backup_2026-04-19_11-32-30.zip', '::1', '2026-04-20 17:32:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'System Backup', 'Created system backup: system_backup_2026-04-19_11-35-33.zip', '::1', '2026-04-20 17:35:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'System Backup', 'Created system backup: system_backup_2026-04-19_11-35-45.zip', '::1', '2026-04-20 17:35:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Download Backup', 'Downloaded backup file: system_backup_2026-04-19_11-35-45.zip', '::1', '2026-04-20 17:35:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'System Backup', 'Created system backup: system_backup_2026-04-19_11-37-52.zip', '::1', '2026-04-20 17:37:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Download Backup', 'Downloaded backup file: system_backup_2026-04-19_11-37-52.zip', '::1', '2026-04-20 17:37:58');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'System Backup', 'Created system backup: system_backup_2026-04-19_11-38-18.zip', '::1', '2026-04-20 17:38:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'System Backup', 'Created system backup: system_backup_2026-04-19_11-41-40.zip', '::1', '2026-04-20 17:41:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Download Backup', 'Downloaded backup file: system_backup_2026-04-19_11-41-40.zip', '::1', '2026-04-20 17:41:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'System Backup', 'Created system backup: system_backup_2026-04-19_11-49-06.zip', '::1', '2026-04-20 17:49:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Download Backup', 'Downloaded backup file: system_backup_2026-04-19_11-49-06.zip', '::1', '2026-04-20 17:49:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Logged in', NULL, '::1', '2026-04-20 17:51:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'System Restore', 'Restored system from ZIP: system_backup_2026-04-19_11-59-56.zip (715 files, 1005 SQL queries)', '::1', '2026-04-20 10:00:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Database Backup', 'Created backup: backup_2026-04-19_12-23-03.sql', '::1', '2026-04-20 10:23:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Download Backup', 'Downloaded backup file: backup_2026-04-19_12-23-03.sql', '::1', '2026-04-20 10:23:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Database Backup', 'Created backup: backup_2026-04-19_12-23-37.sql', '::1', '2026-04-20 10:23:37');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Download Backup', 'Downloaded backup file: backup_2026-04-19_12-23-37.sql', '::1', '2026-04-20 10:23:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Database Backup', 'Created backup: backup_2026-04-19_12-24-30.sql', '::1', '2026-04-20 10:24:30');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Database Backup', 'Created backup: backup_2026-04-19_12-28-18.sql', '::1', '2026-04-20 10:28:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Database Backup', 'Created backup: backup_2026-04-19_12-28-21.sql', '::1', '2026-04-20 10:28:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Download Backup', 'Downloaded backup file: backup_2026-04-19_12-28-21.sql', '::1', '2026-04-20 10:28:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Database Backup', 'Created backup: backup_2026-04-19_12-28-35.sql', '::1', '2026-04-20 10:28:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Database Restore', 'Restored from backup_2026-04-19_12-35-33.sql: 1021 success, 0 failed', '::1', '2026-04-20 02:36:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'System Backup', 'Created system backup: system_backup_2026-04-19_12-36-13.zip', '::1', '2026-04-20 02:36:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Download Backup', 'Downloaded backup file: system_backup_2026-04-19_12-36-13.zip', '::1', '2026-04-20 02:36:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Database Backup', 'Created backup: backup_2026-04-19_12-36-21.sql', '::1', '2026-04-20 02:36:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Download Backup', 'Downloaded backup file: backup_2026-04-19_12-36-21.sql', '::1', '2026-04-20 02:36:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'Database Backup', 'Created backup: backup_2026-04-19_12-36-28.sql', '::1', '2026-04-20 02:36:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('0', '321229', 'System Restore', 'Restored system from ZIP: system_backup_2026-04-19_12-36-41.zip (716 files, 1029 SQL queries)', '::1', '2026-04-19 18:36:57');


-- Table: announcements
DROP TABLE IF EXISTS `announcements`;

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `date_posted` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'General',
  `expiry_date` date DEFAULT NULL,
  `posted_by` varchar(255) DEFAULT 'Admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `announcements`
INSERT INTO `announcements` (`id`, `title`, `content`, `date_posted`, `image`, `category`, `expiry_date`, `posted_by`) VALUES ('40', 'Fishing Schedule & Safety Reminder', 'Reminder to all boat operators and fishermen: please follow the approved fishing schedule and always wear life vests while at sea. Check weather updates before departure and avoid sailing during strong winds.', '2026-02-26', NULL, 'Fishing', '2026-04-03', 'Alexa');
INSERT INTO `announcements` (`id`, `title`, `content`, `date_posted`, `image`, `category`, `expiry_date`, `posted_by`) VALUES ('41', 'Community Clean-Up Drive', 'Join our Clean-Up Drive on March 2, 2026 (Monday), 6:00 AM. Assembly point: Covered Court. Bring gloves, sacks, and water. Let’s keep our shoreline clean and safe for everyone.', '2026-02-26', NULL, 'Event', '2026-05-14', 'Alexa');
INSERT INTO `announcements` (`id`, `title`, `content`, `date_posted`, `image`, `category`, `expiry_date`, `posted_by`) VALUES ('43', 'Weather Advisory (High Waves)', 'Due to the latest weather advisory, all small boats are advised not to sail until further notice. Secure boats and fishing equipment. Stay alert for official updates from the barangay and local authorities.', '2026-02-26', NULL, 'Emergency', '2026-02-27', 'Alexa');
INSERT INTO `announcements` (`id`, `title`, `content`, `date_posted`, `image`, `category`, `expiry_date`, `posted_by`) VALUES ('40', 'Fishing Schedule & Safety Reminder', 'Reminder to all boat operators and fishermen: please follow the approved fishing schedule and always wear life vests while at sea. Check weather updates before departure and avoid sailing during strong winds.', '2026-02-26', NULL, 'Fishing', '2026-04-03', 'Alexa');
INSERT INTO `announcements` (`id`, `title`, `content`, `date_posted`, `image`, `category`, `expiry_date`, `posted_by`) VALUES ('41', 'Community Clean-Up Drive', 'Join our Clean-Up Drive on March 2, 2026 (Monday), 6:00 AM. Assembly point: Covered Court. Bring gloves, sacks, and water. Let’s keep our shoreline clean and safe for everyone.', '2026-02-26', NULL, 'Event', '2026-05-14', 'Alexa');
INSERT INTO `announcements` (`id`, `title`, `content`, `date_posted`, `image`, `category`, `expiry_date`, `posted_by`) VALUES ('43', 'Weather Advisory (High Waves)', 'Due to the latest weather advisory, all small boats are advised not to sail until further notice. Secure boats and fishing equipment. Stay alert for official updates from the barangay and local authorities.', '2026-02-26', NULL, 'Emergency', '2026-02-27', 'Alexa');
INSERT INTO `announcements` (`id`, `title`, `content`, `date_posted`, `image`, `category`, `expiry_date`, `posted_by`) VALUES ('0', 'Boat Safety Training', 'All members are invited to attend the Boat Safety Training organized by the association. This training aims to provide important knowledge on safety measures, emergency procedures, and proper boat handling while at sea. Members are encouraged to participate to ensure their safety and preparedness during fishing activities.', '2026-04-19', NULL, 'Fishing', NULL, 'Jovelyn');
INSERT INTO `announcements` (`id`, `title`, `content`, `date_posted`, `image`, `category`, `expiry_date`, `posted_by`) VALUES ('0', 'Disaster Preparedness Seminar', 'A Disaster Preparedness Seminar will be held for all members of the association. This event aims to educate participants on how to prepare for natural disasters such as typhoons and floods, especially while working at sea. Important safety guidelines and emergency response procedures will be discussed to ensure the safety of all members.', '2026-04-19', NULL, 'Reminder', NULL, 'Jovelyn');


-- Table: archived_announcements
DROP TABLE IF EXISTS `archived_announcements`;

CREATE TABLE `archived_announcements` (
  `id` int(10) unsigned NOT NULL,
  `original_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT 'General',
  `date_posted` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `archived_announcements`
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('2', '30', 'Clean-Up Drive', 'The Association will conduct a coastal clean-up this coming Sunday at 6:00 AM. Please bring gloves, sacks, and cleaning tools', 'Screenshot 2025-08-25 221659.png', 'Announcement', '2025-06-17 00:00:00', '2026-01-17 15:52:25');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('3', '34', 'Qui adipisicing minu', 'Laudantium culpa vo', '1769407049_testFile.png', 'Announcement', '2026-01-25 00:00:00', '2026-01-26 05:59:13');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('4', '35', 'Optio mollitia duci', 'Accusamus est praes', '1769407100_testFile.png', 'Announcement', '2026-01-25 00:00:00', '2026-01-26 06:00:07');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('5', '32', 'Fishing Permit Renewal', 'Members are reminded to renew their fishing permits before the end of the month to avoid penalties.hehe', NULL, 'Announcement', '2025-06-17 00:00:00', '2026-01-26 06:06:01');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('6', '33', ' Fishing Tournament Announcement!', 'Join us this weekend for a friendly Fishing Tournament at the riverside! Cast your lines, compete for the biggest catch, and enjoy a day of fun and camaraderie. Don\'t forget your gear—see you there!\r\n\r\n', NULL, 'Announcement', '2025-06-16 00:00:00', '2026-02-28 03:27:10');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('7', '11', 'Let’s Go Fishing!', 'Calling all fishing enthusiasts! Spend a peaceful day by the water and reel in some fun. Bring your bait, rod, and good vibes!\r\n\r\n', NULL, 'Announcement', '2025-06-16 00:00:00', '2026-02-28 03:27:23');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('8', '10', 'Community Fishing Day', 'Grab your rods and join us for a relaxing Fishing Day by the lake! It’s the perfect time to unwind, bond with fellow anglers, and enjoy the great outdoors. Open to all ages—everyone’s welcome!', 'Screenshot 2025-09-07 225612.png', 'Announcement', '2025-06-16 00:00:00', '2026-02-28 03:29:45');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('9', '38', 'Aspernatur dolor ea', 'Ipsa dolorum sunt', '1772105097_testFile.png', 'Announcement', '2026-02-26 00:00:00', '2026-02-28 03:29:50');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('10', '37', 'Aspernatur dolor ea', 'Ipsa dolorum sunt', '1772104983_testFile.png', 'Announcement', '2026-02-26 00:00:00', '2026-02-28 03:29:54');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('2', '30', 'Clean-Up Drive', 'The Association will conduct a coastal clean-up this coming Sunday at 6:00 AM. Please bring gloves, sacks, and cleaning tools', 'Screenshot 2025-08-25 221659.png', 'Announcement', '2025-06-17 00:00:00', '2026-01-17 07:52:25');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('3', '34', 'Qui adipisicing minu', 'Laudantium culpa vo', '1769407049_testFile.png', 'Announcement', '2026-01-25 00:00:00', '2026-01-25 21:59:13');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('4', '35', 'Optio mollitia duci', 'Accusamus est praes', '1769407100_testFile.png', 'Announcement', '2026-01-25 00:00:00', '2026-01-25 22:00:07');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('5', '32', 'Fishing Permit Renewal', 'Members are reminded to renew their fishing permits before the end of the month to avoid penalties.hehe', NULL, 'Announcement', '2025-06-17 00:00:00', '2026-01-25 22:06:01');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('6', '33', ' Fishing Tournament Announcement!', 'Join us this weekend for a friendly Fishing Tournament at the riverside! Cast your lines, compete for the biggest catch, and enjoy a day of fun and camaraderie. Don\'t forget your gear—see you there!\r\n\r\n', NULL, 'Announcement', '2025-06-16 00:00:00', '2026-02-27 19:27:10');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('7', '11', 'Let’s Go Fishing!', 'Calling all fishing enthusiasts! Spend a peaceful day by the water and reel in some fun. Bring your bait, rod, and good vibes!\r\n\r\n', NULL, 'Announcement', '2025-06-16 00:00:00', '2026-02-27 19:27:23');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('8', '10', 'Community Fishing Day', 'Grab your rods and join us for a relaxing Fishing Day by the lake! It’s the perfect time to unwind, bond with fellow anglers, and enjoy the great outdoors. Open to all ages—everyone’s welcome!', 'Screenshot 2025-09-07 225612.png', 'Announcement', '2025-06-16 00:00:00', '2026-02-27 19:29:45');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('9', '38', 'Aspernatur dolor ea', 'Ipsa dolorum sunt', '1772105097_testFile.png', 'Announcement', '2026-02-26 00:00:00', '2026-02-27 19:29:50');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('10', '37', 'Aspernatur dolor ea', 'Ipsa dolorum sunt', '1772104983_testFile.png', 'Announcement', '2026-02-26 00:00:00', '2026-02-27 19:29:54');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('0', '39', 'General Assembly Meeting', 'All members are invited to attend our General Assembly on March 5, 2026 (Thursday), 2:00 PM at the Barangay Hall. Important updates and upcoming activities will be discussed. Attendance is highly encouraged.', NULL, 'Announcement', '2026-02-26 00:00:00', '2026-04-20 16:07:23');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('0', '42', 'Deadline for Membership Dues', 'Please settle your monthly dues on or before March 10, 2026 to avoid penalties and to keep your membership active. You may pay at the office during business hours.', NULL, 'Announcement', '2026-02-26 00:00:00', '2026-04-20 16:10:03');


-- Table: association_glance
DROP TABLE IF EXISTS `association_glance`;

CREATE TABLE `association_glance` (
  `id` int(11) NOT NULL,
  `overview` longtext NOT NULL,
  `founded_year` int(11) NOT NULL,
  `members_count` int(11) NOT NULL,
  `projects_count` int(11) NOT NULL,
  `events_count` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `association_glance`
INSERT INTO `association_glance` (`id`, `overview`, `founded_year`, `members_count`, `projects_count`, `events_count`, `updated_at`) VALUES ('1', 'Since its founding in 2009, the Bankero and Fishermen Association has been a united community of bangkeros and coastal stakeholders committed to safe, sustainable, and service‑oriented operations.\r\n\r\nThe association works closely with local government units, partner agencies, and community organizations to promote responsible tourism, protect marine resources, and uplift the lives of its members and their families.\r\n\r\nThrough regular trainings, livelihood programs, and outreach activities, the Bankero and Fishermen Association continues to strengthen camaraderie, professionalism, and shared responsibility among its members.', '2009', '450', '50', '62', '2026-02-17 10:44:03');
INSERT INTO `association_glance` (`id`, `overview`, `founded_year`, `members_count`, `projects_count`, `events_count`, `updated_at`) VALUES ('1', 'Since its founding in 2009, the Bankero and Fishermen Association has been a united community of bangkeros and coastal stakeholders committed to safe, sustainable, and service‑oriented operations.\r\n\r\nThe association works closely with local government units, partner agencies, and community organizations to promote responsible tourism, protect marine resources, and uplift the lives of its members and their families.\r\n\r\nThrough regular trainings, livelihood programs, and outreach activities, the Bankero and Fishermen Association continues to strengthen camaraderie, professionalism, and shared responsibility among its members.', '2009', '450', '50', '62', '2026-02-17 02:44:03');


-- Table: attendance
DROP TABLE IF EXISTS `attendance`;

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','absent','excused') DEFAULT 'present',
  `time_in` time DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_attendance` (`event_id`,`member_id`,`attendance_date`),
  KEY `idx_member` (`member_id`),
  KEY `idx_event` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `attendance`

-- Table: awards
DROP TABLE IF EXISTS `awards`;

CREATE TABLE `awards` (
  `award_id` int(11) NOT NULL,
  `award_title` varchar(255) NOT NULL,
  `awarding_body` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `year_received` int(11) DEFAULT NULL,
  `date_received` date DEFAULT NULL,
  `award_image` varchar(255) DEFAULT NULL,
  `certificate_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data for table `awards`
INSERT INTO `awards` (`award_id`, `award_title`, `awarding_body`, `category`, `description`, `year_received`, `date_received`, `award_image`, `certificate_file`, `created_at`) VALUES ('1', 'Outstanding Coastal Resource Management Award', 'BFAR Region III', 'Regional', 'Recognized for exemplary efforts in sustainable fishing practices, marine conservation initiatives, and community-led coastal protection programs in Olongapo City.', '2025', '2026-02-03', '1770855144_3748da6d-7b9a-4046-b8ce-8b4950b0863e.jpg', 'cert_1770855144_Screenshot2026-02-12081155.png', '2026-02-12 16:14:10');
INSERT INTO `awards` (`award_id`, `award_title`, `awarding_body`, `category`, `description`, `year_received`, `date_received`, `award_image`, `certificate_file`, `created_at`) VALUES ('1', 'Outstanding Coastal Resource Management Award', 'BFAR Region III', 'Regional', 'Recognized for exemplary efforts in sustainable fishing practices, marine conservation initiatives, and community-led coastal protection programs in Olongapo City.', '2025', '2026-02-03', '1770855144_3748da6d-7b9a-4046-b8ce-8b4950b0863e.jpg', 'cert_1770855144_Screenshot2026-02-12081155.png', '2026-02-12 08:14:10');


-- Table: awards_archive
DROP TABLE IF EXISTS `awards_archive`;

CREATE TABLE `awards_archive` (
  `archive_id` int(11) NOT NULL,
  `award_id` int(11) NOT NULL,
  `award_title` varchar(255) NOT NULL,
  `awarding_body` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `year_received` int(11) NOT NULL,
  `date_received` date NOT NULL,
  `award_image` varchar(255) DEFAULT NULL,
  `certificate_file` varchar(255) DEFAULT NULL,
  `original_created_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data for table `awards_archive`
INSERT INTO `awards_archive` (`archive_id`, `award_id`, `award_title`, `awarding_body`, `category`, `description`, `year_received`, `date_received`, `award_image`, `certificate_file`, `original_created_at`, `archived_at`) VALUES ('1', '2', 'Excellence in Community Fisheries Development', 'Department of Agriculture', 'National', 'Awarded for outstanding contribution in improving livelihood opportunities, strengthening fisherfolk organizations, and implementing effective fisheries development programs', '2026', '2026-02-02', '1770855239_d2e2cba5-9d81-4867-bdf6-796219834802.jpg', '', '2026-02-12 16:15:46', '2026-02-14 11:22:25');
INSERT INTO `awards_archive` (`archive_id`, `award_id`, `award_title`, `awarding_body`, `category`, `description`, `year_received`, `date_received`, `award_image`, `certificate_file`, `original_created_at`, `archived_at`) VALUES ('2', '4', 'Et similique volupta', 'Quia rerum nihil sin', 'Regional', 'Nihil eligendi reici', '1983', '2008-08-02', '1771011846_testFile.png', 'cert_1771011846_testFile.pdf', '2026-02-14 11:45:54', '2026-02-14 11:46:00');
INSERT INTO `awards_archive` (`archive_id`, `award_id`, `award_title`, `awarding_body`, `category`, `description`, `year_received`, `date_received`, `award_image`, `certificate_file`, `original_created_at`, `archived_at`) VALUES ('1', '2', 'Excellence in Community Fisheries Development', 'Department of Agriculture', 'National', 'Awarded for outstanding contribution in improving livelihood opportunities, strengthening fisherfolk organizations, and implementing effective fisheries development programs', '2026', '2026-02-02', '1770855239_d2e2cba5-9d81-4867-bdf6-796219834802.jpg', '', '2026-02-12 08:15:46', '2026-02-14 03:22:25');
INSERT INTO `awards_archive` (`archive_id`, `award_id`, `award_title`, `awarding_body`, `category`, `description`, `year_received`, `date_received`, `award_image`, `certificate_file`, `original_created_at`, `archived_at`) VALUES ('2', '4', 'Et similique volupta', 'Quia rerum nihil sin', 'Regional', 'Nihil eligendi reici', '1983', '2008-08-02', '1771011846_testFile.png', 'cert_1771011846_testFile.pdf', '2026-02-14 03:45:54', '2026-02-14 03:46:00');


-- Table: backups
DROP TABLE IF EXISTS `backups`;

CREATE TABLE `backups` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filesize` bigint(20) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Data for table `backups`
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('1', 'backup_2026-02-09_14-36-04.sql', '64233', '321220', '2026-02-09 13:37:49');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('2', 'backup_2026-02-09_14-36-10.sql', '64687', '321220', '2026-02-09 13:37:55');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('3', 'backup_2026-02-11_15-53-25.sql', '67247', '321220', '2026-02-11 14:55:11');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('4', 'backup_2026-02-11_15-55-15.sql', '68121', '321220', '2026-02-11 14:57:02');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('5', 'backup_2026-02-11_16-09-29.sql', '68561', '321220', '2026-02-11 23:11:15');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('6', 'backup_2026-02-11_16-10-06.sql', '68956', '321220', '2026-02-11 23:11:53');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('7', 'backup_2026-02-11_16-16-31.sql', '69351', '321220', '2026-02-11 23:18:18');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('8', 'backup_2026-02-11_16-23-19.sql', '69746', '321220', '2026-02-11 23:25:06');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('9', 'backup_2026-02-11_16-46-34.sql', '70374', '321220', '2026-02-12 07:48:21');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('10', 'backup_2026-02-11_16-54-56.sql', '70770', '321220', '2026-02-12 07:56:42');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('11', 'backup_2026-02-11_16-58-41.sql', '71398', '321220', '2026-02-12 08:00:28');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('12', 'backup_2026-02-11_17-08-19.sql', '71794', '321220', '2026-02-12 08:10:05');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('13', 'backup_2026-02-11_19-35-07.sql', '74479', '321220', '2026-02-12 10:36:54');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('14', 'backup_2026-02-12_02-02-25.sql', '76859', '321220', '2026-02-12 17:04:17');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('15', 'backup_2026-02-17_15-05-51.sql', '103053', '321220', '2026-02-17 23:07:44');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('16', 'backup_2026-02-17_15-15-23.sql', '103682', '321220', '2026-02-17 23:17:17');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('17', 'backup_2026-02-18_00-07-30.sql', '105276', '321220', '2026-02-19 00:09:24');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('18', 'backup_2026-02-18_00-44-19.sql', '105673', '321220', '2026-02-19 00:46:13');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('19', 'backup_2026-02-26_15-57-29.sql', '124808', '321220', '2026-02-27 23:59:29');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('20', 'backup_2026-03-04_20-25-22.sql', '143861', '321220', '2026-03-06 19:25:22');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('21', 'backup_2026-03-05_17-13-40.sql', '146798', '321220', '2026-03-07 16:13:40');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('1', 'backup_2026-02-09_14-36-04.sql', '64233', '321220', '2026-02-09 05:37:49');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('2', 'backup_2026-02-09_14-36-10.sql', '64687', '321220', '2026-02-09 05:37:55');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('3', 'backup_2026-02-11_15-53-25.sql', '67247', '321220', '2026-02-11 06:55:11');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('4', 'backup_2026-02-11_15-55-15.sql', '68121', '321220', '2026-02-11 06:57:02');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('5', 'backup_2026-02-11_16-09-29.sql', '68561', '321220', '2026-02-11 15:11:15');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('6', 'backup_2026-02-11_16-10-06.sql', '68956', '321220', '2026-02-11 15:11:53');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('7', 'backup_2026-02-11_16-16-31.sql', '69351', '321220', '2026-02-11 15:18:18');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('8', 'backup_2026-02-11_16-23-19.sql', '69746', '321220', '2026-02-11 15:25:06');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('9', 'backup_2026-02-11_16-46-34.sql', '70374', '321220', '2026-02-11 23:48:21');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('10', 'backup_2026-02-11_16-54-56.sql', '70770', '321220', '2026-02-11 23:56:42');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('11', 'backup_2026-02-11_16-58-41.sql', '71398', '321220', '2026-02-12 00:00:28');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('12', 'backup_2026-02-11_17-08-19.sql', '71794', '321220', '2026-02-12 00:10:05');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('13', 'backup_2026-02-11_19-35-07.sql', '74479', '321220', '2026-02-12 02:36:54');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('14', 'backup_2026-02-12_02-02-25.sql', '76859', '321220', '2026-02-12 09:04:17');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('15', 'backup_2026-02-17_15-05-51.sql', '103053', '321220', '2026-02-17 15:07:44');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('16', 'backup_2026-02-17_15-15-23.sql', '103682', '321220', '2026-02-17 15:17:17');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('17', 'backup_2026-02-18_00-07-30.sql', '105276', '321220', '2026-02-18 16:09:24');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('18', 'backup_2026-02-18_00-44-19.sql', '105673', '321220', '2026-02-18 16:46:13');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('19', 'backup_2026-02-26_15-57-29.sql', '124808', '321220', '2026-02-27 15:59:29');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('20', 'backup_2026-03-04_20-25-22.sql', '143861', '321220', '2026-03-06 11:25:22');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('21', 'backup_2026-03-05_17-13-40.sql', '146798', '321220', '2026-03-07 08:13:40');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('0', 'backup_2026-03-31_18-59-35.sql', '225174', '321229', '2026-04-02 00:59:35');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('0', 'backup_2026-04-03_19-05-09.sql', '235785', '321229', '2026-04-05 01:05:09');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('0', 'backup_2026-04-05_18-27-59.sql', '237190', '321229', '2026-04-07 00:27:59');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('0', 'backup_2026-04-05_19-24-09.sql', '237814', '321229', '2026-04-07 01:24:09');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('0', 'backup_2026-04-10_03-36-09.sql', '248703', '321229', '2026-04-11 09:36:09');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('0', 'backup_2026-04-14_05-07-58.sql', '247003', '321219', '2026-04-15 11:07:59');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('0', 'backup_2026-04-19_12-23-03.sql', '262581', '321229', '2026-04-20 10:23:03');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('0', 'backup_2026-04-19_12-23-37.sql', '263205', '321229', '2026-04-20 10:23:37');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('0', 'backup_2026-04-19_12-24-30.sql', '263829', '321229', '2026-04-20 10:24:30');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('0', 'backup_2026-04-19_12-28-18.sql', '264223', '321229', '2026-04-20 10:28:18');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('0', 'backup_2026-04-19_12-28-21.sql', '264617', '321229', '2026-04-20 10:28:21');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('0', 'backup_2026-04-19_12-28-35.sql', '265241', '321229', '2026-04-20 10:28:36');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('0', 'backup_2026-04-19_12-36-21.sql', '266351', '321229', '2026-04-20 02:36:21');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('0', 'backup_2026-04-19_12-36-28.sql', '266975', '321229', '2026-04-20 02:36:29');


-- Table: community_achievement_images
DROP TABLE IF EXISTS `community_achievement_images`;

CREATE TABLE `community_achievement_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `achievement_id` int(11) NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_achievement_id` (`achievement_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `community_achievement_images`
INSERT INTO `community_achievement_images` (`id`, `achievement_id`, `image_path`, `sort_order`, `created_at`) VALUES ('2', '1', 'uploads/achievements/1775757876_204d483a.jpg', '1', '2026-04-11 02:04:36');
INSERT INTO `community_achievement_images` (`id`, `achievement_id`, `image_path`, `sort_order`, `created_at`) VALUES ('3', '1', 'uploads/achievements/1775757876_369a1e45.jpg', '2', '2026-04-11 02:04:36');
INSERT INTO `community_achievement_images` (`id`, `achievement_id`, `image_path`, `sort_order`, `created_at`) VALUES ('4', '1', 'uploads/achievements/1775757876_74007cc5.jpg', '3', '2026-04-11 02:04:36');
INSERT INTO `community_achievement_images` (`id`, `achievement_id`, `image_path`, `sort_order`, `created_at`) VALUES ('5', '1', 'uploads/achievements/1775757876_ebbbd61e.jpg', '4', '2026-04-11 02:04:36');
INSERT INTO `community_achievement_images` (`id`, `achievement_id`, `image_path`, `sort_order`, `created_at`) VALUES ('6', '1', 'uploads/achievements/1775757876_ab21cedb.jpg', '5', '2026-04-11 02:04:36');
INSERT INTO `community_achievement_images` (`id`, `achievement_id`, `image_path`, `sort_order`, `created_at`) VALUES ('7', '1', 'uploads/achievements/1775757876_6f57b3cb.jpg', '6', '2026-04-11 02:04:36');
INSERT INTO `community_achievement_images` (`id`, `achievement_id`, `image_path`, `sort_order`, `created_at`) VALUES ('8', '1', 'uploads/achievements/1775757876_01b4ef65.jpg', '7', '2026-04-11 02:04:36');
INSERT INTO `community_achievement_images` (`id`, `achievement_id`, `image_path`, `sort_order`, `created_at`) VALUES ('9', '1', 'uploads/achievements/1775757876_1692e68a.jpg', '8', '2026-04-11 02:04:36');
INSERT INTO `community_achievement_images` (`id`, `achievement_id`, `image_path`, `sort_order`, `created_at`) VALUES ('10', '1', 'uploads/achievements/1775757876_e266e0cd.jpg', '9', '2026-04-11 02:04:36');
INSERT INTO `community_achievement_images` (`id`, `achievement_id`, `image_path`, `sort_order`, `created_at`) VALUES ('11', '1', 'uploads/achievements/1775757876_066d06e0.jpg', '10', '2026-04-11 02:04:36');
INSERT INTO `community_achievement_images` (`id`, `achievement_id`, `image_path`, `sort_order`, `created_at`) VALUES ('12', '1', 'uploads/achievements/1775757876_52060182.jpg', '11', '2026-04-11 02:04:36');
INSERT INTO `community_achievement_images` (`id`, `achievement_id`, `image_path`, `sort_order`, `created_at`) VALUES ('13', '1', 'uploads/achievements/1775757876_b2eb129a.jpg', '12', '2026-04-11 02:04:36');
INSERT INTO `community_achievement_images` (`id`, `achievement_id`, `image_path`, `sort_order`, `created_at`) VALUES ('14', '1', 'uploads/achievements/1775757876_335ecf7a.jpg', '13', '2026-04-11 02:04:36');
INSERT INTO `community_achievement_images` (`id`, `achievement_id`, `image_path`, `sort_order`, `created_at`) VALUES ('15', '1', 'uploads/achievements/1775757876_a883dadd.jpg', '14', '2026-04-11 02:04:36');
INSERT INTO `community_achievement_images` (`id`, `achievement_id`, `image_path`, `sort_order`, `created_at`) VALUES ('16', '1', 'uploads/achievements/1775757876_6850b3ee.jpg', '15', '2026-04-11 02:04:36');
INSERT INTO `community_achievement_images` (`id`, `achievement_id`, `image_path`, `sort_order`, `created_at`) VALUES ('17', '1', 'uploads/achievements/1775757876_110d2c1c.jpg', '16', '2026-04-11 02:04:36');


-- Table: community_achievements
DROP TABLE IF EXISTS `community_achievements`;

CREATE TABLE `community_achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `caption` text DEFAULT NULL,
  `tag` varchar(100) DEFAULT NULL,
  `image_path` varchar(500) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `community_achievements`
INSERT INTO `community_achievements` (`id`, `title`, `caption`, `tag`, `image_path`, `sort_order`, `is_active`, `created_at`) VALUES ('1', 'Bangus in Corn Oil', 'A product of our community\'s hard work and dedication, this Bangus in Corn Oil is crafted by local fisherfolk through our livelihood program. Fresh milkfish is carefully cleaned, deboned, and slow-cooked in pure corn oil — preserving its natural flavor and tenderness. More than just a meal, every can represents the skill, effort, and resilience of our bankero community.', 'Product Made', 'uploads/achievements/1775757876_204d483a.jpg', '0', '1', '2026-04-05 00:30:19');


-- Table: community_achievements_archive
DROP TABLE IF EXISTS `community_achievements_archive`;

CREATE TABLE `community_achievements_archive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `caption` text DEFAULT NULL,
  `tag` varchar(100) DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `archived_by` int(11) DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `community_achievements_archive`
INSERT INTO `community_achievements_archive` (`id`, `original_id`, `title`, `caption`, `tag`, `image_path`, `sort_order`, `archived_by`, `archived_at`) VALUES ('1', '2', 'dsdsd', 'sdd', 'sdsd', 'uploads/achievements/1775234584_504f9dcb.jpg', '0', '321229', '2026-04-05 00:49:44');


-- Table: contact_messages
DROP TABLE IF EXISTS `contact_messages`;

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `contact_messages`
INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `status`, `created_at`) VALUES ('7', 'Norman Noble', 'xogivogoz@example.com', 'Voluptatem consectet', 'read', '2026-04-02 00:18:15');
INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `status`, `created_at`) VALUES ('8', 'Ashely Barker', 'miruqi@example.com', 'Nihil velit quos aut', 'replied', '2026-04-02 00:18:19');
INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `status`, `created_at`) VALUES ('17', 'Travis Mccall', 'cejaz@example.com', 'Voluptatem inventor', 'read', '2026-04-02 00:54:29');
INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `status`, `created_at`) VALUES ('18', 'Rigel Alexander', 'topepuh@example.com', 'Amet aliquam velit', 'replied', '2026-04-02 00:54:34');
INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `status`, `created_at`) VALUES ('19', 'quics', 'jovelynbuena12@gmail.com', 'hi', 'replied', '2026-04-17 22:30:34');
INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `status`, `created_at`) VALUES ('20', 'Ferdinand Rios', 'pajug@example.com', 'Officia et voluptate', 'unread', '2026-04-19 19:40:31');


-- Table: contact_messages_archive
DROP TABLE IF EXISTS `contact_messages_archive`;

CREATE TABLE `contact_messages_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data for table `contact_messages_archive`
INSERT INTO `contact_messages_archive` (`archive_id`, `original_id`, `name`, `email`, `message`, `status`, `created_at`, `archived_at`) VALUES ('3', '6', 'Selma Henry', 'peruvan@example.com', 'Ducimus saepe non m', 'read', '2026-04-01 00:18:11', '2026-04-04 02:53:36');
INSERT INTO `contact_messages_archive` (`archive_id`, `original_id`, `name`, `email`, `message`, `status`, `created_at`, `archived_at`) VALUES ('4', '2', 'Jovelyn Buena', 'jovelybuena12@gmail.com', 'hi po hehe ganda nyo po', 'read', '2025-10-18 09:16:22', '2026-04-04 21:45:49');
INSERT INTO `contact_messages_archive` (`archive_id`, `original_id`, `name`, `email`, `message`, `status`, `created_at`, `archived_at`) VALUES ('5', '16', 'Orli Morris', 'milacuvovo@example.com', 'Dolores cillum eius sdsad\r\nasdas\r\ndasd\r\nasdasd\r\nasdas\r\ndasd\r\nad\r\nasdas\r\ndas\r\ndas\r\ndas\r\ndas\r\nd', 'unread', '2026-04-01 00:54:23', '2026-04-15 18:19:29');
INSERT INTO `contact_messages_archive` (`archive_id`, `original_id`, `name`, `email`, `message`, `status`, `created_at`, `archived_at`) VALUES ('6', '5', 'Alma Hampton', 'qycycekuz@example.com', 'Sed culpa voluptatu', 'read', '2026-04-01 00:18:00', '2026-04-19 19:41:39');


-- Table: core_values
DROP TABLE IF EXISTS `core_values`;

CREATE TABLE `core_values` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `core_values`
INSERT INTO `core_values` (`id`, `title`, `description`, `sort_order`, `created_at`) VALUES ('2', 'Unity', 'We stand together as one association, helping and supporting one another in every challenge and opportunity.', '1', '2026-02-17 10:31:53');
INSERT INTO `core_values` (`id`, `title`, `description`, `sort_order`, `created_at`) VALUES ('4', 'Sustainability', 'We promote responsible fishing and boating practices to protect our seas and ensure a livelihood for future generations.', '4', '2026-02-17 10:32:25');
INSERT INTO `core_values` (`id`, `title`, `description`, `sort_order`, `created_at`) VALUES ('6', 'Accountability', 'We take responsibility for our actions, keep our word, and use association resources with care and fairness.', '5', '2026-02-17 10:33:22');
INSERT INTO `core_values` (`id`, `title`, `description`, `sort_order`, `created_at`) VALUES ('3', 'Integrity', 'We act with honesty and transparency in all our decisions and transactions for the welfare of our members.', '2', '2026-02-17 10:32:14');
INSERT INTO `core_values` (`id`, `title`, `description`, `sort_order`, `created_at`) VALUES ('7', 'Compassion', 'We value each member’s situation and work to uplift the lives of fishermen, boatmen, and their families.', '6', '2026-02-17 10:33:52');
INSERT INTO `core_values` (`id`, `title`, `description`, `sort_order`, `created_at`) VALUES ('5', 'Service', 'We are committed to serving our members and the community through programs, trainings, and timely assistance.', '3', '2026-02-17 10:32:44');


-- Table: core_values_archive
DROP TABLE IF EXISTS `core_values_archive`;

CREATE TABLE `core_values_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `original_created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `core_values_archive`

-- Table: downloadable_resources
DROP TABLE IF EXISTS `downloadable_resources`;

CREATE TABLE `downloadable_resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_key` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `icon_class` varchar(100) DEFAULT NULL,
  `color_hex` varchar(20) DEFAULT '#0d6efd',
  `file_path` varchar(500) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `downloadable_resources`
INSERT INTO `downloadable_resources` (`id`, `file_key`, `title`, `icon_class`, `color_hex`, `file_path`, `sort_order`, `is_active`, `created_at`) VALUES ('2', 'officers_list', 'officers List', '', '#0d6efd', 'uploads/resources/1775150994_officers_List.pdf', '1', '1', '2026-04-04 01:29:54');
INSERT INTO `downloadable_resources` (`id`, `file_key`, `title`, `icon_class`, `color_hex`, `file_path`, `sort_order`, `is_active`, `created_at`) VALUES ('3', 'uploaded_pdf', 'Awards and Recognition', '', '#0d6efd', 'uploads/resources/1775151377_Awards_and_Recognition.pdf', '2', '1', '2026-04-04 01:36:17');
INSERT INTO `downloadable_resources` (`id`, `file_key`, `title`, `icon_class`, `color_hex`, `file_path`, `sort_order`, `is_active`, `created_at`) VALUES ('5', 'attendance_sheet', 'Attendance Sheet', '', '#0d6efd', NULL, '2', '1', '2026-04-04 01:45:18');
INSERT INTO `downloadable_resources` (`id`, `file_key`, `title`, `icon_class`, `color_hex`, `file_path`, `sort_order`, `is_active`, `created_at`) VALUES ('7', 'officers_list', 'officers List', '', '#0d6efd', NULL, '4', '1', '2026-04-04 01:46:31');
INSERT INTO `downloadable_resources` (`id`, `file_key`, `title`, `icon_class`, `color_hex`, `file_path`, `sort_order`, `is_active`, `created_at`) VALUES ('8', 'event_guidelines', 'event Guidelines', '', '#0d6efd', NULL, '5', '1', '2026-04-04 02:03:17');
INSERT INTO `downloadable_resources` (`id`, `file_key`, `title`, `icon_class`, `color_hex`, `file_path`, `sort_order`, `is_active`, `created_at`) VALUES ('9', 'membership_form', 'Membership Form', '', '#0d6efd', NULL, '6', '1', '2026-04-04 02:13:04');


-- Table: downloadable_resources_archive
DROP TABLE IF EXISTS `downloadable_resources_archive`;

CREATE TABLE `downloadable_resources_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `file_key` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `icon_class` varchar(100) DEFAULT NULL,
  `color_hex` varchar(20) DEFAULT '#0d6efd',
  `file_path` varchar(500) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `original_created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `downloadable_resources_archive`
INSERT INTO `downloadable_resources_archive` (`archive_id`, `original_id`, `file_key`, `title`, `icon_class`, `color_hex`, `file_path`, `sort_order`, `is_active`, `original_created_at`, `archived_at`) VALUES ('1', '1', 'membership_form', 'Membership Form', '', '#0d6efd', NULL, '1', '1', '2026-02-16 11:43:03', '2026-04-04 01:29:42');
INSERT INTO `downloadable_resources_archive` (`archive_id`, `original_id`, `file_key`, `title`, `icon_class`, `color_hex`, `file_path`, `sort_order`, `is_active`, `original_created_at`, `archived_at`) VALUES ('2', '4', 'attendance_sheet', 'Attendance Sheet', '', '#0d6efd', NULL, '3', '1', '2026-04-03 01:37:30', '2026-04-04 01:45:03');
INSERT INTO `downloadable_resources_archive` (`archive_id`, `original_id`, `file_key`, `title`, `icon_class`, `color_hex`, `file_path`, `sort_order`, `is_active`, `original_created_at`, `archived_at`) VALUES ('3', '6', 'membership_form', 'Membership Form', '', '#0d6efd', NULL, '3', '1', '2026-04-03 01:45:58', '2026-04-04 02:12:58');


-- Table: events
DROP TABLE IF EXISTS `events`;

CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT 'General',
  `description` text DEFAULT NULL,
  `event_poster` varchar(255) DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_date` (`date`),
  KEY `idx_is_archived` (`is_archived`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `events`
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('1', 'DOLE LIVELIHOOD PROGRAM', '2026-03-17', '01:42:00', 'Driftwood, Olongapo City', 'General Meeting', 'sdadew', '1772732304_c1e3386a_Gemini_Generated_Image_oixh2poixh2poixh.png', '1', NULL, '2026-03-07 01:38:24', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('3', 'Gone Fishing 2025!', '2025-05-09', '16:54:00', 'Baloy olongapo city', 'General', ' Gone Fishing is a fun and relaxing community event that brings together fishing enthusiasts of all ages. Whether youre a seasoned angler or trying it out for the first time, this event offers a great opportunity to enjoy the outdoors, share techniques, and build camaraderie among fellow fishermen. ', '../uploads/Screenshot_2025-04-12_133511.png', '0', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('4', 'Big BAS Event (Bangkero and Fishermen Association Special Gathering)', '2025-05-03', '16:54:00', 'Subic Zambales', 'General', 'The Big BAS Event is the annual grand gathering of the Bangkero and Fishermen Association—a celebration of unity, hard work, and community spirit.It’s a day of fun, recognition, and connection for all members and their families. Come celebrate the heart of our coastal community at the biggest event of the year!', '../uploads/Screenshot_2025-04-12_145620.png', '0', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('6', 'Red Sea International Sport Fishing Tournament', '2025-06-27', '12:47:00', 'San maracelino', 'General', 'Red Sea Int`l Sport Fishing Tournament, will be the first global tournament to host top anglers from all around the world along with local teams competing in both Trolling ', 'Screenshot_2025-06-04_105423.png', '0', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('12', 'Red Sea International Sport Fishing Tournament', '2025-08-27', '10:19:00', 'Drift Wood Baretto Olongapo City', 'General', 'Everyone is expected to come', 'Screenshot_2025-08-25_221659.png', '0', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('13', '1. Family-Friendly Fishing Tournament (Pine Island)', '2025-09-09', '22:47:00', 'Pine Island, Zambales', 'General', 'A welcoming event geared toward families, featuring casual competition, a captains meeting, food, and drinks. It&#039;s designed to be inclusive and social, perfect for anglers of all ages.', 'Screenshot_2025-09-07_224712.png', '0', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('14', '1st Subic Bay Shore Fishing Tournament', '2026-07-24', '14:55:00', 'San Bernardino Fishing Site, Subic Bay Freeport Zone, Zambales', 'General', 'The inaugural shore-fishing competition in Subic Bay, spotlighting responsible angling and marine conservation. Organized by Fish’n Town with the support of the Subic Bay Metropolitan Authority and local sponsors, it blends sport with sustainable tourism and community engagement.', 'Screenshot_2025-09-07_225612.png', '0', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('16', 'Red Sea International Sport Fishing Tournament', '2025-09-17', '01:19:00', 'Castillejos Zambales', 'General', 'ophelia', '1757571370_29d28442-8efd-4d61-8164-45cfd342a2a7.jpg', '1', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('17', 'Red Sea International Sport Fishing Tournament', '2027-04-16', '01:26:00', 'Baloy olongapo city', 'General', 'ako po geloy m caloy', '1757586688_0e320bcc-941d-4276-a8b0-c89a1408b719.jpg', '0', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('26', 'Colin Lee', '2024-07-25', '10:17:00', 'Voluptate tenetur qu', 'Livelihood', 'Culpa molestiae ipsa', '1760551305_Screenshot_2025-09-24_162407.png', '0', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('27', 'Test Event', '2026-01-18', '10:00:00', 'Beach', 'General', 'Description here', '', '1', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('28', '1st Subic Bay Shore Fishing Tournament', '2026-01-31', '01:44:00', 'Bahay', 'Cleanup', 'birthday ni admin', '1768671680_Screenshot_2025-10-18_225224.png', '1', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('29', 'josedwsd', '2026-01-05', '16:10:00', 'wddfw', 'Festival', 'fefe', '1769069244_Screenshot_2025-06-10_081141.png', '1', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('30', 'Elvis Mclaughlin', '2016-02-08', '15:58:00', 'Philadelphia', 'Training', 'Tempora quis sunt n', '1769401632_Screenshot_2026-01-26_122601.png', '0', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('31', '1st Subic Bay Shore Fishing DFD', '2026-01-29', '19:50:00', 'fsd', 'Festival', 'fgd', '', '1', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('32', '1SDFSDAnament', '2026-01-27', '20:13:00', 'ererre', 'Festival', 'refer', '1769401740_Screenshot_2026-01-26_122504.png', '0', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('33', 'Ocean Santiago', '1999-05-21', '14:37:00', 'Dallas', 'Training', 'Qui voluptas molliti', '1769398941_knscsd2526-a11baf2f-4450-4b71-8f7c-a3d1776be7cd.jpg', '0', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('34', '1SDFSDAnament', '2026-01-06', '08:00:00', 'San Bernardino Fishing Site, Subic Bay Freeport Zone, Zambales', 'Festival', 'fdyfg', '', '1', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('35', '1st Subic Bay Shore Fishing Tournament', '2026-01-02', '08:00:00', 'dsdsd', 'Festival', 'dsdd', '', '1', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('36', 'Casey Gilmore', '1985-09-20', '16:39:00', 'Tucson', 'General', 'Excepteur cupiditate', '1769401663_testFile.png', '0', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('37', 'Maia Galloway', '1979-09-30', '21:29:00', 'Oklahoma City', 'Training', 'Sint non expedita co', '1769407825_testFile.png', '0', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('38', 'Annual Fishing Association Gathering and Community Outreach', '2026-02-22', '21:36:00', 'New York', 'Officers Meeting', 'The [Name of Fishing Association] is proud to announce its much-anticipated Annual Fishing Association Gathering, an event that brings together local anglers, community members, and environmental enthusiasts for a day of learning, networking, and celebration of our rich fishing culture. This year’s event promises to be bigger and better, emphasizing not only the sport and livelihood of fishing but also the sustainable practices that ensure our waters remain bountiful for generations to come.\r\n\r\nAttendees will have the unique opportunity to participate in a variety of activities designed to cater to both seasoned fishermen and beginners alike. The day will begin with an opening ceremony highlighting the achievements of association members over the past year, including awards for outstanding contributions to the community and excellence in sustainable fishing practices. Following the ceremony, interactive workshops will be held, covering topics such as modern fishing techniques, proper handling of aquatic species, safety measures, and environmental conservation. Experienced anglers will share their knowledge on equipment maintenance, bait selection, and effective fishing strategies, ensuring that participants gain practical skills they can apply in the field.', '1769582474_Screenshot_2026-01-26_122601.png', '0', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('39', 'DOLE LIVELIHOOD PROGRAM', '2026-03-05', '10:15:00', 'Driftwood, Olongapo City', 'Activity', 'DOLE Integrated Livelihood Program (DILP) is a Department of Labor and Employment initiative that provides livelihood assistance—such as starter kits, tools, training, and small business support—to help workers, unemployed individuals, and community groups build sustainable income and improve their quality of life.', '1772111302_049e986a_Screenshot_2026-02-26_210758.png', '0', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('40', 'Shelley Lambert', '2005-07-04', '01:17:00', 'Provident non aliqu', 'Training', 'Eius rerum eum dolor', '', '0', NULL, '2026-03-07 01:42:29', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('41', 'test', '2026-03-26', '08:00:00', 'asdasdas', 'Festival', 'sdfasd', '', '0', NULL, '2026-03-26 14:32:33', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('42', 'Ivan Langley', '1971-02-18', '09:03:00', 'Charlotte', 'General', 'Pariatur Magni est', '1774979756_2feb181c_testFile.png', '0', NULL, '2026-04-02 01:55:56', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('43', 'Bangkero Livelihood Training Program', '2026-04-10', '01:22:00', 'Covered Court', 'Training', 'Isang araw na livelihood training para sa mga miyembro ng asosasyon. Maglalaman ito ng hands-on na pagsasanay sa boat maintenance, basic welding, at safety sa ilog. Libre ang lahat ng materyales. Limitado lamang ang bilang ng kalahok.', '1775237983_86e8b887_Gemini_Generated_Image_uw0q6auw0q6auw0q.png', '0', NULL, '2026-04-02 01:56:45', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('44', 'Bangkero Association General Assembly 2026', '2026-05-03', '08:00:00', ':00 AM. Location: Barangay Hall, Olongapo City, Zambales.', 'General Meeting', 'Imbitado ang lahat ng miyembro ng Bangkero Association sa taunang General Assembly para sa taong 2026. Dito tatalakayin ang mga nakaraang aktibidad, financial updates, at mga plano para sa susunod na taon. Lahat ng miyembro ay hinihikayat na dumalo.', '1775237938_e8ffe933_Gemini_Generated_Image_5o1xwa5o1xwa5o1x.png', '0', NULL, '2026-04-02 01:57:20', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('45', 'Deacon Blackburn', '2026-04-04', '20:12:00', 'Oklahoma City', 'Training', 'Ut non similique ver', '1775238125_a061e3a2_Gemini_Generated_Image_darq5ddarq5ddarq.png', '0', NULL, '2026-04-04 22:08:14', NULL);
INSERT INTO `events` (`id`, `event_name`, `date`, `time`, `location`, `category`, `description`, `event_poster`, `is_archived`, `created_by`, `created_at`, `updated_at`) VALUES ('46', 'Coastal Clean-Up Drive', '2026-04-30', '08:00:00', 'Driftwood, Olongapo City', 'Cleanup', 'Join us for our Coastal Clean-Up Drive! We invite all members to participate in cleaning our coastal and river areas to help protect the environment and support our fishing community. Let’s work together for a cleaner and healthier surroundings.', '1776581957_18f52f43_Beach_Clean_up_Invitation_Video_-_Made_with_PosterMyWall.jpg', '0', NULL, '2026-04-20 14:59:17', NULL);


-- Table: events_archive
DROP TABLE IF EXISTS `events_archive`;

CREATE TABLE `events_archive` (
  `id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT 'General',
  `date` date NOT NULL,
  `time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_poster` varchar(255) DEFAULT 'default.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `events_archive`

-- Table: featured_programs
DROP TABLE IF EXISTS `featured_programs`;

CREATE TABLE `featured_programs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `icon_class` varchar(100) DEFAULT NULL,
  `button_label` varchar(100) DEFAULT 'View Events',
  `button_link` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `featured_programs`
INSERT INTO `featured_programs` (`id`, `title`, `description`, `icon_class`, `button_label`, `button_link`, `sort_order`, `created_at`) VALUES ('1', 'Coastal Clean-up Drives', 'Regular community-led initiatives to protect our marine environment, preserve coastal ecosystems, and maintain clean beaches for future generations.', 'bi-water', 'View Events', 'Button Link (URL): events.php?category=cleanup', '1', '2026-02-17 17:32:05');
INSERT INTO `featured_programs` (`id`, `title`, `description`, `icon_class`, `button_label`, `button_link`, `sort_order`, `created_at`) VALUES ('2', 'Fishermen Livelihood Support', 'Providing financial assistance, equipment support, and sustainable fishing resources to help local fishermen improve their income and quality of life.', 'bi-briefcase', 'View Events', 'events.php?category=livelihood', '2', '2026-02-17 17:32:49');
INSERT INTO `featured_programs` (`id`, `title`, `description`, `icon_class`, `button_label`, `button_link`, `sort_order`, `created_at`) VALUES ('3', 'Safety & Maritime Training', 'Comprehensive training programs covering sea safety, first aid, navigation, and emergency protocols to ensure the well-being of all fishermen.', 'bi-shield-check', 'View Events', 'events.php?category=training', '3', '2026-02-17 17:33:16');
INSERT INTO `featured_programs` (`id`, `title`, `description`, `icon_class`, `button_label`, `button_link`, `sort_order`, `created_at`) VALUES ('4', 'Environmental Protection', 'Advocacy and action programs focused on marine conservation, sustainable fishing practices, and educating the community about environmental responsibility.', 'bi-tree', 'View Events', 'events.php?category=environment', '4', '2026-02-17 17:34:14');


-- Table: featured_programs_archive
DROP TABLE IF EXISTS `featured_programs_archive`;

CREATE TABLE `featured_programs_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `icon_class` varchar(100) DEFAULT NULL,
  `button_label` varchar(100) DEFAULT 'View Events',
  `button_link` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `original_created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `featured_programs_archive`

-- Table: galleries
DROP TABLE IF EXISTS `galleries`;

CREATE TABLE `galleries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT 'Uncategorized',
  `images` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `galleries`
INSERT INTO `galleries` (`id`, `title`, `category`, `images`, `created_at`) VALUES ('9', 'Meeting with Congressman Jay Khonghun', 'Meetings', '1764505526_7948644f2b70.jpg', '2025-11-30 20:25:26');
INSERT INTO `galleries` (`id`, `title`, `category`, `images`, `created_at`) VALUES ('10', 'From Shore to Sea: Turtle Release', 'Activities', '1764505663_d204b6b97742.jpg,1764505663_8925b8b97bc7.jpg,1764505663_044e4a1da3d6.jpg,1764505663_4f5cf884a967.jpg,1764505663_1df2bdac84c2.jpg,1764505663_278e7b4c86c3.jpg', '2025-11-30 20:27:43');
INSERT INTO `galleries` (`id`, `title`, `category`, `images`, `created_at`) VALUES ('11', 'Dole Integrated Livelihood Program', 'Awards', '1769582283_a901f558671f.jfif,1769582283_93a835c05dbf.jfif,1769582283_19c8db94c93b.jfif,1769582283_0b2645f2c412.jfif,1769582283_3b568394debd.jfif,1769582283_e61b5a7bce34.jfif,1769582283_1087cd88e8a7.jfif,1769582283_62632cece70b.jfif', '2026-01-28 14:39:37');
INSERT INTO `galleries` (`id`, `title`, `category`, `images`, `created_at`) VALUES ('12', 'DOLE INTEGRATED LIVELIHOOD PROGRAM AWARDING', 'Awards', '1770997718_b3975cd5e08c.jfif,1770997718_6a8ca065561d.jfif,1770997718_2f83c2f7170c.jfif,1770997718_b2a8ebeee842.jfif,1770997718_9fb46ac2919f.jfif,1770997718_21974482059b.jfif,1770997718_9bf37cdc3508.jfif,1770997718_c1d676672e72.jfif,1770997718_c63173b6db5b.jfif', '2026-02-14 15:50:25');


-- Table: galleries_archive
DROP TABLE IF EXISTS `galleries_archive`;

CREATE TABLE `galleries_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `gallery_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT 'Uncategorized',
  `images` text NOT NULL,
  `original_created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `galleries_archive`
INSERT INTO `galleries_archive` (`archive_id`, `gallery_id`, `title`, `category`, `images`, `original_created_at`, `archived_at`) VALUES ('1', '14', 'Ut sed est corrupti', 'Meetings', '1771011807_f5a69b413e7d.jfif', '2026-02-13 11:45:14', '2026-02-14 19:45:24');
INSERT INTO `galleries_archive` (`archive_id`, `gallery_id`, `title`, `category`, `images`, `original_created_at`, `archived_at`) VALUES ('2', '13', 'Ut sed est corrupti', 'Meetings', '1771010750_af5f4ed1d0fd.jfif', '2026-02-12 19:27:37', '2026-03-07 00:49:46');


-- Table: home_carousel_slides
DROP TABLE IF EXISTS `home_carousel_slides`;

CREATE TABLE `home_carousel_slides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subtitle` text NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `primary_button_label` varchar(100) DEFAULT 'Learn More',
  `primary_button_link` varchar(255) DEFAULT 'about_us.php',
  `secondary_button_label` varchar(100) DEFAULT 'Join Us',
  `secondary_button_link` varchar(255) DEFAULT 'contact_us.php',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `home_carousel_slides`
INSERT INTO `home_carousel_slides` (`id`, `title`, `subtitle`, `image_path`, `primary_button_label`, `primary_button_link`, `secondary_button_label`, `secondary_button_link`, `sort_order`, `created_at`) VALUES ('1', 'Strengthening Our Fishing Communities', 'We empower small-scale fishers through livelihood support, training, and community-led programs across our coastal barangays.', 'uploads/carousel/1771270044_slides2.jpg', 'Learn More', 'about_us.php', 'Join Us', 'contact_us.php', '1', '2026-02-17 19:25:35');
INSERT INTO `home_carousel_slides` (`id`, `title`, `subtitle`, `image_path`, `primary_button_label`, `primary_button_link`, `secondary_button_label`, `secondary_button_link`, `sort_order`, `created_at`) VALUES ('2', 'Sustainable and Responsible Fishing', 'Together with our partners, we promote responsible fishing practices to protect our seas and secure future livelihoods.', 'uploads/carousel/1771269853_bg1.jpg', 'Learn More', 'about_us.php', 'Join Us', 'contact_us.php', '2', '2026-02-17 19:26:03');
INSERT INTO `home_carousel_slides` (`id`, `title`, `subtitle`, `image_path`, `primary_button_label`, `primary_button_link`, `secondary_button_label`, `secondary_button_link`, `sort_order`, `created_at`) VALUES ('3', 'Partners in Community Development', 'We work with government, NGOs, and private organizations to bring support and opportunities closer to our fishing communities.', 'uploads/carousel/1771270009_slide3.jpg', 'Learn More', 'about_us.php', 'Join Us', 'contact_us.php', '3', '2026-02-17 19:26:31');


-- Table: home_carousel_slides_archive
DROP TABLE IF EXISTS `home_carousel_slides_archive`;

CREATE TABLE `home_carousel_slides_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` text NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `primary_button_label` varchar(100) DEFAULT 'Learn More',
  `primary_button_link` varchar(255) DEFAULT 'about_us.php',
  `secondary_button_label` varchar(100) DEFAULT 'Join Us',
  `secondary_button_link` varchar(255) DEFAULT 'contact_us.php',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `original_created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `home_carousel_slides_archive`

-- Table: member_archive
DROP TABLE IF EXISTS `member_archive`;

CREATE TABLE `member_archive` (
  `member_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `dob` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `work_type` varchar(50) DEFAULT NULL,
  `license_number` varchar(100) DEFAULT NULL,
  `boat_name` varchar(100) DEFAULT NULL,
  `fishing_area` varchar(100) DEFAULT NULL,
  `emergency_name` varchar(100) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `agreement` tinyint(1) DEFAULT 0,
  `image` varchar(255) DEFAULT 'default_member.png',
  PRIMARY KEY (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `member_archive`
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('43', 'Jovelyn  S.', 'jovelybuena2@gmail.com', '09100176413', '2025-10-15 23:40:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('64', 'Jovelyn S. Buena', '9898jknjk@gmail.com', '098765434567', '2026-01-26 21:17:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('76', 'dfgdg dfdgdf dfdfd', 'hgfdsfgvbn@gmail.com', '0987654', '2025-10-04 21:19:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('77', 'Cristopher M. De Jesus', 'dejesus@gmail.com', '098765434567', '2026-02-14 18:17:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('78', 'mew S meow', 'dkvodsfwefjwscd@gmail.com', '09876543', '2026-02-14 18:10:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('80', 'Ignatius q Pittman', 'fifuz@mailinator.com', '+1 (641) 841-86', '2026-03-07 02:55:45', '1974-12-16', 'Female', 'Fuga Illum ea alia', 'Bangkero', '770', 'Adena Cox', 'Corrupti sint quo r', 'Bree Curtis', '+1 (481) 765-3659', '1', 'member_68efc09ac12232.36903530_anime-girl-blue-eyes-white-hair-4k-wallpaper-uhdpaper.com-3025d.jpg');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('81', 'ghfd gfh fgdfg', 'ytuuyfgg@gmail.com', '0987654', '2025-10-15 23:38:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('82', 'meew s dsdfdf', 'fgfgfgg@gmail.com', '90876543', '2025-10-15 23:33:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('83', 'Irma Id consequat Et exe Young', 'zyqido@mailinator.com', '+1 (197) 621-40', '2025-10-15 23:33:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('84', 'Tad Placeat quia qui sa Ingram', 'zajav@mailinator.com', '+1 (201) 356-43', '2026-01-26 09:50:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('85', 'Ishmael X Stevens', 'fuqohymyka@example.com', '09620356555', '2026-04-15 03:27:45', '1975-01-27', 'Male', '930 East Rocky Milton Freeway', 'Fisherman', '', 'Hollee Gray', 'Optio atque corpori', 'Cyrus Rosa', '+1 (788) 159-6648', '1', 'member_69ceb6687dfd89.49338048_download15.jfif');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('87', 'Kirsten E Vaughn', 'sihyle@example.com', '9999422326', '2026-02-14 19:26:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('91', 'Cailin W Garcia', 'taluxumad@example.com', '1414-473-1257', '2026-04-15 03:27:59', '1974-02-03', 'Male', '46 South Second Street, Ad obcaecati consequ, Explicabo Quia repr, Nam enim provident, Region VI 2209', 'Both', '', '', NULL, 'Harriet Spence', '1988-948-1617', '1', 'member_69c360f14c6342.55282697_Gemini_Generated_Image_6bsa6y6bsa6y6bsa.png');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('94', 'Lev B Washington', 'womeh@example.com', '1647-745-2924', '2026-04-04 02:42:03', '1981-07-15', 'Female', '919 Second Avenue, Qui quam ea consequu, Voluptates saepe et, Dolore consequatur d, NCR 2209', 'Both', '', '', '', 'Melinda Frederick', '1651-432-4866', '1', 'member_69c3610b592b44.45769530_testFile.png');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('95', 'Robert V takasa', 'robertosanseaa@example.com', '09620562555', '2026-04-15 03:27:05', '1982-02-02', 'Male', '640B Coastal Road, Barretto, Olongapo City, Zambales, Region II 2200', 'Fisherman', '', '', '', 'Oprah Fowler', '0963-565-4524', '1', 'member_69ceb6f3a34a70.43635561_fc2400be-9d89-4c78-a9d3-225e0429c6f7.jfif');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('96', 'Meghan L Hancock', 'wuba@example.com', '1584-994-4225', '2026-04-15 03:26:49', '1972-08-23', 'Male', '83 New Freeway, Aliquip est voluptat, Et quam officiis aut, Doloribus nulla magn, Region VII 2209', 'Both', '', '', NULL, 'Anika Hoffman', '1951-128-8958', '1', 'member_69ceb87306d127.62122841_testFile.png');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('97', 'Darrel L Baldwin', 'xutyke@example.com', '1478-844-1228', '2026-04-15 03:26:54', '2004-11-17', 'Male', '58 Rocky Nobel Freeway, Velit non do mollit, Quam eum aliquid est, Similique mollitia v, NCR 2209', 'Fisherman', '', '', NULL, 'Fritz Petersen', '1398-742-6298', '1', 'member_69ceb89127a5f1.15771067_testFile.png');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`, `dob`, `gender`, `address`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('98', 'Ivan Pr Sargent', 'nojeduni@mailinator.com', '1172-775-9673', '2026-04-15 03:26:59', '1980-08-08', 'Female', 'Eligendi vitae esse, Aliqua Alias do pla, Id laudantium conse, Dolores qui irure an, Region II 2209', 'Bangkero', '', '', NULL, 'Abigail Wilkins', '1148-598-5239', '1', 'default_member.png');


-- Table: member_attendance
DROP TABLE IF EXISTS `member_attendance`;

CREATE TABLE `member_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('present','absent','excused') NOT NULL DEFAULT 'present',
  `remarks` text DEFAULT NULL,
  `encoded_by` int(11) DEFAULT NULL,
  `encoded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_member_event` (`member_id`,`event_id`),
  KEY `idx_attendance_date` (`attendance_date`),
  KEY `fk_att_event` (`event_id`),
  CONSTRAINT `fk_att_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_att_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `member_attendance`
INSERT INTO `member_attendance` (`id`, `member_id`, `event_id`, `attendance_date`, `time_in`, `time_out`, `status`, `remarks`, `encoded_by`, `encoded_at`) VALUES ('5', '67', '14', '2026-07-24', '22:25:00', '22:27:00', 'present', '', '321220', '2026-02-24 14:28:22');
INSERT INTO `member_attendance` (`id`, `member_id`, `event_id`, `attendance_date`, `time_in`, `time_out`, `status`, `remarks`, `encoded_by`, `encoded_at`) VALUES ('6', '67', '17', '2027-04-16', '22:26:00', NULL, 'present', '', '321220', '2026-02-24 14:28:34');
INSERT INTO `member_attendance` (`id`, `member_id`, `event_id`, `attendance_date`, `time_in`, `time_out`, `status`, `remarks`, `encoded_by`, `encoded_at`) VALUES ('7', '67', '39', '2026-03-05', '23:54:00', '21:56:00', 'present', '', '321220', '2026-02-24 16:10:39');
INSERT INTO `member_attendance` (`id`, `member_id`, `event_id`, `attendance_date`, `time_in`, `time_out`, `status`, `remarks`, `encoded_by`, `encoded_at`) VALUES ('10', '67', '27', '2026-01-18', '16:19:00', '20:19:00', 'present', '', '321220', '2026-02-28 08:21:33');
INSERT INTO `member_attendance` (`id`, `member_id`, `event_id`, `attendance_date`, `time_in`, `time_out`, `status`, `remarks`, `encoded_by`, `encoded_at`) VALUES ('16', '88', '46', '2026-04-30', '15:36:00', '15:38:00', 'present', '', '321229', '2026-04-20 15:34:56');
INSERT INTO `member_attendance` (`id`, `member_id`, `event_id`, `attendance_date`, `time_in`, `time_out`, `status`, `remarks`, `encoded_by`, `encoded_at`) VALUES ('17', '67', '46', '2026-04-30', '15:36:00', '16:39:00', 'present', '', '321229', '2026-04-20 15:34:56');


-- Table: members
DROP TABLE IF EXISTS `members`;

CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `street` varchar(255) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `municipality` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `region` varchar(50) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `membership_status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `dob` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `civil_status` varchar(20) DEFAULT NULL,
  `work_type` enum('Fisherman','Bangkero','Both') DEFAULT NULL,
  `membership_type` varchar(20) DEFAULT NULL,
  `license_number` varchar(50) NOT NULL,
  `municipal_permit_no` varchar(50) DEFAULT NULL,
  `bfar_fisherfolk_id` varchar(50) DEFAULT NULL,
  `boat_name` varchar(255) DEFAULT NULL,
  `fishing_area` varchar(255) DEFAULT NULL,
  `emergency_name` varchar(255) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `agreement` tinyint(1) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_civil_status` (`civil_status`),
  KEY `idx_membership_type` (`membership_type`),
  KEY `idx_municipality` (`municipality`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `members`
INSERT INTO `members` (`id`, `name`, `email`, `phone`, `address`, `street`, `barangay`, `municipality`, `province`, `region`, `zip_code`, `membership_status`, `created_at`, `dob`, `gender`, `civil_status`, `work_type`, `membership_type`, `license_number`, `municipal_permit_no`, `bfar_fisherfolk_id`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('62', 'Jose M. Manalo', 'joseantonio@gmail.com', '098866554433', 'Calapacuan, Subic Zambales', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2025-09-11 14:02:06', '2003-01-28', 'Male', NULL, 'Fisherman', NULL, '', '', '', 'sdfdfd', 'sdfdf', 'sdff', 'sdffds', '1', 'member_69cebc29b70641.24943470_Gemini_Generated_Image_oixh2poixh2poixh.png');
INSERT INTO `members` (`id`, `name`, `email`, `phone`, `address`, `street`, `barangay`, `municipality`, `province`, `region`, `zip_code`, `membership_status`, `created_at`, `dob`, `gender`, `civil_status`, `work_type`, `membership_type`, `license_number`, `municipal_permit_no`, `bfar_fisherfolk_id`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('67', 'Noli Boy N Cocjin', 'noliboy@gmail.com', '098786765777', 'Bulacan', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2025-09-19 12:17:39', '2006-07-12', 'Male', NULL, 'Fisherman', NULL, '', '', '', 'argie', 'bulacan', 'dkjfdfsii', '098789', '1', 'member_69d7d8b2ab5897.89146273_Screenshot2026-04-10004813.png');
INSERT INTO `members` (`id`, `name`, `email`, `phone`, `address`, `street`, `barangay`, `municipality`, `province`, `region`, `zip_code`, `membership_status`, `created_at`, `dob`, `gender`, `civil_status`, `work_type`, `membership_type`, `license_number`, `municipal_permit_no`, `bfar_fisherfolk_id`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('88', 'Bella K encarnason', 'bellaencarnason@example.com', '0962-043-3414', '1123 National Highway, Barretto, Olongapo City, Zambales, Region III 2200', '1123 National Highway', 'Barretto', 'Olongapo City', 'Zambales', 'Region III', '2200', 'active', '2026-03-07 02:46:13', '1983-06-07', 'Female', 'Single', 'Fisherman', NULL, '', 'MP-2026-0157', '2026-FISH-00123', 'Alea Gallegos', '', 'Nina Roach', '0974-474-6222', '1', 'member_69a87fb98f35f7.77010506_Screenshot2026-03-05025328.png');
INSERT INTO `members` (`id`, `name`, `email`, `phone`, `address`, `street`, `barangay`, `municipality`, `province`, `region`, `zip_code`, `membership_status`, `created_at`, `dob`, `gender`, `civil_status`, `work_type`, `membership_type`, `license_number`, `municipal_permit_no`, `bfar_fisherfolk_id`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('89', 'Roanna R Balanas', 'roansbalanas2@example.com', '0962-046-5456', '0456 Baloy Beach Road, Barretto, Olongapo City, Zambales, Region III 2200', '0456 Baloy Beach Road', 'Barretto', 'Olongapo City', 'Zambales', 'Region III', '2200', 'active', '2026-03-07 02:48:51', '1985-01-29', 'Male', 'Single', 'Bangkero', NULL, '', '', '', 'Balanas Boat', NULL, 'Gloria Spears', '0963-215-6656', '1', 'member_69a87e93ad78c3.36926512_Gemini_Generated_Image_oixh2poixh2poixh.png');
INSERT INTO `members` (`id`, `name`, `email`, `phone`, `address`, `street`, `barangay`, `municipality`, `province`, `region`, `zip_code`, `membership_status`, `created_at`, `dob`, `gender`, `civil_status`, `work_type`, `membership_type`, `license_number`, `municipal_permit_no`, `bfar_fisherfolk_id`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('93', 'Charissa V Humphrey', 'hedevo@example.com', '0962-056-4554', '719 West White Hague Drive, Dolorem commodo mini, Ea voluptas consecte, Quia dolor ut sed po, Region IX 2209', '719 West White Hague Drive', 'Dolorem commodo mini', 'Ea voluptas consecte', 'Quia dolor ut sed po', 'Region IX', '2209', 'active', '2026-04-04 02:32:22', '1977-08-04', 'Female', 'Single', 'Bangkero', NULL, '', '', '', '', NULL, 'Kasper Weiss', '1574-213-5147', '1', 'member_69ceb636875099.02151232_Gemini_Generated_Image_oeo4bxoeo4bxoeo4.png');


-- Table: mission_vision
DROP TABLE IF EXISTS `mission_vision`;

CREATE TABLE `mission_vision` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mission` longtext NOT NULL,
  `vision` longtext NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `mission_vision`
INSERT INTO `mission_vision` (`id`, `mission`, `vision`, `updated_at`) VALUES ('1', 'To empower local fishermen and boatmen through collaboration, sustainable practices, training programs, and strong leadership, ensuring the welfare and continuous development of our members and their families.', 'To be the leading fishermen association in the region, recognized for fostering unity, promoting sustainable fishing practices, and creating lasting opportunities for growth and prosperity in our community.', '2026-02-17 18:22:04');


-- Table: officer_roles
DROP TABLE IF EXISTS `officer_roles`;

CREATE TABLE `officer_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `display_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_role_name` (`role_name`),
  KEY `idx_display_order` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `officer_roles`
INSERT INTO `officer_roles` (`id`, `role_name`, `description`, `created_at`, `updated_at`, `display_order`) VALUES ('1', 'President', 'Chief executive officer; oversees overall operations and represents the organization', '0000-00-00 00:00:00', '2026-03-07 00:37:24', '1');
INSERT INTO `officer_roles` (`id`, `role_name`, `description`, `created_at`, `updated_at`, `display_order`) VALUES ('2', 'Vice President', 'Assists the President and assumes duties in their absence; oversees specific committees', '0000-00-00 00:00:00', '2026-03-07 00:37:24', '2');
INSERT INTO `officer_roles` (`id`, `role_name`, `description`, `created_at`, `updated_at`, `display_order`) VALUES ('3', 'Secretary', 'Maintains records, minutes of meetings, and official correspondence', '0000-00-00 00:00:00', '2026-03-07 00:37:24', '3');
INSERT INTO `officer_roles` (`id`, `role_name`, `description`, `created_at`, `updated_at`, `display_order`) VALUES ('4', 'Treasurer', 'Manages financial records, budgets, and monetary transactions', '0000-00-00 00:00:00', '2026-03-07 00:37:24', '4');
INSERT INTO `officer_roles` (`id`, `role_name`, `description`, `created_at`, `updated_at`, `display_order`) VALUES ('6', 'Auditor', 'Reviews and verifies financial records for accuracy and compliance', '2026-03-07 00:26:54', '2026-03-07 00:37:24', '5');
INSERT INTO `officer_roles` (`id`, `role_name`, `description`, `created_at`, `updated_at`, `display_order`) VALUES ('7', 'Business Manager', 'Manages business operations and fundraising activities', '2026-03-07 00:27:03', '2026-03-07 00:37:24', '6');
INSERT INTO `officer_roles` (`id`, `role_name`, `description`, `created_at`, `updated_at`, `display_order`) VALUES ('8', 'Peace Officer', 'Maintains order and ensures adherence to rules during meetings', '2026-03-07 00:27:29', '2026-03-07 00:37:24', '7');
INSERT INTO `officer_roles` (`id`, `role_name`, `description`, `created_at`, `updated_at`, `display_order`) VALUES ('9', 'Sergeant-at-Arms', 'Ensures security, maintains discipline, and executes ceremonial duties', '2026-03-07 00:27:37', '2026-03-07 00:37:24', '8');


-- Table: officer_roles_archive
DROP TABLE IF EXISTS `officer_roles_archive`;

CREATE TABLE `officer_roles_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `role_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data for table `officer_roles_archive`
INSERT INTO `officer_roles_archive` (`archive_id`, `original_id`, `role_name`, `description`, `created_at`, `archived_at`) VALUES ('1', '5', 'Board of Director', 'Provides oversight and policy guidance; participates in approvals and planning.', '1970-01-01 08:00:00', '2026-03-07 00:26:40');


-- Table: officers
DROP TABLE IF EXISTS `officers`;

CREATE TABLE `officers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `position` varchar(255) NOT NULL,
  `term_start` date NOT NULL,
  `term_end` date NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `fk_role` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `officers`
INSERT INTO `officers` (`id`, `member_id`, `position`, `term_start`, `term_end`, `image`, `role_id`, `description`) VALUES ('36', '67', 'Secretary', '2025-09-08', '2025-09-24', '1758256680_Screenshot 2024-07-27 113641.png', NULL, NULL);
INSERT INTO `officers` (`id`, `member_id`, `position`, `term_start`, `term_end`, `image`, `role_id`, `description`) VALUES ('39', '62', 'President', '2025-09-18', '2025-09-10', '1758257402_Screenshot 2024-04-15 220726.png', NULL, NULL);
INSERT INTO `officers` (`id`, `member_id`, `position`, `term_start`, `term_end`, `image`, `role_id`, `description`) VALUES ('41', '62', 'President', '2025-09-16', '2025-09-23', '1758258737_background.png', NULL, 'fgfgfg');
INSERT INTO `officers` (`id`, `member_id`, `position`, `term_start`, `term_end`, `image`, `role_id`, `description`) VALUES ('49', '67', '', '2025-10-14', '2029-05-14', '1775753417_Screenshot 2026-04-10 004813.png', '1', 'A dedicated leader of the Bankero & Fishermen Association, Mr. Noli Boy steers the organization with vision and commitment. As President, he presides over all official meetings, represents the association in community and government engagements, and ensures that the welfare of every member remains the foundation of every decision.');
INSERT INTO `officers` (`id`, `member_id`, `position`, `term_start`, `term_end`, `image`, `role_id`, `description`) VALUES ('55', '62', '', '2024-12-08', '2026-02-27', '1764511540_Screenshot 2025-04-23 150253.png', '2', 'J. Jose serves as a dedicated and visionary Vice president, bringing over 15 years of leadership experience in strategic planning, organizational development, and community engagement. ');
INSERT INTO `officers` (`id`, `member_id`, `position`, `term_start`, `term_end`, `image`, `role_id`, `description`) VALUES ('59', '88', '', '2026-03-02', '2026-04-30', '1772651106_Screenshot 2026-03-05 025328.png', '3', 'Bella K. Encarnason keeps the Bankero & Fishermen Association organized and well-documented. As Secretary, she records the minutes of every meeting, manages official correspondence, and maintains the integrity of the association\'s records — ensuring that every voice heard in the organization is properly preserved.');
INSERT INTO `officers` (`id`, `member_id`, `position`, `term_start`, `term_end`, `image`, `role_id`, `description`) VALUES ('61', '89', '', '2026-02-09', '2027-06-15', '1772651314_Screenshot 2026-03-05 030823.png', '4', 'Roanna R. Balanas safeguards the financial trust placed in the Bankero & Fishermen Association. As Treasurer, she manages the collection of dues, maintains accurate financial records, and presents transparent reports to the membership — upholding the association\'s commitment to honesty and accountability.');
INSERT INTO `officers` (`id`, `member_id`, `position`, `term_start`, `term_end`, `image`, `role_id`, `description`) VALUES ('62', '62', '', '2026-03-29', '2026-08-20', '1775156307_Gemini_Generated_Image_oixh2poixh2poixh.png', '7', 'Jose M. Manalo plays a vital role in sustaining the financial and operational growth of the Bankero & Fishermen Association. As Business Manager, he oversees income-generating projects, manages partnerships, and works to ensure the association\'s programs are well-funded and running efficiently for the benefit of its members.');


-- Table: officers_archive
DROP TABLE IF EXISTS `officers_archive`;

CREATE TABLE `officers_archive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `term_start` date DEFAULT NULL,
  `term_end` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `officers_archive`
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('2', '67', '3', '2025-09-23', '2025-09-10', '1758266934_background.png', '2025-10-04 21:43:55');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('3', '62', '1', '2023-06-12', '2025-11-19', '1757571148_Screenshot 2025-09-08 003235.png', '2025-10-12 23:01:38');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('4', '79', '1', '2025-10-15', '2025-10-21', NULL, '2025-10-12 23:01:41');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('5', '67', '3', '2025-10-08', '2030-06-05', '1759659520_Screenshot 2025-09-08 003107.png', '2025-10-12 23:01:43');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('7', '79', '2', '2025-10-08', '2025-12-11', '1760280765_background.png', '2025-10-12 23:01:47');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('8', '64', '2', '2025-09-08', '2025-10-11', '1757586797_Screenshot 2025-09-08 003004.png', '2025-10-12 23:01:50');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('9', '43', '3', '2025-10-23', '2025-10-23', '1760282388_Screenshot 2024-04-05 214617.png', '2025-10-12 23:20:05');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('10', '43', '3', '2025-10-18', '2025-10-14', '1760281938_Screenshot 2024-04-20 212354.png', '2025-10-12 23:20:08');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('11', '79', '5', '2020-02-22', '1984-10-23', '', '2025-10-15 23:40:33');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('12', '67', '1', '2025-11-06', '2029-06-11', '1760281376_Screenshot 2025-10-12 121319.png', '2025-11-30 22:05:51');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('13', '83', '8', '2026-02-25', '2026-03-27', '', '2026-03-07 02:56:00');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('14', '97', '6', '2026-03-31', '2026-04-17', '', '2026-04-04 03:11:51');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('15', '96', '6', '2026-06-08', '2027-06-08', '1775411715_yuyu.jpg', '2026-04-07 01:55:42');


-- Table: partners_sponsors
DROP TABLE IF EXISTS `partners_sponsors`;

CREATE TABLE `partners_sponsors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `logo_path` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'partner',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `website_url` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `partners_sponsors`
INSERT INTO `partners_sponsors` (`id`, `name`, `logo_path`, `type`, `sort_order`, `created_at`, `website_url`) VALUES ('1', 'Municipality of Olongapo City', 'uploads/partners/1771268607_olongapo.png', 'partner', '1', '2026-02-17 19:05:17', 'https://olongapocity.gov.ph/');
INSERT INTO `partners_sponsors` (`id`, `name`, `logo_path`, `type`, `sort_order`, `created_at`, `website_url`) VALUES ('2', 'Bureau of Fisheries & Aquatic Resources', 'uploads/partners/1771268633_bfar.png', 'partner', '2', '2026-02-17 19:05:43', 'https://www.bfar.da.gov.ph/');
INSERT INTO `partners_sponsors` (`id`, `name`, `logo_path`, `type`, `sort_order`, `created_at`, `website_url`) VALUES ('3', 'Olongapo City Agriculture Department', 'uploads/partners/1771268658_agriculture.png', 'sponsor', '3', '2026-02-17 19:06:08', 'https://www.facebook.com/OlongapoCityAgricultureOffice/');
INSERT INTO `partners_sponsors` (`id`, `name`, `logo_path`, `type`, `sort_order`, `created_at`, `website_url`) VALUES ('4', 'USAID', 'uploads/partners/1771268688_usaid.png', 'sponsor', '4', '2026-02-17 19:06:38', 'https://www.usaid.gov/');


-- Table: partners_sponsors_archive
DROP TABLE IF EXISTS `partners_sponsors_archive`;

CREATE TABLE `partners_sponsors_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `logo_path` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'partner',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `original_created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `partners_sponsors_archive`

-- Table: system_config
DROP TABLE IF EXISTS `system_config`;

CREATE TABLE `system_config` (
  `id` int(11) NOT NULL,
  `assoc_name` varchar(255) NOT NULL,
  `assoc_email` varchar(255) NOT NULL,
  `assoc_phone` varchar(50) NOT NULL,
  `assoc_address` text NOT NULL,
  `assoc_logo` varchar(255) DEFAULT NULL,
  `auto_backup_status` tinyint(1) NOT NULL DEFAULT 0,
  `backup_storage_limit_mb` int(11) NOT NULL DEFAULT 100,
  `auto_backup_next_run` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `system_config`
INSERT INTO `system_config` (`id`, `assoc_name`, `assoc_email`, `assoc_phone`, `assoc_address`, `assoc_logo`, `auto_backup_status`, `backup_storage_limit_mb`, `auto_backup_next_run`) VALUES ('1', 'Bankero and Fishermen Association ', 'info@association.org', '9620433464', 'Barreto Street, Olongapo City', 'assoc_logo.png', '0', '100', NULL);


-- Table: transparency_beneficiaries
DROP TABLE IF EXISTS `transparency_beneficiaries`;

CREATE TABLE `transparency_beneficiaries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `household_name` varchar(255) DEFAULT NULL,
  `assistance_type` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `amount_value` decimal(15,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `date_assisted` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'served',
  `barangay` varchar(100) DEFAULT NULL,
  `municipality` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `short_story` text DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_transparency_beneficiaries_program_date` (`program_id`,`date_assisted`),
  KEY `idx_transparency_beneficiaries_assistance_type` (`assistance_type`),
  KEY `idx_transparency_beneficiaries_featured` (`featured`),
  CONSTRAINT `fk_transparency_beneficiaries_program` FOREIGN KEY (`program_id`) REFERENCES `transparency_programs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `transparency_beneficiaries`
INSERT INTO `transparency_beneficiaries` (`id`, `program_id`, `name`, `household_name`, `assistance_type`, `category`, `amount_value`, `quantity`, `date_assisted`, `status`, `barangay`, `municipality`, `province`, `photo_path`, `short_story`, `featured`, `created_at`, `updated_at`) VALUES ('1', NULL, 'ssa', NULL, 'Relief Goods', NULL, '499.99', '5', '2026-03-25', 'served', 'baretto', NULL, NULL, NULL, 'galing', '1', '2026-03-25 16:13:19', '0000-00-00 00:00:00');
INSERT INTO `transparency_beneficiaries` (`id`, `program_id`, `name`, `household_name`, `assistance_type`, `category`, `amount_value`, `quantity`, `date_assisted`, `status`, `barangay`, `municipality`, `province`, `photo_path`, `short_story`, `featured`, `created_at`, `updated_at`) VALUES ('3', NULL, 'dsd', NULL, 'Training', NULL, '20.00', NULL, '2026-03-31', 'served', 'ss', NULL, NULL, NULL, 'sdsd', '0', '2026-04-01 01:28:22', '0000-00-00 00:00:00');
INSERT INTO `transparency_beneficiaries` (`id`, `program_id`, `name`, `household_name`, `assistance_type`, `category`, `amount_value`, `quantity`, `date_assisted`, `status`, `barangay`, `municipality`, `province`, `photo_path`, `short_story`, `featured`, `created_at`, `updated_at`) VALUES ('5', NULL, 'jovelyn', NULL, 'Educational', NULL, '5000.00', '0', '2026-03-25', 'served', 'calapacuan', NULL, NULL, NULL, 'salamat po huhu', '1', '2026-04-01 01:33:43', '0000-00-00 00:00:00');
INSERT INTO `transparency_beneficiaries` (`id`, `program_id`, `name`, `household_name`, `assistance_type`, `category`, `amount_value`, `quantity`, `date_assisted`, `status`, `barangay`, `municipality`, `province`, `photo_path`, `short_story`, `featured`, `created_at`, `updated_at`) VALUES ('6', NULL, 'Brendan Tran', NULL, 'Financial', NULL, '552.52', '258', '1988-08-07', 'in-progress', 'Provident adipisci', NULL, NULL, NULL, 'Doloremque qui rerum', '1', '2026-04-01 01:54:05', '0000-00-00 00:00:00');


-- Table: transparency_beneficiaries_archive
DROP TABLE IF EXISTS `transparency_beneficiaries_archive`;

CREATE TABLE `transparency_beneficiaries_archive` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `original_id` int(10) unsigned NOT NULL,
  `program_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `assistance_type` varchar(50) DEFAULT NULL,
  `amount_value` decimal(15,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `date_assisted` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'served',
  `barangay` varchar(100) DEFAULT NULL,
  `short_story` text DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `archived_by` int(10) unsigned DEFAULT NULL,
  `archived_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `transparency_beneficiaries_archive`

-- Table: transparency_campaigns
DROP TABLE IF EXISTS `transparency_campaigns`;

CREATE TABLE `transparency_campaigns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `goal_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'planned',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_transparency_campaigns_slug` (`slug`),
  KEY `idx_transparency_campaigns_status` (`status`),
  KEY `idx_transparency_campaigns_dates` (`start_date`,`end_date`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `transparency_campaigns`
INSERT INTO `transparency_campaigns` (`id`, `name`, `slug`, `description`, `goal_amount`, `status`, `start_date`, `end_date`, `banner_image`, `created_at`, `updated_at`) VALUES ('6', 'DOLE Integrated Livelihood Program – Awarding of Livelihood Kits', 'dole-integrated-livelihood-program-awarding', 'Grant support under the DOLE Integrated Livelihood Program providing livelihood starter kits (processing equipment, containers, and supplies) to association members to enhance income-generating activities.', '50000.00', 'completed', '0000-00-00', '2026-02-11', '', '2026-02-17 09:21:26', '2026-02-17 09:21:26');
INSERT INTO `transparency_campaigns` (`id`, `name`, `slug`, `description`, `goal_amount`, `status`, `start_date`, `end_date`, `banner_image`, `created_at`, `updated_at`) VALUES ('7', 'Bangon Bangkero: Boat Repair & Replacement', 'bangon-bangkero-boat-assistance', 'Fundraising drive to repair and replace damaged fishing boats for registered members, including provision of basic fishing gear and safety equipment.', '20000.00', 'active', NULL, '2026-04-18', '', '2026-04-01 01:32:08', '2026-04-01 01:32:08');


-- Table: transparency_campaigns_archive
DROP TABLE IF EXISTS `transparency_campaigns_archive`;

CREATE TABLE `transparency_campaigns_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `goal_amount` decimal(15,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`archive_id`),
  KEY `idx_status` (`status`),
  KEY `idx_archived_at` (`archived_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `transparency_campaigns_archive`
INSERT INTO `transparency_campaigns_archive` (`archive_id`, `original_id`, `name`, `slug`, `description`, `goal_amount`, `status`, `start_date`, `end_date`, `banner_image`, `created_at`, `updated_at`, `archived_at`, `archived_by`) VALUES ('1', '4', 'Fisherfolk Skills & Safety Training', 'fisherfolk-skills-safety-training', 'Campaign to support a series of trainings on sea safety, financial literacy, and sustainable fishing practices for association members.', '15000.00', 'completed', NULL, '2026-02-09', '', '2026-02-19 09:07:50', '2026-03-07 03:23:11', '2026-03-08 00:12:57', '321220');
INSERT INTO `transparency_campaigns_archive` (`archive_id`, `original_id`, `name`, `slug`, `description`, `goal_amount`, `status`, `start_date`, `end_date`, `banner_image`, `created_at`, `updated_at`, `archived_at`, `archived_by`) VALUES ('3', '0', 'Emergency Relief Fund –', 'emergency-relief-bagyong-', 'Emergency relief campaign to provide food packs, clean water, and basic necessities to fishing families heavily affected by Bagyong Ramon in coastal barangays.', '25000.00', 'planned', NULL, '2026-03-16', '', NULL, NULL, '2026-04-05 00:42:34', '321229');


-- Table: transparency_donation_images
DROP TABLE IF EXISTS `transparency_donation_images`;

CREATE TABLE `transparency_donation_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `donation_id` int(11) NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_donation_id` (`donation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `transparency_donation_images`
INSERT INTO `transparency_donation_images` (`id`, `donation_id`, `image_path`, `sort_order`, `created_at`) VALUES ('1', '9', 'uploads/assistance/1775755944_2f7559e8.jpg', '0', '2026-04-11 01:32:24');
INSERT INTO `transparency_donation_images` (`id`, `donation_id`, `image_path`, `sort_order`, `created_at`) VALUES ('2', '9', 'uploads/assistance/1775755944_0c909e7c.jpg', '1', '2026-04-11 01:32:24');
INSERT INTO `transparency_donation_images` (`id`, `donation_id`, `image_path`, `sort_order`, `created_at`) VALUES ('3', '9', 'uploads/assistance/1775755944_545f072f.jpg', '2', '2026-04-11 01:32:24');
INSERT INTO `transparency_donation_images` (`id`, `donation_id`, `image_path`, `sort_order`, `created_at`) VALUES ('4', '9', 'uploads/assistance/1775755944_6163c8a5.jpg', '3', '2026-04-11 01:32:24');
INSERT INTO `transparency_donation_images` (`id`, `donation_id`, `image_path`, `sort_order`, `created_at`) VALUES ('5', '9', 'uploads/assistance/1775755944_fc6ac914.jpg', '4', '2026-04-11 01:32:24');
INSERT INTO `transparency_donation_images` (`id`, `donation_id`, `image_path`, `sort_order`, `created_at`) VALUES ('6', '9', 'uploads/assistance/1775755944_7879ee1b.jpg', '5', '2026-04-11 01:32:24');
INSERT INTO `transparency_donation_images` (`id`, `donation_id`, `image_path`, `sort_order`, `created_at`) VALUES ('7', '9', 'uploads/assistance/1775755944_a3d5186f.jpg', '6', '2026-04-11 01:32:24');
INSERT INTO `transparency_donation_images` (`id`, `donation_id`, `image_path`, `sort_order`, `created_at`) VALUES ('8', '9', 'uploads/assistance/1775755944_056b4d95.jpg', '7', '2026-04-11 01:32:24');
INSERT INTO `transparency_donation_images` (`id`, `donation_id`, `image_path`, `sort_order`, `created_at`) VALUES ('9', '9', 'uploads/assistance/1775755944_aecf72cb.jpg', '8', '2026-04-11 01:32:24');


-- Table: transparency_donation_items
DROP TABLE IF EXISTS `transparency_donation_items`;

CREATE TABLE `transparency_donation_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `donation_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit` varchar(50) DEFAULT NULL,
  `unit_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_value` decimal(15,2) GENERATED ALWAYS AS (`quantity` * `unit_value`) STORED,
  `photo` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_donation_id` (`donation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `transparency_donation_items`
INSERT INTO `transparency_donation_items` (`id`, `donation_id`, `item_name`, `quantity`, `unit`, `unit_value`, `total_value`, `photo`, `created_at`) VALUES ('1', '11', '20kg of Oil', '1.00', '', '600.00', '600.00', 'uploads/assistance/items/1776517363_item_0_caee73.jpg', '2026-04-19 21:02:43');
INSERT INTO `transparency_donation_items` (`id`, `donation_id`, `item_name`, `quantity`, `unit`, `unit_value`, `total_value`, `photo`, `created_at`) VALUES ('2', '11', 'bigas 50kg', '1.00', '2', '3000.00', '3000.00', NULL, '2026-04-19 21:02:43');
INSERT INTO `transparency_donation_items` (`id`, `donation_id`, `item_name`, `quantity`, `unit`, `unit_value`, `total_value`, `photo`, `created_at`) VALUES ('3', '12', '20kg of Oil', '1.00', '', '600.00', '600.00', 'uploads/assistance/items/1776517741_item_0_2b46e8.jpg', '2026-04-19 21:09:01');
INSERT INTO `transparency_donation_items` (`id`, `donation_id`, `item_name`, `quantity`, `unit`, `unit_value`, `total_value`, `photo`, `created_at`) VALUES ('4', '12', 'bigas 50kg', '1.00', '2', '3000.00', '3000.00', NULL, '2026-04-19 21:09:01');
INSERT INTO `transparency_donation_items` (`id`, `donation_id`, `item_name`, `quantity`, `unit`, `unit_value`, `total_value`, `photo`, `created_at`) VALUES ('23', '9', 'Pressure Cap (Pressure Cooker) - 40cm', '2.00', 'box', '1790.00', '3580.00', NULL, '2026-04-19 21:42:12');
INSERT INTO `transparency_donation_items` (`id`, `donation_id`, `item_name`, `quantity`, `unit`, `unit_value`, `total_value`, `photo`, `created_at`) VALUES ('24', '9', 'Corn Oil (Marca Leon 1 gal tin)', '5.00', 'tin', '450.00', '2250.00', NULL, '2026-04-19 21:42:12');
INSERT INTO `transparency_donation_items` (`id`, `donation_id`, `item_name`, `quantity`, `unit`, `unit_value`, `total_value`, `photo`, `created_at`) VALUES ('25', '9', 'Glass Jar / Mason Jar (24x12oz)', '8.00', 'box', '350.00', '2800.00', NULL, '2026-04-19 21:42:12');
INSERT INTO `transparency_donation_items` (`id`, `donation_id`, `item_name`, `quantity`, `unit`, `unit_value`, `total_value`, `photo`, `created_at`) VALUES ('26', '9', 'Fiesta (Kid Bubbles/ dishwashing)', '1.00', 'box', '300.00', '300.00', NULL, '2026-04-19 21:42:12');
INSERT INTO `transparency_donation_items` (`id`, `donation_id`, `item_name`, `quantity`, `unit`, `unit_value`, `total_value`, `photo`, `created_at`) VALUES ('27', '9', 'Sugar (brown)', '1.00', 'sack', '2500.00', '2500.00', NULL, '2026-04-19 21:42:12');
INSERT INTO `transparency_donation_items` (`id`, `donation_id`, `item_name`, `quantity`, `unit`, `unit_value`, `total_value`, `photo`, `created_at`) VALUES ('28', '9', 'Wooden crate (xk10110 Dz-400)', '1.00', 'crate', '500.00', '500.00', NULL, '2026-04-19 21:42:12');


-- Table: transparency_donations
DROP TABLE IF EXISTS `transparency_donations`;

CREATE TABLE `transparency_donations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(10) unsigned DEFAULT NULL,
  `donor_name` varchar(255) DEFAULT NULL,
  `donor_type` varchar(50) DEFAULT NULL,
  `donation_type` enum('cash','in_kind') NOT NULL DEFAULT 'cash',
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'PHP',
  `date_received` date NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_code` varchar(100) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'confirmed',
  `is_restricted` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_transparency_donations_campaign_date` (`campaign_id`,`date_received`),
  KEY `idx_transparency_donations_status` (`status`),
  KEY `idx_transparency_donations_reference` (`reference_code`),
  CONSTRAINT `fk_transparency_donations_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `transparency_campaigns` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `transparency_donations`
INSERT INTO `transparency_donations` (`id`, `campaign_id`, `donor_name`, `donor_type`, `donation_type`, `amount`, `currency`, `date_received`, `payment_method`, `reference_code`, `status`, `is_restricted`, `notes`, `created_at`, `updated_at`, `image_path`) VALUES ('1', '6', 'Department of Labor and Employment (DOLE)', 'organization', 'cash', '50000.00', 'PHP', '2026-02-10', 'Grant (in kind)', 'DOLE-ILP-2026-01', 'confirmed', '1', 'Livelihood starter kits (processing equipment, containers, corn oil, and supplies) awarded to members under DOLE Integrated Livelihood Program.', '2026-02-17 09:23:57', '2026-02-17 09:23:57', NULL);
INSERT INTO `transparency_donations` (`id`, `campaign_id`, `donor_name`, `donor_type`, `donation_type`, `amount`, `currency`, `date_received`, `payment_method`, `reference_code`, `status`, `is_restricted`, `notes`, `created_at`, `updated_at`, `image_path`) VALUES ('9', NULL, 'DOLE', 'DOLE', 'in_kind', '11930.00', 'PHP', '2026-04-05', NULL, '', 'confirmed', '0', '', '2026-04-10 01:32:24', '2026-04-18 21:42:12', NULL);
INSERT INTO `transparency_donations` (`id`, `campaign_id`, `donor_name`, `donor_type`, `donation_type`, `amount`, `currency`, `date_received`, `payment_method`, `reference_code`, `status`, `is_restricted`, `notes`, `created_at`, `updated_at`, `image_path`) VALUES ('10', NULL, 'Department of Labor and Employment (DOLE)', 'DOLE', 'cash', '200000.00', 'PHP', '2026-04-18', NULL, '', 'confirmed', '0', '', '2026-04-18 20:41:59', '0000-00-00 00:00:00', NULL);


-- Table: transparency_donations_archive
DROP TABLE IF EXISTS `transparency_donations_archive`;

CREATE TABLE `transparency_donations_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) NOT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `donor_name` varchar(255) NOT NULL,
  `donor_type` varchar(100) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'PHP',
  `date_received` date DEFAULT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `reference_code` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'confirmed',
  `is_restricted` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`archive_id`),
  KEY `idx_campaign_id` (`campaign_id`),
  KEY `idx_donor_type` (`donor_type`),
  KEY `idx_date_received` (`date_received`),
  KEY `idx_archived_at` (`archived_at`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `transparency_donations_archive`
INSERT INTO `transparency_donations_archive` (`archive_id`, `original_id`, `campaign_id`, `donor_name`, `donor_type`, `amount`, `currency`, `date_received`, `payment_method`, `reference_code`, `status`, `is_restricted`, `notes`, `archived_at`, `archived_by`) VALUES ('3', '0', '0', 'sd', 'DOLE', '3.00', 'PHP', '2026-03-31', '', '', '0', '0', '0', '2026-04-05 00:42:39', '321229');
INSERT INTO `transparency_donations_archive` (`archive_id`, `original_id`, `campaign_id`, `donor_name`, `donor_type`, `amount`, `currency`, `date_received`, `payment_method`, `reference_code`, `status`, `is_restricted`, `notes`, `archived_at`, `archived_by`) VALUES ('4', '0', '0', 'ss', 'DOLE', '50.00', '0', '2026-03-31', '', '', '0', '0', '', '2026-04-05 00:42:41', '321229');
INSERT INTO `transparency_donations_archive` (`archive_id`, `original_id`, `campaign_id`, `donor_name`, `donor_type`, `amount`, `currency`, `date_received`, `payment_method`, `reference_code`, `status`, `is_restricted`, `notes`, `archived_at`, `archived_by`) VALUES ('5', '0', '0', 'Philippine Red Cross (PRC)', 'NGO', '3600.00', 'PHP', '2026-04-18', '', '', 'confirmed', '0', '', '2026-04-19 21:09:07', '321229');
INSERT INTO `transparency_donations_archive` (`archive_id`, `original_id`, `campaign_id`, `donor_name`, `donor_type`, `amount`, `currency`, `date_received`, `payment_method`, `reference_code`, `status`, `is_restricted`, `notes`, `archived_at`, `archived_by`) VALUES ('6', '0', '0', 'Philippine Red Cross (PRC)', 'NGO', '3600.00', 'PHP', '2026-04-18', '', '', 'confirmed', '0', '', '2026-04-19 21:42:21', '321229');


-- Table: transparency_expenses
DROP TABLE IF EXISTS `transparency_expenses`;

CREATE TABLE `transparency_expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `expense_date` date DEFAULT NULL,
  `paid_to` varchar(255) DEFAULT NULL,
  `reference_code` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `donation_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `transparency_expenses`
INSERT INTO `transparency_expenses` (`id`, `title`, `category`, `amount`, `expense_date`, `paid_to`, `reference_code`, `notes`, `donation_id`, `created_at`, `updated_at`) VALUES ('1', 'Product Expenses', 'Livelihood', '6000.00', '2026-04-18', 'person paid', '', '0', '1', '2026-04-19 20:37:25', NULL);


-- Table: transparency_hero_settings
DROP TABLE IF EXISTS `transparency_hero_settings`;

CREATE TABLE `transparency_hero_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `transparency_hero_settings`
INSERT INTO `transparency_hero_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('1', 'title', 'Transparency & Association Progress', '2026-04-11 03:09:57');
INSERT INTO `transparency_hero_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('2', 'subtitle', 'Promoting accountability through transparent reporting of assistance received, programs implemented, and sustainable initiatives that empower our fishing community.', '2026-04-11 03:09:57');
INSERT INTO `transparency_hero_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('3', 'bg_image', 'uploads/hero_bg/transparency_hero_1775761829.jpg', '2026-04-11 03:10:29');


-- Table: transparency_impact_metrics
DROP TABLE IF EXISTS `transparency_impact_metrics`;

CREATE TABLE `transparency_impact_metrics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `metric_key` varchar(100) NOT NULL,
  `label` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `value` decimal(18,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(50) DEFAULT NULL,
  `calculation_mode` varchar(20) NOT NULL DEFAULT 'manual',
  `auto_source` varchar(100) DEFAULT NULL,
  `last_computed_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_transparency_impact_metrics_key` (`metric_key`),
  KEY `idx_transparency_impact_metrics_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `transparency_impact_metrics`
INSERT INTO `transparency_impact_metrics` (`id`, `metric_key`, `label`, `description`, `value`, `unit`, `calculation_mode`, `auto_source`, `last_computed_at`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES ('1', 'product', 'fishermen', NULL, '200.00', 'ss', 'manual', NULL, NULL, '1', '0', '2026-03-25 16:12:14', '0000-00-00 00:00:00');


-- Table: transparency_programs
DROP TABLE IF EXISTS `transparency_programs`;

CREATE TABLE `transparency_programs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `allocated_budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `utilized_budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'planned',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `linked_campaign_id` int(10) unsigned DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_transparency_programs_category_status` (`category`,`status`),
  KEY `idx_transparency_programs_linked_campaign` (`linked_campaign_id`),
  CONSTRAINT `fk_transparency_programs_campaign` FOREIGN KEY (`linked_campaign_id`) REFERENCES `transparency_campaigns` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `transparency_programs`
INSERT INTO `transparency_programs` (`id`, `name`, `category`, `description`, `allocated_budget`, `utilized_budget`, `status`, `start_date`, `end_date`, `linked_campaign_id`, `location`, `sort_order`, `created_at`, `updated_at`) VALUES ('1', 'Montana Cook', 'environmental', 'Cillum enim soluta e', '21.07', '66.89', 'ongoing', '2021-09-28', '1993-05-25', '6', 'Atlanta', '68', '2026-02-17 09:37:18', '2026-02-17 09:37:18');
INSERT INTO `transparency_programs` (`id`, `name`, `category`, `description`, `allocated_budget`, `utilized_budget`, `status`, `start_date`, `end_date`, `linked_campaign_id`, `location`, `sort_order`, `created_at`, `updated_at`) VALUES ('2', 'DOLE Integrated Livelihood – Starter Kits 2025', 'livelihood', 'Provision of starter kits and livelihood assistance to qualified beneficiaries in partnership with DOLE Integrated Livelihood Program (DILP). Includes training, orientation, and distribution of livelihood kits.', '25000.00', '22000.00', 'completed', '2026-02-02', '2026-02-25', '6', 'Driftwood, Olongapo City', '1', '2026-02-17 09:48:41', '2026-03-05 03:20:23');
INSERT INTO `transparency_programs` (`id`, `name`, `category`, `description`, `allocated_budget`, `utilized_budget`, `status`, `start_date`, `end_date`, `linked_campaign_id`, `location`, `sort_order`, `created_at`, `updated_at`) VALUES ('3', 'DOLE Integrated Livelihood – Starter Kits 2025', 'livelihood', 'Provision of starter kits and livelihood assistance to qualified beneficiaries in partnership with DOLE Integrated Livelihood Program (DILP). Includes training, orientation, and distribution of livelihood kits.', '21.00', '21.00', 'completed', '2026-02-02', '2026-02-25', '6', 'Driftwood, Olongapo City', '1', '2026-02-17 09:51:58', '2026-03-05 03:18:45');


-- Table: transparency_reports
DROP TABLE IF EXISTS `transparency_reports`;

CREATE TABLE `transparency_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `report_type` varchar(50) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_transparency_reports_type_year` (`report_type`,`year`),
  KEY `idx_transparency_reports_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `transparency_reports`

-- Table: transparency_settings
DROP TABLE IF EXISTS `transparency_settings`;

CREATE TABLE `transparency_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hero_title` varchar(255) DEFAULT NULL,
  `hero_subtitle` text DEFAULT NULL,
  `hero_last_updated_override` date DEFAULT NULL,
  `transparency_statement` text DEFAULT NULL,
  `disclaimer_text` text DEFAULT NULL,
  `show_downloads` tinyint(1) NOT NULL DEFAULT 1,
  `show_activity_gallery` tinyint(1) NOT NULL DEFAULT 1,
  `primary_color` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `transparency_settings`

-- Table: users
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `username` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `force_password_change` tinyint(1) DEFAULT 0,
  `temp_password` varchar(255) DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` enum('admin','member','officer') NOT NULL,
  `transparency_role` varchar(20) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=321230 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `users`
INSERT INTO `users` (`username`, `password_hash`, `force_password_change`, `temp_password`, `id`, `role`, `transparency_role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('argie2', '$2y$10$PXmeV0TETc4CasIO.PUGYe4s18MgWUyQOcwmCYDtimhT.By3nXXhC', '0', NULL, '321211', 'admin', NULL, 'approved', '2025-10-04 19:58:05', 'argie2@gmail.com', '096204334624', 'Male', 'bulacan, bulacan ', '1760856293_cybernetic-cool-anime-cyborg-girl-9y-1920x1080.jpg', 'argie', 'buena', NULL, '0');
INSERT INTO `users` (`username`, `password_hash`, `force_password_change`, `temp_password`, `id`, `role`, `transparency_role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('jesus', '$2y$10$PhnUYt.9NRtCq6DRfHIo/.guZDBPbcZEKYSVcmZyosBX7A1TeCJIW', '0', NULL, '321212', 'member', NULL, 'pending', '2025-10-05 12:21:05', 'dejesus@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, '77', '0');
INSERT INTO `users` (`username`, `password_hash`, `force_password_change`, `temp_password`, `id`, `role`, `transparency_role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('josemarichan', '$2y$10$eV5aMXWy16uOeZk1wT20x.dooy/pMTUZn0FjBZCI.yxJHpp2HbjQe', '0', NULL, '321214', 'officer', NULL, 'rejected', '2025-10-05 19:53:29', 'josemarichan@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');
INSERT INTO `users` (`username`, `password_hash`, `force_password_change`, `temp_password`, `id`, `role`, `transparency_role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('avina', '$2y$10$ys1a63YOm1/oqj6QvsJe/eRpdP.oLLqMYZKHbXn/kCYZcOavQy/ku', '0', NULL, '321215', 'officer', NULL, 'rejected', '2025-10-15 17:40:17', 'avina@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');
INSERT INTO `users` (`username`, `password_hash`, `force_password_change`, `temp_password`, `id`, `role`, `transparency_role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('Alexa', '$2y$10$02SlnXsbCGRHo.Ylr6RGaeqyy4iO.JoL6iYFtzgRgLBbFgfJAWf.q', '0', NULL, '321216', 'admin', NULL, 'approved', '2025-10-15 17:42:16', 'alexa@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1');
INSERT INTO `users` (`username`, `password_hash`, `force_password_change`, `temp_password`, `id`, `role`, `transparency_role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('admin', '$2y$10$T1cQRA8Y2SqmVCfXM08dF.u.DFPWWu75Cbu0tF5Q86n1mtCQOQA4O', '0', NULL, '321219', 'admin', NULL, 'approved', '2025-10-19 15:31:04', 'admin@gmail.com', '09876543456', 'Female', 'Sitio Bukid, Calapacuan Subic Zambales', '1776105165_Slayówa robloxa.jpg', 'Admin', 'San Jose', NULL, '0');
INSERT INTO `users` (`username`, `password_hash`, `force_password_change`, `temp_password`, `id`, `role`, `transparency_role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('klare', '$2y$10$d50LR9qy9u52qRHlRKjakO90anP8rG01PunxbukTPCjv9i1cTUvO2', '0', NULL, '321220', 'admin', NULL, 'approved', '2025-10-19 16:41:30', 'klare@gmail.com', '09620433464', 'Female', 'Calapacuan', '1770819859_knscsd2526-a11baf2f-4450-4b71-8f7c-a3d1776be7cd.jpg', 'Klare desteen', 'Montefalco', NULL, '1');
INSERT INTO `users` (`username`, `password_hash`, `force_password_change`, `temp_password`, `id`, `role`, `transparency_role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('John', '$2y$10$hTbYydbNq/9embkicVkbHO/8uA3g.MsCL3CdgpL24mzzQ2fTxp2fi', '0', NULL, '321221', 'admin', NULL, 'approved', '2025-12-01 15:11:15', 'johncarlmangino2@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1');
INSERT INTO `users` (`username`, `password_hash`, `force_password_change`, `temp_password`, `id`, `role`, `transparency_role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('kim', '$2y$10$Z.kcTA4TWDbusbBPru7Vxu2Rsrh4ipEK4RuQrTiU7lf50wHI88ija', '0', NULL, '321222', 'officer', NULL, 'rejected', '2025-12-01 15:11:48', 'kim@gmail.com', '095678434', 'Female', 'San Marcelino', '1764573767_Screenshot 2025-02-28 124358.png', 'Kimberly', 'Mangino', NULL, '0');
INSERT INTO `users` (`username`, `password_hash`, `force_password_change`, `temp_password`, `id`, `role`, `transparency_role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('officer', '$2y$10$9hXspEoAQUp9qwIqx7FWyuvx1/ROmn.vzyhEQlDoDRL9vryN8DoBy', '0', NULL, '321223', 'officer', 'secretary', 'approved', '2026-01-18 17:55:30', 'officers@gmail.com', '09620433464', 'Female', '', '1774972433_Screenshot 2026-03-27 214325.png', 'officer', 'officer', NULL, '0');
INSERT INTO `users` (`username`, `password_hash`, `force_password_change`, `temp_password`, `id`, `role`, `transparency_role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('katkat', '$2y$10$K60WieyP2Z6vtmw63DAwF..azLkSgKt2G9bRSCF/R4c4.OT.vrXb.', '0', NULL, '321224', 'officer', NULL, 'pending', '2026-02-10 11:00:23', 'altheakaliego@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');
INSERT INTO `users` (`username`, `password_hash`, `force_password_change`, `temp_password`, `id`, `role`, `transparency_role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('johncarl', '$2y$10$GA3I.H2dAHoL0cYc2S9afu49I00E09ZH9gi7957Iaw/NhhGqb6a/i', '0', NULL, '321225', 'admin', NULL, 'approved', '2026-02-13 17:06:37', 'johncarlmangino7@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1');
INSERT INTO `users` (`username`, `password_hash`, `force_password_change`, `temp_password`, `id`, `role`, `transparency_role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('carl', '$2y$10$lRf/vTLIcxFRiO6xFIM6gOu8ZbQYASgk6xOxiLQuSLX8dM2yzQ2oG', '0', NULL, '321226', 'officer', NULL, 'approved', '2026-02-13 17:10:51', 'johncarlmangino17@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');
INSERT INTO `users` (`username`, `password_hash`, `force_password_change`, `temp_password`, `id`, `role`, `transparency_role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('argie', '$2y$10$DvGOiIvyJK./dyStHW92gOwgQTCY5sVWrRegA/8HFanhRW5/ZT8EC', '0', NULL, '321227', 'officer', NULL, 'approved', '2026-03-06 23:29:32', 'ARGIEPO3@GMAIL.COM', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');
INSERT INTO `users` (`username`, `password_hash`, `force_password_change`, `temp_password`, `id`, `role`, `transparency_role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('jovelyn', '$2y$10$8ppgBJ1VRNFhVmBmezcPcene.qlUhgo5mP5/hPmLIQQQO5m2uY4lS', '0', NULL, '321229', 'admin', NULL, 'approved', '2026-03-26 12:10:27', 'jovelynbuena12@gmail.com', '09100176413', NULL, '', '1774973303_Screenshot 2026-03-27 214325.png', 'jovelyn', 'Buena', NULL, '1');


-- Table: users_archive
DROP TABLE IF EXISTS `users_archive`;

CREATE TABLE `users_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `transparency_role` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data for table `users_archive`
INSERT INTO `users_archive` (`archive_id`, `original_id`, `username`, `email`, `password`, `role`, `status`, `is_admin`, `created_at`, `archived_at`, `transparency_role`) VALUES ('2', '321228', 'klare31', 'ARGIEPOa3@GMAIL.COM', '$2y$10$MZA5KAySj.kODdv57zA14e4Ls.eVN3zXzTsyRvD3K7chIm9nBbm7.', 'officer', 'pending', '0', '2026-03-05 23:34:42', '2026-04-05 01:17:13', NULL);


-- Table: who_we_are
DROP TABLE IF EXISTS `who_we_are`;

CREATE TABLE `who_we_are` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `who_we_are`
INSERT INTO `who_we_are` (`id`, `title`, `content`, `image`, `created_at`, `updated_at`) VALUES ('2', 'Who we are', 'The Bankero & Fishermen Association was founded in November 2009 in Barretto, Olongapo City under the leadership of Mr. Noliboy Cocjin. Starting with around 300–400 members, the association has since grown and organized its members into smaller groups for more effective management.\r\n\r\nDedicated to supporting local boatmen and fishermen, the association serves as a vital link for their welfare and development. To strengthen communication and organizational efficiency, the association is now adopting the Bankero & Fishermen Association Management System, which will automate membership records, announcements, and event scheduling, while introducing SMS notifications for timely updates.\r\n\r\nThrough this modernization, the association continues its mission of empowering members, enhancing participation, and preserving the livelihood of the fishing community.', NULL, '2026-02-17 17:07:51', NULL);


-- Table: who_we_are_archive
DROP TABLE IF EXISTS `who_we_are_archive`;

CREATE TABLE `who_we_are_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `original_created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `who_we_are_archive`
