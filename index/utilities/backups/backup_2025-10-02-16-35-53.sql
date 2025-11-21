-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: bangkero_association
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `date_posted` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (9,' Fishing Tournament Announcement!','Join us this weekend for a friendly Fishing Tournament at the riverside! Cast your lines, compete for the biggest catch, and enjoy a day of fun and camaraderie. Don\'t forget your gear—see you there!\r\n\r\n','2025-06-16',NULL),(10,'Community Fishing Day','Grab your rods and join us for a relaxing Fishing Day by the lake! It’s the perfect time to unwind, bond with fellow anglers, and enjoy the great outdoors. Open to all ages—everyone’s welcome!\r\n\r\n','2025-06-16',NULL),(11,'Let’s Go Fishing!','Calling all fishing enthusiasts! Spend a peaceful day by the water and reel in some fun. Bring your bait, rod, and good vibes!\r\n\r\n','2025-06-16',NULL),(19,'Clean-Up Drive','The Association will conduct a coastal clean-up this coming Sunday at 6:00 AM. Please bring gloves, sacks, and cleaning tools','2025-06-17',NULL),(20,'Fishing Permit Renewal','Members are reminded to renew their fishing permits before the end of the month to avoid penalties.','2025-06-17',NULL),(21,'Monthly Meeting Reminder','All members are reminded that our monthly meeting will be held on Saturday, 2:00 PM at the Barangay Hall. Attendance is required.','2025-06-18',NULL);
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backups`
--

DROP TABLE IF EXISTS `backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backups`
--

LOCK TABLES `backups` WRITE;
/*!40000 ALTER TABLE `backups` DISABLE KEYS */;
/*!40000 ALTER TABLE `backups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_poster` varchar(255) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `location` text NOT NULL,
  `category` varchar(100) DEFAULT 'General',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (3,'../uploads/Screenshot_2025-04-12_133511.png','Gone Fishing 2025!',' Gone Fishing is a fun and relaxing community event that brings together fishing enthusiasts of all ages. Whether youre a seasoned angler or trying it out for the first time, this event offers a great opportunity to enjoy the outdoors, share techniques, and build camaraderie among fellow fishermen. ','2025-05-09','16:54:00','Baloy olongapo city','General'),(4,'../uploads/Screenshot_2025-04-12_145620.png','Big BAS Event (Bangkero and Fishermen Association Special Gathering)','The Big BAS Event is the annual grand gathering of the Bangkero and Fishermen Association—a celebration of unity, hard work, and community spirit.It’s a day of fun, recognition, and connection for all members and their families. Come celebrate the heart of our coastal community at the biggest event of the year!','2025-05-03','16:54:00','Subic Zambales','General'),(6,'Screenshot_2025-06-04_105423.png','Red Sea International Sport Fishing Tournament','Red Sea Int`l Sport Fishing Tournament, will be the first global tournament to host top anglers from all around the world along with local teams competing in both Trolling ','2025-06-27','12:47:00','San maracelino','General'),(12,'Screenshot_2025-08-25_221659.png','Red Sea International Sport Fishing Tournament','Everyone is expected to come','2025-08-27','10:19:00','Drift Wood Baretto Olongapo City','General'),(13,'Screenshot_2025-09-07_224712.png','1. Family-Friendly Fishing Tournament (Pine Island)','A welcoming event geared toward families, featuring casual competition, a captains meeting, food, and drinks. It&#039;s designed to be inclusive and social, perfect for anglers of all ages.','2025-09-09','22:47:00','Pine Island, Zambales','General'),(14,'Screenshot_2025-09-07_225612.png','1st Subic Bay Shore Fishing Tournament','The inaugural shore-fishing competition in Subic Bay, spotlighting responsible angling and marine conservation. Organized by Fish’n Town with the support of the Subic Bay Metropolitan Authority and local sponsors, it blends sport with sustainable tourism and community engagement.','2025-09-16','14:55:00',' San Bernardino Fishing Site, Subic Bay Freeport Zone, Zambales','General'),(16,'1757571370_29d28442-8efd-4d61-8164-45cfd342a2a7.jpg','Red Sea International Sport Fishing Tournament','erkej fnekw dfwkmw ewr werw m','2025-09-17','01:19:00','Castillejos Zambales','General'),(17,'1757586688_0e320bcc-941d-4276-a8b0-c89a1408b719.jpg','Red Sea International Sport Fishing Tournament','kjdkjgeorfoe','2027-04-16','01:26:00','Baloy olongapo city','General'),(18,'97f300eb-0157-46c6-923a-dc6dc5795167.jpg','Red Sea International Sport Fishing Tournament','The inaugural shore-fishing competition in Subic Bay is a highly anticipated gathering that promises to unite fishing enthusiasts, families, tourists, and local communities for a day of excitement, camaraderie, and outdoor adventure. This one-of-a-kind event goes beyond the thrill of the catch by promoting responsible and sustainable angling practices, ensuring that participants interact with the marine environment in a way that preserves its delicate ecosystem. By following carefully designed guidelines, anglers of all levels can enjoy the challenge of the sport while contributing to the protection of Subic Bay’s rich biodiversity, which is home to numerous fish species and other aquatic life. The competition also offers educational components, workshops, and hands-on demonstrations to encourage participants to adopt eco-friendly fishing methods and raise awareness about marine conservation.\r\n\r\nOrganized by Fish’n Town, with the strong support of the Subic Bay Metropolitan Authority and generous contributions from local sponsors, this event seamlessly blends sport, sustainability, and community engagement. It highlights the importance of promoting tourism that is environmentally responsible while celebrating local culture and traditions. Attendees can expect a range of activities designed to entertain and inform, from guided fishing sessions and expert tips from seasoned anglers to interactive booths and displays focused on protecting the marine ecosystem. More than just a competition, the event fosters a sense of community, encouraging participants to forge new friendships, share knowledge, and actively contribute to preserving the beauty and abundance of Subic Bay for future generations. By combining sport, education, and conservation, this inaugural event sets a strong example for how recreation and environmental stewardship can go hand in hand.','2026-08-14','18:56:00','San maracelino','Livelihood');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `galleries`
--

DROP TABLE IF EXISTS `galleries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `galleries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `images` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `galleries`
--

LOCK TABLES `galleries` WRITE;
/*!40000 ALTER TABLE `galleries` DISABLE KEYS */;
INSERT INTO `galleries` VALUES (5,'Sea Turtle Release','1758469902_9bf0e322-ea5c-4941-84f0-f645bfa9c7bb.jpg,1758469902_d4939b6f-de6c-4b02-a826-11cbfe7c1497.jpg,1758469902_f71ca372-aa04-43ae-81cb-2d12ce016654.jpg,1758469902_8dd190e1-1a9a-42be-a465-172fb537404f.jpg,1758469902_eff0de09-5039-4641-a014-54eea82c6bbf.jpg,1758469902_44665509-61da-4617-aa21-80c215f97085.jpg','2025-09-21 15:51:42'),(6,'Artificial Reef Deployment','1758470061_97f300eb-0157-46c6-923a-dc6dc5795167.jpg,1758470061_2253112b-10d0-486d-9097-246ede3c29b0.jpg,1758470061_708053de-3c21-4d57-b87c-cdd1e8303dbc.jpg,1758470061_3cbecd59-36f6-4fd3-a7a1-c12f3c9a5612.jpg,1758470061_e0004861-b5d2-4313-a5b8-bed32b928f33.jpg','2025-09-21 15:54:21');
/*!40000 ALTER TABLE `galleries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_archive`
--

DROP TABLE IF EXISTS `member_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_archive` (
  `member_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `archived_at` datetime NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `membership_status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `dob` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `work_type` enum('Fisherman','Bangkero','Both') DEFAULT NULL,
  `license_number` varchar(50) NOT NULL,
  `boat_name` varchar(255) DEFAULT NULL,
  `fishing_area` varchar(255) DEFAULT NULL,
  `emergency_name` varchar(255) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `agreement` tinyint(1) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `pref_notifications` tinyint(1) DEFAULT 1,
  `pref_language` varchar(20) DEFAULT 'English',
  `pref_theme` varchar(20) DEFAULT 'Light',
  `theme` varchar(20) DEFAULT 'light',
  `language` varchar(20) DEFAULT 'english',
  PRIMARY KEY (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_archive`
--

LOCK TABLES `member_archive` WRITE;
/*!40000 ALTER TABLE `member_archive` DISABLE KEYS */;
INSERT INTO `member_archive` VALUES (57,'Roberto Callua Brice','robertocalluag@gmail.com','09876543456789','2025-09-11 14:34:31',NULL,'active','2025-09-11 06:34:31',NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,0,NULL,NULL,1,'English','Light','light','english'),(66,'Jerry John Britannia','dkvodsfwefjwscd@gmail.com','09876567890','2025-09-11 14:06:08',NULL,'active','2025-09-11 06:06:08',NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,0,NULL,NULL,1,'English','Light','light','english');
/*!40000 ALTER TABLE `member_archive` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_archives`
--

DROP TABLE IF EXISTS `member_archives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_archives` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `archived_at` datetime DEFAULT current_timestamp(),
  `type` varchar(50) DEFAULT 'Other',
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_archives`
--

LOCK TABLES `member_archives` WRITE;
/*!40000 ALTER TABLE `member_archives` DISABLE KEYS */;
INSERT INTO `member_archives` VALUES (1,33,'Sample Archive','This is a test archive item.','2025-06-17 15:14:59','Other'),(2,33,'Profile Updated','You updated your profile information.','2025-06-18 09:26:07','Profile Update'),(3,33,'Profile Updated','You updated your profile information.','2025-06-18 09:26:39','Profile Update'),(4,33,'Profile Updated','You updated your profile information.','2025-06-18 09:27:02','Profile Update'),(5,33,'Profile Updated','You updated your profile information.','2025-06-18 09:29:28','Profile Update'),(6,33,'Profile Updated','You updated your profile information.','2025-06-18 09:29:36','Profile Update'),(7,33,'Logged In','Logged in at Jun 18, 2025 03:36 AM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-18 09:36:54','Login'),(8,33,'Logged In','Logged in at Jun 18, 2025 03:37 AM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-18 09:37:42','Login'),(9,33,'Logged In','Logged in at Jun 18, 2025 03:50 AM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-18 09:50:27','Login'),(10,33,'Logged In','Logged in at Jun 18, 2025 09:53 AM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-18 09:53:13','Login'),(11,33,'Profile Updated','You updated your profile information.','2025-06-18 10:09:38','Profile Update'),(12,33,'Profile Updated','You updated your profile information.','2025-06-18 10:09:45','Profile Update'),(13,33,'Profile Updated','You updated your profile information.','2025-06-18 10:12:14','Profile Update'),(14,33,'Profile Updated','You updated your profile information.','2025-06-18 10:15:32','Profile Update'),(15,33,'Changed Password','Password changed on Jun 18, 2025 04:24 AM','2025-06-18 10:24:34','Changed Password'),(16,33,'Logged In','Logged in at Jun 18, 2025 10:24 AM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-18 10:24:49','Login'),(17,33,'Logged In','Logged in at Jun 18, 2025 11:01 AM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-18 11:01:46','Login'),(18,33,'Profile Updated','You updated your profile information.','2025-06-18 11:02:17','Profile Update'),(19,33,'Profile Updated','You updated your profile information.','2025-06-18 11:02:27','Profile Update'),(20,33,'Logged In','Logged in at Jun 18, 2025 01:29 PM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-18 13:29:13','Login'),(21,33,'Changed Password','Password changed on Jun 18, 2025 07:29 AM','2025-06-18 13:29:32','Changed Password'),(22,33,'Logged In','Logged in at Jun 18, 2025 01:29 PM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-18 13:29:54','Login'),(23,33,'Logged In','Logged in at Jun 18, 2025 05:29 PM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-18 17:29:40','Login'),(24,33,'Profile Updated','You updated your profile information.','2025-06-18 17:31:25','Profile Update'),(25,33,'Profile Updated','You updated your profile information.','2025-06-18 17:31:29','Profile Update'),(26,33,'Logged In','Logged in at Jun 25, 2025 01:29 PM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-25 13:29:40','Login'),(27,33,'Logged In','Logged in at Jun 25, 2025 01:30 PM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-25 13:30:19','Login'),(28,33,'Logged In','Logged in at Jun 25, 2025 02:19 PM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-25 14:19:25','Login'),(29,33,'Logged In','Logged in at Jun 25, 2025 02:43 PM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-25 14:43:49','Login'),(30,34,'Logged In','Logged in at Jun 25, 2025 02:45 PM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-25 14:45:51','Login'),(31,34,'Logged In','Logged in at Jun 25, 2025 02:50 PM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-25 14:50:36','Login'),(32,33,'Logged In','Logged in at Jun 25, 2025 02:51 PM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-25 14:51:29','Login'),(33,35,'Logged In','Logged in at Jun 25, 2025 02:53 PM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-25 14:53:12','Login'),(34,35,'Logged In','Logged in at Jun 25, 2025 02:55 PM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-25 14:55:50','Login'),(35,35,'Profile Updated','You updated your profile information.','2025-06-25 15:11:59','Profile Update'),(36,35,'Profile Updated','You updated your profile information.','2025-06-25 15:12:03','Profile Update'),(37,35,'Changed Password','Password changed on Jun 25, 2025 09:16 AM','2025-06-25 15:16:22','Changed Password'),(38,35,'Preferences Updated','You updated your preferences.','2025-06-25 15:16:38','Preferences'),(39,35,'Preferences Updated','You updated your preferences.','2025-06-25 15:16:43','Preferences'),(40,35,'Preferences Updated','You updated your preferences.','2025-06-25 15:19:54','Preferences'),(41,35,'Preferences Updated','You updated your preferences.','2025-06-25 15:20:06','Preferences'),(42,35,'Preferences Updated','You updated your preferences.','2025-06-25 15:20:17','Preferences'),(43,35,'Preferences Updated','You updated your preferences.','2025-06-25 15:21:29','Preferences'),(44,35,'Preferences Updated','You updated your preferences.','2025-06-25 15:21:36','Preferences'),(45,35,'Preferences Updated','You updated your preferences.','2025-06-25 15:23:45','Preferences'),(46,35,'Preferences Updated','You updated your preferences.','2025-06-25 15:27:06','Preferences'),(47,35,'Preferences Updated','You updated your preferences.','2025-06-25 15:34:59','Preferences'),(48,35,'Preferences Updated','You updated your preferences.','2025-06-25 15:35:07','Preferences'),(49,35,'Preferences Updated','You updated your preferences.','2025-06-25 15:35:24','Preferences'),(50,35,'Logged In','Logged in at Jun 25, 2025 03:35 PM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-25 15:35:40','Login'),(51,35,'Na-update ang mga Kagustuhan','In-update mo ang iyong mga kagustuhan.','2025-06-25 15:35:57','Preferences'),(52,35,'Preferences Updated','You updated your preferences.','2025-06-25 15:36:00','Preferences'),(53,33,'Logged In','Logged in at Jun 26, 2025 10:31 AM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-26 10:31:14','Login'),(54,33,'Na-update ang mga Kagustuhan','In-update mo ang iyong mga kagustuhan.','2025-06-26 10:33:10','Preferences'),(55,33,'Na-update ang mga Kagustuhan','In-update mo ang iyong mga kagustuhan.','2025-06-26 10:33:19','Preferences'),(56,33,'Preferences Updated','You updated your preferences.','2025-06-26 10:33:24','Preferences'),(57,33,'Preferences Updated','You updated your preferences.','2025-06-26 10:34:13','Preferences'),(58,33,'Logged In','Logged in at Jun 26, 2025 10:49 AM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-26 10:49:11','Login'),(59,33,'Changed Password','Password changed on Jun 26, 2025 04:49 AM','2025-06-26 10:49:36','Changed Password'),(60,33,'Logged In','Logged in at Jun 26, 2025 10:51 AM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-26 10:51:51','Login'),(61,33,'Logged In','Logged in at Jun 26, 2025 02:30 PM from device: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36, IP: ::1','2025-06-26 14:30:01','Login');
/*!40000 ALTER TABLE `member_archives` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_details`
--

DROP TABLE IF EXISTS `member_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `work_type` enum('Fisherman','Bangkero','Both') DEFAULT NULL,
  `experience` int(11) DEFAULT 0,
  `license_number` varchar(50) DEFAULT NULL,
  `boat_ownership` enum('Yes','No') DEFAULT NULL,
  `boat_name` varchar(255) DEFAULT NULL,
  `fishing_area` varchar(255) DEFAULT NULL,
  `emergency_name` varchar(255) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `health_concerns` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `agreement` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `member_details_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_details`
--

LOCK TABLES `member_details` WRITE;
/*!40000 ALTER TABLE `member_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `membership_status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `dob` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `work_type` enum('Fisherman','Bangkero','Both') DEFAULT NULL,
  `license_number` varchar(50) NOT NULL,
  `boat_name` varchar(255) DEFAULT NULL,
  `fishing_area` varchar(255) DEFAULT NULL,
  `emergency_name` varchar(255) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `agreement` tinyint(1) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `pref_notifications` tinyint(1) DEFAULT 1,
  `pref_language` varchar(20) DEFAULT 'English',
  `pref_theme` varchar(20) DEFAULT 'Light',
  `theme` varchar(20) DEFAULT 'light',
  `language` varchar(20) DEFAULT 'english',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `members`
--

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
INSERT INTO `members` VALUES (43,'Jovelyn  S.','jovelybuena2@gmail.com','09100176413','','active','2025-09-10 18:09:05',NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,0,NULL,NULL,1,'English','Light','light','english'),(62,'Jose Antonio M.','joseantonio@gmail.com','09886656554433','Calapacuan, Subic Zambales','active','2025-09-11 06:02:06','2025-09-09','Male','Fisherman','','','','','',1,'member_68c267a475cf20.82725123_thelightinthisisinsanity_photography.jpg',NULL,1,'English','Light','light','english'),(64,'Jovelyn S Buena','9898jknjk@gmail.com','098765434567','Baretto, Olongapo City','active','2025-09-11 06:02:33','2025-09-23','Male','Fisherman','','','','','',0,'default_member.png',NULL,1,'English','Light','light','english'),(67,'Argie  B. Berena','argieberena@gmail.com','098786765777','Bulacan','active','2025-09-19 04:17:39','2025-09-16','Male','Fisherman','9809','argie','bulacan','dkjfdfsii','098789',1,'member_68ccd963555564.58457445_Screenshot2025-03-07135115.png','argie',1,'English','Light','light','english'),(68,'dfgdg dfdgdf dfdfd','hgfdsfgvbn@gmail.com','0987654','ghfds','active','2025-09-19 04:39:51','2025-09-10','Female','Fisherman','453452','dfsa','fds','gfds','hgfd',1,'member_68ccde97014c53.48554030_Screenshot2024-04-05214617.png','sdfsd',1,'English','Light','light','english');
/*!40000 ALTER TABLE `members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `officer_roles`
--

DROP TABLE IF EXISTS `officer_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `officer_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `officer_roles`
--

LOCK TABLES `officer_roles` WRITE;
/*!40000 ALTER TABLE `officer_roles` DISABLE KEYS */;
INSERT INTO `officer_roles` VALUES (1,'President',NULL),(2,'Vice President',NULL),(3,'Secretary',NULL),(4,'Treasurer',NULL),(5,'Board of Director',NULL);
/*!40000 ALTER TABLE `officer_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `officers`
--

DROP TABLE IF EXISTS `officers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  KEY `fk_role` (`role_id`),
  CONSTRAINT `fk_role` FOREIGN KEY (`role_id`) REFERENCES `officer_roles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `officers_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `officers`
--

LOCK TABLES `officers` WRITE;
/*!40000 ALTER TABLE `officers` DISABLE KEYS */;
INSERT INTO `officers` VALUES (33,62,'','2023-06-12','2025-11-27','1757571148_Screenshot 2025-09-08 003235.png',1,NULL),(35,64,'','2025-09-08','2025-10-11','1757586797_Screenshot 2025-09-08 003004.png',2,NULL),(36,67,'Secretary','2025-09-08','2025-09-24','1758256680_Screenshot 2024-07-27 113641.png',NULL,NULL),(37,68,'Treasurer','2025-09-24','2025-09-09','1758256852_Screenshot 2024-04-05 214617.png',NULL,NULL),(38,68,'Secretary','2025-09-18','2025-09-10','1758257346_background.png',NULL,NULL),(39,62,'President','2025-09-18','2025-09-10','1758257402_Screenshot 2024-04-15 220726.png',NULL,NULL),(40,68,'President','2025-09-23','2025-09-17','1758258154_background.png',NULL,'fdgdf'),(41,62,'President','2025-09-16','2025-09-23','1758258737_background.png',NULL,'fgfgfg'),(43,67,'','2025-09-23','2025-09-10','1758266934_background.png',3,'gffghfghfg');
/*!40000 ALTER TABLE `officers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_name` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `username` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` enum('admin','member','officer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email` varchar(255) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `username_2` (`username`),
  UNIQUE KEY `email_2` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=321211 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('user','user',233,'member','2025-04-12 04:32:32','user@gmail.com',NULL),('joana','$2y$10$5pQzQlH82zbPu',234,'member','2025-04-11 22:45:56','althea.ksaliegowewew@gmail.com',NULL),('kim','$2y$10$0Z0hjj1VKebnA',235,'member','2025-04-11 22:50:45','kimberly22@gmail.com',21),('admin','admin',321192,'admin','2025-04-12 04:58:15','admin2gmail.com',NULL),('joycielyn','$2y$10$gTItK3xQN/EXA',321193,'member','2025-04-21 04:33:07','joycielynjimenez@gmail.com',22),('carl','$2y$10$24CdkYL95xSsT',321194,'member','2025-04-22 23:00:31','johncarlmangino2@gmail.com',23),('root','$2y$10$mnUEq.XrIvGzt',321195,'member','2025-04-22 23:10:41','jovelybuena122@gmail.com',24),('jove','$2y$10$EbZCqXA22g7qR',321196,'member','2025-04-22 23:13:56','jovelybuena12@gmail.com',25),('jovelyn','$2y$10$O.4LwKsCFsJRL',321197,'member','2025-04-22 23:24:46','jvdsdcds2@gmail.com',26),('cath','$2y$10$.i8ZU8tMLk3m/zZlq2Bt1ORI8utA9GvVJc/Agvie8nYDhFhV1kOXm',321198,'member','2025-04-23 04:24:33','jovelynss@gmail.com',27),('nico','$2y$10$7P86ib0IVqG.hd4846yRkOa021.hPLDOZ3IWN5CfyBieNXwLxIdS6',321199,'member','2025-06-15 21:09:05','nico@gmail.com',28),('alpha','$2y$10$Oa4c2RXTCNOIJtRNl0gr.umqG/OyXR5j6/B/zmtpEKPd49TRXC7em',321200,'member','2025-06-15 23:45:05','alpha@gmail.com',29),('marian','$2y$10$YbvLlvslO2wB10cXxxkCOOXzTNcnGTCQmrHoS9uPL.S6oHwDj9Px2',321201,'member','2025-06-16 00:19:55','marianrivera@gmail.com',31),('jovi','$2y$10$u2mf64KenxnsXk7UqNjm6OIM7TqhBMCxQOb8ZcQf687slUaV7m1OW',321202,'member','2025-06-16 00:24:11','jovelynbuena3312@gmail.com',32),('kifi','$2y$10$iHdkDR90QELw3ZU/yrGurug6FLDlCaGmuEo.N.MOSRKfiuoe5UAam',321203,'member','2025-06-16 20:38:07','9898jknjk@gmail.com',33),('jerry','$2y$10$VjW9QaHdwS72LAOHEJhU5udDf5o02WRz.9YwXB1ak0tWdhzZvcJPK',321204,'member','2025-06-25 00:45:36','dkvodsfwefjwscd@gmail.com',34),('fix','$2y$10$sP9dHCRqxePfL9fsyldWuO6FzBzNxZeW7YexYL1YhYpqNstHpP45.',321205,'member','2025-06-25 00:52:56','bnjdksmfs@gmail.com',35),('lynne','$2y$10$J2rphPJKHLkLIHRW4OymXuYHz0HtUHhkILVDHHJPUIX9TxAD8TCQO',321207,'member','2025-09-07 11:05:08','jovelybuena2@gmail.com',37),('jose','$2y$10$3tANWR4ESMbYJENu0ZkysulnKOIHLI0AMXm6bbg7ibdkKClfoBtYi',321208,'member','2025-09-10 11:44:27','joseantonio@gmail.com',40),('argie','$2y$10$UALDyhmBTVF3YksiNFo.Se1sAJWZYRxwPkkokxFRdkLs9BLw3RPU2',321209,'member','2025-09-18 22:17:39','argieberena@gmail.com',67),('sdfsd','$2y$10$1WaozbYFv3JnMQ2xUhM3T.dx3h9o.r9Yoi5RqrsU8ZgerlMFdKig2',321210,'member','2025-09-18 22:39:51','hgfdsfgvbn@gmail.com',68);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-02 22:35:53
