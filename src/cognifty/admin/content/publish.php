<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');

Cgn::loadModLibrary('Content::Cgn_Content');

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
/*
			switch($t['data']['sub_type']) {
				case 'article':
					$db->query('select * from cgn_article_publish 
						WHERE cgn_content_id = '.$id);
					$db->nextRecord();
					$result = $db->record;
					$db->freeResult();
					$published = new Cgn_Article($db->record['cgn_article_publish_id']);
					break;
				case 'web':
					$db->query('select * from cgn_web_publish 
						WHERE cgn_content_id = '.$id);
					$db->nextRecord();
					$result = $db->record;
					$db->freeResult();
					$published = new Cgn_WebPage($db->record['cgn_web_publish_id']);
					break;

				case 'image':
					$db->query('select * from cgn_image_publish 
						WHERE cgn_content_id = '.$id);
					$db->nextRecord();
					$result = $db->record;
					$db->freeResult();
					$published = new Cgn_Image($db->record['cgn_image_publish_id']);
					break;

				case 'asset':
				case 'file':
					$db->query('select * from cgn_file_publish 
						WHERE cgn_content_id = '.$id);
					$db->nextRecord();
					$result = $db->record;
					$db->freeResult();
					$published = new Cgn_Asset($db->record['cgn_file_publish_id']);
					break;

				case 'blog_entry':
					$db->query('select * from cgn_blog_entry_publish 
						WHERE cgn_content_id = '.$id);
					$db->nextRecord();
					$result = $db->record;
					$db->freeResult();
					Cgn::loadModLibrary('Blog::BlogEntry','admin');
					$published = new Blog_BlogEntry($db->record['cgn_blog_entry_publish_id']);
					break;

				default:
					die('unknown sub type: '.$t['data']['sub_type']);
					
			}
 */

			$t['last_version'] = $published->getVersion();

			$values = array(
				'id'=>$t['data']['cgn_content_id'],
				'current_version'=>sprintf('%d',$t['data']['version']),
				'last_version'=>sprintf('%d',$t['last_version'])
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

		$subtype = $content->dataItem->sub_type;

		$cgnService = 'web';

		switch($subtype) {
		case 'article':
			$article = Cgn_ContentPublisher::publishAsArticle($content);
			$cgnService = 'articles';
			break;
		case 'web':
			$web = Cgn_ContentPublisher::publishAsWeb($content);
			$cgnService = 'web';
			break;

		case 'blog_entry':
			Cgn::loadModLibrary('Blog::BlogEntry','admin');
			$blog = Blog_BlogEntry::publishAsBlog($content);
			$this->presenter = 'redirect';
			$t['url'] = cgn_adminurl(
				'blog','post', '', array('blog_id'=>$blog->getBlogId()));
			return;
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

		}

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content',$cgnService);
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
