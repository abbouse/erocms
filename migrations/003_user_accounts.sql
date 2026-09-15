-- ============================================================
-- Migration 003: User Accounts (Foydalanuvchi profillari va moderatsiya)
-- Sana: 2026-09-15
-- ============================================================

USE `sekschi`;
SET NAMES utf8mb4;

-- 1. Sayt foydalanuvchilari jadvali
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

-- 2. Foydalanuvchilar yuklagan videolar (moderatsiya kutmoqda)
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

-- 3. Izohlardagi javoblar va bildirishnomalar jadvali
CREATE TABLE IF NOT EXISTS `ero_notifications` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `member_id`   INT(11) NOT NULL COMMENT 'Qabul qiluvchi a\'zo ID',
  `from_author` VARCHAR(100) NOT NULL COMMENT 'Javob yozgan odam niki',
  `id_video`    INT(11) NOT NULL COMMENT 'Video ID',
  `comment_id`  INT(11) NOT NULL DEFAULT 0,
  `text`        TEXT NOT NULL,
  `is_read`     TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=unread, 1=read',
  `date`        INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_member_read` (`member_id`, `is_read`),
  KEY `idx_video` (`id_video`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. ero_files jadvaliga member_id ustuni qo'shish (agar yo'q bo'lsa)
DROP PROCEDURE IF EXISTS upgrade_ero_files_member_id;
DELIMITER $$
CREATE PROCEDURE upgrade_ero_files_member_id()
BEGIN
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'member_id'
    ) THEN
        ALTER TABLE `ero_files` ADD COLUMN `member_id` INT(11) NOT NULL DEFAULT 0 AFTER `added`;
    END IF;
END$$
DELIMITER ;

CALL upgrade_ero_files_member_id();
DROP PROCEDURE IF EXISTS upgrade_ero_files_member_id;
