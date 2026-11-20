-- 1.1.0: εξαίρεση ιδιοκτησίας από τις απώλειες θέρμανσης
ALTER TABLE `#__kx_units` ADD COLUMN `heat_excluded` TINYINT NOT NULL DEFAULT 0 AFTER `heat_coef`;
