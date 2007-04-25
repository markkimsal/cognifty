<?php
	 
/**
 * login
 *
 * Handles logins for site.  Also emails
 * lost passwords to users.
 */
class Cgn_Service_Login_Main extends Cgn_Service {

	function Cgn_Service_Login_Main() {
	}


	/**
	 * show login box
	 * use this cookie for long term session $_COOKIE['cgn_ltsession'];
	 */
	function mainEvent(&$sys, &$t) {
		//permanent login cookie

		$t['canregister'] = $this->_allowRegister;

		if ($sys->getvars['loginredir'] != '') {
			$t['redir'] = $sys->getvars['loginredir'];
		} else {
			$t['redir'] = $_SERVER['HTTP_REFERER'];
		}
		$t['redir'] = base64_encode($t['redir']);
	}


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
	function loginEvent(&$lcObj, &$lcTemplate) {
		 
		if ($lcObj->postvars['hp'] == 'no') {
			$this->presenter = 'redirect';
			$lcTemplate['url'] = lcurl('login','register','', array('e'=>$lcObj->postvars['email']));
			return;
		}

		$username = $lcObj->postvars["email"];
		$password = $lcObj->postvars["password"];
		$redir = base64_decode($lcObj->postvars["loginredir"]);
		if (strlen($redir ) < 1) {
			$redir = base64_decode($lcObj->getvars["loginredir"]);
		}
	
		/*
		$lcUser =& LcUser::getCurrentUser();
		$lcUser->username = $username;
		$lcUser->password = $password;
		 
		$db = DB::getHandle();
		if (!$lcUser->validateLogin($db)) {
			$lcUser->username = "anonymous";
			$lcTemplate[message] = "There was an error with your username or password.  Please try again.";
			$this->presenter = "errorMessage";
			return;
		} else {
			$lcUser->bindSession();
			//set permanent login cookie
			if ($lcObj->postvars["permanent"] != '' ) {
				global $tail;
				setcookie("LC_LOGIN", $username, time()+7200, $tail, COOKIE_HOST);
			} else {
				global $tail;
				setcookie("LC_LOGIN", '', 0, $tail, COOKIE_HOST);
			}
		}

		if ($lcUser->sessionvars['loginredir'] != '') {
			$this->presenter = 'redirect';
			$lcTemplate['url'] = $lcUser->sessionvars['loginredir'];
		}
		else*/
	       	if ($redir != '' ) {
			$this->presenter = 'redirect';
			$lcTemplate['url'] = $redir;
		} else {
			$this->presenter = 'redirect';
			$lcTemplate['url'] = DEFAULT_URL;
		}
	}
}
?>
