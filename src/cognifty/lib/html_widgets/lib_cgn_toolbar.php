<?php

class Cgn_HtmlWidget_Toolbar extends Cgn_HtmlWidget {
	var $tagName = 'div';
	var $classes = array('adm_toolbar');
	var $buttons = array();
	var $style   = array('padding'=>'3px', 'width'=>'642px');
	var $type    = 'panel';
	var $id      = 'toolbar';

	function addButton($b) {
		$this->buttons[] = $b;
	}

	/**
	 *  Called from parent::toHtml()
	 */
	function getContents() {
		$html = '';
		foreach ($this->buttons as $btn) {
			$html .= $btn->toHtml();
		}
		return $html;
	}
}

class Cgn_HtmlWidget_Button extends Cgn_HtmlWidget {

	var $display = '';
	var $href    = '#';
	var $tagName = 'button';
	var $classes = array('adm_toolbtn');
	var $attribs = array('type'=>'button');
	var $type    = 'button';

	function Cgn_HtmlWidget_Button($href, $d="Submit") {
		$this->setId(null);
		$this->href = $href;
		$this->setDisplay($d);
	}

	function getContents() {
		return $this->display;
	}

	function setDisplay($d) {
		$this->display = $d;
//		$this->attribs['value'] = $this->display;
	}

	function toHtml() {
		$html  = '<span id="button_'.$this->id.'" '.$this->printClass().'>';
		$html .= "\n";
		$html .= '  <button type="button" onclick="document.location.href=\''.htmlspecialchars($this->href).'\';">'.$this->display.'</button>';
		$html .= "\n";
		$html .= '</span>';
		return $html;
//		return parent::toHtml();
	}
}
?>