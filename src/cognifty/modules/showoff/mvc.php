<?php
Cgn::loadLibrary('Html_Widgets::Lib_Cgn_Widget');
Cgn::loadLibrary('Lib_Cgn_Mvc');
Cgn::loadLibrary('Lib_Cgn_Mvc_Table');

class Cgn_Service_Showoff_Mvc extends Cgn_Service {

	function mainEvent(&$req, &$t) {
		$t['title']  = '<h3>Table Widgets</h3>';
		$t['title'] .= '<p>see the source code below:</p>';
		$list = new Cgn_Mvc_TableModel();
		$list->data = array(
			array('Cell #1', 'Cell #2', 'Cell #3'),
			array ('Cell #2-1', 'Cell #2-2', 'Cell #2-3')
		);
		$list->headers = array('Col 1', 'Col 2', 'Col 3');
		$t['tableView'] = new Cgn_Mvc_TableView($list);

		$thisCode = file_get_contents(__FILE__);
		$t['code'] = '<hr/><pre>'.htmlentities($thisCode).'</pre>';
	}
}
