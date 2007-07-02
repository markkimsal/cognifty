<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/lib/lib_cgn_mvc_table.php');


class Cgn_Service_Content_Web extends Cgn_Service_Admin {

	function Cgn_Service_Content_Web() {
	}

	function mainEvent(&$sys, &$t) {

		$t['message1'] = '<h3>Web Pages</h3>';
	
		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_web_publish ORDER BY title');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			$list->data[] = array(
				cgn_adminlink($db->record['title'],'content','view','',array('id'=>$db->record['cgn_content_id'])),
				cgn_adminlink('edit','content','edit','',array('id'=>$db->record['cgn_content_id'])),
				cgn_adminlink('delete','content','delete','',array('id'=>$db->record['cgn_content_id']))
			);
		}
		$list->headers = array('Title','Edit','Delete');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
	}
}


?>
