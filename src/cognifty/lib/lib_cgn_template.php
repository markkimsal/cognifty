<?php


class Cgn_Template {


	var $templateName = 'default';
	var $templateStyle = 'index';


	function Cgn_Template() {
		$this->templateName = Cgn_ObjectStore::getString("config://template/default/name");
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


	function siteName() {
		static $siteName;
		if (!$siteName) {
			$siteName = Cgn_ObjectStore::getString("config://template/site/name");
		}
		return $siteName;
	}


	function siteTagLine() {
		static $siteTag;
		if (!$siteTag) {
			$siteTag = Cgn_ObjectStore::getString("config://template/site/tagline");
		}
		return $siteTag;
	}


	function pageName() {
		return 'Home';
	}


	function parseTemplate($templateStyle = 'index') {
		$t = Cgn_ObjectStore::getArray("template://variables/");

		$baseDir = Cgn_ObjectStore::getString("config://template/base/dir");
		if ($templateStyle=='' || $templateStyle=='index') {
			include( $baseDir. $this->templateName.'/index.html.php');
		} else {
			//try special style, if not fall back to index
			if (!include( $baseDir. $this->templateName.'/'.$templateStyle.'.html.php') ) {

				include( $baseDir. $this->templateName.'/index.html.php');
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


	function parseTemplateSection($sectionId='') {
		$obj = Cgn_ObjectStore::getObject('object://defaultOutputHandler');
		$obj->doParseTemplateSection($sectionId);
	}

	function doParseTemplateSection($sectionId='') {
//		echo "Layout engine parsing content for [$sectionId].&nbsp;  ";
		$modulePath = Cgn_ObjectStore::getString("path://default/cgn/module");

		switch($sectionId) {
			case 'content.main':
				list($module,$service,$event) = explode('.', Cgn_ObjectStore::getObject('request://mse'));
				$this->parseTemplateFile( $modulePath ."/$module/templates/$service"."_$event.html.php");
				return;
			break;

		}

		$key = str_replace('.','/',$sectionId);
		//section id did not match some basic ones, look for object store variables
		if (Cgn_ObjectStore::hasConfig("object://layout/".$key.'/name') ) {
			$x = Cgn_ObjectStore::getConfig('object://layout/'.$key.'/name');
			$obj = Cgn_ObjectStore::getObject('object://'.$x);
			$meth = Cgn_ObjectStore::getConfig('object://layout/'.$key.'/method');
			// echo '<h2>'.$sectionId.'</h2>';      SCOTTCHANGE 20070619  Didn't want to see this in NAV BAR MENU AREA
			echo '<BR>';
			echo $obj->{$meth}($sectionId);
			//Cgn_ObjectStore::debug();
			//list($module,$service,$event) = explode('.', Cgn_ObjectStore::getConfig('object://layout/'.$key));
			//$x = Cgn_ObjectStore::getConfig('object://layout/'.$key);
			//print_r($x);
			//print_r($module);
		} else {
			echo $sectionId;
			echo "lsdkjfsd";
		}
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
}

/**
 * wrapper for static function
 */
function cgn_url() {
	//XXX UPDATE 
	//needs to handle https as well
	echo 'http://'.Cgn_Template::baseurl();
}


/**
 * wrapper for static function
 */
function cgn_templateurl() {
	//XXX UPDATE 
	//needs to handle https as well
	echo 'http://'.Cgn_Template::url();
}


/**
 * wrapper for static function
 */
function cgn_appurl($mod='main',$class='',$event='',$args=array()) {
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
function cgn_applink($link,$mod='main',$class='',$event='',$args=array()) {
	return '<a href="'.cgn_appurl($mod,$class,$event,$args).'">'.$link.'</a>';
}

/**
 * wrapper for static function
 */
function cgn_adminurl($mod='main',$class='',$event='',$args=array()) {
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
	return 'http://'.$baseUri.'admin.php/'.$mse.$getStr;
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
	return '<a href="http://'.$baseUri.'admin.php/'.$mse.$getStr.'">'.$text.'</a>';
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
