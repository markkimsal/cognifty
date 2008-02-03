<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

include_once(CGN_SYS_PATH.'/app-lib/lib_cgn_content.php');

class Cgn_Service_Content_Image extends Cgn_Service_AdminCrud {

	function Cgn_Service_Content_Image() {
	}

	function mainEvent(&$sys, &$t) {

		$t['message1'] = '<h3>Images</h3>';
	
		$db = Cgn_Db_Connector::getHandle();
//		$db->query('select * from cgn_image_publish ORDER BY title');
		$db->query('SELECT A.title, A.cgn_content_id, A.published_on, B.cgn_image_publish_id
				FROM cgn_content AS A
				LEFT JOIN cgn_image_publish AS B
					ON A.cgn_content_id = B.cgn_content_id
				WHERE sub_type = "image" 
			   	ORDER BY title');


		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			if ($db->record['published_on']) {
				$published = '<img src="'.cgn_url().'/icons/default/bool_yes_24.png">';
				$preview = '<img src="'.cgn_adminurl('content','preview','showImage',array('id'=>$db->record['cgn_image_publish_id'])).'"/>'; 
			} else {
				$published = '';
				$preview = '';
			}

			$list->data[] = array(
				cgn_adminlink($db->record['title'],'content','view','',array('id'=>$db->record['cgn_content_id'])),
				$published,
				$preview,
//				cgn_adminlink('edit','content','edit','',array('id'=>$db->record['cgn_content_id'])),
				cgn_adminlink('delete','content','image','del',array('cgn_image_publish_id'=>$db->record['cgn_image_publish_id'], 'table'=>'cgn_image_publish'))
			);
		}
		// __FIXME__ add in editing capabilities.
		$list->headers = array('Title','Published','Preview','Delete');
		//$list->headers = array('Title','Preview','Edit','Delete');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
	}


	/**
	 * Override this event so that we can unset the published_on date
	 * in the content table.
	 */
	function delEvent(&$req, &$t) {
		$table = $req->cleanString('table');
		$id    = $req->cleanInt($table.'_id');
		if ($table != 'cgn_image_publish') {
			return parent::delEvent($req,$t);
		}

		//this is removing a image publish record, basically an "unpublish" event
		$image = new Cgn_Image($id);
		$contentId = $image->getContentId();
		$content = new Cgn_Content($contentId);
		$content->dataItem->published_on = 0;
		$content->save();

		$table = $req->cleanString('table');
		return parent::delEvent($req,$t);
	}
}

?>
