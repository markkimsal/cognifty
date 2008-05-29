<?php
require_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
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
			$table->data[]  = array(
				$modInfo->codeName,
				$modInfo->installedVersion,
				$modInfo->isInstalled
				);
		}
		$table->headers = array('Module', 'Version', 'Installed');

		$t['renderer'] = new Cgn_Mvc_AdminTableView($table);

		$t['renderer']->setColRenderer( 2, new Cgn_Mvc_Table_YesNoRenderer() );

		//admin modules
		$adminTable = new Cgn_Mvc_TableModel();
		foreach ($modList as $modInfo) {
			if ($modInfo->isFrontend) { continue; }
			$adminTable->data[]  = array(
				$modInfo->codeName,
				$modInfo->installedVersion,
				$modInfo->isInstalled
				);
		}
		$adminTable->headers = array('Module', 'Version', 'Installed');
		$t['adminTable'] = new Cgn_Mvc_AdminTableView($adminTable);
		$t['adminTable']->setColRenderer( 2, new Cgn_Mvc_Table_YesNoRenderer() );
	}
}
?>
