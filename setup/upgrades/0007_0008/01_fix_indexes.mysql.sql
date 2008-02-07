ALTER TABLE `cgn_file_publish`
    ALTER `cgn_content_id` SET DEFAULT '',
    ALTER `cgn_content_version` SET DEFAULT '',
    DROP INDEX published_on_idx,
    ADD INDEX `published_on_idx` (`published_on`),
    DROP INDEX created_on_idx,
    ADD INDEX `created_on_idx` (`created_on`),
    DROP INDEX cgn_content_idx;


ALTER TABLE `cgn_image_publish`
    ALTER `cgn_content_id` SET DEFAULT '',
    ALTER `cgn_content_version` SET DEFAULT '',
    DROP INDEX published_on_idx,
    ADD INDEX `published_on_idx` (`published_on`),
    DROP INDEX created_on_idx,
    ADD INDEX `created_on_idx` (`created_on`),
    DROP INDEX cgn_content_idx;

ALTER TABLE `cgn_article_publish`
    ALTER `cgn_content_id` SET DEFAULT '',
    ALTER `cgn_content_version` SET DEFAULT '',
    DROP INDEX published_on_idx,
    ADD INDEX `published_on_idx` (`published_on`),
    DROP INDEX created_on_idx,
    ADD INDEX `created_on_idx` (`created_on`);


ALTER TABLE `cgn_web_publish`
    ALTER `cgn_content_version` SET DEFAULT 1,
    DROP INDEX published_on_idx,
    ADD INDEX `published_on_idx` (`published_on`);
