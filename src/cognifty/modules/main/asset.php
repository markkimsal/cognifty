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
		$article = new Cgn_DataItem('cgn_file_publish');
		$article->andWhere('link_text', $link);
		$article->_cols = array('title', 'mime', 'cgn_file_publish_id');
		$article->load();
		if ($article->_isNew) {
			//no article found
			Cgn_ErrorStack::throwWarning('Cannot find that article.', 121);
			return false;
		}

		//ob_start('gz_handler') breaks firefox when downloading gzips
		// so we will clear out the buffer no matter what type of file is being 
		// downloaded.
		ob_end_clean();
		ob_end_clean();

		/**
		 * These two headers are only needed by IE (6?)
		 */
		header('Cache-Control: public, must-revalidate');
		header('Pragma: Public');

		$offset = 60 * 60 * 24 * 1;
		$ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
		header($ExpStr); 

		header('Content-Type: '. $article->mime);
		header('Content-Disposition: attachment;filename='.$article->title.';');
		$db = Cgn_Db_Connector::getHandle();
		$streamTicket = $db->prepareBlobStream('cgn_file_publish', 'binary', $article->cgn_file_publish_id, 5, 'cgn_file_publish_id');

		header('Content-Length: '. sprintf('%d', ($streamTicket['bytelen'])));
		while (! $streamTicket['finished'] ) {
			echo $db->nextStreamChunk($streamTicket);
			ob_flush();
		}
		exit();
	}

	function imageEvent(&$req, &$t) {
		$link = $req->getvars[0];
		$image = new Cgn_DataItem('cgn_image_publish');
		$image->andWhere('link_text', $link);
		$image->load();
		header('Content-type: '. $image->mime);
		echo $image->web_image;
		exit();
	}
}

?>
