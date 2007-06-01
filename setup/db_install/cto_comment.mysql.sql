-- Dumping SQL for project cognuto
-- entity version: 0.0
-- DB type: mysql
-- generated on: 05.23.2007


DROP TABLE IF EXISTS `cto_comment`;
CREATE TABLE `cto_comment` (
		
	`cto_comment_id` int (10) NOT NULL auto_increment, 
	`title` varchar (255) NOT NULL, 
	`body` text NOT NULL, 
	`created_on` int (10) NOT NULL, 
	`modified_on` int (10) NOT NULL, 
	`owner` int (10) NOT NULL, 
	`cto_material_id` int (10) NOT NULL, 
	`rating` int (10) NOT NULL,
	PRIMARY KEY (cto_comment_id) 
);

CREATE INDEX cto_material_idx ON cto_comment (cto_material_id);

