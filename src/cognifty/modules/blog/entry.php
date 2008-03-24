<?php


class Cgn_Service_Blog_Entry extends Cgn_Service_Trusted {

	var $untrustLimit = 5;
	var $entry = null;

	function Cgn_Service_Blog_Entry () {
		$this->screenPosts();
//		$this->trustPlugin('requireCookie');
//		$this->trustPlugin('throttle',3);
		$this->trustPlugin('html',10);
//		$this->trustPlugin('secureForm');
	}

	/**
	 * Return an array to be placed into the bread crumb trail.
	 *
	 * @return 	Array 	list of strings.
	 */
	function getBreadCrumbs() {

		if ($this->entry != null) {
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
		// __TODO__
		//find a potential blog name in the URL or session

		$entry = new Cgn_DataItem('cgn_blog_entry_publish');
		$entry->load($entryId);
		$t['entryObj'] = $entry;
		$this->entry = $entry;

		$loader = new Cgn_DataItem('cgn_blog_comment');
		$loader->limit(10);
		$loader->andWhere('cgn_blog_entry_publish_id', $entryId);
		$loader->andWhere('approved','1');
		$loader->sort('posted_on','DESC');
		$t['commentList'] = $loader->find();

		//set the title of the blog
		$blog = new Cgn_DataItem('cgn_blog');
		$blog->load($entry->cgn_blog_id);

		Cgn_Template::setPageTitle($entry->title);
		Cgn_Template::setSiteName($blog->title);

		$t['permalink'] = cgn_appurl('blog','entry'). sprintf('%03d',$entry->cgn_blog_id).'/'.date('Y',$entry->posted_on).'/'.date('m',$entry->posted_on).'/'.$entry->link_text.'_'.sprintf('%05d',$entry->cgn_blog_entry_publish_id).'.html';
	}

	function commentEvent(&$req, &$t) {
		$entryId = $req->cleanInt('id');
		$user = $req->getUser();

		$text = $req->cleanHtml('comment');
		$comment = new Cgn_DataItem('cgn_blog_comment');
		$comment->cgn_blog_entry_publish_id = $entryId;
		$comment->user_id = $user->userId;
		if ($comment->user_id > 0) {
			$comment->user_name = $user->getUsername();
		} else {
			$comment->user_name = $req->cleanString('name');
		}
		$comment->user_ip_addr = $_SERVER['REMOTE_ADDR'];
		$comment->user_email   = $req->cleanString('email');
		$comment->user_url   = $req->cleanString('home_page');
		$comment->spam_rating   = $this->getSpamScore();
		if ($comment->spam_rating > 0) {
			$comment->approved   = 0;
		} else {
			$comment->approved   = 1;
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
}
?>
