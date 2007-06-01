-- Dumping SQL for project cognuto
-- entity version: 0.0
-- DB type: mysql
-- generated on: 05.23.2007


DROP TABLE IF EXISTS `cto_material`;
CREATE TABLE `cto_material` (
		
	`cto_material_id` int (10) NOT NULL auto_increment, 
	`title` varchar (255) NOT NULL, 
	`body` text NOT NULL, 
	`created_on` int (10) NOT NULL, 
	`modified_on` int (10) NOT NULL, 
	`owner` int (10) NOT NULL,
	PRIMARY KEY (cto_material_id) 
);
