<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_blog_attrib`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_blog_attrib` (
	`cgn_blog_attrib_id` integer (11) NOT NULL auto_increment, 
	`cgn_blog_id` integer (11) unsigned NOT NULL default '0', 
	`code` varchar (30) NOT NULL default '', 
	`type` varchar (30) NOT NULL default '', 
	`value` varchar (255) NOT NULL default '', 
	`edited_on` integer (11) unsigned NOT NULL default 0,
	`created_on` integer (11) unsigned NOT NULL default 0,
	PRIMARY KEY (`cgn_blog_attrib_id`) 
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX edited_on_idx ON cgn_blog_attrib (`edited_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX created_on_idx ON cgn_blog_attrib (`created_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `cgn_blog_idx` ON cgn_blog_attrib (`cgn_blog_id`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
ALTER TABLE `cgn_blog_attrib` COLLATE utf8_general_ci;
sqldelimeter;
$installTableSchemas[] = $table;

?>