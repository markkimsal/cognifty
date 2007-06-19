<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');

include_once('../cognifty/app-lib/lib_cgn_content.php');

class Cgn_Service_Content_Preview extends Cgn_Service_Admin {

	var $templateStyle = 'blank';
	var $presenter = 'self';

	function Cgn_Service_Content_Preview () {

	}


	function imagesEvent(&$req, &$t) {

		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_image_publish');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {

//<a onclick="insertTags('[[',']]','Article Title');return false" href="#">link to article</a>
$t['data'][] = '<div onclick="parent.insertTags(\'{{img:'.$db->record['link_text'].'\',\'}}\',\'\');" style="float:left;text-align:center;margin-right:13px;"><img height="60" src="'.cgn_adminurl('content','preview','showImage',array('id'=>$db->record['cgn_image_publish_id'])).'"/><br/>'.$db->record['title'].'</div>';
		}
	}



	function articlesEvent(&$req, &$t) {

		$this->templateName = 'preview_images';
		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_article_publish');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {

//<a onclick="insertTags('[[',']]','Article Title');return false" href="#">link to article</a>
$t['data'][] = '<div onclick="parent.insertTags(\'[['.$db->record['link_text'].'|\',\']]\',\''.$db->record['title'].'\');" style="float:left;text-align:center;margin-right:13px;">'.$db->record['title'].'</div>';
		}
	}

	function showImageEvent(&$req, &$t) {

		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_image_publish where cgn_image_publish_id = '.$req->cleanInt('id'));
		$db->nextRecord();
		echo $db->record['binary'];
		exit();
	}

	/**
	 * Show a rendered preview of content.
	 * use the passed in ID, or the POSTed "content"
	 */
	function showEvent(&$req, &$t) {
		$mime = $req->cleanString('m');
		$content = '';
		if (isset($req->postvars['content'])) {
			$content = $req->cleanString('content');
		} else {
			//use the passed in ID
			$id = $req->cleanInt('id');
			$content = new Cgn_Content($id);
			if ($content->isFile() && $content->usedAs('image')) {
				// __ FIXME __ use real mime type
				header('Content-type: image/jpeg');
				header('Content-length: '.strlen($content->dataItem->binary));
				echo $content->dataItem->binary;
				exit();
			} else if (!$content->isFile()) {
				Cgn_Preview_InitWiki();
				$article= new Cgn_Content($id);
				$content = $article->dataItem->content;
			}
		}
		if ($mime == 'wiki') {
			echo p_render('xhtml',p_get_instructions($content),$info);
		} else {
			$t['content'] = $content;
		}
	}

	/**
	 * Use the default front end template to show
	 */
	function output(&$req,&$t) {
		$myTemplate =& Cgn_ObjectStore::getObject("object://defaultOutputHandler");
		$myTemplate->parseTemplate('index');
	}
}

function Cgn_Preview_InitWiki() {

	define('DOKU_BASE', cgn_appurl('main','content','image'));
	define('DOKU_CONF', dirname(__FILE__).'/../../lib/dokuwiki/ ');

	include_once(dirname(__FILE__).'/../../lib/wiki/lib_cgn_wiki.php');
	include_once(dirname(__FILE__).'/../../lib/dokuwiki/parser.php');
	include_once(dirname(__FILE__).'/../../lib/dokuwiki/lexer.php');
	include_once(dirname(__FILE__).'/../../lib/dokuwiki/handler.php');
	include_once(dirname(__FILE__).'/../../lib/dokuwiki/renderer.php');
	include_once(dirname(__FILE__).'/../../lib/dokuwiki/xhtml.php');
	include_once(dirname(__FILE__).'/../../lib/dokuwiki/parserutils.php');
}

?>
