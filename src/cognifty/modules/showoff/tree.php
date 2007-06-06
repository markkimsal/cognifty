<?php
include('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include('../cognifty/lib/lib_cgn_mvc.php');
include('../cognifty/lib/lib_cgn_mvc_tree.php');

//include('../cognifty/lib/html_widgets/lib_cgn_panel.php');
//include('../cognifty/lib/html_widgets/lib_cgn_menu.php');

class Cgn_Service_Showoff_Tree extends Cgn_Service {

	function Cgn_Service_Showoff_Tree () {

	}

	function mainEvent(&$req, &$t) {
		$list = new Cgn_Mvc_TreeModel();
		
		$treeItem = new Cgn_Mvc_TreeItem('node #1');
		$list->appendChild($treeItem,null);
		$treeItem2 = new Cgn_Mvc_TreeItem('node #2');
		$list->appendChild($treeItem2,$treeItem);
		$treeItem3 = new Cgn_Mvc_TreeItem('node #3');
		$list->appendChild($treeItem3,$treeItem);
		$treeItem4 = new Cgn_Mvc_TreeItem('node #4');
		$list->appendChild($treeItem4,null);
		$treeItem5 = new Cgn_Mvc_TreeItem('node #5');
		$list->appendChild($treeItem5,$treeItem4);

		for ($q=6; $q < 20; $q++) {
			$treeItemX = new Cgn_Mvc_TreeItem('node #'.$q);
			$list->appendChild($treeItemX,$treeItem4);
			unset($treeItemX);
		}

//		Cgn::debug($treeItem);
//		Cgn::debug($list->itemList);

		$t['treePanel'] = new Cgn_Mvc_TreeView2($list);
		$t['message1'] = 'this is the main event';
		$t['code'] = '<pre>'.htmlentities(file_get_contents('../cognifty/modules/showoff/tree.php')).'</pre>';
	}

	function formatEvent(&$req, &$t) {
		include_once('../cognifty/lib/lib_cgn_active_formatter.php');
		$t['tel'] = '9995550123';
	}

	function aboutEvent(&$sys, &$t) {
		$t['message1'] = 'this is the main event';
	}
}

?>
