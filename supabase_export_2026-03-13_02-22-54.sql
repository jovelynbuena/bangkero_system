-- Supabase PostgreSQL Export
-- Generated: 2026-03-13 02:22:54

-- Enable required extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Table: activity_logs
DROP TABLE IF EXISTS "activity_logs" CASCADE;
CREATE TABLE "activity_logs" (
    "id" SERIAL PRIMARY KEY,
    "user_id" INTEGER DEFAULT NULL,
    "action" VARCHAR(255) NOT NULL NOT NULL,
    "description" TEXT,
    "ip_address" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
)

-- Insert data for activity_logs
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('1', '321192', 'Logged in', NULL, '::1', '2025-10-01 08:06:50');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('2', '321192', 'Logged in', NULL, '::1', '2025-10-01 08:11:47');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('3', '321192', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-10-01 08:30:55');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('4', '321192', 'Logged in', NULL, '::1', '2025-10-01 08:31:00');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('5', '321192', 'Restored member', 'Restored member: dfgdg dfdgdf dfdfd', '::1', '2025-10-01 08:38:16');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('6', '321192', 'Restored member: dfgdg dfdgdf dfdfd', 'Restored member: dfgdg dfdgdf dfdfd', NULL, '2025-10-01 08:45:41');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('7', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-01 08:55:13');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('8', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-01 08:57:49');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('9', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-01 08:58:02');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('10', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-01 09:02:57');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('11', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-01 09:06:48');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('12', '321192', 'Visited Announcements Page', 'User visited the admin announcements page.', '::1', '2025-10-01 09:06:58');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('13', '321192', 'Added announcement', 'Title: fgdfg', NULL, '2025-10-01 09:16:43');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('14', '321192', 'Added announcement', 'Title: erd', NULL, '2025-10-01 09:21:42');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('15', '321192', 'Added announcement', 'Title: tert', NULL, '2025-10-01 09:25:17');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('16', '321192', 'Updated announcement', 'Title: tert', NULL, '2025-10-01 09:25:26');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('17', '321192', 'Updated announcement', 'Title: tert', NULL, '2025-10-01 09:26:06');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('18', '321192', 'Updated event', 'Event: Red Sea International Sport Fishing Tournament', NULL, '2025-10-01 09:32:06');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('19', '321192', 'Added event', 'Event: Gone Fishing 2025!', NULL, '2025-10-01 09:34:31');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('20', '321192', 'Logged in', NULL, '::1', '2025-10-03 04:00:14');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('21', '321192', 'Logged in', NULL, '::1', '2025-10-03 04:23:48');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('22', '321211', 'Failed login attempt (not approved)', 'Attempted username: argie2', '::1', '2025-10-03 04:26:52');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('23', '321211', 'Failed login attempt (not approved)', 'Attempted username: argie2', '::1', '2025-10-03 04:27:20');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('24', '321211', 'Logged in', NULL, '::1', '2025-10-03 04:29:59');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('25', '321211', 'Logged in', NULL, '::1', '2025-10-03 04:42:25');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('26', '321192', 'Logged in', NULL, '::1', '2025-10-03 04:55:27');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('27', '321192', 'Restored member: dfgdg dfdgdf dfdfd', 'Restored member: dfgdg dfdgdf dfdfd', NULL, '2025-10-03 05:00:56');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('28', '321192', 'Archived officer ID: 35', 'Archived officer ID: 35', NULL, '2025-10-03 05:36:07');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('29', '321192', 'Restored officer: Jovelyn S Buena', 'Restored officer: Jovelyn S Buena', NULL, '2025-10-03 05:43:51');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('30', '321192', 'Archived officer ID: 43', 'Archived officer ID: 43', NULL, '2025-10-03 05:43:55');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('31', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-03 05:56:40');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('32', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-03 05:57:09');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('33', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-03 06:00:03');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('34', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-03 06:00:15');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('35', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-03 06:00:56');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('36', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-03 06:02:19');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('37', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-03 06:02:43');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('38', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-03 06:03:23');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('39', '321192', 'Restored event: ', 'Restored event: ', NULL, '2025-10-03 06:04:19');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('40', '321192', 'Logged in', NULL, '::1', '2025-10-03 08:58:04');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('41', '321211', 'Logged in', NULL, '::1', '2025-10-03 09:16:01');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('42', '321192', 'Logged in', NULL, '::1', '2025-10-03 09:16:16');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('43', '321192', 'Logged in', NULL, '::1', '2025-10-04 03:46:31');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('44', '321192', 'Logged in', NULL, '::1', '2025-10-09 00:09:40');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('45', '321192', 'Logged in', NULL, '::1', '2025-10-10 00:06:49');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('46', '321192', 'Added announcement', 'Title: sadasd', NULL, '2025-10-10 00:49:06');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('47', '321192', 'Edited announcement', 'Edited Title: sadasd', NULL, '2025-10-10 00:52:42');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('48', '321192', 'Added announcement', 'Title: sdfsdf', NULL, '2025-10-10 00:53:17');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('49', '321192', 'Added announcement', 'Title: sdfsdf', NULL, '2025-10-10 00:56:03');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('50', '321192', 'Edited announcement', 'Edited Title: sadasd', NULL, '2025-10-10 01:13:57');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('51', '321192', 'Edited announcement', 'Edited Title: sadasd', NULL, '2025-10-10 01:19:45');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('52', '321192', 'Edited announcement', 'Edited Title: sadasd', NULL, '2025-10-10 01:20:47');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('53', '321192', 'Edited announcement', 'Edited Title: Community Fishing Day', NULL, '2025-10-10 02:49:13');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('54', '321192', 'Edited announcement', 'Edited Title: Clean-Up Drive', NULL, '2025-10-10 02:49:38');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('55', '321192', 'Logged in', NULL, '::1', '2025-10-10 19:28:22');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('56', '321192', 'Logged in', NULL, '::1', '2025-10-10 21:23:12');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('57', '321216', 'Failed login attempt (not approved)', 'Attempted username: alexa', '::1', '2025-10-14 01:43:08');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('58', '321218', 'Failed login attempt (not approved)', 'Attempted username: burn', '::1', '2025-10-14 01:46:53');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('59', NULL, 'Failed login attempt (user not found)', 'Attempted username: burnw', '::1', '2025-10-14 01:47:01');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('60', '321216', 'Failed login attempt (not approved)', 'Attempted username: alexa', '::1', '2025-10-14 06:58:43');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('61', '321192', 'Logged in', NULL, '::1', '2025-10-17 21:17:55');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('62', '321192', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-10-17 22:22:02');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('63', '321192', 'Logged in', NULL, '::1', '2025-10-17 22:22:07');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('64', '321192', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-10-17 22:33:10');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('65', '321192', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-10-17 22:33:16');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('66', '321192', 'Logged in', NULL, '::1', '2025-10-17 22:33:22');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('67', '321192', 'Logged in', NULL, '::1', '2025-10-17 22:34:50');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('68', '321211', 'Logged in', NULL, '::1', '2025-10-17 22:35:30');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('69', '321211', 'Failed login attempt (wrong password)', 'Attempted username: argie2', '::1', '2025-10-17 22:45:32');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('70', '321211', 'Logged in', NULL, '::1', '2025-10-17 22:45:43');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('71', '321192', 'Logged in', NULL, '::1', '2025-10-17 23:09:23');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('72', '321192', 'Logged in', NULL, '::1', '2025-10-17 23:27:26');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('73', '321197', 'Failed login attempt (wrong password)', 'Attempted username: jovelyn', '::1', '2025-10-17 23:28:40');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('74', '321197', 'Failed login attempt (wrong password)', 'Attempted username: jovelyn', '::1', '2025-10-17 23:28:50');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('75', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-17 23:28:53');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('76', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-17 23:29:03');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('77', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-17 23:29:08');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('78', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-17 23:29:14');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('79', NULL, 'Failed login attempt (user not found)', 'Attempted username: admin', '::1', '2025-10-17 23:29:35');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('80', '321219', 'Logged in', NULL, '::1', '2025-10-17 23:31:09');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('81', NULL, 'Failed login attempt (user not found)', 'Attempted username: paimon', '::1', '2025-10-17 23:33:06');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('82', '321219', 'Logged in', NULL, '::1', '2025-10-17 23:36:03');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('83', '321211', 'Failed login attempt (wrong password)', 'Attempted username: argie2', '::1', '2025-10-18 00:24:52');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('84', '321211', 'Logged in', NULL, '::1', '2025-10-18 00:25:03');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('85', '321211', 'Logged in', NULL, '::1', '2025-10-18 00:41:38');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('86', '321220', 'Logged in', NULL, '::1', '2025-10-18 00:42:26');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('87', '321211', 'Logged in', NULL, '::1', '2025-10-18 00:42:47');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('88', '321211', 'Logged in', NULL, '::1', '2025-10-18 01:09:41');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('89', '321211', 'Logged in', NULL, '::1', '2025-10-18 01:57:18');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('90', '321211', 'Logged in', NULL, '::1', '2025-10-18 02:03:39');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('91', '321215', 'Failed login attempt (not approved)', 'Attempted username: avina', '::1', '2025-10-18 02:19:45');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('92', '321216', 'Logged in', NULL, '::1', '2025-10-18 02:19:51');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('93', '321220', 'Logged in', NULL, '::1', '2025-10-18 02:37:42');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('94', '321220', 'Logged in', NULL, '::1', '2025-10-18 02:41:22');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('95', '321219', 'Logged in', NULL, '::1', '2025-10-18 02:43:32');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('96', '321220', 'Logged in', NULL, '::1', '2025-10-18 02:45:02');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('97', '321220', 'Logged in', NULL, '::1', '2025-10-18 02:48:42');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('98', '321219', 'Logged in', NULL, '::1', '2025-10-18 02:49:14');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('99', '321219', 'Logged in', NULL, '::1', '2025-10-18 02:49:29');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('100', '321219', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-11-29 02:36:10');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('101', '321219', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2025-11-29 02:36:19');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('102', '321219', 'Logged in', NULL, '::1', '2025-11-29 02:36:27');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('103', '321219', 'Logged in', NULL, '::1', '2025-11-29 03:17:46');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('104', '321219', 'Logged in', NULL, '::1', '2025-11-29 05:16:28');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('105', '321220', 'Logged in', NULL, '::1', '2025-11-29 06:09:37');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('106', '321219', 'Logged in', NULL, '::1', '2025-11-29 06:15:40');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('107', '321219', 'Logged in', NULL, '::1', '2025-11-29 06:33:00');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('108', '321219', 'Logged in', NULL, '::1', '2025-11-29 06:33:07');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('109', '321220', 'Logged in', NULL, '::1', '2025-11-29 07:09:02');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('110', '321219', 'Logged in', NULL, '::1', '2025-11-29 23:03:26');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('111', '321219', 'Logged in', NULL, '::1', '2025-11-29 23:11:52');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('112', '321222', 'Logged in', NULL, '::1', '2025-11-29 23:20:41');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('113', '321219', 'Logged in', NULL, '::1', '2025-11-29 23:40:44');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('114', '321219', 'Logged in', NULL, '::1', '2025-11-30 04:19:06');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('115', '321219', 'Failed login attempt (wrong password)', 'Attempted username: admin', '::1', '2026-01-12 10:35:36');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('116', '321219', 'Logged in', NULL, '::1', '2026-01-12 10:35:43');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('117', '321219', 'Added announcement', 'Title: titen', NULL, '2026-01-12 10:37:44');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('118', '321219', 'Logged in', NULL, '::1', '2026-01-16 06:38:06');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('119', '321215', 'Failed login attempt (not approved)', 'Attempted username: avina', '::1', '2026-01-16 09:55:33');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('120', '321220', 'Logged in', NULL, '::1', '2026-01-16 09:55:41');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('121', '321220', 'Logged in', NULL, '::1', '2026-01-16 09:57:04');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('122', '0', 'Logged in', NULL, '::1', '2026-01-16 09:57:39');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('123', '321219', 'Logged in', NULL, '::1', '2026-01-19 04:02:01');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('124', '321219', 'Logged in', NULL, '::1', '2026-01-19 04:43:57');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('125', '321219', 'Logged in', NULL, '::1', '2026-01-20 20:43:33');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('126', '321219', 'Logged in', NULL, '::1', '2026-01-24 15:58:25');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('127', '321219', 'Logged in', NULL, '::1', '2026-01-24 21:12:26');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('128', '0', 'Logged in', NULL, '::1', '2026-01-24 21:14:53');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('129', '321220', 'Logged in', NULL, '::1', '2026-01-24 21:15:36');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('130', '321220', 'Logged in', NULL, '::1', '2026-01-24 21:42:50');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('131', '321220', 'Added announcement', 'Title: Qui exercitation sun', NULL, '2026-01-24 21:45:16');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('132', '321220', 'Added announcement', 'Title: Qui adipisicing minu', NULL, '2026-01-24 21:59:02');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('133', '321220', 'Added announcement', 'Title: Optio mollitia duci', NULL, '2026-01-24 21:59:54');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('134', '321220', 'Edited announcement', 'Edited Title: Fishing Permit Renewal', NULL, '2026-01-24 22:01:36');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('135', '321219', 'Logged in', NULL, '::1', '2026-01-25 05:15:25');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('136', '321219', 'Logged in', NULL, '::1', '2026-01-26 22:17:55');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('137', '321219', 'Logged in', NULL, '::1', '2026-01-26 22:33:05');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('138', '321220', 'Logged in', NULL, '::1', '2026-01-26 22:33:43');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('139', '321220', 'Restored officer: Cristopher M. De Jesus', 'Restored officer: Cristopher M. De Jesus', NULL, '2026-01-26 22:41:13');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('140', '321219', 'Logged in', NULL, '::1', '2026-02-08 02:40:36');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('141', '321220', 'Logged in', NULL, '::1', '2026-02-08 02:42:27');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('142', '0', 'Failed login attempt (not approved)', 'Attempted username: katkat', '::1', '2026-02-08 03:02:23');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('143', '321220', 'Logged in', NULL, '::1', '2026-02-08 03:38:41');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('144', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-19-29.sql', '::1', '2026-02-08 04:39:27');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('145', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_13-44-50.sql (61,563 bytes)', '::1', '2026-02-08 04:46:35');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('146', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-44-50.sql', '::1', '2026-02-08 04:46:38');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('147', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_13-48-02.sql (62,034 bytes)', '::1', '2026-02-08 04:49:47');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('148', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-48-02.sql', '::1', '2026-02-08 04:49:51');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('149', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_13-50-19.sql (62,505 bytes)', '::1', '2026-02-08 04:52:04');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('150', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-50-19.sql', '::1', '2026-02-08 04:52:08');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('151', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_13-50-19.sql', '::1', '2026-02-08 05:00:19');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('152', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_14-05-11.sql (63,208 bytes)', '::1', '2026-02-08 05:06:55');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('153', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_14-05-11.sql', '::1', '2026-02-08 05:06:57');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('154', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_14-06-39.sql (63,679 bytes)', '::1', '2026-02-08 05:08:24');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('155', '321220', 'Delete Backup', 'Deleted backup file: backup_2025-10-02-16-43-24.sql', '::1', '2026-02-08 05:19:29');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('156', '321220', 'Delete Backup', 'Deleted backup file: test_backup_2026-02-09_14-35-02.sql', '::1', '2026-02-08 05:37:13');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('157', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_14-36-04.sql (64,233 bytes)', '::1', '2026-02-08 05:37:49');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('158', '321220', 'Database Backup', 'Created backup: backup_2026-02-09_14-36-10.sql (64,687 bytes)', '::1', '2026-02-08 05:37:55');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('159', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-09_14-36-10.sql', '::1', '2026-02-08 05:38:01');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('160', '321220', 'Logged in', NULL, '::1', '2026-02-08 20:08:07');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('161', '321220', 'Logged in', NULL, '::1', '2026-02-10 06:22:42');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('162', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-10 06:26:56');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('163', '321220', 'Logged in', NULL, '::1', '2026-02-10 06:27:05');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('164', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_15-53-25.sql (67,247 bytes)', '::1', '2026-02-10 06:55:11');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('165', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_15-53-25.sql', '::1', '2026-02-10 06:55:15');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('166', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_15-53-25.sql', '::1', '2026-02-10 06:56:32');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('167', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_15-55-15.sql (68,121 bytes)', '::1', '2026-02-10 06:57:02');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('168', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_15-55-15.sql', '::1', '2026-02-10 06:57:05');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('169', '321220', 'Database Restore', 'Restored database from: backup_2026-02-11_16-00-21.sql', '::1', '2026-02-10 15:11:02');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('170', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-09-29.sql', '::1', '2026-02-10 15:11:15');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('171', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-10-06.sql', '::1', '2026-02-10 15:11:53');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('172', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-16-31.sql', '::1', '2026-02-10 15:18:18');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('173', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-23-19.sql', '::1', '2026-02-10 15:25:06');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('174', '321220', 'Database Restore', 'Restored database from: backup_2026-02-11_16-28-52.sql', '::1', '2026-02-10 23:44:01');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('175', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-46-34.sql', '::1', '2026-02-10 23:48:21');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('176', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-54-56.sql', '::1', '2026-02-10 23:56:43');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('177', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_16-54-56.sql', '::1', '2026-02-10 23:56:46');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('178', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_16-58-41.sql', '::1', '2026-02-11 00:00:28');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('179', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_17-08-19.sql', '::1', '2026-02-11 00:10:05');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('180', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_17-08-19.sql', '::1', '2026-02-11 00:10:08');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('181', '321220', 'Logged in', NULL, '::1', '2026-02-11 02:14:38');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('182', '321220', 'Logged in', NULL, '::1', '2026-02-11 02:24:59');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('183', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-11 02:30:18');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('184', '321220', 'Logged in', NULL, '::1', '2026-02-11 02:30:33');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('185', '321220', 'Database Backup', 'Created backup: backup_2026-02-11_19-35-07.sql', '::1', '2026-02-11 02:36:54');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('186', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-11_19-35-07.sql', '::1', '2026-02-11 02:37:09');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('187', '321220', 'Database Backup', 'Created backup: backup_2026-02-12_02-02-25.sql', '::1', '2026-02-11 09:04:17');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('188', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-12_02-02-25.sql', '::1', '2026-02-11 09:04:38');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('189', '0', 'Failed login attempt (not approved)', 'Attempted username: johncarl', '::1', '2026-02-11 09:08:55');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('190', '321219', 'Logged in', NULL, '::1', '2026-02-11 09:09:10');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('191', '0', 'Failed login attempt (wrong password)', 'Attempted username: officer', '::1', '2026-02-11 09:10:48');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('192', '0', 'Logged in', NULL, '::1', '2026-02-11 09:10:58');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('193', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-11 09:12:49');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('194', '321220', 'Logged in', NULL, '::1', '2026-02-11 09:13:01');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('195', '321220', 'Logged in', NULL, '::1', '2026-02-11 10:00:18');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('196', '321220', 'Logged in', NULL, '::1', '2026-02-11 23:22:59');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('197', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-12 22:24:02');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('198', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-12 22:24:10');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('199', '321220', 'Logged in', NULL, '::1', '2026-02-12 22:24:21');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('200', '321220', 'Logged in', NULL, '::1', '2026-02-12 23:36:36');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('201', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-02-15 22:14:02');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('202', '321220', 'Logged in', NULL, '::1', '2026-02-15 22:14:11');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('203', '321220', 'Logged in', NULL, '::1', '2026-02-15 23:41:35');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('204', '321220', 'Logged in', NULL, '::1', '2026-02-16 00:56:30');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('205', '321220', 'Logged in', NULL, '::1', '2026-02-16 14:57:38');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('206', '321220', 'Database Backup', 'Created backup: backup_2026-02-17_15-05-51.sql', '::1', '2026-02-16 15:07:44');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('207', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-17_15-05-51.sql', '::1', '2026-02-16 15:07:47');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('208', '321220', 'Database Backup', 'Created backup: backup_2026-02-17_15-15-23.sql', '::1', '2026-02-16 15:17:17');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('209', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-17_15-15-23.sql', '::1', '2026-02-16 15:17:19');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('210', '321220', 'Logged in', NULL, '::1', '2026-02-16 23:25:42');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('211', '321220', 'Database Restore', 'Restored database from backup file: backup_2026-02-17_23-23-59.sql (415 queries executed)', '::1', '2026-02-17 07:28:25');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('212', '321220', 'Database Restore', 'Restored database from backup file: backup_2026-02-17_23-44-27.sql (416 queries executed)', '::1', '2026-02-17 15:46:53');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('213', '321220', 'Database Backup', 'Created backup: backup_2026-02-18_00-07-30.sql', '::1', '2026-02-17 16:09:24');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('214', '321220', 'Database Backup', 'Created backup: backup_2026-02-18_00-44-19.sql', '::1', '2026-02-17 16:46:13');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('215', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-18_00-44-19.sql', '::1', '2026-02-17 16:46:23');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('216', '321220', 'Logged in', NULL, '::1', '2026-02-17 17:01:35');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('217', '321220', 'Database Restore', 'Restored database from backup file: backup_2026-02-18_01-53-12.sql (445 queries executed)', '::1', '2026-02-18 02:12:28');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('218', '321220', 'Logged in', NULL, '::1', '2026-02-18 19:47:58');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('219', '321219', 'Logged in', NULL, '::1', '2026-02-19 14:01:26');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('220', '321220', 'Logged in', NULL, '::1', '2026-02-19 14:35:57');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('221', '321220', 'Logged in', NULL, '::1', '2026-02-21 16:50:08');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('222', '321220', 'Logged in', NULL, '::1', '2026-02-22 15:25:22');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('223', '321220', 'Logged in', NULL, '::1', '2026-02-22 15:52:31');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('224', '321220', 'Logged in', NULL, '::1', '2026-02-22 15:52:44');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('225', '321220', 'Logged in', NULL, '::1', '2026-02-22 17:37:16');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('226', '321220', 'Logged in', NULL, '::1', '2026-02-23 00:48:35');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('227', '321220', 'Logged in', NULL, '::1', '2026-02-26 15:59:08');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('228', '321220', 'Database Backup', 'Created backup: backup_2026-02-26_15-57-29.sql', '::1', '2026-02-26 15:59:29');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('229', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-02-26_15-57-29.sql', '::1', '2026-02-26 15:59:32');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('230', '321220', 'Logged in', NULL, '::1', '2026-02-26 17:33:19');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('231', '321220', 'Added announcement', 'Title: Aspernatur dolor ea', NULL, '2026-02-26 19:24:59');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('232', '321220', 'Added announcement', 'Title: Aspernatur dolor ea', NULL, '2026-02-26 19:24:59');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('233', '321220', 'Added announcement', 'Title: Aspernatur dolor ea', NULL, '2026-02-26 19:26:53');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('234', '321220', 'Added announcement', 'Title: General Assembly Meeting', NULL, '2026-02-26 19:31:31');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('235', '321216', 'Logged in', NULL, '::1', '2026-02-26 19:32:27');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('236', '321216', 'Added announcement', 'Title: Fishing Schedule & Safety Reminder', NULL, '2026-02-26 19:33:00');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('237', '321216', 'Added announcement', 'Title: Community Clean-Up Drive', NULL, '2026-02-26 19:34:08');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('238', '321216', 'Edited announcement', 'Edited Title: Community Clean-Up Drive', NULL, '2026-02-26 19:34:35');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('239', '321216', 'Added announcement', 'Title: Deadline for Membership Dues', NULL, '2026-02-26 19:35:02');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('240', '321216', 'Added announcement', 'Title: Weather Advisory (High Waves)', NULL, '2026-02-26 19:35:36');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('241', '321216', 'Logged in', NULL, '::1', '2026-02-26 20:20:48');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('242', '321216', 'Logged in', NULL, '::1', '2026-02-26 20:25:33');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('243', '321223', 'Logged in', NULL, '::1', '2026-02-27 04:57:12');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('244', '321220', 'Logged in', NULL, '::1', '2026-02-27 05:29:15');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('245', '321220', 'Logged in', NULL, '::1', '2026-02-27 05:30:00');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('246', '321223', 'Logged in', NULL, '::1', '2026-02-27 05:31:06');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('247', '321220', 'Logged in', NULL, '::1', '2026-02-27 05:35:53');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('248', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-03-05 07:26:21');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('249', '321220', 'Failed login attempt (wrong password)', 'Attempted username: klare', '::1', '2026-03-05 07:26:28');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('250', '321220', 'Logged in', NULL, '::1', '2026-03-05 07:26:34');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('251', '321220', 'Logged in', NULL, '::1', '2026-03-05 07:35:00');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('252', '321220', 'Logged in', NULL, '::1', '2026-03-05 08:48:45');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('253', '321220', 'Database Backup', 'Created backup: backup_2026-03-04_20-25-22.sql', '::1', '2026-03-05 11:25:22');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('254', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-03-04_20-25-22.sql', '::1', '2026-03-05 11:25:23');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('255', '321220', 'Logged in', NULL, '::1', '2026-03-05 19:37:18');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('256', '321220', 'Database Backup', 'Created backup: backup_2026-03-05_17-13-40.sql', '::1', '2026-03-06 08:13:40');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('257', '321220', 'Download Backup', 'Downloaded backup file: backup_2026-03-05_17-13-40.sql', '::1', '2026-03-06 08:13:42');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('258', '321220', 'Database Restore', 'Restored database from backup file: backup_2026-03-05_17-26-29.sql (523 queries executed)', '::1', '2026-03-06 00:29:27');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('259', '321220', 'Logged in', NULL, '::1', '2026-03-06 03:12:55');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('260', '321220', 'Added announcement', 'Title: Voluptate magni volu', NULL, '2026-03-06 03:16:22');
INSERT INTO "activity_logs" ("id", "user_id", "action", "description", "ip_address", "created_at") VALUES ('261', '321220', 'Logged in', NULL, '::1', '2026-03-06 08:30:31');

-- Table: announcements
DROP TABLE IF EXISTS "announcements" CASCADE;
CREATE TABLE "announcements" (
    "id" SERIAL PRIMARY KEY,
    "title" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "content" TEXT,
    "date_posted" DATE DEFAULT NULL,
    "image" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "category" VARCHAR(50) DEFAULT 'General' DEFAULT 'General',
    "expiry_date" DATE DEFAULT NULL,
    "posted_by" VARCHAR(255) DEFAULT 'Admin' DEFAULT 'Admin',
    PRIMARY KEY ("id")
)

-- Insert data for announcements
INSERT INTO "announcements" ("id", "title", "content", "date_posted", "image", "category", "expiry_date", "posted_by") VALUES ('39', 'General Assembly Meeting', 'All members are invited to attend our General Assembly on March 5, 2026 (Thursday), 2:00 PM at the Barangay Hall. Important updates and upcoming activities will be discussed. Attendance is highly encouraged.', '2026-02-26', NULL, 'Meeting', '2026-05-06', 'Klare desteen');
INSERT INTO "announcements" ("id", "title", "content", "date_posted", "image", "category", "expiry_date", "posted_by") VALUES ('40', 'Fishing Schedule & Safety Reminder', 'Reminder to all boat operators and fishermen: please follow the approved fishing schedule and always wear life vests while at sea. Check weather updates before departure and avoid sailing during strong winds.', '2026-02-26', NULL, 'Fishing', '2026-04-03', 'Alexa');
INSERT INTO "announcements" ("id", "title", "content", "date_posted", "image", "category", "expiry_date", "posted_by") VALUES ('41', 'Community Clean-Up Drive', 'Join our Clean-Up Drive on March 2, 2026 (Monday), 6:00 AM. Assembly point: Covered Court. Bring gloves, sacks, and water. Letâ€™s keep our shoreline clean and safe for everyone.', '2026-02-26', NULL, 'Event', '2026-05-14', 'Alexa');
INSERT INTO "announcements" ("id", "title", "content", "date_posted", "image", "category", "expiry_date", "posted_by") VALUES ('42', 'Deadline for Membership Dues', 'Please settle your monthly dues on or before March 10, 2026 to avoid penalties and to keep your membership active. You may pay at the office during business hours.', '2026-02-26', NULL, 'Reminder', '2026-03-13', 'Alexa');
INSERT INTO "announcements" ("id", "title", "content", "date_posted", "image", "category", "expiry_date", "posted_by") VALUES ('43', 'Weather Advisory (High Waves)', 'Due to the latest weather advisory, all small boats are advised not to sail until further notice. Secure boats and fishing equipment. Stay alert for official updates from the barangay and local authorities.', '2026-02-26', NULL, 'Emergency', '2026-02-27', 'Alexa');

-- Table: archived_announcements
DROP TABLE IF EXISTS "archived_announcements" CASCADE;
CREATE TABLE "archived_announcements" (
    "id" INTEGER NOT NULL,
    "original_id" INTEGER NOT NULL,
    "title" VARCHAR(255) NOT NULL NOT NULL,
    "content" TEXT NOT NULL,
    "image" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "category" VARCHAR(100) DEFAULT 'General' DEFAULT 'General',
    "date_posted" TIMESTAMP DEFAULT NULL,
    "archived_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
)

-- Insert data for archived_announcements
INSERT INTO "archived_announcements" ("id", "original_id", "title", "content", "image", "category", "date_posted", "archived_at") VALUES ('2', '30', 'Clean-Up Drive', 'The Association will conduct a coastal clean-up this coming Sunday at 6:00 AM. Please bring gloves, sacks, and cleaning tools', 'Screenshot 2025-08-25 221659.png', 'Announcement', '2025-06-17 00:00:00', '2026-01-16 07:52:25');
INSERT INTO "archived_announcements" ("id", "original_id", "title", "content", "image", "category", "date_posted", "archived_at") VALUES ('3', '34', 'Qui adipisicing minu', 'Laudantium culpa vo', '1769407049_testFile.png', 'Announcement', '2026-01-25 00:00:00', '2026-01-24 21:59:13');
INSERT INTO "archived_announcements" ("id", "original_id", "title", "content", "image", "category", "date_posted", "archived_at") VALUES ('4', '35', 'Optio mollitia duci', 'Accusamus est praes', '1769407100_testFile.png', 'Announcement', '2026-01-25 00:00:00', '2026-01-24 22:00:07');
INSERT INTO "archived_announcements" ("id", "original_id", "title", "content", "image", "category", "date_posted", "archived_at") VALUES ('5', '32', 'Fishing Permit Renewal', 'Members are reminded to renew their fishing permits before the end of the month to avoid penalties.hehe', NULL, 'Announcement', '2025-06-17 00:00:00', '2026-01-24 22:06:01');
INSERT INTO "archived_announcements" ("id", "original_id", "title", "content", "image", "category", "date_posted", "archived_at") VALUES ('6', '33', ' Fishing Tournament Announcement!', 'Join us this weekend for a friendly Fishing Tournament at the riverside! Cast your lines, compete for the biggest catch, and enjoy a day of fun and camaraderie. Don''t forget your gearâ€”see you there!

', NULL, 'Announcement', '2025-06-16 00:00:00', '2026-02-26 19:27:10');
INSERT INTO "archived_announcements" ("id", "original_id", "title", "content", "image", "category", "date_posted", "archived_at") VALUES ('7', '11', 'Letâ€™s Go Fishing!', 'Calling all fishing enthusiasts! Spend a peaceful day by the water and reel in some fun. Bring your bait, rod, and good vibes!

', NULL, 'Announcement', '2025-06-16 00:00:00', '2026-02-26 19:27:23');
INSERT INTO "archived_announcements" ("id", "original_id", "title", "content", "image", "category", "date_posted", "archived_at") VALUES ('8', '10', 'Community Fishing Day', 'Grab your rods and join us for a relaxing Fishing Day by the lake! Itâ€™s the perfect time to unwind, bond with fellow anglers, and enjoy the great outdoors. Open to all agesâ€”everyoneâ€™s welcome!', 'Screenshot 2025-09-07 225612.png', 'Announcement', '2025-06-16 00:00:00', '2026-02-26 19:29:45');
INSERT INTO "archived_announcements" ("id", "original_id", "title", "content", "image", "category", "date_posted", "archived_at") VALUES ('9', '38', 'Aspernatur dolor ea', 'Ipsa dolorum sunt', '1772105097_testFile.png', 'Announcement', '2026-02-26 00:00:00', '2026-02-26 19:29:50');
INSERT INTO "archived_announcements" ("id", "original_id", "title", "content", "image", "category", "date_posted", "archived_at") VALUES ('10', '37', 'Aspernatur dolor ea', 'Ipsa dolorum sunt', '1772104983_testFile.png', 'Announcement', '2026-02-26 00:00:00', '2026-02-26 19:29:54');
INSERT INTO "archived_announcements" ("id", "original_id", "title", "content", "image", "category", "date_posted", "archived_at") VALUES ('11', '36', 'Aspernatur dolor ea', 'Ipsa dolorum sunt', '1772104982_testFile.png', 'Announcement', '2026-02-26 00:00:00', '2026-02-26 19:29:57');
INSERT INTO "archived_announcements" ("id", "original_id", "title", "content", "image", "category", "date_posted", "archived_at") VALUES ('12', '44', 'Voluptate magni volu', 'Voluptatem Corporis', NULL, 'Announcement', '2026-03-06 00:00:00', '2026-03-06 09:53:47');

-- Table: association_glance
DROP TABLE IF EXISTS "association_glance" CASCADE;
CREATE TABLE "association_glance" (
    "id" SERIAL PRIMARY KEY,
    "overview" TEXT NOT NULL,
    "founded_year" INTEGER NOT NULL,
    "members_count" INTEGER NOT NULL,
    "projects_count" INTEGER NOT NULL,
    "events_count" INTEGER NOT NULL,
    "updated_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
)

-- Insert data for association_glance
INSERT INTO "association_glance" ("id", "overview", "founded_year", "members_count", "projects_count", "events_count", "updated_at") VALUES ('1', 'Since its founding in 2009, the Bankero and Fishermen Association has been a united community of bangkeros and coastal stakeholders committed to safe, sustainable, and serviceâ€‘oriented operations.

The association works closely with local government units, partner agencies, and community organizations to promote responsible tourism, protect marine resources, and uplift the lives of its members and their families.

Through regular trainings, livelihood programs, and outreach activities, the Bankero and Fishermen Association continues to strengthen camaraderie, professionalism, and shared responsibility among its members.', '2009', '450', '50', '62', '2026-02-16 02:44:03');

-- Table: attendance
DROP TABLE IF EXISTS "attendance" CASCADE;
CREATE TABLE "attendance" (
    "id" SERIAL PRIMARY KEY,
    "event_id" INTEGER NOT NULL,
    "member_id" INTEGER NOT NULL,
    "attendance_date" DATE NOT NULL,
    "status" enum('present',
    "time_in" TIME DEFAULT NULL,
    "notes" TEXT,
    "recorded_by" INTEGER DEFAULT NULL,
    "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT NULL,
    PRIMARY KEY ("id")
)

-- Table: awards
DROP TABLE IF EXISTS "awards" CASCADE;
CREATE TABLE "awards" (
    "award_id" SERIAL PRIMARY KEY,
    "award_title" VARCHAR(255) NOT NULL NOT NULL,
    "awarding_body" VARCHAR(255) NOT NULL NOT NULL,
    "category" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "description" TEXT,
    "year_received" INTEGER DEFAULT NULL,
    "date_received" DATE DEFAULT NULL,
    "award_image" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "certificate_file" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("award_id")
)

-- Insert data for awards
INSERT INTO "awards" ("award_id", "award_title", "awarding_body", "category", "description", "year_received", "date_received", "award_image", "certificate_file", "created_at") VALUES ('1', 'Outstanding Coastal Resource Management Award', 'BFAR Region III', 'Regional', 'Recognized for exemplary efforts in sustainable fishing practices, marine conservation initiatives, and community-led coastal protection programs in Olongapo City.', '2025', '2026-02-03', '1770855144_3748da6d-7b9a-4046-b8ce-8b4950b0863e.jpg', 'cert_1770855144_Screenshot2026-02-12081155.png', '2026-02-11 08:14:10');

-- Table: awards_archive
DROP TABLE IF EXISTS "awards_archive" CASCADE;
CREATE TABLE "awards_archive" (
    "archive_id" SERIAL PRIMARY KEY,
    "award_id" INTEGER NOT NULL,
    "award_title" VARCHAR(255) NOT NULL NOT NULL,
    "awarding_body" VARCHAR(255) NOT NULL NOT NULL,
    "category" VARCHAR(100) NOT NULL NOT NULL,
    "description" TEXT,
    "year_received" INTEGER NOT NULL,
    "date_received" DATE NOT NULL,
    "award_image" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "certificate_file" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "original_created_at" TIMESTAMP NULL DEFAULT NULL,
    "archived_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("archive_id")
)

-- Insert data for awards_archive
INSERT INTO "awards_archive" ("archive_id", "award_id", "award_title", "awarding_body", "category", "description", "year_received", "date_received", "award_image", "certificate_file", "original_created_at", "archived_at") VALUES ('1', '2', 'Excellence in Community Fisheries Development', 'Department of Agriculture', 'National', 'Awarded for outstanding contribution in improving livelihood opportunities, strengthening fisherfolk organizations, and implementing effective fisheries development programs', '2026', '2026-02-02', '1770855239_d2e2cba5-9d81-4867-bdf6-796219834802.jpg', '', '2026-02-11 08:15:46', '2026-02-13 03:22:25');
INSERT INTO "awards_archive" ("archive_id", "award_id", "award_title", "awarding_body", "category", "description", "year_received", "date_received", "award_image", "certificate_file", "original_created_at", "archived_at") VALUES ('2', '4', 'Et similique volupta', 'Quia rerum nihil sin', 'Regional', 'Nihil eligendi reici', '1983', '2008-08-02', '1771011846_testFile.png', 'cert_1771011846_testFile.pdf', '2026-02-13 03:45:54', '2026-02-13 03:46:00');

-- Table: backups
DROP TABLE IF EXISTS "backups" CASCADE;
CREATE TABLE "backups" (
    "id" SERIAL PRIMARY KEY,
    "filename" VARCHAR(255) NOT NULL NOT NULL,
    "filesize" bigINTEGER NOT NULL,
    "created_by" INTEGER NOT NULL,
    "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
)

-- Insert data for backups
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('1', 'backup_2026-02-09_14-36-04.sql', '64233', '321220', '2026-02-08 05:37:49');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('2', 'backup_2026-02-09_14-36-10.sql', '64687', '321220', '2026-02-08 05:37:55');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('3', 'backup_2026-02-11_15-53-25.sql', '67247', '321220', '2026-02-10 06:55:11');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('4', 'backup_2026-02-11_15-55-15.sql', '68121', '321220', '2026-02-10 06:57:02');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('5', 'backup_2026-02-11_16-09-29.sql', '68561', '321220', '2026-02-10 15:11:15');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('6', 'backup_2026-02-11_16-10-06.sql', '68956', '321220', '2026-02-10 15:11:53');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('7', 'backup_2026-02-11_16-16-31.sql', '69351', '321220', '2026-02-10 15:18:18');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('8', 'backup_2026-02-11_16-23-19.sql', '69746', '321220', '2026-02-10 15:25:06');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('9', 'backup_2026-02-11_16-46-34.sql', '70374', '321220', '2026-02-10 23:48:21');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('10', 'backup_2026-02-11_16-54-56.sql', '70770', '321220', '2026-02-10 23:56:42');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('11', 'backup_2026-02-11_16-58-41.sql', '71398', '321220', '2026-02-11 00:00:28');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('12', 'backup_2026-02-11_17-08-19.sql', '71794', '321220', '2026-02-11 00:10:05');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('13', 'backup_2026-02-11_19-35-07.sql', '74479', '321220', '2026-02-11 02:36:54');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('14', 'backup_2026-02-12_02-02-25.sql', '76859', '321220', '2026-02-11 09:04:17');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('15', 'backup_2026-02-17_15-05-51.sql', '103053', '321220', '2026-02-16 15:07:44');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('16', 'backup_2026-02-17_15-15-23.sql', '103682', '321220', '2026-02-16 15:17:17');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('17', 'backup_2026-02-18_00-07-30.sql', '105276', '321220', '2026-02-17 16:09:24');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('18', 'backup_2026-02-18_00-44-19.sql', '105673', '321220', '2026-02-17 16:46:13');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('19', 'backup_2026-02-26_15-57-29.sql', '124808', '321220', '2026-02-26 15:59:29');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('20', 'backup_2026-03-04_20-25-22.sql', '143861', '321220', '2026-03-05 11:25:22');
INSERT INTO "backups" ("id", "filename", "filesize", "created_by", "created_at") VALUES ('21', 'backup_2026-03-05_17-13-40.sql', '146798', '321220', '2026-03-06 08:13:40');

-- Table: contact_messages
DROP TABLE IF EXISTS "contact_messages" CASCADE;
CREATE TABLE "contact_messages" (
    "id" SERIAL PRIMARY KEY,
    "name" VARCHAR(100) NOT NULL NOT NULL,
    "email" VARCHAR(100) NOT NULL NOT NULL,
    "message" TEXT NOT NULL,
    "status" enum('unread',
    "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
)

-- Insert data for contact_messages
INSERT INTO "contact_messages" ("id", "name", "email", "message", "status", "created_at") VALUES ('1', 'Jovelyn Buena', 'jovelybuena12@gmail.com', 'hi po hehe ganda nyo po', 'read', '2025-10-18 01:16:22');
INSERT INTO "contact_messages" ("id", "name", "email", "message", "status", "created_at") VALUES ('2', 'he', 'jovelybuena12@gmail.com', 'gello
', 'unread', '2025-10-28 01:20:02');

-- Table: contact_messages_archive
DROP TABLE IF EXISTS "contact_messages_archive" CASCADE;
CREATE TABLE "contact_messages_archive" (
    "archive_id" SERIAL PRIMARY KEY,
    "original_id" INTEGER DEFAULT NULL,
    "name" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "email" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "message" TEXT,
    "status" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "created_at" TIMESTAMP DEFAULT NULL,
    "archived_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("archive_id")
)

-- Table: core_values
DROP TABLE IF EXISTS "core_values" CASCADE;
CREATE TABLE "core_values" (
    "id" SERIAL PRIMARY KEY,
    "title" VARCHAR(255) NOT NULL NOT NULL,
    "description" TEXT NOT NULL,
    "sort_order" INTEGER NOT NULL DEFAULT '0',
    "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
)

-- Insert data for core_values
INSERT INTO "core_values" ("id", "title", "description", "sort_order", "created_at") VALUES ('2', 'Unity', 'We stand together as one association, helping and supporting one another in every challenge and opportunity.', '1', '2026-02-16 02:31:53');
INSERT INTO "core_values" ("id", "title", "description", "sort_order", "created_at") VALUES ('3', 'Integrity', 'We act with honesty and transparency in all our decisions and transactions for the welfare of our members.', '2', '2026-02-16 02:32:14');
INSERT INTO "core_values" ("id", "title", "description", "sort_order", "created_at") VALUES ('4', 'Sustainability', 'We promote responsible fishing and boating practices to protect our seas and ensure a livelihood for future generations.', '4', '2026-02-16 02:32:25');
INSERT INTO "core_values" ("id", "title", "description", "sort_order", "created_at") VALUES ('5', 'Service', 'We are committed to serving our members and the community through programs, trainings, and timely assistance.', '3', '2026-02-16 02:32:44');
INSERT INTO "core_values" ("id", "title", "description", "sort_order", "created_at") VALUES ('6', 'Accountability', 'We take responsibility for our actions, keep our word, and use association resources with care and fairness.', '5', '2026-02-16 02:33:22');
INSERT INTO "core_values" ("id", "title", "description", "sort_order", "created_at") VALUES ('7', 'Compassion', 'We value each memberâ€™s situation and work to uplift the lives of fishermen, boatmen, and their families.', '6', '2026-02-16 02:33:52');

-- Table: core_values_archive
DROP TABLE IF EXISTS "core_values_archive" CASCADE;
CREATE TABLE "core_values_archive" (
    "archive_id" SERIAL PRIMARY KEY,
    "original_id" INTEGER DEFAULT NULL,
    "title" VARCHAR(255) NOT NULL NOT NULL,
    "description" TEXT NOT NULL,
    "sort_order" INTEGER NOT NULL DEFAULT '0',
    "original_created_at" TIMESTAMP DEFAULT NULL,
    "archived_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("archive_id")
)

-- Table: downloadable_resources
DROP TABLE IF EXISTS "downloadable_resources" CASCADE;
CREATE TABLE "downloadable_resources" (
    "id" SERIAL PRIMARY KEY,
    "file_key" VARCHAR(100) NOT NULL NOT NULL,
    "title" VARCHAR(255) NOT NULL NOT NULL,
    "icon_class" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "color_hex" VARCHAR(20) DEFAULT '#0d6efd' DEFAULT '#0d6efd',
    "sort_order" INTEGER NOT NULL DEFAULT '0',
    "is_active" tinyINTEGER NOT NULL DEFAULT '1',
    "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
)

-- Insert data for downloadable_resources
INSERT INTO "downloadable_resources" ("id", "file_key", "title", "icon_class", "color_hex", "sort_order", "is_active", "created_at") VALUES ('1', 'membership_form', 'Membership Form', '', '#0d6efd', '1', '1', '2026-02-16 03:43:03');

-- Table: downloadable_resources_archive
DROP TABLE IF EXISTS "downloadable_resources_archive" CASCADE;
CREATE TABLE "downloadable_resources_archive" (
    "archive_id" SERIAL PRIMARY KEY,
    "original_id" INTEGER DEFAULT NULL,
    "file_key" VARCHAR(100) NOT NULL NOT NULL,
    "title" VARCHAR(255) NOT NULL NOT NULL,
    "icon_class" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "color_hex" VARCHAR(20) DEFAULT '#0d6efd' DEFAULT '#0d6efd',
    "sort_order" INTEGER NOT NULL DEFAULT '0',
    "is_active" tinyINTEGER NOT NULL DEFAULT '1',
    "original_created_at" TIMESTAMP DEFAULT NULL,
    "archived_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("archive_id")
)

-- Table: events
DROP TABLE IF EXISTS "events" CASCADE;
CREATE TABLE "events" (
    "id" SERIAL PRIMARY KEY,
    "event_poster" VARCHAR(255) NOT NULL NOT NULL,
    "event_name" VARCHAR(255) NOT NULL NOT NULL,
    "description" TEXT NOT NULL,
    "date" DATE NOT NULL,
    "time" TIME NOT NULL,
    "location" TEXT NOT NULL,
    "category" VARCHAR(100) DEFAULT 'General' DEFAULT 'General',
    "is_archived" tinyINTEGER NOT NULL DEFAULT '0',
    PRIMARY KEY ("id")
)

-- Insert data for events
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('3', '../uploads/Screenshot_2025-04-12_133511.png', 'Gone Fishing 2025!', ' Gone Fishing is a fun and relaxing community event that brings together fishing enthusiasts of all ages. Whether youre a seasoned angler or trying it out for the first time, this event offers a great opportunity to enjoy the outdoors, share techniques, and build camaraderie among fellow fishermen. ', '2025-05-09', '16:54:00', 'Baloy olongapo city', 'General', '0');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('4', '../uploads/Screenshot_2025-04-12_145620.png', 'Big BAS Event (Bangkero and Fishermen Association Special Gathering)', 'The Big BAS Event is the annual grand gathering of the Bangkero and Fishermen Associationâ€”a celebration of unity, hard work, and community spirit.Itâ€™s a day of fun, recognition, and connection for all members and their families. Come celebrate the heart of our coastal community at the biggest event of the year!', '2025-05-03', '16:54:00', 'Subic Zambales', 'General', '0');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('6', 'Screenshot_2025-06-04_105423.png', 'Red Sea International Sport Fishing Tournament', 'Red Sea Int`l Sport Fishing Tournament, will be the first global tournament to host top anglers from all around the world along with local teams competing in both Trolling ', '2025-06-27', '12:47:00', 'San maracelino', 'General', '0');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('12', 'Screenshot_2025-08-25_221659.png', 'Red Sea International Sport Fishing Tournament', 'Everyone is expected to come', '2025-08-27', '10:19:00', 'Drift Wood Baretto Olongapo City', 'General', '0');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('13', 'Screenshot_2025-09-07_224712.png', '1. Family-Friendly Fishing Tournament (Pine Island)', 'A welcoming event geared toward families, featuring casual competition, a captains meeting, food, and drinks. It&#039;s designed to be inclusive and social, perfect for anglers of all ages.', '2025-09-09', '22:47:00', 'Pine Island, Zambales', 'General', '0');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('14', 'Screenshot_2025-09-07_225612.png', '1st Subic Bay Shore Fishing Tournament', 'The inaugural shore-fishing competition in Subic Bay, spotlighting responsible angling and marine conservation. Organized by Fishâ€™n Town with the support of the Subic Bay Metropolitan Authority and local sponsors, it blends sport with sustainable tourism and community engagement.', '2026-07-24', '14:55:00', 'San Bernardino Fishing Site, Subic Bay Freeport Zone, Zambales', 'General', '0');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('16', '1757571370_29d28442-8efd-4d61-8164-45cfd342a2a7.jpg', 'Red Sea International Sport Fishing Tournament', 'ophelia', '2025-09-17', '01:19:00', 'Castillejos Zambales', 'General', '1');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('17', '1757586688_0e320bcc-941d-4276-a8b0-c89a1408b719.jpg', 'Red Sea International Sport Fishing Tournament', 'ako po geloy m caloy', '2027-04-16', '01:26:00', 'Baloy olongapo city', 'General', '0');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('26', '1760551305_Screenshot_2025-09-24_162407.png', 'Colin Lee', 'Culpa molestiae ipsa', '2024-07-25', '10:17:00', 'Voluptate tenetur qu', 'Livelihood', '0');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('27', '', 'Test Event', 'Description here', '2026-01-18', '10:00:00', 'Beach', 'General', '1');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('28', '1768671680_Screenshot_2025-10-18_225224.png', '1st Subic Bay Shore Fishing Tournament', 'birthday ni admin', '2026-01-31', '01:44:00', 'Bahay', 'Cleanup', '1');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('29', '1769069244_Screenshot_2025-06-10_081141.png', 'josedwsd', 'fefe', '2026-01-05', '16:10:00', 'wddfw', 'Festival', '1');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('30', '1769401632_Screenshot_2026-01-26_122601.png', 'Elvis Mclaughlin', 'Tempora quis sunt n', '2016-02-08', '15:58:00', 'Philadelphia', 'Training', '0');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('31', '', '1st Subic Bay Shore Fishing DFD', 'fgd', '2026-01-29', '19:50:00', 'fsd', 'Festival', '1');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('32', '1769401740_Screenshot_2026-01-26_122504.png', '1SDFSDAnament', 'refer', '2026-01-27', '20:13:00', 'ererre', 'Festival', '0');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('33', '1769398941_knscsd2526-a11baf2f-4450-4b71-8f7c-a3d1776be7cd.jpg', 'Ocean Santiago', 'Qui voluptas molliti', '1999-05-21', '14:37:00', 'Dallas', 'Training', '0');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('34', '', '1SDFSDAnament', 'fdyfg', '2026-01-06', '08:00:00', 'San Bernardino Fishing Site, Subic Bay Freeport Zone, Zambales', 'Festival', '1');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('35', '', '1st Subic Bay Shore Fishing Tournament', 'dsdd', '2026-01-02', '08:00:00', 'dsdsd', 'Festival', '1');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('36', '1769401663_testFile.png', 'Casey Gilmore', 'Excepteur cupiditate', '1985-09-20', '16:39:00', 'Tucson', 'General', '0');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('37', '1769407825_testFile.png', 'Maia Galloway', 'Sint non expedita co', '1979-09-30', '21:29:00', 'Oklahoma City', 'Training', '0');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('38', '1769582474_Screenshot_2026-01-26_122601.png', 'Annual Fishing Association Gathering and Community Outreach', 'The [Name of Fishing Association] is proud to announce its much-anticipated Annual Fishing Association Gathering, an event that brings together local anglers, community members, and environmental enthusiasts for a day of learning, networking, and celebration of our rich fishing culture. This yearâ€™s event promises to be bigger and better, emphasizing not only the sport and livelihood of fishing but also the sustainable practices that ensure our waters remain bountiful for generations to come.

Attendees will have the unique opportunity to participate in a variety of activities designed to cater to both seasoned fishermen and beginners alike. The day will begin with an opening ceremony highlighting the achievements of association members over the past year, including awards for outstanding contributions to the community and excellence in sustainable fishing practices. Following the ceremony, interactive workshops will be held, covering topics such as modern fishing techniques, proper handling of aquatic species, safety measures, and environmental conservation. Experienced anglers will share their knowledge on equipment maintenance, bait selection, and effective fishing strategies, ensuring that participants gain practical skills they can apply in the field.', '2026-02-22', '21:36:00', 'New York', 'Officers Meeting', '0');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('39', '1772111302_049e986a_Screenshot_2026-02-26_210758.png', 'DOLE LIVELIHOOD PROGRAM', 'DOLE Integrated Livelihood Program (DILP) is a Department of Labor and Employment initiative that provides livelihood assistanceâ€”such as starter kits, tools, training, and small business supportâ€”to help workers, unemployed individuals, and community groups build sustainable income and improve their quality of life.', '2026-03-05', '10:15:00', 'Driftwood, Olongapo City', 'Activity', '0');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('40', '', 'Shelley Lambert', 'Eius rerum eum dolor', '2005-07-04', '01:17:00', 'Provident non aliqu', 'Training', '0');
INSERT INTO "events" ("id", "event_poster", "event_name", "description", "date", "time", "location", "category", "is_archived") VALUES ('41', '', 'Zena Massey', 'Doloribus ipsum alia', '1996-06-27', '17:26:00', 'Officia in est disti', 'Other', '0');

-- Table: events_archive
DROP TABLE IF EXISTS "events_archive" CASCADE;
CREATE TABLE "events_archive" (
    "id" INTEGER NOT NULL,
    "event_name" VARCHAR(255) NOT NULL NOT NULL,
    "category" VARCHAR(100) DEFAULT 'General' DEFAULT 'General',
    "date" DATE NOT NULL,
    "time" TIME NOT NULL,
    "location" VARCHAR(255) NOT NULL NOT NULL,
    "description" TEXT,
    "event_poster" VARCHAR(255) DEFAULT 'default.jpg' DEFAULT 'default.jpg',
    "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
)

-- Table: featured_programs
DROP TABLE IF EXISTS "featured_programs" CASCADE;
CREATE TABLE "featured_programs" (
    "id" SERIAL PRIMARY KEY,
    "title" VARCHAR(255) NOT NULL NOT NULL,
    "description" TEXT NOT NULL,
    "icon_class" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "button_label" VARCHAR(100) DEFAULT 'View Events' DEFAULT 'View Events',
    "button_link" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "sort_order" INTEGER NOT NULL DEFAULT '0',
    "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
)

-- Insert data for featured_programs
INSERT INTO "featured_programs" ("id", "title", "description", "icon_class", "button_label", "button_link", "sort_order", "created_at") VALUES ('1', 'Coastal Clean-up Drives', 'Regular community-led initiatives to protect our marine environment, preserve coastal ecosystems, and maintain clean beaches for future generations.', 'bi-water', 'View Events', 'Button Link (URL): events.php?category=cleanup', '1', '2026-02-16 01:32:05');
INSERT INTO "featured_programs" ("id", "title", "description", "icon_class", "button_label", "button_link", "sort_order", "created_at") VALUES ('2', 'Fishermen Livelihood Support', 'Providing financial assistance, equipment support, and sustainable fishing resources to help local fishermen improve their income and quality of life.', 'bi-briefcase', 'View Events', 'events.php?category=livelihood', '2', '2026-02-16 01:32:49');
INSERT INTO "featured_programs" ("id", "title", "description", "icon_class", "button_label", "button_link", "sort_order", "created_at") VALUES ('3', 'Safety & Maritime Training', 'Comprehensive training programs covering sea safety, first aid, navigation, and emergency protocols to ensure the well-being of all fishermen.', 'bi-shield-check', 'View Events', 'events.php?category=training', '3', '2026-02-16 01:33:16');
INSERT INTO "featured_programs" ("id", "title", "description", "icon_class", "button_label", "button_link", "sort_order", "created_at") VALUES ('4', 'Environmental Protection', 'Advocacy and action programs focused on marine conservation, sustainable fishing practices, and educating the community about environmental responsibility.', 'bi-tree', 'View Events', 'events.php?category=environment', '4', '2026-02-16 01:34:14');

-- Table: featured_programs_archive
DROP TABLE IF EXISTS "featured_programs_archive" CASCADE;
CREATE TABLE "featured_programs_archive" (
    "archive_id" SERIAL PRIMARY KEY,
    "original_id" INTEGER DEFAULT NULL,
    "title" VARCHAR(255) NOT NULL NOT NULL,
    "description" TEXT NOT NULL,
    "icon_class" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "button_label" VARCHAR(100) DEFAULT 'View Events' DEFAULT 'View Events',
    "button_link" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "sort_order" INTEGER NOT NULL DEFAULT '0',
    "original_created_at" TIMESTAMP DEFAULT NULL,
    "archived_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("archive_id")
)

-- Table: galleries
DROP TABLE IF EXISTS "galleries" CASCADE;
CREATE TABLE "galleries" (
    "id" SERIAL PRIMARY KEY,
    "title" VARCHAR(255) NOT NULL NOT NULL,
    "category" VARCHAR(100) DEFAULT 'Uncategorized' DEFAULT 'Uncategorized',
    "images" TEXT NOT NULL,
    "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
)

-- Insert data for galleries
INSERT INTO "galleries" ("id", "title", "category", "images", "created_at") VALUES ('9', 'Meeting with Congressman Jay Khonghun', 'Meetings', '1764505526_7948644f2b70.jpg', '2025-11-29 04:25:26');
INSERT INTO "galleries" ("id", "title", "category", "images", "created_at") VALUES ('10', 'From Shore to Sea: Turtle Release', 'Activities', '1764505663_d204b6b97742.jpg,1764505663_8925b8b97bc7.jpg,1764505663_044e4a1da3d6.jpg,1764505663_4f5cf884a967.jpg,1764505663_1df2bdac84c2.jpg,1764505663_278e7b4c86c3.jpg', '2025-11-29 04:27:43');
INSERT INTO "galleries" ("id", "title", "category", "images", "created_at") VALUES ('11', 'Dole Integrated Livelihood Program', 'Awards', '1769582283_a901f558671f.jfif,1769582283_93a835c05dbf.jfif,1769582283_19c8db94c93b.jfif,1769582283_0b2645f2c412.jfif,1769582283_3b568394debd.jfif,1769582283_e61b5a7bce34.jfif,1769582283_1087cd88e8a7.jfif,1769582283_62632cece70b.jfif', '2026-01-26 22:39:37');
INSERT INTO "galleries" ("id", "title", "category", "images", "created_at") VALUES ('12', 'DOLE INTEGRATED LIVELIHOOD PROGRAM AWARDING', 'Awards', '1770997718_b3975cd5e08c.jfif,1770997718_6a8ca065561d.jfif,1770997718_2f83c2f7170c.jfif,1770997718_b2a8ebeee842.jfif,1770997718_9fb46ac2919f.jfif,1770997718_21974482059b.jfif,1770997718_9bf37cdc3508.jfif,1770997718_c1d676672e72.jfif,1770997718_c63173b6db5b.jfif', '2026-02-12 23:50:25');
INSERT INTO "galleries" ("id", "title", "category", "images", "created_at") VALUES ('15', 'Quaerat in consequat', 'Events', '1772738233_3bdfcceef896.jpg', '2026-03-06 03:17:13');

-- Table: galleries_archive
DROP TABLE IF EXISTS "galleries_archive" CASCADE;
CREATE TABLE "galleries_archive" (
    "archive_id" SERIAL PRIMARY KEY,
    "gallery_id" INTEGER DEFAULT NULL,
    "title" VARCHAR(255) NOT NULL NOT NULL,
    "category" VARCHAR(100) DEFAULT 'Uncategorized' DEFAULT 'Uncategorized',
    "images" TEXT NOT NULL,
    "original_created_at" TIMESTAMP DEFAULT NULL,
    "archived_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("archive_id")
)

-- Insert data for galleries_archive
INSERT INTO "galleries_archive" ("archive_id", "gallery_id", "title", "category", "images", "original_created_at", "archived_at") VALUES ('1', '14', 'Ut sed est corrupti', 'Meetings', '1771011807_f5a69b413e7d.jfif', '2026-02-13 11:45:14', '2026-02-13 03:45:24');
INSERT INTO "galleries_archive" ("archive_id", "gallery_id", "title", "category", "images", "original_created_at", "archived_at") VALUES ('2', '13', 'Ut sed est corrupti', 'Meetings', '1771010750_af5f4ed1d0fd.jfif', '2026-02-12 19:27:37', '2026-03-05 08:49:46');

-- Table: home_carousel_slides
DROP TABLE IF EXISTS "home_carousel_slides" CASCADE;
CREATE TABLE "home_carousel_slides" (
    "id" SERIAL PRIMARY KEY,
    "title" VARCHAR(255) NOT NULL NOT NULL,
    "subtitle" TEXT NOT NULL,
    "image_path" VARCHAR(255) NOT NULL NOT NULL,
    "primary_button_label" VARCHAR(100) DEFAULT 'Learn More' DEFAULT 'Learn More',
    "primary_button_link" VARCHAR(255) DEFAULT 'about_us.php' DEFAULT 'about_us.php',
    "secondary_button_label" VARCHAR(100) DEFAULT 'Join Us' DEFAULT 'Join Us',
    "secondary_button_link" VARCHAR(255) DEFAULT 'contact_us.php' DEFAULT 'contact_us.php',
    "sort_order" INTEGER NOT NULL DEFAULT '0',
    "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
)

-- Insert data for home_carousel_slides
INSERT INTO "home_carousel_slides" ("id", "title", "subtitle", "image_path", "primary_button_label", "primary_button_link", "secondary_button_label", "secondary_button_link", "sort_order", "created_at") VALUES ('1', 'Strengthening Our Fishing Communities', 'We empower small-scale fishers through livelihood support, training, and community-led programs across our coastal barangays.', 'uploads/carousel/1771270044_slides2.jpg', 'Learn More', 'about_us.php', 'Join Us', 'contact_us.php', '1', '2026-02-16 03:25:35');
INSERT INTO "home_carousel_slides" ("id", "title", "subtitle", "image_path", "primary_button_label", "primary_button_link", "secondary_button_label", "secondary_button_link", "sort_order", "created_at") VALUES ('2', 'Sustainable and Responsible Fishing', 'Together with our partners, we promote responsible fishing practices to protect our seas and secure future livelihoods.', 'uploads/carousel/1771269853_bg1.jpg', 'Learn More', 'about_us.php', 'Join Us', 'contact_us.php', '2', '2026-02-16 03:26:03');
INSERT INTO "home_carousel_slides" ("id", "title", "subtitle", "image_path", "primary_button_label", "primary_button_link", "secondary_button_label", "secondary_button_link", "sort_order", "created_at") VALUES ('3', 'Partners in Community Development', 'We work with government, NGOs, and private organizations to bring support and opportunities closer to our fishing communities.', 'uploads/carousel/1771270009_slide3.jpg', 'Learn More', 'about_us.php', 'Join Us', 'contact_us.php', '3', '2026-02-16 03:26:31');

-- Table: home_carousel_slides_archive
DROP TABLE IF EXISTS "home_carousel_slides_archive" CASCADE;
CREATE TABLE "home_carousel_slides_archive" (
    "archive_id" SERIAL PRIMARY KEY,
    "original_id" INTEGER DEFAULT NULL,
    "title" VARCHAR(255) NOT NULL NOT NULL,
    "subtitle" TEXT NOT NULL,
    "image_path" VARCHAR(255) NOT NULL NOT NULL,
    "primary_button_label" VARCHAR(100) DEFAULT 'Learn More' DEFAULT 'Learn More',
    "primary_button_link" VARCHAR(255) DEFAULT 'about_us.php' DEFAULT 'about_us.php',
    "secondary_button_label" VARCHAR(100) DEFAULT 'Join Us' DEFAULT 'Join Us',
    "secondary_button_link" VARCHAR(255) DEFAULT 'contact_us.php' DEFAULT 'contact_us.php',
    "sort_order" INTEGER NOT NULL DEFAULT '0',
    "original_created_at" TIMESTAMP DEFAULT NULL,
    "archived_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("archive_id")
)

-- Table: member_archive
DROP TABLE IF EXISTS "member_archive" CASCADE;
CREATE TABLE "member_archive" (
    "member_id" INTEGER NOT NULL,
    "name" VARCHAR(150) NOT NULL NOT NULL,
    "email" VARCHAR(150) DEFAULT NULL DEFAULT NULL,
    "phone" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "archived_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "dob" DATE DEFAULT NULL,
    "gender" VARCHAR(20) DEFAULT NULL DEFAULT NULL,
    "address" TEXT,
    "work_type" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "license_number" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "boat_name" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "fishing_area" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "emergency_name" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "emergency_phone" VARCHAR(20) DEFAULT NULL DEFAULT NULL,
    "agreement" tinyINTEGER DEFAULT '0',
    "image" VARCHAR(255) DEFAULT 'default_member.png' DEFAULT 'default_member.png',
    PRIMARY KEY ("member_id")
)

-- Insert data for member_archive
INSERT INTO "member_archive" ("member_id", "name", "email", "phone", "archived_at", "dob", "gender", "address", "work_type", "license_number", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('43', 'Jovelyn  S.', 'jovelybuena2@gmail.com', '09100176413', '2025-10-14 07:40:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO "member_archive" ("member_id", "name", "email", "phone", "archived_at", "dob", "gender", "address", "work_type", "license_number", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('64', 'Jovelyn S. Buena', '9898jknjk@gmail.com', '098765434567', '2026-01-25 05:17:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO "member_archive" ("member_id", "name", "email", "phone", "archived_at", "dob", "gender", "address", "work_type", "license_number", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('76', 'dfgdg dfdgdf dfdfd', 'hgfdsfgvbn@gmail.com', '0987654', '2025-10-03 05:19:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO "member_archive" ("member_id", "name", "email", "phone", "archived_at", "dob", "gender", "address", "work_type", "license_number", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('77', 'Cristopher M. De Jesus', 'dejesus@gmail.com', '098765434567', '2026-02-13 02:17:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO "member_archive" ("member_id", "name", "email", "phone", "archived_at", "dob", "gender", "address", "work_type", "license_number", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('78', 'mew S meow', 'dkvodsfwefjwscd@gmail.com', '09876543', '2026-02-13 02:10:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO "member_archive" ("member_id", "name", "email", "phone", "archived_at", "dob", "gender", "address", "work_type", "license_number", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('80', 'Ignatius q Pittman', 'fifuz@mailinator.com', '+1 (641) 841-86', '2026-03-05 10:55:45', '1974-12-16', 'Female', 'Fuga Illum ea alia', 'Bangkero', '770', 'Adena Cox', 'Corrupti sint quo r', 'Bree Curtis', '+1 (481) 765-3659', '1', 'member_68efc09ac12232.36903530_anime-girl-blue-eyes-white-hair-4k-wallpaper-uhdpaper.com-3025d.jpg');
INSERT INTO "member_archive" ("member_id", "name", "email", "phone", "archived_at", "dob", "gender", "address", "work_type", "license_number", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('81', 'ghfd gfh fgdfg', 'ytuuyfgg@gmail.com', '0987654', '2025-10-14 07:38:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO "member_archive" ("member_id", "name", "email", "phone", "archived_at", "dob", "gender", "address", "work_type", "license_number", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('82', 'meew s dsdfdf', 'fgfgfgg@gmail.com', '90876543', '2025-10-14 07:33:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO "member_archive" ("member_id", "name", "email", "phone", "archived_at", "dob", "gender", "address", "work_type", "license_number", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('83', 'Irma Id consequat Et exe Young', 'zyqido@mailinator.com', '+1 (197) 621-40', '2025-10-14 07:33:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO "member_archive" ("member_id", "name", "email", "phone", "archived_at", "dob", "gender", "address", "work_type", "license_number", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('84', 'Tad Placeat quia qui sa Ingram', 'zajav@mailinator.com', '+1 (201) 356-43', '2026-01-24 17:50:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');
INSERT INTO "member_archive" ("member_id", "name", "email", "phone", "archived_at", "dob", "gender", "address", "work_type", "license_number", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('87', 'Kirsten E Vaughn', 'sihyle@example.com', '9999422326', '2026-02-13 03:26:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 'default_member.png');

-- Table: member_attendance
DROP TABLE IF EXISTS "member_attendance" CASCADE;
CREATE TABLE "member_attendance" (
    "id" SERIAL PRIMARY KEY,
    "member_id" INTEGER NOT NULL,
    "event_id" INTEGER NOT NULL,
    "attendance_date" DATE NOT NULL,
    "time_in" TIME DEFAULT NULL,
    "time_out" TIME DEFAULT NULL,
    "status" enum('present',
    "remarks" TEXT,
    "encoded_by" INTEGER DEFAULT NULL,
    "encoded_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
)

-- Insert data for member_attendance
INSERT INTO "member_attendance" ("id", "member_id", "event_id", "attendance_date", "time_in", "time_out", "status", "remarks", "encoded_by", "encoded_at") VALUES ('5', '67', '14', '2026-07-24', '22:25:00', '22:27:00', 'present', '', '321220', '2026-02-22 22:28:22');
INSERT INTO "member_attendance" ("id", "member_id", "event_id", "attendance_date", "time_in", "time_out", "status", "remarks", "encoded_by", "encoded_at") VALUES ('6', '67', '17', '2027-04-16', '22:26:00', NULL, 'present', '', '321220', '2026-02-22 22:28:34');
INSERT INTO "member_attendance" ("id", "member_id", "event_id", "attendance_date", "time_in", "time_out", "status", "remarks", "encoded_by", "encoded_at") VALUES ('7', '67', '39', '2026-03-05', '23:54:00', '21:56:00', 'present', '', '321220', '2026-02-23 00:10:39');
INSERT INTO "member_attendance" ("id", "member_id", "event_id", "attendance_date", "time_in", "time_out", "status", "remarks", "encoded_by", "encoded_at") VALUES ('8', '83', '39', '2026-03-05', '23:54:00', '21:56:00', 'present', '', '321220', '2026-02-23 00:10:39');
INSERT INTO "member_attendance" ("id", "member_id", "event_id", "attendance_date", "time_in", "time_out", "status", "remarks", "encoded_by", "encoded_at") VALUES ('10', '67', '27', '2026-01-18', '16:19:00', '20:19:00', 'present', '', '321220', '2026-02-26 16:21:33');
INSERT INTO "member_attendance" ("id", "member_id", "event_id", "attendance_date", "time_in", "time_out", "status", "remarks", "encoded_by", "encoded_at") VALUES ('11', '83', '27', '2026-01-18', '16:19:00', '16:22:00', 'present', '', '321220', '2026-02-26 16:21:33');
INSERT INTO "member_attendance" ("id", "member_id", "event_id", "attendance_date", "time_in", "time_out", "status", "remarks", "encoded_by", "encoded_at") VALUES ('13', '84', '27', '2026-01-18', '16:19:00', '22:19:00', 'present', '', '321220', '2026-02-26 16:21:33');

-- Table: members
DROP TABLE IF EXISTS "members" CASCADE;
CREATE TABLE "members" (
    "id" SERIAL PRIMARY KEY,
    "name" VARCHAR(100) NOT NULL NOT NULL,
    "email" VARCHAR(100) NOT NULL NOT NULL,
    "phone" VARCHAR(15) NOT NULL NOT NULL,
    "address" TEXT NOT NULL,
    "street" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "barangay" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "municipality" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "province" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "region" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "zip_code" VARCHAR(10) DEFAULT NULL DEFAULT NULL,
    "membership_status" enum('active',
    "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "dob" DATE DEFAULT NULL,
    "gender" enum('Male',
    "civil_status" VARCHAR(20) DEFAULT NULL DEFAULT NULL,
    "work_type" enum('Fisherman',
    "membership_type" VARCHAR(20) DEFAULT NULL DEFAULT NULL,
    "license_number" VARCHAR(50) NOT NULL NOT NULL,
    "municipal_permit_no" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "bfar_fisherfolk_id" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "boat_name" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "fishing_area" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "emergency_name" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "emergency_phone" VARCHAR(20) DEFAULT NULL DEFAULT NULL,
    "agreement" tinyINTEGER DEFAULT '0',
    "image" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    PRIMARY KEY ("id"),
    UNIQUE ("email")
)

-- Insert data for members
INSERT INTO "members" ("id", "name", "email", "phone", "address", "street", "barangay", "municipality", "province", "region", "zip_code", "membership_status", "created_at", "dob", "gender", "civil_status", "work_type", "membership_type", "license_number", "municipal_permit_no", "bfar_fisherfolk_id", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('62', 'Jose M. Manalo', 'joseantonio@gmail.com', '098866554433', 'Calapacuan, Subic Zambales', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2025-09-09 22:02:06', '2025-09-09', 'Male', NULL, 'Fisherman', NULL, 'sdfdfdf', NULL, NULL, 'sdfdfd', 'sdfdf', 'sdff', 'sdffds', '1', 'member_68c267a475cf20.82725123_thelightinthisisinsanity_photography.jpg');
INSERT INTO "members" ("id", "name", "email", "phone", "address", "street", "barangay", "municipality", "province", "region", "zip_code", "membership_status", "created_at", "dob", "gender", "civil_status", "work_type", "membership_type", "license_number", "municipal_permit_no", "bfar_fisherfolk_id", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('67', 'Argie  B.', 'argieberena@gmail.com', '098786765777', 'Bulacan', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2025-09-17 20:17:39', '2006-07-12', 'Male', NULL, 'Fisherman', NULL, '', NULL, NULL, 'argie', 'bulacan', 'dkjfdfsii', '098789', '1', 'member_68ccd963555564.58457445_Screenshot2025-03-07135115.png');
INSERT INTO "members" ("id", "name", "email", "phone", "address", "street", "barangay", "municipality", "province", "region", "zip_code", "membership_status", "created_at", "dob", "gender", "civil_status", "work_type", "membership_type", "license_number", "municipal_permit_no", "bfar_fisherfolk_id", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('82', 'Zoe T Delacruz', 'byvupov@example.com', '0962549895', '40 Oak Parkway', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-02-10 06:38:50', '2005-07-13', 'Other', NULL, 'Both', NULL, '482', NULL, NULL, 'Tad Church', 'Neque commodo dolore', 'Basia Mcfarland', '+1 (418) 576-5538', '1', 'member_698c94114f2f16.27846167_fc2400be-9d89-4c78-a9d3-225e0429c6f7.jfif');
INSERT INTO "members" ("id", "name", "email", "phone", "address", "street", "barangay", "municipality", "province", "region", "zip_code", "membership_status", "created_at", "dob", "gender", "civil_status", "work_type", "membership_type", "license_number", "municipal_permit_no", "bfar_fisherfolk_id", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('83', 'Bart  Javillonar', 'bartjavillonar@gmail.com', '09304871699', 'Calapacuan Subic Zambales', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-02-11 10:12:39', '1979-02-02', 'Male', NULL, 'Both', NULL, '12345678', NULL, NULL, 'Bart', '', '', '09304871699', '1', 'member_698d36ad1cf256.72974108_3748da6d-7b9a-4046-b8ce-8b4950b0863e11.png');
INSERT INTO "members" ("id", "name", "email", "phone", "address", "street", "barangay", "municipality", "province", "region", "zip_code", "membership_status", "created_at", "dob", "gender", "civil_status", "work_type", "membership_type", "license_number", "municipal_permit_no", "bfar_fisherfolk_id", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('84', 'Michael P Madden', 'neqoroto@example.com', '09620433464', '131 First Court', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-02-13 02:13:01', '2004-11-14', 'Female', NULL, 'Both', NULL, '684', NULL, NULL, 'Amery Owen', 'Unde anim eum sint e', 'Theodore Mcfadden', '+1 (292) 609-7475', '1', 'member_698f6942500e93.49704418_testFile.png');
INSERT INTO "members" ("id", "name", "email", "phone", "address", "street", "barangay", "municipality", "province", "region", "zip_code", "membership_status", "created_at", "dob", "gender", "civil_status", "work_type", "membership_type", "license_number", "municipal_permit_no", "bfar_fisherfolk_id", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('85', 'Ishmael X Stevens', 'fuqohymyka@example.com', '09620356555', '930 East Rocky Milton Freeway', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-02-13 02:13:24', '1975-01-27', 'Other', NULL, 'Fisherman', NULL, '209', NULL, NULL, 'Hollee Gray', 'Optio atque corpori', 'Cyrus Rosa', '+1 (788) 159-6648', '1', 'member_698f69592d09d0.05922985_testFile.png');
INSERT INTO "members" ("id", "name", "email", "phone", "address", "street", "barangay", "municipality", "province", "region", "zip_code", "membership_status", "created_at", "dob", "gender", "civil_status", "work_type", "membership_type", "license_number", "municipal_permit_no", "bfar_fisherfolk_id", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('88', 'Bella K encarnason', 'bellaencarnason@example.com', '0962-043-3414', '1123 National Highway, Barretto, Olongapo City, Zambales, Region III 2200', '1123 National Highway', 'Barretto', 'Olongapo City', 'Zambales', 'Region III', '2200', 'active', '2026-03-05 10:46:13', '1983-06-07', 'Female', 'Single', 'Fisherman', NULL, '', 'MP-2026-0157', '2026-FISH-00123', 'Alea Gallegos', '', 'Nina Roach', '0974-474-6222', '1', 'member_69a87fb98f35f7.77010506_Screenshot2026-03-05025328.png');
INSERT INTO "members" ("id", "name", "email", "phone", "address", "street", "barangay", "municipality", "province", "region", "zip_code", "membership_status", "created_at", "dob", "gender", "civil_status", "work_type", "membership_type", "license_number", "municipal_permit_no", "bfar_fisherfolk_id", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('89', 'Roanna R Balanas', 'roansbalanas2@example.com', '0962-046-5456', '0456 Baloy Beach Road, Barretto, Olongapo City, Zambales, Region III 2200', '0456 Baloy Beach Road', 'Barretto', 'Olongapo City', 'Zambales', 'Region III', '2200', 'active', '2026-03-05 10:48:51', '1985-01-29', 'Male', 'Single', 'Bangkero', NULL, '', '', '', 'Balanas Boat', NULL, 'Gloria Spears', '0963-215-6656', '1', 'member_69a87e93ad78c3.36926512_Gemini_Generated_Image_oixh2poixh2poixh.png');
INSERT INTO "members" ("id", "name", "email", "phone", "address", "street", "barangay", "municipality", "province", "region", "zip_code", "membership_status", "created_at", "dob", "gender", "civil_status", "work_type", "membership_type", "license_number", "municipal_permit_no", "bfar_fisherfolk_id", "boat_name", "fishing_area", "emergency_name", "emergency_phone", "agreement", "image") VALUES ('90', 'Robert V takasa', 'robertosanseaa@example.com', '1396-139-9688', '640B Coastal Road, Barretto, Olongapo City, Zambales, Region II 2200', '640B Coastal Road', 'Barretto', 'Olongapo City', 'Zambales', 'Region II', '2200', 'active', '2026-03-05 10:51:58', '1982-02-02', 'Male', 'Married', 'Fisherman', NULL, '', 'MP-2026-0157', '2026-FISH-00123', '', NULL, 'Oprah Fowler', '0963-565-4524', '1', 'member_69a87f4e2a0218.28752034_Gemini_Generated_Image_nqqfn8nqqfn8nqqf.png');

-- Table: mission_vision
DROP TABLE IF EXISTS "mission_vision" CASCADE;
CREATE TABLE "mission_vision" (
    "id" SERIAL PRIMARY KEY,
    "mission" TEXT NOT NULL,
    "vision" TEXT NOT NULL,
    "updated_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
)

-- Insert data for mission_vision
INSERT INTO "mission_vision" ("id", "mission", "vision", "updated_at") VALUES ('1', 'To empower local fishermen and boatmen through collaboration, sustainable practices, training programs, and strong leadership, ensuring the welfare and continuous development of our members and their families.', 'To be the leading fishermen association in the region, recognized for fostering unity, promoting sustainable fishing practices, and creating lasting opportunities for growth and prosperity in our community.', '2026-02-16 02:22:04');

-- Table: officer_roles
DROP TABLE IF EXISTS "officer_roles" CASCADE;
CREATE TABLE "officer_roles" (
    "id" SERIAL PRIMARY KEY,
    "role_name" VARCHAR(100) NOT NULL NOT NULL,
    "description" TEXT,
    "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "display_order" INTEGER DEFAULT '0',
    PRIMARY KEY ("id")
)

-- Insert data for officer_roles
INSERT INTO "officer_roles" ("id", "role_name", "description", "created_at", "updated_at", "display_order") VALUES ('1', 'President', 'Chief executive officer; oversees overall operations and represents the organization', '0000-00-00 00:00:00', '2026-03-05 08:37:24', '1');
INSERT INTO "officer_roles" ("id", "role_name", "description", "created_at", "updated_at", "display_order") VALUES ('2', 'Vice President', 'Assists the President and assumes duties in their absence; oversees specific committees', '0000-00-00 00:00:00', '2026-03-05 08:37:24', '2');
INSERT INTO "officer_roles" ("id", "role_name", "description", "created_at", "updated_at", "display_order") VALUES ('3', 'Secretary', 'Maintains records, minutes of meetings, and official correspondence', '0000-00-00 00:00:00', '2026-03-05 08:37:24', '3');
INSERT INTO "officer_roles" ("id", "role_name", "description", "created_at", "updated_at", "display_order") VALUES ('4', 'Treasurer', 'Manages financial records, budgets, and monetary transactions', '0000-00-00 00:00:00', '2026-03-05 08:37:24', '4');
INSERT INTO "officer_roles" ("id", "role_name", "description", "created_at", "updated_at", "display_order") VALUES ('6', 'Auditor', 'Reviews and verifies financial records for accuracy and compliance', '2026-03-05 08:26:54', '2026-03-05 08:37:24', '5');
INSERT INTO "officer_roles" ("id", "role_name", "description", "created_at", "updated_at", "display_order") VALUES ('7', 'Business Manager', 'Manages business operations and fundraising activities', '2026-03-05 08:27:03', '2026-03-05 08:37:24', '6');
INSERT INTO "officer_roles" ("id", "role_name", "description", "created_at", "updated_at", "display_order") VALUES ('8', 'Peace Officer', 'Maintains order and ensures adherence to rules during meetings', '2026-03-05 08:27:29', '2026-03-05 08:37:24', '7');
INSERT INTO "officer_roles" ("id", "role_name", "description", "created_at", "updated_at", "display_order") VALUES ('9', 'Sergeant-at-Arms', 'Ensures security, maintains discipline, and executes ceremonial duties', '2026-03-05 08:27:37', '2026-03-05 08:37:24', '8');

-- Table: officer_roles_archive
DROP TABLE IF EXISTS "officer_roles_archive" CASCADE;
CREATE TABLE "officer_roles_archive" (
    "archive_id" SERIAL PRIMARY KEY,
    "original_id" INTEGER DEFAULT NULL,
    "role_name" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "description" TEXT,
    "created_at" TIMESTAMP DEFAULT NULL,
    "archived_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("archive_id")
)

-- Insert data for officer_roles_archive
INSERT INTO "officer_roles_archive" ("archive_id", "original_id", "role_name", "description", "created_at", "archived_at") VALUES ('1', '5', 'Board of Director', 'Provides oversight and policy guidance; participates in approvals and planning.', '1970-01-01 08:00:00', '2026-03-05 08:26:40');

-- Table: officers
DROP TABLE IF EXISTS "officers" CASCADE;
CREATE TABLE "officers" (
    "id" SERIAL PRIMARY KEY,
    "member_id" INTEGER NOT NULL,
    "position" VARCHAR(255) NOT NULL NOT NULL,
    "term_start" DATE NOT NULL,
    "term_end" DATE NOT NULL,
    "image" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "role_id" INTEGER DEFAULT NULL,
    "description" TEXT,
    PRIMARY KEY ("id")
)

-- Insert data for officers
INSERT INTO "officers" ("id", "member_id", "position", "term_start", "term_end", "image", "role_id", "description") VALUES ('36', '67', 'Secretary', '2025-09-08', '2025-09-24', '1758256680_Screenshot 2024-07-27 113641.png', NULL, NULL);
INSERT INTO "officers" ("id", "member_id", "position", "term_start", "term_end", "image", "role_id", "description") VALUES ('39', '62', 'President', '2025-09-18', '2025-09-10', '1758257402_Screenshot 2024-04-15 220726.png', NULL, NULL);
INSERT INTO "officers" ("id", "member_id", "position", "term_start", "term_end", "image", "role_id", "description") VALUES ('41', '62', 'President', '2025-09-16', '2025-09-23', '1758258737_background.png', NULL, 'fgfgfg');
INSERT INTO "officers" ("id", "member_id", "position", "term_start", "term_end", "image", "role_id", "description") VALUES ('49', '67', '', '2025-10-14', '2029-05-14', '1764511607_Screenshot 2025-11-30 220633.png', '1', '');
INSERT INTO "officers" ("id", "member_id", "position", "term_start", "term_end", "image", "role_id", "description") VALUES ('51', '64', '', '2025-09-11', '2025-10-22', '1760281419_Screenshot 2025-10-12 121319.png', '4', 'super pretty');
INSERT INTO "officers" ("id", "member_id", "position", "term_start", "term_end", "image", "role_id", "description") VALUES ('55', '62', '', '2024-12-08', '2026-02-27', '1764511540_Screenshot 2025-04-23 150253.png', '2', 'J. Jose serves as a dedicated and visionary Vice president, bringing over 15 years of leadership experience in strategic planning, organizational development, and community engagement. ');
INSERT INTO "officers" ("id", "member_id", "position", "term_start", "term_end", "image", "role_id", "description") VALUES ('56', '80', '', '2025-10-29', '2025-12-05', '1764514621_background.png', '3', 'sd');
INSERT INTO "officers" ("id", "member_id", "position", "term_start", "term_end", "image", "role_id", "description") VALUES ('57', '77', '', '2025-09-28', '2026-01-15', '1759659719_Screenshot 2025-04-23 125832.png', '4', NULL);
INSERT INTO "officers" ("id", "member_id", "position", "term_start", "term_end", "image", "role_id", "description") VALUES ('59', '88', '', '2026-03-02', '2026-04-30', '1772651106_Screenshot 2026-03-05 025328.png', '3', '');
INSERT INTO "officers" ("id", "member_id", "position", "term_start", "term_end", "image", "role_id", "description") VALUES ('60', '90', '', '2026-04-27', '2027-06-08', '1772650859_Gemini_Generated_Image_oixh2poixh2poixh.png', '2', '');
INSERT INTO "officers" ("id", "member_id", "position", "term_start", "term_end", "image", "role_id", "description") VALUES ('61', '89', '', '2026-02-09', '2027-06-15', '1772651314_Screenshot 2026-03-05 030823.png', '4', '');

-- Table: officers_archive
DROP TABLE IF EXISTS "officers_archive" CASCADE;
CREATE TABLE "officers_archive" (
    "id" SERIAL PRIMARY KEY,
    "member_id" INTEGER NOT NULL,
    "role_id" INTEGER NOT NULL,
    "term_start" DATE DEFAULT NULL,
    "term_end" DATE DEFAULT NULL,
    "image" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "archived_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
)

-- Insert data for officers_archive
INSERT INTO "officers_archive" ("id", "member_id", "role_id", "term_start", "term_end", "image", "archived_at") VALUES ('2', '67', '3', '2025-09-23', '2025-09-10', '1758266934_background.png', '2025-10-03 05:43:55');
INSERT INTO "officers_archive" ("id", "member_id", "role_id", "term_start", "term_end", "image", "archived_at") VALUES ('3', '62', '1', '2023-06-12', '2025-11-19', '1757571148_Screenshot 2025-09-08 003235.png', '2025-10-11 07:01:38');
INSERT INTO "officers_archive" ("id", "member_id", "role_id", "term_start", "term_end", "image", "archived_at") VALUES ('4', '79', '1', '2025-10-15', '2025-10-21', NULL, '2025-10-11 07:01:41');
INSERT INTO "officers_archive" ("id", "member_id", "role_id", "term_start", "term_end", "image", "archived_at") VALUES ('5', '67', '3', '2025-10-08', '2030-06-05', '1759659520_Screenshot 2025-09-08 003107.png', '2025-10-11 07:01:43');
INSERT INTO "officers_archive" ("id", "member_id", "role_id", "term_start", "term_end", "image", "archived_at") VALUES ('7', '79', '2', '2025-10-08', '2025-12-11', '1760280765_background.png', '2025-10-11 07:01:47');
INSERT INTO "officers_archive" ("id", "member_id", "role_id", "term_start", "term_end", "image", "archived_at") VALUES ('8', '64', '2', '2025-09-08', '2025-10-11', '1757586797_Screenshot 2025-09-08 003004.png', '2025-10-11 07:01:50');
INSERT INTO "officers_archive" ("id", "member_id", "role_id", "term_start", "term_end", "image", "archived_at") VALUES ('9', '43', '3', '2025-10-23', '2025-10-23', '1760282388_Screenshot 2024-04-05 214617.png', '2025-10-11 07:20:05');
INSERT INTO "officers_archive" ("id", "member_id", "role_id", "term_start", "term_end", "image", "archived_at") VALUES ('10', '43', '3', '2025-10-18', '2025-10-14', '1760281938_Screenshot 2024-04-20 212354.png', '2025-10-11 07:20:08');
INSERT INTO "officers_archive" ("id", "member_id", "role_id", "term_start", "term_end", "image", "archived_at") VALUES ('11', '79', '5', '2020-02-22', '1984-10-23', '', '2025-10-14 07:40:33');
INSERT INTO "officers_archive" ("id", "member_id", "role_id", "term_start", "term_end", "image", "archived_at") VALUES ('12', '67', '1', '2025-11-06', '2029-06-11', '1760281376_Screenshot 2025-10-12 121319.png', '2025-11-29 06:05:51');
INSERT INTO "officers_archive" ("id", "member_id", "role_id", "term_start", "term_end", "image", "archived_at") VALUES ('13', '83', '8', '2026-02-25', '2026-03-27', '', '2026-03-05 10:56:00');

-- Table: partners_sponsors
DROP TABLE IF EXISTS "partners_sponsors" CASCADE;
CREATE TABLE "partners_sponsors" (
    "id" SERIAL PRIMARY KEY,
    "name" VARCHAR(255) NOT NULL NOT NULL,
    "logo_path" VARCHAR(255) NOT NULL NOT NULL,
    "type" VARCHAR(50) NOT NULL DEFAULT 'partner' NOT NULL DEFAULT 'partner',
    "sort_order" INTEGER NOT NULL DEFAULT '0',
    "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
)

-- Insert data for partners_sponsors
INSERT INTO "partners_sponsors" ("id", "name", "logo_path", "type", "sort_order", "created_at") VALUES ('1', 'Municipality of Olongapo City', 'uploads/partners/1771268607_olongapo.png', 'partner', '1', '2026-02-16 03:05:17');
INSERT INTO "partners_sponsors" ("id", "name", "logo_path", "type", "sort_order", "created_at") VALUES ('2', 'Bureau of Fisheries & Aquatic Resources', 'uploads/partners/1771268633_bfar.png', 'partner', '2', '2026-02-16 03:05:43');
INSERT INTO "partners_sponsors" ("id", "name", "logo_path", "type", "sort_order", "created_at") VALUES ('3', 'Olongapo City Agriculture Department', 'uploads/partners/1771268658_agriculture.png', 'sponsor', '3', '2026-02-16 03:06:08');
INSERT INTO "partners_sponsors" ("id", "name", "logo_path", "type", "sort_order", "created_at") VALUES ('4', 'USAID', 'uploads/partners/1771268688_usaid.png', 'sponsor', '4', '2026-02-16 03:06:38');

-- Table: partners_sponsors_archive
DROP TABLE IF EXISTS "partners_sponsors_archive" CASCADE;
CREATE TABLE "partners_sponsors_archive" (
    "archive_id" SERIAL PRIMARY KEY,
    "original_id" INTEGER DEFAULT NULL,
    "name" VARCHAR(255) NOT NULL NOT NULL,
    "logo_path" VARCHAR(255) NOT NULL NOT NULL,
    "type" VARCHAR(50) NOT NULL DEFAULT 'partner' NOT NULL DEFAULT 'partner',
    "sort_order" INTEGER NOT NULL DEFAULT '0',
    "original_created_at" TIMESTAMP DEFAULT NULL,
    "archived_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("archive_id")
)

-- Table: system_config
DROP TABLE IF EXISTS "system_config" CASCADE;
CREATE TABLE "system_config" (
    "id" INTEGER NOT NULL,
    "assoc_name" VARCHAR(255) NOT NULL NOT NULL,
    "assoc_email" VARCHAR(255) NOT NULL NOT NULL,
    "assoc_phone" VARCHAR(50) NOT NULL NOT NULL,
    "assoc_address" TEXT NOT NULL,
    "assoc_logo" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "auto_backup_status" tinyINTEGER NOT NULL DEFAULT '0',
    "backup_storage_limit_mb" INTEGER NOT NULL DEFAULT '100',
    "auto_backup_next_run" TIMESTAMP DEFAULT NULL,
    PRIMARY KEY ("id")
)

-- Insert data for system_config
INSERT INTO "system_config" ("id", "assoc_name", "assoc_email", "assoc_phone", "assoc_address", "assoc_logo", "auto_backup_status", "backup_storage_limit_mb", "auto_backup_next_run") VALUES ('1', 'Bankero and Fishermen Association ', 'info@association.org', '9620433464', 'Barreto Street, Olongapo City', 'assoc_logo.png', '1', '100', NULL);

-- Table: transparency_beneficiaries
DROP TABLE IF EXISTS "transparency_beneficiaries" CASCADE;
CREATE TABLE "transparency_beneficiaries" (
    "id" INTEGER NOT NULL,
    "program_id" INTEGER DEFAULT NULL,
    "name" VARCHAR(255) NOT NULL NOT NULL,
    "household_name" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "assistance_type" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "category" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "amount_value" decimal(15,
    "quantity" INTEGER DEFAULT NULL,
    "date_assisted" DATE NOT NULL,
    "status" VARCHAR(20) NOT NULL DEFAULT 'served' NOT NULL DEFAULT 'served',
    "barangay" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "municipality" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "province" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "photo_path" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "short_story" TEXT,
    "featured" tinyINTEGER NOT NULL DEFAULT '0',
    "created_at" TIMESTAMP NOT NULL,
    "updated_at" TIMESTAMP NOT NULL,
    PRIMARY KEY ("id")
)

-- Table: transparency_campaigns
DROP TABLE IF EXISTS "transparency_campaigns" CASCADE;
CREATE TABLE "transparency_campaigns" (
    "id" INTEGER NOT NULL,
    "name" VARCHAR(255) NOT NULL NOT NULL,
    "slug" VARCHAR(191) DEFAULT NULL DEFAULT NULL,
    "description" TEXT,
    "goal_amount" decimal(15,
    "status" VARCHAR(20) NOT NULL DEFAULT 'planned' NOT NULL DEFAULT 'planned',
    "start_date" DATE DEFAULT NULL,
    "end_date" DATE DEFAULT NULL,
    "banner_image" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "created_at" TIMESTAMP NOT NULL,
    "updated_at" TIMESTAMP NOT NULL,
    PRIMARY KEY ("id"),
    UNIQUE ("slug")
)

-- Insert data for transparency_campaigns
INSERT INTO "transparency_campaigns" ("id", "name", "slug", "description", "goal_amount", "status", "start_date", "end_date", "banner_image", "created_at", "updated_at") VALUES ('1', 'Emergency Relief Fund â€“', 'emergency-relief-bagyong-', 'Emergency relief campaign to provide food packs, clean water, and basic necessities to fishing families heavily affected by Bagyong Ramon in coastal barangays.', '25000.00', 'planned', NULL, '2026-03-16', '', '2026-02-17 09:04:37', '2026-03-05 03:22:57');
INSERT INTO "transparency_campaigns" ("id", "name", "slug", "description", "goal_amount", "status", "start_date", "end_date", "banner_image", "created_at", "updated_at") VALUES ('3', 'Bangon Bangkero: Boat Repair & Replacement', 'bangon-bangkero-boat-assistance', 'Fundraising drive to repair and replace damaged fishing boats for registered members, including provision of basic fishing gear and safety equipment.', '20000.00', 'active', NULL, '2026-04-18', '', '2026-02-17 09:06:59', '2026-03-05 03:24:04');
INSERT INTO "transparency_campaigns" ("id", "name", "slug", "description", "goal_amount", "status", "start_date", "end_date", "banner_image", "created_at", "updated_at") VALUES ('6', 'DOLE Integrated Livelihood Program â€“ Awarding of Livelihood Kits', 'dole-integrated-livelihood-program-awarding', 'Grant support under the DOLE Integrated Livelihood Program providing livelihood starter kits (processing equipment, containers, and supplies) to association members to enhance income-generating activities.', '50000.00', 'completed', '0000-00-00', '2026-02-11', '', '2026-02-17 09:21:26', '2026-02-17 09:21:26');

-- Table: transparency_campaigns_archive
DROP TABLE IF EXISTS "transparency_campaigns_archive" CASCADE;
CREATE TABLE "transparency_campaigns_archive" (
    "archive_id" SERIAL PRIMARY KEY,
    "original_id" INTEGER NOT NULL,
    "name" VARCHAR(255) NOT NULL NOT NULL,
    "slug" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "description" TEXT,
    "goal_amount" decimal(15,
    "status" VARCHAR(50) DEFAULT 'active' DEFAULT 'active',
    "start_date" DATE DEFAULT NULL,
    "end_date" DATE DEFAULT NULL,
    "banner_image" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "created_at" TIMESTAMP NULL DEFAULT NULL,
    "updated_at" TIMESTAMP NULL DEFAULT NULL,
    "archived_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "archived_by" INTEGER DEFAULT NULL,
    PRIMARY KEY ("archive_id")
)

-- Insert data for transparency_campaigns_archive
INSERT INTO "transparency_campaigns_archive" ("archive_id", "original_id", "name", "slug", "description", "goal_amount", "status", "start_date", "end_date", "banner_image", "created_at", "updated_at", "archived_at", "archived_by") VALUES ('1', '4', 'Fisherfolk Skills & Safety Training', 'fisherfolk-skills-safety-training', 'Campaign to support a series of trainings on sea safety, financial literacy, and sustainable fishing practices for association members.', '15000.00', 'completed', NULL, '2026-02-09', '', '2026-02-17 17:07:50', '2026-03-05 11:23:11', '2026-03-06 08:12:57', '321220');

-- Table: transparency_donations
DROP TABLE IF EXISTS "transparency_donations" CASCADE;
CREATE TABLE "transparency_donations" (
    "id" INTEGER NOT NULL,
    "campaign_id" INTEGER DEFAULT NULL,
    "donor_name" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "donor_type" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "amount" decimal(15,
    "currency" VARCHAR(10) NOT NULL DEFAULT 'PHP' NOT NULL DEFAULT 'PHP',
    "date_received" DATE NOT NULL,
    "payment_method" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "reference_code" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "status" VARCHAR(20) NOT NULL DEFAULT 'confirmed' NOT NULL DEFAULT 'confirmed',
    "is_restricted" tinyINTEGER NOT NULL DEFAULT '0',
    "notes" TEXT,
    "created_at" TIMESTAMP NOT NULL,
    "updated_at" TIMESTAMP NOT NULL,
    PRIMARY KEY ("id")
)

-- Insert data for transparency_donations
INSERT INTO "transparency_donations" ("id", "campaign_id", "donor_name", "donor_type", "amount", "currency", "date_received", "payment_method", "reference_code", "status", "is_restricted", "notes", "created_at", "updated_at") VALUES ('1', '6', 'Department of Labor and Employment (DOLE)', 'organization', '50000.00', 'PHP', '2026-02-10', 'Grant (in kind)', 'DOLE-ILP-2026-01', 'confirmed', '1', 'Livelihood starter kits (processing equipment, containers, corn oil, and supplies) awarded to members under DOLE Integrated Livelihood Program.', '2026-02-17 09:23:57', '2026-02-17 09:23:57');

-- Table: transparency_donations_archive
DROP TABLE IF EXISTS "transparency_donations_archive" CASCADE;
CREATE TABLE "transparency_donations_archive" (
    "archive_id" SERIAL PRIMARY KEY,
    "original_id" INTEGER NOT NULL,
    "campaign_id" INTEGER DEFAULT NULL,
    "donor_name" VARCHAR(255) NOT NULL NOT NULL,
    "donor_type" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "amount" decimal(15,
    "currency" VARCHAR(10) DEFAULT 'PHP' DEFAULT 'PHP',
    "date_received" DATE DEFAULT NULL,
    "payment_method" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "reference_code" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "status" VARCHAR(50) DEFAULT 'confirmed' DEFAULT 'confirmed',
    "is_restricted" tinyINTEGER DEFAULT '0',
    "notes" TEXT,
    "archived_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "archived_by" INTEGER DEFAULT NULL,
    PRIMARY KEY ("archive_id")
)

-- Table: transparency_impact_metrics
DROP TABLE IF EXISTS "transparency_impact_metrics" CASCADE;
CREATE TABLE "transparency_impact_metrics" (
    "id" INTEGER NOT NULL,
    "metric_key" VARCHAR(100) NOT NULL NOT NULL,
    "label" VARCHAR(255) NOT NULL NOT NULL,
    "description" TEXT,
    "value" decimal(18,
    "unit" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "calculation_mode" VARCHAR(20) NOT NULL DEFAULT 'manual' NOT NULL DEFAULT 'manual',
    "auto_source" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "last_computed_at" TIMESTAMP DEFAULT NULL,
    "is_active" tinyINTEGER NOT NULL DEFAULT '1',
    "display_order" INTEGER NOT NULL DEFAULT '0',
    "created_at" TIMESTAMP NOT NULL,
    "updated_at" TIMESTAMP NOT NULL,
    PRIMARY KEY ("id"),
    UNIQUE ("metric_key")
)

-- Table: transparency_programs
DROP TABLE IF EXISTS "transparency_programs" CASCADE;
CREATE TABLE "transparency_programs" (
    "id" INTEGER NOT NULL,
    "name" VARCHAR(255) NOT NULL NOT NULL,
    "category" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "description" TEXT,
    "allocated_budget" decimal(15,
    "utilized_budget" decimal(15,
    "status" VARCHAR(20) NOT NULL DEFAULT 'planned' NOT NULL DEFAULT 'planned',
    "start_date" DATE DEFAULT NULL,
    "end_date" DATE DEFAULT NULL,
    "linked_campaign_id" INTEGER DEFAULT NULL,
    "location" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "sort_order" INTEGER NOT NULL DEFAULT '0',
    "created_at" TIMESTAMP NOT NULL,
    "updated_at" TIMESTAMP NOT NULL,
    PRIMARY KEY ("id")
)

-- Insert data for transparency_programs
INSERT INTO "transparency_programs" ("id", "name", "category", "description", "allocated_budget", "utilized_budget", "status", "start_date", "end_date", "linked_campaign_id", "location", "sort_order", "created_at", "updated_at") VALUES ('1', 'Montana Cook', 'environmental', 'Cillum enim soluta e', '21.07', '66.89', 'ongoing', '2021-09-28', '1993-05-25', '6', 'Atlanta', '68', '2026-02-17 09:37:18', '2026-02-17 09:37:18');
INSERT INTO "transparency_programs" ("id", "name", "category", "description", "allocated_budget", "utilized_budget", "status", "start_date", "end_date", "linked_campaign_id", "location", "sort_order", "created_at", "updated_at") VALUES ('2', 'DOLE Integrated Livelihood â€“ Starter Kits 2025', 'livelihood', 'Provision of starter kits and livelihood assistance to qualified beneficiaries in partnership with DOLE Integrated Livelihood Program (DILP). Includes training, orientation, and distribution of livelihood kits.', '25000.00', '22000.00', 'completed', '2026-02-02', '2026-02-25', '6', 'Driftwood, Olongapo City', '1', '2026-02-17 09:48:41', '2026-03-05 03:20:23');
INSERT INTO "transparency_programs" ("id", "name", "category", "description", "allocated_budget", "utilized_budget", "status", "start_date", "end_date", "linked_campaign_id", "location", "sort_order", "created_at", "updated_at") VALUES ('3', 'DOLE Integrated Livelihood â€“ Starter Kits 2025', 'livelihood', 'Provision of starter kits and livelihood assistance to qualified beneficiaries in partnership with DOLE Integrated Livelihood Program (DILP). Includes training, orientation, and distribution of livelihood kits.', '21.00', '21.00', 'completed', '2026-02-02', '2026-02-25', '6', 'Driftwood, Olongapo City', '1', '2026-02-17 09:51:58', '2026-03-05 03:18:45');

-- Table: transparency_reports
DROP TABLE IF EXISTS "transparency_reports" CASCADE;
CREATE TABLE "transparency_reports" (
    "id" INTEGER NOT NULL,
    "title" VARCHAR(255) NOT NULL NOT NULL,
    "file_path" VARCHAR(255) NOT NULL NOT NULL,
    "report_type" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "year" INTEGER DEFAULT NULL,
    "is_active" tinyINTEGER NOT NULL DEFAULT '1',
    "display_order" INTEGER NOT NULL DEFAULT '0',
    "created_at" TIMESTAMP NOT NULL,
    "updated_at" TIMESTAMP NOT NULL,
    PRIMARY KEY ("id")
)

-- Table: transparency_settings
DROP TABLE IF EXISTS "transparency_settings" CASCADE;
CREATE TABLE "transparency_settings" (
    "id" INTEGER NOT NULL,
    "hero_title" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "hero_subtitle" TEXT,
    "hero_last_updated_override" DATE DEFAULT NULL,
    "transparency_statement" TEXT,
    "disclaimer_text" TEXT,
    "show_downloads" tinyINTEGER NOT NULL DEFAULT '1',
    "show_activity_gallery" tinyINTEGER NOT NULL DEFAULT '1',
    "primary_color" VARCHAR(20) DEFAULT NULL DEFAULT NULL,
    "created_at" TIMESTAMP NOT NULL,
    "updated_at" TIMESTAMP NOT NULL,
    PRIMARY KEY ("id")
)

-- Table: users
DROP TABLE IF EXISTS "users" CASCADE;
CREATE TABLE "users" (
    "username" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "password_hash" VARCHAR(255) NOT NULL NOT NULL,
    "id" SERIAL PRIMARY KEY,
    "role" enum('admin',
    "transparency_role" VARCHAR(20) DEFAULT NULL DEFAULT NULL,
    "status" enum('pending',
    "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "email" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "mobile" VARCHAR(20) DEFAULT NULL DEFAULT NULL,
    "gender" enum('Male',
    "address" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "avatar" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "first_name" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "last_name" VARCHAR(100) DEFAULT NULL DEFAULT NULL,
    "member_id" INTEGER DEFAULT NULL,
    "is_admin" tinyINTEGER DEFAULT '0',
    PRIMARY KEY ("id")
)

-- Insert data for users
INSERT INTO "users" ("username", "password_hash", "id", "role", "transparency_role", "status", "created_at", "email", "mobile", "gender", "address", "avatar", "first_name", "last_name", "member_id", "is_admin") VALUES ('argie2', '$2y$10$PXmeV0TETc4CasIO.PUGYe4s18MgWUyQOcwmCYDtimhT.By3nXXhC', '321211', 'admin', NULL, 'approved', '2025-10-03 03:58:05', 'argie2@gmail.com', '096204334624', 'Male', 'bulacan, bulacan ', '1760856293_cybernetic-cool-anime-cyborg-girl-9y-1920x1080.jpg', 'argie', 'buena', NULL, '0');
INSERT INTO "users" ("username", "password_hash", "id", "role", "transparency_role", "status", "created_at", "email", "mobile", "gender", "address", "avatar", "first_name", "last_name", "member_id", "is_admin") VALUES ('jesus', '$2y$10$PhnUYt.9NRtCq6DRfHIo/.guZDBPbcZEKYSVcmZyosBX7A1TeCJIW', '321212', 'member', NULL, 'pending', '2025-10-03 20:21:05', 'dejesus@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, '77', '0');
INSERT INTO "users" ("username", "password_hash", "id", "role", "transparency_role", "status", "created_at", "email", "mobile", "gender", "address", "avatar", "first_name", "last_name", "member_id", "is_admin") VALUES ('josemarichan', '$2y$10$eV5aMXWy16uOeZk1wT20x.dooy/pMTUZn0FjBZCI.yxJHpp2HbjQe', '321214', 'officer', NULL, 'rejected', '2025-10-04 03:53:29', 'josemarichan@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');
INSERT INTO "users" ("username", "password_hash", "id", "role", "transparency_role", "status", "created_at", "email", "mobile", "gender", "address", "avatar", "first_name", "last_name", "member_id", "is_admin") VALUES ('avina', '$2y$10$ys1a63YOm1/oqj6QvsJe/eRpdP.oLLqMYZKHbXn/kCYZcOavQy/ku', '321215', 'officer', NULL, 'pending', '2025-10-14 01:40:17', 'avina@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');
INSERT INTO "users" ("username", "password_hash", "id", "role", "transparency_role", "status", "created_at", "email", "mobile", "gender", "address", "avatar", "first_name", "last_name", "member_id", "is_admin") VALUES ('Alexa', '$2y$10$02SlnXsbCGRHo.Ylr6RGaeqyy4iO.JoL6iYFtzgRgLBbFgfJAWf.q', '321216', 'admin', NULL, 'approved', '2025-10-14 01:42:16', 'alexa@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1');
INSERT INTO "users" ("username", "password_hash", "id", "role", "transparency_role", "status", "created_at", "email", "mobile", "gender", "address", "avatar", "first_name", "last_name", "member_id", "is_admin") VALUES ('admin', '$2y$10$T1cQRA8Y2SqmVCfXM08dF.u.DFPWWu75Cbu0tF5Q86n1mtCQOQA4O', '321219', 'admin', NULL, 'approved', '2025-10-17 23:31:04', 'admin@gmail.com', '09876543456', 'Female', 'Sitio Bukid, Calapacuan Subic Zambales', '1760859106_photo_2024-08-13_09-05-00.jpg', 'Jovelyn', 'San Jose', NULL, '0');
INSERT INTO "users" ("username", "password_hash", "id", "role", "transparency_role", "status", "created_at", "email", "mobile", "gender", "address", "avatar", "first_name", "last_name", "member_id", "is_admin") VALUES ('klare', '$2y$10$d50LR9qy9u52qRHlRKjakO90anP8rG01PunxbukTPCjv9i1cTUvO2', '321220', 'admin', NULL, 'approved', '2025-10-18 00:41:30', 'klare@gmail.com', '09620433464', 'Female', 'Calapacuan', '1770819859_knscsd2526-a11baf2f-4450-4b71-8f7c-a3d1776be7cd.jpg', 'Klare desteen', 'Montefalco', NULL, '1');
INSERT INTO "users" ("username", "password_hash", "id", "role", "transparency_role", "status", "created_at", "email", "mobile", "gender", "address", "avatar", "first_name", "last_name", "member_id", "is_admin") VALUES ('John', '$2y$10$hTbYydbNq/9embkicVkbHO/8uA3g.MsCL3CdgpL24mzzQ2fTxp2fi', '321221', 'admin', NULL, 'approved', '2025-11-29 23:11:15', 'johncarlmangino2@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1');
INSERT INTO "users" ("username", "password_hash", "id", "role", "transparency_role", "status", "created_at", "email", "mobile", "gender", "address", "avatar", "first_name", "last_name", "member_id", "is_admin") VALUES ('kim', '$2y$10$Z.kcTA4TWDbusbBPru7Vxu2Rsrh4ipEK4RuQrTiU7lf50wHI88ija', '321222', 'officer', NULL, 'approved', '2025-11-29 23:11:48', 'kim@gmail.com', '095678434', 'Female', 'San Marcelino', '1764573767_Screenshot 2025-02-28 124358.png', 'Kimberly', 'Mangino', NULL, '0');
INSERT INTO "users" ("username", "password_hash", "id", "role", "transparency_role", "status", "created_at", "email", "mobile", "gender", "address", "avatar", "first_name", "last_name", "member_id", "is_admin") VALUES ('officer', '$2y$10$9hXspEoAQUp9qwIqx7FWyuvx1/ROmn.vzyhEQlDoDRL9vryN8DoBy', '321223', 'officer', 'secretary', 'approved', '2026-01-17 01:55:30', 'officers@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');
INSERT INTO "users" ("username", "password_hash", "id", "role", "transparency_role", "status", "created_at", "email", "mobile", "gender", "address", "avatar", "first_name", "last_name", "member_id", "is_admin") VALUES ('katkat', '$2y$10$K60WieyP2Z6vtmw63DAwF..azLkSgKt2G9bRSCF/R4c4.OT.vrXb.', '321224', 'officer', NULL, 'pending', '2026-02-08 19:00:23', 'altheakaliego@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');
INSERT INTO "users" ("username", "password_hash", "id", "role", "transparency_role", "status", "created_at", "email", "mobile", "gender", "address", "avatar", "first_name", "last_name", "member_id", "is_admin") VALUES ('johncarl', '$2y$10$GA3I.H2dAHoL0cYc2S9afu49I00E09ZH9gi7957Iaw/NhhGqb6a/i', '321225', 'admin', NULL, 'approved', '2026-02-12 01:06:37', 'johncarlmangino7@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1');
INSERT INTO "users" ("username", "password_hash", "id", "role", "transparency_role", "status", "created_at", "email", "mobile", "gender", "address", "avatar", "first_name", "last_name", "member_id", "is_admin") VALUES ('carl', '$2y$10$lRf/vTLIcxFRiO6xFIM6gOu8ZbQYASgk6xOxiLQuSLX8dM2yzQ2oG', '321226', 'officer', NULL, 'approved', '2026-02-12 01:10:51', 'johncarlmangino17@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');
INSERT INTO "users" ("username", "password_hash", "id", "role", "transparency_role", "status", "created_at", "email", "mobile", "gender", "address", "avatar", "first_name", "last_name", "member_id", "is_admin") VALUES ('argie', '$2y$10$DvGOiIvyJK./dyStHW92gOwgQTCY5sVWrRegA/8HFanhRW5/ZT8EC', '321227', 'officer', NULL, 'approved', '2026-03-05 07:29:32', 'ARGIEPO3@GMAIL.COM', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');
INSERT INTO "users" ("username", "password_hash", "id", "role", "transparency_role", "status", "created_at", "email", "mobile", "gender", "address", "avatar", "first_name", "last_name", "member_id", "is_admin") VALUES ('klare31', '$2y$10$MZA5KAySj.kODdv57zA14e4Ls.eVN3zXzTsyRvD3K7chIm9nBbm7.', '321228', 'officer', NULL, 'pending', '2026-03-05 07:34:42', 'ARGIEPOa3@GMAIL.COM', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');

-- Table: users_archive
DROP TABLE IF EXISTS "users_archive" CASCADE;
CREATE TABLE "users_archive" (
    "archive_id" SERIAL PRIMARY KEY,
    "original_id" INTEGER DEFAULT NULL,
    "username" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "email" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "password" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "role" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "status" VARCHAR(50) DEFAULT NULL DEFAULT NULL,
    "is_admin" tinyINTEGER DEFAULT NULL,
    "created_at" TIMESTAMP DEFAULT NULL,
    "archived_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "transparency_role" VARCHAR(20) DEFAULT NULL DEFAULT NULL,
    PRIMARY KEY ("archive_id")
)

-- Table: who_we_are
DROP TABLE IF EXISTS "who_we_are" CASCADE;
CREATE TABLE "who_we_are" (
    "id" SERIAL PRIMARY KEY,
    "title" VARCHAR(255) NOT NULL NOT NULL,
    "content" TEXT NOT NULL,
    "image" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "created_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY ("id")
)

-- Insert data for who_we_are
INSERT INTO "who_we_are" ("id", "title", "content", "image", "created_at", "updated_at") VALUES ('2', 'Who we are', 'The Bankero & Fishermen Association was founded in November 2009 in Barretto, Olongapo City under the leadership of Mr. Noliboy Cocjin. Starting with around 300â€“400 members, the association has since grown and organized its members into smaller groups for more effective management.

Dedicated to supporting local boatmen and fishermen, the association serves as a vital link for their welfare and development. To strengthen communication and organizational efficiency, the association is now adopting the Bankero & Fishermen Association Management System, which will automate membership records, announcements, and event scheduling, while introducing SMS notifications for timely updates.

Through this modernization, the association continues its mission of empowering members, enhancing participation, and preserving the livelihood of the fishing community.', NULL, '2026-02-16 01:07:51', NULL);

-- Table: who_we_are_archive
DROP TABLE IF EXISTS "who_we_are_archive" CASCADE;
CREATE TABLE "who_we_are_archive" (
    "archive_id" SERIAL PRIMARY KEY,
    "original_id" INTEGER DEFAULT NULL,
    "title" VARCHAR(255) NOT NULL NOT NULL,
    "content" TEXT NOT NULL,
    "image" VARCHAR(255) DEFAULT NULL DEFAULT NULL,
    "original_created_at" TIMESTAMP DEFAULT NULL,
    "archived_at" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("archive_id")
)
