<?php

class Cgn_Menu extends Cgn_HtmlWidget {

	var $tagName = 'div';
	var $type = 'panel';
	var $label;
	var $title;
	var $linkModel;

	function Cgn_Menu($t,$model) {
		$this->setId();
		$this->name = $this->id;
		if ($t) {
			$this->label = new Cgn_Label($t);
		} else {
			$this->label = new Cgn_Label($this->name);
		}

		$this->linkModel = $model;
	}


	function getContents() {
		$html = '';
		$listView = new Cgn_MenuView($this->linkModel);
		$html .= $this->label->toHtml();
		$html .= $listView->toHtml();
		return $html;
	}
}


class Cgn_MenuView extends Cgn_Mvc_ListView {


	function toHtml($id='') {
		$html  = '';
		$html .= $this->printOpen();
		$rows = $this->_model->getRowCount();
		for($x=0; $x < $rows; $x++) {
			$datum = $this->_model->getValueAt($x,0);
			$href  = $this->_model->getValueAt($x,1);
			$html .= '<li style="list_li_1">';
			$html .= '<a href="'.$href.'">'.$datum.'</a>';
			$html .= '</li>'."\n";
		}
		$html .= $this->printClose();
		return $html;
	}
}


?>
