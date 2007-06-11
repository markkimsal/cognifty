<?php
include_once(CGN_LIB_PATH."/lib_cgn_db_master.php");

/*************************************************** 
 *
 * This file is under the LogiCreate Public License
 *
 * A copy of the license is in your LC distribution
 * called license.txt.  If you are missing this
 * file you can obtain the latest version from
 * http://logicreate.com/license.html
 *
 * LogiCreate is copyright by Tap Internet, Inc.
 * http://www.tapinternet.com/
 ***************************************************/

	// mysql-specific functions for LC_db
	//
	 
	class Cgn_Db_Mysql extends Cgn_Db_Connector {
		 
		var $RESULT_TYPE = MYSQL_ASSOC;
		 
		/**
		 * Connect to the DB server
		 *
		 * Uses the classes internal host,user,password, and database variables
		 * @return void
		 */
		function connect() {
			 
			if ($this->driverID == 0 ) {
				if ($this->persistent == 'y') {
					$this->driverID = @mysql_pconnect($this->host, $this->user, $this->password);
				} else {
					$this->driverID = @mysql_connect($this->host, $this->user, $this->password);
				}
				if (!$this->driverID) {
			//		$this->halt();
				}
			}
			if ($this->driverID) {
				mysql_select_db($this->database, $this->driverID);
			}
		}
		 
		 
		/**
		 * Send query to the DB
		 *
		 * Results are stored in $this->resultSet;
		 * @return  void
		 * @param  string $queryString SQL command to send
		 */
		 
		function query($queryString, $log = true) {
			
			$this->queryString = $queryString;
			$start = microtime();
			if ($this->driverID == 0 ) {
				$this->connect();
			}
			 
			$resSet = mysql_query($queryString, $this->driverID);
			$this->row = 0;
			 
			if (!$resSet ) {
				$this->errorNumber = mysql_errno();
				$this->errorMessage = mysql_error();
				if ($log) {
					trigger_error('database error: ('.$this->errorNumber.') '.$this->errorMessage.'
						<br/> statement was: <br/>
						'.$queryString);
				}
			}
			if (is_resource($resSet) )
				$this->resultSet[] = $resSet;
			 
			/*
			$end = microtime();
			$j = split(" ", $start);
			$s = $j[1] = $j[0];
			$f = split(" ", $end);
			$e = $f[1] = $f[0];
			if (($e-$s) >.1) {
				// slow query
			}
			$this->exectime = abs($e-$s);
			$this->log();
			*/
		}
		 
		 
		/**
		 * Close connection
		 *
		 * @return void
		 */
		function close() {
			mysql_close($this->driverID);
		}
		 
		 
		/**
		 * Grab the next record from the resultSet
		 *
		 * Returns true while there are more records, false when the set is empty
		 * Automatically frees result when the result set is emtpy
		 * @return boolean
		 * @param  int $resID Specific resultSet, default is last query
		 */
		function next_record($resID = false) {
			if (! $resID ) {
				$resID = count($this->resultSet) -1;
			}
			 
			$this->Record = mysql_fetch_array($this->resultSet[$resID], $this->RESULT_TYPE);
			$this->row += 1;
			 
			//no more records in the result set?
			$ret = is_array($this->Record);
			if (! $ret ) {
				if (is_resource($this->resultSet[$resID]) ) {
					mysql_free_result($this->resultSet[$resID]);
					array_pop($this->resultSet);
				}
			}
			$this->record = &$this->Record;
			return $ret;
		}
		 
		 
		function nextRecord($resID = false) {
			$ret = $this->next_record($resID);
			$this->record = &$this->Record;
			return $ret;
		}
		 
		 
		/**
		 * Short hand for query() and next_record().
		 *
		 * @param string $sql SQL Command
		 */
		function queryOne($sql) {
			$this->query($sql);
			$this->next_record();
			array_pop($this->resultSet);
		}
		function queryGetOne($sql) {
			$this->query($sql);
			$this->next_record();
			return $this->Record;
		} 
		/**
		 * Short hand way to send a select statement.
		 *
		 * @param string $table  SQL table name
		 * @param string $fields  Column names
		 * @param string $where  Additional where clause
		 * @param string $orderby Optional orderby clause
		 */
		function select($table, $fields = "*", $where = "", $orderby = "") {
			if ($where) {
				$where = " where $where";
			}
			if ($orderby) {
				$orderby = " order by $orderby";
			}
			$sql = "select $fields from $table $where $orderby";
			$this->query($sql);
		}
		 
		 
		/**
		 * Short hand way to send a select statement and pull back one result.
		 *
		 * @param string $table  SQL table name
		 * @param string $fields  Column names
		 * @param string $where  Additional where clause
		 * @param string $orderby Optional orderby clause
		 */
		function selectOne($table, $fields = "*", $where = "", $orderby = "") {
			if ($where) {
				$where = " where $where";
			}
			if ($orderby) {
				$orderby = " order by $orderby";
			}
			$sql = "select $fields from $table $where $orderby";
			$this->queryOne($sql);
		}
		 
		 
		/**
		 * Halt execution after a fatal DB error
		 *
		 * Called when the last query to the DB produced an error.
		 * Exiting from the program ensures that no data can be
		 * corrupted.  This is called only after fatal DB queries
		 * such as 'no such table' or 'syntax error'.
		 *
		 * @return void
		 */
		function halt() {
			print "We are having temporary difficulties transmitting to our database.  We recommend you stop for a few minutes, and start over again from the beginning of the website.  Thank you for your patience.";
			printf("<b>Database Error</b>: (%s) %s<br>%s\n", $this->errorNumber, $this->errorMessage, $this->queryString);
			exit();
		}
		 
		 
		 
		/**
		 * Moves resultSet cursor to beginning
		 * @return void
		 */
		function reset() {
			mysql_data_seek($this->Query_ID, 0);
		}
		 
		 
		/**
		 * Moves resultSet cursor to an aribtrary position
		 *
		 * @param int $row Desired index offset
		 * @return void
		 */
		function seek($row) {
			mysql_data_seek($this->resultSet, $row);
			$this->row = $row;
		}
		 
		 
		/**
		 * Retrieves last error message from the DB
		 *
		 * @return string Error message
		 */
		function getLastError() {
			$this->errorNumber = mysql_errno();
			$this->errorMessage = mysql_error();
			return $this->errorMessage;
		}
		 
		 
		/**
		 * Return the last identity field to be created
		 *
		 * @return mixed
		 */
		function getInsertID() {
			return mysql_insert_id($this->driverID);
		}
		 
		 
		/**
		 * Return the number of rows affected by the last query
		 *
		 * @return int number of affected rows
		 */
		function getNumRows() {
			$resID = count($this->resultSet) -1;
			return @mysql_num_rows($this->resultSet[$resID]);
		}
		 
		 
		 
		function disconnect() {
			mysql_close();
		}
		 
		function getTables() {
			$this->query("show tables");
			$j = $this->RESULT_TYPE;
			$this->RESULT_TYPE = MYSQL_BOTH;
			while ($this->next_record()) {
				$x[] = $this->Record[0];
			}
			$this->RESULT_TYPE = $j;
			return $x;
		}
		 
		function getTableIndexes($table = '') {
			$this->query("show index from $table");
			while ($this->next_record()) {
				extract($this->Record);
				$_idx[$Key_name][$Seq_in_index]['column'] = $Column_name;
				$_idx[$Key_name][$Seq_in_index]['unique'] = $Non_unique;
			}
			return $_idx;
		}
		 
		function getTableColumns($table = '') {
			if ($this->driverID == 0 ) {
				$this->connect();
			}
#			$dbfields = $this->queryGetAll("show columns from $table");
#print_r($dbfields);
			$dbfields = mysql_list_fields($this->database, $table, $this->driverID);
			if (!$dbfields) {
				return false;
			}
			$columns = mysql_num_fields($dbfields);
			$this->RESULT_TYPE = MYSQL_ASSOC;
			for ($i = 0; $i < $columns; $i++) {
				$name = mysql_field_name($dbfields, $i);
				if (($this->RESULT_TYPE == MYSQL_ASSOC) || ($this->RESULT_TYPE == MYSQL_BOTH) ) {
					$field[name][$name] = $name;
					$field[type][$name] = mysql_field_type($dbfields, $i);
					$field[len][$name] = mysql_field_len($dbfields, $i);
					$field[flags][$name] = mysql_field_flags($dbfields, $i);
				}
				if (($this->RESULT_TYPE == MYSQL_NUM) || ($this->RESULT_TYPE == MYSQL_BOTH) ) {
					$field[name][] = $name;
					$field[type][] = mysql_field_type($dbfields, $i);
					$field[len][] = mysql_field_len($dbfields, $i);
					$field[flags][] = mysql_field_flags($dbfields, $i);
				}
			}
			$this->query("describe $table");
			while ($this->next_record()) {
				$type = $this->Record['Type'];
				$name = $this->Record['Field'];
				if (eregi("\(", $type)) {
					list($type, $junk) = split("\(", $type);
					if ($type == 'enum') {
						$type .= "(".$junk;
					}
				} else {
					$field['len'][$name] = '';
				}
				$field['type'][$name] = $type;
			}
			return $field;
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
		 

		function quote($val) { 
			return mysql_real_escape_string($val);
		}
	}
?>
