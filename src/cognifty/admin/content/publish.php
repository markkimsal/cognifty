<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');

Cgn::loadModLibrary('Content::Cgn_Content');

class Cgn_Service_Content_Publish extends Cgn_Service_Admin {

	/**
	 * This is only set when calling the event "content_publish_$subtype"
	 */
	public $eventContentObj = NULL;


	function mainEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$db = Cgn_Db_Connector::getHandle();
		$db->query('select * from cgn_content 
			WHERE cgn_content_id = '.$id);

		$db->nextRecord();
		$t['data'] = $db->record;

		if ($t['data']['type'] == '') {
			$t['error'] = 'Unknown data type, cannot publish.';
			return;
		}

		if ($t['data']['sub_type'] == '') {
			//don't allow a publish
			/*
			$t['publishForm'] = $this->_loadPublishForm(
				$t['data']['type'],
				array('id'=>$t['data']['cgn_content_id']));
			 */
		} else {
			//load the published content based on type
			//$published = null;
			$subType = $t['data']['sub_type'];
			$published = Cgn_ContentPublisher::loadPublished($subType, $id);

			$lastVersion = 0;
			if (is_object($published)) {
				$lastVersion = $published->getVersion();
			}

			$values = array(
				'id'=>$t['data']['cgn_content_id'],
				'current_version'=>sprintf('%d',$t['data']['version']),
				'last_version'=>sprintf('%d',$lastVersion)
				);
			$t['republishForm'] = 
				$this->_loadPublishForm(
					$t['data']['type'],
					$values
				);

		}


		if ( ! is_object($db) ) {
			$db = Cgn_Db_Connector::getHandle();
		}
		//get content relations
		$db->query('SELECT from_id FROM cgn_content_rel
			WHERE to_id = '.$id);
		$relIds = array();
		$list = new Cgn_Mvc_ListModel();
		//cut up the data into table data

		while ($db->nextRecord()) {
			$finder = new Cgn_DataItem('cgn_content');
			//don't load bin nor content... might be too big for just showing titles
			$finder->_excludes[] = 'content';
			$finder->_excludes[] = 'binary';
			$finder->load($db->record['from_id']);
			$list->data[] = cgn_adminlink($finder->title,'content', 'view', '', array('id'=>$finder->cgn_content_id));


		}
		$t['dataList'] = new Cgn_Mvc_ListView($list);
		$t['dataList']->style['list-style'] = 'disc';

	}


	function useAsTextEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$subtype = $req->cleanInt('subtype');

		$content = new Cgn_Content($id);
		switch($subtype) {
		case 1:
			$subtypeName = 'article';
			break;
		case 2:
			$subtypeName = 'web';
			break;
		case 3:
			$subtypeName = 'blog';
			break;

		case 4:
			$subtypeName = 'news';
			break;
		}

		$content->dataItem->sub_type = $subtypeName;
		$content->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','view','',array('id'=>$id));
	}


	function useAsFileEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$subtype = $req->cleanInt('subtype');

		$content = new Cgn_Content($id);
		switch($subtype) {
		case 1:
			$subtypeName = 'image';
			break;

		case 2:
			$subtypeName = 'file';
			break;
		}
		$content->dataItem->sub_type = $subtypeName;
		$content->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','view','',array('id'=>$id));
	}


	function publishEvent(&$req, &$t) {
		$id = $req->cleanInt('id');

		$content = new Cgn_Content($id);

		$subType = $content->dataItem->sub_type;

		$cgnService = 'web';

		switch($subType) {
			case 'article':
				$article = Cgn_ContentPublisher::publishAsArticle($content);
				$cgnService = 'articles';
				break;
			case 'web':
				$web = Cgn_ContentPublisher::publishAsWeb($content);
				$cgnService = 'web';
				break;


			case 'news':
				break;

			case 'image':
				$image = Cgn_ContentPublisher::publishAsImage($content);
				$cgnService = 'image';
				break;

			case 'asset':
			case 'file':
				$ast = Cgn_ContentPublisher::publishAsAsset($content);
				$cgnService = 'assets';
				break;

			default:
				$signal = 'content_publish_'.sprintf('%s', $subType);
				$this->eventContentObj = $content;
				$res = $this->emit($signal);
				if ($res === NULL) {
					$u = $req->getUser();
					$u->addSessionMessage('Unknown content type, cannot pubish', 'msg_warn');
					$t['url'] = cgn_adminurl(
						'content');
				} else {
					//set the redirect to the returned value
					$t['url'] = $res;
				}
				break;
		}

		$this->presenter = 'redirect';
		if (!isset($t['url'])) {
			$t['url'] = cgn_adminurl(
				'content',$cgnService);
		}
	}


	function _loadPublishForm($type,$values=array()) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('publish');
		$f->action = cgn_adminurl('content','publish','publish');
		$f->label = 'Publish Content';

		$f->appendElement(new Cgn_Form_ElementLabel('cv','Current version: '),$values['current_version']);
		$f->appendElement(new Cgn_Form_ElementLabel('cv','Last published version: '),$values['last_version']);

		if ($type == 'file') {
			$f->action = cgn_adminurl('content','publish','publish');
		}
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['id']);
		return $f;
	}
}
?>
