<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_content_tag_link`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_content_tag_link` (
	`cgn_content_tag_id` integer (11) NOT NULL, 
	`cgn_content_id` integer (11) NOT NULL
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX cgn_content_tag_idx ON cgn_content_tag_link (`cgn_content_tag_id`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX cgn_content_idx ON cgn_content_tag_link (`cgn_content_id`);
sqldelimeter;
$installTableSchemas[] = $table;

?>