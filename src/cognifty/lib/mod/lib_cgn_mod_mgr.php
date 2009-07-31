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
			if (substr($entry,0,1) == '.' || substr($entry, -1) == '~') {
				continue;
			}
			$mod = new Cgn_Module_Info($entry, NULL, CGN_MODULE_PATH.'/'.$entry);
			$list[] = $mod;
		}

		if (defined('CGN_MODULE_LOCAL_PATH') && file_exists(CGN_MODULE_LOCAL_PATH)) {
			$d = dir(CGN_MODULE_LOCAL_PATH);
			while ($entry = $d->read()){
				if (substr($entry,0,1) == '.' || substr($entry, -1) == '~') {
					continue;
				}
				$mod = new Cgn_Module_Info($entry, NULL, CGN_MODULE_LOCAL_PATH.'/'.$entry);
				$list[] = $mod;
			}
		}

		$d = dir(CGN_ADMIN_PATH);
		while ($entry = $d->read()){
			if (substr($entry,0,1) == '.' || substr($entry, -1) == '~') {
				continue;
			}
			$mod = new Cgn_Module_Info($entry, TRUE, CGN_ADMIN_PATH.'/'.$entry);
			$list[] = $mod;
		}

		if (defined('CGN_ADMIN_LOCAL_PATH') && file_exists(CGN_ADMIN_LOCAL_PATH)) {
			$d = dir(CGN_ADMIN_LOCAL_PATH);
			while ($entry = $d->read()){
				if (substr($entry,0,1) == '.' || substr($entry, -1) == '~') {
					continue;
				}
				$mod = new Cgn_Module_Info($entry, TRUE, CGN_ADMIN_LOCAL_PATH.'/'.$entry);
				$list[] = $mod;
			}
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
	var $isFrontend      = TRUE;
	var $isAdmin         = FALSE;
	var $isInstalled     = TRUE; //assume installed because not all modules have install.ini
	var $isInstallable   = FALSE;
	var $installedVersion = 'core';
	var $availableVersion = 0;
	var $installedOn     = NULL;
	var $upgradedOn      = NULL;
	var $readmeFile      = NULL;
	var $fullModulePath  = '';

	/**
	 * Construct a new object which holds information about a module and parses 
	 * it's install.ini, meta.ini and install.xml
	 *
	 * @param String $codeName    name of the module directory. ex: login
	 * @param Bool   $isAdmin	  whether this module lives in cognifty/modules or cognifty/admin
	 * @param String $pathToModule Module will respect deafult.ini settings, but you can override
	 *                             those values with this one. (usefull when installing new mod)
	 */
	public function __construct($codeName, $isAdmin=NULL, $pathToModule='') {
		//if the passed in argument is null, reply on "inspectModule()s" findings.
		//else, use this as an override
		if ($isAdmin !== NULL) {
			$this->isAdmin = $isAdmin;
			$this->isFrontend = !(bool)$isAdmin;
		}

		$this->codeName = $codeName;
		$this->inspectModule($pathToModule);

		//if the passed in argument is null, reply on "inspectModule()s" findings.
		//else, use this as an override
		if ($isAdmin !== NULL) {
			$this->isAdmin = $isAdmin;
			$this->isFrontend =  !(bool)$isAdmin;
		}
	}

	/**
	 * Return a number as a string for this version.
	 * If the version is "core", return the version of
	 * the base installation
	 */
	public function getVersionString() {
		if ($this->installedVersion === 'core') {
			return Cgn_SystemRunner::getReleaseNumber();
		}
		return $this->installedVersion;
	}

	/**
	 * Collect information about this module
	 * If $pathToModule is not passed, or is '', then Cgn::getModulePath will be called
	 *
	 * @return void;
	 */
	public function inspectModule($pathToModule = '') {
		if ($pathToModule == '') {
			$pathToModule = Cgn::getModulePath($this->codeName, $this->isAdmin? 'admin':'modules');
		}

		//ensure trailing slash
		if (substr($pathToModule, -1) !== '/')
			$pathToModule .= '/';

		$this->fullModulePath = $pathToModule;

		//check to see if the module exists
		if(!file_exists($pathToModule)) {
			$this->isInstalled = FALSE;
			$this->installedVersion = 0;
			//reset the directory to "local-modules"
			if (defined('CGN_MODULE_LOCAL_PATH') && !$this->isAdmin) {
				$localMod = CGN_MODULE_LOCAL_PATH;
				$this->fullModulePath = $localMod.'/'.$this->codeName.'/';
			}
			if (defined('CGN_ADMIN_LOCAL_PATH') && $this->isAdmin) {
				$localMod = CGN_ADMIN_LOCAL_PATH;
				$this->fullModulePath = $localMod.'/'.$this->codeName.'/';
			}
		}

		$pathToMeta = $this->fullModulePath.'meta.ini';
		$pathToInstall = $this->fullModulePath.'install.ini';
		$pathToReadme = $this->fullModulePath.'README.txt';

		if (@file_exists($pathToMeta)) {
			$inistuff = ob_get_contents();
//			ob_end_clean();
			$throwAway = Cgn_ErrorStack::pullError();
//			$majorSection = basename($inifile,".ini");
			$configs = parse_ini_file($pathToMeta, TRUE);
			//only save the values that start with "config."
			$this->availableVersion = 0;
			$this->installedVersion = 0;
			$this->isInstalled = FALSE;
			foreach($configs as $k=>$v) {
				if (strstr($k,'version.') ) {
					$this->availableVersion = $v;
				}
				if (strstr($k,'is.admin') ) {
					$this->isAdmin = (bool)$v;
					$this->isFrontend = !(bool)$v;
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

		if (file_exists($pathToReadme)) {
			$this->readmeFile = $pathToReadme;
		} else {
			$pathToReadme = $this->fullModulePath.'README';
			if (file_exists($pathToReadme)) {
				$this->readmeFile = $pathToReadme;
			}
		}
	}

	/**
	 * Returns true if there is a config.ini file present in
	 * the module's dir
	 */
	public function hasConfig() {
		return (bool)@file_exists($this->fullModulePath.'/config.ini');
	}

	public function hasUpgrade() {
		if (!$this->isInstalled) {
			return FALSE;
		}
		if ($this->installedVersion == 'core') {
			return FALSE;
		}
		if (version_compare($this->availableVersion, $this->installedVersion, '>') > 0) {
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

	/**
	 * Retrieve contents of local.ini, or use config.ini if no 
	 * local.ini file exists.
	 */
	public function getLocalIniContents() {
		if (@file_exists($this->fullModulePath.'/local.ini')) {
			return file_get_contents($this->fullModulePath.'/local.ini');
		}
		return file_get_contents($this->fullModulePath.'/config.ini');
	}

	/**
	 * Create a Cgn_Mod_Info object from a given 
	 * directory.  Look for "meta.ini" to see if this is
	 * an admin module or not.
	 *
	 * $modInfo = Cgn_Mod_Info::createFromDir('/tmp/upload/foobar');
	 * $modInfo->codeName == 'foobar';
	 *
	 * @param String $dir  location of module including dir with module name
	 */
	public static function createFromDir($dir) {
		$modName = basename(rtrim($dir, '/'));
		return new Cgn_Module_Info($modName, NULL, $dir);
	}

}
