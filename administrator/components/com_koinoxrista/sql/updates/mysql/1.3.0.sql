-- 1.3.0: λόγος εξαίρεσης από τη θέρμανση
ALTER TABLE `#__kx_units` ADD COLUMN `heat_excluded_reason` VARCHAR(255) NOT NULL DEFAULT '' AFTER `heat_excluded`;
