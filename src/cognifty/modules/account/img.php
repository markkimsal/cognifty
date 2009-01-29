<?php

/**
 * Account image
 *
 */
class Cgn_Service_Account_Img extends Cgn_Service {

	var $requireLogin = true;

	function Cgn_Service_Account_Main() {
	}

	/**
	 * Show the account icon.
	 *
	 * @param String $i   file hash of account GUID
	 * @param int    $uid GUID of account
	 */
	function mainEvent($req, &$t) {
		$t['file'] = $req->cleanString('i');
		$this->presenter = 'self';
	}

	/**
	 * Output the account image with content-type header
	 */
	function output($req, &$t) {
		header('Content-Type: image/png');
		$f = fopen(BASE_DIR.'/media/icons/default/user_icon.png', 'r');
		fpassthru($f);
		fclose($f);
	}
}
?>
