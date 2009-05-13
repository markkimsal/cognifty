<?php

/**
 * login
 *
 * Handles logins for site.  Also emails
 * lost passwords to users.
 *
 * @emit login_register_save_before
 * @emit login_register_save_after
 * @emit login_register_save_error
 */
class Cgn_Service_Login_Register extends Cgn_Service {

	var $_allowRegister = TRUE;

	public $regUser  = NULL;
	public $regEmail = NULL;
	public $regPw    = NULL;
	public $regPw2   = NULL;

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
		$em  = $req->cleanString('email');
		$pw  = $req->cleanString('password');
		$pw2 = $req->cleanString('password2');
		$u->username = $em;
		$u->email    = $em;

		$u->password = $u->_hashPassword($pw);

		$this->regUser      = $u;
		$this->regEmail     = $em;
		$this->regPw        = $pw;
		$this->regPw2       = $pw2;

		//signalResult should be true to continue with registration
		$signalResult = $this->emit('login_register_save_before');

		if ($signalResult === FALSE) {
			Cgn_ErrorStack::throwError('Unknown error with registration.', 506);
			return false;
		}
		//check basic registration requirements
		if (strlen($pw) < 3) {
			Cgn_ErrorStack::throwError('Password is not long enough.', 506);
			return false;
		}

		//check basic registration requirements
		if ($pw !== $pw2) {
			Cgn_ErrorStack::throwError('Passwords do not match.', 506);
			return false;
		}

		if (!Cgn_User::registerUser($u)) {
			$this->emit('login_register_save_error');
			Cgn_ErrorStack::throwError('User already exists.', 505);
			return false;
		}
		$this->emit('login_register_save_after');

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
