<?php

/**
 * login
 *
 * Handles logins for site.  Also emails
 * lost passwords to users.
 *
 * @emit login_success_after
 * @emit logout_success_after
 */
class Cgn_Service_Login_Main extends Cgn_Service {

	public $redirectModule     = 'account';
	public $redirectService    = '';
	public $redirectUrlLogout  = '';
	public $redirectUrlLogin   = '';

	/**
	 * @See Cgn_Service_Login_Main::init()
	 */
	public $_allowRegister = TRUE;


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
		$loginRedirectKey = 'config://default/login/redirect/module';
		if (Cgn_ObjectStore::hasConfig($loginRedirectKey)) {
			$this->redirectModule = 
				Cgn_ObjectStore::getConfig($loginRedirectKey);
		}
		$loginRedirectKey = 'config://default/login/redirect/service';
		if (Cgn_ObjectStore::hasConfig($loginRedirectKey)) {
			$this->redirectService = 
				Cgn_ObjectStore::getConfig($loginRedirectKey);
		}
		$loginRedirectKey = 'config://default/login/redirect/loginafter';
		if (Cgn_ObjectStore::hasConfig($loginRedirectKey)) {
			$this->redirectUrlLogin = 
				Cgn_ObjectStore::getConfig($loginRedirectKey);
		}


		return parent::init($req, $mod, $srv, $evt);
	}

	/**
	 * Show login box on main_main.html.php
	 * use this cookie for long term session $_COOKIE['cgn_ltsession'];
	 *
	 * Redirects after a good login to any "loginredir" GET/POST params
	 * otherwise redirects to $this->redirectModule.
	 */
	function mainEvent(&$req, &$t) {
		//permanent login cookie
		
		//@see Cgn_Service_Login_Main::init()
		$t['canregister'] = $this->_allowRegister;


		//if a module wants the login back, it should set loginredir
		//  otherwise, the "requireLoginEvent" should be the method
		//  to automatically try to kick back to the right place.
		if ($req->cleanString('loginredir') != '') {
			$t['redir'] = $req->cleanString('loginredir');
		}

		if (isset($t['redir'])) {
			$t['redir'] = base64_encode($t['redir']);
		} else {
			$t['redir'] = '';
		}

		$u = $req->getUser();
		$clear = $req->cleanString('clear');
		if(! $u->isAnonymous() && $clear !== 'y') {
			$t['username'] = $u->getUsername();
		}
	}

	/**
	 * set the redirect back to the http referrer or 
	 * the current page based on PHP_SELF
	 */
	function requireLoginEvent(&$req, &$t) {
		//permanent login cookie

		//@see Cgn_Service_Login_Main::init()
		$t['canregister'] = $this->_allowRegister;
		if (! isset($t['redir'])) {
			if (@$req->getvars['loginredir'] != '') {
				$t['redir'] = $req->getvars['loginredir'];
			} else if (isset($_SERVER['HTTP_REFERER'])) {
				$t['redir'] = $_SERVER['HTTP_REFERER'];
			} else {
				$t['redir'] = $_SERVER['PHP_SELF'];
			}
			$t['redir'] = base64_encode($t['redir']);
		}
	}

	/**
	 *
	 */
	function loginEvent(&$req, &$t) {

		if ($req->vars['hp'] == 'no' && $req->vars['password']==='') {
			$this->presenter = 'redirect';
			$t['url'] = cgn_appurl('login','register','', array('e'=>$req->postvars['email']));
//			echo "redirecting to : ". cgn_appurl('login','register','', array('e'=>$req->postvars['email']));
			return;
		}

		$user = Cgn_SystemRequest::getUser();
		if ($user->login($req->cleanString('email'),
				$req->cleanString('password'))) {
			$this->user = $user;
			$this->emit('login_success_after');
			unset($this->user);
		} else {
			//grab the error
			$e = Cgn_ErrorStack::pullError();
			$req->getUser()->addMessage('There was a problem with your login information.', 'msg_warn');
			$t['username'] = $req->cleanString('email');
			$this->templateName = 'main_main';
//			Cgn_ErrorStack::throwError('No such user found', 501);
			$redir = base64_decode($req->cleanString("loginredir"));
			if (strlen($redir ) > 0) {
				$t['redir'] = $req->cleanString("loginredir");
			}
			return TRUE;
		}

		$user->addSessionMessage("Sign-in Successful");
		$this->presenter = 'redirect';

		$redir = base64_decode($req->cleanString("loginredir"));

		if ($redir != '' ) {
			$t['url'] = $redir;
		} else {
			if ($this->redirectUrlLogin != '') {
				$t['url'] = $this->redirectUrlLogin;
			} else {
				$t['url'] = cgn_appurl($this->redirectModule, $this->redirectService);
			}
		}
	}

	function logoutEvent(&$req, &$t) {
		$user = Cgn_SystemRequest::getUser();
		if ($user->isAnonymous()) {
			$user->endSession();
		} else {
			$user->unBindSession();
			$user->endSession();
		}
		$this->user = $user;
		$signalResult = $this->emit('logout_success_after');
		unset($this->user);

		$this->presenter = 'redirect';
		//accpet URLs from slots
		if ($this->redirectUrlLogout  != '') {
			$t['url'] = $this->redirectUrlLogout;
		} else {
			//don't redirect to "main", or else we go back to main after a sign in.
			// main is no different than the home page, so we want to go to accounts
			// after a good login, not back to the home page.
			$t['url'] = cgn_url();
		}
	}

	// EVERYTHING BELOW HERE IS DEPRECATED

	/**
	 * email lost password to user
	 */
	function lostRun(&$lcObj, &$lcTemplate) {
		$email = trim($lcObj->postvars["email"]);

		$db = DB::getHandle();
		$db->query("select * from lcUsers where email = '$email'");
		if ($db->next_record()) {
			$message = "Your username is ".$db->Record[username]." and your password is ".$db->Record[password];
			mail($email, "Your account information for ".SITE_NAME, $message, "From: ".WEBMASTER_EMAIL);
			$lcTemplate["status"] = "Your password has been sent to $email.";
		} else {
			$lcTemplate["status"] = "We have no account on file with the email address $email.";
		}

		$lcObj->templateName = "loginlost";
		$lcTemplate["title"] = "Lost password";
	}

	/**
	 * If user selected "no don't have a password", redirect to registration.
	 * If they put in a password, check for validity.
	 */
	/*
	function loginRun(&$req, &$t) {
		if ($req->vars['hp'] == 'no' && $req->vars['password']==='') {
			$this->presenter = 'redirect';
			$t['url'] = cgn_curl('login','register','', array('e'=>$req->postvars['email']));
			return;
		}

		$username = $req->postvars["email"];
		$password = $req->postvars["password"];
		$redir = base64_decode($req->postvars["loginredir"]);
		if (strlen($redir ) < 1) {
			$redir = base64_decode($req->getvars["loginredir"]);
		}

	    if ($redir != '' ) {
			$this->presenter = 'redirect';
			$t['url'] = $redir;
		} else {
			$this->presenter = 'redirect';
			$t['url'] = cgn_appurl($this->redirectModule);//DEFAULT_URL;
		}
	}
	 */
}
?>
