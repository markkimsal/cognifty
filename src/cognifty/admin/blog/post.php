<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

Cgn::loadModLibrary('Content::Cgn_Content');
Cgn::loadLibrary('Form::Lib_Cgn_Form');
Cgn::loadModLibrary('Content::Cgn_WikiLayout');

Cgn::loadModLibrary('Blog::UserBlog','admin');
Cgn::loadModLibrary('Blog::BlogContent','admin');
Cgn::loadModLibrary('Blog::BlogComment','admin');

class Cgn_Service_Blog_Post extends Cgn_Service_AdminCrud {

	var $displayName = 'Blog';
	var $db = NULL;

	function Cgn_Blog_Content_Post () {
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

		if ($entryId > 0 && $blogId > 0) {
			$this->displayName .= '&nbsp;/&nbsp;';
			$this->displayName .= cgn_adminlink($blogName, 'blog', 'post','', array('blog_id'=>$blogId));

			$this->displayName .= '&nbsp;/&nbsp;';
			$this->displayName .= $entryName;
		} else if ($blogId > 0 ) {
			$this->displayName .= '&nbsp;/&nbsp;';
			$this->displayName .= $blogName;
		}

	}


	function mainEvent(&$req, &$t) {
		$blogId = $req->cleanInt('blog_id');

		$commentCount = Blog_BlogComment::countPendingComments($blogId);

		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('blog','main','edit'),"New Blog");
		$t['toolbar']->addButton($btn1);
		$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('blog','post','edit', array('blog_id'=>$blogId, 'm'=>'wiki')),"New Blog Post (Wiki)");
		$t['toolbar']->addButton($btn2);
		$btn4 = new Cgn_HtmlWidget_Button(cgn_adminurl('blog','post','edit', array('blog_id'=>$blogId, 'm'=>'html')),"New Blog Post");
		$t['toolbar']->addButton($btn4);
		$btn3 = new Cgn_HtmlWidget_Button(cgn_adminurl('blog','comment','', array('id'=>$blogId) ),"Approve Comments ($commentCount)");
		$t['toolbar']->addButton($btn3);

		$userBlogs = Blog_BlogContent::loadFromBlogId($blogId, TRUE);
		$parentBlog = new Blog_UserBlog($blogId);

		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		foreach($userBlogs as $_entryContent) {
			if ($_entryContent->dataItem->published_on ) {
				$delLink = cgn_adminlink('unpublish','blog','post','del',array('cgn_content_id'=>$_entryContent->dataItem->cgn_content_id, 'table'=>'cgn_blog_entry_publish', 'key'=>'cgn_content', 'blog_id'=>$blogId));
			} else {
				$delLink = cgn_adminlink('delete','blog','post','del',array('cgn_content_id'=>$_entryContent->dataItem->cgn_content_id, 'table'=>'cgn_content', 'blog_id'=>$blogId));
			}

			$commentCount = $_entryContent->getCommentCount();
			if ($commentCount > 0 ) {
				$commentLink = cgn_adminlink(
					$_entryContent->getCommentCount(),
					'blog','comment','entry',
					array('id'=>$blogId, 'post_id'=>$_entryContent->getContentId())
				);
			} else {
				$commentLink  = '0';
			}
			$list->data[] = array(
				cgn_adminlink($_entryContent->getTitle(),'blog','post','view',array('id'=>$_entryContent->dataItem->cgn_content_id)),
				$_entryContent->getCaption(),
				$_entryContent->getUsername(),
				$commentLink,
				$delLink 
			);
		}
		$list->headers = array('Title','Tag-Line','Author','Comments','Delete');

		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
		$this->makeBreadCrumbs($blogId, $parentBlog->getTitle());
	}

	/**
	 * Create a new web record, a new content record, join them,
	 *  then forward to content editing.
	 */
	function editEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$blogId = $req->cleanInt('blog_id');
		if ($blogId < 1) {
			$blogId = 1;
		}

		$mime = $req->cleanString('m');
		$values = array();
		if ($id > 0) {
			$content = new Cgn_Content($id);
			$values = $content->dataItem->valuesAsArray();
			$mime = $content->dataItem->mime;
			$values['mime'] = $mime;
			$values['edit'] = true;
			$values['blog_id'] = $content->getAttribute('blog_id')->value;
		} else {
			$content = new Cgn_Content();
			$values['mime'] = $mime;
			$values['edit'] = false;
			$values['blog_id'] = $blogId;
		}

		$t['form'] = $this->_loadContentForm($values);
		$t['form']->layout = new Cgn_Form_WikiLayout();
		$t['form']->layout->mime = $mime;


		$parentBlog = new Blog_UserBlog($blogId);
		$this->makeBreadCrumbs($blogId, $parentBlog->getTitle(), $id, $content->dataItem->title);
	}

	/**
	 * Create a new content record, use-as blogpost
	 */
	function saveEvent(&$req, &$t) {
		$id = $req->cleanInt('id');
		$blogId = $req->cleanInt('blog_id');

		if ($id > 0 ) {
			$post = new Blog_BlogContent($id);
		} else {
			$post = new Blog_BlogContent();
		}
		$mime = $req->cleanString('mime');
		if (strpos($mime, 'wiki') !== FALSE) {
			$post->setMime('text/wiki');
		}

		$post->setLinkText($req->cleanString('link_text'));
		$post->setTitle($req->cleanString('title'));
		$post->setContent($req->cleanMultiLine('content'));
		//excerpt is description
		$post->setDescription($req->cleanMultiLine('content_ex'));
		$post->setCaption($req->cleanString('caption'));
		$post->setBlogId($blogId);
		$post->setAuthorId($req->getUser()->userId);
		$newid = $post->save();
		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'blog', 'post', 'view', array('id'=>$newid, 'blog_id'=>$blogId));
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
		$textarea->excerpt = $values['description'];
		$f->appendElement($textarea,$values['content']);
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$values['cgn_content_id']);
		$f->appendElement(new Cgn_Form_ElementHidden('mime'),$values['mime']);
		$f->appendElement(new Cgn_Form_ElementHidden('blog_id'),$values['blog_id']);

		return $f;
	}



	function viewEvent(&$req, &$t) {

		$id = $req->cleanInt('id');
		$t['content'] = new Cgn_DataItem('cgn_content');
		$t['content']->load($id);

		//toolbar
		$t['toolbar'] = new Cgn_HtmlWidget_Toolbar();
		
		$btn1 = new Cgn_HtmlWidget_Button(cgn_adminurl('blog','post','edit', array('id'=>$t['content']->cgn_content_id)),"Edit");
		$t['toolbar']->addButton($btn1);

		if ($t['content']->sub_type != '') { 
			$btn2 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','publish','',array('id'=>$t['content']->cgn_content_id)),"Publish");
			$t['toolbar']->addButton($btn2);
		}

		// Cgn::debug($t['toolbar']);

		$contentObj = new Cgn_Content($id);

		//load attributes from the database
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
				$btn4 = new Cgn_HtmlWidget_Button(cgn_adminurl('content','edit','unpublish', array('cgn_'.$sub_type.'_publish_id'=>$publishId, 'table'=>'cgn_'.$sub_type.'_publish')),"Unpublish");
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
	}


	/**
	 * Remove published_on field from cgn_content after delEvents are run
	 */
	function eventAfter(&$req, &$t) {
		if ($this->eventName === 'del') {
			$contentId = (int)$req->vars['cgn_content_id'];
			if ($contentId > 0 ) {
				$content = new Cgn_DataItem('cgn_content');
				$content->_cols = array('cgn_content_id', 'published_on');
				$content->load($contentId);
				$content->_nuls = array('published_on');
				$content->published_on = NULL;
				$content->save();
			}
		}
	}

	function _loadAttributesForm($values=array(), $id) {
		Cgn::loadModLibrary('Blog::UserBlog', 'admin');

		$f = new Cgn_FormAdmin('content_attr');
		$f->label = 'Set attributes for this Blog Post.';

		$allBlogs = Blog_UserBlog::loadAll();

		$radio = new Cgn_Form_ElementRadio('blog_id', 'Which Blog?');
		foreach ($allBlogs as $_b) {
			$radio->addChoice($_b->_item->get('title'), $_b->_item->get('cgn_blog_id'), ($values['blog_id']->value == $_b->_item->get('cgn_blog_id')));
		}
		$f->action = cgn_adminurl('content','edit','saveAttr');
		$f->appendElement(new Cgn_Form_ElementHidden('id'),$id);

		$f->appendElement($radio);
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
