DROP TABLE IF EXISTS `cgn_content_attrib`;
CREATE TABLE `cgn_content_attrib` (
	`cgn_content_attrib_id` integer (11) NOT NULL auto_increment, 
	`cgn_content_id` integer (11) unsigned NOT NULL default '0', 
	`code` varchar (30) NOT NULL default '', 
	`type` varchar (30) NOT NULL default '', 
	`value` varchar (255) NOT NULL default '', 
	`edited_on` integer (11) unsigned NOT NULL default 0,
	`created_on` integer (11) unsigned NOT NULL default 0,
	PRIMARY KEY (`cgn_content_attrib_id`) 
);

CREATE INDEX edited_on_idx ON cgn_content_attrib (`edited_on`);
CREATE INDEX created_on_idx ON cgn_content_attrib (`created_on`);
CREATE INDEX `cgn_content_idx` ON cgn_content_attrib (`cgn_content_id`);
ALTER TABLE `cgn_content_attrib` COLLATE utf8_general_ci;
