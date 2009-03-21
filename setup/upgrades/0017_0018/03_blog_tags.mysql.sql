-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.14.2007


DROP TABLE IF EXISTS `cgn_blog_entry_tag`;
CREATE TABLE `cgn_blog_entry_tag` (
	`cgn_blog_entry_tag_id` integer (11) NOT NULL auto_increment, 
	`name`      varchar (255) NOT NULL, 
	`link_text` varchar (255) NOT NULL, 
	PRIMARY KEY (cgn_blog_entry_tag_id) 
);

CREATE INDEX name_idx ON cgn_blog_entry_tag (`name`);
CREATE INDEX link_text_idx ON cgn_blog_entry_tag (`link_text`);
ALTER TABLE `cgn_blog_entry_tag` COLLATE utf8_general_ci;
-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.14.2007


DROP TABLE IF EXISTS `cgn_blog_entry_tag_link`;
CREATE TABLE `cgn_blog_entry_tag_link` (
		
	`cgn_blog_entry_tag_id` integer (11) NOT NULL, 
	`cgn_blog_entry_id` integer (11) NOT NULL,
	`created_on` integer (11) NOT NULL,
	`tag_type` varchar (55) NOT NULL default ''
);

CREATE INDEX cgn_blog_entry_tag_idx ON `cgn_blog_entry_tag_link` (`cgn_blog_entry_tag_id`);
CREATE INDEX cgn_blog_entry_idx ON `cgn_blog_entry_tag_link` (`cgn_blog_entry_id`);
CREATE INDEX tag_type_idx ON `cgn_blog_entry_tag_link` (`tag_type`);
