-- ====================================================================
-- EroCMS: Bosh Sahifa Smart Feed Algoritmi Migratsiyasi
-- ====================================================================

USE `sekschi`;

-- 1. ero_files jadvaliga sevimli, izohlar soni va umumiy reyting balli ustunlarini qo'shish
SET @col_fav := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'favorites_count');
SET @sql_fav := IF(@col_fav = 0, 'ALTER TABLE `ero_files` ADD `favorites_count` INT(11) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt_fav FROM @sql_fav;
EXECUTE stmt_fav;
DEALLOCATE PREPARE stmt_fav;

SET @col_comm := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'comments_count');
SET @sql_comm := IF(@col_comm = 0, 'ALTER TABLE `ero_files` ADD `comments_count` INT(11) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt_comm FROM @sql_comm;
EXECUTE stmt_comm;
DEALLOCATE PREPARE stmt_comm;

SET @col_sc := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'score');
SET @sql_sc := IF(@col_sc = 0, 'ALTER TABLE `ero_files` ADD `score` INT(11) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt_sc FROM @sql_sc;
EXECUTE stmt_sc;
DEALLOCATE PREPARE stmt_sc;

-- 2. Tezkor so'rovlar uchun indekslar yaratish
SET @idx_sc := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND INDEX_NAME = 'idx_score');
SET @sql_idx_sc := IF(@idx_sc = 0, 'ALTER TABLE `ero_files` ADD INDEX `idx_score` (`score`)', 'SELECT 1');
PREPARE stmt_idx_sc FROM @sql_idx_sc;
EXECUTE stmt_idx_sc;
DEALLOCATE PREPARE stmt_idx_sc;

SET @idx_dt := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND INDEX_NAME = 'idx_date_id');
SET @sql_idx_dt := IF(@idx_dt = 0, 'ALTER TABLE `ero_files` ADD INDEX `idx_date_id` (`date`, `id`)', 'SELECT 1');
PREPARE stmt_idx_dt FROM @sql_idx_dt;
EXECUTE stmt_idx_dt;
DEALLOCATE PREPARE stmt_idx_dt;

SET @idx_vw := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND INDEX_NAME = 'idx_view');
SET @sql_idx_vw := IF(@idx_vw = 0, 'ALTER TABLE `ero_files` ADD INDEX `idx_view` (`view`)', 'SELECT 1');
PREPARE stmt_idx_vw FROM @sql_idx_vw;
EXECUTE stmt_idx_vw;
DEALLOCATE PREPARE stmt_idx_vw;

-- 3. Boshlang'ich sinxronizatsiya
UPDATE `ero_files` f SET 
  f.comments_count = (SELECT COUNT(*) FROM `ero_comments` c WHERE c.id_video = f.id),
  f.favorites_count = (SELECT COUNT(*) FROM `ero_favorites` fav WHERE fav.id_video = f.id);

-- 4. Koeffitsiyent bo'yicha ballarni dastlabki hisoblash
-- Ko'rishlar (1) + Layklar (10) - Dislayklar (5) + Yuklashlar (15) + Izohlar (20) + Sevimlilar (25)
UPDATE `ero_files` SET 
  `score` = (`view` * 1) + (`likes` * 10) - (`dislikes` * 5) + (`downloads` * 15) + (`comments_count` * 20) + (`favorites_count` * 25);
