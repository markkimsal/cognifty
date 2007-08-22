
DROP TABLE IF EXISTS `cgn_mxq_channel`;
CREATE TABLE `cgn_mxq_channel` (
	  `cgn_mxq_channel_id` int(10) unsigned NOT NULL auto_increment,
	  `name` varchar(255) NOT NULL default '',
	  `channel_type` char(10) NOT NULL default 'ps',
	  `created_on` int(11) unsigned NOT NULL default '0',
	  PRIMARY KEY `cgn_mxq_channel_idx` (`cgn_mxq_channel_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1; 

ALTER TABLE `cgn_mxq_channel` ADD INDEX `created_on_idx` (`created_on`);
