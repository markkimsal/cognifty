<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/app-lib/lib_cgn_content.php');
include_once('../cognifty/lib/form/lib_cgn_form.php');
include_once('../cognifty/admin/content/wiki_form.php');

class Cgn_Service_Content_Edit extends Cgn_Service_Admin {

	function Cgn_Service_Content_Edit () {

	}

	function mainEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$content = new Cgn_Content($id);
		$values = $content->dataItem->valuesAsArray();
		$t['version'] = $content->dataItem->version;

		$t['form'] = $this->_loadContentForm($values);
		$t['form']->layout = new Cgn_Form_WikiLayout();
	}

	/**
	 * Increase the version number while saving
	 */
	function saveEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$content = new Cgn_DataItem('cgn_content');
		$content->load($id);
		$content->version = $content->version +1;
		$content->content = $req->cleanString('content');
		$content->title = $req->cleanString('title');
		$content->caption = $req->cleanString('caption');
		$content->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','view','',array('id'=>$id));
	}



	function _loadContentForm($values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('content_01');
		$f->action = cgn_adminurl('content','edit','save');
		$f->label = 'Edit content';
		$f->appendElement(new Cgn_Form_ElementInput('title'),$values['title']);
		$f->appendElement(new Cgn_Form_ElementInput('caption','Sub-title'),$values['caption']);
		$f->appendElement(new Cgn_Form_ElementText('content'),$values['content']);
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_content_id']);

		return $f;
	}
}
?>
