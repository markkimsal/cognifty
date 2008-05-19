<?php

Cgn::loadModLibrary('Blog::UserBlog','admin');

class Cgn_Service_Blog_Main extends Cgn_Service {

	function Cgn_Service_Blog_Main () {

	}

	/**
	 * Return an array to be placed into the bread crumb trail.
	 *
	 * @return 	Array 	list of strings.
	 */
	function getBreadCrumbs() {

		return array('Blog');
	}


	/**
	 * Load the default blog and show some posts in it
	 */
	function mainEvent(&$sys, &$t) {
		// __TODO__
		//find a potential blog name in the URL or session

		$loader = new Cgn_DataItem('cgn_blog');
		$loader->andWhere('is_default',1);
		$loader->_rsltByPkey = false;
		$blogList = $loader->find();

		// __TODO__
		// check for errors
		if (! isset($blogList[0]) || !is_object($blogList[0]) ) {
			Cgn_ErrorStack::throwError('No blog configured.',501);
			return;
		}
		$userBlog = new Blog_UserBlog(0);
		$userBlog->_item = $blogList[0];
		$prevStyle = $userBlog->getAttribute('preview_style');

		Cgn_Template::setPageTitle($userBlog->_item->title);
		Cgn_Template::setSiteName($userBlog->_item->title);

		$entryLoader = new Cgn_DataItem('cgn_blog_entry_publish');
		$entryLoader->andWhere('cgn_blog_id', $userBlog->_item->cgn_blog_id);
		$entryLoader->sort('posted_on', 'DESC');
		$entryLoader->limit(10);
		$t['entries'] = $entryLoader->find();
		//
		if ($prevStyle === 1) {
			foreach ($t['entries'] as $idx => $entryObj) {
				$entryObj->content = substr(
					strip_tags($entryObj->content),
					0, 1000
				);
				$t['entries'][$idx] = $entryObj;
			}
			$t['prevStyle'] = "partial";
		} else {
			$t['prevStyle'] = "full";
		}
	}
}

?>
