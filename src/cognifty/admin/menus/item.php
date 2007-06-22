<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/lib/lib_cgn_mvc_table.php');

class Cgn_Service_Menus_Item extends Cgn_Service_Admin {

	function Cgn_Service_Menus_Item () {

	}


	function mainEvent(&$req, &$t) {
		$mid = $req->cleanInt('mid');
		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT * FROM cgn_menu_item 
			WHERE cgn_menu_id = '.$mid);


		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			$list->data[] = array(
				cgn_adminlink($db->record['title'],'menus','main','edit',array('id'=>$db->record['cgn_menu_id'])),
				$db->record['type'],
				cgn_adminlink('delete','menus','main','delete',array('id'=>$db->record['cgn_menu_id']))
			);
		}
		$list->headers = array('Title','Type','Delete');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
		$t['spacer'] = '<br/>';
		$t['addlink'] = cgn_adminlink('add new item', 'menus','item','edit', array('mid'=>$mid));
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

		$item = new Cgn_DataItem('cgn_menu_item');
		if ($itemId) {
			$item->load($itemId);
		}
		$item->title = $req->cleanString('title');
		$item->type  = $req->cleanString('type');
		$item->url  = $req->cleanString('url');
		$item->cgn_menu_id  = $menuId;
		$item->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'menus','item','',array('mid'=>$menuId));
	}

	function editEvent(&$req, &$t) {
		$menuId = $req->cleanInt('mid');
		$t['itemForm'] = $this->_loadMenuItemForm(array('mid'=>$menuId));
	}


	function _loadMenuItemForm($values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('content_01');
		$f->action = cgn_adminurl('menus','item','save');
		$f->label = 'Menu Item';
		$f->appendElement(new Cgn_Form_ElementInput('title'));
		$f->appendElement(new Cgn_Form_ElementInput('type'));
		$f->appendElement(new Cgn_Form_ElementInput('url','URL'));
		$f->appendElement(new Cgn_Form_ElementHidden('mid'),$values['mid']);
		return $f;
	}
}

?>
