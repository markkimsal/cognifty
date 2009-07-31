
ALTER TABLE `cgn_user` ADD COLUMN `id_provider`        varchar (30) NOT NULL default "self";
ALTER TABLE `cgn_user` ADD COLUMN `id_provider_token`  varchar (200) NULL default NULL;
ALTER TABLE `cgn_user` ADD COLUMN `reg_date`           int (10) UNSIGNED  NULL default NULL;
ALTER TABLE `cgn_user` ADD COLUMN `reg_referrer`       varchar (255) NULL default NULL;
ALTER TABLE `cgn_user` ADD COLUMN `reg_cpm`            varchar (30) NULL default NULL;
ALTER TABLE `cgn_user` ADD COLUMN `reg_ip_addr`        varchar (39) NOT NULL default "";
ALTER TABLE `cgn_user` ADD COLUMN `login_date`         int (10) UNSIGNED NULL default NULL;
ALTER TABLE `cgn_user` ADD COLUMN `login_ip_addr`      varchar (39) NOT NULL default "";
ALTER TABLE `cgn_user` ADD COLUMN `login_referrer`     varchar (255) NULL default NULL;

ALTER TABLE `cgn_user` MODIFY COLUMN `username`     varchar (255) NULL default NULL;

CREATE UNIQUE INDEX id_username_idx ON cgn_user (id_provider, username);
