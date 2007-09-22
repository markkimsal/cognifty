<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/html_widgets/lib_cgn_toolbar.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/lib/lib_cgn_mvc_table.php');

include_once('../cognifty/app-lib/lib_cgn_content.php');


class Cgn_Service_Content_Web extends Cgn_Service_AdminCrud {

	function Cgn_Service_Content_Web() {
	}

	function mainEvent(&$req, &$t) {

		$t['message1'] = '<span style="font-size:120%;">Web Pages</span>';

		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','web','new'),"New Page");
		$t['toolbar']->addButton($btn1);


	
		$db = Cgn_Db_Connector::getHandle();
		/*
		$db->query('(select 0, title, cgn_content_id, "yes" AS published from cgn_web_publish) 
				UNION
				(select B.cgn_content_id, cgn_content.title, cgn_content.cgn_content_id, "no" AS published 
					FROM cgn_content 
					LEFT JOIN cgn_web_publish AS B ON cgn_content.cgn_content_id = B.cgn_Content_id
					WHERE sub_type = "web" AND B.cgn_content_id IS NULL )
			   	ORDER BY title');
		 */

		$db->query('SELECT title, cgn_content_id, published_on
				FROM cgn_content
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

			$list->data[] = array(
				cgn_adminlink($db->record['title'],'content','view','',array('id'=>$db->record['cgn_content_id'])),
				$published,
				cgn_adminlink('edit','content','edit','',array('id'=>$db->record['cgn_content_id'])),
				cgn_adminlink('delete','content','web','del',array('cgn_web_publish_id'=>$db->record['cgn_web_publish_id'], 'table'=>'cgn_web_publish'))
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

		return parent::delEvent($req,$t);
	}

	/**
	 * Create a new web record, a new content record, join them,
	 *  then forward to content editing.
	 */
	function newEvent(&$req, &$t) {
		$webPage = Cgn_Content_WebPage::createNew('New Page');

		$newid = $webPage->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','edit','',array('id'=>$newid));
	}
}

?>
