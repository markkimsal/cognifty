<?php

/**
 * Manager a user's account
 */
class Cgn_User_Account extends Cgn_Data_Model {

	/**
	 * Return the root unique filename to identify this account image.
	 */
	public static function makeAccountImageBasename($uid, $aid) {
		return md5(sha1( intval($uid) . intval($aid)));
	}

	public static function getAccountImageUrl($uid, $aid) {
		return cgn_url().'var/acct_img/'.Cgn_User_Account::getImageUrl(Cgn_User_Account::makeAccountImageBasename($uid, $aid));
	}

	public static function getImageUrl($basename) {
		return Cgn_User_Account::getRelativeDir($basename).'w'.$basename.'.png';
	}

	public static function getImageFilename($basename) {
		return Cgn_User_Account::getCacheDir($basename).'w'.$basename.'.png';
	}

	public static function getRelativeDir($basename) {
		$name = basename($basename);
		$one = substr($name, 0, 1);
		$two = $one.'/'.substr($name, 1, 1).'/';
		return $two;
	}

	public static function getCacheDir($basename) {
		$start = BASE_DIR.'var/acct_img';
		//var_dump( $start.'/'.Cgn_User_Account::getRelativeDir($basename));
		return $start.'/'.Cgn_User_Account::getRelativeDir($basename);
	}



}


/**
 * Manage an organization's account
 */
class Cgn_Org_Account extends Cgn_Data_Model {

}

?>
