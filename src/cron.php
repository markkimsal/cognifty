<?php

//var_dump($_ENV);
$sapi = php_sapi_name();
//if sapi is CLI, then we're all good
if ($sapi !== 'cli') {
	if ($sapi === 'apache2handler' ||  $sapi === 'apache' || $sapi === 'fcgi') {
		header ('HTTP/1.X 501 Not Implemented');
		echo "Not Implemented.";
		die(-1);
	}
	//some distros mixup CGI and CLI
	if ($sapi === 'cgi') {

		//disallowed users are common apache names
		$disallowedUsers = array('apache', 'www-data', 'nobody', 'httpd');
		//make sure we're not being called from HTTP
		if (isset($_ENV['USER']) &&
			 in_array($_ENV['USER'], $disallowedUsers) ) {
				header ('HTTP/1.X 501 Not Implemented');
				echo "Not Implemented.";
				die(-1);
		} else {
			//no ENV['USER'] set, we'd better die to be safe
			header ('HTTP/1.X 501 Not Implemented');
			echo "Not Implemented.";
			die(-1);
		}
	}
}

//calling from cron changes the working dir.
chdir(dirname(__FILE__));

$start = microtime(1);
define('BASE_DIR',dirname(__FILE__).'/');
//chdir(BASE_DIR);
//ob_start('ob_gzhandler');

/**
 * load a simple bootstrap file to get some basic 
 * funtionality and configs loaded
 *
 */

if (! @include_once(BASE_DIR.'../boot/bootstrap.php') ) {
	if (! @include_once(BASE_DIR.'./boot/bootstrap.php') ) {
		 @include_once('bootstrap.php');
	}
}

/**
 *  __ FIXME __ move this somewhere else, use ini system
 */
include_once(CGN_LIB_PATH.'/lib_cgn_user.php');
include_once(CGN_LIB_PATH.'/lib_cgn_data_item.php');
include_once(CGN_LIB_PATH.'/lib_cgn_cleaner.php');
include_once(CGN_LIB_PATH.'/lib_cgn_util.php');
include_once(CGN_LIB_PATH.'/lib_cgn_error.php');


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
?>
