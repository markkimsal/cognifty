<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_blog_entry_publish`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_blog_entry_publish` (
	`cgn_blog_entry_publish_id` integer (11) NOT NULL auto_increment, 
	`cgn_content_id` integer (11) NOT NULL, 
	`cgn_content_version` integer (11) NOT NULL, 
	`cgn_blog_id` integer (11) NOT NULL, 
	`title` varchar (255) NOT NULL, 
	`caption` varchar (255) NOT NULL, 
	`content` text NOT NULL, 
	`link_text` varchar (255) NOT NULL,
	`posted_on` integer (11) NOT NULL default 0,
	`edited_on` integer (11) NOT NULL default 0,
	PRIMARY KEY (`cgn_blog_entry_publish_id`) 
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `edited_on_idx` ON `cgn_blog_entry_publish` (`edited_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `posted_on_idx` ON `cgn_blog_entry_publish` (`posted_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `cgn_blog_idx` ON `cgn_blog_entry_publish` (`cgn_blog_id`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `cgn_content_idx` ON `cgn_blog_entry_publish` (`cgn_content_id`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
ALTER TABLE `cgn_blog_entry_publish` COLLATE utf8_general_ci;
sqldelimeter;
$installTableSchemas[] = $table;

?>
