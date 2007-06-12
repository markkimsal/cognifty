<?php


class Cgn_Service_Main_Main extends Cgn_Service {

	function Cgn_Service_Main_Main () {

	}


	/**
	 * Attempt to load up a published article and show it
	 */
	function mainEvent(&$sys, &$t) {
		Cgn_Template::assignString('Message1','This is the main event!');
		$article = new Cgn_DataItem('cgn_content_publish');
		$article->_pkey = 'cgn_content_publish_id';
		$article->load(1);
		$t['article'] = $article;
	}

	function aboutEvent(&$sys, &$t) {
		Cgn_Template::assignString('Message2','This is the about page!');
	}
}

?>
