<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

Cgn::loadModLibrary('Blog::UserBlog','admin');

class Cgn_Service_Blog_Main extends Cgn_Service_AdminCrud {

	var $displayName = 'Blog';
	var $db = null;
	var $tableName = 'cgn_blog';

	function Cgn_Blog_Content_Main () {
		$this->db = Cgn_Db_Connector::getHandle();
	}


	function mainEvent(&$req, &$t) {
		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('blog','main','edit'),"New Blog");
		$t['toolbar']->addButton($btn1);
		$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('blog','post','edit'),"New Blog Post");
		$t['toolbar']->addButton($btn2);


		$userBlogs = Blog_UserBlog::loadAll();


		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		foreach($userBlogs as $_blog) {
			if ($_blog->getBlogId() ) {
				$delLink = cgn_adminlink('unpublish','content','web','del',array('cgn_web_publish_id'=>$db->record['cgn_web_publish_id'], 'table'=>'cgn_web_publish'));
			} else {
				$delLink = cgn_adminlink('delete','content','web','del',array('cgn_content_id'=>$db->record['cgn_content_id'], 'table'=>'cgn_content'));
			}
			$list->data[] = array(
				cgn_adminlink($_blog->getTitle(),'blog','post','',array('blog_id'=>$_blog->getBlogId())),
				$_blog->getCaption(),
				$_blog->getDescription(),
				 cgn_adminlink('edit','blog','main','edit',array('id'=>$_blog->getBlogId())), 
				'' /* $delLink */ 
			);
		}
		$list->headers = array('Title','Tag-Line','Description','Edit','Delete');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
	}

	/**
	 * Allow changing of the blog name
	 */
	function editEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$values = array();
		$blog = new Blog_UserBlog($id);
		$values = $blog->_item->valuesAsArray();
		$values['preview_style']  = $blog->getAttribute('preview_style')->value;
		$t['form'] = $this->_loadEditForm($values);
	}

	/**
	 * save the data item, then save the attributes
	 */
	function saveEvent(&$req, &$t) {
		parent::saveEvent($req, $t);
		$blog = new Blog_UserBlog(0);
		$blog->_item = $this->item;
		$blog->setAttribute('preview_style', $req->cleanInt('prev_style'), 'int');
		$blog->setAttribute('entpp', $req->cleanInt('entpp'), 'int');
		$blog->saveAttributes();
	}

	function _loadEditForm($values=array()) {
		//defaults
		$values['entpp'] = isset($values['entpp']) ? $values['entpp'] : 5;

		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_FormAdmin('blog_edit');
		$f->width="auto";
		$f->action = cgn_adminurl('blog','main','save');
		$f->label = '';
		$title = new Cgn_Form_ElementInput('title');
		$title->size = 55;
		$f->appendElement($title,$values['title']);

		$check = new Cgn_Form_ElementCheck('is_default','Make This Blog Default?');
		$check->addChoice('Yes','1',$values['is_default']);

		$f->appendElement($check);

		$preview = new Cgn_Form_ElementRadio('prev_style','Preview Style');

		$preview->addChoice('Use first 1000 characters',$values['preview_style']===1);
		$preview->addChoice('Use excerpt field',$values['preview_style']===2);
		$preview->addChoice('Show full post',$values['preview_style'] ===3);


		$entpp = new Cgn_Form_ElementInput('entpp', 'Entries per page');
		$entpp->size = 4;
		$f->appendElement($entpp,$values['entpp']);


		$f->appendElement($preview);

		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_blog_id']);
//		var_dump($title);exit();
		/*
		$caption = new Cgn_Form_ElementInput('caption','Sub-title');
		$caption->size = 55;
		$f->appendElement($caption,$values['caption']);
		 */
		return $f;
	}
}
?>
