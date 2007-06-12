-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_article_publish`;
CREATE TABLE `cgn_article_publish` (
		
	`cgn_article_publish_id` integer (11) NOT NULL auto_increment, 
	`cgn_content_id` integer (11) NOT NULL, 
	`cgn_content_version` integer (11) NOT NULL, 
	`cgn_guid` varchar (255) NOT NULL, 
	`title` varchar (255) NOT NULL, 
	`type` varchar (255) NOT NULL, 
	`sub_type` varchar (255) NOT NULL, 
	`mime` varchar (255) NOT NULL, 
	`caption` varchar (255) NOT NULL, 
	`description` text NOT NULL, 
	`content` text NOT NULL, 
	`link_text` varchar (255) NOT NULL,
	PRIMARY KEY (cgn_article_publish_id) 
);
