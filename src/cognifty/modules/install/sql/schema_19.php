<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_obj_trash`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_obj_trash` (
	`cgn_obj_trash_id` int (11) NOT NULL auto_increment, 
	`table` varchar (255) NOT NULL, 
	`content` longtext NOT NULL, 
	`user_id` integer (11) NOT NULL, 
	`deleted_on` integer (11) NOT NULL, 
	PRIMARY KEY (cgn_obj_trash_id) 
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX user_idx ON cgn_obj_trash (user_id)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX deleted_on_idx ON cgn_obj_trash (deleted_on)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
ALTER TABLE `cgn_obj_trash` COLLATE utf8_general_ci;
sqldelimeter;
$installTableSchemas[] = $table;

?>