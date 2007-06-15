<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/app-lib/lib_cgn_content.php');

class Cgn_Service_Content_Edit extends Cgn_Service_Admin {

	function Cgn_Service_Content_Edit () {

	}

	function mainEvent(&$req, &$t) {
		$t['form'] = $this->_loadContentForm();
	}


	function _loadContentForm($values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('reg');
		$f->action = cgn_adminurl('content','main','save');
		$f->label = 'Edit content';
		$f->appendElement(new Cgn_Form_ElementInput('title'),$values['title']);
		$f->appendElement(new Cgn_Form_ElementInput('caption','Sub-title'));
		$f->appendElement(new Cgn_Form_ElementText('content'));
		$f->appendElement(new Cgn_Form_ElementHidden('event'),'save');
		return $f;
	}
}
?>
