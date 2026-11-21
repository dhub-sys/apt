-- 1.8.0: ομάδες ιδιοκτησιών
CREATE TABLE IF NOT EXISTS `#__kx_groups` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `building_id` INT NOT NULL,
  `title` VARCHAR(100) NOT NULL DEFAULT '',
  `unit_ids` TEXT NULL,
  `ordering` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_building` (`building_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE `#__kx_expenses` ADD COLUMN `group_id` INT NOT NULL DEFAULT 0 AFTER `key_id`;
