<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_group`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_group` (
	`cgn_group_id` int (11) NOT NULL auto_increment, 
	`code` varchar (255) NOT NULL, 
	`display_name` varchar (255) NOT NULL, 
	`active_on` int (11) NOT NULL, 
	`active_key` varchar (255) NOT NULL,
	PRIMARY KEY (cgn_group_id) 
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX code_idx ON cgn_group (code);
sqldelimeter;
$installTableSchemas[] = $table;

?>