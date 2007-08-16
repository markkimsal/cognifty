<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/app-lib/lib_cgn_content.php');
include_once('../cognifty/lib/form/lib_cgn_form.php');

class Cgn_Service_Content_View extends Cgn_Service_Admin {

	function Cgn_Service_Content_View () {

	}

	function mainEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$t['content'] = new Cgn_DataItem('cgn_content');
		$t['content']->load($id);
		//__ FIXME __ check for a failed load

		$t['showPreview'] = false;
		if (@$t['content']->sub_type == '') {
			$t['useForm'] = $this->_loadUseForm($t['content']->type, $t['content']->valuesAsArray());
		}
		if (@$t['content']->type == 'text' && $t['content']->sub_type != '') {
			$t['showPreview'] = true;
		}
	}




	function _loadUseForm($type,$values=array()) {
		$f = new Cgn_FormAdmin('use_as');
		$f->label = 'Choose how to use this content';

		$radio = new Cgn_Form_ElementRadio('subtype','Choose a type');
		if ($type == 'text') {
			$radio->addChoice('Article');
			$radio->addChoice('Web Page');
//			$radio->addChoice('Blog');
//			$radio->addChoice('News');
			$f->action = cgn_adminurl('content','publish','useAsText');
		} else if ($type == 'file') {
			$radio->addChoice('Web Image');
			$radio->addChoice('Downloadable Attachment');
			$f->action = cgn_adminurl('content','publish','useAsFile');
		}
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_content_id']);

		$f->appendElement($radio);

		return $f;
	}
}
?>
