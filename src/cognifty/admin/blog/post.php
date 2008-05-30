<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
include_once(CGN_SYS_PATH.'/app-lib/form/wikilayout.php');
include_once(CGN_SYS_PATH.'/app-lib/lib_cgn_content.php');

Cgn::loadModLibrary('Blog::UserBlog','admin');
Cgn::loadModLibrary('Blog::BlogContent','admin');
Cgn::loadModLibrary('Blog::BlogComment','admin');

class Cgn_Service_Blog_Post extends Cgn_Service_AdminCrud {

	var $displayName = 'Blog';
	var $db = null;

	function Cgn_Blog_Content_Post () {
		$this->db = Cgn_Db_Connector::getHandle();
	}


	function mainEvent(&$req, &$t) {
		$blogId = $req->cleanInt('blog_id');

		$commentCount = Blog_BlogComment::countPendingComments($blogId);

		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('blog','main','new'),"New Blog");
		$t['toolbar']->addButton($btn1);
		$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('blog','post','edit', array('mime'=>'wiki')),"New Blog Post");
		$t['toolbar']->addButton($btn2);
		$btn3 = new Cgn_HtmlWidget_Button(cgn_adminurl('blog','comment','', array('id'=>$blogId) ),"Approve Comments ($commentCount)");
		$t['toolbar']->addButton($btn3);


		$userBlogs = Blog_BlogContent::loadFromBlogId($blogId);

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		foreach($userBlogs as $_blog) {
			if ($_blog->dataItem->cgn_blog_entry_publish_id ) {
				$delLink = cgn_adminlink('unpublish','blog','post','del',array('cgn_content_id'=>$_blog->dataItem->cgn_content_id, 'table'=>'cgn_blog_entry_publish', 'key'=>'cgn_content'));
			} else {
				$delLink = cgn_adminlink('delete','blog','post','del',array('cgn_content_id'=>$_blog->dataItem->cgn_content_id, 'table'=>'cgn_content'));
			}
			$list->data[] = array(
				cgn_adminlink($_blog->getTitle(),'content','view','',array('id'=>$_blog->dataItem->cgn_content_id)),
				$_blog->getCaption(),
				$_blog->getUsername(),
				 cgn_adminlink('edit','blog','post','edit',array('id'=>$_blog->dataItem->cgn_content_id)),
				 $delLink 
			);
		}
		$list->headers = array('Title','Tag-Line','Author','Edit','Delete');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);

	}

	/**
	 * Create a new web record, a new content record, join them,
	 *  then forward to content editing.
	 */
	function editEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$mime = $req->cleanString('m');
		$values = array();
		if ($id > 0) {
			$content = new Cgn_Content($id);
			$values = $content->dataItem->valuesAsArray();
			$mime = $content->dataItem->mime;
			$values['mime'] = $mime;
			$values['edit'] = true;
		} else {
			$content = new Cgn_Content();
			$values['mime'] = $mime;
			$values['edit'] = false;
		}

		$t['form'] = $this->_loadContentForm($values);
		$t['form']->layout = new Cgn_Form_WikiLayout();
		$t['form']->layout->mime = $mime;
	}

	/**
	 * Create a new content record, use-as blogpost
	 */
	function saveEvent(&$req, &$t) {
		$id = $req->cleanInt('id');

		if ($id > 0 ) {
			$post = new Blog_BlogContent($id);
		} else {
			$post = new Blog_BlogContent();
		}
		$post->setTitle($req->cleanString('title'));
		$post->setContent($req->cleanString('content'));
		//exceprt is description
		$post->setDescription($req->cleanString('content_ex'));
		$post->setCaption($req->cleanString('caption'));
		$post->setBlogId(1);
		$post->setAuthorId($req->getUser()->userId);
		$newid = $post->save();
		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'blog','post','',array('id'=>$newid));

	}


	/**
	 * Auto-generate a form using the form library
	 */
	function _loadContentForm($values=array()) {
		include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
		include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Cgn_Form('content_01');
		$f->width="auto";
		$f->action = cgn_adminurl('blog','post','save');
		$f->label = '';
		$title = new Cgn_Form_ElementInput('title');
		$title->size = 55;

		$f->appendElement($title,$values['title']);
		$caption = new Cgn_Form_ElementInput('caption','Sub-title');
		$caption->size = 55;
		$f->appendElement($caption,$values['caption']);

//		if ($values['edit'] == true) {
			$link = new Cgn_Form_ElementInput('link_text','Link');
			$link->size = 55;
			$f->appendElement($link,$values['link_text']);
//		}


		$version = new Cgn_Form_ElementLabel('version','Version', $values['version']);
		$f->appendElement($version);

		$textarea = new Cgn_Form_ElementText('content','Content', 35, 90);
		$f->appendElement($textarea,$values['content']);
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_content_id']);
		$f->appendElement(new Cgn_Form_ElementHidden('mime'),$values['mime']);

		return $f;
	}

}

?>
