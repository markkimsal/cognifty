<?php

class Cgn_Menu {

	var $dataItem   = null;
	var $showHeader = -1;
	var $items      = array();
	var $widget     = null;

	function Cgn_Menu($id=0) {
		$this->dataItem = new Cgn_DataItem('cgn_menu');
		if ($id>0) {
			$this->dataItem->load($id);
		}
	}

	function loadCodename($name) {
		$this->dataItem->andWhere('code_name',$name);
		$menus = $this->dataItem->find();
		if ( count($menus) < 1) { 
			//if not connected to the DB, the library throws an error.
			$x = Cgn_ErrorStack::pullError('php');
			return false; 
		}
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
			ORDER BY parent_id,rank ASC');
		while ($db->nextRecord()) {
			$x = new Cgn_DataItem('cgn_menu_item');
			$x->row2Obj($db->record);
			$this->items[] = $x;
		}
		return true;
	}

	function getTitle() {
		if ($this->showHeader > 0 ) {
			return '<h'.$this->showHeader.'>'.$this->dataItem->title.'</h'.$this->showHeader.'>';
		} else {
			// return $this->dataItem->title;   SCOTTCHANGE 20070727
			return "<!-- Menu Code : ".$this->dataItem->title." --> \n";
		}
	}

	function toHtml($extras=array()) {
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
		include_once(CGN_LIB_PATH.'/lib_cgn_mvc_tree.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_menu.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_panel.php');

		$html = '';
		$widget =  new Cgn_HtmlWidget_Menu($this->getTitle(), $this->showLinksTree());
		if ( isset($this->dataItem->show_title) && $this->dataItem->show_title == 1) {
			$widget->setShowTitle($this->dataItem->show_title);
		}
		if (isset($extras['class'])) {
			$widget->setViewClasses( array($extras['class']) );
		}

//		$html .= $this->showLinksTree();
		$html .= $widget->toHtml();
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

		$list = new Cgn_Mvc_TreeModel();
		$parentList = array();
		$saveThisMenu = -1;
		$session =& Cgn_Session::getSessionObj();
		$lastMenuLink = $session->get('_last_menu');
		$lastMenuItem = null;
		$anyExpanded = false;
		if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING'])) {
			$requestUri = substr($_SERVER['REQUEST_URI'], 0, -(strlen($_SERVER['QUERY_STRING'])+1) );
		} else {
			$requestUri = $_SERVER['REQUEST_URI'];
		}

		foreach ($this->items as $item) {
			unset($treeItem);
			$treeItem = null;
			if ($item->type == 'web') {
				$url = cgn_appurl('main','page').$item->url;
				// FIXME  wth is this?
				if ($item->parent_id) {
					$treeItem = new Cgn_Mvc_TreeItem(
						'<a href="'.$url.'">'.$item->title.'</a>');
				} else {
					$treeItem = new Cgn_Mvc_TreeItem(
						'<a href="'.$url.'">'.$item->title.'</a>');
				}
			} else if ( $item->type == 'section' ) {
				$url = cgn_appurl('main','section').$item->url;
				$treeItem = new Cgn_Mvc_TreeItem('<a href="'.$url.'">'.$item->title.'</a>');

			} else if ( $item->type == 'article' ) {
				$url = cgn_appurl('main','content').$item->url;
				$treeItem = new Cgn_Mvc_TreeItem('<a href="'.$url.'">'.$item->title.'</a>');

			} else if ( $item->type == 'asset' ) {
				$url = cgn_appurl('main','asset').$item->url;
				$treeItem = new Cgn_Mvc_TreeItem('<a href="'.$url.'">'.$item->title.'</a>');
			} else if ( $item->type == 'blank' ) {
				$url = cgn_appurl('main','main').$item->url;
				$treeItem = new Cgn_Mvc_TreeItem('<a href="#">'.$item->title.'</a>');
			} else if ( $item->type == 'local' ) {
				$url = 'http://'.Cgn_Template::baseurl().$item->url;
				$treeItem = new Cgn_Mvc_TreeItem('<a href="'.$url.'">'.$item->title.'</a>');
			} else {
				$url = $item->url;
				$treeItem = new Cgn_Mvc_TreeItem('<a href="'.$url.'">'.$item->title.'</a>');
			}

			//should menu item be expanded
			$urlArray =  parse_url($url);
			if ( isset($urlArray['path']) && 
//				strpos( $urlArray['path'], $requestUri) !== false ) {
//				strpos( $requestUri, $urlArray['path']) !== false ) {
				$urlArray['path'] == $requestUri ) {
				$treeItem->_expanded = true;
				$saveThisMenu = $item->cgn_menu_item_id;
				$anyExpanded = true;
			}

			//hold on to a reference to the menu that was last clicked
			if ($lastMenuLink == $item->cgn_menu_item_id) {
				//__FIXME__ in PHP5 this might need to be copied
				$lastMenuItem = $item;
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
					$anyExpanded = true;
				}
				$list->appendChild($treeItem, $itemRef);
			}
		}
		if ($saveThisMenu > 0) {
			$session->set('_last_menu', $saveThisMenu);
		}
		if ($lastMenuItem !== null && $anyExpanded == false) {
			//open this menu item
			$lastMenuItem->_expanded = true;
			if ( $lastMenuItem->parent_id != 0 ) {
				$itemRef =& $parentList[ $lastMenuItem->parent_id ];
				$itemRef->_expanded = true;
			}
		}


		return $list;

		/*
		$view = new Cgn_Mvc_TreeView2($list);
		$view->title = 'Links';
		return $view->toHtml();
		 */
	}

	function isLoaded() {
		if (! is_object($this->dataItem) ) {
			return false;
		}
		return ! $this->dataItem->_isNew;
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
