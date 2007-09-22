<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_SYS_PATH.'/app-lib/lib_cgn_content.php');
include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
include_once('../cognifty/admin/content/wiki_form.php');

class Cgn_Service_Content_Edit extends Cgn_Service_AdminCrud {

	function Cgn_Service_Content_Edit () {

	}

	function mainEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$mime = $req->cleanString('m');
		$values = array();
		if ($id > 0) {
			$content = new Cgn_Content($id);
			$values = $content->dataItem->valuesAsArray();
			$t['version'] = $content->dataItem->version;
			$mime = $content->dataItem->mime;
			$values['mime'] = $mime;
			$values['edit'] = true;
		} else {
			$content = new Cgn_Content();
			$values['mime'] = $mime;
			$values['edit'] = false;
		}
		$t['form'] = $this->_loadContentForm($values);
		$t['form']->layout = new Cgn_Form_WikiLayout();
		$t['form']->layout->mime = $mime;
		$t['mime'] = $mime;
	}

	/**
	 * Increase the version number while saving
	 */
	function saveEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		if ($id > 0 ) {
			$content = new Cgn_Content($id);
		} else {
			$content = new Cgn_Content();
			$content->setType('text');
			//save mime
			$mime = $req->cleanString('mime');
			if ($mime == 'html') {
				$content->setMime('text/html');
			} else if ($mime == 'wiki') {
				$content->setMime('text/wiki');
			}
		}

		$content->setContent($req->cleanString('content'));

		$content->dataItem->title = $req->cleanString('title');
		$content->dataItem->caption = $req->cleanString('caption');
		$linkText = $req->cleanString('link_text');
		if (strlen($linkText)) {
			$content->dataItem->link_text = $linkText;
		}
		$id = $content->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','view','',array('id'=>$id));
	}

	/**
	 * Override basic crud event, don't allow published content to be deleted.
	 */
	function delEvent(&$req, &$t) {
		$table = $req->cleanString('table');
		$id    = $req->cleanInt($table.'_id');

		$content = new Cgn_DataItem('cgn_content');
		if ($id > 0 ) {
			$content->load($id);
		}

		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT A.*
					FROM cgn_content AS A
					LEFT JOIN cgn_'.$content->sub_type.'_publish AS B
					USING (cgn_content_id)
					WHERE A.sub_type = "'.$content->sub_type.'"
					AND B.cgn_content_id = '.$content->cgn_content_id.'
					');
		if (!$db->nextRecord()) {
			//proceed with delete
			parent::delEvent($req,$t);
		} else {
			Cgn_ErrorStack::throwSessionMessage("Content is in use, cannot delete.");
		}
		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','main');
	}


	/**
	 * Override basic crud event, don't allow published content to be deleted.
	 * This code should be in main, not edit
	 */
	function undoEvent(&$req, &$t) {
		parent::undoEvent($req,$t);
		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','main');
	}


	/**
	 * Auto-generate a form using the form library
	 */
	function _loadContentForm($values=array()) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('content_01');
		$f->width="auto";
		$f->action = cgn_adminurl('content','edit','save');
		$f->label = '';
		$title = new Cgn_Form_ElementInput('title');
		$title->size = 55;

		$f->appendElement($title,$values['title']);
		$caption = new Cgn_Form_ElementInput('caption','Sub-title');
		$caption->size = 55;
		$f->appendElement($caption,$values['caption']);

		if ($values['edit'] == true) {
			$link = new Cgn_Form_ElementInput('link_text','Link');
			$link->size = 55;
			$f->appendElement($link,$values['link_text']);
		}


		$version = new Cgn_Form_ElementLabel('version','Version', $values['version']);
		$f->appendElement($version);

		$textarea = new Cgn_Form_ElementText('content','Content', 35, 90);
		$f->appendElement($textarea,$values['content']);
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_content_id']);
		$f->appendElement(new Cgn_Form_ElementHidden('mime'),$values['mime']);

		return $f;
	}

}
?>
