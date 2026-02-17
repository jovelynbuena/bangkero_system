-- Database Backup
-- Generated on: 2026-02-18 00:07:30
-- Database: sql12814263

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


-- Table: activity_logs
DROP TABLE IF EXISTS `activity_logs`;

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=213 DEFAULT CHARSET=utf8mb4;

-- Data for table `activity_logs`
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('1', '321192', 'Logged in', NULL, '::1', '2025-10-01 00:06:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('2', '321192', 'Logged in', NULL, '::1', '2025-10-01 00:11:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('3', '321192', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-10-01 00:30:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('4', '321192', 'Logged in', NULL, '::1', '2025-10-01 00:31:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('5', '321192', 'Restored member', 'Restored member: dfgdg dfdgdf dfdfd', '::1', '2025-10-01 00:38:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('6', '321192', 'Restored member: dfgdg dfdgdf dfdfd', 'Restored member: dfgdg dfdgdf dfdfd', NULL, '2025-10-01 00:45:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('7', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-01 00:55:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('8', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-01 00:57:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('9', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-01 00:58:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('10', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-01 01:02:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('11', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-01 01:06:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('12', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-01 01:06:58');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('13', '321192', 'Added announcement', 'Title: fgdfg', NULL, '2025-10-01 01:16:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('14', '321192', 'Added announcement', 'Title: erd', NULL, '2025-10-01 01:21:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('15', '321192', 'Added announcement', 'Title: tert', NULL, '2025-10-01 01:25:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('16', '321192', 'Updated announcement', 'Title: tert', NULL, '2025-10-01 01:25:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('17', '321192', 'Updated announcement', 'Title: tert', NULL, '2025-10-01 01:26:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('18', '321192', 'Updated event', 'Event: Red Sea International Sport Fishing Tournament', NULL, '2025-10-01 01:32:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('19', '321192', 'Added event', 'Event: Gone Fishing 2025!', NULL, '2025-10-01 01:34:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('20', '321192', 'Logged in', NULL, '::1', '2025-10-02 20:00:14');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('21', '321192', 'Logged in', NULL, '::1', '2025-10-02 20:23:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('22', '321211', 'Failed login attempt (not approved)', 'Attempted username: argie2', '::1', '2025-10-02 20:26:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('23', '321211', 'Failed login attempt (not approved)', 'Attempted username: argie2', '::1', '2025-10-02 20:27:20');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('24', '321211', 'Logged in', NULL, '::1', '2025-10-02 20:29:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('25', '321211', 'Logged in', NULL, '::1', '2025-10-02 20:42:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('26', '321192', 'Logged in', NULL, '::1', '2025-10-02 20:55:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('27', '321192', 'Restored member: dfgdg dfdgdf dfdfd', 'Restored member: dfgdg dfdgdf dfdfd', NULL, '2025-10-02 21:00:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('28', '321192', 'Archived officer ID: 35', 'Archived officer ID: 35', NULL, '2025-10-02 21:36:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('29', '321192', 'Restored officer: Jovelyn S Buena', 'Restored officer: Jovelyn S Buena', NULL, '2025-10-02 21:43:51');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('30', '321192', 'Archived officer ID: 43', 'Archived officer ID: 43', NULL, '2025-10-02 21:43:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('31', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-02 21:56:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('32', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-02 21:57:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('33', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-02 22:00:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('34', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-02 22:00:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('35', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-02 22:00:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('36', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-02 22:02:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('37', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-02 22:02:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('38', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-02 22:03:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('39', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-02 22:04:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('40', '321192', 'Logged in', NULL, '::1', '2025-10-03 00:58:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('41', '321211', 'Logged in', NULL, '::1', '2025-10-03 01:16:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('42', '321192', 'Logged in', NULL, '::1', '2025-10-03 01:16:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('43', '321192', 'Logged in', NULL, '::1', '2025-10-03 19:46:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('44', '321192', 'Logged in', NULL, '::1', '2025-10-08 16:09:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('45', '321192', 'Logged in', NULL, '::1', '2025-10-09 16:06:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('46', '321192', 'Added announcement', 'Title: sadasd', NULL, '2025-10-09 16:49:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('47', '321192', 'Edited announcement', 'Edited Title: sadasd', NULL, '2025-10-09 16:52:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('48', '321192', 'Added announcement', 'Title: sdfsdf', NULL, '2025-10-09 16:53:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('49', '321192', 'Added announcement', 'Title: sdfsdf', NULL, '2025-10-09 16:56:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('50', '321192', 'Edited announcement', 'Edited Title: sadasd', NULL, '2025-10-09 17:13:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('51', '321192', 'Edited announcement', 'Edited Title: sadasd', NULL, '2025-10-09 17:19:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('52', '321192', 'Edited announcement', 'Edited Title: sadasd', NULL, '2025-10-09 17:20:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('53', '321192', 'Edited announcement', 'Edited Title: Community Fishing Day', NULL, '2025-10-09 18:49:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('54', '321192', 'Edited announcement', 'Edited Title: Clean-Up Drive', NULL, '2025-10-09 18:49:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('55', '321192', 'Logged in', NULL, '::1', '2025-10-10 11:28:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('56', '321192', 'Logged in', NULL, '::1', '2025-10-10 13:23:12');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('57', '321216', 'Failed login attempt (not approved)', 'Attempted username: alexa', '::1', '2025-10-13 17:43:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('58', '321218', 'Failed login attempt (not approved)', 'Attempted username: burn', '::1', '2025-10-13 17:46:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('59', NULL, 'Failed login attempt (user not found)', 'Attempted username: burnw', '::1', '2025-10-13 17:47:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('60', '321216', 'Failed login attempt (not approved)', 'Attempted username: alexa', '::1', '2025-10-13 22:58:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('61', '321192', 'Logged in', NULL, '::1', '2025-10-17 13:17:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('62', '321192', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-10-17 14:22:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('63', '321192', 'Logged in', NULL, '::1', '2025-10-17 14:22:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('64', '321192', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-10-17 14:33:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('65', '321192', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-10-17 14:33:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('66', '321192', 'Logged in', NULL, '::1', '2025-10-17 14:33:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('67', '321192', 'Logged in', NULL, '::1', '2025-10-17 14:34:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('68', '321211', 'Logged in', NULL, '::1', '2025-10-17 14:35:30');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('69', '321211', 'Failed login attempt (wrong password)', 'Attempted username: argie2', '::1', '2025-10-17 14:45:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('70', '321211', 'Logged in', NULL, '::1', '2025-10-17 14:45:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('71', '321192', 'Logged in', NULL, '::1', '2025-10-17 15:09:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('72', '321192', 'Logged in', NULL, '::1', '2025-10-17 15:27:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('73', '321197', 'Failed login attempt (wrong password)', 'Attempted username: jovelyn', '::1', '2025-10-17 15:28:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('74', '321197', 'Failed login attempt (wrong password)', 'Attempted username: jovelyn', '::1', '2025-10-17 15:28:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('75', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-17 15:28:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('76', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-17 15:29:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('77', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-17 15:29:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('78', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-17 15:29:14');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('79', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-17 15:29:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('80', '321219', 'Logged in', NULL, '::1', '2025-10-17 15:31:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('81', NULL, 'Failed login attempt (user not found)', 'Attempted username: paimon', '::1', '2025-10-17 15:33:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('82', '321219', 'Logged in', NULL, '::1', '2025-10-17 15:36:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('83', '321211', 'Failed login attempt (wrong password)', 'Attempted username: argie2', '::1', '2025-10-17 16:24:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('84', '321211', 'Logged in', NULL, '::1', '2025-10-17 16:25:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('85', '321211', 'Logged in', NULL, '::1', '2025-10-17 16:41:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('86', '321220', 'Logged in', NULL, '::1', '2025-10-17 16:42:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('87', '321211', 'Logged in', NULL, '::1', '2025-10-17 16:42:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('88', '321211', 'Logged in', NULL, '::1', '2025-10-17 17:09:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('89', '321211', 'Logged in', NULL, '::1', '2025-10-17 17:57:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('90', '321211', 'Logged in', NULL, '::1', '2025-10-17 18:03:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('91', '321215', 'Failed login attempt (not approved)', 'Attempted username: avina', '::1', '2025-10-17 18:19:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('92', '321216', 'Logged in', NULL, '::1', '2025-10-17 18:19:51');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('93', '321220', 'Logged in', NULL, '::1', '2025-10-17 18:37:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('94', '321220', 'Logged in', NULL, '::1', '2025-10-17 18:41:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('95', '321219', 'Logged in', NULL, '::1', '2025-10-17 18:43:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('96', '321220', 'Logged in', NULL, '::1', '2025-10-17 18:45:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('97', '321220', 'Logged in', NULL, '::1', '2025-10-17 18:48:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('98', '321219', 'Logged in', NULL, '::1', '2025-10-17 18:49:14');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('99', '321219', 'Logged in', NULL, '::1', '2025-10-17 18:49:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('100', '321219', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-11-28 18:36:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('101', '321219', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-11-28 18:36:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('102', '321219', 'Logged in', NULL, '::1', '2025-11-28 18:36:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('103', '321219', 'Logged in', NULL, '::1', '2025-11-28 19:17:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('104', '321219', 'Logged in', NULL, '::1', '2025-11-28 21:16:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('105', '321220', 'Logged in', NULL, '::1', '2025-11-28 22:09:37');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('106', '321219', 'Logged in', NULL, '::1', '2025-11-28 22:15:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('107', '321219', 'Logged in', NULL, '::1', '2025-11-28 22:33:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('108', '321219', 'Logged in', NULL, '::1', '2025-11-28 22:33:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('109', '321220', 'Logged in', NULL, '::1', '2025-11-28 23:09:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('110', '321219', 'Logged in', NULL, '::1', '2025-11-29 15:03:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('111', '321219', 'Logged in', NULL, '::1', '2025-11-29 15:11:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('112', '321222', 'Logged in', NULL, '::1', '2025-11-29 15:20:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('113', '321219', 'Logged in', NULL, '::1', '2025-11-29 15:40:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('114', '321219', 'Logged in', NULL, '::1', '2025-11-29 20:19:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('115', '321219', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2026-01-12 02:35:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('116', '321219', 'Logged in', NULL, '::1', '2026-01-12 02:35:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('117', '321219', 'Added announcement', 'Title: titen', NULL, '2026-01-12 02:37:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('118', '321219', 'Logged in', NULL, '::1', '2026-01-15 22:38:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('119', '321215', 'Failed login attempt (not approved)', 'Attempted username: avina', '::1', '2026-01-16 01:55:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('120', '321220', 'Logged in', NULL, '::1', '2026-01-16 01:55:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('121', '321220', 'Logged in', NULL, '::1', '2026-01-16 01:57:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('122', '0', 'Logged in', NULL, '::1', '2026-01-16 01:57:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('123', '321219', 'Logged in', NULL, '::1', '2026-01-18 20:02:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('124', '321219', 'Logged in', NULL, '::1', '2026-01-18 20:43:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('125', '321219', 'Logged in', NULL, '::1', '2026-01-20 12:43:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('126', '321219', 'Logged in', NULL, '::1', '2026-01-24 07:58:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('127', '321219', 'Logged in', NULL, '::1', '2026-01-24 13:12:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('128', '0', 'Logged in', NULL, '::1', '2026-01-24 13:14:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('129', '321220', 'Logged in', NULL, '::1', '2026-01-24 13:15:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('130', '321220', 'Logged in', NULL, '::1', '2026-01-24 13:42:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('131', '321220', 'Added announcement', 'Title: Qui exercitation sun', NULL, '2026-01-24 13:45:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('132', '321220', 'Added announcement', 'Title: Qui adipisicing minu', NULL, '2026-01-24 13:59:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('133', '321220', 'Added announcement', 'Title: Optio mollitia duci', NULL, '2026-01-24 13:59:54');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('134', '321220', 'Edited announcement', 'Edited Title: Fishing Permit Renewal', NULL, '2026-01-24 14:01:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('135', '321219', 'Logged in', NULL, '::1', '2026-01-24 21:15:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('136', '321219', 'Logged in', NULL, '::1', '2026-01-26 14:17:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('137', '321219', 'Logged in', NULL, '::1', '2026-01-26 14:33:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('138', '321220', 'Logged in', NULL, '::1', '2026-01-26 14:33:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('139', '321220', 'Restored officer: Cristopher M. De Jesus', 'Restored officer: Cristopher M. De Jesus', NULL, '2026-01-26 14:41:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('140', '321219', 'Logged in', NULL, '::1', '2026-02-07 18:40:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('141', '321220', 'Logged in', NULL, '::1', '2026-02-07 18:42:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('142', '0', 'Failed login attempt (not approved)', 'Attempted username: katkat', '::1', '2026-02-07 19:02:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('143', '321220', 'Logged in', NULL, '::1', '2026-02-07 19:38:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('144', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-19-29.sql', '::1', '2026-02-07 20:39:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('145', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_13-44-50.sql (61,563 bytes)', '::1', '2026-02-07 20:46:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('146', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-44-50.sql', '::1', '2026-02-07 20:46:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('147', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_13-48-02.sql (62,034 bytes)', '::1', '2026-02-07 20:49:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('148', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-48-02.sql', '::1', '2026-02-07 20:49:51');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('149', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_13-50-19.sql (62,505 bytes)', '::1', '2026-02-07 20:52:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('150', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-50-19.sql', '::1', '2026-02-07 20:52:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('151', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-50-19.sql', '::1', '2026-02-07 21:00:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('152', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_14-05-11.sql (63,208 bytes)', '::1', '2026-02-07 21:06:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('153', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_14-05-11.sql', '::1', '2026-02-07 21:06:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('154', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_14-06-39.sql (63,679 bytes)', '::1', '2026-02-07 21:08:24');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('155', '321220', 'Delete Backup', 'Deleted backup file: backup_2025-10-02-16-43-24.sql', '::1', '2026-02-07 21:19:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('156', '321220', 'Delete Backup', 'Deleted backup file: test_backup_2026-02-09_14-35-02.sql', '::1', '2026-02-07 21:37:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('157', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_14-36-04.sql (64,233 bytes)', '::1', '2026-02-07 21:37:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('158', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_14-36-10.sql (64,687 bytes)', '::1', '2026-02-07 21:37:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('159', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_14-36-10.sql', '::1', '2026-02-07 21:38:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('160', '321220', 'Logged in', NULL, '::1', '2026-02-08 12:08:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('161', '321220', 'Logged in', NULL, '::1', '2026-02-09 22:22:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('162', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-09 22:26:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('163', '321220', 'Logged in', NULL, '::1', '2026-02-09 22:27:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('164', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_15-53-25.sql (67,247 bytes)', '::1', '2026-02-09 22:55:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('165', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_15-53-25.sql', '::1', '2026-02-09 22:55:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('166', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_15-53-25.sql', '::1', '2026-02-09 22:56:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('167', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_15-55-15.sql (68,121 bytes)', '::1', '2026-02-09 22:57:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('168', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_15-55-15.sql', '::1', '2026-02-09 22:57:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('169', '321220', 'Database Restore', 'Restored database from: backup_2026-02-11_16-00-21.sql', '::1', '2026-02-10 07:11:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('170', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-09-29.sql', '::1', '2026-02-10 07:11:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('171', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-10-06.sql', '::1', '2026-02-10 07:11:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('172', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-16-31.sql', '::1', '2026-02-10 07:18:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('173', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-23-19.sql', '::1', '2026-02-10 07:25:06');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('174', '321220', 'Database Restore', 'Restored database from: backup_2026-02-11_16-28-52.sql', '::1', '2026-02-10 15:44:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('175', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-46-34.sql', '::1', '2026-02-10 15:48:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('176', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-54-56.sql', '::1', '2026-02-10 15:56:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('177', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_16-54-56.sql', '::1', '2026-02-10 15:56:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('178', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-58-41.sql', '::1', '2026-02-10 16:00:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('179', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_17-08-19.sql', '::1', '2026-02-10 16:10:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('180', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_17-08-19.sql', '::1', '2026-02-10 16:10:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('181', '321220', 'Logged in', NULL, '::1', '2026-02-10 18:14:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('182', '321220', 'Logged in', NULL, '::1', '2026-02-10 18:24:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('183', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-10 18:30:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('184', '321220', 'Logged in', NULL, '::1', '2026-02-10 18:30:33');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('185', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_19-35-07.sql', '::1', '2026-02-10 18:36:54');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('186', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_19-35-07.sql', '::1', '2026-02-10 18:37:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('187', '321220', 'Database Backup', 'Created backup: backup_2026-02-12_02-02-25.sql', '::1', '2026-02-11 01:04:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('188', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-12_02-02-25.sql', '::1', '2026-02-11 01:04:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('189', '0', 'Failed login attempt (not approved)', 'Attempted username: johncarl', '::1', '2026-02-11 01:08:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('190', '321219', 'Logged in', NULL, '::1', '2026-02-11 01:09:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('191', '0', 'Failed login attempt (wrong password)', 'Attempted username: officer', '::1', '2026-02-11 01:10:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('192', '0', 'Logged in', NULL, '::1', '2026-02-11 01:10:58');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('193', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-11 01:12:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('194', '321220', 'Logged in', NULL, '::1', '2026-02-11 01:13:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('195', '321220', 'Logged in', NULL, '::1', '2026-02-11 02:00:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('196', '321220', 'Logged in', NULL, '::1', '2026-02-11 15:22:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('197', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-12 14:24:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('198', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-12 14:24:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('199', '321220', 'Logged in', NULL, '::1', '2026-02-12 14:24:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('200', '321220', 'Logged in', NULL, '::1', '2026-02-12 15:36:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('201', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-15 14:14:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('202', '321220', 'Logged in', NULL, '::1', '2026-02-15 14:14:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('203', '321220', 'Logged in', NULL, '::1', '2026-02-15 15:41:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('204', '321220', 'Logged in', NULL, '::1', '2026-02-15 16:56:30');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('205', '321220', 'Logged in', NULL, '::1', '2026-02-16 06:57:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('206', '321220', 'Database Backup', 'Created backup: backup_2026-02-17_15-05-51.sql', '::1', '2026-02-16 07:07:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('207', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-17_15-05-51.sql', '::1', '2026-02-16 07:07:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('208', '321220', 'Database Backup', 'Created backup: backup_2026-02-17_15-15-23.sql', '::1', '2026-02-16 07:17:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('209', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-17_15-15-23.sql', '::1', '2026-02-16 07:17:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('210', '321220', 'Logged in', NULL, '::1', '2026-02-16 15:25:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('211', '321220', 'Database Restore', 'Restored database from backup file: backup_2026-02-17_23-23-59.sql (415 queries executed)', '::1', '2026-02-16 23:28:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('212', '321220', 'Database Restore', 'Restored database from backup file: backup_2026-02-17_23-44-27.sql (416 queries executed)', '::1', '2026-02-17 07:46:53');


-- Table: announcements
DROP TABLE IF EXISTS `announcements`;

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `content` text,
  `date_posted` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'General',
  `expiry_date` date DEFAULT NULL,
  `posted_by` varchar(255) DEFAULT 'Admin',
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_expiry_date` (`expiry_date`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4;

-- Data for table `announcements`
INSERT INTO `announcements` (`id`, `title`, `content`, `date_posted`, `image`, `category`, `expiry_date`, `posted_by`) VALUES ('10', 'Community Fishing Day', 'Grab your rods and join us for a relaxing Fishing Day by the lake! It’s the perfect time to unwind, bond with fellow anglers, and enjoy the great outdoors. Open to all ages—everyone’s welcome!', '2025-06-16', 'Screenshot 2025-09-07 225612.png', 'General', NULL, 'Admin');
INSERT INTO `announcements` (`id`, `title`, `content`, `date_posted`, `image`, `category`, `expiry_date`, `posted_by`) VALUES ('11', 'Let’s Go Fishing!', 'Calling all fishing enthusiasts! Spend a peaceful day by the water and reel in some fun. Bring your bait, rod, and good vibes!\r\n\r\n', '2025-06-16', NULL, 'General', NULL, 'Admin');
INSERT INTO `announcements` (`id`, `title`, `content`, `date_posted`, `image`, `category`, `expiry_date`, `posted_by`) VALUES ('33', ' Fishing Tournament Announcement!', 'Join us this weekend for a friendly Fishing Tournament at the riverside! Cast your lines, compete for the biggest catch, and enjoy a day of fun and camaraderie. Don\'t forget your gear—see you there!\r\n\r\n', '2025-06-16', NULL, 'General', NULL, 'Admin');


-- Table: archived_announcements
DROP TABLE IF EXISTS `archived_announcements`;

CREATE TABLE `archived_announcements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `original_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT 'General',
  `date_posted` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- Data for table `archived_announcements`
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('2', '30', 'Clean-Up Drive', 'The Association will conduct a coastal clean-up this coming Sunday at 6:00 AM. Please bring gloves, sacks, and cleaning tools', 'Screenshot 2025-08-25 221659.png', 'Announcement', '2025-06-17 00:00:00', '2026-01-15 23:52:25');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('3', '34', 'Qui adipisicing minu', 'Laudantium culpa vo', '1769407049_testFile.png', 'Announcement', '2026-01-25 00:00:00', '2026-01-24 13:59:13');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('4', '35', 'Optio mollitia duci', 'Accusamus est praes', '1769407100_testFile.png', 'Announcement', '2026-01-25 00:00:00', '2026-01-24 14:00:07');
INSERT INTO `archived_announcements` (`id`, `original_id`, `title`, `content`, `image`, `category`, `date_posted`, `archived_at`) VALUES ('5', '32', 'Fishing Permit Renewal', 'Members are reminded to renew their fishing permits before the end of the month to avoid penalties.hehe', NULL, 'Announcement', '2025-06-17 00:00:00', '2026-01-24 14:06:01');


-- Table: association_glance
DROP TABLE IF EXISTS `association_glance`;

CREATE TABLE `association_glance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `overview` longtext NOT NULL,
  `founded_year` int(11) NOT NULL,
  `members_count` int(11) NOT NULL,
  `projects_count` int(11) NOT NULL,
  `events_count` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Data for table `association_glance`
INSERT INTO `association_glance` (`id`, `overview`, `founded_year`, `members_count`, `projects_count`, `events_count`, `updated_at`) VALUES ('1', 'Since its founding in 2009, the Bankero and Fishermen Association has been a united community of bangkeros and coastal stakeholders committed to safe, sustainable, and service‑oriented operations.\r\n\r\nThe association works closely with local government units, partner agencies, and community organizations to promote responsible tourism, protect marine resources, and uplift the lives of its members and their families.\r\n\r\nThrough regular trainings, livelihood programs, and outreach activities, the Bankero and Fishermen Association continues to strengthen camaraderie, professionalism, and shared responsibility among its members.', '2009', '450', '50', '62', '2026-02-15 18:44:03');


-- Table: awards
DROP TABLE IF EXISTS `awards`;

CREATE TABLE `awards` (
  `award_id` int(11) NOT NULL AUTO_INCREMENT,
  `award_title` varchar(255) NOT NULL,
  `awarding_body` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text,
  `year_received` int(11) DEFAULT NULL,
  `date_received` date DEFAULT NULL,
  `award_image` varchar(255) DEFAULT NULL,
  `certificate_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`award_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- Data for table `awards`
INSERT INTO `awards` (`award_id`, `award_title`, `awarding_body`, `category`, `description`, `year_received`, `date_received`, `award_image`, `certificate_file`, `created_at`) VALUES ('1', 'Outstanding Coastal Resource Management Award', 'BFAR Region III', 'Regional', 'Recognized for exemplary efforts in sustainable fishing practices, marine conservation initiatives, and community-led coastal protection programs in Olongapo City.', '2025', '2026-02-03', '1770855144_3748da6d-7b9a-4046-b8ce-8b4950b0863e.jpg', 'cert_1770855144_Screenshot2026-02-12081155.png', '2026-02-11 00:14:10');


-- Table: awards_archive
DROP TABLE IF EXISTS `awards_archive`;

CREATE TABLE `awards_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `award_id` int(11) NOT NULL,
  `award_title` varchar(255) NOT NULL,
  `awarding_body` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text,
  `year_received` int(11) NOT NULL,
  `date_received` date NOT NULL,
  `award_image` varchar(255) DEFAULT NULL,
  `certificate_file` varchar(255) DEFAULT NULL,
  `original_created_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`archive_id`),
  KEY `award_id` (`award_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- Data for table `awards_archive`
INSERT INTO `awards_archive` (`archive_id`, `award_id`, `award_title`, `awarding_body`, `category`, `description`, `year_received`, `date_received`, `award_image`, `certificate_file`, `original_created_at`, `archived_at`) VALUES ('1', '2', 'Excellence in Community Fisheries Development', 'Department of Agriculture', 'National', 'Awarded for outstanding contribution in improving livelihood opportunities, strengthening fisherfolk organizations, and implementing effective fisheries development programs', '2026', '2026-02-02', '1770855239_d2e2cba5-9d81-4867-bdf6-796219834802.jpg', '', '2026-02-11 00:15:46', '2026-02-12 19:22:25');
INSERT INTO `awards_archive` (`archive_id`, `award_id`, `award_title`, `awarding_body`, `category`, `description`, `year_received`, `date_received`, `award_image`, `certificate_file`, `original_created_at`, `archived_at`) VALUES ('2', '4', 'Et similique volupta', 'Quia rerum nihil sin', 'Regional', 'Nihil eligendi reici', '1983', '2008-08-02', '1771011846_testFile.png', 'cert_1771011846_testFile.pdf', '2026-02-12 19:45:54', '2026-02-12 19:46:00');


-- Table: backups
DROP TABLE IF EXISTS `backups`;

CREATE TABLE `backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `filesize` bigint(20) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

-- Data for table `backups`
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('1', 'backup_2026-02-09_14-36-04.sql', '64233', '321220', '2026-02-07 21:37:49');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('2', 'backup_2026-02-09_14-36-10.sql', '64687', '321220', '2026-02-07 21:37:55');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('3', 'backup_2026-02-11_15-53-25.sql', '67247', '321220', '2026-02-09 22:55:11');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('4', 'backup_2026-02-11_15-55-15.sql', '68121', '321220', '2026-02-09 22:57:02');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('5', 'backup_2026-02-11_16-09-29.sql', '68561', '321220', '2026-02-10 07:11:15');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('6', 'backup_2026-02-11_16-10-06.sql', '68956', '321220', '2026-02-10 07:11:53');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('7', 'backup_2026-02-11_16-16-31.sql', '69351', '321220', '2026-02-10 07:18:18');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('8', 'backup_2026-02-11_16-23-19.sql', '69746', '321220', '2026-02-10 07:25:06');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('9', 'backup_2026-02-11_16-46-34.sql', '70374', '321220', '2026-02-10 15:48:21');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('10', 'backup_2026-02-11_16-54-56.sql', '70770', '321220', '2026-02-10 15:56:42');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('11', 'backup_2026-02-11_16-58-41.sql', '71398', '321220', '2026-02-10 16:00:28');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('12', 'backup_2026-02-11_17-08-19.sql', '71794', '321220', '2026-02-10 16:10:05');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('13', 'backup_2026-02-11_19-35-07.sql', '74479', '321220', '2026-02-10 18:36:54');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('14', 'backup_2026-02-12_02-02-25.sql', '76859', '321220', '2026-02-11 01:04:17');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('15', 'backup_2026-02-17_15-05-51.sql', '103053', '321220', '2026-02-16 07:07:44');
INSERT INTO `backups` (`id`, `filename`, `filesize`, `created_by`, `created_at`) VALUES ('16', 'backup_2026-02-17_15-15-23.sql', '103682', '321220', '2026-02-16 07:17:17');


-- Table: contact_messages
DROP TABLE IF EXISTS `contact_messages`;

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- Data for table `contact_messages`
INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `status`, `created_at`) VALUES ('1', 'Jovelyn Buena', 'jovelybuena12@gmail.com', 'hi po hehe ganda nyo po', 'read', '2025-10-17 17:16:22');
INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `status`, `created_at`) VALUES ('2', 'he', 'jovelybuena12@gmail.com', 'gello\r\n', 'unread', '2025-10-27 17:20:02');


-- Table: contact_messages_archive
DROP TABLE IF EXISTS `contact_messages_archive`;

CREATE TABLE `contact_messages_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `message` text,
  `status` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data for table `contact_messages_archive`

-- Table: core_values
DROP TABLE IF EXISTS `core_values`;

CREATE TABLE `core_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

-- Data for table `core_values`
INSERT INTO `core_values` (`id`, `title`, `description`, `sort_order`, `created_at`) VALUES ('2', 'Unity', 'We stand together as one association, helping and supporting one another in every challenge and opportunity.', '1', '2026-02-15 18:31:53');
INSERT INTO `core_values` (`id`, `title`, `description`, `sort_order`, `created_at`) VALUES ('3', 'Integrity', 'We act with honesty and transparency in all our decisions and transactions for the welfare of our members.', '2', '2026-02-15 18:32:14');
INSERT INTO `core_values` (`id`, `title`, `description`, `sort_order`, `created_at`) VALUES ('4', 'Sustainability', 'We promote responsible fishing and boating practices to protect our seas and ensure a livelihood for future generations.', '4', '2026-02-15 18:32:25');
INSERT INTO `core_values` (`id`, `title`, `description`, `sort_order`, `created_at`) VALUES ('5', 'Service', 'We are committed to serving our members and the community through programs, trainings, and timely assistance.', '3', '2026-02-15 18:32:44');
INSERT INTO `core_values` (`id`, `title`, `description`, `sort_order`, `created_at`) VALUES ('6', 'Accountability', 'We take responsibility for our actions, keep our word, and use association resources with care and fairness.', '5', '2026-02-15 18:33:22');
INSERT INTO `core_values` (`id`, `title`, `description`, `sort_order`, `created_at`) VALUES ('7', 'Compassion', 'We value each member’s situation and work to uplift the lives of fishermen, boatmen, and their families.', '6', '2026-02-15 18:33:52');


-- Table: core_values_archive
DROP TABLE IF EXISTS `core_values_archive`;

CREATE TABLE `core_values_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `original_created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Data for table `core_values_archive`

-- Table: downloadable_resources
DROP TABLE IF EXISTS `downloadable_resources`;

CREATE TABLE `downloadable_resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_key` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `icon_class` varchar(100) DEFAULT NULL,
  `color_hex` varchar(20) DEFAULT '#0d6efd',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Data for table `downloadable_resources`
INSERT INTO `downloadable_resources` (`id`, `file_key`, `title`, `icon_class`, `color_hex`, `sort_order`, `is_active`, `created_at`) VALUES ('1', 'membership_form', 'Membership Form', '', '#0d6efd', '1', '1', '2026-02-15 19:43:03');


-- Table: downloadable_resources_archive
DROP TABLE IF EXISTS `downloadable_resources_archive`;

CREATE TABLE `downloadable_resources_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `file_key` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `icon_class` varchar(100) DEFAULT NULL,
  `color_hex` varchar(20) DEFAULT '#0d6efd',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `original_created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data for table `downloadable_resources_archive`

-- Table: events
DROP TABLE IF EXISTS `events`;

CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_poster` varchar(255) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `location` text NOT NULL,
  `category` varchar(100) DEFAULT 'General',
  `is_archived` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4;

-- Data for table `events`
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('3', '../uploads/Screenshot_2025-04-12_133511.png', 'Gone Fishing 2025!', ' Gone Fishing is a fun and relaxing community event that brings together fishing enthusiasts of all ages. Whether youre a seasoned angler or trying it out for the first time, this event offers a great opportunity to enjoy the outdoors, share techniques, and build camaraderie among fellow fishermen. ', '2025-05-09', '16:54:00', 'Baloy olongapo city', 'General', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('4', '../uploads/Screenshot_2025-04-12_145620.png', 'Big BAS Event (Bangkero and Fishermen Association Special Gathering)', 'The Big BAS Event is the annual grand gathering of the Bangkero and Fishermen Association—a celebration of unity, hard work, and community spirit.It’s a day of fun, recognition, and connection for all members and their families. Come celebrate the heart of our coastal community at the biggest event of the year!', '2025-05-03', '16:54:00', 'Subic Zambales', 'General', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('6', 'Screenshot_2025-06-04_105423.png', 'Red Sea International Sport Fishing Tournament', 'Red Sea Int`l Sport Fishing Tournament, will be the first global tournament to host top anglers from all around the world along with local teams competing in both Trolling ', '2025-06-27', '12:47:00', 'San maracelino', 'General', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('12', 'Screenshot_2025-08-25_221659.png', 'Red Sea International Sport Fishing Tournament', 'Everyone is expected to come', '2025-08-27', '10:19:00', 'Drift Wood Baretto Olongapo City', 'General', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('13', 'Screenshot_2025-09-07_224712.png', '1. Family-Friendly Fishing Tournament (Pine Island)', 'A welcoming event geared toward families, featuring casual competition, a captains meeting, food, and drinks. It&#039;s designed to be inclusive and social, perfect for anglers of all ages.', '2025-09-09', '22:47:00', 'Pine Island, Zambales', 'General', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('14', 'Screenshot_2025-09-07_225612.png', '1st Subic Bay Shore Fishing Tournament', 'The inaugural shore-fishing competition in Subic Bay, spotlighting responsible angling and marine conservation. Organized by Fish’n Town with the support of the Subic Bay Metropolitan Authority and local sponsors, it blends sport with sustainable tourism and community engagement.', '2026-07-24', '14:55:00', 'San Bernardino Fishing Site, Subic Bay Freeport Zone, Zambales', 'General', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('16', '1757571370_29d28442-8efd-4d61-8164-45cfd342a2a7.jpg', 'Red Sea International Sport Fishing Tournament', 'ophelia', '2025-09-17', '01:19:00', 'Castillejos Zambales', 'General', '1');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('17', '1757586688_0e320bcc-941d-4276-a8b0-c89a1408b719.jpg', 'Red Sea International Sport Fishing Tournament', 'ako po geloy m caloy', '2027-04-16', '01:26:00', 'Baloy olongapo city', 'General', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('26', '1760551305_Screenshot_2025-09-24_162407.png', 'Colin Lee', 'Culpa molestiae ipsa', '2024-07-25', '10:17:00', 'Voluptate tenetur qu', 'Livelihood', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('27', '', 'Test Event', 'Description here', '2026-01-18', '10:00:00', 'Beach', 'General', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('28', '1768671680_Screenshot_2025-10-18_225224.png', '1st Subic Bay Shore Fishing Tournament', 'birthday ni admin', '2026-01-31', '01:44:00', 'Bahay', 'Cleanup', '1');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('29', '1769069244_Screenshot_2025-06-10_081141.png', 'josedwsd', 'fefe', '2026-01-05', '16:10:00', 'wddfw', 'Festival', '1');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('30', '1769401632_Screenshot_2026-01-26_122601.png', 'Elvis Mclaughlin', 'Tempora quis sunt n', '2016-02-08', '15:58:00', 'Philadelphia', 'Training', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('31', '', '1st Subic Bay Shore Fishing DFD', 'fgd', '2026-01-29', '19:50:00', 'fsd', 'Festival', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('32', '1769401740_Screenshot_2026-01-26_122504.png', '1SDFSDAnament', 'refer', '2026-01-27', '20:13:00', 'ererre', 'Festival', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('33', '1769398941_knscsd2526-a11baf2f-4450-4b71-8f7c-a3d1776be7cd.jpg', 'Ocean Santiago', 'Qui voluptas molliti', '1999-05-21', '14:37:00', 'Dallas', 'Training', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('34', '', '1SDFSDAnament', 'fdyfg', '2026-01-06', '08:00:00', 'San Bernardino Fishing Site, Subic Bay Freeport Zone, Zambales', 'Festival', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('35', '', '1st Subic Bay Shore Fishing Tournament', 'dsdd', '2026-01-02', '08:00:00', 'dsdsd', 'Festival', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('36', '1769401663_testFile.png', 'Casey Gilmore', 'Excepteur cupiditate', '1985-09-20', '16:39:00', 'Tucson', 'General', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('37', '1769407825_testFile.png', 'Maia Galloway', 'Sint non expedita co', '1979-09-30', '21:29:00', 'Oklahoma City', 'Training', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('38', '1769582474_Screenshot_2026-01-26_122601.png', 'Annual Fishing Association Gathering and Community Outreach', 'The [Name of Fishing Association] is proud to announce its much-anticipated Annual Fishing Association Gathering, an event that brings together local anglers, community members, and environmental enthusiasts for a day of learning, networking, and celebration of our rich fishing culture. This year’s event promises to be bigger and better, emphasizing not only the sport and livelihood of fishing but also the sustainable practices that ensure our waters remain bountiful for generations to come.\r\n\r\nAttendees will have the unique opportunity to participate in a variety of activities designed to cater to both seasoned fishermen and beginners alike. The day will begin with an opening ceremony highlighting the achievements of association members over the past year, including awards for outstanding contributions to the community and excellence in sustainable fishing practices. Following the ceremony, interactive workshops will be held, covering topics such as modern fishing techniques, proper handling of aquatic species, safety measures, and environmental conservation. Experienced anglers will share their knowledge on equipment maintenance, bait selection, and effective fishing strategies, ensuring that participants gain practical skills they can apply in the field.', '2026-02-22', '21:36:00', 'New York', 'Livelihood', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('39', '1769582457_Screenshot_2026-01-26_122453.png', 'Rashad Branch', 'Ut esse placeat po', '2026-03-05', '10:15:00', 'Charlotte', 'Festival', '0');
INSERT INTO `events` (`id`, `event_poster`, `event_name`, `description`, `date`, `time`, `location`, `category`, `is_archived`) VALUES ('40', '', 'Shelley Lambert', 'Eius rerum eum dolor', '2005-07-04', '01:17:00', 'Provident non aliqu', 'Training', '0');


-- Table: events_archive
DROP TABLE IF EXISTS `events_archive`;

CREATE TABLE `events_archive` (
  `id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT 'General',
  `date` date NOT NULL,
  `time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text,
  `event_poster` varchar(255) DEFAULT 'default.jpg',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- Data for table `featured_programs`
INSERT INTO `featured_programs` (`id`, `title`, `description`, `icon_class`, `button_label`, `button_link`, `sort_order`, `created_at`) VALUES ('1', 'Coastal Clean-up Drives', 'Regular community-led initiatives to protect our marine environment, preserve coastal ecosystems, and maintain clean beaches for future generations.', 'bi-water', 'View Events', 'Button Link (URL): events.php?category=cleanup', '1', '2026-02-15 17:32:05');
INSERT INTO `featured_programs` (`id`, `title`, `description`, `icon_class`, `button_label`, `button_link`, `sort_order`, `created_at`) VALUES ('2', 'Fishermen Livelihood Support', 'Providing financial assistance, equipment support, and sustainable fishing resources to help local fishermen improve their income and quality of life.', 'bi-briefcase', 'View Events', 'events.php?category=livelihood', '2', '2026-02-15 17:32:49');
INSERT INTO `featured_programs` (`id`, `title`, `description`, `icon_class`, `button_label`, `button_link`, `sort_order`, `created_at`) VALUES ('3', 'Safety & Maritime Training', 'Comprehensive training programs covering sea safety, first aid, navigation, and emergency protocols to ensure the well-being of all fishermen.', 'bi-shield-check', 'View Events', 'events.php?category=training', '3', '2026-02-15 17:33:16');
INSERT INTO `featured_programs` (`id`, `title`, `description`, `icon_class`, `button_label`, `button_link`, `sort_order`, `created_at`) VALUES ('4', 'Environmental Protection', 'Advocacy and action programs focused on marine conservation, sustainable fishing practices, and educating the community about environmental responsibility.', 'bi-tree', 'View Events', 'events.php?category=environment', '4', '2026-02-15 17:34:14');


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
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `original_created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data for table `featured_programs_archive`

-- Table: galleries
DROP TABLE IF EXISTS `galleries`;

CREATE TABLE `galleries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT 'Uncategorized',
  `images` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;

-- Data for table `galleries`
INSERT INTO `galleries` (`id`, `title`, `category`, `images`, `created_at`) VALUES ('9', 'Meeting with Congressman Jay Khonghun', 'Meetings', '1764505526_7948644f2b70.jpg', '2025-11-28 20:25:26');
INSERT INTO `galleries` (`id`, `title`, `category`, `images`, `created_at`) VALUES ('10', 'From Shore to Sea: Turtle Release', 'Activities', '1764505663_d204b6b97742.jpg,1764505663_8925b8b97bc7.jpg,1764505663_044e4a1da3d6.jpg,1764505663_4f5cf884a967.jpg,1764505663_1df2bdac84c2.jpg,1764505663_278e7b4c86c3.jpg', '2025-11-28 20:27:43');
INSERT INTO `galleries` (`id`, `title`, `category`, `images`, `created_at`) VALUES ('11', 'Dole Integrated Livelihood Program', 'Awards', '1769582283_a901f558671f.jfif,1769582283_93a835c05dbf.jfif,1769582283_19c8db94c93b.jfif,1769582283_0b2645f2c412.jfif,1769582283_3b568394debd.jfif,1769582283_e61b5a7bce34.jfif,1769582283_1087cd88e8a7.jfif,1769582283_62632cece70b.jfif', '2026-01-26 14:39:37');
INSERT INTO `galleries` (`id`, `title`, `category`, `images`, `created_at`) VALUES ('12', 'DOLE INTEGRATED LIVELIHOOD PROGRAM AWARDING', 'Awards', '1770997718_b3975cd5e08c.jfif,1770997718_6a8ca065561d.jfif,1770997718_2f83c2f7170c.jfif,1770997718_b2a8ebeee842.jfif,1770997718_9fb46ac2919f.jfif,1770997718_21974482059b.jfif,1770997718_9bf37cdc3508.jfif,1770997718_c1d676672e72.jfif,1770997718_c63173b6db5b.jfif', '2026-02-12 15:50:25');
INSERT INTO `galleries` (`id`, `title`, `category`, `images`, `created_at`) VALUES ('13', 'Ut sed est corrupti', 'Meetings', '1771010750_af5f4ed1d0fd.jfif', '2026-02-12 19:27:37');


-- Table: galleries_archive
DROP TABLE IF EXISTS `galleries_archive`;

CREATE TABLE `galleries_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `gallery_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT 'Uncategorized',
  `images` text NOT NULL,
  `original_created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Data for table `galleries_archive`
INSERT INTO `galleries_archive` (`archive_id`, `gallery_id`, `title`, `category`, `images`, `original_created_at`, `archived_at`) VALUES ('1', '14', 'Ut sed est corrupti', 'Meetings', '1771011807_f5a69b413e7d.jfif', '2026-02-13 11:45:14', '2026-02-12 19:45:24');


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
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- Data for table `home_carousel_slides`
INSERT INTO `home_carousel_slides` (`id`, `title`, `subtitle`, `image_path`, `primary_button_label`, `primary_button_link`, `secondary_button_label`, `secondary_button_link`, `sort_order`, `created_at`) VALUES ('1', 'Strengthening Our Fishing Communities', 'We empower small-scale fishers through livelihood support, training, and community-led programs across our coastal barangays.', 'uploads/carousel/1771270044_slides2.jpg', 'Learn More', 'about_us.php', 'Join Us', 'contact_us.php', '1', '2026-02-15 19:25:35');
INSERT INTO `home_carousel_slides` (`id`, `title`, `subtitle`, `image_path`, `primary_button_label`, `primary_button_link`, `secondary_button_label`, `secondary_button_link`, `sort_order`, `created_at`) VALUES ('2', 'Sustainable and Responsible Fishing', 'Together with our partners, we promote responsible fishing practices to protect our seas and secure future livelihoods.', 'uploads/carousel/1771269853_bg1.jpg', 'Learn More', 'about_us.php', 'Join Us', 'contact_us.php', '2', '2026-02-15 19:26:03');
INSERT INTO `home_carousel_slides` (`id`, `title`, `subtitle`, `image_path`, `primary_button_label`, `primary_button_link`, `secondary_button_label`, `secondary_button_link`, `sort_order`, `created_at`) VALUES ('3', 'Partners in Community Development', 'We work with government, NGOs, and private organizations to bring support and opportunities closer to our fishing communities.', 'uploads/carousel/1771270009_slide3.jpg', 'Learn More', 'about_us.php', 'Join Us', 'contact_us.php', '3', '2026-02-15 19:26:31');


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
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `original_created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data for table `home_carousel_slides_archive`

-- Table: member_archive
DROP TABLE IF EXISTS `member_archive`;

CREATE TABLE `member_archive` (
  `member_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data for table `member_archive`
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`) VALUES ('43', 'Jovelyn  S.', 'jovelybuena2@gmail.com', '09100176413', '2025-10-13 23:40:45');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`) VALUES ('64', 'Jovelyn S. Buena', '9898jknjk@gmail.com', '098765434567', '2026-01-24 21:17:00');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`) VALUES ('76', 'dfgdg dfdgdf dfdfd', 'hgfdsfgvbn@gmail.com', '0987654', '2025-10-02 21:19:54');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`) VALUES ('77', 'Cristopher M. De Jesus', 'dejesus@gmail.com', '098765434567', '2026-02-12 18:17:06');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`) VALUES ('78', 'mew S meow', 'dkvodsfwefjwscd@gmail.com', '09876543', '2026-02-12 18:10:19');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`) VALUES ('81', 'ghfd gfh fgdfg', 'ytuuyfgg@gmail.com', '0987654', '2025-10-13 23:38:41');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`) VALUES ('82', 'meew s dsdfdf', 'fgfgfgg@gmail.com', '90876543', '2025-10-13 23:33:37');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`) VALUES ('83', 'Irma Id consequat Et exe Young', 'zyqido@mailinator.com', '+1 (197) 621-40', '2025-10-13 23:33:33');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`) VALUES ('84', 'Tad Placeat quia qui sa Ingram', 'zajav@mailinator.com', '+1 (201) 356-43', '2026-01-24 09:50:12');
INSERT INTO `member_archive` (`member_id`, `name`, `email`, `phone`, `archived_at`) VALUES ('87', 'Kirsten E Vaughn', 'sihyle@example.com', '9999422326', '2026-02-12 19:26:43');


-- Table: members
DROP TABLE IF EXISTS `members`;

CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `membership_status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dob` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `work_type` enum('Fisherman','Bangkero','Both') DEFAULT NULL,
  `license_number` varchar(50) NOT NULL,
  `boat_name` varchar(255) DEFAULT NULL,
  `fishing_area` varchar(255) DEFAULT NULL,
  `emergency_name` varchar(255) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `agreement` tinyint(1) DEFAULT '0',
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4;

-- Data for table `members`
INSERT INTO `members` (`id`, `name`, `email`, `phone`, `address`, `membership_status`, `created_at`, `dob`, `gender`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('62', 'Jose M. Manalo', 'joseantonio@gmail.com', '098866554433', 'Calapacuan, Subic Zambales', 'active', '2025-09-09 14:02:06', '2025-09-09', 'Male', 'Fisherman', 'sdfdfdf', 'sdfdfd', 'sdfdf', 'sdff', 'sdffds', '1', 'member_68c267a475cf20.82725123_thelightinthisisinsanity_photography.jpg');
INSERT INTO `members` (`id`, `name`, `email`, `phone`, `address`, `membership_status`, `created_at`, `dob`, `gender`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('67', 'Argie  B. Berena', 'argieberena@gmail.com', '098786765777', 'Bulacan', 'active', '2025-09-17 12:17:39', '2025-09-16', 'Male', 'Fisherman', '9809', 'argie', 'bulacan', 'dkjfdfsii', '098789', '1', 'member_68ccd963555564.58457445_Screenshot2025-03-07135115.png');
INSERT INTO `members` (`id`, `name`, `email`, `phone`, `address`, `membership_status`, `created_at`, `dob`, `gender`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('80', 'Ignatius q Pittman', 'fifuz@mailinator.com', '+1 (641) 841-86', 'Fuga Illum ea alia', 'active', '2025-10-10 12:36:43', '1974-12-16', 'Female', 'Bangkero', '770', 'Adena Cox', 'Corrupti sint quo r', 'Bree Curtis', '+1 (481) 765-3659', '1', 'member_68efc09ac12232.36903530_anime-girl-blue-eyes-white-hair-4k-wallpaper-uhdpaper.com-3025d.jpg');
INSERT INTO `members` (`id`, `name`, `email`, `phone`, `address`, `membership_status`, `created_at`, `dob`, `gender`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('82', 'Zoe T Delacruz', 'byvupov@example.com', '0962549895', '40 Oak Parkway', 'active', '2026-02-09 22:38:50', '2005-07-13', 'Other', 'Both', '482', 'Tad Church', 'Neque commodo dolore', 'Basia Mcfarland', '+1 (418) 576-5538', '1', 'member_698c94114f2f16.27846167_fc2400be-9d89-4c78-a9d3-225e0429c6f7.jfif');
INSERT INTO `members` (`id`, `name`, `email`, `phone`, `address`, `membership_status`, `created_at`, `dob`, `gender`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('83', 'Bart  Javillonar', 'bartjavillonar@gmail.com', '09304871699', 'Calapacuan Subic Zambales', 'active', '2026-02-11 02:12:39', '1979-02-02', 'Male', 'Both', '12345678', 'Bart', '', '', '09304871699', '1', 'member_698d36ad1cf256.72974108_3748da6d-7b9a-4046-b8ce-8b4950b0863e11.png');
INSERT INTO `members` (`id`, `name`, `email`, `phone`, `address`, `membership_status`, `created_at`, `dob`, `gender`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('84', 'Michael P Madden', 'neqoroto@example.com', '09620433464', '131 First Court', 'active', '2026-02-12 18:13:01', '2004-11-14', 'Female', 'Both', '684', 'Amery Owen', 'Unde anim eum sint e', 'Theodore Mcfadden', '+1 (292) 609-7475', '1', 'member_698f6942500e93.49704418_testFile.png');
INSERT INTO `members` (`id`, `name`, `email`, `phone`, `address`, `membership_status`, `created_at`, `dob`, `gender`, `work_type`, `license_number`, `boat_name`, `fishing_area`, `emergency_name`, `emergency_phone`, `agreement`, `image`) VALUES ('85', 'Ishmael X Stevens', 'fuqohymyka@example.com', '09620356555', '930 East Rocky Milton Freeway', 'active', '2026-02-12 18:13:24', '1975-01-27', 'Other', 'Fisherman', '209', 'Hollee Gray', 'Optio atque corpori', 'Cyrus Rosa', '+1 (788) 159-6648', '1', 'member_698f69592d09d0.05922985_testFile.png');


-- Table: mission_vision
DROP TABLE IF EXISTS `mission_vision`;

CREATE TABLE `mission_vision` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mission` longtext NOT NULL,
  `vision` longtext NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Data for table `mission_vision`
INSERT INTO `mission_vision` (`id`, `mission`, `vision`, `updated_at`) VALUES ('1', 'To empower local fishermen and boatmen through collaboration, sustainable practices, training programs, and strong leadership, ensuring the welfare and continuous development of our members and their families.', 'To be the leading fishermen association in the region, recognized for fostering unity, promoting sustainable fishing practices, and creating lasting opportunities for growth and prosperity in our community.', '2026-02-15 18:22:04');


-- Table: officer_roles
DROP TABLE IF EXISTS `officer_roles`;

CREATE TABLE `officer_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- Data for table `officer_roles`
INSERT INTO `officer_roles` (`id`, `role_name`, `description`, `created_at`) VALUES ('1', 'President', NULL, '0000-00-00 00:00:00');
INSERT INTO `officer_roles` (`id`, `role_name`, `description`, `created_at`) VALUES ('2', 'Vice President', NULL, '0000-00-00 00:00:00');
INSERT INTO `officer_roles` (`id`, `role_name`, `description`, `created_at`) VALUES ('3', 'Secretary', NULL, '0000-00-00 00:00:00');
INSERT INTO `officer_roles` (`id`, `role_name`, `description`, `created_at`) VALUES ('4', 'Treasurer', '', '0000-00-00 00:00:00');
INSERT INTO `officer_roles` (`id`, `role_name`, `description`, `created_at`) VALUES ('5', 'Board of Director', NULL, '0000-00-00 00:00:00');


-- Table: officer_roles_archive
DROP TABLE IF EXISTS `officer_roles_archive`;

CREATE TABLE `officer_roles_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `role_name` varchar(255) DEFAULT NULL,
  `description` text,
  `created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data for table `officer_roles_archive`

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
  `description` text,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `fk_role` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4;

-- Data for table `officers`
INSERT INTO `officers` (`id`, `member_id`, `position`, `term_start`, `term_end`, `image`, `role_id`, `description`) VALUES ('36', '67', 'Secretary', '2025-09-08', '2025-09-24', '1758256680_Screenshot 2024-07-27 113641.png', NULL, NULL);
INSERT INTO `officers` (`id`, `member_id`, `position`, `term_start`, `term_end`, `image`, `role_id`, `description`) VALUES ('39', '62', 'President', '2025-09-18', '2025-09-10', '1758257402_Screenshot 2024-04-15 220726.png', NULL, NULL);
INSERT INTO `officers` (`id`, `member_id`, `position`, `term_start`, `term_end`, `image`, `role_id`, `description`) VALUES ('41', '62', 'President', '2025-09-16', '2025-09-23', '1758258737_background.png', NULL, 'fgfgfg');
INSERT INTO `officers` (`id`, `member_id`, `position`, `term_start`, `term_end`, `image`, `role_id`, `description`) VALUES ('49', '67', '', '2025-10-14', '2029-05-14', '1764511607_Screenshot 2025-11-30 220633.png', '1', 's');
INSERT INTO `officers` (`id`, `member_id`, `position`, `term_start`, `term_end`, `image`, `role_id`, `description`) VALUES ('51', '64', '', '2025-09-11', '2025-10-22', '1760281419_Screenshot 2025-10-12 121319.png', '4', 'super pretty');
INSERT INTO `officers` (`id`, `member_id`, `position`, `term_start`, `term_end`, `image`, `role_id`, `description`) VALUES ('55', '62', '', '2024-12-08', '2026-02-27', '1764511540_Screenshot 2025-04-23 150253.png', '2', 'J. Jose serves as a dedicated and visionary Vice president, bringing over 15 years of leadership experience in strategic planning, organizational development, and community engagement. ');
INSERT INTO `officers` (`id`, `member_id`, `position`, `term_start`, `term_end`, `image`, `role_id`, `description`) VALUES ('56', '80', '', '2025-10-29', '2025-12-05', '1764514621_background.png', '3', 'sd');
INSERT INTO `officers` (`id`, `member_id`, `position`, `term_start`, `term_end`, `image`, `role_id`, `description`) VALUES ('57', '77', '', '2025-09-28', '2026-01-15', '1759659719_Screenshot 2025-04-23 125832.png', '4', NULL);


-- Table: officers_archive
DROP TABLE IF EXISTS `officers_archive`;

CREATE TABLE `officers_archive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `term_start` date DEFAULT NULL,
  `term_end` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;

-- Data for table `officers_archive`
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('2', '67', '3', '2025-09-23', '2025-09-10', '1758266934_background.png', '2025-10-02 21:43:55');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('3', '62', '1', '2023-06-12', '2025-11-19', '1757571148_Screenshot 2025-09-08 003235.png', '2025-10-10 23:01:38');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('4', '79', '1', '2025-10-15', '2025-10-21', NULL, '2025-10-10 23:01:41');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('5', '67', '3', '2025-10-08', '2030-06-05', '1759659520_Screenshot 2025-09-08 003107.png', '2025-10-10 23:01:43');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('7', '79', '2', '2025-10-08', '2025-12-11', '1760280765_background.png', '2025-10-10 23:01:47');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('8', '64', '2', '2025-09-08', '2025-10-11', '1757586797_Screenshot 2025-09-08 003004.png', '2025-10-10 23:01:50');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('9', '43', '3', '2025-10-23', '2025-10-23', '1760282388_Screenshot 2024-04-05 214617.png', '2025-10-10 23:20:05');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('10', '43', '3', '2025-10-18', '2025-10-14', '1760281938_Screenshot 2024-04-20 212354.png', '2025-10-10 23:20:08');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('11', '79', '5', '2020-02-22', '1984-10-23', '', '2025-10-13 23:40:33');
INSERT INTO `officers_archive` (`id`, `member_id`, `role_id`, `term_start`, `term_end`, `image`, `archived_at`) VALUES ('12', '67', '1', '2025-11-06', '2029-06-11', '1760281376_Screenshot 2025-10-12 121319.png', '2025-11-28 22:05:51');


-- Table: partners_sponsors
DROP TABLE IF EXISTS `partners_sponsors`;

CREATE TABLE `partners_sponsors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `logo_path` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'partner',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- Data for table `partners_sponsors`
INSERT INTO `partners_sponsors` (`id`, `name`, `logo_path`, `type`, `sort_order`, `created_at`) VALUES ('1', 'Municipality of Olongapo City', 'uploads/partners/1771268607_olongapo.png', 'partner', '1', '2026-02-15 19:05:17');
INSERT INTO `partners_sponsors` (`id`, `name`, `logo_path`, `type`, `sort_order`, `created_at`) VALUES ('2', 'Bureau of Fisheries & Aquatic Resources', 'uploads/partners/1771268633_bfar.png', 'partner', '2', '2026-02-15 19:05:43');
INSERT INTO `partners_sponsors` (`id`, `name`, `logo_path`, `type`, `sort_order`, `created_at`) VALUES ('3', 'Olongapo City Agriculture Department', 'uploads/partners/1771268658_agriculture.png', 'sponsor', '3', '2026-02-15 19:06:08');
INSERT INTO `partners_sponsors` (`id`, `name`, `logo_path`, `type`, `sort_order`, `created_at`) VALUES ('4', 'USAID', 'uploads/partners/1771268688_usaid.png', 'sponsor', '4', '2026-02-15 19:06:38');


-- Table: partners_sponsors_archive
DROP TABLE IF EXISTS `partners_sponsors_archive`;

CREATE TABLE `partners_sponsors_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `logo_path` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'partner',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `original_created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `auto_backup_status` tinyint(1) NOT NULL DEFAULT '0',
  `backup_storage_limit_mb` int(11) NOT NULL DEFAULT '100',
  `auto_backup_next_run` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data for table `system_config`
INSERT INTO `system_config` (`id`, `assoc_name`, `assoc_email`, `assoc_phone`, `assoc_address`, `assoc_logo`, `auto_backup_status`, `backup_storage_limit_mb`, `auto_backup_next_run`) VALUES ('1', 'Bankero and Fishermen Association ', 'info@association.org', '9620433464', 'Barreto Street, Olongapo City', 'assoc_logo.png', '1', '100', NULL);


-- Table: users
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `username` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=321227 DEFAULT CHARSET=utf8mb4;

-- Data for table `users`
INSERT INTO `users` (`username`, `password_hash`, `id`, `role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('argie2', '$2y$10$PXmeV0TETc4CasIO.PUGYe4s18MgWUyQOcwmCYDtimhT.By3nXXhC', '321211', 'admin', 'approved', '2025-10-02 19:58:05', 'argie2@gmail.com', '096204334624', 'Male', 'bulacan, bulacan ', '1760856293_cybernetic-cool-anime-cyborg-girl-9y-1920x1080.jpg', 'argie', 'buena', NULL, '0');
INSERT INTO `users` (`username`, `password_hash`, `id`, `role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('jesus', '$2y$10$PhnUYt.9NRtCq6DRfHIo/.guZDBPbcZEKYSVcmZyosBX7A1TeCJIW', '321212', 'member', 'pending', '2025-10-03 12:21:05', 'dejesus@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, '77', '0');
INSERT INTO `users` (`username`, `password_hash`, `id`, `role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('josemarichan', '$2y$10$eV5aMXWy16uOeZk1wT20x.dooy/pMTUZn0FjBZCI.yxJHpp2HbjQe', '321214', 'officer', 'rejected', '2025-10-03 19:53:29', 'josemarichan@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');
INSERT INTO `users` (`username`, `password_hash`, `id`, `role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('avina', '$2y$10$ys1a63YOm1/oqj6QvsJe/eRpdP.oLLqMYZKHbXn/kCYZcOavQy/ku', '321215', 'officer', 'pending', '2025-10-13 17:40:17', 'avina@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');
INSERT INTO `users` (`username`, `password_hash`, `id`, `role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('Alexa', '$2y$10$02SlnXsbCGRHo.Ylr6RGaeqyy4iO.JoL6iYFtzgRgLBbFgfJAWf.q', '321216', 'admin', 'approved', '2025-10-13 17:42:16', 'alexa@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1');
INSERT INTO `users` (`username`, `password_hash`, `id`, `role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('admin', '$2y$10$T1cQRA8Y2SqmVCfXM08dF.u.DFPWWu75Cbu0tF5Q86n1mtCQOQA4O', '321219', 'admin', 'approved', '2025-10-17 15:31:04', 'admin@gmail.com', '09876543456', 'Female', 'Sitio Bukid, Calapacuan Subic Zambales', '1760859106_photo_2024-08-13_09-05-00.jpg', 'Jovelyn', 'San Jose', NULL, '0');
INSERT INTO `users` (`username`, `password_hash`, `id`, `role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('klare', '$2y$10$d50LR9qy9u52qRHlRKjakO90anP8rG01PunxbukTPCjv9i1cTUvO2', '321220', 'admin', 'approved', '2025-10-17 16:41:30', 'klare@gmail.com', '09620433464', 'Female', 'Calapacuan', '1770819859_knscsd2526-a11baf2f-4450-4b71-8f7c-a3d1776be7cd.jpg', 'Klare desteen', 'Montefalco', NULL, '1');
INSERT INTO `users` (`username`, `password_hash`, `id`, `role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('John', '$2y$10$hTbYydbNq/9embkicVkbHO/8uA3g.MsCL3CdgpL24mzzQ2fTxp2fi', '321221', 'admin', 'approved', '2025-11-29 15:11:15', 'johncarlmangino2@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1');
INSERT INTO `users` (`username`, `password_hash`, `id`, `role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('kim', '$2y$10$Z.kcTA4TWDbusbBPru7Vxu2Rsrh4ipEK4RuQrTiU7lf50wHI88ija', '321222', 'officer', 'approved', '2025-11-29 15:11:48', 'kim@gmail.com', '095678434', 'Female', 'San Marcelino', '1764573767_Screenshot 2025-02-28 124358.png', 'Kimberly', 'Mangino', NULL, '0');
INSERT INTO `users` (`username`, `password_hash`, `id`, `role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('officer', '$2y$10$9hXspEoAQUp9qwIqx7FWyuvx1/ROmn.vzyhEQlDoDRL9vryN8DoBy', '321223', 'officer', 'approved', '2026-01-16 17:55:30', 'officers@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');
INSERT INTO `users` (`username`, `password_hash`, `id`, `role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('katkat', '$2y$10$K60WieyP2Z6vtmw63DAwF..azLkSgKt2G9bRSCF/R4c4.OT.vrXb.', '321224', 'officer', 'pending', '2026-02-08 11:00:23', 'altheakaliego@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');
INSERT INTO `users` (`username`, `password_hash`, `id`, `role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('johncarl', '$2y$10$GA3I.H2dAHoL0cYc2S9afu49I00E09ZH9gi7957Iaw/NhhGqb6a/i', '321225', 'admin', 'approved', '2026-02-11 17:06:37', 'johncarlmangino7@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1');
INSERT INTO `users` (`username`, `password_hash`, `id`, `role`, `status`, `created_at`, `email`, `mobile`, `gender`, `address`, `avatar`, `first_name`, `last_name`, `member_id`, `is_admin`) VALUES ('carl', '$2y$10$lRf/vTLIcxFRiO6xFIM6gOu8ZbQYASgk6xOxiLQuSLX8dM2yzQ2oG', '321226', 'officer', 'approved', '2026-02-11 17:10:51', 'johncarlmangino17@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');


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
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Data for table `users_archive`

-- Table: who_we_are
DROP TABLE IF EXISTS `who_we_are`;

CREATE TABLE `who_we_are` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- Data for table `who_we_are`
INSERT INTO `who_we_are` (`id`, `title`, `content`, `image`, `created_at`, `updated_at`) VALUES ('2', 'Who we are', 'The Bankero & Fishermen Association was founded in November 2009 in Barretto, Olongapo City under the leadership of Mr. Noliboy Cocjin. Starting with around 300–400 members, the association has since grown and organized its members into smaller groups for more effective management.\r\n\r\nDedicated to supporting local boatmen and fishermen, the association serves as a vital link for their welfare and development. To strengthen communication and organizational efficiency, the association is now adopting the Bankero & Fishermen Association Management System, which will automate membership records, announcements, and event scheduling, while introducing SMS notifications for timely updates.\r\n\r\nThrough this modernization, the association continues its mission of empowering members, enhancing participation, and preserving the livelihood of the fishing community.', NULL, '2026-02-15 17:07:51', NULL);


-- Table: who_we_are_archive
DROP TABLE IF EXISTS `who_we_are_archive`;

CREATE TABLE `who_we_are_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `original_created_at` datetime DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data for table `who_we_are_archive`
