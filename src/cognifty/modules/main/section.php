<?php


class Cgn_Service_Main_Section extends Cgn_Service {

	var $sectionTitle = '';

	function Cgn_Service_Main_Section () {

	}

	function getBreadCrumbs() {
		if ($this->sectionTitle == '') {
			return array('All Articles');
		} else {
			return array(cgn_applink('All Articles', 'main', 'section'), $this->sectionTitle );
		}

	}

	/**
	 * Load up a number of articles and display them.
	 * Only show the first bit of the text and the author.
	 * This should be highly configurable from a "front-page"
	 * manager in the admin section.
	 */
	function mainEvent(&$req, &$t) {
		if (isset($req->getvars[0])) {
			$this->sectionTitle = trim($req->getvars[0]);
		}

		//if no section, show all sections with the browse event
		if ($this->sectionTitle == '') {
			$template = Cgn_Template::getDefaultHandler();
			$template->contentTpl = 'section_browse';
			return $this->browseEvent($req, $t);
		}

		$sectionObj = new Cgn_DataItem('cgn_article_section');
		$sectionObj->andWhere('link_text',$this->sectionTitle);
		$sectionObj->load();

		$articleList = $this->fetchArticlesInSection($this->sectionTitle);

		$sectionList = array();
		$db = Cgn_Db_Connector::getHandle();
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
			$t['content'][] = substr(strip_tags($article->content,'<br><em><i><strong><b><p>'),0,300)."<br/><br/>";
			unset($article->content);
			$t['articles'][] = $article;
		}
		$t['sectionList'] = $sectionList;
		$t['sectionTitle'] = $sectionObj->title;
	}


	/**
	 * Show all sections
	 */
	function browseEvent(&$req, &$t) {

		$sectionObj = new Cgn_DataItem('cgn_article_section');
		$sectionObj->orderBy('link_text');
		$t['sections'] = $sectionObj->find();
		$t['articles'] = array();
		foreach ($t['sections'] as  $_sec) {
			$t['articles'][$_sec->cgn_article_section_id] = $this->fetchArticlesInSection($_sec->link_text,3);
		}
	}

	/**
	 * Load data items from a specific section title
	 *
	 * @return Array  list of data items
	 */
	function fetchArticlesInSection($secTitle, $limit=0) {
		//default the limit to 7
		if ($limit == 0) {
			$limit = 7;
		}

		$articleList = array();

		//database query
		$db = Cgn_Db_Connector::getHandle();
		$db->query('
			SELECT A.* FROM
			cgn_article_publish AS A
			LEFT JOIN
			cgn_article_section_link AS B
			ON A.cgn_article_publish_id = B.cgn_article_publish_id
			LEFT JOIN
			cgn_article_section AS C
			ON B.cgn_article_section_id = C.cgn_article_section_id
			WHERE
			C.link_text = "'.$secTitle.'"
			ORDER BY published_on DESC
			LIMIT '.$limit);

		$articleList = array();
		while ($db->nextRecord()) {
			$x = new Cgn_DataItem('cgn_article_publish');
			$x->row2Obj($db->record);
			$articleList[$x->cgn_article_publish_id] = $x;
		}
		return $articleList;
	}


}
