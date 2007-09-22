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

	function baseurl() {
		static $baseUri;
		static $baseDir;
		if (!$baseDir) {
			$baseDir = Cgn_ObjectStore::getString("config://template/base/dir");
		}
		if (!$baseUri) {
			$baseUri = Cgn_ObjectStore::getString("config://template/base/uri");
		}
		return $baseUri;
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


	function parseTemplate($templateStyle = 'index') {
		$t = Cgn_ObjectStore::getArray("template://variables/");

		$templateName = Cgn_ObjectStore::getString("config://template/default/name");
		$baseDir = Cgn_ObjectStore::getString("config://template/base/dir");


		if ($_SESSION['_debug_template'] != '') { 
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
		if ($_SESSION['_debug_frontend'] === true) { 
			$systemHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");
			//default system handler handles all front end requests
			if ( is_object($systemHandler->currentRequest)) {
				$_SESSION['_debug_frontend'] = false;
				$_SESSION['_debug_template'] = '';
			}
		}
	}


	function assignArray($n,&$a) {
		Cgn_ObjectStore::storeValue("template://variables/".$n, $a);
	}


	function assignNum($n,$num) {
		Cgn_ObjectStore::storeValue("template://variables/".$n, $num);
	}


	function assignString($n,$s) {
		Cgn_ObjectStore::storeValue("template://variables/".$n, $s);
	}


	function assignObject($n,$o) {
		Cgn_ObjectStore::storeValue("template://variables/".$n, $o);
	}


	function assign($n,&$v) {
		Cgn_ObjectStore::storeValue("template://variables/".$n, $v);
	}


	function sectionHasContent($sectionId='') {
		return true;
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
				$output .= call_user_func($cb, $sectionId, $this);
			} else {
				$output .= call_user_func($cb, $sectionId, $this);
			}
			$doRegular = false;
		}
		if (!$doRegular) {
			echo $output;
			return true;
		}

		//proceed with regular templating, no callbacks found.
		if ($_SESSION['_debug_frontend'] === true) { 
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

	function debugParseTemplateSection($sectionId='') {
		echo '<div style="border:1px solid red;width:100%;height:12em;">'.$sectionId.'</div>';
	}

	function doParseTemplateSection($sectionId='') {
//		echo "Layout engine parsing content for [$sectionId].&nbsp;  ";
		$modulePath = Cgn_ObjectStore::getString("path://default/cgn/module");

		switch($sectionId) {
			case 'content.main':
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
			//echo $obj->{$meth}($sectionId);
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
					if (is_array($val) ) {
						echo "<pre>\n";
						print_r($val);
						echo "</pre>\n";
					} else {
						echo "$val<br/>\n";
					}
				}
			}
//			echo "can't open file $filename.\n";
		} else {
			return true;
		}
	}


	function showMenu($name,$extras=array()) {
		include_once('../cognifty/lib/lib_cgn_menu.php');
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

	function showMessages() {
		$sess = Cgn_ObjectStore::getObject('object://defaultSessionLayer');
		$msgs = $sess->get('_messages');
		if (is_array($msgs) ) {
			if (count($msgs) > 1) {
				$errors = '<ul>';
				foreach ($errorList as $e) {
					$errors .= "<li>".$e."</li>\n";
				}
				$errors .= '</ul>';
			} else {
				$errors = $msgs[0];
			}

			$html .= '<div class="messagebox msg_info">
				<table width="100%" cellpadding="0" cellspacing="0"><tr><td width="60">
				</td><td>
					'.$errors.'
				</td></tr></table>
			</div>
			';

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
	if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']== 'On') || $https) {
		return 'https://'.Cgn_Template::baseurl();
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
	if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'On') || $https) {
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
	$baseUri = Cgn_ObjectStore::getString("config://template/base/uri");
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
	$baseUri = Cgn_ObjectStore::getString("config://template/base/uri");
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
?>
