<?php
include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/lib/lib_cgn_mvc_table.php');

class Cgn_Service_Users_Main extends Cgn_Service {

	function Cgn_Service_Users_Main () {

	}


	function mainEvent(&$sys, &$t) {
		$t['message1'] = '<h1>Users</h1>';
		$t['message2'] = cgn_adminlink("Add new user",'users','main','add');

		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_user');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			$list->data[] = array(
				$db->record['username'],
				$db->record['email'],
				cgn_adminlink('view','users','view','',array('id'=>$db->record['cgn_user_id'])),
				cgn_adminlink('edit','users','edit','',array('id'=>$db->record['cgn_user_id'])),
				cgn_adminlink('delete','users','main','deleteUser',array('id'=>$db->record['cgn_user_id']))
			);
		}
		$list->headers = array('Username','email','View','Edit','Delete');
//		$list->columns = array('title','caption','content');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
		$t['menuPanel']->style['width'] = 'auto';
		$t['menuPanel']->style['border'] = '1px solid black';

	}

	/**
	 * Show which groups a user is in
	 */
	function viewEvent(&$req, &$t) {

	}


	function addEvent(&$req, &$t) {
		$t['form'] = $this->_loadUserForm();
	}


	function deleteUserEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		if ($id == '') {
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('users','main');
		}  else {
			$db = Cgn_Db_Connector::getHandle();
			$db->query("DELETE FROM cgn_user 
			WHERE cgn_user.cgn_user_id = $id LIMIT 1");
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('users','main');
		}
	}


	function saveEvent(&$req, &$t) {
		$user = new Cgn_DataItem('cgn_user');
		$user->username = $req->cleanString('username');
		$user->password = Cgn_User::_hashPassword($req->cleanString('password'));
		$user->email = $req->cleanString('email');
		$user->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'users','main');
	}

	function _loadUserForm($values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('reg');
		$f->action = cgn_adminurl('users','main','save');
		$f->label = 'Add new user';
		$f->appendElement(new Cgn_Form_ElementInput('username'));
		$f->appendElement(new Cgn_Form_ElementPassword('password'));
		$f->appendElement(new Cgn_Form_ElementPassword('password','Repeat'));
		$f->appendElement(new Cgn_Form_ElementInput('email','E-mail'));
		return $f;
	}

}

?>
