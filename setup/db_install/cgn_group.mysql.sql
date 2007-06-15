-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_group`;
CREATE TABLE `cgn_group` (
		
	`cgn_group_id` int (11) NOT NULL auto_increment, 
	`code` varchar (255) NOT NULL, 
	`display_name` varchar (255) NOT NULL, 
	`active_on` int (11) NOT NULL, 
	`active_key` varchar (255) NOT NULL,
	PRIMARY KEY (cgn_group_id) 
);

CREATE INDEX code_idx ON cgn_group (code);
