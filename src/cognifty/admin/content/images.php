<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');


class Cgn_Service_Content_Images extends Cgn_Service_AdminCrud {

	function Cgn_Service_Content_Images() {
	}

	function mainEvent(&$sys, &$t) {

		$t['message1'] = '<h3>Images</h3>';
	
		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_image_publish ORDER BY title');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			$list->data[] = array(
				cgn_adminlink($db->record['title'],'content','view','',array('id'=>$db->record['cgn_content_id'])),
				'<img src="'.cgn_adminurl('content','preview','showImage',array('id'=>$db->record['cgn_image_publish_id'])).'"/>',
//				cgn_adminlink('edit','content','edit','',array('id'=>$db->record['cgn_content_id'])),
				cgn_adminlink('delete','content','images','del',array('cgn_image_publish_id'=>$db->record['cgn_image_publish_id'], 'table'=>'cgn_image_publish'))
			);
		}
		// __FIXME__ add in editing capabilities.
		$list->headers = array('Title','Preview','Delete');
		//$list->headers = array('Title','Preview','Edit','Delete');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
	}
}

?>
