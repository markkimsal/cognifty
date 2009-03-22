<?php

Cgn::loadModLibrary('Blog::UserBlog','admin');

class Cgn_Service_Blog_Main extends Cgn_Service {

	function Cgn_Service_Blog_Main () {

	}

	/**
	 * Add list of blog tags to the layout under content section "nav.blogtags"
	 */
	public function eventBefore($req, &$t) {
		Cgn::loadModLibrary('Blog::Blog_Layout');
		$myTemplate =& Cgn_Template::getDefaultHandler();
		$myTemplate->regSectionCallback( array('Cgn_Blog_Layout', 'showTagsAsLi'), 'nav.blogtags' );
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
	function mainEvent(&$req, &$t) {
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
		$prevStyle = $userBlog->getAttribute('preview_style')->value;
		$entpp = $userBlog->getAttribute('entpp')->value;
		if (! $entpp) {
			$entpp = 5;
		}

		Cgn_Template::setSiteName($userBlog->_item->title);
		if ($userBlog->hasTagLine()) {
			Cgn_Template::setPageTitle($userBlog->getTagLine());
			Cgn_Template::setSiteTagLine($userBlog->getTagLine());
		} else {
			Cgn_Template::setPageTitle($userBlog->_item->title);
		}

		//add title to the template
		$t['blogTitle'] = $userBlog->_item->title;
		$t['blogDescription'] = $userBlog->_item->description;

		$entryLoader = new Cgn_DataItem('cgn_blog_entry_publish');
		$entryLoader->andWhere('cgn_blog_id', $userBlog->_item->cgn_blog_id);
		$entryLoader->sort('posted_on', 'DESC');

		$totalEntries = $entryLoader->getUnlimitedCount();
		$currentPage = $req->cleanInt('page');
		if ($currentPage == 0) {
			$currentPage = 0;
		}
		$pageCrit = $this->getPageCriteria($currentPage, $entpp, $totalEntries);
		$t['nextlink'] = '';
		$t['prevlink'] = '';
		//flip the pages and URLs so that "previous" entries are next pages
		if($pageCrit['prev_page'] !== '') {
			$t['nextlink'] = cgn_appurl('blog','main','',array('page'=>$pageCrit['prev_page']));
		}
		if($pageCrit['next_page'] !== '') {
			$t['prevlink'] = cgn_appurl('blog','main','',array('page'=>$pageCrit['next_page']));
		}

		//set limit
		if ($currentPage > 0) {
			$entryLoader->limit($entpp, $currentPage-1);
		} else {
			$entryLoader->limit($entpp);
		}
		$t['entries'] = $entryLoader->find();

		$this->setContentPreview($t['entries'], $prevStyle);

		if ($prevStyle === 1) {
			$t['prevStyle'] = "partial";
		} else if ($prevStyle === 2) {
			$t['prevStyle'] = "partial";
		} else {
			$t['prevStyle'] = "full";
		}
	}

	/**
	 * Use the preview style int to select either the first 1000 characters of content, 
	 * or use the blog entry's excerpt field.
	 */
	public function setContentPreview(&$entries, $prevStyle) {
		if ($prevStyle === 1) {
			foreach ($entries as $idx => $entryObj) {
				$entryObj->content = substr(
					strip_tags($entryObj->content),
					0, 1000
				);
				$entries[$idx] = $entryObj;
			}
		} else if ($prevStyle === 2) {
			//use excerpt field
			foreach ($entries as $idx => $entryObj) {
				$entryObj->content = $entryObj->excerpt;
				$entries[$idx] = $entryObj;
			}
		}
	}

	/**
	 * Return an array of variables for next/prev page numbers.
	 *
	 * Pages start at 1
	 */
	public function getPageCriteria($currentPage, $rpp, $totalRec) {
		//pages start at 1
		if ($currentPage == 0) {
			$currentPage = 1;
		}
		$searchPages = array (
			'current_page'=>$currentPage,
			'next_page'=>$currentPage+1,
			'last_page'=>ceil($totalRec / $rpp),
			'prev_page'=>$currentPage-1,
			'first_page'=>'0'
		);
		//don't allow broken next/prev links
		if ($searchPages['next_page'] > $searchPages['last_page'] ) {
			$searchPages['next_page'] = '';
		}
		if ($searchPages['prev_page'] <= $searchPages['first_page'] ) {
			$searchPages['prev_page'] = '';
		}
		return $searchPages;
	}
}

?>
