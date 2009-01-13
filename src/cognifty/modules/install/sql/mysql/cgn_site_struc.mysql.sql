-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql


DROP TABLE IF EXISTS `cgn_site_struct`;
CREATE TABLE `cgn_site_struct` (
		
	`cgn_site_struct_id` integer (10) unsigned NOT NULL auto_increment, 
	`node_id` integer (10) unsigned NOT NULL default 0, 
	`parent_id` integer (10) unsigned NOT NULL default 0, 
	`node_kind` char    (10)  NOT NULL default 'web', 
	`title`     varchar (255) NOT NULL, 
	`link_text` varchar (255) NOT NULL, 
	PRIMARY KEY (`cgn_site_struct_id`) 
);

CREATE INDEX `node_idx`        ON `cgn_site_struct` (`node_id`);
CREATE INDEX `parent_idx`      ON `cgn_site_struct` (`parent_id`);
CREATE INDEX `node_kind_idx`   ON `cgn_site_struct` (`node_kind`);
CREATE INDEX `title_idx`       ON `cgn_site_struct` (`title`);
CREATE INDEX `link_text_idx`   ON `cgn_site_struct` (`link_text`);

ALTER TABLE `cgn_site_struct` COLLATE utf8_general_ci;
