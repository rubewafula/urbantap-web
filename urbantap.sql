-- MySQL dump 10.13  Distrib 5.7.11, for Linux (x86_64)
--
-- Host: localhost    Database: urbantap
-- ------------------------------------------------------
-- Server version	5.7.11-0ubuntu6

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `provider_services_id` int(10) unsigned NOT NULL,
  `service_provider_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `status` enum('BOOKED','ACCEPTED','CANCELLED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BOOKED',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `appointments_provider_services_id_foreign` (`provider_services_id`),
  KEY `appointments_service_provider_id_foreign` (`service_provider_id`),
  KEY `appointments_customer_id_foreign` (`customer_id`),
  CONSTRAINT `appointments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_provider_services_id_foreign` FOREIGN KEY (`provider_services_id`) REFERENCES `provider_services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_service_provider_id_foreign` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `balances`
--

DROP TABLE IF EXISTS `balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `balances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `balance` double(10,2) NOT NULL,
  `bonus` double(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `balances`
--

LOCK TABLES `balances` WRITE;
/*!40000 ALTER TABLE `balances` DISABLE KEYS */;
/*!40000 ALTER TABLE `balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_trails`
--

DROP TABLE IF EXISTS `booking_trails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `booking_trails` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `status_id` int(11) NOT NULL,
  `description` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `originator` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_trails_bookings_id_fk1` (`booking_id`),
  CONSTRAINT `booking_trails_bookings_id_fk1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_trails`
--

LOCK TABLES `booking_trails` WRITE;
/*!40000 ALTER TABLE `booking_trails` DISABLE KEYS */;
INSERT INTO `booking_trails` VALUES (1,3,1,'Nothing much a rename','','2019-02-14 21:39:49','2019-02-14 21:39:49'),(2,3,7,'Testing cancel booking','USER','2019-02-16 18:28:58','2019-02-16 18:28:58');
/*!40000 ALTER TABLE `booking_trails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `provider_service_id` int(10) unsigned NOT NULL,
  `service_provider_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `booking_time` datetime NOT NULL,
  `booking_duration` int(10) NOT NULL,
  `expiry_time` datetime DEFAULT NULL,
  `status_id` int(11) NOT NULL,
  `booking_type` enum('USER LOCATION','PROVIDER LOCATION') COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `location` json DEFAULT NULL COMMENT '{"name":"lukume","lat":32.080,"lng":56.93}',
  PRIMARY KEY (`id`),
  KEY `booking_user_id_fk1` (`user_id`),
  KEY `booking_service_provider_id_fk1` (`service_provider_id`),
  KEY `booking_provider_service_id_fk1` (`provider_service_id`),
  CONSTRAINT `booking_provider_service_id_fk1` FOREIGN KEY (`provider_service_id`) REFERENCES `provider_services` (`id`),
  CONSTRAINT `booking_service_provider_id_fk1` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`),
  CONSTRAINT `booking_user_id_fk1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (3,2,3,2,'2019-02-15 12:30:00',45,'2019-02-15 13:20:00',7,'USER LOCATION',NULL,'2019-02-14 21:29:31','2019-02-14 21:29:31',NULL),(4,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION','2019-02-16 01:14:39','2019-02-15 22:14:39','2019-02-15 22:14:39','{\"name\": \"Kasarani\"}'),(5,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION','2019-02-16 19:08:34','2019-02-16 16:08:34','2019-02-16 16:08:34','{\"name\": \"Kasarani\"}'),(6,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION','2019-02-16 19:08:43','2019-02-16 16:08:43','2019-02-16 16:08:43','{\"name\": \"Kasarani\"}'),(7,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION','2019-02-16 19:08:48','2019-02-16 16:08:48','2019-02-16 16:08:48','{\"name\": \"Kasarani\"}'),(8,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION','2019-02-16 19:12:11','2019-02-16 16:12:11','2019-02-16 16:12:11','{\"name\": \"Kasarani\"}'),(9,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION','2019-02-16 19:12:52','2019-02-16 16:12:52','2019-02-16 16:12:52','{\"name\": \"Kasarani\"}');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `businesses`
--

DROP TABLE IF EXISTS `businesses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `businesses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_provider_id` int(10) unsigned NOT NULL,
  `business_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lng` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `facebook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `businesses_service_provider_id_foreign` (`service_provider_id`),
  CONSTRAINT `businesses_service_provider_id_foreign` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `businesses`
--

LOCK TABLES `businesses` WRITE;
/*!40000 ALTER TABLE `businesses` DISABLE KEYS */;
/*!40000 ALTER TABLE `businesses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_id` int(10) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Salon',0,'2019-01-04 18:26:34','2019-01-04 18:26:34'),(2,'Massage',0,'2019-01-04 18:26:34','2019-01-04 18:26:34'),(3,'shoes',0,'2019-01-26 20:07:20','2019-01-26 20:07:20'),(4,'under 23 shoes',0,'2019-01-26 20:13:15','2019-01-26 20:13:15'),(5,'Media Professionals',0,'2019-01-26 21:08:06','2019-01-26 21:08:06');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cost_parameters`
--

DROP TABLE IF EXISTS `cost_parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cost_parameters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `weight` double(10,2) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cost_parameters`
--

LOCK TABLES `cost_parameters` WRITE;
/*!40000 ALTER TABLE `cost_parameters` DISABLE KEYS */;
/*!40000 ALTER TABLE `cost_parameters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inboxes`
--

DROP TABLE IF EXISTS `inboxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inboxes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `msisdn` mediumint(9) NOT NULL,
  `network` enum('SAFARICOM','AIRTEL','TELKOM','EQUITEL','ORANGE','JTL') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_id` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inboxes`
--

LOCK TABLES `inboxes` WRITE;
/*!40000 ALTER TABLE `inboxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `inboxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(8,'2018_09_30_123658_create_service_providers_table',1),(9,'2018_10_01_114758_create_categories_table',1),(10,'2018_10_01_143625_create_user_groups_table',1),(11,'2018_10_01_143747_create_permissions_table',1),(12,'2018_10_01_143821_create_user_permissions_table',1),(13,'2018_10_01_144237_create_businesses_table',1),(14,'2018_10_01_145014_create_operating_hours_table',1),(15,'2018_10_01_145235_create_services_table',1),(16,'2018_10_02_123847_create_service_provider_images_table',1),(17,'2018_10_02_124129_create_provider_services_table',1),(22,'2018_11_01_092645_create_appointments_table',1),(102,'2019_01_07_185249_create_status_categories_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mpesa_transactions`
--

DROP TABLE IF EXISTS `mpesa_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mpesa_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `msisdn` int(11) NOT NULL,
  `transaction_time` datetime NOT NULL,
  `message` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_no` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mpesa_code` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double(10,2) NOT NULL,
  `names` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paybill_no` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpesa_transactions`
--

LOCK TABLES `mpesa_transactions` WRITE;
/*!40000 ALTER TABLE `mpesa_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `mpesa_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operating_hours`
--

DROP TABLE IF EXISTS `operating_hours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operating_hours` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_provider_id` int(10) unsigned NOT NULL,
  `service_day` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_from` time NOT NULL,
  `time_to` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status_id` int(10) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `operating_hours_service_provider_id_fk1` (`service_provider_id`),
  CONSTRAINT `operating_hours_service_provider_id_fk1` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`),
  CONSTRAINT `operating_hours_service_provider_id_foreign` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operating_hours`
--

LOCK TABLES `operating_hours` WRITE;
/*!40000 ALTER TABLE `operating_hours` DISABLE KEYS */;
INSERT INTO `operating_hours` VALUES (2,2,'Monday','09:00:00','14:00:00','2019-02-10 16:02:25','2019-02-10 16:02:25',1),(3,2,'Tuesday','09:00:00','15:00:00','2019-02-10 18:08:53','2019-02-10 18:08:53',3),(4,2,'Saturday','09:00:00','17:00:00','2019-02-16 16:04:20','2019-02-16 16:04:20',1);
/*!40000 ALTER TABLE `operating_hours` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `outboxes`
--

DROP TABLE IF EXISTS `outboxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `outboxes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `msisdn` mediumint(9) NOT NULL,
  `network` enum('SAFARICOM','AIRTEL','TELKOM','EQUITEL','ORANGE','JTL') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_id` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `outboxes`
--

LOCK TABLES `outboxes` WRITE;
/*!40000 ALTER TABLE `outboxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `outboxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_received` datetime NOT NULL,
  `booking_id` int(11) NOT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paid_by_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paid_by_msisdn` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double(10,2) NOT NULL,
  `received_payment` double(10,2) NOT NULL,
  `balance` double(10,2) NOT NULL,
  `status_id` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,'MQUIQ7672U','2019-02-15 00:42:40',3,'MPESA','Reuben Wafula','254726986944',1000.00,1000.00,200.00,1,NULL,'2019-02-14 21:42:40','2019-02-14 21:42:40');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'Manage Users','CRUD Users','2019-01-04 18:26:34','2019-01-04 18:26:34'),(2,'View Dashboard','View Dashboard analytics','2019-01-04 18:26:34','2019-01-04 18:26:34');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portfolios`
--

DROP TABLE IF EXISTS `portfolios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portfolios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_provider_id` int(10) unsigned NOT NULL,
  `media_data` json DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_id` int(11) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `portfolios_service_provider_id_fk_1` (`service_provider_id`),
  CONSTRAINT `portfolios_service_provider_id_fk_1` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`),
  CONSTRAINT `portfolios_service_providers_fk` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portfolios`
--

LOCK TABLES `portfolios` WRITE;
/*!40000 ALTER TABLE `portfolios` DISABLE KEYS */;
INSERT INTO `portfolios` VALUES (1,2,'{\"name\": \"\", \"size\": 8667, \"type\": \"image\", \"extension\": \"jpeg\", \"media_url\": \"public/image/.jpeg\"}','Grooming on another level. Well beyond zero',3,'2019-02-10 13:23:34','2019-02-10 13:19:46','2019-02-10 13:19:46'),(2,2,'{\"name\": \"\", \"size\": 8667, \"type\": \"image\", \"extension\": \"jpeg\", \"media_url\": \"public/image/.jpeg\"}','Grooming on another level. Well beyond zero',1,'2019-02-10 13:27:23','2019-02-10 13:27:23','2019-02-10 13:27:23');
/*!40000 ALTER TABLE `portfolios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provider_categories`
--

DROP TABLE IF EXISTS `provider_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provider_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_provider_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `status_id` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `provider_categories_service_providers_fk` (`service_provider_id`),
  KEY `provider_categories_categories_fk` (`category_id`),
  CONSTRAINT `provider_categories_categories_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `provider_categories_service_providers_fk` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provider_categories`
--

LOCK TABLES `provider_categories` WRITE;
/*!40000 ALTER TABLE `provider_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `provider_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provider_services`
--

DROP TABLE IF EXISTS `provider_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provider_services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_provider_id` int(10) unsigned NOT NULL,
  `service_id` int(10) unsigned NOT NULL,
  `description` varchar(600) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost` float(10,2) NOT NULL,
  `duration` int(10) NOT NULL COMMENT 'Duration in minutes',
  `rating` float(10,2) DEFAULT '0.01',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status_id` int(10) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `provicder_services_service_id_fk_k` (`service_id`),
  KEY `provicder_services_service_provider_id_fk_k` (`service_provider_id`),
  CONSTRAINT `provicder_services_service_id_fk_k` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  CONSTRAINT `provicder_services_service_provider_id_fk_k` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`),
  CONSTRAINT `provider_services_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `provider_services_service_provider_id_foreign` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provider_services`
--

LOCK TABLES `provider_services` WRITE;
/*!40000 ALTER TABLE `provider_services` DISABLE KEYS */;
INSERT INTO `provider_services` VALUES (1,2,1,'Cut wam service 23',1000.00,45,0.00,'2019-02-10 14:15:32','2019-02-10 14:15:32',3),(2,2,1,'Cut wam service 23',1000.00,45,0.01,'2019-02-10 17:03:47','2019-02-10 17:03:47',1);
/*!40000 ALTER TABLE `provider_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `service_provider_id` int(10) unsigned NOT NULL,
  `provider_service_id` int(10) unsigned NOT NULL,
  `rating` int(11) NOT NULL,
  `review` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_id` int(11) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `review_user_id_fk_1` (`user_id`),
  KEY `review_service_provider_id_fk_1` (`service_provider_id`),
  KEY `review_provider_services_id_fk_1` (`provider_service_id`),
  CONSTRAINT `review_provider_services_id_fk_1` FOREIGN KEY (`provider_service_id`) REFERENCES `provider_services` (`id`),
  CONSTRAINT `review_service_provider_id_fk_1` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`),
  CONSTRAINT `review_user_id_fk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `reviews_service_providers_fk` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_users_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,2,2,1,1,'Tender care service, quite beautiful',3,'2019-02-10 14:59:07','2019-02-10 14:22:32','2019-02-10 14:22:32'),(2,2,2,1,1,'Mwaa, amawesmake tena noma sana',1,'2019-02-10 14:41:10','2019-02-10 14:41:10','2019-02-10 14:41:10');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_costs`
--

DROP TABLE IF EXISTS `service_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_costs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `base_cost` double(10,2) NOT NULL,
  `status_id` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_costs`
--

LOCK TABLES `service_costs` WRITE;
/*!40000 ALTER TABLE `service_costs` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_costs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_package_details`
--

DROP TABLE IF EXISTS `service_package_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_package_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_package_id` int(10) NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `media_data` json DEFAULT NULL COMMENT 'sample {"media_url":"...", "media_type":"[video|audio|image]", "size":"xMB"}',
  `status_id` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_package_details`
--

LOCK TABLES `service_package_details` WRITE;
/*!40000 ALTER TABLE `service_package_details` DISABLE KEYS */;
INSERT INTO `service_package_details` VALUES (1,1,'Some coool service you will enjoy 23','null',3,NULL,'2019-01-27 15:35:13','2019-01-27 15:35:13'),(2,1,'Wholistic head attention',NULL,1,'2019-01-27 17:13:19','2019-01-27 17:13:19','2019-01-27 17:13:19'),(3,1,'Wholistic head attention',NULL,1,'2019-01-27 17:18:32','2019-01-27 17:18:32','2019-01-27 17:18:32'),(4,1,'Some+coool+service+you_will_enjoy','{\"name\": null, \"size\": 8667, \"type\": \"image\", \"extension\": \"jpeg\", \"media_url\": \"public/image/.jpeg\"}',1,'2019-01-27 17:28:28','2019-01-27 17:28:28','2019-01-27 17:28:28'),(5,1,'Some+coool+service+you_will_enjoy','{\"name\": null, \"size\": 8667, \"type\": \"image\", \"extension\": \"jpeg\", \"media_url\": \"public/image/b.jpeg.jpeg\"}',1,'2019-01-27 17:35:43','2019-01-27 17:35:43','2019-01-27 17:35:43'),(6,1,'Some coool service you_will_enjoy','{\"name\": \"Some-coool-service-you-will-enjoy\", \"size\": 8667, \"type\": \"image\", \"extension\": \"jpeg\", \"media_url\": \"public/image/Some-coool-service-you-will-enjoy.jpeg\"}',1,'2019-01-27 17:43:56','2019-01-27 17:43:56','2019-01-27 17:43:56'),(7,1,'Some+coool+service+you_will_enjoy','{\"name\": \"Some-coool-service-you-will-enjoy\", \"size\": 8667, \"type\": \"image\", \"extension\": \"jpeg\", \"media_url\": \"public/image/Some-coool-service-you-will-enjoy.jpeg\"}',1,'2019-01-27 18:02:59','2019-01-27 18:02:59','2019-01-27 18:02:59');
/*!40000 ALTER TABLE `service_package_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_packages`
--

DROP TABLE IF EXISTS `service_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_packages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `package_name` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_id` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_packages`
--

LOCK TABLES `service_packages` WRITE;
/*!40000 ALTER TABLE `service_packages` DISABLE KEYS */;
INSERT INTO `service_packages` VALUES (1,1,'Golden Ladies Salon 23','Best salon jab for the old',1,'2019-01-26 21:47:34','2019-01-26 21:47:34','2019-01-26 21:47:34'),(2,1,'Golden PAP','Best salon jab for the old',1,'2019-01-26 22:27:25','2019-01-26 22:27:25','2019-01-26 22:27:25');
/*!40000 ALTER TABLE `service_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_provider_images`
--

DROP TABLE IF EXISTS `service_provider_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_provider_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_provider_id` int(10) unsigned NOT NULL,
  `image` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_provider_images_service_provider_id_foreign` (`service_provider_id`),
  CONSTRAINT `service_provider_images_service_provider_id_foreign` FOREIGN KEY (`service_provider_id`) REFERENCES `service_providers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_provider_images`
--

LOCK TABLES `service_provider_images` WRITE;
/*!40000 ALTER TABLE `service_provider_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_provider_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_providers`
--

DROP TABLE IF EXISTS `service_providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_providers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `type` smallint(6) NOT NULL DEFAULT '1',
  `service_provider_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_description` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
  `work_location` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_lat` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_lng` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_id` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `overall_rating` float(10,2) DEFAULT '0.00',
  `overall_dislikes` int(11) DEFAULT '0',
  `overall_likes` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_service_providers_fk-1` (`user_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `user_service_providers_fk-1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_providers`
--

LOCK TABLES `service_providers` WRITE;
/*!40000 ALTER TABLE `service_providers` DISABLE KEYS */;
INSERT INTO `service_providers` VALUES (2,1,1,'Rube Wagph','Baber service',NULL,NULL,NULL,'1',0.00,0,0,'2019-02-09 17:42:47','2019-02-09 17:42:47'),(3,2,1,'Wa pau','Saloon worker in Kamasa','Kasarani',NULL,NULL,'1',0.00,0,0,'2019-02-09 20:39:35','2019-02-09 20:39:35');
/*!40000 ALTER TABLE `service_providers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `service_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_meta` json DEFAULT NULL,
  `priority` int(10) DEFAULT '0',
  `service_icon` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_id` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `services_category_id_foreign` (`category_id`),
  CONSTRAINT `services_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,1,'Hair Salon',NULL,0,'salon.png',3,'2019-01-26 21:46:00','2019-01-26 21:46:00',NULL),(2,1,'Nail Salon',NULL,0,'nail-salon.png',1,'2019-01-27 13:24:21','2019-01-27 13:24:21','2019-01-27 13:24:21'),(3,1,'Tattoos',NULL,0,'tattoos.png',1,'2019-03-07 19:53:22','2019-03-07 19:53:22',NULL),(4,1,'Eyebrows & Lashes',NULL,0,'eye-lashes.png',1,'2019-03-07 19:53:53','2019-03-07 19:53:53',NULL),(5,1,'Beauty Salon',NULL,0,'beauty-salon.png',1,'2019-03-07 19:54:08','2019-03-07 19:54:08',NULL),(6,1,'Massage',NULL,0,'massage.png',1,'2019-03-07 19:54:21','2019-03-07 19:54:21',NULL),(7,1,'Makeup Artist',NULL,0,NULL,1,'2019-03-07 19:54:34','2019-03-07 19:54:34',NULL),(9,1,'Personal Trainer',NULL,0,NULL,1,'2019-03-07 19:55:51','2019-03-07 19:55:51',NULL),(10,1,'Wedding Makeup',NULL,0,NULL,1,'2019-03-07 19:56:05','2019-03-07 19:56:05',NULL),(11,1,'Hair Removal',NULL,0,NULL,1,'2019-03-07 19:56:30','2019-03-07 19:56:30',NULL),(12,1,'Piercing',NULL,0,NULL,1,'2019-03-07 19:56:46','2019-03-07 19:56:46',NULL),(13,1,'Physical Therapy',NULL,0,NULL,1,'2019-03-07 19:57:01','2019-03-07 19:57:01',NULL);
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `status_categories`
--

DROP TABLE IF EXISTS `status_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `status_categories`
--

LOCK TABLES `status_categories` WRITE;
/*!40000 ALTER TABLE `status_categories` DISABLE KEYS */;
INSERT INTO `status_categories` VALUES (1,'001','Transaction status','2019-01-27 18:53:13','2019-01-27 18:53:13',NULL),(2,'002','Transaction status',NULL,'2019-01-27 18:57:35','2019-01-27 18:57:35');
/*!40000 ALTER TABLE `status_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `statuses`
--

DROP TABLE IF EXISTS `statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statuses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status_category_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `statuses`
--

LOCK TABLES `statuses` WRITE;
/*!40000 ALTER TABLE `statuses` DISABLE KEYS */;
INSERT INTO `statuses` VALUES (1,'001','Profile pending approval from admin','2019-01-27 19:52:54','2019-01-27 19:52:54',NULL,1),(2,'002','Profile pending approval',NULL,'2019-01-27 19:56:30','2019-01-27 19:56:30',0);
/*!40000 ALTER TABLE `statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `top_services`
--

DROP TABLE IF EXISTS `top_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `top_services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(10) unsigned NOT NULL,
  `priority` int(2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `top_services_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `top_services`
--

LOCK TABLES `top_services` WRITE;
/*!40000 ALTER TABLE `top_services` DISABLE KEYS */;
INSERT INTO `top_services` VALUES (1,1,0,'2019-02-24 22:43:02','2019-02-24 22:43:02'),(2,2,0,'2019-02-24 22:43:02','2019-02-24 22:43:02'),(3,3,0,'2019-03-07 20:08:52','2019-03-07 20:08:52'),(4,4,0,'2019-03-07 20:08:52','2019-03-07 20:08:52'),(5,5,0,'2019-03-07 20:08:52','2019-03-07 20:08:52'),(6,6,0,'2019-03-07 20:08:52','2019-03-07 20:08:52');
/*!40000 ALTER TABLE `top_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `transaction_type` enum('DEBIT','CREDIT') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference` varchar(70) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double(10,2) NOT NULL,
  `running_balance` double(10,2) NOT NULL,
  `status_id` int(11) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_groups`
--

DROP TABLE IF EXISTS `user_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_groups`
--

LOCK TABLES `user_groups` WRITE;
/*!40000 ALTER TABLE `user_groups` DISABLE KEYS */;
INSERT INTO `user_groups` VALUES (1,'Super Admin','Overall system super admin','2019-01-04 18:26:35','2019-01-04 18:26:35'),(2,'Admin','System Admins','2019-01-04 18:26:35','2019-01-04 18:26:35'),(3,'Organisation Manager','Manage a certain organisation','2019-01-04 18:26:35','2019-01-04 18:26:35');
/*!40000 ALTER TABLE `user_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_permissions`
--

DROP TABLE IF EXISTS `user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_group` int(11) NOT NULL,
  `permission` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_permissions`
--

LOCK TABLES `user_permissions` WRITE;
/*!40000 ALTER TABLE `user_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_personal_details`
--

DROP TABLE IF EXISTS `user_personal_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_personal_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `id_number` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` datetime NOT NULL,
  `gender` enum('Male','Female','Un-disclosed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `passport_photo` json DEFAULT NULL COMMENT 'sample {"media_url":"...", "media_type":"[video|audio|image]", "size":"xMB"}',
  `home_location` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_phone_no` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_user_personal_details_fk-1` (`user_id`),
  CONSTRAINT `user_user_personal_details_fk-1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_personal_details`
--

LOCK TABLES `user_personal_details` WRITE;
/*!40000 ALTER TABLE `user_personal_details` DISABLE KEYS */;
INSERT INTO `user_personal_details` VALUES (1,1,'24080410','2019-02-09 20:41:06','Male',NULL,NULL,NULL,'2019-02-09 17:41:06','2019-02-09 17:41:06'),(2,1,'25098989','2019-01-01 00:00:00','Male',NULL,'kasarani',NULL,'2019-02-09 22:35:35','2019-02-09 22:35:35'),(3,2,'245637373','2019-10-01 00:00:00','Male','{\"name\": \"\", \"size\": 8667, \"type\": \"image\", \"extension\": \"jpeg\", \"media_url\": \"public/image/.jpeg\"}','Kasarani','7245242525','2019-02-09 23:18:47','2019-02-09 23:18:47');
/*!40000 ALTER TABLE `user_personal_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_group` int(10) unsigned NOT NULL DEFAULT '100',
  `phone_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Dennis Muoki',1,'+254713653112','muokid3@gmail.com','$2y$10$5Tf07ADgOESV4to4wTrq1.RLqtDQKzJRVge20.JqzyDAQbcsF6.wa',NULL,NULL,NULL),(2,'Pauline Weku',1,'254726986977','pauline@ke.ke','',NULL,'2019-02-09 20:28:25','2019-02-09 20:28:25');
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

-- Dump completed on 2019-03-26  0:43:38
