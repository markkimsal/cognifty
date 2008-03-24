<?php


class Cgn_Template {


	var $templateStyle = 'index';
	var $contentTpl    = '';
	var $scriptLinks   = array();
	var $styleLinks    = array();
	var $extraJs       = array();
	var $charset       = 'UTF-8';
	var $callbacks     = array();


	function Cgn_Template() {
		//$this->templateName = Cgn_ObjectStore::getString("config://template/default/name");
	}

	function baseurl($useHttps = false) {
		static $baseUri;

		if (!$baseUri) {
			$baseUri = Cgn_ObjectStore::getString("config://template/base/uri");
		}
		return $baseUri;
	}

	function baseadminurl() {
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

	function url() {
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

	function setSiteName($n) {
		 Cgn_ObjectStore::storeConfig("config://template/site/name", $n);
	}

	function siteName() {
		static $siteName;
		if (!$siteName) {
			$siteName = Cgn_ObjectStore::getString("config://template/site/name");
		}
		return $siteName;
	}

	function setPageTitle($t) {
		 Cgn_ObjectStore::storeConfig("config://template/site/pageTitle", $t);
	}

	function getPageTitle() {
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

	function siteTagLine() {
		static $siteTag;
		if (!$siteTag) {
			$siteTag = Cgn_ObjectStore::getString("config://template/site/tagline");
		}
		return $siteTag;
	}


	/**
	 * Show the default template, register the content.main section to only show an error.
	 */
	function showFatalError($errorCode) {

		$handler =& Cgn_Template::getDefaultHandler();
		$handler->regSectionCallback( array($handler,'doShowFatalError'));
		$handler->parseTemplate();
	}

	function doShowFatalError() {
		header('HTTP/1.0 404 Not Found');
		$html = '<h2>File Not Found.</h2>';
		echo $html;
	}

	function parseTemplate($templateStyle = 'index') {
		$t = Cgn_ObjectStore::getArray("template://variables/");

		$templateName = Cgn_ObjectStore::getString("config://template/default/name");
		$baseDir = Cgn_ObjectStore::getString("config://template/base/dir");


		if (@$_SESSION['_debug_template'] != '') { 
			$systemHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");
			if ( is_object($systemHandler->currentRequest)) {
				$templateName = $_SESSION['_debug_template'];
				Cgn_ObjectStore::storeConfig("config://template/default/name",$templateName);
			}
		}

		if ($templateStyle=='' || $templateStyle=='index') {
			include( $baseDir. $templateName.'/index.html.php');
		} else {
			//try special style, if not fall back to index
			if (!@include( $baseDir. $templateName.'/'.$templateStyle.'.html.php') ) {
				//eat the error
				//failed include
				$e = Cgn_ErrorStack::pullError('php');
				//file not found
				$e = Cgn_ErrorStack::pullError('php');

				include( $baseDir. $templateName.'/index.html.php');
			}
		}

		//clean up session variables, this is done with the whole page here
		if (@$_SESSION['_debug_frontend'] === true) { 
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


	/**
	 * Try to answer the question of whenter or not this 
	 * page request has content for different sections
	 */
	function sectionHasContent($sectionId='') {
		if ($sectionId == 'content.main') { return true; }
		if (count($this->callbacks) ) {
			return true;
		} else {
			return false;
		}
	}

	function &getDefaultHandler() {
		return Cgn_ObjectStore::getObject('object://defaultOutputHandler');
	}

	function regSectionCallback($callback) {
		$this->callbacks[] = $callback;
	}

	function parseTemplateSection($sectionId='') {
		$obj = Cgn_ObjectStore::getObject('object://defaultOutputHandler');

		//do callbacks? or regular?
		$doRegular = true;
		$output = '';
		foreach ($this->callbacks as $cb) {
			if (is_array($cb) ) {
				$output .= $cb[0]->{$cb[1]}($sectionId, $this);
//				$output .= $cb[0]->{$cb[1]}call_user_func($cb, $sectionId, $this);
			} else {
//				$output .= call_user_func($cb, $sectionId, $this);
			}
			$doRegular = false;
		}
		if (!$doRegular) {
			echo $output;
			return true;
		}

		//proceed with regular templating, no callbacks found.
		if (@$_SESSION['_debug_frontend'] === true) { 
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

	/**
	 * Default Implementation of parseTemplateSection
	 *
	 * @param string $sectionId name of the template section
	 */
	function doParseTemplateSection($sectionId='') {
//		echo "Layout engine parsing content for [$sectionId].&nbsp;  ";
		$modulePath = Cgn_ObjectStore::getString("path://default/cgn/module");

		switch($sectionId) {
			case 'content.main':
				//show errors if there are any
				if (Cgn_ErrorStack::count()) {
					$terminate = false;
					$errors = array();
					$stack =& Cgn_ErrorStack::_singleton();
					for ($z=0; $z < $stack->count; ++$z) {
						if ($stack->stack[$z]->type != 'error' 
							&& $stack->stack[$z]->type != 'php'
							&& $stack->stack[$z]->type != 'sec') {
							continue;
						}
						if ($stack->stack[$z]->type == 'error' ) {
							$terminate = true;
						}
						$errors[] = $stack->stack[$z]->message;
						//TODO: do I need to pull it off the stack like this?
//						$stack->pullError();
					}
					echo $this->doShowMessage($errors);
					if ($terminate) { return true; }
				}
//		 Cgn_ErrorStack::showErrorBox();
//		 Cgn_ErrorStack::showWarnings();

				list($module,$service,$event) = explode('.', Cgn_ObjectStore::getObject('request://mse'));
				if ($this->contentTpl != '') {
					$this->parseTemplateFile( $modulePath ."/$module/templates/".$this->contentTpl.".html.php");
				} else {
					$this->parseTemplateFile( $modulePath ."/$module/templates/$service"."_$event.html.php");
				}
			break;

		}

		$key = str_replace('.','/',$sectionId);
		//section id did not match some basic ones, look for object store variables
		if (Cgn_ObjectStore::hasConfig("object://layout/".$key.'/name') ) {
			$x = Cgn_ObjectStore::getConfig('object://layout/'.$key.'/name');
			$obj = Cgn_ObjectStore::getObject('object://'.$x);
			$meth = Cgn_ObjectStore::getConfig('object://layout/'.$key.'/method');
			// echo '<h2>'.$sectionId.'</h2>';      SCOTTCHANGE 20070619  Didn't want to see this in NAV BAR MENU AREA
			//echo '<BR/>';
			echo $obj->{$meth}($sectionId);
			//Cgn_ObjectStore::debug();
			//list($module,$service,$event) = explode('.', Cgn_ObjectStore::getConfig('object://layout/'.$key));
			//$x = Cgn_ObjectStore::getConfig('object://layout/'.$key);
			//print_r($x);
			//print_r($module);
		} else {
//			echo $sectionId;
//			echo "N/A";
		}
		return true;
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
					if (is_array($val) ) {
						echo "<pre>\n";
						print_r($val);
						echo "</pre>\n";
					} else {
						echo "$val<br/>\n";
					}
					// */
				}
			}
//			echo "can't open file $filename.\n";
		} else {
			return true;
		}
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
			$html = $this->doShowMessage($msgs);
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

		//default system handler handles all front end requests
		$ticket = $systemHandler->ticketList[0];
		if (is_object($ticket->instance) && $serviceCrumbs = $ticket->instance->getBreadCrumbs()) {
			$crumbs = $serviceCrumbs;
		} else {
			return false;
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
		echo implode('&nbsp;/&nbsp;', $crumbs);
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
 * wrapper for static function
 */
function cgn_appurl($mod='main',$class='',$event='',$args=array(),$scheme='http') {
	$getStr = '/';
	foreach ($args as $k=>$v) {
		$getStr .= urlencode($k).'='.urlencode($v).'/';
	}

	//XXX UPDATE 
	//needs to handle https as well
	$baseUri = Cgn_ObjectStore::getString("config://template/base/uri");
	$mse = $mod;
	if (strlen($class) ) {
		$mse .= '.'.$class;
	}
	if (strlen($event) ) {
		$mse .= '.'.$event;
	}

	if (Cgn_ObjectStore::getString("config://template/use/rewrite") == true) {
		return 'http://'.$baseUri.$mse.$getStr;
	} else {
		return 'http://'.$baseUri.'index.php/'.$mse.$getStr;
	}
}

/**
 * wrapper for static function
 */
function cgn_applink($link,$mod='main',$class='',$event='',$args=array(),$scheme='http') {
	return '<a href="'.cgn_appurl($mod,$class,$event,$args).'">'.$link.'</a>';
}

function cgn_pagelink($title,$link,$args=array(),$scheme='http') {
	return '<a href="'.cgn_pageurl($title,$args).'">'.$link.'</a>';
}

function cgn_pageurl($title,$args=array(),$scheme='http') {
	return cgn_appurl('main','page','',$args).$title;
}

function cgn_homelink($link,$scheme='http') {
	return '<a href="'.cgn_url().'">'.$link.'</a>';
}

/**
 * wrapper for static function
 */
function cgn_adminurl($mod='main',$class='',$event='',$args=array(),$scheme='https') {
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

	$baseUri = Cgn_Template::baseadminurl();
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

	$baseUri = Cgn_Template::baseadminurl();
	return '<a href="https://'.$baseUri.'admin.php/'.$mse.$getStr.'">'.$text.'</a>';
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
