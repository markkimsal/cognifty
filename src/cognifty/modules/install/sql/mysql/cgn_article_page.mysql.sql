-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_article_page`;
CREATE TABLE `cgn_article_page` (
		
	`cgn_article_page_id` integer (11) NOT NULL auto_increment, 
	`cgn_article_publish_id` integer (11) NOT NULL default 0, 
	`title` varchar (255) NOT NULL, 
	`content` text NOT NULL, 
	PRIMARY KEY (cgn_article_page_id) 
);

CREATE INDEX cgn_article_publish_idx ON `cgn_article_page` (`cgn_article_publish_id`);
ALTER TABLE `cgn_article_page` COLLATE utf8_general_ci;
