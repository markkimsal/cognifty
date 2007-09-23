-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.14.2007


DROP TABLE IF EXISTS `cgn_menu_item`;
CREATE TABLE `cgn_menu_item` (
		
	`cgn_menu_item_id` integer (11) NOT NULL auto_increment, 
	`cgn_menu_id` integer (11) NOT NULL, 
	`parent_id` integer (11) NULL, 
	`title` varchar (255) NOT NULL, 
	`code_name` varchar (32) NOT NULL, 
	`url` varchar (255) NOT NULL, 
	`type` varchar (10) NOT NULL, 
	`obj_id` int (11) NOT NULL, 
	`edited_on` integer (11) NOT NULL default 0,
	`created_on` integer (11) NOT NULL default 0,
	`rank` tinyint (4) unsigned NOT NULL default 0,
	PRIMARY KEY (cgn_menu_item_id) 
);

CREATE INDEX edited_on_idx ON cgn_menu_item (`edited_on`);
CREATE INDEX created_on_idx ON cgn_menu_item (`created_on`);
CREATE INDEX cgn_menu_idx ON cgn_menu_item (`cgn_menu_id`);
CREATE INDEX parent_idx ON cgn_menu_item (`parent_id`);
CREATE INDEX obj_idx ON cgn_menu_item (`obj_id`);
ALTER TABLE `cgn_menu_item` COLLATE utf8_collate_ci;
