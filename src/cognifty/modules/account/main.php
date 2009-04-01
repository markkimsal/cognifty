<?php

/**
 * Account
 *
 * Handle user accounts and profiles
 */
class Cgn_Service_Account_Main extends Cgn_Service {

	var $requireLogin = true;

	function Cgn_Service_Account_Main() {
	}

	function getBreadCrumbs() {
		return array('Account Home');
	}

	/**
	 * Show account settings.
	 */
	function mainEvent($req, &$t) {
		$u = $req->getUser();
		$t['acctObj'] = new Cgn_DataItem('cgn_account');
		$t['acctObj']->load(' cgn_user_id = '.$u->userId);

	}
}
?>
