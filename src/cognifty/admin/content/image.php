<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

Cgn::loadModLibrary('Content::Cgn_Content');

class Cgn_Service_Content_Image extends Cgn_Service_AdminCrud {

	function Cgn_Service_Content_Image() {
		$this->displayName = 'Images';
	}

	function getHomeUrl() {
		return cgn_adminurl('content','image');
	}

	function mainEvent(&$req, &$t) {
		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','upload'),"New Image");
		$t['toolbar']->addButton($btn1);
		// GENERIC EXAMPLE OF HOW TO ADD ANOTHER BUTTON
		// $btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','web','new', array('mime'=>'wiki')),"New Wiki Page");
		// $t['toolbar']->addButton($btn2);

		$finder = new Cgn_DataItem('cgn_content');
		$finder->_cols = array('cgn_content.*', 'Tb.cgn_image_publish_id', 'Tb.cgn_content_version');
		$finder->hasOne('cgn_image_publish', 'cgn_content_id', 'Tb');

		$finder->andWhere('sub_type', 'image');
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

				$preview = '<img src="'.cgn_adminurl('content','preview','showImage',array('cid'=>$record['cgn_content_id'])).'" height="64" border="1"/>'; 
				

			if ($record['cgn_image_publish_id'] ) {
				$delLink = cgn_adminlink('unpublish','content','image','del',array('cgn_image_publish_id'=>$record['cgn_image_publish_id'], 'table'=>'cgn_image_publish'));
			} else {
				$delLink = cgn_adminlink('delete','content','image','del',array('cgn_content_id'=>$record['cgn_content_id'], 'table'=>'cgn_content'));
			}

			$list->data[] = array(
				cgn_adminlink($record['title'],'content','view','',array('id'=>$record['cgn_content_id'])),
				$status,
			// cgn_adminlink('edit','content','edit','',array('id'=>$record['cgn_content_id'])),
				$delLink,
				$preview
			);
		}
		// __FIXME__ add in editing capabilities.
		$list->headers = array('Title','Status','Delete','Preview');
		//$list->headers = array('Title','Preview','Edit','Delete');

		$t['adminTable'] = new Cgn_Mvc_TableView_Admin_Paged($list, $curPage);
		//set up pagination variables
		$t['adminTable']->setNextUrl( cgn_adminurl('content', 'image', '', array('p'=>'%d')) );
		$t['adminTable']->setPrevUrl( cgn_adminurl('content', 'image', '', array('p'=>'%d')) );
		$t['adminTable']->setBaseUrl( cgn_adminurl('content', 'image') );
		$t['adminTable']->setRpp($rpp);
	}


	/**
	 * Override this event so that we can unset the published_on date
	 * in the content table.
	 */
	function delEvent(&$req, &$t) {
		$table = $req->cleanString('table');
		$id    = $req->cleanInt($table.'_id');
		if ($table != 'cgn_image_publish') {
			return parent::delEvent($req,$t);
		}

		//this is removing a image publish record, basically an "unpublish" event
		$image = new Cgn_Image($id);
		$contentId = $image->getContentId();
		$content = new Cgn_Content($contentId);
		$content->dataItem->published_on = 0;
		$content->save();

		return parent::delEvent($req,$t);
	}


	function undoEvent($req, &$t) {
		$table = $req->cleanString('table');
		if ($table != 'cgn_image_publish') {
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
}

?>
