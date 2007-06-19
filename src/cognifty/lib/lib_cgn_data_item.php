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
	var $_where         = array();
	var $_filterNames   = true;
	var $_tblPrefix     = '';
	var $_isNew         = false;


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
			$db->query( $this->buildInsert() );
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
		$db->query( $this->buildSelect($whereQ) );
		if(!$db->nextRecord()) {
			return false;
		}
		$objs = array();
		do {
			$x = new Cgn_DataItem($this->_table,$this->_pkey);
			$x->row2Obj($db->record);
			$x->_isNew = false;
			$objs[$x->{$x->_pkey}] = $x;
		} while ($db->nextRecord());
		return $objs;
	}


	function row2Obj($row) {
		foreach ($row as $k=>$v) {
			//optionally translate k to k prime
			$this->{$k} = $v;
			$this->_colMap[$k] = $k;
		}
	}


	function getTable() {
		return $this->_tblPrefix.$this->_table;
	}


	function buildSelect($whereQ='') {
		return "SELECT * FROM ".$this->getTable()." ".$this->buildWhere($whereQ);
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
			} else {
				$values[] = "'".addslashes($vars[$k])."'";
			}
		}

		$sql .= ' (`'.implode('`,`',$fields).'`) ';
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
			$set .= "`$k` = '".addslashes($vars[$k])."'";
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
			$whereQ .= $struct['k'] .' '. $struct['s']. ' ';

			//if (in_array($this->_colMap,$struct['v'])) {
			if (substr($struct['v'],0,1) == '`') {
				$whereQ .= $struct['v'].' ';
			} else {
				$whereQ .= '"'.$struct['v'].'" ';
			}
		}

		if (strlen($whereQ) ) {$whereQ = ' where '.$whereQ;}
		return $whereQ;
	}


	function andWhere($k,$v,$s='=') {
		$this->_where[] = array('k'=>$k,'v'=>$v,'s'=>$s,'andor'=>'and');
	}
}

?>
