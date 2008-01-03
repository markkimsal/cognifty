<?
Cgn::loadAppLibrary('Cgn_Content');

class Blog_BlogContent extends Cgn_Content {

	var $metaObj;

	function Blog_BlogContent($id=-1) {
		parent::Cgn_Content($id);
		$this->dataItem->sub_type = 'blog_entry';
		$this->dataItem->type     = 'text';
		$this->dataItem->mime = 'text/html';
		$this->metaObj = new Cgn_Content_MetaData();
	}

	function loadFromBlogId($blogId) {
		$finder = new Cgn_DataItem('cgn_content');
		$finder->andWhere('sub_type', 'blog_entry');
		$finder->hasOne('cgn_blog_entry_publish','cgn_content_id', 'Tpub');
		$finder->_cols = array('cgn_content.*','Tpub.cgn_blog_entry_publish_id');
		$finder->_rsltByPkey = true;
		$blogs = $finder->find();

		$userBlogs = array();
		foreach($blogs as $key => $_obj) {
			$userBlogs[$key] = Blog_BlogContent::createObj($_obj);
		}
		return $userBlogs;
	}

	function loadAll() {
		$finder = new Cgn_DataItem('cgn_content');
		$finder->_rsltByPkey = true;
		$blogs = $finder->find();

		$userBlogs = array();
		foreach($blogs as $key => $_obj) {
			$userBlogs[$key] = Blog_BlogContent::createObj($_obj);
		}
		return $userBlogs;
	}

	function createNew($title='',$subtype = 'web') {
		$x = new Blog_BlogContent();
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
	function getContentId() {
		return $this->dataItem->cgn_content_id;
	}

	/**
	 * Getter
	 */
	function getTitle() {
		return $this->dataItem->title;
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
