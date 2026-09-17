-- ====================================================================
-- EroCMS: Tezlik va Performans Indekslari (Page Loading Speed Optimization)
-- ====================================================================

USE `sekschi`;

-- 1. Kategoriya sahifalari uchun kompozit indekslar (category + date, category + view)
-- Ushbu indekslar kategoriya ichidagi 1000 lab videolarni saralash va sanashni 
-- to'liq jadvalni scan qilish (Full Table Scan) o'rniga 0.001 soniyaga tushiradi.
SET @idx_cd := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND INDEX_NAME = 'idx_category_date');
SET @sql_cd := IF(@idx_cd = 0, 'ALTER TABLE `ero_files` ADD INDEX `idx_category_date` (`category`, `date`)', 'SELECT 1');
PREPARE stmt_cd FROM @sql_cd;
EXECUTE stmt_cd;
DEALLOCATE PREPARE stmt_cd;

SET @idx_cv := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND INDEX_NAME = 'idx_category_view');
SET @sql_cv := IF(@idx_cv = 0, 'ALTER TABLE `ero_files` ADD INDEX `idx_category_view` (`category`, `view`)', 'SELECT 1');
PREPARE stmt_cv FROM @sql_cv;
EXECUTE stmt_cv;
DEALLOCATE PREPARE stmt_cv;

-- 2. Translit bo'yicha tezkor video qidirish (watch/{translit}.html)
SET @idx_tr := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND INDEX_NAME = 'idx_translit');
SET @sql_tr := IF(@idx_tr = 0, 'ALTER TABLE `ero_files` ADD INDEX `idx_translit` (`translit`(191))', 'SELECT 1');
PREPARE stmt_tr FROM @sql_tr;
EXECUTE stmt_tr;
DEALLOCATE PREPARE stmt_tr;

-- 3. Kategoriyalar transliti bo'yicha indeks ({category_translit}/)
SET @idx_ctr := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_categories' AND INDEX_NAME = 'idx_cat_translit');
SET @sql_ctr := IF(@idx_ctr = 0, 'ALTER TABLE `ero_categories` ADD INDEX `idx_cat_translit` (`translit`(191))', 'SELECT 1');
PREPARE stmt_ctr FROM @sql_ctr;
EXECUTE stmt_ctr;
DEALLOCATE PREPARE stmt_ctr;

-- 4. Sevimlilar (Favorites) va Onlayn foydalanuvchilar tezkor so'rovi
SET @idx_fav := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_favorites' AND INDEX_NAME = 'idx_video_data');
SET @sql_fav := IF(@idx_fav = 0, 'ALTER TABLE `ero_favorites` ADD INDEX `idx_video_data` (`id_video`, `data`(45))', 'SELECT 1');
PREPARE stmt_fav FROM @sql_fav;
EXECUTE stmt_fav;
DEALLOCATE PREPARE stmt_fav;

SET @idx_onl := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_online' AND INDEX_NAME = 'idx_online_date');
SET @sql_onl := IF(@idx_onl = 0, 'ALTER TABLE `ero_online` ADD INDEX `idx_online_date` (`date`)', 'SELECT 1');
PREPARE stmt_onl FROM @sql_onl;
EXECUTE stmt_onl;
DEALLOCATE PREPARE stmt_onl;
