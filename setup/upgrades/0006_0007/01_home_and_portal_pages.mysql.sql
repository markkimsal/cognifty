ALTER TABLE `cgn_web_publish` ADD COLUMN `is_home` tinyint (2) NULL default NULL;
ALTER TABLE `cgn_web_publish` ADD COLUMN `is_portal` tinyint (2) NULL default NULL;
CREATE INDEX `is_home_idx` ON `cgn_web_publish` (`is_home`);
