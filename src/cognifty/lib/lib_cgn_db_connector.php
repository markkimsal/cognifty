<?php
/**
 * class to abstract mysql into LC framework
 *
 * This class wraps the mysql php function calls in
 * a layer that is used directly with the LC modules and
 * system infrastructure.  This class can easily be
 * duplicated or subclassed to work with other DBs.
 *
 * This class supports multiple result sets, wherein
 * DB queries and result sets may be stacked on top
 * of each other.
 * <i>Example:</i>
 * <code>
 *  $db->query("select * from lcUsers");
 *  while ($db->nextRecord() ) {
 *    //note that this result set does not get reset
 *    $u = $db->record['username'];
 *    $db->query("select * from payments where username = '".$u."'");
 *    while ($db->nextRecord() ) {
 *       print_r($db->record);
 *    }
 *  }
 * </code>
 *
 * @abstract
 */
class Cgn_Db_Connector {

	var $driverId = 0;
	// Result of mysql_connect().
	var $resultSet;
	// Result of most recent mysql_query().
	var $record = array();
	// current mysql_fetch_array()-result.
	var $row;
	// current row number.
	var $RESULT_TYPE;
	var $errorNumber;
	// Error number when there's an error
	var $errorMessage = "";
	// Error message when there's an error
	var $logFile = "/tmp/logfile";
	var $logFileDelimiter = "\n----\n";
	var $extraLogging = false;
	var $persistent = false;

	var $_dsnHandles = array(); //cache of objects per DSN, should be static class var.


	function log() {
		$u = lcUser::getCurrentUser();
		$name = $u->username;
		$f = fopen($this->logFile, "a+");
		fputs($f, time()." :: $name :: ".$this->queryString.$this->logFileDelimiter);
		fclose($f);
		if ($this->extraLogging) {
			$extra = strtotime(date("m/d/Y"));
			$f = fopen($this->logFile."_".$extra, "a+");
			fputs($f, time()." :: $name :: ".$this->queryString.$this->logFileDelimiter);
			fclose($f);
		}
	}

	/**
	 * Return a copy of a database connector object.
	 *
	 * Allow overriding of object creation from URIs by calling
	 *  the globally configured defaultDatabaseLayer in the object store
	 * @return  object  copy of a db object that has the settings of a DSN entry
	 */
	static function &getHandle($dsn = 'default') {

		$dsnPool =& Cgn_ObjectStore::getObject('object://defaultDatabaseLayer');
		//get the list of connection setups
		//$_dsn = DB::getDSN();

		// if a connection has already been made and in the handles array
		// get it out

		if (@!is_object($dsnPool->_dsnHandles[$dsn]) ) {
			//createHandles stores the ref in _dsnHandles
			if (!$dsnPool->createHandle($dsn) ) {
				$dsn = 'default';
			}
		}
		$x =& $dsnPool->_dsnHandles[$dsn];
		// __FIXME__ optimize the next two lines by only executing them on PHP5
		// 4 already makes a shallow copy.
		$copy = $x;
		$copy->resultSet = array();

		//return by value (copy) to make sure
		// nothing has access to old query results
		// keeps the same connection ID though
		return $copy;
	}


	/**
	 * Return a reference of a database connector object.
	 *
	 * Allow overriding of object creation from URIs by calling
	 *  the globally configured defaultDatabaseLayer in the object store
	 * @return  object  ref of a db object that has the settings of a DSN entry
	 */
	function& getHandleRef($dsn = 'default') {

		$dsnPool =& Cgn_ObjectStore::getObject('object://defaultDatabaseLayer');
		//get the list of connection setups
		//$_dsn = DB::getDSN();

		// if a connection has already been made and in the handles array
		// get it out

		if (@!is_object($dsnPool->_dsnHandles[$dsn]) ) {
			//createHandles stores the ref in _dsnHandles
			if (!$dsnPool->createHandle($dsn) ) {
				$dsn = 'default';
			}
		}
		return $dsnPool->_dsnHandles[$dsn];
	}

	/**
	 * Create a new database connection from the given DSN and store it 
	 * internally in "_dsnHandles" array.
	 */
	function createHandle($dsn='default') {
		$t = Cgn_ObjectStore::getConfig("dsn://$dsn.uri");
		if ( $t === NULL ) {
			return false;
		}

		$_dsn = parse_url(Cgn_ObjectStore::getConfig("dsn://$dsn.uri"));

		//make sure the driver is loaded
		$driver = $_dsn['scheme'];
		if (!class_exists("Cgn_Db_".$driver, false)) {
			include_once(CGN_LIB_PATH.'/lib_cgn_db_'.$driver.'.php');
		}
		$d = "Cgn_Db_$driver";
		$x = new $d();
		$x->host = $_dsn['host'];
		$x->database = substr($_dsn['path'],1);
		$x->user = $_dsn['user'];
		$x->password = @$_dsn['pass'];
//			$x->persistent = $_dsn[$dsn]['persistent'];
		$x->connect();
		$this->_dsnHandles[$dsn] = $x;
		return true;
	}


	/**
	 * Connect to the DB server
	 *
	 * Uses the classes internal host,user,password, and database variables
	 * @return void
	 *
	 * @abstract
	 */
	function connect() {}


	/**
	 * Send query to the DB
	 *
	 * Results are stored in $this->resultSet;
	 * @return  void
	 * @param  string $queryString SQL command to send
	 *
	 * @abstract
	 */
	function query($queryString) {}

	/**
	 * Send a statement to the DB
	 *
	 * Do not expect a result set
	 * @return  void
	 * @param  string $statementString  SQL command to send
	 */
	function exec($statementString) {}

	/**
	 * Close connection
	 *
	 * @return void
	 */
	function close() {
		$pointer = Cgn_Db_Connector::getHandle();
		return $pointer->close();
	}

	/**
	 * Close connection
	 *
	 * @return void
	 */
	function disconnect() {
		$pointer = Cgn_Db_Connector::getHandle();
		return $pointer->close();
	}

	/**
	 * Grab the next record from the resultSet
	 *
	 * Returns true while there are more records, false when the set is empty
	 * Automatically frees result when the result set is emtpy
	 * @return boolean
	 * @param  int $resID Specific resultSet, default is last query
	 *
	 * @abstract
	 */
	function nextRecord($resID = false) {}


	/**
	 * Clean up resources for this result.
	 * Pop the top result off the stack.
	 *
	 * @abstract
	 */
	function freeResult() {}


	/**
	 * Short hand for query() and nextRecord().
	 *
	 * @param string $sql SQL Command
	 */
	function queryOne($sql) {}

	function getAll($query) {
		return $this->queryGetAll($query);
	}

	function queryGetAll($query, $report=TRUE) { 
		$this->query($query, $report);
		$rows = array();
		while($this->nextRecord()) {
			$rows[] = $this->record;
		}
		return $rows;
	}

	function fetchAll() {
		$rows = array();
		while($this->nextRecord()) {
			$rows[] = $this->record;
		}
		return $rows;
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
	 * Prepare to stream a blob record
	 *
	 * @param string $table SQL table name
	 * @param string $col   SQL column name
	 * @param int    $id    Unique record id
	 * @param int    $pct   Size of each blob chunk as percentage of total
	 * @param string $idcol Name of column that holds identity if not table.'_id'
	 * @return array stream handle with info needed for nextChunk()
	 */
	function prepareBlobStream($table, $col, $id, $pct=10, $idcol='') {
		if ($idcol == '') {$idcol = $table.'_id';}
		$this->queryOne('SELECT CHAR_LENGTH(`'.$col.'`) as `bitlen` from `'.$table.'` WHERE `'.$idcol.'` = '.sprintf('
			%d',$id));
		$ticket = array();
		$ticket['table'] = $table;
		$ticket['col']   = $col;
		$ticket['id']    = $id;
		$ticket['pct']   = $pct;
		$ticket['idcol'] = $idcol;
		$ticket['bytelen'] = $this->record['bitlen'];
		$ticket['finished'] = false;
		$ticket['byteeach'] = floor($ticket['bytelen'] * ($pct / 100));
		$ticket['bytelast']  = $ticket['bytelen'] % ((1/$pct) * 100);
		$ticket['pctdone'] = 0;
		return $ticket;
	}

	/**
	 * Select a percentage of a blob field
	 *
	 * @param $ticket required array from prepareBlobStream()
	 */
	function nextStreamChunk(&$ticket) {
		if ($ticket['finished']) { return false; }

		$_x = (floor($ticket['pctdone']/$ticket['pct']) * $ticket['byteeach']) + 1;
		$_s = $ticket['byteeach'];

		if ($ticket['finished'] == TRUE) {
			return NULL;
		}

		if ($ticket['pctdone'] + $ticket['pct'] >= 100) {
			//grab the uneven bits with this last pull
			$_s += $ticket['bytelast'];
			$this->queryOne('SELECT SUBSTR(`'.$ticket['col'].'`,'.$_x.') 
				AS `blobstream` FROM '.$ticket['table'].' WHERE `'.$ticket['idcol'].'` = '.sprintf('%d',$ticket['id']));
		} else {
			$this->queryOne('SELECT SUBSTR(`'.$ticket['col'].'`,'.$_x.','.$_s.') 
				AS `blobstream` FROM '.$ticket['table'].' WHERE `'.$ticket['idcol'].'` = '.sprintf('%d',$ticket['id']));
		}
		$ticket['pctdone'] += $ticket['pct'];
		if ($ticket['pctdone'] >= 100) { 
			$ticket['finished'] = TRUE;
		}
		return $this->record['blobstream'];
	}


	/**
	 * Moves resultSet cursor to beginning
	 * @return void
	 * @abstract
	 */
	function reset() {}


	/**
	 * Moves resultSet cursor to an aribtrary position
	 *
	 * @param int $row Desired index offset
	 * @return void
	 * @abstract
	 */
	function seek($row) {}


	/**
	 * Retrieves last error message from the DB
	 *
	 * @return string Error message
	 * @abstract
	 */
	function getLastError() {}


	/**
	 * Return the last identity field to be created
	 *
	 * @return mixed
	 * @abstract
	 */
	function getInsertID() {}


	/**
	 * Return the number of rows affected by the last query
	 *
	 * @return int number of affected rows
	 * @abstract
	 */
	function getNumRows() {}


	function executeQuery($query) {
		//$this->RESULT_TYPE = MYSQL_ASSOC;
		//print "*** ".$query->toString() ."\n<br/>\n";
		$this->query($query->toString());
	}


	function &getDSN($name) {
		$dsn = Cgn_ObjectStore::getObject("dsn://$name.uri");
		return $dsn;
	}

	/**
	 * Return column definitions in array format
	 *
	 * @return Array   list of structures that define a table's columns.
	 */
	function getTableColumns($table) {
		return array();
	}
}
?>
