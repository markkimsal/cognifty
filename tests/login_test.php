<?php

require_once('../cognifty/lib/lib_cgn_obj_store.php');
require_once('../cognifty/lib/lib_cgn_user.php');
require_once('../cognifty/lib/lib_cgn_db_connector.php');
require_once('../cognifty/lib/lib_cgn_db_mysql.php');

Mock::generate('Cgn_Db_Connector');
Mock::generate('Cgn_Db_Mysql');

class TestOfLogins extends UnitTestCase {

	function setUp() {
		static $count;

		$count++;
//		if ($count > 1) { return; }//wtf simpletest 

		$this->user = new Cgn_User();
		$this->user->username = 'testuser';
		$this->user->setPassword('testpass');

		//setup the database
		require_once('../../tests/testlib/lib_cgn_db_mock.php');
		$mockDbConnector = new Cgn_Db_MockConnector();
		Cgn_ObjectStore::storeObject('object://defaultDatabaseLayer',$mockDbConnector);

		$mysql = Cgn_Db_Connector::getHandle();
		$mysql->expectAtLeastOnce('query', array(
			"SELECT cgn_user_id FROM cgn_user
			WHERE username ='".$this->user->username."' 
			AND password = '".$this->user->password."'"
			));
		$mysql->expectAtLeastOnce('getNumRows');
		$mysql->setReturnValue('getNumRows',1);
		$mysql->record = array('number'=>1);

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
		$result = $this->user->login('testuser','testpass');
		echo "result = $result <br/>";
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
