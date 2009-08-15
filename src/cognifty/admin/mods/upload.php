<?php
Cgn::loadLibrary('Html_widgets::Lib_Cgn_Widget');
Cgn::loadLibrary('Html_widgets::Lib_Cgn_Toolbar');
Cgn::loadLibrary('Form::Lib_Cgn_Form');
//MVC
Cgn::loadLibrary('Lib_Cgn_Mvc');
Cgn::loadLibrary('Lib_Cgn_Mvc_Table');
//module manager and utilities
Cgn::loadLibrary('Mod::Lib_Cgn_Mod_Mgr');

Cgn::loadModLibrary('Mods::Cgn_Install_Mgr', 'admin');

/**
 * Handle uploading of gzip files and extraction to var/tmp/install_*
 */
class Cgn_Service_Mods_Upload extends Cgn_Service_Admin {
	 
	public $displayName = 'Modules - Upload a module';

	/**
	 * Show upload form
	 */
	public function mainEvent($req, &$t) {
		$this->_makeToolbar($t);

		$result = $this->_createLandingFolder();
		if (!$result) {
			$u = $req->getUser();
			$u->addMessage('Cannot create folder for saving module uploads. Make sure "var" is writable.', 'msg_warn');
		}

		$t['uploadForm'] = $this->_loadUploadForm(array(), 0);
	}


	/**
	 * Grab a file from $_FILES and extract it to 
	 * CGN_BASE.var/tmp/install_{rand}
	 */
	public function saveModEvent($req, &$t) {

		$result = $this->_createLandingFolder();
		if (!$result) {
			$u = $req->getUser();
			$u->addMessage('Cannot create folder for saving module uploads. Make sure "var" is writable.', 'msg_warn');
			return TRUE;
		}

		$landing = BASE_DIR.'var/tmp/';
		$dirname = $this->_mkranddir($landing, 'install_');

		$ziptmp = $_FILES['modzip']['tmp_name'];
		include(CGN_LIB_PATH.'/phing/lib/Zip.php');
		$req->setSessionVar('mod_install_current', $dirname);
		$zip = new Archive_Zip($ziptmp);
		$zip->extract(array('add_path'=>$landing.$dirname));

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'mods', 'install', '');
	}

	protected function _loadUploadForm($values=array(), $id) {
		$f = new Cgn_FormAdmin('up01','','POST','multipart/form-data');
		$f->label = 'Upload a module from your computer.';
		$f->width = '600px';

		$input = new Cgn_Form_ElementFile('modzip', 'Module Package');
		$f->appendElement($input, '');

		/*
		$check = new Cgn_Form_ElementCheck('old_tag', 'Existing Tag');
		foreach ($values as $_v) {
			$check->addChoice($_v->get('name'), $_v->get('link_text'), TRUE);
		}
		$f->appendElement($check);
		 */
		$f->action = cgn_adminurl('mods', 'upload', 'saveMod');
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$id);
		return $f;
	}

	protected function _makeToolbar(&$t) {
		//create toolbar action buttons
		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();

		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods','main'), "Back to Modules");
		$t['toolbar']->addButton($btn1);
	}

	/**
	 * Try to make CGN_BASE.'var/tmp/' and make sure it's writable
	 */
	protected function _createLandingFolder() {
		$landing = BASE_DIR.'var/tmp/';
		if (! @file_exists($landing)) {
			$res = @mkdir($landing);
		}
		return @is_writable($landing);
	}

	/**
	 * return only the relative, random dir name with any prefix, 
	 * not the full path
	 *
	 */
	protected function _mkranddir($loc='/tmp/', $prefix='') {
		if (substr($loc, -1) !== '/') {
			$loc .= '/';
		}
		$rnd = rand(1000000, 9999999);
		$dirname = $prefix . base_convert($rnd, 10, 26);
		if (!@mkdir($loc.$dirname)) {
			return FALSE;
		}
		return $dirname;
	}
}

