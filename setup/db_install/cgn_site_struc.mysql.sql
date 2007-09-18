-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql


DROP TABLE IF EXISTS `cgn_site_struc`;
CREATE TABLE `cgn_struct` (
		
	`cgn_struct_id` integer (10) unsigned NOT NULL auto_increment, 
	`node_id` integer (10) unsigned NOT NULL default 0, 
	`parent_id` integer (10) unsigned NOT NULL default 0, 
	`node_kind` char    (10)  NOT NULL default 'web', 
	`title`     varchar (255) NOT NULL, 
	PRIMARY KEY (cgn_struct_id) 
);

CREATE INDEX `node_idx`        ON cgn_struct (`node_id`);
CREATE INDEX `parent_idx`      ON cgn_struct (`parent_id`);
CREATE INDEX `node_kind_idx`   ON cgn_struct (`node_kind`);

