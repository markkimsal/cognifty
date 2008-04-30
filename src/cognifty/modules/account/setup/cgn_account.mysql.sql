DROP TABLE IF EXISTS `cgn_account`;
CREATE TABLE `cgn_account` (

	`cgn_account_id` integer (11) unsigned NOT NULL auto_increment, 
	`cgn_user_id` integer (11) unsigned NOT NULL, 
	`firstname` varchar (100) NOT NULL default '',
	`lastname` varchar (100) NOT NULL default '',
	`title` varchar (12) NOT NULL default '',
	`birth_date` integer (11) NOT NULL default '0',
	`ref_id` varchar (50) NOT NULL default '',
	`ref_no` integer (11) NOT NULL default '0',
	PRIMARY KEY (`cgn_account_id`) 
);

CREATE INDEX `cgn_user_idx` ON `cgn_account` (`cgn_user_id`);
ALTER TABLE `cgn_account` COLLATE utf8_general_ci;
