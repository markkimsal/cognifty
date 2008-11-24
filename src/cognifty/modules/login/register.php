<?php

/**
 * login
 *
 * Handles logins for site.  Also emails
 * lost passwords to users.
 */
class Cgn_Service_Login_Register extends Cgn_Service {

	/**
	 * @See Cgn_Service_Login_Register::init()
	 */
	var $_allowRegister = TRUE;


	/**
	 * Checks a global setting for allowing self registration.
	 *
	 * Change this value in your default.ini to show or hide the register 
	 * option on the login page: 
	 * [config]
	 * allow.selfregister=[ true | false ]
	 */
	function init($req, $mod, $srv, $evt) {
		$selfRegisterKey = 'config://default/allow/selfregister';
		if (Cgn_ObjectStore::hasConfig($selfRegisterKey)) {
			$this->_allowRegister = (bool)
				Cgn_ObjectStore::getConfig($selfRegisterKey);
		}
		parent::init($req, $mod, $srv, $evt);
		return $this->_allowRegister;
	}


	/**
	 * show login box
	 * use this cookie for long term session $_COOKIE['cgn_ltsession'];
	 */
	function mainEvent(&$req, &$t) {
		$values = array();
		$values['email'] = $req->cleanString('e');
		$t['form'] = $this->_loadRegForm($values);
	}


	/**
	 * save the registration
	 */
	function saveEvent(&$req, &$t) {
		$u = &$req->getUser();
		if (! $u->isAnonymous() ) {
			$u->addSessionMessage('You cannot register when you are already logged in.');
			$this->redirectHome($t);
			return false;
		}
		$em = $req->cleanString('email');
		$pw = $req->cleanString('password');
		$u->username = $em;
		$u->email    = $em;

		$u->password = $u->_hashPassword($pw);

		if (!Cgn_User::registerUser($u)) {
			Cgn_ErrorStack::throwError('User already exists.', 505);
			return false;
		}
		if ($u->login($em,$pw)) {
			$u->bindSession();
		}
		$this->presenter = 'redirect';
		$u->addSessionMessage("Congratulations, your account has been registered.");
		$t['url'] = cgn_appurl('account');
	}


	function _loadRegForm($values) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('reg');
		$f->action = cgn_appurl('login','register','save', array(), 'https');
		$f->label = 'Site Registration';
		$f->appendElement(new Cgn_Form_ElementInput('email'),$values['email']);
		$f->appendElement(new Cgn_Form_ElementPassword('password'));
		$f->appendElement(new Cgn_Form_ElementPassword('password2','Confirm Password'));
		$f->appendElement(new Cgn_Form_ElementHidden('event'),'save');
		return $f;
	}
}
?>
