<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_menu`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_menu` (
	`cgn_menu_id` integer (11) NOT NULL auto_increment, 
	`title` varchar (255) NOT NULL, 
	`code_name` varchar (255) NOT NULL, 
	`edited_on` integer (11) NOT NULL default 1,
	`created_on` integer (11) NOT NULL default 1,
	PRIMARY KEY (cgn_menu_id) 
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX code_name_idx ON cgn_menu (`code_name`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX edited_on_idx ON cgn_menu (`edited_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX created_on_idx ON cgn_menu (`edited_on`);
sqldelimeter;
$installTableSchemas[] = $table;

?>