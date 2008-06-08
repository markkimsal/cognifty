<?
Cgn::loadAppLibrary('Cgn_Content');

class Blog_BlogContent extends Cgn_Content {

	var $metaObj;
	var $commentCount = -1;

	function Blog_BlogContent($id=-1) {
		parent::Cgn_Content($id);
		$this->dataItem->sub_type = 'blog_entry';
		$this->dataItem->type     = 'text';
		$this->dataItem->mime = 'text/html';
		$this->metaObj = new Cgn_Content_MetaData();
	}

	/**
	 * Load Blog_BlogContent items from a parent blog ID
	 *
	 * @param int blogId   get blog posts from this id
	 */
	function loadFromBlogId($blogId) {
		$finder = new Cgn_DataItem('cgn_content');
		$finder->andWhere('sub_type', 'blog_entry');
		$finder->hasOne('cgn_blog_entry_publish', 'cgn_content_id', 'Tpub');
		$finder->hasOne('cgn_content_attrib', 'cgn_content_id', 'Tattr');
		$finder->andWhere('Tattr.code', 'blog_id');
		$finder->andWhere('Tattr.value', $blogId);
		$finder->orderBy('created_on DESC');
		$finder->_cols = array('cgn_content.*', 'Tpub.cgn_blog_entry_publish_id');
		$finder->_rsltByPkey = TRUE;
		$blogs = $finder->find();

		$userBlogs = array();
		foreach($blogs as $key => $_obj) {
			$userBlogs[$key] = Blog_BlogContent::createObj($_obj);
		}
		return $userBlogs;
	}

	function loadAll() {
		$finder = new Cgn_DataItem('cgn_content');
		$finder->_rsltByPkey = TRUE;
		$blogs = $finder->find();

		$userBlogs = array();
		foreach($blogs as $key => $_obj) {
			$userBlogs[$key] = Blog_BlogContent::createObj($_obj);
		}
		return $userBlogs;
	}

	function createNew($title='', $subtype = 'web') {
		$x = new Blog_BlogContent();
		$x->setTitle($title);
		return $x;
	}

	function setBlogId($id) {
		$this->setAttribute('blog_id', $id, 'int');
	}

	function getBlogId() {
		return intval($this->getAttribute('blog_id')->value);
	}

	function setAuthorId($id) {
		$this->setAttribute('author_id', $id, 'int');
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
	function getContentId() {
		return $this->dataItem->cgn_content_id;
	}

	/**
	 * Getter
	 */
	function getTitle() {
		return $this->dataItem->title;
	}

	/**
	 * Getter
	 */
	function getCaption() {
		return $this->dataItem->caption;
	}

	function getUsername() {
		$userId = $this->getAttribute('author_id')->value;
		$user = Cgn_User::load($userId);
		if ($user != null) {
			return $user->username;
		} else {
			return '';
		}
	}

	/**
	 * Return the comment count, or load it from the database.
	 */
	function getCommentCount() {
		if ($this->commentCount === -1) {
			$counter = new Cgn_DataItem('cgn_blog_comment');
			$counter->andWhere('cgn_blog_entry_publish_id', $this->dataItem->cgn_blog_entry_publish_id);
			$counter->_cols = array('COUNT(cgn_blog_comment.cgn_blog_comment_id) AS total_rec');
			$counter->load();
			$this->commentCount = (int)$counter->total_rec;
		}
		return $this->commentCount;
	}

	/**
	 * make a new blog post given a content object.
	 */
	function createObj($dataItem) {
		$x = new Blog_BlogContent();
		$x->dataItem = $dataItem;
		$x->dataItem->sub_type = 'blog_entry';
		return $x;
	}
}
?>
