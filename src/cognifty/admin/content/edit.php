<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
include_once(CGN_SYS_PATH.'/app-lib/form/wikilayout.php');

Cgn::loadModLibrary('Content::Cgn_Content');

class Cgn_Service_Content_Edit extends Cgn_Service_AdminCrud {

	function Cgn_Service_Content_Edit () {

	}

	function mainEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$mime = $req->cleanString('m');
		$type = $req->cleanString('type');
		$values = array();

		if ($type == 'web') {
			$values['title'] = 'New Page';
		}

		if ($id > 0) {
			$content = new Cgn_Content($id);
			if (strlen($content->dataItem->link_text) < 1) {
				$content->setLinkText();
			}
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

		$t['headerPage'] = '<h2 class="header_page">Editing '.strtoupper($mime).' Content</h2>';
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

		$content->setContent($req->cleanMultiLine('content'));

		//excerpt is description
		$content->setDescription($req->cleanMultiLine('content_ex'));

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

		if ($content->sub_type == '') {
			//this item was never published as anyting
			parent::delEvent($req,$t);
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl(
				'content','main');
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

	function saveAttrEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
//		$t['content'] = new Cgn_DataItem('cgn_content');
//		$t['content']->load($id);

		$contentObj = new Cgn_Content($id);
		$contentObj->loadAllAttributes();
		$is_portal = $req->cleanString('is_portal');
		if( $is_portal !== NULL) {
			if ( $is_portal === 'yes') {
				$contentObj->setAttribute('is_portal', 1, 'int');
			} else {
				$contentObj->setAttribute('is_portal', 0, 'int');
			}
			$contentObj->save();
		}
		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','view','',array('id'=>$id));
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

//		if ($values['edit'] == true) {
			$link = new Cgn_Form_ElementInput('link_text','URL text<br/>(optional)');
			$link->size = 55;
			$f->appendElement($link,$values['link_text']);
//		}


		$version = new Cgn_Form_ElementLabel('version','Version', $values['version']);
		$f->appendElement($version);

		$textarea = new Cgn_Form_ElementText('content','', 35, 90);
		$textarea->excerpt = $values['description'];
		$f->appendElement($textarea,$values['content']);
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_content_id']);
		$f->appendElement(new Cgn_Form_ElementHidden('mime'),$values['mime']);

		return $f;
	}

}
?>
