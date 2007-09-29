<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/lib/lib_cgn_mvc_table.php');

include_once('../cognifty/app-lib/lib_cgn_content.php');

class Cgn_Service_Content_Preview extends Cgn_Service_Admin {

	var $templateStyle = 'blank';

	function Cgn_Service_Content_Preview () {

	}


	function browseImagesEvent(&$req, &$t) {

		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_image_publish ORDER BY title');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {

			$str = '<div onclick="parent.insertImage(\''.$db->record['link_text'].'\',\''.$db->record['cgn_content_id'].'\');" style="cursor:pointer;float:left;text-align:center;margin-right:13px;">';
			$str .= '<img height="60" src="'.cgn_adminurl('content','preview','showImage',array('id'=>$db->record['cgn_image_publish_id'])).'" style="cursor:pointer;float:left;text-align:center;margin-right:13px;">';
			$str .= $db->record['title'].'</div>';
			$t['data'][] = $str;
		}
	}

	function browseFilesEvent(&$req, &$t) {

		$this->templateName = 'preview_browseArticles';
		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_file_publish ORDER BY title');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {

			$str = '<div onclick="parent.insertFile(\''.$db->record['link_text'].'\',\''.$db->record['title'].'\',\''.$db->record['cgn_content_id'].'\');" style="cursor:pointer;float:left;text-align:center;margin-right:13px;">';
			$str .= '<img src="'.cgn_url().'icons/default/document.png" align="left"/>';
			$str .= $db->record['title'].'</div>';
			$t['data'][] = $str;
		}
	}

	function browseArticlesEvent(&$req, &$t) {

		$this->templateName = 'preview_browseArticles';
		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_article_publish ORDER BY title');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {

			$str = '<div onclick="parent.insertArticle(\''.$db->record['link_text'].'\',\''.$db->record['title'].'\',\''.$db->record['cgn_content_id'].'\');" style="cursor:pointer;float:left;text-align:center;margin-right:13px;">';
			$str .= '<img src="'.cgn_url().'icons/default/document.png" align="left"/>';
			$str .= $db->record['title'].'</div>';
			$t['data'][] = $str;
		}
	}

	function browsePagesEvent(&$req, &$t) {

		$this->templateName = 'preview_browsePages';
		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_web_publish ORDER BY title');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {

			$str = '<div onclick="parent.insertPage(\''.$db->record['link_text'].'\',\''.$db->record['title'].'\',\''.$db->record['cgn_content_id'].'\');" style="cursor:pointer;float:left;text-align:center;margin-right:13px;">';
			$str .= '<img src="'.cgn_url().'icons/default/html.png" align="left"/>';
			$str .= $db->record['title'].'</div>';
			$t['data'][] = $str;
		}
	}

	function showImageEvent(&$req, &$t) {

		$db = Cgn_Db_Connector::getHandle();
		$db->query('select thm_image,mime from cgn_image_publish where cgn_image_publish_id = '.$req->cleanInt('id'));
		$db->nextRecord();
		if (strlen($db->record['thm_image']) < 1) {
			$db->query('select org_image,mime from cgn_image_publish where cgn_image_publish_id = '.$req->cleanInt('id'));
			$db->nextRecord();
			header('Content-type: '.$db->record['mime']);
			echo $db->record['org_image'];
			exit();
		}
		header('Content-type: '.$db->record['mime']);
		echo $db->record['thm_image'];
		exit();
	}

	/**
	 * Show a rendered preview of content.
	 * use the passed in ID, or the POSTed "content"
	 */
	function showEvent(&$req, &$t) {
		$mime = $req->cleanString('m');
		$dl = $req->cleanInt('dl');
		$content = '';
		if (isset($req->postvars['content'])) {
			$content = $req->cleanString('content');

			if ($mime == 'wiki' || $mime == 'text/wiki') {
				Cgn_Preview_InitWiki();
				$t['content'] = p_render('xhtml',p_get_instructions($content),$info);
			} else {
				$t['content'] = $content;
			}

		} else {
			//use the passed in ID
			$id = $req->cleanInt('id');
			$content = new Cgn_Content($id);
			$mime = $content->dataItem->mime;
			if ($content->isFile() && $content->usedAs('image')) {
				// __ FIXME __ use real mime type
				header('Content-type: '.$content->dataItem->mime);
				header('Content-length: '.strlen($content->dataItem->binary));
				echo $content->dataItem->binary;
				exit();
			} else if ($content->isFile()) {

				if ( $dl < 1 ) {
					$t['content'] = cgn_adminlink('Download this file.',
						'content','preview','show',array('id'=>$id,'dl'=>1));
				} else {
					header('Content-type: application/octet-stream');
					header('Content-length: '.strlen($content->dataItem->binary));
					echo $content->dataItem->binary;
					exit();
				}
			} else if (!$content->isFile()) {
				$article= new Cgn_Content($id);
				$content = $article->dataItem->content;

				if ($mime == 'wiki' || $mime == 'text/wiki') {
					Cgn_Preview_InitWiki();
					$t['content'] = p_render('xhtml',p_get_instructions($content),$info);
				} else {
					$t['content'] = $content;
				}

			}
		}
		//switch to self presenter so we can use the front-end template
		$this->presenter = 'self';
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
