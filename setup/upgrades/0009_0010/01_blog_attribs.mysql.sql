CREATE TABLE `cgn_blog_attrib` (
    `cgn_blog_attrib_id` int(11) NOT NULL auto_increment,
    `cgn_blog_id` int(11) unsigned NOT NULL DEFAULT '0',
    code varchar(30) NOT NULL DEFAULT '',
    type varchar(30) NOT NULL DEFAULT '',
    value varchar(255) NOT NULL DEFAULT '',
    `edited_on` int(11) unsigned NOT NULL DEFAULT '0',
    `created_on` int(11) unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`cgn_blog_attrib_id`),
    INDEX `edited_on_idx` (`edited_on`),
    INDEX `created_on_idx` (`created_on`),
    INDEX `cgn_blog_idx` (`cgn_blog_id`)
) TYPE=MyISAM;

