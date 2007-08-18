<?php
include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/html_widgets/lib_cgn_toolbar.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/lib/lib_cgn_mvc_table.php');

class Cgn_Service_Users_Edit extends Cgn_Service {

	function Cgn_Service_Users_Edit () {

	}


	function mainEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$t['users'] = new Cgn_DataItem('cgn_user','cgn_user_id');
		$t['users']->load($id);
		$values = array();

		if ($id > 0) {
			$db = Cgn_Db_Connector::getHandle();
			$db->query("SELECT * FROM cgn_user
			WHERE cgn_user_id = $id");

			while ($db->nextRecord()) {
				$values = $db->record;
			}
			$t['form01'] = $this->_loadEditUserForm($values);
		}

	}


	function saveUserEditEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		if ($id == '') {
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('users','main');
		}  else if ($req->cleanString('username') == '')  {
		 	$this->presenter = 'redirect';
		 	$t['url'] = cgn_adminurl('users','edit','',array('id'=>$id));
		}  else if ($req->cleanString('email') == '')  {
		 	$this->presenter = 'redirect';
		 	$t['url'] = cgn_adminurl('users','edit','',array('id'=>$id));
		 }  else if ($req->cleanString('password1') != $req->cleanString('password2'))  {
		 	$this->presenter = 'redirect';
		 	$t['url'] = cgn_adminurl('users','edit','',array('id'=>$id));
		}  else {
			$user = new Cgn_DataItem('cgn_user');
			$user->load($id);
			$user->username = $req->cleanString('username');

			if ($req->cleanString('password1') != '') {
				$user->password = Cgn_User::_hashPassword($req->cleanString('password1'));
			}
			$user->email = $req->cleanString('email');
			$user->save();
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('users','main');
		}
	}

	function _loadEditUserForm($values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$id = $values['cgn_user_id'];
		$f = new Cgn_FormAdmin('useredit');
		$f->width = '600px';
		$f->action = cgn_adminurl('users','edit','saveUserEdit');

		$f->label = 'Record  : '.$id.'<br />
			     Userid&nbsp; : '.$values['username'].'<br /><br />
			     <h4>  NOTE : If you leave the passwords blank, they WILL NOT be overwritten.</h4>
			     <h4>  NOTE : *  Indicates a required field. </h4><br />';

		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_user_id']);
		$f->appendElement(new Cgn_Form_ElementInput('username', '* User ID'),$values['username']);
		$f->appendElement(new Cgn_Form_ElementPassword('password1', 'Password'));
		$f->appendElement(new Cgn_Form_ElementPassword('password2','Confirm'));
		$f->appendElement(new Cgn_Form_ElementInput('email', '* Email'),$values['email']);
		return $f;
	}

}

?>

