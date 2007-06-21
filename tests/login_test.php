<?php

require_once('../cognifty/lib/lib_cgn_obj_store.php');
require_once('../cognifty/lib/lib_cgn_user.php');
require_once('../cognifty/lib/lib_cgn_db_connector.php');
require_once('../cognifty/lib/lib_cgn_db_mysql.php');

Mock::generate('Cgn_Db_Connector');
Mock::generate('Cgn_Db_Mysql');

class TestOfLogins extends UnitTestCase {

	function setUp() {
		$this->user = new Cgn_User();
		$this->user->username = 'testuser';
		$this->user->setPassword('testpass');

		//setup the database
		require_once('../../tests/testlib/lib_cgn_db_mock.php');
		$mockDbConnector = new Cgn_Db_MockConnector();
		$mockDbConnector->_dsnHandles['default'] = $mysql;
		Cgn_ObjectStore::storeObject('object://defaultDatabaseLayer',$mockDbConnector);
	}

	function testPassword() {
		//make sure md5 worked, 32 chars
		$this->assertEqual(strlen($this->user->password), 32);
		//test sha1 and md5
		$this->assertEqual($this->user->password, 
			'09202be3249d1bd81d509b9c9977da5b');
	}

	function testLogin() {

		$mysql =& Cgn_Db_Connector::getHandle();
		$mysql->expectOnce('query', array(
			"SELECT count(*) as number FROM cgn_users
			WHERE username ='testuser' 
			AND password = '09202be3249d1bd81d509b9c9977da5b'"
			));
		$mysql->record = array('number'=>1);
		$result = $this->user->login('testuser','testpass');
		$this->assertEqual(true, $result);
	}

	/*
	function testAddVals() {
		$start = count($_SESSION);
		$this->simple->set('foo','bar');
		$end = count($_SESSION);
		$this->assertEqual($start+1, $end);
	}
	 */

}
?>
