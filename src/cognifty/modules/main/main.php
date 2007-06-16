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

		define('DOKU_BASE', cgn_appurl('main','content','image'));
		define('DOKU_CONF', dirname(__FILE__).'/../../lib/dokuwiki/ ');
		foreach ($articleList as $article) {
			$t['articles'][] = $article;
			if (strstr($article->mime, 'wiki') ) {
				include_once(dirname(__FILE__).'/../../lib/dokuwiki/parser.php');
				include_once(dirname(__FILE__).'/../../lib/dokuwiki/lexer.php');
				include_once(dirname(__FILE__).'/../../lib/dokuwiki/handler.php');
				include_once(dirname(__FILE__).'/../../lib/dokuwiki/renderer.php');
				include_once(dirname(__FILE__).'/../../lib/dokuwiki/xhtml.php');
				include_once(dirname(__FILE__).'/../../lib/dokuwiki/parserutils.php');
				$t['content'][] = p_render('xhtml',p_get_instructions($article->content),$info);
			} else {
				$t['content'][] = '<p>'.str_replace("\n", '</p><p>',$article->content).'</p>';
			}
		}
	}

	function aboutEvent(&$sys, &$t) {
		$t['Message2'] = 'This is the about page!';
	}
}

?>
