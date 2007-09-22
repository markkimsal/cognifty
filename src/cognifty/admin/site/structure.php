<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_tree.php');


class Cgn_Service_Site_Structure extends Cgn_Service_AdminCrud {

	function Cgn_Service_Site_Structure () {

	}


	function mainEvent(&$req, &$t) {


		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT * FROM cgn_menu_item 
			WHERE cgn_menu_id = 1 ORDER BY parent_id,rank,title');



		$list2 = new Cgn_Mvc_TreeModel();
		$list2->headers = array('Title','Order','URL','Type','Delete');
		$list2->columns = array('Title','Order','URL','Type','Delete');
		$parentList = array();

		while($db->nextRecord()) {
			$item = $db->record;
			unset($treeItem);
			$treeItem = null;
			$treeItem = new Cgn_Mvc_TreeItem();
			$treeItem->data = array(
				$db->record['title'],
				array('id'=>$db->record['cgn_menu_item_id']),
				$db->record['url'],
				$db->record['type'],
				cgn_adminlink('delete','menus','item','del',
								array('cgn_menu_item_id'=>$db->record['cgn_menu_item_id'],
								'table'=>'cgn_menu_item',
								'mid'=>$this->menuId))
			);


			//save the tree item in a list of parents for later reference
			if ($item['parent_id'] == 0) {
				$parentList[ $item['cgn_menu_item_id'] ] =& $treeItem;

				//no parent
				$list2->appendChild($treeItem, null);
			} else {
				$itemRef =& $parentList[ $item['parent_id'] ];
				if ($treeItem->_expanded) {
					$itemRef->_expanded = true;
				}
				$list2->appendChild($treeItem, $itemRef);
			}
		}

		$t['treeView'] = new Cgn_Mvc_YuiTreeView($list2);

	}


}

?>
