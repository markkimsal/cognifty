<?php

/*
 * static init
 */
if (! defined('data_item_init')) {
	$g_db_handle = null;
	$x = Cgn_Db_Connector::getHandle();
	Cgn_DbWrapper::setHandle($x);
	define('data_item_init',true);
}



/**
 * Cgn_DbWrapper::setHandle($a);
 * $db = Cgn_DbWrapper::getHandle();
 */
class Cgn_DbWrapper {

	function setHandle($db='') {
		global $g_db_handle;
		$g_db_handle = $db;
	}


	function &getHandle() {
		global $g_db_handle;
		return $g_db_handle;
	}
}


class Cgn_DataItem {

	var $_table;
	var $_pkey;
	var $_relatedMany   = array();
	var $_relatedSingle = array();
	var $_colMap        = array();
	var $_typeMap       = array();
	var $_where         = array();		//list of where sub-arrays
	var $_excludes      = array();		//list of columns not to select
	var $_cols          = array();		//list of columns for selects
	var $_nuls          = array();		//list of columns that can hold null
	var $_limit         = -1;
	var $_start         = -1;
	var $_sort          = array();
	var $_filterNames   = true;
	var $_tblPrefix     = '';
	var $_isNew         = false;
	var $_debugSql      = false;
	var $_rsltByPkey    = true;


	function Cgn_DataItem($t,$pkey='') {
		$this->_table = $t;
		$this->_pkey = $pkey;
		if (!$this->_pkey) {
			$this->_pkey = $this->_table.'_id';
		}
		$this->_isNew = true;
	}

	/**
	 * Return all the values as an array
	 */
	function valuesAsArray() {
		$vars = get_object_vars($this);
		$keys = array_keys($vars);
		$values = array();
		foreach ($keys as $k) {
			//skip private and data item specific members
			if (substr($k,0,1) == '_') { continue; }
			$values[$k] = $vars[$k];
		}
		return $values;
	}

	/**
	 * Set this object's primary key field
	 */
	function setPrimaryKey($n) {
		$this->{$this->_pkey} = $n;
	}

	/**
	 * Get this object's primary key field
	 */
	function getPrimaryKey() {
		return $this->{$this->_pkey};
	}

	/**
	 * Insert or update
	 */
	function save() {
		/*
		if ( $this->isNew() ) {
			$this->setPrimaryKey(ClassSectionsPeer::doInsert($this,$dsn));
		} else {
			ClassSectionsPeer::doUpdate($this,$dsn);
		}
		 */
//		Cgn::debug( $this->buildInsert() );
//		exit();
		$db = Cgn_DbWrapper::getHandle();

		if ( $this->_isNew ) {
			if (!$db->query( $this->buildInsert() )) {
				//pulling the db error hides the specifics of the SQL
				if (Cgn_ErrorStack::pullError()) {
					Cgn_ErrorStack::throwError("Cannot save data item.\n".
					$db->errorMessage, E_USER_WARNING);
				}
				return false;
			}
			$this->{$this->_pkey} = $db->getInsertId();
			$this->_isNew = false;
		} else {
			$db->query( $this->buildUpdate() );
		}
		return $this->{$this->_pkey};
//		$ret .= implode(",",$this->fields);
	}


	function load($where='') {
		$db = Cgn_DbWrapper::getHandle();
		$whereQ = '';
		if (is_array($where) ) {
			$whereQ = implode(' and ',$where);
		} else if (strlen($where) ) {
			$whereQ = $this->_pkey .' = '.$where;
		}
		$db->query( $this->buildSelect($whereQ) );
		if(!$db->nextRecord()) {
			return false;
		}
		$this->row2Obj($db->record);
		$this->_isNew = false;
		return true;
	}


	function find($where='') {
		$db = Cgn_DbWrapper::getHandle();
		$whereQ = '';
		if (is_array($where) ) {
			$whereQ = implode(' and ',$where);
		} else if (strlen($where) ) {
			$whereQ = $this->_pkey .' = '.$where;
		}
		if ($this->_debugSql) {
			cgn::debug( $this->buildSelect($whereQ) );
		}
		$db->query( $this->buildSelect($whereQ) );

		$objs = array();

		if(!$db->nextRecord()) {
			return $objs;
		}

		do {
			$x = new Cgn_DataItem($this->_table,$this->_pkey);
			$x->_excludes = $this->_excludes;
			$x->row2Obj($db->record);
			$x->_isNew = false;
			if ( $this->_rsltByPkey == true) {
				$objs[$x->{$x->_pkey}] = $x;
			} else {
				$objs[] = $x;
			}
		} while ($db->nextRecord());
		return $objs;
	}


	function delete($where='') {
		$db = Cgn_DbWrapper::getHandle();
		$whereQ = '';
		//maybe the where should be an array of IDs,
		// not an array of "x=y" ?
		/*
		if (is_array($where) ) {
			$whereQ = implode(' and ',$where);
		} else if (strlen($where) ) {
			$whereQ = $this->_pkey .' = '.$where;
		}
		*/
		$whereQ = $this->_pkey .' = '.$this->{$this->_pkey};
		$db->query( $this->buildDelete($whereQ) );
	}


	function row2Obj($row) {
		foreach ($row as $k=>$v) {
			if (in_array($k,$this->_excludes)) { continue; }
			//optionally translate k to k prime
			$this->{$k} = $v;
			$this->_colMap[$k] = $k;
		}
		$this->_isNew = false;
	}


	function getTable() {
		return $this->_tblPrefix.$this->_table;
	}


	function buildSelect($whereQ='') {
		if (count($this->_cols) > 0) {
			$cols = implode(',',$this->_cols);
		} else {
			$cols = '*';
		}
		return "SELECT ".$cols." FROM ".$this->getTable()." ".$this->buildWhere($whereQ). " ". $this->buildSort(). " " . $this->buildLimit();
	}

	function buildDelete($whereQ='') {
		return "DELETE FROM ".$this->getTable()." ".$this->buildWhere($whereQ). " " . $this->buildLimit();
	}

	function buildInsert() {
		$sql = "INSERT INTO ".$this->getTable()." ";
		$vars = get_object_vars($this);
		$keys = array_keys($vars);
		$fields = array();
		$values = array();
		foreach ($keys as $k) {
			if (substr($k,0,1) == '_') { continue; }
			$fields[] = $k;
			if (
			   isset($this->_types[$k]) &&
			   $this->_types[$k] == 'binary') {
				   //__ FIXME __ do not force mysql in this library.
				$values[] = "'".mysql_real_escape_string($vars[$k])."'";
			} else if (in_array($k,$this->_nuls) && $vars[$k] == null ) {
				$values[] = "NULL";

			} else {
				$values[] = "'".addslashes($vars[$k])."'";
			}
		}

		$sql .= ' (`'.implode('`, `',$fields).'`) ';
		$sql .= 'VALUES ('.implode(',',$values).') ';
		return $sql;
	}


	function buildUpdate() {
		$sql = "UPDATE ".$this->getTable()." SET ";
		$vars = get_object_vars($this);
		$keys = array_keys($vars);
		$fields = array();
		$values = array();
		$set = '';
		foreach ($keys as $k) {
			if (substr($k,0,1) == '_') { continue; }
			if (strlen($set) ) { $set .= ', ';}
			if (in_array($k,$this->_nuls) && $vars[$k] == null ) {
				$set .= "`$k` = NULL";
			} else {
				$set .= "`$k` = '".addslashes($vars[$k])."'";
			}
		}
		$sql .= $set;
		$sql .= ' WHERE '.$this->_pkey .' = '.$this->{$this->_pkey}.' LIMIT 1';
		return $sql;
	}



	/**
	 * construct a where clause including "WHERE "
	 */
	function buildWhere($whereQ='') {
		foreach ($this->_where as $struct) {
			if (strlen($whereQ) ) {$whereQ .= ' '.$struct['andor'].' ';}

			//fix = NULL, change to IS NULL
			//fix != NULL, change to IS NOT NULL
			if ($struct['v'] == NULL) {
				if ($struct['s'] == '=') {
					$struct['s'] = 'IS';
				}
				if ($struct['s'] == '!=') {
					$struct['s'] = 'IS NOT';
				}
			}
			$whereQ .= $struct['k'] .' '. $struct['s']. ' ';

			//if (in_array($this->_colMap,$struct['v'])) {
			if (substr($struct['v'],0,1) == '`') {
				$whereQ .= $struct['v'].' ';
			} else if ($struct['v'] == 'NULL') {
				$whereQ .= $struct['v'].' ';
			} else if ($struct['v'] == NULL) {
				$whereQ .= 'NULL'.' ';
			} else {
				$whereQ .= '"'.$struct['v'].'" ';
			}
		}

		if (strlen($whereQ) ) {$whereQ = ' where '.$whereQ;}
		return $whereQ;
	}

	function buildSort() {
		if (count($this->_sort) < 1 ) {
			return '';
		}
		$sortQ = '';
		foreach ($this->_sort as $col=>$acdc) {
			if (strlen($sortQ) ) {$sortQ .= ', ';}
			$sortQ .= ' '.$col.' '.$acdc;
		}
		return 'ORDER BY '.$sortQ;
	}

	function buildLimit() {
		/*
		$sortQ = '';
		foreach ($this->_sort as $col=>$acdc) {
			if (strlen($sortQ) ) {$sortQ .= ', ';}
			$sortQ .= ' '.$col.' '.$acdc;
		}
		return $sortQ;
		 */
		if ($this->_limit != -1) {
			return " LIMIT ".$this->_limit. " ";
		}
		return '';
	}

	function andWhere($k,$v,$s='=') {
		$this->_where[] = array('k'=>$k,'v'=>$v,'s'=>$s,'andor'=>'and');
	}

	function orWhere($k,$v,$s='=') {
		$this->_where[] = array('k'=>$k,'v'=>$v,'s'=>$s,'andor'=>'or');
	}


	function limit($l, $start=0) {
		$this->_limit = $l;
		$this->_start = $start;
	}

	function sort($col, $acdc='DESC') {
		$this->_sort[$col] = $acdc;
	}

	function _exclude($col) {
		$this->_excludes[] = $col;
	}

	function __toString() {
		return "Cgn_DataItem [table:".$this->_table."] [id:".sprintf('%d',$this->getPrimaryKey())."] [new:".($this->_isNew?'yes':'no')."] \n<br/>\n";
	}
}

?>
