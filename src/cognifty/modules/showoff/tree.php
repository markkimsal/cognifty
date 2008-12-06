<?php
Cgn::loadLibrary('Html_Widgets::Lib_Cgn_Widget');
Cgn::loadLibrary('Lib_Cgn_Mvc');
Cgn::loadLibrary('Lib_Cgn_Mvc_Tree');

class Cgn_Service_Showoff_Tree extends Cgn_Service {

	function mainEvent(&$req, &$t) {
		$t['title']  = '<h3>Tree Widgets</h3>';
		$t['title'] .= '<p>see the source code below:</p>';
		$list = new Cgn_Mvc_TreeModel();
		
		$treeItem = new Cgn_Mvc_TreeItem('node #1');
		$list->appendChild($treeItem, NULL);
		$treeItem2 = new Cgn_Mvc_TreeItem('node #2');
		$list->appendChild($treeItem2, $treeItem);
		$treeItem3 = new Cgn_Mvc_TreeItem('node #3');
		$list->appendChild($treeItem3, $treeItem);
		$treeItem4 = new Cgn_Mvc_TreeItem('node #4');
		$list->appendChild($treeItem4, NULL);
		$treeItem5 = new Cgn_Mvc_TreeItem('node #5');
		$list->appendChild($treeItem5, $treeItem4);

		for ($q=6; $q < 20; $q++) {
			$treeItemX = new Cgn_Mvc_TreeItem('node #'.$q);
			$list->appendChild($treeItemX,$treeItem4);
			unset($treeItemX);
		}

		$t['treePanel'] = new Cgn_Mvc_TreeView2($list);

		$thisCode = file_get_contents(__FILE__);
		$t['code'] = '<hr/><pre>'.htmlentities($thisCode).'</pre>';
	}
}
