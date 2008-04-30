DROP TABLE IF EXISTS `cgn_account_address`;
CREATE TABLE `cgn_account_address` (

	`cgn_account_address_id` integer (11) unsigned NOT NULL auto_increment, 
	`cgn_account_id` integer (11) unsigned NOT NULL default '0', 
	`created_on` integer (11) unsigned NOT NULL default '0',
	`edited_on` integer (11) unsigned NOT NULL default '0',
	`ref_id` varchar (100) NULL,
	`ref_no` integer (11) unsigned NULL,
	`address_type` varchar (10) NOT NULL default '', 
	`telephone` varchar (30) NOT NULL default '',
	`fax` varchar (30) NOT NULL default '',
	`street` varchar (100) NOT NULL default '',
	`additional` varchar (100) NULL,
	`city` varchar (25) NOT NULL default '',
	`region` varchar (25) NOT NULL default '',
	`country` varchar (25) NOT NULL default '',
	`post_code` varchar (11) NOT NULL default '',
	PRIMARY KEY (`cgn_account_address_id`) 
);

CREATE INDEX `cgn_account_idx` ON `cgn_account_address` (`cgn_account_id`);
ALTER TABLE `cgn_account_address` COLLATE utf8_general_ci;
