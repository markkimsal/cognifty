-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_web_publish`;
CREATE TABLE `cgn_web_publish` (
		
	`cgn_web_publish_id` integer (11) NOT NULL auto_increment, 
	`cgn_content_id` integer (11) NOT NULL default '0', 
	`cgn_content_version` integer (11) NOT NULL default '1', 
	`cgn_guid` varchar (255) NOT NULL default '', 
	`title` varchar (255) NOT NULL default '', 
	`mime` varchar (255) NOT NULL default '', 
	`caption` varchar (255) NOT NULL default '', 
	`description` text NOT NULL default '', 
	`content` text NOT NULL default '', 
	`link_text` varchar (255) NOT NULL default '',
	`published_on` integer (11) NOT NULL default 0,
	`edited_on` integer (11) NOT NULL default 0,
	`created_on` integer (11) NOT NULL default 0,
	`is_home` tinyint (2) NULL default NULL,
	`is_portal` tinyint (2) NULL default NULL,
	PRIMARY KEY (cgn_web_publish_id) 
);

CREATE INDEX `edited_on_idx` ON cgn_web_publish (`edited_on`);
CREATE INDEX `published_on_idx` ON cgn_web_publish (`published_on`);
CREATE INDEX `created_on_idx` ON cgn_web_publish (`created_on`);
CREATE INDEX `link_text_idx` ON cgn_web_publish (`link_text`);
CREATE INDEX `cgn_content_idx` ON cgn_web_publish (`cgn_content_id`);
CREATE INDEX `is_home_idx` ON cgn_web_publish (`is_home`);
ALTER TABLE `cgn_web_publish` COLLATE utf8_general_ci;
