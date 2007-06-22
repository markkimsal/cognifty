<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_article_section_link`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_article_section_link` (
	`cgn_article_section_id` int (11) NOT NULL, 
	`cgn_article_publish_id` int (11) NOT NULL, 
	`active_on` int (11) NOT NULL
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX cgn_article_section_idx ON cgn_article_section_link (`cgn_article_section_id`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX cgn_article_publish_idx ON cgn_article_section_link (`cgn_article_publish_id`);
sqldelimeter;
$installTableSchemas[] = $table;

?>