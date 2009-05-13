<?php
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_toolbar.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc.php');
include_once(CGN_LIB_PATH.'/lib_cgn_mvc_table.php');

include_once(CGN_LIB_PATH.'/form/lib_cgn_form.php');
Cgn::loadModLibrary('Content::Cgn_Content');

Cgn::loadModLibrary('Blog::UserBlog','admin');
Cgn::loadModLibrary('Blog::BlogComment','admin');

class Cgn_Service_Blog_Comment extends Cgn_Service_AdminCrud {

	var $displayName = 'Blog';
	var $db = NULL;

	function Cgn_Blog_Content_Comment() {
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
			$this->displayName .= cgn_adminlink($blogName, 'blog', 'post', '', array('blog_id'=>$blogId));
			$this->displayName .= '&nbsp;/&nbsp;';
			$this->displayName .= 'Comments';
		}
	}

	function mainEvent(&$req, &$t) {
		$blogId = $req->cleanInt('id');

		$finder = new Cgn_DataItem('cgn_blog_comment');
		$finder->andWhere('Tentry.cgn_blog_id', $blogId);
		$finder->andWhere('approved', 0);
		$finder->hasOne('cgn_blog_entry_publish', 'cgn_blog_entry_publish_id', 'Tentry', 'cgn_blog_entry_publish_id');

		$finder->_cols = array('cgn_blog_comment.*','Tentry.cgn_blog_entry_publish_id', 'Tentry.title');
		$finder->_rsltByPkey = TRUE;
		$finder->limit(50);
		$finder->sort('posted_on', 'ASC');
		$comments = $finder->find();
		$list = $this->makeCommentTable($comments, $blogId);
		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
		$t['menuPanel']->attribs['width']='100%';

		$parentBlog = new Blog_UserBlog($blogId);
		$this->makeBreadCrumbs($blogId, $parentBlog->getTitle());
	}

	/**
	 * Only show comments for the post id referred to in 'post_id'
	 */
	function entryEvent(&$req, &$t) {
		$blogId = $req->cleanInt('id');
		$postId = $req->cleanInt('post_id');

		$finder = new Cgn_DataItem('cgn_blog_comment');
		$finder->andWhere('Tentry.cgn_blog_id', $blogId);
		$finder->andWhere('cgn_content_id', $postId);
//		$finder->andWhere('approved', 0);
		$finder->hasOne('cgn_blog_entry_publish', 'cgn_blog_entry_publish_id', 'Tentry', 'cgn_blog_entry_publish_id');

		$finder->_cols = array('cgn_blog_comment.*','Tentry.cgn_blog_entry_publish_id', 'Tentry.title');
		$finder->_rsltByPkey = TRUE;
//		$finder->limit(50);
		$finder->sort('posted_on', 'ASC');
		$comments = $finder->find();
		$list = $this->makeCommentTable($comments, $blogId, $postId);
		$t['menuPanel'] = new Cgn_Mvc_AdminTableView($list);
		$t['menuPanel']->attribs['width']='100%';

		$parentBlog = new Blog_UserBlog($blogId);
		$this->makeBreadCrumbs($blogId, $parentBlog->getTitle());
	}


	function swapapproveEvent(&$req, &$t) {
		$blogId = $req->cleanInt('id');
		$postId = $req->cleanInt('post_id');

		$commentId = $req->cleanInt('comment_id');

		$comment = new Cgn_DataItem('cgn_blog_comment');
		$comment->load($commentId);
		$comment->approved = intval($comment->approved);
		if (!$comment->_isNew) {
			if (intval($comment->approved) == 1) {
				$comment->approved = 0;
			} else {
				$comment->approved = 1;
			}
			//$comment->approved = $comment->approved xor 1;
			$comment->save();
		}
//		cgn::debug($comment);exit();

		$attribs = array('id'=>$blogId);
		if ($postId > 0) {
			$attribs['post_id'] = $postId;
			$event = 'entry';
		} else {
			$event = '';
		}
		$this->presenter = 'redirect';
		$t['url'] = cgn_adminurl(
			'blog', 'comment', $event, $attribs
		);
	}

	function deleteEvent(&$req, &$t) {
		$id    = $req->cleanInt('comment_id');
		$blogId    = $req->cleanInt('id');
		$postId    = $req->cleanInt('post_id');

		$obj   = new Cgn_DataItem('cgn_blog_comment');
		$obj->load($id);
		if ($obj->_isNew) {
			//ERRCODE 581 missing input
			Cgn_ErrorStack::throwError("Object not found", 582);
			return false;
		}

		$trash = new Cgn_DataItem('cgn_obj_trash');
		$trash->table   = 'cgn_blog_comment';
		$trash->content = serialize($obj);
		$trash->title = 'blog comment';
		if ($obj->title) {
			$trash->title = $obj->title;
		} else if ($obj->display_name) {
			$trash->display_name = $obj->display_name;
		}

		$u = $req->getUser();
		$trash->user_id = $u->userId;
		$trash->deleted_on = time();
		$trashId = $trash->save();
		$t['trashId'] = $trashId;

		list($module,$service,$event) = explode('.', Cgn_ObjectStore::getObject('request://mse'));
		if ($trashId > 0 ) {
			$obj->delete();
			$t['message'] = "Object deleted.";
			//get the current MSE
			$req->getvars['undo_id'] = $trashId;
			$undoLink = cgn_adminlink('Undo?',$module,$service,'undo', $req->getvars);

			Cgn_ErrorStack::throwSessionMessage("Object deleted.  ".$undoLink);
		}
		$this->redirectHome($t);

		$attribs = array('id'=>$blogId);
		if ($postId > 0) {
			$attribs['post_id'] = $postId;
			$event = 'entry';
		} else {
			$event = '';
		}
		$t['url'] = cgn_adminurl('blog', 'comment', $event, $attribs);
	}

	function makeCommentTable(&$comments, $blogId, $postId = 0) {
		$list = new Cgn_Mvc_TableModel();

		//cut up the data into table data
		foreach($comments as $_rec) {
			$commentObj = Blog_BlogComment::createObj($_rec);

			$linkAttribs = array('comment_id'=>$commentObj->dataItem->cgn_blog_comment_id, 'id'=>$blogId);
			if ($postId > 0 ) {
				$linkAttribs['post_id'] = $postId;
			}

			if (intval($commentObj->dataItem->approved) == 1 ) {
				$approve = cgn_adminlink('hide','blog','comment','swapapprove',$linkAttribs);
			} else {
				$approve = cgn_adminlink('approve','blog','comment','swapapprove',$linkAttribs);
			}
			$delete = cgn_adminlink('delete','blog','comment','delete',$linkAttribs);
			$list->data[] = array(
				$commentObj->getContent(100),
//				."<br/>".
//				'<font size="-1">'.cgn_adminlink('view full comment','content','view','',array('id'=>$commentObj->dataItem->cgn_content_id)).'</font>',
				$_rec->user_ip_addr,
				$commentObj->getUsername(),
				$_rec->title,
				$approve,
				$delete
			);
		}
		$list->headers = array('Comment','IP', 'Author','Entry','Approve/Hide','Delete');
		return $list;
	}
}

?>
