-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_web_publish`;
CREATE TABLE `cgn_web_publish` (
		
	`cgn_web_publish_id` integer (11) NOT NULL auto_increment, 
	`cgn_content_id` integer (11) NOT NULL, 
	`cgn_content_version` integer (11) NOT NULL, 
	`cgn_guid` varchar (255) NOT NULL, 
	`title` varchar (255) NOT NULL, 
	`mime` varchar (255) NOT NULL, 
	`caption` varchar (255) NOT NULL, 
	`description` text NOT NULL, 
	`content` text NOT NULL, 
	`link_text` varchar (255) NOT NULL,
	`published_on` integer (11) NOT NULL default 0,
	`edited_on` integer (11) NOT NULL default 0,
	`created_on` integer (11) NOT NULL default 0,
	PRIMARY KEY (cgn_web_publish_id) 
);

CREATE INDEX edited_on_idx ON cgn_web_publish (`edited_on`);
CREATE INDEX published_on_idx ON cgn_web_publish (`edited_on`);
CREATE INDEX created_on_idx ON cgn_web_publish (`created_on`);
CREATE INDEX link_text_idx ON cgn_web_publish (`link_text`);
