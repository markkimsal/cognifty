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
		$article->_cols = array('title', 'mime', 'cgn_file_publish_id');
		$article->load();
		if ($article->_isNew) {
			//no article found
			Cgn_ErrorStack::throwWarning('Cannot find that article.', 121);
			return false;
		}
		/**
		 * These two headers are only needed by IE (6?)
		 */
		header('Cache-Control: public, must-revalidate');
		header('Pragma: Public');

		$offset = 60 * 60 * 24 * 1;
   		$ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
   		header($ExpStr); 

		header('Content-type: '. $article->mime);
		header('Content-disposition: attachment;filename='.$article->title.';');
		$db = Cgn_Db_Connector::getHandle();
		$streamTicket = $db->prepareBlobStream('cgn_file_publish', 'binary', $article->cgn_file_publish_id, 5, 'cgn_file_publish_id');

		header('Content-size: '. $streamTicket['bitlen']);
ob_flush();
ob_end_flush();
		while ($chunk = $db->nextStreamChunk($streamTicket) ) {
			echo $chunk;
		}
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
