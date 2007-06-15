<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');

class Cgn_Service_Content_Articles extends Cgn_Service_Admin {

	function Cgn_Service_Content_Articles() {
	}

	function mainEvent(&$sys, &$t) {

		$t['message1'] = '<h1>Articles</h1>';
	
		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_article_publish');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			$list->data[] = array(
				$db->record['title'],
				$db->record['caption'],
				$db->record['type'],
				$db->record['sub_type'],
				cgn_adminlink('view','content','view','',array('id'=>$db->record['cgn_content_id'])),
				cgn_adminlink('edit','content','edit','',array('id'=>$db->record['cgn_content_id']))
			);
		}
		$list->headers = array('Title','Sub-Title','Type','Used as','View','Edit');
//		$list->columns = array('title','caption','content');

		/*
		$list->data = array(
			0=> array('link 1','foobar.php'),
			1=> array('link 2','foobar.php'),
			2=> array('link 3','foobar.php')
		);
		 */

//		$t['listPanel'] = new Cgn_ListView($list);
//		Cgn_Template::assignObject('listPanel',$t['listPanel']);

//		$t['menuPanel'] = new Cgn_Menu('Sample Menu',$list);
		$t['menuPanel'] = new Cgn_Mvc_TableView($list);
		$t['menuPanel']->style['width'] = 'auto';
		$t['menuPanel']->style['border'] = '1px solid black';

		$t['link'] = '<a href="'.cgn_adminurl('content','main','addArticle').'">Add</a>';
	}
}


?>
