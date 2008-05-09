<?php

require_once(CGN_LIB_PATH.'/lib_cgn_obj_store.php');
require_once(CGN_LIB_PATH.'/lib_cgn_user.php');
require_once(CGN_LIB_PATH.'/lib_cgn_db_connector.php');
require_once(CGN_LIB_PATH.'/lib_cgn_db_mysql.php');

/*
Mock::generate('Cgn_Db_Connector');
Mock::generate('Cgn_Db_Mysql');
 */

class TestOfLogins extends PHPUnit_Framework_TestCase {

	function setUp() {
		static $count;

		$this->user = new Cgn_User();
		$this->user->username = 'testuser';
		$this->user->setPassword('testpass');
	}

	function testPassword() {
		//make sure md5 worked, 32 chars
		$this->assertEquals(strlen($this->user->password), 32);
		//test sha1 and md5
		$this->assertEquals($this->user->password, 
			'09202be3249d1bd81d509b9c9977da5b');
	}

	function testAddUser() {

		$this->user->email = 'testuser';
		$result = Cgn_User::registerUser($this->user);
		$this->assertEquals($result, TRUE);
	}

	function testLogin() {
//		$this->setupLogin();
		$result = $this->user->login('testuser','testpass');
		$this->assertEquals(true, $result);
	}

	function setupLogin() {

		//setup the database
		/*
		require_once('../phpunit/mockobj/lib_cgn_db_mock.php');
		$testConnector = new Cgn_Db_MockConnector();
		Cgn_ObjectStore::storeObject('object://defaultDatabaseLayer',$testConnector);
		 */
		/*
		$x = Cgn_Db_Connector::getHandle();
		Cgn_DbWrapper::setHandle($x);
		 */

		$mysql = Cgn_Db_Connector::getHandle();
		$mysql->expects($this->atLeastOnce())
			->method('query')
			->with( $this->equalTo(
					"SELECT cgn_user_id, email FROM cgn_user
			WHERE username ='".$this->user->username."' 
			AND password = '".$this->user->password."'"
				));

		$mysql->expects($this->once())
			->method('getNumRows')
			->will( $this->returnValue(1));

		$mysql->expects($this->once())
			->method('nextRecord')
			->will( $this->returnValue(true));

		$mysql->record = array('number'=>1, 'cgn_user_id'=>1);
		$testConnector->_dsnHandles['default'] = $mysql;
	}

	/*
	function testAddVals() {
		$start = count($_SESSION);
		$this->simple->set('foo','bar');
		$end = count($_SESSION);
		$this->assertEquals($start+1, $end);
	}
	 */

}
?>
