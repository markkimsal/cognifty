<?php


class Cgn_Service_Blog_Entry extends Cgn_Service_Trusted {

	var $untrustLimit = 1;

	function Cgn_Service_Blog_Entry () {
		$this->screenPosts();
		$this->trustPlugin('requireCookie');
		$this->trustPlugin('throttle',3);
		$this->trustPlugin('html',10);
//		$this->trustPlugin('secureForm');
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

		$loader = new Cgn_DataItem('cgn_blog_comment');
		$loader->limit(10);
		$loader->andWhere('cgn_blog_entry_publish_id', $entryId);
		$loader->sort('posted_on','DESC');
		$t['commentList'] = $loader->find();

		//set the title of the blog
		$blog = new Cgn_DataItem('cgn_blog');
		$blog->load($entry->cgn_blog_id);

		Cgn_Template::setPageTitle($blog->title);
		Cgn_Template::setSiteName($blog->title);
	}

	function commentEvent(&$req, &$t) {
		$entryId = $req->cleanInt('id');
		$user = $req->getUser();

		$text = $req->cleanHtml('comment');
		$comment = new Cgn_DataItem('cgn_blog_comment');
		$comment->cgn_blog_entry_publish_id = $entryId;
		$comment->user_id = $user->userId;
		$comment->user_ip_addr = $_SERVER['REMOTE_ADDR'];
		$comment->user_email   = $req->cleanString('email');
		$comment->user_name   = $req->cleanString('name');
		$comment->user_url   = $req->cleanString('home_page');
		$comment->spam_rating   = $this->getSpamScore();
		$comment->approved   = 1;
		$comment->content   = $text;
		$comment->posted_on = time();
		$comment->save();

		$this->presenter = 'redirect';
		$t['url'] = cgn_appurl('blog','entry','', array('id'=>$entryId));
	}
}

?>
