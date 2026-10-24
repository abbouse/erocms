-- ====================================================================
-- EroCMS: Remote Video Stream Failover & Performance Optimization
-- ====================================================================

USE `sekschi`;

-- 1. remote_url ustunini qo'shish (Ota sayt video oqimi havolasi)
SET @col_remote := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'remote_url');
SET @sql_remote := IF(@col_remote = 0, 'ALTER TABLE `ero_files` ADD COLUMN `remote_url` TEXT NULL AFTER `embed`', 'SELECT 1');
PREPARE stmt_rem FROM @sql_remote;
EXECUTE stmt_rem;
DEALLOCATE PREPARE stmt_rem;

-- 2. origin_url ustunini qo'shish (Donor sahifa havolasi)
SET @col_origin := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND COLUMN_NAME = 'origin_url');
SET @sql_origin := IF(@col_origin = 0, 'ALTER TABLE `ero_files` ADD COLUMN `origin_url` VARCHAR(1000) NULL AFTER `remote_url`', 'SELECT 1');
PREPARE stmt_orig FROM @sql_origin;
EXECUTE stmt_orig;
DEALLOCATE PREPARE stmt_orig;

-- 3. Mavjud videolar uchun zaxira to'ldirish
UPDATE `ero_files` SET `remote_url` = `address` WHERE (`remote_url` IS NULL OR `remote_url` = '') AND `address` LIKE 'http%';
UPDATE `ero_files` SET `remote_url` = `recoil` WHERE (`remote_url` IS NULL OR `remote_url` = '') AND `recoil` LIKE 'http%';
UPDATE `ero_files` SET `remote_url` = `embed` WHERE (`remote_url` IS NULL OR `remote_url` = '') AND `embed` LIKE 'http%';

-- 4. Tezkor indekslar (Instant Seek & Duplicate Prevention)
SET @idx_cd := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND INDEX_NAME = 'idx_category_date');
SET @sql_cd := IF(@idx_cd = 0, 'ALTER TABLE `ero_files` ADD INDEX `idx_category_date` (`category`, `date`)', 'SELECT 1');
PREPARE stmt_cd FROM @sql_cd;
EXECUTE stmt_cd;
DEALLOCATE PREPARE stmt_cd;

SET @idx_tr := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND INDEX_NAME = 'idx_translit');
SET @sql_tr := IF(@idx_tr = 0, 'ALTER TABLE `ero_files` ADD INDEX `idx_translit` (`translit`(191))', 'SELECT 1');
PREPARE stmt_tr FROM @sql_tr;
EXECUTE stmt_tr;
DEALLOCATE PREPARE stmt_tr;

SET @idx_uniq := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ero_files' AND INDEX_NAME = 'idx_uniqueness');
SET @sql_uniq := IF(@idx_uniq = 0, 'ALTER TABLE `ero_files` ADD INDEX `idx_uniqueness` (`uniqueness`(64))', 'SELECT 1');
PREPARE stmt_uniq FROM @sql_uniq;
EXECUTE stmt_uniq;
DEALLOCATE PREPARE stmt_uniq;
