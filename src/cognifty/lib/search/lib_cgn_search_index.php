<?php

include_once(CGN_LIB_PATH.'/Zend/Search/Lucene.php');
include_once(CGN_LIB_PATH.'/Zend/Search/Lucene/Analysis/Analyzer.php');
include_once(CGN_LIB_PATH.'/Zend/Search/Lucene/Analysis/Analyzer/Common.php');
include_once(CGN_LIB_PATH.'/Zend/Search/Lucene/Analysis/Analyzer/Common/TextNum.php');


/**
 * Wrapper class that manages multiple Zend_Search_Lucene indexes
 *
 * @package Cgn_Search
 */
class Cgn_Search_Index {

	static $indexList     = array();
	var $currentIndex     = NULL;
	var $currentIndexName = '';
	var $currentDoc       = NULL;

	/**
	 * Commit the currentIndex after N document adds
	 */
	var $commitEvery      = 1000;

	/**
	 * Keep track of the current number of addedDocuments
	 */
	var $addCount         = 0;

	//can't use other constants to buld a constant... dumb
	//const CGN_SEARCH_ROOT = BASE_DIR,'var/search_cache/';
	const CGN_SEARCH_ROOT  = 'var/search_cache/';

	/**
	 * Create a new instance of Cgn_Search_Index and load or create the specified index by name.
	 *
	 */
	function Cgn_Search_Index($indexName, $force = FALSE) {
		if ($force === TRUE || !isset(Cgn_Search_Index::$indexList[$indexName])) {
			$this->createIndex($indexName);
		}

        if (Cgn_Search_Index::$indexList[$indexName]->_closed) {
			die('index is already closed.');
		}
		$this->currentIndex = Cgn_Search_Index::$indexList[$indexName];
		$this->currentIndexName = $indexName;
	}


	/**
	 * Return a reference to the current index specified by "$indexName" in the constructor.
	 *
	 * @return Object Zend_Search_Lucene
	 */
	function getIndex() {
		return $this->currentIndex;
	}


	/**
	 * Create a new Zend_Search_Lucene index in the directory specified by Cgn_Search_Index::CGN_SEARCH_ROOT
	 *
	 * @param String $indexName the name of this index, this implies a directory name on disk.
	 */
	function createIndex($indexName) {
		$indexPath = BASE_DIR . Cgn_Search_Index::CGN_SEARCH_ROOT.$indexName;

		// the index object to return
		$index = NULL;

		if (!file_exists($indexPath)) {
			$index = Zend_Search_Lucene::create($indexPath); 
		} else {
			try {
            $index = Zend_Search_Lucene::open($indexPath); 
			} catch (Zend_Search_Lucene_Exception $e) {
				throw($e);
			}
		}
		Cgn_Search_Index::$indexList[$indexName] = $index;
	}

	/**
	 * Creates a new Zend_Search_Lucene_Document and stores it on $this->currentDoc
	 */
	function createDoc() {
		$this->currentDoc = new Zend_Search_Lucene_Document(); 
	}

	function saveDoc() {
		$this->currentIndex->addDocument($this->currentDoc); 
		$this->addCount++;
		if ($this->addCount % $this->commitEvery  === 0 ) {
			$this->currentIndex->commit();
		}
	}

	/**
	 * Close the currentIndex, reset the addCount, commit this index
	 */
	function close() {
		$this->currentIndex->commit();
		$this->addCount = 0;
		Cgn_Search_Index::$indexList[$this->currentIndexName] = NULL;
		unset(Cgn_Search_Index::$indexList[$this->currentIndexName]);
		unset($this->currentIndex);
		$this->currentIndex = NULL;
	}

	/**
	 * Return the number of active, non-deleted documents in this index.
	 *
	 * This is a pass through to the Zend method.
	 * @return int  number of documents
	 */
	function getDocumentCount() {
		return $this->currentIndex->numDocs();
	}

    /**
     * Returns a list of all unique field names that exist in this index.
     *
	 * This is a pass through to the Zend method.
     * @return array
     */
	function getFieldNames() {
		return $this->currentIndex->getFieldNames();
	}

	/**
	 * Delete a document from the index using its hit ID
	 *
	 * This is a pass through to the Zend method.
	 * @param int hid  the hit ID
	 */
	function delete($hid) {
		$this->currentIndex->delete($hid);
	}


	/**
	 * Execute a query
	 *
	 * This is a pass through to the Zend method.
	 * @param mixed $q  the ZSL query
	 */
	function find($q) {
		return $this->currentIndex->find($q);
	}
}
