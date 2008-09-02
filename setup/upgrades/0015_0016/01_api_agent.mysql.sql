ALTER TABLE `cgn_user`
    ADD COLUMN  `enable_agent` tinyint(1) unsigned NOT NULL default '0';

ALTER TABLE `cgn_user`
    ADD COLUMN  `agent_key` varchar(60) NOT NULL default '';


CREATE INDEX agent_key_idx ON cgn_user (agent_key);
