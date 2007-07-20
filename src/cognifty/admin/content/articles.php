<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/lib/lib_cgn_mvc_table.php');


class Cgn_Service_Content_Articles extends Cgn_Service_AdminCrud {

	function Cgn_Service_Content_Articles() {
	}

	function mainEvent(&$sys, &$t) {

		$t['message1'] = '<h3>Articles</h3>';
	
		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_article_publish ORDER BY title');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			$list->data[] = array(
				cgn_adminlink($db->record['title'],'content','articles','view',array('id'=>$db->record['cgn_article_publish_id'])),
				$db->record['caption'],
				cgn_adminlink('edit','content','edit','',array('id'=>$db->record['cgn_content_id'])),
				cgn_adminlink('delete','content','articles','del',array('id'=>$db->record['cgn_article_publish_id'], 'table'=>'cgn_article_publish'))
			);
		}
		$list->headers = array('Title','Sub-Title','Edit','Delete');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
	}


	function viewEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$t['content'] = new Cgn_DataItem('cgn_article_publish');
		$t['content']->load($id);

		//load all sections
		$s = new Cgn_DataItem('cgn_article_section');
		$allSections = $s->find();

		//load linked sections
		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT * FROM cgn_article_section_link
			WHERE cgn_article_publish_id = '.$id);
		$linkedSections = array();
		while ($db->nextRecord()) {
			$linkedSections[] = $db->record;
		}

		$linkArray = array();
		foreach ($linkedSections as $sec) {
			$linkArray[] = $sec['cgn_article_section_id'];
		}

		$secArray = array();
		foreach ($allSections as $sec) {
			$secArray[$sec->cgn_article_section_id] = $sec->title;
		}

		$t['sectionForm'] = $this->_loadSectionForm($secArray,$linkArray,array('id'=>$id));
		//__ FIXME __ check for a failed load
	}


	function sectionEvent(&$req, &$t) {
		$new_sec = $req->cleanString('new_sec');
		$articleId = $req->cleanInt('id');
		/*
		$old_sec = $req->cleanArray('sec');

		cgn::debug($old_sec);
		exit();
		 */
		$newSections = array();
		if ( strlen(trim($new_sec)) ) {
			$newSections = explode(';',$new_sec);
		}
		$sectionIds = array();
		foreach ($newSections as $sec) {
			$s = new Cgn_DataItem('cgn_article_section');
			$s->andWhere('title',trim($sec));
			$s->load();
			//if non-existant make a new section
			if ($s->_isNew) {
				$s->title = trim($sec);
				$s->link_text = cgn_link_text($sec);
				$sectionIds[] = $s->save();
			} else {
				$sectionIds[] = $s->cgn_article_section_id;
			}
		}
		//find links to old sections
		foreach ($req->postvars['sec'] as $val) {
			print_r($val);
			$sectionIds[] = intval($val);
		}

		$db = Cgn_Db_Connector::getHandle();
		$db->query('DELETE FROM cgn_article_section_link
			WHERE cgn_article_publish_id = '.$articleId);

		//link old and new section ids
		foreach ($sectionIds as $id) {
			$link = new Cgn_DataItem('cgn_article_section_link');
			$link->andWhere('cgn_article_section_id',$id);
			$link->andWhere('cgn_article_publish_id',$articleId);
			$link->load();
			if ($link->_isNew) {
				$link->cgn_article_section_id = $id;
				$link->cgn_article_publish_id = $articleId;
				$link->save();
			}
		}
		/*
		foreach ($newSections as $sec) {
//			$link = new Cgn_DataItem('cgn_article_section_link');
//			$link->andWhere('cgn_article_section_id');
		*/


		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','articles','view',array('id'=>$articleId));
	}


	function _loadSectionForm($sections=array(),$links=array(),$values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('section_01');
		$f->width="430px";
		$f->action = cgn_adminurl('content','articles','section');
		$f->label = 'Link to sections';
		$f->appendElement(new Cgn_Form_ElementInput('new_sec','New Sections'));
		if ( count($sections) ) {
			$check = new Cgn_Form_ElementCheck('sec','Choose a section');
			foreach ($sections as $id =>$sec) {
				if (in_array($id, $links)){
					$check->addChoice($sec,$id,1);
				} else {
					$check->addChoice($sec,$id);
				}
			}
			$f->appendElement($check);
		}
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['id']);
		return $f;
	}
}

?>
