ALTER TABLE `cgn_site_struct` ADD COLUMN `link_text` VARCHAR (255) NOT NULL AFTER `title`;

CREATE INDEX `title_idx`       ON `cgn_site_struct` (`title`);
CREATE INDEX `link_text_idx`   ON `cgn_site_struct` (`link_text`);

