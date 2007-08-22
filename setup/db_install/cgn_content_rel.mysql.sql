CREATE TABLE `cgn_content_rel` (
	  `from_id` int(10) unsigned NOT NULL default '0',
	  `to_id` int(10) unsigned NOT NULL default '0',
) ENGINE=MyISAM DEFAULT CHARSET=latin1; 

CREATE INDEX `from_idx` ON `cgn_content_rel` (`from_id`);
CREATE INDEX `to_idx` ON `cgn_content_rel` (`to_id`);
