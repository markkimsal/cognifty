<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');


class Cgn_Service_Content_Articles extends Cgn_Service_AdminCrud {

	function Cgn_Service_Content_Articles() {
		$this->displayName = 'Articles';
	}

	function mainEvent(&$req, &$t) {

		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','edit','',array('m'=>'html')),"New HTML Article");
		$t['toolbar']->addButton($btn1);
		$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','edit','',array('m'=>'wiki')),"New Wiki Article");
		$t['toolbar']->addButton($btn2);

		$finder = new Cgn_DataItem('cgn_content');
		$finder->_cols = array('cgn_content.*', 'Tb.cgn_article_publish_id', 'Tb.cgn_content_version');
		$finder->hasOne('cgn_article_publish', 'cgn_content_id', 'Tb');
		$finder->andWhere('sub_type', 'article');
		$finder->orderBy('cgn_content.title');

		// $db = Cgn_Db_Connector::getHandle();
		// $db->query('select * from cgn_article_publish ORDER BY title');
		//$db->query('SELECT A.title, A.cgn_content_id, A.version, A.published_on, B.cgn_article_publish_id, B.cgn_content_version
		//		FROM cgn_content AS A
		//		LEFT JOIN cgn_article_publish AS B
		//			ON A.cgn_content_id = B.cgn_content_id
		//		WHERE sub_type = "article" 
		//	   	ORDER BY title');

		//set up pagination variables
		$curPage = $req->cleanInt('p');
		if ($curPage == 0 ) {
			$curPage = 1;
		}
		$rpp = 20;

		$finder->limit($rpp, ($curPage-1));
		$totalRows = $finder->getUnlimitedCount();
		
		$list = new Cgn_Mvc_TableModel();
		$list->setUnlimitedRowCount($totalRows);

		$items = $finder->findAsArray();

		//cut up the data into table data
		foreach($items as $record) {
			if ($record['published_on']) {
				$status = '<img src="'.cgn_url().
				'/media/icons/default/bool_yes_24.png">';
				if ($record['version']==$record['cgn_content_version']) {
					$status = '<img src="'.cgn_url().
					'/media/icons/default/bool_yes_24.png">';
				} else {
					$status = '<img src="'.cgn_url().
					'/media/icons/default/caution_24.png">';
				}
					
			} else {
				$status = '';
			}

			$editLinks = 
			cgn_adminlink('edit','content','edit','',array('id'=>$record['cgn_content_id']));	

			if ($record['cgn_article_publish_id'] ) {
				$delLink = cgn_adminlink('unpublish','content','articles','del',array('cgn_article_publish_id'=>$record['cgn_article_publish_id'], 'table'=>'cgn_article_publish'));
			} else {
				$delLink = cgn_adminlink('delete','content','articles','del',array('cgn_content_id'=>$record['cgn_content_id'], 'table'=>'cgn_content'));
			}

			$list->data[] = array(
				cgn_adminlink($record['title'],'content','view','',array('id'=>$record['cgn_content_id'])),
				$status,
				$editLinks,
				$delLink
			);

		}
		$list->headers = array('Title','Sub-Title','Edit','Delete');

		// ADDING PAGINATION TO ASSETS ADMIN MODULE
		$t['adminTable'] = new Cgn_Mvc_TableView_Admin_Paged($list);
		//set up pagination variables
		$t['adminTable']->setCurPage($curPage);
 		$t['adminTable']->setNextUrl( cgn_adminurl('content', 'articles', '', array('p'=>'%d')) );
		$t['adminTable']->setPrevUrl( cgn_adminurl('content', 'articles', '', array('p'=>'%d')) );
		$t['adminTable']->setBaseUrl( cgn_adminurl('content', 'articles') );
		$t['adminTable']->setRpp($rpp);
	}


	/**
	 * Update section list, bounce user back to content.view
	 *
	 * FIXME: try to intelligently update the list of sections 
	 * per article so we can keep the timestamp
	 */
	function sectionEvent(&$req, &$t) {
		$new_sec = $req->cleanString('new_sec');
		$contentId = $req->cleanInt('id');

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
		if ( is_array($req->postvars['sec']) ) {
			foreach ($req->postvars['sec'] as $val) {
				$sectionIds[] = intval($val);
			}
		}

		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT cgn_article_publish_id from cgn_article_publish
				WHERE cgn_content_id = '.$contentId);
		$db->nextRecord();
		$db->freeResult();
		$articleId = $db->record['cgn_article_publish_id'];
		//*
		$db->query('DELETE FROM cgn_article_section_link
			WHERE cgn_article_publish_id = '.$articleId);
		// */

		//link old and new section ids
		foreach ($sectionIds as $id) {
			$link = new Cgn_DataItem('cgn_article_section_link', 'cgn_article_section_id');
			$link->andWhere('cgn_article_section_id',$id);
			$link->andWhere('cgn_article_publish_id',$articleId);
			$link->load();
			if ($link->_isNew) {
				$link->cgn_article_section_id = $id;
				$link->cgn_article_publish_id = $articleId;
				$link->active_on = time();
				$link->save();
			}
		}


		$user = Cgn_SystemRequest::getUser();
		$user->addSessionMessage("Article sections updated. (#".$articleId.")");
		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','view','',array('id'=>$contentId));
	}


	function _loadSectionForm($sections=array(),$links=array(),$values=array()) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
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
