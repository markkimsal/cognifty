
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


ALTER TABLE `cgn_article_publish`
    ALTER `cgn_content_id` SET DEFAULT '',
    ALTER `cgn_content_version` SET DEFAULT '';

ALTER TABLE `cgn_article_section_link`
    ALTER `cgn_article_section_id` SET DEFAULT '',
    ALTER `cgn_article_publish_id` SET DEFAULT '';

ALTER TABLE `cgn_file_publish`
    ALTER `cgn_content_id` SET DEFAULT '',
    ALTER `cgn_content_version` SET DEFAULT '';

ALTER TABLE `cgn_group`
    ALTER `active_on` SET DEFAULT '';

ALTER TABLE `cgn_image_publish`
    ALTER `cgn_content_id` SET DEFAULT '',
    ALTER `cgn_content_version` SET DEFAULT '';

ALTER TABLE `cgn_menu_item`
    ALTER `cgn_menu_id` SET DEFAULT '',
    ALTER `obj_id` SET DEFAULT '';

ALTER TABLE `cgn_metadata`
    ALTER `cgn_content_publish_id` SET DEFAULT '';

ALTER TABLE `cgn_metadata_publish`
    ALTER `cgn_content_id` SET DEFAULT '';

ALTER TABLE `cgn_mxq_channel`
    MODIFY `channel_type` char(10) NOT NULL DEFAULT 'point';
#
#  Fieldformat of
#    cgn_mxq_channel.channel_type changed from varchar(10) NOT NULL DEFAULT 'ps' to char(10) NOT NULL DEFAULT 'point'.
#  Possibly data modifications needed!
#

ALTER TABLE `cgn_sess`
    ADD `saved_on` int(11) NOT NULL DEFAULT '0' AFTER cgn_sess_key,
    MODIFY `cgn_sess_key` varchar(100) NOT NULL DEFAULT '';
#
#  Fieldformat of
#    cgn_sess.cgn_sess_key changed from varchar(255) NOT NULL DEFAULT '' to varchar(100) NOT NULL DEFAULT ''.
#  Possibly data modifications needed!
#