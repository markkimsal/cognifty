<?php

$start = microtime(1);
define('BASE_DIR',dirname(__FILE__).'/');
//chdir(BASE_DIR);
//ob_start('ob_gzhandler');

/**
 * load a simple bootstrap file to get some basic 
 * funtionality and configs loaded
 *
 */

if (! include(BASE_DIR.'./boot/bootstrap.php') ) {
	if (! include(BASE_DIR.'../boot/bootstrap.php') ) {
		include('bootstrap.php');
	}
}

/**
 *  __ FIXME __ move this somewhere else, use ini system
 */
include(CGN_LIB_PATH.'/lib_cgn_user.php');
include(CGN_LIB_PATH.'/lib_cgn_data_item.php');
include(CGN_LIB_PATH.'/lib_cgn_data_model.php');
include(CGN_LIB_PATH.'/lib_cgn_cleaner.php');
include(CGN_LIB_PATH.'/lib_cgn_util.php');
include(CGN_LIB_PATH.'/lib_cgn_error.php');


/**
 * load the default request handler
 */
$myHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");

//set configurable parameter from a constant.  This is different for the admin section
$frontModules = CGN_MODULE_PATH;
Cgn_ObjectStore::storeConfig("path://default/cgn/module", $frontModules);

#$myDsn =& Cgn_ObjectStore::getObject("dsn://default");

$myHandler->initRequestTickets($_SERVER['PHP_SELF']);

$myHandler->runTickets();

#echo sprintf('%.2f',(microtime(1) - $start)*1000);
#echo "<hr><pre>"; print_r(get_included_files());
#

if( Cgn_ObjectStore::hasConfig("object://default/handler/log") ) {
	$logHandler =& Cgn_ObjectStore::getObject("object://defaultLogHandler");
	$request = $myHandler->currentRequest;
	$logHandler->record($request, $myHandler->ticketList[0], $request->getUser());
}
?>
