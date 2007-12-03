<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_site_struc`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_site_struct` (
	`cgn_site_struct_id` integer (10) unsigned NOT NULL auto_increment, 
	`node_id` integer (10) unsigned NOT NULL default 0, 
	`parent_id` integer (10) unsigned NOT NULL default 0, 
	`node_kind` char    (10)  NOT NULL default 'web', 
	`title`     varchar (255) NOT NULL, 
	PRIMARY KEY (cgn_site_struct_id) 
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `node_idx`        ON cgn_site_struct (`node_id`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `parent_idx`      ON cgn_site_struct (`parent_id`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `node_kind_idx`   ON cgn_site_struct (`node_kind`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
ALTER TABLE `cgn_site_struct` COLLATE utf8_general_ci;
sqldelimeter;
$installTableSchemas[] = $table;

?>
