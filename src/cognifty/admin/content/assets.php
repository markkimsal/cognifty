<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

include_once(CGN_SYS_PATH.'/app-lib/lib_cgn_content.php');



class Cgn_Service_Content_Assets extends Cgn_Service_AdminCrud {

	function Cgn_Service_Content_Assets() {
		$this->displayName = 'Assets';
	}

	function mainEvent(&$sys, &$t) {


		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','upload'),"New File");
		$t['toolbar']->addButton($btn1);
		// GENERIC EXAMPLE OF HOW TO ADD ANOTHER BUTTON
		// $btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','web','new', array('mime'=>'wiki')),"New Wiki Page");
		// $t['toolbar']->addButton($btn2);

	
		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT A.title, A.cgn_content_id, A.version, A.published_on, B.cgn_file_publish_id, B.cgn_content_version
				FROM cgn_content AS A
				LEFT JOIN cgn_file_publish AS B
					ON A.cgn_content_id = B.cgn_content_id
				WHERE sub_type = "file" 
			   	ORDER BY title');


		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {

			if ($db->record['published_on']) {
				$status = '<img src="'.cgn_url().
				'/media/icons/default/bool_yes_24.png">';
				if ($db->record['version']==$db->record['cgn_content_version']) {
					$status = '<img src="'.cgn_url().
					'/media/icons/default/bool_yes_24.png">';
				} else {
					$status = '<img src="'.cgn_url().
					'/media/icons/default/caution_24.png">';
				}

			} else {
				$status = '';
			}

			$fileDescription = $db->record['description'];

			if ($db->record['cgn_file_publish_id'] ) {
				$delLink = cgn_adminlink('unpublish','content','assets','del',array('cgn_file_publish_id'=>$db->record['cgn_file_publish_id'], 'table'=>'cgn_file_publish'));
			} else {
				$delLink = cgn_adminlink('delete','content','assets','del',array('cgn_content_id'=>$db->record['cgn_content_id'], 'table'=>'cgn_content'));
			}

			$list->data[] = array(
				cgn_adminlink($db->record['title'],'content','view','',array('id'=>$db->record['cgn_content_id'])),
				$status,
				$fileDescription,
				$delLink
			);

		}
		// __FIXME__ add in edit capabilities
		// $list->headers = array('Title','Description','Delete');
		$list->headers = array('Title','Status','Description','Delete');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
	}

	/**
	 * Override this event so that we can unset the published_on date
	 * in the content table.
	 */
	function delEvent(&$req, &$t) {
		$table = $req->cleanString('table');
		$id    = $req->cleanInt($table.'_id');
		if ($table != 'cgn_file_publish') {
			return parent::delEvent($req,$t);
		}

		//this is removing a asset publish record, basically an "unpublish" event
		$asset = new Cgn_Asset($id);
		$contentId = $asset->getContentId();
		$content = new Cgn_Content($contentId);
		$content->dataItem->published_on = 0;
		$content->save();

		$table = $req->cleanString('table');
		return parent::delEvent($req,$t);
	}

}

?>
