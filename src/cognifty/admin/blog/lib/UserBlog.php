<?php

class Blog_UserBlog { /* extends Cgn_DataObject { */

	var $_item = null;

	function Blog_UserBlog($id=0) {
		$this->_item = new Cgn_DataItem('cgn_blog');
		if ($id > 0 ) {
			$this->_item->load($id);
		}
	}

	function loadAll() {
		$finder = new Cgn_DataItem('cgn_blog');
		$finder->_rsltByPkey = true;
		$blogs = $finder->find();

		$userBlogs = array();
		foreach($blogs as $key => $_obj) {
			$userBlogs[$key] = Blog_UserBlog::createObj($_obj);
		}
		return $userBlogs;
	}

	function createObj($dataItem) {
		$x = new Blog_UserBlog();
		$x->_item = $dataItem;
		return $x;
	}

	function getTitle() {
		return $this->_item->title;
	}

	function getBlogId() {
		return $this->_item->cgn_blog_id;
	}

	function getCaption() {
		return $this->_item->caption;
	}

	function getDescription() {
		return $this->_item->description;
	}

	function getUsername() {
		return $this->_item->owner_id;
	}
}

?>
