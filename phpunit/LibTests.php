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
class Cognifty_TestSuite_Lib extends PHPUnit_Framework_TestSuite
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
		require_once('lib/active_formatter_test.php');
        $suite = new Cognifty_TestSuite_Lib( 'phpUnderControl - Library Tests' );

		$suite->addTestSuite('Cgn_ActiveFormatter_Test');

        return $suite;
    }

}

if ( PHPUnit_MAIN_METHOD === 'Cognifty_LibTests::main' )
{
    Cognifty_LibTests::main();
}


