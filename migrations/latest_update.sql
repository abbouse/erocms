-- ==========================================================
-- EroCMS: Birlashgan Yangilanishlar (Latest Update)
-- Ushbu SQL faylni phpMyAdmin yoki MySQL CLI orqali yuklang
-- ==========================================================

-- 1. DMCA Mualliflik Huquqi Shikoyatlari Jadvali
CREATE TABLE IF NOT EXISTS `ero_dmca` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `video_url` TEXT NOT NULL,
  `message` TEXT NOT NULL,
  `date` INT(11) NOT NULL,
  `status` INT(11) NOT NULL DEFAULT '0',
  `ip` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Mehmonlar Harakatlari Monitoringi Jadvali (Statistika)
CREATE TABLE IF NOT EXISTS `ero_activity` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `action` ENUM('view', 'like', 'dislike', 'favorite', 'unfavorite', 'download', 'search', 'comment') NOT NULL,
  `id_file` INT(11) DEFAULT '0',
  `query_text` TEXT DEFAULT NULL,
  `date` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_action` (`action`),
  KEY `idx_id_file` (`id_file`),
  KEY `idx_date` (`date`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Faqat Admin Boshqaradigan Reklama Jadvali
CREATE TABLE IF NOT EXISTS `ero_advertising` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `site` TEXT NOT NULL,
  `name` TEXT NOT NULL,
  `colour` VARCHAR(32) DEFAULT '#ff9900',
  `term` INT(11) NOT NULL DEFAULT '0',
  `owner` VARCHAR(64) DEFAULT 'admin',
  `position` VARCHAR(32) DEFAULT 'all',
  `clicks` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_term` (`term`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Foydalanuvchilar Izohlari Jadvali
CREATE TABLE IF NOT EXISTS `ero_comments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_file` INT(11) NOT NULL,
  `author` VARCHAR(128) NOT NULL,
  `comment` TEXT NOT NULL,
  `date` INT(11) NOT NULL,
  `ip` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_file` (`id_file`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
