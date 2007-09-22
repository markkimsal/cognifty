<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

include_once(CGN_SYS_PATH.'/app-lib/lib_cgn_content.php');


class Cgn_Service_Content_Web extends Cgn_Service_AdminCrud {

	function Cgn_Service_Content_Web() {
	}

	function mainEvent(&$req, &$t) {

		$t['message1'] = '<span style="font-size:120%;">Web Pages</span>';

		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','web','new'),"New HTML Page");
		$t['toolbar']->addButton($btn1);
		$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','web','new', array('mime'=>'wiki')),"New Wiki Page");
		$t['toolbar']->addButton($btn2);

	
		$db = Cgn_Db_Connector::getHandle();

		$db->query('SELECT A.title, A.cgn_content_id, A.published_on, B.cgn_web_publish_id
				FROM cgn_content AS A
				LEFT JOIN cgn_web_publish AS B
					ON A.cgn_content_id = B.cgn_content_id
				WHERE sub_type = "web" 
			   	ORDER BY title');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			if ($db->record['published_on']) {
				$published = 'yes';
			} else {
				$published = 'no';
			}

			if ($db->record['cgn_web_publish_id'] ) {
				$delLink = cgn_adminlink('unpublish','content','web','del',array('cgn_web_publish_id'=>$db->record['cgn_web_publish_id'], 'table'=>'cgn_web_publish'));
			} else {
				$delLink = cgn_adminlink('delete','content','web','del',array('cgn_content_id'=>$db->record['cgn_content_id'], 'table'=>'cgn_content'));
			}
			$list->data[] = array(
				cgn_adminlink($db->record['title'],'content','view','',array('id'=>$db->record['cgn_content_id'])),
				$published,
				cgn_adminlink('edit','content','edit','',array('id'=>$db->record['cgn_content_id'])),
				$delLink
			);
		}
		$list->headers = array('Title','Published','Edit','Delete');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
	}

	/**
	 * Override this event so that we can unset the published_on date
	 * in the content table.
	 */
	function delEvent(&$req, &$t) {
		$table = $req->cleanString('table');
		$id    = $req->cleanInt($table.'_id');
		if ($table != 'cgn_web_publish') {
			return parent::delEvent($req,$t);
		}

		//this is removing a web publish record, basically an "unpublish" event
		$web = new Cgn_WebPage($id);
		$contentId = $web->getContentId();
		$content = new Cgn_Content($contentId);
		$content->dataItem->published_on = 0;
		$content->save();

		$table = $req->cleanString('table');
		return parent::delEvent($req,$t);
	}

	/**
	 * Create a new web record, a new content record, join them,
	 *  then forward to content editing.
	 */
	function newEvent(&$req, &$t) {
		$webPage = Cgn_Content_WebPage::createNew('New Page');

		$mime = $req->cleanString('mime');
		if ($mime == 'wiki') {
			$webPage->setWiki();
		}

		$newid = $webPage->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','edit','',array('id'=>$newid));
	}
}

?>
