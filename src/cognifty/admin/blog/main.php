<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

Cgn::loadModLibrary('Blog::UserBlog','admin');

class Cgn_Service_Blog_Main extends Cgn_Service_AdminCrud {

	var $displayName = 'Blog';
	var $db = NULL;
	var $tableName = 'cgn_blog';
	var $usesConfig = true;

	function Cgn_Blog_Content_Main () {
		$this->db = Cgn_Db_Connector::getHandle();
	}

	/**
	 * Alter the displayName variable to reflect breadcrumbs
	 */
	function makeBreadCrumbs($blogId=0, $blogName='', $entryId=0, $entryName='') {
		//if either blog or entry ID is changed, make the 
		//default display name word clickable
		if ($blogId > 0 || $entryId > 0) {
			$this->displayName = cgn_adminlink($this->displayName, 'blog');
		}
		if ($blogId > 0 ) {
			$this->displayName .= '&nbsp;/&nbsp;';
			$this->displayName .= $blogName;
		}
	}


	function mainEvent(&$req, &$t) {
		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('blog','main','edit'),"New Blog");
		$t['toolbar']->addButton($btn1);
		/*
		$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('blog','post','edit'),"New Blog Post");
		$t['toolbar']->addButton($btn2);
		 */


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
		$values['social_1']  = $blog->getAttribute('social_1')->value;
		$values['social_2']  = $blog->getAttribute('social_2')->value;
		$values['social_3']  = $blog->getAttribute('social_3')->value;
		$values['social_4']  = $blog->getAttribute('social_4')->value;
		$t['form'] = $this->_loadEditForm($values);

		$this->makeBreadCrumbs($id, $blog->getTitle());
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


		$bookmarks = $req->vars['book_marks'];
		$blog->setAttribute('social_1', 'disabled', 'string');
		$blog->setAttribute('social_2', 'disabled', 'string');
		$blog->setAttribute('social_3', 'disabled', 'string');
		$blog->setAttribute('social_4', 'disabled', 'string');
		foreach ($bookmarks as $socialId) {
			$socialId = intval($socialId);
			$blog->setAttribute('social_'.$socialId, 'enabled', 'string');
		}
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

		$desc = new Cgn_Form_ElementInput('description');
		$desc->size = 55;
		$f->appendElement($desc,$values['description']);

		$caption = new Cgn_Form_ElementInput('caption', 'Tag Line');
		$caption->size = 55;
		$f->appendElement($caption,$values['caption']);



		$check = new Cgn_Form_ElementCheck('is_default','Make This Blog Default?');
		$check->addChoice('Yes','1',$values['is_default']);

		$f->appendElement($check);



		//entries per page
		$entpp = new Cgn_Form_ElementInput('entpp', 'Entries per page');
		$entpp->size = 4;
		$f->appendElement($entpp,$values['entpp']);


		//preview style
		$preview = new Cgn_Form_ElementRadio('prev_style','Preview Style');

		$preview->addChoice('Use first 1000 characters',$values['preview_style']===1);
		$preview->addChoice('Use excerpt field',$values['preview_style']===2);
		$preview->addChoice('Show full post',$values['preview_style'] ===3);

		$f->appendElement($preview);

		//social bookmarks
		$social = new Cgn_Form_ElementCheck('book_marks','Social Bookmarks');

		$title1   = $this->getConfig('social_1_title');
		$title2   = $this->getConfig('social_2_title');
		$title3   = $this->getConfig('social_3_title');
		$title4   = $this->getConfig('social_4_title');
		$social->addChoice($title1,'01', $values['social_1']==='enabled');
		$social->addChoice($title2,'02', $values['social_2']==='enabled');
		$social->addChoice($title3,'03', $values['social_3']==='enabled');
		$social->addChoice($title4,'04', $values['social_4']==='enabled');

		$f->appendElement($social);

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
