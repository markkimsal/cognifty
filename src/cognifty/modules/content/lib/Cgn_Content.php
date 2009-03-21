<?php

/**
 * Designed to work with the Cgn_DataItem class
 */
class Cgn_Content {

	public $dataItem;
	public $link_text   = '';
	public $sub_type    = '';
	public $type        = '';
	public $created_on  = '';
	public $version     = 1;
	public $attribs     = array();
	public $tags        = array();

	public $_attribsLoaded = FALSE;
	public $_tagsLoaded = FALSE;

	function Cgn_Content($id=-1) {
		$this->dataItem = new Cgn_DataItem('cgn_content');

		if ($id > 0 ) {
			$this->dataItem->cgn_content_id = $id;
			$this->dataItem->load($id);
		} else {
			//set a uniqid for this content
			$this->_initDataItem();
		}
		//$this->init();
	}


	/**
	 * Sets some default parameters
	 */
	function _initDataItem() {
		$this->dataItem->cgn_guid =  cgn_uuid();
		$this->dataItem->version = 0;
		$this->dataItem->created_on = time();
		$this->dataItem->type = '';
		$this->dataItem->sub_type = '';
		$this->dataItem->link_text = '';
		$this->dataItem->title = '';
		$this->dataItem->edited_on = NULL;
	}

	/**
	 * Setter
	 */
	function setType($t) {
		$this->dataItem->type = $t;
	}

	/**
	 * Setter
	 * Update the "link_text" property as well.
	 */
	function setTitle($t) {
		$this->dataItem->title = $t;
		if ($this->dataItem->link_text == '') {
			$this->setLinkText($t);
		}
	}

	function setCaption($c) {
		$this->dataItem->caption = $c;
	}

	/**
	 * Setter
	 */
	function setMime($m) {
		$this->dataItem->mime = $m;
	}

	/**
	 * Setter
	 */
	function setContent(&$c) {
		$this->dataItem->content = $c;
	}

	/**
	 * Setter
	 */
	function setDescription(&$d) {
		$this->dataItem->description = $d;
	}

	/**
	 * Getter
	 *
	 * Return cgn_content_id
	 */
	function getId() {
		return $this->dataItem->cgn_content_id;
	}

	/**
	 * Update some basic vars everytime content is edited
	 */
	function _editBump() {
		$this->dataItem->edited_on = time();
		$this->dataItem->version = $this->dataItem->version +1;
	}

	/**
	 * Only set the publised on time once.
	 *
	 * @param int $time unix timestamp of published date
	 */
	function setPublishedOn($time=NULL) {
		if ($time  === NULL) {
			$time = time();
		}
		if ($this->dataItem->published_on == 0 ||
			$this->dataItem->published_on == '') {
				$this->dataItem->published_on = $time;
		}
	}

	/**
	 * Is this content item a file?
	 */
	function isFile() {
		return ($this->dataItem->type == 'file');
	}

	/**
	 * Is this content item a text item?
	 */
	function isText() {
		return ($this->dataItem->type == 'text');
	}

	/**
	 * Return TRUE if this content is used as the given sub type
	 */
	function usedAs($subtype) {
		return ($this->dataItem->sub_type == $subtype);
	}

	/**
	 * fill the data item with this's values
	 */
	function save() {
		if (!$this->preSave()) {
			trigger_error('unable to preSave content item');
			return FALSE;
		}
		$ret = 0;

		$this->_editBump();

		$this->_updateRelations();

		if (strlen($this->dataItem->link_text) < 1) {
			$this->setLinkText();
		}
		if (strlen($this->dataItem->cgn_guid) < 32) {
			$this->dataItem->cgn_guid = cgn_uuid();
		}
		$ret = $this->dataItem->save();

		if ($ret) {
			if (!$this->postSave()) {
				//TODO: rollback
				trigger_error('unable to postSave content item');
				return FALSE;
			}
		}
		return $ret;
	}

	/**
	 * Allow for overriding
	 */
	function preSave() {
		return TRUE;
	}

	/**
	 * Save attributes if any exist
	 */
	function postSave() {
		$ret = TRUE;
		foreach ($this->attribs as $_attrib) {
			$_attrib->cgn_content_id = $this->dataItem->cgn_content_id;
			$ret = ($_attrib->save() > 0) || $ret;
		}
		return $ret;
	}

	/**
	 * Cleanse the link_text or title variable of bad URL characters.
	 */
	function setLinkText($lt = '') {
		if ($lt == '') {
			$lt = $this->dataItem->link_text;
		}
		if ($lt == '') {
			$lt = $this->dataItem->title;
		}
		$lt = str_replace('&', ' and ', $lt);
		$lt = str_replace(' ', '_', $lt);

		$pattern = '/[\x{21}-\x{2C}]|[\x{2F}]|[\x{5B}-\x{5E}]|[\x{7E}]/';
		$preglt = preg_replace($pattern, '_', $lt);
		if ($preglt == '') {
			//preg throws an error if the pattern cannot compile
			//(old preg libraries)
			$e = Cgn_ErrorStack::pullError('php');
			$len = strlen($lt);
			for($i = 0; $i < $len; $i++) {
				$hex =ord($lt{$i});
				if ($hex < 44 || $hex == 47 ) {
					$lt{$i} = '_';
				}

				if ($hex >= 91 && $hex <= 94 ) {
					$lt{$i} = '_';
				}
				if ($hex == 126 ) {
					$lt{$i} = '_';
				}
			}
		$preglt = $lt;
		}

		$lt = str_replace('___', '_', $preglt);
		$lt = str_replace('__', '_', $lt);
		$lt = str_replace('__', '_', $lt);

		$this->dataItem->link_text = $lt;
	}

	/**
	 * Find id="cgn|nn|" in the source and relate this file to that one
	 *
	 * returns number of relations found, or -1 on error
	 * @return int number of relations found, or -1 on error
	 */
	function _updateRelations() {
		if ( !$this->isText() ){
			//no error, but we won't scan binary content
			return 0;
		}
		$matches = array();
		if ($this->dataItem->mime == 'text/wiki') {
			preg_match_all('/\?cgnid\=(\d+)/', $this->dataItem->content, $matches);
			$thisId = sprintf('%d',$this->dataItem->cgn_content_id);
		} else {
			preg_match_all('/cgn_id\|(\d+)\|/', $this->dataItem->content, $matches);
			$thisId = sprintf('%d',$this->dataItem->cgn_content_id);
		}

		//I like this term, FastLane Reader / FastLane Writer... hehe
		$db = Cgn_Db_Connector::getHandle();
		$db->query('DELETE FROM
			cgn_content_rel WHERE from_id = '.$thisId);

		if ( count($matches[1]) == 0 ) { return 0; }

		//array matches will have [0]=>"cgn_id|4|", [1]=> just 4
		foreach ($matches[1] as $contentId) {
			$db->query('INSERT INTO
			cgn_content_rel 
		   (from_id, to_id) VALUES ('.$thisId.', '.$contentId.')');
		}
		return count($matches[1]);
	}

	function setAttribute($name, $val, $type = 'string') {
		if (! $this->_attribsLoaded) {
			$this->loadAllAttributes();
		}
		if (!isset($this->attribs[$name]) ) {
			$this->attribs[$name] = new Cgn_DataItem('cgn_content_attrib');
			$this->attribs[$name]->code = $name;
			$this->attribs[$name]->type = $type;
			$this->attribs[$name]->created_on = time();
		}
		$this->attribs[$name]->edited_on = time();
		$this->attribs[$name]->value = $val;
		return TRUE;
	}

	/**
	 * Load all attributes if they're not loaded
	 */
	function getAttribute($name) {
		if ( count($this->attribs) == 0) {
			//try to load all attribs
			$this->loadAllAttributes();
		}
		if (isset($this->attribs[$name]) ) {
			return $this->attribs[$name];
		}
		return FALSE;
	}

	function loadAllAttributes() {
		$finder = new Cgn_DataItem('cgn_content_attrib');
		$finder->andWhere('cgn_content_id', $this->dataItem->cgn_content_id);
		$attribs = $finder->find();
		foreach ($attribs as $_attrib) {
			$name = $_attrib->code;
			$this->attribs[$name] = $_attrib;
		}
		/*
		if (isset($this->customAttribs)) {
			foreach ($this->customAttribs as $attrCode) {
				if (! isset($this->attribs[$attrCode])) {
					$this->attribs[$attrCode] = new Cgn_DataItem('cgn_content_attrib');
					$this->attribs[$attrCode]->code = $attrCode;
				}
			}
		}
		 */
		$this->_attribsLoaded = TRUE;
		return TRUE;
	}

	function loadAllTags() {
		$finder = new Cgn_DataItem('cgn_content_tag_link');
		$finder->hasOne('cgn_content_tag', 'cgn_content_tag_id', 'Ttag', 'cgn_content_tag_id');
		$finder->andWhere('cgn_content_id', $this->dataItem->cgn_content_id);
		$this->tags = $finder->find();
		$this->_tagsLoaded = TRUE;
		return TRUE;
	}


	/**
	 * Getter
	 */
	function getContent() {
		return $this->dataItem->content;
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
			return FALSE;
		}
		if ($content->dataItem->_isNew == TRUE) {
			trigger_error("Can't publish an unsaved content item");
			return FALSE;
		}
		//change this content as well
		$content->dataItem->sub_type = 'image';

		//only up the published date once
		$content->setPublishedOn();

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
		$image->setPublishedOn( $content->dataItem->published_on );

		$image->save();
		return $image;
	}

	/**
	 * create or load a Cgn_Article object out of this content
	 */
	function publishAsArticle($content) {
		if ($content->dataItem->cgn_content_id < 1) {
			trigger_error("Can't publish an unsaved content item");
			return FALSE;
		}
		if ($content->dataItem->_isNew == TRUE) {
			trigger_error("Can't publish an unsaved content item");
			return FALSE;
		}
		//change this content as well
		$content->dataItem->sub_type = 'article';
		//only up the published date once
		$content->setPublishedOn();

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
			$article->setExcerptWiki($content->dataItem->description);
			$article->dataItem->description = $article->dataItem->excerpt;
			unset($article->dataItem->excerpt);
		} else {
			$article->setContentHtml($content->dataItem->content);
			$article->dataItem->description = $content->dataItem->description;
		}
		$article->dataItem->link_text = $content->dataItem->link_text;
		$article->dataItem->cgn_content_version = $content->dataItem->version;
		$article->dataItem->edited_on = $content->dataItem->edited_on;
		$article->dataItem->created_on = $content->dataItem->created_on;
		$article->setPublishedOn( $content->dataItem->published_on );

		$article->save();
		return $article;
	}

	/**
	 * create or load a Cgn_Web object out of this content
	 */
	function publishAsWeb($content) {
		if ($content->dataItem->cgn_content_id < 1) {
			trigger_error("Can't publish an unsaved content item");
			return FALSE;
		}
		if ($content->dataItem->_isNew == TRUE) {
			trigger_error("Can't publish an unsaved content item");
			return FALSE;
		}

		//change this content as well
		$content->dataItem->sub_type = 'web';
		//only up the published date once
		$content->setPublishedOn();
		$content->dataItem->save();

		//__ FIXME __ use the data item for this search functionality
		$db = Cgn_Db_Connector::getHandle();
		$db->query("SELECT * FROM cgn_web_publish WHERE
			cgn_content_id = ".$content->dataItem->cgn_content_id);
		if ($db->nextRecord()) {
			$web = new Cgn_WebPage();
			$web->dataItem->row2Obj($db->record);
			$web->dataItem->_isNew = FALSE;
		} else {
			$web = new Cgn_WebPage();
		}

		$web->dataItem->cgn_content_id = $content->dataItem->cgn_content_id;
		$web->dataItem->cgn_guid = $content->dataItem->cgn_guid;
		$web->dataItem->title = $content->dataItem->title;
		$web->dataItem->mime = $content->dataItem->mime;

		//caption
		if (isset($content->dataItem->caption)) {
			$web->dataItem->caption = $content->dataItem->caption;
		} else {
			$web->dataItem->caption = '';
		}

		//wiki content
		if ($content->dataItem->mime == 'text/wiki') {
			$web->setContentWiki($content->dataItem->content);
		} else {
			$web->dataItem->content = $content->dataItem->content;
		}

		//description
		$web->dataItem->description = @$content->dataItem->description;

		$web->dataItem->link_text = @$content->dataItem->link_text;
		$web->dataItem->cgn_content_version = @$content->dataItem->version;
		$web->dataItem->edited_on = @$content->dataItem->edited_on;
		$web->dataItem->created_on = @$content->dataItem->created_on;
		$web->setPublishedOn( $content->dataItem->published_on );

		$id = $web->save();
		return $web;
	}


	/**
	 * create or load a Cgn_Asset object out of this content
	 */
	function publishAsAsset($content) {
		if ($content->dataItem->cgn_content_id < 1) {
			trigger_error("Can't publish an unsaved content item");
			return FALSE;
		}
		if ($content->dataItem->_isNew == TRUE) {
			trigger_error("Can't publish an unsaved content item");
			return FALSE;
		}
		//change this content as well
		$content->dataItem->sub_type = 'file';
		//only up the published date once
		$content->setPublishedOn();

		$content->dataItem->save();


		//__ FIXME __ use the data item for this search functionality
		$db = Cgn_Db_Connector::getHandle();
		$db->query("SELECT * FROM cgn_file_publish WHERE
			cgn_content_id = ".$content->dataItem->cgn_content_id);
		if ($db->nextRecord()) {
			$asset = new Cgn_Asset();
			$asset->dataItem->row2Obj($db->record);
			$asset->dataItem->_isNew = FALSE;
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
		$asset->setPublishedOn( $content->dataItem->published_on );

		$asset->save();
		return $asset;
	}

	/**
	 * Load a subtype of Cgn_PublishedContent.
	 * If the sub-type is not known by the system, an event will be fired 
	 * so custom code can handle the loading.
	 *
	 * @event content_load_published_$subType return the sub class
	 * @return Object Cgn_PublishedContent  specific sub class
	 * @param string  $subType   value of sub_type column in cgn_content table
	 * @param int     $id        value of cgn_content_id table
	 */
	public static function loadPublished($subType, $id) {
		$published = NULL;
		$db = Cgn_Db_Connector::getHandle();

		switch($subType) {
			case 'article':
				$db->query('select * from cgn_article_publish 
					WHERE cgn_content_id = '.$id);
				$db->nextRecord();
				$result = $db->record;
				if ($result) {
					$db->freeResult();
					$published = new Cgn_Article($result['cgn_article_publish_id']);
				}
				break;
			case 'web':
				$db->query('select * from cgn_web_publish 
					WHERE cgn_content_id = '.$id);
				$db->nextRecord();
				$result = $db->record;
				if ($result) {
					$db->freeResult();
					$published = new Cgn_WebPage($result['cgn_web_publish_id']);
				}
				break;

			case 'image':
				$db->query('select * from cgn_image_publish 
					WHERE cgn_content_id = '.$id);
				$db->nextRecord();
				$result = $db->record;
				if ($result) {
					$db->freeResult();
					$published = new Cgn_Image($result['cgn_image_publish_id']);
				}
				break;

			case 'asset':
			case 'file':
				$db->query('select * from cgn_file_publish 
					WHERE cgn_content_id = '.$id);
				$db->nextRecord();
				$result = $db->record;
				if ($result) {
					$db->freeResult();
					$published = new Cgn_Asset($result['cgn_file_publish_id']);
				}
				break;

			default:
				$plugin = Cgn_ContentPublisher::_findPluginForSubType($subType);
				if ($plugin !== NULL) {
					$published = $plugin->loadPublished($id);
				} else {
					$req = Cgn_SystemRequest::getCurrentRequest();
					$u = $req->getUser();
					$u->addSessionMessage('Unknown content type, cannot pubish', 'msg_warn');
					$t['url'] = cgn_adminurl(
						'content');
					break;
				}

				if ($published == NULL) {
					$req = Cgn_SystemRequest::getCurrentRequest();
					$u = $req->getUser();
					$u->addSessionMessage('Error publishing content', 'msg_warn');
					$t['url'] = cgn_adminurl(
						'content');
					break;
				}
				break;

		}
		return $published;
	}


	public static function _findPluginForSubType($subType) {

		$configArray = Cgn_ObjectStore::getArray('config://default/content/extrasubtype');
		foreach ($configArray as $_code => $_v) {
			$plugin = Cgn_ObjectStore::includeObject($_v);
			if ($plugin === NULL) {
				$e = Cgn_ErrorStack::pullError('php');
				continue;
			}

			if ($subType == $plugin->getFormValue()) {
				return $plugin;
			}
		}
		return NULL;
	}
}


/**
 * Hold some base functions for all content items that *can be* published.
 * 
 */
class Cgn_PublishedContent extends Cgn_Data_Model {
	public $contentItem;
	public $dataItem;
	public $metaObj;
	public $tableName    = '';
	//save in search by default
	public $useSearch    = TRUE;

	function Cgn_PublishedContent($id=-1) {
		$this->dataItem = new Cgn_DataItem($this->tableName);
		if ($id > 0 ) {
			$this->dataItem->load($id);
		}
	}

	function getVersion() {
		return $this->dataItem->cgn_content_version;
	}

	/**
	 *  Hook for subclasses
	 */
	function preSave() {
		return TRUE;
	}

	/**
	 * Hook for sub-classes
	 *
	 * Try to save in global search index if "useSearch" === TRUE
	 *
	 * @return  boolean  always TRUE
	 */
	function postSave() {
		if ($this->useSearch === TRUE) {
			$this->indexInSearch();
		}
		return TRUE;
	}

	function save() {
		$this->preSave();

		if (strlen($this->dataItem->link_text) < 1) {
			$this->setLinkText();
		}
		if (strlen($this->dataItem->cgn_guid) < 32) {
			$this->dataItem->cgn_guid = $this->contentItem->cgn_guid;
		}
		$ret =  $this->dataItem->save();

		if ($ret) {
			if (!$this->postSave()) {
				//TODO: rollback
				trigger_error('unable to postSave content item');
				return FALSE;
			}
		}
		return $ret;
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

	/**
	 * Only set the publised on time once.
	 *
	 * @param int $time unix timestamp of published date
	 */
	function setPublishedOn($time=NULL) {
		if ($time  === NULL) {
			$time = time();
		}
		if ($this->dataItem->published_on == 0 ||
			$this->dataItem->published_on == '') {
				$this->dataItem->published_on = $time;
		}
	}

	/**
	 * Converts wiki text into HTML with doku wiki (single page).
	 *
	 * Updates the $this->dataItem->content variable to rendered HTML.
	 * This method does not deal with multiple pages, although overridden 
	 * implementations might deal with multiple pages (articles).
	 *
	 * The translation process removes ?cgnid=N codes from the source which 
	 * are used only for tracking content connections.
	 *
	 * @param String $wikiContent wiki source
	 */
	function setContentWiki($wikiContent) {
		if (!defined('DOKU_WIKI')) {
			define('DOKU_BASE', cgn_appurl('main','content','image'));
		}
		if (!defined('DOKU_CONF')) {
			define('DOKU_CONF', CGN_LIB_PATH.'/dokuwiki/ ');
		}

		include_once(CGN_LIB_PATH.'/wiki/lib_cgn_wiki.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/parser.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/lexer.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/handler.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/renderer.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/xhtml.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/parserutils.php');

		//remove the ?cgnid=X that is only used for internal tracking
		$wikiContent = preg_replace('/\?cgnid\=(\d+)/', '',$wikiContent);
		$this->dataItem->content = p_render('xhtml',p_get_instructions($wikiContent),$info);
	}


	/**
	 * Converts wiki text into HTML with doku wiki (single page).
	 *
	 * Updates the $this->dataItem->excerpt variable to rendered HTML.
	 *
	 * The translation process removes ?cgnid=N codes from the source which 
	 * are used only for tracking content connections.
	 *
	 * @param String $wikiContent wiki source
	 */
	function setExcerptWiki($wikiContent) {
		if (!defined('DOKU_WIKI')) {
			define('DOKU_BASE', cgn_appurl('main','content','image'));
		}
		if (!defined('DOKU_CONF')) {
			define('DOKU_CONF', CGN_LIB_PATH.'/dokuwiki/ ');
		}

		include_once(CGN_LIB_PATH.'/wiki/lib_cgn_wiki.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/parser.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/lexer.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/handler.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/renderer.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/xhtml.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/parserutils.php');

		//remove the ?cgnid=X that is only used for internal tracking
		$wikiContent = preg_replace('/\?cgnid\=(\d+)/', '',$wikiContent);
		$info = array();
		$this->dataItem->excerpt = p_render('xhtml',p_get_instructions($wikiContent),$info);
	}
}


/**
 * Help publish content to the article table
 */
class Cgn_Article extends Cgn_PublishedContent {
	public $dataItem;
	public $tableName = 'cgn_article_publish';
	public $pages = array();
	public $hasPages = FALSE;


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
		define('DOKU_CONF', CGN_LIB_PATH.'/dokuwiki/ ');

		include_once(CGN_LIB_PATH.'/wiki/lib_cgn_wiki.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/parser.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/lexer.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/handler.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/renderer.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/xhtml.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/parserutils.php');

		//remove the ?cgnid=X that is only used for internal tracking
		$wikiContent = preg_replace('/\?cgnid\=(\d+)/', '',$wikiContent);
		$pages = $this->separatePages($wikiContent);
		$info = array();
		if (is_array($pages) ) {
			//extract the first page into the main article
			$this->dataItem->content = p_render('xhtml',p_get_instructions($pages[0]->dataItem->content),$info);
			$this->hasPages = TRUE;
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
	 * Separate pages for HTML content too.
	 *
	 * Still use the wiki token in html tho
	 */
	function setContentHtml($content) {
		$pages = $this->separatePages($content);
		$info = array();
		if (is_array($pages) ) {
			//extract the first page into the main article
			$this->dataItem->content = $pages[0]->dataItem->content;
			$this->hasPages = TRUE;
			unset($pages[0]);
			//render each additional page's content
			foreach ($pages as $idx => $articlePage) {
				$articlePage->dataItem->content = $articlePage->dataItem->content;
				$this->pages[] = $articlePage;
			}
			unset($pages);
		} else {
			$this->dataItem->content = $content;
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
			//*/
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
		if ($this->hasPages && ! $this->dataItem->isNew) {

			//don't delete if there's no published article id,
			//that means this is a brand new article, it couldn't possibly have pages to clean up
			if ($this->dataItem->cgn_article_publish_id > 0)  {
				$db = Cgn_Db_Connector::getHandle();
				$db->query("DELETE FROM cgn_article_page where cgn_article_publish_id = ".$this->dataItem->cgn_article_publish_id);
			}
		}

		$ret =  $this->dataItem->save();

		if ($ret) {
			if (!$this->postSave()) {
				//TODO: rollback
				trigger_error('unable to postSave content item');
				return FALSE;
			}
		}
		return $ret;
	}

	/**
	 * Save article pages.
	 */
	function postSave() {
		$ret = TRUE;
		foreach($this->pages as $articlePage) {
			$articlePage->dataItem->cgn_article_publish_id = $this->dataItem->cgn_article_publish_id;
			$ret = $ret && $articlePage->save();
		}
		return $ret;
	}
}

class Cgn_ArticlePage extends Cgn_PublishedContent {
	public $dataItem;
	public $tableName = 'cgn_article_page';

	function save() {
		return $this->dataItem->save();
	}
}


/**
 * Help publish content to the news item table
 */
class Cgn_NewsItem extends Cgn_PublishedContent {
	public $dataItem;
}


/**
 * Help publish content to the image table
 */
class Cgn_Image extends Cgn_PublishedContent {
	public $dataItem;
	public $tableName = 'cgn_image_publish';
	public $mimeType = '';

	/**
	 * Create web sized image and thumb nail
	 */
	function preSave() {

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
			unlink($tmpfname);
			return FALSE;
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
		$thumbheight = 128;
		if ($width > $thumbwidth) {
			//resize proportionately
			$ratio = $thumbwidth / $width;
			$new2width  = $thumbwidth;
			$new2height = intval($height * $ratio);
		} else {
			//Check if image is really tall and thin.
			//Don't do this for the medium size image because 
			// vertically tall images aren't a problem for most layouts.
			if ($height > $thumbheight) {
				$ratio = $thumbheight / $height;
				$new2height  = $thumbheight;
				$new2width   = intval($width * $ratio);
			} else {
				//use defaults, image is small enough 
				$new2width = $width;
				$new2height = (int)$height;
			}
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

/*
header('Content-type: image/png');
imagePng($thmImage);
exit();
 */
		ob_start(); // start a new output buffer
		switch ($this->mimeType) {
			case 'image/png':
			imagePng( $webImage, NULL, 7);
			break;

			case 'image/jpeg':
			case 'image/jpg':
			imageJpeg( $webImage, NULL, 80 );
			break;

			case 'image/gif':
			imageGif( $webImage, NULL, 80 );
			break;
		}

		$this->dataItem->web_image = ob_get_contents();
		ob_end_clean(); // stop this output buffer
		imageDestroy($webImage);

		ob_start(); // start a new output buffer
		switch ($this->mimeType) {
			case 'image/png':
			imagePng( $thmImage, NULL, 7 );
			break;

			case 'image/jpeg':
			case 'image/jpg':
			imageJpeg( $thmImage, NULL, 80 );
			break;

			case 'image/gif':
			imageGif( $thmImage, NULL, 80 );
			break;
		}
		$this->dataItem->thm_image = ob_get_contents();
		ob_end_clean(); // stop this output buffer
		imageDestroy($thmImage);

		unlink($tmpfname);
	}


	function figureMime() {
		if ($this->dataItem->mime != '') {
			if ($this->dataItem->mime != 'application/octet-stream') {
			$this->mimeType = $this->dataItem->mime;
			return;
			}
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


	function getContentId() {
		return $this->dataItem->cgn_content_id;
	}
}


/**
 * Help publish content to the generic asset table.
 * This is supposed to be things like flash plugins, PDFs, 
 * other embedded items, or things that need plugin players.
 */
class Cgn_WebPage extends Cgn_PublishedContent {

	public $dataItem;
	public $contentObj;
	public $metaObj;
	public $tableName = 'cgn_web_publish';

	function setContentWiki($wikiContent) {
		define('DOKU_BASE', cgn_appurl('main','content','image'));
		define('DOKU_CONF', CGN_LIB_PATH.'/dokuwiki/ ');

		include_once(CGN_LIB_PATH.'/wiki/lib_cgn_wiki.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/parser.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/lexer.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/handler.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/renderer.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/xhtml.php');
		include_once(CGN_LIB_PATH.'/dokuwiki/parserutils.php');

		$wikiContent = preg_replace('/\?cgnid\=(\d+)/', '',$wikiContent);

		$this->dataItem->content = p_render('xhtml',p_get_instructions($wikiContent),$info);
	}

	function getSectionContent($name) {
		$html = '';
		$lines = explode("\n",$this->dataItem->content);
		$parsing = FALSE;
		foreach($lines as $l) {
			if (trim($l) == '<!-- END: '.$name.' -->'
				|| trim($l) == '&lt;!-- END: '.$name.' --&gt;') {
				$parsing = FALSE;
			}

			if ($parsing) {
				$html .= $l;
			}
			if (trim($l) == '<!-- BEGIN: '.$name.' -->'
				|| trim($l) == '&lt;!-- BEGIN: '.$name.' --&gt;') {
				$parsing = TRUE;
			}
		}
		return $html;
	}

	function isPublished() {
		return TRUE;
	}

	/**
	 * Return the is_portal variable as TRUE or FALSE
	 */
	function isPortal() {
		if (!isset($this->dataItem->is_portal)) {
			return FALSE;
		}
		return (bool)$this->dataItem->is_portal;
	}

	/**
	 * Getter
	 */
	function getTitle() {
		return $this->dataItem->title;
	}

	function getContentId() {
		return $this->dataItem->cgn_content_id;
	}

}

/**
 * Help publish content to the generic asset table.
 * This is supposed to be things like flash plugins, PDFs, 
 * other embedded items, or things that need plugin players.
 */
class Cgn_Asset extends Cgn_PublishedContent {
	public $dataItem;
	public $tableName = 'cgn_file_publish';


	// SCOTTCHANGE	20080221
	function getContentId() {
		return $this->dataItem->cgn_content_id;
	}
	// END SCOTTCHANGE

}

/**
 * Content WebPages are content items that have been "used as" a web page.
 *
 * This object has 2 basic sub objects.  The meta data object, combined
 *   with the regular Cgn_Content data record object will make this a
 *   "web page" item.
 *
 * <ul>
 * 	<li>dataItem: cgn_content record</li>
 * 	<li>metaObj: cgn_content_meta object</li>
 * </ul>
 *  
 */
class Cgn_Content_WebPage extends Cgn_Content {

	public $metaObj;

	function Cgn_Content_WebPage($id=-1) {
		parent::Cgn_Content($id);
		$this->dataItem->sub_type  = 'web';
		$this->dataItem->type      = 'text';
		$this->dataItem->mime      = 'text/html';
		$this->metaObj = new Cgn_Content_MetaData();
	}

	function createNew($title='',$subtype = 'web') {
		$x = new Cgn_Content_WebPage();
		$x->setTitle($title);
		return $x;
	}


	/**
	 * Change the mimetype so that it is wiki.
	 */
	function setWiki() {
		$this->dataItem->mime = 'text/wiki';
	}

	/**
	 * get the primary key of the core content item
	 */
	function getContentId() {
		return $this->dataItem->cgn_content_id;
	}

	/**
	 * Getter
	 */
	function getTitle() {
		return $this->dataItem->title;
	}

	/**
	 * make a new web page given a content object.
	 */
	function &make($content) {
		$x = new Cgn_Content_WebPage();
		$x->dataItem =& $content->dataItem;
		$x->dataItem->sub_type = 'web';
		return $x;
	}

	/** 
	 * Treat this page as a portal page with many embedded content items
	 */
	function setPortal($boolean=TRUE) {
		$this->metaObj->set('is_portal', $boolean);
	}

	/** 
	 * Treat this page as a portal page with many embedded content items
	 *
	 * @return boolean
	 */
	function isPortal() {
		$isPo = $this->metaObj->get('is_portal');
		return $isPo === NULL ? FALSE: $isPo;
	}

	/** 
	 * Treat this page as the one and only home page
	 */
	function setHp($boolean=TRUE) {
		$this->metaObj->set('is_home', $boolean);
	}

	/** 
	 * Treat this page as the one and only home page
	 *
	 * @return boolean
	 */
	function isHp() {
		$isHp = $this->metaObj->get('is_home');
		return $isHp === NULL ? FALSE: $isHp;
	}

}


/**
 * Hold simple key value pairs for a content sub-type
 *
 * Recommended array holds keys of "values" that are recommended.
 * Required array holds keys of "values" that are required.
 */
class Cgn_Content_MetaData {
	public $values = array();
	public $recmd  = array();
	public $reqrd  = array();

	function Cgn_Content_MetaData() {
	}

	function set($k,$v) {
		$this->values[$k] = $v;
	}

	function get($k) {
		return @$this->values[$k];
	}

	function loadSettingsForType($type = 'web') {
		switch ($type) {
		case 'web':
			$this->reqrd[] = 'is_portal';
			$this->reqrd[] = 'is_home';
			break;

		case 'article':
			$this->recmd[] = 'section';
			break;
		}
	}
}

/**
 * Act as a plugin for publishing non-native types of content
 */
class Cgn_Content_Publisher_Plugin {


	public $codeName    = 'cgn_publish_plugin';
	public $displayName = 'Sample Publish Plugin';

	public function getFormValue() {
		return $this->codeName;
	}

	public function getDisplayName() {
		return $this->displayName;
	}

	/**
	 * Construct a URL to return to after publishing is done.
	 * Return an empty string to get the default behavior.
	 * You can retrieve ID information from the $publishedContent parameter
	 *
	 * @param Object $publishedContent the resulting published content
	 */
	public function getReturnUrl($publishedContent) {
		return '';
	}


	/**
	 * Default implementation, does nothing, returns NULL
	 *
	 * @return NULL
	 */
	public function publishAsCustom($content) {
		return NULL;
	}
}
?>
