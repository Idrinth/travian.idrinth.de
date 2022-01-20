/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE IF NOT EXISTS `travian` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin */;
USE `travian`;

CREATE TABLE IF NOT EXISTS `alliances` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id` char(36) COLLATE utf8mb4_bin NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `world` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `key` char(36) COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`aid`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `deff_calls` (
  `aid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id` char(36) COLLATE utf8mb4_bin NOT NULL,
  `key` char(36) COLLATE utf8mb4_bin NOT NULL,
  `scouts` int(10) unsigned NOT NULL DEFAULT '0',
  `troops` int(10) unsigned NOT NULL DEFAULT '0',
  `heroes` int(10) unsigned NOT NULL DEFAULT '0',
  `x` int(11) NOT NULL DEFAULT '0',
  `y` int(11) NOT NULL DEFAULT '0',
  `world` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '0',
  `creator` int(10) unsigned NOT NULL,
  `arrival` datetime NOT NULL,
  `alliance` int(10) unsigned NOT NULL DEFAULT '0',
  `player` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `advanced_troop_data` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `grain_storage` int(10) unsigned NOT NULL DEFAULT '0',
  `grain` int(10) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `grain_production` int(11) NOT NULL DEFAULT '0',
  `grain_info_hours` int(10) unsigned NOT NULL DEFAULT '2',
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `anti` tinyint(3) unsigned NOT NULL DEFAULT '50',
  PRIMARY KEY (`aid`),
  UNIQUE KEY `id` (`id`),
  KEY `alliance` (`alliance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `deff_call_supplies` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `account` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `deff_call` int(10) unsigned NOT NULL DEFAULT '0',
  `grain` int(10) unsigned NOT NULL DEFAULT '0',
  `arrival` datetime NOT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`aid`),
  KEY `deff_call` (`deff_call`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `deff_call_supports` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `creator` int(10) unsigned NOT NULL,
  `scouts` int(10) unsigned NOT NULL,
  `troops` int(10) unsigned NOT NULL,
  `hero` tinyint(4) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `arrival` datetime NOT NULL,
  `deff_call` int(10) unsigned NOT NULL,
  `account` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `troop_type` char(50) COLLATE utf8mb4_bin DEFAULT NULL,
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`),
  KEY `deff_call` (`deff_call`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `hero` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player` int(10) unsigned NOT NULL,
  `alliance` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `helmet` int(10) unsigned NOT NULL DEFAULT '0',
  `helmet_first_seen` datetime NOT NULL,
  `helmet_last_seen` datetime NOT NULL,
  `horse` int(10) unsigned NOT NULL DEFAULT '0',
  `horse_first_seen` datetime NOT NULL,
  `horse_last_seen` datetime NOT NULL,
  `left_hand` int(10) unsigned NOT NULL DEFAULT '0',
  `left_hand_first_seen` datetime NOT NULL,
  `left_hand_last_seen` datetime NOT NULL,
  `right_hand` int(10) unsigned NOT NULL DEFAULT '0',
  `right_hand_first_seen` datetime NOT NULL,
  `right_hand_last_seen` datetime NOT NULL,
  `shoes` int(10) unsigned NOT NULL DEFAULT '0',
  `shoes_first_seen` datetime NOT NULL,
  `shoes_last_seen` datetime NOT NULL,
  `armor` int(10) unsigned NOT NULL DEFAULT '0',
  `armor_first_seen` datetime NOT NULL,
  `armor_last_seen` datetime NOT NULL,
  `last_update` datetime DEFAULT NULL,
  `last_change` datetime DEFAULT NULL,
  PRIMARY KEY (`aid`),
  UNIQUE KEY `player_alliance` (`player`,`alliance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `hero_updates` (
  `hero` int(10) unsigned NOT NULL,
  `user` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`hero`,`user`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `my_hero` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL,
  `world` varchar(250) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `fighting_strength` int(10) unsigned NOT NULL,
  `off_bonus` int(10) unsigned NOT NULL,
  `deff_bonus` int(10) unsigned NOT NULL,
  `resources` int(10) unsigned NOT NULL,
  `boot_bonus` int(10) unsigned NOT NULL DEFAULT '0',
  `standard_bonus` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`),
  KEY `user_world` (`user`,`world`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `resource_pushes` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id` char(36) COLLATE utf8mb4_bin NOT NULL,
  `key` char(36) COLLATE utf8mb4_bin NOT NULL,
  `grain` int(10) unsigned NOT NULL,
  `iron` int(10) unsigned NOT NULL,
  `lumber` int(10) unsigned NOT NULL,
  `clay` int(10) unsigned NOT NULL,
  `resources` int(10) unsigned NOT NULL,
  `x` int(10) unsigned NOT NULL,
  `player` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `y` int(10) unsigned NOT NULL,
  `world` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `alliance` int(10) unsigned NOT NULL DEFAULT '0',
  `arrival` datetime NOT NULL,
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `creator` int(10) unsigned NOT NULL,
  PRIMARY KEY (`aid`),
  UNIQUE KEY `id` (`id`),
  KEY `alliance` (`alliance`),
  KEY `world` (`world`(191)),
  KEY `arrival` (`arrival`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `resource_push_supplies` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resource_push` int(10) unsigned NOT NULL DEFAULT '0',
  `creator` int(10) unsigned NOT NULL,
  `account` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `crop` int(10) unsigned NOT NULL,
  `iron` int(10) unsigned NOT NULL,
  `clay` int(10) unsigned NOT NULL,
  `lumber` int(10) unsigned NOT NULL,
  `arrival` datetime NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`aid`),
  KEY `resource_push` (`resource_push`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `troops` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `world` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '0',
  `x` int(11) NOT NULL,
  `y` int(11) NOT NULL,
  `tribe` enum('gaul','roman','teuton','egyptian','hun') COLLATE utf8mb4_bin NOT NULL,
  `soldier1` int(10) unsigned NOT NULL DEFAULT '0',
  `soldier2` int(10) unsigned NOT NULL DEFAULT '0',
  `soldier3` int(10) unsigned NOT NULL DEFAULT '0',
  `soldier4` int(10) unsigned NOT NULL DEFAULT '0',
  `soldier5` int(10) unsigned NOT NULL DEFAULT '0',
  `soldier6` int(10) unsigned NOT NULL DEFAULT '0',
  `ram` int(10) unsigned NOT NULL DEFAULT '0',
  `catapult` int(10) unsigned NOT NULL DEFAULT '0',
  `settler` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `chief` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hero` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tournament_square` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`),
  KEY `user` (`user`),
  KEY `world_user` (`world`(191),`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `troop_updates` (
  `aid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(11) unsigned NOT NULL,
  `offensive` int(11) unsigned NOT NULL,
  `multipurpose` int(11) unsigned NOT NULL,
  `defensive` int(11) unsigned NOT NULL,
  `scouts` int(11) unsigned NOT NULL,
  `date` date NOT NULL,
  `world` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`),
  KEY `user_date_world` (`user`,`date`,`world`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `users` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `discord_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `discriminator` char(4) COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`aid`),
  UNIQUE KEY `discord_id` (`discord_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `user_alliance` (
  `user` int(10) unsigned NOT NULL,
  `alliance` int(10) unsigned NOT NULL,
  `rank` enum('Follower','Member','High Council','Creator') COLLATE utf8mb4_bin NOT NULL DEFAULT 'Follower',
  PRIMARY KEY (`user`,`alliance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `user_deff_call` (
  `user` int(10) unsigned NOT NULL,
  `deff_call` int(10) unsigned NOT NULL,
  `advanced` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user`,`deff_call`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `user_world` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL,
  `world` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`aid`),
  KEY `user_world` (`user`,`world`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `world_updates` (
  `world` varchar(250) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `updated` datetime DEFAULT NULL,
  `lastUsed` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`world`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `x_world` (
  `field_id` int(10) NOT NULL,
  `x` int(11) NOT NULL,
  `y` int(11) NOT NULL,
  `tribe` int(10) NOT NULL,
  `village_id` int(11) NOT NULL DEFAULT '0',
  `village_name` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `player_id` int(11) NOT NULL DEFAULT '0',
  `player_name` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '0',
  `alliance_id` int(11) NOT NULL DEFAULT '0',
  `alliance_name` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '0',
  `population` int(11) NOT NULL DEFAULT '0',
  `region` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `is_capital` tinyint(4) NOT NULL,
  `is_city` tinyint(4) DEFAULT NULL,
  `points` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `world_alliances` (
  `aid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `world` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `from` date NOT NULL,
  `until` date NOT NULL,
  UNIQUE KEY `aid` (`aid`),
  KEY `id` (`id`,`world`(191)),
  KEY `name` (`name`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `world_players` (
  `aid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `from` date NOT NULL,
  `until` date NOT NULL,
  `alliance` int(10) unsigned NOT NULL DEFAULT '0',
  `world` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`aid`),
  KEY `name` (`name`(191)),
  KEY `id` (`id`,`world`(191)) USING BTREE,
  KEY `alliance` (`world`(191),`alliance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `world_villages` (
  `aid` bigint(20) NOT NULL AUTO_INCREMENT,
  `id` int(10) unsigned NOT NULL,
  `x` smallint(6) NOT NULL DEFAULT '0',
  `y` smallint(6) NOT NULL DEFAULT '0',
  `world` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `player` int(10) unsigned NOT NULL DEFAULT '0',
  `day` date NOT NULL,
  `population` int(10) unsigned NOT NULL,
  `tribe` enum('roman','gaul','egyptian','teuton','hun') COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`aid`),
  KEY `world_player` (`world`(191),`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
