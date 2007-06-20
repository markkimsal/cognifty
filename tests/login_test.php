<?php

require_once('../src/cognifty/lib/lib_cgn_user.php');
require_once('../src/cognifty/lib/lib_cgn_db_master.php');
require_once('../src/cognifty/lib/lib_cgn_db_mysql.php');

Mock::generate('Cgn_Db_Connector');
Mock::generate('Cgn_Db_Mysql');

class TestOfLogins extends UnitTestCase {

	function setUp() {
		$this->user = new Cgn_User();
		$this->user->username = 'testuser';
		$this->user->setPassword('testpass');
	}

	function testPassword() {
		//make sure md5 worked, 32 chars
		$this->assertEqual(strlen($this->user->password), 32);
		//test sha1 and md5
		$this->assertEqual($this->user->password, 
			'09202be3249d1bd81d509b9c9977da5b');
	}

	function testLogin() {
		$db = new MockCgn_Db_Connector();
		$mysql = new MockCgn_Db_Mysql();
		$db->setReturnReference('getHandle',$mysql);
		$db->expect('getHandle', array());

		$result = $this->user->login('testuser','testpass');
		$this->assertEqual(true, $result);
//		$this->assertEqual(32, strlen($this->simple->sessionId));
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
