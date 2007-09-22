<?php
	 
/**
 * login
 *
 * Handles logins for site.  Also emails
 * lost passwords to users.
 */
class Cgn_Service_Login_Register extends Cgn_Service {

	function Cgn_Service_Login_Register() {
	}


	/**
	 * show login box
	 * use this cookie for long term session $_COOKIE['cgn_ltsession'];
	 */
	function mainEvent(&$req, &$t) {
		$values = array();
		$values['email'] = $req->getvars['e'];

//		$t['canregister'] = $this->_allowRegister;
		$t['form'] = $this->_loadRegForm($values);
	}


	/**
	 * save the registration
	 */
	function saveEvent(&$req, &$t) {
		$u = &$req->getUser();
		if (! $u->isAnonymous() ) {
			return false;
		}
		$em = cgn_cleanEmail($req->postvars['email']);
		$pw = cgn_cleanPassword($req->postvars['password']);
		$u->username = $em;
		$u->email    = $em;
		$u->password = $pw;

		if (!Cgn_User::registerUser($u)) {
			echo "user already exists";
			return false;
		}
	}


	function _loadRegForm($values) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('reg');
		$f->action = cgn_appurl('login','register','save');
		$f->label = 'Site Registration';
		$f->appendElement(new Cgn_Form_ElementInput('email'),$values['email']);
		$f->appendElement(new Cgn_Form_ElementPassword('password'));
		$f->appendElement(new Cgn_Form_ElementPassword('password2','Confirm Password'));
		$f->appendElement(new Cgn_Form_ElementHidden('event'),'save');
		return $f;
	}
}
?>
