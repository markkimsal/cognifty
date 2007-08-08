<?php


class Cgn_Service_Main_Asset extends Cgn_Service {

	function Cgn_Service_Main_Asset () {

	}


	/**
	 * Load up a number of articles and display them.
	 * Only show the first bit of the text and the author.
	 * This should be highly configurable from a "front-page"
	 * manager in the admin section.
	 */
	function mainEvent(&$req, &$t) {
		$link = $req->getvars[0];
		// __ FIXME __ clean the link
		$link = trim(addslashes($link));
		$article = new Cgn_DataItem('cgn_file_publish');
		$article->andWhere('link_text', $link);
		$article->load();
		if ($article->_isNew) {
			//no article found
			Cgn_ErrorStack::throwWarning('Cannot find that article.', 121);
			return false;
		}
		header('Content-type: '. $article->mime);
		header('Content-disposition: attachment;filename='.$article->title.';');
		header('Content-size: '. strlen($article->binary));
		echo $article->binary;
		exit();
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
}

?>
