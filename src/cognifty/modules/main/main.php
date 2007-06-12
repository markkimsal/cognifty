<?php


class Cgn_Service_Main_Main extends Cgn_Service {

	function Cgn_Service_Main_Main () {

	}


	/**
	 * Attempt to load up a published article and show it
	 */
	function mainEvent(&$sys, &$t) {
		Cgn_Template::assignString('Message1','This is the main event!');
		$article = new Cgn_DataItem('cgn_article_publish');
		$article->load(1);
		$t['article'] = $article;
		if (strstr($article->mime, 'wiki') ) {
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
		Cgn_Template::assignString('Message2','This is the about page!');
	}
}

?>
