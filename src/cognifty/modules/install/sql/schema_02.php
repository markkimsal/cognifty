<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_user_group_link`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_user_group_link` (
	`cgn_group_id` int (11) NOT NULL, 
	`cgn_user_id` int (11) NOT NULL, 
	`active_on` int (11) NOT NULL
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX cgn_group_idx ON cgn_user_group_link (`cgn_group_id`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX cgn_user_idx ON cgn_user_group_link (`cgn_user_id`);
sqldelimeter;
$installTableSchemas[] = $table;

?>