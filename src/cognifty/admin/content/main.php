<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');

include_once('../cognifty/lib/form/lib_cgn_form.php');
include_once('../cognifty/admin/content/wiki_form.php');

include_once('../cognifty/admin/content/content_table.php');

class Cgn_Service_Content_Main extends Cgn_Service_Admin {

	function Cgn_Service_Content_Main () {

	}


	function mainEvent(&$req, &$t) {
		$t['message1'] = '<h1>Content</h1>';

		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT * from cgn_content 
			WHERE published_on < edited_on');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			$list->data[] = array(
				cgn_adminlink(
				   $db->record['title'],
				   'content','view','',array('id'=>$db->record['cgn_content_id'])),
				$db->record['caption'],
				$db->record['type'],
				$db->record['sub_type'],
				cgn_adminlink('Edit','content','edit','',array('id'=>$db->record['cgn_content_id'])),
			);
		}
		$list->headers = array('Title','Sub-Title','Type','Used as','Actions');
//		$list->columns = array('title','caption','content');

//		$t['menuPanel'] = new Cgn_Menu('Sample Menu',$list);
		$t['form'] = new Cgn_Mvc_ContentTableView($list);
	}


	/*
	function addEvent(&$req, &$t) {
//		Cgn_Template::assignString('Message1','This is the main event!');

		$mime = $req->cleanString('m');
		$t['form'] = $this->_loadContentForm(array('mime'=>$mime));

		if ($mime == 'wiki') {
			$t['form']->layout = new Cgn_Form_WikiLayout();
			$t['mime'] = 'wiki';
		} else {
			$t['mime'] = 'html';
		}
	}
	 */

	/*

	function saveEvent(&$req, &$t) {
		$content = new Cgn_DataItem('cgn_content');
		$content->_pkey = 'cgn_content_id';
		$content->content = $req->cleanString('content');
		$content->title = $req->cleanString('title');
		$content->caption = $req->cleanString('caption');
		$content->type = 'text';
		$content->cgn_guid =  cgn_uuid();
		$content->version = 1;
		//save mime
		$mime = $req->cleanString('mime');
		if ($mime == 'html') {
			$content->mime = 'html';
		} else if ($mime == 'wiki') {
			$content->mime = 'wiki';
		}

		$id = $content->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','view','',array('id'=>$id));
	}
	 */



	function _loadContentForm($values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('content_01');
		$f->action = cgn_adminurl('content','main','save');
		$f->label = 'Add new content';
		$f->appendElement(new Cgn_Form_ElementInput('title'));
		$f->appendElement(new Cgn_Form_ElementInput('caption','Sub-title'));
		$f->appendElement(new Cgn_Form_ElementText('content'));
		$f->appendElement(new Cgn_Form_ElementHidden('mime'),$values['mime']);
		return $f;
	}
}

?>
