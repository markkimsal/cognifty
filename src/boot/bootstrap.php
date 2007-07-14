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

$bootstrapConfigs = @parse_ini_file('bootstrap.ini',true);
if (!$bootstrapConfigs) {
       $bootstrapConfigs= parse_ini_file(BASE_DIR.'../boot/core.ini',true);
}




$prefix = $bootstrapConfigs['core']['config.prefix'];
$sysPath = '';
$libPath = '';
$pluginPath = '';
$filterPath = '';

//convert .ini file settings into defined constants
foreach ($bootstrapConfigs['path'] as $key => $val) {
	$keyname = strtoupper($key);
	$keyname = str_replace('.','_',$keyname);
	//attempt recursive variable replacement in ini values
	$val = str_replace('@sys.path@', $sysPath, $val);

	define($prefix.$keyname,$val);

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


Cgn_ObjectStore::parseConfig('boot/template.ini');
Cgn_ObjectStore::parseConfig('boot/default.ini');
Cgn_ObjectStore::parseConfig('boot/layout.ini');


$base = @$_SERVER['HTTP_HOST'];
$script = substr(@$_SERVER['SCRIPT_FILENAME'],strrpos(@$_SERVER['SCRIPT_FILENAME'],'/')+1);
$tail = str_replace($script,'',@$_SERVER['SCRIPT_NAME']);
$base = $base.$tail;
Cgn_ObjectStore::storeConfig('config://template/base/uri',$base);
 
includeObject($bootstrapConfigs['object']['sys.handler']);// Cgn_SystemRunner


/** 
 * make all sections of bootstrap.ini file available in standard format
 * Example
 * [section1]
 * key=val
 *
 * is accessible via getObject("section1://key);
 *
 */

foreach($bootstrapConfigs as $scheme=>$configs) { 
	if ($scheme=='dsn') { 
		continue;
	}
	foreach($configs as $key=>$val) { 
		if ($scheme != 'object') {
//		if (strpos($val,":")==0) {
			Cgn_ObjectStore::storeValue("$scheme://$key/",$val);
		} else { 
			includeObject($val);
		}
	}
}


/**
 * parse dsn in a specific manner
 *
 */
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
			if ($scheme == 'object') { die('lskdjf');}
			Cgn_ObjectStore::storeValue("$scheme://$key/",$val);
		} else { 
			includeObject($val);
		}
	}
}



/**
 * define bootstrap function to assist in loading files.
 * format is filename
 */
function includeFile($fileName) {
	global $libPath;
	$fileName = str_replace('@lib.path@',$libPath,$fileName);
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

	$filePackage = explode(':',$objectToken);

	$fileName = str_replace('@lib.path@',Cgn_ObjectStore::getConfig('config://cgn/path/lib'),$filePackage[0]);
	$fileName = str_replace('@plugin.path@',Cgn_ObjectStore::getConfig('config://cgn/path/plugin'),$fileName);
	$fileName = str_replace('@filter.path@',Cgn_ObjectStore::getConfig('config://cgn/path/filter'),$fileName);

	if ($fileName == '') { print_R(debug_backtrace());}
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
