<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_user_lost_ticket`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_user_lost_ticket` (
	`cgn_user_lost_ticket_id` integer (11) unsigned NOT NULL auto_increment, 
	`cgn_user_id` int (11) NOT NULL, 
	`ticket` varchar (65) NOT NULL, 
	`created_on` int (11) NOT NULL,
	PRIMARY KEY (`cgn_user_lost_ticket_id`) 
)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX ticket_idx ON cgn_user_lost_ticket (`ticket`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE INDEX cgn_user_idx ON cgn_user_lost_ticket (`cgn_user_id`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
ALTER TABLE `cgn_user_lost_ticket` COLLATE utf8_general_ci;
sqldelimeter;
$installTableSchemas[] = $table;

?>