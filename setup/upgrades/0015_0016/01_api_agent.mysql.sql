ALTER TABLE `cgn_user`
    ADD COLUMN  `enable_agent` tinyint(1) unsigned NOT NULL default '0';

ALTER TABLE `cgn_user`
    ADD COLUMN  `agent_key` varchar(60) NOT NULL default '';
