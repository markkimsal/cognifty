<?php

class Cgn_HtmlWidget_Toolbar extends Cgn_HtmlWidget {
	var $tagName = 'div';
	var $classes = array('adm_toolbar');
	var $buttons = array();
	var $style   = array('padding'=>'3px', 'width'=>'100%');
	var $type    = 'panel';
	var $id      = 'toolbar';
	var $separator = ' | ';

	function addButton($b) {
		$this->buttons[] = $b;
	}

	/**
	 * Don't show any HTML if there are no buttons
	 */
	function toHtml() {
		$contents = $this->getContents();
		if (empty($contents)) {
			return '';
		}
		$html  = '';
		$html .= $this->printOpen();
		$html .= $contents;
		$html .= $this->printClose();
		return $html;
	}


	/**
	 *  Called from parent::toHtml()
	 */
	function getContents() {
		$html = '';
		foreach ($this->buttons as $btn) {
			if ($html != '')
				$html .= $this->separator;
			$html .= $btn->toHtml();
		}
		return $html;
	}

	/**
	 * Set the HTML content which separates buttons.
	 * Pass '' to have no separator
	 *
	 * @param $html String   HTML placed between each button
	 */
	function setSeparator($html) {
		$this->separator = $html;
	}
}

class Cgn_HtmlWidget_Button extends Cgn_HtmlWidget {

	var $display = '';
	var $href    = '#';
	var $onclick = '';
	var $tagName = 'button';
	var $classes = array('adm_toolbtn');
	var $attribs = array('type'=>'button');
	var $type    = 'button';

	function Cgn_HtmlWidget_Button($href, $d="Submit") {
		$this->setId(NULL);
		$this->href = $href;
		$this->setDisplay($d);
	}

	/**
	 * Set the javascript which should be triggered onclick.
	 *
	 * This will override any href settings from the constructor.
	 */
	function setJavascript($js) {
		$this->onclick = $js;
	}

	function getContents() {
		return $this->display;
	}

	function setDisplay($d) {
		$this->display = $d;
//		$this->attribs['value'] = $this->display;
	}

	/**
	 * Return specific javascript, or document.location.href = $this->href.
	 *
	 * @return String   javascript code
	 */
	function buildOnclick() {
		if ($this->onclick == '') {
			return 'document.location.href=\''.htmlspecialchars($this->href).'\';';
		} else {
			return $this->onclick;
		}
	}

	function toHtml() {
		$this->attribs['onclick'] = $this->buildOnclick();

		$html  = '<span id="button_'.$this->id.'" '.$this->printClass().'>';
		$html .= '  '.$this->printOpen(). $this->display. $this->printClose();
		$html .= '</span>'.PHP_EOL;
		return $html;
	}
}
?>
