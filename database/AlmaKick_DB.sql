-- AlmaKick Database Structure (Summary Version)
-- Recommended for phpMyAdmin import

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+01:00";

-- --------------------------------------------------------
-- Database: `almakick`
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `almakick` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `almakick`;

-- --------------------------------------------------------

-- 1. Users Table (Players, Organizers, Super Admin)
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `role` enum('user','super_admin') NOT NULL DEFAULT 'user',
  `preferred_role` varchar(50) DEFAULT NULL,
  `trust_score` int(11) NOT NULL DEFAULT 100,
  `skill_rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `mvp_count` int(11) NOT NULL DEFAULT 0,
  `matches_played` int(11) NOT NULL DEFAULT 0,
  `total_goals` int(11) NOT NULL DEFAULT 0,
  `is_banned` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- 2. Matches Table (Core)
CREATE TABLE `matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `format` varchar(20) NOT NULL COMMENT 'e.g., 5v5, 7v7',
  `max_players` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'For Leaflet Maps API',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'For Leaflet Maps API',
  `visibility` enum('public','private') NOT NULL DEFAULT 'public',
  `total_cost` decimal(8,2) NOT NULL DEFAULT 0.00,
  `status` enum('open','full','finished','cancelled') NOT NULL DEFAULT 'open',
  `cancellation_reason` varchar(255) DEFAULT NULL COMMENT 'e.g., Bad weather',
  `is_urgent` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'If a player is missing',
  `result_home` int(11) DEFAULT NULL,
  `result_away` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `matches_host_id_foreign` (`host_id`),
  CONSTRAINT `matches_host_id_foreign` FOREIGN KEY (`host_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- 3. Registrations Table (Bench Logic + 1 Guest)
CREATE TABLE `registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('registered','waitlist','cancelled') NOT NULL DEFAULT 'registered',
  `has_guest` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'The Guest (+1)',
  `team` enum('home','away') DEFAULT NULL,
  `goals_scored` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `registrations_match_id_foreign` (`match_id`),
  KEY `registrations_user_id_foreign` (`user_id`),
  CONSTRAINT `registrations_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `registrations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- 4. Evaluations Table (Post-match feedback, MVP and Reports)
CREATE TABLE `evaluations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL,
  `evaluator_id` int(11) NOT NULL,
  `evaluated_id` int(11) NOT NULL,
  `skill_vote` tinyint(4) DEFAULT NULL COMMENT 'Rating from 1 to 5',
  `thumb_down` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Serious behavioral report',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `evaluations_match_id_foreign` (`match_id`),
  KEY `evaluations_evaluator_id_foreign` (`evaluator_id`),
  KEY `evaluations_evaluated_id_foreign` (`evaluated_id`),
  CONSTRAINT `evaluations_evaluated_id_foreign` FOREIGN KEY (`evaluated_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluations_evaluator_id_foreign` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluations_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- 5. Trust History (Trust Score Engine and 24h Rules)
CREATE TABLE `trust_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `match_id` int(11) DEFAULT NULL,
  `score_change` int(11) NOT NULL COMMENT 'Bonus or Penalty e.g., -15, -40, +5',
  `reason` varchar(255) NOT NULL COMMENT 'Reason for trust score change',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `trust_history_user_id_foreign` (`user_id`),
  KEY `trust_history_match_id_foreign` (`match_id`),
  CONSTRAINT `trust_history_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trust_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

COMMIT;

