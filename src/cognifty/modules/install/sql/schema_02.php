<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_metadata_publish`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_metadata_publish` (
	`cgn_metadata_publish_id` integer (11) NOT NULL auto_increment, 
	`cgn_content_id` integer (11) NOT NULL, 
	`author` varchar (255) NOT NULL, 
	`copyright` varchar (255) NOT NULL, 
	`license` varchar (255) NOT NULL, 
	`version` varchar (255) NOT NULL, 
	`status` varchar (255) NOT NULL, 
	`updated_on` integer (11) NOT NULL default 0, 
	`created_on` integer (11) NOT NULL default 0,
	PRIMARY KEY (cgn_metadata_publish_id) 
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX cgn_content_idx ON cgn_metadata_publish (cgn_content_id);
sqldelimeter;
$installTableSchemas[] = $table;

?>