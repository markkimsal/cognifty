<?php
Cgn::loadLibrary('Html_Widgets::Lib_Cgn_Widget');
Cgn::loadLibrary('Lib_Cgn_Mvc');
Cgn::loadLibrary('Lib_Cgn_Data_Item');

class Cgn_Service_Showoff_Db extends Cgn_Service {

	function mainEvent(&$sys, &$t) {
		$list = new Cgn_Mvc_ListModel();

		$t['message1'] = '<h3>Database Items</h3>';

		$x = Cgn_Db_Connector::getHandle();
		Cgn_DbWrapper::setHandle($x);
		$user = new Cgn_DataItem('cgn_user');
		$user->_cols = array('username', 'email');
		$user->limit(10);
		$users = $user->find();
		foreach ($users as $_u) {
			if (!$username = $_u->username)
				$username = substr($_u->email, strpos( $_u->email, '@'));
			$list->data[] = $username;
		}
		//$list->data = array('first','second','third');
		$t['listPanel'] = new Cgn_Mvc_ListView($list);

		$thisCode = file_get_contents(__FILE__);
		$t['code'] = '<hr/><pre>'.htmlentities($thisCode).'</pre>';
	}
}
