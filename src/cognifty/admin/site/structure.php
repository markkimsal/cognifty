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
		$newStructId = $req->cleanInt('id');
		if ($newStructId !== NULL) {
			Cgn_Session::getSessionObj()->set('site_struct_id', $newStructId);
			$structId = $newStructId;
		}
		if (! isset($structId)) {
			$structId = 0;
		}

		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT * FROM cgn_site_struct 
--			WHERE parent_id = 0  or parent_id <= '.$structId.' 
			ORDER BY parent_id, cgn_site_struct_id, title');


		$list2 = new Cgn_Mvc_TreeModel();
		$list2->headers = array('Title','Order','URL','Type','Delete');
		$list2->columns = array('Title','Order','URL','Type','Delete');
		$parentList = array();

		$homeItem = new Cgn_Mvc_TreeItem();
		$homeItem->data = array(
			'Home Page',
			cgn_adminurl('site','structure','', array('id'=>0)),
			'Home Page',
			'web',
			'');

		$homeItem->_expanded = TRUE;
		$list2->appendChild($homeItem, null);

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
				$list2->appendChild($treeItem, $homeItem);
			} else {
				$itemRef =& $parentList[ $item['parent_id'] ];
//				echo "Adding child because ".$item['parent_id']." != 0 <br/>\n";
				if ($treeItem->_expanded) {
					$itemRef->_expanded = true;
				}
				$list2->appendChild($treeItem, $itemRef);
				unset($itemRef);
			}
		}

		//load the content object that goes with the currently selected structId
		//*
		if ($structId == 0 ) {
			$t['contentTitle'] = 'Home Page';
		} else {
			$struct = new Cgn_DataItem('cgn_site_struct');
			$struct->load($structId);
			$content = new Cgn_DataItem('cgn_content');
			$content->load($struct->node_id);
			$t['contentTitle'] = $content->title;
		}
		// */

//		cgn::debug($list2);
//		exit();
		$t['treeView'] = new Cgn_Mvc_YuiTreeView($list2);
	}

	function addEvent(&$req, &$t) {

		$structId = Cgn_Session::getSessionObj()->get('site_struct_id');
		$structId = intval($structId);

		$content_id = $req->cleanInt('id');

		$content = new Cgn_DataItem('cgn_content');
		$content->load($content_id);

		$struct = new Cgn_DataItem('cgn_site_struct');
		$struct->node_kind = $content->sub_type;
		$struct->title = $content->title;
		$struct->node_id = $content->cgn_content_id;
		$struct->parent_id = $structId;
		$struct->save();

		Cgn_Session::getSessionObj()->set('site_struct_id', $struct->getPrimaryKey());
		$this->redirectHome($t);
	}


	function delEvent(&$req, &$t) {
		$structId = Cgn_Session::getSessionObj()->get('site_struct_id');
		if (! isset($structId)) {
			$structId = 0;
		}

		if ($structId == 0 ) {
			$this->redirectHome($t);
			return;
		}

		$struct = new Cgn_DataItem('cgn_site_struct');
		$struct->load($structId);

		//move child links "up"
		$db = Cgn_Db_Connector::getHandle();
		$db->exec('update cgn_site_struct set parent_id = '.$struct->parent_id.' 
			WHERE parent_id = '.$struct->cgn_site_struct_id);
		$struct->delete();
		$this->redirectHome($t);
	}


	/**
	 * Show a list of content and modules to link into the site structure
	 */
	function browseEvent(&$req, &$t) {
		$loader = new Cgn_DataItem('cgn_content');
		$loader->_excludes[] = 'binary';
		$loader->_excludes[] = 'content';
		$loader->limit(50,0);
		$list = new Cgn_Mvc_TableModel();
		$items = $loader->find();
		foreach ($items as $_item) {
			$vals = $_item->valuesAsArray();
			$list->data[] = array('title'=>cgn_adminlink($vals['title'], 'site', 'structure', 'add', 
										   array('id'=>$vals['cgn_content_id'])),
								  'sub_type'=>$vals['sub_type']);
		}
//		cgn::debug($list->data);
		$list->columns = array('title','sub_type');
		$list->headers = array('Title','Used As');
		$t['table'] = new Cgn_Mvc_TableView($list);
	}

	function debugEvent($req, &$t) {
		$list2 = new Cgn_Mvc_TreeModel();
		$list2->headers = array('Title','Order','URL','Type','Delete');
		$list2->columns = array('Title','Order','URL','Type','Delete');
		$parentList = array();

			unset($treeItem);
			unset($itemRef);
			$treeItem = null;
			$treeItem = new Cgn_Mvc_TreeItem();
			$treeItem->data = array(
				"Hello"
			);

			$treeItem->_expanded = true;
			//no parent
			$list2->appendChild($treeItem, NULL);

			$treeItem2 = null;
			$treeItem2 = new Cgn_Mvc_TreeItem();
			$treeItem2->data = array(
				"Hello 2"
			);
			$treeItem2->_expanded = true;
			$list2->appendChild($treeItem2, $treeItem);


			$treeItem3 = null;
			$treeItem3 = new Cgn_Mvc_TreeItem();
			$treeItem3->data = array(
				"Hello 3"
			);
			$list2->appendChild($treeItem3, NULL);



		cgn::debug($list2);
//		exit();
		$t['treeView'] = new Cgn_Mvc_YuiTreeView($list2);

	}
}
?>
