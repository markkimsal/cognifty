<?php

include_once(CGN_LIB_PATH.'/search/lib_cgn_search_index.php');
/**
 * Search Service
 * @package Cgn_Search
 */
class Cgn_Service_Search_Main extends Cgn_Service {

		function __construct() { }

		function mainEvent(&$req, &$t) {
			$t['message'] = "Search Results for ... " . $req->cleanHtml('q');

			try {
				$index = new Cgn_Search_Index('global');
			} catch (Zend_Search_Lucene_Exception $e) {
				$index = NULL;
				$err = Cgn_ErrorStack::pullError('php');
				$this->templateName = 'search_error';
				return false;
			}
			$query = $req->cleanString('q');
			$hits = $index->find($query);

			$idsByTable = array();
			$queries = array();
			foreach ($hits as $h) {
				$doc = $h->getDocument();
				$idsByTable[$doc->getFieldValue('table_name')][] = $doc->getFieldValue('database_id');
			}
			foreach ($idsByTable as $table=>$id) {
				$queries[] = "(select title, link_text, '".$table."' as table_name, ".$table."_id from ".$table." where ".$table."_id in (".implode(', ', $id)."))";
			}
			if ( count($queries)) {
				$unionSelect = implode(' UNION ', $queries);
				$db = Cgn_Db_Connector::getHandle();
				$db->query($unionSelect);
				while($db->nextRecord()) {
					$table = $db->record['table_name'];
					switch($table) {
						case 'cgn_web_publish':
							$db->record['url'] =  cgn_appurl('main','page').$db->record['link_text'];
							break;
						case 'cgn_article_publish':
							$db->record['url'] =  cgn_appurl('main','content').$db->record['link_text'];
							break;
						case 'cgn_blog_entry_publish':
							$db->record['url'] =  cgn_appurl('blog','entry', '', array('id'=>$db->record[$table.'_id'])).$db->record['link_text'];
							break;

					}
					$t['results'][] = $db->record;
				}
			}

			//allow caching of search results for back button
			header('Cache-Control: public');
			header('Pragma: Public');

			$offset = 60 * 60 * 1;
			$ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
			header($ExpStr); 
		}
}
