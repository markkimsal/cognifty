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
		/*
		if ($id > 0) {
			$content = new Cgn_Content($id);
			$values = $content->dataItem->valuesAsArray();
			$t['version'] = $content->dataItem->version;
			$mime = $content->dataItem->mime;
			$values['mime'] = $mime;
			$values['edit'] = true;
		} else {
			$content = new Cgn_Content();
			$values['mime'] = $mime;
			$values['edit'] = false;
		}
		 */
		$t['form'] = $this->_loadEditForm($values);
	}

	function _loadEditForm($values=array()) {
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

	/**
	 * Create a new web record, a new content record, join them,
	 *  then forward to content editing.
	 */
	function newEvent(&$req, &$t) {
		$webPage = Cgn_Content_WebPage::createNew('New Page');

		$mime = $req->cleanString('mime');
		if ($mime == 'wiki') {
			$webPage->setWiki();
		}

		$newid = $webPage->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'content','edit','',array('id'=>$newid));
	}
//}

?>
