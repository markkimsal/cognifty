<?php

/**
 * Handle key-value pairs for modules
 */
class Cgn_ModuleConfig {
	var $moduleConfigs = array();


	/**
	 * Read config.ini from module directory
	 */
	function initModule($moduleName) {
		$modulePath = Cgn_ObjectStore::getConfig('path://default/cgn/module');
		//parse ini can't deal with string contents
//		ob_start();
		if (@file_exists($modulePath.'/'.$moduleName.'/config.ini') ) { 
			$inistuff = ob_get_contents();
//			ob_end_clean();
			$throwAway = Cgn_ErrorStack::pullError();
//			$majorSection = basename($inifile,".ini");
			$configs = parse_ini_file($modulePath.'/'.$moduleName.'/config.ini',true);
			$this->moduleConfigs[$moduleName] = $configs;
		} else {
//			ob_end_clean();
			//not having an include file registers 2 php errors/warnings/notices
//			$throwAway = Cgn_ErrorStack::pullError('php');
//			$throwAway = Cgn_ErrorStack::pullError('php');
		}
	}

	function getModuleKeys($moduleName) {
		if (isset($this->moduleConfigs[$moduleName])) {
			return array_keys( $this->moduleConfigs[$moduleName] );
		} else {
			return array();
		}
	}

	function getModuleVal($moduleName,$key) {
		if (isset($this->moduleConfigs[$moduleName][$key])) {
			return  $this->moduleConfigs[$moduleName][$key];
		} else {
			return null;
		}
	}
}
