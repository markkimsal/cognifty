<?php


class Cgn_Service_Tutorial_Cheatsheet extends Cgn_Service {

	function Cgn_Service_Tutorial_Cheatsheet () {

	}


	function mainEvent(&$req, &$t) {
		Cgn_Template::setPageTitle("Live Cheatsheet");
		Cgn_Template::setSiteTagLine("Live cheatsheet: copy and paste common cognifty framework functions.");
	}
}
