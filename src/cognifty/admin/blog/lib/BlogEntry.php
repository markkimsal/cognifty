<?php

class Blog_BlogEntry extends Cgn_PublishedContent {

	var $dataItem;
	var $tableName = 'cgn_blog_entry_publish';

	/**
	 * create or load a Cgn_Web object out of this content
	 */
	static function publishAsBlog($content) {
		if ($content->dataItem->cgn_content_id < 1) {
			trigger_error("Can't publish an unsaved content item");
			return false;
		}
		if ($content->dataItem->_isNew == true) {
			trigger_error("Can't publish an unsaved content item");
			return false;
		}

		//change this content as well
		$content->dataItem->sub_type = 'blog_entry';
		$content->dataItem->published_on = time();
		$content->dataItem->save();

		//__ FIXME __ use the data item for this search functionality
		$db = Cgn_Db_Connector::getHandle();
		$db->query("SELECT * FROM cgn_blog_entry_publish WHERE
			cgn_content_id = ".$content->dataItem->cgn_content_id);
		if ($db->nextRecord()) {
			$entry = new Blog_BlogEntry();
			$entry->dataItem->row2Obj($db->record);
			$entry->dataItem->_isNew = false;
		} else {
			$entry = new Blog_BlogEntry();
		}
		//load attributes
		$content->loadAllAttributes();

		$entry->dataItem->cgn_blog_id = $content->attribs['blog_id']->value;
		$entry->dataItem->author_id = $content->attribs['author_id']->value;
		$entry->dataItem->cgn_content_id = $content->dataItem->cgn_content_id;
		$entry->dataItem->title = $content->dataItem->title;
		$entry->dataItem->caption = $content->dataItem->caption;
		if ($content->dataItem->mime == 'text/wiki') {
			$entry->setContentWiki($content->dataItem->content);
			$entry->setExcerptWiki($content->dataItem->description);
		} else {
			$entry->dataItem->content = $content->dataItem->content;
			$entry->dataItem->excerpt = $content->dataItem->description;
		}
		$entry->dataItem->link_text = $content->dataItem->link_text;
		$entry->dataItem->cgn_content_version = $content->dataItem->version;
		$entry->dataItem->edited_on = $content->dataItem->edited_on;
//		$entry->dataItem->created_on = $content->dataItem->created_on;
//		$entry->dataItem->published_on = $content->dataItem->published_on;

		$entry->setPublished();

		$id = $entry->save();
		if ($id) {
			Blog_BlogEntry::_publishTags($entry, $content);
		}
		return $entry;
	}

	function setPublished() {
		if ($this->dataItem->posted_on < 1 ) {
			$this->dataItem->posted_on = time();
		}
	}

	function getBlogId() {
		return $this->dataItem->cgn_blog_id;
	}

	/**
	 * Push saved tags over to the front-end blog module.
	 */
	static function _publishTags($entry, $content) {
		$id = $entry->getPrimaryKey();

		$eraser = new Cgn_DataItem('cgn_blog_entry_tag_link');
		$eraser->andWhere('cgn_blog_entry_id', $id);
		$eraser->delete();


		$content->loadAllTags();

		$linkedIds = array();

		foreach ($content->tags as $_t) {
			$tagObj = new Cgn_DataItem('cgn_blog_entry_tag');
			$tagObj->set('link_text', $_t->get('link_text'));
			$tagObj->set('name', $_t->get('name'));
			$tagObj->loadExisting();
			if ($tagObj->_isNew) {
				$tagObj->save();
			}
			$newId = $tagObj->getPrimaryKey();
			$newLink = new Cgn_DataItem('cgn_blog_entry_tag_link');
			$newLink->set('cgn_blog_entry_id', $id);
			$newLink->set('cgn_blog_entry_tag_id', $newId);
			$newLink->set('created_on', $_t->get('created_on'));
			$newLink->save();
			unset($newLink);
			unset($tagObj);
		}
	}
}

?>
