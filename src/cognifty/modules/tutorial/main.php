<?php


class Cgn_Service_Tutorial_Main extends Cgn_Service {

	function Cgn_Service_Tutorial_Main () {

	}


	function mainEvent(&$req, &$t) {
	//	Cgn_Template::assignString('Message1','This is the main event!');
	}

	function pageEvent(&$req, &$t) {
		//secure the input
		if (isset($req->getvars['p'])) {
			$filename = basename(@$req->getvars['p']);
		} else {
			$filename = basename(@$req->getvars[0]);
		}
		//fix old style URLs
		$aliases = 
			array ('concept1'=> 'Framework_Concepts.html'
			);

		if (in_array($filename, array_keys($aliases))) {
			$filename = $aliases[$filename];
		}

		if (substr($filename,-5) === '.html') {
			$filename = substr($filename,0, -5);
		} else {
			//sub-directory simulation
			$filename = basename(@$req->getvars[1]);
		}

		//get our location
		$modDir = Cgn_ObjectStore::getConfig('path://default/cgn/module');
		if (file_exists($modDir.'/tutorial/tut/'.$filename.'.html')) {
			$t['contents'] = @file_get_contents($modDir.'/tutorial/tut/'.$filename.'.html');
		} else if (file_exists($modDir.'/tutorial/tut/'.$filename.'.wiki')) {
			$text = @file_get_contents($modDir.'/tutorial/tut/'.$filename.'.wiki');

			$this->wikiDeps();
      		$t['contents'] = p_render('xhtml',p_get_instructions($text),$info); //no caching on old revisions
		}

		if(!$t['contents']) {
			$t['contents'] = 'Sorry, file not found.';
		}
		$t['css'] = '
			<style type="text/css"> .code { background-color:#EEE; border:1px dashed silver;}
		/* syntax highlighting code */
.code .br0  { color: #66cc66; }
.code .co1  { color: #808080; font-style: italic; }
.code .co2  { color: #808080; font-style: italic; }
.code .co3  { color: #808080; }
.code .coMULTI  { color: #808080; font-style: italic; }
.code .es0  { color: #000099; font-weight: bold; }
.code .kw1  { color: #b1b100; }
.code .kw2  { color: #000000; font-weight: bold; }
.code .kw3  { color: #000066; }
.code .kw4  { color: #993333; }
.code .kw5  { color: #0000ff; }
.code .me1  { color: #006600; }
.code .me2  { color: #006600; }
.code .nu0  { color: #cc66cc; }
.code .re0  { color: #0000ff; }
.code .re1  { color: #0000ff; }
.code .re2  { color: #0000ff; }
.code .re3  { color:#ff3333; font-weight:bold; }
.code .re4  { color: #009999; }
.code .st0  { color: #ff0000; }
.code .sy0  { color: #66cc66; }

		
		</style>';
	}

	function wikiDeps() {
		include(dirname(__FILE__).'/../../lib/wiki/lib_cgn_wiki.php');
		include(dirname(__FILE__).'/../../lib/dokuwiki/parser.php');
		include(dirname(__FILE__).'/../../lib/dokuwiki/lexer.php');
		include(dirname(__FILE__).'/../../lib/dokuwiki/handler.php');
		include(dirname(__FILE__).'/../../lib/dokuwiki/renderer.php');
		//include(dirname(__FILE__).'/../../lib/dokuwiki/wiki.php');
		include(dirname(__FILE__).'/../../lib/dokuwiki/xhtml.php');
		include(dirname(__FILE__).'/../../lib/dokuwiki/parserutils.php');
	}


}

?>
