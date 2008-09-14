<?php

include(CGN_LIB_PATH.'/lib_cgn_service.php');

/**
 * Wrap an outside request into a set of
 * variables.  Defaults to HTTP environment
 */
class Cgn_SystemRequest {

	var $vars           = array();
	var $getvars        = array();
	var $postvars       = array();
	var $cookies        = array();
	var $isAdmin        = FALSE;
	var $requestedUrl   = '';
	var $mse            = '';
	var $sapiType       = '';
	var $isAjax         = FALSE;

	/**
	 * Parse the URL into system request information
	 *
	 * if there's no '=' in a var, then add the vars positionally
	 * 
	 * foo.bar.baz/myvarX/key=5
	 * vars[1] would be myvarX
	 * vars['key'] would be 5
	 *
	 */
	function Cgn_SystemRequest() {
		/*
		$this->vars = Cgn_ObjectStore::getObject('request://request');
		$this->getvars = Cgn_ObjectStore::getObject('request://get');
		$this->postvars = Cgn_ObjectStore::getObject('request://post');
		$this->cookies = Cgn_ObjectStore::getObject('request://cookie');
		 */
//		$this->get =& $this->getvars;
//		$this->post =& $this->postvars;
	}


	/**
	 * XXX _TODO_ list all types as defines
	 */
	function getRequestType() {
		return $this->sapiType;
		/*
		if(php_sapi_name()=='cli') { 
			return 'cli';
		} else { 
			return 'http';
		}
		 */
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
		function stripslashes_array($array) {
		 return is_array($array) ? array_map('stripslashes_array',$array) : stripslashes($array);
		}
		$_GET= stripslashes_array($_GET);
		$_POST= stripslashes_array($_POST);
		$_REQUEST= stripslashes_array($_REQUEST);
		$_COOKIE= stripslashes_array($_COOKIE);
		}
	}


	/**
	 * This method cleans a string from the GET or POST. 
	 * It does *not* escape data safely for SQL.
	 * Order of preference is GET then POST
	 *
	 * @return string
	 */
	function cleanString($name) {
		if (isset($this->getvars[$name])){
			return (string)urldecode($this->getvars[$name]);
		} else {
			return (string)@urldecode($this->postvars[$name]);
		}
	}

	/**
	 * This method cleans an integer from the GET or POST. 
	 * It always returns the result of intval()
	 * Order of preference is GET then POST
	 *
	 * @return int
	 */
	function cleanInt($name) {
		if (isset($this->getvars[$name])){
			return intval($this->getvars[$name]);
		} else {
			return intval(@$this->postvars[$name]);
		}
	}

	/**
	 * This method cleans a string from the GET or POST, removing any HTML tags. 
	 * It does *not* escape data safely for SQL.
	 * Order of preference is GET then POST
	 *
	 * @return string
	 */
	function cleanHtml($name) {
		if (isset($this->getvars[$name])){
			return (string)strip_tags(urldecode($this->getvars[$name]));
		} else {
			return (string)@strip_tags(urldecode($this->postvars[$name]));
		}
	}

	function url($params='') { 
		$baseUrl = Cgn_ObjectStore::getValue("config://template/base/uri",$uri);
		return $baseUrl."index.php/".$params;
	}

	function getCurrentRequest() {
		return Cgn_ObjectStore::getObject('request://currentRequest');
	}
}



class Cgn_SystemRunner {

	/**
	 * list of tickets to run
	 */
	var $ticketList     = array();
	var $ticketDoneList = array();
	var $serviceList    = array();

	/**
	 * Reference to current Cgn_SystemRequest
	 */
	var $currentRequest = NULL;

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

		//initRequestInfo();
		$this->initRequestObject();

		//attempt Vanity URL parsing
		$vanityUrl = '';
		$potentialTicket = '';

		//look for stuff in the ini file
		if ( isset($_SERVER['PATH_INFO'])) {
			$vanityUrl =  @substr($_SERVER['PATH_INFO'],1);
			$vanityUrl =  str_replace('.', '/', $vanityUrl);
		}

		if ($vanityUrl != '' && 
				Cgn_ObjectStore::hasConfig("uris://default/".$vanityUrl)) {
			$potentialTicket = Cgn_ObjectStore::getConfig("uris://default/".$vanityUrl);
		}

		if (strlen($potentialTicket) ) {
			$ticketRequests = explode(',', $potentialTicket);
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

	function processSapiCli() {
		global $argv;
		$this->currentRequest->sapiType = 'cli';

		//cron.php or index.php from arg list
		@array_shift($argv);
		$this->currentRequest->requestedUrl = implode('/', $argv);
		$this->currentRequest->mse = $argv[0];
		@array_shift($argv);

		foreach($argv as $num=>$p) { 
			//only put argv in the get and request
			// if there's no equal sign
			// otherwise you get duplicate entries "[0]=>foo=bar"
			if (!strstr($p,'=')) {
				$argv[$num] = $p;
				$get[$num] = $p;
			} else {
				@list($k,$v) = explode("=",$p);
				if ($v!='') { 
					$argv[$k] = $v;
					$get[$k] = $v;
				}
			}
		}
		$this->currentRequest->getvars = $get;
	}

	function processSapiHttp() {
		$params = $_REQUEST;
		$get = $_GET;
		$this->currentRequest->sapiType = 'http';
		if (array_key_exists('PATH_INFO', $_SERVER) && $_SERVER['PATH_INFO']!='') { 		
			$this->currentRequest->requestedUrl = $_SERVER['PATH_INFO'];

			if (substr($_SERVER['PATH_INFO'],-1) == '/' ) {
				$parts = explode("/",substr($_SERVER['PATH_INFO'],1,-1));
			} else {
				$parts = explode("/",substr($_SERVER['PATH_INFO'],1));
			}
			$this->currentRequest->mse = $parts[0];
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

		$this->currentRequest->vars = $params;
		$this->currentRequest->getvars = $get;
		$this->currentRequest->postvars = $_POST;

		// get the base URI 
		// store in the template config area for template processing

		$path = explode("/",$_SERVER['SCRIPT_NAME']);
		array_pop($path);	
		$path = implode("/",$path);
		$uri = $_SERVER['HTTP_HOST'].$path.'/';
		Cgn_ObjectStore::storeValue("config://template/base/uri",$uri);
	}

	function processSapiCgi() {
		$params = $_REQUEST;
		$get = $_GET;
		$this->currentRequest->sapiType = 'cgi';
		if (array_key_exists('ORIG_PATH_INFO', $_SERVER) && $_SERVER['ORIG_PATH_INFO']!='') {
			$this->currentRequest->requestedUrl = $_SERVER['ORIG_PATH_INFO'];
			if (substr($_SERVER['PATH_INFO'],-1) == '/' ) {
				$parts = explode("/",substr($_SERVER['ORIG_PATH_INFO'],1,-1));
			} else {
				$parts = explode("/",substr($_SERVER['ORIG_PATH_INFO'],1));
			}
			$this->currentRequest->mse = $parts[0];
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

		if (strlen($_SERVER['PATH_INFO'])) {
			$_SERVER['FIXED_SCRIPT_NAME'] = substr($_SERVER['REQUEST_URI'], 0, -strlen($_SERVER['PATH_INFO']));
		} else if (strlen($_SERVER['ORIG_PATH_INFO'])) {
			$_SERVER['FIXED_SCRIPT_NAME'] = substr($_SERVER['REQUEST_URI'], 0, -strlen($_SERVER['ORIG_PATH_INFO']));
		} else {
			$_SERVER['FIXED_SCRIPT_NAME'] = $_SERVER['REQUEST_URI'];
		}
		$path = explode("/",$_SERVER['FIXED_SCRIPT_NAME']);
		array_pop($path);
		$path = implode("/",$path);
		$uri = $_SERVER['HTTP_HOST'].$path.'/';
//          var_dump($_SERVER);
//          die($_SERVER['FIXED_SCRIPT_NAME']);
		Cgn_ObjectStore::storeValue("config://template/base/uri",$uri);

		$this->currentRequest->vars = $params;
		$this->currentRequest->getvars = $get;
		$this->currentRequest->postvars = $_POST;
	}

	function initRequestObject($sapi='') { 

		Cgn_SystemRequest::stripMagic();
		$this->currentRequest = new Cgn_SystemRequest();

		if ($sapi=='') { 
			$sapi = php_sapi_name();
		}

		switch($sapi) { 

			case "cli":
				$this->processSapiCli();
			break;


			case "apache":
			case "apache2filter":
			case "apache2handler":
				$this->processSapiHttp();
			break;


			case "cgi-fcgi":
			case "cgi":
				$this->processSapiCgi();
			break;

			default:
				die('unknonwn sapi: '.$sapi);

		}

		$module = $service = $event = '';
		$mseParts = @explode('.', $this->currentRequest->mse);
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

		if (in_array( 'xhr', array_keys($this->currentRequest->vars),TRUE)) {
			$this->currentRequest->isAjax = TRUE;
		} else {
			$this->currentRequest->isAjax = FALSE;
		}

		//i really hate php notices
	//	@list($module, $service, $event) = @explode(".", $mse);
		/*
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
		if (in_array( 'xhr', array_keys($params))) {
			$true = true;
			Cgn_ObjectStore::storeValue('request://ajax', $true);
		} else {
			$false = false;
			Cgn_ObjectStore::storeValue('request://ajax', $false);
		}
*/
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


	/**
	 * Run all tickets.
	 *
	 * When a ticket is done, move it to $this->ticketDoneList[].
	 * Initialize the session and the template here.
	 */
	function runTickets() {

		//initialize the class if it has not been loaded yet (lazy loading)
		Cgn_ObjectStore::getObject('object://defaultSessionLayer');

		$mySession =& Cgn_Session::getSessionObj();
		$mySession->start();

		//initialize the class if it has not been loaded yet (lazy loading)
		//@@TODO, the new autoload function should make this unneeded
		Cgn_ObjectStore::getObject('object://defaultOutputHandler');

		$req = &$this->currentRequest;

		//start the session here
		$req->getUser()->startSession();

		//set up the template vars
		$template = array();
		Cgn_ObjectStore::setArray("template://variables/", $template);


		Cgn_ObjectStore::storeObject('request://currentRequest',$this->currentRequest);
		while(count($this->ticketList)) {
			$tk = array_shift($this->ticketList);
			$service = $this->runCogniftyTicket($tk);
			$this->ticketDoneList[] = $tk;
		}

		if (! is_object($service)) {
			return false;
		}

		//use the last service as the main one
		// OUTPUT happens here

		switch($service->presenter) {
			case 'default':
				$myTemplate =& Cgn_ObjectStore::getObject("object://defaultOutputHandler");
				if ($service->templateName != '') {
					$myTemplate->contentTpl = $service->templateName;
				}
				$myTemplate->parseTemplate($service->templateStyle);
				break;
			case 'redirect':
				$template = Cgn_ObjectStore::getArray("template://variables/");
				$myRedirector =& Cgn_ObjectStore::getObject("object://redirectOutputHandler");
				$myRedirector->redirect($req,$template);
				break;
			case 'self':
				$template = Cgn_ObjectStore::getArray("template://variables/");
				$service->output($req,$template);
				break;
			default:
				break;
		}
		Cgn_Template::cleanAll();
		$mySession->close();
	}

	/**
	 * Runs an individual ticket by loading a module and service and calling the event.
	 *
	 * @param Cgn_SystemTicket $tk  the current ticket in the request stack from "initRequestInfo"
	 *
	 * @see initRequestInfo()
	 */
	public function runCogniftyTicket($tk) {

		//create a fresh template array for every ticket, merge results later
		$template = array();
		$req = $this->currentRequest;

		$includeResult = class_exists($tk->className, FALSE);
		if (!$includeResult) {
			$includeResult = $this->includeService($tk);
		}

		if (!$includeResult) {
			return false;
		}

		$className = $tk->className;
		$service = new $className();

		$allowed = $service->init($req, $tk->module, $tk->service, $tk->event);

		$tk->instance = $service;

		$needsLogin = false;
		if ($allowed == true) {
			$u = $req->getUser();
			if (!$service->authorize($tk->event, $u) ) {
				$allowed = false;
				$needsLogin  = true;
			}
		}
		if ($allowed == true) {
			/**
			 * handle module configuration
			 */
			if ($service->usesConfig === true) {
				$serviceConfig =& Cgn_ObjectStore::getObject('object://defaultConfigHandler');
				$serviceConfig->initModule($tk->module);
				$service->initConfig($serviceConfig);
			}
		} else {
			//not allowed, init went fine though
			//if not allowed, and request is ajax, simply return nothing
			if ($req->isAjax) {
				return false;
			}
			if ($needsLogin) {
				$newTicket = new Cgn_SystemTicket('login', 'main', 'requireLogin');
				array_push($this->ticketList, $newTicket);
				Cgn_Template::assignArray('redir', base64_encode(
					cgn_appurl($tk->module, $tk->service, $tk->event, $req->getvars)
				));
				return false;
			} else {
				Cgn_ErrorStack::throwError('Unable to process request: Your request was not trusted by the server.', '601', 'sec');
				$myTemplate =& Cgn_ObjectStore::getObject("object://defaultOutputHandler");
				$myTemplate->parseTemplate($service->templateStyle);
				return false;
			}
		}

		$currentMse = $tk->module.'.'.$tk->service.'.'.$tk->event;
		Cgn_ObjectStore::storeValue('request://mse',$currentMse);


		$service->eventBefore($req, $template);
		$eventName = $tk->event;
		$service->processEvent($eventName, $req, $template);
		$service->eventAfter($req, $template);
		foreach ($template as $k => $v) {
			Cgn_Template::assignArray($k,$v);
		}
		//cleanup
		unset($template);

		return $service;
	}

	/**
	 * Try to include a service from a variety of directories.
	 *
	 * If module is overridden ('config://override/module/MODNAME') use that path.
	 * If module is customized ('config://custom/module/MODNAME') try that path.
	 *
	 * Else use default module path ('path://default/cgn/module').
	 *
	 * If the module cannot be included at all, slip-stream in the file not found service ('config://default/fnf').
	 *
	 * The customized path is treated as a fallback mechanism for changing 1 or 2 files of a default module.
	 * The override path is used as a complete replacement, there is no fallback for missing files.
	 *
	 * @param $tk Cgn_SystemTicket ticket file from runCogniftyTickets function
	 * @return bool  false if the file could not be included
	 */
	function includeService($tk) {
		$customPath = '';
		if ( Cgn_ObjectStore::hasConfig('path://default/override/module/'.$tk->module)) {
			$modulePath = Cgn_ObjectStore::getConfig('path://default/override/module/'.$tk->module);
		} else if (Cgn_ObjectStore::hasConfig('path://default/custom/module/'.$tk->module)) {
			$customPath = Cgn_ObjectStore::getConfig('path://default/override/module/'.$tk->module);
			$modulePath = Cgn_ObjectStore::getConfig('path://default/cgn/module').'/'.$tk->module;
		} else {
			$modulePath = Cgn_ObjectStore::getConfig('path://default/cgn/module').'/'.$tk->module;
		}

		if ($customPath != '' && !@include($customPath.'/'.$tk->filename)) {
			//fallback
			Cgn_ErrorStack::pullError('php');
			Cgn_ErrorStack::pullError('php');
			if (!include($modulePath.'/'.$tk->filename) ) { 
				Cgn_ErrorStack::pullError('php');
				Cgn_ErrorStack::pullError('php');
				$this->handleFileNotFound($tk);
				return FALSE;
			}
			return TRUE;
		}
		if (!include($modulePath.'/'.$tk->filename) ) { 
			Cgn_ErrorStack::pullError('php');
			Cgn_ErrorStack::pullError('php');
			$this->handleFileNotFound($tk);
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Handle file not found errors by adding a ticket to execute the fnf module
	 */
	function handleFileNotFound($tk) {
		//load the file not found settings from default.ini
		$fnf = Cgn_ObjectStore::getArray('config://default/fnf');

		//if the ticket is exactly the FNF settings, then we can't even find the 404 page
		if ($tk->module === $fnf['module']
			&& $tk->service === $fnf['service']
			&& $tk->event === $fnf['event']) {

			//don't get caught in an infinite loop
			Cgn_Template::showFatalError('404');
			return;
		}
		//make a new ticket based on the fnf settings and slip stream it into the ticket list
		$newTicket = new Cgn_SystemTicket($fnf['module'], $fnf['service'], $fnf['event']);
		array_push($this->ticketList, $newTicket);
	}


	function unsetTickets() {
		foreach ($this->ticketList as $idx => $tk) {
			unset($tk->instance);
			unset($tk);
			unset($this->ticketList[$idx]);
		}
		foreach ($this->ticketDoneList as $idx => $tk) {
			unset($tk->instance);
			unset($tk);
			unset($this->ticketDoneList[$idx]);
		}
		$this->ticketList = array();
		$this->ticketDoneList = array();
	}

	static function stackTicket($tick) {
		$myHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");
		array_push($myHandler->ticketList, $tick);
	}

	function __destruct() {
		$this->unsetTickets();
	}

	function isAdmin() {
		return $this->currentRequest->isAdmin;
	}

	function getReleaseNumber() {
		return Cgn_ObjectStore::getConfig('core://release.number');
	}

	function getBuildNumber() {
		return Cgn_ObjectStore::getConfig('core://build.number');
	}
}



class Cgn_SystemTicket {

	var $module;	//represents a set of services
	var $service;	//a collection of events
	var $event;	//one class method to run
	var $filename;
	var $className;
	var $instance   = NULL; //hold an instance of the object that was run for this ticket.
	var $isDefault  = false;
	var $isRouted   = false;
	var $isFinished = false;//one MSE ran for this module?


	function Cgn_SystemTicket($m='main', $s='main', $e='main') {
		$this->module = $m;
		$this->service = $s;
		$this->event = $e;
		$this->filename = $s .'.php';
		$this->className = 'Cgn_Service_'.str_replace('-', '', ucfirst($m)).'_'.ucfirst($s);
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

}


class Cgn_SystemRunner_Admin extends Cgn_SystemRunner {


	function runTickets() {
		//notices; undefined array keys should be handled differently
		// than undefined variables in PHP, but they're not.
		ini_set('error_reporting', E_ALL &~ E_NOTICE);

		$mySession =& Cgn_Session::getSessionObj();
		$mySession->start();

		$req = $this->currentRequest;
		$req->getUser()->startSession();

		$modulePath = Cgn_ObjectStore::getConfig('path://default/cgn/admin/module');

		//XXX _TODO_ get template from object store. kernel should make template
		$template = array();
		$req->isAdmin = true;
		$this->currentRequest =& $req;
		Cgn_ObjectStore::storeObject('request://currentRequest',$req);

		$systemHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");
		$u = $req->getUser();
		$allowed = false;
		foreach ($this->ticketList as $_tkIdx => $tk) {
			if(!@include($modulePath.'/'.$tk->module.'/'.$tk->filename)) {
				echo "Cannot find the requested admin module. ".$tk->module."/".$tk->filename;
				return false;
			}
			$className = $tk->className;
			$service = new $className();

			$allowed = $service->init($req, $tk->module, $tk->service, $tk->event);

			/**
			 * handle module configuration
			 */
			if ($service->usesConfig === TRUE) {
				$serviceConfig =& Cgn_ObjectStore::getObject('object://defaultConfigHandler');
				$serviceConfig->initModule($tk->module);
				$service->initConfig($serviceConfig);
			}


			$this->ticketList[$_tkIdx]->instance = $service;
			$this->serviceList[] =& $service;

			if ($service->authorize($tk->event, $u) ) {
				$service->eventBefore($req, $template);
				$service->processEvent($tk->event, $req, $template);
				$service->eventAfter($req, $template);
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
	 * @param $area String either 'modules' or 'admin'
	 */
	static function loadModLibrary($name, $area='modules') {
		list($module, $file) = explode('::', $name);
		$module = strtolower($module);

		$modulePath = Cgn::getModulePath($module, $area);

		if (file_exists($modulePath.'/lib/'.$file.'.php')) {
			include_once($modulePath.'/lib/'.$file.'.php');
			return TRUE;
		} else {
			//failed to include
			//if customize, try fallback
			//otherwise, return FALSE
			if (Cgn::isModuleCustomized($module, $area)) {
				$fallbackPath = Cgn::getFallbackModulePath($module, $area);
				if (file_exists($fallbackPath.'/lib/'.$file.'.php')) {
					include_once($fallbackPath.'/lib/'.$file.'.php');
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	static function loadAppLibrary($name, $area='modules') {
		if (strpos($name , '::')) {
			list($module, $file) = explode('::', $name);
			$module = strtolower($module).'/'.strtolower($file);
			$file = strtolower($file);
		} else {
			$module = strtolower($name);
		}
		if (file_exists(CGN_APPLIB_PATH.'/'.$module.'.php')) {
			include_once(CGN_APPLIB_PATH.'/'.$module.'.php');
			return true;
		}
		return false;
	}


	static function loadLibrary($name) {
		if (strpos($name , '::')) {
			list($module, $file) = explode('::', $name);
			$module = strtolower($module).'/'.strtolower($file);
			$file = strtolower($file);
		} else {
			$module = strtolower($name);
		}
		if (file_exists(CGN_LIB_PATH.'/'.$module.'.php')) {
			include_once(CGN_LIB_PATH.'/'.$module.'.php');
			return true;
		}
		return false;
	}

	/**
	 * Used to find the module directory's location if it is overridden.
	 */
	static function getModulePath($moduleName, $area='modules') {
		$customPath   = '';
		$fallbackPath =  CGN_SYS_PATH.'/'.$area.'/'.$moduleName;
		if ($area == 'modules') {
			$overrideKey = 'path://default/override/module/'.$moduleName;
			$customKey   = 'path://default/custom/module/'.$moduleName;
			$defaultKey  = 'path://default/cgn/module';
		} else {
			$overrideKey = 'path://default/override/module/'.$moduleName;
			$customKey   = 'path://default/custom/module/'.$moduleName;
			$defaultKey  = 'path://default/cgn/admin/module';
		}

		if ( Cgn_ObjectStore::hasConfig($overrideKey)) {
			$modulePath = Cgn_ObjectStore::getConfig($overrideKey);
		} else if (Cgn_ObjectStore::hasConfig($customKey)) {
			$modulePath = Cgn_ObjectStore::getConfig($customKey);
		} else if (Cgn_ObjectStore::hasConfig($defaultKey)) {
			$modulePath = Cgn_ObjectStore::getConfig($defaultKey).'/'.$moduleName;
		} else {
			$modulePath = $fallbackPath; 
		}

		return $modulePath;
	}


	/**
	 * Used to find a fallback directory if parts of the module are not customized
	 */
	static function getFallbackModulePath($moduleName, $area='modules') {

		$fallbackPath =  CGN_SYS_PATH.'/'.$area.'/'.$moduleName;
		return $fallbackPath;
	}

	/**
	 * Return true if the module is customized, this is different than overridden
	 *
	 * @returm bool TRUE if the module is customized, FALSE otherwhise
	 */
	static function isModuleCustomized($moduleName, $area='modules') {
		if ($area == 'modules') {
			$customKey   = 'path://default/custom/module/'.$moduleName;
		} else {
			$customKey   = 'path://default/custom/module/'.$moduleName;
		}

		return Cgn_ObjectStore::hasConfig($customKey);
	}

	/**
	 * Return true if the module is overridden, this is different than customized
	 *
	 * @returm bool TRUE if the module is overridden, FALSE otherwhise
	 */
	static function isModuleOverridden($moduleName, $area='modules') {
		if ($area == 'modules') {
			$overrideKey = 'path://default/override/module/'.$moduleName;
		} else {
			$overrideKey = 'path://default/override/module/'.$moduleName;
		}

		return Cgn_ObjectStore::hasConfig($customKey);
	}
}
?>
