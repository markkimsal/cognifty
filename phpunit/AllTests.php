<?php

if ( defined( 'PHPUnit_MAIN_METHOD' ) === false )
{
    define( 'PHPUnit_MAIN_METHOD', 'Cognifty_AllTests::main' );
}

define('BASE_DIR',dirname(__FILE__).'/../src/');
chdir(BASE_DIR);

require('boot/bootstrap.php');

/**
 *  __ FIXME __ move this somewhere else, use ini system
 */
include(CGN_LIB_PATH.'/lib_cgn_user.php');
include(CGN_LIB_PATH.'/lib_cgn_data_item.php');
include(CGN_LIB_PATH.'/lib_cgn_cleaner.php');
include(CGN_LIB_PATH.'/lib_cgn_util.php');
include(CGN_LIB_PATH.'/lib_cgn_error.php');


/*
$dsnPool =& Cgn_ObjectStore::getObject('object://defaultDatabaseLayer');
var_dump($dsnPool);
exit();
 */

/**
 */
class Cognifty_TestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Test suite main method.
     *
     * @return void
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run( self::suite() );
    }

    protected function setUp()
	{
	}


	public function __destruct() {
	//	$this->deleteTestDb();
	}

	protected function tearDown() {
	}

   
    /**
     * Creates the phpunit test suite for this package.
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
		//self::deleteTestDb();
		self::createTestDb();
		require('../phpunit/login_test.php');
		require('../phpunit/session_test.php');
		require('../phpunit/content_test.php');
		require('../phpunit/user_test.php');
        $suite = new Cognifty_TestSuite( 'phpUnderControl - AllTests' );

		$suite->addTestSuite('TestOfLogins');
		$suite->addTestSuite('TestOfSession');
		$suite->addTestSuite('TestOfContent');
		$suite->addTestSuite('TestOfUsers');

        return $suite;
    }

	public static function createTestDb() {
		$dsn = 'mysql://root:mysql@localhost/cognifty_test';
		Cgn_ObjectStore::storeConfig("dsn://default.uri", $dsn);

		$dsnPool =& Cgn_ObjectStore::getObject('object://defaultDatabaseLayer');
	    $dsnPool->createHandle($dsn='default');
		$db = Cgn_Db_Connector::getHandle();
		$db->isSelected = TRUE;
		$db->query('drop database `cognifty_test`');
		$db->query('create database `cognifty_test`');
		$db->connect();
		Cgn_DbWrapper::setHandle($db);

		$installDir = 'cognifty/modules/install';
		for ($x=1; $x <= 29; $x++) {
			$installTableSchemas = array();
			include($installDir.'/sql/schema_'.sprintf('%02d',$x).'.php');
			if (count($installTableSchemas)<1 ) {
				next;
			}
			foreach ($installTableSchemas as $schema) {
				if (trim($schema) == '') { continue;}
				if (!$db->query($schema)) {
					echo "query failed. ($x)\n";
					echo $db->errorMessage."\n";
					//print_r($schema);
					return false;
				}
			}
		}
	}

	public function deleteTestDb() {
		$db = Cgn_Db_Connector::getHandle();
		$db->query('drop database `cognifty_test`');
	}
}

if ( PHPUnit_MAIN_METHOD === 'Cognifty_AllTests::main' )
{
    Cognifty_AllTests::main();
}


 
class MySuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        return new MySuite('MyTest');
    }
 
    protected function setUp()
    {
        print "\nMySuite::setUp()";
    }
 
    protected function tearDown()
    {
        print "\nMySuite::tearDown()";
    }
}

