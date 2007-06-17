<?php
	 
/**
 * login
 *
 * Handles logins for site.  Also emails
 * lost passwords to users.
 */
class Cgn_Service_Login_Main extends Cgn_Service {

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
			$t['redir'] = $_SERVER['HTTP_REFERER'];
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

		$x = Cgn_Db_Connector::getHandle();
		Cgn_DbWrapper::setHandle($x);
		$user = new Cgn_DataItem('cgn_user');
		$user->_pkey = 'cgn_user_id';
		$user->andWhere('username',$req->postvars['email']);
		$user->andWhere('password',$req->postvars['password']);
		$user->load();
//Cgn::debug($user);
		if ($user->username == $req->postvars['email']) {
			$u = &$req->getUser();
			$u->username = $user->username;
			$u->email = $user->email;
			$u->userId = $user->cgn_user_id;
			$u->bindSession();
		}
//Cgn::debug($u);
		if ($req->vars['hp'] == 'no') {
			$this->presenter = 'redirect';
			$t['url'] = cgn_appurl('login','register','', array('e'=>$req->postvars['email']));
			echo "redirecting to : ". cgn_appurl('login','register','', array('e'=>$req->postvars['email']));
			return;
		}

			echo "redirecting to : ". cgn_appurl('login','register','', array('e'=>$req->postvars['email']));
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
	function loginRun(&$req, &$t) {

		print_r($req);
		die('lksjdf');
		if ($req->vars['hp'] == 'no') {
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
			if ($req->postvars["permanent"] != '' ) {
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
			$t['url'] = $redir;
		} else {
			$this->presenter = 'redirect';
			$t['url'] = DEFAULT_URL;
		}
	}
}
?>
