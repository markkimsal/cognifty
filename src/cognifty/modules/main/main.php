<?php


class Cgn_Service_Main_Main extends Cgn_Service {

	function Cgn_Service_Main_Main () {

	}


	/**
	 * Load up a number of articles and display them.
	 * Only show the first bit of the text and the author.
	 * This should be highly configurable from a "front-page"
	 * manager in the admin section.
	 */
	function mainEvent(&$sys, &$t) {
		$loader = new Cgn_DataItem('cgn_article_publish');
		$articleList = $loader->find('cgn_article_publish_id < 5');

		foreach ($articleList as $article) {
			//just show previews of the content
			$t['content'][] = substr(strip_tags($article->content,'<br><em><i><strong><b><p>'),0,300);
			unset($article->content);
			$t['articles'][] = $article;
		}
	}

	function aboutEvent(&$sys, &$t) {
		$t['Message2'] = 'This is the about page!';
	}
}

?>
