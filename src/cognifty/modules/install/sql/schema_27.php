<?
$installTableSchemas = array();
$table = <<<sqldelimeter
DROP TABLE IF EXISTS `cgn_mxq`
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
CREATE TABLE `cgn_mxq` (
	  `cgn_mxq_id` int(10) unsigned NOT NULL auto_increment,
	  `cgn_mxq_channel_id` int(10) unsigned NOT NULL default '0',
	  `msg` longblob NOT NULL,
	  `received_on` int(10) unsigned NOT NULL default '0',
	  `viewed_on` int(10) unsigned NOT NULL default '0',
	  `msg_name` varchar(100) NOT NULL default '',
	  `return_address` varchar(200) NOT NULL default '',
	  `expiry_date` int(11) unsigned NOT NULL default '0',
	  `format_version` tinyint(2) unsigned NOT NULL default '0',
	  `format_type` varchar(10) NOT NULL default 'text/xml',
	  PRIMARY KEY `cgn_mxq_idx` (`cgn_mxq_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
ALTER TABLE `cgn_mxq` ADD INDEX `received_on_idx` (`received_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
ALTER TABLE `cgn_mxq` ADD INDEX `viewed_on_idx` (`viewed_on`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
ALTER TABLE `cgn_mxq` ADD INDEX `cgn_mxq_channel_idx` (`cgn_mxq_channel_id`)
sqldelimeter;
$installTableSchemas[] = $table;
$table = <<<sqldelimeter
ALTER TABLE `cgn_mxq` COLLATE utf8_general_ci;
sqldelimeter;
$installTableSchemas[] = $table;

?>