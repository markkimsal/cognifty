<?php


/**
 * This function is needed to help PHP emulate static variables
 * for subclasses (needed for both php4 and 5
 */
function Cgn_HtmlWidget_GlobalSetId($id = null) {
	static $num=0;
	$num++;
	return $num;
}

class Cgn_HtmlWidget {


	var $id = null;
	var $type;
	var $tagName;
	var $classes = array();
	var $style = array();
	var $attribs = array();
	var $name;

	function Cgn_HtmlWidget() {
		if ($this->id == null) {
			$this->setId(null);
		}
	}

	function toHtml() {
		$html  = '';
		$html .= $this->printOpen();
		$html .= $this->getContents();
		$html .= $this->printClose();
		return $html;
	}

	function setId($id = null) {
		if ($id === null) {
			$num = Cgn_HtmlWidget_GlobalSetId();
			$this->id = $this->type.sprintf('%03d',$num);
		} else {
			$this->id = $id;
		}
	}

	function setClass($c) {
		$this->classes[0] = $c;
	}

	function addClass($c) {
		$this->classes[] = $c;
	}

	function printOpen() {
		return '<'.$this->tagName.' '.$this->printId().$this->printAttribs().$this->printClass().$this->printStyle().'>';
	}

	function printClose() {
		return '</'.$this->tagName.'>'."\n";
	}

	function printId() {
		if ($this->id == '') {$this->setId();}
		return ' id="'.$this->id.'" ';
	}

	function printClass() {
		if ( count ($this->classes) < 1) { return ''; }
		return ' class="'. implode(' ',$this->classes).'" ';
	}

	function printStyle() {
		if ( count ($this->style) < 1) { return ''; }
		$html  = '';
		$html .= ' style="';
		foreach ($this->style as $k=>$v) {
			$html .= "$k:$v;";
		}
		return $html.'" ';
	}

	function printAttribs() {
		$html  = '';
		foreach ($this->attribs as $k=>$v) {
			$html .= " $k=\"$v\" ";
		}
		return $html;
	}

	function getContents() {
		return $this->name;
	}
}
?>
