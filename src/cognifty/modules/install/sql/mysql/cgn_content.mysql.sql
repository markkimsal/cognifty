-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.14.2007


DROP TABLE IF EXISTS `cgn_content`;
CREATE TABLE `cgn_content` (
		
	`cgn_content_id` integer (11) NOT NULL auto_increment, 
	`cgn_guid` varchar (255) NOT NULL, 
	`title` varchar (255) NOT NULL, 
	`type` varchar (255) NOT NULL, 
	`sub_type` varchar (255) NOT NULL, 
	`mime` varchar (255) NOT NULL, 
	`caption` varchar (255) NOT NULL, 
	`description` text NOT NULL, 
	`notes` text NOT NULL, 
	`content` text default NULL, 
	`binary` longblob default NULL, 
	`filename` varchar (255) NOT NULL, 
	`link_text` varchar (255) NOT NULL,
	`version` integer (11) NOT NULL default 1,
	`published_on` integer (11) NOT NULL default 0,
	`edited_on` integer (11) NOT NULL default 0,
	`created_on` integer (11) NOT NULL default 0,
	PRIMARY KEY (cgn_content_id) 
);

CREATE INDEX edited_on_idx ON cgn_content (`edited_on`);
CREATE INDEX published_on_idx ON cgn_content (`published_on`);
CREATE INDEX created_on_idx ON cgn_content (`created_on`);
CREATE INDEX sub_type_idx ON cgn_content (`sub_type`);
ALTER TABLE `cgn_content` COLLATE utf8_general_ci;
