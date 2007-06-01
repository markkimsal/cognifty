-- Dumping SQL for project cognuto
-- entity version: 0.0
-- DB type: mysql
-- generated on: 05.23.2007


DROP TABLE IF EXISTS `cto_invitation_token`;
CREATE TABLE `cto_invitation_token` (
		
	`cto_invitation_token_id` int (10) NOT NULL auto_increment, 
	`invitee` varchar (255) NOT NULL, 
	`address` varchar (255) NOT NULL, 
	`token` varchar (255) NOT NULL, 
	`invited_on` int (11) NOT NULL, 
	`cto_invitation_list_id` int (11) NOT NULL, 
	`cto_material_id` int (10) NOT NULL,
	PRIMARY KEY (cto_invitation_token_id) 
);

CREATE INDEX cto_invitation_list_idx ON cto_invitation_token (cto_invitation_list_id);
CREATE INDEX cto_material_idx ON cto_invitation_token (cto_material_id);

