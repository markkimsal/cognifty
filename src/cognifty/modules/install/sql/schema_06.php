<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_content`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_content` (
	`cgn_content_id` integer (11) NOT NULL auto_increment, 
	`cgn_guid` varchar (255) NOT NULL, 
	`title` varchar (255) NOT NULL, 
	`type` varchar (255) NOT NULL, 
	`sub_type` varchar (255) NOT NULL, 
	`mime` varchar (255) NOT NULL, 
	`caption` varchar (255) NOT NULL, 
	`description` text NOT NULL, 
	`notes` text NOT NULL, 
	`content` text default NULL, 
	`binary` longblob default NULL, 
	`filename` varchar (255) NOT NULL, 
	`link_text` varchar (255) NOT NULL,
	`version` integer (11) NOT NULL default 1,
	PRIMARY KEY (cgn_content_id) 
);
sqldelimeter;
$installTableSchemas[] = $table;

?>