<?php

Cgn::loadModLibrary('Content::Cgn_Content');

class Cgn_Service_Main_Main extends Cgn_Service {

	var $crumbs = NULL;

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

		if ($this->_findHomePage($t)) {
			return TRUE;
		}

		$articleList = $this->loadLatestArticles($t);
		Cgn_ErrorStack::pullError('php');


		$blogList = $this->_findBlogPosts($t);
		//var_dump($blogList);
		Cgn_ErrorStack::pullError('php');

		//can't even find articles, use the welcome page.
		if ( count ($articleList) < 1 && count($blogList) < 1) {
			$myTemplate =& Cgn_ObjectStore::getObject("object://defaultOutputHandler");
			$myTemplate->contentTpl = 'main_welcome';
		}
	}

	/**
	 * Load 5 of the latest cgn_article_publish entries.
	 */
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
			if (strlen($article->description)) {
				$t['content'][] = $article->description;
			} else {
				$t['content'][] = $article->content;
			}

			unset($article->content);
			$t['articles'][] = $article;
		}
		$t['sectionList'] = $sectionList;
		return $articleList;
	}


	/**
	 * Handle the 'content.main' template section as well as
	 * any specific dynamic page sections.
	 */
	function templateSection($name, &$templateHandler) {
		if ($name == 'content.main') {
			$t =& Cgn_ObjectStore::getArray("template://variables/");
			$this->loadLatestArticles($t);
			$templateHandler->contentTpl = 'main_main';
//			var_dump($this->loadLatestArticles($t));
			$templateHandler->doParseTemplateSection($name);
			return;
		}
		//any other call to this function would have been 
		//registered with $template->regSectionCallback($name)
		//which is done by inspecting the page sections.  
		//So $name must be a page section if it is not 'content.main'
		return $this->pageObj->getSectionContent($name);
	}


	public function _findHomePage(&$t) {
		$web = new Cgn_DataItem('cgn_web_publish');
		$web->andWhere('is_home', 1);
		$web->load();
		Cgn_ErrorStack::pullError('php');
		if ( $web->_isNew) {
			return FALSE;
		}
		$this->pageObj = new Cgn_WebPage($web->cgn_web_publish_id);

		$t['web'] = $web;
		$t['caption'] = $web->caption;
		$t['title'] = $web->title;
		$t['content'] = $web->content;

		$myTemplate =& Cgn_Template::getDefaultHandler();
		$myTemplate->contentTpl = 'page_main';
		if ($this->pageObj->isPortal()) {
			$myTemplate->regSectionCallback( array($this, 'templateSection') );
			//register each page section under the "templateSection" callback
			$sections = $this->pageObj->getSectionList();
			foreach ($sections as $_sect) 
				$myTemplate->regSectionCallback( array($this, 'templateSection'), $_sect);
		} else {
			$sections = $this->pageObj->getSectionList();
			foreach ($sections as $_sect) {
				$rslt = $this->emit('content_page_section_'.$_sect);
				if ($rslt !== NULL && $rslt !== FALSE) {
				$t['content'] = str_replace(
					'<!-- BEGIN: '.$_sect.' -->', 
					$this->_makeDummyBuyNow().' <!-- BEGIN: '.$_sect.' -->',
					$this->pageObj->dataItem->content);
				}
			}
		}
		return TRUE;
	}


	/**
	 * Load blog posts from the default blog
	 */
	public function _findBlogPosts(&$t) {
		//no page found, load up some articles
		$loader = new Cgn_DataItem('cgn_blog_entry_publish');
		$loader->limit(5);
		$loader->sort('posted_on','DESC');
		$articleList = $loader->find();

		$sectionList = array();
		$db = Cgn_Db_Connector::getHandle();
		$t['content'] = array();
		foreach ($articleList as $article) {
			/*
			$db->query("SELECT A.*, B.cgn_article_publish_id
				FROM cgn_article_section AS A
				LEFT JOIN cgn_article_section_link AS B
				ON A.cgn_article_section_id = B.cgn_article_section_id
				WHERE B.cgn_article_publish_id = ".$article->cgn_article_publish_id);
			while ($db->nextRecord()) {
				$sectionList[$db->record['cgn_article_publish_id']][$db->record['link_text']] = $db->record['title'];
			}
			 */

			$published = explode(' ',date('F d Y',$article->posted_on));
			$published['month'] = $published[0];
			$published['date'] = $published[1];
			$published['year'] = $published[2];


			//just show previews of the content
			if (strlen($article->description)) {
				$t['content'][] = $article->description;
			} else {
				$t['content'][] = $article->content;
			}
			$t['publishedDateList'][] = '
	<div class="blog_entry_date">
		<span style="font-size:90%;">
		'.$published['month'].'
		</span>
		<br/>
		<span style="font-size:150%;">
		'.$published['date'].'
		</span>
	</div> ';

			$t['readMoreList'][] = cgn_appurl('blog', 'entry', '', array('id'=>$article->cgn_blog_entry_publish_id)).$article->link_text;
			unset($article->content);
			$t['articles'][] = $article;
		}
		$t['sectionList'] = $sectionList;
		return $articleList;
	}
}
?>
