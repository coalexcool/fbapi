-- Adminer 4.8.3 MySQL 8.0.16 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE `footboly` /*!40100 DEFAULT CHARACTER SET utf8 */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `footboly`;

DROP TABLE IF EXISTS `leagues`;
CREATE TABLE `leagues` (
  `id_league` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_league`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `leagues` (`id_league`, `name`, `icon`, `image`) VALUES
(13,	'Lega 2',	'/',	NULL),
(14,	'Lega 2',	'/',	'/');

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id_notification` int(11) NOT NULL AUTO_INCREMENT,
  `user_from_id` int(11) NOT NULL,
  `user_middle_id` int(11) NOT NULL,
  `user_to_id` int(11) NOT NULL,
  `proposal_json` json NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  PRIMARY KEY (`id_notification`),
  KEY `user_from_id` (`user_from_id`),
  KEY `user_to_id` (`user_to_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_from_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`user_to_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `notifications_permissions`;
CREATE TABLE `notifications_permissions` (
  `id_notif_user` int(11) NOT NULL AUTO_INCREMENT,
  `id_to` int(11) NOT NULL,
  `id_permission` int(11) NOT NULL,
  `id_from` int(11) NOT NULL,
  `id_team` int(11) NOT NULL,
  PRIMARY KEY (`id_notif_user`),
  KEY `id_to` (`id_to`),
  KEY `id_permission` (`id_permission`),
  KEY `id_from` (`id_from`),
  CONSTRAINT `notifications_permissions_ibfk_1` FOREIGN KEY (`id_to`) REFERENCES `users` (`id`),
  CONSTRAINT `notifications_permissions_ibfk_2` FOREIGN KEY (`id_permission`) REFERENCES `permissions` (`id_permission`),
  CONSTRAINT `notifications_permissions_ibfk_3` FOREIGN KEY (`id_from`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `notifications_permissions` (`id_notif_user`, `id_to`, `id_permission`, `id_from`, `id_team`) VALUES
(3,	14,	1,	11,	20),
(4,	14,	1,	11,	21);

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id_permission` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `name_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `create_propose` tinyint(1) DEFAULT '0',
  `answer_propose` tinyint(1) DEFAULT '0',
  `sell_player_to_market` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `permissions` (`id_permission`, `name`, `name_code`, `create_propose`, `answer_propose`, `sell_player_to_market`) VALUES
(1,	'Amministratore',	'admin',	1,	1,	1),
(2,	'Direttore sportivo',	'ds',	1,	0,	0);

DROP TABLE IF EXISTS `players`;
CREATE TABLE `players` (
  `id_player` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `ft_id` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `qi` decimal(5,2) DEFAULT NULL,
  `qa` decimal(5,2) DEFAULT NULL,
  `id_team` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_player`),
  KEY `id_team` (`id_team`),
  CONSTRAINT `players_ibfk_1` FOREIGN KEY (`id_team`) REFERENCES `teams` (`id_team`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `players` (`id_player`, `name`, `surname`, `ft_id`, `icon`, `image`, `qi`, `qa`, `id_team`) VALUES
(62,	'a',	'a',	'1',	'.',	'.',	10.00,	12.00,	NULL);

DROP TABLE IF EXISTS `teams`;
CREATE TABLE `teams` (
  `id_team` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `id_league` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_team`),
  KEY `id_league` (`id_league`),
  CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`id_league`) REFERENCES `leagues` (`id_league`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `triggers`;
CREATE TABLE `triggers` (
  `id_trigger` tinyint(4) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `name_code` text NOT NULL,
  `points` int(11) NOT NULL,
  PRIMARY KEY (`id_trigger`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `triggers_requests`;
CREATE TABLE `triggers_requests` (
  `id_trigger_request` int(11) NOT NULL AUTO_INCREMENT,
  `id_trigger` tinyint(4) NOT NULL,
  `from_team` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_trigger_request`),
  KEY `id_trigger` (`id_trigger`),
  KEY `from_team` (`from_team`),
  CONSTRAINT `triggers_requests_ibfk_1` FOREIGN KEY (`id_trigger`) REFERENCES `triggers` (`id_trigger`),
  CONSTRAINT `triggers_requests_ibfk_2` FOREIGN KEY (`from_team`) REFERENCES `teams` (`id_team`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `user_permissions`;
CREATE TABLE `user_permissions` (
  `id_user_role` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_team` int(11) DEFAULT NULL,
  `id_permission` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_user_role`),
  UNIQUE KEY `id_user` (`id_user`),
  KEY `id_team` (`id_team`),
  KEY `id_permission` (`id_permission`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`id_team`) REFERENCES `teams` (`id_team`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_ibfk_2` FOREIGN KEY (`id_permission`) REFERENCES `permissions` (`id_permission`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` text NOT NULL,
  `name` text NOT NULL,
  `surname` text NOT NULL,
  `phone` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `token` text,
  `superadmin` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`, `username`, `name`, `surname`, `phone`, `email`, `password`, `token`, `superadmin`, `status`) VALUES
(11,	'a',	'a',	'a',	'',	'a@a.com',	'$2y$10$w25Rsg/wTyiKAP/T09bhmOm429EWSmeb4vmR3QIIGKYTUe38g64xy',	'125bf9404e6bb4630853b496493f0766cacebee6801cd2be14',	1,	0),
(13,	'User1',	'a',	'a',	NULL,	'user1@a.com',	'a',	'a',	0,	1),
(14,	'User2',	'a',	'a',	NULL,	'user2@a.com',	'a',	'a',	0,	1),
(15,	'User3',	'a',	'a',	NULL,	'user3@a.com',	'a',	'a',	0,	1),
(16,	'User4',	'a',	'a',	NULL,	'user4@a.com',	'a',	'a',	0,	1),
(17,	'User5',	'a',	'a',	NULL,	'user5@a.com',	'a',	'a',	0,	1),
(18,	'User6',	'a',	'a',	NULL,	'user6@a.com',	'a',	'a',	0,	1);

-- 2024-10-17 18:16:22
