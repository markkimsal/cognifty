<?php

class Cgn_HtmlWidget_Menu extends Cgn_HtmlWidget {

	var $tagName = 'div';
	var $type = 'panel';
	var $title;
	var $linkModel;
	var $showTitle = true;
	var $viewClasses = array();


	function Cgn_HtmlWidget_Menu($t, $model) {
		$this->setId();
		$this->name = $this->id;
		$this->title = $t;
		/*
		if ($t) {
			$this->label = new Cgn_Label($t);
		} else {
			$this->label = new Cgn_Label($this->name);
		}
		 */

		$this->linkModel = $model;
	}

	function setViewClasses($a) {
		$this->viewClasses = $a;
	}

	function printOpen() { return ''; }
	function printClose() { return ''; }

	function getContents() {
		include_once(CGN_LIB_PATH.'/lib_cgn_mvc_tree.php');
		$html = '';
		if (strstr(strtolower( get_class($this->linkModel)) , 'tree') )  {
			$listView = new Cgn_Mvc_TreeView2($this->linkModel);
			if ( is_array($this->viewClasses) && count($this->viewClasses) > 0) {
				$listView->classes = $this->viewClasses;
			}
//			$listView->classes = array('mainlevel-sidenav');
		} else {
			$listView = new Cgn_Mvc_MenuView($this->linkModel);
		}
		if ($this->showTitle) {
			$html .= $this->title;
		}
		$html .= $listView->toHtml($this->id);
		return $html;
	}

	function setShowTitle($b=true) {
		$this->showTitle = $b;
	}
}


class Cgn_Mvc_MenuView extends Cgn_Mvc_ListView {


	function toHtml($id='') {
		$html  = '';
		$html .= $this->printOpen();
		$rows = $this->_model->getRowCount();
		for($x=0; $x < $rows; $x++) {
			$datum = $this->_model->getValueAt($x,0);
			$href  = $this->_model->getValueAt($x,1);
			$html .= '<li class="list_li_1">';
			$html .= '<a href="'.$href.'">'.$datum.'</a>';
			$html .= '</li>'."\n";
		}
		$html .= $this->printClose();
		return $html;
	}
}


?>
