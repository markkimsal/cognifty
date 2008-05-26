<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_log_visitor`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_log_visitor` (
	`cgn_log_visitor_id` int (11) NOT NULL auto_increment, 
	`ip_addr` char (16) NOT NULL default '', 
	`user_id` integer (11) unsigned NULL,
	`recorded_on` integer (11) unsigned NOT NULL default '0', 
	`session_id` char (32) NOT NULL default '', 
	`url` varchar (255) NOT NULL default '', 
	PRIMARY KEY (cgn_log_visitor_id) 
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `user_idx` ON cgn_log_visitor (`user_id`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `recorded_on_idx` ON cgn_log_visitor (`recorded_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
ALTER TABLE `cgn_log_visitor` COLLATE utf8_general_ci;
sqldelimeter;
$installTableSchemas[] = $table;

?>