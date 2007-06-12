-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.11.2007


DROP TABLE IF EXISTS `cgn_metadata_version`;
CREATE TABLE `cgn_metadata_version` (
		
	`cgn_metadata_version_id` integer (11) NOT NULL auto_increment, 
	`cgn_content_version_id` integer (11) NOT NULL, 
	`cgn_kind` varchar (255) NOT NULL, 
	`author` varchar (255) NOT NULL, 
	`copyright` varchar (255) NOT NULL, 
	`license` varchar (255) NOT NULL, 
	`version` varchar (255) NOT NULL, 
	`status` varchar (255) NOT NULL, 
	`updated_on` integer (11) NOT NULL, 
	`created_on` integer (11) NOT NULL,
	PRIMARY KEY (cgn_metadata_version_id) 
);

CREATE INDEX cgn_content_version_idx ON cgn_metadata_version (cgn_content_version_id);
CREATE INDEX cgn_kind_idx ON cgn_metadata_version (cgn_kind);

