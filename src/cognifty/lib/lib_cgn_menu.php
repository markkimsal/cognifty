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
		if ($this->dataItem->_isNew) { return false; }
		// __ FIXME __
		// should use 1 to many relationship in data item
		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT * FROM cgn_menu_item
			WHERE cgn_menu_id = '.$this->dataItem->cgn_menu_id.'
			ORDER BY parent_id ASC');
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
		$html = '';
		if ($this->dataItem->show_title) {
			$html = $this->getTitle();
		}
		$html .= $this->showLinksTree();
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

	function showLinksTree() {
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		include_once('../cognifty/lib/lib_cgn_mvc.php');
		include_once('../cognifty/lib/lib_cgn_mvc_tree.php');

		$list = new Cgn_Mvc_TreeModel();
		$parentList = array();

		foreach ($this->items as $item) {
			unset($treeItem);
			$treeItem = null;
			if ($item->type == 'web') {
				if ($item->parent_id) {
					$url = cgn_appurl('main','page').$item->url;

					$treeItem = new Cgn_Mvc_TreeItem('<a href="'.$url.'">'.$item->title.'</a>');
					if (strpos($url, $_SERVER['REQUEST_URI']) ) {
						$treeItem->_expanded = true;
					}
				} else {
					$treeItem = new Cgn_Mvc_TreeItem(''.$item->title.'');
				}
			} else if ( $item->type == 'section' ) {
				$treeItem = new Cgn_Mvc_TreeItem('<a href="'.cgn_appurl('main','section').$item->url.'">'.$item->title.'</a>');
			}

			//save the tree item in a list of parents for later reference
			if ($item->parent_id == 0) {
				$parentList[ $item->cgn_menu_item_id ] =& $treeItem;

				//no parent
				$list->appendChild($treeItem, null);
			} else {
				$itemRef =& $parentList[ $item->parent_id ];
				if ($treeItem->_expanded) {
					$itemRef->_expanded = true;
				}
				$list->appendChild($treeItem, $itemRef);
			}
		}

		$view = new Cgn_Mvc_TreeView2($list);
		$view->title = 'Links';
		return $view->toHtml();
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
