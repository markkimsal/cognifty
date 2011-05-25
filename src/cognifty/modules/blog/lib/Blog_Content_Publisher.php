<?php

class Cgn_Content_Publisher_Blog extends Cgn_Content_Publisher_Plugin {

	public $codeName    = 'blog_entry';
	public $tableName   = 'cgn_blog_entry_publish';
	public $displayName = 'Blog Entry';

	public function getFormValue() {
		return $this->codeName;
	}

	public function getDisplayName() {
		return $this->displayName;
	}


	/**
	 * Called from Cgn_Content_Publisher
	 */
	public function loadPublished($id) {
		Cgn::loadModLibrary('Blog::BlogEntry','admin');

		$entry = new Blog_BlogEntry();
		$entry->dataItem->andWhere('cgn_content_id', $id);
		$entry->dataItem->load();
		return $entry;
		/*
		var_dump($entry);

		$db->query('select * from cgn_blog_entry_publish 
			WHERE cgn_content_id = '.$id);
		$db->nextRecord();
		$result = $db->record;
		if ($result) {
			$db->freeResult();
			Cgn::loadModLibrary('Blog::BlogEntry','admin');
			$published = new Blog_BlogEntry($result['cgn_blog_entry_publish_id']);
		}
		 */
	}

	/**
	 * Called from a signal in admin/content/publish
	 *
	 * @param Object $signal   Signal with source containing 'eventContentObj' object
	 * @return String          Url to redirect after publishing
	 */
	public function publishAsCustom($content) {
		Cgn::loadModLibrary('Blog::BlogEntry','admin');
		$content->dataItem->sub_type = $this->codeName;
		$previousPublish = $content->dataItem->get('published_on');
		$blog = Blog_BlogEntry::publishAsBlog($content);
		if ($blog === NULL) { return NULL; }
		$this->pingUpdateSites($blog, $previousPublish);
		return $blog;
	}

	public function getReturnUrl($blog) {
		return cgn_adminurl('blog', 'post', 'view', array('id'=>$blog->get('cgn_content_id'), 'blog_id'=>$blog->getBlogId()));
	}

	/**
	 * Initialize any core attributes to their default value.
	 */
	public function initDefaultAttributes($content) {
		$content->attribs['blog_id'] = new Cgn_DataItem('cgn_content_attrib');
		$content->attribs['blog_id']->code = 'blog_id';
		$content->attribs['blog_id']->type = 'int';
		$content->attribs['blog_id']->created_on = -1;
		$content->attribs['blog_id']->edited_on  = -1;
		$content->attribs['blog_id']->value      = NULL;
	}

	/**
	 * Send a ping to ping-o-matic
	 *
	 * @return  Bool  true if HTTP connection returned 200 OK status
	 */
	public function pingUpdateSites($blogEntry, $previousPublish) {
		Cgn::loadModLibrary('Blog::UserBlog','admin');
		Cgn::loadLibrary('Http::lib_cgn_http');

		//only ping once
		if ($previousPublish > 0 ) {
			return;
		}

		//load the blog
		$blogId = $blogEntry->get('cgn_blog_id');
		$userBlog = new Blog_UserBlog($blogId);
		$blogName = $userBlog->getTitle();
		$blogUrl  = cgn_appurl('blog');
		$rssUrl   = cgn_appurl('rss');

		$payload = '
<?xml version="1.0"?>
<methodCall>
<methodName>weblogUpdates.extendedPing</methodName>
<params>
<param><value><string>%s</string></value></param>
<param><value><string>%s</string></value></param>
<param><value><string>%s</string></value></param>
</params></methodCall>';
		$payload = sprintf($payload, htmlspecialchars($blogName), htmlspecialchars($blogUrl), htmlspecialchars($rssUrl));

		$con = new  Cgn_Http_Connection('rpc.pingomatic.com', '/');
		$con->setHeader('Content-Type', 'text/xml');
		$con->setBody($payload);
		$resp = $con->doPost();
		if (strstr($con->responseStatus, '200')) {
			return true;
		}
		return false;
	}
}
