<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_file_publish`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_file_publish` (
  `cgn_file_publish_id` int(11) NOT NULL auto_increment,
  `cgn_content_id` int(11) NOT NULL,
  `cgn_content_version` int(11) NOT NULL,
  `cgn_guid` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `mime` varchar(255) NOT NULL,
  `caption` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `binary` longblob NOT NULL,
  `link_text` varchar(255) NOT NULL,
  `published_on` integer (11) NOT NULL default 0,
  `edited_on` integer (11) NOT NULL default 0,
  `created_on` integer (11) NOT NULL default 0,
  PRIMARY KEY  (`cgn_file_publish_id`)
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX edited_on_idx ON cgn_file_publish (`edited_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX published_on_idx ON cgn_file_publish (`published_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX created_on_idx ON cgn_file_publish (`created_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX link_text_idx ON cgn_file_publish (`link_text`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
ALTER TABLE `cgn_file_publish` COLLATE utf8_general_ci;
sqldelimeter;
$installTableSchemas[] = $table;

?>