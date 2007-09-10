<?php

class Cgn_Template_DynArea extends Cgn_Template {


	var $templateStyle = 'index';
	var $scriptLinks   = array();
	var $styleLinks    = array();
	var $extraJs       = array();
	var $charset       = 'UTF-8';


	function Cgn_Template_DynArea() {
	}


	/**
	 * Inspect the database to find a match for the current
	 * request object and use the area settings for that request.
	 */
	function parseTemplate($templateStyle = 'index') {

		//if inside admin area, exit
		$systemHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");
		if ( !is_object($systemHandler->currentRequest)) {
			parent::parseTemplate($templateStyle);
			return;
		}

		$loader = new Cgn_DataItem('cgn_site_area');
		$loader->andWhere('is_default',1);
		$loader->_rsltByPkey = false;
		$areas = $loader->find();
		$defArea = $areas[0];
		if ( !is_object($defArea) ) {
			$defArea = new Cgn_DataItem('cgn_site_area');
			$defArea->load(1);
		}

		$t = Cgn_ObjectStore::getArray("template://variables/");

		$baseDir = Cgn_ObjectStore::getString("config://template/base/dir");
		$templateName = $defArea->site_template;
		$templateStyle = $defArea->template_style;

		Cgn_ObjectStore::storeConfig("config://template/default/name", $templateName);


		if ($_SESSION['_debug_template'] != '') { 
			if ( is_object($systemHandler->currentRequest)) {
				$templateName = $_SESSION['_debug_template'];
				Cgn_ObjectStore::storeConfig("config://template/default/name",$templateName);
			}
		}

		if (!include( $baseDir. $templateName.'/'.$templateStyle.'.html.php') ) {
			include( $baseDir. $templateName.'/index.html.php');
		}

		//clean up session variables, this is done with the whole page here
		if ($_SESSION['_debug_frontend'] === true) { 
			//default system handler handles all front end requests
			if ( is_object($systemHandler->currentRequest)) {
				$_SESSION['_debug_frontend'] = false;
				$_SESSION['_debug_template'] = '';
			}
		}
	}


}
?>
