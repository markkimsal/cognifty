<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

class Cgn_Service_Users_Groups extends Cgn_Service {

	function Cgn_Service_Users_Groups () {
		$this->displayName = 'Group Maintenance';
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
		$list->headers = array('Display Name','Group Code','Delete');
//		$list->columns = array('title','caption','content');

		$t['form'] = $this->_loadGroupForm();
		$t['spacer'] = "<br/>\n";

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
		$t['menuPanel']->style['width'] = '454px';
		$t['menuPanel']->style['border'] = '1px solid black';

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
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('reg');
		$f->action = cgn_adminurl('users','groups','save');
		$f->label = 'Add new Group';
		$f->appendElement(new Cgn_Form_ElementInput('display_name', 'Display Name'));
		$f->appendElement(new Cgn_Form_ElementInput('code', 'Group Code'));
		return $f;
	}
}

?>
