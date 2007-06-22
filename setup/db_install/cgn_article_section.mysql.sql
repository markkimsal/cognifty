-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_article_section`;
CREATE TABLE `cgn_article_section` (
		
	`cgn_article_section_id` integer (11) NOT NULL auto_increment, 
	`title` varchar (255) NOT NULL, 
	`link_text` varchar (255) NOT NULL, 
	PRIMARY KEY (cgn_article_section_id) 
);

CREATE INDEX link_text_idx ON `cgn_article_section` (`link_text`);
