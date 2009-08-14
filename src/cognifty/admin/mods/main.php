<?php
Cgn::loadLibrary('Html_widgets::Lib_Cgn_Widget');
Cgn::loadLibrary('Html_widgets::Lib_Cgn_Toolbar');
//MVC
Cgn::loadLibrary('Lib_Cgn_Mvc');
Cgn::loadLibrary('Lib_Cgn_Mvc_Table');
//module manager and utilities
Cgn::loadLibrary('Mod::Lib_Cgn_Mod_Mgr');

class Cgn_Service_Mods_Main extends Cgn_Service_Admin {
	 
	public $displayName = 'Modules';

	/**
	 * Create a table to display the modules in
	 */
	function mainEvent($req, &$t) {
		$this->_makeToolbar($t);

		$modList = Cgn_Module_Manager::getInstalledModules();
//		$modList = array(0=>array('a','b','c'),1=>array('a','b','c'),2=>array('a','b','c'));
		$table = new Cgn_Mvc_TableModel();
		foreach ($modList as $modInfo) {
			if ($modInfo->isAdmin) { continue; }
			$isInstalled = 'No';
			if ($modInfo->isInstalled) { 
				$isInstalled = 'Yes';
				if ($modInfo->hasUpgrade()) {
					$isInstalled = 'Upgrade Available';
				}
			} else {
			}
			$midamid = ($modInfo->isAdmin)? 'amid':'mid';
			//config links
			if ($modInfo->hasConfig()) {
				$configLink = cgn_adminlink(
					"Change Settings",
					'mods', 'config', '', array($midamid=>$modInfo->codeName)
				);
			} else {
				$configLink = '<span style="font-weight:bold;color:#eee">Change Settings</span>';
			}

			//access links
			$accessLink = cgn_applink(
				"Go To Module",
				$modInfo->codeName
			);

			$table->data[]  = array(
				cgn_adminlink($modInfo->getDisplayName(), 'mods', 'main', 'view', array('mid'=>$modInfo->codeName)),
				$modInfo->getVersionString(),
				$isInstalled,
				$configLink . ' | '. $accessLink
				);
		}
		$table->headers = array('Module', 'Version', 'Installed', 'Actions');

		$t['renderer'] = new Cgn_Mvc_AdminTableView($table);
		$t['renderer']->setColWidth( 0, '50%' );
		$t['renderer']->setColWidth( 1, '10%' );

//		$t['renderer']->setColRenderer( 2, new Cgn_Mvc_Table_YesNoRenderer() );

		//admin modules
		$adminTable = new Cgn_Mvc_TableModel();
		foreach ($modList as $modInfo) {
			if ($modInfo->isFrontend) { continue; }
			$isInstalled = 'No';
			if ($modInfo->isInstalled) { 
				$isInstalled = 'Yes';
			} else {
				if ($modInfo->hasUpgrade()) {
					$isInstalled = 'Upgrade Available';
				}
			}

			$midamid = ($modInfo->isAdmin)? 'amid':'mid';
			//config links
			if ($modInfo->hasConfig()) {
				$configLink = cgn_adminlink(
					"Change Settings",
					'mods', 'config', '', array($midamid=>$modInfo->codeName)
				);
			} else {
				$configLink = '<span style="font-weight:bold;color:#eee">Change Settings</span>';
			}

			//access links
			$accessLink = cgn_adminlink(
				"Go To Module",
				$modInfo->codeName
			);


			$adminTable->data[]  = array(
				cgn_adminlink($modInfo->getDisplayName(), 'mods', 'main', 'view', array('amid'=>$modInfo->codeName)),
				$modInfo->getVersionString(),
				$isInstalled,
				$configLink . ' | '. $accessLink
				);
		}
		$adminTable->headers = array('Module', 'Version', 'Installed', 'Actions');
		$t['adminTable'] = new Cgn_Mvc_AdminTableView($adminTable);
		//$t['adminTable']->setColRenderer( 2, new Cgn_Mvc_Table_YesNoRenderer() );
		$t['adminTable']->setColWidth( 0, '50%' );
		$t['adminTable']->setColWidth( 1, '10%' );
	}



	/**
	 * show details about mid module
	 */
	function viewEvent($req, &$t) {
		$isAdmin = FALSE;
		$mid = $req->cleanString('mid');
		if (!$mid) {
			$isAdmin = TRUE;
			$mid = $req->cleanString('amid');
		}

//		$t['header'] = '<h3>'.ucfirst($mid).' Module Details</h3>';
		$this->displayName = ucfirst($mid).' Module Details';

		//load module info object
		$modInfo = new Cgn_Module_Info($mid, $isAdmin);


		$midamid = ($modInfo->isAdmin)? 'amid':'mid';

		//create toolbar action buttons
		$t['mytoolbar'] = new Cgn_HtmlWidget_Toolbar();
		if (!$modInfo->isInstalled) {
			$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods','install','', array($midamid=>$mid)), "Install Module");
			$t['mytoolbar']->addButton($btn1);
		}
		if ($modInfo->hasUpgrade()) {
			$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods','install','', array($midamid=>$mid)), "Upgrade Module");
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
			$modInfo->getDisplayName(), 
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
		if ($modInfo->hasReadme()) {
			$t['readmeLabel'] = '<h3>Readme File</h3>';
			$t['readmeContents'] = file_get_contents($modInfo->readmeFile);
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
