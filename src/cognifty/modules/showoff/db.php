<?php
include('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include('../cognifty/lib/lib_cgn_mvc.php');

include_once('../cognifty/lib/lib_cgn_data_item.php');


class Cgn_Service_Showoff_Db extends Cgn_Service {

	function Cgn_Service_Showoff_Db () {

	}


	function mainEvent(&$sys, &$t) {
		$list = new Cgn_ListModel();
		$list->data = array('first','second','third');
		$t['listPanel'] = new Cgn_ListView($list);
//		Cgn_Template::assignObject('listPanel',$t['listPanel']);

		$t['message1'] = 'this is the main event';

		$myDb =& Cgn_ObjectStore::getObject("object://defaultDatabaseLayer");
		$x = Cgn_Db_Connector::getHandle();
		Cgn_DbWrapper::setHandle($x);
		$user = new Cgn_DataItem('lcUsers');
		$user->_pkey = 'lc_user_id';
		$user->load(1);

		//$mySession =& Cgn_ObjectStore::getObject("object://defaultSessionLayer");
		//$mySession->set('time',rand(0,50));
	}


	function aboutEvent(&$sys, &$t) {
		$t['message1'] = 'this is the main event';
	}
}

?>
