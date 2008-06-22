-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_blog`;
CREATE TABLE `cgn_blog` (
		
	`cgn_blog_id` integer (11) NOT NULL auto_increment, 
	`name` varchar (255) NOT NULL, 
	`title` varchar (255) NOT NULL, 
	`caption` varchar (255) NOT NULL, 
	`description` text NOT NULL, 
	`edited_on` integer (11) NOT NULL default 0,
	`created_on` integer (11) NOT NULL default 0,
	`owner_id` integer (11) NOT NULL default 0,
	`is_default` tinyint (4) NOT NULL default 0,
	PRIMARY KEY (cgn_blog_id) 
);

CREATE INDEX `edited_on_idx` ON `cgn_blog` (`edited_on`);
CREATE INDEX `created_on_idx` ON `cgn_blog` (`created_on`);
CREATE INDEX `owner_idx` ON `cgn_blog` (`owner_id`);
CREATE INDEX `is_default_idx` ON `cgn_blog` (`is_default`);
ALTER TABLE `cgn_blog` COLLATE utf8_general_ci;
