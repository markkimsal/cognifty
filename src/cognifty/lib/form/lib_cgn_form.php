<?php


class Cgn_Form {

	var $name = 'cgn_form';
	var $elements = array();
	var $hidden = array();
	var $label = '';
	var $action;
	var $method;
	var $enctype;
	var $layout = null;           //layout object to render the form
	var $width = '450px';

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
		if ($layout !== null) {
			return $layout->renderForm($this);
		}
		if ($this->layout !== null) {
			return $this->layout->renderForm($this);
		}
		$layout = new Cgn_Form_Layout();
		return $layout->renderForm($this);
	}
}

class Cgn_FormAdmin extends Cgn_Form {

	/**
	 * Use Fancy layout
	 */
	function toHtml($layout=null) {
		if ($layout !== null) {
			return $layout->renderForm($this);
		}
		if ($this->layout !== null) {
			return $this->layout->renderForm($this);
		}
		$layout = new Cgn_Form_LayoutFancy();
		return $layout->renderForm($this);
	}
}

class Cgn_FormAdminDelete extends Cgn_Form {

	/**
	 * Use Fancy Delete layout
	 */
	function toHtml($layout=null) {
		if ($layout !== null) {
			return $layout->renderForm($this);
		}
		if ($this->layout !== null) {
			return $this->layout->renderForm($this);
		}
		$layout = new Cgn_Form_LayoutFancyDelete();
		return $layout->renderForm($this);
	}
}


class Cgn_Form_Element {
	var $type;
	var $name;
	var $id;
	var $label;
	var $value;
	var $size;

	function Cgn_Form_Element($name,$label=-1, $size=35) {
		$this->name = $name;
		$this->label = $label;
		if ($this->label == -1) {
			$this->label = ucfirst($this->name);
		}
		$this->size = $size;
	}
}

class Cgn_Form_ElementLabel extends Cgn_Form_Element {
	var $type = 'label';

	function Cgn_Form_ElementLabel($name, $label=-1,  $value= '') {
			$this->name = $name;
			$this->value = $value;
			$this->label = $label;
	}

	function toHtml() {
		return '<span name="'.$this->name.'" id="'.$this->name.'">'.htmlentities($this->value,ENT_QUOTES).'</span>';
	}
}

class Cgn_Form_ElementContentLine extends Cgn_Form_Element {
	var $type = 'contentLine';

	function Cgn_Form_ElementContentLine($value= '') {
			$this->value = $value;
	}

	function toHtml() {
		return $this->value;
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
	var $rows;
	var $cols;

	function Cgn_Form_ElementText($name, $label=-1,$rows=15,$cols=65) {
		$this->name = $name;
		$this->label = $label;
		if ($this->label == -1) {
			$this->label = ucfirst($this->name);
		}
		$this->rows = $rows;
		$this->cols = $cols;
	}
}


class Cgn_Form_ElementPassword extends Cgn_Form_Element {
	var $type = 'password';
}


class Cgn_Form_ElementRadio extends Cgn_Form_Element {
	var $type = 'radio';
	var $choices = array();

	function addChoice($c,$selected=0) {
		$top = count($this->choices);
		$this->choices[$top]['title'] = $c;
		$this->choices[$top]['selected'] = $selected;
		return count($this->choices)-1;
	}
}

class Cgn_Form_ElementSelect extends Cgn_Form_Element {
	var $type = 'select';
	var $choices = array();
	var $size = 1;
	var $selectedVal = null;

	function Cgn_Form_ElementSelect($name,$label=-1, $size=7, $selectedVal = null) {
		parent::Cgn_Form_Element($name, $label, $size);
		$this->selectedVal = $selectedVal;
	}

	function addChoice($c,$v='',$selected=0) {
		$top = count($this->choices);

		if ($this->selectedVal == $v) {
			$selected = true;
		}

		$this->choices[$top]['title'] = $c;
		$this->choices[$top]['selected'] = $selected;
		$this->choices[$top]['value'] = $v;


		return count($this->choices)-1;
	}

	function toHtml() {
		$html = '<select name="'.$this->name.'" id="'.$this->name.'" size="'.$this->size.'">';
		foreach ($this->choices as $cid => $c) {
			$selected = '';
			if ($c['selected'] == 1) { $selected = ' SELECTED="SELECTED" '; }
			if ($c['value'] != '') { $value = ' value="'.htmlentities($c['value']).'" ';} else { $value = ''; }
		$html .= '<option id="'.$this->name.sprintf('%02d',$cid+1).'" '.$value.$selected.'>'.$c['title'].'</option> ';
		}
		return $html."</select>\n";
	}
}


class Cgn_Form_ElementCheck extends Cgn_Form_Element {
	var $type = 'check';
	var $choices = array();

	function addChoice($c,$v='',$selected=0) {
		$top = count($this->choices);
		$this->choices[$top]['title'] = $c;
		if ($v == '') {
			$this->choices[$top]['value'] = sprintf('%02d',$top+1);
		} else {
			$this->choices[$top]['value'] = $v;
		}
		$this->choices[$top]['selected'] = $selected;
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
		$action = '';
		if ($form->action) {
			$action = ' action="'.$form->action.'" ';
		}
		$html .= '<form method="'.$form->method.'" name="'.$form->name.'" id="'.$form->name.'"'.$action;
		if ($form->enctype) {
			$html .= ' enctype="'.$form->enctype.'"';
		}
		$html .= '>';
		$html .= "\n";
		$html .= '<table border="0" cellspacing="3" cellpadding="3">';
		foreach ($form->elements as $e) {
			$html .= '<tr><td valign="top">';
			$html .= $e->label.'</td><td valign="top">';
			if ($e->type == 'textarea') {
				$html .= '<textarea name="'.$e->name.'" id="'.$e->name.'" rows="'.$e->rows.'" cols="'.$e->cols.'" >'.htmlentities($e->value,ENT_QUOTES).'</textarea>';
			} else if ($e->type == 'radio') {
				foreach ($e->choices as $cid => $c) {
					$selected = '';
					if ($c['selected'] == 1) { $selected = ' CHECKED="CHECKED" '; }
				$html .= '<input type="radio" name="'.$e->name.'" id="'.$e->name.sprintf('%02d',$cid+1).'" value="'.sprintf('%02d',$cid+1).'"'.$selected.'/>'.$c['title'].'<br/> ';
				}
			} else if ($e->type == 'select') {
				$html .= $e->toHtml();
			} else if ($e->type == 'label') {
				$html .= $e->toHtml();
			} else if ($e->type == 'contentLine') {
				$html .= $e->toHtml();
			} else if ($e->type == 'check') {
				foreach ($e->choices as $cid => $c) {
					$selected = '';
					if ($c['selected'] == 1) { $selected = ' CHECKED="CHECKED" '; }
				$html .= '<input type="checkbox" name="'.$e->name.'[]" id="'.$e->name.sprintf('%02d',$cid+1).'" value="'.$c['value'].'"'.$selected.'/>'.$c['title'].'<br/> ';
				}
			} else {
				$html .= '<input type="'.$e->type.'" name="'.$e->name.'" id="'.$e->name.'" value="'.htmlentities($e->value,ENT_QUOTES).'" size="'.$e->size.'"/>';
			}
			$html .= '</td></tr>';
		}
		$html .= '</table>';

		foreach ($form->hidden as $e) {
			$html .= '<input type="hidden" name="'.$e->name.'" id="'.$e->name.'" value="'.htmlentities($e->value,ENT_QUOTES).'"/>';
		}
		$html .= '<input type="submit" name="'.$form->name.'_submit" value="Submit"/>';
		$html .= '</form>';
		$html .= "\n";

		return $html;
	}
}


class Cgn_Form_LayoutFancy extends Cgn_Form_Layout {

	function renderForm($form) {
		$html = '<div style="padding:1px;background-color:#FFF;border:1px solid silver;width:'.$form->width.';">';
		$html .= '<div class="cgn_form" style="padding:5px;background-color:#EEE;">';
		if ($form->label != '' ) {
			$html .= '<h3 style="padding:0px 0px 3pt;">'.$form->label.'</h3>';
			$html .= "\n";
		}
		if ($form->formHeader != '' ) {
			$html .= '<P style="padding:0px 0px 3pt; text-align:justify;">'.$form->formHeader.'</P>';
			$html .= "\n";
		}
//		$attribs = array('method'=>$form->method, 'name'=>$form->name, 'id'=>$form->id);
		$action = '';
		if ($form->action) {
			$action = ' action="'.$form->action.'" ';
		}
		$html .= '<form method="'.$form->method.'" name="'.$form->name.'" id="'.$form->name.'"'.$action;
		if ($form->enctype) {
			$html .= ' enctype="'.$form->enctype.'"';
		}
		$html .= '>';
		$html .= "\n";
		$html .= '<table border="0" cellspacing="3" cellpadding="3">';
		foreach ($form->elements as $e) {
			$html .= '<tr><td valign="top" align="right" nowrap>';
			$html .= $e->label.'</td><td valign="top">';
			if ($e->type == 'textarea') {
				$html .= '<textarea class="forminput" name="'.$e->name.'" id="'.$e->name.'" rows="'.$e->rows.'" cols="'.$e->cols.'" >'.htmlentities($e->value,ENT_QUOTES).'</textarea>';
			} else if ($e->type == 'radio') {
				foreach ($e->choices as $cid => $c) {
					$selected = '';
					if ($c['selected'] == 1) { $selected = ' CHECKED="CHECKED" '; }
				$html .= '<input type="radio" name="'.$e->name.'" id="'.$e->name.sprintf('%02d',$cid+1).'" value="'.sprintf('%02d',$cid+1).'"'.$selected.'/>'.$c['title'].'<br/> ';
				}
			} else if ($e->type == 'select') {
				$html .= $e->toHtml();
			} else if ($e->type == 'label') {
				$html .= $e->toHtml();
			} else if ($e->type == 'contentLine') {
				$html .= "<span style=\"text-align: justify;\">";
				$html .= $e->toHtml();
				$html .= "</span>";
			} else if ($e->type == 'check') {
				foreach ($e->choices as $cid => $c) {
					$selected = '';
					if ($c['selected'] == 1) { $selected = ' CHECKED="CHECKED" '; }
				$html .= '<input type="checkbox" name="'.$e->name.'[]" id="'.$e->name.sprintf('%02d',$cid+1).'" value="'.$c['value'].'"'.$selected.'/>'.$c['title'].'<br/> ';
				}
			} else {
				$html .= '<input class="forminput" type="'.$e->type.'" name="'.$e->name.'" id="'.$e->name.'" value="'.htmlentities($e->value,ENT_QUOTES).'" size="'.$e->size.'"/>';
			}
			$html .= '</td></tr>';
		}
		$html .= '</table><br />';
		if ($form->formFooter != '' ) {
			$html .= '<P style="padding:0px 0px 3pt;text-align:justify;">'.$form->formFooter.'</P>';
			$html .= "\n";
		}
		$html .= '<div style="width:90%;text-align:right;">';
		$html .= "\n";
		$html .= '<input class="submitbutton" type="submit" name="'.$form->name.'_submit" value="Save"/>';
		$html .= '&nbsp;&nbsp;';
		$html .= '<input style="width:7em;" class="formbutton" type="button" name="'.$form->name.'_cancel" onclick="javascript:history.go(-1);" value="Cancel"/>';
		$html .= "\n";
		$html .= '</div>';
		$html .= "\n";

		foreach ($form->hidden as $e) {
			$html .= '<input type="hidden" name="'.$e->name.'" id="'.$e->name.'" value="'.htmlentities($e->value,ENT_QUOTES).'"/>';
		}

		$html .= '</form>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= "\n";

		return $html;
	}
}


class Cgn_Form_LayoutFancyDelete extends Cgn_Form_Layout {

	function renderForm($form) {
		$html = '<div style="padding:1px;background-color:#FFF;border:1px solid silver;width:'.$form->width.';">';
		$html .= '<div class="cgn_form" style="padding:5px;background-color:#EEE;">';
		if ($form->label != '' ) {
			$html .= '<h3 style="padding:0px 0px 3pt;">'.$form->label.'</h3>';
			$html .= "\n";
		}
		if ($form->formHeader != '' ) {
			$html .= '<P style="padding:0px 0px 3pt; text-align:justify;">'.$form->formHeader.'</P>';
			$html .= "\n";
		}
//		$attribs = array('method'=>$form->method, 'name'=>$form->name, 'id'=>$form->id);
		$action = '';
		if ($form->action) {
			$action = ' action="'.$form->action.'" ';
		}
		$html .= '<form method="'.$form->method.'" name="'.$form->name.'" id="'.$form->name.'"'.$action;
		if ($form->enctype) {
			$html .= ' enctype="'.$form->enctype.'"';
		}
		$html .= '>';
		$html .= "\n";
		$html .= '<table border="0" cellspacing="3" cellpadding="3">';
		foreach ($form->elements as $e) {
			$html .= '<tr><td valign="top">';
			$html .= $e->label.'</td><td valign="top">';
			if ($e->type == 'textarea') {
				$html .= '<textarea class="forminput" name="'.$e->name.'" id="'.$e->name.'" rows="'.$e->rows.'" cols="'.$e->cols.'" >'.htmlentities($e->value,ENT_QUOTES).'</textarea>';
			} else if ($e->type == 'radio') {
				foreach ($e->choices as $cid => $c) {
					$selected = '';
					if ($c['selected'] == 1) { $selected = ' CHECKED="CHECKED" '; }
				$html .= '<input type="radio" name="'.$e->name.'" id="'.$e->name.sprintf('%02d',$cid+1).'" value="'.sprintf('%02d',$cid+1).'"'.$selected.'/>'.$c['title'].'<br/> ';
				}
			} else if ($e->type == 'select') {
				$html .= $e->toHtml();
			} else if ($e->type == 'label') {
				$html .= $e->toHtml();
			} else if ($e->type == 'contentLine') {
				$html .= "<span style=\"text-align: justify;\">";
				$html .= $e->toHtml();
				$html .= "</span>";
			} else if ($e->type == 'check') {
				foreach ($e->choices as $cid => $c) {
					$selected = '';
					if ($c['selected'] == 1) { $selected = ' CHECKED="CHECKED" '; }
				$html .= '<input type="checkbox" name="'.$e->name.'[]" id="'.$e->name.sprintf('%02d',$cid+1).'" value="'.$c['value'].'"'.$selected.'/>'.$c['title'].'<br/> ';
				}
			} else {
				$html .= '<input class="forminput" type="'.$e->type.'" name="'.$e->name.'" id="'.$e->name.'" value="'.htmlentities($e->value,ENT_QUOTES).'" size="'.$e->size.'"/>';
			}
			$html .= '</td></tr>';
		}
		$html .= '</table>';
		if ($form->formFooter != '' ) {
			$html .= '<P style="padding:0px 0px 3pt;text-align:justify;">'.$form->formFooter.'</P>';
			$html .= "\n";
		}
		$html .= '<div style="width:90%;text-align:right;">';
		$html .= "\n";
		$html .= '<input class="submitbuttondelete" type="submit" name="'.$form->name.'_submit" value="Delete"/>';
		$html .= '&nbsp;&nbsp;';
		$html .= '<input style="width:7em;" class="formbutton" type="button" name="'.$form->name.'_cancel" onclick="javascript:history.go(-1);" value="Cancel"/>';
		$html .= "\n";
		$html .= '</div>';
		$html .= "\n";

		foreach ($form->hidden as $e) {
			$html .= '<input type="hidden" name="'.$e->name.'" id="'.$e->name.'" value="'.htmlentities($e->value,ENT_QUOTES).'"/>';
		}

		$html .= '</form>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= "\n";

		return $html;
	}
}


?>
