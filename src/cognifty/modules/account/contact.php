<?php

Cgn::loadModLibrary('Account::Account_Base');
Cgn::loadModLibrary('Account::Account_Address');

/**
 * login
 *
 * Handles logins for site.  Also emails
 * lost passwords to users.
 *
 * @emit account_email_changed
 * @emit account_profile_save_after
 */
class Cgn_Service_Account_Contact extends Cgn_Service {

	var $requireLogin = true;
	var $user         = NULL;
	var $profile      = array();

	function Cgn_Service_Account_Contact() {
	}

	function getBreadCrumbs() {
		return array( cgn_applink('Account Home', 'account'), 'Change your contact information');
	}

	/**
	 * Show password change form.
	 */
	function mainEvent(&$req, &$t) {
		$user = $req->getUser();
		$account = Account_Base::loadByUserId($user->userId);
		$t['contact'] = $account->_dataItem->valuesAsArray();
		$t['contact']['email'] = $user->email;

		$address = Account_Address::loadByAccountId($account->_dataItem->getPrimaryKey());
		$t['contact'] = array_merge($t['contact'], $address->valuesAsArray());

		//db errors are "trigger_errors" in case the Cgn_ErrorStack is not used
		// as a handler.
		// an upgrade to the cgn_account_attrib table may result in an
		// error as tables are only dynamically rebuilt on insert/update
		$e = Cgn_ErrorStack::pullError('php');

		$t['contactForm'] = $this->_loadContactInfoForm($t['contact']);
	}

	/**
	 * Process password change form.
	 */
	function changeEvent(&$req, &$t) {
		$email      = $req->cleanString('email');
		$password   = $req->cleanString('password');
		$phone      = $req->cleanString('phone');

		$user = $req->getUser();

		$badPassword = true;
		if ($req->getUser()->_hashPassword($password) == $req->getUser()->password) {
			$badPassword = false;
		}

		if ($email != $req->getUser()->email && $badPassword) {
			$req->getUser()->addMessage('Your password is not correct.', 'msg_warn');
			$newTicket = new Cgn_SystemTicket($this->moduleName, $this->serviceName, 'main');
			Cgn_SystemRunner::stackTicket($newTicket);
			return true;
		}

		if ($email != $req->getUser()->email && !$badPassword) {
			if (!$this->_checkEmailUniqueness($email)) {
				$req->getUser()->addMessage('This email is not valid.', 'msg_warn');
				$newTicket = new Cgn_SystemTicket($this->moduleName, $this->serviceName, 'main');
				Cgn_SystemRunner::stackTicket($newTicket);
				return true;
			}
			$u = $req->getUser();
			$u->email = $email;
			$u->save();
			$this->user = $u;
			$this->emit('account_email_changed');
			unset($this->user);
			$u->bindSession();
		}

		$account = Account_Base::loadByUserId($user->userId);
		$address = Account_Address::loadByAccountId($account->_dataItem->getPrimaryKey());
		if (!$address->dataItem->_isNew) {
		}

		//db errors are "trigger_errors" in case the Cgn_ErrorStack is not used
		// as a handler.
		// an upgrade to the cgn_account_attrib table may result in an
		// error as tables are only dynamically rebuilt on insert/update
		$e = Cgn_ErrorStack::pullError('php');

		$address->set('telephone', $phone);
		$address->save();
		$this->profile = $address->valuesAsArray();
		$this->emit('account_profile_save_after');
		unset($this->profile);

		$req->getUser()->addSessionMessage('Your account information has been updated.');

		$this->presenter = 'redirect';
		$t['url'] = cgn_appurl('account');
	}


	protected function _loadContactInfoForm($values) {
		Cgn::loadLibrary('Form::lib_cgn_form');
		Cgn::loadLibrary('Html_widgets::lib_cgn_widget');

		$f = new Cgn_Form('contact_info');
		$f->width = '40em';
		$f->label = 'Change your account information';

		$f->layout = new Cgn_Form_Layout_Dl();

		$f->action = cgn_sappurl('account', 'contact', 'change');

//		$f->appendElement(new Cgn_Form_ElementInput('firstname','First Name'), @$values['firstname']);
//		$f->appendElement(new Cgn_Form_ElementInput('lastname','Last Name'), @$values['lastname']);
		$f->appendElement(new Cgn_Form_ElementInput('phone',   'Phone Number'), @$values['telephone']);


		$f->appendElement(new Cgn_Form_ElementContentLine('You must supply your current password to change your e-mail address'));

		$f->appendElement(new Cgn_Form_ElementInput('email','E-mail'), @$values['email']);
		$f->appendElement(new Cgn_Form_ElementPassword('password','Password'));

		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['id']);
		return $f;
	}

	/**
	 * Return false if email is already used
	 *
	 * @return bool  True if the email is free to be used
	 */
	protected function _checkEmailUniqueness($email) {
		//check email validity
		$finder = new Cgn_DataItem('cgn_user');
		$finder->andWhere('username', $email);
		$finder->orWhereSub('email', $email);
		$rows = $finder->findAsArray();
		if (count($rows)) {
			return FALSE;
		}
		return TRUE;
	}
}
