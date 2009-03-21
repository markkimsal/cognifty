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

	var $ownerIdField = 'user_id';
	var $groupIdField = 'group_id';

	var $parentTable   = '';
	var $parentIdField = '';

	var $sharingModeRead   = 'same-group';
	var $sharingModeCreate = 'same-group';
	var $sharingModeUpdate = 'same-owner';
	var $sharingModeDelete = 'same-owner';

	function __construct($id=NULL) {
		if ($this->tableName !== '') {
			$this->initDataItem();
		}
		if ($id !== NULL) {
			$this->load($id);
		}
	}

	/**
	 * Initialize the internal data item to a new Cgn_DataItem.
	 *
	 * Requires $this->tableName to be set.  Called from constructor
	 *
	 */
	function initDataItem() {
		$this->dataItem = new Cgn_DataItem($this->tableName);
	}

	/**
	 * Replace the current dataItem.
	 *
	 * This is intended to be called from Cgn_DataModel_List
	 *
	 */
	function setDataItem($item) {
		$this->dataItem = $item;
	}

	/**
	 * Get this object's primary key field
	 */
	function getPrimaryKey() {
		return $this->dataItem->getPrimaryKey();
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
	 * Data_Item passthrough.
	 *
	 * Return an array of values
	 */
	function valuesAsArray() {
		return $this->dataItem->valuesAsArray();
	}

	/**
	 * Data_Item passthrough.
	 *
	 * Initialize a blank data item
	 */
	function initBlank() {
		$this->dataItem->initBlank();
	}

	/**
	 * Save the internal dataItem to the database.
	 *
	 * @return mixed FALSE on failure, integer primary key on success
	 */
	function save() {
		$u = Cgn_SystemRequest::getUser();
		if ($this->dataItem->_isNew) {
			$sharing = $this->sharingModeCreate;
		} else {
			$sharing = $this->sharingModeUpdate;
		}
		switch ($sharing) {
			//where group id in a list of group
			case 'same-group':
				$this->dataItem->andWhere($this->groupIdField, $u->getGroupIds(), 'IN');
				$this->dataItem->orWhereSub($this->groupIdField,0);
				break;

			case 'same-owner':
				$this->dataItem->andWhere($this->ownerIdField, $u->getUserId());
				$this->dataItem->orWhereSub($this->ownerIdField,0);
				break;

			case 'parent-group':
				$this->dataItem->_cols[] = $this->tableName.'.*';
				$this->dataItem->hasOne($this->parentTable, $this->parentIdField, 'Tparent', $this->parentIdField);
				$this->dataItem->andWhere('Tparent.'.$this->groupIdField, $u->getGroupIds(), 'IN');
				$this->dataItem->orWhereSub('Tparent.'.$this->groupIdField,0);
				break;

			case 'registered':
				if ($u->isAnonymous()) { return false; }
		}


		$pkey = $this->dataItem->save();
		if (!$pkey) {
			return false;
		}
		if ($this->useSearch === TRUE) {
			$this->indexInSearch();
		}
		return $pkey;
	}

	/**
	 * Load the internal dataItem using the $id
	 *
	 * @param $id int Unique id
	 */
	function load($id) {
		$u = Cgn_SystemRequest::getUser();
		switch ($this->sharingModeRead) {
			//where group id in a list of group
			case 'same-group':
				$this->dataItem->andWhere($this->groupIdField, $u->getGroupIds(), 'IN');
				$this->dataItem->orWhereSub($this->groupIdField,0);
				break;

			case 'same-owner':
				$this->dataItem->andWhere($this->ownerIdField, $u->getUserId());
				$this->dataItem->orWhereSub($this->ownerIdField,0);
				break;

			case 'parent-group':
				$this->dataItem->_cols[] = $this->tableName.'.*';
				$this->dataItem->hasOne($this->parentTable, $this->parentIdField, 'Tparent', $this->parentIdField);
				$this->dataItem->andWhere('Tparent.'.$this->groupIdField, $u->getGroupIds(), 'IN');
				$this->dataItem->orWhereSub('Tparent.'.$this->groupIdField,0);
				break;

			case 'parent-owner':
				$this->dataItem->_cols[] = $this->tableName.'.*';
				$this->dataItem->hasOne($this->parentTable, $this->parentIdField, 'Tparent', $this->parentIdField);
				$this->dataItem->andWhere('Tparent.'.$this->ownerIdField, $u->getUserId());
				break;

			case 'registered':
				if ($u->isAnonymous()) { return false; }
		}
		//load failed
		if (!$this->dataItem->load($id)) {
			return FALSE;
		}
		//load succeeded, but no permission
		return $this->dataItem->getPrimaryKey() != 0;
	}

	/**
	 * Delete the internal dataItem from the database
	 *
	 * @return mixed FALSE on failure, TRUE on success
	 */
	function delete() {
		$u = Cgn_SystemRequest::getUser();
		if ($this->dataItem->_isNew) {
			return FALSE;
		} else {
			$sharing = $this->sharingModeDelete;
		}

		//if the item has been loaded (_isNew is false) clear all the where parts first
		$this->dataItem->resetWhere();
		switch ($sharing) {
			//where group id in a list of group
			case 'same-group':
				$this->dataItem->andWhere($this->groupIdField, $u->getGroupIds(), 'IN');
				$this->dataItem->orWhereSub($this->groupIdField,0);
				break;

			case 'same-owner':
				$this->dataItem->andWhere($this->ownerIdField, $u->getUserId());
				$this->dataItem->orWhereSub($this->ownerIdField,0);
				break;

			case 'parent-group':
				$this->dataItem->_cols[] = $this->tableName.'.*';
				$this->dataItem->hasOne($this->parentTable, $this->parentIdField, 'Tparent', $this->parentIdField);
				$this->dataItem->andWhere('Tparent.'.$this->groupIdField, $u->getGroupIds(), 'IN');
				$this->dataItem->orWhereSub('Tparent.'.$this->groupIdField,0);
				break;

			case 'registered':
				if ($u->isAnonymous()) { return false; }
		}


		$backup = $this->dataItem;
		$perms = $this->dataItem->load($this->dataItem->getPrimaryKey());
		//no perms to load with delete model means no perms to delete
		if (!$perms) {
			$this->dataItem = $backup;
			return FALSE;
		} else {
			$this->dataItem->delete();
		}
		unset($backup);
		if ($this->useSearch === TRUE) {
			$this->removeFromSearch();
		}
		return $perms;
	}


	/**
	 *
	 */
	function removeFromSearch() {
		require_once(CGN_LIB_PATH.'/search/lib_cgn_search_index.php');
		$index = new Cgn_Search_Index($this->searchIndexName);
		//find and delete old database_id and table_name from index
		$this->foobarOldDoc($index, $this->tableName, $this->dataItem->getPrimaryKey());
	}

	/**
	 *
	 */
	function indexInSearch() {
		static $cxx;

		$cxx++;
		require_once(CGN_LIB_PATH.'/search/lib_cgn_search_index.php');
		$index = new Cgn_Search_Index($this->searchIndexName);
		if ($index->isClosed) {
			return FALSE;
		}
		//find and delete old database_id and table_name from index
		$this->foobarOldDoc($index, $this->tableName, $this->dataItem->getPrimaryKey());

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

	function __toString() {
		return $this->dataItem->__toString();
	}
}


/**
 * Class for handling collections of data items
 */
class Cgn_Data_Model_List {

	var $dataItemList     = array();
	var $tableName        = '';
	var $modelName        = '';
	var $searchIndexName = 'global';
	var $useSearch    = FALSE;

	var $ownerIdField = 'user_id';
	var $groupIdField = 'group_id';

	var $sharingModeRead   = 'same-group';
	var $sharingModeCreate = 'same-group';
	var $sharingModeUpdate = 'same-owner';
	var $sharingModeDelete = 'same-owner';

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
	 * @param $u Cgn_User the user in question
	 */
	function loadVisibleList($u = NULL, $extraWhere = '') {
		if ($u == NULL) {
			$u = Cgn_SystemRequest::getUser();
		}
		switch ($this->sharingModeRead) {
			//where group id in a list of group
			case 'same-group':
				$this->dataItem->andWhere($this->groupIdField, $u->getGroupIds(), 'IN');
				$this->dataItem->orWhereSub($this->groupIdField,0);
				break;

			case 'same-owner':
				$this->dataItem->andWhere($this->ownerIdField, $u->getUserId());
				$this->dataItem->orWhereSub($this->ownerIdField,0);
				break;

			case 'registered':
				if ($u->isAnonymous()) { return false; }
		}
		$this->dataItemList = $this->dataItem->find($extraWhere);
		if ($this->modelName != '') {
			foreach ($this->dataItemList as $idx => $_di) {
				$this->dataItemList[$idx] = $this->makeDataModel($_di);
			}
		}
		return $this->dataItemList;
	}

	/**
	 * @param $u Cgn_User the user in question
	 */
	function loadVisibleListAsArray($u = NULL, $extraWhere = '') {
		if ($u == NULL) {
			$u = Cgn_SystemRequest::getUser();
		}
		switch ($this->sharingModeRead) {
			//where group id in a list of group
			case 'same-group':
				$this->dataItem->andWhere($this->groupIdField, $u->getGroupIds(), 'IN');
				$this->dataItem->orWhereSub($this->groupIdField,0);
				break;

			case 'same-owner':
				$this->dataItem->andWhere($this->ownerIdField, $u->getUserId());
				$this->dataItem->orWhereSub($this->ownerIdField,0);
				break;

			case 'registered':
				if ($u->isAnonymous()) { return false; }
		}
		return $this->dataItem->findAsArray($extraWhere);
	}

	/**
	 * Create a Cgn_Data_Model for each Cgn_Data_Item return in a result
	 */
	public function makeDataModel($item) {
		$modelName = $this->modelName;
		$model = new $modelName();
		$model->setDataItem($item);
		return $model;
	}

	function __toString() {
		$str = '';
		foreach ($this->dataItemList as $_di) {
			$str .= $_di->__toString();
		}
		return $str;
	}
}

