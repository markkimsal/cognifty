<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/lib/lib_cgn_mvc_table.php');

include_once('../cognifty/lib/lib_cgn_menu.php');

class Cgn_Service_Menus_Item extends Cgn_Service_Admin {

	function Cgn_Service_Menus_Item () {

	}


	function mainEvent(&$req, &$t) {
		$mid = $req->cleanInt('mid');
		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT * FROM cgn_menu_item 
			WHERE cgn_menu_id = '.$mid);

		$m  = new Cgn_Menu(0);
		$m->load('main.menu');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			$list->data[] = array(
				cgn_adminlink($db->record['title'],'menus','item','edit',array('id'=>$db->record['cgn_menu_item_id'], 'mid'=>$db->record['cgn_menu_id'], 't'=>$db->record['type'])),
				$db->record['url'],
				$db->record['type'],
				cgn_adminlink('delete','menus','main','delete',array('id'=>$db->record['cgn_menu_id']))
			);
		}
		$list->headers = array('Title','URL','Type','Delete');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
		$t['spacer'] = '<br/>';
		$t['pagelink'] = cgn_adminlink('Add Web Page Link', 'menus','item','edit', array('mid'=>$mid,'t'=>'web'));
		$t['articlelink'] = cgn_adminlink('Add Article Section Link', 'menus','item','edit', array('mid'=>$mid,'t'=>'section'));
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
		$itemId = $req->cleanInt('id');
		$menuId = $req->cleanInt('mid');
		$type   = $req->cleanString('t');

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

		$type = $req->cleanString('t');
		if ($type == 'web') {
			//load all pages
			$loader = new Cgn_DataItem('cgn_web_publish');
			$loader->_exclude('content');
			$pages = $loader->find();
			$t['itemForm'] = $this->_webMenuItemForm($values, $pages);
		}

		if ($type == 'section') {
			$loader = new Cgn_DataItem('cgn_article_section');
//			$loader->_exclude('content');
			$sections = $loader->find();
			$t['itemForm'] = $this->_sectionMenuItemForm($values, $sections);
		}
	}


	function _webMenuItemForm($values=array(), $pages=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('content_01');
		$f->action = cgn_adminurl('menus','item','save');
		$f->label = 'Menu Item';
		$f->appendElement(new Cgn_Form_ElementInput('title'), $values['title']);
		$page = new Cgn_Form_ElementSelect('page','Page',5);
		foreach ($pages as $pageObj) {
			$page->addChoice($pageObj->title, $pageObj->cgn_web_publish_id);
		}

		$f->appendElement($page);
		$f->appendElement(new Cgn_Form_ElementHidden('mid'),$values['mid']);
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_menu_item_id']);
		$f->appendElement(new Cgn_Form_ElementHidden('t'),'web');
		return $f;
	}


	function _sectionMenuItemForm($values=array(), $sections=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('content_01');
		$f->action = cgn_adminurl('menus','item','save');
		$f->label = 'Menu Item';
		$f->appendElement(new Cgn_Form_ElementInput('title'), $values['title']);
		$select = new Cgn_Form_ElementSelect('section','Section',5);
		foreach ($sections as $sectionObj) {
			$select->addChoice($sectionObj->title, $sectionObj->cgn_article_section_id);
		}

		$f->appendElement($select);
		$f->appendElement(new Cgn_Form_ElementHidden('mid'),$values['mid']);
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_menu_item_id']);
		$f->appendElement(new Cgn_Form_ElementHidden('t'),'section');
		return $f;
	}

}

?>
