<?php


include(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');
include(CGN_LIB_PATH.'/search/lib_cgn_search_index.php');

/**
 * Service for managing site search engines
 * @package Modules_Site 
 */
class Cgn_Service_Site_Search extends Cgn_Service_Admin {

	function __construct() { 
		$this->displayName = 'Manage Search';
	}

	/**
	 * Show a list of folders in var/search_cache/.
	 *
	 * The list of folders represent lucene search indexes
	 */
	function mainEvent(&$req, &$t) {
		$d = dir(BASE_DIR.'var/search_cache/');
		$data = array();


	$list = new Cgn_Mvc_TableModel();
		$list->data = array(
			0=> array('link 1','foobar.php'),
			1=> array('link 2','foobar.php'),
			2=> array('link 3','foobar.php')
		);

		while ($entry = $d->read()) {
			//skip private files, . and ..
			if ( strpos($entry , '.') === 0) {
				continue;
			}

			try {
				$index = new Cgn_Search_Index($entry);
				$count = $index->getDocumentCount();
				$status = 'OK';
				$index->close();
			} catch (Zend_Search_Lucene_Exception $e) {
				//set status as 'error'
				$status = 'error';
				$count = 'N/A';
				//swallow PHP error in ZF for chmod
				Cgn_ErrorStack::pullError('php');
				throw $e;
			}

			$data[] = array(
				$entry,
				$count,
				$status
			);
		}

		$list->data = $data;
		$list->headers = array('Name', 'Num. Documents', 'Status');
		$t['listPanel'] = new Cgn_Mvc_AdminTableView($list);
		$t['listPanel']->attribs['width'] = '600';
		$t['listPanel']->setColWidth(0, '300');
		$t['listPanel']->setColWidth(1, '100');
		$t['listPanel']->setColWidth(2, '100');

		$t['message'] = "This is the main event.";
	}
}
