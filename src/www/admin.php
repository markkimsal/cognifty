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

//EXTRA FOR ADMIN
Cgn_ObjectStore::parseConfig('boot/admin.ini');


//run tickets now calls the templating...
//Swap module dir for admin dir for module parsing.
$adminModules = Cgn_ObjectStore::getConfig("path://cgn/admin/module");
Cgn_ObjectStore::storeConfig("path://cgn/module", $adminModules);

//Swap admin template name with default template name
$adminTemplate = Cgn_ObjectStore::getConfig("config://admin/template/name");
Cgn_ObjectStore::storeConfig("config://template/default/name", $adminTemplate);

$myTemplate =& Cgn_ObjectStore::getObject("object://defaultOutputHandler");
$myTemplate->templateName = $adminTemplate;

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
