<?php


class Cgn_Form {

	var $name = 'cgn_form';
	var $elements = array();
	var $label = '';
	var $action;
	var $method;

	function Cgn_Form($name = 'cgn_form', $action='', $method='POST') {
		$this->name = $name;
		$this->action = $action;
		$this->method = $method;
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

	function Cgn_Form_Element($name,$label=-1) {
		$this->name = $name;
		$this->label = $label;
		if ($this->label == -1) {
			$this->label = ucfirst($this->name);
		}
	}
}

class Cgn_Form_ElementHidden extends Cgn_Form_Element {
	var $type = 'hidden';
}


class Cgn_Form_ElementInput extends Cgn_Form_Element {
	var $type = 'input';
}


class Cgn_Form_ElementText extends Cgn_Form_Element {
	var $type = 'textarea';
}



class Cgn_Form_ElementPassword extends Cgn_Form_Element {
	var $type = 'password';
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
		$html .= '<form method="'.$form->method.'" name="'.$form->name.'" id="'.$form->name.'"'.$action.'>';
		$html .= "\n";
		$html .= '<table border="0" cellspacing="3" cellpadding="3">';
		foreach ($form->elements as $e) {
			$html .= '<tr><td>';
			$html .= $e->label.'</td><td>';
			if ($e->type == 'textarea') {
				$html .= '<textarea name="'.$e->name.'" id="'.$e->name.'" rows="15" cols="70">'.$e->value.'</textarea>';
			} else {
				$html .= '<input type="'.$e->type.'" name="'.$e->name.'" id="'.$e->name.'" value="'.$e->value.'">';
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
