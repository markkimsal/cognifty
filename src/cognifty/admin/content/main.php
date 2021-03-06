<?php

include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');


Cgn::loadModLibrary('Content::Cgn_Content');

class Cgn_Service_Content_Main extends Cgn_Service_Admin {

	function Cgn_Service_Content_Main () {

		$this->displayName = 'New Content';
	}


	/**
	 * Lists pending content and shows toolbar buttons for controlling content.
	 */
	function mainEvent(&$req, &$t) {

		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn1 = new Cgn_HtmlWidget_Button( cgn_adminurl('content','edit','',array('m'=>'html')), "Add HTML Content");
		$t['toolbar']->addButton($btn1);
		$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','edit','',array('m'=>'wiki')), "Add Wiki Content");
		$t['toolbar']->addButton($btn2);
		$btn3 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','upload'), "Upload a File");
		$t['toolbar']->addButton($btn3);
		$btn4 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','upload'), "Upload an Image");
		$t['toolbar']->addButton($btn4);


		$contentRecs = array();

		$db = Cgn_Db_Connector::getHandle();


		//find specific types of content (sub_type)
		$contentTypes = $this->_getAllContentTypes();
		foreach ($contentTypes as $type => $table) {
			$db->query('SELECT A.*, B.cgn_content_version as pubver
						FROM cgn_content AS A
						LEFT JOIN '.$table.' AS B
						USING (cgn_content_id)
						WHERE A.sub_type = "'.$type.'"
						AND (B.cgn_content_id IS NULL OR B.cgn_content_version < A.version)
						');
			while ($db->nextRecord()) {
				$contentRecs[$db->record['cgn_content_id']]  = $db->record;
			}
		}
		//find content which is not "used-as" anything
		$db->query('SELECT A.*, 0 as pubver
					FROM cgn_content AS A
					WHERE 
					(A.sub_type = ""
					or A.sub_type IS NULL)
					');
		while ($db->nextRecord()) {
			$contentRecs[$db->record['cgn_content_id']]  = $db->record;
		}


		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		foreach ($contentRecs as $record) {
			$list->data[] = array(
				cgn_adminlink(
				   $record['title'],
				   'content','view','',array('id'=>$record['cgn_content_id'])),
				$record['caption'],
				'unpublished @'.$record['version']. ' published @'. sprintf('%d',$record['pubver']),
				$record['sub_type'],
				cgn_adminlink('edit','content','edit','',array('id'=>$record['cgn_content_id'])),
				cgn_adminlink('delete','content','edit','del',array('cgn_content_id'=>$record['cgn_content_id'],'table'=>'cgn_content')),
			);
		}



		$list->headers = array('Title','Sub-Title','Version','Used as','Edit','Delete');
//		$list->columns = array('title','caption','content');

		$t['table'] = new Cgn_Mvc_AdminTableView($list);

		$list2 = new Cgn_Mvc_TableModel();
		$t['table2'] = new Cgn_Mvc_AdminTableView($list2);
	}


	function _loadContentForm($values=array()) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('content_01');
		$f->action = cgn_adminurl('content','main','save');
		$f->label = 'Add new content';
		$f->appendElement(new Cgn_Form_ElementInput('title'));
		$f->appendElement(new Cgn_Form_ElementInput('caption','Sub-title'));
		$f->appendElement(new Cgn_Form_ElementText('content'));
		$f->appendElement(new Cgn_Form_ElementHidden('mime'),$values['mime']);
		return $f;
	}

	public function _getAllContentTypes() {
		$contentTypes = array(
			'web' => 'cgn_web_publish',
			'image' => 'cgn_image_publish',
			'file' => 'cgn_file_publish',
			'article' => 'cgn_article_publish');

		$configArray = Cgn_ObjectStore::getArray('config://default/content/extrasubtype');
		foreach ($configArray as $_code => $_v) {
			$plugin = Cgn_ObjectStore::includeObject($_v);
			if ($plugin === NULL) {
				$e = Cgn_ErrorStack::pullError('php');
				continue;
			}
			$contentTypes[$plugin->getFormValue()] = $plugin->getPublishedTable();
		}
		return $contentTypes;
	}
}

?>
