<?php
include(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

//include(CGN_LIB_PATH.'/html_widgets/lib_cgn_panel.php');
//include(CGN_LIB_PATH.'/html_widgets/lib_cgn_menu.php');

class Cgn_Service_Showoff_Mvc extends Cgn_Service {

	function Cgn_Service_Showoff_Mvc () {

	}

	function mainEvent(&$req, &$t) {
		$t['title'] = '<h3>Table Widgets</h3><p>see the source code below:</p>';
		$list = new Cgn_Mvc_TableModel();
		$list->data = array(
                array('Cell #1', 'Cell #2', 'Cell #3'),
                array ('Cell #2-1', 'Cell #2-2', 'Cell #2-3')
                );

                $list->headers = array('Col 1', 'Col 2', 'Col 3');
		$t['ablePanel'] = new Cgn_Mvc_TableView($list);
		$t['code'] = '<pre>'.htmlentities(file_get_contents(CGN_SYS_PATH.'/modules/showoff/mvc.php')).'</pre>';
	}
}

?>
