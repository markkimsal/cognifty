<?php
	//bootstrap.php
	// load basic config values
	// load core library files
	// initialize defaults
	// return control to front controller.

	//depends on:
	// bootstrap.ini in the same directory as this file.

//bootstrap procedure
define('CGN_BOOT_DIR',dirname(__FILE__).'/');

$cached = false;
$included_files = array();

$trytocache = true;

//cache object
if (file_exists(CGN_BOOT_DIR.'bootstrap.cache')) {
	$fo = fopen(CGN_BOOT_DIR.'bootstrap.cache', 'r');

	$const = fgets($fo, 4096*2);
	$constArray = unserialize($const);
	foreach ($constArray as $k =>$v) {
		@define($k,$v);
	}

	$files = fgets($fo, 4096*2);
	$fileArray = unserialize($files);
	foreach ($fileArray as $f) {
		include_once($f);
	}
	$cache = '';
	while (!feof($fo) ) {
		$cache .= fgets($fo, 4096*2);
	}
	fclose($fo);
	$objstore = unserialize($cache);
	//init object store
	Cgn_ObjectStore::getSingleton($objstore);
	unset($cache);
	unset($files);
	unset($const);
	unset($fileArary);
	unset($constArary);
	$cached = true;
}
//done - cache object

//if (!$cached) {
	$bootstrapConfigs = @parse_ini_file('bootstrap.ini',true);
	if (!$bootstrapConfigs) {
		   $bootstrapConfigs= parse_ini_file(CGN_BOOT_DIR.'core.ini',true);
	}
	$prefix = $bootstrapConfigs['core']['config.prefix'];
	$sysPath = '';
	$libPath = '';
	$pluginPath = '';
	$filterPath = '';
//}

//convert .ini file settings into defined constants
if (!$cached) {
	foreach ($bootstrapConfigs['path'] as $key => $val) {
		$keyname = strtoupper($key);
		$keyname = str_replace('.','_',$keyname);
		//attempt recursive variable replacement in ini values
		$val = str_replace('@sys.path@', $sysPath, $val);

		@define($prefix.$keyname,$val);

		if ($key == 'sys.path') { $sysPath = $val; }
		if ($key == 'lib.path') { $libPath = $val; }
		if ($key == 'plugin.path') { $pluginPath = $val; }
		if ($key == 'filter.path') { $filterPath = $val; }
	}



	//load the default classloader
	$classLoaderPackage = explode(':',$bootstrapConfigs['object']['class.loader']);

	$success = includeFile($classLoaderPackage[0]);
	if (!$success) {die("*** required resource unavailable.\n". $classLoaderPackage[0]."\n");}
	$$classLoaderPackage[2] = new $classLoaderPackage[1];

	$success = includeFile(CGN_LIB_PATH.'/lib_cgn_core.php');
	if (!$success) {die("*** required resource unavailable.\n". CGN_LIB_PATH.'/lib_cgn_core.php'."\n");}

	$success = includeFile(CGN_LIB_PATH.'/lib_cgn_obj_store.php');
	if (!$success) {die("*** required resource unavailable.\n". CGN_LIB_PATH.'/lib_cgn_obj_store.php'."\n");}


	Cgn_ObjectStore::init();
	Cgn_ObjectStore::storeConfig('config://cgn/path/lib',$libPath);
	Cgn_ObjectStore::storeConfig('config://cgn/path/sys',$sysPath);
	Cgn_ObjectStore::storeConfig('config://cgn/path/plugin',$pluginPath);
	Cgn_ObjectStore::storeConfig('config://cgn/path/filter',$filterPath);
}

	Cgn_ObjectStore::parseConfig('template.ini');
	Cgn_ObjectStore::parseConfig('default.ini');
	Cgn_ObjectStore::parseConfig('layout.ini');

	$base = @$_SERVER['HTTP_HOST'];
	$script = substr(@$_SERVER['SCRIPT_FILENAME'],strrpos(@$_SERVER['SCRIPT_FILENAME'],'/')+1);
	$tail = str_replace($script,'',@$_SERVER['SCRIPT_NAME']);
	$base = $base.$tail;
	Cgn_ObjectStore::storeConfig('config://template/base/uri',$base);
	 
if (!$cached) {
	includeObject($bootstrapConfigs['object']['sys.handler']);// Cgn_SystemRunner
}

/** 
 * make all sections of bootstrap.ini file available in standard format
 * Example
 * [section1]
 * key=val
 *
 * is accessible via getObject("section1://key);
 *
 */
//if (!$cached) {
	foreach($bootstrapConfigs as $scheme=>$configs) { 
		if ($scheme=='dsn') { 
	//		continue;
		}
		foreach($configs as $key=>$val) { 
			if ($scheme != 'object') {
	//		if (strpos($val,":")==0) {
				Cgn_ObjectStore::storeConfig("$scheme://$key/",$val);
			} else { 
				includeObject($val);
			}
		}
	}
//}


/**
 * parse dsn in a specific manner
 *
 */
if (!$cached) {
	foreach ($bootstrapConfigs['dsn'] as $key => $val) {
		// values with :// are assumed to be user/pass/host schemes
		// values without are assumed to be .uri configs
		if (strpos($val,"://")==0) {
			includeObject($val,'db');
		} else {
			Cgn_ObjectStore::storeConfig("dsn://$key",$val);
		}
	}

	$configConfigs = parse_ini_file(CGN_BOOT_DIR.'default.ini', TRUE);
	foreach($configConfigs as $scheme=>$configs) { 

		if ($scheme=='dsn') { 
			continue;
		}
		foreach($configs as $key=>$val) { 
			if (strpos($val,":")==0) {
				if ($scheme == 'object') { die('incorrect scheme for objects');}
				Cgn_ObjectStore::storeValue("$scheme://$key/",$val);
			} else { 
				includeObject($val);
			}
		}
	}
}



//cache object
if (is_writable(CGN_BOOT_DIR) && !$cached  && $trytocache) {
	$x =& Cgn_ObjectStore::getSingleton();
	$stuff = serialize($x);
	$files = serialize($included_files);
	$const = get_defined_constants();
	$newConst = array();
	foreach ($const as $k => $v) {
		if (substr($k,0,3) == 'CGN') {
			$newConst[$k]= $v;
		}
	}
	$const = serialize($newConst);
	$fo = @fopen(CGN_BOOT_DIR.'bootstrap.cache','w');
	if ($fo) {
		fputs($fo, $const."\n");
		fputs($fo, $files."\n");
		fputs($fo, $stuff);
		fclose($fo);
	}
	unset($k);
	unset($v);
	unset($const);
	unset($files);
	unset($stuff);
}
//done - cache object




/**
 * define bootstrap function to assist in loading files.
 * format is filename
 */
function includeFile($fileName) {
	global $libPath, $included_files;
	$fileName = str_replace('@lib.path@',$libPath,$fileName);
	$included_files[] = $fileName;
	$s = include_once($fileName);
	if (! $s ) {
		echo "Failed to include $fileName \n";
	}
	return $s;
}



/**
 * define bootstrap function to assist in loading files.
 * format is filename:classname:objectname
 */
function includeObject($objectToken,$scheme='object') {
	global $included_files;
	static $libPath, $pluginPath, $filterPath = '';
	if ($libPath == '') { $libPath = Cgn_ObjectStore::getConfig('config://cgn/path/lib'); }
	if ($pluginPath == '') { $pluginPath = Cgn_ObjectStore::getConfig('config://cgn/path/plugin'); }
	if ($filterPath == '') { $filterPath = Cgn_ObjectStore::getConfig('config://cgn/path/filter'); }

	$filePackage = explode(':',$objectToken);

	$fileName = str_replace('@lib.path@', $libPath, $filePackage[0]);
	$fileName = str_replace('@plugin.path@', $pluginPath, $fileName);
	$fileName = str_replace('@filter.path@', $filterPath, $fileName);

	if ($fileName == '') { print_r(debug_backtrace());}
	$included_files[] = $fileName;
	$s = include_once($fileName);
	if (! $s ) {
		echo "Failed to include $fileName \n";
	}
	$className = $filePackage[1];
	$tempObj = new $className();
	Cgn_ObjectStore::storeObject($scheme.'://'.$filePackage[2],$tempObj);
	return $s;
}



?>
