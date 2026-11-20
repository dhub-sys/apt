-- 1.4.0: τύπος αυτονομίας με συντελεστή απωλειών fi ανά διαμέρισμα, εμφάνιση παγίου
ALTER TABLE `#__kx_units` ADD COLUMN `heat_fi` DECIMAL(8,4) NULL DEFAULT NULL AFTER `heat_coef`;
ALTER TABLE `#__kx_buildings` ADD COLUMN `heating_fixed_show` VARCHAR(10) NOT NULL DEFAULT 'heating' AFTER `heating_fixed_pct`;
ALTER TABLE `#__kx_buildings` MODIFY `heating_fixed_pct` DECIMAL(6,3) NOT NULL DEFAULT 30;
