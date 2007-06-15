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

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		while ($db->nextRecord()) {
			$t['data'] = $db->record;
		}
		if ($t['data']['type'] == '') {
			$t['error'] = 'Unknown data type, cannot publish.';
			return;
		}

		$t['form'] = $this->_loadPublishForm(
			$t['data']['type'],
			array('id'=>$t['data']['cgn_content_id']));
	}



	function publishAsTextEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$subtype = $req->cleanInt('subtype');

		$content = new Cgn_Content($id);
		switch($subtype) {
		case 1:
			$subtypeName = 'article';
			$article = $content->asArticle();
			$article->save();

		case 2:
			$subtypeName = 'blog';

		case 3:
			$subtypeName = 'news';
		}

		//update main table with the id of the published content
		/* finish this later
		$pubId = $cont->cgn_content_publish_id;
		$db = Cgn_Db_Connector::getHandle();
		$db->query("UPDATE cgn_content SET cgn_content_publish_id = ".$pubId." WHERE cgn_content_id = ".$id);
		 */

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','main');
	}


	function publishAsFileEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$subtype = $req->cleanInt('subtype');

		$content = new Cgn_Content($id);
		switch($subtype) {
		case 1:
			$subtypeName = 'image';
			$image = $content->asImage();
			$image->save();
			break;

		case 2:
			$subtypeName = 'document';
			$doc = $content->asDocument();
			$doc->save();
			break;

		case 3:
			$subtypeName = 'news';
		}

		//update main table with the id of the published content
		/* finish this later
		$pubId = $cont->cgn_content_publish_id;
		$db = Cgn_Db_Connector::getHandle();
		$db->query("UPDATE cgn_content SET cgn_content_publish_id = ".$pubId." WHERE cgn_content_id = ".$id);
		 */

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','main');
	}



	function _loadPublishForm($type,$values=array()) {
		include_once('../cognifty/lib/form/lib_cgn_form.php');
		include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('publish');
		$f->action = cgn_adminurl('content','publish','publishAsText');
		$f->label = 'Publish Content';
		$radio = new Cgn_Form_ElementRadio('subtype','Choose a type');
		if ($type == 'text') {
			$radio->addChoice('Article');
			$radio->addChoice('Blog');
			$radio->addChoice('News');
		} else if ($type == 'file') {
			$f->action = cgn_adminurl('content','publish','publishAsFile');
			$radio->addChoice('Web Image');
			$radio->addChoice('Downloadable Attachment');
		}
		$f->appendElement($radio);
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['id']);
		$f->appendElement(new Cgn_Form_ElementHidden('event'),'publishAs');
		return $f;
	}


}
?>
