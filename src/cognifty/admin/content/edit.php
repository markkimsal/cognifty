<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/app-lib/lib_cgn_content.php');
include_once('../cognifty/lib/form/lib_cgn_form.php');
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
		} else {
			$content = new Cgn_Content();
			$values['mime'] = $mime;
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
		$content = new Cgn_DataItem('cgn_content');
		if ($id > 0 ) {
			$content->load($id);
		} else {
			$content->created_on = time();
			$content->type = 'text';
			//save mime
			$mime = $req->cleanString('mime');
			if ($mime == 'html') {
				$content->mime = 'text/html';
			} else if ($mime == 'wiki') {
				$content->mime = 'text/wiki';
			}
		}

		$content->edited_on = time();
		$content->version = $content->version +1;
		$content->content = $req->cleanString('content');
		$content->title = $req->cleanString('title');
		$content->caption = $req->cleanString('caption');
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
					AND B.cgn_content_id IS NOT NULL
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
	 * Auto-generate a form using the form library
	 */
	function _loadContentForm($values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
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
