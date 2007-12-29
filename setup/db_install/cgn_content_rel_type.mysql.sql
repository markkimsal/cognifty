CREATE TABLE `cgn_content_rel_type` (
	`cgn_content_rel_type_id` int(10) unsigned NOT NULL auto_increment,
	`rel_code` char(10) NOT NULL default '',
	`display_name` varchar(255) NOT NULL default '',
	PRIMARY KEY (`cgn_content_rel_type_id`) 
) ENGINE=MyISAM DEFAULT CHARSET=latin1; 

CREATE INDEX `rel_code_idx` ON `cgn_content_rel_type` (`rel_code`);
ALTER TABLE `cgn_content_rel_type` COLLATE utf8_general_ci;

INSERT INTO `cgn_content_rel_type` (`rel_code`,`display_name`) VALUES ('embed', 'Displayed inside content');
INSERT INTO `cgn_content_rel_type` (`rel_code`,`display_name`) VALUES ('link', 'Linked from content');
INSERT INTO `cgn_content_rel_type` (`rel_code`,`display_name`) VALUES ('ref', 'Referenced in content');
INSERT INTO `cgn_content_rel_type` (`rel_code`,`display_name`) VALUES ('rel', 'Related content');
INSERT INTO `cgn_content_rel_type` (`rel_code`,`display_name`) VALUES ('sim', 'Similar content');
INSERT INTO `cgn_content_rel_type` (`rel_code`,`display_name`) VALUES ('rec', 'Recommended content');
