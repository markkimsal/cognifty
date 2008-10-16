#
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


ALTER TABLE `cgn_user_group_link`
    ALTER `cgn_group_id` SET DEFAULT 0,
    ALTER `cgn_user_id` SET DEFAULT 0,
    ALTER `active_on` SET DEFAULT 0;
