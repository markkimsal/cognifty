<?php
include(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include(CGN_LIB_PATH.'/lib_cgn_mvc.php');

include(CGN_LIB_PATH.'/html_widgets/lib_cgn_panel.php');
include(CGN_LIB_PATH.'/html_widgets/lib_cgn_menu.php');

class Cgn_Service_Showoff_Main extends Cgn_Service {

	function Cgn_Service_Showoff_Main () {

	}

	function mainEvent(&$req, &$t) {
		$list = new Cgn_Mvc_ListModel();
		$list->data = array(
			0=> array('link 1','foobar.php'),
			1=> array('link 2','foobar.php'),
			2=> array('link 3','foobar.php')
		);

//		$t['listPanel'] = new Cgn_ListView($list);

		$t['menuPanel'] = new Cgn_HtmlWidget_Menu('Sample Menu',$list);
		$t['menuPanel']->style['width'] = 'auto';
		$t['menuPanel']->style['border'] = '1px solid black';

		$t['message1'] = 'this is the main event';
		$t['code'] = '<pre>'.htmlentities(file_get_contents(CGN_SYS_PATH.'/modules/showoff/main.php')).'</pre>';
	}

	function formatEvent(&$req, &$t) {
		include_once(CGN_LIB_PATH.'/lib_cgn_active_formatter.php');
		$t['tel'] = '9995550123';
	}

	function aboutEvent(&$sys, &$t) {
		$t['message1'] = 'this is the main event';
	}
}

?>
