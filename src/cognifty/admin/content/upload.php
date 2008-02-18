<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_SYS_PATH.'/app-lib/lib_cgn_content.php');

class Cgn_Service_Content_Upload extends Cgn_Service_Admin {

	function Cgn_Service_Content_Upload () {

	}

	function mainEvent(&$req, &$t) {
		$t['form'] = $this->_loadContentForm();
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

		$content->dataItem->binary = file_get_contents($_FILES['filename']['tmp_name']);
		//encode the binary data properly (nulls and quotes)
		$content->dataItem->_bins['binary'] = 'binary';
		$content->setTitle( $req->cleanString('title') );
		$content->dataItem->caption = $req->cleanString('caption');
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
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('up01','','POST','multipart/form-data');
		$f->width="600px";
		$f->action = cgn_adminurl('content','upload','saveUpload');
		$f->label = 'Upload a file';
		$f->appendElement(new Cgn_Form_ElementHidden('MAX_FILE_SIZE'),2000000);
		$f->appendElement(new Cgn_Form_ElementFile('filename','Upload','file','',55));
		$f->appendElement(new Cgn_Form_ElementInput('title','Save As','','',55));
		$f->appendElement(new Cgn_Form_ElementInput('description','Description','','',55));
//		$f->appendElement(new Cgn_Form_ElementText('notes','notes',10,50));
		return $f;
	}
}
?>
