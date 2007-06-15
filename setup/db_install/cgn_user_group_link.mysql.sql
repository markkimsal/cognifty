-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_user_group_link`;
CREATE TABLE `cgn_user_group_link` (
		
	`cgn_group_id` int (11) NOT NULL, 
	`cgn_user_id` int (11) NOT NULL, 
	`active_on` int (11) NOT NULL
);

CREATE INDEX cgn_group_idx ON cgn_user_group_link (`cgn_group_id`);
CREATE INDEX cgn_user_idx ON cgn_user_group_link (`cgn_user_id`);
