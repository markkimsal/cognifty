<?php
require_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
require_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
require_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

require_once(CGN_LIB_PATH.'/mod/lib_cgn_mod_mgr.php');

class Cgn_Service_Mods_Main extends Cgn_Service {
	 
	/**
	 * Create a table to display the modules in
	 */
	function mainEvent(&$cc, &$t) {
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
			$table->data[]  = array(
				cgn_adminlink($modInfo->codeName, 'mods', 'main', 'view', array('mid'=>$modInfo->codeName)),
				$modInfo->installedVersion,
				$isInstalled
				);
		}
		$table->headers = array('Module', 'Version', 'Installed');

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

			$adminTable->data[]  = array(
				cgn_adminlink($modInfo->codeName, 'mods', 'main', 'view', array('mid'=>$modInfo->codeName)),
				$modInfo->installedVersion,
				$isInstalled
				);
		}
		$adminTable->headers = array('Module', 'Version', 'Installed');
		$t['adminTable'] = new Cgn_Mvc_AdminTableView($adminTable);
		//$t['adminTable']->setColRenderer( 2, new Cgn_Mvc_Table_YesNoRenderer() );
		$t['adminTable']->setColWidth( 0, '50%' );
		$t['adminTable']->setColWidth( 1, '10%' );
	}



	/**
	 * show details about mid module
	 */
	function viewEvent(&$req, &$t) {
		$mid = $req->cleanString('mid');

		$t['header'] = '<h3>'.ucfirst($mid).' Module Details</h3>';

		//load module info object
		$modInfo = new Cgn_Module_Info($mid);

		//create toolbar action buttons
		$t['mytoolbar'] = new Cgn_HtmlWidget_Toolbar();
		if (!$modInfo->isInstalled) {
			$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods','install','', array('mid'=>$mid)), "Install Module");
			$t['mytoolbar']->addButton($btn1);
		}
		if ($modInfo->hasUpgrade()) {
			$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods','install','doUpgrade', array('mid'=>$mid)), "Upgrade Module");
			$t['mytoolbar']->addButton($btn2);
		}

		if (!$modInfo->isAdmin) {
			$btn3 = new Cgn_HtmlWidget_Button(cgn_adminurl($mid), "Access Module");
			$t['mytoolbar']->addButton($btn3);
		}


		//make data table
		$table = new Cgn_Mvc_TableModel();
		$table->data[] = array(
			'Module Name',
			$modInfo->codeName, 
		);
		$table->data[] = array(
			'Installed Version',
			$modInfo->installedVersion, 
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
}
?>
