<?php
//define('wc_inc_path',dirname(__FILE__).'/wiclear');
/*
require wc_inc_path.'/inc/classes/WikiRenderer.class.php';
require wc_inc_path.'/inc/classes/WiclearWikiRenderer.conf.php';
require wc_inc_path.'/inc/lib/url.lib.php';
require wc_inc_path.'/inc/lib/format.lib.php';
require wc_inc_path.'/inc/classes/utf8_helper.class.php';
 */

//include(dirname(__FILE__).'/../../lib/pear_wiki/Text_Wiki-1.2.0RC2/Text/Wiki.php');
include(dirname(__FILE__).'/../../lib/wiki/lib_cgn_wiki.php');
include(dirname(__FILE__).'/../../lib/dokuwiki/parser.php');
include(dirname(__FILE__).'/../../lib/dokuwiki/lexer.php');
include(dirname(__FILE__).'/../../lib/dokuwiki/handler.php');
include(dirname(__FILE__).'/../../lib/dokuwiki/renderer.php');
//include(dirname(__FILE__).'/../../lib/dokuwiki/wiki.php');
include(dirname(__FILE__).'/../../lib/dokuwiki/xhtml.php');
include(dirname(__FILE__).'/../../lib/dokuwiki/parserutils.php');


class Cgn_Service_Showoff_Wiki extends Cgn_Service {

	function Cgn_Service_Showoff_Wiki () {

	}

	function mainEvent(&$req, &$t) {
		$t['message1'] = 'You are using the Doku Wiki rendering library.';
		$t['message2'] = 'Below you should see some wiki text.';


		if ($req->postvars['wiki']) {
			$text = $req->postvars['wiki'];
		} else {
			$text = $this->getDefaultWikiText();
		}
		/*
		$wikiRendererConfig = new WiclearWikiRendererConfig();
		$wikiRenderer = new WikiRenderer($wikiRendererConfig);
    		$t['preview'] = $wikiRenderer->render($this->getDefaultWikiText());
		 */

		/*
		$striparray=array();
		$parser=new Parser();
		$parser->mOutputType=OT_WIKI;
		$parser->mOptions = new ParserOptions();
		$striptext=$parser->strip($text, $striparray, true);
		$t['preview2'] = $striptext;
		$t['preview'] = $striparray;
	//	print_r($text);exit();
			*/
		/*
            	$ret = & Text_Wiki::factory('Creole', null);
		echo $ret->parse($text);
		$t['preview'] =  $ret->render();
		 */
		/**
		$wiki = new Cgn_Wiki_Document();
		$wiki->text = $text;
		$wiki->parse();
		$t['preview'] = $wiki->toHtml();
		 */


      		$t['preview'] = p_render('xhtml',p_get_instructions($text),$info); //no caching on old revisions


		$t['form'] = $this->_loadRegForm($text);
	}


	function _loadRegForm($wikiText) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('reg');
		$f->label = 'Edit this Wiki text';
		$w = new Cgn_Form_ElementText('wiki');
		$w->value = $wikiText;
		$f->appendElement($w);
		return $f;
	}


	function getDefaultWikiText() {
		return '
== Help ==

next line down

//italics//

[[http://google.com/|link to google]]

this text should appear as a link [[http://foobar.com]]

  * This is an unordered list
  * Bullet point 1
    * Note (that is a **really good** bullet point 1)
  * Web site 3: [[http://foobar.com]]

**bold wording**  and then **more bold wording**
';
	}
}

?>
