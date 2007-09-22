<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
include_once('../cognifty/admin/content/wiki_form.php');


class Cgn_Service_Content_Main extends Cgn_Service_Admin {

	function Cgn_Service_Content_Main () {

	}


	function mainEvent(&$req, &$t) {
		$t['message1'] = '<h1>Content</h1>';

		$contentRecs = array();

		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT * 
					FROM cgn_content AS A
					WHERE A.published_on < A.edited_on ORDER BY title');
		while ($db->nextRecord()) {
			$contentRecs[$db->record['cgn_content_id']]  = $db->record;
		}

		//find all other types of content
		$contentTypes = array('web','image','file','article');
		foreach ($contentTypes as $type) {
			$db->query('SELECT A.*
						FROM cgn_content AS A
						LEFT JOIN cgn_'.$type.'_publish AS B
						USING (cgn_content_id)
						WHERE A.sub_type = "'.$type.'"
						AND B.cgn_content_id IS NULL
						');
			while ($db->nextRecord()) {
				$contentRecs[$db->record['cgn_content_id']]  = $db->record;
			}
		}



		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		foreach ($contentRecs as $record) {
			$list->data[] = array(
				cgn_adminlink(
				   $record['title'],
				   'content','view','',array('id'=>$record['cgn_content_id'])),
				$record['caption'],
				$record['type'],
				$record['sub_type'],
				cgn_adminlink('edit','content','edit','',array('id'=>$record['cgn_content_id'])),
				cgn_adminlink('delete','content','edit','del',array('cgn_content_id'=>$record['cgn_content_id'],'table'=>'cgn_content')),
			);
		}



		$list->headers = array('Title','Sub-Title','Type','Used as','Edit','Delete');
//		$list->columns = array('title','caption','content');

//		$t['menuPanel'] = new Cgn_Menu('Sample Menu',$list);
		$t['form'] = new Cgn_Mvc_AdminTableView($list);
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
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
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
