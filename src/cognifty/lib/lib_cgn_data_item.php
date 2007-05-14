<?php

$g_db_handle = null;


/**
 * Cgn_DbWrapper::setHandle($a);
 * $db = Cgn_DbWrapper::getHandle();
 */
class Cgn_DbWrapper {

	function &setHandle($db='') {
		global $g_db_handle;
		$g_db_handle = $db;
	}


	function getHandle() {
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


	function load($where='') {
		$db = Cgn_DbWrapper::getHandle();
		$whereQ = '';
		if (is_array($where) ) {
			$whereQ = implode(' and ',$where);
		} else if (strlen($where) ) {
			$whereQ = $this->_pkey .' = '.$where;
		}

		$db->query( $this->buildSelect($whereQ) );
		$db->nextRecord();
		$this->row2Obj($db->record);
		$this->_isNew = false;
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
		if (strlen($whereQ) ) {$whereQ = ' where '.$whereQ;}
		return "SELECT * FROM ".$this->getTable()." ".$whereQ;
	}

}

?>
