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
		$t['canregister'] = $this->_allowRegister;
		$t['form'] = $this->_loadRegForm();
	}


	function _loadRegForm() {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('reg');
		$f->label = 'Site Registration';
		$f->appendElement(new Cgn_Form_ElementInput('email'));
		$f->appendElement(new Cgn_Form_ElementPassword('password'));
		$f->appendElement(new Cgn_Form_ElementPassword('password2','Confirm Password'));
		return $f;
	}
}
?>
