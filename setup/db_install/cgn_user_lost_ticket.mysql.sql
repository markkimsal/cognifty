-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_user_lost_ticket`;
CREATE TABLE `cgn_user_lost_ticket` (

	`cgn_user_lost_ticket_id` integer (11) unsigned NOT NULL auto_increment, 
	`cgn_user_id` int (11) NOT NULL, 
	`ticket` varchar (65) NOT NULL, 
	`created_on` int (11) NOT NULL,
	PRIMARY KEY (`cgn_user_lost_ticket_id`) 
);

CREATE INDEX ticket_idx ON cgn_user_lost_ticket (`ticket`);
CREATE INDEX cgn_user_idx ON cgn_user_lost_ticket (`cgn_user_id`);
ALTER TABLE `cgn_user_lost_ticket` COLLATE utf8_general_ci;
