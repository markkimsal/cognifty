<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_sess`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_sess` (
	`cgn_sess_id` integer (11) unsigned NOT NULL auto_increment, 
	`cgn_sess_key` varchar (100) NOT NULL, 
	`saved_on` int (11) NOT NULL default 0, 
	`data` longtext NOT NULL, 
	PRIMARY KEY (cgn_sess_id) 
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX cgn_sess_key_idx ON cgn_sess (cgn_sess_key);
sqldelimeter;
$installTableSchemas[] = $table;

?>