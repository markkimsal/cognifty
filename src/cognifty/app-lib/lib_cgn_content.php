<?php

/**
 * Designed to work with the Cgn_DataItem class
 */
class Cgn_Content {
	var $dataItem;


	function Cgn_Content($id=-1) {
		$this->dataItem = new Cgn_DataItem('cgn_content');
		if ($id > 0 ) {
			$this->dataItem->cgn_content_id = $id;
			$this->dataItem->load($id);
		} else {
			//set a uniqid for this content
			$this->dataItem->cgn_guid =  cgn_uuid();
			$this->dataItem->version = 1;
		}

//		$this->dataItem->save();
		/*
		//save to publish table
		$this->dataItem->_table = 'cgn_content_publish';
		$this->dataItem->_pkey = 'cgn_content_publish_id';
		unset($cont->cgn_content_id);
		$this->dataItem->_isNew = true;
		$newId = $cont->save();
		 */
	}

	/**
	 * create or load a Cgn_Article object out of this content
	 */
	function asArticle() {
		if ($this->dataItem->cgn_content_id < 1) {
			trigger_error("Can't publish an unsaved content item");
			return false;
		}
		if ($this->dataItem->_isNew == true) {
			trigger_error("Can't publish an unsaved content item");
			return false;
		}
		//change this content as well
		$this->dataItem->sub_type = 'article';
		$this->dataItem->save();


		//__ FIXME __ use the data item for this search functionality
		$db = Cgn_Db_Connector::getHandle();
		$db->query("SELECT * FROM cgn_article_publish WHERE
			cgn_content_id = ".$this->dataItem->cgn_content_id);
		if ($db->nextRecord()) {
			$article = new Cgn_Article();
			$article->dataItem->row2Obj($db->record);
			$article->dataItem->_isNew = false;
		} else {
			$article = new Cgn_Article();

		}
		$article->dataItem->cgn_content_id = $this->dataItem->cgn_content_id;
		$article->dataItem->cgn_guid = $this->dataItem->cgn_guid;
		$article->dataItem->title = $this->dataItem->title;
		$article->dataItem->mime = $this->dataItem->mime;
		$article->dataItem->caption = $this->dataItem->caption;
		$article->dataItem->content = $this->dataItem->content;
		$article->dataItem->description = $this->dataItem->description;
		$article->dataItem->link_text = $this->dataItem->link_text;

		return $article;
	}


	/**
	 * create or load a Cgn_Image object out of this content
	 */
	function asImage() {
		if ($this->dataItem->cgn_content_id < 1) {
			trigger_error("Can't publish an unsaved content item");
			return false;
		}
		if ($this->dataItem->_isNew == true) {
			trigger_error("Can't publish an unsaved content item");
			return false;
		}
		//change this content as well
		$this->dataItem->sub_type = 'image';
		$this->dataItem->save();


		//__ FIXME __ use the data item for this search functionality
		$db = Cgn_Db_Connector::getHandle();
		$db->query("SELECT * FROM cgn_image_publish WHERE
			cgn_content_id = ".$this->dataItem->cgn_content_id);
		if ($db->nextRecord()) {
			$image = new Cgn_Article();
			$image->dataItem->row2Obj($db->record);
			$image->dataItem->_isNew = false;
			return $image;
		}

		$image = new Cgn_Image();
		$image->dataItem->cgn_content_id = $this->dataItem->cgn_content_id;
		$image->dataItem->cgn_guid = $this->dataItem->cgn_guid;
		$image->dataItem->title = $this->dataItem->title;
		$image->dataItem->mime = $this->dataItem->mime;
		$image->dataItem->caption = $this->dataItem->caption;
		$image->dataItem->binary = $this->dataItem->binary;
		$image->dataItem->description = $this->dataItem->description;
		$image->dataItem->link_text = $this->dataItem->link_text;

		return $image;
	}


	function save() {
		if (strlen($this->dataItem->link_text) < 1) {
			$this->setLinkText();
		}
		if (strlen($this->dataItem->cgn_guid) < 32) {
			$this->dataItem->cgn_guid = cgn_uuid();
		}
		return $this->dataItem->save();
	}


	function setLinkText($lt = '') {
		if ($lt == '') {
			$this->dataItem->link_text = str_replace(' ','_', $this->dataItem->title);
			$this->dataItem->link_text = str_replace(',','_', $this->dataItem->link_text);
			$this->dataItem->link_text = str_replace('\'','_', $this->dataItem->link_text);
			$this->dataItem->link_text = str_replace('"','_', $this->dataItem->link_text);
			$this->dataItem->link_text = str_replace('__','_', $this->dataItem->link_text);
		} else {
			$this->dataItem->link_text = $lt;
		}
	}
}



/**
 * Hold some base functions for all content items
 */
class Cgn_PublishedContent {
	var $contentItem;
	var $dataItem;
	var $tableName = '';

	function Cgn_PublishedContent($id=-1) {
		$this->dataItem = new Cgn_DataItem($this->tableName);
		if ($id > 0 ) {
			$this->dataItem->setPkey($id);
			$this->dataItem->load();
		}
	}

	function save() {
		if (strlen($this->dataItem->link_text) < 1) {
			$this->setLinkText();
		}
		if (strlen($this->dataItem->cgn_guid) < 32) {
			$this->dataItem->cgn_guid = $this->contentItem->cgn_guid;
		}
		return $this->dataItem->save();
	}

	function setLinkText($lt = '') {
		if ($lt == '') {
			$this->dataItem->link_text = str_replace(' ','_', $this->dataItem->title);
			$this->dataItem->link_text = str_replace(',','_', $this->dataItem->link_text);
			$this->dataItem->link_text = str_replace('\'','_', $this->dataItem->link_text);
			$this->dataItem->link_text = str_replace('"','_', $this->dataItem->link_text);
			$this->dataItem->link_text = str_replace('__','_', $this->dataItem->link_text);
		} else {
			$this->dataItem->link_text = $lt;
		}
	}
}



/**
 * Help publish content to the article table
 */
class Cgn_Article extends Cgn_PublishedContent {
	var $contentItem;
	var $dataItem;
	var $tableName = 'cgn_article_publish';
}


/**
 * Help publish content to the blog entry table
 */
class Cgn_BlogEntry extends Cgn_PublishedContent {
	var $contentItem;
}


/**
 * Help publish content to the news item table
 */
class Cgn_NewsItem extends Cgn_PublishedContent {
	var $contentItem;
}


/**
 * Help publish content to the image table
 */
class Cgn_Image extends Cgn_PublishedContent {
	var $contentItem;
	var $dataItem;
	var $tableName = 'cgn_image_publish';
}


/**
 * Help publish content to the generic asset table.
 * This is supposed to be things like flash plugins, PDFs, 
 * other embedded items, or things that need plugin players.
 */
class Cgn_Asset extends Cgn_PublishedContent {
	var $contentItem;
	var $dataItem;
	var $tableName = 'cgn_file_publish';
}

?>
