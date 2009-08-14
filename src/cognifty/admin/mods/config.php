<?php
Cgn::loadLibrary('Html_widgets::Lib_Cgn_Widget');
Cgn::loadLibrary('Html_widgets::Lib_Cgn_Toolbar');
//MVC
Cgn::loadLibrary('Lib_Cgn_Mvc');
Cgn::loadLibrary('Lib_Cgn_Mvc_Table');
//module manager and utilities
Cgn::loadLibrary('Mod::Lib_Cgn_Mod_Mgr');

class Cgn_Service_Mods_Config extends Cgn_Service_Admin {

	public $displayName = 'Configure Modules';

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

		$this->displayName = 'Configure '.ucfirst($mid).' Module';

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
			$t['configForm'] = $this->_makeConfigForm($modInfo);
		}
	}

	public function saveEvent($req, &$t) {
		$isAdmin = FALSE;
		$mid = $req->cleanString('mid');
		if (!$mid) {
			$isAdmin = TRUE;
			$mid = $req->cleanString('amid');
		}

		$t['header'] = '<h3>'.ucfirst($mid).' Module Details</h3>';

		//load module info object
		$modInfo = new Cgn_Module_Info($mid, $isAdmin);

		$f = fopen($modInfo->fullModulePath.'/local.ini', 'w');
		fwrite($f, $req->cleanMultiline('config'));
		fclose($f);

		$this->presenter = 'redirect';
			$midamid = ($modInfo->isAdmin)? 'amid':'mid';
		$t['url'] = cgn_adminurl(
			'mods', 'main', 'view', array($midamid=>$mid));
	}

	protected function _makeToolbar(&$t) {
		//create toolbar action buttons
		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();

		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods','upload'), "Upload Module");
		$t['toolbar']->addButton($btn1);
	}

	protected function _makeConfigButton($modInfo, &$t) {

		$midamid = ($modInfo->isAdmin)? 'amid':'mid';
		$mid = $modInfo->codeName;
		$params = array($midamid=>$mid);

		if (!$modInfo->isAdmin) {
			$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods', 'main'), "List All Modules");
			$t['mytoolbar']->addButton($btn1);
			$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods', 'main', 'view', $params), "Back to Module");
			$t['mytoolbar']->addButton($btn2);
			$btn3 = new Cgn_HtmlWidget_Button(cgn_appurl($mid), "Access Module");
			$t['mytoolbar']->addButton($btn3);
		}
		if ($modInfo->isAdmin) {
			$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods', 'main'), "List All Modules");
			$t['mytoolbar']->addButton($btn1);
			$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('mods', 'main', 'view', $params), "Back to Module");
			$t['mytoolbar']->addButton($btn2);
			$btn3 = new Cgn_HtmlWidget_Button(cgn_adminurl($mid), "Access Module");
			$t['mytoolbar']->addButton($btn3);
		}


		if ($modInfo->hasConfig()) {
			$btn = new Cgn_HtmlWidget_Button(
				cgn_adminurl('mods', 'config', '', array($midamid=>$mid)),
				"Change Settings");
			$t['mytoolbar']->addButton($btn);
		}
	}

	protected function _makeConfigForm($modInfo) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('up01','','POST');
		$f->width="600px";
		$f->action = cgn_adminurl('mods','config','save');
		$f->label = 'Change default settings';

		$contents = $modInfo->getLocalIniContents();
		$titleInput = new Cgn_Form_ElementText('config', '', 30, 50);
		$titleInput->setValue($contents);


		$f->appendElement($titleInput);

		$midamid = ($modInfo->isAdmin)? 'amid':'mid';
		$f->appendElement(new Cgn_Form_ElementHidden($midamid), $modInfo->codeName);
//		$f->appendElement(new Cgn_Form_ElementText('notes','notes',10,50));
		return $f;
	}
}
?>
