-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_blog_comment`;
CREATE TABLE `cgn_blog_comment` (
		
	`cgn_blog_comment_id` integer (11) NOT NULL auto_increment, 
	`cgn_blog_entry_publish_id` integer (11) NOT NULL default '0', 
	`user_id` integer (11) NOT NULL default '0', 
	`user_ip_addr` varchar (39) NOT NULL default '', 
	`user_email` varchar (255) NOT NULL default '', 
	`user_name` varchar (255) NOT NULL default '', 
	`user_url` varchar (255) NOT NULL default '', 
	`spam_rating` tinyint (1) NOT NULL default '0', 
	`approved` tinyint (1) unsigned NOT NULL default '0',
	`tag` varchar (32) NULL, 
	`rating` tinyint (2) NULL,
	`content` text NOT NULL default '', 
	`posted_on` integer (11) NOT NULL default 0,
	PRIMARY KEY (`cgn_blog_comment_id`) 
);

CREATE INDEX `posted_on_idx` ON `cgn_blog_comment` (`posted_on`);
CREATE INDEX `cgn_blog_idx` ON `cgn_blog_comment` (`cgn_blog_entry_publish_id`);
ALTER TABLE `cgn_blog_comment` COLLATE utf8_general_ci;
