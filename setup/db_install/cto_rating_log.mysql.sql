-- Dumping SQL for project cognuto
-- entity version: 0.0
-- DB type: mysql
-- generated on: 05.23.2007


DROP TABLE IF EXISTS `cto_rating_log`;
CREATE TABLE `cto_rating_log` (
		
	`cto_rating_log_id` int (10) NOT NULL auto_increment, 
	`user_id` int (10) NOT NULL, 
	`created_on` int (10) NOT NULL, 
	`cto_comment_id` int (10) NOT NULL, 
	`cto_material_id` int (10) NOT NULL, 
	`rating` int (10) NOT NULL,
	PRIMARY KEY (cto_rating_log_id) 
);

CREATE INDEX cto_comment_idx ON cto_rating_log (cto_comment_id);

