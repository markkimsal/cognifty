DROP TABLE IF EXISTS `cgn_account`;
CREATE TABLE `cgn_account` (
		
	`cgn_account_id` integer (11) unsigned NOT NULL auto_increment, 
	`user_id` integer (11) unsigned NOT NULL, 
	`firstname` varchar (100) NOT NULL default '',
	`lastname` varchar (100) NOT NULL default '',
	`title` varchar (12) NOT NULL default '',
	`birthDate` integer (11) NOT NULL default '0',
	PRIMARY KEY (`cgn_account_id`) 
);

CREATE INDEX `user_idx` ON `cgn_account` (`user_id`);
ALTER TABLE `cgn_account` COLLATE utf8_general_ci;
