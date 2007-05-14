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
	 * removes effects of Magic Quotes GPC
	 */
	function stripMagic() {
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


	function url($params='') { 
		$baseUrl = Cgn_ObjectStore::getValue("config://templates/base/uri",$uri);
		return $baseUrl."index.php/".$params;
	}
}



class Cgn_SystemRunner {

	/**
	 * list of tickets to run
	 */
	var $ticketList = array();

	/**
	 * Decide which function to run based on the
	 * the input URL,
	 * format of path info is
	 * index.php/module.subModule.event/var1=blah/var2=blah
	 * (technically, index.php is not parth of PATH_INFO)
	 */
	function Cgn_SystemRunner() {
	}


	function initRequestTickets($url) {

		initRequestInfo();

		//look for stuff in the ini file
		#$vanityUrl = str_replace('/','.', substr($_SERVER['PATH_INFO'],1));
//		$vanityUrl = str_replace('/','.', Cgn_ObjectStore::getValue('request://mse'));
		$vanityUrl = str_replace('.','/', Cgn_ObjectStore::getValue('request://mse'));

		if (Cgn_ObjectStore::hasConfig("config://uris/".$vanityUrl)) {
			$potentialTicket = Cgn_ObjectStore::getConfig("config://uris/".$vanityUrl);
		}
		if (strlen($potentialTicket) ) {
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
		//take off the first / so we can explode cleanly

		
		$mse = Cgn_ObjectStore::getObject("request://mse");

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
		//Cgn::debug($x);
		//Cgn::debug($x->vars);
		$this->ticketList[] = $x;
	}


	function runTickets() {
		$modulePath = Cgn_ObjectStore::getConfig('config://cgn/path/module');

		//XXX _TODO_ get template from object store. kernel should make template
		$template = array();
		$req = new Cgn_SystemRequest();
		foreach ($this->ticketList as $tk) {
			include($modulePath.'/'.$tk->module.'/'.$tk->filename);
			$className = $tk->className;
			$service = new $className();
			//$service->processEvent($tk->event, $this, $template);
			$service->processEvent($tk->event, $req, $template);
			foreach ($template as $k => $v) {
				Cgn_Template::assignArray($k,$v);
			}
		}
		//use the last service as the main one
		// OUTPUT happens here
		switch($service->presenter) {
			case 'default':
				$myTemplate =& Cgn_ObjectStore::getObject("object://defaultOutputHandler");
				$myTemplate->parseTemplate();
				break;
			case 'redirect':
				$myRedirector =& Cgn_ObjectStore::getObject("object://redirectOutputHandler");
				$myRedirector->redirect($req,$template);
			case 'self':
				$service->output($req,$template);
		}

	}
}



class Cgn_SystemTicket {

	var $module;	//represents a set of services
	var $service;	//a collection of events
	var $event;	//one class method to run
	var $filename;
	var $className;


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
			$params = $_REQUEST;
			$get = array();
			if (array_key_exists('PATH_INFO', $_SERVER) && $_SERVER['PATH_INFO']!='') { 		
				if (substr($_SERVER['PATH_INFO'],-1) == '/' ) {
					$parts = explode("/",substr($_SERVER['PATH_INFO'],1,-1));
				} else {
					$parts = explode("/",substr($_SERVER['PATH_INFO'],1));
				}
				$mse = $parts[0];
				array_shift($parts);
				foreach($parts as $num=>$p) { 
					$params[$num] = $p;
					$get[$num] = $p;
					list($k,$v) = explode("=",$p);
					if ($v!='') { 
						$params[$k] = $v;
						$get[$k] = $v;
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

	}

	list($module, $service, $event) = explode(".", $mse);
	if ($module=='') { 
		$module	= Cgn_ObjectStore::getValue("core://default.module");
	}
	if ($service=='') { 
		$service= Cgn_ObjectStore::getValue("core://default.service");
	}
	if ($event=='') { 
		$event	= Cgn_ObjectStore::getValue("core://default.event");
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
		$modulePath = Cgn_ObjectStore::getConfig('config://cgn/path/admin/module');

		//XXX _TODO_ get template from object store. kernel should make template
		$template = array();
		foreach ($this->ticketList as $tk) {
			include($modulePath.'/'.$tk->module.'/'.$tk->filename);
			$className = $tk->className;
			$service = new $className();
			$service->processEvent($tk->event, $this, $template);
		}
		$myTemplate =& Cgn_ObjectStore::getObject("object://defaultTemplateHandler");
		$myTemplate->parseTemplate();
	}

}


class Cgn_OutputHandler {

	function redirect($req,$t) {
		header('Location: '.$t['url']);
	}
}
?>
