<?php


class Cgn_Service_Content_Articles extends Cgn_Service_Admin {

	function Cgn_Service_Content_Articles() {
	}

	function mainEvent(&$sys, &$t) {
		Cgn_Template::assignString('Message1','This is the main event!');
	}
}

?>
