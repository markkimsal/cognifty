ALTER TABLE `cgn_user` 
 ADD COLUMN `locale` varchar(10)  NOT NULL DEFAULT '' AFTER `password`;
ALTER TABLE `cgn_user` 
 ADD COLUMN `tzone`  varchar(45)  NOT NULL DEFAULT '' AFTER `locale`;
