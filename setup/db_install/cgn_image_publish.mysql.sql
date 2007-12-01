-- Dumping SQL for project cognifty
-- entity version: 0.0
-- DB type: mysql
-- generated on: 06.15.2007

DROP TABLE IF EXISTS `cgn_image_publish`;

CREATE TABLE `cgn_image_publish` (
  `cgn_image_publish_id` int(11) NOT NULL auto_increment,
  `cgn_content_id` int(11) NOT NULL,
  `cgn_content_version` int(11) NOT NULL,
  `cgn_guid` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `mime` varchar(255) NOT NULL,
  `caption` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `org_image` longblob NOT NULL,
  `web_image` longblob NOT NULL,
  `thm_image` longblob NOT NULL,
  `filename` varchar (255) NOT NULL, 
  `link_text` varchar(255) NOT NULL,
  `published_on` integer (11) NOT NULL default 1,
  `edited_on` integer (11) NOT NULL default 1,
  `created_on` integer (11) NOT NULL default 1,
  PRIMARY KEY  (`cgn_image_publish_id`)
);

CREATE INDEX edited_on_idx ON cgn_image_publish (`edited_on`);
CREATE INDEX published_on_idx ON cgn_image_publish (`published_on`);
CREATE INDEX created_on_idx ON cgn_image_publish (`created_on`);
CREATE INDEX link_text_idx ON cgn_image_publish (`link_text`);
ALTER TABLE `cgn_image_publish` COLLATE utf8_general_ci;
