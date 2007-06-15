<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/app-lib/lib_cgn_content.php');

class Cgn_Service_Content_Upload extends Cgn_Service_Admin {

	function Cgn_Service_Content_Upload () {

	}

	function mainEvent(&$req, &$t) {
		$t['form'] = $this->_loadContentForm();
	}


	function saveUploadEvent(&$req, &$t) {
		$content = new Cgn_DataItem('cgn_content');
//		print_r($_FILES);exit();
		$content->_pkey = 'cgn_content_id';
		$content->binary = file_get_contents($_FILES['filename']['tmp_name']);
		//encode the binary data properly (nulls and quotes)
		$content->_types['binary'] = 'binary';
		$content->title = $req->cleanString('title');
		$content->caption = $req->cleanString('caption');
		$content->filename = trim($_FILES['filename']['name']);
		$content->type = 'file';
		$content->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','main');
	}



	function _loadPublishForm($values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('publish');
		$f->action = cgn_adminurl('content','edit','publishAs');
		$f->label = 'Publish Content';
		$radio = new Cgn_Form_ElementRadio('subtype','Choose a type');
		$radio->addChoice('Article');
		$radio->addChoice('Blog');
		$radio->addChoice('News');
		$f->appendElement($radio);
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['id']);
		$f->appendElement(new Cgn_Form_ElementHidden('event'),'publishAs');
		return $f;
	}



	function _loadContentForm($values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('up01','','POST','multipart/form-data');
		$f->action = cgn_adminurl('content','upload','saveUpload');
		$f->label = 'Upload a file';
		$f->appendElement(new Cgn_Form_ElementHidden('MAX_FILE_SIZE'),2000000);
		$f->appendElement(new Cgn_Form_ElementFile('filename','Upload','file','',55));
		$f->appendElement(new Cgn_Form_ElementInput('title','Save As','','',55));
		$f->appendElement(new Cgn_Form_ElementInput('description','Description','','',55));
		$f->appendElement(new Cgn_Form_ElementText('notes','notes',10,75));
		return $f;
	}
}
?>
