-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_user`;
CREATE TABLE `cgn_user` (
		
	`cgn_user_id` integer (11) unsigned NOT NULL auto_increment, 
	`username` varchar (255) NOT NULL, 
	`email` varchar (255) NOT NULL, 
	`password` varchar (255) NOT NULL, 
	`active_on` integer (11) NOT NULL default 0, 
	`active_key` varchar (255) NOT NULL,
	PRIMARY KEY (cgn_user_id) 
);

CREATE INDEX email_idx ON cgn_user (email);
CREATE INDEX active_on_idx ON cgn_user (active_on);
CREATE INDEX active_key_idx ON cgn_user (active_key);
CREATE INDEX username_idx ON cgn_user (username);

ALTER TABLE `cgn_user` COLLATE utf8_collate_ci;
