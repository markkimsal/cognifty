<?php


class Cgn_LayoutManager {


	function showMainContent($sectionName) {
		if ($sectionName == 'content.main') {
			include('');
		}

		echo "Layout engine parsing content for [$sectionName]";
	}


	function showBox($sectionName) {
		include('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		include('../cognifty/lib/lib_cgn_mvc.php');
		include('../cognifty/lib/lib_cgn_mvc_tree.php');

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

		$view = new Cgn_Mvc_TreeView2($list);
		$view->title = 'Links';
		return $view->toHtml();

		return "Layout engine parsing content for [$sectionName]";
	}
}


?>
