<?php

/**
 * login
 *
 * Handles logins for site.  Also emails
 * lost passwords to users.
 */
class Cgn_Service_Account_Password extends Cgn_Service {

	var $requireLogin = true;

	function Cgn_Service_Account_Password() {
	}


	function getBreadCrumbs() {
		return array( cgn_applink('Account Home', 'account'), 'Change your password');
	}


	/**
	 * Show password change form.
	 */
	function mainEvent(&$req, &$t) {

	}

	/**
	 * Process password change form.
	 */
	function changeEvent(&$req, &$t) {
		$oldpwd  = $req->cleanString('password');
		$newpwd  = $req->cleanString('newpassword');
		$newpwd2 = $req->cleanString('newpassword2');
		if ($req->getUser()->_hashPassword($oldpwd) != $req->getUser()->password) {
		//	var_dump($req->getUser()->_hashPassword($oldpwd));exit();
			$req->getUser()->addSessionMessage('Your password does not match.');
			return false;
		}
		if ($newpwd != $newpwd2) {
			$req->getUser()->addSessionMessage('Your password does not match.');
			return false;
		}

		$user = $req->getUser();
		$user->setPassword( $newpwd );
		$user->save();
		$req->getUser()->addSessionMessage('Your password has been updated.');
		$this->presenter = 'redirect';
		$t['url'] = cgn_appurl('account');
	}
}
?>
