-- SQL schema export for futsydb
CREATE DATABASE IF NOT EXISTS `futsydb` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `futsydb`;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `administrador`;
CREATE TABLE `administrador` (
  `id_admin` int unsigned NOT NULL AUTO_INCREMENT,
  `rol` varchar(10) NOT NULL DEFAULT 'Admin',
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `coleccionista`;
CREATE TABLE `coleccionista` (
  `id_collector` int unsigned NOT NULL AUTO_INCREMENT,
  `rol` enum('collector','admin') NOT NULL DEFAULT 'collector',
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` mediumblob,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_pack_open` datetime DEFAULT NULL,
  `updated_password_at` datetime DEFAULT NULL,
  `pack_balance` int unsigned NOT NULL DEFAULT '0',
  `last_pack_claim_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_collector`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `pack`;
CREATE TABLE `pack` (
  `id_pack` int unsigned NOT NULL AUTO_INCREMENT,
  `pack_name` varchar(50) NOT NULL,
  `cards_amount` int unsigned NOT NULL DEFAULT '5',
  PRIMARY KEY (`id_pack`),
  UNIQUE KEY `uq_pack_name` (`pack_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `team`;
CREATE TABLE `team` (
  `id_team` int unsigned NOT NULL AUTO_INCREMENT,
  `country` varchar(60) NOT NULL,
  `players_amount` int unsigned NOT NULL DEFAULT '0',
  `group` enum('A','B','C','D','E','F','G','H','I','J','K','L') NOT NULL,
  `team_fact` varchar(255) DEFAULT NULL,
  `flag` mediumblob NOT NULL,
  `registered_by` int unsigned NOT NULL,
  PRIMARY KEY (`id_team`),
  KEY `registered_by` (`registered_by`),
  CONSTRAINT `team_ibfk_1` FOREIGN KEY (`registered_by`) REFERENCES `administrador` (`id_admin`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `player`;
CREATE TABLE `player` (
  `id_player` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `shirtnumber` int NOT NULL,
  `position` varchar(50) NOT NULL,
  `id_team` int unsigned NOT NULL,
  `photo` mediumblob NOT NULL,
  `registered_by` int unsigned NOT NULL,
  PRIMARY KEY (`id_player`),
  KEY `registered_by` (`registered_by`),
  KEY `idx_player_team` (`id_team`),
  CONSTRAINT `player_ibfk_1` FOREIGN KEY (`id_team`) REFERENCES `team` (`id_team`),
  CONSTRAINT `player_ibfk_2` FOREIGN KEY (`registered_by`) REFERENCES `administrador` (`id_admin`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `card`;
CREATE TABLE `card` (
  `id_card` int unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int unsigned NOT NULL,
  `rarity` enum('Common','Rare','Epic','Legendary') NOT NULL,
  `card_image` mediumblob,
  PRIMARY KEY (`id_card`),
  KEY `id_player` (`id_player`),
  KEY `idx_card_rarity` (`rarity`),
  CONSTRAINT `card_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `player` (`id_player`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `packopening`;
CREATE TABLE `packopening` (
  `id_opening` int unsigned NOT NULL AUTO_INCREMENT,
  `id_collector` int unsigned DEFAULT NULL,
  `id_pack` int unsigned DEFAULT NULL,
  `open_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id_opening`),
  KEY `id_pack` (`id_pack`),
  KEY `idx_packopening_collector_date` (`id_collector`,`open_date`),
  CONSTRAINT `packopening_ibfk_1` FOREIGN KEY (`id_collector`) REFERENCES `coleccionista` (`id_collector`),
  CONSTRAINT `packopening_ibfk_2` FOREIGN KEY (`id_pack`) REFERENCES `pack` (`id_pack`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `inventory`;
CREATE TABLE `inventory` (
  `id_inventory` int unsigned NOT NULL AUTO_INCREMENT,
  `id_collector` int unsigned NOT NULL,
  `id_card` int unsigned NOT NULL,
  `quantity` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_inventory`),
  UNIQUE KEY `id_collector` (`id_collector`,`id_card`),
  KEY `id_card` (`id_card`),
  CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`id_collector`) REFERENCES `coleccionista` (`id_collector`),
  CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`id_card`) REFERENCES `card` (`id_card`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `trade`;
CREATE TABLE `trade` (
  `id_trade` int unsigned NOT NULL AUTO_INCREMENT,
  `id_sender` int unsigned NOT NULL,
  `id_receiver` int unsigned DEFAULT NULL,
  `status` enum('pending','accepted','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_trade`),
  KEY `idx_trade_status_created` (`status`,`created_at`),
  KEY `idx_trade_sender_status` (`id_sender`,`status`),
  KEY `idx_trade_receiver_status` (`id_receiver`,`status`),
  CONSTRAINT `trade_ibfk_1` FOREIGN KEY (`id_sender`) REFERENCES `coleccionista` (`id_collector`),
  CONSTRAINT `trade_ibfk_2` FOREIGN KEY (`id_receiver`) REFERENCES `coleccionista` (`id_collector`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `tradecards`;
CREATE TABLE `tradecards` (
  `id_tradecard` int unsigned NOT NULL AUTO_INCREMENT,
  `id_trade` int unsigned NOT NULL,
  `id_card` int unsigned NOT NULL,
  `quantity` int unsigned NOT NULL DEFAULT '1',
  `owner` enum('sender','receiver') NOT NULL,
  PRIMARY KEY (`id_tradecard`),
  KEY `id_trade` (`id_trade`),
  KEY `id_card` (`id_card`),
  CONSTRAINT `tradecards_ibfk_1` FOREIGN KEY (`id_trade`) REFERENCES `trade` (`id_trade`),
  CONSTRAINT `tradecards_ibfk_2` FOREIGN KEY (`id_card`) REFERENCES `card` (`id_card`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `packcards`;
CREATE TABLE `packcards` (
  `id_packcard` int unsigned NOT NULL AUTO_INCREMENT,
  `id_opening` int unsigned NOT NULL,
  `id_card` int unsigned NOT NULL,
  PRIMARY KEY (`id_packcard`),
  KEY `idx_packcards_opening` (`id_opening`),
  KEY `idx_packcards_card` (`id_card`),
  CONSTRAINT `packcards_ibfk_1` FOREIGN KEY (`id_opening`) REFERENCES `packopening` (`id_opening`),
  CONSTRAINT `packcards_ibfk_2` FOREIGN KEY (`id_card`) REFERENCES `card` (`id_card`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

SET FOREIGN_KEY_CHECKS=1;