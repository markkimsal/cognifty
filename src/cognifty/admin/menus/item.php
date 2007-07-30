<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/lib/lib_cgn_mvc_table.php');
include_once('../cognifty/lib/lib_cgn_mvc_tree.php');

include_once('../cognifty/lib/lib_cgn_menu.php');

class Cgn_Service_Menus_Item extends Cgn_Service_AdminCrud {

	function Cgn_Service_Menus_Item () {

	}


	function mainEvent(&$req, &$t) {
		$mid = $req->cleanInt('mid');
		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT * FROM cgn_menu_item 
			WHERE cgn_menu_id = '.$mid.' ORDER BY parent_id,rank,title');

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
				cgn_adminlink($db->record['title'],'menus','item','edit',array('id'=>$db->record['cgn_menu_item_id'], 'mid'=>$db->record['cgn_menu_id'], 't'=>$db->record['type'])),
				array('rank'=>$db->record['rank'],'id'=>$db->record['cgn_menu_item_id']),
				$db->record['url'],
				$db->record['type'],
				cgn_adminlink('delete','menus','item','del',array('cgn_menu_item_id'=>$db->record['cgn_menu_item_id'],'table'=>'cgn_menu_item'))
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

		$t['treeView'] = new Cgn_Mvc_TreeView($list2);
		$sortColumn = new Cgn_Mvc_SortingColumnRenderer();

		$t['treeView']->setColRenderer(1,$sortColumn);

		$t['spacer'] = '<br/>'; 	 
		$t['pagelink'] = cgn_adminlink('Link to Web Page', 'menus','item','edit', array('mid'=>$mid,'t'=>'web')); 	 
		$t['sectionlink'] = cgn_adminlink('Link to Article Section', 'menus','item','edit', array('mid'=>$mid,'t'=>'section'));
		$t['articlelink'] = cgn_adminlink('Link to Article', 'menus','item','edit', array('mid'=>$mid,'t'=>'article'));
		$t['assetlink'] = cgn_adminlink('Link to File Downlaod', 'menus','item','edit', array('mid'=>$mid,'t'=>'asset'));
		$t['blanklink'] = cgn_adminlink('Link Parent', 'menus','item','edit', array('mid'=>$mid,'t'=>'blank'));


/*
		$db = Cgn_DB::getHandle('default');
		$tables = $db->getTables();
		foreach($tables as $table) { 
			$info[$table] = $db->getTableColumns($table);
		}
		print_r($info);
*/
	}


	function saveEvent(&$req, &$t) {
		$itemId   = $req->cleanInt('id');
		$menuId   = $req->cleanInt('mid');
		$type     = $req->cleanString('t');
		$parentId = $req->cleanInt('parent');

		$item = new Cgn_DataItem('cgn_menu_item');
		if ($itemId) {
			$item->load($itemId);
		} else {
			if ($menuId > 0) {
				//only set the menu id on creation
				$item->cgn_menu_id  = $menuId;
				$item->created_on = time();
				//update the rank variable, put this item at the bottom.
				$db = Cgn_Db_Connector::getHandle();
				if ($parentId == 0 ) {
					$rankSql = 'SELECT MAX(rank) AS rank FROM cgn_menu_item
						WHERE cgn_menu_id = '.$menuId.'
						AND parent_id IS NULL';
				} else {
					$rankSql =('SELECT MAX(rank) AS rank FROM cgn_menu_item
						WHERE cgn_menu_id = '.$menuId.'
						AND parent_id = '.$parentId);
				}
				$db->query($rankSql);
				$db->nextRecord();
				$item->rank = ($db->record['rank']+1);	
			}
		}
		if ($parentId > 0 ) {
			$item->parent_id = $parentId;
		}


		$item->title = $req->cleanString('title');
		if ($type == 'web') {
			$item->type  = 'web';
			$item->obj_id  = $req->cleanInt('web');
			$page = new Cgn_DataItem('cgn_web_publish');
			$page->_cols[] = 'link_text';
			$page->load($item->obj_id);
			$item->url  = $page->link_text;
		} 
		if ($type == 'section') {
			$item->type  = 'section';
			$item->obj_id  = $req->cleanInt('section');
			$page = new Cgn_DataItem('cgn_article_section');
			$page->_cols[] = 'link_text';
			$page->load($item->obj_id);
			$item->url  = $page->link_text;
		}
		if ($type == 'article') {
			$item->type  = 'article';
			$item->obj_id  = $req->cleanInt('article');
			$page = new Cgn_DataItem('cgn_article_publish');
			$page->_cols[] = 'link_text';
			$page->load($item->obj_id);
			$item->url  = $page->link_text;
		}
		if ($type == 'asset') {
			$item->type  = 'asset';
			$item->obj_id  = $req->cleanInt('asset');
			$page = new Cgn_DataItem('cgn_asset_publish');
			$page->_cols[] = 'link_text';
			$page->load($item->obj_id);
			$item->url  = $page->link_text;
		}
		if ($type == 'blank') {
			$item->type  = 'blank';
		}

		$item->edited_on = time();
		$item->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'menus','item','',array('mid'=>$menuId));
	}


	function editEvent(&$req, &$t) {
		$menuId = $req->cleanInt('mid');
		$id = $req->cleanInt('id');
		$dataItem = new Cgn_DataItem('cgn_menu_item');
		if ($id > 0 ) {
			$dataItem->load($id);
		}
		$values = $dataItem->valuesAsArray();
		$values['mid'] = $menuId;

		//load all parent level items
		$loader = new Cgn_DataItem('cgn_menu_item');
		$loader->andWhere('cgn_menu_id',$menuId);
		$loader->andWhere('parent_id','NULL', 'IS');
		$loader->andWhere('cgn_menu_item_id',$id, '!=');
		$parentItems = $loader->find();

		$type = $req->cleanString('t');
		if ($type == 'web') {
			//load all pages
			$loader = new Cgn_DataItem('cgn_web_publish');
			$loader->_exclude('content');
			$values['link_type'] = 'web';
			$values['link_name'] = 'Web Page';

			$pages = $loader->find();
			$t['itemForm'] = $this->_linkedMenuItemForm($values, $pages, $parentItems);
		}

		if ($type == 'section') {
			$loader = new Cgn_DataItem('cgn_article_section');
			$loader->_exclude('content');
			$sections = $loader->find();
			$values['link_type'] = 'section';
			$values['link_name'] = 'Section';
			$t['itemForm'] = $this->_linkedMenuItemForm($values, $sections, $parentItems);
		}

		if ($type == 'article') {
			$loader = new Cgn_DataItem('cgn_article_publish');
			$loader->_exclude('content');
			$links = $loader->find();
			$values['link_type'] = 'article';
			$values['link_name'] = 'Article';
			$t['itemForm'] = $this->_linkedMenuItemForm($values, $links, $parentItems);
		}

		if ($type == 'blank') {
			$t['itemForm'] = $this->_blankMenuItemForm($values, $parentItems);
		}
	}


	/*
	function _webMenuItemForm($values=array(), $pages=array(), $parents=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('content_01');
		$f->action = cgn_adminurl('menus','item','save');
		$f->label = 'Menu Item';
		$f->appendElement(new Cgn_Form_ElementInput('title'), $values['title']);
		$page = new Cgn_Form_ElementSelect('page','Page',5, $values['obj_id']);
		foreach ($pages as $pageObj) {
			$page->addChoice($pageObj->title, $pageObj->cgn_web_publish_id);
		}
		$f->appendElement($page);

		$parent = new Cgn_Form_ElementSelect('parent','Parent Item',5, $values['parent_id']);
		foreach ($parents as $parentObj) {
			$parent->addChoice($parentObj->title, $parentObj->cgn_menu_item_id);
		}
		$f->appendElement($parent);

		$f->appendElement(new Cgn_Form_ElementHidden('mid'),$values['mid']);
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_menu_item_id']);
		$f->appendElement(new Cgn_Form_ElementHidden('t'),'web');
		return $f;
	}
	*/


	function _linkedMenuItemForm($values=array(), $links=array(),$parents=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('content_01');
		$f->action = cgn_adminurl('menus','item','save');
		$f->label = 'Menu Item';
		$f->appendElement(new Cgn_Form_ElementInput('title'), $values['title']);
		$select = new Cgn_Form_ElementSelect($values['link_type'],$values['link_name'],5, $values['obj_id']);
		foreach ($links as $linkObj) {
			$select->addChoice($linkObj->title, $linkObj->getPrimaryKey());
		}
		$f->appendElement($select);

		$parent = new Cgn_Form_ElementSelect('parent','Parent Item',5, $values['parent_id']);
		foreach ($parents as $parentObj) {
			$parent->addChoice($parentObj->title, $parentObj->cgn_menu_item_id);
		}
		$f->appendElement($parent);

		$f->appendElement(new Cgn_Form_ElementHidden('mid'),$values['mid']);
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_menu_item_id']);
		$f->appendElement(new Cgn_Form_ElementHidden('t'),$values['link_type']);
		return $f;
	}


	function _blankMenuItemForm($values=array(), $parents=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('content_01');
		$f->action = cgn_adminurl('menus','item','save');
		$f->label = 'Menu Item';
		$f->appendElement(new Cgn_Form_ElementInput('title'), $values['title']);

		$parent = new Cgn_Form_ElementSelect('parent','Parent Item',5, $values['parent_id']);
		foreach ($parents as $parentObj) {
			$parent->addChoice($parentObj->title, $parentObj->cgn_menu_item_id);
		}
		$f->appendElement($parent);

		$f->appendElement(new Cgn_Form_ElementHidden('mid'),$values['mid']);
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_menu_item_id']);
		$f->appendElement(new Cgn_Form_ElementHidden('t'),'blank');
		return $f;
	}


	/**
	 * Use the ID, menu ID and current rank to move an item up in listing.
	 *
	 * This function is overridden to allow for linking of menu IDs and parent IDs
	 *  to get the correct buddy
	 */
	function rankUpEvent($req, &$t) { 
		$mid = $req->cleanInt('mid');
		$id  = $req->cleanInt('id');

		$item = new Cgn_DataItem('cgn_menu_item');
		$item->load($id);
		$item->_nuls[] = 'parent_id';

		//the buddy will either be the item directly above or below
		$buddy = new Cgn_DataItem('cgn_menu_item');
		//moving up... want to find a rank less than this one
		$buddy->andWhere('rank',$item->rank,'<');
		$buddy->andWhere('cgn_menu_id',$item->cgn_menu_id);
		$buddy->andWhere('parent_id',$item->parent_id);
		$buddy->sort('rank','DESC');
//		$buddy->_debugSql = true;
		$buddy->_rsltByPkey = false;

		$buddyList = $buddy->find();
		$buddyItem = $buddyList[0];

		if(! is_object($buddyItem) ) {
			Cgn_ErrorStack::throwError("Object not found", 582);
			return false;
		}
		$buddyItem->_nuls[] = 'parent_id';
		//swap these two ranks
		$buddyRank = $buddyItem->rank;
		$buddyItem->rank = $item->rank;
		$item->rank = $buddyRank;
		$buddyItem->save();
		$item->save();
	}


	/**
	 * Use the ID, menu ID and current rank to move an item down in listing
	 */
	function rankDownEvent($req, &$t) {
		$mid = $req->cleanInt('mid');
		$id  = $req->cleanInt('id');

		$item = new Cgn_DataItem('cgn_menu_item');
		$item->load($id);
	}

}

?>
