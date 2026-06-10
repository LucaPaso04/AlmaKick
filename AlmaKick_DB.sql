SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+01:00";
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Scegli il nome del tuo DB (almakick o campus_calcetto)
CREATE DATABASE IF NOT EXISTS `almakick` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `almakick`;

DROP TABLE IF EXISTS `friendships`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `trust_history`;
DROP TABLE IF EXISTS `evaluations`;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `matches`;
DROP TABLE IF EXISTS `users`;

-- --------------------------------------------------------
-- 1. Tabella `users` (Ora con username come Chiave Primaria e last_name)
CREATE TABLE `users` (
  `username` varchar(50) NOT NULL,
  `friend_code` varchar(10) DEFAULT NULL,
  `name` varchar(100) NOT NULL,        -- Nome
  `last_name` varchar(100) NOT NULL,   -- Cognome
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `preferred_role` varchar(30) DEFAULT NULL,
  `role` enum('user','super_admin') NOT NULL DEFAULT 'user',
  `trust_score` int(10) unsigned NOT NULL DEFAULT 100,
  `mvp_count` int(10) unsigned NOT NULL DEFAULT 0,
  `matches_played` int(10) unsigned NOT NULL DEFAULT 0,
  `total_goals` int(10) unsigned NOT NULL DEFAULT 0,
  `skill_rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `is_banned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`username`),
  UNIQUE KEY `users_friend_code_unique` (`friend_code`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_is_banned_index` (`is_banned`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Tabella `matches`
CREATE TABLE `matches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `host_username` varchar(50) NOT NULL,
  `status` enum('open','full','finished','cancelled') NOT NULL DEFAULT 'open',
  `visibility` enum('public','private') NOT NULL DEFAULT 'public',
  `date` date NOT NULL,
  `time` time NOT NULL,
  `mvp_deadline` datetime DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `format` varchar(10) NOT NULL DEFAULT '5v5',
  `max_players` tinyint(3) unsigned NOT NULL,
  `total_cost` decimal(8,2) unsigned NOT NULL DEFAULT 0.00,
  `is_urgent` tinyint(1) NOT NULL DEFAULT 0,
  `result_home` tinyint(3) unsigned DEFAULT NULL,
  `result_away` tinyint(3) unsigned DEFAULT NULL,
  `mvp_assigned` tinyint(1) NOT NULL DEFAULT 0,
  `mvp_username` varchar(50) DEFAULT NULL,
  `cancellation_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `matches_host_username_foreign` (`host_username`),
  KEY `matches_mvp_username_foreign` (`mvp_username`),
  CONSTRAINT `matches_host_username_foreign` FOREIGN KEY (`host_username`) REFERENCES `users` (`username`) ON DELETE CASCADE,
  CONSTRAINT `matches_mvp_username_foreign` FOREIGN KEY (`mvp_username`) REFERENCES `users` (`username`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Tabella `registrations`
CREATE TABLE `registrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) unsigned NOT NULL,
  `username` varchar(50) NOT NULL,
  `status` enum('registered','waitlist','cancelled') NOT NULL DEFAULT 'registered',
  `has_guest` tinyint(1) NOT NULL DEFAULT 0,
  `team` enum('home','away') DEFAULT NULL,
  `goals_scored` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `registrations_match_username_unique` (`match_id`,`username`),
  KEY `registrations_username_foreign` (`username`),
  KEY `registrations_status_index` (`status`),
  CONSTRAINT `registrations_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `registrations_username_foreign` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Tabella `evaluations`
CREATE TABLE `evaluations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) unsigned NOT NULL,
  `evaluator_username` varchar(50) NOT NULL,
  `evaluated_username` varchar(50) NOT NULL,
  `skill_vote` tinyint(3) unsigned DEFAULT NULL,
  `thumb_down` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evaluations_match_id_foreign` (`match_id`),
  KEY `evaluations_evaluator_foreign` (`evaluator_username`),
  KEY `evaluations_evaluated_index` (`evaluated_username`,`skill_vote`),
  CONSTRAINT `evaluations_evaluated_foreign` FOREIGN KEY (`evaluated_username`) REFERENCES `users` (`username`) ON DELETE CASCADE,
  CONSTRAINT `evaluations_evaluator_foreign` FOREIGN KEY (`evaluator_username`) REFERENCES `users` (`username`) ON DELETE CASCADE,
  CONSTRAINT `evaluations_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Tabella `trust_history`
CREATE TABLE `trust_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `match_id` bigint(20) unsigned DEFAULT NULL,
  `score_change` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trust_history_username_index` (`username`),
  KEY `trust_history_match_id_foreign` (`match_id`),
  CONSTRAINT `trust_history_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trust_history_username_foreign` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Tabella `reports`
CREATE TABLE `reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reporter_username` varchar(50) NOT NULL,
  `reported_username` varchar(50) NOT NULL,
  `reason` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reports_reporter_foreign` (`reporter_username`),
  KEY `reports_reported_foreign` (`reported_username`),
  KEY `reports_status_index` (`status`),
  CONSTRAINT `reports_reported_foreign` FOREIGN KEY (`reported_username`) REFERENCES `users` (`username`) ON DELETE CASCADE,
  CONSTRAINT `reports_reporter_foreign` FOREIGN KEY (`reporter_username`) REFERENCES `users` (`username`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. Tabella `friendships`
CREATE TABLE `friendships` (
  `sender_username` varchar(50) NOT NULL,
  `recipient_username` varchar(50) NOT NULL,
  `status` enum('pending','accepted','declined','blocked') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`sender_username`,`recipient_username`),
  KEY `friendships_recipient_foreign` (`recipient_username`),
  KEY `friendships_status_index` (`status`),
  CONSTRAINT `friendships_recipient_foreign` FOREIGN KEY (`recipient_username`) REFERENCES `users` (`username`) ON DELETE CASCADE,
  CONSTRAINT `friendships_sender_foreign` FOREIGN KEY (`sender_username`) REFERENCES `users` (`username`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- INSERIMENTO DATI DI TEST (Adattati alla nuova struttura)
-- ========================================================

-- Utenti di base
INSERT INTO `users` (`username`, `friend_code`, `name`, `last_name`, `email`, `email_verified_at`, `password`, `phone`, `preferred_role`, `role`, `created_at`, `updated_at`) VALUES 
('admin_test', 'RWBMYD', 'Admin', 'Test', 'admin@email.it', NOW(), '$2y$12$rMficTvQd3ZLBtPnPjp8VeoPSAs.5W.erlprPs.YbRCGniPyv3gTC', '3331234567', 'Defender', 'super_admin', NOW(), NOW()),
('mario_rossi', '5JXTKP', 'Mario', 'Rossi', 'user@email.it', NOW(), '$2y$12$tdivzfJLVFGBbQ1G88qw4ectIDlSoqRP07CnyBr2f1X81FeYR1nuW', '3339876543', 'Striker', 'user', NOW(), NOW()),
('thomas_st', 'MNCO0P', 'Thomas', 'Steuber', 'thomas@example.org', NULL, '$2y$12$navszHVgM7SEjtrrvyz.neckQu4BUbr7KuIcb4a.SmUt/291TTo3S', '3102877196', 'Midfielder', 'user', NOW(), NOW()),
('prof_brown', 'CFWPXS', 'Misael', 'Brown', 'brown@example.org', NULL, '$2y$12$navszHVgM7SEjtrrvyz.neckQu4BUbr7KuIcb4a.SmUt/291TTo3S', '5179122216', 'Goalkeeper', 'user', NOW(), NOW()),
('gertrude_b', 'IR3TMB', 'Gertrude', 'Braun', 'gertrude@example.org', NULL, '$2y$12$navszHVgM7SEjtrrvyz.neckQu4BUbr7KuIcb4a.SmUt/291TTo3S', '3853907416', 'Goalkeeper', 'user', NOW(), NOW());

-- Elenco Partite (Incluse le due nuove casistiche richieste)
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `created_at`, `updated_at`) VALUES 
(1, 'mario_rossi', 'open', 'public', '2026-06-15', '19:30', 'Bologna Sports Center', '5v5', 10, 50.00, NULL, NULL, 0, NOW(), NOW()),
(2, 'thomas_st', 'open', 'private', '2026-06-20', '21:00', 'Centro Sportivo San Siro', '7v7', 14, 70.00, NULL, NULL, 0, NOW(), NOW()),
-- CASISTICA 1: Partita finita, NON creata da admin, admin_test deve VOTARE (risultato c'è, mvp_assigned è 0)
(3, 'mario_rossi', 'finished', 'public', '2026-06-08', '20:00', 'Bologna Sports Center', '5v5', 10, 50.00, 4, 2, 0, NOW(), NOW()),
-- CASISTICA 2: Partita finita, creata da admin, admin_test deve AGGIORNARE IL TABELLINO (risultati a NULL)
(4, 'admin_test', 'finished', 'public', '2026-06-09', '21:00', 'Bologna Center Pitch', '5v5', 10, 60.00, NULL, NULL, 0, NOW(), NOW());

-- Iscrizioni alle Partite
INSERT INTO `registrations` (`match_id`, `username`, `status`, `team`, `goals_scored`, `created_at`, `updated_at`) VALUES 
-- Iscrizioni Partita 1
(1, 'mario_rossi', 'registered', 'home', 0, NOW(), NOW()),
(1, 'prof_brown', 'registered', 'away', 0, NOW(), NOW()),
(1, 'gertrude_b', 'registered', 'home', 0, NOW(), NOW()),
-- Iscrizioni Partita 2
(2, 'thomas_st', 'registered', 'home', 0, NOW(), NOW()),
(2, 'mario_rossi', 'registered', 'away', 0, NOW(), NOW()),
-- Iscrizioni Partita 3 (Admin partecipa e deve votare i compagni di squadra 'home')
(3, 'mario_rossi', 'registered', 'home', 2, NOW(), NOW()),
(3, 'admin_test', 'registered', 'home', 1, NOW(), NOW()),
(3, 'gertrude_b', 'registered', 'home', 1, NOW(), NOW()),
(3, 'thomas_st', 'registered', 'away', 1, NOW(), NOW()),
(3, 'prof_brown', 'registered', 'away', 1, NOW(), NOW()),
-- Iscrizioni Partita 4 (Admin è l'host, i gol dei singoli sono a 0 in attesa di aggiornamento)
(4, 'admin_test', 'registered', 'home', 0, NOW(), NOW()),
(4, 'mario_rossi', 'registered', 'home', 0, NOW(), NOW()),
(4, 'thomas_st', 'registered', 'away', 0, NOW(), NOW()),
(4, 'prof_brown', 'registered', 'away', 0, NOW(), NOW()),
(4, 'gertrude_b', 'registered', 'away', 0, NOW(), NOW());

-- Amicizie
INSERT INTO `friendships` (`sender_username`, `recipient_username`, `status`, `created_at`) VALUES 
('admin_test', 'mario_rossi', 'accepted', NOW()),
('thomas_st', 'mario_rossi', 'pending', NOW()),
('gertrude_b', 'prof_brown', 'accepted', NOW());

SET FOREIGN_KEY_CHECKS = 1;