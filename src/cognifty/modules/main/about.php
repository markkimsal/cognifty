<?php


class Cgn_Service_Main_About extends Cgn_Service {

	var $crumbs = array();

	function Cgn_Service_Main_About () {

	}

	function getBreadCrumbs() {
		return $this->crumbs;
	}

	/**
	 * Load up a number of articles and display them.
	 * Only show the first bit of the text and the author.
	 * This should be highly configurable from a "front-page"
	 * manager in the admin section.
	 */
	function mainEvent(&$sys, &$t) {
		$this->crumbs[] = "About";
	}
}

?>
