-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.14.2007


DROP TABLE IF EXISTS `cgn_content_tag_link`;
CREATE TABLE `cgn_content_tag_link` (
		
	`cgn_content_tag_id` integer (11) NOT NULL, 
	`cgn_content_id` integer (11) NOT NULL,
	`created_on` integer (11) NOT NULL,
	`tag_type` varchar (55) NOT NULL default ''
);

CREATE INDEX cgn_content_tag_idx ON `cgn_content_tag_link` (`cgn_content_tag_id`);
CREATE INDEX cgn_content_idx ON `cgn_content_tag_link` (`cgn_content_id`);
CREATE INDEX tag_type_idx ON `cgn_content_tag_link` (`tag_type`);
