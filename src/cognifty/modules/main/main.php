<?php

include_once(CGN_SYS_PATH.'/app-lib/lib_cgn_content.php');

class Cgn_Service_Main_Main extends Cgn_Service {

	var $crumbs = NULL;

	function Cgn_Service_Main_Main () {
//		$handler =& Cgn_Template::getDefaultHandler();
//		$handler->regSectionCallback( array($this, 'templateSection') );
	}

	/**
	 * Return an array to be placed into the bread crumb trail.
	 *
	 * @return 	Array 	list of strings.
	 */
	function getBreadCrumbs() {
		return array();
	}


	/**
	 * Load up a number of articles and display them.
	 * Only show the first bit of the text and the author.
	 * This should be highly configurable from a "front-page"
	 * manager in the admin section.
	 */
	function mainEvent(&$sys, &$t) {
		//try to use the "home" style, "home.html.php"
		$this->templateStyle = 'home';

		//try to find a page that "is_home"
		// if no page found, show last 5 articles

		$web = new Cgn_DataItem('cgn_web_publish');
		$web->andWhere('is_home', 1);
		$web->load();
		Cgn_ErrorStack::pullError('php');
		if (! $web->_isNew) {
			$this->pageObj = new Cgn_WebPage($web->cgn_web_publish_id);

			$t['web'] = $web;
			$t['caption'] = $web->caption;
			$t['title'] = $web->title;
			$t['content'] = $web->content;

			$myTemplate =& Cgn_ObjectStore::getObject("object://defaultOutputHandler");
			$myTemplate->contentTpl = 'page_main';
			if ($this->pageObj->isPortal()) {
				$myTemplate =& Cgn_Template::getDefaultHandler();
				$myTemplate->regSectionCallback( array($this, 'templateSection') );
			}

			return true;
		}

		$articleList = $this->loadLatestArticles($t);
		Cgn_ErrorStack::pullError('php');
		//can't even find articles, use the welcome page.
		if ( count ($articleList) < 1) {
			$myTemplate =& Cgn_ObjectStore::getObject("object://defaultOutputHandler");
			$myTemplate->contentTpl = 'main_welcome';
		}
	}

	function loadLatestArticles(&$t) {
		//no page found, load up some articles
		$loader = new Cgn_DataItem('cgn_article_publish');
		$loader->limit(5);
		$loader->sort('published_on','DESC');
		$articleList = $loader->find();

		$sectionList = array();
		$db = Cgn_Db_Connector::getHandle();
		$t['content'] = array();
		foreach ($articleList as $article) {
			$db->query("SELECT A.*, B.cgn_article_publish_id
				FROM cgn_article_section AS A
				LEFT JOIN cgn_article_section_link AS B
				ON A.cgn_article_section_id = B.cgn_article_section_id
				WHERE B.cgn_article_publish_id = ".$article->cgn_article_publish_id);
			while ($db->nextRecord()) {
				$sectionList[$db->record['cgn_article_publish_id']][$db->record['link_text']] = $db->record['title'];
			}
			//just show previews of the content
//			$t['content'][] = substr(strip_tags($article->content,'<br><em><i><strong><b><p>'),0,300).'<br><br>';
			$t['content'][] = substr(strip_tags($article->content),0,1000);
//			$t['content'][] = $article->content;

			unset($article->content);
			$t['articles'][] = $article;
		}
		$t['sectionList'] = $sectionList;
		return $articleList;
	}


	function templateSection($name, &$templateHandler) {
		if ($name == 'content.side') {
			$templateHandler->doParseTemplateSection($name);
		}
		if ($name == 'content.top') {
			return $this->pageObj->getSectionContent($name);
		}

		if ($name == 'content.main') {
			$t =& Cgn_ObjectStore::getArray("template://variables/");
			$this->loadLatestArticles($t);
			$templateHandler->contentTpl = 'main_main';
//			var_dump($this->loadLatestArticles($t));
			$templateHandler->doParseTemplateSection($name);
		}

		/*
		if ( $this->pageObj ) { return $this->pageObj->getSectionContent($name); }

		if ($name == 'content.main') {
			$templateHandler->doParseTemplateSection($name);
		}
		 */

	}
}
?>
