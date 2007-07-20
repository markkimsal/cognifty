<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/lib/lib_cgn_mvc_table.php');
include_once('../cognifty/lib/lib_cgn_mvc_tree.php');

include_once('../cognifty/lib/lib_cgn_menu.php');

class Cgn_Service_Menus_Item extends Cgn_Service_Admin {

	function Cgn_Service_Menus_Item () {

	}


	function mainEvent(&$req, &$t) {
		$mid = $req->cleanInt('mid');
		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT * FROM cgn_menu_item 
			WHERE cgn_menu_id = '.$mid.' ORDER BY parent_id,title');

			/*
		$m  = new Cgn_Menu(0);
		$m->load('main.menu');
		*/

		$list2 = new Cgn_Mvc_TreeModel();
		$list2->headers = array('Title','URL','Type','Delete');
		$list2->columns = array('Title','URL','Type','Delete');
		$parentList = array();

		while($db->nextRecord()) {
			$item = $db->record;
			unset($treeItem);
			$treeItem = null;
			$treeItem = new Cgn_Mvc_TreeItem();
			$treeItem->data = array(
				cgn_adminlink($db->record['title'],'menus','item','edit',array('id'=>$db->record['cgn_menu_item_id'], 'mid'=>$db->record['cgn_menu_id'], 't'=>$db->record['type'])),
				$db->record['url'],
				$db->record['type'],
				cgn_adminlink('delete','menus','main','delete',array('id'=>$db->record['cgn_menu_id']))
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
		$t['spacer'] = '<br/>'; 	 
		$t['pagelink'] = cgn_adminlink('Link to Web Page', 'menus','item','edit', array('mid'=>$mid,'t'=>'web')); 	 
		$t['sectionlink'] = cgn_adminlink('Link to Article Section', 'menus','item','edit', array('mid'=>$mid,'t'=>'section'));
		$t['articlelink'] = cgn_adminlink('Link to Article', 'menus','item','edit', array('mid'=>$mid,'t'=>'article'));
		$t['assetlink'] = cgn_adminlink('Link to File Downlaod', 'menus','item','edit', array('mid'=>$mid,'t'=>'asset'));
		$t['blanklink'] = cgn_adminlink('Link Parent', 'menus','item','edit', array('mid'=>$mid,'t'=>'asset'));


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
			}
		}
		$item->title = $req->cleanString('title');
		if ($type == 'web') {
			$item->type  = 'web';
			$item->web_id  = $req->cleanInt('page');
			$page = new Cgn_DataItem('cgn_web_publish');
			$page->_cols[] = 'link_text';
			$page->load($item->web_id);
			$item->url  = $page->link_text;
		} 
		if ($type == 'section') {
			$item->type  = 'section';
			$item->section_id  = $req->cleanInt('section');
			$page = new Cgn_DataItem('cgn_article_section');
			$page->_cols[] = 'link_text';
			$page->load($item->section_id);
			$item->url  = $page->link_text;
		}
		if ($type == 'article') {
			$item->type  = 'article';
			$item->article_id  = $req->cleanInt('article');
			$page = new Cgn_DataItem('cgn_article_publish');
			$page->_cols[] = 'link_text';
			$page->load($item->article_id);
			$item->url  = $page->link_text;
		}
		if ($type == 'asset') {
			$item->type  = 'asset';
			$item->asset_id  = $req->cleanInt('asset');
			$page = new Cgn_DataItem('cgn_asset_publish');
			$page->_cols[] = 'link_text';
			$page->load($item->asset_id);
			$item->url  = $page->link_text;
		}
		if ($type == 'blank') {
			$item->type  = 'blank';
			$item->url  = '#';
		}

		if ($parentId > 0 ) {
			$item->parent_id = $parentId;
		}

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
		$loader->andWhere('parent_id','0');
		$loader->orWhere('parent_id','NULL', 'IS');
		$parentItems = $loader->find();

		$type = $req->cleanString('t');
		if ($type == 'web') {
			//load all pages
			$loader = new Cgn_DataItem('cgn_web_publish');
			$loader->_exclude('content');
			$pages = $loader->find();
			$t['itemForm'] = $this->_webMenuItemForm($values, $pages, $parentItems);
		}

		if ($type == 'section') {
			$loader = new Cgn_DataItem('cgn_article_section');
//			$loader->_exclude('content');
			$sections = $loader->find();
			$values['link_type'] = 'section';
			$values['link_name'] = 'Section';
			$t['itemForm'] = $this->_linkedMenuItemForm($values, $sections, $parentItems);
		}

		if ($type == 'article') {
			$loader = new Cgn_DataItem('cgn_article_publish');
//			$loader->_exclude('content');
			$links = $loader->find();
			$values['link_type'] = 'article';
			$values['link_name'] = 'Article';
			$t['itemForm'] = $this->_linkedMenuItemForm($values, $links, $parentItems);
		}
	}


	function _webMenuItemForm($values=array(), $pages=array(), $parents=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('content_01');
		$f->action = cgn_adminurl('menus','item','save');
		$f->label = 'Menu Item';
		$f->appendElement(new Cgn_Form_ElementInput('title'), $values['title']);
		$page = new Cgn_Form_ElementSelect('page','Page',5, $values['web_id']);
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


	function _linkedMenuItemForm($values=array(), $links=array(),$parents=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('content_01');
		$f->action = cgn_adminurl('menus','item','save');
		$f->label = 'Menu Item';
		$f->appendElement(new Cgn_Form_ElementInput('title'), $values['title']);
		$select = new Cgn_Form_ElementSelect($values['link_type'],$values['link_name'],5, $values['section_id']);
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

}

?>
