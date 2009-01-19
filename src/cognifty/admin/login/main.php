<?php
	 
/**
 * login
 *
 * Handles logins for site.  Also emails
 * lost passwords to users.
 */
class Cgn_Service_Login_Main extends Cgn_Service_Admin {

	var $requireLogin = FALSE;
	var $templateStyle = 'login';
	var $_allowRegister = FALSE;

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
		} else if (isset($_SERVER['HTTP_REFERER']) && !strpos($_SERVER['HTTP_REFERER'], 'login')) {
			$t['redir'] = $_SERVER['HTTP_REFERER'];
			$t['redir'] = base64_encode($t['redir']);
		} else {
			$t['redir'] = $_SERVER['PHP_SELF'];
			$t['redir'] = base64_encode($t['redir']);
		}
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
			$t['url'] = cgn_adminurl('login', '', '', array('loginredir'=>$req->cleanString('loginredir')));
			return;
		}
		if ($req->vars['hp'] == 'no') {
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('login','register','', array('e'=>$req->postvars['email']));
//			echo "redirecting to : ". cgn_adminurl('login','register','', array('e'=>$req->postvars['email']));
			return;
		}

		$this->presenter = 'redirect';

		$redir = base64_decode($req->cleanString("loginredir"));
		if (strlen($redir ) < 1) {
			$redir = base64_decode($req->cleanString("loginredir"));
		}

		if ($redir != '' && !strpos($redir, 'login') ) {
			$t['url'] = $redir;
		} else {
			$t['url'] = cgn_adminurl('main');
		}
	}

	/**
	 * End session for the user.
	 */
	function logoutEvent($req, &$t) {
		$user = Cgn_SystemRequest::getUser();
		$user->unBindSession();
		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl('main');
	}


	/**
	 * Streamline login events
	 */
	function requireLoginEvent($req, &$t) {
		$t['canregister'] = $this->_allowRegister;
		if (! isset($t['redir'])) {
			if (@$req->getvars['loginredir'] != '') {
				$t['redir'] = $req->getvars['loginredir'];
			} else {
				$t['redir'] = $_SERVER['REQUEST_URI'];
			}
			$t['redir'] = base64_encode($t['redir']);
		}
	}
}
?>
