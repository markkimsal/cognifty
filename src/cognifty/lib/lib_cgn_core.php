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
	var $prodEnv        = 'prod';

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

		if (defined ('CGN_PRODUCTION_ENVIRONMENT')) 
		$this->prodEnv = CGN_PRODUCTION_ENVIRONMENT;
	}

	/**
	 * @return boolean True if this production environment is 'demo'
	 */
	public function isDemo() {
		return $this->isEnv('demo');
	}

	/**
	 * @return boolean True if this production environment is 'test'
	 */
	public function isTest() {
		return $this->isEnv('test');
	}

	/**
	 * @return boolean True if this production environment is 'prod'
	 */
	public function isProduction() {
		return $this->isEnv('prod');
	}

	/**
	 * @return boolean True if this production environment is 'dev'
	 */
	public function isDevelopment() {
		return $this->isEnv('dev');
	}

	/**
	 * @return boolean True if this production environment is $state
	 */
	public function isEnv($state) {
		return $this->prodEnv == $state;
	}

	/**
	 * XXX _TODO_ list all types as defines
	 */
	function getRequestType() {
		return $this->sapiType;
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
	static function &getUser() {
		global $cgnUser;
		return $cgnUser;
	}

	/**
	 * Return the default session object.
	 *
	 * @see Cgn_Session::getSessionObj
	 * @return Object   the default session object.
	 */
	function getSession() {
		return Cgn_Session::getSessionObj();
	}


	function getSessionVar($key) {
		$m = Cgn_Session::getSessionObj();
		return $m->get($key);
	}

	function setSessionVar($key, $val) {
		$m = Cgn_Session::getSessionObj();
		return $m->set($key, $val);
	}

	function clearSessionVar($key) {
		$m = Cgn_Session::getSessionObj();
		return $m->clear($key);
	}

	/**
	 * removes effects of Magic Quotes GPC
	 */
	static function stripMagic() {
		@set_magic_quotes_runtime(0);
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
	 * This method finds a parameter from the GET or POST. 
	 * Order of preference is GET then POST
	 *
	 * @return bool  true if the key exists in get or post
	 */
	function hasParam($name) {
		if (isset($this->getvars[$name])) {
			return TRUE;
		}
		if (isset($this->postvars[$name])) {
			return TRUE;
		}
		return FALSE;
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
			$val = $this->getvars[$name];
		} else {
			$val = @$this->postvars[$name];
		}
		if ($val == '') {
			return '';
		}
		if (is_array($val)) {
			array_walk_recursive($val, array('Cgn', 'removeCtrlChar'));
		} else {
		   	Cgn::removeCtrlChar($val);
			$val = (string)$val;
		}
		return $val;

	}

	/**
	 * This method cleans a multi-line string from the GET or POST. 
	 * It does *not* escape data safely for SQL.
	 * Order of preference is GET then POST
	 *
	 * This method allows new line, line feed and tab characters
	 * @return string
	 */
	function cleanMultiLine($name) {
		if (isset($this->getvars[$name])){
			$val = $this->getvars[$name];
		} else {
			$val = @$this->postvars[$name];
		}
		if ($val == '') {
			return '';
		}
		$allow = array();
		$allow[] = ord("\t");
		$allow[] = ord("\n");
		$allow[] = ord("\r");

		if (is_array($val)) {
			array_walk_recursive($val, array('Cgn', 'removeCtrlChar'), $allow);
		} else {
		   	Cgn::removeCtrlChar($val, NULL, $allow);
			$val = (string)$val;
		}
		return $val;

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
			if (is_array($this->getvars[$name])){
				return Cgn::cleanIntArray($this->getvars[$name]);
			}
			return intval($this->getvars[$name]);
		} else {
			if (@is_array($this->postvars[$name])){
				return Cgn::cleanIntArray($this->postvars[$name]);
			}
			return intval(@$this->postvars[$name]);
		}
	}

	/**
	 * This method cleans a float from the GET or POST. 
	 * It always returns the result of floatval()
	 * Order of preference is GET then POST
	 *
	 * @return float
	 */
	function cleanFloat($name) {
		if (isset($this->getvars[$name])){
			if (is_array($this->getvars[$name])){
				return Cgn::cleanFloatArray($this->getvars[$name]);
			}
			return floatval($this->getvars[$name]);
		} else {
			if (@is_array($this->postvars[$name])){
				return Cgn::cleanFloatArray($this->postvars[$name]);
			}
			return floatval(@$this->postvars[$name]);
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

	static function url($params='') { 
		$baseUrl = Cgn_ObjectStore::getValue("config://template/base/uri",$uri);
		return $baseUrl."index.php/".$params;
	}

	static function getCurrentRequest() {
		return Cgn_ObjectStore::getObject('request://currentRequest');
	}

	public function isAdmin() {
		return $this->isAdmin;
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
		@date_default_timezone_set(@date_default_timezone_get());
	}


	function initRequestTickets($url) {

		//initRequestInfo();
		$this->initRequestObject();

		//attempt Vanity URL parsing
		$vanityUrl = '';
		$potentialTicket = '';

		//look for stuff in the ini file
		if ( isset($_SERVER['PATH_INFO'])) {
			$vanityUrl =  @substr(rawurldecode($_SERVER['PATH_INFO']),1);
			$vanityUrl =  str_replace('.', '/', $vanityUrl);
		}

		if ($vanityUrl != '' && 
				Cgn_ObjectStore::hasConfig("uris://default/".$vanityUrl)) {
			$potentialTicket = Cgn_ObjectStore::getConfig("uris://default/".$vanityUrl);
		}

		if (!strlen($potentialTicket)) {
			//try just the first part as a synonym for a module
			$vanityList = explode('/', $vanityUrl);
			if (@strlen($vanityList[0])) {
				if (Cgn_ObjectStore::hasConfig("uris://default/".$vanityList[0]))
				$potentialTicket = Cgn_ObjectStore::getConfig("uris://default/".$vanityList[0]);
			}
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
			return;
		}

		//if not, parse URL
		$this->parseUrl($url);
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
			$this->currentRequest->mse =$parts[0];
			array_shift($parts);
			foreach($parts as $num=>$p) { 
				//only put url parts in the get and request
				// if there's no equal sign
				// otherwise you get duplicate entries "[0]=>foo=bar"
				if (!strstr($p,'=')) {
					$p = rawurldecode($p);
					$params[$num] = $p;
					$get[$num] = $p;
				} else {
					@list($k,$v) = explode("=",$p);
					if ($v!='') { 
						$k = rawurldecode($k);
						$v = rawurldecode($v);
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
		} else {
			if (substr($_SERVER['PATH_INFO'],-1) == '/' ) {
				$parts = explode("/",substr($_SERVER['PATH_INFO'],1,-1));
			} else {
				$parts = explode("/",substr($_SERVER['PATH_INFO'],1));
			}
		}
		$this->currentRequest->mse = urldecode($parts[0]);
		array_shift($parts);
		foreach($parts as $num=>$p) {
			//only put url parts in the get and request
			// if there's no equal sign
			// otherwise you get duplicate entries "[0]=>foo=bar"
			if (!strstr($p,'=')) {
				$p = urldecode($p);
				$params[$num] = $p;
				$get[$num] = $p;
			} else {
				@list($k,$v) = explode("=",$p);
				if ($v!='') {
					$k = urldecode($k);
					$v = urldecode($v);
					$params[$k] = $v;
					$get[$k] = $v;
				}
			}
		}

		// get the base URI
		// store in the template config area for template processing
		if ($qflag = strpos($_SERVER['REQUEST_URI'], '?')) {
			if ($qflag !== FALSE)
			$myrequesturi = substr($_SERVER['REQUEST_URI'], 0, $qflag);
		} else {
			$myrequesturi = $_SERVER['REQUEST_URI'];
		}

		//this is a total hack.  NGINX + FCGI gets the SERVER vars correct 
		// (because it's up to you to set them in nginx conf file).  Apache + PHP CGI
		// screws up the SCRIPT_NAME on purpose for some reason.
		if (isset($_SERVER['SERVER_SOFTWARE']) && $_SERVER['SERVER_SOFTWARE'] != 'nginx') {
			if (strlen($_SERVER['PATH_INFO'])) {
				$_SERVER['FIXED_SCRIPT_NAME'] = substr($myrequesturi, 0, -strlen($_SERVER['PATH_INFO']));
			} else if (strlen($_SERVER['ORIG_PATH_INFO'])) {
				$_SERVER['FIXED_SCRIPT_NAME'] = substr($myrequesturi, 0, -strlen($_SERVER['ORIG_PATH_INFO']));
			} else {
				$_SERVER['FIXED_SCRIPT_NAME'] = $myrequesturi;
			}
		} else {
				$_SERVER['FIXED_SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'];
		}

		$path = explode("/",$_SERVER['FIXED_SCRIPT_NAME']);
		array_pop($path);
		$path = implode("/",$path);
		$uri = $_SERVER['HTTP_HOST'].$path.'/';
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

		//preload classes for session deserialization
		foreach ($this->ticketList as $tk) {
			$includeResult = class_exists($tk->className, FALSE);
			if (!$includeResult) {
				$includeResult = $this->includeService($tk);
			}
		}

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
			$currentMse = $tk->module.'.'.$tk->service.'.'.$tk->event;
			Cgn_ObjectStore::storeValue('request://mse',$currentMse);
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

		//tickets may be stacked, need to check for class exists in this 
		// function as well as in the runTickets wrapper
		$includeResult = class_exists($tk->className, FALSE);
		if (!$includeResult) {
			$includeResult = $this->includeService($tk);
		}

		$className = $tk->className;
		if (!class_exists($className)) {
			Cgn_ErrorStack::throwError('Unable to find any service at the given URL.', 500);
			return false;
		}

		$service = new $className();

		$allowed = $service->init($req, $tk->module, $tk->service, $tk->event);

		$tk->instance = $service;

		$needsLogin = false;
		if ($allowed == true) {
			$u = $req->getUser();
			if (!$service->authorize($tk->event, $u) ) {
				$allowed    = false;
				//$needsLogin = $service->requireLogin;
				$needsLogin = $u->isAnonymous();
			}
		}

		//not allowed: either init failed or user is denied
		if ($allowed != true) {
			//if not allowed, and request is ajax, simply return nothing
			if ($req->isAjax) {
				return false;
			}
			if ($needsLogin) {
				return $service->onAuthFailure($eventName, $req, $template);
			} else {
				return $service->onAccessDenied($u, $req, $template);
			}
		}

		$service->eventBefore($req, $template);
		$eventName = $tk->event;
		$service->processEvent($eventName, $req, $template);
		$service->eventAfter($req, $template);


		$e = Cgn_ErrorStack::peekError('error');
		if ( $e && strstr($e->message, 'no such event') !== FALSE && $req->isDevelopment()) {
			//if systemrequest is dev then don't throw an error
			$e = Cgn_ErrorStack::pullError('error');
		}

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
	 * If module is overridden ('path://default/override/module/MODNAME') use that path.
	 * If module is customized ('path://default/custom/module/MODNAME') try that path.
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

		if ($customPath != '' && !include($customPath.'/'.$tk->filename)) {
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

			if ($this->currentRequest->isAdmin) {
				$adminTemplate = Cgn_ObjectStore::getConfig("config://admin/template/name");
				Cgn_ObjectStore::storeConfig("config://template/default/name", $adminTemplate);
			}

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

	/**
	 * Stack a ticket for the front-end system
	 *
	 * @param $tick Cgn_SystemTicket
	 * @void
	 */
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

	static function getReleaseNumber() {
		return Cgn_ObjectStore::getConfig('core://release.number');
	}

	static function getBuildNumber() {
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

		//preload classes for session deserialization
		foreach ($this->ticketList as $tk) {
			$includeResult = class_exists($tk->className, FALSE);
			if (!$includeResult) {
				$includeResult = $this->includeService($tk);
			}
		}

		$mySession =& Cgn_Session::getSessionObj();
		$mySession->start();

		$req = $this->currentRequest;
		$req->getUser()->startSession();

		$req->isAdmin = true;
		$this->currentRequest =& $req;
		Cgn_ObjectStore::storeObject('request://currentRequest',$req);

		$systemHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");
		$u = $req->getUser();

		//set up the template vars
		$template = array();
		Cgn_ObjectStore::setArray("template://variables/", $template);

		while(count($this->ticketList)) {
			$tk = array_shift($this->ticketList);
			$currentMse = $tk->module.'.'.$tk->service.'.'.$tk->event;
			Cgn_ObjectStore::storeValue('request://mse',$currentMse);
			$service = $this->runCogniftyTicket($tk);
			$this->ticketDoneList[] = $tk;
		}
		if (! is_object($service)) {
			return false;
		}

		switch($service->presenter) {
			case 'default':
				//use the admin template by default.
				$adminTemplate = Cgn_ObjectStore::getConfig("config://admin/template/name");
				Cgn_ObjectStore::storeConfig("config://template/default/name", $adminTemplate);
				$myTemplate =& Cgn_ObjectStore::getObject("object://defaultOutputHandler");
				$myTemplate->parseTemplate($service->templateStyle);
			break;

			case 'redirect':
				$template = Cgn_ObjectStore::getArray("template://variables/");
				$myRedirector =& Cgn_ObjectStore::getObject("object://redirectOutputHandler");
				$myRedirector->redirect($req, $template);
			break;
			case 'self':
				$template = Cgn_ObjectStore::getArray("template://variables/");
				$service->output($req, $template);

		}
		/*

		//ajax request which did not pass init() or authorize()
		// don't make a template, just return blank
		if ($req->isAjax) {
			return FALSE;
		}
		if ($needsLogin) {
			$template['url'] = cgn_adminurl('login');
			$myRedirector =& Cgn_ObjectStore::getObject("object://redirectOutputHandler");
			$myRedirector->redirect($req,$template);
			return FALSE;
		} else {
			//switch the template to thde default admin template
			$adminTemplate = 
				Cgn_ObjectStore::getConfig("config://admin/template/name");

			Cgn_ObjectStore::storeConfig("config://template/default/name", 
				$adminTemplate);

			Cgn_ErrorStack::throwError('Unable to process request: '.
				'Your request was not trusted by the server.', '601', 'sec');
			$myTemplate =& Cgn_ObjectStore::getObject("object://defaultOutputHandler");
			$myTemplate->parseTemplate($service->templateStyle);
			return FALSE;
		}
		 */
	}

	/**
	 * Try to include a service from a variety of directories.
	 *
	 * If module is overridden ('config://admin/override/module/MODNAME') use that path.
	 * If module is customized ('config://admin/custom/module/MODNAME') try that path.
	 *
	 * Else use default module path ('path://admin/cgn/module').
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

		$modulePath = Cgn::getModulePath($tk->module, 'admin');

		if ($customPath != '' && !include($customPath.'/'.$tk->filename)) {
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
	 * Stack a ticket for the admin system
	 *
	 * @param $tick Cgn_SystemTicket
	 * @void
	 */
	static function stackTicket($tick) {
		$myHandler =& Cgn_ObjectStore::getObject("object://adminSystemHandler");
		array_push($myHandler->ticketList, $tick);
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
		if (is_object($x) 
			&& method_exists($x, '__toDebug')) {
				echo $x->__toDebug();
		} else {
			print_r($x);
		}
		echo "</pre>\n";
	}

	/**
	 * Return text cleaned enough to be used in a URL
	 */
	static public function makeLinkText($t) {
		$lt = str_replace('&', ' and ', $t);
		$lt = str_replace(' ', '_', $lt);

		$pattern = '/[\x{21}-\x{2C}]|[\x{2F}]|[\x{5B}-\x{5E}]|[\x{7E}]/';
		$preglt = preg_replace($pattern, '_', $lt);
		if ($preglt == '') {
			//preg throws an error if the pattern cannot compile
			//(old preg libraries)
			$e = Cgn_ErrorStack::pullError('php');
			$len = strlen($lt);
			for($i = 0; $i < $len; $i++) {
				$hex =ord($lt{$i});
				if ($hex < 44 || $hex == 47 ) {
					$lt{$i} = '_';
				}

				if ($hex >= 91 && $hex <= 94 ) {
					$lt{$i} = '_';
				}
				if ($hex == 126 ) {
					$lt{$i} = '_';
				}
			}
		$preglt = $lt;
		}

		$lt = str_replace('___', '_', $preglt);
		$lt = str_replace('__', '_', $lt);
		$lt = str_replace('__', '_', $lt);
		return $lt;
	}

	/**
	 * Clean a multi-dimensional array of integers
	 */
	static public function cleanIntArray($input, $loop=0) {
		if ($loop >100) return (int)$input;
		if (!is_array($input)) return (int)$input;
		$output = array();
		foreach ($input as $k=>$v) {
			$output[$k] = Cgn::cleanIntArray($v, $loop++);
		}
		return $output;
	}

	/**
	 * Clean a multi-dimensional array of doubles/floats
	 */
	static public function cleanFloatArray($input, $loop=0) {
		if ($loop >100) return (float)$input;
		if (!is_array($input)) return (float)$input;
		$output = array();
		foreach ($input as $k=>$v) {
			$output[$k] = Cgn::cleanFloatArray($v, $loop++);
		}
		return $output;
	}

	/**
	 * Replaces any non-printable control characters with underscores (_).
	 * Can be called with array_walk or array_walk_recursive
	 */
	static public function removeCtrlChar(&$input, $key = NULL, $allow = array()) {
		//preg throws an error if the pattern cannot compile
		$len = strlen($input);
		$extra = count($allow);
		for($i = 0; $i < $len; $i++) {
			$hex =ord($input{$i});
			if ($extra && in_array($hex, $allow)) {
				continue;
			}
			if ( ($hex < 32) ) {
				$input{$i} = '_';
			}
			if ($hex == 127 ) {
				$input{$i} = '_';
			}
		}
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
			$overrideKey = 'path://default/override/admin/'.$moduleName;
			$customKey   = 'path://default/custom/admin/'.$moduleName;
			$defaultKey  = 'path://admin/cgn/module';
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
			$overrideKey = 'path://default/override/admin/'.$moduleName;
		}

		return Cgn_ObjectStore::hasConfig($customKey);
	}
}
?>
