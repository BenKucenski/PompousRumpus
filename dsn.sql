CREATE DATABASE  IF NOT EXISTS `dsn` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `dsn`;
-- MySQL dump 10.13  Distrib 8.0.22, for Win64 (x86_64)
--
-- Host: 192.168.0.70    Database: dsn
-- ------------------------------------------------------
-- Server version	5.7.13-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `friend_list`
--

DROP TABLE IF EXISTS `friend_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `friend_list` (
                               `friend_list_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                               `user_id` int(10) unsigned NOT NULL,
                               `user_guid` varchar(255) NOT NULL,
                               `remote_guid` varchar(255) NOT NULL,
                               `remote_domain` varchar(255) NOT NULL,
                               `created_at` datetime NOT NULL,
                               `is_mute` tinyint(4) NOT NULL DEFAULT '0',
                               PRIMARY KEY (`friend_list_id`),
                               UNIQUE KEY `unique_friend` (`user_guid`,`remote_guid`,`remote_domain`),
                               KEY `is_muted` (`is_mute`),
                               KEY `friend_list_user_idx` (`user_id`),
                               CONSTRAINT `friend_list_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `friend_request`
--

DROP TABLE IF EXISTS `friend_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `friend_request` (
                                  `friend_request_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                  `user_id` int(10) unsigned NOT NULL,
                                  `user_guid` varchar(255) NOT NULL,
                                  `is_sticky` tinyint(4) DEFAULT '0',
                                  `created_at` datetime NOT NULL,
                                  `completed_at` datetime DEFAULT NULL,
                                  PRIMARY KEY (`friend_request_id`),
                                  UNIQUE KEY `user_guid_UNIQUE` (`user_guid`),
                                  KEY `friend_request_user_idx` (`user_id`),
                                  CONSTRAINT `friend_request_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ip_access`
--

DROP TABLE IF EXISTS `ip_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ip_access` (
                             `ip_access_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                             `user_guid` varchar(255) NOT NULL,
                             `remote_guid` varchar(255) NOT NULL,
                             `remote_domain` varchar(255) NOT NULL,
                             `remote_addr` varchar(255) NOT NULL,
                             `created_at` datetime NOT NULL,
                             `last_seen_at` datetime NOT NULL,
                             `is_blocked` tinyint(4) NOT NULL DEFAULT '0',
                             PRIMARY KEY (`ip_access_id`),
                             UNIQUE KEY `unique_request` (`user_guid`,`remote_guid`,`remote_domain`,`remote_addr`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `post`
--

DROP TABLE IF EXISTS `post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post` (
                        `post_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                        `user_id` int(10) unsigned NOT NULL,
                        `post_guid` varchar(255) DEFAULT NULL,
                        `content` text NOT NULL,
                        `created_at` datetime NOT NULL,
                        `content_hash` varchar(255) NOT NULL,
                        PRIMARY KEY (`post_id`),
                        UNIQUE KEY `post_guid_UNIQUE` (`post_guid`),
                        KEY `post_user_id_idx` (`user_id`),
                        KEY `post_created` (`created_at`),
                        CONSTRAINT `post_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `post_response`
--

DROP TABLE IF EXISTS `post_response`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post_response` (
                                 `post_response_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                 `user_id` int(10) unsigned DEFAULT NULL,
                                 `response_guid` varchar(255) DEFAULT NULL,
                                 `post_guid` varchar(255) DEFAULT NULL,
                                 `post_domain` varchar(255) DEFAULT NULL,
                                 `content` text,
                                 `user_at_domain` varchar(255) DEFAULT NULL,
                                 `remote_hash` varchar(255) DEFAULT NULL,
                                 `created_at` datetime DEFAULT NULL,
                                 PRIMARY KEY (`post_response_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
                        `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                        `username` varchar(255) NOT NULL,
                        `password` varchar(255) NOT NULL,
                        `guid` varchar(255) NOT NULL,
                        `created_at` datetime DEFAULT NULL,
                        PRIMARY KEY (`user_id`),
                        UNIQUE KEY `username_UNIQUE` (`username`),
                        UNIQUE KEY `guid_UNIQUE` (`guid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-01-15 21:59:35
