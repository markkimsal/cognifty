<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_content_tag`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_content_tag` (
	`cgn_content_tag_id` integer (11) NOT NULL auto_increment, 
	`name` varchar (255) NOT NULL, 
	PRIMARY KEY (cgn_content_tag_id) 
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX name_idx ON cgn_content_tag (`name`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
ALTER TABLE `cgn_content_tag` COLLATE utf8_general_ci;
sqldelimeter;
$installTableSchemas[] = $table;

?>
