<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_web_publish`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_web_publish` (
	`cgn_web_publish_id` integer (11) NOT NULL auto_increment, 
	`cgn_content_id` integer (11) NOT NULL default '0', 
	`cgn_content_version` integer (11) NOT NULL default '1', 
	`cgn_guid` varchar (255) NOT NULL default '', 
	`title` varchar (255) NOT NULL default '', 
	`mime` varchar (255) NOT NULL default '', 
	`caption` varchar (255) NOT NULL default '', 
	`description` text NOT NULL default '', 
	`content` text NOT NULL default '', 
	`link_text` varchar (255) NOT NULL default '',
	`published_on` integer (11) NOT NULL default 0,
	`edited_on` integer (11) NOT NULL default 0,
	`created_on` integer (11) NOT NULL default 0,
	`is_home` tinyint (2) NULL default NULL,
	`is_portal` tinyint (2) NULL default NULL,
	PRIMARY KEY (cgn_web_publish_id) 
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `edited_on_idx` ON cgn_web_publish (`edited_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `published_on_idx` ON cgn_web_publish (`published_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `created_on_idx` ON cgn_web_publish (`created_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `link_text_idx` ON cgn_web_publish (`link_text`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `cgn_content_idx` ON cgn_web_publish (`cgn_content_id`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `is_home_idx` ON cgn_web_publish (`is_home`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
ALTER TABLE `cgn_web_publish` COLLATE utf8_general_ci;
sqldelimeter;
$installTableSchemas[] = $table;

?>