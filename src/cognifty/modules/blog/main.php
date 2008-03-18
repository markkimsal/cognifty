<?php


class Cgn_Service_Blog_Main extends Cgn_Service {

	function Cgn_Service_Blog_Main () {

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
		$defaultBlog = $blogList[0];

		Cgn_Template::setPageTitle($defaultBlog->title);
		Cgn_Template::setSiteName($defaultBlog->title);

		$entryLoader = new Cgn_DataItem('cgn_blog_entry_publish');
		$entryLoader->andWhere('cgn_blog_id', $defaultBlog->cgn_blog_id);
		$entryLoader->sort('posted_on', 'DESC');
		$entryLoader->limit(10);
		$t['entries'] = $entryLoader->find();
	}
}

?>
