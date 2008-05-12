<?php
	 
/**
 * login
 *
 * Handles logins for site.  Also emails
 * lost passwords to users.
 */
class Cgn_Service_Login_Main extends Cgn_Service {

	var $_allowRegister = true;
	var $redirectModule = 'account';

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
			$t['redir'] = $_SERVER['HTTP_REFERER'];
		}
		$t['redir'] = base64_encode($t['redir']);
	}

	function requireLoginEvent(&$req, &$t) {
		//permanent login cookie

		$t['canregister'] = $this->_allowRegister;
		if (! isset($t['redir'])) {
			if (@$req->getvars['loginredir'] != '') {
				$t['redir'] = $req->getvars['loginredir'];
			} else {
				$t['redir'] = $_SERVER['HTTP_REFERER'];
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
			$user->bindSession();
		} else {
//			Cgn_ErrorStack::throwError('No such user found', 501);
			return false;
		}

		$user->addSessionMessage("Login Successful");
		$this->presenter = 'redirect';

		$redir = base64_decode($req->postvars["loginredir"]);
		if (strlen($redir ) < 1) {
			$redir = base64_decode($req->getvars["loginredir"]);
		}

	    if ($redir != '' ) {
			$t['url'] = $redir;
		} else {
			$t['url'] = cgn_appurl($this->redirectModule);
		}
	}

	function logoutEvent(&$req, &$t) {
		$user = Cgn_SystemRequest::getUser();
		$user->unBindSession();
		$this->presenter = 'redirect';
		$t['url'] = cgn_appurl('main');
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
