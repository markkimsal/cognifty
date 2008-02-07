<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_blog_comment`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_blog_comment` (
	`cgn_blog_comment_id` integer (11) NOT NULL auto_increment, 
	`cgn_blog_entry_publish_id` integer (11) NOT NULL default '0', 
	`user_id` integer (11) NOT NULL default '0', 
	`user_ip_addr` varchar (39) NOT NULL default '', 
	`user_email` varchar (255) NOT NULL default '', 
	`user_name` varchar (255) NOT NULL default '', 
	`user_url` varchar (255) NOT NULL default '', 
	`spam_rating` tinyint (1) NOT NULL default '0', 
	`approved` tinyint (1) unsigned NOT NULL default '0',
	`tag` varchar (32) NULL, 
	`source` char (10) NOT NULL default 'comment', 
	`rating` tinyint (2) NULL,
	`content` text NOT NULL default '', 
	`posted_on` integer (11) NOT NULL default 0,
	PRIMARY KEY (`cgn_blog_comment_id`) 
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `posted_on_idx` ON `cgn_blog_comment` (`posted_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX `cgn_blog_idx` ON `cgn_blog_comment` (`cgn_blog_entry_publish_id`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
ALTER TABLE `cgn_blog_comment` COLLATE utf8_general_ci;
sqldelimeter;
$installTableSchemas[] = $table;

?>