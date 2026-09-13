-- ==========================================================
-- EroCMS Migration 001: Visitor Activity Tracking Table
-- Tavsif: Sayt mehmonlarining harakatlari (like, sevimli, qidiruv, yuklash, ko'rish) logi
-- ==========================================================

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
