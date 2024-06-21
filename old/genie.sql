-- MySQL dump 10.13  Distrib 8.0.37, for Linux (x86_64)
--
-- Host: localhost    Database: genealogy
-- ------------------------------------------------------
-- Server version	8.0.37-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `family_tree`
--

DROP TABLE IF EXISTS `family_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `family_tree` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family_tree`
--

LOCK TABLES `family_tree` WRITE;
/*!40000 ALTER TABLE `family_tree` DISABLE KEYS */;
INSERT INTO `family_tree` VALUES (1,1,'Andalous','Arbre de famille','2024-06-14 02:52:52');
/*!40000 ALTER TABLE `family_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gender`
--

DROP TABLE IF EXISTS `gender`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gender` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gender`
--

LOCK TABLES `gender` WRITE;
/*!40000 ALTER TABLE `gender` DISABLE KEYS */;
/*!40000 ALTER TABLE `gender` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `person`
--

DROP TABLE IF EXISTS `person`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person` (
  `id` int NOT NULL AUTO_INCREMENT,
  `family_tree_id` int NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `place_of_birth` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `date_of_death` date DEFAULT NULL,
  `place_of_death` varchar(255) DEFAULT NULL,
  `gender_id` int DEFAULT NULL,
  `spouse_id` int DEFAULT NULL,
  `optional_fields` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=232 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `person`
--

LOCK TABLES `person` WRITE;
/*!40000 ALTER TABLE `person` DISABLE KEYS */;
INSERT INTO `person` VALUES (8,1,'Fouzia','Andalsi',NULL,NULL,NULL,NULL,2,NULL,NULL),(9,1,'Nabil','El Andaloussi','1969-04-09','Casablanca',NULL,NULL,1,NULL,NULL),(10,1,'Imane','El Andaloussi','1985-01-02','Casablanca',NULL,NULL,2,NULL,NULL),(11,1,'Youness','El Andaloussi','1974-08-17','Casablanca',NULL,NULL,1,NULL,NULL),(12,1,'Leila','Andalsi','1950-02-26','Fes',NULL,NULL,2,NULL,NULL),(13,1,'Mohamed','Manjra','1950-06-06','Fes',NULL,NULL,1,NULL,NULL),(14,1,'Anass','Manjra','1974-02-26','Marrakech',NULL,NULL,1,NULL,NULL),(15,1,'Houda','Manjra','1972-06-26','Asfi',NULL,NULL,2,NULL,NULL),(16,1,'Karim','Manjra','1974-02-26',NULL,NULL,NULL,1,NULL,NULL),(17,1,'Mohamed','Andalsi',NULL,NULL,NULL,NULL,1,NULL,NULL),(18,1,'Hayat','Andalsi',NULL,NULL,NULL,NULL,2,NULL,NULL),(19,1,'Zahra','Andalsi',NULL,NULL,NULL,NULL,1,NULL,NULL),(20,1,'Othmane','Andalsi',NULL,NULL,NULL,NULL,1,NULL,NULL),(21,1,'Zineb','Andalsi',NULL,NULL,NULL,NULL,1,NULL,NULL),(22,1,'Kamal','Andalsi',NULL,NULL,NULL,NULL,1,NULL,NULL),(23,1,'Karima','Andalsi',NULL,NULL,NULL,NULL,2,NULL,NULL),(24,1,'Radia','Loudiyi Cherrat',NULL,'',NULL,'',2,NULL,NULL),(25,1,'Hassan','Andalsi',NULL,NULL,NULL,NULL,1,NULL,NULL),(26,1,'Farida','Andaloussi',NULL,NULL,NULL,NULL,2,NULL,NULL),(27,1,'Abdou','Bensouda',NULL,NULL,NULL,NULL,1,NULL,NULL),(28,1,'Fouad','Bensouda',NULL,NULL,NULL,NULL,1,NULL,NULL),(29,1,'Badr','Bensouda',NULL,NULL,NULL,NULL,1,NULL,NULL),(30,1,'Fadwa','Bensouda',NULL,NULL,NULL,NULL,1,NULL,NULL),(31,1,'Karima','Andaloussi',NULL,NULL,NULL,NULL,2,NULL,NULL),(32,1,'Gérard','Letourneau',NULL,NULL,NULL,NULL,1,NULL,NULL),(33,1,'Stéphane','Letourneau',NULL,NULL,NULL,NULL,1,NULL,NULL),(34,1,'Franck','Letourneau',NULL,NULL,NULL,NULL,1,NULL,NULL),(35,1,'Mohamed','Andaloussi',NULL,NULL,NULL,NULL,1,NULL,NULL),(36,1,'Kamal','Andaloussi',NULL,NULL,NULL,NULL,1,NULL,NULL),(37,1,'Nadia','Andaloussi',NULL,NULL,NULL,NULL,2,NULL,NULL),(38,1,'Samira','Andaloussi',NULL,NULL,NULL,NULL,2,NULL,NULL),(41,1,'Abdellatif','El Andaloussi','1942-04-28','Oujda',NULL,NULL,1,NULL,NULL),(42,1,'Aicha','Andaloussi',NULL,NULL,NULL,NULL,2,NULL,NULL),(43,1,'Fatema','Andaloussi',NULL,NULL,NULL,NULL,2,NULL,NULL),(44,1,'Abdelaziz','Andaloussi',NULL,'',NULL,'',1,NULL,NULL),(45,1,'Ali','Andaloussi',NULL,NULL,NULL,NULL,1,NULL,NULL),(46,1,'Soumaya','Andaloussi',NULL,NULL,NULL,NULL,1,NULL,NULL),(47,1,'Rachid','Andaloussi',NULL,NULL,NULL,NULL,1,NULL,NULL),(48,1,'Abdelhaq','Andaloussi',NULL,NULL,NULL,NULL,1,NULL,NULL),(49,1,'Michelle','Ferré',NULL,NULL,NULL,NULL,2,NULL,NULL),(50,1,'Jamil','Andaloussi',NULL,NULL,NULL,NULL,1,NULL,NULL),(51,1,'Reda','Andaloussi',NULL,NULL,NULL,NULL,1,NULL,NULL),(52,1,'Mohamed','El Andaloussi',NULL,'',NULL,'',1,NULL,NULL),(53,1,'Zineb','Belcadi Andaloussi',NULL,'',NULL,'',2,NULL,NULL),(54,1,'Hamid','Faiz',NULL,NULL,NULL,NULL,1,NULL,NULL),(55,1,'Hassan','Faiz',NULL,NULL,NULL,NULL,1,NULL,NULL),(56,1,'Jawad','Faiz',NULL,NULL,NULL,NULL,1,NULL,NULL),(57,1,'Saida','Faiz',NULL,'',NULL,'',2,NULL,NULL),(58,1,'Mohamed','Faiz',NULL,NULL,NULL,NULL,1,NULL,NULL),(59,1,'Lina','Faiz',NULL,NULL,NULL,NULL,2,NULL,NULL),(60,1,'Malak','Faiz',NULL,NULL,NULL,NULL,2,NULL,NULL),(61,1,'Meriem','Faiz',NULL,NULL,NULL,NULL,2,NULL,NULL),(62,1,'Ali','Faiz',NULL,NULL,NULL,NULL,1,NULL,NULL),(63,1,'Nabil','Jalal',NULL,NULL,NULL,NULL,1,NULL,NULL),(64,1,'Ryan','Jalal',NULL,NULL,NULL,NULL,1,NULL,NULL),(65,1,'Ouassim','Jalal',NULL,NULL,NULL,NULL,1,NULL,NULL),(66,1,'Houda','Lahjomri',NULL,NULL,NULL,NULL,2,NULL,NULL),(67,1,'Adam','Manjra',NULL,NULL,NULL,NULL,1,NULL,NULL),(68,1,'Youssef','Manjra',NULL,NULL,NULL,NULL,1,NULL,NULL),(69,1,'Omar','Loudiyi',NULL,NULL,NULL,NULL,1,NULL,NULL),(70,1,'Kais','Loudiyi',NULL,NULL,NULL,NULL,1,NULL,NULL),(71,1,'Noama','Loudiyi',NULL,NULL,NULL,NULL,2,NULL,NULL),(72,1,'Saad','Loudiyi',NULL,NULL,NULL,NULL,1,NULL,NULL),(73,1,'Mohamed','Boutaleb',NULL,NULL,NULL,NULL,1,NULL,NULL),(74,1,'Maha','Boutaleb',NULL,'',NULL,'',2,NULL,NULL),(75,1,'Siham','Boutaleb',NULL,NULL,NULL,NULL,2,NULL,NULL),(76,1,'Tahar','Manjra',NULL,NULL,NULL,NULL,1,NULL,NULL),(77,1,'Yasmine','Manjra',NULL,NULL,NULL,NULL,2,NULL,NULL),(78,1,'Mounia','Manjra',NULL,NULL,NULL,NULL,2,NULL,NULL),(79,1,'Maria','Manjra',NULL,NULL,NULL,NULL,2,NULL,NULL),(80,1,'Ouadia','Loudiyi',NULL,NULL,NULL,NULL,2,NULL,NULL),(81,1,'Karim','Tazi',NULL,NULL,NULL,NULL,1,NULL,NULL),(82,1,'Taha','Tazi',NULL,NULL,NULL,NULL,1,NULL,NULL),(83,1,'Lilya','Tazi',NULL,NULL,NULL,NULL,1,NULL,NULL),(84,1,'Alaa','Andalsi',NULL,NULL,NULL,NULL,2,NULL,NULL),(85,1,'Selma','Andalsi',NULL,NULL,NULL,NULL,2,NULL,NULL),(86,1,'Aya','Andalsi',NULL,NULL,NULL,NULL,2,NULL,NULL),(87,1,'Sajedah','Andalsi',NULL,NULL,NULL,NULL,2,NULL,NULL),(88,1,'Dounia','El Manaa',NULL,NULL,NULL,NULL,2,NULL,NULL),(89,1,'Amine','El Manaa',NULL,NULL,NULL,NULL,1,NULL,NULL),(90,1,'Maelie','Letourneau',NULL,NULL,NULL,NULL,2,NULL,NULL),(91,1,'Yann','Letourneau',NULL,NULL,NULL,NULL,1,NULL,NULL),(92,1,'Abdelali','Andaloussi',NULL,'',NULL,'',1,NULL,NULL),(93,1,'Ahmed','Andaloussi',NULL,NULL,NULL,NULL,1,NULL,NULL),(94,1,'Latefa','Serghini',NULL,NULL,NULL,NULL,2,NULL,NULL),(95,1,'Mohamed','Belcadi',NULL,NULL,NULL,NULL,1,NULL,NULL),(96,1,'Mehdi','Belcadi',NULL,NULL,NULL,NULL,1,NULL,NULL),(97,1,'Faouzi','Belkadi',NULL,NULL,NULL,NULL,1,NULL,NULL),(98,1,'Hakima','Belkadi',NULL,NULL,NULL,NULL,2,NULL,NULL),(99,1,'Majida','Belkadi',NULL,NULL,NULL,NULL,1,NULL,NULL),(100,1,'Loubna','Belkadi',NULL,NULL,NULL,NULL,2,NULL,NULL),(101,1,'Bouchra','Belkadi',NULL,NULL,NULL,NULL,2,NULL,NULL),(102,1,'Mohamed','Belcadi Fils',NULL,NULL,NULL,NULL,1,NULL,NULL),(103,1,'Fatmika','Belkadi',NULL,NULL,NULL,NULL,2,NULL,NULL),(104,1,'Rabia','Htala',NULL,NULL,NULL,NULL,2,NULL,NULL),(105,1,'Fedoul','Kessara',NULL,'',NULL,'',2,NULL,NULL),(106,1,'Amal','Belcadi',NULL,NULL,NULL,NULL,1,NULL,NULL),(107,1,'Said','Belcadi',NULL,NULL,NULL,NULL,1,NULL,NULL),(108,1,'Khalil','Belcadi',NULL,NULL,NULL,NULL,1,NULL,NULL),(109,1,'Khadija','Belcadi',NULL,NULL,NULL,NULL,1,NULL,NULL),(110,1,'Abdesslam','Belcadi',NULL,NULL,NULL,NULL,1,NULL,NULL),(111,1,'Nacera','Belcadi',NULL,NULL,NULL,NULL,2,NULL,NULL),(112,1,'Omar','Belcadi',NULL,NULL,NULL,NULL,1,NULL,NULL),(113,1,'Lamiae','Belqadi',NULL,NULL,NULL,NULL,2,NULL,NULL),(114,1,'Nacer','Benkirane',NULL,NULL,NULL,NULL,1,NULL,NULL),(115,1,'Yassine','Benkirane',NULL,NULL,NULL,NULL,1,NULL,NULL),(116,1,'Rita','Benkirane',NULL,NULL,NULL,NULL,2,NULL,NULL),(117,1,'Mohamed','Loudiyi Cherrat',NULL,NULL,NULL,NULL,1,NULL,NULL),(118,1,'Fatma','Loudiyi Cherrat',NULL,NULL,NULL,NULL,1,NULL,NULL),(119,1,'Batoul','Loudiyi Cherrat',NULL,NULL,NULL,NULL,2,NULL,NULL),(120,1,'Ahmed','Loudiyi Cherrat',NULL,NULL,NULL,NULL,1,NULL,NULL),(121,1,'Meftaha','Loudiyi Cherrat',NULL,NULL,NULL,NULL,2,NULL,NULL),(122,1,'Mohamed','Loudiyi Cherrat Pere',NULL,NULL,NULL,NULL,1,NULL,NULL),(123,1,'Kheddouj','Manjra',NULL,NULL,NULL,NULL,2,NULL,NULL),(124,1,'Amine','Andalsi',NULL,NULL,NULL,NULL,1,NULL,NULL),(125,1,'Yahya','Andalsi',NULL,NULL,NULL,NULL,1,NULL,NULL),(126,1,'Radia','Andalsi',NULL,NULL,NULL,NULL,2,NULL,NULL),(127,1,'Hasna','Jalal',NULL,NULL,NULL,NULL,2,NULL,NULL),(128,1,'Mohamed','Mejbar',NULL,NULL,NULL,NULL,1,NULL,NULL),(129,1,'Abderrahim','Mejbar',NULL,NULL,NULL,NULL,1,NULL,NULL),(130,1,'Aziz','Mejbar',NULL,NULL,NULL,NULL,1,NULL,NULL),(131,1,'Driss','Mejbar',NULL,NULL,NULL,NULL,1,NULL,NULL),(132,1,'Amina','Mejbar',NULL,NULL,NULL,NULL,1,NULL,NULL),(133,1,'Abdelfettah','Mejbar',NULL,NULL,NULL,NULL,1,NULL,NULL),(134,1,'Saida','Mejbar',NULL,NULL,NULL,NULL,2,NULL,NULL),(135,1,'Mekki','Chraibi',NULL,NULL,NULL,NULL,1,NULL,NULL),(136,1,'Mohamed','Chraibi',NULL,NULL,NULL,NULL,1,NULL,NULL),(137,1,'Rachida','Chraibi',NULL,NULL,NULL,NULL,1,NULL,NULL),(138,1,'Abdellatif','Chraibi',NULL,NULL,NULL,NULL,1,NULL,NULL),(139,1,'Souad','Chraibi',NULL,NULL,NULL,NULL,1,NULL,NULL),(140,1,'Ahmed','Chraibi',NULL,NULL,NULL,NULL,1,NULL,NULL),(141,1,'Zineb','Chraibi',NULL,NULL,NULL,NULL,2,NULL,NULL),(142,5,'Granpa','Smurf',NULL,NULL,NULL,NULL,1,NULL,NULL),(143,5,'Granma','Smurf',NULL,NULL,NULL,NULL,2,NULL,NULL),(144,5,'Daddy','Smurf',NULL,NULL,NULL,NULL,1,NULL,NULL),(145,5,'Mommy','Smurf',NULL,NULL,NULL,NULL,2,NULL,NULL),(146,5,'Alfred','Smurf',NULL,NULL,NULL,NULL,1,NULL,NULL),(147,5,'Smurfette','Smurf',NULL,NULL,NULL,NULL,1,NULL,NULL),(148,5,'Uncle','Smurf',NULL,NULL,NULL,NULL,1,NULL,NULL),(149,5,'Auntie','Smurf',NULL,NULL,NULL,NULL,1,NULL,NULL),(150,5,'Gargamel','Malefic',NULL,NULL,NULL,NULL,1,NULL,NULL),(151,5,'Junior','Gargamel',NULL,NULL,NULL,NULL,1,NULL,NULL),(152,5,'Ms','Gargamel',NULL,NULL,NULL,NULL,2,NULL,NULL),(153,1,'Faïçal ','El Krari',NULL,NULL,NULL,NULL,1,NULL,NULL),(154,1,'Ali','El Krari',NULL,NULL,NULL,NULL,1,NULL,NULL),(155,1,'Yasmine','Boutaleb',NULL,NULL,NULL,NULL,1,NULL,NULL),(156,1,'Mamoun','Boutaleb ',NULL,NULL,NULL,NULL,1,NULL,NULL),(157,1,'Mohamed kamal','Andaloussi',NULL,'',NULL,'',1,NULL,NULL),(158,1,'Najah','Andaloussi',NULL,NULL,NULL,NULL,2,NULL,NULL),(159,1,'Mounira','Andaloussi',NULL,NULL,NULL,NULL,2,NULL,NULL),(160,1,'Kenza','Belqadi',NULL,NULL,NULL,NULL,1,NULL,NULL),(161,1,'Moulay Ahmed','Belqadi',NULL,NULL,NULL,NULL,1,NULL,NULL),(162,1,'Yasmine','Lebonté',NULL,NULL,NULL,NULL,2,NULL,NULL),(163,1,'Sébastien','Lebonté',NULL,NULL,NULL,NULL,1,NULL,NULL),(164,1,'Fatéma','Andaloussi',NULL,'',NULL,'',2,NULL,NULL),(165,1,'Zineb bent Aziz','Andaloussi',NULL,NULL,NULL,NULL,2,NULL,NULL),(166,1,'Radia','Boutaleb Andaloussi',NULL,NULL,NULL,NULL,2,NULL,NULL),(167,1,'Basma','El Krari',NULL,NULL,NULL,NULL,2,NULL,NULL),(168,1,'Mehdi','El Krari',NULL,NULL,NULL,NULL,1,NULL,NULL),(169,1,'Zhor','Sajid',NULL,'',NULL,'',2,NULL,NULL),(170,1,'Hnia','El Yafi',NULL,NULL,NULL,NULL,2,NULL,NULL),(171,1,'Mohamed','Sajid',NULL,NULL,NULL,NULL,1,NULL,NULL),(172,1,'Saida','Sajid',NULL,NULL,NULL,NULL,2,NULL,NULL),(173,1,'Fatiha','Sajid',NULL,NULL,NULL,NULL,2,NULL,NULL),(174,1,'Khadija','Sajid',NULL,'',NULL,'',1,NULL,NULL),(175,1,'Bahija','El Krari',NULL,NULL,NULL,NULL,2,NULL,NULL),(176,1,'Abdelhaq','Bensouda',NULL,NULL,NULL,NULL,1,NULL,NULL),(177,1,'Latefa','Bensouda',NULL,NULL,NULL,NULL,1,NULL,NULL),(178,1,'Khadija','Bensouda',NULL,NULL,NULL,NULL,1,NULL,NULL),(180,1,'Fouzia','Bensouda',NULL,NULL,NULL,NULL,1,NULL,NULL),(181,1,'Jalil','Bensouda',NULL,NULL,NULL,NULL,1,NULL,NULL),(182,1,'Mohamed','Bensouda',NULL,NULL,NULL,NULL,1,NULL,NULL),(183,1,'Houda','Bensouda',NULL,NULL,NULL,NULL,2,NULL,NULL),(184,1,'Loubna','Bensouda',NULL,NULL,NULL,NULL,1,NULL,NULL),(185,1,'Naima','Bensouda',NULL,NULL,NULL,NULL,2,NULL,NULL),(186,1,'Hassan','Kessara',NULL,NULL,NULL,NULL,1,NULL,NULL),(187,1,'Zineb','Kessara',NULL,NULL,NULL,NULL,2,NULL,NULL),(188,1,'Abdelkrim','Mechrafi',NULL,NULL,NULL,NULL,1,NULL,NULL),(189,1,'Zoubida','Mechrafi',NULL,NULL,NULL,NULL,1,NULL,NULL),(190,1,'Fatem Zahra','Mechrafi',NULL,NULL,NULL,NULL,2,NULL,NULL),(191,1,'Abdeslam','Mechrafi',NULL,NULL,NULL,NULL,1,NULL,NULL),(192,1,'Moha','Belcadi','2024-12-12',NULL,NULL,NULL,1,NULL,NULL),(193,1,'Nadia','Belcadi',NULL,NULL,NULL,NULL,2,NULL,NULL),(194,1,'Wafa','Belcadi',NULL,NULL,NULL,NULL,2,NULL,NULL),(195,1,'Mounia','Belcadi',NULL,NULL,NULL,NULL,2,NULL,NULL),(196,1,'Hassan','Belcadi',NULL,NULL,NULL,NULL,1,NULL,NULL),(197,1,'Houssein','Belcadi',NULL,NULL,NULL,NULL,1,NULL,NULL),(198,1,'Naima','El Bouab',NULL,NULL,NULL,NULL,2,NULL,NULL),(199,1,'Rajaa','El Bouab',NULL,NULL,NULL,NULL,2,NULL,NULL),(200,1,'Sidi Ahmed','Belcadi',NULL,NULL,NULL,NULL,1,NULL,NULL),(201,1,'Zakia','Benchekroun',NULL,NULL,NULL,NULL,2,NULL,NULL),(202,1,'Col Azzedine','Benchekroun',NULL,NULL,NULL,NULL,1,NULL,NULL),(203,1,'Mohamed','Benchekroun',NULL,NULL,NULL,NULL,1,NULL,NULL),(204,1,'Assia','Benchekroun',NULL,NULL,NULL,NULL,2,NULL,NULL),(205,1,'Lilya','Bensouda',NULL,NULL,NULL,NULL,1,NULL,NULL),(206,1,'Chemsee','Moussadak',NULL,NULL,NULL,NULL,2,NULL,NULL),(207,1,'Tarek','Moussadak',NULL,NULL,NULL,NULL,1,NULL,NULL),(208,1,'Fatema','Moussadak',NULL,NULL,NULL,NULL,2,NULL,NULL),(209,1,'Sirine','Bensouda',NULL,NULL,NULL,NULL,1,NULL,NULL),(210,1,'Ilyas','Bensouda',NULL,NULL,NULL,NULL,1,NULL,NULL),(211,1,'Maeline','Moussadak',NULL,'',NULL,'',2,NULL,NULL),(212,1,'Zhor Jouhara','Benyahya',NULL,NULL,NULL,NULL,2,NULL,NULL),(213,1,'Latefa','Benyahya',NULL,NULL,NULL,NULL,2,NULL,NULL),(214,1,'Mehdi','Benyahya',NULL,NULL,NULL,NULL,1,NULL,NULL),(215,1,'Abdellah','Bennis',NULL,NULL,NULL,NULL,1,NULL,NULL),(216,1,'Wafaa','Bennis',NULL,NULL,NULL,NULL,2,NULL,NULL),(217,1,'Sanaa','Bennis',NULL,NULL,NULL,NULL,2,NULL,NULL),(218,1,'Adil','Bennis',NULL,NULL,NULL,NULL,1,NULL,NULL),(219,1,'Driss Ben','Bennani',NULL,NULL,NULL,NULL,1,NULL,NULL),(220,1,'Adam','Bennani',NULL,NULL,NULL,NULL,1,NULL,NULL),(221,1,'Mona','Bennani',NULL,NULL,NULL,NULL,2,NULL,NULL),(222,1,'Said','Ettair',NULL,NULL,NULL,NULL,1,NULL,NULL),(223,1,'Ines','Ettair',NULL,NULL,NULL,NULL,2,NULL,NULL),(224,1,'Ilyas','Ettair',NULL,NULL,NULL,NULL,1,NULL,NULL),(225,1,'Rania','Ettair',NULL,NULL,NULL,NULL,2,NULL,NULL),(226,1,'Ayline','Bennis',NULL,NULL,NULL,NULL,1,NULL,NULL),(227,1,'Hadi','Housseini',NULL,'',NULL,'',1,NULL,NULL),(228,1,'Meriem','Housseini',NULL,'',NULL,'',2,NULL,NULL),(229,1,'Tahar Pere','Manjra',NULL,NULL,NULL,NULL,1,NULL,NULL),(230,1,'Fatema','Chraibi',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(231,1,'Abderrahmane','Manjra',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `person` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `person_relationship`
--

DROP TABLE IF EXISTS `person_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person_relationship` (
  `id` int NOT NULL AUTO_INCREMENT,
  `family_tree_id` int NOT NULL,
  `person_id1` int NOT NULL,
  `person_id2` int NOT NULL,
  `relationship_type_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=481 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `person_relationship`
--

LOCK TABLES `person_relationship` WRITE;
/*!40000 ALTER TABLE `person_relationship` DISABLE KEYS */;
INSERT INTO `person_relationship` VALUES (8,1,9,8,2),(9,1,10,8,2),(10,1,11,8,2),(11,1,13,12,5),(12,1,14,12,7),(13,1,15,12,7),(14,1,9,11,1),(15,1,10,11,1),(16,1,10,9,1),(17,1,16,12,1),(19,1,16,13,7),(21,1,14,13,7),(22,1,15,13,7),(23,1,16,14,1),(24,1,15,14,1),(25,1,15,16,1),(26,1,18,17,7),(27,1,19,17,7),(28,1,20,17,7),(30,1,41,8,5),(31,1,9,41,7),(32,1,11,41,7),(33,1,10,41,7),(35,1,18,19,1),(36,1,21,19,4),(37,1,20,19,4),(38,1,18,21,1),(39,1,21,21,2),(40,1,8,22,1),(41,1,12,22,1),(42,1,23,22,5),(44,1,22,24,7),(45,1,8,24,7),(46,1,12,24,7),(47,1,8,25,7),(48,1,22,25,7),(49,1,25,25,7),(50,1,24,25,5),(51,1,41,26,1),(52,1,27,26,5),(53,1,28,26,7),(54,1,29,26,7),(55,1,30,26,7),(56,1,28,29,1),(57,1,30,29,1),(58,1,28,27,7),(61,1,18,20,4),(62,1,21,20,1),(64,1,37,35,7),(65,1,38,35,7),(66,1,32,31,5),(67,1,41,31,1),(68,1,35,31,1),(69,1,34,31,7),(70,1,33,31,7),(71,1,26,31,1),(72,1,33,32,7),(74,1,35,41,1),(75,1,36,37,1),(76,1,38,37,1),(77,1,45,44,7),(79,1,47,44,7),(80,1,46,45,1),(81,1,47,45,1),(82,1,47,46,1),(83,1,46,44,7),(84,1,41,44,1),(85,1,42,44,1),(86,1,26,44,1),(87,1,31,44,1),(88,1,41,42,1),(89,1,35,42,1),(90,1,44,42,1),(91,1,43,42,7),(92,1,44,35,1),(93,1,51,50,1),(94,1,50,48,7),(95,1,51,48,7),(96,1,49,48,5),(99,1,50,49,7),(100,1,51,49,7),(101,1,48,42,1),(102,1,31,42,1),(103,1,48,41,1),(104,1,53,52,5),(105,1,25,52,4),(106,1,41,52,7),(107,1,48,52,7),(108,1,35,52,7),(109,1,31,52,7),(110,1,42,52,7),(111,1,26,52,7),(112,1,41,53,7),(113,1,26,53,7),(114,1,31,53,7),(115,1,48,53,7),(116,1,42,53,7),(117,1,44,53,7),(118,1,54,58,7),(119,1,55,58,7),(120,1,57,58,7),(121,1,56,58,7),(123,1,18,54,5),(124,1,55,54,1),(125,1,57,54,1),(126,1,56,54,1),(127,1,57,55,1),(128,1,56,55,1),(129,1,63,19,5),(130,1,64,19,7),(131,1,65,19,7),(132,1,64,63,7),(134,1,65,63,7),(135,1,59,54,7),(136,1,60,54,7),(138,1,62,54,7),(139,1,61,54,7),(140,1,60,59,1),(141,1,61,59,1),(142,1,62,59,1),(143,1,59,18,7),(145,1,60,18,7),(146,1,61,18,7),(147,1,62,18,7),(148,1,60,61,1),(149,1,62,61,1),(150,1,66,16,5),(151,1,67,16,7),(153,1,68,16,7),(154,1,37,73,5),(157,1,74,73,7),(158,1,75,73,7),(159,1,74,37,7),(160,1,75,37,7),(161,1,74,75,1),(162,1,77,76,7),(163,1,78,76,7),(164,1,79,76,7),(165,1,78,77,1),(166,1,79,77,1),(167,1,13,76,1),(168,1,70,69,7),(169,1,72,69,7),(170,1,80,69,7),(171,1,71,69,7),(172,1,81,21,5),(173,1,82,21,7),(174,1,83,21,7),(176,1,82,81,7),(177,1,83,81,7),(178,1,83,82,1),(179,1,84,22,7),(180,1,85,22,7),(181,1,86,22,7),(182,1,87,22,7),(184,1,84,23,7),(185,1,85,23,7),(187,1,86,23,7),(188,1,87,23,7),(189,1,85,84,1),(190,1,86,84,1),(191,1,87,84,1),(192,1,86,85,1),(193,1,87,85,1),(194,1,88,46,7),(195,1,89,46,7),(196,1,90,34,1),(197,1,34,33,1),(198,1,91,33,7),(199,1,34,32,7),(200,1,41,92,1),(201,1,44,92,1),(204,1,92,52,7),(205,1,41,93,1),(206,1,44,93,1),(207,1,48,93,1),(208,1,35,93,1),(209,1,93,52,7),(210,1,42,93,1),(211,1,31,93,1),(212,1,26,93,1),(214,1,69,94,5),(215,1,70,94,7),(217,1,72,94,7),(218,1,80,94,7),(219,1,71,94,7),(220,1,53,95,1),(222,1,53,96,4),(223,1,95,96,4),(226,1,101,102,7),(228,1,100,102,7),(229,1,97,102,7),(230,1,103,102,5),(231,1,97,103,7),(232,1,98,103,7),(233,1,100,103,7),(234,1,101,103,7),(235,1,104,95,5),(236,1,105,95,5),(237,1,111,104,7),(238,1,110,104,7),(239,1,108,104,7),(240,1,106,104,7),(241,1,109,104,7),(242,1,107,104,7),(243,1,108,95,7),(244,1,107,95,7),(245,1,106,95,7),(246,1,109,95,7),(247,1,110,95,7),(248,1,112,108,7),(249,1,113,108,7),(250,1,102,95,7),(251,1,114,113,5),(252,1,115,113,7),(253,1,116,113,7),(254,1,116,115,1),(256,1,116,114,7),(257,1,115,114,7),(258,1,120,24,1),(259,1,119,24,1),(260,1,121,24,1),(261,1,69,24,1),(262,1,118,24,1),(264,1,117,24,1),(266,1,24,122,7),(268,1,125,20,7),(270,1,124,20,7),(271,1,126,20,7),(272,1,127,20,1),(273,1,121,128,5),(274,1,129,128,7),(276,1,130,128,7),(277,1,133,128,7),(278,1,134,128,7),(280,1,119,135,5),(281,1,136,135,7),(282,1,140,135,7),(283,1,137,135,7),(284,1,141,135,7),(285,1,139,135,7),(287,1,140,119,7),(288,1,136,119,7),(289,1,141,119,7),(290,1,139,119,7),(292,1,137,119,7),(293,1,99,97,1),(294,1,101,97,1),(295,1,100,97,1),(296,5,144,142,2),(298,5,143,142,5),(299,5,148,144,1),(300,5,146,144,2),(302,5,147,149,7),(303,5,148,149,5),(304,5,147,148,1),(305,5,144,145,5),(306,5,146,145,2),(307,5,152,150,5),(308,5,151,150,2),(310,1,10,153,5),(311,1,154,153,7),(312,1,122,123,5),(313,1,24,123,7),(314,1,155,37,7),(315,1,156,37,7),(316,1,155,73,7),(317,1,156,73,7),(319,1,157,36,7),(320,1,158,36,1),(321,1,36,159,5),(322,1,158,159,7),(323,1,157,159,7),(324,1,160,112,7),(325,1,161,112,7),(326,1,162,15,7),(327,1,162,163,7),(328,1,15,163,5),(329,1,164,44,5),(331,1,45,164,7),(332,1,47,164,7),(334,1,46,164,7),(335,1,165,44,7),(336,1,45,165,1),(337,1,46,165,1),(338,1,47,165,1),(339,1,165,164,7),(340,1,36,166,7),(341,1,38,166,7),(342,1,37,166,7),(343,1,36,35,7),(344,1,166,35,5),(345,1,167,153,1),(346,1,168,153,1),(347,1,17,25,7),(348,1,17,24,7),(349,1,21,17,7),(350,1,12,17,1),(351,1,8,17,1),(352,1,22,17,1),(353,1,17,169,5),(354,1,18,169,7),(355,1,19,169,7),(356,1,138,135,7),(357,1,138,119,7),(359,1,170,171,5),(360,1,169,171,7),(361,1,172,171,7),(362,1,174,171,7),(363,1,173,171,7),(364,1,174,170,7),(366,1,169,170,7),(367,1,172,170,7),(368,1,173,170,7),(369,1,132,121,7),(370,1,129,121,7),(371,1,133,121,7),(372,1,130,121,7),(373,1,134,121,7),(374,1,132,128,7),(375,1,129,132,1),(376,1,134,132,1),(377,1,133,132,1),(378,1,131,132,1),(379,1,130,132,1),(380,1,153,175,7),(381,1,29,27,7),(382,1,30,27,7),(383,1,27,176,1),(386,1,181,27,4),(387,1,180,27,4),(388,1,182,27,1),(389,1,177,27,1),(390,1,178,27,1),(391,1,176,185,5),(392,1,184,185,7),(393,1,183,185,7),(396,1,184,176,7),(397,1,183,176,7),(398,1,187,186,1),(399,1,105,186,1),(400,1,42,186,5),(401,1,188,189,4),(402,1,188,190,1),(403,1,189,190,4),(404,1,188,187,7),(405,1,190,187,7),(406,1,187,191,5),(407,1,188,191,7),(408,1,190,191,7),(409,1,195,192,1),(410,1,196,192,1),(411,1,197,192,1),(412,1,194,192,1),(413,1,193,192,1),(414,1,192,96,7),(415,1,193,96,7),(416,1,194,96,7),(417,1,196,96,7),(418,1,197,96,7),(421,1,96,198,5),(422,1,199,198,1),(423,1,200,108,7),(424,1,201,108,5),(425,1,202,203,1),(427,1,201,203,7),(428,1,204,202,5),(429,1,209,28,7),(430,1,210,28,7),(431,1,205,29,7),(432,1,206,30,7),(433,1,211,30,7),(434,1,207,30,5),(435,1,211,207,7),(436,1,206,207,7),(437,1,206,211,1),(438,1,118,214,5),(439,1,212,214,2),(440,1,213,214,7),(441,1,212,215,5),(442,1,216,215,7),(443,1,217,215,7),(444,1,218,215,7),(445,1,216,219,5),(446,1,220,219,7),(447,1,221,219,7),(448,1,220,216,7),(449,1,221,216,7),(450,1,221,220,1),(451,1,226,218,7),(452,1,227,221,5),(453,1,228,221,7),(455,1,228,227,1),(456,1,223,222,7),(457,1,224,222,7),(458,1,225,222,7),(459,1,217,222,5),(460,1,223,217,7),(461,1,224,217,7),(462,1,225,217,7),(463,1,216,212,7),(464,1,217,212,7),(465,1,218,212,7),(466,1,155,74,1),(467,1,156,74,1),(468,1,156,155,1),(469,1,75,74,1),(470,1,156,75,1),(471,1,155,75,1),(472,1,229,230,5),(473,1,229,13,7),(474,1,230,135,1),(475,1,229,76,7),(476,1,229,231,7),(477,1,230,13,7),(478,1,230,231,7),(479,1,230,76,7),(480,1,13,231,1);
/*!40000 ALTER TABLE `person_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relationship_type`
--

DROP TABLE IF EXISTS `relationship_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `relationship_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `family_tree_id` int NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relationship_type`
--

LOCK TABLES `relationship_type` WRITE;
/*!40000 ALTER TABLE `relationship_type` DISABLE KEYS */;
INSERT INTO `relationship_type` VALUES (1,1,'Fraternel'),(2,1,'Parent'),(3,1,'Ami'),(4,1,'Half Sibling'),(5,1,'Mariage'),(6,1,'Fiancailles'),(7,1,'Enfant'),(8,1,'Cousin');
/*!40000 ALTER TABLE `relationship_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
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

-- Dump completed on 2024-06-16 20:27:56
