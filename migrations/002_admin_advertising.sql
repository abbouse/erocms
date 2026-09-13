-- ==========================================================
-- EroCMS Migration 002: Admin Advertising Management Table
-- Tavsif: Faqat admin boshqaradigan reklama bannerlari va havolalari
-- ==========================================================

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
