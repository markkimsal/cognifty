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
		$article = new Cgn_DataItem('cgn_article_publish');
		$article->andWhere('link_text', $link);

		$article->load();
		if ($article->_isNew) {
			//no article found
			Cgn_ErrorStack::throwWarning('Cannot find that article.', 121);
			return false;
		}
		$page = new Cgn_DataItem('cgn_article_page');
		$page->andWhere('cgn_article_publish_id',$article->cgn_article_publish_id);
		$page->sort('cgn_article_page_id','ASC');
		$nextPages = $page->find();

		if (is_array($nextPages) && count($nextPages) > 0 ) {
			$t['hasPages'] = true;
			foreach($nextPages as $idx => $articlePage) {
				$t['nextPages'][] = $articlePage->title;
			}
		}

		$t['article'] = $article;
		$t['caption'] = $article->caption;
		$t['title'] = $article->title;
		$t['content'] = $article->content;

		Cgn_Template::setPageTitle($article->title);

		//if we're on another page, use that page's content
		$currentPage = $req->cleanInt('p');
		//start counting from 1
		if ($currentPage == 0) { $currentPage = 1; }

		if ($currentPage > 1) {
			//find the right page based on page number
			$x = 0;
			foreach ($nextPages as $articlePage) {
				$x++;
				if ($x == $currentPage-1) {
					$t['content'] =  $articlePage->content;
					$t['title'] =  $articlePage->title;
					$t['caption'] =  '';
				}
			}
		}
		//put currentPageIdx and nextPageIdx into the template
		$t['currentPageIdx'] = $currentPage;
		$t['nextPageIdx'] = -1; //no next page

		if ($t['currentPageIdx'] <= count($nextPages)) {
			$t['nextPageIdx'] = $currentPage+1;
		}

		$sectionList = array();
		$db = Cgn_Db_Connector::getHandle();
		$db->query("SELECT A.*, B.cgn_article_publish_id
			FROM cgn_article_section AS A
			LEFT JOIN cgn_article_section_link AS B
			ON A.cgn_article_section_id = B.cgn_article_section_id
			WHERE B.cgn_article_publish_id = ".$article->cgn_article_publish_id);
		while ($db->nextRecord()) {
			$sectionList[$db->record['link_text']] = $db->record['title'];
		}
		$t['sectionList'] = $sectionList;

		
		/*
		if ($article->mime ==  'text/wiki') {
			define('DOKU_BASE', cgn_appurl('main','content','image'));
			define('DOKU_CONF', dirname(__FILE__).'/../../lib/dokuwiki/ ');

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
		*/
	}

	function imageEvent(&$req, &$t) {
		$link = $req->getvars[0];
		$image = new Cgn_DataItem('cgn_image_publish');
		$image->andWhere('link_text', $link);
		$image->load();
		/*
		 * These two headers are only needed by IE (6?)
		 */
		header('Cache-Control: public, must-revalidate');
		header('Pragma: Public');
		//cache for 2 hours
		$offset = 60 * 60 * 2;
		$ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
		header($ExpStr);

		header('Content-type: '. $image->mime);
		if (isset($t['preferThumb']) &&
			strlen($image->thm_image)) {
			echo $image->thm_image;
		} 
		elseif ( strlen($image->web_image)) {
			echo $image->web_image;
		} else {
			echo $image->org_image;
		}

		exit();
	}


	function thumbEvent(&$req, &$t) {
		$t['preferThumb'] = true;
		$this->imageEvent($req,$t);
	}

	function aboutEvent(&$sys, &$t) {
		$t['Message2'] = 'This is the about page!';
	}
}

?>
