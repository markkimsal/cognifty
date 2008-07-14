<?php


/**
 * This class is intended to be sub-classed and wrap the Cgn_DataItem
 *
 * @abstract
 */
class Cgn_Data_Model {

	var $dataItem     = NULL;
	var $tableName        = '';
	var $searchIndexName = 'global';
	var $useSearch    = FALSE;

	function __construct() {
		if ($this->tableName !== '') {
			$this->initDataItem();
		}
	}

	/**
	 * Initialize the internal data item to a new Cgn_DataItem.
	 *
	 * Requires $this->tableName to be set.  Called from constructor
	 *
	 */
	function  initDataItem() {
		$this->dataItem = new Cgn_DataItem($this->tableName);
	}

	/**
	 * Set a value of the internal dataItem.
	 *
	 * @param $key string  column name
	 * @param $val mixed   any value
	 */
	function set($key, $value) {
		$this->dataItem->{$key} = $value;
	}

	/**
	 * Return a value of the internal dataItem.
	 *
	 * @return mixed value of internal dataItem
	 */
	function get($key) {
		return $this->dataItem->{$key};
	}

	/**
	 * Save the internal dataItem to the database
	 */
	function save() {
		$this->dataItem->save();
		if ($this->useSearch === TRUE) {
			$this->indexInSearch();
		}
	}

	/**
	 * Load the internal dataItem using the $id
	 *
	 * @param $id int Unique id
	 */
	function load($id) {
		$this->dataItem->load($id);
	}

	/**
	 *
	 */
	function indexInSearch() {
		static $cxx;

		$cxx++;
		require_once(CGN_LIB_PATH.'/search/lib_cgn_search_index.php');
		$index = new Cgn_Search_Index($this->searchIndexName);
		//find and delete old database_id and table_name from index
		$this->foobarOldDoc(&$index, $this->tableName, $this->dataItem->getPrimaryKey());

		var_dump($index->currentIndex->_directory);
		$index->createDoc();
		$index->currentDoc->addField(Zend_Search_Lucene_Field::Keyword('database_id', $this->dataItem->getPrimaryKey())); 
		$index->currentDoc->addField(Zend_Search_Lucene_Field::Keyword('table_name', $this->tableName)); 

		$vals = $this->dataItem->valuesAsArray();
		$blobOfData = '';
		foreach ($vals as $k =>$v) {
			//exclude the pkey
			if ($k == $this->dataItem->_pkey) {
				continue;
			}
			//store title, name, display_name, or link_text as separate fields
			if ($k == 'title' ||
				$k == 'name' ||
				$k == 'display_name' ||
				$k == 'link_text' ) {
					$index->currentDoc->addField(Zend_Search_Lucene_Field::Unstored($k, $v)); 
			} else {
				$blobOfData .= $v;
			}
		}

		if ($blobOfData !== '') {
			$index->currentDoc->addField(Zend_Search_Lucene_Field::Unstored('_search_data', $blobOfData)); 
		}

		$index->saveDoc();
	}

	/**
	 * load the old record and delete it
	 */
	function foobarOldDoc(&$index, $tableName, $pkey) {
    	$query = new Zend_Search_Lucene_Search_Query_MultiTerm();
		$dbTerm  = new Zend_Search_Lucene_Index_Term($pkey, 'database_id');
		$tblTerm = new Zend_Search_Lucene_Index_Term($tableName, 'table_name');

	    $query->addTerm($dbTerm, TRUE);
	    $query->addTerm($tblTerm, TRUE);

		$hits = $index->find($query);
		foreach ($hits as $h) {
			$index->currentIndex->delete($h->id);
			$index->currentIndex->commit();
			$index->currentIndex->optimize();
		}
	}
}

