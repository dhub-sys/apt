-- 1.5.0: συντελεστής κατανάλωσης ei (χιλιοστά θέρμανσης ή ξεχωριστός ανά διαμέρισμα)
ALTER TABLE `#__kx_buildings` ADD COLUMN `heating_ei` VARCHAR(10) NOT NULL DEFAULT 'coef' AFTER `heating_fixed_show`;
