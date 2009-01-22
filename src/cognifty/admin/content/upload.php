<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');

Cgn::loadModLibrary('Content::Cgn_Content');

class Cgn_Service_Content_Upload extends Cgn_Service_Admin {

	function Cgn_Service_Content_Upload () {

	}

	/**
	 * show a form to upload a new content item
	 */
	function mainEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$values = array();

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
	}

	function saveUploadEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		if ($id > 0 ) {
			$content = new Cgn_Content($id);
		} else {
			$content = new Cgn_Content($id);
			$content->dataItem->type = 'file';
			//save mime
			$mime = $req->cleanString('mime');
		}

		if (isset($_FILES['filename'])
			&& $_FILES['filename']['error'] == UPLOAD_ERR_OK) {
			$content->dataItem->binary = file_get_contents($_FILES['filename']['tmp_name']);
		} else {
			trigger_error('file not uploaded properly ('.$_FILES['filename']['error'].')');
			return false;
		}
		//encode the binary data properly (nulls and quotes)
		$content->dataItem->_bins['binary'] = 'binary';
		$content->setTitle( $req->cleanString('title') );
		$content->dataItem->caption = $req->cleanString('description');
		$content->dataItem->filename = trim($_FILES['filename']['name']);
		$content->dataItem->mime = trim($_FILES['filename']['type']);

		$content->dataItem->edited_on = time();

		$content->dataItem->type = 'file';
		$newid = $content->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','view','',array('id'=>$newid));
	}

	function _loadContentForm($values=array()) {
		$this->displayName = 'Upload a File';

		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('up01','','POST','multipart/form-data');
		$f->width="600px";
		$f->action = cgn_adminurl('content','upload','saveUpload');
		$f->label = 'Choose a file from your computer to upload.';
		$f->appendElement(new Cgn_Form_ElementHidden('MAX_FILE_SIZE'),2000000);
		$f->appendElement(new Cgn_Form_ElementFile('filename','Upload',55));
		$titleInput = new Cgn_Form_ElementInput('title','Save As',55);
		if (isset($values['title'])) {
			$f->appendElement($titleInput, $values['title']);
		} else {
			$f->appendElement($titleInput);
		}
		$captionInput = new Cgn_Form_ElementInput('description','Description',55);
		if (isset($values['caption'])) {
			$f->appendElement($captionInput, $values['caption']);
		} else {
			$f->appendElement($captionInput);
		}

		if (isset($values['version'])) {
			$version = new Cgn_Form_ElementLabel('version','Version', $values['version']);
			$f->appendElement($version);
		}

		if (isset($values['cgn_content_id'])) {
			$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_content_id']);
		}
//		$f->appendElement(new Cgn_Form_ElementText('notes','notes',10,50));
		return $f;
	}
}
?>
