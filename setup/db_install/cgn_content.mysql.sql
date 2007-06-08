-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.08.2007


DROP TABLE IF EXISTS `cgn_content`;
CREATE TABLE `cgn_content` (
		
	`cgn_content_id` integer (11) NOT NULL auto_increment, 
	`cgn_guid` varchar (255) NOT NULL, 
	`cgn_title` varchar (255) NOT NULL, 
	`cgn_type` varchar (255) NOT NULL, 
	`cgn_sub_type` varchar (255) NOT NULL, 
	`cgn_mime` varchar (255) NOT NULL, 
	`cgn_caption` varchar (255) NOT NULL, 
	`cgn_description` text NOT NULL, 
	`cgn_notes` text NOT NULL, 
	`cgn_content` text NOT NULL, 
	`cgn_binary` text NOT NULL, 
	`cgn_filename` varchar (255) NOT NULL, 
	`cgn_link_text` varchar (255) NOT NULL,
	PRIMARY KEY (cgn_content_id) 
);
