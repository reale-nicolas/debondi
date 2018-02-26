-- MySQL dump 10.13  Distrib 5.7.21, for Linux (x86_64)
--
-- Host: 192.168.10.10    Database: debondi
-- ------------------------------------------------------
-- Server version	5.7.20-0ubuntu0.16.04.1

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
-- Table structure for table `bus_lines`
--

DROP TABLE IF EXISTS `bus_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bus_lines` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `line` int(11) NOT NULL,
  `ramal` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `interest_points` text COLLATE utf8mb4_unicode_ci,
  `neighborhoods` text COLLATE utf8mb4_unicode_ci,
  `literal_path` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bus_lines`
--

LOCK TABLES `bus_lines` WRITE;
/*!40000 ALTER TABLE `bus_lines` DISABLE KEYS */;
INSERT INTO `bus_lines` VALUES (0,1,'C','',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(1,2,'A','',NULL,NULL,NULL,'2018-01-04 00:20:48','2018-01-04 00:20:48'),(2,2,'B','',NULL,NULL,NULL,'2018-01-04 00:20:48','2018-01-04 00:20:48'),(3,2,'B','Villa-floresta',NULL,NULL,NULL,'2018-01-04 00:20:48','2018-01-04 00:20:48'),(4,2,'B','Parque-Industrial',NULL,NULL,NULL,'2018-01-04 00:20:48','2018-01-04 00:20:48'),(5,2,'C','',NULL,NULL,NULL,'2018-01-04 00:20:48','2018-01-04 00:20:48'),(6,2,'D','',NULL,NULL,NULL,'2018-01-04 00:20:48','2018-01-04 00:20:48'),(7,2,'E','',NULL,NULL,NULL,'2018-01-04 00:20:48','2018-01-04 00:20:48'),(8,2,'F','',NULL,NULL,NULL,'2018-01-04 00:20:48','2018-01-04 00:20:48'),(9,2,'G','',NULL,NULL,NULL,'2018-01-04 00:20:48','2018-01-04 00:20:48'),(11,3,'A','',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(12,3,'B','',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(13,3,'C','',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(14,4,'A','Barrio Los Sauces',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(15,4,'A','Barrio Roberto Romero',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(16,4,'B','',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(17,4,'C','',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(18,4,'D','',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(19,5,'A','Huaico-Mirasoles',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(20,5,'A','Barrio Los Profesionales',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(21,5,'A','Av. Reyes Catolicos',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(22,5,'A','Av. Sansone',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(23,5,'B','',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(24,5,'C','',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(25,6,'A','',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(26,6,'A','Huaico-Mirasoles',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(27,6,'B','',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(28,6,'C','',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(29,8,'A','',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(30,8,'B','',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57'),(31,8,'C','',NULL,NULL,NULL,'2018-01-04 01:18:57','2018-01-04 01:18:57');
/*!40000 ALTER TABLE `bus_lines` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-02-18 15:29:51