-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_site_area`;
CREATE TABLE `cgn_site_area` (
		
	`cgn_site_area_id` integer (11) NOT NULL auto_increment, 
	`title` varchar (255) NOT NULL, 
	`description` text NULL, 
	`site_template` varchar (25) NOT NULL default 0,
	`template_style` varchar (25) NOT NULL default 0,
	`cgn_def_menu_id` integer (11) NOT NULL default 0,
	`edited_on` integer (11) NOT NULL default 0,
	`created_on` integer (11) NOT NULL default 0,
	`owner_id` integer (11) NOT NULL default 0,
	`is_default` tinyint (4) NOT NULL default 0,
	PRIMARY KEY (cgn_site_area_id) 
);

CREATE INDEX `edited_on_idx` ON `cgn_site_area` (`edited_on`);
CREATE INDEX `published_on_idx` ON `cgn_site_area` (`edited_on`);
CREATE INDEX `created_on_idx` ON `cgn_site_area` (`created_on`);
CREATE INDEX `owner_idx` ON `cgn_site_area` (`owner_id`);
CREATE INDEX `is_default_idx` ON `cgn_site_area` (`is_default`);
ALTER TABLE `cgn_site_area` COLLATE utf8_general_ci;
