<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

Cgn::loadModLibrary('Content::Cgn_Content');


class Cgn_Service_Content_Web extends Cgn_Service_AdminCrud {

	function Cgn_Service_Content_Web() {
		$this->displayName = 'Pages';
	}

	function mainEvent(&$req, &$t) {

		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','edit','', array('type'=>'web', 'm'=>'html')), "New HTML Page");
		$t['toolbar']->addButton($btn1);
		$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','edit','', array('type'=>'web', 'm'=>'wiki')), "New Wiki Page");
		$t['toolbar']->addButton($btn2);
		$btn3 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','web','republish'), "Mass Republish");
		$t['toolbar']->addButton($btn3);
		$btn4 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','web', 'homepage'), "Set a Homepage");
		$t['toolbar']->addButton($btn4);

		$finder = new Cgn_DataItem('cgn_content');
		$finder->_cols = array('cgn_content.*', 'Tb.cgn_web_publish_id', 'Tb.cgn_content_version');
		$finder->hasOne('cgn_web_publish', 'cgn_content_id', 'Tb');
		$finder->andWhere('sub_type', 'web');
		$finder->orderBy('cgn_content.title');
		
		// $db = Cgn_Db_Connector::getHandle();

		// $db->query('SELECT A.title, A.cgn_content_id, A.version, A.published_on, B.cgn_web_publish_id, B.cgn_content_version
		//		FROM cgn_content AS A
		//		LEFT JOIN cgn_web_publish AS B
		//			ON A.cgn_content_id = B.cgn_content_id
		//		WHERE sub_type = \'web\' 
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
				// is the record published ??
				$status = '<img src="'.cgn_url().
				'/media/icons/default/bool_yes_24.png">';
				// check if versions are in sync ??
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
			
			$editLinks = cgn_adminlink('edit','content','edit','',array('id'=>$record['cgn_content_id']));
			
			if ($record['cgn_web_publish_id'] ) {
				$delLink = cgn_adminlink('unpublish','content','web','del',array('cgn_web_publish_id'=>$record['cgn_web_publish_id'], 'table'=>'cgn_web_publish'));
			} else {
				$delLink = cgn_adminlink('delete','content','web','del',array('cgn_content_id'=>$record['cgn_content_id'], 'table'=>'cgn_content'));
			}
			$list->data[] = array(
				cgn_adminlink($record['title'],'content','view','',array('id'=>$record['cgn_content_id'])),
				$status,
				$editLinks,
				$delLink
			);
		}
		$list->headers = array('Title','Status','Edit','Delete');

		// ADDING PAGINATION TO ASSETS ADMIN MODULE
		$t['adminTable'] = new Cgn_Mvc_TableView_Admin_Paged($list);
		//set up pagination variables
		$t['adminTable']->setCurPage($curPage);
 		$t['adminTable']->setNextUrl( cgn_adminurl('content', 'web', '', array('p'=>'%d')) );
		$t['adminTable']->setPrevUrl( cgn_adminurl('content', 'web', '', array('p'=>'%d')) );
		$t['adminTable']->setBaseUrl( cgn_adminurl('content', 'web') );
		$t['adminTable']->setRpp($rpp);
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
	 * Fix publish time when undo'ing a delete
	 */
	function undoEvent($req, &$t) {
		$table = $req->cleanString('table');
		if ($table != 'cgn_web_publish') {
			return parent::undoEvent($req,$t);
		}

		$trash = new Cgn_DataItem('cgn_obj_trash');
		$trash->load( $req->cleanInt('undo_id') );
		$obj = unserialize($trash->content);
		$contentId = $obj->cgn_content_id;

		$content = new Cgn_Content($contentId);
		$content->dataItem->published_on = time();
		$content->save();

		return parent::undoEvent($req,$t);
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


	/**
	 * Republish all content IDs which are currently published
	 */
	function republishEvent(&$req, &$t) {
		$finder = new Cgn_DataItem('cgn_content');
		$finder->_cols = array('cgn_content.*');
		$finder->hasOne('cgn_web_publish', 'cgn_content_id', 'Tpub'); 
		$finder->andWhere('Tpub.cgn_content_id', 'NULL', 'IS NOT');
		$contentList = $finder->find();

		$count = 0;
		foreach ($contentList as $_content) {
			$web = new Cgn_WebPage();
			$web->dataItem = $_content;
			$web = Cgn_ContentPublisher::publishAsWeb($web);
			$count++;
		}
		$req->getUser()->addSessionMessage('Re-published '.$count.' Web pages.');
		$this->redirectHome($t);
	}

	function homepageEvent(&$req, &$t) {

		$this->displayName = 'Pages - Set a homepage';

		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','web',''), "Back to Pages");
		$t['toolbar']->addButton($btn2);

		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT A.title, A.cgn_content_id, A.version, A.published_on, B.cgn_web_publish_id, B.cgn_content_version, B.is_home
				FROM cgn_content AS A
				LEFT JOIN cgn_web_publish AS B
					ON A.cgn_content_id = B.cgn_content_id
				WHERE sub_type = "web" 
			   	ORDER BY title');

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			if ($db->record['is_home']=='1') {
				
				// is the is_home field true (1)  ??
				$status = '<img src="'.cgn_url().
				'/media/icons/default/bool_yes_24.png">';
				
				// if it's true,(1), provide a link to unset this as a home page ??
				$unsetLink = cgn_adminlink('clear','content','web','unsetHp',array('cgn_web_publish_id'=>$db->record['cgn_web_publish_id'], 'table'=>'cgn_web_publish'));
			
				// if it's tru, (1), leave blank, it is already set ??
				$setLink = '';
			
			} else {
			
				// if it's false, anything other than (1), leave blank ??
				$status = '';
				
				// if it's false, anything other than (1), leave blank, nothing to unset ??
				$unsetLink = '';

				// if it's false, anything other than (1), provide a link to set this as a home page ??
				$setLink = cgn_adminlink('set','content', 'web','setHp',array('cgn_web_publish_id'=>$db->record['cgn_web_publish_id'], 'table'=>'cgn_web_publish'));			
			}
			
			$list->data[] = array(
				cgn_adminlink($db->record['title'],'content','view','',array('id'=>$db->record['cgn_content_id'])),
				$status,
				$unsetLink,
				$setLink
			);
		}
		
		$list->headers = array('Title','Status','Clear HP Status','Set HP Status');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
	}

	/**
	 * Set the is_home data to '1'
	 * in the cgn_web_publish table.
	 */
	public function setHpEvent(&$req, &$t) {
		$table = $req->cleanString('table');
		$id    = $req->cleanInt($table.'_id');
		if ($table != 'cgn_web_publish') {
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('content', 'web', 'homepage');
			return;
		}

		$db = Cgn_Db_Connector::getHandle();

		$sqlQuery01 = 'UPDATE '.$table.' SET is_home=1 WHERE '.$table.'.cgn_web_publish_id='.$id; 
		$db->query($sqlQuery01);

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl('content', 'web', 'homepage');
	}

	/**
	 * Set the is_home data to '0'
	 * in the cgn_web_publish table.
	 */
	public function unsetHpEvent(&$req, &$t) {
		$table = $req->cleanString('table');
		$id    = $req->cleanInt($table.'_id');
		if ($table != 'cgn_web_publish') {
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl('content', 'web', 'homepage');
			return;
		}

		$db = Cgn_Db_Connector::getHandle();

		$sqlQuery01 = 'UPDATE '.$table.' SET is_home=0 WHERE '.$table.'.cgn_web_publish_id='.$id; 
		$db->query($sqlQuery01);

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl('content', 'web', 'homepage');
	}



}

