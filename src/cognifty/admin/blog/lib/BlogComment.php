<?

class Blog_BlogComment {

	var $dataItem;

	function Blog_BlogComment($id=-1) {
	}

	function loadFromBlogId($blogId, $limit=50, $start=0) {
		$finder = new Cgn_DataItem('cgn_blog_comment');
		$finder->andWhere('Tentry.cgn_blog_id', $blogId);
		$finder->hasOne('cgn_blog_entry_publish', 'cgn_blog_entry_publish_id', 'Tentry', 'cgn_blog_entry_publish_id');

		$finder->_cols = array('cgn_blog_comment.*','Tentry.cgn_blog_entry_publish_id');
		$finder->_rsltByPkey = true;
		$finder->limit($limit);
//		$finder->echoSelect();
		$comments = $finder->find();

		$result = array();
		foreach($comments as $key => $_obj) {
			$result[$key] = Blog_BlogComment::createObj($_obj);
		}
		return $result;
	}

	function loadAll() {
		$finder = new Cgn_DataItem('cgn_blog_comment');
		$finder->_rsltByPkey = true;
		$blogs = $finder->find();

		$userBlogs = array();
		foreach($blogs as $key => $_obj) {
			$userBlogs[$key] = Blog_BlogComment::createObj($_obj);
		}
		return $userBlogs;
	}


	function countPendingComments($blogId) {
		$finder = new Cgn_DataItem('cgn_blog_comment');
		$finder->andWhere('Tentry.cgn_blog_id', $blogId);
		$finder->andWhere('approved', 0);
		$finder->hasOne('cgn_blog_entry_publish', 'cgn_blog_entry_publish_id', 'Tentry', 'cgn_blog_entry_publish_id');

		$finder->_cols = array('count(cgn_blog_comment.cgn_blog_comment_id) as pending_count');
		$finder->_rsltByPkey = true;
		$result = $finder->find();
		return $result[0]->pending_count;
	}


	function createNew($title='',$subtype = 'web') {
		$x = new Blog_BlogComment();
		$x->setTitle($title);
		return $x;
	}

	function setBlogId($id) {
		$this->setAttribute('blog_id',$id, 'int');
	}

	function setAuthorId($id) {
		$this->setAttribute('author_id',$id, 'int');
	}

	/**
	 * Change the mimetype so that it is wiki.
	 */
	function setWiki() {
		$this->dataItem->mime = 'text/wiki';
	}

	/**
	 * get the primary key of the core content item
	 */
	function getCommentId() {
		return $this->dataItem->cgn_content_id;
	}

	/**
	 * Getter
	 */
	function getContent($sub=300) {
		return substr($this->dataItem->content,0, $sub);
	}

	/**
	 * Getter
	 */
	function getCaption() {
		return $this->dataItem->caption;
	}

	function getUsername() {
		return $this->dataItem->user_name;
		/*
		$user = Cgn_User::load($userId);
		if ($user != null) {
			return $user->username;
		} else {
			return '';
		}
		 */
	}

	/**
	 * make a new blog post given a content object.
	 */
	function createObj($dataItem) {
		$x = new Blog_BlogComment();
		$x->dataItem = $dataItem;
		return $x;
	}
}
?>
