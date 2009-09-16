-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_user`;
CREATE TABLE `cgn_user` (
		
	`cgn_user_id`        integer (11) unsigned NOT NULL auto_increment, 
	`username`           varchar (255) NULL default NULL, 
	`email`              varchar (255) NOT NULL default "", 
	`password`           varchar (255) NOT NULL, 
	`locale`             varchar (10) NOT NULL default "", 
	`tzone`              varchar (45) NOT NULL default "", 
	`active_on`          integer (11) NOT NULL default 0, 
	`active_key`         varchar (255) NOT NULL default "",
	`id_provider`        varchar (30) NOT NULL default "self",
	`id_provider_token`  varchar (200) NULL default NULL,
	`reg_date`           int     (10) UNSIGNED NULL default NULL,
	`reg_referrer`       varchar (255) NULL default NULL,
	`reg_cpm`            varchar (30) NULL default NULL,
	`reg_ip_addr`        varchar (39) NOT NULL default "",
	`login_date`         int     (10) UNSIGNED NULL default NULL,
	`login_ip_addr`      varchar (39) NOT NULL default "",
	`login_referrer`     varchar (255) NULL default NULL,
	`agent_key`          varchar (60) NOT NULL default "",
	`enable_agent` tinyint(1) unsigned NOT NULL default '0',
	PRIMARY KEY (cgn_user_id) 
);

CREATE INDEX email_idx ON cgn_user (email);
CREATE INDEX active_on_idx ON cgn_user (active_on);
CREATE INDEX active_key_idx ON cgn_user (active_key);
CREATE INDEX username_idx ON cgn_user (username);

CREATE INDEX agent_key_idx ON cgn_user (agent_key);

CREATE UNIQUE INDEX id_username_idx ON cgn_user (id_provider, username);

ALTER TABLE `cgn_user` COLLATE utf8_general_ci;
