CREATE  TABLE  users_ (id INT(11) AUTO_INCREMENT,
first_name VARCHAR(100) NOT NULL,
last_name VARCHAR(100) NOT NULL,
email VARCHAR(191) NOT NULL,
phone_no VARCHAR(191) NOT NULL,
PRIMARY KEY(id));   
CREATE TABLE  role_user(role_id INT(10),user_id INT(10));
ALTER  TABLE  role_user ADD FOREIGN KEY(role_id) REFERENCES roles(id) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE `role_user` CHANGE `user_id` `user_id` INT(10) NOT NULL; 

-- MySQL dump 10.13  Distrib 5.7.25, for Linux (x86_64)
--
-- Host: localhost    Database: urbantap
-- ------------------------------------------------------
-- Server version	5.7.25-0ubuntu0.16.04.2


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
  `amount` double(10,2) DEFAULT '0.00',
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
INSERT INTO `bookings` VALUES (3,2,3,2,'2019-02-15 12:30:00',45,'2019-02-15 13:20:00',7,'USER LOCATION',0.00,NULL,'2019-02-14 21:29:31','2019-02-14 21:29:31',NULL),(4,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 01:14:39','2019-02-15 22:14:39','2019-02-15 22:14:39','{\"name\": \"Kasarani\"}'),(5,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 19:08:34','2019-02-16 16:08:34','2019-02-16 16:08:34','{\"name\": \"Kasarani\"}'),(6,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 19:08:43','2019-02-16 16:08:43','2019-02-16 16:08:43','{\"name\": \"Kasarani\"}'),(7,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 19:08:48','2019-02-16 16:08:48','2019-02-16 16:08:48','{\"name\": \"Kasarani\"}'),(8,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 19:12:11','2019-02-16 16:12:11','2019-02-16 16:12:11','{\"name\": \"Kasarani\"}'),(9,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 19:12:52','2019-02-16 16:12:52','2019-02-16 16:12:52','{\"name\": \"Kasarani\"}');
INSERT INTO `bookings` VALUES (3,2,3,2,'2019-02-15 12:30:00',45,'2019-02-15 13:20:00',7,'USER LOCATION',0.00,NULL,'2019-02-14 21:29:31','2019-02-14 21:29:31',NULL),(4,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 01:14:39','2019-02-15 22:14:39','2019-02-15 22:14:39','{\"name\": \"Kasarani\"}'),(5,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 19:08:34','2019-02-16 16:08:34','2019-02-16 16:08:34','{\"name\": \"Kasarani\"}'),(6,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 19:08:43','2019-02-16 16:08:43','2019-02-16 16:08:43','{\"name\": \"Kasarani\"}'),(7,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 19:08:48','2019-02-16 16:08:48','2019-02-16 16:08:48','{\"name\": \"Kasarani\"}'),(8,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 19:12:11','2019-02-16 16:12:11','2019-02-16 16:12:11','{\"name\": \"Kasarani\"}'),(9,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 19:12:52','2019-02-16 16:12:52','2019-02-16 16:12:52','{\"name\": \"Kasarani\"}');
INSERT INTO `bookings` VALUES (3,2,3,2,'2019-02-15 12:30:00',45,'2019-02-15 13:20:00',7,'USER LOCATION',0.00,NULL,'2019-02-14 21:29:31','2019-02-14 21:29:31',NULL),(4,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 01:14:39','2019-02-15 22:14:39','2019-02-15 22:14:39','{\"name\": \"Kasarani\"}'),(5,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 19:08:34','2019-02-16 16:08:34','2019-02-16 16:08:34','{\"name\": \"Kasarani\"}'),(6,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 19:08:43','2019-02-16 16:08:43','2019-02-16 16:08:43','{\"name\": \"Kasarani\"}'),(7,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 19:08:48','2019-02-16 16:08:48','2019-02-16 16:08:48','{\"name\": \"Kasarani\"}'),(8,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 19:12:11','2019-02-16 16:12:11','2019-02-16 16:12:11','{\"name\": \"Kasarani\"}'),(9,2,2,2,'2019-02-16 12:09:00',45,'2019-02-16 13:01:00',1,'USER LOCATION',0.00,'2019-02-16 19:12:52','2019-02-16 16:12:52','2019-02-16 16:12:52','{\"name\": \"Kasarani\"}');
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
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
=======
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
=======
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
=======
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
INSERT INTO `categories` VALUES (1,'Salon',0,'2019-01-04 18:26:34','2019-01-04 18:26:34'),(2,'Massage',0,'2019-01-04 18:26:34','2019-01-04 18:26:34'),(3,'shoes',0,'2019-01-26 20:07:20','2019-01-26 20:07:20'),(4,'under 23 shoes',0,'2019-01-26 20:13:15','2019-01-26 20:13:15'),(5,'Media Professionals',0,'2019-01-26 21:08:06','2019-01-26 21:08:06');
=======
INSERT INTO `categories` VALUES (1,'Salon',0,'2019-01-04 18:26:34','2019-01-04 18:26:34'),(2,'Massage',0,'2019-01-04 18:26:34','2019-01-04 18:26:34'),(3,'shoes',0,'2019-01-26 20:07:20','2019-01-26 20:07:20'),(4,'under 23 shoes',0,'2019-01-26 20:13:15','2019-01-26 20:13:15'),(5,'Media Professionals',0,'2019-01-26 21:08:06','2019-01-26 21:08:06'),(6,'Media Personalities',0,'2019-04-05 21:36:49','2019-04-05 21:36:49');
>>>>>>> rube/service-request
=======
INSERT INTO `categories` VALUES (1,'Salon',0,'2019-01-04 18:26:34','2019-01-04 18:26:34'),(2,'Massage',0,'2019-01-04 18:26:34','2019-01-04 18:26:34'),(3,'shoes',0,'2019-01-26 20:07:20','2019-01-26 20:07:20'),(4,'under 23 shoes',0,'2019-01-26 20:13:15','2019-01-26 20:13:15'),(5,'Media Professionals',0,'2019-01-26 21:08:06','2019-01-26 21:08:06'),(6,'Media Personalities',0,'2019-04-05 21:36:49','2019-04-05 21:36:49');
>>>>>>> rube/service-request
=======
INSERT INTO `categories` VALUES (1,'Salon',0,'2019-01-04 18:26:34','2019-01-04 18:26:34'),(2,'Massage',0,'2019-01-04 18:26:34','2019-01-04 18:26:34'),(3,'shoes',0,'2019-01-26 20:07:20','2019-01-26 20:07:20'),(4,'under 23 shoes',0,'2019-01-26 20:13:15','2019-01-26 20:13:15'),(5,'Media Professionals',0,'2019-01-26 21:08:06','2019-01-26 21:08:06'),(6,'Media Personalities',0,'2019-04-05 21:36:49','2019-04-05 21:36:49');
>>>>>>> rube/service-request
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
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
  `cost_parameter` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
>>>>>>> rube/service-request
=======
  `cost_parameter` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
>>>>>>> rube/service-request
=======
  `cost_parameter` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
>>>>>>> rube/service-request
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
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
=======
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
=======
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
=======
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(8,'2018_09_30_123658_create_service_providers_table',1),(9,'2018_10_01_114758_create_categories_table',1),(10,'2018_10_01_143625_create_user_groups_table',1),(11,'2018_10_01_143747_create_permissions_table',1),(12,'2018_10_01_143821_create_user_permissions_table',1),(13,'2018_10_01_144237_create_businesses_table',1),(14,'2018_10_01_145014_create_operating_hours_table',1),(15,'2018_10_01_145235_create_services_table',1),(16,'2018_10_02_123847_create_service_provider_images_table',1),(17,'2018_10_02_124129_create_provider_services_table',1),(22,'2018_11_01_092645_create_appointments_table',1),(102,'2019_01_07_185249_create_status_categories_table',2);
=======
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(8,'2018_09_30_123658_create_service_providers_table',1),(9,'2018_10_01_114758_create_categories_table',1),(10,'2018_10_01_143625_create_user_groups_table',1),(11,'2018_10_01_143747_create_permissions_table',1),(12,'2018_10_01_143821_create_user_permissions_table',1),(13,'2018_10_01_144237_create_businesses_table',1),(14,'2018_10_01_145014_create_operating_hours_table',1),(15,'2018_10_01_145235_create_services_table',1),(16,'2018_10_02_123847_create_service_provider_images_table',1),(17,'2018_10_02_124129_create_provider_services_table',1),(22,'2018_11_01_092645_create_appointments_table',1),(102,'2019_01_07_185249_create_status_categories_table',2),(103,'2016_06_01_000001_create_oauth_auth_codes_table',3),(104,'2016_06_01_000002_create_oauth_access_tokens_table',3),(105,'2016_06_01_000003_create_oauth_refresh_tokens_table',3),(106,'2016_06_01_000004_create_oauth_clients_table',3),(107,'2016_06_01_000005_create_oauth_personal_access_clients_table',3),(108,'2019_01_11_233237_users_table',4),(109,'2019_01_11_235234_users_table_verified',4),(110,'2019_01_12_000434_add_verficationfields',4);
>>>>>>> rube/service-request
=======
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(8,'2018_09_30_123658_create_service_providers_table',1),(9,'2018_10_01_114758_create_categories_table',1),(10,'2018_10_01_143625_create_user_groups_table',1),(11,'2018_10_01_143747_create_permissions_table',1),(12,'2018_10_01_143821_create_user_permissions_table',1),(13,'2018_10_01_144237_create_businesses_table',1),(14,'2018_10_01_145014_create_operating_hours_table',1),(15,'2018_10_01_145235_create_services_table',1),(16,'2018_10_02_123847_create_service_provider_images_table',1),(17,'2018_10_02_124129_create_provider_services_table',1),(22,'2018_11_01_092645_create_appointments_table',1),(102,'2019_01_07_185249_create_status_categories_table',2),(103,'2016_06_01_000001_create_oauth_auth_codes_table',3),(104,'2016_06_01_000002_create_oauth_access_tokens_table',3),(105,'2016_06_01_000003_create_oauth_refresh_tokens_table',3),(106,'2016_06_01_000004_create_oauth_clients_table',3),(107,'2016_06_01_000005_create_oauth_personal_access_clients_table',3),(108,'2019_01_11_233237_users_table',4),(109,'2019_01_11_235234_users_table_verified',4),(110,'2019_01_12_000434_add_verficationfields',4);
>>>>>>> rube/service-request
=======
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(8,'2018_09_30_123658_create_service_providers_table',1),(9,'2018_10_01_114758_create_categories_table',1),(10,'2018_10_01_143625_create_user_groups_table',1),(11,'2018_10_01_143747_create_permissions_table',1),(12,'2018_10_01_143821_create_user_permissions_table',1),(13,'2018_10_01_144237_create_businesses_table',1),(14,'2018_10_01_145014_create_operating_hours_table',1),(15,'2018_10_01_145235_create_services_table',1),(16,'2018_10_02_123847_create_service_provider_images_table',1),(17,'2018_10_02_124129_create_provider_services_table',1),(22,'2018_11_01_092645_create_appointments_table',1),(102,'2019_01_07_185249_create_status_categories_table',2),(103,'2016_06_01_000001_create_oauth_auth_codes_table',3),(104,'2016_06_01_000002_create_oauth_access_tokens_table',3),(105,'2016_06_01_000003_create_oauth_refresh_tokens_table',3),(106,'2016_06_01_000004_create_oauth_clients_table',3),(107,'2016_06_01_000005_create_oauth_personal_access_clients_table',3),(108,'2019_01_11_233237_users_table',4),(109,'2019_01_11_235234_users_table_verified',4),(110,'2019_01_12_000434_add_verficationfields',4);
>>>>>>> rube/service-request
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
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
  `msisdn` int(11) NOT NULL,
=======
  `msisdn` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
>>>>>>> rube/service-request
=======
  `msisdn` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
>>>>>>> rube/service-request
=======
  `msisdn` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
>>>>>>> rube/service-request
  `transaction_time` datetime NOT NULL,
  `message` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_no` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mpesa_code` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double(10,2) NOT NULL,
  `names` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paybill_no` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
  `bill_ref_no` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_ref` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
>>>>>>> rube/service-request
=======
  `bill_ref_no` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_ref` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
>>>>>>> rube/service-request
=======
  `bill_ref_no` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_ref` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
>>>>>>> rube/service-request
  `status_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
=======
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
=======
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
=======
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpesa_transactions`
--

LOCK TABLES `mpesa_transactions` WRITE;
/*!40000 ALTER TABLE `mpesa_transactions` DISABLE KEYS */;
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
INSERT INTO `mpesa_transactions` VALUES (1,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','21','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 18:44:24','2019-04-02 18:44:24'),(2,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:18:26','2019-04-02 19:18:26'),(3,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:19:17','2019-04-02 19:19:17'),(4,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:19:37','2019-04-02 19:19:37'),(5,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:43:29','2019-04-02 19:43:29'),(6,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:43:32','2019-04-02 19:43:32'),(7,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:44:36','2019-04-02 19:44:36'),(8,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',1,'2019-04-02 19:51:08','2019-04-02 19:51:08'),(9,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',1,'2019-04-02 19:51:23','2019-04-02 19:51:23'),(10,NULL,'254726498973','2019-04-11 21:57:43','Pay Bill','75','NDB96EJ0F5',10.00,'ANTONY THIONGO ','731029','75','NDB96EJ0F5',0,NULL,NULL),(11,NULL,'254726498973','2019-04-11 22:59:17','Pay Bill','65','NDB36FCN0H',10.00,'ANTONY THIONGO ','731029','65','NDB36FCN0H',0,NULL,NULL),(12,NULL,'254726498973','2019-04-11 23:46:21','Pay Bill','67','NDB26FO0I4',10.00,'ANTONY THIONGO ','731029','67','NDB26FO0I4',0,NULL,NULL),(13,NULL,'254726498973','2019-04-11 23:48:34','Pay Bill','78','NDB46FOBG2',10.00,'ANTONY THIONGO ','731029','78','NDB46FOBG2',1,NULL,NULL),(14,NULL,'254726498973','2019-04-12 00:25:57','Pay Bill','43','NDC56FVX5F',10.00,'ANTONY THIONGO ','731029','43','NDC56FVX5F',1,NULL,NULL),(15,NULL,'254726498973','2019-04-12 20:27:12','Pay Bill','37','NDC475ZLMY',10.00,'ANTONY THIONGO ','731029','37','NDC475ZLMY',1,NULL,NULL),(16,NULL,'254726498973','2019-04-12 21:00:30','Pay Bill','85','NDC9776383',10.00,'ANTONY THIONGO ','731029','85','NDC9776383',1,NULL,NULL);
>>>>>>> rube/service-request
=======
INSERT INTO `mpesa_transactions` VALUES (1,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','21','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 18:44:24','2019-04-02 18:44:24'),(2,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:18:26','2019-04-02 19:18:26'),(3,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:19:17','2019-04-02 19:19:17'),(4,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:19:37','2019-04-02 19:19:37'),(5,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:43:29','2019-04-02 19:43:29'),(6,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:43:32','2019-04-02 19:43:32'),(7,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:44:36','2019-04-02 19:44:36'),(8,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',1,'2019-04-02 19:51:08','2019-04-02 19:51:08'),(9,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',1,'2019-04-02 19:51:23','2019-04-02 19:51:23'),(10,NULL,'254726498973','2019-04-11 21:57:43','Pay Bill','75','NDB96EJ0F5',10.00,'ANTONY THIONGO ','731029','75','NDB96EJ0F5',0,NULL,NULL),(11,NULL,'254726498973','2019-04-11 22:59:17','Pay Bill','65','NDB36FCN0H',10.00,'ANTONY THIONGO ','731029','65','NDB36FCN0H',0,NULL,NULL),(12,NULL,'254726498973','2019-04-11 23:46:21','Pay Bill','67','NDB26FO0I4',10.00,'ANTONY THIONGO ','731029','67','NDB26FO0I4',0,NULL,NULL),(13,NULL,'254726498973','2019-04-11 23:48:34','Pay Bill','78','NDB46FOBG2',10.00,'ANTONY THIONGO ','731029','78','NDB46FOBG2',1,NULL,NULL),(14,NULL,'254726498973','2019-04-12 00:25:57','Pay Bill','43','NDC56FVX5F',10.00,'ANTONY THIONGO ','731029','43','NDC56FVX5F',1,NULL,NULL),(15,NULL,'254726498973','2019-04-12 20:27:12','Pay Bill','37','NDC475ZLMY',10.00,'ANTONY THIONGO ','731029','37','NDC475ZLMY',1,NULL,NULL),(16,NULL,'254726498973','2019-04-12 21:00:30','Pay Bill','85','NDC9776383',10.00,'ANTONY THIONGO ','731029','85','NDC9776383',1,NULL,NULL);
>>>>>>> rube/service-request
=======
INSERT INTO `mpesa_transactions` VALUES (1,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','21','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 18:44:24','2019-04-02 18:44:24'),(2,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:18:26','2019-04-02 19:18:26'),(3,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:19:17','2019-04-02 19:19:17'),(4,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:19:37','2019-04-02 19:19:37'),(5,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:43:29','2019-04-02 19:43:29'),(6,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:43:32','2019-04-02 19:43:32'),(7,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',0,'2019-04-02 19:44:36','2019-04-02 19:44:36'),(8,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',1,'2019-04-02 19:51:08','2019-04-02 19:51:08'),(9,NULL,'726498973','2019-04-02 20:50:12','Pay Bill','INV001','M930303',50.00,'ANTONY NJOROGE THIONGO','731029','B399393','M930303',1,'2019-04-02 19:51:23','2019-04-02 19:51:23'),(10,NULL,'254726498973','2019-04-11 21:57:43','Pay Bill','75','NDB96EJ0F5',10.00,'ANTONY THIONGO ','731029','75','NDB96EJ0F5',0,NULL,NULL),(11,NULL,'254726498973','2019-04-11 22:59:17','Pay Bill','65','NDB36FCN0H',10.00,'ANTONY THIONGO ','731029','65','NDB36FCN0H',0,NULL,NULL),(12,NULL,'254726498973','2019-04-11 23:46:21','Pay Bill','67','NDB26FO0I4',10.00,'ANTONY THIONGO ','731029','67','NDB26FO0I4',0,NULL,NULL),(13,NULL,'254726498973','2019-04-11 23:48:34','Pay Bill','78','NDB46FOBG2',10.00,'ANTONY THIONGO ','731029','78','NDB46FOBG2',1,NULL,NULL),(14,NULL,'254726498973','2019-04-12 00:25:57','Pay Bill','43','NDC56FVX5F',10.00,'ANTONY THIONGO ','731029','43','NDC56FVX5F',1,NULL,NULL),(15,NULL,'254726498973','2019-04-12 20:27:12','Pay Bill','37','NDC475ZLMY',10.00,'ANTONY THIONGO ','731029','37','NDC475ZLMY',1,NULL,NULL),(16,NULL,'254726498973','2019-04-12 21:00:30','Pay Bill','85','NDC9776383',10.00,'ANTONY THIONGO ','731029','85','NDC9776383',1,NULL,NULL);
>>>>>>> rube/service-request
/*!40000 ALTER TABLE `mpesa_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
=======
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
-- Table structure for table `oauth_access_tokens`
--

DROP TABLE IF EXISTS `oauth_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_access_tokens_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_access_tokens`
--

LOCK TABLES `oauth_access_tokens` WRITE;
/*!40000 ALTER TABLE `oauth_access_tokens` DISABLE KEYS */;
INSERT INTO `oauth_access_tokens` VALUES ('0318117b4fbc463b2932bdba99fac62f012d7566af8db58dacc223e397e92cbc368cd07f0c2c2d6e',10,1,'Personal Access Token','[]',0,'2019-04-10 19:23:41','2019-04-10 19:23:41','2020-04-10 22:23:41'),('215706cd7757915c23203375be20d669a4441674f54b946dd637a57de97d1dadaa656b4907aa96e9',10,1,'Personal Access Token','[]',0,'2019-04-10 19:11:42','2019-04-10 19:11:42','2020-04-10 22:11:42'),('4f376dbd22c4269e7bd04e7f02cad66c205397d484bc8d91eece4f18d4e3e3a338303839fa7717e1',10,1,'Personal Access Token','[]',0,'2019-04-10 19:23:07','2019-04-10 19:23:07','2020-04-10 22:23:07'),('7c6d00ac79eeba921f821ba806264379c18d2457781c89d1981d410e72b4b7b4a1eafd19410f1753',10,1,'Personal Access Token','[]',0,'2019-04-10 19:42:16','2019-04-10 19:42:16','2020-04-10 22:42:16'),('7f811202f534ae13c91aed25e173ef47819318402c6bb8c59d85c25b5520803ea04a3d7943293f33',10,1,'Personal Access Token','[]',0,'2019-04-10 19:11:41','2019-04-10 19:11:41','2020-04-10 22:11:41'),('92ec97d42d7869f18a5d2fb450016d27376e7f5d758d6c3983c7ac5a302819139f7d209b58ba6b04',9,1,'Personal Access Token','[]',0,'2019-04-08 09:38:40','2019-04-08 09:38:40','2020-04-08 12:38:40'),('caeffa771a1da843a41c7cffd7393e48df3e981d1d29eea8685842d9dab82727acacc87999350c29',9,1,'Personal Access Token','[]',0,'2019-04-08 07:02:43','2019-04-08 07:02:43','2020-04-08 10:02:43'),('d72f597616d6f79083ba1ef05bd196812ff0974c63f8af9ac78f2b9282107dfd9c33db072015f6ff',10,1,'Personal Access Token','[]',0,'2019-04-10 19:42:12','2019-04-10 19:42:12','2020-04-10 22:42:12'),('e0524eab70521b2d0ae42e1e4ddabfa240acfc3a9bd5f959cecc4090a42074a2fe3748d798c86e27',9,1,'Personal Access Token','[]',0,'2019-04-08 09:40:20','2019-04-08 09:40:20','2020-04-08 12:40:20'),('e4aad3c23330d237e2c4c0c0e06c7af0604da7fd3e4844a177b639b47398a483a06b074220931c99',10,1,'Personal Access Token','[]',0,'2019-04-10 18:53:14','2019-04-10 18:53:14','2020-04-10 21:53:14');
/*!40000 ALTER TABLE `oauth_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_auth_codes`
--

DROP TABLE IF EXISTS `oauth_auth_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_auth_codes`
--

LOCK TABLES `oauth_auth_codes` WRITE;
/*!40000 ALTER TABLE `oauth_auth_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_auth_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_clients`
--

DROP TABLE IF EXISTS `oauth_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_clients_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_clients`
--

LOCK TABLES `oauth_clients` WRITE;
/*!40000 ALTER TABLE `oauth_clients` DISABLE KEYS */;
INSERT INTO `oauth_clients` VALUES (1,NULL,'URBANTAP Personal Access Client','s6nzAQQxiL6LDS8WRrccqJa1ykzwpvrXCJwQa8e2','http://localhost',1,0,0,'2019-04-08 07:00:37','2019-04-08 07:00:37'),(2,NULL,'URBANTAP Password Grant Client','h6i3hpT46owdzrcVFFnUud2nldRKxDJ3jKECBQ7m','http://localhost',0,1,0,'2019-04-08 07:00:37','2019-04-08 07:00:37');
/*!40000 ALTER TABLE `oauth_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_personal_access_clients`
--

DROP TABLE IF EXISTS `oauth_personal_access_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_personal_access_clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_personal_access_clients_client_id_index` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_personal_access_clients`
--

LOCK TABLES `oauth_personal_access_clients` WRITE;
/*!40000 ALTER TABLE `oauth_personal_access_clients` DISABLE KEYS */;
INSERT INTO `oauth_personal_access_clients` VALUES (1,1,'2019-04-08 07:00:37','2019-04-08 07:00:37');
/*!40000 ALTER TABLE `oauth_personal_access_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_refresh_tokens`
--

DROP TABLE IF EXISTS `oauth_refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_refresh_tokens`
--

LOCK TABLES `oauth_refresh_tokens` WRITE;
/*!40000 ALTER TABLE `oauth_refresh_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_refresh_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
<<<<<<< HEAD
<<<<<<< HEAD
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
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
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
=======
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
  `msisdn` mediumtext COLLATE utf8mb4_unicode_ci,
  `network` enum('SAFARICOM','AIRTEL','TELKOM','EQUITEL','ORANGE','JTL','EMAIL') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_id` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `email` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
<<<<<<< HEAD
<<<<<<< HEAD
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `outboxes`
--

LOCK TABLES `outboxes` WRITE;
/*!40000 ALTER TABLE `outboxes` DISABLE KEYS */;
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
INSERT INTO `outboxes` VALUES (1,14,'254725920576','SAFARICOM',NULL,NULL,'Dear Erito Wanyamam,\n Use 7174 to verify your URBANTAP account. STOP *456*9*5#',24,'2019-04-22 14:14:26','2019-04-22 14:14:26',NULL);
>>>>>>> rube/service-request
=======
INSERT INTO `outboxes` VALUES (1,14,'254725920576','SAFARICOM',NULL,NULL,'Dear Erito Wanyamam,\n Use 7174 to verify your URBANTAP account. STOP *456*9*5#',24,'2019-04-22 14:14:26','2019-04-22 14:14:26',NULL);
>>>>>>> rube/service-request
=======
INSERT INTO `outboxes` VALUES (1,14,'254725920576','SAFARICOM',NULL,NULL,'Dear Erito Wanyamam,\n Use 7174 to verify your URBANTAP account. STOP *456*9*5#',24,'2019-04-22 14:14:26','2019-04-22 14:14:26',NULL);
>>>>>>> rube/service-request
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
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
  `provider_service_id` int(10) unsigned NOT NULL,
>>>>>>> rube/service-request
=======
  `provider_service_id` int(10) unsigned NOT NULL,
>>>>>>> rube/service-request
=======
  `provider_service_id` int(10) unsigned NOT NULL,
>>>>>>> rube/service-request
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
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
INSERT INTO `portfolios` VALUES (1,2,'{\"name\": \"\", \"size\": 8667, \"type\": \"image\", \"extension\": \"jpeg\", \"media_url\": \"public/image/.jpeg\"}','Grooming on another level. Well beyond zero',3,'2019-02-10 13:23:34','2019-02-10 13:19:46','2019-02-10 13:19:46'),(2,2,'{\"name\": \"\", \"size\": 8667, \"type\": \"image\", \"extension\": \"jpeg\", \"media_url\": \"public/image/.jpeg\"}','Grooming on another level. Well beyond zero',1,'2019-02-10 13:27:23','2019-02-10 13:27:23','2019-02-10 13:27:23');
=======
INSERT INTO `portfolios` VALUES (1,2,'{\"name\": \"\", \"size\": 8667, \"type\": \"image\", \"extension\": \"jpeg\", \"media_url\": \"public/image/.jpeg\"}','Grooming on another level. Well beyond zero',3,'2019-02-10 13:23:34','2019-02-10 13:19:46','2019-02-10 13:19:46',0),(2,2,'{\"name\": \"\", \"size\": 8667, \"type\": \"image\", \"extension\": \"jpeg\", \"media_url\": \"public/image/.jpeg\"}','Grooming on another level. Well beyond zero',1,'2019-02-10 13:27:23','2019-02-10 13:27:23','2019-02-10 13:27:23',0);
>>>>>>> rube/service-request
=======
INSERT INTO `portfolios` VALUES (1,2,'{\"name\": \"\", \"size\": 8667, \"type\": \"image\", \"extension\": \"jpeg\", \"media_url\": \"public/image/.jpeg\"}','Grooming on another level. Well beyond zero',3,'2019-02-10 13:23:34','2019-02-10 13:19:46','2019-02-10 13:19:46',0),(2,2,'{\"name\": \"\", \"size\": 8667, \"type\": \"image\", \"extension\": \"jpeg\", \"media_url\": \"public/image/.jpeg\"}','Grooming on another level. Well beyond zero',1,'2019-02-10 13:27:23','2019-02-10 13:27:23','2019-02-10 13:27:23',0);
>>>>>>> rube/service-request
=======
INSERT INTO `portfolios` VALUES (1,2,'{\"name\": \"\", \"size\": 8667, \"type\": \"image\", \"extension\": \"jpeg\", \"media_url\": \"public/image/.jpeg\"}','Grooming on another level. Well beyond zero',3,'2019-02-10 13:23:34','2019-02-10 13:19:46','2019-02-10 13:19:46',0),(2,2,'{\"name\": \"\", \"size\": 8667, \"type\": \"image\", \"extension\": \"jpeg\", \"media_url\": \"public/image/.jpeg\"}','Grooming on another level. Well beyond zero',1,'2019-02-10 13:27:23','2019-02-10 13:27:23','2019-02-10 13:27:23',0);
>>>>>>> rube/service-request
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
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
=======
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
-- Table structure for table `role_user`
--

DROP TABLE IF EXISTS `role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_role_id_user_id_unique` (`role_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_user`
--

LOCK TABLES `role_user` WRITE;
/*!40000 ALTER TABLE `role_user` DISABLE KEYS */;
INSERT INTO `role_user` VALUES (3,9,5,NULL,NULL),(4,9,6,NULL,NULL),(5,9,7,NULL,NULL),(6,9,8,NULL,NULL),(7,9,9,NULL,NULL),(8,9,10,NULL,NULL);
/*!40000 ALTER TABLE `role_user` ENABLE KEYS */;
UNLOCK TABLES;

--
<<<<<<< HEAD
<<<<<<< HEAD
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
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
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
=======
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
  `instagram` varchar(244) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter` varchar(244) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook` varchar(244) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_phone` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_location_city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
<<<<<<< HEAD
<<<<<<< HEAD
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
  `business_description` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
  `work_location` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_lat` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_lng` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_id` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
  `overall_rating` float(10,2) DEFAULT '0.00',
  `overall_dislikes` int(11) DEFAULT '0',
  `overall_likes` int(11) DEFAULT '0',
=======
=======
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
  `total_requests` int(11) NOT NULL DEFAULT '23',
  `overall_rating` float(10,2) DEFAULT '0.00',
  `overall_dislikes` int(11) DEFAULT '0',
  `overall_likes` int(11) DEFAULT '0',
  `cover_photo` json DEFAULT NULL COMMENT 'sample {"media_url":"...", "media_type":"[video|audio|image]", "size":"xMB"}',
<<<<<<< HEAD
<<<<<<< HEAD
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_service_providers_fk-1` (`user_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `user_service_providers_fk-1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
=======
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
=======
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
=======
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_providers`
--

LOCK TABLES `service_providers` WRITE;
/*!40000 ALTER TABLE `service_providers` DISABLE KEYS */;
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
INSERT INTO `service_providers` VALUES (2,1,1,'Rube Wagph','Baber service',NULL,NULL,NULL,'1',0.00,0,0,'2019-02-09 17:42:47','2019-02-09 17:42:47'),(3,2,1,'Wa pau','Saloon worker in Kamasa','Kasarani',NULL,NULL,'1',0.00,0,0,'2019-02-09 20:39:35','2019-02-09 20:39:35');
=======
INSERT INTO `service_providers` VALUES (2,1,1,'Rube Wagph',NULL,NULL,NULL,NULL,NULL,NULL,'Baber service',NULL,NULL,NULL,'1',23,0.00,0,0,NULL,'2019-02-09 17:42:47','2019-02-09 17:42:47'),(3,2,1,'Wa pau',NULL,NULL,NULL,NULL,NULL,NULL,'Saloon worker in Kamasa','Kasarani',NULL,NULL,'1',23,0.00,0,0,NULL,'2019-02-09 20:39:35','2019-02-09 20:39:35'),(4,3,1,'Kamirithu  herbs  clinic',NULL,NULL,NULL,NULL,NULL,NULL,'Treatment  of  deases','kasarani',NULL,NULL,'1',23,0.00,0,0,NULL,'2019-04-15 17:38:12','2019-04-15 17:38:12');
>>>>>>> rube/service-request
=======
INSERT INTO `service_providers` VALUES (2,1,1,'Rube Wagph',NULL,NULL,NULL,NULL,NULL,NULL,'Baber service',NULL,NULL,NULL,'1',23,0.00,0,0,NULL,'2019-02-09 17:42:47','2019-02-09 17:42:47'),(3,2,1,'Wa pau',NULL,NULL,NULL,NULL,NULL,NULL,'Saloon worker in Kamasa','Kasarani',NULL,NULL,'1',23,0.00,0,0,NULL,'2019-02-09 20:39:35','2019-02-09 20:39:35'),(4,3,1,'Kamirithu  herbs  clinic',NULL,NULL,NULL,NULL,NULL,NULL,'Treatment  of  deases','kasarani',NULL,NULL,'1',23,0.00,0,0,NULL,'2019-04-15 17:38:12','2019-04-15 17:38:12');
>>>>>>> rube/service-request
=======
INSERT INTO `service_providers` VALUES (2,1,1,'Rube Wagph',NULL,NULL,NULL,NULL,NULL,NULL,'Baber service',NULL,NULL,NULL,'1',23,0.00,0,0,NULL,'2019-02-09 17:42:47','2019-02-09 17:42:47'),(3,2,1,'Wa pau',NULL,NULL,NULL,NULL,NULL,NULL,'Saloon worker in Kamasa','Kasarani',NULL,NULL,'1',23,0.00,0,0,NULL,'2019-02-09 20:39:35','2019-02-09 20:39:35'),(4,3,1,'Kamirithu  herbs  clinic',NULL,NULL,NULL,NULL,NULL,NULL,'Treatment  of  deases','kasarani',NULL,NULL,'1',23,0.00,0,0,NULL,'2019-04-15 17:38:12','2019-04-15 17:38:12');
>>>>>>> rube/service-request
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
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
=======
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
=======
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
=======
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `status_categories`
--

LOCK TABLES `status_categories` WRITE;
/*!40000 ALTER TABLE `status_categories` DISABLE KEYS */;
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
INSERT INTO `status_categories` VALUES (1,'001','Transaction status','2019-01-27 18:53:13','2019-01-27 18:53:13',NULL),(2,'002','Transaction status',NULL,'2019-01-27 18:57:35','2019-01-27 18:57:35');
=======
INSERT INTO `status_categories` VALUES (1,'001','Transaction status','2019-01-27 18:53:13','2019-01-27 18:53:13',NULL),(2,'002','Transaction status',NULL,'2019-01-27 18:57:35','2019-01-27 18:57:35'),(3,'003','Another status',NULL,'2019-04-03 15:02:25','2019-04-03 15:02:25');
>>>>>>> rube/service-request
=======
INSERT INTO `status_categories` VALUES (1,'001','Transaction status','2019-01-27 18:53:13','2019-01-27 18:53:13',NULL),(2,'002','Transaction status',NULL,'2019-01-27 18:57:35','2019-01-27 18:57:35'),(3,'003','Another status',NULL,'2019-04-03 15:02:25','2019-04-03 15:02:25');
>>>>>>> rube/service-request
=======
INSERT INTO `status_categories` VALUES (1,'001','Transaction status','2019-01-27 18:53:13','2019-01-27 18:53:13',NULL),(2,'002','Transaction status',NULL,'2019-01-27 18:57:35','2019-01-27 18:57:35'),(3,'003','Another status',NULL,'2019-04-03 15:02:25','2019-04-03 15:02:25');
>>>>>>> rube/service-request
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
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
=======
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
=======
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
=======
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
>>>>>>> rube/service-request
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
INSERT INTO `transactions` VALUES (1,3,'CREDIT','M930303',50.00,50.00,0,'2019-04-02 22:44:37','2019-04-02 19:44:37','2019-04-02 19:44:37'),(2,3,'CREDIT','M930303',50.00,50.00,1,'2019-04-02 22:51:08','2019-04-02 19:51:08','2019-04-02 19:51:08'),(3,3,'CREDIT','M930303',50.00,50.00,1,'2019-04-02 22:51:23','2019-04-02 19:51:23','2019-04-02 19:51:23'),(4,11,'CREDIT','NDB36FCN0H',10.00,10.00,0,'2019-04-11 19:59:19','2019-04-11 16:59:19','2019-04-11 16:59:19'),(5,11,'CREDIT','NDB26FO0I4',10.00,10.00,0,'2019-04-11 20:46:22','2019-04-11 17:46:22','2019-04-11 17:46:22'),(6,11,'CREDIT','NDB46FOBG2',10.00,10.00,1,'2019-04-11 20:48:35','2019-04-11 17:48:35','2019-04-11 17:48:35'),(7,11,'CREDIT','NDC56FVX5F',10.00,10.00,1,'2019-04-11 21:25:59','2019-04-11 18:25:59','2019-04-11 18:25:59'),(8,11,'CREDIT','NDC475ZLMY',10.00,10.00,1,'2019-04-12 17:27:13','2019-04-12 14:27:13','2019-04-12 14:27:13'),(9,11,'CREDIT','NDC9776383',10.00,10.00,1,'2019-04-12 18:00:32','2019-04-12 15:00:32','2019-04-12 15:00:32');
>>>>>>> rube/service-request
=======
INSERT INTO `transactions` VALUES (1,3,'CREDIT','M930303',50.00,50.00,0,'2019-04-02 22:44:37','2019-04-02 19:44:37','2019-04-02 19:44:37'),(2,3,'CREDIT','M930303',50.00,50.00,1,'2019-04-02 22:51:08','2019-04-02 19:51:08','2019-04-02 19:51:08'),(3,3,'CREDIT','M930303',50.00,50.00,1,'2019-04-02 22:51:23','2019-04-02 19:51:23','2019-04-02 19:51:23'),(4,11,'CREDIT','NDB36FCN0H',10.00,10.00,0,'2019-04-11 19:59:19','2019-04-11 16:59:19','2019-04-11 16:59:19'),(5,11,'CREDIT','NDB26FO0I4',10.00,10.00,0,'2019-04-11 20:46:22','2019-04-11 17:46:22','2019-04-11 17:46:22'),(6,11,'CREDIT','NDB46FOBG2',10.00,10.00,1,'2019-04-11 20:48:35','2019-04-11 17:48:35','2019-04-11 17:48:35'),(7,11,'CREDIT','NDC56FVX5F',10.00,10.00,1,'2019-04-11 21:25:59','2019-04-11 18:25:59','2019-04-11 18:25:59'),(8,11,'CREDIT','NDC475ZLMY',10.00,10.00,1,'2019-04-12 17:27:13','2019-04-12 14:27:13','2019-04-12 14:27:13'),(9,11,'CREDIT','NDC9776383',10.00,10.00,1,'2019-04-12 18:00:32','2019-04-12 15:00:32','2019-04-12 15:00:32');
>>>>>>> rube/service-request
=======
INSERT INTO `transactions` VALUES (1,3,'CREDIT','M930303',50.00,50.00,0,'2019-04-02 22:44:37','2019-04-02 19:44:37','2019-04-02 19:44:37'),(2,3,'CREDIT','M930303',50.00,50.00,1,'2019-04-02 22:51:08','2019-04-02 19:51:08','2019-04-02 19:51:08'),(3,3,'CREDIT','M930303',50.00,50.00,1,'2019-04-02 22:51:23','2019-04-02 19:51:23','2019-04-02 19:51:23'),(4,11,'CREDIT','NDB36FCN0H',10.00,10.00,0,'2019-04-11 19:59:19','2019-04-11 16:59:19','2019-04-11 16:59:19'),(5,11,'CREDIT','NDB26FO0I4',10.00,10.00,0,'2019-04-11 20:46:22','2019-04-11 17:46:22','2019-04-11 17:46:22'),(6,11,'CREDIT','NDB46FOBG2',10.00,10.00,1,'2019-04-11 20:48:35','2019-04-11 17:48:35','2019-04-11 17:48:35'),(7,11,'CREDIT','NDC56FVX5F',10.00,10.00,1,'2019-04-11 21:25:59','2019-04-11 18:25:59','2019-04-11 18:25:59'),(8,11,'CREDIT','NDC475ZLMY',10.00,10.00,1,'2019-04-12 17:27:13','2019-04-12 14:27:13','2019-04-12 14:27:13'),(9,11,'CREDIT','NDC9776383',10.00,10.00,1,'2019-04-12 18:00:32','2019-04-12 15:00:32','2019-04-12 15:00:32');
>>>>>>> rube/service-request
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
=======
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
-- Table structure for table `user_balance`
--

DROP TABLE IF EXISTS `user_balance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_balance` (
  `user_balance_id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `transaction_id` bigint(20) NOT NULL,
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bonus_balance` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`user_balance_id`),
  UNIQUE KEY `user_id_unq` (`user_id`),
  KEY `user_id` (`user_id`),
  KEY `bonus_balance` (`bonus_balance`),
  KEY `transaction_id` (`transaction_id`),
  CONSTRAINT `user_balance_fk1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_balance`
--

LOCK TABLES `user_balance` WRITE;
/*!40000 ALTER TABLE `user_balance` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_balance` ENABLE KEYS */;
UNLOCK TABLES;

--
<<<<<<< HEAD
<<<<<<< HEAD
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
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
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
=======
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
  `first_name` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_group` int(10) unsigned NOT NULL DEFAULT '100',
  `phone_no` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_verified` int(1) DEFAULT '0',
  `confirmation_token` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_id` int(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `verification_code` int(10) unsigned DEFAULT NULL,
  `verified` tinyint(4) NOT NULL DEFAULT '0',
  `verification_sends` tinyint(4) NOT NULL DEFAULT '0',
  `verification_tries` tinyint(4) NOT NULL DEFAULT '0',
  `verified_time` datetime DEFAULT NULL,
  `verification_expiry_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
<<<<<<< HEAD
<<<<<<< HEAD
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
=======
>>>>>>> rube/service-request
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
INSERT INTO `users` VALUES (1,'Dennis Muoki',1,'+254713653112','muokid3@gmail.com','$2y$10$5Tf07ADgOESV4to4wTrq1.RLqtDQKzJRVge20.JqzyDAQbcsF6.wa',NULL,NULL,NULL),(2,'Pauline Weku',1,'254726986977','pauline@ke.ke','',NULL,'2019-02-09 20:28:25','2019-02-09 20:28:25');
=======
INSERT INTO `users` VALUES (1,'Dennis Muoki',NULL,1,'+254713653112','muokid3@gmail.com','$2y$10$5Tf07ADgOESV4to4wTrq1.RLqtDQKzJRVge20.JqzyDAQbcsF6.wa',NULL,0,NULL,NULL,NULL,NULL,NULL,0,0,0,NULL,NULL),(2,'Pauline Weku',NULL,1,'254726986977','pauline@ke.ke','',NULL,0,NULL,NULL,'2019-02-09 20:28:25','2019-02-09 20:28:25',NULL,0,0,0,NULL,NULL),(3,'ANTONY NJOROGE THIONGO',NULL,4,'0726498973','0726498973@urbantap.com','$2y$10$5Bw.lNGwQbxAuVP0ysWUleAyOgiEcJF3R9EAS1IkMaaAnhWroOFru',NULL,0,NULL,NULL,NULL,NULL,NULL,0,0,0,NULL,NULL),(4,'John','Doe',100,'254726986944','johndoe@gmail.com','$2y$10$ld2oi7yVCT/gtLcY1PempOQujQjk.PJLHSc1.XKR373bqbTwAqvuS',NULL,0,'9055',20,'2019-04-07 16:56:19','2019-04-07 16:56:19',NULL,0,0,0,NULL,NULL),(5,'John','Doe',100,'254726986944','kabuyanga@gmail.com','$2y$10$hfmSKpoFp53TXTRKRKmb9OYDk1EFe2ajA2q4XB/YJgBlj0cHUgFhO',NULL,0,'4734',20,'2019-04-07 17:03:24','2019-04-07 17:03:24',NULL,0,0,0,NULL,NULL),(6,'John','Doe',100,'254726986944','kabuyanga23@gmail.com','$2y$10$3SuGHWTi0BUKWE.1uBp2KOu2ty5PMah/7oCp21EN3.xsuPJLkarIi',NULL,0,'9078',20,'2019-04-07 17:05:17','2019-04-07 17:05:17',NULL,0,0,0,NULL,NULL),(7,'John','Doe',100,'254726986944','kabuyanga27@gmail.com','$2y$10$wogPS/CbG6qdPm0klqmn1eZyq5vQgnhYh99YtwqwPtiS64kjvDtXC',NULL,0,'3675',20,'2019-04-07 17:07:55','2019-04-07 17:07:55',NULL,0,0,0,NULL,NULL),(8,'Evans','Wanyamam',100,'254726986944','Wanyama@gmail.com','$2y$10$ZeGGlaz5mmFXcAVdGOiG4uBPx5mx6OXDnxh5ipwEwFWe9ZYdMk7Uy',NULL,0,'8890',20,'2019-04-08 06:28:13','2019-04-08 06:28:13',NULL,0,0,0,NULL,NULL),(9,'Evans','Wachiye',100,'254726986944','okwawanyama@gmail.com','$2y$10$4GQDB28FVvH6LjouF51Vs.TrzhJ/HMi7VY71SHrhuQ4fhWs.UJwCe',NULL,0,'5362',20,'2019-04-08 06:31:59','2019-04-08 06:31:59',NULL,0,0,0,NULL,NULL),(10,'Titus','Githiru',100,'254726986944','titusgithiru@gmail.com','$2y$10$4JgEmTDSxz7BflZ0XDaROOD7Cpesj2wAYrNOIc8f.ttPuHcvzXaDy',NULL,0,'1159',20,'2019-04-10 18:50:57','2019-04-10 18:50:57',5910,0,0,0,NULL,NULL),(11,'ANTONY','',4,'254726498973','254726498973@urbantap.co.ke','$2y$10$tjc8OE06urUjFimZ4CAqp.qH9jyG8AZRX.W/1OPdX/jTI9WtiuQF2',NULL,0,NULL,NULL,NULL,NULL,NULL,0,0,0,NULL,NULL),(12,'Wekes Reto',NULL,100,'254735920576',NULL,'$2y$10$JQti.MRl55Hql3HAJubWT.9OWTOT19rSGuo8hhfqOqGA6StlAA5Ta',NULL,0,'0',NULL,'2019-04-22 14:07:23','2019-04-22 14:07:23',5472,0,0,0,NULL,NULL),(13,'Ericko Wachiyr',NULL,100,'254725920576',NULL,'$2y$10$9PG7AmKAlwCcDmEww4G6Y.4uaTp2I6/ZVowfg8LLiCrjAwANqSbAS',NULL,0,'0',NULL,'2019-04-22 14:09:58','2019-04-22 14:09:58',7593,0,0,0,NULL,NULL),(14,'Erito Wanyamam',NULL,100,'254725920576',NULL,'$2y$10$W.Ysv1xNSTPYGf1w9vzzs.RKqOBzHS1hQ6R7MmVwtTgQ9EEp7SR6G',NULL,0,'0',NULL,'2019-04-22 14:14:26','2019-04-22 14:14:26',7174,0,0,0,NULL,NULL);
>>>>>>> rube/service-request
=======
INSERT INTO `users` VALUES (1,'Dennis Muoki',NULL,1,'+254713653112','muokid3@gmail.com','$2y$10$5Tf07ADgOESV4to4wTrq1.RLqtDQKzJRVge20.JqzyDAQbcsF6.wa',NULL,0,NULL,NULL,NULL,NULL,NULL,0,0,0,NULL,NULL),(2,'Pauline Weku',NULL,1,'254726986977','pauline@ke.ke','',NULL,0,NULL,NULL,'2019-02-09 20:28:25','2019-02-09 20:28:25',NULL,0,0,0,NULL,NULL),(3,'ANTONY NJOROGE THIONGO',NULL,4,'0726498973','0726498973@urbantap.com','$2y$10$5Bw.lNGwQbxAuVP0ysWUleAyOgiEcJF3R9EAS1IkMaaAnhWroOFru',NULL,0,NULL,NULL,NULL,NULL,NULL,0,0,0,NULL,NULL),(4,'John','Doe',100,'254726986944','johndoe@gmail.com','$2y$10$ld2oi7yVCT/gtLcY1PempOQujQjk.PJLHSc1.XKR373bqbTwAqvuS',NULL,0,'9055',20,'2019-04-07 16:56:19','2019-04-07 16:56:19',NULL,0,0,0,NULL,NULL),(5,'John','Doe',100,'254726986944','kabuyanga@gmail.com','$2y$10$hfmSKpoFp53TXTRKRKmb9OYDk1EFe2ajA2q4XB/YJgBlj0cHUgFhO',NULL,0,'4734',20,'2019-04-07 17:03:24','2019-04-07 17:03:24',NULL,0,0,0,NULL,NULL),(6,'John','Doe',100,'254726986944','kabuyanga23@gmail.com','$2y$10$3SuGHWTi0BUKWE.1uBp2KOu2ty5PMah/7oCp21EN3.xsuPJLkarIi',NULL,0,'9078',20,'2019-04-07 17:05:17','2019-04-07 17:05:17',NULL,0,0,0,NULL,NULL),(7,'John','Doe',100,'254726986944','kabuyanga27@gmail.com','$2y$10$wogPS/CbG6qdPm0klqmn1eZyq5vQgnhYh99YtwqwPtiS64kjvDtXC',NULL,0,'3675',20,'2019-04-07 17:07:55','2019-04-07 17:07:55',NULL,0,0,0,NULL,NULL),(8,'Evans','Wanyamam',100,'254726986944','Wanyama@gmail.com','$2y$10$ZeGGlaz5mmFXcAVdGOiG4uBPx5mx6OXDnxh5ipwEwFWe9ZYdMk7Uy',NULL,0,'8890',20,'2019-04-08 06:28:13','2019-04-08 06:28:13',NULL,0,0,0,NULL,NULL),(9,'Evans','Wachiye',100,'254726986944','okwawanyama@gmail.com','$2y$10$4GQDB28FVvH6LjouF51Vs.TrzhJ/HMi7VY71SHrhuQ4fhWs.UJwCe',NULL,0,'5362',20,'2019-04-08 06:31:59','2019-04-08 06:31:59',NULL,0,0,0,NULL,NULL),(10,'Titus','Githiru',100,'254726986944','titusgithiru@gmail.com','$2y$10$4JgEmTDSxz7BflZ0XDaROOD7Cpesj2wAYrNOIc8f.ttPuHcvzXaDy',NULL,0,'1159',20,'2019-04-10 18:50:57','2019-04-10 18:50:57',5910,0,0,0,NULL,NULL),(11,'ANTONY','',4,'254726498973','254726498973@urbantap.co.ke','$2y$10$tjc8OE06urUjFimZ4CAqp.qH9jyG8AZRX.W/1OPdX/jTI9WtiuQF2',NULL,0,NULL,NULL,NULL,NULL,NULL,0,0,0,NULL,NULL),(12,'Wekes Reto',NULL,100,'254735920576',NULL,'$2y$10$JQti.MRl55Hql3HAJubWT.9OWTOT19rSGuo8hhfqOqGA6StlAA5Ta',NULL,0,'0',NULL,'2019-04-22 14:07:23','2019-04-22 14:07:23',5472,0,0,0,NULL,NULL),(13,'Ericko Wachiyr',NULL,100,'254725920576',NULL,'$2y$10$9PG7AmKAlwCcDmEww4G6Y.4uaTp2I6/ZVowfg8LLiCrjAwANqSbAS',NULL,0,'0',NULL,'2019-04-22 14:09:58','2019-04-22 14:09:58',7593,0,0,0,NULL,NULL),(14,'Erito Wanyamam',NULL,100,'254725920576',NULL,'$2y$10$W.Ysv1xNSTPYGf1w9vzzs.RKqOBzHS1hQ6R7MmVwtTgQ9EEp7SR6G',NULL,0,'0',NULL,'2019-04-22 14:14:26','2019-04-22 14:14:26',7174,0,0,0,NULL,NULL);
>>>>>>> rube/service-request
=======
INSERT INTO `users` VALUES (1,'Dennis Muoki',NULL,1,'+254713653112','muokid3@gmail.com','$2y$10$5Tf07ADgOESV4to4wTrq1.RLqtDQKzJRVge20.JqzyDAQbcsF6.wa',NULL,0,NULL,NULL,NULL,NULL,NULL,0,0,0,NULL,NULL),(2,'Pauline Weku',NULL,1,'254726986977','pauline@ke.ke','',NULL,0,NULL,NULL,'2019-02-09 20:28:25','2019-02-09 20:28:25',NULL,0,0,0,NULL,NULL),(3,'ANTONY NJOROGE THIONGO',NULL,4,'0726498973','0726498973@urbantap.com','$2y$10$5Bw.lNGwQbxAuVP0ysWUleAyOgiEcJF3R9EAS1IkMaaAnhWroOFru',NULL,0,NULL,NULL,NULL,NULL,NULL,0,0,0,NULL,NULL),(4,'John','Doe',100,'254726986944','johndoe@gmail.com','$2y$10$ld2oi7yVCT/gtLcY1PempOQujQjk.PJLHSc1.XKR373bqbTwAqvuS',NULL,0,'9055',20,'2019-04-07 16:56:19','2019-04-07 16:56:19',NULL,0,0,0,NULL,NULL),(5,'John','Doe',100,'254726986944','kabuyanga@gmail.com','$2y$10$hfmSKpoFp53TXTRKRKmb9OYDk1EFe2ajA2q4XB/YJgBlj0cHUgFhO',NULL,0,'4734',20,'2019-04-07 17:03:24','2019-04-07 17:03:24',NULL,0,0,0,NULL,NULL),(6,'John','Doe',100,'254726986944','kabuyanga23@gmail.com','$2y$10$3SuGHWTi0BUKWE.1uBp2KOu2ty5PMah/7oCp21EN3.xsuPJLkarIi',NULL,0,'9078',20,'2019-04-07 17:05:17','2019-04-07 17:05:17',NULL,0,0,0,NULL,NULL),(7,'John','Doe',100,'254726986944','kabuyanga27@gmail.com','$2y$10$wogPS/CbG6qdPm0klqmn1eZyq5vQgnhYh99YtwqwPtiS64kjvDtXC',NULL,0,'3675',20,'2019-04-07 17:07:55','2019-04-07 17:07:55',NULL,0,0,0,NULL,NULL),(8,'Evans','Wanyamam',100,'254726986944','Wanyama@gmail.com','$2y$10$ZeGGlaz5mmFXcAVdGOiG4uBPx5mx6OXDnxh5ipwEwFWe9ZYdMk7Uy',NULL,0,'8890',20,'2019-04-08 06:28:13','2019-04-08 06:28:13',NULL,0,0,0,NULL,NULL),(9,'Evans','Wachiye',100,'254726986944','okwawanyama@gmail.com','$2y$10$4GQDB28FVvH6LjouF51Vs.TrzhJ/HMi7VY71SHrhuQ4fhWs.UJwCe',NULL,0,'5362',20,'2019-04-08 06:31:59','2019-04-08 06:31:59',NULL,0,0,0,NULL,NULL),(10,'Titus','Githiru',100,'254726986944','titusgithiru@gmail.com','$2y$10$4JgEmTDSxz7BflZ0XDaROOD7Cpesj2wAYrNOIc8f.ttPuHcvzXaDy',NULL,0,'1159',20,'2019-04-10 18:50:57','2019-04-10 18:50:57',5910,0,0,0,NULL,NULL),(11,'ANTONY','',4,'254726498973','254726498973@urbantap.co.ke','$2y$10$tjc8OE06urUjFimZ4CAqp.qH9jyG8AZRX.W/1OPdX/jTI9WtiuQF2',NULL,0,NULL,NULL,NULL,NULL,NULL,0,0,0,NULL,NULL),(12,'Wekes Reto',NULL,100,'254735920576',NULL,'$2y$10$JQti.MRl55Hql3HAJubWT.9OWTOT19rSGuo8hhfqOqGA6StlAA5Ta',NULL,0,'0',NULL,'2019-04-22 14:07:23','2019-04-22 14:07:23',5472,0,0,0,NULL,NULL),(13,'Ericko Wachiyr',NULL,100,'254725920576',NULL,'$2y$10$9PG7AmKAlwCcDmEww4G6Y.4uaTp2I6/ZVowfg8LLiCrjAwANqSbAS',NULL,0,'0',NULL,'2019-04-22 14:09:58','2019-04-22 14:09:58',7593,0,0,0,NULL,NULL),(14,'Erito Wanyamam',NULL,100,'254725920576',NULL,'$2y$10$W.Ysv1xNSTPYGf1w9vzzs.RKqOBzHS1hQ6R7MmVwtTgQ9EEp7SR6G',NULL,0,'0',NULL,'2019-04-22 14:14:26','2019-04-22 14:14:26',7174,0,0,0,NULL,NULL);
>>>>>>> rube/service-request
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

<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
-- Dump completed on 2019-03-26  0:43:38
=======
-- Dump completed on 2019-04-22 22:00:36
>>>>>>> rube/service-request
=======
-- Dump completed on 2019-04-22 22:00:36
>>>>>>> rube/service-request
=======
-- Dump completed on 2019-04-22 22:00:36
>>>>>>> rube/service-request
