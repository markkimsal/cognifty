<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_blog`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_blog` (
	`cgn_blog_id` integer (11) NOT NULL auto_increment, 
	`name` varchar (255) NOT NULL, 
	`title` varchar (255) NOT NULL, 
	`caption` varchar (255) NOT NULL, 
	`description` text NOT NULL, 
	`edited_on` integer (11) NOT NULL default 0,
	`created_on` integer (11) NOT NULL default 0,
	`owner_id` integer (11) NOT NULL default 0,
	`is_default` tinyint (4) NOT NULL default 0,
	PRIMARY KEY (cgn_blog_id) 
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `edited_on_idx` ON `cgn_blog` (`edited_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `published_on_idx` ON `cgn_blog` (`edited_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `created_on_idx` ON `cgn_blog` (`created_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `owner_idx` ON `cgn_blog` (`owner_id`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `is_default_idx` ON `cgn_blog` (`is_default`);
sqldelimeter;
$installTableSchemas[] = $table;

?>