DROP TABLE IF EXISTS `cgn_mxq`;
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
	  PRIMARY KEY (`cgn_mxq_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE INDEX `received_on_idx` ON `cgn_mxq` (`received_on`);
CREATE INDEX `viewed_on_idx` ON `cgn_mxq` (`viewed_on`);
CREATE INDEX `cgn_mxq_channel_idx` ON `cgn_mxq` (`cgn_mxq_channel_id`);
ALTER TABLE `cgn_mxq` COLLATE utf8_general_ci;
