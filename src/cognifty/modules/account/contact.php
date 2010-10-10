<?php

Cgn::loadModLibrary('Account::Account_Base');
Cgn::loadModLibrary('Account::Account_Address');

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


		$t['contactForm'] = $this->_loadContactInfoForm($t['contact']);
	}

	/**
	 * Process password change form.
	 */
	function changeEvent(&$req, &$t) {
		$firstname  = $req->cleanString('firstname');
		$lastname   = $req->cleanString('lastname');
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
			$u = $req->getUser();
			$u->email = $email;
			$u->save();
			$u->bindSession();
		}

		$account = Account_Base::loadByUserId($user->userId);
		$account->firstname = $firstname;
		$account->lastname  = $lastname;
		$account->save();

		$address = Account_Address::loadByAccountId($account->_dataItem->getPrimaryKey());
		$address->set('telephone', $phone);
		$address->set('firstname', $firstname);
		$address->set('lastname',  $lastname);
		$address->save();

		$req->getUser()->addSessionMessage('Your account information has been updated.');

		$this->presenter = 'redirect';
		$t['url'] = cgn_appurl('account');
	}


	protected function _loadContactInfoForm($values) {
		Cgn::loadLibrary('Form::lib_cgn_form');
		Cgn::loadLibrary('Html_widgets::lib_cgn_widget');

		$f = new Cgn_Form('contact_info');
		$f->width = '40em';
		$f->label = 'Change your contact information';

		$f->layout = new Cgn_Form_Layout_Dl();

		$f->action = cgn_sappurl('account', 'contact', 'change');

		$f->appendElement(new Cgn_Form_ElementInput('firstname','First Name'), @$values['firstname']);
		$f->appendElement(new Cgn_Form_ElementInput('lastname','Last Name'), @$values['lastname']);
		$f->appendElement(new Cgn_Form_ElementInput('phone',   'Phone Number'), @$values['telephone']);


		$f->appendElement(new Cgn_Form_ElementContentLine('You must supply your current password to change your e-mail address'));

		$f->appendElement(new Cgn_Form_ElementInput('email','E-mail'), @$values['email']);
		$f->appendElement(new Cgn_Form_ElementPassword('password','Password'), @$values['password']);

		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['id']);
		return $f;
	}
}
