-- ====================================================================
-- EroCMS: phpMyAdmin Uchun Tayyor SQL Skript (Barcha Yangi Imkoniyatlar)
-- Baza nomi: sekschi
-- ====================================================================
-- QO'LLANMA:
-- 1. phpMyAdmin-ga kiring va chap tomondan "sekschi" bazasini tanlang.
-- 2. Yuqoridagi "SQL" bo'limiga o'ting.
-- 3. Quyidagi barcha kodni nusxalab (copy) joylang va "Go" (Вперёд) tugmasini bosing.
-- ====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------------------
-- 1. Foydalanuvchilar (A'zolar) Jadvali
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ero_members` (
  `id`           INT(11) NOT NULL AUTO_INCREMENT,
  `username`     VARCHAR(50) NOT NULL,
  `email`        VARCHAR(150) NULL DEFAULT NULL,
  `password`     VARCHAR(255) NOT NULL,
  `avatar`       VARCHAR(255) NOT NULL DEFAULT '',
  `bio`          TEXT,
  `status`       TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 0=banned',
  `token`        VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Remember me / sessiya tokeni',
  `reset_token`  VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Parolni tiklash tokeni',
  `reset_expiry` INT(11) NOT NULL DEFAULT 0,
  `date`         INT(11) NOT NULL,
  `last_seen`    INT(11) NOT NULL DEFAULT 0,
  `ip`           VARCHAR(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `email` (`email`),
  KEY `token` (`token`(32))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Email majburiy bo'lmasligi uchun (agar jadval avval yaratilgan bo'lsa)
ALTER TABLE `ero_members` MODIFY `email` VARCHAR(150) NULL DEFAULT NULL;

-- --------------------------------------------------------------------
-- 2. Foydalanuvchilar Yuklagan Videolar (Moderatsiya Navbati)
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
-- 3. Izohlardagi Javoblar va Bildirishnomalar Jadvali
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ero_notifications` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `member_id`   INT(11) NOT NULL COMMENT 'Xabar oluvchi a\'zo ID',
  `from_author` VARCHAR(100) NOT NULL COMMENT 'Javob bergan a\'zo niki',
  `id_video`    INT(11) NOT NULL COMMENT 'Video ID',
  `comment_id`  INT(11) NOT NULL DEFAULT 0,
  `text`        TEXT NOT NULL,
  `is_read`     TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=o\'qilmagan, 1=o\'qilgan',
  `date`        INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_member_read` (`member_id`, `is_read`),
  KEY `idx_video` (`id_video`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 4. Mehmonlar va Foydalanuvchilar Harakatlari (Statistika)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ero_activity` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `ip`          VARCHAR(45) NOT NULL,
  `user_agent`  VARCHAR(255) DEFAULT NULL,
  `action`      ENUM('view', 'like', 'dislike', 'favorite', 'unfavorite', 'download', 'search', 'comment') NOT NULL,
  `id_file`     INT(11) DEFAULT '0',
  `query_text`  TEXT DEFAULT NULL,
  `date`        INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_action` (`action`),
  KEY `idx_id_file` (`id_file`),
  KEY `idx_date` (`date`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 5. Reklamalar Jadvali
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ero_advertising` (
  `id`       INT(11) NOT NULL AUTO_INCREMENT,
  `site`     TEXT NOT NULL,
  `name`     TEXT NOT NULL,
  `colour`   VARCHAR(32) DEFAULT '#ff9900',
  `term`     INT(11) NOT NULL DEFAULT '0',
  `owner`    VARCHAR(64) DEFAULT 'admin',
  `position` VARCHAR(32) DEFAULT 'all',
  `clicks`   INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_term` (`term`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 6. DMCA Shikoyatlari Jadvali
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ero_dmca` (
  `id`        INT(11) NOT NULL AUTO_INCREMENT,
  `name`      VARCHAR(255) NOT NULL,
  `email`     VARCHAR(255) NOT NULL,
  `video_url` TEXT NOT NULL,
  `message`   TEXT NOT NULL,
  `date`      INT(11) NOT NULL,
  `status`    INT(11) NOT NULL DEFAULT '0',
  `ip`        VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 7. Izohlar Jadvali
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ero_comments` (
  `id`       INT(11) NOT NULL AUTO_INCREMENT,
  `id_video` INT(11) NOT NULL,
  `author`   VARCHAR(128) NOT NULL,
  `text`     TEXT NOT NULL,
  `date`     INT(11) NOT NULL,
  `ip`       VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_video` (`id_video`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 8. Like va Dislike Ovozlar Jadvali
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ero_likes` (
  `id`       INT(11) NOT NULL AUTO_INCREMENT,
  `id_video` INT(11) NOT NULL,
  `ip`       VARCHAR(45) NOT NULL,
  `type`     ENUM('like','dislike') NOT NULL DEFAULT 'like',
  `date`     INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_like_video` (`id_video`),
  KEY `idx_like_ip_video` (`id_video`, `ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 9. Sevimlilar (Favorites) Jadvali
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ero_favorites` (
  `id`       INT(11) NOT NULL AUTO_INCREMENT,
  `id_video` INT(11) NOT NULL,
  `data`     VARCHAR(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fav_video` (`id_video`),
  KEY `idx_fav_data` (`data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------------------
-- 10. Asosiy `ero_files` Jadvaliga Yangi Ustunlarni Xavfsiz Qo'shish
-- (Agar ustun allaqachon mavjud bo'lsa xato bermay o'tkazib yuboradi)
-- --------------------------------------------------------------------
SET @dbname = DATABASE();

-- likes
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'likes');
SET @query = IF(@col = 0, 'ALTER TABLE `ero_files` ADD COLUMN `likes` INT(11) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- dislikes
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'dislikes');
SET @query = IF(@col = 0, 'ALTER TABLE `ero_files` ADD COLUMN `dislikes` INT(11) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- embed
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'embed');
SET @query = IF(@col = 0, 'ALTER TABLE `ero_files` ADD COLUMN `embed` TEXT NULL', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- member_id (videoni qaysi ro'yxatdan o'tgan user yuklaganligi)
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'member_id');
SET @query = IF(@col = 0, 'ALTER TABLE `ero_files` ADD COLUMN `member_id` INT(11) NOT NULL DEFAULT 0 AFTER `added`', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- favorites_count (smart feed tavsiyalar algoritmi uchun)
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'favorites_count');
SET @query = IF(@col = 0, 'ALTER TABLE `ero_files` ADD COLUMN `favorites_count` INT(11) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- comments_count
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'comments_count');
SET @query = IF(@col = 0, 'ALTER TABLE `ero_files` ADD COLUMN `comments_count` INT(11) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- score (smart feed umumiy engagement balli)
SET @col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'score');
SET @query = IF(@col = 0, 'ALTER TABLE `ero_files` ADD COLUMN `score` INT(11) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------------------
-- 11. Bosh Sahifa va Qidiruvni Tezlashtiruvchi Indekslar
-- --------------------------------------------------------------------
SET @idx = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'ero_files' AND INDEX_NAME = 'idx_score');
SET @query = IF(@idx = 0, 'ALTER TABLE `ero_files` ADD INDEX `idx_score` (`score`)', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'ero_files' AND INDEX_NAME = 'idx_date_id');
SET @query = IF(@idx = 0, 'ALTER TABLE `ero_files` ADD INDEX `idx_date_id` (`date`, `id`)', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'ero_files' AND INDEX_NAME = 'idx_view');
SET @query = IF(@idx = 0, 'ALTER TABLE `ero_files` ADD INDEX `idx_view` (`view`)', 'SELECT 1');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------------------
-- 12. Dastlabki Hisoblash va Sinxronizatsiya
-- --------------------------------------------------------------------
UPDATE `ero_files` f SET 
  f.comments_count = (SELECT COUNT(*) FROM `ero_comments` c WHERE c.id_video = f.id),
  f.favorites_count = (SELECT COUNT(*) FROM `ero_favorites` fav WHERE fav.id_video = f.id);

UPDATE `ero_files` SET 
  `score` = (`view` * 1) + (`likes` * 10) - (`dislikes` * 5) + (`downloads` * 15) + (`comments_count` * 20) + (`favorites_count` * 25);

SET FOREIGN_KEY_CHECKS = 1;
