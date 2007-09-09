-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_blog_entry_publish`;
CREATE TABLE `cgn_blog_entry_publish` (
		
	`cgn_blog_entry_publish_id` integer (11) NOT NULL auto_increment, 
	`cgn_content_id` integer (11) NOT NULL, 
	`cgn_content_version` integer (11) NOT NULL, 
	`cgn_blog_id` integer (11) NOT NULL, 
	`title` varchar (255) NOT NULL, 
	`caption` varchar (255) NOT NULL, 
	`content` text NOT NULL, 
	`link_text` varchar (255) NOT NULL,
	`published_on` integer (11) NOT NULL default 0,
	`edited_on` integer (11) NOT NULL default 0,
	`created_on` integer (11) NOT NULL default 0,
	PRIMARY KEY (`cgn_blog_entry_publish_id`) 
);

CREATE INDEX `edited_on_idx` ON `cgn_blog_entry_publish` (`edited_on`);
CREATE INDEX `published_on_idx` ON `cgn_blog_entry_publish` (`edited_on`);
CREATE INDEX `created_on_idx` ON `cgn_blog_entry_publish` (`created_on`);
CREATE INDEX `cgn_blog_idx` ON `cgn_blog_entry_publish` (`cgn_blog_id`);
CREATE INDEX `cgn_content_idx` ON `cgn_blog_entry_publish` (`cgn_content_id`);
