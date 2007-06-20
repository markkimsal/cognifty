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
	 * Is this content item a file?
	 */
	function isFile() {
		return ($this->dataItem->type == 'file');
	}

	/**
	 * Return true if this content is used as the given sub type
	 */
	function usedAs($subtype) {
		return ($this->dataItem->sub_type == $subtype);
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
		if ($this->dataItem->mime == 'text/wiki') {
			$article->setContentWiki($this->dataItem->content);
		} else {
			$article->dataItem->content = $this->dataItem->content;
		}
		$article->dataItem->description = $this->dataItem->description;
		$article->dataItem->link_text = $this->dataItem->link_text;
		$article->dataItem->cgn_content_version = $this->dataItem->version;
		$article->dataItem->edited_on = $this->dataItem->edited_on;
		$article->dataItem->created_on = $this->dataItem->created_on;
		$article->dataItem->published_on = $this->dataItem->published_on;

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
		$image->dataItem->cgn_content_version = $this->dataItem->version;
		$image->dataItem->edited_on = $this->dataItem->edited_on;
		$image->dataItem->created_on = $this->dataItem->created_on;
		$image->dataItem->published_on = $this->dataItem->published_on;

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
			$this->dataItem->setPrimarykey($id);
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
	var $dataItem;
	var $tableName = 'cgn_article_publish';
	var $pages = array();
	var $hasPages = false;


	/**
	 * Override constructor to load all pages
	 */
	function Cgn_Article($id=-1) {
		$this->dataItem = new Cgn_DataItem($this->tableName);
		if ($id > 0 ) {
			$this->dataItem->setPrimarykey($id);
			$this->dataItem->load();

			$page = new Cgn_DataItem('cgn_article_page');
			$page->andWhere('cgn_article_publish_id',$id);
			$this->pages = $page->find();
		}
	}

	function setContentWiki($wikiContent) {
		define('DOKU_BASE', cgn_appurl('main','content','image'));
		define('DOKU_CONF', dirname(__FILE__).'/../lib/dokuwiki/ ');

		include_once(dirname(__FILE__).'/../lib/wiki/lib_cgn_wiki.php');
		include_once(dirname(__FILE__).'/../lib/dokuwiki/parser.php');
		include_once(dirname(__FILE__).'/../lib/dokuwiki/lexer.php');
		include_once(dirname(__FILE__).'/../lib/dokuwiki/handler.php');
		include_once(dirname(__FILE__).'/../lib/dokuwiki/renderer.php');
		include_once(dirname(__FILE__).'/../lib/dokuwiki/xhtml.php');
		include_once(dirname(__FILE__).'/../lib/dokuwiki/parserutils.php');
		$pages = $this->separatePages($wikiContent);
		$info = array();
		if (is_array($pages) ) {
			//extract the first page into the main article
			$this->dataItem->content = p_render('xhtml',p_get_instructions($pages[0]->dataItem->content),$info);
			$this->hasPages = true;
			unset($pages[0]);
			//render each additional page's content
			foreach ($pages as $idx => $articlePage) {
				$articlePage->dataItem->content = p_render('xhtml',p_get_instructions($articlePage->dataItem->content),$info);
				$this->pages[] = $articlePage;
			}
			unset($pages);
		} else {
			$this->dataItem->content = p_render('xhtml',p_get_instructions($wikiContent),$info);
		}
	}

	/**
	 * Try to turn the content into multiple pages.
	 * The first page returned will be the content of the article
	 */
	function separatePages($wikiContent) {
		$breakLines = array();
		$pages = preg_match_all('/\{\{pagebreak:((.)+)\}\}/',$wikiContent,$breakLines);
		$pageArray = array();

		$lastTitle = '';
		foreach ($breakLines[0] as $idx => $breakLine) {
			@list($contents,$wikiContent) = explode($breakLine,$wikiContent);
			/*
			print_R($contents);
			echo "^^^ ..... \n";
			print_R($wikiContent);
			echo "___ ..... \n";
			*/
			$x = new Cgn_ArticlePage();
			$x->dataItem->content = $contents;
			$pageArray[] = $x;
		}
		$x = new Cgn_ArticlePage();
		$x->dataItem->content = $wikiContent;
		$pageArray[] = $x;

		//add in the titles
		foreach ($pageArray as $idx => $articlePage) {
			//the first page is part of the main article object
			if ($idx == 0) { continue; }
			$pageArray[$idx]->dataItem->title = $breakLines[1][$idx-1];
		}
		return $pageArray;
		//print_r($pageArray);exit();
	}

	/**
	 * override the save function to save all pages
	 */
	function save() {
		if (strlen($this->dataItem->link_text) < 1) {
			$this->setLinkText();
		}
		if (strlen($this->dataItem->cgn_guid) < 32) {
			$this->dataItem->cgn_guid = $this->contentItem->cgn_guid;
		}

		//__ FIXME __ use a library to do this... ?
		if ($this->hasPages) {
			$db = Cgn_Db_Connector::getHandle();
			$db->query("delete from cgn_article_page where cgn_article_publish_id = ".$this->dataItem->cgn_article_publish_id);
		}

		foreach($this->pages as $articlePage) {
			$articlePage->dataItem->cgn_article_publish_id = $this->dataItem->cgn_article_publish_id;
			$articlePage->save();
		}
		return $this->dataItem->save();
	}
}

class Cgn_ArticlePage extends Cgn_PublishedContent {
	var $dataItem;
	var $tableName = 'cgn_article_page';

	function save() {
		return $this->dataItem->save();
	}
}

/**
 * Help publish content to the blog entry table
 */
class Cgn_BlogEntry extends Cgn_PublishedContent {
	var $dataItem;
}


/**
 * Help publish content to the news item table
 */
class Cgn_NewsItem extends Cgn_PublishedContent {
	var $dataItem;
}


/**
 * Help publish content to the image table
 */
class Cgn_Image extends Cgn_PublishedContent {
	var $dataItem;
	var $tableName = 'cgn_image_publish';
}


/**
 * Help publish content to the generic asset table.
 * This is supposed to be things like flash plugins, PDFs, 
 * other embedded items, or things that need plugin players.
 */
class Cgn_Asset extends Cgn_PublishedContent {
	var $dataItem;
	var $tableName = 'cgn_file_publish';
}

?>
