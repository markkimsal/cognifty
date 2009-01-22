<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

Cgn::loadModLibrary('Content::Cgn_Content');

class Cgn_Service_Content_Preview extends Cgn_Service_Admin {

	var $templateStyle = 'blank';

	function Cgn_Service_Content_Preview () {

	}


	function browseImagesEvent(&$req, &$t) {

		$p = $req->cleanInt('p');
		if ($p == 0 ) {
			$p = 1;
		}
		$start = (($p-1));
		$finder = new Cgn_DataItem('cgn_image_publish');
		$finder->orderBy('title');
		$finder->limit(10, $start);
		$finder->_rsltByPkey = FALSE;

//		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		$rows = $finder->findAsArray();
		foreach ($rows as $r) {
			$t['data'][] = $r;
		}

		$t['curPage'] = $p;
		$t['maxPage'] = ceil($finder->getUnlimitedCount() / 10);
		$t['urlNext'] = cgn_adminurl('content', 'preview', 'browseImages', array('p'=>$p+1));
		$t['urlPrev'] = cgn_adminurl('content', 'preview', 'browseImages', array('p'=>$p-1));
		$t['urlBase'] = cgn_adminurl('content', 'preview', 'browseImages');
	}

	function browseFilesEvent(&$req, &$t) {

		$this->templateName = 'preview_browseFiles';

		$p = $req->cleanInt('p');
		if ($p == 0 ) {
			$p = 1;
		}
		$start = (($p-1));
		$finder = new Cgn_DataItem('cgn_file_publish');
		$finder->_cols = array('title', 'link_text', 'cgn_content_id', 'title', 'caption', 'cgn_guid');
		$finder->orderBy('title');
		$finder->limit(10, $start);
		$finder->_rsltByPkey = FALSE;

		//cut up the data into table data
		$rows = $finder->findAsArray();
		foreach ($rows as $r) {
			$guid = $r['cgn_guid'];
			$str = '<div onclick="parent.$(\'#container-1 ol\').tabsClick(1);parent.$(\'#content\').focus();window.setTimeout(\'parent.insertFile(\\\''.$r['link_text'].'\\\',\\\''.$r['title'].'\\\',\\\''.$r['cgn_content_id'].'\\\');\',300);" style="cursor:pointer;float:left;text-align:left;margin-right:13px;">';

			//$str .= '<img src="'.cgn_url().'media/icons/default/document.png" align="left"/>';
			$str .= '<img src="'.cgn_appurl('webutil', 'identicon', '', array('s'=>'m', 'id'=>md5($guid))).'icon.png" 
				style="padding-right:1em;" align="left"/>';
			$str .= '<span style="font-size:130%">'.$r['title'].'</span><br/>';
			$str .= $r['caption'].'</div>';
			$t['data'][] = $str;
		}

		$t['curPage'] = $p;
		$t['maxPage'] = ceil($finder->getUnlimitedCount() / 10);
		$t['urlNext'] = cgn_adminurl('content', 'preview', 'browseFiles', array('p'=>$p+1));
		$t['urlPrev'] = cgn_adminurl('content', 'preview', 'browseFiles', array('p'=>$p-1));
		$t['urlBase'] = cgn_adminurl('content', 'preview', 'browseFiles');
	}

	function browseArticlesEvent(&$req, &$t) {

		$this->templateName = 'preview_browseArticles';
		$p = $req->cleanInt('p');
		if ($p == 0 ) {
			$p = 1;
		}
		$start = (($p-1));
		$finder = new Cgn_DataItem('cgn_article_publish');
		$finder->_cols = array('title', 'link_text', 'cgn_content_id', 'title', 'caption', 'cgn_guid');
		$finder->orderBy('title');
		$finder->limit(10, $start);
		$finder->_rsltByPkey = FALSE;

		//cut up the data into table data
		$rows = $finder->findAsArray();
		foreach ($rows as $r) {
			$guid = $r['cgn_guid'];
			$str = '<div onclick="parent.$(\'#container-1 ol\').tabsClick(1);parent.$(\'#content\').focus();window.setTimeout(\'parent.insertArticle(\\\''.$r['link_text'].'\\\',\\\''.$r['title'].'\\\',\\\''.$r['cgn_content_id'].'\\\');\',300);" style="cursor:pointer;float:left;text-align:left;margin-right:13px;">';

//			$str .= '<img src="'.cgn_url().'media/icons/default/document.png" align="left"/>';
			$str .= '<img src="'.cgn_appurl('webutil', 'identicon', '', array('s'=>'m', 'id'=>md5($guid))).'icon.png" 
				style="padding-right:1em;" align="left"/>';

			$str .= '<span style="font-size:130%">'.$r['title'].'</span><br/>';
			$str .= $r['caption'].'</div>';
			$t['data'][] = $str;
		}
		$t['curPage'] = $p;
		$t['maxPage'] = ceil($finder->getUnlimitedCount() / 10);
		$t['urlNext'] = cgn_adminurl('content', 'preview', 'browseArticles', array('p'=>$p+1));
		$t['urlPrev'] = cgn_adminurl('content', 'preview', 'browseArticles', array('p'=>$p-1));
		$t['urlBase'] = cgn_adminurl('content', 'preview', 'browseArticles');
	}

	function browsePagesEvent(&$req, &$t) {

		$this->templateName = 'preview_browsePages';

		$p = $req->cleanInt('p');
		if ($p == 0 ) {
			$p = 1;
		}
		$start = (($p-1));
		$finder = new Cgn_DataItem('cgn_web_publish');
		$finder->_cols = array('title', 'link_text', 'cgn_content_id', 'title', 'caption', 'cgn_guid');
		$finder->orderBy('title');
		$finder->limit(10, $start);
		$finder->_rsltByPkey = FALSE;


		//cut up the data into table data
		$rows = $finder->findAsArray();
		foreach ($rows as $r) {
			$guid = $r['cgn_guid'];
			$str = '<div onclick="parent.$(\'#container-1 ol\').tabsClick(1);parent.$(\'#content\').focus();window.setTimeout(\'parent.insertPage(\\\''.$r['link_text'].'\\\',\\\''.$r['title'].'\\\',\\\''.$r['cgn_content_id'].'\\\');\',300);" style="cursor:pointer;float:left;text-align:left;margin-right:13px;">';

			//$str .= '<img src="'.cgn_url().'media/icons/default/html.png" align="left"/>';
			$str .= '<img src="'.cgn_appurl('webutil', 'identicon', '', array('s'=>'m', 'id'=>md5($guid))).'icon.png" 
				style="padding-right:1em;" align="left"/>';
			$str .= '<span style="font-size:130%">'.$r['title'].'</span><br/>';
			$str .= $r['caption'].'</div>';
			$t['data'][] = $str;
		}

		$t['curPage'] = $p;
		$t['maxPage'] = ceil($finder->getUnlimitedCount() / 10);
		$t['urlNext'] = cgn_adminurl('content', 'preview', 'browsePages', array('p'=>$p+1));
		$t['urlPrev'] = cgn_adminurl('content', 'preview', 'browsePages', array('p'=>$p-1));
		$t['urlBase'] = cgn_adminurl('content', 'preview', 'browsePages');
	}

	function showImageEvent(&$req, &$t) {

		$db = Cgn_Db_Connector::getHandle();
		$contentId = $req->cleanInt('cid');
		if ($contentId > 0 ) {
			$db->query('SELECT `binary` FROM cgn_content WHERE cgn_content_id = '.$contentId);
			$db->nextRecord();
			header('Content-type: '.$db->record['mime']);
			echo $db->record['binary'];
			exit();
		}
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
		if ($content = $req->cleanMultiLine('content')) {
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
