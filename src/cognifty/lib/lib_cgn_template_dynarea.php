<?php

include("../cognifty/lib/lib_cgn_template.php");

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
		if (!$defArea->_isNew) { //loading succeeded
			$templateName = $defArea->site_template;
			$templateStyle = $defArea->template_style;


			$baseDir = Cgn_ObjectStore::getString("config://template/base/dir");
			$templateName = $defArea->site_template;
			$templateStyle = $defArea->template_style;
			Cgn_ObjectStore::storeConfig("config://template/default/name", $templateName);
		}
		parent::parseTemplate($templateStyle);
	}
}
?>
