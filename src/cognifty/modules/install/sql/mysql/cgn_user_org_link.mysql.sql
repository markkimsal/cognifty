
-- DROP TABLE IF EXISTS `cgn_user_org_link`;
CREATE TABLE IF NOT EXISTS`cgn_user_org_link` (
		
	`cgn_org_id`  int (11) NOT NULL default 0, 
	`cgn_user_id` int (11) NOT NULL default 0, 
	`joined_on`   int (11) NOT NULL default 0,
	`is_active`   int (11) NOT NULL default 0,
	`role_code`   varchar (32) NOT NULL default 'member',
	`inviter_id`  int (11) NULL
);

CREATE INDEX cgn_org_idx     ON cgn_user_org_link (`cgn_org_id`);
CREATE INDEX cgn_user_idx    ON cgn_user_org_link (`cgn_user_id`);
CREATE INDEX is_active_idx   ON cgn_user_org_link (`is_active`); 
ALTER TABLE `cgn_user_org_link` COLLATE utf8_general_ci;
