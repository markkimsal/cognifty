<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/app-lib/lib_cgn_content.php');

class Cgn_Service_Content_Publish extends Cgn_Service_Admin {

	function Cgn_Service_Content_Publish () {

	}


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
			$t['publishForm'] = $this->_loadPublishForm(
				$t['data']['type'],
				array('id'=>$t['data']['cgn_content_id']));
		} else {
			$t['republishForm'] = $this->_loadRePublishForm(
				$t['data']['type'],
				array('id'=>$t['data']['cgn_content_id']));

			$db->query('select * from cgn_article_publish 
				WHERE cgn_content_id = '.$id);

			$db->nextRecord();
			$t['last_version'] = $db->record['cgn_content_version'];
		}
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

		case 3:
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
			$subtypeName = 'asset';
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

		$subtype = $content->dataItem->sub_type;

		switch($subtype) {
		case 'article':
			$article = Cgn_ContentPublisher::publishAsArticle($content);
			break;
		case 'web':
			$web = Cgn_ContentPublisher::publishAsWeb($content);
			break;

		case 'blog':
			break;

		case 'news':
			break;

		case 'image':
			$image = Cgn_ContentPublisher::publishAsImage($content);
			break;

		case 'asset':
			$ast = Cgn_ContentPublisher::publishAsAsset($content);
			break;

		}

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','main');
	}


	function _loadRePublishForm($type,$values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('publish');
		$f->action = cgn_adminurl('content','publish','publish');
		$f->label = 'Re-Publish Content';
		if ($type == 'file') {
			$f->action = cgn_adminurl('content','publish','publish');
		}
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['id']);
		return $f;
	}

/*
	function _loadPublishForm($type,$values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('publish');
		$f->action = cgn_adminurl('content','publish','publish');
		$f->label = 'Publish Content';
		$radio = new Cgn_Form_ElementRadio('subtype','Choose a type');
		if ($type == 'text') {
			$radio->addChoice('Article');
			$radio->addChoice('Blog');
			$radio->addChoice('News');
		} else if ($type == 'file') {
			$f->action = cgn_adminurl('content','publish','publish');
			$radio->addChoice('Web Image');
			$radio->addChoice('Downloadable Attachment');
		}
		$f->appendElement($radio);
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['id']);
		return $f;
	}
	*/
}
?>
