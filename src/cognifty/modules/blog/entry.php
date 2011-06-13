<?php

Cgn::loadModLibrary('Blog::UserBlog','admin');

class Cgn_Service_Blog_Entry extends Cgn_Service_Trusted {

	var $untrustLimit  = 3;
	var $entry         = NULL;
	var $usesConfig    = TRUE;
	var $dieOnFailure  = TRUE;

	function Cgn_Service_Blog_Entry () {
		$this->screenPosts();
//		$this->trustPlugin('requireCookie');
		$this->trustPlugin('throttle',10);
		$this->trustPlugin('html',10);
//		$this->trustPlugin('secureForm');
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

		if ($this->entry != NULL) {
			return array (
				cgn_applink('Blog','blog'),
				$this->entry->title
			);

		}
		return array('Blog');
	}


	/**
	 * Load the default blog and show some posts in it
	 */
	function mainEvent(&$req, &$t) {
		$entryId = $req->cleanInt('id');
		//find a potential blog name in the URL or session
		//permalink format is /blog_id/Year/mo/filename_postid.html
		if ($entryId == 0) {
			$filename = (string)$req->vars[3];
			$matches = array();
			//get the last digit, there could be others in the title.
			$pregResult = preg_match_all("/\d+/", $filename, $matches);
			$entryId = (int) array_pop($matches[0]);
			$blogId  = (int)$req->vars[0];
		}

		$entry = new Cgn_DataItem('cgn_blog_entry_publish');
		if (!$entry->load($entryId)) {
			return;
		}
		$t['entryObj'] = $entry;
		$this->entry = $entry;


//		$t['author'] = new Cgn_User('cgn_user');
		$t['author'] = Cgn_User::load($entry->get('author_id'));

		$loader = new Cgn_DataItem('cgn_blog_comment');
//		$loader->limit(10);
		$loader->andWhere('cgn_blog_entry_publish_id', $entryId);
		$loader->andWhere('approved','1');
		$loader->sort('posted_on','ASC');
		$t['commentList'] = $loader->find();

		//load blog settings
		$userBlog = new Blog_UserBlog($entry->cgn_blog_id);

		//set the title of the blog
		Cgn_Template::setPageTitle($entry->title);
		Cgn_Template::setSiteName($userBlog->getTitle());

		if ($userBlog->hasTagLine()) {
			Cgn_Template::setSiteTagLine($userBlog->getTagLine());
		}

		$t['permalink'] = cgn_appurl('blog','entry'). sprintf('%03d',$entry->cgn_blog_id).'/'.date('Y',$entry->posted_on).'/'.date('m',$entry->posted_on).'/'.$entry->link_text.'_'.sprintf('%05d',$entry->cgn_blog_entry_publish_id).'.html';

		//load social bookmarks
		$t['social_bookmarks'] = array();
		//TODO, make the limit dynamic
		for ($soc_x=1; $soc_x <= 5; $soc_x++) {
		if ($userBlog->getAttribute('social_'.$soc_x)->value === 'enabled') {
			$soc_url = $this->getConfig('social_'.$soc_x.'_url');
			$soc_url = str_replace('{title}', urlencode($entry->title), $soc_url);
			$soc_url = str_replace('{url}', urlencode($t['permalink']), $soc_url);
			$t['social_bookmarks'][] = 
				array(
					'title' => $this->getConfig('social_'.$soc_x.'_title'),
					'icon'  => $this->getConfig('social_'.$soc_x.'_icon'),
					'url'   => $soc_url
				);
		}
		}

		$u = $req->getUser();
		//load user values into template
		$t['userName'] = $u->getDisplayName();

		//load previous user values, if any
		$this->loadCookieValues($t);

		$values['cgn_blog_entry_publish_id'] = $entry->get('cgn_blog_entry_publish_id');
		if (!$u->isAnonymous()) {
			$values['user_name'] = $u->getDisplayName();
		}
		$t['commentForm'] = $this->_loadCommentForm($values);
	}


	function commentEvent(&$req, &$t) {
//		cgn::debug($this);exit();
		$entryId = $req->cleanInt('id');
		$user = $req->getUser();

		$text = $req->cleanHtml('comment');
		if (strlen($text) < 1) {
			$this->presenter = 'redirect';
			$t['url'] = cgn_appurl('blog','entry','', array('id'=>$entryId));
			return;
		}
		$entry = new Cgn_DataItem('cgn_blog_entry_publish');
		$entry->load($entryId);
		$secondsOld = time() - $entry->get('posted_on');
		if ($secondsOld > (86400 * 30)) {
			$user->addSessionMessage('Comments have been disabled for posts which are over one month old.');
			$this->presenter = 'redirect';
			$t['url'] = cgn_appurl('blog','entry','', array('id'=>$entryId));
			return;
		}


		$comment = new Cgn_DataItem('cgn_blog_comment');
		$comment->cgn_blog_entry_publish_id = $entryId;
		$comment->user_id = $user->userId;
		if ($comment->user_id > 0) {
			$comment->user_name = $user->getDisplayName();
			$this->setCommentCookie($req);
		} else {
			$comment->user_name = $req->cleanHtml('user_name');
			$this->setCommentCookie($req);
		}
		$comment->user_ip_addr = $_SERVER['REMOTE_ADDR'];
		$comment->user_email   = $req->cleanHtml('email');
		if (strlen($req->cleanHtml('home_page')) > 8) {
			$comment->user_url   = $req->cleanHtml('home_page');
		}
		$comment->spam_rating   = $this->getSpamScore();
		if ($comment->spam_rating > 0) {
			$comment->approved   = 0;
			$user->addSessionMessage('Your comment has been saved and will appear after it has been approved by a moderator.');
		} else {
			$comment->approved   = 1;
			$user->addSessionMessage('Thank you for your comments.');
		}

		$comment->source    = 'comment';
		$comment->content   = $text;
		$comment->posted_on = time();
		$comment->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_appurl('blog','entry','', array('id'=>$entryId));
	}

	public function tagEvent($req, &$t) {
		$tag = $req->cleanString(0);
		$tagLoader = new Cgn_DataItem('cgn_blog_entry_tag');
		$tagLoader->andWhere('link_text', $tag);
		$tags = $tagLoader->find();
		$idList = array();
		foreach ($tags as $_tag) {
			$idList[] = $_tag->get('cgn_blog_entry_tag_id');
		}

		$entryLoader = new Cgn_DataItem('cgn_blog_entry_publish');
		$entryLoader->hasOne('cgn_blog_entry_tag_link', 'cgn_blog_entry_id', 'Tlink', 'cgn_blog_entry_publish_id');
		$entryLoader->andWhere('Tlink.cgn_blog_entry_tag_id', $idList, 'IN');
//		$entryLoader->andWhere('cgn_blog_id', $userBlog->_item->cgn_blog_id);
		$entryLoader->sort('posted_on', 'DESC');

		$totalEntries = $entryLoader->getUnlimitedCount();
		$entpp = 10;
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

	function trackbackEvent(&$req, &$t) {
		$entryId = $req->cleanInt('id');
		$user = $req->getUser();

		$text = $req->cleanHtml('excerpt');
		if (strlen($text) < 1) {
			$this->presenter = 'self';
			return;
		}
		$comment = new Cgn_DataItem('cgn_blog_comment');
		$comment->cgn_blog_entry_publish_id = $entryId;
//		$comment->user_id = $user->userId;
		$comment->user_ip_addr = $_SERVER['REMOTE_ADDR'];
//		$comment->user_email   = $req->cleanString('email');
		$comment->user_name   = $req->cleanString('blog_name');
		$comment->user_url   = $req->cleanString('url');
		$comment->spam_rating   = $this->getSpamScore();
		if ($comment->spam_rating > 0) {
			$comment->approved   = 0;
		} else {
			$comment->approved   = 1;
		}

		$comment->source    = 'ping';
		$comment->content   = $text;
		$comment->posted_on = time();
		$comment->save();

		$this->presenter = 'self';
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


	function output() {
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>
    <response>
    <error>0</error>
    </response>";
	}

	/**
	 * Save the user's name and home page
	 */
	function setCommentCookie($req) {
		$values = array();
		$values['user_name'] = $req->cleanString('user_name');
		$values['home_page'] = $req->cleanString('home_page');
		setcookie('CGNBLOG', serialize($values), time()+3600*24*365, '/');
	}

	/**
	 * Load the user's name and home page from a cookie into the template
	 */
	function loadCookieValues(&$t) {
		//nothing
		$values = unserialize($_COOKIE['CGNBLOG']);
		if (!$values) return;

		if (isset($values['user_name'])) {
			$t['userName'] = $values['user_name'];
		}
		if (isset($values['home_page'])) {
			$t['homePage'] = $values['home_page'];
		}
		if (strpos($t['homePage'], 'http') !== 0) {
			$t['homePage'] = 'http://'.$t['homePage'];
		}
	}

	public function _loadCommentForm($values) {
		Cgn::loadLibrary('Form::lib_cgn_form');
		Cgn::loadLibrary('Html_Widget::lib_cgn_widget');
		$f = new Cgn_Form('blog_comment');

		$f->action = cgn_appurl('blog', 'entry', 'comment', array('id'=>$values['cgn_blog_entry_publish_id']));

		$f->label = 'Leave a Comment';
		$f->layout = new Cgn_Form_Layout_Dl();
		if (isset($values['user_name'])) {
			$f->appendElement(new Cgn_Form_ElementLabel('user_name_label', 'Your Name'), @$values['user_name']);
			$f->appendElement(new Cgn_Form_ElementHidden('user_name', 'Your Name'), @$values['user_name']);
		} else {
			$f->appendElement(new Cgn_Form_ElementInput('user_name', 'Your Name'), @$values['user_name']);
		}
		$f->appendElement(new Cgn_Form_ElementInput('home_page', 'Your Homepage'));
		$f->appendElement(new Cgn_Form_ElementText('comment'));
		return $f;
	}
}
?>
