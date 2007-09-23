-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_metadata`;
CREATE TABLE `cgn_metadata` (
		
	`cgn_metadata_id` integer (11) NOT NULL auto_increment, 
	`cgn_content_publish_id` integer (11) NOT NULL, 
	`author` varchar (255) NOT NULL, 
	`copyright` varchar (255) NOT NULL, 
	`license` varchar (255) NOT NULL, 
	`version` varchar (255) NOT NULL, 
	`status` varchar (255) NOT NULL, 
	`updated_on` integer (11) NOT NULL default 0, 
	`created_on` integer (11) NOT NULL default 0,
	PRIMARY KEY (cgn_metadata_id) 
);

CREATE INDEX cgn_content_publish_idx ON cgn_metadata (cgn_content_publish_id);

ALTER TABLE `cgn_metadata` COLLATE utf8_collate_ci;
