-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.12.2007


DROP TABLE IF EXISTS `cgn_article_section_link`;
CREATE TABLE `cgn_article_section_link` (
		
	`cgn_article_section_id` int (11) NOT NULL, 
	`cgn_article_publish_id` int (11) NOT NULL, 
	`active_on` int (11) NOT NULL default 0
);

CREATE INDEX cgn_article_section_idx ON cgn_article_section_link (`cgn_article_section_id`);
CREATE INDEX cgn_article_publish_idx ON cgn_article_section_link (`cgn_article_publish_id`);
