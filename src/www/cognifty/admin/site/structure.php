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
		$structId = Cgn_Session::getSessionObj()->get('site_struct_id');
		if ($newStructId = $req->cleanInt('id')) {
			Cgn_Session::getSessionObj()->set('site_struct_id', $newStructId);
			$structId = $newStructId;
		}
		if (! isset($structId)) {
			$structId = 0;
		}

		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT * FROM cgn_site_struct 
			WHERE parent_id = 0  or parent_id <= '.$structId.' ORDER BY parent_id, title');


		$list2 = new Cgn_Mvc_TreeModel();
		$list2->headers = array('Title','Order','URL','Type','Delete');
		$list2->columns = array('Title','Order','URL','Type','Delete');
		$parentList = array();

		while($db->nextRecord()) {
			$item = $db->record;
			unset($treeItem);
			unset($itemRef);
			$treeItem = null;
			$treeItem = new Cgn_Mvc_TreeItem();
			$treeItem->data = array(
				$db->record['title'],
				cgn_adminurl('site','structure','', array('id'=>$db->record['cgn_site_struct_id'])),
				$db->record['url'],
				$db->record['type'],
				cgn_adminlink('delete','menus','item','del',
								array('cgn_site_struct_id'=>$db->record['cgn_site_struct_id'],
								'table'=>'cgn_site_struct',
								'mid'=>$this->menuId))
			);


			$parentList[ $item['cgn_site_struct_id'] ] =& $treeItem;
			if ($item['cgn_site_struct_id'] == $structId) {
				$treeItem->_expanded = true;
			}
			//save the tree item in a list of parents for later reference
			if ($item['parent_id'] == 0) {
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
