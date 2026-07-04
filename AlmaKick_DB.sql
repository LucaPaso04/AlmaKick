SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+01:00";
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `almakick` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `almakick`;

DROP TABLE IF EXISTS `friendships`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `trust_history`;
DROP TABLE IF EXISTS `evaluations`;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `matches`;
DROP TABLE IF EXISTS `users`;

-- Table "users"
CREATE TABLE `users` (
  `username` varchar(50) NOT NULL,
  `friend_code` varchar(10) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
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

-- Table "matches"
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

-- Table "registrations"
CREATE TABLE `registrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `match_id` bigint(20) unsigned NOT NULL,
  `username` varchar(50) NOT NULL,
  `status` enum('registered','waitlist','cancelled') NOT NULL DEFAULT 'registered',
  `has_guest` tinyint(1) NOT NULL DEFAULT 0,
  `team` enum('home','away') DEFAULT NULL,
  `goals_scored` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `offer_expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `registrations_match_username_unique` (`match_id`,`username`),
  KEY `registrations_username_foreign` (`username`),
  KEY `registrations_status_index` (`status`),
  CONSTRAINT `registrations_match_id_foreign` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `registrations_username_foreign` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table "evaluations"
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

-- Table "trust_history"
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

-- Table "reports"
CREATE TABLE `reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reporter_username` varchar(50) NOT NULL,
  `reported_username` varchar(50) NOT NULL,
  `match_id` bigint(20) unsigned DEFAULT NULL,
  `reason` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reports_reporter_foreign` (`reporter_username`),
  KEY `reports_reported_foreign` (`reported_username`),
  KEY `reports_match_foreign` (`match_id`),
  KEY `reports_status_index` (`status`),
  CONSTRAINT `reports_reported_foreign` FOREIGN KEY (`reported_username`) REFERENCES `users` (`username`) ON DELETE CASCADE,
  CONSTRAINT `reports_reporter_foreign` FOREIGN KEY (`reporter_username`) REFERENCES `users` (`username`) ON DELETE CASCADE,
  CONSTRAINT `reports_match_foreign` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table "friendships"
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

-- Table "notifications"
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_recipient` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_recipient_foreign` (`user_recipient`),
  KEY `notifications_is_read_index` (`is_read`),
  CONSTRAINT `notifications_user_recipient_foreign` FOREIGN KEY (`user_recipient`) REFERENCES `users` (`username`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- DATA INSERTIONS
-- --------------------------------------------------------

-- Base Users
INSERT INTO `users` (`username`, `friend_code`, `name`, `last_name`, `email`, `password`, `phone`, `preferred_role`, `role`, `created_at`, `updated_at`) VALUES 
('admin_test', 'RWBMYD', 'Admin', 'Test', 'admin@email.it', '$2y$12$rMficTvQd3ZLBtPnPjp8VeoPSAs.5W.erlprPs.YbRCGniPyv3gTC', '3331234567', 'Difensore', 'super_admin', NOW(), NOW()),
('mario_rossi', '5JXTKP', 'Mario', 'Rossi', 'user@email.it', '$2y$12$tdivzfJLVFGBbQ1G88qw4ectIDlSoqRP07CnyBr2f1X81FeYR1nuW', '3339876543', 'Attaccante', 'user', NOW(), NOW()),
('tommaso_st', 'MNCO0P', 'Tommaso', 'Stella', 'tommaso@email.it', '$2y$12$navszHVgM7SEjtrrvyz.neckQu4BUbr7KuIcb4a.SmUt/291TTo3S', '3102877196', 'Centrocampista', 'user', NOW(), NOW()),
('michele_marrone', 'CFWPXS', 'Michele', 'Marrone', 'michele@email.it', '$2y$12$navszHVgM7SEjtrrvyz.neckQu4BUbr7KuIcb4a.SmUt/291TTo3S', '5179122216', 'Portiere', 'user', NOW(), NOW()),
('giulia_brunelli', 'IR3TMB', 'Giulia', 'Brunelli', 'giulia@email.it', '$2y$12$navszHVgM7SEjtrrvyz.neckQu4BUbr7KuIcb4a.SmUt/291TTo3S', '3853907416', 'Portiere', 'user', NOW(), NOW());

-- Extra Users
INSERT INTO users (username, friend_code, name, last_name, email, password, phone, role, preferred_role, trust_score, skill_rating, mvp_count, matches_played, total_goals, is_banned, created_at, updated_at) VALUES
('luigi_verdi', 'VBQH9Z', 'Luigi', 'Verdi', 'luigi@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331112222', 'user', 'Portiere', 90, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('giovanni_neri', 'EE7LR9', 'Giovanni', 'Neri', 'giovanni@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3333334444', 'user', 'Difensore', 60, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('marco_bianchi', '4FIBGA', 'Marco', 'Bianchi', 'marco@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3335556666', 'user', 'Centrocampista', 100, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('andrea_gialli', 'YL6JU2', 'Andrea', 'Gialli', 'andrea@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3337778888', 'user', 'Attaccante', 40, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('paolo_marroni', '5R01AI', 'Paolo', 'Marroni', 'paolo@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3339990000', 'user', 'Difensore', 100, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('francesco_blu', 'VVUBUY', 'Francesco', 'Blu', 'francesco@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3332221111', 'user', 'Portiere', 10, 0.00, 0, 0, 0, 1, NOW(), NOW()),
('stefano_viola', '7DP4TI', 'Stefano', 'Viola', 'stefano@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3334445555', 'user', 'Centrocampista', 85, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('roberto_arancio', 'ZN08GT', 'Roberto', 'Arancio', 'roberto@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3336667777', 'user', 'Attaccante', 100, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('luca_celeste', 'DOFUBC', 'Luca', 'Celeste', 'luca@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3338889999', 'user', 'Difensore', 75, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('alessandro_indaco', 'D4GQAN', 'Alessandro', 'Indaco', 'alessandro@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331010101', 'user', 'Portiere', 100, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('daniele_oliva', 'M3US68', 'Daniele', 'Oliva', 'daniele@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3332020202', 'user', 'Centrocampista', 50, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('matteo_perla', 'YAOBV3', 'Matteo', 'Perla', 'matteo@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3333030303', 'user', 'Attaccante', 95, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('simone_smeraldo', '82VE8Q', 'Simone', 'Smeraldo', 'simone@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3334040404', 'user', 'Difensore', 100, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('davide_topazio', '5GYTVE', 'Davide', 'Topazio', 'davide@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3335050505', 'user', 'Portiere', 65, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('federico_rubino', '1NX62W', 'Federico', 'Rubino', 'federico@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3336060606', 'user', 'Centrocampista', 100, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('lorenzo_zaffiro', 'SHFEN0', 'Lorenzo', 'Zaffiro', 'lorenzo@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3337070707', 'user', 'Attaccante', 100, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('giacomo_ambra', 'U85L8B', 'Giacomo', 'Ambra', 'giacomo@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3338080808', 'user', 'Difensore', 45, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('emanuele_giada', '8L1T7H', 'Emanuele', 'Giada', 'emanuele@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3339090909', 'user', 'Centrocampista', 100, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('filippo_onice', 'DPC41C', 'Filippo', 'Onice', 'filippo@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331122334', 'user', 'Attaccante', 80, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('valerio_bronzo', 'OXJUEC', 'Valerio', 'Bronzo', 'valerio@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231231', 'user', 'Portiere', 95, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('chiara_rosa', 'N33DJ8', 'Chiara', 'Rosa', 'chiara@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231232', 'user', 'Attaccante', 100, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('martina_lilla', 'XX4Q01', 'Martina', 'Lilla', 'martina@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231233', 'user', 'Centrocampista', 80, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('giuseppe_turchese', 'IUBOE4', 'Giuseppe', 'Turchese', 'giuseppe@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231234', 'user', 'Difensore', 60, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('sofia_corallo', '6PRIOD', 'Sofia', 'Corallo', 'sofia@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231235', 'user', 'Attaccante', 100, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('marco_diamante', 'YMPU8K', 'Marco', 'Diamante', 'marcod@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231236', 'user', 'Difensore', 90, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('elena_ametista', 'OUFSIB', 'Elena', 'Ametista', 'elena@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231237', 'user', 'Centrocampista', 100, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('fabio_quarzo', 'WFQ38W', 'Fabio', 'Quarzo', 'fabio@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231238', 'user', 'Portiere', 85, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('vittorio_ossidiana', 'ZQYXY3', 'Vittorio', 'Ossidiana', 'vittorio@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231239', 'user', 'Attaccante', 75, 0.00, 0, 0, 0, 0, NOW(), NOW()),
('sara_rubino', 'Q620UF', 'Sara', 'Rubino', 'sara@email.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3331231240', 'user', 'Difensore', 100, 0.00, 0, 0, 0, 0, NOW(), NOW());


-- --------------------------------------------------------
-- MATCHES
-- --------------------------------------------------------

-- Match 1: Future open match (5v5), partially filled, no teams assigned
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `created_at`, `updated_at`) VALUES
(1, 'mario_rossi', 'open', 'public', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '19:30:00', 'Bologna Sports Center', '5v5', 10, 50.00, NULL, NULL, 0, NOW(), NOW());

-- Match 2: Future open match (7v7), hosted by Tommaso Stella, private visibility, no teams assigned
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `created_at`, `updated_at`) VALUES
(2, 'tommaso_st', 'open', 'private', DATE_ADD(CURDATE(), INTERVAL 5 DAY), '21:00:00', 'Centro Sportivo San Siro', '7v7', 14, 70.00, NULL, NULL, 0, NOW(), NOW());

-- Match 3: Finished match (5v5), scores are entered (4-2), but MVP is not assigned yet (users can vote)
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `created_at`, `updated_at`) VALUES
(3, 'mario_rossi', 'finished', 'public', DATE_SUB(CURDATE(), INTERVAL 8 DAY), '20:00:00', 'Bologna Sports Center', '5v5', 10, 50.00, 4, 2, 0, NOW(), NOW());

-- Match 4: Finished match (5v5) where scores are NOT entered yet (host needs to report scores)
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `created_at`, `updated_at`) VALUES
(4, 'admin_test', 'finished', 'public', DATE_SUB(CURDATE(), INTERVAL 7 DAY), '21:00:00', 'Bologna Center Pitch', '5v5', 10, 60.00, NULL, NULL, 0, NOW(), NOW());

-- Match 5: Finished match (7v7), completely resolved, scores set (3-3), MVP assigned to Lorenzo Zaffiro
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `mvp_username`, `created_at`, `updated_at`) VALUES
(5, 'luca_celeste', 'finished', 'public', DATE_SUB(CURDATE(), INTERVAL 15 DAY), '20:00:00', 'Centro Sportivo Olimpia', '7v7', 14, 100.00, 3, 3, 1, 'lorenzo_zaffiro', NOW(), NOW());

-- Match 11: Future open match (5v5) with few registrations (3/10), private visibility
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `created_at`, `updated_at`) VALUES
(11, 'stefano_viola', 'open', 'private', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '18:00:00', 'Campetti San Paolo', '5v5', 10, 55.00, NULL, NULL, 0, NOW(), NOW());

-- Match 12: Future open match (7v7) with guest registrations (4 seats occupied)
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `created_at`, `updated_at`) VALUES
(12, 'luca_celeste', 'open', 'public', DATE_ADD(CURDATE(), INTERVAL 4 DAY), '20:00:00', 'Polisportiva Nord', '7v7', 14, 120.00, NULL, NULL, 0, NOW(), NOW());

-- Match 13: Future open match (5v5) with very few registrations (2/10), private visibility
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `created_at`, `updated_at`) VALUES
(13, 'simone_smeraldo', 'open', 'private', DATE_ADD(CURDATE(), INTERVAL 6 DAY), '21:30:00', 'Calcetto Club', '5v5', 10, 70.00, NULL, NULL, 0, NOW(), NOW());

-- Match 14: Future full match (5v5), 10 players registered, 1 in waitlist, no teams assigned (ready for team generation)
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `created_at`, `updated_at`) VALUES
(14, 'sofia_corallo', 'full', 'public', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '19:30:00', 'Arena Sport', '5v5', 10, 60.00, NULL, NULL, 0, NOW(), NOW());

-- Match 888: Future full match (5v5) starting in 2 hours (urgent/last-minute), 10 players registered, 1 waitlisted, no teams assigned
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `created_at`, `updated_at`) VALUES
(888, 'sofia_corallo', 'full', 'public', DATE(DATE_ADD(NOW(), INTERVAL 2 HOUR)), TIME(DATE_ADD(NOW(), INTERVAL 2 HOUR)), 'Campo Test Manuale', '5vs5', 10, 50.00, NULL, NULL, 0, NOW(), NOW());

-- Match 111: Future open match (5v5), urgent (in 2 days), 8/10 spots occupied (one guest), no teams assigned
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `created_at`, `updated_at`) VALUES
(111, 'luigi_verdi', 'open', 'public', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '20:30:00', 'Campus calcetto CUS', '5v5', 10, 60.00, NULL, NULL, 0, NOW(), NOW());

-- Match 211: Future open match (7v7) with 6 registrations (6/14), public visibility, no teams assigned
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `created_at`, `updated_at`) VALUES
(211, 'marco_bianchi', 'open', 'public', DATE_ADD(CURDATE(), INTERVAL 5 DAY), '19:00:00', 'Centro Sportivo Olimpia', '7v7', 14, 98.00, NULL, NULL, 0, NOW(), NOW());

-- Match 311: Future full match (5v5), 10 players registered, no teams assigned (ready for team generation)
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `created_at`, `updated_at`) VALUES
(311, 'paolo_marroni', 'full', 'public', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '21:00:00', 'Campetti San Paolo', '5v5', 10, 50.00, NULL, NULL, 0, NOW(), NOW());

-- Match 15: Finished match (5v5), completely resolved, scores set (4-8), MVP assigned to Vittorio Ossidiana
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `mvp_username`, `created_at`, `updated_at`) VALUES
(15, 'luigi_verdi', 'finished', 'public', DATE_SUB(CURDATE(), INTERVAL 3 DAY), '21:00:00', 'Campetti San Paolo', '5v5', 10, 55.00, 4, 8, 1, 'vittorio_ossidiana', NOW(), NOW());

-- Match 16: Finished match (5v5), completely resolved, scores set (10-2), MVP assigned to Chiara Rosa
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `mvp_username`, `created_at`, `updated_at`) VALUES
(16, 'chiara_rosa', 'finished', 'public', DATE_SUB(CURDATE(), INTERVAL 7 DAY), '19:00:00', 'Campus calcetto CUS', '5v5', 10, 60.00, 10, 2, 1, 'chiara_rosa', NOW(), NOW());

-- Match 17: Finished match (7v7), completely resolved, scores set (5-5), MVP assigned to Vittorio Ossidiana
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `mvp_username`, `created_at`, `updated_at`) VALUES
(17, 'marco_diamante', 'finished', 'private', DATE_SUB(CURDATE(), INTERVAL 14 DAY), '20:00:00', 'Centro Sportivo Olimpia', '7v7', 14, 98.00, 5, 5, 1, 'vittorio_ossidiana', NOW(), NOW());

-- Match 18: Finished match (5v5), completely resolved, scores set (6-7), MVP assigned to Lorenzo Zaffiro
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `mvp_username`, `created_at`, `updated_at`) VALUES
(18, 'lorenzo_zaffiro', 'finished', 'public', DATE_SUB(CURDATE(), INTERVAL 21 DAY), '22:00:00', 'Calcetto Club', '5v5', 10, 70.00, 6, 7, 1, 'lorenzo_zaffiro', NOW(), NOW());

-- Match 411: Finished match (5v5), completely resolved, scores set (7-5), MVP not assigned
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `result_home`, `result_away`, `mvp_assigned`, `created_at`, `updated_at`) VALUES
(411, 'roberto_arancio', 'finished', 'public', DATE_SUB(CURDATE(), INTERVAL 2 DAY), '18:30:00', 'Playground Stadio', '5v5', 10, 60.00, 7, 5, 0, NOW(), NOW());

-- Match 6: Cancelled match due to bad weather
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(6, 'giovanni_neri', 'cancelled', 'public', DATE_SUB(CURDATE(), INTERVAL 5 DAY), '21:00:00', 'Campo Periferia', '5v5', 10, 50.00, 'Meteo avverso', NOW(), NOW());

-- Match 7: Cancelled match due to bad weather
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(7, 'giovanni_neri', 'cancelled', 'public', DATE_SUB(CURDATE(), INTERVAL 12 DAY), '21:00:00', 'Campo Periferia', '5v5', 10, 50.00, 'Meteo avverso', NOW(), NOW());

-- Match 8: Cancelled match due to bad weather
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(8, 'giovanni_neri', 'cancelled', 'public', DATE_SUB(CURDATE(), INTERVAL 20 DAY), '21:00:00', 'Campo Periferia', '5v5', 10, 50.00, 'Meteo avverso', NOW(), NOW());

-- Match 9: Cancelled match due to bad weather
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(9, 'giovanni_neri', 'cancelled', 'public', DATE_SUB(CURDATE(), INTERVAL 25 DAY), '21:00:00', 'Campo Periferia', '5v5', 10, 50.00, 'Meteo avverso', NOW(), NOW());

-- Match 10: Cancelled match due to insufficient player count
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(10, 'andrea_gialli', 'cancelled', 'public', DATE_SUB(CURDATE(), INTERVAL 1 DAY), '22:00:00', 'Campetti San Paolo', '5v5', 10, 50.00, 'Non abbiamo raggiunto il numero', NOW(), NOW());

-- Match 19: Cancelled match due to pitch issues
INSERT INTO `matches` (`id`, `host_username`, `status`, `visibility`, `date`, `time`, `location`, `format`, `max_players`, `total_cost`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(19, 'vittorio_ossidiana', 'cancelled', 'public', DATE_SUB(CURDATE(), INTERVAL 2 DAY), '20:30:00', 'Arena Sport', '5v5', 10, 60.00, 'Infortunio del campo', NOW(), NOW());


-- --------------------------------------------------------
-- REGISTRATIONS
-- --------------------------------------------------------

INSERT INTO registrations (match_id, username, status, has_guest, team, goals_scored, created_at, updated_at) VALUES
-- Registrations for Match 1 (Future open, no teams, 3 seats occupied by luigi, prof_brown and gertrude)
(1, 'mario_rossi', 'registered', 0, NULL, 0, NOW(), NOW()),
(1, 'michele_marrone', 'registered', 0, NULL, 0, NOW(), NOW()),
(1, 'giulia_brunelli', 'registered', 0, NULL, 0, NOW(), NOW()),

-- Registrations for Match 2 (Future open, no teams, 2 seats occupied by tommaso and mario)
(2, 'tommaso_st', 'registered', 0, NULL, 0, NOW(), NOW()),
(2, 'mario_rossi', 'registered', 0, NULL, 0, NOW(), NOW()),

-- Registrations for Match 3 (Finished 4-2, MVP pending, 10 players registered, 5 home / 5 away, goals matching score)
(3, 'mario_rossi', 'registered', 0, 'home', 2, NOW(), NOW()),
(3, 'admin_test', 'registered', 0, 'home', 1, NOW(), NOW()),
(3, 'giulia_brunelli', 'registered', 0, 'home', 1, NOW(), NOW()),
(3, 'luigi_verdi', 'registered', 0, 'home', 0, NOW(), NOW()),
(3, 'marco_bianchi', 'registered', 0, 'home', 0, NOW(), NOW()),
(3, 'tommaso_st', 'registered', 0, 'away', 1, NOW(), NOW()),
(3, 'michele_marrone', 'registered', 0, 'away', 1, NOW(), NOW()),
(3, 'giovanni_neri', 'registered', 0, 'away', 0, NOW(), NOW()),
(3, 'andrea_gialli', 'registered', 0, 'away', 0, NOW(), NOW()),
(3, 'paolo_marroni', 'registered', 0, 'away', 0, NOW(), NOW()),

-- Registrations for Match 4 (Finished, scores pending, 10 players registered, 5 home / 5 away, 0 goals)
(4, 'admin_test', 'registered', 0, 'home', 0, NOW(), NOW()),
(4, 'mario_rossi', 'registered', 0, 'home', 0, NOW(), NOW()),
(4, 'luigi_verdi', 'registered', 0, 'home', 0, NOW(), NOW()),
(4, 'marco_bianchi', 'registered', 0, 'home', 0, NOW(), NOW()),
(4, 'paolo_marroni', 'registered', 0, 'home', 0, NOW(), NOW()),
(4, 'tommaso_st', 'registered', 0, 'away', 0, NOW(), NOW()),
(4, 'michele_marrone', 'registered', 0, 'away', 0, NOW(), NOW()),
(4, 'giulia_brunelli', 'registered', 0, 'away', 0, NOW(), NOW()),
(4, 'giovanni_neri', 'registered', 0, 'away', 0, NOW(), NOW()),
(4, 'andrea_gialli', 'registered', 0, 'away', 0, NOW(), NOW()),

-- Registrations for Match 5 (Finished 3-3, 14 players registered, 7 home / 7 away, goals matching score)
(5, 'luca_celeste', 'registered', 0, 'home', 1, NOW(), NOW()),
(5, 'alessandro_indaco', 'registered', 0, 'home', 0, NOW(), NOW()),
(5, 'daniele_oliva', 'registered', 0, 'home', 1, NOW(), NOW()),
(5, 'matteo_perla', 'registered', 0, 'home', 1, NOW(), NOW()),
(5, 'simone_smeraldo', 'registered', 0, 'home', 0, NOW(), NOW()),
(5, 'davide_topazio', 'registered', 0, 'home', 0, NOW(), NOW()),
(5, 'federico_rubino', 'registered', 0, 'home', 0, NOW(), NOW()),
(5, 'lorenzo_zaffiro', 'registered', 0, 'away', 2, NOW(), NOW()),
(5, 'giacomo_ambra', 'registered', 0, 'away', 0, NOW(), NOW()),
(5, 'emanuele_giada', 'registered', 0, 'away', 0, NOW(), NOW()),
(5, 'filippo_onice', 'registered', 0, 'away', 1, NOW(), NOW()),
(5, 'valerio_bronzo', 'registered', 0, 'away', 0, NOW(), NOW()),
(5, 'chiara_rosa', 'registered', 0, 'away', 0, NOW(), NOW()),
(5, 'martina_lilla', 'registered', 0, 'away', 0, NOW(), NOW()),

-- Registrations for Match 11 (Future open, no teams, 3/10 occupied)
(11, 'stefano_viola', 'registered', 0, NULL, 0, NOW(), NOW()),
(11, 'martina_lilla', 'registered', 0, NULL, 0, NOW(), NOW()),
(11, 'elena_ametista', 'registered', 0, NULL, 0, NOW(), NOW()),

-- Registrations for Match 12 (Future open, no teams, guest included, 4 seats occupied)
(12, 'luca_celeste', 'registered', 0, NULL, 0, NOW(), NOW()),
(12, 'simone_smeraldo', 'registered', 1, NULL, 0, NOW(), NOW()),
(12, 'giacomo_ambra', 'registered', 0, NULL, 0, NOW(), NOW()),
(12, 'filippo_onice', 'registered', 0, NULL, 0, NOW(), NOW()),

-- Registrations for Match 13 (Future open, no teams, 2/10 occupied)
(13, 'simone_smeraldo', 'registered', 0, NULL, 0, NOW(), NOW()),
(13, 'emanuele_giada', 'registered', 0, NULL, 0, NOW(), NOW()),

-- Registrations for Match 14 (Future full 10/10, 1 in waitlist, no teams assigned)
(14, 'sofia_corallo', 'registered', 0, NULL, 0, NOW(), NOW()),
(14, 'filippo_onice', 'registered', 0, NULL, 0, NOW(), NOW()),
(14, 'valerio_bronzo', 'registered', 0, NULL, 0, NOW(), NOW()),
(14, 'chiara_rosa', 'registered', 0, NULL, 0, NOW(), NOW()),
(14, 'martina_lilla', 'registered', 0, NULL, 0, NOW(), NOW()),
(14, 'giuseppe_turchese', 'registered', 0, NULL, 0, NOW(), NOW()),
(14, 'marco_diamante', 'registered', 0, NULL, 0, NOW(), NOW()),
(14, 'elena_ametista', 'registered', 0, NULL, 0, NOW(), NOW()),
(14, 'fabio_quarzo', 'registered', 0, NULL, 0, NOW(), NOW()),
(14, 'vittorio_ossidiana', 'registered', 0, NULL, 0, NOW(), NOW()),
(14, 'sara_rubino', 'waitlist', 0, NULL, 0, NOW(), NOW()),

-- Registrations for Match 888 (Future full 10/10, 1 in waitlist, urgent match starting in 2h, no teams assigned)
(888, 'sofia_corallo', 'registered', 0, NULL, 0, NOW(), NOW()),
(888, 'mario_rossi', 'registered', 0, NULL, 0, NOW(), NOW()),
(888, 'tommaso_st', 'registered', 0, NULL, 0, NOW(), NOW()),
(888, 'michele_marrone', 'registered', 0, NULL, 0, NOW(), NOW()),
(888, 'giulia_brunelli', 'registered', 0, NULL, 0, NOW(), NOW()),
(888, 'luigi_verdi', 'registered', 0, NULL, 0, NOW(), NOW()),
(888, 'giovanni_neri', 'registered', 0, NULL, 0, NOW(), NOW()),
(888, 'marco_bianchi', 'registered', 0, NULL, 0, NOW(), NOW()),
(888, 'andrea_gialli', 'registered', 0, NULL, 0, NOW(), NOW()),
(888, 'paolo_marroni', 'registered', 0, NULL, 0, NOW(), NOW()),
(888, 'admin_test', 'waitlist', 0, NULL, 0, NOW(), NOW()),

-- Registrations for Match 111 (Future open, urgent, 8 seats occupied with one guest: 8 seats total, no waitlist)
(111, 'luigi_verdi', 'registered', 0, NULL, 0, NOW(), NOW()),
(111, 'mario_rossi', 'registered', 0, NULL, 0, NOW(), NOW()),
(111, 'marco_bianchi', 'registered', 1, NULL, 0, NOW(), NOW()), 
(111, 'paolo_marroni', 'registered', 0, NULL, 0, NOW(), NOW()),
(111, 'stefano_viola', 'registered', 0, NULL, 0, NOW(), NOW()),
(111, 'roberto_arancio', 'registered', 0, NULL, 0, NOW(), NOW()),
(111, 'luca_celeste', 'registered', 0, NULL, 0, NOW(), NOW()),
(111, 'alessandro_indaco', 'registered', 0, NULL, 0, NOW(), NOW()),

-- Registrations for Match 211 (Future open, no teams, 6/14 occupied)
(211, 'marco_bianchi', 'registered', 0, NULL, 0, NOW(), NOW()),
(211, 'giovanni_neri', 'registered', 0, NULL, 0, NOW(), NOW()),
(211, 'andrea_gialli', 'registered', 0, NULL, 0, NOW(), NOW()),
(211, 'daniele_oliva', 'registered', 0, NULL, 0, NOW(), NOW()),
(211, 'valerio_bronzo', 'registered', 0, NULL, 0, NOW(), NOW()),
(211, 'giuseppe_turchese', 'registered', 0, NULL, 0, NOW(), NOW()),

-- Registrations for Match 311 (Future full 10/10, no teams assigned)
(311, 'paolo_marroni', 'registered', 0, NULL, 0, NOW(), NOW()),
(311, 'mario_rossi', 'registered', 0, NULL, 0, NOW(), NOW()),
(311, 'luigi_verdi', 'registered', 0, NULL, 0, NOW(), NOW()),
(311, 'marco_bianchi', 'registered', 0, NULL, 0, NOW(), NOW()),
(311, 'stefano_viola', 'registered', 0, NULL, 0, NOW(), NOW()),
(311, 'roberto_arancio', 'registered', 0, NULL, 0, NOW(), NOW()),
(311, 'luca_celeste', 'registered', 0, NULL, 0, NOW(), NOW()),
(311, 'alessandro_indaco', 'registered', 0, NULL, 0, NOW(), NOW()),
(311, 'daniele_oliva', 'registered', 0, NULL, 0, NOW(), NOW()),
(311, 'matteo_perla', 'registered', 0, NULL, 0, NOW(), NOW()),

-- Registrations for Match 15 (Finished 4-8, 10 players, MVP assigned, goals matching score)
(15, 'luigi_verdi', 'registered', 0, 'home', 0, NOW(), NOW()),
(15, 'giuseppe_turchese', 'registered', 0, 'home', 0, NOW(), NOW()),
(15, 'sofia_corallo', 'registered', 0, 'home', 2, NOW(), NOW()),
(15, 'marco_diamante', 'registered', 0, 'home', 1, NOW(), NOW()),
(15, 'elena_ametista', 'registered', 0, 'home', 1, NOW(), NOW()),
(15, 'fabio_quarzo', 'registered', 0, 'away', 0, NOW(), NOW()),
(15, 'vittorio_ossidiana', 'registered', 0, 'away', 4, NOW(), NOW()),
(15, 'sara_rubino', 'registered', 0, 'away', 1, NOW(), NOW()),
(15, 'giovanni_neri', 'registered', 0, 'away', 1, NOW(), NOW()),
(15, 'marco_bianchi', 'registered', 0, 'away', 2, NOW(), NOW()),

-- Registrations for Match 16 (Finished 10-2, 10 players, MVP assigned, goals matching score)
(16, 'chiara_rosa', 'registered', 0, 'home', 5, NOW(), NOW()),
(16, 'martina_lilla', 'registered', 0, 'home', 2, NOW(), NOW()),
(16, 'giuseppe_turchese', 'registered', 0, 'home', 1, NOW(), NOW()),
(16, 'sofia_corallo', 'registered', 0, 'home', 2, NOW(), NOW()),
(16, 'marco_diamante', 'registered', 0, 'home', 0, NOW(), NOW()),
(16, 'elena_ametista', 'registered', 0, 'away', 1, NOW(), NOW()),
(16, 'fabio_quarzo', 'registered', 0, 'away', 0, NOW(), NOW()),
(16, 'vittorio_ossidiana', 'registered', 0, 'away', 1, NOW(), NOW()),
(16, 'sara_rubino', 'registered', 0, 'away', 0, NOW(), NOW()),
(16, 'andrea_gialli', 'registered', 0, 'away', 0, NOW(), NOW()),

-- Registrations for Match 17 (Finished 5-5, 14 players, MVP assigned, goals matching score)
(17, 'marco_diamante', 'registered', 0, 'home', 0, NOW(), NOW()),
(17, 'elena_ametista', 'registered', 0, 'home', 0, NOW(), NOW()),
(17, 'fabio_quarzo', 'registered', 0, 'home', 0, NOW(), NOW()),
(17, 'vittorio_ossidiana', 'registered', 0, 'home', 3, NOW(), NOW()),
(17, 'sara_rubino', 'registered', 0, 'home', 1, NOW(), NOW()),
(17, 'luigi_verdi', 'registered', 0, 'home', 0, NOW(), NOW()),
(17, 'giovanni_neri', 'registered', 0, 'home', 1, NOW(), NOW()),
(17, 'marco_bianchi', 'registered', 0, 'away', 1, NOW(), NOW()),
(17, 'andrea_gialli', 'registered', 0, 'away', 1, NOW(), NOW()),
(17, 'paolo_marroni', 'registered', 0, 'away', 1, NOW(), NOW()),
(17, 'stefano_viola', 'registered', 0, 'away', 0, NOW(), NOW()),
(17, 'roberto_arancio', 'registered', 0, 'away', 2, NOW(), NOW()),
(17, 'luca_celeste', 'registered', 0, 'away', 0, NOW(), NOW()),
(17, 'alessandro_indaco', 'registered', 0, 'away', 0, NOW(), NOW()),

-- Registrations for Match 18 (Finished 6-7, 10 players, MVP assigned, goals matching score)
(18, 'lorenzo_zaffiro', 'registered', 0, 'home', 3, NOW(), NOW()),
(18, 'daniele_oliva', 'registered', 0, 'home', 1, NOW(), NOW()),
(18, 'matteo_perla', 'registered', 0, 'home', 2, NOW(), NOW()),
(18, 'simone_smeraldo', 'registered', 0, 'home', 0, NOW(), NOW()),
(18, 'davide_topazio', 'registered', 0, 'home', 0, NOW(), NOW()),
(18, 'filippo_onice', 'registered', 0, 'away', 2, NOW(), NOW()),
(18, 'valerio_bronzo', 'registered', 0, 'away', 0, NOW(), NOW()),
(18, 'chiara_rosa', 'registered', 0, 'away', 3, NOW(), NOW()),
(18, 'martina_lilla', 'registered', 0, 'away', 1, NOW(), NOW()),
(18, 'giuseppe_turchese', 'registered', 0, 'away', 1, NOW(), NOW()),

-- Registrations for Match 411 (Finished 7-5, 10 players, no MVP assigned, goals matching score)
(411, 'roberto_arancio', 'registered', 0, 'home', 3, NOW(), NOW()),
(411, 'mario_rossi', 'registered', 0, 'home', 2, NOW(), NOW()),
(411, 'marco_bianchi', 'registered', 0, 'home', 2, NOW(), NOW()),
(411, 'simone_smeraldo', 'registered', 0, 'home', 0, NOW(), NOW()),
(411, 'davide_topazio', 'registered', 0, 'home', 0, NOW(), NOW()),
(411, 'federico_rubino', 'registered', 0, 'away', 2, NOW(), NOW()),
(411, 'lorenzo_zaffiro', 'registered', 0, 'away', 3, NOW(), NOW()),
(411, 'giacomo_ambra', 'registered', 0, 'away', 0, NOW(), NOW()),
(411, 'emanuele_giada', 'registered', 0, 'away', 0, NOW(), NOW()),
(411, 'filippo_onice', 'registered', 0, 'away', 0, NOW(), NOW()),

-- Registrations for Match 6 (Cancelled, host and other players registered with cancelled status)
(6, 'giovanni_neri', 'cancelled', 0, NULL, 0, NOW(), NOW()),
(6, 'admin_test', 'cancelled', 0, NULL, 0, NOW(), NOW()),
(6, 'mario_rossi', 'cancelled', 0, NULL, 0, NOW(), NOW()),

-- Registrations for Match 7 (Cancelled, host registered with cancelled status)
(7, 'giovanni_neri', 'cancelled', 0, NULL, 0, NOW(), NOW()),

-- Registrations for Match 8 (Cancelled, host registered with cancelled status)
(8, 'giovanni_neri', 'cancelled', 0, NULL, 0, NOW(), NOW()),

-- Registrations for Match 9 (Cancelled, host registered with cancelled status)
(9, 'giovanni_neri', 'cancelled', 0, NULL, 0, NOW(), NOW()),

-- Registrations for Match 10 (Cancelled, host registered with cancelled status)
(10, 'andrea_gialli', 'cancelled', 0, NULL, 0, NOW(), NOW()),

-- Registrations for Match 19 (Cancelled, host registered with cancelled status)
(19, 'vittorio_ossidiana', 'cancelled', 0, NULL, 0, NOW(), NOW());


-- --------------------------------------------------------
-- FRIENDSHIPS
-- --------------------------------------------------------

INSERT INTO friendships (sender_username, recipient_username, status, created_at) VALUES
('admin_test', 'mario_rossi', 'accepted', NOW()),
('tommaso_st', 'mario_rossi', 'pending', NOW()),
('tommaso_st', 'admin_test', 'pending', NOW()),
('giulia_brunelli', 'michele_marrone', 'accepted', NOW());


-- --------------------------------------------------------
-- EVALUATIONS
-- --------------------------------------------------------

INSERT INTO evaluations (evaluator_username, evaluated_username, match_id, skill_vote, thumb_down, created_at) VALUES
('mario_rossi', 'roberto_arancio', 4, 4, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('mario_rossi', 'marco_bianchi', 4, 4, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('mario_rossi', 'simone_smeraldo', 4, 3, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('mario_rossi', 'davide_topazio', 4, 5, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('roberto_arancio', 'mario_rossi', 4, 5, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('roberto_arancio', 'marco_bianchi', 4, 3, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('roberto_arancio', 'simone_smeraldo', 4, 2, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('roberto_arancio', 'davide_topazio', 4, 4, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('luigi_verdi', 'giuseppe_turchese', 15, 4, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('luigi_verdi', 'sofia_corallo', 15, 5, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('sofia_corallo', 'luigi_verdi', 15, 3, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('chiara_rosa', 'martina_lilla', 16, 4, 0, DATE_SUB(NOW(), INTERVAL 6 DAY)),
('chiara_rosa', 'giuseppe_turchese', 16, 2, 1, DATE_SUB(NOW(), INTERVAL 6 DAY)),
('federico_rubino', 'lorenzo_zaffiro', 4, 5, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('federico_rubino', 'giacomo_ambra', 4, 1, 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('federico_rubino', 'emanuele_giada', 4, 4, 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('federico_rubino', 'filippo_onice', 4, 3, 0, DATE_SUB(NOW(), INTERVAL 2 DAY));


-- --------------------------------------------------------
-- TRUST HISTORY
-- --------------------------------------------------------

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


-- --------------------------------------------------------
-- REPORTS
-- --------------------------------------------------------

INSERT INTO reports (reporter_username, reported_username, match_id, reason, description, status, admin_notes, created_at, updated_at) VALUES
('chiara_rosa', 'giuseppe_turchese', 16, 'Tossicità', 'Ha insultato pesantemente tutti i compagni di squadra in chat per tutta la partita.', 'pending', NULL, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY)),
('luigi_verdi', 'francesco_blu', 15, 'Assenza non giustificata', 'Non si è presentato alla partita senza avvisare prima.', 'resolved', 'Utente ammonito e penalizzato nel trust score.', DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY));


-- --------------------------------------------------------
-- NOTIFICATIONS
-- --------------------------------------------------------

INSERT INTO notifications (user_recipient, type, message, link, is_read, created_at, updated_at) VALUES
-- Friend request notification for admin_test from tommaso_st
('admin_test', 'friend_request', '👋 Tommaso Stella (@tommaso_st) ti ha inviato una richiesta di amicizia!', '/profile?tab=social', 0, DATE_SUB(NOW(), INTERVAL 1 HOUR), NOW()),

-- Friend request notification for mario_rossi from tommaso_st
('mario_rossi', 'friend_request', '👋 Tommaso Stella (@tommaso_st) ti ha inviato una richiesta di amicizia!', '/profile?tab=social', 0, DATE_SUB(NOW(), INTERVAL 2 HOUR), NOW()),

-- Friend accept notification for admin_test from mario_rossi
('admin_test', 'friend_accept', '🤝 Mario Rossi (@mario_rossi) ha accettato la tua richiesta di amicizia!', '/profile?username=mario_rossi', 0, DATE_SUB(NOW(), INTERVAL 1 DAY), NOW()),

-- Match cancellation notification for admin_test for Match 6 (cancelled by giovanni_neri)
('admin_test', 'match_cancellation', '⚠️ La partita a Campo Periferia del 28/06/2026 è stata annullata dall\'organizzatore (Motivo: Meteo avverso).', '/matches/6', 0, DATE_SUB(NOW(), INTERVAL 5 DAY), NOW()),

-- Match cancellation notification for mario_rossi for Match 6
('mario_rossi', 'match_cancellation', '⚠️ La partita a Campo Periferia del 28/06/2026 è stata annullata dall\'organizzatore (Motivo: Meteo avverso).', '/matches/6', 0, DATE_SUB(NOW(), INTERVAL 5 DAY), NOW());

SET FOREIGN_KEY_CHECKS = 1;
