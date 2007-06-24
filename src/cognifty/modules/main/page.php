<?php


class Cgn_Service_Main_Page extends Cgn_Service {

	function Cgn_Service_Main_Page () {

	}


	/**
	 * Load up a number of pages and display them.
	 */
	function mainEvent(&$req, &$t) {
		$link = $req->getvars[0];
		// __ FIXME __ clean the link
		$link = trim(addslashes($link));
		$web = new Cgn_DataItem('cgn_web_publish');
		$web->andWhere('link_text', $link);
		$web->load();

		$t['web'] = $web;
		$t['caption'] = $web->caption;
		$t['title'] = $web->title;
		$t['content'] = $web->content;
	}

	function imageEvent(&$req, &$t) {
		$link = $req->getvars[0];
		// __ FIXME __ clean the link
		$link = trim(addslashes($link));
		$image = new Cgn_DataItem('cgn_image_publish');
		$image->andWhere('link_text', $link);
		$image->load();
		header('Content-type: '. $image->mime);
		echo $image->web_image;
		exit();
	}

	function aboutEvent(&$sys, &$t) {
		$t['Message2'] = 'This is the about page!';
	}
}

?>
