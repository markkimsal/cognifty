<?php

/*
 * static init
 */
if (! defined('TRN_DATA_ITEM_INIT')) {
	global $g_db_handle;
	$db = Cgn_ObjectStore::getObject('object://defaultDatabaseLayer');
	Cgn_DbWrapper::setHandle(Cgn_Db_Connector::getHandle());
	define('TRN_DATA_ITEM_INIT', TRUE);
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
	var $_bins          = array();		//list of columns that can hold binary 
	var $_limit         = -1;
	var $_start         = -1;
	var $_sort          = array();
	var $_groupBy       = array();
	var $_orderBy       = array();
	var $_filterNames   = TRUE;
	var $_tblPrefix     = '';
	var $_isNew         = FALSE;
	var $_debugSql      = FALSE;
	var $_rsltByPkey    = TRUE;
//	var $_dsnName       = 'default';


	/**
	 * Initialize a new data item.
	 *
	 * Sets "_isNew" to true, load() and find() set _isNew to false
	 *
	 * @param String $table 	the name of the table in the database
	 * @param String $pkey 		if left empty, pkey defaults to $t.'_id'
	 * @constructor
	 * @see load
	 * @see find
	 */
	function Cgn_DataItem($t,$pkey='') {
		$this->_table = $t;
		$this->_pkey = $pkey;
		if (!$this->_pkey) {
			$this->_pkey = $this->_table.'_id';
		}
		//set the pkey to null to stop notices
		$this->{$this->_pkey} = NULL;
		$this->_isNew = TRUE;
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
		return @$this->{$this->_pkey};
	}

	/**
	 * Insert or update
	 *
	 * @return mixed FALSE on failure, integer primary key on success
	 */
	function save() {
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
			if (!$db->query( $this->buildUpdate() ) ) {
				return false;
			}
		}
		return $this->{$this->_pkey};
	}


	/**
	 * Load one record from the DB
	 *
	 * @param string $where  Optional: if an array, it is imploded with " and ", 
	 *   if it is a string, it is added as a condition for the pkey
	 */
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
		$db->freeResult();
		if (empty($db->record)) {
			return false;
		}
		$this->row2Obj($db->record);
		$this->_isNew = false;
		return TRUE;
	}

	/**
	 * Load multiple records from the DB
	 *
	 * @param string $where  Optional: if an array, it is imploded with " and ", 
	 *   if it is a string it is treated as the first part of the where clause
	 */

	function find($where='') {
		$db = Cgn_DbWrapper::getHandle();
		$whereQ = '';
		if (is_array($where) ) {
			$whereQ = implode(' and ',$where);
		} else {
			$whereQ = $where;
		}
		/*
		} else if (strlen($where) ) {
			$whereQ = $this->_pkey .' = '.$where;
		 */
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
			if ( $this->_rsltByPkey == TRUE) {
				if (! isset($db->record[$x->_pkey])) {
					$objs[] = $x;
				} else {
					$objs[$db->record[$x->_pkey]] = $x;
				}
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
		if (! isset($this->{$this->_pkey}) && $where != '') {
			$this->{$this->_pkey} = $where;
		}
		if ( isset($this->{$this->_pkey}) ) {
			$whereQ = $this->_pkey .' = "'.$this->{$this->_pkey}.'"';
		}
		$db->query( $this->buildDelete($whereQ) );
	}

	function getUnlimitedCount($where='') {
		$db = Cgn_DbWrapper::getHandle();
		$whereQ = '';
		if (is_array($where) ) {
			$whereQ = implode(' and ',$where);
		} else if (strlen($where) ) {
			$whereQ = $this->_pkey .' = '.$where;
		}
		$db->query( $this->buildCountSelect($whereQ) );
		if(!$db->nextRecord()) {
			return false;
		}
		$db->freeResult();
		if (empty($db->record)) {
			return false;
		}
		return $db->record['total_rec'];
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
		return "SELECT ".$cols." FROM ".$this->getTable()." ".$this->buildJoin(). " ".$this->buildWhere($whereQ). " ". $this->buildSort(). " ". $this->buildGroup() ." " . $this->buildOrder()." " . $this->buildLimit();
	}

	function buildCountSelect($whereQ='') {
		$cols = 'count(*) as total_rec';
		return "SELECT ".$cols." FROM ".$this->getTable()." ".$this->buildJoin(). " ".$this->buildWhere($whereQ). " ". $this->buildSort(). " ". $this->buildGroup() ;
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
			if ( in_array($k,$this->_bins) ) {
				   //__ FIXME __ do not force mysql in this library.
				$values[] = "_binary'".mysql_real_escape_string($vars[$k])."'";
			} else if (in_array($k,$this->_nuls) && $vars[$k] == NULL ) {
				//intentionally doing a double equals here, 
				// if the col is nullabe, try real hard to insert a NULL
				$values[] = "NULL";

			} else {
				//add slashes works just like mysql_real_escape_string
				// (for latin1 and UTF-8) but is faster and testable.
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
			if (in_array($k,$this->_nuls) && $vars[$k] == NULL ) {
				$set .= "`$k` = NULL";
			} else {
				$set .= "`$k` = '".addslashes($vars[$k])."'";
			}
		}
		$sql .= $set;
		$sql .= ' WHERE '.$this->_pkey .' = '.$this->{$this->_pkey}.' LIMIT 1';
		return $sql;
	}

	function buildJoin() {
		$sql = '';
		foreach ($this->_relatedSingle as $_idx => $rel) {
			$tbl = $rel['table'];
			$als = $rel['alias'];
			$fk  = $rel['fk'];
			$lk  = $rel['lk'];
			$sql .= 'LEFT JOIN `'.$tbl.'` AS '.$als.' 
				ON '.$this->_table.'.'.$lk.' = '.$als.'.`'.$fk.'` ';
		}
		return $sql;
	}

	/**
	 * construct a where clause including "WHERE "
	 */
	function buildWhere($whereQ='') {
		foreach ($this->_where as $struct) {
			$v     = $struct['v'];
			$s     = $struct['s'];
			$k     = $struct['k'];
			$andor = $struct['andor'];
			if (strlen($whereQ) ) {$whereQ .= ' '.$andor.' ';}

			if (isset($struct['subclauses'])) {
				$whereQ .= '(';
			}

			$atom = $this->_whereAtomToString($struct);

			if (isset($struct['subclauses'])) {
				foreach ($struct['subclauses'] as $cl) {
					$whereQ .= $this->_whereAtomToString($cl, $atom);
				}
				$whereQ .= ')';
			} else {
				$whereQ .= $atom;
			}

		}
		if (strlen($whereQ) ) {$whereQ = ' where '.$whereQ;}
		return $whereQ;
	}

	/**
	 * Convert a where structure into a string, one part at time
	 */
	function _whereAtomToString($struct, $atom='') {
		$v     = $struct['v'];
		$s     = $struct['s'];
		$k     = $struct['k'];
		$andor = $struct['andor'];
		if (strlen($atom) ) {$atom .= ' '.$andor.' ';}

		//fix = NULL, change to IS NULL
		//fix != NULL, change to IS NOT NULL
		if ($v === NULL && in_array($k, $this->_nuls)) {
			if ($s == '=') {
				$s = 'IS';
			}
			if ($s == '!=') {
				$s = 'IS NOT';
			}
		}
		$atom .= $k .' '. $s. ' ';

		//if (in_array($this->_colMap,$v)) {
		if (is_string($v) && $v !== 'NULL') {
			$atom .= '"'.addslashes($v).'" ';
		} else if ( is_int($v) || is_float($v)) {
			$atom .= $v.' ';
		} else if (is_array($v) && $s == 'IN') {
			$atom .= '('.implode(',', $v).') ';
		} else if (substr($v,0,1) == '`') {
			$atom .= $v.' ';
		} else if ($v === 'NULL') {
			$atom .= $v.' ';
		} else if ($v === NULL) {
			$atom .= 'NULL'.' ';
		} else {
			$atom .= '"'.addslashes($v).'" ';
		}
		return $atom;
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
			return " LIMIT ".($this->_start * $this->_limit).", ".$this->_limit." ";
		}
		return '';
	}

	function buildOrder() {
		if (count($this->_orderBy) > 0) {
			return " ORDER BY  ".implode(',',$this->_orderBy);
		}
		return '';
	}


	function buildGroup() {
		if (count($this->_groupBy) > 0) {
			return " GROUP BY  ".implode(',',$this->_groupBy);
		}
		return '';
	}


	function andWhere($k,$v,$s='=') {
		$this->_where[] = array('k'=>$k,'v'=>$v,'s'=>$s,'andor'=>'and');
	}

	function orWhere($k,$v,$s='=') {
		$this->_where[] = array('k'=>$k,'v'=>$v,'s'=>$s,'andor'=>'or');
	}

	function orWhereSub($k,$v,$s='=') {
		$where = array_pop($this->_where);
		$where['subclauses'][] = array('k'=>$k,'v'=>$v,'s'=>$s,'andor'=>'or');
		$this->_where[] = $where;
	}

	function andWhereSub($k,$v,$s='=') {
		$where = array_pop($this->_where);
		$where['subclauses'][] = array('k'=>$k,'v'=>$v,'s'=>$s,'andor'=>'and');
		$this->_where[] = $where;
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

	function groupBy($col) {
		$this->_groupBy[] = $col;
	}

	function orderBy($col) {
		$this->_orderBy[] = $col;
	}

	function initBlank() {
		$db = Cgn_DbWrapper::getHandle();
		//TODO: this is mysql specific, move to driver
		$db->query('SHOW COLUMNS FROM `'.$this->_table.'`');
		while ($db->nextRecord() ){
			$this->{$db->record['Field']} = $db->record['Default'];
		}
	}

	function hasMany($table, $alias='') {
		if ($alias == '') { $alias = 'T'.count($this->_relatedMany);}
		$this->_relatedMany[] = array('table'=>$table, 'alias'=>$alias);
	}

	function hasOne($table, $fk = '', $alias='', $lk = '') {
		if ($alias == '') { $alias = 'T'.count($this->_relatedSingle);}
		if ($fk == '') { $fk = $table.'_id';}
		if ($lk == '') { $lk = $this->getTable().'_id'; }
		$this->_relatedSingle[] = array('fk'=>$fk, 'table'=>$table, 'alias'=>$alias, 'lk'=>$lk);
	}

	function __toString() {
		return "Cgn_DataItem [table:".$this->_table."] [id:".sprintf('%d',$this->getPrimaryKey())."] [new:".($this->_isNew?'yes':'no')."] \n<br/>\n";
	}

	/**
	 * Used for debugging
	 */
	function echoSelect($whereQ='') {
		echo "<pre>\n";
		echo $this->buildSelect($whereQ);
		echo "</pre>\n";
	}

	function echoDelete($whereQ='') {
		echo $this->buildDelete($whereQ);
	}

	function echoInsert($whereQ = '') {
		echo "<pre>\n";
		echo $this->buildInsert($whereQ);
		echo "</pre>\n";
	}

	function echoUpdate($whereQ = '') {
		echo "<pre>\n";
		echo $this->buildUpdate($whereQ);
		echo "</pre>\n";
	}
}

?>
