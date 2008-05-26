<?
$installTableSchemas = array();
$table = <<<sqldelimeter
CREATE TABLE `cgn_content_rel` (
	  `from_id` int(10) unsigned NOT NULL default '0',
	  `to_id` int(10) unsigned NOT NULL default '0',
	  `cgn_content_rel_type_id` int(10) unsigned NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `from_idx` ON `cgn_content_rel` (`from_id`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `to_idx` ON `cgn_content_rel` (`to_id`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
ALTER TABLE `cgn_content_rel` COLLATE utf8_general_ci;
sqldelimeter;
$installTableSchemas[] = $table;

?>