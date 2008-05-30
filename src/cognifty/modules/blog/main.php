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

		Cgn_Template::setPageTitle($userBlog->_item->title);
		Cgn_Template::setSiteName($userBlog->_item->title);

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
		} else if ($prevStyle === 2) {
			//use excerpt field
			foreach ($t['entries'] as $idx => $entryObj) {
				$entryObj->content = $entryObj->excerpt;
				$t['entries'][$idx] = $entryObj;
			}
			$t['prevStyle'] = "partial";
		} else {
			$t['prevStyle'] = "full";
		}
	}

	public function getPageCriteria($currentPage, $rpp, $totalRec) {
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
