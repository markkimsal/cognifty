<?php

class Cgn_Menu {

	var $dataItem = null;
	var $showHeader = -1;
	var $items = array();

	function Cgn_Menu($id=0) {
		$this->dataItem = new Cgn_DataItem('cgn_menu');
		if ($id>0) {
			$this->dataItem->load($id);
		}
	}

	function load($name) {
		$this->dataItem->andWhere('code_name',$name);
		$menus = $this->dataItem->find();
//		cgn::debug($menus);
		foreach ($menus as $m) {
			if (is_object($m)) {
				$this->dataItem = $m;
			}
		}
		// __ FIXME __
		// should use 1 to many relationship in data item
		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT * FROM cgn_menu_item
			WHERE cgn_menu_id = '.$this->dataItem->cgn_menu_id);
		while ($db->nextRecord()) {
			$x = new Cgn_DataItem('cgn_menu_item');
			$x->row2Obj($db->record);
			$this->items[] = $x;
		}
	}

	function getTitle() {
		if ($this->showHeader > 0 ) {
			return '<h'.$this->showHeader.'>'.$this->dataItem->title.'</h'.$this->showHeader.'>';
		} else {
			return $this->dataItem->title;
		}
	}

	function toHtml() {
		$html  = $this->getTitle();
		$html .= $this->showLinks();
		return $html;
	}

	function showLinks() {
		$html = '<ul>';
		foreach ($this->items as $item) {
			if ($item->type == 'web') {
				$html .= '<li><a href="'.cgn_appurl('main','page').$item->url.'">'.$item->title."</a></li>\n";
			} else if ( $item->type == 'section' ) {
				$html .= '<li><a href="'.cgn_appurl('main','section').$item->url.'">'.$item->title."</a></li>\n";
			}
		}
		return $html.'</ul>';
	}
}


class Cgn_MenuItem {

	var $dataItem = null;

	function Cgn_MenuItem($id=0) {
		$this->dataItem = new Cgn_DataItem('cgn_menu_item');
		if ($id>0) {
			$this->dataItem->load($id);
		}
	}
}
