<?php


class Cgn_Service_Main_Section extends Cgn_Service {

	function Cgn_Service_Main_Section () {

	}


	/**
	 * Load up a number of articles and display them.
	 * Only show the first bit of the text and the author.
	 * This should be highly configurable from a "front-page"
	 * manager in the admin section.
	 */
	function mainEvent(&$req, &$t) {
		$section = trim($req->getvars[0]);
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
			C.title = "'.$section.'"
			ORDER BY published_on DESC
			LIMIT 7');

		$articleList = array();
		while ($db->nextRecord()) {
			$x = new Cgn_DataItem('cgn_article_publish');
			$x->row2Obj($db->record);
			$articleList[$x->cgn_article_publish_id] = $x;
		}

		$loader = new Cgn_DataItem('cgn_article_publish');
		$loader->limit(5);
		$loader->sort('published_on','DESC');
		$articleList = $loader->find();

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
			$t['content'][] = substr(strip_tags($article->content,'<br><em><i><strong><b><p>'),0,300).'<br><br>';
			unset($article->content);
			$t['articles'][] = $article;
		}
		$t['sectionList'] = $sectionList;
	}
}

?>
