<?php
Cgn::loadLibrary('Html_widgets::Lib_Cgn_Widget');
Cgn::loadLibrary('Html_widgets::Lib_Cgn_Toolbar');
//MVC
Cgn::loadLibrary('Lib_Cgn_Mvc');
Cgn::loadLibrary('Lib_Cgn_Mvc_Table');
//module manager and utilities
Cgn::loadLibrary('Mod::Lib_Cgn_Mod_Mgr');

Cgn::loadModLibrary('Mods::Cgn_Install_Mgr', 'admin');

class Cgn_Service_Mods_Install extends Cgn_Service_Admin {
	 
	/**
	 * Create a table to display the modules in
	 */
	public function mainEvent($req, &$t) {

		$modInfo = $this->_getModInfo($req);
		$zipPath = $this->_getSessionInstall($req);
		if ($modInfo) {
			if ($zipPath) {
				$zipPath .= '/'. $modInfo->codeName.'/';
			} else {
				$zipPath = $modInfo->fullModulePath;
			}
		}

		$t['mid'] = $modInfo->codeName;
		$mid = $modInfo->codeName;
		$t['step'] = $req->cleanInt('step');

		$t['modInfo'] = $modInfo;
		$t['header'] = '<h3>Module Install: '.ucfirst($mid).'</h3>';
		$installer = new Cgn_Install_Mgr($zipPath, $modInfo);

		if (!$installer->canInstall()) {
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('mods', 'main');
			$u = $req->getUser();
			$u->addSessionMessage('Cannot install this module. Check that all directories are writable.', 'msg_warn');
			return TRUE;
		}

		$t['oldversion'] = $installer->existingModInfo->installedVersion;
		$t['newversion'] = $installer->newModInfo->availableVersion;

		$installer->initInstall();
		$doUpgrade = ! $installer->isInstallation();

		if ($doUpgrade) {
			$t['installInfo'] = 'Going to perform an upgrade.';
		} else {
			$t['installInfo'] = 'Going to perform an installation.';
		}


		$t['midamid'] = ($installer->existingModInfo->isAdmin)? 'amid':'mid';

		$taskList = $installer->getTaskList();
		if ($taskList === FALSE) {
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('mods', 'main');
			$u = $req->getUser();
			$u->addSessionMessage('Cannot find installation or upgrade task matching your verison.', 'msg_warn');
			return TRUE;
		}

		$phProject = $installer->phingCommand->project;
		$t['tasks'] = array();
		foreach ($taskList as $_tidx => $_t) {
			$status = ($_tidx < $t['step'])? 'done':'notdone';
			$t['tasks'][] = array('name'=>$_t->subTarget,
			'description'=>$phProject->replaceProperties($installer->getTargetDescription($_t->getTarget())),
			'status'=>$status); 
		}

		$installer->setCurrentStep($t['step']);
	}

	/**
	 * Perform one step of the installation
	 */
	public function stepEvent($req, &$t) {
		$modInfo = $this->_getModInfo($req);
		/*
		if ($modInfo == NULL) {
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('mods', 'main');
			$u = $req->getUser();
			$u->addSessionMessage('Lost module during installation.', 'msg_warn');
			return TRUE;
		}
		 */

		$zipPath = $this->_getSessionInstall($req);
		if ($zipPath) {
			$zipPath .= '/'. $modInfo->codeName.'/';
		} else {
			$zipPath = $modInfo->fullModulePath;
		}

		$t['mid'] = $modInfo->codeName;
		$mid = $modInfo->codeName;
		$t['step'] = $req->cleanInt('step');

		$t['modInfo'] = $modInfo;
		$t['header'] = '<h3>Module Install: '.ucfirst($mid).'</h3>';
		$installer = new Cgn_Install_Mgr($zipPath, $modInfo);

		if (!$installer->canInstall()) {
			$t['error'] = 'Cannot install this module.';
			return FALSE;
		}

		$t['oldversion'] = $installer->existingModInfo->installedVersion;
		$t['newversion'] = $installer->newModInfo->availableVersion;

		$installer->initInstall();
		$doUpgrade = ! $installer->isInstallation();

		if ($doUpgrade) {
			$t['installInfo'] = 'Going to perform an upgrade.';
		} else {
			$t['installInfo'] = 'Going to perform an installation.';
		}

		$t['midamid'] = ($installer->existingModInfo->isAdmin)? 'amid':'mid';

		$taskList = $installer->getTaskList();

		if ($t['step'] > count($taskList)) { 
			$this->presenter = 'redirect';
			$midamid = ($installer->existingModInfo->isAdmin)? 'amid':'mid';
			$t['url'] = cgn_adminurl('mods', 'install', 'finish', array($midamid=>$modInfo->codeName));
			return TRUE;
		}

		$phProject = $installer->phingCommand->project;
		$t['tasks'] = array();
		foreach ($taskList as $_tidx => $_t) {
			$status = ($_tidx < $t['step'])? 'done':'notdone';
			$t['tasks'][] = array('name'=>$_t->subTarget,
			'description'=>$phProject->replaceProperties($installer->getTargetDescription($_t->getTarget())),
			'status'=>$status); 
		}
		$installer->setCurrentStep($t['step']);

		try {
			$out = $installer->phingCommand->capturePhingOutput = true;
			$out = $installer->runCurrentStep();
			if (is_array($out)) {
				echo( implode("\n<br/>", $out));
			}
		} catch (Exception $ex) {
			$t['failure'] = TRUE;
			$t['step']--;
			//status not done
			$u = $req->getUser();
			$u->addMessage('Step failed: '.$ex->getMessage(), 'msg_warn');
		}
		$t['logOut'] = $installer->phingCommand->logOut;
		$t['logErr'] = $installer->phingCommand->logErr;
		$t['proceed'] = cgn_adminlink('Procceed', 'mods', 'install', 'step', array('mid'=>$mid, 'step'=>$t['step']+1));
	}

	/**
	 * Finalize the installation by updating or creating an "install.ini" file.
	 */
	public function finishEvent($req, &$t) {
		$modInfo = $this->_getModInfo($req);
		/*
		if ($modInfo == NULL) {
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('mods', 'main');
			$u = $req->getUser();
			$u->addSessionMessage('Lost module during installation.', 'msg_warn');
			return TRUE;
		}
		 */

		$zipPath = $this->_getSessionInstall($req);
		if ($zipPath) {
			$zipPath .= '/'. $modInfo->codeName.'/';
		} else {
			$zipPath = $modInfo->fullModulePath;
		}

		$t['modInfo'] = $modInfo;
		$t['mid'] = $modInfo->codeName;
		$mid = $modInfo->codeName;
		$t['step'] = $req->cleanInt('step');


		$t['header'] = '<h3>Module Install: '.ucfirst($mid).'</h3>';
		$installer = new Cgn_Install_Mgr($zipPath, $modInfo);

		$t['oldversion'] = $installer->existingModInfo->installedVersion;
		$t['newversion'] = $installer->newModInfo->availableVersion;

		if (!$installer->canInstall()) {
			$t['error'] = 'Cannot install this module.';
			return FALSE;
		}
		
		try {
			$installer->initInstall();
			$doUpgrade = ! $installer->isInstallation();
			$installer->finishInstall();
		} catch (Exception $ex) {
			$this->presenter = 'redirect';
			$midamid = ($installer->newModInfo->isAdmin)? 'amid':'mid';
			$t['url'] = cgn_adminurl('mods', 'main', 'view', array($midamid=>$modInfo->codeName));
			$u = $req->getUser();
			$u->addSessionMessage($ex->getMessage(), 'msg_warn');
			return TRUE;
		}

		//clean-up any uploaded files, directories, and session keys
		$this->_cleanupTemp($req);

		//add session message and return to main module screen
		$u = $req->getUser();
		$u->addSessionMessage('Module Installed: '.ucfirst($t['mid']));
		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl('mods');

	}

	/**
	 * Create modinfo from get string of mid or amid
	 * Also look in session for existing upload
	 */
	protected function _getModInfo($req) {
		if ($mid = $req->cleanString('mid')) {
			$isAdmin = FALSE;
		} else {
			$mid = $req->cleanString('amid');
			$isAdmin = TRUE;
		}
		/*
		$specialPath = '';
		if ($req->getSessionVar('mod_install_current')) {
			$dir = $this->_getLandingFolder().$req->getSessionVar('mod_install_current');
			$dh = @dir($dir);
			if (!$dh) { $this->_cleanupTemp($req); return NULL; }
			while ($entry = $dh->read()) {
				if ( substr($entry, 0, 1) == '.') continue;
				$mid = $entry;
				$isAdmin = NULL;
//				$specialPath = $dir.'/'.$entry;
			}
		}
		 */
		if ($mid == '') {
			return NULL;
		}

		$modInfo = new Cgn_Module_Info($mid, $isAdmin, $specialPath);
		return $modInfo;
	}

	/**
	 * Return the path to an unzippped module if 
	 * the session has a variable set
	 */
	protected function _getSessionInstall($req) {
		$x = $req->getSessionVar('mod_install_current'); 
		if (!$x) {
			return '';
		}
		$dir = $this->_getLandingFolder().$x;
		$dh = @dir($dir);
		if (!$dh) { $this->_cleanupTemp($req); return NULL; }
		while ($entry = $dh->read()) {
			if ( substr($entry,0, 1) == '.') continue;
			$mid = $entry;
			$isAdmin = NULL;
		}
		return $dir.'/'.$mid.'/';
	}

	/**
	 * Try to make CGN_BASE.'var/tmp/' and make sure it's writable
	 */
	protected function _getLandingFolder() {
		$landing = BASE_DIR.'var/tmp/';
		return $landing;
	}

	/**
	 * Remove directories from the landing directory, remove session keys
	 * relating to this installation.
	 */
	protected function _cleanupTemp($req) {
		$landing = $this->_getLandingFolder();
		$x = $req->getSessionVar('mod_install_current'); 
		if ($x) {
			$this->_rmDirTree($landing.$x);
			$req->clearSessionVar('mod_install_current');
		}
		//TODO: clean up any dirs that might have had errors
	}

	/**
	 * Delete a folder and all of its contents
	 */
	protected function _rmDirTree($dir) {
		if (!file_exists($dir)) return true; 
		if (!is_dir($dir) || is_link($dir)) return @unlink($dir); 

		foreach (scandir($dir) as $item) { 
			if ($item == '.' || $item == '..') continue; 

			if (!$this->_rmDirTree($dir . "/" . $item)) { 
				@chmod($dir . "/" . $item, 0777); 
				if (!$this->_rmDirTree($dir . "/" . $item)) return FALSE; 
			}; 
		} 
		return @rmdir($dir); 
	}
}

