<?php


class Cgn_Form {

	var $name = 'cgn_form';
	var $elements = array();
	var $hidden = array();
	var $label = '';
	var $action;
	var $method;
	var $enctype;

	function Cgn_Form($name = 'cgn_form', $action='', $method='POST', $enctype='') {
		$this->name = $name;
		$this->action = $action;
		$this->method = $method;
		$this->enctype = $enctype;
	}

	function appendElement($e,$value='') {
		if ($value != '') {
			$e->value = $value;
		}
		if ($e->type == 'hidden') {
			$this->hidden[] = $e;
		} else {
			$this->elements[] = $e;
		}
	}

	function toHtml($layout=null) {
		if ($layout != null) {
			return $layout->renderForm($this);
		}
		if ($this->layout != null) {
			return $this->layout->renderForm($this);
		}
		$layout = new Cgn_Form_Layout();
		return $layout->renderForm($this);
	}
}


class Cgn_Form_Element {
	var $type;
	var $name;
	var $id;
	var $label;
	var $value;
	var $rows;
	var $cols;
	var $size;

	function Cgn_Form_Element($name,$label=-1,$rows=15,$cols=75, $size=35) {
		$this->name = $name;
		$this->label = $label;
		if ($this->label == -1) {
			$this->label = ucfirst($this->name);
		}
		$this->rows = $rows;
		$this->cols = $cols;
		$this->size = $size;
	}
}

class Cgn_Form_ElementHidden extends Cgn_Form_Element {
	var $type = 'hidden';
}


class Cgn_Form_ElementInput extends Cgn_Form_Element {
	var $type = 'input';
}

class Cgn_Form_ElementFile extends Cgn_Form_Element {
	var $type = 'file';
}

class Cgn_Form_ElementText extends Cgn_Form_Element {
	var $type = 'textarea';
}


class Cgn_Form_ElementPassword extends Cgn_Form_Element {
	var $type = 'password';
}


class Cgn_Form_ElementRadio extends Cgn_Form_Element {
	var $type = 'radio';
	var $choices = array();

	function addChoice($c) {
		$this->choices[] = $c;
		return count($this->choices)-1;
	}
}


class Cgn_Form_Processor {
}


class Cgn_Form_Layout {

	function renderForm($form) {
		$html = '';
		if ($form->label != '' ) {
			$html .= '<h2 class="cgn_form">'.$form->label.'</h2>';
			$html .= "\n";
		}
//		$attribs = array('method'=>$form->method, 'name'=>$form->name, 'id'=>$form->id);
		if ($form->action) {
			$action = ' action="'.$form->action.'" ';
		}
		$html .= '<form method="'.$form->method.'" name="'.$form->name.'" id="'.$form->name.'"'.$action;
		if ($form->enctype) {
			$html .= ' enctype="'.$form->enctype.'"'.$enctype;
		}
		$html .= '>';
		$html .= "\n";
		$html .= '<table border="0" cellspacing="3" cellpadding="3">';
		foreach ($form->elements as $e) {
			$html .= '<tr><td valign="top">';
			$html .= $e->label.'</td><td valign="top">';
			if ($e->type == 'textarea') {
				$html .= '<textarea name="'.$e->name.'" id="'.$e->name.'" rows="'.$e->rows.'" cols="'.$e->cols.'" ></textarea>';
			} else if ($e->type == 'radio') {
				foreach ($e->choices as $cid => $c) {
				$html .= '<input type="radio" name="'.$e->name.'" id="'.$e->name.sprintf('%02d',$cid+1).'" value="'.sprintf('%02d',$cid+1).'">'.$c.'<br/> ';
				}
			} else {
				$html .= '<input type="'.$e->type.'" name="'.$e->name.'" id="'.$e->name.'" value="'.$e->value.'" size="'.$e->size.'">';
			}
			$html .= '</td></tr>';
		}
		$html .= '</table>';

		foreach ($form->hidden as $e) {
			$html .= '<input type="hidden" name="'.$e->name.'" id="'.$e->name.'" value="'.$e->value.'">';
		}
		$html .= '<input type="submit" name="'.$form->name.'_submit" value="Submit">';
		$html .= '</form>';
		$html .= "\n";

		return $html;
	}
}


?>
