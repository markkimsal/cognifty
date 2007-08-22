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
 *  $db->query("select * from lcUsers");
 * while ($db->nextRecord() ) {
 *  $db->query("select * from payments where username = '".$db->record['username']."'");
 *  while ($db->nextRecord() ) {
 *   print_r($db->record);
 *  }
 * }
 *
 * @abstract
 */
class Cgn_Db_Connector {
		 
	var $driverID;
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

	 
	function DB() {
	}
	 
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
	function getHandle($dsn = 'default') {
		 
		$dsnPool =& Cgn_ObjectStore::getObject('object://defaultDatabaseLayer');
		//get the list of connection setups
		//$_dsn = DB::getDSN();
		 
		// if a connection has already been made and in the handles array
		// get it out
		 
		if (@!is_object($dsnPool->_dsnHandles[$dsn]) ) {
			//createHandles stores the ref in _dsnHandles
			$dsnPool->createHandle($dsn);
		}
		$x = $dsnPool->_dsnHandles[$dsn];
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
			$dsnPool->createHandle($dsn);
		}
		return $dsnPool->_dsnHandles[$dsn];
	}
	 
	 
	/**
	 * Create a new database connection from the given DSN and store it 
	 * internally in "_dsnHandles" array.
	 */
	function createHandle($dsn='default') {
		$t = Cgn_ObjectStore::getConfig("dsn://$dsn.uri");
		$_dsn = parse_url(Cgn_ObjectStore::getConfig("dsn://$dsn.uri"));

		//make sure the driver is loaded
		$driver = $_dsn['scheme'];
		include_once(CGN_LIB_PATH.'/lib_cgn_db_'.$driver.'.php');
		$d = "Cgn_Db_$driver";
		$x = new $d();
		$x->host = $_dsn['host'];
		$x->database = substr($_dsn['path'],1);
		$x->user = $_dsn['user'];
		$x->password = @$_dsn['pass'];
//			$x->persistent = $_dsn[$dsn]['persistent'];
		$x->connect();
		$this->_dsnHandles[$dsn] = $x;
	}

	 
	/**
	 * Connect to the DB server
	 *
	 * Uses the classes internal host,user,password, and database variables
	 * @return void
	 */
	function connect() {
		 
		 
	}
	 
	 
	/**
	 * Send query to the DB
	 *
	 * Results are stored in $this->resultSet;
	 * @return  void
	 * @param  string $queryString SQL command to send
	 */
	function query($queryString) {
		 
	}
	 
	 
	/**
	 * Close connection
	 *
	 * @return void
	 */
	function close() {
		$pointer = lcDB::getHandle();
		return $pointer->close();
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
	}
	 
	 
	 
	 
	/**
	 * Short hand for query() and nextRecord().
	 *
	 * @param string $sql SQL Command
	 */
	function queryOne($sql) {
	}
		 
	function getAll($query) {
		return $this->queryGetAll($query);
	}

	function queryGetAll($query) { 
		$this->query($query);
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
		 
	}
	 
	 
	/**
	 * Moves resultSet cursor to an aribtrary position
	 *
	 * @param int $row Desired index offset
	 * @return void
	 */
	function seek($row) {
		 
	}
	 
	 
	/**
	 * Retrieves last error message from the DB
	 *
	 * @return string Error message
	 */
	function getLastError() {
		 
	}
	 
	 
	/**
	 * Return the last identity field to be created
	 *
	 * @return mixed
	 */
	function getInsertID() {

	}


	/**
	 * Return the number of rows affected by the last query
	 *
	 * @return int number of affected rows
	 */
	function getNumRows() {
		 
	}
	 
	 
	function disconnect() {
		 
	}


	function singleton($s = '') {
		static $singleton;
		if (isset($singleton)) {
			return $singleton;
		} else {
			if ($s) {
				$singleton = $s;
			}
		}
	}


	function executeQuery($query) {
		$this->RESULT_TYPE = MYSQL_ASSOC;
		//print "*** ".$query->toString() ."\n<br/>\n";
		$this->query($query->toString());
	}
	 
	 
	function &getDSN($name) {
//echo "looking for $name<Br>";
		$dsn = Cgn_ObjectStore::getObject("dsn://$name.uri");
		return $dsn;
	}
}
?>
