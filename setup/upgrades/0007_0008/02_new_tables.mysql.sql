CREATE TABLE `cgn_content_rel_type` (
    `cgn_content_rel_type_id` int(10) unsigned NOT NULL auto_increment,
    `rel_code` char(10) NOT NULL DEFAULT '',
    `display_name` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`cgn_content_rel_type_id`),
    INDEX `rel_code_idx` (`rel_code`)
) TYPE=MyISAM;

ALTER TABLE `cgn_content_rel`
    ADD `cgn_content_rel_type_id` int(10) unsigned NOT NULL DEFAULT '0' AFTER to_id;
