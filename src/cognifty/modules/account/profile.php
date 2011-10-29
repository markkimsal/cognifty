<?php

Cgn::loadModLibrary('Account::Account_Base');
Cgn::loadModLibrary('Account::Account_Address');

/**
 * login
 *
 * Handles logins for site.  Also emails
 * lost passwords to users.
 */
class Cgn_Service_Account_Profile extends Cgn_Service {

	var $requireLogin = true;
	var $attributes   = array();
	var $user         = NULL;

	function Cgn_Service_Account_Contact() {
	}

	function getBreadCrumbs() {
		return array( cgn_applink('Account Home', 'account'), 'Change your public profile');
	}

	/**
	 * Show password change form.
	 */
	function editEvent(&$req, &$t) {
		$user = $req->getUser();
		$account = Account_Base::loadByUserId($user->userId);
		$t['contact'] = $account->_dataItem->valuesAsArray();

		$address = Account_Address::loadByAccountId($account->_dataItem->getPrimaryKey());
		$t['contact'] = array_merge($address->valuesAsArray(), $t['contact']);
		$t['contact'] = array_merge($t['contact'], $account->attributes);

		//db errors are "trigger_errors" in case the Cgn_ErrorStack is not used
		// as a handler.
		// an upgrade to the cgn_account_attrib table may result in an
		// error as tables are only dynamically rebuilt on insert/update
		$e = Cgn_ErrorStack::pullError('php');


		$t['profileForm'] = $this->_loadProfileForm($t['contact']);
	}

	/**
	 * Process password change form.
	 */
	function changeEvent(&$req, &$t) {
		$firstname  = $req->cleanString('firstname');
		$lastname   = $req->cleanString('lastname');
		$ws         = $req->cleanString('ws');
		$fb         = $req->cleanString('fb');
		$tw         = $req->cleanString('tw');
		$bio        = $req->cleanMultiline('bio');

		if ( strlen($ws) && substr($ws, 0, 4) != 'http') {
			$ws = 'http://'.$ws;
		}

		if ( strlen($tw) && substr($tw, 0, 1) == '@') {
			$tw = substr($tw, 1);
		}
		if ( strlen($tw) && substr($tw, 0, 4) == 'http') {
			$twiturl = parse_url($tw);
			if (isset($twiturl['fragment'])) {
				$tw = substr($twiturl['fragment'], 2);
			} else {
				$tw = substr($twiturl['path'], 1);
			}
		}

		$user = $req->getUser();

		$badPassword = true;
		if ($req->getUser()->_hashPassword($password) == $req->getUser()->password) {
			$badPassword = false;
		}


		$account = Account_Base::loadByUserId($user->userId);
		//db errors are "trigger_errors" in case the Cgn_ErrorStack is not used
		// as a handler.
		// an upgrade to the cgn_account_attrib table may result in an
		// error as tables are only dynamically rebuilt on insert/update
		$e = Cgn_ErrorStack::pullError('php');

		$account->attributes['ws'] = $ws;
		$account->attributes['tw'] = $tw;
		$account->attributes['fb'] = $fb;
		$account->attributes['bio'] = $bio;
		$account->firstname = $firstname;
		$account->lastname  = $lastname;
		$account->save();

		$this->attributes = $account->attributes;
		$this->attributes['firstname'] = $firstname;
		$this->attributes['lastname']  = $lastname;
		$this->user = $user;
		$this->account = $account;
		$this->emit('account_profile_save_after');
		unset($this->attributes);
		unset($this->user);

		$req->getUser()->addSessionMessage('Your account information has been updated.');

		$this->presenter = 'redirect';
		$t['url'] = cgn_appurl('account');
	}


	protected function _loadProfileForm($values) {
		Cgn::loadLibrary('Form::lib_cgn_form');
		Cgn::loadLibrary('Html_widgets::lib_cgn_widget');

		$f = new Cgn_Form('contact_info');
		$f->width = '50em';
		$f->label = 'Update your public profile';

		$f->layout = new Cgn_Form_Layout_Dl();

		$f->action = cgn_sappurl('account', 'profile', 'change');

		$f->appendElement(new Cgn_Form_ElementInput('firstname','First Name'), @$values['firstname']);
		$f->appendElement(new Cgn_Form_ElementInput('lastname','Last Name'), @$values['lastname']);

		$f->appendElement(new Cgn_Form_ElementInput('ws','Web Site'),  @$values['ws']);
		$f->appendElement(new Cgn_Form_ElementInput('tw','Twitter'),   @$values['tw']);
		$f->appendElement(new Cgn_Form_ElementInput('fb','Facebook'),  @$values['fb']);


		$f->appendElement(new Cgn_Form_ElementText('bio','About You'),  @$values['bio']);

	//	$f->appendElement(new Cgn_Form_ElementHidden('id'), $values['id']);
		return $f;
	}
}
