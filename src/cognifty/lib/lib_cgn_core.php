<?php

includeFile(CGN_LIB_PATH.'/lib_cgn_service.php');

/**
 * Wrap an outside request into a set of
 * variables.  Defaults to HTTP environment
 */
class Cgn_SystemRequest {

	var $vars = array();
	var $getvars = array();
	var $postvars = array();
	var $cookies = array();
	var $isAdmin = false;

/**
 *
 * parse the URL into system request information
 *
 * if there's no '=' in a var, then add the vars positionally
 * 
 * foo.bar.baz/myvarX/key=5
 * vars[1] would be myvarX
 * vars['key'] would be 5
 *
 */
	function Cgn_SystemRequest() {
		$this->vars = Cgn_ObjectStore::getObject('request://request');
		$this->getvars = Cgn_ObjectStore::getObject('request://get');
		$this->postvars = Cgn_ObjectStore::getObject('request://post');
		$this->cookies = Cgn_ObjectStore::getObject('request://cookie');
//		$this->get =& $this->getvars;
//		$this->post =& $this->postvars;
	}


	/**
	 * XXX _TODO_ list all types as defines
	 */
	function getRequestType() {
		if(php_sapi_name()=='cli') { 
			return 'cli';
		} else { 
			return 'http';
		}
	}


	/**
	 * return a copy of the cookies for this request.
	 */
	function getCookies() {
		return $this->cookies;
	}


	/**
	 * Return a reference to the current, global user
	 */
	function &getUser() {
		global $cgnUser;
		return $cgnUser;
	}

	/**
	 * removes effects of Magic Quotes GPC
	 */
	static function stripMagic() {
		set_magic_quotes_runtime(0);
		// if magic_quotes_gpc strip slashes from GET POST COOKIE
		if (get_magic_quotes_gpc()){
		function stripslashes_array($array){
		 return is_array($array) ? array_map('stripslashes_array',$array) : stripslashes($array);
		}
		$_GET= stripslashes_array($_GET);
		$_POST= stripslashes_array($_POST);
		$_REQUEST= stripslashes_array($_REQUEST);
		$_COOKIE= stripslashes_array($_COOKIE);
		}
	}


	function cleanString($name) {
		if (isset($this->getvars[$name])){
			return (string)urldecode($this->getvars[$name]);
		} else {
			return (string)@urldecode($this->postvars[$name]);
		}
	}

	function cleanInt($name) {
		if (isset($this->getvars[$name])){
			return intval($this->getvars[$name]);
		} else {
			return intval(@$this->postvars[$name]);
		}
	}

	function cleanHtml($name) {
		if (isset($this->getvars[$name])){
			return (string)strip_tags(urldecode($this->getvars[$name]));
		} else {
			return (string)@strip_tags(urldecode($this->postvars[$name]));
		}
	}

	function url($params='') { 
		$baseUrl = Cgn_ObjectStore::getValue("config://templates/base/uri",$uri);
		return $baseUrl."index.php/".$params;
	}

	function getCurrentRequest() {
		return Cgn_ObjectStore::getObject('object://currentRequest');
	}
}



class Cgn_SystemRunner {

	/**
	 * list of tickets to run
	 */
	var $ticketList = array();
	var $serviceList = array();

	/**
	 * Reference to current Cgn_SystemRequest
	 */
	var $currentRequest = null;

	/**
	 * Decide which function to run based on the
	 * the input URL,
	 * format of path info is
	 * index.php/module.subModule.event/var1=blah/var2=blah
	 * (technically, index.php is not parth of PATH_INFO)
	 *
	 *
	 * ALTERNATE
	 * index.php/module.subModule/var1=blah/var2=blah/?event=foo
	 */
	function Cgn_SystemRunner() {
	}


	function initRequestTickets($url) {

		initRequestInfo();

		//look for stuff in the ini file
		#$vanityUrl = str_replace('/','.', substr($_SERVER['PATH_INFO'],1));
//		$vanityUrl = str_replace('/','.', Cgn_ObjectStore::getValue('request://mse'));
		$vanityUrl = str_replace('.','/', Cgn_ObjectStore::getValue('request://mse'));

		$potentialTicket = '';
		if (Cgn_ObjectStore::hasConfig("config://uris/".$vanityUrl)) {
			$potentialTicket = Cgn_ObjectStore::getConfig("config://uris/".$vanityUrl);
		}
		if (@strlen($potentialTicket) ) {
			$ticketRequests = explode(',',$potentialTicket);
			foreach ($ticketRequests as $tk) {
				$tkParts = explode('.', $tk);
				$x = new Cgn_SystemTicket($tkParts[0],$tkParts[1],$tkParts[2]);
				$this->ticketList[] = $x;
			}
			//inject first ticket back into MSE
			$tkParts = explode('.', $ticketRequests[0]);
			$newMse = $tkParts[0].'.'.$tkParts[1].'.'.$tkParts[2];
			Cgn_ObjectStore::storeValue('request://mse',$newMse);
		} else {
			//if not, parse URL
			$this->parseUrl($url);
		}
	}


	function parseUrl($url) {
		$mse = Cgn_ObjectStore::getObject("request://mse");
		$defaultModule = Cgn_ObjectStore::getValue("config://default/module");

		//boom
		$bits = explode('.', $mse);

		if ( @strlen($bits[0]) ) {
			$m = $bits[0];
		}
		if ( @strlen($bits[1]) ) {
			$s = $bits[1];
		}
		if ( @strlen($bits[2]) ) {
			$e = $bits[2];
		}

		$x = new Cgn_SystemTicket($m,$s,$e);

		if ( $m == $defaultModule ) {
			$x->isDefault = true;
		}
		//Cgn::debug($x);
		//Cgn::debug($x->vars);
		$this->ticketList[] = $x;
	}


	function runTickets() {
		$modulePath = Cgn_ObjectStore::getConfig('path://default/cgn/module');

		//XXX _TODO_ get template from object store. kernel should make template
		$template = array();
		$req = new Cgn_SystemRequest();
		$this->currentRequest =& $req;
		Cgn_ObjectStore::storeObject('request://currentRequest',$req);
		foreach ($this->ticketList as $_tkIdx => $tk) {
			if (!include($modulePath.'/'.$tk->module.'/'.$tk->filename) ) { 
				echo "Cannot find the requested module. ".$tk->module."/".$tk->filename;
				return false;
			}

			$className = $tk->className;
			$service = new $className();
			$this->ticketList[$_tkIdx]->instance = $service;

			$this->serviceList[] =& $service;

			$allowed = $service->init($req);

			if ($allowed == true) {
				$service->processEvent($tk->event, $req, $template);
				foreach ($template as $k => $v) {
					Cgn_Template::assignArray($k,$v);
				}

			} else {
				Cgn_ErrorStack::throwError('Unable to process request: '.$service->untrustReasons,'601','sec');
				$myTemplate =& Cgn_ObjectStore::getObject("object://defaultOutputHandler");
				$myTemplate->parseTemplate($service->templateStyle);
				return false;
				break;
			}

			/*
			if ($service->authorize($tk->event, $u) ) {
				$service->processEvent($tk->event, $req, $template);
				$allowed = true;
			} else {
				$allowed = false;
				break;
			}
			 */

		}
		//use the last service as the main one
		// OUTPUT happens here
		switch($service->presenter) {
			case 'default':
				$myTemplate =& Cgn_ObjectStore::getObject("object://defaultOutputHandler");
				$myTemplate->parseTemplate($service->templateStyle);
				break;
			case 'redirect':
				$myRedirector =& Cgn_ObjectStore::getObject("object://redirectOutputHandler");
				$myRedirector->redirect($req,$template);
			case 'self':
				$service->output($req,$template);
		}
	}

	function isAdmin() {
		return $this->currentRequest->isAdmin;
	}
}



class Cgn_SystemTicket {

	var $module;	//represents a set of services
	var $service;	//a collection of events
	var $event;	//one class method to run
	var $filename;
	var $className;
	var $instance = null; //hold an instance of the object that was run for this ticket.
	var $isDefault = false;
	var $isRouted = false;


	function Cgn_SystemTicket($m='main', $s='main', $e='main') {
		$this->module = $m;
		$this->service = $s;
		$this->event = $e;
		$this->filename = $s .'.php';
		$this->className = 'Cgn_Service_'.ucfirst($m).'_'.ucfirst($s);
	}
}


/**
 * set the basic mse and params info
 * based on the sapi 
 *
 * if no sapi is given use php_sapi_name
 *
 * (I left the option to override the sapi for testing)
 *
 * calls systemRunner::stripMagic() static method to 
 * negate effects of magic_quotes_gpc on GET/POST/COOKIE
 *
 * Stores mse (module, service, event) string in to config://mse
 * Stores the parameters array in to config://params
 *
 * Example 1
 * =========
 * http://localhost/www/index.php/foo.cal.bar/param1/param2=hello
 * 'foo.cal.bar' is available by
 * $mse = Cgn_ObjectStore::getObject('request://mse');
 * param1/param2=hello is available by
 * $params = Cgn_ObjectStore::getObject('request://params');
 * 
 * Example 2
 * =========
 * php4 /path/to/index.php foo.mycal param1 "param2=hello"
 * 'foo.mycal' is available by
 * $mse = Cgn_ObjectStore::getObject('request://mse');
 * param1 param2=hello is available by
 * $params = Cgn_ObjectStore::getObject('request://params');
 * 
 * @param string SAPI name
 *
 */ 

function initRequestInfo($sapi='') { 

	Cgn_SystemRequest::stripMagic();

	$mse = '';
	$params = array();

	if ($sapi=='') { 
		$sapi = php_sapi_name();
	}

	switch($sapi) { 

		case "cli":
			global $argv;
			$mse = $argv[1];
			@array_shift($argv);
			@array_shift($argv);
			$params = $argv;
		break;


		case "apache":
		case "apache2filter":
		case "apache2handler":
		case "cgi-fcgi":
		case "cgi":
			$params = $_REQUEST;
			$get = $_GET;
			if (array_key_exists('PATH_INFO', $_SERVER) && $_SERVER['PATH_INFO']!='') { 		
				if (substr($_SERVER['PATH_INFO'],-1) == '/' ) {
					$parts = explode("/",substr($_SERVER['PATH_INFO'],1,-1));
				} else {
					$parts = explode("/",substr($_SERVER['PATH_INFO'],1));
				}
				$mse = $parts[0];
				array_shift($parts);
				foreach($parts as $num=>$p) { 
					//only put url parts in the get and request
					// if there's no equal sign
					// otherwise you get duplicate entries "[0]=>foo=bar"
					if (!strstr($p,'=')) {
						$params[$num] = $p;
						$get[$num] = $p;
					} else {
						@list($k,$v) = explode("=",$p);
						if ($v!='') { 
							$params[$k] = $v;
							$get[$k] = $v;
						}
					}
				}
			}	

// get the base URI 
// store in the template config area for template processing

			$path = explode("/",$_SERVER['SCRIPT_NAME']);
			array_pop($path);	
			$path = implode("/",$path);
			$uri = $_SERVER['HTTP_HOST'].$path.'/';
			Cgn_ObjectStore::storeValue("config://templates/base/uri",$uri);
		break;

		default:
			die('unknonwn sapi: '.$sapi);

	}

	//i really hate php notices
//	@list($module, $service, $event) = @explode(".", $mse);
	$module = $service = $event = '';
	$mseParts = @explode('.', $mse);
	if (isset($mseParts[0])) $module  = $mseParts[0];
	if (isset($mseParts[1])) $service = $mseParts[1];
	if (isset($mseParts[2])) $event   = $mseParts[2];

	if (strlen($event) < 1 && isset($_POST['event']) ) { $event = trim($_POST['event']); }
	if (strlen($event) < 1 && isset($_GET['event']) ) { $event = trim($_GET['event']); }

	if ($module=='') { 
		$module	= Cgn_ObjectStore::getValue("config://default/module");
	}
	if ($service=='') { 
		$service= Cgn_ObjectStore::getValue("config://default/service");
	}
	if ($event=='') { 
		$event	= Cgn_ObjectStore::getValue("config://default/event");
	}
	
	$mse = $module.'.'.$service.'.'.$event;

	Cgn_ObjectStore::storeValue('request://mse', $mse);
	Cgn_ObjectStore::storeObject('request://get', $get);
	Cgn_ObjectStore::storeObject('request://request', $params);
	Cgn_ObjectStore::storeObject('request://post', $_POST);
	Cgn_ObjectStore::storeObject('request://cookie', $_COOKIE);
		

}


class Cgn_SystemRunner_Admin extends Cgn_SystemRunner {


	function runTickets() {
		//notices; undefined array keys should be handled differently
		// than undefined variables in PHP, but they're not.
		ini_set('error_reporting', E_ALL &~ E_NOTICE);

		$modulePath = Cgn_ObjectStore::getConfig('path://default/cgn/admin/module');

		//XXX _TODO_ get template from object store. kernel should make template
		$template = array();
		$req = new Cgn_SystemRequest();
		$req->isAdmin = true;
		$this->currentRequest =& $req;
		Cgn_ObjectStore::storeObject('request://currentRequest',$req);

		$systemHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");
		$u = $req->getUser();
		$allowed = false;
		foreach ($this->ticketList as $tk) {
			if(!include($modulePath.'/'.$tk->module.'/'.$tk->filename)) {
				echo "Cannot find the requested admin module. ".$tk->module."/".$tk->filename;
				return false;
			}
			$className = $tk->className;
			$service = new $className();
			$service->init($req);
			$this->serviceList[] =& $service;
			if ($service->authorize($tk->event, $u) ) {
				$service->processEvent($tk->event, $req, $template);
				$allowed = true;
			} else {
				$allowed = false;
				break;
			}

			foreach ($template as $k => $v) {
				Cgn_Template::assignArray($k,$v);
			}
		}
		if ($allowed == true) {
			switch($service->presenter) {
				case 'default':
					//use the admin template by default.
					$adminTemplate = Cgn_ObjectStore::getConfig("config://admin/template/name");
					Cgn_ObjectStore::storeConfig("config://template/default/name", $adminTemplate);

					$myTemplate =& Cgn_ObjectStore::getObject("object://defaultOutputHandler");
					$myTemplate->parseTemplate($service->templateStyle);
				break;

				case 'redirect':
					$myRedirector =& Cgn_ObjectStore::getObject("object://redirectOutputHandler");
					$myRedirector->redirect($req,$template);
				break;
				case 'self':
					$service->output($req,$template);

			}
		} else {
			$template['url'] = cgn_adminurl('login');
			$myRedirector =& Cgn_ObjectStore::getObject("object://redirectOutputHandler");
			$myRedirector->redirect($req,$template);
		}
	}
}


class Cgn_OutputHandler {

	function redirect($req,$t) {
		if ( Cgn_ErrorStack::count()) {
			echo "You are being redirected, but the script has generated errors. ";
			echo '<a href="'.$t['url'].'">Click here to proceed.</a>';
			echo Cgn_ErrorStack::showErrorBox();
		} else {
			header('Location: '.$t['url']);
		}
	}
}


class Cgn {
	function debug($x) {
		echo "<pre>\n";
		print_r($x);
		echo "</pre>\n";
	}

	/**
	 * Attempt to include a file from a number of different locations
	 *
	 * @param $name String name of the library
	 */
	static function loadModLibrary($name, $area='modules') {
		list($module, $file) = explode('::', $name);
		$module = strtolower($module);
		if (file_exists(CGN_SYS_PATH.'/'.$area.'/'.$module.'/lib/'.$file.'.php')) {
			include(CGN_SYS_PATH.'/'.$area.'/'.$module.'/lib/'.$file.'.php');
			return true;
		}
		return false;

		/*
		if (file_exists(CGN_SYS_PATH.'/modules/'.$module.'lib/'.$file.'.php')) {
			include(CGN_SYS_PATH.'/modules/'.$module.'lib/'.$file.'.php');
			return true;
		}
		 */
	}

	static function loadAppLibrary($name, $area='modules') {
		$module = strtolower($name);
		if (file_exists(CGN_SYS_PATH.'/app-lib/'.$module.'.php')) {
			include(CGN_SYS_PATH.'/app-lib/'.$module.'.php');
			return true;
		}
		return false;
	}

	static function loadLibrary($name) {
		list($module, $file) = explode('::', $name);
		$module = strtolower($module);
		$file = strtolower($file);
		if (file_exists(CGN_LIB_PATH.'/'.$module.'/'.$file.'.php')) {
			include(CGN_LIB_PATH.'/'.$module.'/'.$file.'.php');
			return true;
		}
		return false;
	}
}
?>
