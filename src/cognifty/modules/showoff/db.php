<?php
include(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include(CGN_LIB_PATH.'/lib_cgn_mvc.php');

include_once(CGN_LIB_PATH.'/lib_cgn_data_item.php');


class Cgn_Service_Showoff_Db extends Cgn_Service {

	function Cgn_Service_Showoff_Db () {

	}

	function mainEvent(&$sys, &$t) {
		$list = new Cgn_Mvc_ListModel();

		$t['message1'] = '<h3>Database Items</h3>';

		$x = Cgn_Db_Connector::getHandle();
		Cgn_DbWrapper::setHandle($x);
		$user = new Cgn_DataItem('cgn_user');
		$user->_cols = array('username');
		$user->limit(10);
		$users = $user->find();
		foreach ($users as $_u) {
			$list->data[] = $_u->username;
		}
		//$list->data = array('first','second','third');
		$t['listPanel'] = new Cgn_Mvc_ListView($list);
	}

	function aboutEvent(&$sys, &$t) {
		$t['message1'] = 'this is the main event';
	}
}

?>
