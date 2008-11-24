<?php

$start = microtime();
define('BASE_DIR', dirname(__FILE__).'/');
//chdir(BASE_DIR);
ob_start('ob_gzhandler');

/**
 * load a simple bootstrap file to get some basic 
 * funtionality and configs loaded
 *
 */

if (! include_once(BASE_DIR.'./boot/bootstrap.php') ) {
	if (! include_once(BASE_DIR.'../boot/bootstrap.php') ) {
		include_once('bootstrap.php');
	}
}

//EXTRA FOR ADMIN
Cgn_ObjectStore::parseConfig('admin-boot/admin.ini');


include(CGN_LIB_PATH.'/lib_cgn_user.php');
include(CGN_LIB_PATH.'/lib_cgn_data_item.php');
include(CGN_LIB_PATH.'/lib_cgn_data_model.php');
include(CGN_LIB_PATH.'/lib_cgn_cleaner.php');
include(CGN_LIB_PATH.'/lib_cgn_util.php');
include(CGN_LIB_PATH.'/lib_cgn_error.php');


//set configurable parameter from a constant.  This is different for the frontend section
$adminModules = CGN_ADMIN_PATH;
Cgn_ObjectStore::storeConfig("path://default/cgn/module", $adminModules);
Cgn_ObjectStore::storeConfig("path://admin/cgn/module", $adminModules);


//Swap admin template name with default template name
// UPDATE: this done in the admin ticket runner because
//  the admin needs to see front-end and back-end templates
//  depending on the circumstances
//$adminTemplate = Cgn_ObjectStore::getConfig("config://admin/template/name");
//Cgn_ObjectStore::storeConfig("config://template/default/name", $adminTemplate);


//set the default MSE for the admin
$main = 'main';
Cgn_ObjectStore::storeConfig("config://default/module", $main);
Cgn_ObjectStore::storeConfig("config://default/service", $main);
Cgn_ObjectStore::storeConfig("config://default/event", $main);


//$myTemplate->parseTemplate();



//Cgn_ObjectStore::debug('boot/admin.ini');
//
/**
 * load the default request handler
 */
$myHandler =& Cgn_ObjectStore::getObject("object://adminSystemHandler");

#$myDsn =& Cgn_ObjectStore::getObject("dsn://default");

$myHandler->initRequestTickets($_SERVER['PHP_SELF']);

$myHandler->runTickets();
#echo microtime()."<BR>".$start;
	
?>
