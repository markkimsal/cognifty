<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_user`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_user` (
	`cgn_user_id` int (11) NOT NULL auto_increment, 
	`username` varchar (255) NOT NULL, 
	`email` varchar (255) NOT NULL, 
	`password` varchar (255) NOT NULL, 
	`active_on` int (11) NOT NULL, 
	`active_key` varchar (255) NOT NULL,
	PRIMARY KEY (cgn_user_id) 
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX email_idx ON cgn_user (email)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX active_on_idx ON cgn_user (active_on)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX active_key_idx ON cgn_user (active_key)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX username_idx ON cgn_user (username);
sqldelimeter;
$installTableSchemas[] = $table;

?>