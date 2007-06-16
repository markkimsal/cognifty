<?php

class Cgn_HtmlWidget {


	var $id;
	var $type;
	var $tagName;
	var $classes = array();
	var $style = array();
	var $attribs = array();
	var $name;

	function toHtml() {
		$html  = '';
		$html .= $this->printOpen();
		$html .= $this->getContents();
		$html .= $this->printClose();
		return $html;
	}


	function setId($id = null) {
		static $num;
		if ($id == null) {
			$num++;
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
		return ' class="'.$this->classes[0].'" ';
	}

	function printStyle() {
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
