<?php


class Cgn_Panel extends Cgn_HtmlWidget {

	var $tagName = 'div';
	var $type = 'panel';
	var $label;
	var $classes = array('wtitle');

	function Cgn_Panel($n='') {
		$this->setId();
		if ($n) {
			$this->label = new Label($n);
			$this->name = $n;
		} else {
			$this->name = $this->id;
		}
		$this->label = new Cgn_Label($this->name);
	}

	function getContents() {
		return $this->label->toHtml();
	}

}


class Cgn_Label extends Cgn_HtmlWidget {
	var $display = '';
	var $tagName = 'div';
	var $classes = array('wtitle');

	function Cgn_Label($d) {
		$this->display = $d;
	}

	function getContents() {
		return $this->display;
	}
}
?>
