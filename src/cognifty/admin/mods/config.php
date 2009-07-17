<?php
Cgn::loadLibrary('Html_widgets::Lib_Cgn_Widget');
Cgn::loadLibrary('Html_widgets::Lib_Cgn_Toolbar');
//MVC
Cgn::loadLibrary('Lib_Cgn_Mvc');
Cgn::loadLibrary('Lib_Cgn_Mvc_Table');
//module manager and utilities
Cgn::loadLibrary('Mod::Lib_Cgn_Mod_Mgr');

class Cgn_Service_Mods_Config extends Cgn_Service_Admin {

	/**
	 * show details about mid module
	 */
	public function mainEvent($req, &$t) {
		$isAdmin = FALSE;
		$mid = $req->cleanString('mid');
		if (!$mid) {
			$isAdmin = TRUE;
			$mid = $req->cleanString('amid');
		}

		$t['header'] = '<h3>'.ucfirst($mid).' Module Details</h3>';

		//load module info object
		$modInfo = new Cgn_Module_Info($mid, $isAdmin);

		//create toolbar action buttons
		$t['mytoolbar'] = new Cgn_HtmlWidget_Toolbar();
		if (!$modInfo->isInstalled) {
			$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods','install','', array('mid'=>$mid)), "Install Module");
			$t['mytoolbar']->addButton($btn1);
		}
		if ($modInfo->hasUpgrade()) {
			$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods','install','', array('mid'=>$mid)), "Upgrade Module");
			$t['mytoolbar']->addButton($btn2);
		}

		if (!$modInfo->isAdmin) {
			$btn3 = new Cgn_HtmlWidget_Button(cgn_appurl($mid), "Access Module");
			$t['mytoolbar']->addButton($btn3);
		}
		if ($modInfo->isAdmin) {
			$btn3 = new Cgn_HtmlWidget_Button(cgn_adminurl($mid), "Access Module");
			$t['mytoolbar']->addButton($btn3);
		}

		$this->_makeConfigButton($modInfo, $t);


		//make data table
		$table = new Cgn_Mvc_TableModel();
		$table->data[] = array(
			'Module Name',
			$modInfo->codeName, 
		);
		$table->data[] = array(
			'Installed Version',
			$modInfo->getVersionString(), 
		); 
		$table->data[] = array(
			'Available Version',
			$modInfo->availableVersion, 
		);
		$table->headers = array('Key', 'Value');
		$t['tableView'] = new Cgn_Mvc_AdminTableView($table);
		$t['tableView']->setColWidth( 0, '50%' );

		//show readme
		if ($modInfo->hasConfig()) {
			$t['readmeLabel'] = '<h3>Config File</h3>';
			$t['readmeContents'] = file_get_contents($modInfo->fullModulePath.'/config.ini');
		}
	}

	protected function _makeToolbar(&$t) {
		//create toolbar action buttons
		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();

		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods','upload'), "Upload Module");
		$t['toolbar']->addButton($btn1);

		/*
		if (!$modInfo->isInstalled) {
		}
		if ($modInfo->hasUpgrade()) {
			$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods','install','', array('mid'=>$mid)), "Upgrade Module");
			$t['toolbar']->addButton($btn2);
		}

		if (!$modInfo->isAdmin) {
			$btn3 = new Cgn_HtmlWidget_Button(cgn_appurl($mid), "Access Module");
			$t['toolbar']->addButton($btn3);
		}
		 */
	}

	protected function _makeConfigButton($modInfo, &$t) {
		if ($modInfo->hasConfig()) {
			$midamid = ($modInfo->isAdmin)? 'amid':'mid';
			$mid = $modInfo->codeName;
			$btn = new Cgn_HtmlWidget_Button(
				cgn_adminurl('mods', 'config', '', array($midamid=>$mid)),
				"Change Settings");
			$t['mytoolbar']->addButton($btn);
		}
	}
}
?>
