<?php


class Cgn_Service_Main_Main extends Cgn_Service {

	function Cgn_Service_Main_Main () {

	}


	function mainEvent(&$sys, &$t) {
		Cgn_Template::assignString('Message1','This is the main event!');
		$mySession =& Cgn_ObjectStore::getObject("object://defaultSessionLayer");
		$mySession->set('time',rand(0,50));
	}

	function aboutEvent(&$sys, &$t) {
		Cgn_Template::assignString('Message2','This is the about page!');
	}
}

?>
