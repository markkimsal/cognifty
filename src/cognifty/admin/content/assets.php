<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/lib/lib_cgn_mvc_table.php');


class Cgn_Service_Content_Assets extends Cgn_Service_Admin {

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
				$db->record['title'],
				$db->record['caption'],
				cgn_adminlink('view','content','view','',array('id'=>$db->record['cgn_content_id'])),
				cgn_adminlink('edit','content','edit','',array('id'=>$db->record['cgn_content_id'])),
				cgn_adminlink('delete','content','delete','',array('id'=>$db->record['cgn_content_id']))
			);
		}
		$list->headers = array('Title','Sub-Title','View','Edit','Delete');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
	}
}

?>
