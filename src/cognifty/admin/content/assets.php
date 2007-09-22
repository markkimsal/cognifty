<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');


class Cgn_Service_Content_Assets extends Cgn_Service_AdminCrud {

	function Cgn_Service_Content_Assets() {
	}

	function mainEvent(&$sys, &$t) {

		$t['message1'] = '<h3>Site Assets</h3>';
	
		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_file_publish ORDER BY title');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			$list->data[] = array(
				cgn_adminlink($db->record['title'],'content','view','',array('id'=>$db->record['cgn_content_id'])),
				$db->record['description'],
//				cgn_adminlink('edit','content','edit','',array('id'=>$db->record['cgn_content_id'])),
				cgn_adminlink('delete','content','assets','del',array('cgn_file_publish_id'=>$db->record['cgn_file_publish_id'], 'table'=>'cgn_file_publish'))
			);
		}
		// __FIXME__ add in edit capabilities
		$list->headers = array('Title','Description','Delete');
		$list->headers = array('Title','Description','Edit','Delete');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
	}
}

?>
