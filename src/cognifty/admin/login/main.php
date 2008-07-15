<?php
	 
/**
 * login
 *
 * Handles logins for site.  Also emails
 * lost passwords to users.
 */
class Cgn_Service_Login_Main extends Cgn_Service_Admin {

	var $requireLogin = false;
	var $templateStyle = 'login';
	var $_allowRegister = true;

	function Cgn_Service_Login_Main() {
	}


	/**
	 * show login box
	 * use this cookie for long term session $_COOKIE['cgn_ltsession'];
	 */
	function mainEvent(&$req, &$t) {
		//permanent login cookie

		$t['canregister'] = $this->_allowRegister;

		if (@$req->getvars['loginredir'] != '') {
			$t['redir'] = $req->getvars['loginredir'];
		} else {
			$t['redir'] = @$_SERVER['HTTP_REFERER'];
		}
		$t['redir'] = base64_encode($t['redir']);

	}


	/**
	 *
	 */
	function loginEvent(&$req, &$t) {
		/*
		$req->cleanVar('');
		$req->cleanPostAs('');
		 */

		$u = &$req->getUser();
		$username = $req->cleanString('email');
		$password = $req->cleanString('password');
		$loginSuccess = $u->login($username,$password);


		if ($loginSuccess) {
			$u->bindSession();
		}
		if ($e = Cgn_ErrorStack::pullError()) {
			$u->addSessionMessage('Not a valid username / password combination.', 'msg_warn');
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('login');
			return;
		}
		if ($req->vars['hp'] == 'no') {
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('login','register','', array('e'=>$req->postvars['email']));
//			echo "redirecting to : ". cgn_adminurl('login','register','', array('e'=>$req->postvars['email']));
			return;
		}

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl('main');
		//echo "redirecting to : ". cgn_appurl('login','register','', array('e'=>$req->postvars['email']));
	}

	/**
	 * End session for the user.
	 */
	function logoutEvent(&$req, &$t) {
		$user = Cgn_SystemRequest::getUser();
		$user->unBindSession();
		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl('main');
	}
}
?>
