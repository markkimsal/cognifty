CREATE TABLE `cgn_site_area` (
    `cgn_site_area_id` int(11) NOT NULL auto_increment,
    `title` varchar(255) NOT NULL DEFAULT '',
    `description` text NULL DEFAULT NULL,
    `site_template` varchar(25) NOT NULL DEFAULT '0',
    `template_style` varchar(25) NOT NULL DEFAULT '0',
    `cgn_def_menu_id` int(11) NOT NULL DEFAULT '0',
    `edited_on` int(11) NOT NULL DEFAULT '0',
    `created_on` int(11) NOT NULL DEFAULT '0',
    `owner_id` int(11) NOT NULL DEFAULT '0',
    `is_default` tinyint(4) NOT NULL DEFAULT '0',
    PRIMARY KEY (`cgn_site_area_id`),
    INDEX `edited_on_idx` (`edited_on`),
    INDEX `published_on_idx` (`edited_on`),
    INDEX `created_on_idx` (`created_on`),
    INDEX `owner_idx` (`owner_id`),
    INDEX `is_default_idx` (`is_default`)
) TYPE=MyISAM;

CREATE TABLE `cgn_site_struct` (
    `cgn_site_struct_id` int(10) unsigned NOT NULL auto_increment,
    `node_id` int(10) unsigned NOT NULL DEFAULT '0',
    `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
    `node_kind` char(10) NOT NULL DEFAULT 'web',
    `title` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`cgn_site_struct_id`),
    INDEX `node_idx` (`node_id`),
    INDEX `parent_idx` (`parent_id`),
    INDEX `node_kind_idx` (`node_kind`)
) TYPE=MyISAM;
