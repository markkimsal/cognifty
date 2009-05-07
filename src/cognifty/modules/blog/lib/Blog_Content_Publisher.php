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
		$blog = Blog_BlogEntry::publishAsBlog($content);
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
}
