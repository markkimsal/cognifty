CREATE TABLE `cgn_log_visitor` (
    `cgn_log_visitor_id` int(11) NOT NULL auto_increment,
    `ip_addr` char(16) NOT NULL DEFAULT '',
    `user_id` int(11) unsigned NULL DEFAULT NULL,
    `recorded_on` int(11) unsigned NOT NULL DEFAULT '0',
    `session_id` char(32) NOT NULL DEFAULT '',
    url varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`cgn_log_visitor_id`),
    INDEX `user_idx` (`user_id`),
    INDEX `recorded_on_idx` (`recorded_on`)
) TYPE=MyISAM;
