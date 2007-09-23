-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 07.19.2007


DROP TABLE IF EXISTS `cgn_obj_trash`;
CREATE TABLE `cgn_obj_trash` (
		
	`cgn_obj_trash_id` int (11) NOT NULL auto_increment, 
	`table` varchar (255) NOT NULL, 
	`content` longtext NOT NULL, 
	`user_id` integer (11) NOT NULL, 
	`deleted_on` integer (11) NOT NULL, 
	PRIMARY KEY (cgn_obj_trash_id) 
);

CREATE INDEX user_idx ON cgn_obj_trash (user_id);
CREATE INDEX deleted_on_idx ON cgn_obj_trash (deleted_on);

ALTER TABLE `cgn_obj_trash` COLLATE utf8_collate_ci;
