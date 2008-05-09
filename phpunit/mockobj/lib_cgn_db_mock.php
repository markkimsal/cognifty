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
class Cgn_Db_MockConnector extends Cgn_Db_Connector {
		 
 
	 
	/**
	 * Create a new database connection from the given DSN and store it 
	 * internally in "_dsnHandles" array.
	 */
	function createHandle($dsn='default') {
		$t = Cgn_ObjectStore::getConfig("dsn://$dsn.uri");
		$_dsn = parse_url(Cgn_ObjectStore::getConfig("dsn://$dsn.uri"));

		//make sure the driver is loaded
		$driver = $_dsn['scheme'];
//		include_once(CGN_LIB_PATH.'/lib_cgn_db_'.$driver.'.php');
		$x = $this->makeMock("Cgn_Db_".ucfirst($driver));
//		$x = new $d();
		$x->host = $_dsn['host'];
		$x->database = substr($_dsn['path'],1);
		$x->user = $_dsn['user'];
		$x->password = @$_dsn['pass'];
//		$x->persistent = $_dsn[$dsn]['persistent'];
		$x->connect();
		$this->_dsnHandles[$dsn] = $x;
	}

    protected function makeMock($className, array $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = TRUE, $callOriginalClone = TRUE, $callAutoload = TRUE)
    {
        if (!is_string($className) || !is_string($mockClassName)) {
            throw new InvalidArgumentException;
        }

        $mock = PHPUnit_Framework_MockObject_Mock::generate(
          $className,
          $methods,
          $mockClassName,
          $callOriginalConstructor,
          $callOriginalClone,
          $callAutoload
        );

        $mockClass  = new ReflectionClass($mock->mockClassName);
        $mockObject = $mockClass->newInstanceArgs($arguments);

        $this->mockObjects[] = $mockObject;

        return $mockObject;
    }

}
?>
