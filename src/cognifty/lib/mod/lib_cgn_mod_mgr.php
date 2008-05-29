<?php

class Cgn_Module_Manager {

	var $_modList     = array();
	var $_modHandler  = NULL;

	public function __construct() {
		$this->modHandler = new Cgn_Module_Manager_File();
	}

	public static function getInstalledModules() {
		return Cgn_Module_Manager::getAllModules();
	}


	public static function getAllModules() {
		return Cgn_Module_Manager_File::getListStatic();
	}
}

/**
 * Directory based module inspector.
 */
class Cgn_Module_Manager_File {

	/**
	 * Return an array of all modules by analyzing the 
	 * modules/ and admin/ folder.
	 */
	public static function getListStatic() {

		$list = array();
		$d = dir(CGN_MODULE_PATH);
		while ($entry = $d->read()){
			if ($d == '.' || $d == '..' || substr($entry,0,1) == '.') {
				continue;
			}
			$mod = new Cgn_Module_Info($entry);
			$list[] = $mod;
		}
		$d = dir(CGN_ADMIN_PATH);
		while ($entry = $d->read()){
			if ($d == '.' || $d == '..' || substr($entry,0,1) == '.') {
				continue;
			}
			$mod = new Cgn_Module_Info($entry, TRUE);
			$list[] = $mod;
		}
		return $list;
	}

	/**
	 * Return an array of all modules by analyzing the 
	 * modules/ and admin/ folder.
	 */
	public function getList() {
		return Cgn_Module_Manager_File::getListStatic();
	}
}

/**
 * Information about a module.
 *
 * $isInstallable  some modules do not need installing.
 * $isInstalled    having an "install.ini" means it's installed
 */
class Cgn_Module_Info {

	var $codeName;
	var $displayName;
	var $isFrontend    = TRUE;
	var $isAdmin       = FALSE;
	var $isInstalled   = TRUE;
	var $isInstallable = FALSE;
	var $installedVersion = 'core';
	var $availableVersion = 0;
	var $installedOn   = NULL;
	var $upgradedOn    = NULL;
	var $readmeFile    = NULL;

	public function __construct($codeName, $isAdmin=FALSE) {
		$this->codeName = $codeName;
		$this->isAdmin = $isAdmin;
		$this->isFrontend ^=  $isAdmin;
		$this->inspectModule();
	}

	/**
	 * Collect information about this module
	 */
	public function inspectModule() {
		$pathToConfig = CGN_MODULE_PATH.'/'.$this->codeName.'/meta.ini';
		$pathToInstall = CGN_MODULE_PATH.'/'.$this->codeName.'/install.ini';
		if ($this->isAdmin) {
			$pathToConfig = CGN_ADMIN_PATH.'/'.$this->codeName.'/meta.ini';
			$pathToInstall = CGN_ADMIN_PATH.'/'.$this->codeName.'/install.ini';
		}
		if (@file_exists($pathToConfig)) {
			$inistuff = ob_get_contents();
//			ob_end_clean();
			$throwAway = Cgn_ErrorStack::pullError();
//			$majorSection = basename($inifile,".ini");
			$configs = parse_ini_file($pathToConfig, TRUE);
			//only save the values that start with "config."
			$this->availableVersion = 0;
			$this->installedVersion = 0;
			$this->isInstalled = FALSE;
			foreach($configs as $k=>$v) {
				if (strstr($k,'version.') ) {
					$this->availableVersion = $v;
				}
			}
		}

		if (@file_exists($pathToInstall)) {
			$inistuff = ob_get_contents();
//			ob_end_clean();
			$throwAway = Cgn_ErrorStack::pullError();
//			$majorSection = basename($inifile,".ini");
			$configs = parse_ini_file($pathToInstall, TRUE);
			//only save the values that start with "config."
			$this->isInstalled = TRUE;
			foreach($configs as $k=>$v) {
				if (strstr($k,'version.') ) {
					$this->installedVersion = $v;
				}
				if ($k == 'installed_on') {
					$this->installedOn = $v;
				}
				if ($k == 'upgraded_on') {
					$this->upgradedOn = $v;
				}
			}
		}
		$pathToReadme = CGN_MODULE_PATH.'/'.$this->codeName.'/README.txt';
		if (file_exists($pathToReadme)) {
			$this->readmeFile = $pathToReadme;
		}
		$pathToReadme = CGN_MODULE_PATH.'/'.$this->codeName.'/README';
		if (file_exists($pathToReadme)) {
			$this->readmeFile = $pathToReadme;
		}
	}

	public function hasUpgrade() {
		if (!$this->isInstalled) {
			return FALSE;
		}
		if ($this->availableVersion > $thiis->installedVersion) {
			return TRUE;
		}
		return FALSE;
	}

	public function hasReadme() {
		if ($this->readmeFile !== NULL) {
			return TRUE;
		}
		return FALSE;
	}
}
