<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

Cgn::loadModLibrary('Content::Cgn_Content');

class Cgn_Service_Content_Assets extends Cgn_Service_AdminCrud {

	function Cgn_Service_Content_Assets() {
		$this->displayName = 'Assets';
	}

	function mainEvent(&$req, &$t) {

		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','upload'),"New File");
		$t['toolbar']->addButton($btn1);
		// GENERIC EXAMPLE OF HOW TO ADD ANOTHER BUTTON
		// $btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','web','new', array('mime'=>'wiki')),"New Wiki Page");
		// $t['toolbar']->addButton($btn2);

		$finder = new Cgn_DataItem('cgn_content');
		$finder->_cols = array('cgn_content.*', 'Tb.cgn_file_publish_id', 'Tb.cgn_content_version');
		$finder->hasOne('cgn_file_publish', 'cgn_content_id', 'Tb');
		$finder->andWhere('sub_type', 'file');
		$finder->orderBy('cgn_content.title');
		
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

			$fileDescription = $record['description'];

			if ($record['cgn_file_publish_id'] ) {
				$delLink = cgn_adminlink('unpublish','content','assets','del',array('cgn_file_publish_id'=>$record['cgn_file_publish_id'], 'table'=>'cgn_file_publish'));
			} else {
				$delLink = cgn_adminlink('delete','content','assets','del',array('cgn_content_id'=>$record['cgn_content_id'], 'table'=>'cgn_content'));
			}

			$list->data[] = array(
				cgn_adminlink($record['title'],'content','view','',array('id'=>$record['cgn_content_id'])),
				$status,
				$fileDescription,
				$delLink
			);
		}
		// __FIXME__ add in edit capabilities
		// $list->headers = array('Title','Description','Delete');
		$list->headers = array('Title','Status','Description','Delete');

		// ADDING PAGINATION TO ASSETS ADMIN MODULE
		$t['adminTable'] = new Cgn_Mvc_TableView_Admin_Paged($list);
		//set up pagination variables
		$t['adminTable']->setCurPage($curPage);
 		$t['adminTable']->setNextUrl( cgn_adminurl('content', 'assets', '', array('p'=>'%d')) );
		$t['adminTable']->setPrevUrl( cgn_adminurl('content', 'assets', '', array('p'=>'%d')) );
		$t['adminTable']->setBaseUrl( cgn_adminurl('content', 'assets') );
		$t['adminTable']->setRpp($rpp);
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
