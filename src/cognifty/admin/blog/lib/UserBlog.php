<?php

class Blog_UserBlog { /* extends Cgn_DataObject { */

	var $_item = NULL;

	var $attribs     = array();
	var $_attribsLoaded = false;

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

	/**
	 * Check whether or not the caption field has characters in it
	 *
	 * @return bool true if the strlen of caption is greater than zero
	 */
	function hasTagLine() {
		return (bool)(strlen($this->_item->caption) > 0);
	}

	function getTagLine() {
		return $this->_item->caption;
	}


	function setAttribute($name, $val, $type = 'string') {
		if (! $this->_attribsLoaded) {
			$this->loadAllAttributes();
		}
		if (!isset($this->attribs[$name]) ) {
			$this->attribs[$name] = new Cgn_DataItem('cgn_blog_attrib');
			$this->attribs[$name]->code = $name;
			$this->attribs[$name]->type = $type;
			$this->attribs[$name]->created_on = time();
		}
		$this->attribs[$name]->edited_on = time();
		$this->attribs[$name]->value = $val;
		return true;
	}

	/**
	 * Load all attributes if they're not loaded
	 */
	function getAttribute($name) {
		if ( count($this->attribs) == 0) {
			//try to load all attribs
			$this->loadAllAttributes();
		}
		if (isset($this->attribs[$name]) ) {
			return $this->attribs[$name];
		}
		return false;
	}

	function loadAllAttributes() {
		$finder = new Cgn_DataItem('cgn_blog_attrib');
		$finder->andWhere('cgn_blog_id', $this->_item->cgn_blog_id);
		$attribs = $finder->find();
		foreach ($attribs as $_attrib) {
			$name = $_attrib->code;
			if ($_attrib->type === 'int') {
				$_attrib->value = (int)$_attrib->value;
			}
			$this->attribs[$name] = $_attrib;
		}
		$this->_attribsLoaded = true;
		return true;
	}

	function saveAttributes() {
		$ret = true;
		foreach ($this->attribs as $_attrib) {
			$_attrib->cgn_blog_id = $this->_item->cgn_blog_id;
			$ret = ($_attrib->save() > 0) || $ret;
		}
		return $ret;
	}
}
?>
