<?php

Cgn::loadModLibrary('Blog::UserBlog','admin');

class Blog_Util {


	/**
	 * Echo an array of CGN data items to the screen.
	 *
	 * @param int number of posts to limit
	 */
	function showRecentPosts($limit=5) {
		$posts = Blog_Util::getRecentPosts($limit);
		foreach ($posts as $entry) {
			echo '<li><a href="'.cgn_appurl('blog','entry','', array('id'=>$entry->cgn_blog_entry_publish_id)).$entry->link_text.'">'.$entry->title.'</a></li>';
		}
	}

	/**
	 * Return an array of CGN data items
	 *
	 * @param int number of posts to limit
	 */
	function getRecentPosts($limit=5) {
		$limit = intval($limit);
		$entry = new Cgn_DataItem('cgn_blog_entry_publish');
		$entry->orderBy('posted_on DESC');
		$entry->limit($limit);
		return $entry->find();
	}

	/**
	 * Return an array of Blog_UserBlog objects, array key is blog_id
	 */
	function getAllBlogs() {
		$blogLoader = new Cgn_DataItem('cgn_blog');
		$blogLoader->orderBy('title', 'ASC');
		$blogList = $blogLoader->find();
		//wrap each data item in a real Blog_UserBlog object
		$userBlogs = array();
		foreach ($blogList as $_item) {
			$userBlogs[$_item->cgn_blog_id] = Blog_UserBlog::createObj($_item);
		}
		return $userBlogs;
	}
}
