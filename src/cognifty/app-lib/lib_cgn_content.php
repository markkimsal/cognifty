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
 * Utility class for publishing
 */
class Cgn_ContentPublisher {

	/**
	 * create or load a Cgn_Image object out of this content
	 */
	function publishAsImage($content) {
		if ($content->dataItem->cgn_content_id < 1) {
			trigger_error("Can't publish an unsaved content item");
			return false;
		}
		if ($content->dataItem->_isNew == true) {
			trigger_error("Can't publish an unsaved content item");
			return false;
		}
		//change this content as well
		$content->dataItem->sub_type = 'image';
		$content->dataItem->published_on = time();
		$content->dataItem->save();


		//__ FIXME __ use the data item for this search functionality
		$db = Cgn_Db_Connector::getHandle();
		$db->query("SELECT * FROM cgn_image_publish WHERE
			cgn_content_id = ".$content->dataItem->cgn_content_id);
		if ($db->nextRecord()) {
			$image = new Cgn_Image();
			$image->dataItem->row2Obj($db->record);
		} else {
			$image = new Cgn_Image();
			$image->dataItem->cgn_content_id = $content->dataItem->cgn_content_id;
			$image->dataItem->cgn_guid = $content->dataItem->cgn_guid;
		}

		$image->dataItem->title = $content->dataItem->title;
		$image->dataItem->mime = $content->dataItem->mime;

		if ($image->dataItem->mime == '') {
			$image->figureMime();
		}
		$image->dataItem->caption = $content->dataItem->caption;
		$image->dataItem->org_image = $content->dataItem->binary;
		$image->dataItem->description = $content->dataItem->description;
		$image->dataItem->filename = $content->dataItem->filename;
		$image->dataItem->link_text = $content->dataItem->link_text;
		$image->dataItem->cgn_content_version = $content->dataItem->version;
		$image->dataItem->edited_on = $content->dataItem->edited_on;
		$image->dataItem->created_on = $content->dataItem->created_on;
		$image->dataItem->published_on = $content->dataItem->published_on;

		$image->save();
		return $image;
	}

	/**
	 * create or load a Cgn_Article object out of this content
	 */
	function publishAsArticle($content) {
		if ($content->dataItem->cgn_content_id < 1) {
			trigger_error("Can't publish an unsaved content item");
			return false;
		}
		if ($content->dataItem->_isNew == true) {
			trigger_error("Can't publish an unsaved content item");
			return false;
		}
		//change this content as well
		$content->dataItem->sub_type = 'article';
		$content->dataItem->published_on = time();
		$content->dataItem->save();


		//__ FIXME __ use the data item for this search functionality
		$db = Cgn_Db_Connector::getHandle();
		$db->query("SELECT * FROM cgn_article_publish WHERE
			cgn_content_id = ".$content->dataItem->cgn_content_id);
		if ($db->nextRecord()) {
			$article = new Cgn_Article();
			$article->dataItem->row2Obj($db->record);
		} else {
			$article = new Cgn_Article();

		}
		$article->dataItem->cgn_content_id = $content->dataItem->cgn_content_id;
		$article->dataItem->cgn_guid = $content->dataItem->cgn_guid;
		$article->dataItem->title = $content->dataItem->title;
		$article->dataItem->mime = $content->dataItem->mime;
		$article->dataItem->caption = $content->dataItem->caption;
		if ($content->dataItem->mime == 'text/wiki') {
			$article->setContentWiki($content->dataItem->content);
		} else {
			$article->dataItem->content = $content->dataItem->content;
		}
		$article->dataItem->description = $content->dataItem->description;
		$article->dataItem->link_text = $content->dataItem->link_text;
		$article->dataItem->cgn_content_version = $content->dataItem->version;
		$article->dataItem->edited_on = $content->dataItem->edited_on;
		$article->dataItem->created_on = $content->dataItem->created_on;
		$article->dataItem->published_on = $content->dataItem->published_on;

		$article->save();
		return $article;
	}

	/**
	 * create or load a Cgn_Web object out of this content
	 */
	function publishAsWeb($content) {
		if ($content->dataItem->cgn_content_id < 1) {
			trigger_error("Can't publish an unsaved content item");
			return false;
		}
		if ($content->dataItem->_isNew == true) {
			trigger_error("Can't publish an unsaved content item");
			return false;
		}
		//change this content as well
		$content->dataItem->sub_type = 'web';
		$content->dataItem->published_on = time();
		$content->dataItem->save();


		//__ FIXME __ use the data item for this search functionality
		$db = Cgn_Db_Connector::getHandle();
		$db->query("SELECT * FROM cgn_web_publish WHERE
			cgn_content_id = ".$content->dataItem->cgn_content_id);
		if ($db->nextRecord()) {
			$web = new Cgn_WebPage();
			$web->dataItem->row2Obj($db->record);
			$web->dataItem->_isNew = false;
		} else {
			$web = new Cgn_WebPage();
		}

		$web->dataItem->cgn_content_id = $content->dataItem->cgn_content_id;
		$web->dataItem->cgn_guid = $content->dataItem->cgn_guid;
		$web->dataItem->title = $content->dataItem->title;
		$web->dataItem->mime = $content->dataItem->mime;
		$web->dataItem->caption = $content->dataItem->caption;
		if ($content->dataItem->mime == 'text/wiki') {
			$web->setContentWiki($content->dataItem->content);
		} else {
			$web->dataItem->content = $content->dataItem->content;
		}
		$web->dataItem->description = $content->dataItem->description;
		$web->dataItem->link_text = $content->dataItem->link_text;
		$web->dataItem->cgn_content_version = $content->dataItem->version;
		$web->dataItem->edited_on = $content->dataItem->edited_on;
		$web->dataItem->created_on = $content->dataItem->created_on;
		$web->dataItem->published_on = $content->dataItem->published_on;

		$web->save();
		return $web;
	}


	/**
	 * create or load a Cgn_Asset object out of this content
	 */
	function publishAsAsset($content) {
		if ($content->dataItem->cgn_content_id < 1) {
			trigger_error("Can't publish an unsaved content item");
			return false;
		}
		if ($content->dataItem->_isNew == true) {
			trigger_error("Can't publish an unsaved content item");
			return false;
		}
		//change this content as well
		$content->dataItem->sub_type = 'asset';
		$content->dataItem->published_on = time();
		$content->dataItem->save();


		//__ FIXME __ use the data item for this search functionality
		$db = Cgn_Db_Connector::getHandle();
		$db->query("SELECT * FROM cgn_file_publish WHERE
			cgn_content_id = ".$content->dataItem->cgn_content_id);
		if ($db->nextRecord()) {
			$asset = new Cgn_Asset();
			$asset->dataItem->row2Obj($db->record);
			$asset->dataItem->_isNew = false;
		} else {
			$asset = new Cgn_Asset();
		}

		$asset->dataItem->cgn_content_id = $content->dataItem->cgn_content_id;
		$asset->dataItem->cgn_guid = $content->dataItem->cgn_guid;
		$asset->dataItem->title = $content->dataItem->title;
		$asset->dataItem->mime = $content->dataItem->mime;
		$asset->dataItem->caption = $content->dataItem->caption;
		$asset->dataItem->binary = $content->dataItem->binary;
		$asset->dataItem->description = $content->dataItem->description;
		$asset->dataItem->link_text = $content->dataItem->link_text;
		$asset->dataItem->cgn_content_version = $content->dataItem->version;
		$asset->dataItem->edited_on = $content->dataItem->edited_on;
		$asset->dataItem->created_on = $content->dataItem->created_on;
		$asset->dataItem->published_on = $content->dataItem->published_on;

		$asset->save();
		return $asset;
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

	function getVersion() {
		return $this->dataItem->cgn_content_version;
	}

	/**
	 *  Hook for subclasses
	 */
	function presave() {
		return;
	}

	function save() {
		$this->presave();

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
	var $mimeType = '';

	/**
	 * Create web sized image and thumb nail
	 */
	function presave() {

		//rely on GD
		if (!function_exists('imagecreate')) { return; }
		if ($this->dataItem->mime == '') {
			$this->figureMime();
		} else {
			$this->mimeType = $this->dataItem->mime;
		}
		$tmpfname = tempnam('/tmp/', "cgnimg_");

		$si = fopen($tmpfname, "w+b");
		fwrite($si, $this->dataItem->org_image);   // write contents to file
		fclose($si);   // close file 
		switch ($this->mimeType) {
			case 'image/png':
			$orig = imageCreateFromPng($tmpfname);
			break;

			case 'image/jpeg':
			case 'image/jpg':
			$orig = imageCreateFromJpeg($tmpfname);
			break;

			case 'image/gif':
			$orig = imageCreateFromGif($tmpfname);
			break;
		}
		if (!$orig) { 
			return false;
		}
		$maxwidth = 580;
		$width  = imageSx($orig);
		$height = imageSy($orig);
		if ($width > $maxwidth) {
			//resize proportionately
			$ratio = $maxwidth / $width;
			$newwidth  = $maxwidth;
			$newheight = $height * $ratio;
		} else {
			$newwidth = $width;
			$newheight = $height;
		}
		$thumbwidth = 128;
		if ($width > $thumbwidth) {
			//resize proportionately
			$ratio = $thumbwidth / $width;
			$new2width  = $thumbwidth;
			$new2height = $height * $ratio;
		} else {
			$new2width = $width;
			$new2height = $height;
		}
		$webImage = imageCreateTrueColor($newwidth,$newheight);
		if (!$webImage) { die('no such handle');}
		imageCopyResampled(
			$webImage, $orig,
			0, 0,
			0, 0,
			$newwidth, $newheight,
			$width, $height);

		$thmImage = imageCreateTrueColor($new2width,$new2height);
		imageCopyResampled(
			$thmImage, $orig,
			0, 0,
			0, 0,
			$new2width, $new2height,
			$width, $height);

		ob_start(); // start a new output buffer
		switch ($this->mimeType) {
			case 'image/png':
			imagePng( $webImage, "", 90 );
			break;

			case 'image/jpeg':
			case 'image/jpg':
			imageJpeg( $webImage, "", 90 );
			break;

			case 'image/gif':
			imageGif( $thmImage, "", 90 );
			break;
		}
		$this->dataItem->web_image = ob_get_contents();
		ob_end_clean(); // stop this output buffer
		imageDestroy($webImage);

		ob_start(); // start a new output buffer
		switch ($this->mimeType) {
			case 'image/png':
			imagePng( $thmImage, "", 90 );
			break;

			case 'image/jpeg':
			case 'image/jpg':
			imageJpeg( $thmImage, "", 90 );
			break;

			case 'image/gif':
			imageGif( $thmImage, "", 90 );
			break;
		}
		$this->dataItem->thm_image = ob_get_contents();
		ob_end_clean(); // stop this output buffer
		imageDestroy($thmImage);

		unlink($tmpfname);
	}


	function figureMime() {
		if ($this->dataItem->mime != '') {
			$this->mimeType = $this->dataItem->mime;
			return;
		}

		$ext = strtolower(substr(
			$this->dataItem->filename,
			(strrpos($this->dataItem->filename,'.')+1)
			)
		);
		switch($ext) {
			case 'png':
				$this->mimeType = 'image/png';
				break;

			case 'jpeg':
			case 'jpg':
				$this->mimeType = 'image/jpeg';
				break;
			case 'gif':
				$this->mimeType = 'image/gif';
				break;
			case 'bmp':
				$this->mimeType = 'image/bmp';
				break;
			default:
				$this->mimeType = 'application/octet-stream';
		}

		$this->dataItem->mime = $this->mimeType;
	}

}


/**
 * Help publish content to the generic asset table.
 * This is supposed to be things like flash plugins, PDFs, 
 * other embedded items, or things that need plugin players.
 */
class Cgn_WebPage extends Cgn_PublishedContent {
	var $dataItem;
	var $tableName = 'cgn_web_publish';

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
		$this->dataItem->content = p_render('xhtml',p_get_instructions($wikiContent),$info);
	}
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
