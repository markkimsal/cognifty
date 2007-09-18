-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_web`;
CREATE TABLE `cgn_web` (
		
	`cgn_web_id` integer (10) unsigned NOT NULL auto_increment, 
	`cgn_content_id` integer (10) unsigned NOT NULL, 
	`title`     varchar (255) NOT NULL, 
	`is_portal` tinyint (2) NOT NULL default 0, 
	`is_home`   tinyint (2) NULL default NULL, 
	`site_area_id` integer (10) unsigned NOT NULL, 
	PRIMARY KEY (cgn_web_id) 
);

CREATE INDEX `is_home_idx`      ON cgn_web (`is_home`);
CREATE INDEX `is_portal_idx`    ON cgn_web (`is_portal`);
CREATE INDEX `cgn_content_idx`  ON cgn_web (`cgn_content_id`);
CREATE INDEX `site_area_idx`    ON cgn_web (`site_area_id`);
