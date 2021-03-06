<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');
include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');

Cgn::loadModLibrary('Content::Cgn_Content');

class Cgn_Service_Content_View extends Cgn_Service_Admin {

	function Cgn_Service_Content_View () {

	}

	function mainEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$t['content'] = new Cgn_DataItem('cgn_content');
		$t['content']->load($id);

		//toolbar
		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		
		if ($t['content']->sub_type === 'blog_entry') { 

			$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('blog','post','edit', array('id'=>$t['content']->cgn_content_id)),"Edit");
			$t['toolbar']->addButton($btn1);
		} else 
		if ($t['content']->type != 'file') { 
			$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','edit','', array('id'=>$t['content']->cgn_content_id)),"Edit");
			$t['toolbar']->addButton($btn1);
		} else {
			$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','upload','', array('id'=>$t['content']->cgn_content_id)),"Edit");
			$t['toolbar']->addButton($btn1);
		}
		if ($t['content']->sub_type != '') { 
			$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','publish','',array('id'=>$t['content']->cgn_content_id)),"Publish");
			$t['toolbar']->addButton($btn2);
		}

		// Cgn::debug($t['toolbar']);

		$contentObj = new Cgn_Content($id);
		$publisherPlugin = $contentObj->getPublisherPlugin();
		$publisherPlugin->initDefaultAttributes($contentObj);
		$contentObj->loadAllAttributes();

		if ($contentObj->usedAs('web')) {
			if (! isset($contentObj->attribs['is_portal'])) {
				$contentObj->setAttribute('is_portal',0, 'int');
			}
		}

		if( count($contentObj->attribs) ) {
			$t['attributeForm'] = $this->_loadAttributesForm($contentObj->attribs, $contentObj->getId());
		}

		//load tags from the database
		$contentObj->loadAllTags();
		$t['tagForm'] = $this->_loadTagForm($contentObj->tags,  $contentObj->getId());

		//__ FIXME __ check for a failed load

		$t['showPreview'] = false;
		if (@$t['content']->sub_type == '') {
			$t['useForm'] = $this->_loadUseForm($t['content']->type, $t['content']->valuesAsArray());
		}
		if (@$t['content']->type == 'text' && $t['content']->sub_type != '') {
			$t['showPreview'] = true;
		}
		if (@$t['content']->type == 'file' && $t['content']->sub_type == 'image') {
			$t['showPreview'] = true;
		}

		if ($contentObj->isText()) {
			$content = strip_tags($contentObj->getContent());
			if ( strlen($content) > 1000) {
				$t['halfPreview'] = nl2br( substr($content,0,1000) ).'...';
			} else {
				$t['halfPreview'] = nl2br( $content );
			}
			unset($content);
		}

		//__FIXME__ this is totally hacked up
		if ($t['content']->sub_type != '') {
			$sub_type = $t['content']->sub_type;
			$id = $t['content']->cgn_content_id;

			$db = Cgn_Db_Connector::getHandle();
			if (!$db->query('select * from cgn_'.$sub_type.'_publish 
				WHERE cgn_content_id = '.$id)) {
					//this is a custom content subtype
					//eat the error
					$e = Cgn_ErrorStack::pullError('php');
			}

			$db->nextRecord();
			$publishId = $db->record['cgn_'.$sub_type.'_publish_id'];
			//only allow either the Delete, or the unpublish button.
			if ($publishId < 1) {
				$btn3 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','edit','del', array('cgn_content_id'=>$t['content']->cgn_content_id, 'table'=>'cgn_content')),"Delete");
				$t['toolbar']->addButton($btn3);
			} else {

				//temporary fix for sub_type => module.php filename
				//TODO: add a plugin based system for sub_types
				switch ($sub_type) {
					case 'article':
						$module = 'articles';
						break;
					case 'file':
						$module = 'assets';
						break;
					default:
						$module = $sub_type;
				}
				$btn4 = new Cgn_HtmlWidget_Button(cgn_adminurl('content',$module,'del', array('cgn_'.$sub_type.'_publish_id'=>$publishId, 'table'=>'cgn_'.$sub_type.'_publish')),"Unpublish");

				$t['toolbar']->addButton($btn4);
			}
		}


		if ( ! is_object($db) ) {
			$db = Cgn_Db_Connector::getHandle();
		}
		//get content relations
		$db->query('SELECT to_id FROM cgn_content_rel
			WHERE from_id = '.$id);
		$relIds = array();
		$list = new Cgn_Mvc_ListModel();
		//cut up the data into table data

		while ($db->nextRecord()) {
			$finder = new Cgn_DataItem('cgn_content');
			//don't load bin nor content... might be too big for just showing titles
			$finder->_excludes[] = 'content';
			$finder->_excludes[] = 'binary';
			$finder->load($db->record['to_id']);
			$list->data[] = cgn_adminlink($finder->title,'content', 'view', '', array('id'=>$finder->cgn_content_id));
		}
		$t['dataList'] = new Cgn_Mvc_ListView($list);
		$t['dataList']->style['list-style'] = 'disc';


		//handle articles for sections
		$sub_type = $t['content']->sub_type;
		if ($sub_type == 'article') {
			$this->loadSectionForm($t, $id);
		}


		$mime = $t['content']->mime;
		$this->displayName = ucfirst($t['content']->get('title')).' ('.strtoupper($mime).' Content)';
	}

	function loadSectionForm(&$t, $id) {
		//load all sections
		$s = new Cgn_DataItem('cgn_article_section');
		$allSections = $s->find();

		//load linked sections
		$db = Cgn_Db_Connector::getHandle();
		$db->query('SELECT cgn_article_publish_id from cgn_article_publish
				WHERE cgn_content_id = '.$id);
		$db->nextRecord();
//TODO: this free result throws an error for some reason, maybe nextRecord calls it already?
//		$db->freeResult();
		$articleId = $db->record['cgn_article_publish_id'];
		if ($articleId == 0 ) {
			return false;
		}
		$db->query('SELECT * FROM cgn_article_section_link
			WHERE cgn_article_publish_id = '.sprintf('%d',$articleId));
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



	function _loadUseForm($type,$values=array()) {
		$f = new Cgn_FormAdmin('use_as');
		$f->label = 'Choose how to use this content';

		$radio = new Cgn_Form_ElementRadio('subtype','Choose a type');
		if ($type == 'text') {
			$radio->addChoice('Article');
			$radio->addChoice('Web Page');
			$f->action = cgn_adminurl('content','publish','useAsText');
		} else if ($type == 'file') {
			$radio->addChoice('Web Image');
			$radio->addChoice('Downloadable Attachment');
			$f->action = cgn_adminurl('content','publish','useAsFile');
		}
		//apply custom content sub types
		$configArray = Cgn_ObjectStore::getArray('config://default/content/extrasubtype');
		foreach ($configArray as $_k => $_v) {
			$plugin = Cgn_ObjectStore::includeObject($_v);
			if ($plugin === FALSE) {
				$e = Cgn_ErrorStack::pullError('php');
				continue;
			}
			$radio->addChoice($plugin->getDisplayName(), $plugin->getFormValue());
		}

		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_content_id']);

		$f->appendElement($radio);

		return $f;
	}

	function _loadAttributesForm($values=array(), $id) {
		$f = new Cgn_FormAdmin('content_attr');
		$f->label = 'Set attributes for this Content Item.';
		$f->action = cgn_adminurl('content','edit','saveAttr');
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$id);

		foreach ($values as $_k => $_attr) {
			if ($_attr->type == 'string') {
				$widget = new Cgn_Form_ElementInput($_k);
				$f->appendElement($widget, $_attr->value);
			} else if ($_attr->type == 'bool') {
				$widget = new Cgn_Form_ElementCheck($_k, $_k);
				$widget->addChoice('Yes', 'yes',($_attr->value == '1'));
				$f->appendElement($widget);
			} else if ($_attr->type == 'int') {
				$widget = new Cgn_Form_ElementInput($_k);
				$f->appendElement($widget, $_attr->value);
			}
			unset($widget);
		}

		return $f;
	}

	function _loadTagForm($values=array(), $id) {
		$f = new Cgn_FormAdmin('content_tag');
		$f->label = 'Set tags for this Content Item.';

		$input = new Cgn_Form_ElementInput('new_tag', 'New Tag');
		$f->appendElement($input, '');

		$check = new Cgn_Form_ElementCheck('old_tag', 'Existing Tag');
		foreach ($values as $_v) {
			$check->addChoice($_v->get('name'), $_v->get('link_text'), TRUE);
		}
		$f->action = cgn_adminurl('content', 'edit', 'saveTag');
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$id);
		$f->appendElement($check);
		return $f;
	}

}
?>
