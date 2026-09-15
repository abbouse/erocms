-- ====================================================================
-- EroCMS: Barcha Yangi Migratsiyalar (Master Migrations File)
-- Baza nomi: sekschi
-- Foydalanuvchi: sekschi
-- Parol: sekschi123
-- ====================================================================
-- 
-- Server terminalida bir martada yuklash buyrug'i:
-- mysql -u sekschi -psekschi123 sekschi < /var/www/www-root/data/www/sekschi.online/migrations/all_migrations.sql
-- 
-- Yoki phpMyAdmin orqali "sekschi" bazasini tanlab "Import" (Импорт) bo'limiga yuklang.
-- ====================================================================

USE `sekschi`;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------------------
-- 1. Mehmonlar Harakatlari Monitoringi Jadvali (Jonli Statistika)
-- --------------------------------------------------------------------
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

-- --------------------------------------------------------------------
-- 2. Faqat Admin Boshqaradigan Reklama Tizimi Jadvali
-- --------------------------------------------------------------------
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

-- --------------------------------------------------------------------
-- 3. DMCA Mualliflik Huquqi Shikoyatlari Jadvali
-- --------------------------------------------------------------------
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

-- --------------------------------------------------------------------
-- 4. Foydalanuvchilar Izohlari Jadvali (To'g'ri: id_video va text)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ero_comments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_video` INT(11) NOT NULL,
  `author` VARCHAR(128) NOT NULL,
  `text` TEXT NOT NULL,
  `date` INT(11) NOT NULL,
  `ip` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_video` (`id_video`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 5. Like / Dislike Ovoz Berish Jadvali
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ero_likes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_video` INT(11) NOT NULL,
  `ip` VARCHAR(45) NOT NULL,
  `type` ENUM('like','dislike') NOT NULL DEFAULT 'like',
  `date` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_like_video` (`id_video`),
  KEY `idx_like_ip_video` (`id_video`, `ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 6. Sevimlilarga Qo'shilgan Videolar Jadvali
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ero_favorites` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_video` INT(11) NOT NULL,
  `data` VARCHAR(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fav_video` (`id_video`),
  KEY `idx_fav_data` (`data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 7. ero_files Jadvaliga Yangi Ustunlarni Xavfsiz Qo'shish (agar yo'q bo'lsa)
-- --------------------------------------------------------------------
DROP PROCEDURE IF EXISTS upgrade_ero_files_schema;
DELIMITER $$
CREATE PROCEDURE upgrade_ero_files_schema()
BEGIN
    -- likes ustuni
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'likes'
    ) THEN
        ALTER TABLE `ero_files` ADD COLUMN `likes` INT(11) NOT NULL DEFAULT 0;
    END IF;

    -- dislikes ustuni
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'dislikes'
    ) THEN
        ALTER TABLE `ero_files` ADD COLUMN `dislikes` INT(11) NOT NULL DEFAULT 0;
    END IF;

    -- embed ustuni
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'embed'
    ) THEN
        ALTER TABLE `ero_files` ADD COLUMN `embed` TEXT NULL;
    END IF;

    -- ero_comments ustunlarini to'g'rilash (id_file -> id_video, comment -> text)
    IF EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_comments' AND COLUMN_NAME = 'id_file'
    ) THEN
        ALTER TABLE `ero_comments` CHANGE `id_file` `id_video` INT(11) NOT NULL;
    END IF;

    IF EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_comments' AND COLUMN_NAME = 'comment'
    ) THEN
        ALTER TABLE `ero_comments` CHANGE `comment` `text` TEXT NOT NULL;
    END IF;

    -- ero_files jadvaliga member_id ustuni qo'shish
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'member_id'
    ) THEN
        ALTER TABLE `ero_files` ADD COLUMN `member_id` INT(11) NOT NULL DEFAULT 0 AFTER `added`;
    END IF;

    -- ero_settings dizayn variantlarini tekshirish
    IF EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_settings' AND COLUMN_NAME = 'designs'
    ) THEN
        ALTER TABLE `ero_settings` MODIFY `designs` ENUM('pink','red','violet') NOT NULL DEFAULT 'red';
    END IF;
END$$
DELIMITER ;

CALL upgrade_ero_files_schema();
DROP PROCEDURE IF EXISTS upgrade_ero_files_schema;

-- --------------------------------------------------------------------
-- 8. Sayt Foydalanuvchilari (A'zolar) Jadvali
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ero_members` (
  `id`           INT(11) NOT NULL AUTO_INCREMENT,
  `username`     VARCHAR(50) NOT NULL,
  `email`        VARCHAR(150) NOT NULL,
  `password`     VARCHAR(255) NOT NULL,
  `avatar`       VARCHAR(255) NOT NULL DEFAULT '',
  `bio`          TEXT,
  `status`       TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 0=banned',
  `token`        VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Remember me / session token',
  `reset_token`  VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Password reset token',
  `reset_expiry` INT(11) NOT NULL DEFAULT 0,
  `date`         INT(11) NOT NULL,
  `last_seen`    INT(11) NOT NULL DEFAULT 0,
  `ip`           VARCHAR(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  KEY `token` (`token`(32))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 9. Foydalanuvchilar Yuklagan Videolar (Moderatsiya Navbati)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ero_user_videos` (
  `id`            INT(11) NOT NULL AUTO_INCREMENT,
  `member_id`     INT(11) NOT NULL DEFAULT 0,
  `name`          VARCHAR(255) NOT NULL,
  `description`   TEXT,
  `category`      INT(11) NOT NULL DEFAULT 0,
  `tags`          VARCHAR(500) NOT NULL DEFAULT '',
  `video_url`     VARCHAR(500) NOT NULL DEFAULT '',
  `file_path`     VARCHAR(500) NOT NULL DEFAULT '',
  `screenshot`    VARCHAR(500) NOT NULL DEFAULT '',
  `status`        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reject_reason` VARCHAR(255) NOT NULL DEFAULT '',
  `date`          INT(11) NOT NULL,
  `ip`            VARCHAR(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 10. Izohlardagi Javoblar va Bildirishnomalar Jadvali
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ero_notifications` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `member_id`   INT(11) NOT NULL COMMENT 'Qabul qiluvchi a\'zo ID',
  `from_author` VARCHAR(100) NOT NULL COMMENT 'Javob yozgan foydalanuvchi niki',
  `id_video`    INT(11) NOT NULL COMMENT 'Video ID',
  `comment_id`  INT(11) NOT NULL DEFAULT 0,
  `text`        TEXT NOT NULL,
  `is_read`     TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=unread, 1=read',
  `date`        INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_member_read` (`member_id`, `is_read`),
  KEY `idx_video` (`id_video`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ====================================================================
-- Barcha Migratsiyalar Muvaffaqiyatli Yakunlandi!
-- ====================================================================

