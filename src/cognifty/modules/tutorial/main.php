<?php


class Cgn_Service_Tutorial_Main extends Cgn_Service {

	function Cgn_Service_Tutorial_Main () {

	}


	function mainEvent(&$sys, &$t) {
	//	Cgn_Template::assignString('Message1','This is the main event!');
	}

	function pageEvent(&$sys, &$t) {
		//secure the input
		$filename = basename($sys->getvars['p']);
		//get our location
		$modDir = Cgn_ObjectStore::getConfig('path://config/cgn/module');
		$t['contents'] = file_get_contents($modDir.'/tutorial/tut/'.$filename.'.html');
	}
}

?>
