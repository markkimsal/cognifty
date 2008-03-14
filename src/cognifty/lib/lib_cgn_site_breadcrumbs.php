<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_tree.php');

class Cgn_Site_BreadCrumbs {

	var $list2 = null; //tree model

	function loadTree() {

		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT * FROM cgn_site_struct 
			ORDER BY parent_id, title');

		$this->list2 = new Cgn_Mvc_TreeModel();
		/*
		$list2->headers = array('Title','Order','URL','Type','Delete');
		$list2->columns = array('Title','Order','URL','Type','Delete');
		 */
		$parentList = array();

		while($db->nextRecord()) {
			$item = $db->record;
			unset($treeItem);
			unset($itemRef);
			$treeItem = null;
			$treeItem = new Cgn_Mvc_TreeItem();
			$treeItem->data = array(
				$db->record['title'],
				cgn_appurl('main','page','' ).$db->record['title'],
				$db->record['node_id'],
				$db->record['node_kind']
			);


			$parentList[ $item['cgn_site_struct_id'] ] =& $treeItem;
			if ($item['cgn_site_struct_id'] == $structId) {
				$treeItem->_expanded = true;
			}
			//save the tree item in a list of parents for later reference
			if ($item['parent_id'] == 0) {
				//no parent
				$this->list2->appendChild($treeItem, null);
			} else {
				$itemRef =& $parentList[ $item['parent_id'] ];
				if ($treeItem->_expanded) {
					$itemRef->_expanded = true;
				}
				$this->list2->appendChild($treeItem, $itemRef);
				unset($itemRef);
			}
		}
	}


	function getTrailForId($nodeId) {
		$list = array();
//		cgn::debug($this->list2);
		foreach ($this->list2->itemList as $idx => $treeNode) {
			if ($treeNode->data[2] == $nodeId) {
				$parentid = $treeNode->_parentPointer;
//				echo "parent id = ".$treeNode->_parentPointer." <br/>";
				while ($parentid != 0) {
					$node = $this->list2->itemList[$parentid];
					$href = $node->data[1];
					array_unshift($list, '<a href="'.$node->data[1].'">'.$node->data[0].'</a>');
					$parentid = $node->_parentPointer;
				}
				break;
			}
		}
		return $list;
	}
}
