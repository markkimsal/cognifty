<?php


class Cgn_Service_Main_Content extends Cgn_Service {

	function Cgn_Service_Main_Content () {

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
		$article = new Cgn_DataItem('cgn_article_publish');
		$article->andWhere('link_text', $link);
		$article->load();
		$t['article'] = $article;
		if ($article->mime ==  'text/wiki') {
			include_once(dirname(__FILE__).'/../../lib/wiki/lib_cgn_wiki.php');
			include_once(dirname(__FILE__).'/../../lib/dokuwiki/parser.php');
			include_once(dirname(__FILE__).'/../../lib/dokuwiki/lexer.php');
			include_once(dirname(__FILE__).'/../../lib/dokuwiki/handler.php');
			include_once(dirname(__FILE__).'/../../lib/dokuwiki/renderer.php');
			include_once(dirname(__FILE__).'/../../lib/dokuwiki/xhtml.php');
			include_once(dirname(__FILE__).'/../../lib/dokuwiki/parserutils.php');
			$t['content'] = p_render('xhtml',p_get_instructions($article->content),$info);
		} else {
			$t['content'] = '<p>'.str_replace("\n", '</p><p>',$article->content).'</p>';
		}
	}

	function aboutEvent(&$sys, &$t) {
		$t['Message2'] = 'This is the about page!';
	}
}

?>
