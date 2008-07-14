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

			$index = new Cgn_Search_Index('global');
			$query = $req->cleanString('q');
			$hits = $index->find($query);

			$idsByTable = array();
			$queries = array();
			foreach ($hits as $h) {
				$doc = $h->getDocument();
				$idsByTable[$doc->getFieldValue('table_name')][] = $doc->getFieldValue('database_id');
			}
			foreach ($idsByTable as $table=>$id) {
				$queries[] = "(select title, link_text from ".$table." where ".$table."_id in (".implode(', ', $id)."))";
			}
			$unionSelect = implode(' UNION ', $queries);
			$db = Cgn_Db_Connector::getHandle();
			$db->query($unionSelect);
			while($db->nextRecord()) {
				$db->record['url'] =  cgn_appurl('main','page').$db->record['link_text'];
				$t['results'][] = $db->record;
			}

			//allow caching of search results for back button
			header('Cache-Control: public');
			header('Pragma: Public');

			$offset = 60 * 60 * 1;
			$ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
			header($ExpStr); 
		}
}
