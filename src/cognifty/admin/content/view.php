<?php

include_once('../cognifty/lib/html_widgets/lib_cgn_widget.php');
include_once('../cognifty/lib/html_widgets/lib_cgn_toolbar.php');
include_once('../cognifty/lib/lib_cgn_mvc.php');
include_once('../cognifty/app-lib/lib_cgn_content.php');
include_once('../cognifty/lib/form/lib_cgn_form.php');

class Cgn_Service_Content_View extends Cgn_Service_Admin {

	function Cgn_Service_Content_View () {

	}

	function mainEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$t['content'] = new Cgn_DataItem('cgn_content');
		$t['content']->load($id);
		//__ FIXME __ check for a failed load

		$t['showPreview'] = false;
		if (@$t['content']->sub_type == '') {
			$t['useForm'] = $this->_loadUseForm($t['content']->type, $t['content']->valuesAsArray());
		}
		if (@$t['content']->type == 'text' && $t['content']->sub_type != '') {
			$t['showPreview'] = true;
		}

		//toolbar

		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		if ($t['content']->sub_type != '') { 
			$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','publish','',array('id'=>$t['content']->cgn_content_id)),"Publish");
			$t['toolbar']->addButton($btn2);
		}

		// __FIXME__ files should be editable
		if ($t['content']->type != 'file') { 
			$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','edit','', array('id'=>$t['content']->cgn_content_id)),"Edit");
			$t['toolbar']->addButton($btn1);
		}

		//__FIXME__ this is totally hacked up
		if ($t['content']->sub_type != '') {
			$sub_type = $t['content']->sub_type;
			$id = $t['content']->cgn_content_id;

			$db = Cgn_Db_Connector::getHandle();
			$db->query('select * from cgn_'.$sub_type.'_publish 
				WHERE cgn_content_id = '.$id);

			$db->nextRecord();
			$publishId = $db->record['cgn_'.$sub_type.'_publish_id'];
			//only allow either the Delete, or the unpublish button.
			if ($publishId < 1) {
				$btn3 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','edit','del', array('cgn_content_id'=>$t['content']->cgn_content_id, 'table'=>'cgn_content')),"Delete");
				$t['toolbar']->addButton($btn3);
			} else {
				$btn4 = new Cgn_HtmlWidget_Button(cgn_adminurl('content',$sub_type,'del', array('cgn_'.$sub_type.'_publish_id'=>$publishId, 'table'=>'cgn_'.$sub_type.'_publish')),"Unpublish");

				$t['toolbar']->addButton($btn4);
			}
		}


		//get content relations
		$db->query('SELECT to_id FROM cgn_content_rel
			WHERE from_id = '.$id);
		$relIds = array();
		$relObjs = array();
		while ($db->nextRecord()) {
			$finder = new Cgn_DataItem('cgn_content');
			//don't load bin nor content... might be too big for just showing titles
			$finder->_excludes[] = 'content';
			$finder->_excludes[] = 'binary';
			$finder->load($db->record['to_id']);
			$relObjs[] = $finder;
		}
		$t['relObjs'] = $relObjs;
	}




	function _loadUseForm($type,$values=array()) {
		$f = new Cgn_FormAdmin('use_as');
		$f->label = 'Choose how to use this content';

		$radio = new Cgn_Form_ElementRadio('subtype','Choose a type');
		if ($type == 'text') {
			$radio->addChoice('Article');
			$radio->addChoice('Web Page');
//			$radio->addChoice('Blog');
//			$radio->addChoice('News');
			$f->action = cgn_adminurl('content','publish','useAsText');
		} else if ($type == 'file') {
			$radio->addChoice('Web Image');
			$radio->addChoice('Downloadable Attachment');
			$f->action = cgn_adminurl('content','publish','useAsFile');
		}
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_content_id']);

		$f->appendElement($radio);

		return $f;
	}
}
?>
