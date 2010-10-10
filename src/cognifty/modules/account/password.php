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

		$t['form'] = $this->_loadPasswordForm();
	}

	/**
	 * Process password change form.
	 */
	function changeEvent(&$req, &$t) {
		$oldpwd  = $req->cleanString('opwd');
		$newpwd  = $req->cleanString('pwd1');
		$newpwd2 = $req->cleanString('pwd2');
		if ($req->getUser()->_hashPassword($oldpwd) != $req->getUser()->password) {
		//	var_dump($req->getUser()->_hashPassword($oldpwd));exit();
			$req->getUser()->addSessionMessage('Your current password is not correct.');
			$newTicket = new Cgn_SystemTicket($this->moduleName, $this->serviceName, 'main');
			Cgn_SystemRunner::stackTicket($newTicket);

			return false;
		}
		if ($newpwd != $newpwd2) {
			$req->getUser()->addSessionMessage('Your new password does not match.');
			$newTicket = new Cgn_SystemTicket($this->moduleName, $this->serviceName, 'main');
			Cgn_SystemRunner::stackTicket($newTicket);
			return false;
		}
		if ( strlen(trim($newpwd)) < 5) {
			$req->getUser()->addSessionMessage('Your new password must be at least six (6) characters long.');
			$newTicket = new Cgn_SystemTicket($this->moduleName, $this->serviceName, 'main');
			Cgn_SystemRunner::stackTicket($newTicket);
			return false;
		}

		$user = $req->getUser();
		$user->setPassword( $newpwd );
		$user->save();
		//current user has changed cached settings, rebind
		$user->bindSession();

		$req->getUser()->addSessionMessage('Your password has been updated.');
		$this->presenter = 'redirect';
		$t['url'] = cgn_appurl('account');
	}


	protected function _loadPasswordForm() {
		Cgn::loadLibrary('Form::lib_cgn_form');
		Cgn::loadLibrary('Html_widgets::lib_cgn_widget');

		$f = new Cgn_Form('pwd_info');
		$f->width = '40em';
		$f->label = 'Change your password';

		$f->layout = new Cgn_Form_Layout_Dl();

		$f->action = cgn_sappurl('account', 'password', 'change');

		$f->appendElement(new Cgn_Form_ElementPassword('opwd','Current Password'));
		$f->appendElement(new Cgn_Form_ElementPassword('pwd1','New Password'));
		$f->appendElement(new Cgn_Form_ElementPassword('pwd2','Confirm New Password'));

//		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['id']);
		return $f;
	}
}
