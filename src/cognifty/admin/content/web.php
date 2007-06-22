<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');

include_once('../cognifty/admin/content/content_table.php');

class Cgn_Service_Content_Web extends Cgn_Service_Admin {

	function Cgn_Service_Content_Web() {
	}

	function mainEvent(&$sys, &$t) {

		$t['message1'] = '<h3>Web Pages</h3>';
	
		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_web_publish');

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

		$t['menuPanel'] = new Cgn_Mvc_ContentTableView($list);
	}
}


?>
