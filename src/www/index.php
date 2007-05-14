<?php

$start = microtime();
define('BASE_DIR',dirname(__FILE__).'/');
chdir(BASE_DIR);
$res = mysql_connect("localhost","root","");

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
