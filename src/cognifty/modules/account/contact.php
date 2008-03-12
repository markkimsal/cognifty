<?php

Cgn::loadModLibrary('Account::Account_Base');

/**
 * login
 *
 * Handles logins for site.  Also emails
 * lost passwords to users.
 */
class Cgn_Service_Account_Contact extends Cgn_Service {

	var $requireLogin = true;

	function Cgn_Service_Account_Contact() {
	}


	/**
	 * Show password change form.
	 */
	function mainEvent(&$req, &$t) {
		$user = $req->getUser();
		$account = Account_Base::loadByUserId($user->userId);
		$t['contact'] = $account->_dataItem->valuesAsArray();
		$t['contact']['email'] = $user->email;
	}

	/**
	 * Process password change form.
	 */
	function changeEvent(&$req, &$t) {
		$firstname  = $req->cleanString('firstname');
		$lastname   = $req->cleanString('lastname');
		$email      = $req->cleanString('email');
		$password   = $req->cleanString('password');

		$user = $req->getUser();

		$badPassword = true;
		if ($req->getUser()->_hashPassword($oldpwd) == $req->getUser()->password) {
			$badPassword = false;
		}
		/*
			$req->getUser()->addSessionMessage('Your password is not correct.');
			return false;
		 */

		$account = Account_Base::loadByUserId($user->userId);
		$account->firstname = $firstname;
		$account->lastname  = $lastname;
		$account->save();
		$req->getUser()->addSessionMessage('Your account information has been updated.');
		/*
		$user->save();
		$req->getUser()->addSessionMessage('Your password has been updated.');
		 */
		$this->presenter = 'redirect';
		$t['url'] = cgn_appurl('account');
	}
}
?>
