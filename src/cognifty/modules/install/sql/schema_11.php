<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_article_section`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_article_section` (
	`cgn_article_section_id` integer (11) NOT NULL auto_increment, 
	`title` varchar (255) NOT NULL, 
	`link_text` varchar (255) NOT NULL, 
	PRIMARY KEY (cgn_article_section_id) 
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX link_text_idx ON `cgn_article_section` (`link_text`);
sqldelimeter;
$installTableSchemas[] = $table;

?>