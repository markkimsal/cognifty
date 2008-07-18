<?php

class Cgn_Db_PdoLite extends Cgn_Db_Connector {

	var $persistent = 'n';
	var $isSelected = FALSE;
	var $pdoDriver = NULL;

	/**
	 * Connect to the DB server
	 *
	 * Uses the classes internal host,user,password, and database variables
	 * @return void
	 */
	function connect() {
		if (!is_object($this->pdoDriver)) {
			$this->pdoDriver = 	new PDO('sqlite:var/'.$this->database.'.db', $this->user, $this->password);
		}
		$this->selectDb($this->database);
	}

	function selectDb($dbName) {
		$this->isSelected = TRUE;
//		$this->pdoDriver->prepare('USE '.$dbName)->execute();
	}


	/**
	 * Send query to the DB
	 *
	 * Results are stored in $this->resultSet;
	 * @return  void
	 * @param  string $queryString SQL command to send
	 * @param  bool   $log trigger_error when there's an SQL problem
	 */
	function query($queryString, $log = TRUE) {
		if (!is_object($this->pdoDriver)) {
			return false;
		}
		$this->queryString = $queryString;

		$statement = $this->pdoDriver->query($queryString);
		if (is_object($statement)) {
			$this->errorNumber = 0;
			$this->errorMessage = '';
			$this->row += 1;
			$this->resultSet[] = $statement;
		} else {
			$this->errorNumber = $this->pdoDriver->errorCode();
			$this->errorMessage = $this->pdoDriver->errorInfo();
			$this->errorMessage = $this->errorMessage[2];
			if ($log) {
				trigger_error('database error: ('.$this->errorNumber.') '.$this->errorMessage.'
					<br/> statement was: <br/>
					'.$queryString);
			}
			return FALSE;
		}
		return TRUE;
	}


	function exec($statementString, $log = true) {
		if (!is_object($this->pdoDriver)) {
			$this->connect();
		}
		//don't try to do queries if there's no DB
		if (! $this->isSelected ) {
			$this->errorMessage = 'no schema selected.';
			return FALSE;
		}

		$this->pdoDriver->exec($queryString);
	}


	/**
	 * Close connection
	 *
	 * @return void
	 */
	function close() {
		$this->pdoDriver = NULL;
	}


	/**
	 * Grab the next record from the resultSet
	 *
	 * Returns true while there are more records, false when the set is empty
	 * Automatically frees result when the result set is emtpy
	 * @return boolean
	 * @param  int $resID Specific resultSet, default is last query
	 */
	function nextRecord($resID = false) {
		if (! $resID ) {
			$resID = count($this->resultSet) -1;
		}
		if (! isset($this->resultSet[$resID]) ) {
			return false;
		}

		$this->record = $this->resultSet[$resID]->fetch(PDO::FETCH_ASSOC);
		$this->row += 1;

		//no more records in the result set?
		$ret = is_array($this->record);
		if (! $ret ) {
			if (is_object($this->resultSet[$resID]) ) {
				$this->freeResult($resID);
			}
		}
		return $ret;
	}


	/**
	 * Clean up resources for this result.
	 * Pop the top result off the stack.
	 *
	 * @abstract
	 */
	function freeResult($resId = FALSE) {
		if (! $resId ) {
			$resId = count($this->resultSet) -1;
		}
		$this->resultSet[$resId]->closeCursor();
		unset($this->resultSet[$resId]);
		//reindex the keys
		$this->resultSet = array_merge($this->resultSet);
	}


	/**
	 * Return the last identity field to be created
	 *
	 * @return mixed
	 */
	function getInsertID() {
		return $this->pdoDriver->lastInsertId();
	}


	function setType($type='ASSOC') {
		$this->prevType = $this->RESULT_TYPE;
		if ($type=='ASSOC') {
			$this->RESULT_TYPE = MYSQL_ASSOC;
		}
		if ($type=='NUM') {
			$this->RESULT_TYPE = MYSQL_NUM;
		}
		if ($type=='BOTH') {
			$this->RESULT_TYPE = MYSQL_BOTH;
		}
	}

	/**
	 * Return the number of rows affected by the last query
	 *
	 * @return int number of affected rows
	 */
	function getNumRows($resID = FALSE) {
		if (! $resID ) {
			$resID = count($this->resultSet) -1;
		}
		
		$countResult = $this->pdoDriver->query($this->queryString);
		return count( $countResult->fetchAll() );
		/*
		if (is_object($this->resultSet[$resID]) ) {
			return $this->resultSet[$resID]->rowCount();
		}
		 */
		return NULL;
	}


	function quote($val) {
		return mysql_real_escape_string($val);
	}
}
