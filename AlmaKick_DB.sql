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
('admin_test', 'RWBMYD', 'Admin', 'Test', 'admin@email.it', NOW(), '$2y$12$rMficTvQd3ZLBtPnPjp8VeoPSAs.5W.erlprPs.YbRCGniPyv3gTC', '3331234567', 'Difensore', 'super_admin', NOW(), NOW()),
('mario_rossi', '5JXTKP', 'Mario', 'Rossi', 'user@email.it', NOW(), '$2y$12$tdivzfJLVFGBbQ1G88qw4ectIDlSoqRP07CnyBr2f1X81FeYR1nuW', '3339876543', 'Attaccante', 'user', NOW(), NOW()),
('thomas_st', 'MNCO0P', 'Thomas', 'Steuber', 'thomas@example.org', NULL, '$2y$12$navszHVgM7SEjtrrvyz.neckQu4BUbr7KuIcb4a.SmUt/291TTo3S', '3102877196', 'Centrocampista', 'user', NOW(), NOW()),
('prof_brown', 'CFWPXS', 'Misael', 'Brown', 'brown@example.org', NULL, '$2y$12$navszHVgM7SEjtrrvyz.neckQu4BUbr7KuIcb4a.SmUt/291TTo3S', '5179122216', 'Portiere', 'user', NOW(), NOW()),
('gertrude_b', 'IR3TMB', 'Gertrude', 'Braun', 'gertrude@example.org', NULL, '$2y$12$navszHVgM7SEjtrrvyz.neckQu4BUbr7KuIcb4a.SmUt/291TTo3S', '3853907416', 'Portiere', 'user', NOW(), NOW());

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

-- ==========================================
-- 1. UTENTI E AMICIZIE
-- ==========================================

INSERT INTO users (username, friend_code, name, last_name, email, password, phone, role, preferred_role, trust_score, skill_rating, mvp_count, matches_played, total_goals, is_banned, created_at, updated_at) VALUES
('luigi_verdi', 'VBQH9Z', 'Luigi', 'Verdi', 'luigi@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331112222', 'user', 'Portiere', 90, 3.8, 1, 15, 0, 0, NOW(), NOW()),
('giovanni_neri', 'EE7LR9', 'Giovanni', 'Neri', 'giovanni@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3333334444', 'user', 'Difensore', 60, 3.2, 0, 10, 2, 0, NOW(), NOW()),
('marco_bianchi', '4FIBGA', 'Marco', 'Bianchi', 'marco@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3335556666', 'user', 'Centrocampista', 100, 4.0, 2, 25, 12, 0, NOW(), NOW()),
('andrea_gialli', 'YL6JU2', 'Andrea', 'Gialli', 'andrea@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3337778888', 'user', 'Attaccante', 40, 2.5, 0, 5, 5, 0, NOW(), NOW()),
('paolo_marroni', '5R01AI', 'Paolo', 'Marroni', 'paolo@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3339990000', 'user', 'Difensore', 100, 4.8, 10, 50, 20, 0, NOW(), NOW()),
('francesco_blu', 'VVUBUY', 'Francesco', 'Blu', 'francesco@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3332221111', 'user', 'Portiere', 10, 1.0, 0, 2, 0, 1, NOW(), NOW()),
('stefano_viola', '7DP4TI', 'Stefano', 'Viola', 'stefano@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3334445555', 'user', 'Centrocampista', 85, 3.5, 1, 8, 4, 0, NOW(), NOW()),
('roberto_arancio', 'ZN08GT', 'Roberto', 'Arancio', 'roberto@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3336667777', 'user', 'Attaccante', 100, 4.2, 3, 18, 22, 0, NOW(), NOW()),
('luca_celeste', 'DOFUBC', 'Luca', 'Celeste', 'luca@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3338889999', 'user', 'Difensore', 75, 3.0, 0, 12, 1, 0, NOW(), NOW()),
('alessandro_indaco', 'D4GQAN', 'Alessandro', 'Indaco', 'alessandro@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331010101', 'user', 'Portiere', 100, 4.7, 4, 30, 0, 0, NOW(), NOW()),
('daniele_oliva', 'M3US68', 'Daniele', 'Oliva', 'daniele@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3332020202', 'user', 'Centrocampista', 50, 2.8, 0, 4, 1, 0, NOW(), NOW()),
('matteo_perla', 'YAOBV3', 'Matteo', 'Perla', 'matteo@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3333030303', 'user', 'Attaccante', 95, 3.9, 2, 14, 10, 0, NOW(), NOW()),
('simone_smeraldo', '82VE8Q', 'Simone', 'Smeraldo', 'simone@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3334040404', 'user', 'Difensore', 100, 4.1, 1, 22, 3, 0, NOW(), NOW()),
('davide_topazio', '5GYTVE', 'Davide', 'Topazio', 'davide@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3335050505', 'user', 'Portiere', 65, 3.4, 0, 9, 0, 0, NOW(), NOW()),
('federico_rubino', '1NX62W', 'Federico', 'Rubino', 'federico@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3336060606', 'user', 'Centrocampista', 100, 4.4, 3, 28, 14, 0, NOW(), NOW()),
('lorenzo_zaffiro', 'SHFEN0', 'Lorenzo', 'Zaffiro', 'lorenzo@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3337070707', 'user', 'Attaccante', 100, 4.9, 8, 40, 65, 0, NOW(), NOW()),
('giacomo_ambra', 'U85L8B', 'Giacomo', 'Ambra', 'giacomo@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3338080808', 'user', 'Difensore', 45, 2.2, 0, 3, 0, 0, NOW(), NOW()),
('emanuele_giada', '8L1T7H', 'Emanuele', 'Giada', 'emanuele@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3339090909', 'user', 'Centrocampista', 100, 3.7, 0, 11, 2, 0, NOW(), NOW()),
('filippo_onice', 'DPC41C', 'Filippo', 'Onice', 'filippo@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331122334', 'user', 'Attaccante', 80, 3.6, 1, 16, 8, 0, NOW(), NOW()),
('valerio_bronzo', 'OXJUEC', 'Valerio', 'Bronzo', 'valerio@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231231', 'user', 'Portiere', 95, 3.5, 0, 10, 0, 0, NOW(), NOW()),
('chiara_rosa', 'N33DJ8', 'Chiara', 'Rosa', 'chiara@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231232', 'user', 'Attaccante', 100, 4.6, 6, 25, 40, 0, NOW(), NOW()),
('martina_lilla', 'XX4Q01', 'Martina', 'Lilla', 'martina@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231233', 'user', 'Centrocampista', 80, 3.8, 1, 15, 6, 0, NOW(), NOW()),
('giuseppe_turchese', 'IUBOE4', 'Giuseppe', 'Turchese', 'giuseppe@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231234', 'user', 'Difensore', 60, 2.5, 0, 5, 0, 0, NOW(), NOW()),
('sofia_corallo', '6PRIOD', 'Sofia', 'Corallo', 'sofia@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231235', 'user', 'Attaccante', 100, 5.0, 15, 60, 100, 0, NOW(), NOW()),
('marco_diamante', 'YMPU8K', 'Marco', 'Diamante', 'marcod@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231236', 'user', 'Difensore', 90, 4.2, 2, 20, 2, 0, NOW(), NOW()),
('elena_ametista', 'OUFSIB', 'Elena', 'Ametista', 'elena@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231237', 'user', 'Centrocampista', 100, 3.9, 0, 12, 4, 0, NOW(), NOW()),
('fabio_quarzo', 'WFQ38W', 'Fabio', 'Quarzo', 'fabio@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231238', 'user', 'Portiere', 85, 3.1, 0, 8, 0, 0, NOW(), NOW()),
('vittorio_ossidiana', 'ZQYXY3', 'Vittorio', 'Ossidiana', 'vittorio@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231239', 'user', 'Attaccante', 75, 4.0, 3, 15, 18, 0, NOW(), NOW()),
('sara_rubino', 'Q620UF', 'Sara', 'Rubino', 'sara@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231240', 'user', 'Difensore', 100, 3.6, 1, 14, 1, 0, NOW(), NOW());

-- ==========================================
-- 2. PARTITE (MATCHES)
-- ==========================================
INSERT INTO matches (id, host_username, date, time, format, max_players, location, latitude, longitude, visibility, total_cost, status, cancellation_reason, result_home, result_away, created_at, updated_at) VALUES
-- Aperte e piene (Future)
(111, 'luigi_verdi', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '20:30:00', '5v5', 10, 'Campus calcetto CUS', 45.4642, 9.1900, 'public', 60.00, 'open', NULL, NULL, NULL, NOW(), NOW()),
(211, 'marco_bianchi', DATE_ADD(CURDATE(), INTERVAL 5 DAY), '19:00:00', '7v7', 14, 'Centro Sportivo Olimpia', 45.4700, 9.2000, 'public', 98.00, 'open', NULL, NULL, NULL, NOW(), NOW()),
(311, 'paolo_marroni', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '21:00:00', '5v5', 10, 'Campetti San Paolo', 41.9028, 12.4964, 'public', 50.00, 'full', NULL, NULL, NULL, NOW(), NOW()),
(11, 'stefano_viola', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '18:00:00', '5v5', 10, 'Campetti San Paolo', 41.9028, 12.4964, 'private', 55.00, 'open', NULL, NULL, NULL, NOW(), NOW()),
(12, 'luca_celeste', DATE_ADD(CURDATE(), INTERVAL 4 DAY), '20:00:00', '7v7', 14, 'Polisportiva Nord', 45.4900, 9.1800, 'public', 120.00, 'open', NULL, NULL, NULL, NOW(), NOW()),
(13, 'simone_smeraldo', DATE_ADD(CURDATE(), INTERVAL 6 DAY), '21:30:00', '5v5', 10, 'Calcetto Club', 41.8900, 12.5100, 'private', 70.00, 'open', NULL, NULL, NULL, NOW(), NOW()),
(14, 'sofia_corallo', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '19:30:00', '5v5', 10, 'Arena Sport', 45.4642, 9.2000, 'public', 60.00, 'full', NULL, NULL, NULL, NOW(), NOW()),

-- Finite (Passate)
(411, 'roberto_arancio', DATE_SUB(CURDATE(), INTERVAL 2 DAY), '18:30:00', '5v5', 10, 'Playground Stadio', 41.8900, 12.5000, 'public', 60.00, 'finished', NULL, 7, 5, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, 'luca_celeste', DATE_SUB(CURDATE(), INTERVAL 10 DAY), '20:00:00', '7v7', 14, 'Centro Sportivo Olimpia', 45.4700, 9.2000, 'public', 100.00, 'finished', NULL, 3, 3, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(15, 'luigi_verdi', DATE_SUB(CURDATE(), INTERVAL 3 DAY), '21:00:00', '5v5', 10, 'Campetti San Paolo', 41.9028, 12.4964, 'public', 55.00, 'finished', NULL, 4, 8, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(16, 'chiara_rosa', DATE_SUB(CURDATE(), INTERVAL 7 DAY), '19:00:00', '5v5', 10, 'Campus calcetto CUS', 45.4642, 9.1900, 'public', 60.00, 'finished', NULL, 10, 2, DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(17, 'marco_diamante', DATE_SUB(CURDATE(), INTERVAL 14 DAY), '20:00:00', '7v7', 14, 'Centro Sportivo Olimpia', 45.4700, 9.2000, 'private', 98.00, 'finished', NULL, 5, 5, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(18, 'lorenzo_zaffiro', DATE_SUB(CURDATE(), INTERVAL 21 DAY), '22:00:00', '5v5', 10, 'Calcetto Club', 41.8900, 12.5100, 'public', 70.00, 'finished', NULL, 6, 7, DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 21 DAY)),

-- Annullate
(6, 'giovanni_neri', DATE_SUB(CURDATE(), INTERVAL 5 DAY), '21:00:00', '5v5', 10, 'Campo Periferia', NULL, NULL, 'public', 50.00, 'cancelled', 'Meteo avverso', NULL, NULL, DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(7, 'giovanni_neri', DATE_SUB(CURDATE(), INTERVAL 12 DAY), '21:00:00', '5v5', 10, 'Campo Periferia', NULL, NULL, 'public', 50.00, 'cancelled', 'Meteo avverso', NULL, NULL, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 12 DAY)),
(8, 'giovanni_neri', DATE_SUB(CURDATE(), INTERVAL 20 DAY), '21:00:00', '5v5', 10, 'Campo Periferia', NULL, NULL, 'public', 50.00, 'cancelled', 'Meteo avverso', NULL, NULL, DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY)),
(9, 'giovanni_neri', DATE_SUB(CURDATE(), INTERVAL 25 DAY), '21:00:00', '5v5', 10, 'Campo Periferia', NULL, NULL, 'public', 50.00, 'cancelled', 'Meteo avverso', NULL, NULL, DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY)),
(10, 'andrea_gialli', DATE_SUB(CURDATE(), INTERVAL 1 DAY), '22:00:00', '5v5', 10, 'Campetti San Paolo', NULL, NULL, 'public', 50.00, 'cancelled', 'Non abbiamo raggiunto il numero', NULL, NULL, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(19, 'vittorio_ossidiana', DATE_SUB(CURDATE(), INTERVAL 2 DAY), '20:30:00', '5v5', 10, 'Arena Sport', 45.4642, 9.2000, 'public', 60.00, 'cancelled', 'Infortunio del campo', NULL, NULL, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY));

-- ==========================================
-- 3. ISCRIZIONI (REGISTRATIONS)
-- ==========================================

INSERT INTO registrations (match_id, username, status, has_guest, team, goals_scored, created_at, updated_at) VALUES
-- Partita 1 (Aperta, URGENTE) 
(111, 'luigi_verdi', 'registered', 0, NULL, 0, NOW(), NOW()),
(111, 'mario_rossi', 'registered', 0, NULL, 0, NOW(), NOW()),
(111, 'marco_bianchi', 'registered', 1, NULL, 0, NOW(), NOW()), 
(111, 'paolo_marroni', 'registered', 0, NULL, 0, NOW(), NOW()),
(111, 'stefano_viola', 'registered', 0, NULL, 0, NOW(), NOW()),
(111, 'roberto_arancio', 'registered', 0, NULL, 0, NOW(), NOW()),
(111, 'luca_celeste', 'registered', 0, NULL, 0, NOW(), NOW()),
(111, 'alessandro_indaco', 'waitlist', 0, NULL, 0, NOW(), NOW()),

-- Partita 2 (Aperta) 
(211, 'marco_bianchi', 'registered', 0, NULL, 0, NOW(), NOW()),
(211, 'giovanni_neri', 'registered', 0, NULL, 0, NOW(), NOW()),
(211, 'andrea_gialli', 'registered', 0, NULL, 0, NOW(), NOW()),
(211, 'daniele_oliva', 'registered', 0, NULL, 0, NOW(), NOW()),
(211, 'valerio_bronzo', 'registered', 0, NULL, 0, NOW(), NOW()),
(211, 'giuseppe_turchese', 'registered', 0, NULL, 0, NOW(), NOW()),

-- Partita 3 (Full) 
(311, 'paolo_marroni', 'registered', 0, 'home', 0, NOW(), NOW()),
(311, 'mario_rossi', 'registered', 0, 'home', 0, NOW(), NOW()),
(311, 'luigi_verdi', 'registered', 0, 'away', 0, NOW(), NOW()),
(311, 'marco_bianchi', 'registered', 0, 'away', 0, NOW(), NOW()),
(311, 'stefano_viola', 'registered', 0, 'home', 0, NOW(), NOW()),
(311, 'roberto_arancio', 'registered', 0, 'away', 0, NOW(), NOW()),
(311, 'luca_celeste', 'registered', 0, 'home', 0, NOW(), NOW()),
(311, 'alessandro_indaco', 'registered', 0, 'away', 0, NOW(), NOW()),
(311, 'daniele_oliva', 'registered', 0, 'home', 0, NOW(), NOW()),
(311, 'matteo_perla', 'registered', 0, 'away', 0, NOW(), NOW()),

-- Partita 11 (Aperta) 
(11, 'stefano_viola', 'registered', 0, NULL, 0, NOW(), NOW()),
(11, 'martina_lilla', 'registered', 0, NULL, 0, NOW(), NOW()),
(11, 'elena_ametista', 'registered', 0, NULL, 0, NOW(), NOW()),

-- Partita 12 (Aperta) 
(12, 'luca_celeste', 'registered', 0, NULL, 0, NOW(), NOW()),
(12, 'simone_smeraldo', 'registered', 1, NULL, 0, NOW(), NOW()),
(12, 'giacomo_ambra', 'registered', 0, NULL, 0, NOW(), NOW()),
(12, 'filippo_onice', 'registered', 0, NULL, 0, NOW(), NOW()),

-- Partita 13 (Aperta, pochi iscritti) 
(13, 'simone_smeraldo', 'registered', 0, NULL, 0, NOW(), NOW()),
(13, 'emanuele_giada', 'registered', 0, NULL, 0, NOW(), NOW()),

-- Partita 14 (Full) 
(14, 'sofia_corallo', 'registered', 0, 'home', 0, NOW(), NOW()),
(14, 'filippo_onice', 'registered', 0, 'away', 0, NOW(), NOW()),
(14, 'valerio_bronzo', 'registered', 0, 'home', 0, NOW(), NOW()),
(14, 'chiara_rosa', 'registered', 0, 'away', 0, NOW(), NOW()),
(14, 'martina_lilla', 'registered', 0, 'home', 0, NOW(), NOW()),
(14, 'giuseppe_turchese', 'registered', 0, 'away', 0, NOW(), NOW()),
(14, 'marco_diamante', 'registered', 0, 'home', 0, NOW(), NOW()),
(14, 'elena_ametista', 'registered', 0, 'away', 0, NOW(), NOW()),
(14, 'fabio_quarzo', 'registered', 0, 'home', 0, NOW(), NOW()),
(14, 'vittorio_ossidiana', 'registered', 0, 'away', 0, NOW(), NOW()),
(14, 'sara_rubino', 'waitlist', 0, NULL, 0, NOW(), NOW()),

-- Partita 4 (Conclusa) 
(411, 'roberto_arancio', 'registered', 0, 'home', 3, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(411, 'mario_rossi', 'registered', 0, 'home', 2, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(411, 'marco_bianchi', 'registered', 0, 'home', 2, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(411, 'simone_smeraldo', 'registered', 0, 'home', 0, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(411, 'davide_topazio', 'registered', 0, 'home', 0, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(411, 'federico_rubino', 'registered', 0, 'away', 2, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(411, 'lorenzo_zaffiro', 'registered', 0, 'away', 3, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(411, 'giacomo_ambra', 'registered', 0, 'away', 0, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(411, 'emanuele_giada', 'registered', 0, 'away', 0, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(411, 'filippo_onice', 'registered', 0, 'away', 0, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),

-- Partita 5 (Conclusa, 7v7 = 14 giocatori) 
(5, 'luca_celeste', 'registered', 0, 'home', 1, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 'alessandro_indaco', 'registered', 0, 'home', 0, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 'daniele_oliva', 'registered', 0, 'home', 1, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 'matteo_perla', 'registered', 0, 'home', 1, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 'simone_smeraldo', 'registered', 0, 'home', 0, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 'davide_topazio', 'registered', 0, 'home', 0, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 'federico_rubino', 'registered', 0, 'home', 0, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 'lorenzo_zaffiro', 'registered', 0, 'away', 2, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 'giacomo_ambra', 'registered', 0, 'away', 0, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 'emanuele_giada', 'registered', 0, 'away', 0, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 'filippo_onice', 'registered', 0, 'away', 1, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 'valerio_bronzo', 'registered', 0, 'away', 0, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 'chiara_rosa', 'registered', 0, 'away', 0, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 'martina_lilla', 'registered', 0, 'away', 0, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY)),

-- Partita 15 (Conclusa)
(15, 'luigi_verdi', 'registered', 0, 'home', 0, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(15, 'giuseppe_turchese', 'registered', 0, 'home', 0, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(15, 'sofia_corallo', 'registered', 0, 'home', 2, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(15, 'marco_diamante', 'registered', 0, 'home', 1, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(15, 'elena_ametista', 'registered', 0, 'home', 1, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(15, 'fabio_quarzo', 'registered', 0, 'away', 0, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(15, 'vittorio_ossidiana', 'registered', 0, 'away', 4, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(15, 'sara_rubino', 'registered', 0, 'away', 1, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(15, 'giovanni_neri', 'registered', 0, 'away', 1, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(15, 'marco_bianchi', 'registered', 0, 'away', 2, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),

-- Partita 16 (Conclusa) 
(16, 'chiara_rosa', 'registered', 0, 'home', 5, DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(16, 'martina_lilla', 'registered', 0, 'home', 2, DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(16, 'giuseppe_turchese', 'registered', 0, 'home', 1, DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(16, 'sofia_corallo', 'registered', 0, 'home', 2, DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(16, 'marco_diamante', 'registered', 0, 'home', 0, DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(16, 'elena_ametista', 'registered', 0, 'away', 1, DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(16, 'fabio_quarzo', 'registered', 0, 'away', 0, DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(16, 'vittorio_ossidiana', 'registered', 0, 'away', 1, DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(16, 'sara_rubino', 'registered', 0, 'away', 0, DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(16, 'andrea_gialli', 'registered', 0, 'away', 0, DATE_SUB(NOW(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),

-- Partita 17 (Conclusa 7v7) 
(17, 'marco_diamante', 'registered', 0, 'home', 0, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(17, 'elena_ametista', 'registered', 0, 'home', 0, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(17, 'fabio_quarzo', 'registered', 0, 'home', 0, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(17, 'vittorio_ossidiana', 'registered', 0, 'home', 3, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(17, 'sara_rubino', 'registered', 0, 'home', 1, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(17, 'luigi_verdi', 'registered', 0, 'home', 0, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(17, 'giovanni_neri', 'registered', 0, 'home', 1, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(17, 'marco_bianchi', 'registered', 0, 'away', 1, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(17, 'andrea_gialli', 'registered', 0, 'away', 1, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(17, 'paolo_marroni', 'registered', 0, 'away', 1, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(17, 'stefano_viola', 'registered', 0, 'away', 0, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(17, 'roberto_arancio', 'registered', 0, 'away', 2, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(17, 'luca_celeste', 'registered', 0, 'away', 0, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(17, 'alessandro_indaco', 'registered', 0, 'away', 0, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),

-- Partita 18 (Conclusa) 
(18, 'lorenzo_zaffiro', 'registered', 0, 'home', 3, DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 21 DAY)),
(18, 'daniele_oliva', 'registered', 0, 'home', 1, DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 21 DAY)),
(18, 'matteo_perla', 'registered', 0, 'home', 2, DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 21 DAY)),
(18, 'simone_smeraldo', 'registered', 0, 'home', 0, DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 21 DAY)),
(18, 'davide_topazio', 'registered', 0, 'home', 0, DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 21 DAY)),
(18, 'filippo_onice', 'registered', 0, 'away', 2, DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 21 DAY)),
(18, 'valerio_bronzo', 'registered', 0, 'away', 0, DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 21 DAY)),
(18, 'chiara_rosa', 'registered', 0, 'away', 3, DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 21 DAY)),
(18, 'martina_lilla', 'registered', 0, 'away', 1, DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 21 DAY)),
(18, 'giuseppe_turchese', 'registered', 0, 'away', 1, DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 21 DAY)),

-- Partite annullate
(6, 'giovanni_neri', 'registered', 0, NULL, 0, DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(7, 'giovanni_neri', 'registered', 0, NULL, 0, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 12 DAY)),
(8, 'giovanni_neri', 'registered', 0, NULL, 0, DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY)),
(9, 'giovanni_neri', 'registered', 0, NULL, 0, DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY)),
(10, 'andrea_gialli', 'registered', 0, NULL, 0, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(19, 'vittorio_ossidiana', 'registered', 0, NULL, 0, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY));

-- ==========================================
-- 4. VALUTAZIONI POST-PARTITA (EVALUATIONS)
-- ==========================================

INSERT INTO evaluations (evaluator_username, evaluated_username, match_id, skill_vote, thumb_down, created_at) VALUES
-- User 1 (mario_rossi) vota tutti i compagni della partita 4 (home)
('mario_rossi', 'roberto_arancio', 4, 4, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('mario_rossi', 'marco_bianchi', 4, 4, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('mario_rossi', 'simone_smeraldo', 4, 3, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('mario_rossi', 'davide_topazio', 4, 5, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),

-- User 9 (roberto_arancio) vota i compagni della partita 4 (home)
('roberto_arancio', 'mario_rossi', 4, 5, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('roberto_arancio', 'marco_bianchi', 4, 3, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('roberto_arancio', 'simone_smeraldo', 4, 2, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('roberto_arancio', 'davide_topazio', 4, 4, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),

-- Esempio di valutazioni per partita 15
('luigi_verdi', 'giuseppe_turchese', 15, 4, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('luigi_verdi', 'sofia_corallo', 15, 5, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('sofia_corallo', 'luigi_verdi', 15, 3, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),

-- Esempio di valutazioni per partita 16
('chiara_rosa', 'martina_lilla', 16, 4, 0, DATE_SUB(NOW(), INTERVAL 6 DAY)),
('chiara_rosa', 'giuseppe_turchese', 16, 2, 1, DATE_SUB(NOW(), INTERVAL 6 DAY)), -- Pollice in giù

-- User 16 (federico_rubino) vota i compagni della partita 4 (away) e mette un pollice in giù a 18 (giacomo_ambra)
('federico_rubino', 'lorenzo_zaffiro', 4, 5, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('federico_rubino', 'giacomo_ambra', 4, 1, 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('federico_rubino', 'emanuele_giada', 4, 4, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('federico_rubino', 'filippo_onice', 4, 3, 0, DATE_SUB(NOW(), INTERVAL 2 DAY));


-- ==========================================
-- 5. TRUST HISTORY LOG
-- ==========================================

INSERT INTO trust_history (username, match_id, score_change, reason, created_at) VALUES
('giovanni_neri', 6, 0, 'Partita annullata dal creatore per Meteo avverso (Nessuna penalità).', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('giovanni_neri', 7, 0, 'Partita annullata dal creatore per Meteo avverso (Nessuna penalità).', DATE_SUB(NOW(), INTERVAL 12 DAY)),
('giovanni_neri', 8, 0, 'Partita annullata dal creatore per Meteo avverso (Nessuna penalità).', DATE_SUB(NOW(), INTERVAL 20 DAY)),
('giovanni_neri', 9, 0, 'Partita annullata dal creatore per Meteo avverso (Nessuna penalità).', DATE_SUB(NOW(), INTERVAL 25 DAY)),
('andrea_gialli', 10, -40, 'Partita annullata a meno di 24h dall''inizio.', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('vittorio_ossidiana', 19, -40, 'Partita annullata a meno di 24h dall''inizio.', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('giacomo_ambra', 4, -5, 'Segnalazione pollice in giù da un compagno di squadra.', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('giuseppe_turchese', 16, -5, 'Segnalazione pollice in giù da un compagno di squadra.', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('francesco_blu', NULL, -15, 'Ritiro iscrizione a meno di 24 ore dalla partita.', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('francesco_blu', NULL, -15, 'Ritiro iscrizione a meno di 24 ore dalla partita.', DATE_SUB(NOW(), INTERVAL 10 DAY)),
('francesco_blu', NULL, -40, 'Partita annullata a meno di 24h dall''inizio.', DATE_SUB(NOW(), INTERVAL 15 DAY));

SET FOREIGN_KEY_CHECKS = 1;