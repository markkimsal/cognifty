<?php


class Cgn_Service_Main_Sub extends Cgn_Service {

	function Cgn_Service_Main_Sub () {

	}


	function mainEvent(&$sys, &$t) {
		Cgn_Template::assignString('Message2','Round 2 of the main event!');
	}
}

?>
