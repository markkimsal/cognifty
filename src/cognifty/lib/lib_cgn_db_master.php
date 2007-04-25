<?php
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
	 * while ($db->next_record() ) {
	 *  $db->query("select * from payments where username = '".$db->Record['username']."'");
	 *  while ($db->next_record() ) {
	 *   print_r($db->Record);
	 *  }
	 * }
	 *
	 * @abstract
	 */
	 
	 
	 
class cgn_DB {
		 
		var $driverID;
		// Result of mysql_connect().
		var $resultSet;
		// Result of most recent mysql_query().
		var $record = array();
		// current mysql_fetch_array()-result.
		var $Record = array();
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
		var $extraLogging = true;
		 
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
		 * Get a copy of the global instance
		 *
		 * The copy returned will use the same database connection
		 * as the global object.
		 * @return  object  copy of a db object that has the settings of a DSN entry
		 */
		function getHandle($dsn = 'default') {
			static $handles = array();
			 
			//get the list of connection setups
			//$_dsn = DB::getDSN();
			 
			// if a connection has already been made and in the handles array
			// get it out
			 
			if (@is_object($handles[$dsn]) ) {
				$x = $handles[$dsn];
			} else {
				$t = Cgn_ObjectStore::getObject("dsn://$dsn.uri");
				$_dsn = parse_url(Cgn_ObjectStore::getObject("dsn://$dsn.uri"));

				//make sure the driver is loaded
				$driver = $_dsn['scheme'];
				include_once(CGN_LIB_PATH.'/lib_cgn_db_'.$driver.'.php');
				$d = "Cgn_Db_$driver";
				$x = new $d();
				$x->host = $_dsn['host'];
				$x->database = substr($_dsn['path'],1);
				$x->user = $_dsn['user'];
				$x->password = $_dsn['pass'];
				$x->persistent = $_dsn[$dsn]['persistent'];
				$x->connect();
				$handles[$dsn] = $x;
			}
			 
			//return by value (copy) to make sure
			// nothing has access to old query results
			// keeps the same connection ID though
			return $x;
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
		function next_record($resID = false) {
		}
		 
		 
		function nextRecord($resID = false) {
			$ret = $this->next_record($resID);
			$this->record = $this->Record;
			return $ret;
		}
		 
		 
		 
		/**
		 * Return the requested column from the current record of the current result set
		 *
		 * @return mixed
		 * @param mixed           $key    Column key into the record set
		 */
		function getRecord($key) {
			$pointer = lcDB::getHandle();
			return $pointer->record[$key];
		}
		 
		 
		/**
		 * Short hand for query() and next_record().
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
		while($this->next_record()) {
			$rows[] = $this->Record;
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
		 
		 
		 
		function &getDSN_old($d = '') {
			static $dsn;
			if (isset($dsn) ) {
				return $dsn;
			} else {
				if ($d) {
					$dsn = $d;
				}
			}
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
		 
		 
		function logQuery($queryString) {
			$date = date('m-d-Y');
			$time = time();
			$u = lcUser::getCurrentUser();
			$log = $time.':'.$u->username.':' . base64_encode($queryString);
			$touch = 0;
			system("touch /tmp/lcdblog/$date.log", $touch);
			if ($touch) {
				system("mkdir /tmp/lcdblog/");
				system("touch /tmp/lcdblog/$date.log", $touch);
			}
			system("echo $log >> /tmp/lcdblog/$date.log");
		}
		 
		 
		function executeQuery($query) {
			$this->RESULT_TYPE = MYSQL_ASSOC;
			//print "*** ".$query->toString() ."\n<br/>\n";
			$this->query($query->toString());
		}
		 
		 
		function &getDSN($name) {
echo "looking for $name<Br>";
			$dsn = Cgn_ObjectStore::getObject("dsn://$name.uri");
			return $dsn;
		}
	 

	}
	 
?>
