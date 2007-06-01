-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 05.23.2007


DROP TABLE IF EXISTS `cgn_user`;
CREATE TABLE `cgn_user` (
		
	`cgn_user_id` int (11) NOT NULL auto_increment, 
	`username` varchar (255) NOT NULL, 
	`email` varchar (255) NOT NULL, 
	`password` varchar (255) NOT NULL,
	PRIMARY KEY (cgn_user_id) 
);

CREATE INDEX email_idx ON cgn_user (email);
CREATE INDEX username_idx ON cgn_user (username);

