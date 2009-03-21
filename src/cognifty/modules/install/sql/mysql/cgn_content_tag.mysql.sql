-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.14.2007


DROP TABLE IF EXISTS `cgn_content_tag`;
CREATE TABLE `cgn_content_tag` (
		
	`cgn_content_tag_id` integer (11) NOT NULL auto_increment, 
	`name`      varchar (255) NOT NULL, 
	`link_text` varchar (255) NOT NULL, 
	PRIMARY KEY (cgn_content_tag_id) 
);

CREATE INDEX name_idx ON cgn_content_tag (`name`);
CREATE INDEX link_text_idx ON cgn_content_tag (`link_text`);
ALTER TABLE `cgn_content_tag` COLLATE utf8_general_ci;
