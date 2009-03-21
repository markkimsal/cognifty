ALTER TABLE `cgn_content_tag` MODIFY COLUMN `cgn_content_tag_id` INTEGER  NOT NULL AUTO_INCREMENT,
 ADD COLUMN `link_text` varchar(255)  NOT NULL DEFAULT '' AFTER `name`;
