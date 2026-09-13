-- =========================================================
-- EroCMS Migration 002: Izohlar (Comments) Jadvalini To'g'rilash
-- =========================================================

-- Agar sizning bazangizda `ero_comments` jadvalida `id_file` yoki `comment` nomli eski ustunlar bo'lsa:
-- ALTER TABLE `ero_comments` CHANGE `id_file` `id_video` INT(11) NOT NULL;
-- ALTER TABLE `ero_comments` CHANGE `comment` `text` TEXT NOT NULL;

-- Agar `ero_comments` jadvali hali ochilmagan bo'lsa:
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
