<?php
Cgn::loadLibrary('Html_widgets::Lib_Cgn_Widget');
Cgn::loadLibrary('Html_widgets::Lib_Cgn_Toolbar');

Cgn::loadLibrary('Mod::Lib_Cgn_Mod_Mgr');

class Cgn_Service_Mods_Utest extends Cgn_Service_Admin {
	 
	public $displayName = 'Modules - Unit Test';
	public $usesConfig  =  TRUE;

	public function mainEvent($req, &$t) {
		$this->_makeToolbar($t);
		//make everything work with multiple modules (just in case)
		// but assume just one is passed for navigation buttons
		$t['amidList'] = array($req->cleanString('amid'));
		$t['midList']  = array($req->cleanString('mid'));

		$isAdmin = FALSE;
		$mid = $req->cleanString('mid');
		if (!$mid) {
			$isAdmin = TRUE;
			$mid = $req->cleanString('amid');
		}

		$this->displayName = 'Test '.ucfirst($mid).' Module';

		//load module info object
		$modInfo = new Cgn_Module_Info($mid, $isAdmin);
		$this->_makeConfigButton($modInfo, $t);

		$u = $req->getUser();
		if ($this->getConfig('simpletest.dir') == '') {
			$u->addMessage('Cannot find simpltest dir.  Please edit the settings for the "Mods" module.', 'msg_warn');
		}
	}

	/**
	 * Run the unit tests, output the result with a bare template
	 */
	public function dorunEvent($req, &$t) {
		$templateStyle = 'bare';

		Cgn_ObjectStore::storeConfig("config://admin/template/name", $templateStyle);

		$amidList = $req->cleanString('amidList');
		$midList = $req->cleanString('midList');
		if (empty($amidList) && empty($midList)) {
			return false;
		}

		$amids = explode(',', $req->cleanString('amidList'));
		$mids  = explode(',', $req->cleanString('midList'));


		if (!empty($amidList)) {
			foreach ($amids as $_amid) {
				$midPath = Cgn::getModulePath($_amid, 'admin');
			}
			if (!is_dir($midPath.DIRECTORY_SEPARATOR.'tests')) {
				echo "no tests for ".$_amid;
				return false;
			}
		}
		if (!empty($midList)) {
			foreach ($mids as $_mid) {
				$midPath = Cgn::getModulePath($_amid, 'admin');
			}
			if (!is_dir($midPath.DIRECTORY_SEPARATOR.'tests')) {
				echo "no tests for ".$_mid;
				return false;
			}
		}

		$_SERVER['argc']=2;
		$_SERVER['argv'][]='php';
		$_SERVER['argv'][]=$midPath.DIRECTORY_SEPARATOR.'tests';
		//require simpletest from configured location
		require_once( implode(DIRECTORY_SEPARATOR, array( $this->getConfig('simpletest.dir'), 'autorun.php')));
		Cgn::loadModLibrary("Mods::Utest_Rundir", "admin");

		simpletest_autorun();
	}

	protected function _makeToolbar(&$t) {
		//create toolbar action buttons
		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();

		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods'), "List All Modules");
		$t['toolbar']->addButton($btn1);
	}


	protected function _makeConfigButton($modInfo, &$t) {

		$midamid = ($modInfo->isAdmin)? 'amid':'mid';
		$mid = $modInfo->codeName;
		$params = array($midamid=>$mid);

		if (!$modInfo->isAdmin) {
			$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods', 'main', 'view', $params), "Back to Module");
			$t['toolbar']->addButton($btn2);
			$btn3 = new Cgn_HtmlWidget_Button(cgn_appurl($mid), "Access This Module");
			$t['toolbar']->addButton($btn3);
		}
		if ($modInfo->isAdmin) {
			$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods', 'main', 'view', $params), "Back to Module");
			$t['toolbar']->addButton($btn2);
			$btn3 = new Cgn_HtmlWidget_Button(cgn_adminurl($mid), "Access This Module");
			$t['toolbar']->addButton($btn3);
		}


		if ($modInfo->hasConfig()) {
			$btn = new Cgn_HtmlWidget_Button(
				cgn_adminurl('mods', 'config', '', array($midamid=>$mid)),
				"Change Settings");
			$t['toolbar']->addButton($btn);
		}
	}

}
