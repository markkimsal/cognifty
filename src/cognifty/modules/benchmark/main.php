<?php


class Cgn_Service_Benchmark_Main extends Cgn_Service {

	/**
	 * Load up a number of articles and display them.
	 * Only show the first bit of the text and the author.
	 * This should be highly configurable from a "front-page"
	 * manager in the admin section.
	 */
	function mainEvent(&$sys, &$t) {
		$this->crumbs[] = "About";
		$this->presenter = 'self';
		$page = new Cgn_DataItem('cgn_web_publish');
		$pages = $page->find();
		$t['content'] ='Content';
	}

	function output(&$sys, &$t) {
		include('cognifty/modules/benchmark/templates/main.html.php');
	}
}

?>
