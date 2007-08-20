<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/lib/lib_cgn_mvc_table.php');
include_once('../cognifty/lib/lib_cgn_mvc_tree.php');
include_once('../cognifty/lib/html_widgets/lib_cgn_toolbar.php');
include_once('../cognifty/lib/lib_cgn_menu.php');

class Cgn_Service_Menus_Item extends Cgn_Service_AdminCrud {

	var $menuId  = -1;

	function Cgn_Service_Menus_Item () {
	}

	function init ($req) {
		$this->menuId = $req->cleanInt('mid');
//		print_r($this->menuId);exit();
	}

	function getHomeUrl() {
		return cgn_adminurl('menus','item','',array('mid'=>$this->menuId));
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

		$t['headerline'] = '<h3>Menu Item Maintenance</h3>';

		$t['treeView'] = new Cgn_Mvc_TreeView($list2);
		$sortColumn = new Cgn_Mvc_SortingColumnRenderer();

		
		$t['treeView']->setColRenderer(1,$sortColumn);

//		$t['pagelink'] = cgn_adminlink('Link to a Web Page', 'menus','item','edit', array('mid'=>$mid,'t'=>'web')); 	 
//		$t['sectionlink'] = cgn_adminlink('Link to an Article Section', 'menus','item','edit', array('mid'=>$mid,'t'=>'section'));
//		$t['articlelink'] = cgn_adminlink('Link to an Article', 'menus','item','edit', array('mid'=>$mid,'t'=>'article'));
//		$t['assetlink'] = cgn_adminlink('Link to an Asset', 'menus','item','edit', array('mid'=>$mid,'t'=>'asset'));
//		$t['modulelink'] = cgn_adminlink('Link to a Site Module', 'menus','item','edit', array('mid'=>$mid,'t'=>'local'));
//		$t['externlink'] = cgn_adminlink('Link to an External URL', 'menus','item','edit', array('mid'=>$mid,'t'=>'extern'));
//		$t['blanklink'] = cgn_adminlink('Create a Link Parent', 'menus','item','edit', array('mid'=>$mid,'t'=>'blank'));
//
		$t['footerline01'] = '<br /><h3>Create a new Menu Item</h3><br />';
		$t['footerline01'] .= 'The buttons on the toolbars below will create new menu items.<br />
			 Press the button for the appropriate link type you need to add to this menu group.<br />';

		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('menus','item','edit', array('mid'=>$mid,'t'=>'web')),"Web Page");
		$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('menus','item','edit', array('mid'=>$mid,'t'=>'section')),"Article Section");
		$btn3 = new Cgn_HtmlWidget_Button(cgn_adminurl('menus','item','edit', array('mid'=>$mid,'t'=>'article')),"Article");
		$btn4 = new Cgn_HtmlWidget_Button(cgn_adminurl('menus','item','edit', array('mid'=>$mid,'t'=>'asset')),"Asset");
		$btn5 = new Cgn_HtmlWidget_Button(cgn_adminurl('menus','item','edit', array('mid'=>$mid,'t'=>'local')),"Module");
		$btn6 = new Cgn_HtmlWidget_Button(cgn_adminurl('menus','item','edit', array('mid'=>$mid,'t'=>'extern')),"External URL");
		$btn7 = new Cgn_HtmlWidget_Button(cgn_adminurl('menus','item','edit', array('mid'=>$mid,'t'=>'blank')),"Parent");

		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$t['toolbar']->addButton($btn1);
		$t['toolbar']->addButton($btn2);
		$t['toolbar']->addButton($btn3);
		$t['toolbar']->addButton($btn4);
		$t['toolbar']->addButton($btn5);
		$t['toolbar']->addButton($btn6);
		$t['toolbar']->addButton($btn7);

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
			$page = new Cgn_DataItem('cgn_file_publish');
			$page->_cols[] = 'link_text';
			$page->load($item->obj_id);
			$item->url  = $page->link_text;
		}
		if ($type == 'local' || $type == 'extern') {
			$item->type  = $type;
			$item->url  = $req->cleanString('url');
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
		$loader->_sort['title'] = 'ASC';
		$parentItems = $loader->find();

		$type = $req->cleanString('t');
		if ($type == 'web') {
			//load all pages
			$loader = new Cgn_DataItem('cgn_web_publish');
			$loader->_exclude('content');
			$loader->_sort['title'] = 'ASC';

			$values['link_type'] = 'web';
			$values['link_name'] = 'Web Page';
			$values['menuTitle'] = 'Link to a Web Page'; 
			$values['menuWidth'] = '600px';
			$values['textline_01'] = '<br />Select a Web Page to link to below.';
			$values['textline_02'] = '<br />You may also associate a Parent Link below:';
			$pages = $loader->find();
			$t['itemForm'] = $this->_linkedMenuItemForm($values, $pages, $parentItems);
		}

		if ($type == 'section') {
			$loader = new Cgn_DataItem('cgn_article_section');
			$loader->_exclude('content');
			$loader->_sort['title'] = 'ASC';
			$sections = $loader->find();
			$values['link_type'] = 'section';
			$values['link_name'] = 'Section';
			$values['menuTitle'] = 'Link to an Article Section'; 
			$values['menuWidth'] = '600px';
			$values['textline_01'] = '<br />Select a Section to link to below.';
			$values['textline_02'] = '<br />You may also associate a Parent Link below:';
			$t['itemForm'] = $this->_linkedMenuItemForm($values, $sections, $parentItems);
		}

		if ($type == 'article') {
			$loader = new Cgn_DataItem('cgn_article_publish');
			$loader->_exclude('content');
			$loader->_sort['title'] = 'ASC';
			$links = $loader->find();
			$values['link_type'] = 'article';
			$values['link_name'] = 'Article';
			$values['menuTitle'] = 'Link to an Article'; 
			$values['menuWidth'] = '800px';
			$values['textline_01'] = '<br />Select an Article to link to below.';
			$values['textline_02'] = '<br />You may also associate a Parent Link below:';
			$t['itemForm'] = $this->_linkedMenuItemForm($values, $links, $parentItems);
		}

		if ($type == 'asset') {
			$loader = new Cgn_DataItem('cgn_file_publish');
			$loader->_exclude('content');
			$loader->_exclude('binary');
			$loader->_sort['title'] = 'ASC';
			$links = $loader->find();
			$values['link_type'] = 'asset';
			$values['link_name'] = 'Asset';
			$values['menuTitle'] = 'Link to an Asset'; 
			$values['menuWidth'] = '600px';
			$values['textline_01'] = '<br />Select an Asset to link to below.';
			$values['textline_02'] = '<br />You may also associate a Parent Link below:';
			$t['itemForm'] = $this->_linkedMenuItemForm($values, $links, $parentItems);
		}

		if ($type == 'local') {
			$values['local'] = true;
			$values['link_type'] = $type;
			$values['menuTitle'] = 'Link to a Site Module'; 
			$values['menuWidth'] = '600px';
			$values['textline_01'] = 'In order to link to a module, you must install the
				folder containing the files in the<br />
				 ../cognifty/modules directory first.';
			$values['textline_02'] = '<br />Example Module: index.php/distributors.main/';
			$t['itemForm'] = $this->_localMenuItemForm($values, $parentItems);
		}

		if ($type == 'extern') {
			$values['local'] = false;
			$values['link_type'] = $type;
			$values['menuTitle'] = 'Link to an External URL'; 
			$values['menuWidth'] = '600px';
			$values['textline_01'] = 'This tool allows you to create a link to an external URL.
				<br />Be sure to enter a complete URL.';
			$values['textline_02'] = '<br />Example: http://www.somewhere.com';
			$t['itemForm'] = $this->_localMenuItemForm($values, $parentItems);
		}

		if ($type == 'blank') {
			$values['menuTitle'] = 'Create a Link Parent' ;
			$values['menuWidth'] = '600px';
			$values['parenttext_01'] = 'This tool will create a new "Top-Level" Menu Item.';
			$values['parenttext_02'] = '<span style="font-weight:bold;">
				Listed below are the current Parents:</span>';
			$t['itemForm'] = $this->_blankMenuItemForm($values, $parentItems);
		}
	}


	function _linkedMenuItemForm($values=array(), $links=array(),$parents=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('content_01');
		$f->action = cgn_adminurl('menus','item','save');
		if ($values['menuWidth'] != '') {
			$f->width = $values['menuWidth'];
		} else {
			$f->width = "auto"; 
		}
		$f->label = $values['menuTitle'];
		$f->appendElement(new Cgn_Form_ElementInput('title','Link Title: '), $values['title']);
		if($values['textline_01'] != '') {
			$f->appendElement(new Cgn_Form_ElementContentLine(), $values['textline_01']);
		}

		$select = new Cgn_Form_ElementSelect($values['link_type'],$values['link_name'].'s:',5, $values['obj_id']);
		foreach ($links as $linkObj) {
			$select->addChoice($linkObj->title, $linkObj->getPrimaryKey());
		}
		$f->appendElement($select);

		if($values['textline_02'] != '') {
			$f->appendElement(new Cgn_Form_ElementContentLine(), $values['textline_02']);
		}
		$parent = new Cgn_Form_ElementSelect('parent','Parent Links:<br />(Optional)',5, $values['parent_id']);
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
		$f = new Cgn_FormAdmin('parentitem_01');
		$f->action = cgn_adminurl('menus','item','save');
		if ($values['menuWidth'] != '') {
			$f->width = $values['menuWidth'];
		} else {
			$f->width = "auto"; 
		}
		$f->label = $values['menuTitle'];
		$f->formHeader = $values['parenttext_01'];
		$f->appendElement(new Cgn_Form_ElementInput('title','New Link: '), $values['title']);
		$f->appendElement(new Cgn_Form_ElementContentLine(), $values['parenttext_02']);
	//	$parent = new Cgn_Form_ElementSelect('parent','Parents: <br />(Optional)',5, $values['parent_id']);
	//	foreach ($parents as $parentObj) {
	//		$parent->addChoice($parentObj->title, $parentObj->cgn_menu_item_id);
	//	}
	//	$f->appendElement($parent);
		$f->appendElement(new Cgn_Form_ElementContentLine(), $parentObj->title);
		foreach ($parents as $parentObj) {
			$f->appendElement(new Cgn_Form_ElementContentLine(), $parentObj->title);
		}
		$f->appendElement(new Cgn_Form_ElementHidden('mid'),$values['mid']);
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_menu_item_id']);
		$f->appendElement(new Cgn_Form_ElementHidden('t'),'blank');
		$f->formFooter = $values['parenttext_04'];
		return $f;
	}


	function _localMenuItemForm($values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('content_01');
		$f->action = cgn_adminurl('menus','item','save');
		if ($values['menuWidth'] != '') {
			$f->width = $values['menuWidth'];
		} else {
			$f->width = "auto"; 
		}
		$f->label = $values['menuTitle'];
		$f->formHeader = $values['textline_01'];
		$f->appendElement(new Cgn_Form_ElementInput('title','New Link: '), $values['title']);
		$f->appendElement(new Cgn_Form_ElementContentLine(), $values['textline_02']);
		if ($values['local']) {
			$f->appendElement(new Cgn_Form_ElementInput('url', "http://".Cgn_Template::baseurl().""), $values['url']);
		} else {
			$f->appendElement(new Cgn_Form_ElementInput('url', "URL: "), $values['url']);
		}

		$f->appendElement(new Cgn_Form_ElementHidden('mid'),$values['mid']);
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_menu_item_id']);
		$f->appendElement(new Cgn_Form_ElementHidden('t'),$values['link_type']);
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
//			Cgn_ErrorStack::throwError("Object not found", 582);
			$this->redirectHome($t);
			return false;
		}
		$buddyItem->_nuls[] = 'parent_id';
		//swap these two ranks
		$buddyRank = $buddyItem->rank;
		$item->rank = $buddyRank;
		$item->save();

		//buddy item should bump its rank +1
		// this will keep the rankings compact if there's a gap
		$buddyItem->rank++;
		$buddyItem->save();
		$this->redirectHome($t);
	}


	/**
	 * Use the ID, menu ID and current rank to move an item down in listing
	 */
	function rankDownEvent($req, &$t) {
		$mid = $req->cleanInt('mid');
		$id  = $req->cleanInt('id');

		$item = new Cgn_DataItem('cgn_menu_item');
		$item->load($id);
		$item->_nuls[] = 'parent_id';

		//the buddy will either be the item directly above or below
		$buddy = new Cgn_DataItem('cgn_menu_item');
		//moving up... want to find a rank less than this one
		$buddy->andWhere('rank',$item->rank,'>');
		$buddy->andWhere('cgn_menu_id',$item->cgn_menu_id);
		$buddy->andWhere('parent_id',$item->parent_id);
		$buddy->sort('rank','ASC');
//		$buddy->_debugSql = true;
		$buddy->_rsltByPkey = false;

		$buddyList = $buddy->find();
		$buddyItem = $buddyList[0];

		if(! is_object($buddyItem) ) {
//			Cgn_ErrorStack::throwError("Object not found", 582);
			$this->redirectHome($t);
			return false;
		}
		$buddyItem->_nuls[] = 'parent_id';
		//swap these two ranks
		$buddyRank = $buddyItem->rank;
		$item->rank = $buddyRank;
		$item->save();

		//buddy item should bump its rank -1
		// this will keep the rankings compact if there's a gap
		if ($buddyItem->rank > 1 ) {
			$buddyItem->rank--;
			$buddyItem->save();
		}
		$this->redirectHome($t);
	}

}

?>
