<?php


class Cgn_Template {


	var $templateName = 'default';
	var $templateStyle = 'index';


	function Cgn_Template() {
		$this->templateName = Cgn_ObjectStore::getString("config://templates/default/name");
	}


	function url() {
		static $baseUri;
		static $templateName;
		static $baseDir;
		if (!$baseDir) {
			$baseDir = Cgn_ObjectStore::getString("config://templates/base/dir");
		}
		if (!$baseUri) {
			$baseUri = Cgn_ObjectStore::getString("config://templates/base/uri");
		}
		if (!$templateName) {
			$templateName = Cgn_ObjectStore::getString("config://templates/default/name");
		}
		return $baseUri.$baseDir.$templateName.'/';
	}


	function siteName() {
		static $siteName;
		if (!$siteName) {
			$siteName = Cgn_ObjectStore::getString("config://templates/site/name");
		}
		return $siteName;
	}


	function pageName() {
		return 'Home';
	}


	function parseTemplate() {
		$t = Cgn_ObjectStore::getArray("template://variables/");

		$baseDir = Cgn_ObjectStore::getString("config://templates/base/dir");
		include( $baseDir. $this->templateName.'/index.html.php');
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
		$modulePath = Cgn_ObjectStore::getString("config://cgn/path/module");

		switch($sectionId) {

			case 'content.main':
				list($module,$service,$event) = explode('.', Cgn_ObjectStore::getObject('request://mse'));
				$this->parseTemplateFile( $modulePath ."/$module/templates/$service"."_$event.html.php");
			break;

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
	$baseUri = Cgn_ObjectStore::getString("config://templates/base/uri");
	$mse = $mod;
	if (strlen($class) ) {
		$mse .= '.'.$class;
	}
	if (strlen($event) ) {
		$mse .= '.'.$event;
	}
	if (Cgn_ObjectStore::getString("config://templates/use/rewrite") == true) {
		return 'http://'.$baseUri.$mse.$getStr;
	} else {
		return 'http://'.$baseUri.'index.php/'.$mse.$getStr;
	}
}


/**
 * wrapper for static function
 */
function cgn_adminurl($mod='main',$class='',$event='') {
	//XXX UPDATE 
	//needs to handle https as well
	$baseUri = Cgn_ObjectStore::getString("config://templates/base/uri");
	$mse = $mod;
	if (strlen($class) ) {
		$mse .= '.'.$class;
	}
	if (strlen($event) ) {
		$mse .= '.'.$event;
	}
	return 'http://'.$baseUri.'admin.php/'.$mse.'/';
}


/**
 * wrapper for static function
 */
function cgn_sitename() {
	echo Cgn_Template::siteName();
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
	return Cgn_ObjectStore::getValue("templates://$key");
}	
?>
