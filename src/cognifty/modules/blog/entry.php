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
		$entry->load($entryId);
		$t['entryObj'] = $entry;
		$this->entry = $entry;

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
		for ($soc_x=1; $soc_x <= 4; $soc_x++) {
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

		//load previous user values, if any
		$user = $req->getUser();
		/*
		if ($user->userId > 0) {
		 */
			$this->loadCookieValues($t);
		/*
		}
		 */
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
		$comment = new Cgn_DataItem('cgn_blog_comment');
		$comment->cgn_blog_entry_publish_id = $entryId;
		$comment->user_id = $user->userId;
		if ($comment->user_id > 0) {
			$comment->user_name = $user->getDisplayName();
		} else {
			$comment->user_name = $req->cleanHtml('user_name');
			$this->setCommentCookie($req);
		}
		$comment->user_ip_addr = $_SERVER['REMOTE_ADDR'];
		$comment->user_email   = $req->cleanHtml('email');
		$comment->user_url   = $req->cleanHtml('home_page');
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
		$t['userName'] = $values['user_name'];
		$t['homePage'] = $values['home_page'];
		if (strpos($t['homePage'], 'http') !== 0) {
			$t['homePage'] = 'http://'.$t['homePage'];
		}
	}
}
?>
