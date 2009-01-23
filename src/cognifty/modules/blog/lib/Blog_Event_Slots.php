<?php

class Blog_Event_Slots {


	/**
	 * Called from the signal manager
	 */
	public function loadPublished($signal) {
		Cgn::loadModLibrary('Blog::BlogEntry','admin');

		$src = $signal->getSource();
		if (!is_object($src)) {
			return NULL;
		}

		if (isset($src->id)) {
			$id = $src->id;
		}
		
		$entry = new Blog_BlogEntry();
		$entry->dataItem->andWhere('cgn_content_id', $id);
		$entry->dataItem->load();
		return $entry;
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
	}

	/**
	 * Called from a signal in admin/content/publish
	 *
	 * @param Object $signal   Signal with source containing 'eventContentObj' object
	 * @return String          Url to redirect after publishing
	 */
	public function publishBlog($signal) {
		$src = $signal->getSource();
		$content = $src->eventContentObj;
		Cgn::loadModLibrary('Blog::BlogEntry','admin');
		$blog = Blog_BlogEntry::publishAsBlog($content);
		return cgn_adminurl(
			'blog','post', '', array('blog_id'=>$blog->getBlogId())
		);
	}
}
