<?php
include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');

class Cgn_Service_Users_Groups extends Cgn_Service {

	function Cgn_Service_Users_Groups () {

	}


	function mainEvent(&$sys, &$t) {
		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_group');

		$list = new Cgn_Mvc_TableModel();
		//cut up the data into table data
		while ($db->nextRecord()) {
			$list->data[] = array(
				$db->record['display_name'],
				$db->record['code'],
				cgn_adminlink('Delete','*','','',array('id'=>$db->record['cgn_user_id']))
			);
		}
		$list->headers = array('Username','email','View');
//		$list->columns = array('title','caption','content');


		$t['menuPanel'] = new Cgn_Mvc_TableView($list);
		$t['menuPanel']->style['width'] = 'auto';
		$t['menuPanel']->style['border'] = '1px solid black';

		$t['form'] = $this->_loadGroupForm();
	}

	function saveEvent(&$req, &$t) {
		$user = new Cgn_DataItem('cgn_group');
		$user->display_name = $req->cleanString('display_name');
		$user->code = $req->cleanString('code');
		$user->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'users','groups');
	}

	function _loadGroupForm($values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('reg');
		$f->action = cgn_adminurl('users','groups','save');
		$f->label = 'Add new group';
		$f->appendElement(new Cgn_Form_ElementInput('display_name', 'Display Name'));
		$f->appendElement(new Cgn_Form_ElementInput('code', 'Group Code'));
		return $f;
	}
}

?>