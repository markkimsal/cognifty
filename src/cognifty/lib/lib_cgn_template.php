<?php


class Cgn_Template {


	var $templateStyle = 'index';
	var $contentTpl    = '';
	var $scriptLinks   = array();
	var $styleLinks    = array();
	var $extraJs       = array();
	var $charset       = 'UTF-8';
	var $headTags      = array();
	/**
	 * Array of callback functions stored by section.id
	 */
	var $callbacks     = array();
	var $styleSheets   = array();


	function Cgn_Template() {
		//$this->templateName = Cgn_ObjectStore::getString("config://template/default/name");
	}

	static function baseurl($useHttps = false) {
		static $baseUri;

		if (!$baseUri) {
			$baseUri = Cgn_ObjectStore::getString("config://template/base/uri");
		}
		return $baseUri;
	}

	static function baseadminurl() {
		static $baseUri;
		if (!$baseUri) {
			if (Cgn_ObjectStore::hasConfig("config://template/base/adminuri")) {
				$baseUri = Cgn_ObjectStore::getString("config://template/base/adminuri");
			} else {
				$baseUri = Cgn_ObjectStore::getString("config://template/base/uri");
			}
		}

		return urldecode($baseUri);
	}

	static function url() {
		static $baseUri;
		static $templateName;
		static $baseDir;
		if (!$baseDir) {
			$baseDir = Cgn_ObjectStore::getString("config://template/base/dir");
		}
		if (!$baseUri) {
			$baseUri = Cgn_ObjectStore::getString("config://template/base/uri");
		}
		if (!$templateName) {
			$templateName = Cgn_ObjectStore::getString("config://template/default/name");
		}
		return $baseUri.$baseDir.$templateName.'/';
	}

	static function setSiteName($n) {
		 Cgn_ObjectStore::storeConfig("config://template/site/name", $n);
	}

	static function siteName() {
		static $siteName;
		if (!$siteName) {
			$siteName = Cgn_ObjectStore::getString("config://template/site/name");
		}
		return $siteName;
	}

	static function setPageTitle($t) {
		 Cgn_ObjectStore::storeConfig("config://template/site/pageTitle", $t);
	}

	static function getPageTitle() {
		static $pageTitle,$charset;
		if (!$pageTitle) {
			if (Cgn_ObjectStore::hasConfig("config://template/site/pageTitle")) {
				$pageTitle = Cgn_ObjectStore::getString("config://template/site/pageTitle");
			} else {
				$pageTitle = Cgn_Template::siteTagLine();
			}

			/*
			if (Cgn_ObjectStore::hasConfig("config://template/site/charset")) {
				$charset = Cgn_ObjectStore::getString("config://template/site/charset");
			} else {
				$charset = 'UTF-8';
			}
			*/
		}
		return htmlentities($pageTitle,ENT_QUOTES);
		//return htmlentities($pageTitle,ENT_QUOTES,$charset);
	}

	static function setSiteTagLine($tagLine) {
		Cgn_ObjectStore::storeConfig("config://template/site/tagline", $tagLine);
	}


	static function siteTagLine() {
		static $siteTag;
		if (!$siteTag) {
			$siteTag = Cgn_ObjectStore::getString("config://template/site/tagline");
		}
		return $siteTag;
	}

	/**
	 * Return a string containing HTML link tags for reach element of $styleSheets array
	 *
	 * @return String   HTML of link tags
	 */
	static function getSiteCss() {
		$ret = '';
		$handler = Cgn_Template::getDefaultHandler();
		if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')) {
			$templateUrl = 'https://'.Cgn_Template::url();
		} else {
			$templateUrl = 'http://'.Cgn_Template::url();
		}
		foreach ($handler->styleSheets as $s) {

			if (strpos($s, '/') === 0 ) {
				$ret .= '<link rel="stylesheet"  type="text/css" href="'.cgn_url().$s.'"></link>'."\n";
			} else {
				$ret .= '<link rel="stylesheet"  type="text/css" href="'.$templateUrl.$s.'"></link>'."\n";
			}
		}
		return $ret;
	}


	/**
	 * Add a complete HTML tag which should be shown in the template HEAD
	 *
	 * @param String $t  full html tag, eg. &lt;link rel=""...
	 */
	static function addSiteHead($t) {
		$handler = Cgn_Template::getDefaultHandler();
		$handler->headTags[] = $t;
	}

	/**
	 * This function is suitable to print inside the <head> tags.
	 * Return a string of all the contents of $headTags
	 *
	 * @return String  all the contents of the default handler's $headTags array
	 */
	static function getSiteHead() {
		$handler = Cgn_Template::getDefaultHandler();
		return implode("\n", $handler->headTags);
	}

	/**
	 * Add a stylesheet to be added to the template.
	 * Only the URL part after "cgn_templateurl()" needs to be passed.
	 *
	 * @param String $s  name of the css file
	 * @return  void
	 */
	static function addSiteCss($s) {
		$handler = Cgn_Template::getDefaultHandler();
		$handler->styleSheets[] = $s;
	}

	/**
	 * Add a URL or full < script > tag to be added to the template 
	 * at a spot specified by the user.
	 *
	 * If the parameter contains a < script > tag, then it will be printed completely.
	 * If the parameter contains a slash / as the first character, it will be
	 *  used as the SRC attribute.
	 * Otherwise, the parameter is treated as the remainder of the SRC attribute after the
	 *  current template URL is added.
	 * @param String $s  either a SRC attribute to a script, or a full < script tag
	 */
	static function addSiteJs($s) {
		$handler = Cgn_Template::getDefaultHandler();
		$handler->extraJs[] = $s;
	}

	/**
	 * Return a string containing script tags for the body or footer of the site.
	 *
	 * @return String SCRIPT tags
	 */
	static function getSiteJs() {
		$ret = '';
		$handler = Cgn_Template::getDefaultHandler();
		if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')) {
			$templateUrl = 'https://'.Cgn_Template::url();
		} else {
			$templateUrl = 'http://'.Cgn_Template::url();
		}

		foreach ($handler->extraJs as $s) {
			if (strpos($s, '<script') === 0) {
				$ret .= $s;
			} else {
				if (strpos($s, '/') === 0 ) {
					$ret .= '<script src="'.cgn_url().$s.'" type="text/javascript"></script>'."\n";
				} else {
					$ret .= '<script src="'.$templateUrl.$s.'" type="text/javascript"></script>'."\n";
				}
			}
		}
		return $ret;
	}

	/**
	 * Return true of the passed in tab name is active
	 *
	 * @return boolean true if tab name is active
	 */
	static function selectedTab($tabName) {
		return (strpos($_SERVER['PHP_SELF'], $tabName) !== FALSE);
		return true;
	}

	/**
	 * Show the default template, register the content.main section to only show an error.
	 */
	static function showFatalError($errorCode) {

		$handler =& Cgn_Template::getDefaultHandler();
		$handler->regSectionCallback( array($handler,'doShowFatalError'));
		$handler->parseTemplate();
	}

	static function doShowFatalError() {
		header('HTTP/1.0 404 Not Found');
		$html = '<h2>File Not Found.</h2>';
		echo $html;
	}

	function parseTemplate($templateStyle = 'index') {

		$templateName = Cgn_ObjectStore::getString("config://template/default/name");
		$baseDir = Cgn_ObjectStore::getString("config://template/base/dir");

		//scope
		$t =& Cgn_ObjectStore::getArray("template://variables/");

		$req = Cgn_SystemRequest::getCurrentRequest();
		if ($req->isAjax) {
			$this->doEncodeJson($t);
			return false;
		}

		if (isset($_SESSION['_debug_template']) &&  $_SESSION['_debug_template'] != '') { 
			$systemHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");
			if ( is_object($systemHandler->currentRequest)) {
				$templateName = $_SESSION['_debug_template'];
				Cgn_ObjectStore::storeConfig("config://template/default/name",$templateName);
			}
		}

		//Get the current user into the scope of the upcoming template include.
		$u =& $req->getUser();

		$templateIncluded = FALSE;
		if ($templateStyle=='' || $templateStyle=='index') {
			if(@include( $baseDir. $templateName.'/index.html.php')) {
				$templateIncluded = TRUE;
			}
		} else {
			//try special style, if not fall back to index
			if (!@include( $baseDir. $templateName.'/'.$templateStyle.'.html.php') ) {
				//eat the error
				//failed include
				$e = Cgn_ErrorStack::pullError('php');
				//file not found
				$e = Cgn_ErrorStack::pullError('php');

				if(include( $baseDir. $templateName.'/index.html.php')) {
					$templateIncluded = TRUE;
				}
			} else {
				$templateIncluded = TRUE;
			}
		}
		if (!$templateIncluded) {
			$errors = array();
			$errors[] = 'Cannot include template.';
			echo $this->doShowMessage($errors);
		}

		//clean up session variables, this is done with the whole page here
		if (isset($_SESSION['_debug_frontend']) && @$_SESSION['_debug_frontend'] === true) { 
			$systemHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");
			//default system handler handles all front end requests
			if ( is_object($systemHandler->currentRequest)) {
				$_SESSION['_debug_frontend'] = false;
				$_SESSION['_debug_template'] = '';
			}
		}
	}


	static function assignArray($n,&$a) {
		Cgn_ObjectStore::storeValue("template://variables/".$n, $a);
	}


	static function assignNum($n,$num) {
		Cgn_ObjectStore::storeValue("template://variables/".$n, $num);
	}


	static function assignString($n,$s) {
		Cgn_ObjectStore::storeValue("template://variables/".$n, $s);
	}


	static function assignObject($n,$o) {
		Cgn_ObjectStore::storeValue("template://variables/".$n, $o);
	}


	static function assign($n,&$v) {
		Cgn_ObjectStore::storeValue("template://variables/".$n, $v);
	}

	static function cleanAll() {
		Cgn_ObjectStore::unsetArray("template://variables/");
	}



	static function &getDefaultHandler() {
		return Cgn_ObjectStore::getObject('object://defaultOutputHandler');
	}

	function regSectionCallback($callback, $sectionId='content.main') {
		$this->callbacks[$sectionId][] = $callback;
	}


	/**
	 * Try to answer the question of whenter or not this 
	 * page request has content for different sections
	 */
	function sectionHasContent($sectionId='') {
		if ($sectionId == 'content.main') { return true; }
		if ( isset($this->callbacks[$sectionId]) && count($this->callbacks[$sectionId]) ) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Wrapper for doParseTemplateSection($sectionId).
	 *
	 * This method first calls the list of registered callbacks with the section name.
	 * If any of these callbacks return data, then the callback is assumed to have 
	 * satisfied the section processing.
	 *
	 * @see Cgn_Template::regSectionCallback
	 * @param String $sectionId the name of the template section (eg: content.main)
	 */
	function parseTemplateSection($sectionId='') {
		$obj = Cgn_ObjectStore::getObject('object://defaultOutputHandler');

		//do callbacks? or regular?
		$output = '';
		if (isset($this->callbacks[$sectionId])) {
			return $this->doSectionCallbacks($sectionId);
		}


		//proceed with regular templating, no callbacks found.
		if (isset($_SESSION['_debug_frontend']) && @$_SESSION['_debug_frontend'] === true) { 
			$systemHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");
			//default system handler handles all front end requests
			if ( is_object($systemHandler->currentRequest)) {
				return $obj->debugParseTemplateSection($sectionId);
			} else {
				return $obj->doParseTemplateSection($sectionId);
			}
		} else {
			return $obj->doParseTemplateSection($sectionId);
		}
	}

	public function doSectionCallbacks($sectionId) {
		$output = '';
		foreach ($this->callbacks[$sectionId] as $cb) {
			if (is_array($cb) ) {
				if (is_object($cb[0]))
				$output .= $cb[0]->{$cb[1]}($sectionId, $this);
				else
				$output .= call_user_func($cb, $sectionId, $this);
			} else {
//				$output .= call_user_func($cb, $sectionId, $this);
			}
		}
		//if the callbacks return any content, skip regular processing
		if ( strlen($output) > 0 ) {
			echo $output;
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Default Implementation of parseTemplateSection
	 *
	 * @param string $sectionId name of the template section
	 */
	function doParseTemplateSection($sectionId='') {

		$layout = Cgn_ObjectStore::getObject('object://defaultLayoutManager');
		$layout->showLayoutSection($sectionId, $this);
		return;
	}

	/**
	 * Show a red border around template sections for debugging / designing purposes.
	 * This is the default implementation.
	 *
	 * @param string $sectionId name of the template section
	 */
	function debugParseTemplateSection($sectionId='') {
		echo '<div style="border:1px solid red;width:100%;height:12em;">'.$sectionId.'</div>';
	}

	function parseTemplateFile($filename) {
		$t = Cgn_ObjectStore::getArray("template://variables/");

		$req = Cgn_SystemRequest::getCurrentRequest();
		//pull in the current user
		$u =& $req->getUser();

		if (! @include($filename) ) {
			if (! is_array($t) ) {
				return false;
			}
			foreach ($t as $key => $val) {
				if ( is_object($val) && method_exists($val,'toHtml') ) {
					echo $val->toHtml();
				} else {
					//echo Cgn_ActiveFormatter::show($val, $key);
					//*
					if (is_array($val)) {
						echo "<pre>\n";
						print_r($val);
						echo "</pre>\n";
					} else {
						echo $val."<span style=\"clear:both;\"></span>\n";
					}
					// */
				}
			}
//			echo "can't open file $filename.\n";
		} else {
			return true;
		}
	}


	/**
	 * Send data as JSON
	 */
	function doEncodeJson(&$t) {
		echo json_encode($t);
	}


	function showMenu($name,$extras=array()) {
		include_once(CGN_LIB_PATH.'/lib_cgn_menu.php');
		$menu = new Cgn_Menu();
		if (!$menu->loadCodename($name)) {
			return false;
		}
		if (!$menu->isLoaded()) { return; }
		if (isset($menu->dataItem->show_title) && $menu->dataItem->show_title == 1) {
			$menu->showHeader = 3;
		}
		echo $menu->toHtml($extras);
//		cgn::debug($menu);
	}


	function showErrors() {
		echo Cgn_ErrorStack::showErrorBox();
		echo Cgn_ErrorStack::showWarnings();
	}

	/**
	 * Get the messages from the session and pass them to doShowMessage().
	 * Directly echo the HTML here.
	 */
	function showSessionMessages() {
		$sess = Cgn_ObjectStore::getObject('object://defaultSessionLayer');
		$msgs = $sess->get('_messages');
		$html = '';
		if (is_array($msgs) && count($msgs) > 0) {
			//split message up into individual tables based on their type
			$types = array();
			foreach ($msgs as $e) {
				$types[$e['type']][] = $e;
			}
			foreach ($types as $msg) {
				$html .= $this->doShowMessage($msg);
			}
		}
		echo $html;
	}

	/**
	 * Default implementation shows 1 error string or an array of strings as a list inside a box
	 *
	 * @param mixed @msg either 1 string or an array of strings
	 */
	function doShowMessage($msgs = '', $type = 'msg_warn') {
		if (is_array($msgs) ) {
			$errors = '';
			if (count($msgs) > 1) {
				$errors = '<ul>';
				foreach ($msgs as $e) {
					if (is_array($e) ) {
						$errors .= "<li>".$e['text']."</li>\n";
						$type = $e['type'];
					} else {
						$errors .= "<li>".$e."</li>\n";
					}
				}
				$errors .= '</ul>';
			} else {
				if (is_array($msgs[0]) ) {
					$errors .= $msgs[0]['text'];
					$type = $msgs[0]['type'];
				} else {
					$errors = $msgs[0];
				}
			}
		} else {
			$errors = $msg;
		}

		$html = '<div class="messagebox '.$type.'">
			<table width="100%" cellpadding="0" cellspacing="0"><tr><td width="60">
			</td><td>
				'.$errors.'
			</td></tr></table>
		</div>
		';

		return $html;
	}

	function showBreadCrumbs() {
		$req = Cgn_SystemRequest::getCurrentRequest();
		if ($req->isAdmin) {
			$systemHandler =& Cgn_ObjectStore::getObject("object://adminSystemHandler");
		} else {
			$systemHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");
		}

		$ticketTop = count($systemHandler->ticketDoneList)-1;
		//default system handler handles all front end requests
		if (!isset($systemHandler->ticketDoneList[$ticketTop])) {
			return FALSE;
		}
		$ticket = $systemHandler->ticketDoneList[$ticketTop];
		if (is_object($ticket->instance) && $serviceCrumbs = $ticket->instance->getBreadCrumbs()) {
			$crumbs = $serviceCrumbs;
		} else {
			return FALSE;
		}
//		Cgn::debug($ticket);
		if ($ticket->isDefault) {
			array_unshift($crumbs, cgn_homelink('Home'));
		} else {
			$m = Cgn_ObjectStore::getValue("config://default/module");
			$s = Cgn_ObjectStore::getValue("config://default/service");
			$e = Cgn_ObjectStore::getValue("config://default/event");

			array_unshift($crumbs, cgn_homelink('Home'));
			//$crumbs[] = cgn_homelink('Home');

			//$crumbs[] = cgn_applink(ucfirst($ticket->module), $ticket->module, $ticket->service, $ticket->event);
		}
		if (count($crumbs)) {
			$html = '<div class="main-content-trail">';
			$html .= implode('&nbsp;/&nbsp;', $crumbs);
			$html .= '</div>';
			echo $html;
		}
	}
}

/**
 * wrapper for static function
 */
function cgn_url($https=0) {
	//XXX UPDATE 
	//needs to handle https as well
	if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']== 'on') || $https) {
		return 'https://'.Cgn_Template::baseurl(true);
	} else {
		return 'http://'.Cgn_Template::baseurl();
	}
}


/**
 * wrapper for static function
 */
function cgn_templateurl($https=0) {
	//XXX UPDATE 
	//needs to handle https as well
	if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || $https) {
		echo 'https://'.Cgn_Template::url();
	} else {
		echo 'http://'.Cgn_Template::url();
	}
}


/**
 * Passthru all arguments to cgn_appurl but force "HTTPS" mode.
 *
 * @param $mod String the name of the module to call, defaults to 'main'
 * @param $class String the name of the service to call, defaults to 'main'
 * @param $event String the function of the service to call, defaults to 'main'
 * @param $scheme String  discards this parameter, it's always "https"

 */
function cgn_sappurl($mod='', $class='', $event='', $args=array(), $scheme='https') {
	return cgn_appurl($mod, $class, $event, $args, 'https');
}

/**
 * Return a formatted URL to a specific MSE
 *
 * @param $mod String the name of the module to call, defaults to 'main'
 * @param $class String the name of the service to call, defaults to 'main'
 * @param $event String the function of the service to call, defaults to 'main'
 * @param $scheme String  Either http or https
 */
function cgn_appurl($mod='', $class='', $event='', $args=array(), $scheme='http') {
	static $sslPort    = -1;
	static $httpPort   = -1;
	static $baseUri    = -1;
	static $useRewrite = -1;

	$getStr = '';
	if (is_array($args) && count($args) > 0) {
		foreach ($args as $k=>$v) {
			$getStr .= rawurlencode($k).'='.rawurlencode($v).'/';
		}
	}

	if ($baseUri === -1) {
		$baseUri = Cgn_ObjectStore::getString("config://template/base/uri");
	}
	$workUri = $baseUri; //copy

	$mse = $mod;
	if (strlen($class) ) {
		$mse .= '.'.$class;
	}
	if (strlen($event) ) {
		$mse .= '.'.$event;
	}
	$mse = $mse.'/';

	if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') {
		$httphost = str_replace(':'.$_SERVER['SERVER_PORT'], '', $_SERVER['HTTP_HOST']);
	}
	if ($scheme === 'https') {
		if ($sslPort == -1) {
			$sslPort = Cgn_ObjectStore::getConfig('config://template/ssl/port');
		}
		//non standard ssl port
		if ($sslPort != '443' && $sslPort != '') {
			$workUri = str_replace($httphost, $httphost .':'.$sslPort, $httphost).'/';
		} else	if ($sslPort === '') {
			//provides a way to shut off SSL for testing
			$scheme = 'http';
		}
	}

	if ($scheme === 'http') {
		if ($httpPort == -1) {
			$httpPort = Cgn_ObjectStore::getConfig('config://template/http/port');
		}
		if ($httpPort != '80' && $httpPort != '') {
			$workUri = str_replace($httphost, $httphost .':'.$httpPort, $httphost).'/';
		}
	}

	if ($useRewrite === -1) {
		$useRewrite = Cgn_ObjectStore::getString("config://template/use/rewrite");
	}
	if ($useRewrite == true) {
		return $scheme.'://'.$workUri.$mse.$getStr;
	} else {
		return $scheme.'://'.$workUri.'index.php/'.$mse.$getStr;
	}
}


/**
 * wrapper for static function
 */
function cgn_applink($link,$mod='main',$class='',$event='',$args=array(),$scheme='http') {
	return '<a href="'.cgn_appurl($mod, $class, $event, $args, $scheme).'">'.$link.'</a>';
}

function cgn_pagelink($title,$link,$args=array(),$scheme='http') {
	return '<a href="'.cgn_pageurl($title, $args, $scheme).'">'.$link.'</a>';
}

function cgn_pageurl($title,$args=array(),$scheme='http') {
	return cgn_appurl('main','page','',$args).$title;
}

function cgn_homelink($link,$scheme='http') {
	return '<a href="'.cgn_url('', '', '', '', $scheme).'">'.$link.'</a>';
}

/**
 * wrapper for static function
 */
function cgn_adminurl($mod='main',$class='',$event='',$args=array(),$scheme='https') {
	static $sslPort = -1;
	$getStr = '';
	if (is_array($args) && count($args) > 0) {
		foreach ($args as $k=>$v) {
			$getStr .= rawurlencode($k).'='.rawurlencode($v).'/';
		}
	}


	//XXX UPDATE 
	//needs to handle https as well
	$mse = $mod;
	if (strlen($class) ) {
		$mse .= '.'.$class;
	}
	if (strlen($event) ) {
		$mse .= '.'.$event;
	}
	$mse = $mse.'/';

	$baseUri = Cgn_Template::baseadminurl();

	if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') {
		$httphost = str_replace(':'.$_SERVER['SERVER_PORT'], '', $_SERVER['HTTP_HOST']);
	}

	if ($scheme === 'https') {
		if ($sslPort == -1) {
			$sslPort = Cgn_ObjectStore::getConfig('config://template/ssl/port');
		}
		if ($sslPort != '443' && $sslPort != '') {
			$baseUri = str_replace($httphost, $httphost .':'.$sslPort, $baseUri);
		} else	if ($sslPort === '') {
			//provides a way to shut off SSL for testing
			$scheme = 'http';
		}
	}

	return $scheme.'://'.$baseUri.'admin.php/'.$mse.$getStr;
}

function cgn_adminsurl($mod='',$class='',$event='',$args=array()) {
	return cgn_adminurl($mod,$class,$event,$args,'https');
}

function cgn_adminlink($text,$mod='main',$class='',$event='',$args=array()) {
	$getStr = '/';
	foreach ($args as $k=>$v) {
		$getStr .= urlencode($k).'='.urlencode($v).'/';
	}

	//XXX UPDATE 
	//needs to handle https as well
	$mse = $mod;
	if (strlen($class) ) {
		$mse .= '.'.$class;
	}
	if (strlen($event) ) {
		$mse .= '.'.$event;
	}

	//$baseUri = Cgn_Template::baseadminurl();
	$href = cgn_adminurl($mod, $class, $event, $args);
	return '<a href="'.$href.'">'.$text.'</a>';
}


/**
 * wrapper for static function
 */
function cgn_sitename() {
	echo Cgn_Template::siteName();
}

function cgn_sitetagline() {
	echo Cgn_Template::siteTagLine();
}

/**
 * wrapper for static function
 */
function cgn_pagename() {
	echo Cgn_Template::pageName();
}

/**
 * helper function for templating
 */
function cgnt($key) { 
	return Cgn_ObjectStore::getValue("template://$key");
}	

function cgn_copyrightname() {
	return Cgn_ObjectStore::getConfig('config://template/copyright/name');
}
?>
