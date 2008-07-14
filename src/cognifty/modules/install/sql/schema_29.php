<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_content_rel_type`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_content_rel_type` (
	`cgn_content_rel_type_id` int(10) unsigned NOT NULL auto_increment,
	`rel_code` char(10) NOT NULL default '',
	`display_name` varchar(255) NOT NULL default '',
	PRIMARY KEY (`cgn_content_rel_type_id`) 
) ENGINE=MyISAM DEFAULT CHARSET=latin1
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `rel_code_idx` ON `cgn_content_rel_type` (`rel_code`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
ALTER TABLE `cgn_content_rel_type` COLLATE utf8_general_ci
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
INSERT INTO `cgn_content_rel_type` (`rel_code`,`display_name`) VALUES ('embed', 'Displayed inside content')
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
INSERT INTO `cgn_content_rel_type` (`rel_code`,`display_name`) VALUES ('link', 'Linked from content')
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
INSERT INTO `cgn_content_rel_type` (`rel_code`,`display_name`) VALUES ('ref', 'Referenced in content')
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
INSERT INTO `cgn_content_rel_type` (`rel_code`,`display_name`) VALUES ('rel', 'Related content')
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
INSERT INTO `cgn_content_rel_type` (`rel_code`,`display_name`) VALUES ('sim', 'Similar content')
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
INSERT INTO `cgn_content_rel_type` (`rel_code`,`display_name`) VALUES ('rec', 'Recommended content');
sqldelimeter;
$installTableSchemas[] = $table;

?>