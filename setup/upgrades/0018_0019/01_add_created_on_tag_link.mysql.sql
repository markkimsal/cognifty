ALTER TABLE `cgn_content_tag_link` 
 ADD COLUMN `created_on` int(12)  NOT NULL DEFAULT 0 AFTER `cgn_content_id`;
