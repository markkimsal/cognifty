-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_sess`;
CREATE TABLE `cgn_sess` (
		
	`cgn_sess_id` integer (11) unsigned NOT NULL auto_increment, 
	`cgn_sess_key` varchar (100) NOT NULL, 
	`saved_on` int (11) NOT NULL default 0, 
	`data` longtext NOT NULL, 
	PRIMARY KEY (cgn_sess_id) 
);

CREATE INDEX cgn_sess_key_idx ON cgn_sess (cgn_sess_key);

