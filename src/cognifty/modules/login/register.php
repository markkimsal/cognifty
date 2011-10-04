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
 * @emit login_register_form
 */
class Cgn_Service_Login_Register extends Cgn_Service {

	public $_allowRegister = TRUE;
	public $_validateEmail = TRUE;

	public $regUser  = NULL;
	public $regEmail = NULL;
	public $regPw    = NULL;
	public $regPw2   = NULL;
	public $registerAfterUrl = '';

	/**
	 * Checks a global setting for allowing self registration.
	 *
	 * Change this value in your default.ini to show or hide the register 
	 * option on the login page: 
	 * [config]
	 * allow.selfregister=[ true | false ]
	 * allow.emailvalidate=[ true | false ]
	 */
	function init($req, $mod, $srv, $evt) {
		$selfRegisterKey = 'config://default/allow/selfregister';
		if (Cgn_ObjectStore::hasConfig($selfRegisterKey)) {
			$this->_allowRegister = (bool)
				Cgn_ObjectStore::getConfig($selfRegisterKey);
		}

		$validateKey = 'config://default/allow/emailvalidate';
		if (Cgn_ObjectStore::hasConfig($validateKey)) {
			$this->_validateEmail = (bool)
				Cgn_ObjectStore::getConfig($validateKey);
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
		//this is from the sign-in page
		if ($e = $req->cleanString('e')) {
			$values['email']    = $e;
		} else {
			//this is from the form
			$values['email']    = $req->cleanString('email');
		}
		$this->form         = $this->_loadRegForm($values);
		$this->formValues   = $values;
		$this->emit('login_register_form');
		$t['form'] = $this->form;
		unset($this->form);
	}


	/**
	 * save the registration
	 */
	function saveEvent($req, &$t) {
		$u = &$req->getUser();
		if (! $u->isAnonymous() ) {
			$u->addSessionMessage('You cannot register when you are already logged in.');
			$this->redirectHome($t);
			return false;
		}

		$em  = $req->cleanString('email');
		$pw  = $req->cleanString('password');
		$pw2 = $req->cleanString('password2');

		//possible username different from email
		if (!$un = $req->cleanString('username')) {
			$un = $em;
		}

		$u->username = $un;
		$u->email    = $em;

		$u->password = $u->_hashPassword($pw);

		$this->regUser      = $u;
		$this->regUsername  = $un;
		$this->regEmail     = $em;
		$this->regPw        = $pw;
		$this->regPw2       = $pw2;

		//signalResult should be true to continue with registration
		$signalResult = $this->emit('login_register_save_before');

		if ($signalResult === FALSE) {
			if (!Cgn_ErrorStack::hasError('error'))
			Cgn_ErrorStack::throwError('Unknown error with registration.', 506);
			$newTicket = new Cgn_SystemTicket('login', 'register');
			Cgn_SystemRunner::stackTicket($newTicket);
			return false;
		}
		//check basic registration requirements
		if (strlen($pw) < 3) {
			Cgn_ErrorStack::throwError('Password is not long enough.', 506);
			$newTicket = new Cgn_SystemTicket('login', 'register');
			Cgn_SystemRunner::stackTicket($newTicket);
			return false;
		}

		//check basic registration requirements
		if ($pw !== $pw2) {
			Cgn_ErrorStack::throwError('Passwords do not match.', 506);
			$newTicket = new Cgn_SystemTicket('login', 'register');
			Cgn_SystemRunner::stackTicket($newTicket);
			return false;
		}

		if ($this->_validateEmail) {
			$token = md5(rand(1000000, 9999999));
			$u->val_tkn = $token;
		}

		if (!Cgn_User::registerUser($u)) {
			$this->emit('login_register_save_error');
			Cgn_ErrorStack::throwError('That e-mail or username is already taken.  Choose a different e-mail address or username.', 506);
			$newTicket = new Cgn_SystemTicket('login', 'register');
			Cgn_SystemRunner::stackTicket($newTicket);
			return false;
		}

		if ($this->_validateEmail) {
			if (!$this->_sendEmailValidation($token, $em, $u->username)) {
				$this->presenter = 'redirect';
				$t['url'] = cgn_appurl('login', 'register', 'pending');
				$u->addSessionMessage("Sorry, we had problems sending your validation e-mail.  We're working right now to fix this issue.", 'msg_warn');
				return false;
			}
			$this->presenter = 'redirect';
			$t['url'] = cgn_appurl('login', 'register', 'pending');
			return false;
		}


		$this->emit('login_register_save_after');

		if ($u->login($un, $pw)) {
			$u->bindSession();
		}
		$this->presenter = 'redirect';
		$u->addSessionMessage("Congratulations, your account has been registered.");
		if ($this->registerAfterUrl == '') {
			$t['url'] = cgn_appurl('account');
		} else {
			$t['url'] = $this->registerAfterUrl;
		}
	}

	/**
	 * Display templates/register_pending.html.php
	 */
	public function pendingEvent($req, &$t) {
	}

	public function verifyEvent($req, &$t) {
		$tk = $req->cleanString('tk');
		$u = $req->getUser();
		if ($u->active_on > 0 && $tk == NULL) {
			$u->addMessage("Your account is already active." );
			$this->presenter = 'redirect';
			$t['url'] = cgn_appurl('account');
			return TRUE;
		}
		$loader = new Cgn_DataItem('cgn_user');
		$loader->andWhere('id_provider', 'self');
		$loader->andWhere('val_tkn', $tk);
		$loader->_rsltByPkey = false;
		$userList = $loader->find();
		$user = $userList[0];

		if (!is_object($user)) {
			$u->addMessage("There is no account with this validation token.", 'msg_warn');
			return TRUE;
		}
		//clean up
		$user->set('active_on', time());
		$user->set('val_tkn', NULL);
		$user->_nuls[] = 'val_tkn';
		$user->save();

		$this->emit('login_register_save_after');

		$u->password = $user->password;
		$u->username = $user->username;
		$u->userId   = $user->cgn_user_id;
		$u->email    = $user->email;

		$u->loadGroups();
		$u->bindSession();

		$this->presenter = 'redirect';
		$u->addSessionMessage("Congratulations, your account has been verified.");
		$t['url'] = cgn_appurl('account');

	}


	function _loadRegForm($values) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('reg_form');
		$f->action = cgn_appurl('login','register','save', array(), 'https');
		$f->layout = new Cgn_Form_Layout_Dl();
		$f->label = Cgn_Template::siteName().' Registration';
		$f->appendElement(new Cgn_Form_ElementInput('email'),$values['email']);
		$f->appendElement(new Cgn_Form_ElementPassword('password'));
		$f->appendElement(new Cgn_Form_ElementPassword('password2','Confirm Password'));
		$f->appendElement(new Cgn_Form_ElementHidden('event'),'save');
		foreach ($f->elements as $_ele) {
			$_ele->required = true;
		}
		return $f;
	}

	public function _sendEmailValidation($token, $email, $username) {
		if(!Cgn::loadLibrary('Mail::lib_cgn_message_mail')) {
			$errormail = Cgn_ObjectStore::getConfig('config://default/email/errornotify');
			mail($errormail, Cgn_Template::siteName()." - Error sending e-mail validation", "Cannot load Mail::lib_cgn_message_mail.");
			Cgn_ErrorStack::throwError('Error sending e-mail validation', 406);
			return FALSE;
		}
		$mail = new Cgn_Message_Mail();
		$mail->toList[] = $email;
		$mail->subject  = 'Registration verification for '.Cgn_Template::siteName();
		$mail->body     = 'Your '.Cgn_Template::siteName().' account has been created. You must verify your e-mail address before your account can be activated for use.

Click the following link to activate your account
'.cgn_sappurl('login', 'register', 'verify', array('tk'=>$token)).'

Note: If the link above does not work, copy and paste the link into your browser.

For your records:
Your user name is: '.$username;

		$mail->from = Cgn_ObjectStore::getConfig('config://default/email/defaultfrom');
		$mail->sendMail();
		return TRUE;
	}
}
