<?php

$start = microtime();
define('BASE_DIR',dirname(__FILE__).'/');
//chdir(BASE_DIR);
ob_start('ob_gzhandler');

/**
 * load a simple bootstrap file to get some basic 
 * funtionality and configs loaded
 *
 */

if (! include_once(BASE_DIR.'../boot/bootstrap.php') ) {
	if (! @include_once(BASE_DIR.'./boot/bootstrap.php') ) {
		 @include_once('bootstrap.php');
	}
}

/**
 *  __ FIXME __ move this somewhere else, use ini system
 */
include_once(BASE_DIR.'../cognifty/lib/lib_cgn_user.php');
include_once(BASE_DIR.'../cognifty/lib/lib_cgn_data_item.php');
include_once(BASE_DIR.'../cognifty/lib/lib_cgn_cleaner.php');
include_once(BASE_DIR.'../cognifty/lib/lib_cgn_util.php');


/**
 * load the default request handler
 */
$myHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");

$mySession =& Cgn_ObjectStore::getObject("object://defaultSessionLayer");
$mySession->set('last_access_time',time());

#$myDsn =& Cgn_ObjectStore::getObject("dsn://default");

$myHandler->initRequestTickets($_SERVER['PHP_SELF']);

$myHandler->runTickets();

$mySession->close();

#echo microtime()."<BR>".$start;
#echo "<hr><pre>"; print_r(get_included_files());
?>
