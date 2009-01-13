<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_tree.php');

class Cgn_Site_BreadCrumbs {

	var $list2 = NULL; //tree model

	function loadTree() {

		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT A.* 
			,B.link_text as web_link_text
			,C.link_text as article_link_text

			FROM cgn_site_struct AS A
			LEFT JOIN cgn_web_publish AS B
				ON A.node_id = B.cgn_content_id
			LEFT JOIN cgn_article_publish AS C
				ON A.node_id = C.cgn_content_id
			ORDER BY A.parent_id, A.title');

		$this->list2 = new Cgn_Mvc_TreeModel();

		$parentList = array();

		while($db->nextRecord()) {
			$item = $db->record;
			unset($treeItem);
			unset($itemRef);
			$treeItem = NULL;
			$treeItem = new Cgn_Mvc_TreeItem();

			$linkText = $db->record['title'];
			if ($db->record['node_kind'] == 'web') {
				$linkText = $db->record['web_link_text'];
			} else if ($db->record['node_kind'] == 'article') {
				$linkText = $db->record['article_link_text'];
			}

			$treeItem->data = array(
				$db->record['title'],
				cgn_appurl('main','page','' ).$linkText,
				$db->record['node_id'],
				$db->record['node_kind']
			);

			$parentList[ $item['cgn_site_struct_id'] ] =& $treeItem;
			/*
			if ($item['cgn_site_struct_id'] == $structId) {
				$treeItem->_expanded = true;
			}
			 */
			//save the tree item in a list of parents for later reference
			if ($item['parent_id'] == 0) {
				//no parent
				$this->list2->appendChild($treeItem, NULL);
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
//			cgn::debug($treeNode);
			if (isset($treeNode->data[2]) && $treeNode->data[2] == $nodeId) {
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
