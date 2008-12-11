<?php


class Cgn_Form {

	var $name      = 'cgn_form';
	var $elements  = array();
	var $hidden    = array();
	var $label     = '';
	var $action;
	var $method;
	var $enctype;
	var $layout     = NULL;           //layout object to render the form
	var $width      = '450px';
	var $style      = array();
	var $showSubmit = TRUE;
	var $labelSubmit = 'Submit';
	var $showCancel = TRUE;
	var $labelCancel = 'Cancel';
	var $actionCancel = 'javascript:history.go(-1);';



	var $formHeader = '';
	var $formFooter = '';

	function Cgn_Form($name = 'cgn_form', $action='', $method='POST', $enctype='') {
		$this->name = $name;
		$this->action = $action;
		$this->method = $method;
		$this->enctype = $enctype;
	}

	function appendElement($e,$value='') {
		if ($value !== '') {
			$e->setValue($value);
//			$e->value = $value;
		}
		if ($e->type == 'hidden') {
			$this->hidden[] = $e;
		} else {
			$this->elements[] = $e;
		}
	}

	function toHtml($layout=NULL) {
		if ($layout !== NULL) {
			return $layout->renderForm($this);
		}
		if ($this->layout !== NULL) {
			return $this->layout->renderForm($this);
		}
		$layout = new Cgn_Form_Layout();
		return $layout->renderForm($this);
	}

	function setShowSubmit($show=TRUE,$labelSubmit='Submit') {
		$this->showSubmit = $show;
		$this->labelSubmit = $labelSubmit;
	}

	function setShowCancel($show=TRUE,$labelCancel='Cancel',$actionCancel='javascript:history.go(-1);') {
		$this->showCancel = $show;
		$this->labelCancel = $labelCancel;
		$this->actionCancel = $actionCancel;
	}
}

class Cgn_FormAdmin extends Cgn_Form {

	/**
	 * Use Fancy layout
	 */
	function toHtml($layout=NULL) {
		if ($layout !== NULL) {
			return $layout->renderForm($this);
		}
		if ($this->layout !== NULL) {
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
	function toHtml($layout=NULL) {
		if ($layout !== NULL) {
			return $layout->renderForm($this);
		}
		if ($this->layout !== NULL) {
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
	var $jsOnChange = '';

	function Cgn_Form_Element($name,$label=-1, $size=30) {
		$this->name = $name;
		$this->label = $label;
		if ($this->label == -1) {
			$this->label = ucfirst($this->name);
		}
		$this->size = $size;
	}

	/**
	 * Set the value for this element
	 */
	function setValue($v) {
		$this->value = $v;
	}

	public function toHtml() {
		if ($this->size) {
			$size = 'size="'.$this->size.'"';
		} else {
			$size = '';
		}
		return '<input type="'.$this->type.'" name="'.$this->name.'" id="'.$this->name.'" '.$size.' value="'.htmlentities($this->value,ENT_QUOTES).'" />';
	}

	/**
	 * Add custom javascript for the onchange event.
	 */
	public function setJsOnChange($js) {
		$this->jsOnChange = $js;
	}

	/**
	 * Get custom javascript for the onchange event.
	 */
	public function getJsOnChange() {
		return $this->jsOnChange;
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

	function addChoice($c, $v='', $selected=0) {
		$top = count($this->choices);
		$this->choices[$top]['title'] = $c;
		$this->choices[$top]['selected'] = $selected;
		$this->choices[$top]['value'] = $v;
		return count($this->choices)-1;
	}

	/**
	 * Sets the selected choices index
	 */
	function setValue($v) {
		foreach ($this->choices as $idx=>$c) {
			if ($c['value'] === $v) {
				$this->choices[$idx]['selected'] = true;
				break;
			}
		}
	}

	function toHtml() {
		$html = '';
		foreach ($this->choices as $cid => $c) {
			$selected = '';
			if ($c['value'] === '') {
				$value = sprintf('%02d', $cid+1);
			} else {
				$value = $c['value'];
			}
			if ($c['selected'] == 1) { $selected = ' CHECKED="CHECKED" '; }
		$html .= '<input type="radio" name="'.$this->name.'" id="'.$this->name.sprintf('%02d',$cid+1).'" value="'.$value.'"'.$selected.'/><label for="'.$this->name.sprintf('%02d',$cid+1).'">'.$c['title'].'</label><br/> ';
		}
		return $html;
	}

}

class Cgn_Form_ElementSelect extends Cgn_Form_Element {
	var $type = 'select';
	var $choices = array();
	var $size = 1;
	var $selectedVal = NULL;

	function Cgn_Form_ElementSelect($name,$label=-1, $size=7, $selectedVal = NULL) {
		parent::Cgn_Form_Element($name, $label, $size);
		$this->selectedVal = $selectedVal;
	}

	function addChoice($c, $v='', $selected=0) {
		$top = count($this->choices);

		if ($this->selectedVal == $v) {
			$selected = true;
		}

		$this->choices[$top]['title'] = $c;
		$this->choices[$top]['selected'] = $selected;
		$this->choices[$top]['value'] = $v;

		return count($this->choices)-1;
	}

	/**
	 * Sets the selected choices index
	 */
	function setValue($v) {
		foreach ($this->choices as $idx=>$c) {
			if ($c['value'] === $v) {
				$this->choices[$idx]['selected'] = true;
				break;
			}
		}
	}

	function toHtml() {
		$onchange = '';
		if ($this->jsOnChange !== '') {
			$onchange = ' onchange="'.$this->jsOnChange.'" ';
		}
		$html = '<select name="'.$this->name.'" id="'.$this->name.'" size="'.$this->size.'" '.$onchange.'>';
		foreach ($this->choices as $cid => $c) {
			$selected = '';
			if ($c['selected'] == 1) { $selected = ' SELECTED="SELECTED" '; }
			if ($c['value'] != '') { $value = ' value="'.htmlentities($c['value']).'" ';} else { $value = ''; }
		$html .= '<option id="'.$this->name.sprintf('%02d',$cid+1).'" '.$value.$selected.'>'.$c['title'].'</option> '."\n";
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

	/**
	 * If only one choice, don't add the array []
	 */
	function getName() {
		if ( count($this->choices) < 2) {
			return $this->name;
		} else {
			return $this->name.'[]';
		}
	}

	/**
	 * Set an array of 'VALUES' which should be "selected".
	 */
	function setValue($x) {
		$this->values = $x;
		if(is_array($x)) {
			foreach($this->values as $k=>$v) {
			}
		}
	}

	function toHtml() {
		$html = '';
		foreach ($this->choices as $cid => $c) {
			$selected = '';
			if ($c['selected'] == 1) { $selected = ' CHECKED="CHECKED" '; }
			if(in_array($c['value'], $this->values)) { $selected = ' CHECKED="CHECKED" '; }
		$html .= '<input type="checkbox" name="'.$this->getName().'" id="'.$this->name.sprintf('%02d',$cid+1).'" value="'.$c['value'].'"'.$selected.'/><label for="'.$this->name.sprintf('%02d',$cid+1).'">'.$c['title'].'</label><br/> ';
		}
		return $html;
	}
}


class Cgn_Form_ElementDate extends Cgn_Form_Element {
	var $type = 'date';

	function Cgn_Form_ElementDate($name,$label=-1, $size=15) {
		$this->name = $name;
		$this->label = $label;
		if ($this->label == -1) {
			$this->label = ucfirst($this->name);
		}
		$this->size = $size;
	}

	function toHtml() {
		$html = '<input name="'.$this->name.'" id="'.$this->name.'" size="'.$this->size.'" value="'.$this->value.'" />';
		return $html."&nbsp;<input class=\"popup_cal\" type=\"button\" name=\"".$this->name."_btn\" value=\"Calendar\">\n";
	}
}

class Cgn_Form_Processor {
}


class Cgn_Form_Layout {

	function renderForm($form) {
		$html = '';
		$html .= '<div class="formContainer">'."\n";
		if ($form->label != '' ) {
			$html .= '<p class="cgn_form_header">'.$form->label.'</p>';
			$html .= "\n";
		}
		if ($form->formHeader != '' ) {
			$html .= '<p class="cgn_form_header_content">'.$form->formHeader.'</p>';
			$html .= "\n";
		}

//		$attribs = array('method'=>$form->method, 'name'=>$form->name, 'id'=>$form->id);
		$action = '';
		if ($form->action) {
			$action = ' action="'.$form->action.'" ';
		}
		$html .= '<form class="data_form" method="'.$form->method.'" name="'.$form->name.'" id="'.$form->name.'"'.$action;
		if ($form->enctype) {
			$html .= ' enctype="'.$form->enctype.'"';
		}
		$html .= $this->printStyle($form);
		$html .= '>';
		$html .= "\n";
		$html .= '<table class="cgn_form_table">'."\n";
		foreach ($form->elements as $e) {
			$html .= '<tr><td class="cgn_form_cell_label" valign="top">'."\n";
			$html .= $e->label.'</td><td class="cgn_form_cell_input" valign="top">'."\n";
			if ($e->type == 'textarea') {
				$html .= '<textarea name="'.$e->name.'" id="'.$e->name.'" rows="'.$e->rows.'" cols="'.$e->cols.'" >'.htmlentities($e->value,ENT_QUOTES).'</textarea>'."\n";
			} else if ($e->type != '') {
				$html .= $e->toHtml();
			} else {
				$html .= '<input type="'.$e->type.'" name="'.$e->name.'" id="'.$e->name.'" value="'.htmlentities($e->value,ENT_QUOTES).'" size="'.$e->size.'"/>'."\n";
			}
			$html .= '</td></tr>'."\n";
		}
		if ($form->formFooter != '') {
			$html .= '<tr><td class="cgn_form_footer_row" colspan="2">'."\n";
				$html .= '<P>'.$form->formFooter.'</P>'."\n";
			$html .= '</td></tr>'."\n";
		}
		$trailingHtml = '';
		if (count($form->hidden)) {
			foreach ($form->hidden as $e) {
				$trailingHtml .= '<input type="hidden" name="'.$e->name.'" id="'.$e->name.'"';
				$trailingHtml .= ' value="'.htmlentities($e->value,ENT_QUOTES).'"/>'."\n";
			}
		}

		if ($form->showSubmit || $form->showCancel) {
			$html .= '<div class="formButtonContainer01">'."\n";
			if ($form->showSubmit == TRUE) {
				$trailingHtml .= '<input type="submit" class="containerButtonSubmit" name="'.$form->name.'_submit" value="'.$form->labelSubmit.'"/>'."\n";
				$trailingHtml .= "\n";
			}
			if ($form->showCancel == TRUE) {
				$trailingHtml .= '<input type="button" class="containerButtonCancel" name="'
					// SCOTTCHANGE
					// .$form->name.'_cancel" onclick="javascript:history.go(-1);" value="'.$form->labelCancel.'"/>';
					.$form->name.'_cancel" onclick="'.$form->actionCancel.'" value="'.$form->labelCancel.'"/>';
				$trailingHtml .= "\n";
			}
			$trailingHtml .= '</div>'."\n";		
		}
		if ($trailingHtml !== '') {
			$html .= '<tr><td class="cgn_form_last_row" colspan="2">'."\n";
			$html .= $trailingHtml."\n";
			$html .= '</td></tr>'."\n";
		}

		$html .= '</table>'."\n";
		$html .= '</form>'."\n";
		$html .= '</div>'."\n";
		
		return $html;
	}

	function printStyle($form) {
		if ( count ($form->style) < 1) { return ''; }
		$html  = '';
		$html .= ' style="';
		foreach ($form->style as $k=>$v) {
			$html .= "$k:$v;";
		}
		return $html.'" ';
	}
}


class Cgn_Form_LayoutFancy extends Cgn_Form_Layout {

	function renderForm($form) {
		$html = '<div style="padding:1px;background-color:#FFF;border:1px solid silver;width:'.$form->width.';">';
		$html .= '<div class="cgn_form" style="padding:5px;background-color:#EEE;">';
		if ($form->label != '' ) {
			$html .= '<h3 style="padding:0px 0px 13pt;">'.$form->label.'</h3>';
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
		$html .= '<form class="data_form" method="'.$form->method.'" name="'.$form->name.'" id="'.$form->name.'"'.$action;
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
			} else if ($e->type == 'contentLine') {
				$html .= "<span style=\"text-align: justify;\">";
				$html .= $e->toHtml();
				$html .= "</span>";
			} else if ($e->type != '') {
				$html .= $e->toHtml();
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
		if ($form->showSubmit == TRUE) {
			$html .= '<input class="submitbutton" type="submit" name="'.$form->name.'_submit" value="Save"/>';
			$html .= '&nbsp;&nbsp;';
		}
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
		$html .= '<form class="data_form" method="'.$form->method.'" name="'.$form->name.'" id="'.$form->name.'"'.$action;
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
				$html .= '<input type="checkbox" name="'.$e->name.'[]" id="'.$e->name.sprintf('%02d',$cid+1).'" value="'.$c['value'].'"'.$selected.'/><label for="'.$e->name.sprintf('%02d',$cid+1).'">'.$c['title'].'</label><br/> ';
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
		if ($form->showSubmit == TRUE) {
			$html .= '<input class="submitbuttondelete" type="submit" name="'.$form->name.'_submit" value="Delete"/>';
			$html .= '&nbsp;&nbsp;';
		}
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
