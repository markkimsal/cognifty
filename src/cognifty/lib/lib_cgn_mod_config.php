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
		if (@file_exists($modulePath.'/'.$moduleName.'/config.ini') ) { 
			$configs = parse_ini_file($modulePath.'/'.$moduleName.'/config.ini',true);
			if (@file_exists($modulePath.'/'.$moduleName.'/local.ini') ) { 
				$localConfigs = parse_ini_file($modulePath.'/'.$moduleName.'/local.ini',true);
				$configs = array_merge($configs, $localConfigs);
			}
			//only save the values that start with "config."
			foreach($configs as $k=>$v) {
				if (strstr($k,'config.') ) {
					$this->moduleConfigs[$moduleName][ substr($k,7) ] = $v;
				}
			}
		} else {
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
