# MySQL Diff 1.3.3
#
# http://www.mysqldiff.com
# (c) 2001-2003 Lippe-Net Online-Service
#
# Create time: 13.01.2008 23:20
#
# --------------------------------------------------------
# Source info
# Host: localhost
# Database: cgn_006
# --------------------------------------------------------
# Target info
# Host: localhost
# Database: cognifty
# --------------------------------------------------------
#


CREATE TABLE `cgn_blog` (
    `cgn_blog_id` int(11) NOT NULL auto_increment,
    `name` varchar(255) NOT NULL DEFAULT '',
    `title` varchar(255) NOT NULL DEFAULT '',
    `caption` varchar(255) NOT NULL DEFAULT '',
    `description` text NOT NULL DEFAULT '',
    `edited_on` int(11) NOT NULL DEFAULT '0',
    `created_on` int(11) NOT NULL DEFAULT '0',
    `owner_id` int(11) NOT NULL DEFAULT '0',
    `is_default` tinyint(4) NOT NULL DEFAULT '0',
    PRIMARY KEY (`cgn_blog_id`),
    INDEX `edited_on_idx` (`edited_on`),
    INDEX `published_on_idx` (`edited_on`),
    INDEX `created_on_idx` (`created_on`),
    INDEX `owner_idx` (`owner_id`),
    INDEX `is_default_idx` (`is_default`)
) TYPE=MyISAM;

CREATE TABLE `cgn_blog_comment` (
    `cgn_blog_comment_id` int(11) NOT NULL auto_increment,
    `cgn_blog_entry_publish_id` int(11) NOT NULL DEFAULT '0',
    `user_id` int(11) NOT NULL DEFAULT '0',
    `user_ip_addr` varchar(39) NOT NULL DEFAULT '',
    `user_email` varchar(255) NOT NULL DEFAULT '',
    `user_name` varchar(255) NOT NULL DEFAULT '',
    `user_url` varchar(255) NOT NULL DEFAULT '',
    `spam_rating` tinyint(1) NOT NULL DEFAULT '0',
    `approved` tinyint(1) unsigned NOT NULL DEFAULT '0',
    `tag` varchar(32) NULL DEFAULT NULL,
    `source` char(10) NOT NULL DEFAULT 'comment',
    `rating` tinyint(2) NULL DEFAULT NULL,
    `content` text NOT NULL DEFAULT '',
    `posted_on` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`cgn_blog_comment_id`),
    INDEX `posted_on_idx` (`posted_on`),
    INDEX `cgn_blog_idx` (`cgn_blog_entry_publish_id`)
) TYPE=MyISAM;

CREATE TABLE `cgn_blog_entry_publish` (
    `cgn_blog_entry_publish_id` int(11) NOT NULL auto_increment,
    `cgn_content_id` int(11) NOT NULL DEFAULT '0',
    `cgn_content_version` int(11) NOT NULL DEFAULT '1',
    `cgn_blog_id` int(11) NOT NULL DEFAULT '0',
    `author_id` int(11) NOT NULL DEFAULT '0',
    `title` varchar(255) NOT NULL DEFAULT '',
    `caption` varchar(255) NOT NULL DEFAULT '',
    `content` text NOT NULL DEFAULT '',
    `link_text` varchar(255) NOT NULL DEFAULT '',
    `cgn_guid` varchar(32) NOT NULL DEFAULT '',
    `posted_on` int(11) NOT NULL DEFAULT '0',
    `edited_on` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`cgn_blog_entry_publish_id`),
    INDEX `edited_on_idx` (`edited_on`),
    INDEX `posted_on_idx` (`posted_on`),
    INDEX `cgn_blog_idx` (`cgn_blog_id`),
    INDEX `cgn_content_idx` (`cgn_content_id`)
) TYPE=MyISAM;

CREATE TABLE `cgn_content_attrib` (
    `cgn_content_attrib_id` int(11) NOT NULL auto_increment,
    `cgn_content_id` int(11) unsigned NOT NULL DEFAULT '0',
    `code` varchar(30) NOT NULL DEFAULT '',
    `type` varchar(30) NOT NULL DEFAULT '',
    `value` varchar(255) NOT NULL DEFAULT '',
    `edited_on` int(11) unsigned NOT NULL DEFAULT '0',
    `created_on` int(11) unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`cgn_content_attrib_id`),
    INDEX `edited_on_idx` (`edited_on`),
    INDEX `created_on_idx` (`created_on`),
    INDEX `cgn_content_idx` (`cgn_content_id`)
) TYPE=MyISAM;

CREATE TABLE `cgn_content_tag` (
    `cgn_content_tag_id` int(11) NOT NULL auto_increment,
    `name` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`cgn_content_tag_id`),
    INDEX `name_idx` (`name`)
) TYPE=MyISAM;

CREATE TABLE `cgn_content_tag_link` (
    `cgn_content_tag_id` int(11) NOT NULL DEFAULT '',
    `cgn_content_id` int(11) NOT NULL DEFAULT '',
    INDEX `cgn_content_tag_idx` (`cgn_content_tag_id`),
    INDEX `cgn_content_idx` (`cgn_content_id`)
) TYPE=MyISAM;

CREATE TABLE `cgn_menu` (
    `cgn_menu_id` int(11) NOT NULL auto_increment,
    `title` varchar(255) NOT NULL DEFAULT '',
    `show_title` int(2) NOT NULL DEFAULT '1',
    `code_name` varchar(32) NOT NULL DEFAULT '',
    `edited_on` int(11) NOT NULL DEFAULT '0',
    `created_on` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`cgn_menu_id`),
    INDEX `code_name_idx` (`code_name`),
    INDEX `edited_on_idx` (`edited_on`),
    INDEX `created_on_idx` (`created_on`)
) TYPE=MyISAM;

CREATE TABLE `cgn_mxq` (
    `cgn_mxq_id` int(10) unsigned NOT NULL auto_increment,
    `cgn_mxq_channel_id` int(10) unsigned NOT NULL DEFAULT '0',
    `msg` longblob NOT NULL DEFAULT '',
    `received_on` int(10) unsigned NOT NULL DEFAULT '0',
    `viewed_on` int(10) unsigned NOT NULL DEFAULT '0',
    `msg_name` varchar(100) NOT NULL DEFAULT '',
    `return_address` varchar(200) NOT NULL DEFAULT '',
    `expiry_date` int(11) unsigned NOT NULL DEFAULT '0',
    `format_version` tinyint(2) unsigned NOT NULL DEFAULT '0',
    `format_type` varchar(10) NOT NULL DEFAULT 'text/xml',
    PRIMARY KEY (`cgn_mxq_id`),
    INDEX `received_on_idx` (`received_on`),
    INDEX `viewed_on_idx` (`viewed_on`),
    INDEX `cgn_mxq_channel_idx` (`cgn_mxq_channel_id`)
) TYPE=MyISAM;

CREATE TABLE `cgn_obj_trash` (
    `cgn_obj_trash_id` int(11) NOT NULL auto_increment,
    `table` varchar(255) NOT NULL DEFAULT '',
    `content` longblob NOT NULL DEFAULT '',
    `title` varchar(255) NOT NULL DEFAULT '',
    `user_id` int(11) NOT NULL DEFAULT '',
    `deleted_on` int(11) NOT NULL DEFAULT '',
    PRIMARY KEY (`cgn_obj_trash_id`),
    INDEX `user_idx` (`user_id`),
    INDEX `deleted_on_idx` (`deleted_on`)
) TYPE=MyISAM;

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
